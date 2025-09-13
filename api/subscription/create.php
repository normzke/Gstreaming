<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate input
    $required_fields = ['package_id', 'transaction_id'];
    foreach ($required_fields as $field) {
        if (empty($input[$field])) {
            echo json_encode(['success' => false, 'message' => "Field $field is required"]);
            exit();
        }
    }
    
    $db = new Database();
    $conn = $db->getConnection();
    
    // Verify payment is completed
    $paymentQuery = "SELECT * FROM payments WHERE mpesa_transaction_id = ? AND user_id = ? AND status = 'completed'";
    $paymentStmt = $conn->prepare($paymentQuery);
    $paymentStmt->execute([$input['transaction_id'], $_SESSION['user_id']]);
    $payment = $paymentStmt->fetch();
    
    if (!$payment) {
        echo json_encode(['success' => false, 'message' => 'Payment not found or not completed']);
        exit();
    }
    
    // Get package details
    $packageQuery = "SELECT * FROM packages WHERE id = ? AND is_active = true";
    $packageStmt = $conn->prepare($packageQuery);
    $packageStmt->execute([$input['package_id']]);
    $package = $packageStmt->fetch();
    
    if (!$package) {
        echo json_encode(['success' => false, 'message' => 'Invalid package']);
        exit();
    }
    
    // Check if user already has an active subscription for this package
    $existingQuery = "SELECT * FROM user_subscriptions WHERE user_id = ? AND package_id = ? AND status = 'active' AND end_date > NOW()";
    $existingStmt = $conn->prepare($existingQuery);
    $existingStmt->execute([$_SESSION['user_id'], $input['package_id']]);
    
    if ($existingStmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'You already have an active subscription for this package']);
        exit();
    }
    
    // Calculate subscription dates
    $startDate = date('Y-m-d H:i:s');
    $endDate = date('Y-m-d H:i:s', strtotime("+{$package['duration_days']} days"));
    
    // Generate streaming credentials
    $streamingUsername = 'user_' . $_SESSION['user_id'] . '_' . time();
    $streamingPassword = generateSecurePassword();
    $streamingUrl = generateStreamingUrl($package['id']);
    
    // Create subscription
    $subscriptionQuery = "INSERT INTO user_subscriptions (user_id, package_id, status, start_date, end_date, auto_renewal) 
                         VALUES (?, ?, 'active', ?, ?, true)";
    $subscriptionStmt = $conn->prepare($subscriptionQuery);
    $subscriptionStmt->execute([
        $_SESSION['user_id'],
        $input['package_id'],
        $startDate,
        $endDate
    ]);
    
    $subscriptionId = $conn->lastInsertId();
    
    // Update payment with subscription ID
    $updatePaymentQuery = "UPDATE payments SET subscription_id = ? WHERE id = ?";
    $updatePaymentStmt = $conn->prepare($updatePaymentQuery);
    $updatePaymentStmt->execute([$subscriptionId, $payment['id']]);
    
    // Create streaming access record
    $streamingQuery = "INSERT INTO user_streaming_access (user_id, subscription_id, streaming_url, username, password, is_active) 
                      VALUES (?, ?, ?, ?, ?, true)";
    $streamingStmt = $conn->prepare($streamingQuery);
    $streamingStmt->execute([
        $_SESSION['user_id'],
        $subscriptionId,
        $streamingUrl,
        $streamingUsername,
        $streamingPassword
    ]);
    
    // Send confirmation email
    sendSubscriptionConfirmationEmail($_SESSION['user_email'], $package, $streamingUrl, $streamingUsername, $streamingPassword, $endDate);
    
    // Create notification
    $notificationQuery = "INSERT INTO notifications (user_id, type, title, message, sent_email) 
                         VALUES (?, 'subscription', 'Subscription Activated', ?, true)";
    $notificationMessage = "Your subscription to {$package['name']} has been activated. You can now access your streaming channels.";
    $notificationStmt = $conn->prepare($notificationQuery);
    $notificationStmt->execute([$_SESSION['user_id'], $notificationMessage]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Subscription created successfully',
        'subscription_id' => $subscriptionId,
        'streaming_url' => $streamingUrl,
        'username' => $streamingUsername,
        'password' => $streamingPassword,
        'end_date' => $endDate
    ]);
    
} catch (Exception $e) {
    error_log('Subscription creation error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to create subscription. Please contact support.']);
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
    // Generate a unique streaming URL based on package
    $baseUrl = 'https://stream.gstreaming.com/';
    $packageHash = hash('md5', $packageId . time());
    return $baseUrl . $packageHash;
}

function sendSubscriptionConfirmationEmail($email, $package, $streamingUrl, $username, $password, $endDate) {
    // In a real implementation, you would send an email here
    // For demo purposes, we'll just log the details
    
    $subject = "GStreaming Subscription Confirmed - {$package['name']}";
    $message = "
    <h2>Welcome to GStreaming!</h2>
    <p>Your subscription to {$package['name']} has been confirmed and activated.</p>
    
    <h3>Your Streaming Details:</h3>
    <ul>
        <li><strong>Streaming URL:</strong> {$streamingUrl}</li>
        <li><strong>Username:</strong> {$username}</li>
        <li><strong>Password:</strong> {$password}</li>
        <li><strong>Valid Until:</strong> " . date('F j, Y', strtotime($endDate)) . "</li>
    </ul>
    
    <h3>How to Access:</h3>
    <ol>
        <li>Download VLC Media Player or IPTV Smarters app</li>
        <li>Add playlist using the streaming URL above</li>
        <li>Enter your username and password when prompted</li>
        <li>Enjoy your channels!</li>
    </ol>
    
    <p>If you need help, contact us on WhatsApp: +254768704834</p>
    
    <p>Thank you for choosing GStreaming!</p>
    ";
    
    // Log email details (in production, send actual email)
    error_log("Subscription confirmation email for $email: " . strip_tags($message));
}
?>
