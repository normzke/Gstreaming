<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/mpesa_integration.php';
require_once '../../includes/payment-processor.php';

header('Content-Type: application/json');

// Log the callback for debugging
$callbackData = file_get_contents('php://input');
error_log('M-PESA Callback received: ' . $callbackData);

try {
    $data = json_decode($callbackData, true);
    
    if (!$data) {
        throw new Exception('Invalid JSON data received');
    }
    
    $db = new Database();
    $conn = $db->getConnection();
    
    // Get M-PESA configuration
    $mpesaConfigQuery = "SELECT config_key, config_value FROM mpesa_config";
    $mpesaConfigStmt = $conn->prepare($mpesaConfigQuery);
    $mpesaConfigStmt->execute();
    $mpesaConfigs = $mpesaConfigStmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    $mpesaConfig = [
        'consumer_key' => $mpesaConfigs['consumer_key'] ?? '',
        'consumer_secret' => $mpesaConfigs['consumer_secret'] ?? '',
        'shortcode' => $mpesaConfigs['shortcode'] ?? '',
        'passkey' => $mpesaConfigs['passkey'] ?? '',
        'callback_url' => $mpesaConfigs['callback_url'] ?? '',
        'environment' => $mpesaConfigs['environment'] ?? 'sandbox'
    ];
    
    $mpesa = new MpesaIntegration($mpesaConfig);
    
    // Validate callback
    if (!$mpesa->validateCallback($data)) {
        throw new Exception('Invalid callback data');
    }
    
    // Extract payment details
    $paymentDetails = $mpesa->extractPaymentDetails($data);
    
    if (!$paymentDetails) {
        throw new Exception('Could not extract payment details');
    }
    
    // Get checkout request ID and receipt number
    $checkoutRequestId = $paymentDetails['CheckoutRequestID'] ?? '';
    $receiptNumber = $paymentDetails['MpesaReceiptNumber'] ?? '';
    $transactionDate = $paymentDetails['TransactionDate'] ?? '';
    $amount = $paymentDetails['Amount'] ?? 0;
    $phoneNumber = $paymentDetails['PhoneNumber'] ?? '';
    
    // Find payment record
    $paymentQuery = "SELECT * FROM payments WHERE mpesa_checkout_request_id = ? AND status = 'pending'";
    $paymentStmt = $conn->prepare($paymentQuery);
    $paymentStmt->execute([$checkoutRequestId]);
    $payment = $paymentStmt->fetch();
    
    if (!$payment) {
        throw new Exception('Payment record not found for checkout request: ' . $checkoutRequestId);
    }
    
    // Update payment status
    $updatePaymentQuery = "UPDATE payments SET 
                          status = 'completed', 
                          mpesa_receipt_number = ?, 
                          transaction_date = CURRENT_TIMESTAMP 
                          WHERE id = ?";
    $updatePaymentStmt = $conn->prepare($updatePaymentQuery);
    $updatePaymentStmt->execute([$receiptNumber, $payment['id']]);
    
    // Create subscription if not exists
    $subscriptionQuery = "SELECT * FROM user_subscriptions WHERE user_id = ? AND package_id = ? AND status = 'active' AND end_date > NOW()";
    $subscriptionStmt = $conn->prepare($subscriptionQuery);
    $subscriptionStmt->execute([$payment['user_id'], $payment['subscription_id'] ? null : 1]); // Default to package 1 if no subscription_id
    
    if (!$subscriptionStmt->fetch()) {
        // Get package details
        $packageQuery = "SELECT * FROM packages WHERE id = ? AND is_active = true";
        $packageStmt = $conn->prepare($packageQuery);
        $packageStmt->execute([$payment['subscription_id'] ?: 1]);
        $package = $packageStmt->fetch();
        
        if ($package) {
            // Calculate subscription dates
            $startDate = date('Y-m-d H:i:s');
            $endDate = date('Y-m-d H:i:s', strtotime("+{$package['duration_days']} days"));
            
            // Create subscription
            $subscriptionQuery = "INSERT INTO user_subscriptions (user_id, package_id, status, start_date, end_date, auto_renewal) 
                                 VALUES (?, ?, 'active', ?, ?, true)";
            $subscriptionStmt = $conn->prepare($subscriptionQuery);
            $subscriptionStmt->execute([$payment['user_id'], $package['id'], $startDate, $endDate]);
            
            $subscriptionId = $conn->lastInsertId();
            
            // Update payment with subscription ID
            $updatePaymentQuery = "UPDATE payments SET subscription_id = ? WHERE id = ?";
            $updatePaymentStmt = $conn->prepare($updatePaymentQuery);
            $updatePaymentStmt->execute([$subscriptionId, $payment['id']]);
            
            // Create streaming access
            $streamingUsername = 'user_' . $payment['user_id'] . '_' . time();
            $streamingPassword = generateSecurePassword();
            $streamingUrl = generateStreamingUrl($package['id']);
            
            $streamingQuery = "INSERT INTO user_streaming_access (user_id, subscription_id, streaming_url, username, password, is_active) 
                              VALUES (?, ?, ?, ?, ?, true)";
            $streamingStmt = $conn->prepare($streamingQuery);
            $streamingStmt->execute([$payment['user_id'], $subscriptionId, $streamingUrl, $streamingUsername, $streamingPassword]);
            
            // Create notification
            $notificationQuery = "INSERT INTO notifications (user_id, type, title, message, sent_email) 
                                 VALUES (?, 'subscription', 'Payment Confirmed', ?, true)";
            $notificationMessage = "Your payment of KES " . number_format($amount) . " has been confirmed. Your subscription is now active.";
            $notificationStmt = $conn->prepare($notificationQuery);
            $notificationStmt->execute([$payment['user_id'], $notificationMessage]);
            
            // Send confirmation email (in production)
            // sendPaymentConfirmationEmail($payment['user_id'], $amount, $receiptNumber);
        }
    }
    
    // Log successful processing
    error_log("M-PESA payment processed successfully: Payment ID {$payment['id']}, Receipt: {$receiptNumber}");
    
    // Return success response to M-PESA
    echo json_encode([
        'ResultCode' => 0,
        'ResultDesc' => 'Success'
    ]);
    
} catch (Exception $e) {
    error_log('M-PESA callback error: ' . $e->getMessage());
    
    // Return error response to M-PESA
    echo json_encode([
        'ResultCode' => 1,
        'ResultDesc' => 'Failed: ' . $e->getMessage()
    ]);
}

function generateSecurePassword($length = 12) {
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    $password = '';
    $max = strlen($characters) - 1;
    
    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[random_int(0, $max)];
    }
    
    return $password;
}

function generateStreamingUrl($packageId) {
    $baseUrl = 'https://stream.gstreaming.com/';
    $packageHash = hash('md5', $packageId . time());
    return $baseUrl . $packageHash;
}
?>