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
        $db = new Database();
        $conn = $db->getConnection();
        
        $checkoutRequestId = $result['checkout_request_id'];
        $receiptNumber = $result['receipt_number'];
        $amount = $result['amount'];
        $phoneNumber = $result['phone_number'];
        
        // Find payment by checkout request ID
        $paymentQuery = "SELECT p.*, us.id as subscription_id 
                        FROM payments p 
                        LEFT JOIN user_subscriptions us ON p.subscription_id = us.id 
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
            
            // Update subscription status
            if ($payment['subscription_id']) {
                $updateSubQuery = "UPDATE user_subscriptions SET 
                                  status = 'active', 
                                  updated_at = CURRENT_TIMESTAMP 
                                  WHERE id = ?";
                $updateSubStmt = $conn->prepare($updateSubQuery);
                $updateSubStmt->execute([$payment['subscription_id']]);
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
        $db = new Database();
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
