<?php
/**
 * Create Subscription & Streaming Access API
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

$data = json_decode(file_get_contents('php://input'), true);
$packageId = $data['package_id'] ?? 0;
$transactionId = $data['transaction_id'] ?? '';

if (!$packageId || !$transactionId) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

$db = Database::getInstance();
$conn = $db->getConnection();

try {
    // Verify payment is completed
    $paymentQuery = "SELECT * FROM payments WHERE mpesa_checkout_request_id = ? AND user_id = ? AND status = 'completed'";
    $paymentStmt = $conn->prepare($paymentQuery);
    $paymentStmt->execute([$transactionId, $_SESSION['user_id']]);
    $payment = $paymentStmt->fetch();

    if (!$payment) {
        echo json_encode(['success' => false, 'message' => 'Payment not verified or not completed']);
        exit();
    }

    // Check if subscription already created for this payment
    $subQuery = "SELECT * FROM user_subscriptions WHERE user_id = ? AND package_id = ? AND status = 'active' ORDER BY created_at DESC LIMIT 1";
    $subStmt = $conn->prepare($subQuery);
    $subStmt->execute([$_SESSION['user_id'], $packageId]);
    $subscription = $subStmt->fetch();

    if (!$subscription) {
        echo json_encode(['success' => false, 'message' => 'No active subscription found. Wait for activation.']);
        exit();
    }

    // Check if streaming access already exists
    $accessQuery = "SELECT * FROM user_streaming_access WHERE subscription_id = ?";
    $accessStmt = $conn->prepare($accessQuery);
    $accessStmt->execute([$subscription['id']]);
    $access = $accessStmt->fetch();

    if (!$access) {
        // Generate credentials
        $streamingUrl = "http://iptv.bingetv.co.ke:8080";
        $username = "btv_" . strtolower(substr($_SESSION['user_email'] ?? 'user', 0, 5)) . "_" . rand(1000, 9999);
        $password = bin2hex(random_bytes(4));

        $insertQuery = "INSERT INTO user_streaming_access (user_id, subscription_id, streaming_url, username, password) 
                        VALUES (?, ?, ?, ?, ?)";
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->execute([$_SESSION['user_id'], $subscription['id'], $streamingUrl, $username, $password]);

        $access = [
            'streaming_url' => $streamingUrl,
            'username' => $username,
            'password' => $password
        ];

        // Send activation email with credentials
        // Get user and package details
        $detailsQuery = "SELECT u.email, u.first_name, p.name as package_name, us.end_date 
                        FROM user_subscriptions us
                        JOIN users u ON us.user_id = u.id
                        JOIN packages p ON us.package_id = p.id
                        WHERE us.id = ?";
        $detailsStmt = $conn->prepare($detailsQuery);
        $detailsStmt->execute([$subscription['id']]);
        $details = $detailsStmt->fetch();

        if ($details) {
            if (!function_exists('sendSubscriptionActivationEmail')) {
                require_once __DIR__ . '/../../../../lib/email_notifications.php';
            }
            sendSubscriptionActivationEmail(
                $details['email'],
                $details['first_name'],
                $details['package_name'],
                $details['end_date'],
                $streamingUrl,
                $username,
                $password
            );
        }
    }

    echo json_encode([
        'success' => true,
        'streaming_url' => $access['streaming_url'],
        'username' => $access['username'],
        'password' => $access['password']
    ]);

} catch (Exception $e) {
    error_log("Subscription Creation Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred during subscription finalization']);
}
