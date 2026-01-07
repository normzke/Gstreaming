<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../lib/mpesa_integration.php';

// Set content type
header('Content-Type: application/json');

// Get raw input
$input = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_MPESA_SIGNATURE'] ?? '';

// Log the callback for debugging
error_log('M-PESA Callback: ' . $input);

try {
    $mpesa = new MpesaIntegration();

    // Validate signature (optional but recommended)
    if (!empty($signature) && !$mpesa->validateCallback($input, $signature)) {
        throw new Exception('Invalid signature');
    }

    // Process callback
    $result = $mpesa->processCallback($input);

    if ($result['success']) {
        // Update payment status in database
        $db = Database::getInstance();
        $conn = $db->getConnection();

        $checkoutRequestId = $result['checkout_request_id'];
        $receiptNumber = $result['receipt_number'];
        $amount = $result['amount'];
        $phoneNumber = $result['phone_number'];

        // Find payment by checkout request ID
        $paymentQuery = "SELECT p.*, us.id as subscription_id, us.package_id, us.end_date as current_end_date, u.email, u.first_name, pk.name as package_name, pk.duration_days 
                        FROM payments p 
                        LEFT JOIN user_subscriptions us ON p.subscription_id = us.id 
                        LEFT JOIN users u ON p.user_id = u.id
                        LEFT JOIN packages pk ON us.package_id = pk.id
                        WHERE p.mpesa_checkout_request_id = ?";
        $paymentStmt = $conn->prepare($paymentQuery);
        $paymentStmt->execute([$checkoutRequestId]);
        $payment = $paymentStmt->fetch(PDO::FETCH_ASSOC);

        if ($payment) {
            // Update payment status
            $updatePaymentQuery = "UPDATE payments SET 
                                  status = 'completed', 
                                  mpesa_receipt_code = ?, 
                                  amount = ?, 
                                  mpesa_phone = ?, 
                                  updated_at = CURRENT_TIMESTAMP 
                                  WHERE id = ?";
            $updatePaymentStmt = $conn->prepare($updatePaymentQuery);
            $updatePaymentStmt->execute([
                $receiptNumber,
                $amount,
                $phoneNumber,
                $payment['id']
            ]);

            // Update subscription status and extend date if renewal
            if ($payment['subscription_id']) {
                // Calculate new end date
                $currentEnd = new DateTime($payment['current_end_date']);
                $now = new DateTime();
                $startFrom = ($currentEnd > $now) ? $currentEnd : $now;
                $duration = $payment['duration_days'] ?? 30; // Default 30 if missing

                $newEnd = clone $startFrom;
                $newEnd->add(new DateInterval('P' . $duration . 'D'));
                $newEndDateStr = $newEnd->format('Y-m-d H:i:s');

                $updateSubQuery = "UPDATE user_subscriptions SET 
                                  status = 'active', 
                                  end_date = ?,
                                  updated_at = CURRENT_TIMESTAMP 
                                  WHERE id = ?";
                $updateSubStmt = $conn->prepare($updateSubQuery);
                $updateSubStmt->execute([$newEndDateStr, $payment['subscription_id']]);

                // Send renewal email
                if (!function_exists('sendSubscriptionRenewalEmail')) {
                    require_once '../../lib/email_notifications.php';
                }
                sendSubscriptionRenewalEmail(
                    $payment['email'],
                    $payment['first_name'],
                    $payment['package_name'],
                    $newEndDateStr
                );
            }

            // Update order status to confirmed (ready for external package generation)
            $orderQuery = "UPDATE orders SET status = 'confirmed', updated_at = CURRENT_TIMESTAMP WHERE payment_id = ?";
            $orderStmt = $conn->prepare($orderQuery);
            $orderStmt->execute([$payment['id']]);

            // Create notification for user
            $notificationQuery = "INSERT INTO notifications (user_id, title, message, type, is_read, created_at) 
                                 VALUES (?, 'Payment Successful', 'Your payment of KES ? has been processed successfully. Your subscription is now active.', 'success', false, CURRENT_TIMESTAMP)";
            $notificationStmt = $conn->prepare($notificationQuery);
            $notificationStmt->execute([$payment['user_id'], $amount]);

            // Send email notification
            if (!function_exists('sendPaymentConfirmationEmail')) {
                require_once '../../lib/email_notifications.php';
            }
            sendPaymentConfirmationEmail(
                $payment['email'],
                $payment['first_name'],
                $amount,
                $receiptNumber,
                $payment['package_name'] ?? 'Subscription Package'
            );

            // Log activity
            $activityQuery = "INSERT INTO activity_logs (user_id, action, details, created_at) 
                             VALUES (?, 'payment_completed', ?, CURRENT_TIMESTAMP)";
            $activityStmt = $conn->prepare($activityQuery);
            $activityStmt->execute([
                $payment['user_id'],
                json_encode([
                    'payment_id' => $payment['id'],
                    'amount' => $amount,
                    'receipt_number' => $receiptNumber
                ])
            ]);
        }

        // Send success response
        echo json_encode([
            'ResultCode' => 0,
            'ResultDesc' => 'Success'
        ]);
    } else {
        // Payment failed - update status
        $db = Database::getInstance();
        $conn = $db->getConnection();

        $checkoutRequestId = $result['checkout_request_id'];

        // Find payment by checkout request ID
        $paymentQuery = "SELECT * FROM payments WHERE mpesa_checkout_request_id = ?";
        $paymentStmt = $conn->prepare($paymentQuery);
        $paymentStmt->execute([$checkoutRequestId]);
        $payment = $paymentStmt->fetch(PDO::FETCH_ASSOC);

        if ($payment) {
            // Update payment status
            $updatePaymentQuery = "UPDATE payments SET 
                                  status = 'failed', 
                                  updated_at = CURRENT_TIMESTAMP 
                                  WHERE id = ?";
            $updatePaymentStmt = $conn->prepare($updatePaymentQuery);
            $updatePaymentStmt->execute([$payment['id']]);

            // Create notification for user
            $notificationQuery = "INSERT INTO notifications (user_id, title, message, type, is_read, created_at) 
                                 VALUES (?, 'Payment Failed', 'Your payment could not be processed. Please try again.', 'error', false, CURRENT_TIMESTAMP)";
            $notificationStmt = $conn->prepare($notificationQuery);
            $notificationStmt->execute([$payment['user_id']]);
        }

        // Send failure response
        echo json_encode([
            'ResultCode' => 1,
            'ResultDesc' => 'Failed'
        ]);
    }

} catch (Exception $e) {
    error_log('M-PESA Callback Error: ' . $e->getMessage());

    // Send error response
    echo json_encode([
        'ResultCode' => 1,
        'ResultDesc' => 'Error processing callback'
    ]);
}
?>