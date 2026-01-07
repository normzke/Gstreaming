<?php
/**
 * M-PESA Payment Status API
 */

require_once __DIR__ . '/../../../../config/config.php';
require_once __DIR__ . '/../../../../config/database.php';
require_once __DIR__ . '/../../../../lib/functions.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$checkoutRequestId = $_GET['transaction_id'] ?? '';

if (!$checkoutRequestId) {
    echo json_encode(['success' => false, 'message' => 'Missing transaction ID']);
    exit();
}

$db = Database::getInstance();
$conn = $db->getConnection();

try {
    // Check payment status in database
    $query = "SELECT status FROM payments WHERE mpesa_checkout_request_id = ? AND user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$checkoutRequestId, $_SESSION['user_id']]);
    $payment = $stmt->fetch();

    if ($payment) {
        echo json_encode([
            'success' => true,
            'payment_status' => $payment['status']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Payment record not found']);
    }

} catch (Exception $e) {
    error_log("M-PESA Status Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while checking payment status']);
}
