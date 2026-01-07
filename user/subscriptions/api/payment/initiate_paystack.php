<?php
/**
 * Initiate Paystack Payment API
 */

require_once __DIR__ . '/../../../../config/config.php';
require_once __DIR__ . '/../../../../config/database.php';
require_once __DIR__ . '/../../../../lib/functions.php';
require_once __DIR__ . '/../../../../lib/paystack_integration.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

$packageId = $data['package_id'] ?? 0;
$email = $data['email'] ?? '';
$amount = $data['amount'] ?? 0;
$devices = $data['devices'] ?? 1;

if (!$packageId || !$amount) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

$db = Database::getInstance();
$conn = $db->getConnection();

// Get package details to verify price
$packageQuery = "SELECT * FROM packages WHERE id = ?";
$packageStmt = $conn->prepare($packageQuery);
$packageStmt->execute([$packageId]);
$package = $packageStmt->fetch();

if (!$package) {
    echo json_encode(['success' => false, 'message' => 'Invalid package']);
    exit();
}

// Create a unique reference
$reference = 'PAY-' . strtoupper(uniqid());

try {
    // Create payment record
    $insertQuery = "INSERT INTO payments (user_id, package_id, amount, currency, payment_method, paystack_reference, status) 
                    VALUES (?, ?, ?, 'KES', 'paystack', ?, 'pending')";
    $insertStmt = $conn->prepare($insertQuery);
    $insertStmt->execute([
        $_SESSION['user_id'],
        $packageId,
        $amount,
        $reference
    ]);

    $paymentId = $conn->lastInsertId();

    // Initialize Paystack transaction
    $paystack = new PaystackIntegration();
    $initData = [
        'email' => $email,
        'amount' => $amount,
        'reference' => $reference,
        'metadata' => [
            'payment_id' => $paymentId,
            'package_id' => $packageId,
            'user_id' => $_SESSION['user_id'],
            'devices' => $devices
        ]
    ];

    $result = $paystack->initializeTransaction($initData);

    if ($result['success']) {
        echo json_encode([
            'success' => true,
            'reference' => $reference,
            'public_key' => PAYSTACK_PUBLIC_KEY,
            'payment_id' => $paymentId
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => $result['message']
        ]);
    }

} catch (Exception $e) {
    error_log("Paystack Initiation Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred during payment initialization']);
}
