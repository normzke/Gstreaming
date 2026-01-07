<?php
/**
 * Automatic Payment Processing System
 * Handles payment status updates and subscription management
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/paystack_integration.php';

class PaymentProcessor
{
    private $db;
    private $conn;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->conn = $this->db->getConnection();
    }

    /**
     * Process M-PESA payment callback
     */
    public function processMpesaCallback($callbackData)
    {
        try {
            $merchantRequestId = $callbackData['MerchantRequestID'] ?? '';
            $checkoutRequestId = $callbackData['CheckoutRequestID'] ?? '';
            $resultCode = $callbackData['ResultCode'] ?? '';
            $resultDesc = $callbackData['ResultDesc'] ?? '';
            $mpesaReceiptNumber = $callbackData['MpesaReceiptNumber'] ?? '';
            $amount = $callbackData['Amount'] ?? 0;
            $phoneNumber = $callbackData['PhoneNumber'] ?? '';

            // Find payment by merchant request ID
            $paymentQuery = "SELECT p.*, u.id as user_id, u.email, u.first_name, u.last_name, pk.name as package_name 
                           FROM payments p 
                           JOIN users u ON p.user_id = u.id 
                           LEFT JOIN packages pk ON p.package_id = pk.id
                           WHERE p.merchant_request_id = ?";
            $paymentStmt = $this->conn->prepare($paymentQuery);
            $paymentStmt->execute([$merchantRequestId]);
            $payment = $paymentStmt->fetch();

            if (!$payment) {
                error_log("Payment not found for merchant request ID: " . $merchantRequestId);
                return false;
            }

            if ($resultCode == 0) {
                // Payment successful
                $this->updatePaymentStatus($payment['id'], 'completed', $mpesaReceiptNumber);
                $this->activateSubscription($payment['user_id'], $payment['package_id']);
                $this->sendPaymentConfirmation($payment);

                // Log activity
                logActivity($payment['user_id'], 'payment_completed', 'Payment completed successfully via M-PESA');

                return true;
            } else {
                // Payment failed
                $this->updatePaymentStatus($payment['id'], 'failed', null, $resultDesc);
                $this->sendPaymentFailureNotification($payment, $resultDesc);

                // Log activity
                logActivity($payment['user_id'], 'payment_failed', 'Payment failed: ' . $resultDesc);

                return false;
            }

        } catch (Exception $e) {
            error_log("Payment processing error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Process Paystack payment callback
     */
    public function processPaystackCallback($reference)
    {
        try {
            $paystack = new PaystackIntegration();
            $verification = $paystack->verifyTransaction($reference);

            if (!$verification['success']) {
                error_log("Paystack verification failed for reference: " . $reference);
                return [
                    'success' => false,
                    'message' => $verification['message']
                ];
            }

            $data = $verification['data'];

            // Find payment by reference
            $paymentQuery = "SELECT p.*, u.id as user_id, u.email, u.first_name, u.last_name, pk.name as package_name 
                           FROM payments p 
                           JOIN users u ON p.user_id = u.id 
                           LEFT JOIN packages pk ON p.package_id = pk.id
                           WHERE p.paystack_reference = ?";
            $paymentStmt = $this->conn->prepare($paymentQuery);
            $paymentStmt->execute([$reference]);
            $payment = $paymentStmt->fetch();

            if (!$payment) {
                error_log("Payment not found for Paystack reference: " . $reference);
                return [
                    'success' => false,
                    'message' => 'Payment record not found'
                ];
            }

            if ($data['status'] === 'success') {
                // Payment successful
                $this->updatePaymentStatus($payment['id'], 'completed', null, null, $reference, json_encode($data));
                $this->activateSubscription($payment['user_id'], $payment['package_id']);
                $this->sendPaymentConfirmation($payment);

                // Log activity
                logActivity($payment['user_id'], 'payment_completed', 'Payment completed successfully via Paystack');

                return [
                    'success' => true,
                    'message' => 'Payment processed successfully'
                ];
            } else {
                // Payment failed
                $failureReason = $data['gateway_response'] ?? 'Transaction was not successful';
                $this->updatePaymentStatus($payment['id'], 'failed', null, $failureReason, $reference, json_encode($data));
                $this->sendPaymentFailureNotification($payment, $failureReason);

                // Log activity
                logActivity($payment['user_id'], 'payment_failed', 'Paystack payment failed: ' . $failureReason);

                return [
                    'success' => false,
                    'message' => 'Payment was not successful'
                ];
            }

        } catch (Exception $e) {
            error_log("Paystack processing error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred during payment processing'
            ];
        }
    }

    /**
     * Update payment status
     */
    private function updatePaymentStatus($paymentId, $status, $mpesaReceiptNumber = null, $failureReason = null, $paystackReference = null, $paystackResponse = null)
    {
        $updateQuery = "UPDATE payments SET status = ?, mpesa_receipt_number = ?, failure_reason = ?, paystack_reference = ?, paystack_response = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        $updateStmt = $this->conn->prepare($updateQuery);
        $updateStmt->execute([$status, $mpesaReceiptNumber, $failureReason, $paystackReference, $paystackResponse, $paymentId]);
    }

    /**
     * Activate user subscription
     */
    private function activateSubscription($userId, $packageId)
    {
        try {
            // Get package details
            $packageQuery = "SELECT * FROM packages WHERE id = ?";
            $packageStmt = $this->conn->prepare($packageQuery);
            $packageStmt->execute([$packageId]);
            $package = $packageStmt->fetch();

            if (!$package) {
                throw new Exception("Package not found");
            }

            // Check for existing subscription (active or recently expired)
            $subQuery = "SELECT * FROM user_subscriptions 
                        WHERE user_id = ? 
                        ORDER BY end_date DESC LIMIT 1";
            $subStmt = $this->conn->prepare($subQuery);
            $subStmt->execute([$userId]);
            $currentSub = $subStmt->fetch();

            $now = new DateTime();
            $durationInterval = new DateInterval('P' . $package['duration_days'] . 'D');

            if ($currentSub && $currentSub['package_id'] == $packageId) {
                // Same package - Renew/Extend
                $currentEndDate = new DateTime($currentSub['end_date']);

                // Calculate new end date: MAX(current_end, now) + duration
                if ($currentEndDate > $now) {
                    $newStartDate = $currentEndDate; // Continues from previous
                } else {
                    $newStartDate = $now; // Starts now (gap filled)
                }

                $newEndDate = clone $newStartDate;
                $newEndDate->add($durationInterval);

                // Update existing record to be active and have new end date
                $updateQuery = "UPDATE user_subscriptions 
                               SET status = 'active', 
                                   end_date = ?, 
                                   updated_at = CURRENT_TIMESTAMP 
                               WHERE id = ?";
                $updateStmt = $this->conn->prepare($updateQuery);
                $updateStmt->execute([$newEndDate->format('Y-m-d H:i:s'), $currentSub['id']]);

                $actionType = 'renewal';

            } else {
                // Different package or no subscription - Start New
                // Cancel old active subs if any
                $cancelQuery = "UPDATE user_subscriptions SET status = 'cancelled', updated_at = CURRENT_TIMESTAMP 
                               WHERE user_id = ? AND status = 'active'";
                $cancelStmt = $this->conn->prepare($cancelQuery);
                $cancelStmt->execute([$userId]);

                // Create new
                $startDate = $now;
                $endDate = clone $startDate;
                $endDate->add($durationInterval);

                $insertQuery = "INSERT INTO user_subscriptions (user_id, package_id, status, start_date, end_date, auto_renewal) 
                               VALUES (?, ?, 'active', ?, ?, true)";
                $insertStmt = $this->conn->prepare($insertQuery);
                $insertStmt->execute([$userId, $packageId, $startDate->format('Y-m-d H:i:s'), $endDate->format('Y-m-d H:i:s')]);

                $actionType = 'activation';
            }

            // Create notification
            $notifTitle = ($actionType === 'renewal') ? 'Subscription Renewed' : 'Welcome to BingeTV!';
            $notifBody = ($actionType === 'renewal')
                ? "Your {$package['name']} subscription has been extended. New expiry: " . $newEndDate->format('M j, Y')
                : "Your {$package['name']} subscription is now active. Enjoy unlimited access to premium content!";

            createNotification($userId, $actionType, $notifTitle, $notifBody);

            // Send Email Notification
            if (function_exists('sendSubscriptionActivationEmail') && $actionType === 'activation') {
                // Fetch credentials if needed, or just send generic welcome
                // Assuming credentials are auto-generated or handled elsewhere. 
                // Note: activateSubscription doesn't generate streaming creds itself, that might be in 'create.php'.
                // But for basic notification:
                // sendSubscriptionActivationEmail($userId, ...); // Needs arguments
            } elseif (function_exists('sendSubscriptionRenewalEmail') && $actionType === 'renewal') {
                // Fetch user email
                $uQuery = "SELECT email, first_name FROM users WHERE id = ?";
                $uStmt = $this->conn->prepare($uQuery);
                $uStmt->execute([$userId]);
                $uData = $uStmt->fetch();

                sendSubscriptionRenewalEmail(
                    $uData['email'],
                    $uData['first_name'],
                    $package['name'],
                    $newEndDate->format('Y-m-d')
                );
            }

            return true;

        } catch (Exception $e) {
            error_log("Subscription activation error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send payment confirmation email
     */
    private function sendPaymentConfirmation($payment)
    {
        if (!function_exists('sendPaymentConfirmationEmail')) {
            require_once __DIR__ . '/email_notifications.php';
        }

        // Determine receipt number (M-PESA receipt or Paystack reference)
        $receiptNumber = $payment['mpesa_receipt_number'] ?? $payment['paystack_reference'] ?? $payment['merchant_request_id'];

        sendPaymentConfirmationEmail(
            $payment['email'],
            $payment['first_name'],
            $payment['amount'],
            $receiptNumber,
            $payment['package_name'] ?? 'Subscription Package'
        );

        error_log("Payment confirmation sent to: " . $payment['email']);
    }

    /**
     * Send payment failure notification
     */
    private function sendPaymentFailureNotification($payment, $reason)
    {
        createNotification(
            $payment['user_id'],
            'payment_failed',
            'Payment Failed',
            "Your payment could not be processed. Reason: {$reason}. Please try again."
        );
    }

    /**
     * Process subscription renewals
     */
    public function processRenewals()
    {
        try {
            // Find subscriptions expiring in 1 day with auto-renewal enabled
            $expiringQuery = "SELECT us.*, u.email, u.first_name, p.name as package_name, p.price 
                             FROM user_subscriptions us 
                             JOIN users u ON us.user_id = u.id 
                             JOIN packages p ON us.package_id = p.id 
                             WHERE us.status = 'active' 
                             AND us.auto_renewal = true 
                             AND us.end_date BETWEEN NOW() AND NOW() + INTERVAL '1 day'";

            $expiringStmt = $this->conn->prepare($expiringQuery);
            $expiringStmt->execute();
            $expiringSubscriptions = $expiringStmt->fetchAll();

            foreach ($expiringSubscriptions as $subscription) {
                // Create renewal payment
                $this->createRenewalPayment($subscription);
            }

            return count($expiringSubscriptions);

        } catch (Exception $e) {
            error_log("Renewal processing error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Create renewal payment
     */
    private function createRenewalPayment($subscription)
    {
        try {
            // Create payment record
            $paymentQuery = "INSERT INTO payments (user_id, package_id, amount, currency, payment_method, status, merchant_request_id) 
                           VALUES (?, ?, ?, 'KES', 'M-PESA', 'pending', ?)";
            $merchantRequestId = 'RENEWAL_' . time() . '_' . $subscription['user_id'];
            $paymentStmt = $this->conn->prepare($paymentQuery);
            $paymentStmt->execute([
                $subscription['user_id'],
                $subscription['package_id'],
                $subscription['price'],
                $merchantRequestId
            ]);

            // Create renewal notification
            createNotification(
                $subscription['user_id'],
                'renewal',
                'Subscription Renewal',
                "Your {$subscription['package_name']} subscription will renew automatically. Amount: KES " . number_format($subscription['price'], 0)
            );

            return true;

        } catch (Exception $e) {
            error_log("Renewal payment creation error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Process expired subscriptions
     */
    public function processExpiredSubscriptions()
    {
        try {
            // Find expired subscriptions
            $expiredQuery = "SELECT us.*, u.email, u.first_name, p.name as package_name 
                           FROM user_subscriptions us 
                           JOIN users u ON us.user_id = u.id 
                           JOIN packages p ON us.package_id = p.id 
                           WHERE us.status = 'active' 
                           AND us.end_date < NOW()";

            $expiredStmt = $this->conn->prepare($expiredQuery);
            $expiredStmt->execute();
            $expiredSubscriptions = $expiredStmt->fetchAll();

            foreach ($expiredSubscriptions as $subscription) {
                // Mark subscription as expired
                $updateQuery = "UPDATE user_subscriptions SET status = 'expired', updated_at = CURRENT_TIMESTAMP WHERE id = ?";
                $updateStmt = $this->conn->prepare($updateQuery);
                $updateStmt->execute([$subscription['id']]);

                // Create expiration notification
                createNotification(
                    $subscription['user_id'],
                    'expired',
                    'Subscription Expired',
                    "Your {$subscription['package_name']} subscription has expired. Renew now to continue enjoying premium content."
                );

                // Send expired email
                if (!function_exists('sendSubscriptionExpiredEmail')) {
                    require_once __DIR__ . '/email_notifications.php';
                }
                sendSubscriptionExpiredEmail(
                    $subscription['email'],
                    $subscription['first_name'],
                    $subscription['package_name']
                );
            }

            return count($expiredSubscriptions);

        } catch (Exception $e) {
            error_log("Expired subscription processing error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get payment statistics
     */
    public function getPaymentStats()
    {
        try {
            $statsQuery = "SELECT 
                          COUNT(*) as total_payments,
                          COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_payments,
                          COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_payments,
                          COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed_payments,
                          COALESCE(SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END), 0) as total_revenue,
                          COALESCE(AVG(CASE WHEN status = 'completed' THEN amount ELSE NULL END), 0) as avg_payment
                          FROM payments";

            $statsStmt = $this->conn->prepare($statsQuery);
            $statsStmt->execute();

            return $statsStmt->fetch();

        } catch (Exception $e) {
            error_log("Payment stats error: " . $e->getMessage());
            return null;
        }
    }
}

/**
 * Process M-PESA callback
 */
function processMpesaCallback($callbackData)
{
    $processor = new PaymentProcessor();
    return $processor->processMpesaCallback($callbackData);
}

/**
 * Process daily renewals and expirations
 */
function processDailySubscriptions()
{
    $processor = new PaymentProcessor();

    $renewals = $processor->processRenewals();
    $expired = $processor->processExpiredSubscriptions();

    return [
        'renewals_processed' => $renewals,
        'expired_processed' => $expired
    ];
}
?>