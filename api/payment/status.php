<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Check if user is logged in
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

try {
    $transactionId = $_GET['transaction_id'] ?? '';
    
    if (empty($transactionId)) {
        echo json_encode(['success' => false, 'message' => 'Transaction ID is required']);
        exit();
    }
    
    $db = new Database();
    $conn = $db->getConnection();
    
    // Get payment details
    $paymentQuery = "SELECT * FROM payments WHERE mpesa_transaction_id = ? AND user_id = ?";
    $paymentStmt = $conn->prepare($paymentQuery);
    $paymentStmt->execute([$transactionId, $_SESSION['user_id']]);
    $payment = $paymentStmt->fetch();
    
    if (!$payment) {
        echo json_encode(['success' => false, 'message' => 'Transaction not found']);
        exit();
    }
    
    // For demo purposes, we'll simulate payment confirmation after 30 seconds
    $timeSinceCreation = time() - strtotime($payment['created_at']);
    
    if ($timeSinceCreation > 30 && $payment['status'] === 'pending') {
        // Simulate successful payment
        $updateQuery = "UPDATE payments SET status = 'completed', mpesa_receipt_number = ? WHERE id = ?";
        $receiptNumber = 'NLJ' . rand(100000, 999999);
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->execute([$receiptNumber, $payment['id']]);
        
        echo json_encode([
            'success' => true,
            'payment_status' => 'completed',
            'receipt_number' => $receiptNumber,
            'message' => 'Payment completed successfully'
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'payment_status' => $payment['status'],
            'message' => $payment['status'] === 'pending' ? 'Payment still pending' : 'Payment completed'
        ]);
    }
    
} catch (Exception $e) {
    error_log('Payment status check error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to check payment status']);
}
?>
