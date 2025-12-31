<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../lib/functions.php';
require_once __DIR__ . '/../../lib/mpesa_integration.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: ../../login.php');
    exit();
}

$db = Database::getInstance();
$conn = $db->getConnection();

$paymentId = $_GET['payment_id'] ?? 0;
$message = '';
$messageType = '';

if (!$paymentId) {
    header('Location: ../subscriptions/subscribe.php');
    exit();
}

// Get payment details
$paymentQuery = "SELECT p.*, pk.name as package_name, pk.price, u.phone, u.first_name, u.last_name 
                FROM payments p 
                JOIN packages pk ON p.package_id = pk.id 
                JOIN users u ON p.user_id = u.id 
                WHERE p.id = ? AND p.user_id = ?";
$paymentStmt = $conn->prepare($paymentQuery);
$paymentStmt->execute([$paymentId, $_SESSION['user_id']]);
$payment = $paymentStmt->fetch();

if (!$payment) {
    header('Location: ../subscriptions/subscribe.php');
    exit();
}

// Handle payment processing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'initiate_mpesa') {
            // Initiate M-PESA payment
            $mpesa = new MpesaIntegration();
            
            $phone = $payment['mpesa_phone'] ?? $payment['phone'];
            $amount = $payment['amount'];
            $accountReference = 'BINGETV' . $paymentId;
            $transactionDesc = 'BingeTV Subscription - ' . $payment['package_name'];
            
            $result = $mpesa->initiateSTKPush($phone, $amount, $accountReference, $transactionDesc);
            
            if ($result['success']) {
                // Update payment with checkout request ID
                $updateQuery = "UPDATE payments SET mpesa_checkout_request_id = ?, status = 'pending', updated_at = CURRENT_TIMESTAMP WHERE id = ?";
                $updateStmt = $conn->prepare($updateQuery);
                $updateStmt->execute([$result['checkout_request_id'], $paymentId]);
                
                $message = 'Payment initiated successfully! Please check your phone and enter your M-PESA PIN to complete the payment.';
                $messageType = 'success';
                
                // Start polling for payment status
                echo "<script>startPaymentPolling('" . $paymentId . "');</script>";
            } else {
                $message = 'Failed to initiate payment: ' . $result['message'];
                $messageType = 'error';
            }
        } elseif ($action === 'check_status') {
            // Check payment status
            $checkoutRequestId = $payment['mpesa_checkout_request_id'];
            
            if ($checkoutRequestId) {
                $mpesa = new MpesaIntegration();
                $result = $mpesa->checkSTKPushStatus($checkoutRequestId);
                
                if ($result['success']) {
                    if ($result['status'] === 'completed') {
                        // Update payment status
                        $updateQuery = "UPDATE payments SET status = 'completed', mpesa_receipt_code = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
                        $updateStmt = $conn->prepare($updateQuery);
                        $updateStmt->execute([$result['receipt_number'], $paymentId]);
                        
                        // Update subscription status
                        $subUpdateQuery = "UPDATE user_subscriptions SET status = 'active', updated_at = CURRENT_TIMESTAMP WHERE id = ?";
                        $subStmt = $conn->prepare($subUpdateQuery);
                        $subStmt->execute([$payment['subscription_id']]);
                        
                        $message = 'Payment completed successfully! Your subscription is now active.';
                        $messageType = 'success';
                        
                        // Redirect to dashboard after 3 seconds
                        echo "<script>setTimeout(function() { window.location.href = '../dashboard/'; }, 3000);</script>";
                    } elseif ($result['status'] === 'failed') {
                        $updateQuery = "UPDATE payments SET status = 'failed', updated_at = CURRENT_TIMESTAMP WHERE id = ?";
                        $updateStmt = $conn->prepare($updateQuery);
                        $updateStmt->execute([$paymentId]);
                        
                        $message = 'Payment failed. Please try again.';
                        $messageType = 'error';
                    } else {
                        $message = 'Payment is still being processed. Please wait...';
                        $messageType = 'info';
                    }
                } else {
                    $message = 'Error checking payment status: ' . $result['message'];
                    $messageType = 'error';
                }
            }
        }
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Refresh payment data
$paymentStmt->execute([$paymentId, $_SESSION['user_id']]);
$payment = $paymentStmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Processing - BingeTV</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/components.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .payment-page {
            background: var(--background-color);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .payment-container {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 500px;
            text-align: center;
        }
        
        .payment-header {
            margin-bottom: 30px;
        }
        
        .payment-header .logo {
            font-family: 'Orbitron', sans-serif;
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 20px;
        }
        
        .payment-header h1 {
            color: var(--text-color-light);
            margin-bottom: 10px;
        }
        
        .payment-header p {
            color: var(--text-color-secondary);
        }
        
        .payment-details {
            background: var(--background-color-dark);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .payment-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            color: var(--text-color-light);
        }
        
        .payment-item:last-child {
            margin-bottom: 0;
            font-weight: 600;
            font-size: 1.1rem;
            color: var(--primary-color);
            border-top: 1px solid var(--border-color);
            padding-top: 10px;
        }
        
        .payment-status {
            margin-bottom: 30px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9rem;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-completed {
            background: #d4edda;
            color: #155724;
        }
        
        .status-failed {
            background: #f8d7da;
            color: #721c24;
        }
        
        .payment-actions {
            margin-bottom: 30px;
        }
        
        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--primary-dark);
        }
        
        .btn-secondary {
            background: var(--text-color-tertiary);
            color: white;
        }
        
        .btn-secondary:hover {
            background: var(--text-color-secondary);
        }
        
        .btn:disabled {
            background: var(--text-color-tertiary);
            cursor: not-allowed;
        }
        
        .loading-spinner {
            display: none;
            margin: 20px 0;
        }
        
        .spinner {
            border: 4px solid var(--border-color);
            border-top: 4px solid var(--primary-color);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .message.info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        .mpesa-instructions {
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            text-align: left;
        }
        
        .mpesa-instructions h4 {
            color: var(--primary-color);
            margin-bottom: 15px;
        }
        
        .mpesa-instructions ol {
            margin: 0;
            padding-left: 20px;
        }
        
        .mpesa-instructions li {
            margin-bottom: 8px;
            color: var(--text-color-light);
        }
    </style>
</head>
<body class="payment-page">
    <div class="payment-container">
        <div class="payment-header">
            <div class="logo">
                <i class="fas fa-satellite-dish"></i>
                BingeTV
            </div>
            <h1>Payment Processing</h1>
            <p>Complete your subscription payment</p>
        </div>

        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="payment-details">
            <div class="payment-item">
                <span>Package:</span>
                <span><?php echo htmlspecialchars($payment['package_name']); ?></span>
            </div>
            <div class="payment-item">
                <span>Amount:</span>
                <span>KES <?php echo number_format($payment['amount'], 0); ?></span>
            </div>
            <div class="payment-item">
                <span>Payment Method:</span>
                <span>M-PESA</span>
            </div>
            <div class="payment-item">
                <span>Total:</span>
                <span>KES <?php echo number_format($payment['amount'], 0); ?></span>
            </div>
        </div>

        <div class="payment-status">
            <span class="status-badge status-<?php echo $payment['status']; ?>">
                <?php echo ucfirst($payment['status']); ?>
            </span>
        </div>

        <?php if ($payment['status'] === 'pending' && !$payment['mpesa_checkout_request_id']): ?>
            <div class="mpesa-instructions">
                <h4><i class="fas fa-mobile-alt"></i> M-PESA Payment Instructions</h4>
                <ol>
                    <li>Click "Pay with M-PESA" below</li>
                    <li>Enter your M-PESA PIN when prompted</li>
                    <li>Wait for payment confirmation</li>
                    <li>Your subscription will be activated automatically</li>
                </ol>
            </div>

            <form method="POST" class="payment-actions">
                <input type="hidden" name="action" value="initiate_mpesa">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-mobile-alt"></i>
                    Pay with M-PESA (Automatic)
                </button>
            </form>
            
            <div style="text-align: center; margin: 1.5rem 0; color: #666;">
                <strong>OR</strong>
            </div>
            
            <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 1.5rem; margin: 1.5rem 0; border-radius: 8px;">
                <h4 style="margin: 0 0 1rem 0; color: #856404;">
                    <i class="fas fa-university"></i> Manual Payment Option
                </h4>
                <div style="background: white; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">
                    <p style="margin: 0.25rem 0; color: #333;"><strong>Bank:</strong> The Family Bank</p>
                    <p style="margin: 0.25rem 0; color: #333;"><strong>Paybill Number:</strong> <span style="color: #8B0000; font-size: 1.2em; font-weight: bold;">222111</span></p>
                    <p style="margin: 0.25rem 0; color: #333;"><strong>Account Number:</strong> <span style="color: #8B0000; font-size: 1.2em; font-weight: bold;">085000092737</span></p>
                    <p style="margin: 0.5rem 0 0 0; color: #666; font-size: 0.85em;">
                        <i class="fas fa-info-circle"></i> Go to M-Pesa → Pay Bill → Enter details above
                    </p>
                </div>
                <a href="submit-mpesa?payment_id=<?php echo $paymentId; ?>" class="btn btn-secondary" style="width: 100%; display: inline-block; text-align: center; padding: 1rem; background: #6c757d; color: white; text-decoration: none; border-radius: 8px;">
                    <i class="fas fa-paste"></i>
                    Already Paid? Submit M-PESA Confirmation
                </a>
                <p style="font-size: 0.9rem; color: #666; margin-top: 0.5rem; text-align: center;">
                    After paying to the account above, paste your M-Pesa confirmation message
                </p>
            </div>
        <?php elseif ($payment['status'] === 'pending' && $payment['mpesa_checkout_request_id']): ?>
            <div class="mpesa-instructions">
                <h4><i class="fas fa-clock"></i> Payment in Progress</h4>
                <p>Please check your phone and enter your M-PESA PIN to complete the payment. The page will automatically update once payment is confirmed.</p>
            </div>

            <div class="loading-spinner" id="loadingSpinner">
                <div class="spinner"></div>
                <p>Checking payment status...</p>
            </div>

            <form method="POST" class="payment-actions">
                <input type="hidden" name="action" value="check_status">
                <button type="submit" class="btn btn-primary" id="checkStatusBtn">
                    <i class="fas fa-sync"></i>
                    Check Status
                </button>
            </form>
        <?php elseif ($payment['status'] === 'completed'): ?>
            <div class="payment-actions">
                <a href="../dashboard/" class="btn btn-primary">
                    <i class="fas fa-tachometer-alt"></i>
                    Go to Dashboard
                </a>
            </div>
        <?php elseif ($payment['status'] === 'failed'): ?>
            <div class="payment-actions">
                <a href="../subscriptions/subscribe" class="btn btn-primary">
                    <i class="fas fa-redo"></i>
                    Try Again
                </a>
            </div>
        <?php endif; ?>

        <div class="payment-actions">
            <a href="../dashboard/" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </a>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="../js/main.js"></script>
    <script>
        function startPaymentPolling(paymentId) {
            const loadingSpinner = document.getElementById('loadingSpinner');
            const checkStatusBtn = document.getElementById('checkStatusBtn');
            
            if (loadingSpinner) {
                loadingSpinner.style.display = 'block';
            }
            if (checkStatusBtn) {
                checkStatusBtn.disabled = true;
            }
            
            // Poll for payment status every 5 seconds
            const pollInterval = setInterval(function() {
                fetch('process.php?payment_id=' + paymentId, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=check_status'
                })
                .then(response => response.text())
                .then(data => {
                    // Check if payment is completed or failed
                    if (data.includes('status-completed') || data.includes('status-failed')) {
                        clearInterval(pollInterval);
                        location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error checking payment status:', error);
                });
            }, 5000);
            
            // Stop polling after 5 minutes
            setTimeout(function() {
                clearInterval(pollInterval);
                if (loadingSpinner) {
                    loadingSpinner.style.display = 'none';
                }
                if (checkStatusBtn) {
                    checkStatusBtn.disabled = false;
                }
            }, 300000);
        }
        
        // Auto-start polling if payment is pending
        <?php if ($payment['status'] === 'pending' && $payment['mpesa_checkout_request_id']): ?>
            startPaymentPolling('<?php echo $paymentId; ?>');
        <?php endif; ?>
    </script>
</body>
</html>
