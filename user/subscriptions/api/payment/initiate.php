<?php
/**
 * Initiate M-PESA Payment API
 */

require_once __DIR__ . '/../../../../config/config.php';
require_once __DIR__ . '/../../../../config/database.php';
require_once __DIR__ . '/../../../../lib/functions.php';
require_once __DIR__ . '/../../../../lib/mpesa_integration.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

$packageId = $data['package_id'] ?? 0;
$phone = $data['phone_number'] ?? '';
$amount = $data['amount'] ?? 0;
$devices = $data['devices'] ?? 1;

if (!$packageId || !$phone || !$amount) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

$db = Database::getInstance();
$conn = $db->getConnection();

try {
    // Get package details
    $packageQuery = "SELECT * FROM packages WHERE id = ?";
    $packageStmt = $conn->prepare($packageQuery);
    $packageStmt->execute([$packageId]);
    $package = $packageStmt->fetch();

    if (!$package) {
        echo json_encode(['success' => false, 'message' => 'Invalid package']);
        exit();
    }

    // Initiate M-PESA STK Push
    $mpesa = new MpesaIntegration();

    // Create a temporary reference
    $accountReference = 'BINGETV' . strtoupper(uniqid());
    $transactionDesc = 'BingeTV Subscription - ' . $package['name'];

    $result = $mpesa->initiateSTKPush($phone, $amount, $accountReference, $transactionDesc);

    if ($result['success']) {
        // Create payment record
        $insertQuery = "INSERT INTO payments (user_id, package_id, amount, currency, payment_method, mpesa_checkout_request_id, status) 
                        VALUES (?, ?, ?, 'KES', 'mpesa', ?, 'pending')";
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->execute([
            $_SESSION['user_id'],
            $packageId,
            $amount,
            $result['checkout_request_id']
        ]);

        $paymentId = $conn->lastInsertId();

        echo json_encode([
            'success' => true,
            'transaction' => [
                'transaction_id' => $result['checkout_request_id'],
                'payment_id' => $paymentId
            ],
            'account_number' => $accountReference,
            'message' => 'STK Push initiated successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => $result['message']
        ]);
    }

} catch (Exception $e) {
    error_log("M-PESA Initiation Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred during payment initialization']);
}
