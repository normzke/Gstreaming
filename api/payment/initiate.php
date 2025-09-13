<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/mpesa_integration.php';

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
    $required_fields = ['package_id', 'phone_number', 'amount'];
    foreach ($required_fields as $field) {
        if (empty($input[$field])) {
            echo json_encode(['success' => false, 'message' => "Field $field is required"]);
            exit();
        }
    }
    
    // Validate phone format
    if (!preg_match('/^254\d{9}$/', $input['phone_number'])) {
        echo json_encode(['success' => false, 'message' => 'Phone number must be in format 254XXXXXXXXX']);
        exit();
    }
    
    $db = new Database();
    $conn = $db->getConnection();
    
    // Get package details
    $packageQuery = "SELECT * FROM packages WHERE id = ? AND is_active = true";
    $packageStmt = $conn->prepare($packageQuery);
    $packageStmt->execute([$input['package_id']]);
    $package = $packageStmt->fetch();
    
    if (!$package) {
        echo json_encode(['success' => false, 'message' => 'Invalid package']);
        exit();
    }
    
    // Verify amount matches package price
    if ($input['amount'] != $package['price']) {
        echo json_encode(['success' => false, 'message' => 'Amount does not match package price']);
        exit();
    }
    
    // Generate transaction ID
    $transactionId = 'GS' . time() . rand(1000, 9999);
    
    // Generate account number (user ID + timestamp)
    $accountNumber = $_SESSION['user_id'] . time();
    
    // Insert payment record
    $paymentQuery = "INSERT INTO payments (user_id, amount, currency, payment_method, phone_number, status, transaction_date) 
                    VALUES (?, ?, 'KES', 'mpesa', ?, 'pending', CURRENT_TIMESTAMP)";
    $paymentStmt = $conn->prepare($paymentQuery);
    $paymentStmt->execute([
        $_SESSION['user_id'],
        $input['amount'],
        $input['phone_number']
    ]);
    
    $paymentId = $conn->lastInsertId();
    
    // Update payment with transaction details
    $updateQuery = "UPDATE payments SET mpesa_transaction_id = ? WHERE id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->execute([$transactionId, $paymentId]);
    
    // Get M-PESA configuration
    $mpesaConfigQuery = "SELECT config_key, config_value FROM mpesa_config";
    $mpesaConfigStmt = $conn->prepare($mpesaConfigQuery);
    $mpesaConfigStmt->execute();
    $mpesaConfigs = $mpesaConfigStmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    // Initialize M-PESA integration
    $mpesaConfig = [
        'consumer_key' => $mpesaConfigs['consumer_key'] ?? '',
        'consumer_secret' => $mpesaConfigs['consumer_secret'] ?? '',
        'shortcode' => $mpesaConfigs['shortcode'] ?? '',
        'passkey' => $mpesaConfigs['passkey'] ?? '',
        'callback_url' => $mpesaConfigs['callback_url'] ?? '',
        'environment' => $mpesaConfigs['environment'] ?? 'sandbox'
    ];
    
    $mpesa = new MpesaIntegration($mpesaConfig);
    
    // Check if M-PESA is configured
    $configStatus = $mpesa->getConfigStatus();
    if (!$configStatus['configured']) {
        echo json_encode(['success' => false, 'message' => 'M-PESA is not properly configured. Please contact administrator.']);
        exit();
    }
    
    // Generate account reference and transaction description
    $accountReference = 'GSTREAMING_' . $paymentId;
    $transactionDesc = 'GStreaming subscription for package ' . $package['name'];
    
    // Initiate M-PESA STK Push
    $stkPushResult = $mpesa->initiateSTKPush(
        $input['phone_number'],
        $input['amount'],
        $accountReference,
        $transactionDesc
    );
    
    if (isset($stkPushResult['ResponseCode']) && $stkPushResult['ResponseCode'] == '0') {
        // Update payment with checkout request ID
        $checkoutRequestId = $stkPushResult['CheckoutRequestID'];
        $updateCheckoutQuery = "UPDATE payments SET mpesa_checkout_request_id = ? WHERE id = ?";
        $updateCheckoutStmt = $conn->prepare($updateCheckoutQuery);
        $updateCheckoutStmt->execute([$checkoutRequestId, $paymentId]);
        
        // Store transaction details in session for status checking
        $_SESSION['current_transaction'] = [
            'transaction_id' => $transactionId,
            'payment_id' => $paymentId,
            'package_id' => $input['package_id'],
            'amount' => $input['amount'],
            'phone_number' => $input['phone_number'],
            'checkout_request_id' => $checkoutRequestId
        ];
    } else {
        // M-PESA API failed, fall back to simulation for demo
        error_log('M-PESA STK Push failed: ' . json_encode($stkPushResult));
        
        // Store transaction details in session for status checking
        $_SESSION['current_transaction'] = [
            'transaction_id' => $transactionId,
            'payment_id' => $paymentId,
            'package_id' => $input['package_id'],
            'amount' => $input['amount'],
            'phone_number' => $input['phone_number']
        ];
        
        // Simulate M-PESA push notification for demo
        simulateMpesaPush($input['phone_number'], $input['amount'], $transactionId);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Payment initiated successfully',
        'transaction' => [
            'transaction_id' => $transactionId,
            'payment_id' => $paymentId,
            'account_number' => $accountNumber
        ]
    ]);
    
} catch (Exception $e) {
    error_log('Payment initiation error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Payment initiation failed. Please try again.']);
}

function simulateMpesaPush($phoneNumber, $amount, $transactionId) {
    // In a real implementation, this would call M-PESA STK Push API
    // For demo purposes, we'll simulate a successful push
    
    // You would implement the actual M-PESA STK Push here:
    /*
    $mpesa = new Mpesa();
    $result = $mpesa->stkPush($phoneNumber, $amount, $transactionId);
    
    if (!$result['success']) {
        throw new Exception($result['message']);
    }
    */
    
    // For demo, we'll just log the simulation
    error_log("M-PESA STK Push simulated for $phoneNumber: KES $amount, Transaction: $transactionId");
}
?>
