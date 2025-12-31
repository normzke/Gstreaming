<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../lib/functions.php';

// Set content type
header('Content-Type: application/json');

// Get raw input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Log the callback for debugging
error_log('External API Callback: ' . $input);

try {
    if (!$data || !isset($data['order_id'])) {
        throw new Exception('Invalid callback data');
    }
    
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $orderId = (int)$data['order_id'];
    $externalOrderId = $data['external_order_id'] ?? '';
    $streamingUrl = $data['streaming_url'] ?? '';
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';
    $status = $data['status'] ?? 'completed';
    
    // Update order with external details
    $updateQuery = "UPDATE orders SET 
                   external_order_id = ?, 
                   external_url = ?, 
                   external_username = ?, 
                   external_password = ?,
                   status = ?,
                   updated_at = CURRENT_TIMESTAMP 
                   WHERE id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->execute([$externalOrderId, $streamingUrl, $username, $password, $status, $orderId]);
    
    // Add status history
    $historyQuery = "INSERT INTO order_status_history (order_id, status, notes, created_at) 
                    VALUES (?, ?, 'External system callback received', CURRENT_TIMESTAMP)";
    $historyStmt = $conn->prepare($historyQuery);
    $historyStmt->execute([$orderId, $status]);
    
    // If completed, send SMS to user
    if ($status === 'completed') {
        // Get order details
        $orderQuery = "SELECT o.*, u.first_name, u.last_name, u.phone, pk.name as package_name
                       FROM orders o 
                       LEFT JOIN users u ON o.user_id = u.id 
                       LEFT JOIN packages pk ON o.package_id = pk.id 
                       WHERE o.id = ?";
        $orderStmt = $conn->prepare($orderQuery);
        $orderStmt->execute([$orderId]);
        $order = $orderStmt->fetch();
        
        if ($order) {
            // Send SMS
            $smsMessage = "BingeTV Subscription Activated!\n";
            $smsMessage .= "Package: " . $order['package_name'] . "\n";
            $smsMessage .= "Streaming URL: " . $streamingUrl . "\n";
            $smsMessage .= "Username: " . $username . "\n";
            $smsMessage .= "Password: " . $password . "\n";
            $smsMessage .= "Thank you for choosing BingeTV!";
            
            // Log SMS sending
            $smsLogQuery = "INSERT INTO activity_logs (user_id, action, details, created_at) 
                            VALUES (?, 'sms_sent', ?, CURRENT_TIMESTAMP)";
            $smsStmt = $conn->prepare($smsLogQuery);
            $smsStmt->execute([$order['user_id'], 'SMS sent with streaming credentials via external callback']);
            
            // Create notification for user
            $notificationQuery = "INSERT INTO notifications (user_id, title, message, type, is_read, created_at) 
                                 VALUES (?, 'Subscription Activated', 'Your subscription has been activated. Check your SMS for streaming details.', 'success', false, CURRENT_TIMESTAMP)";
            $notificationStmt = $conn->prepare($notificationQuery);
            $notificationStmt->execute([$order['user_id']]);
        }
    }
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Order updated successfully',
        'order_id' => $orderId
    ]);
    
} catch (Exception $e) {
    error_log('External API Callback Error: ' . $e->getMessage());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
