<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../lib/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: ../../login.php');
    exit();
}

$db = new Database();
$conn = $db->getConnection();
$user = getCurrentUser();

$message = '';
$messageType = '';

// Get payment ID from URL or session
$paymentId = $_GET['payment_id'] ?? $_SESSION['pending_payment_id'] ?? 0;

// Get payment details
$payment = null;
if ($paymentId) {
    $paymentQuery = "SELECT p.*, pk.name as package_name 
                    FROM payments p 
                    JOIN packages pk ON p.package_id = pk.id 
                    WHERE p.id = ? AND p.user_id = ?";
    $paymentStmt = $conn->prepare($paymentQuery);
    $paymentStmt->execute([$paymentId, $_SESSION['user_id']]);
    $payment = $paymentStmt->fetch();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mpesaMessage = trim($_POST['mpesa_message'] ?? '');
    $mpesaCode = trim($_POST['mpesa_code'] ?? '');
    $phoneNumber = trim($_POST['phone_number'] ?? '');
    $amount = (float)($_POST['amount'] ?? 0);
    $packageId = (int)($_POST['package_id'] ?? 0);
    
    if (empty($mpesaMessage)) {
        $message = 'Please paste your M-Pesa confirmation message.';
        $messageType = 'error';
    } elseif ($amount <= 0) {
        $message = 'Invalid amount.';
        $messageType = 'error';
    } else {
        try {
            $conn->beginTransaction();
            
            // Create or update payment record
            if (!$paymentId) {
                $insertPayment = "INSERT INTO payments (user_id, package_id, amount, payment_method, status, is_manual_confirmation, created_at)
                                 VALUES (?, ?, ?, 'mpesa_manual', 'pending', true, NOW())";
                $paymentStmt = $conn->prepare($insertPayment);
                $paymentStmt->execute([$_SESSION['user_id'], $packageId, $amount]);
                $paymentId = $conn->lastInsertId();
            }
            
            // Save manual submission
            $submitQuery = "INSERT INTO manual_payment_submissions (user_id, payment_id, package_id, amount, mpesa_code, mpesa_message, phone_number, status)
                           VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')";
            $submitStmt = $conn->prepare($submitQuery);
            $submitStmt->execute([$_SESSION['user_id'], $paymentId, $packageId, $amount, $mpesaCode, $mpesaMessage, $phoneNumber]);
            
            $conn->commit();
            
            $message = 'M-Pesa confirmation submitted successfully! Our team will review and activate your subscription within 1 hour.';
            $messageType = 'success';
            
            // Clear form
            $mpesaMessage = '';
            $mpesaCode = '';
            
        } catch (Exception $e) {
            $conn->rollback();
            $message = 'Failed to submit confirmation. Please try again or contact support.';
            $messageType = 'error';
            error_log('MANUAL_MPESA_SUBMISSION_ERROR: ' . $e->getMessage());
        }
    }
}

$page_title = 'Submit M-Pesa Payment';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - BingeTV</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Orbitron:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/user/css/main.css">
    <link rel="stylesheet" href="/user/css/components.css">
    <style>
        .submit-mpesa-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
        }
        .mpesa-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 1rem;
            margin: 1rem 0;
            border-radius: 4px;
        }
        .warning-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 1rem;
            margin: 1rem 0;
            border-radius: 4px;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            font-size: 1rem;
            font-family: inherit;
        }
        .form-group textarea {
            min-height: 150px;
            resize: vertical;
        }
        .form-group input:focus, .form-group textarea:focus {
            outline: none;
            border-color: #8B0000;
        }
        .btn-submit {
            background: linear-gradient(135deg, #8B0000, #660000);
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(139, 0, 0, 0.3);
        }
        .payment-details {
            background: #f9f9f9;
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
        }
        .payment-details p {
            margin: 0.5rem 0;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    
    <div class="submit-mpesa-container">
        <div class="mpesa-card">
            <h1><i class="fas fa-mobile-alt"></i> Submit M-Pesa Payment Confirmation</h1>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>" style="padding: 1rem; border-radius: 8px; margin: 1rem 0; background: <?php echo $messageType === 'success' ? '#d4edda' : '#f8d7da'; ?>; color: <?php echo $messageType === 'success' ? '#155724' : '#721c24'; ?>;">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <div class="info-box">
                <h3><i class="fas fa-info-circle"></i> How to Pay via M-Pesa</h3>
                <ol>
                    <li>Go to <strong>M-Pesa</strong> on your phone</li>
                    <li>Select <strong>"Lipa Na M-Pesa"</strong> → <strong>"Pay Bill"</strong></li>
                    <li>Enter Business Number: <strong style="color: #8B0000; font-size: 1.2em;">222111</strong> (The Family Bank)</li>
                    <li>Enter Account Number: <strong style="color: #8B0000; font-size: 1.2em;">085000092737</strong></li>
                    <li>Enter the amount shown below</li>
                    <li>Enter your M-Pesa PIN and confirm</li>
                    <li>Copy the entire M-Pesa confirmation SMS you receive</li>
                    <li>Paste it in the form below</li>
                    <li>Our admin team will verify and activate your subscription within 1 hour</li>
                </ol>
            </div>
            
            <div class="warning-box" style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 1rem; margin: 1rem 0; border-radius: 4px;">
                <p style="margin: 0; color: #856404;"><i class="fas fa-exclamation-triangle"></i> <strong>Payment Details:</strong></p>
                <div style="margin-top: 0.5rem; padding: 0.75rem; background: white; border-radius: 4px;">
                    <p style="margin: 0.25rem 0;"><strong>Paybill Number:</strong> <span style="color: #8B0000; font-size: 1.1em; font-weight: bold;">222111</span> (The Family Bank)</p>
                    <p style="margin: 0.25rem 0;"><strong>Account Number:</strong> <span style="color: #8B0000; font-size: 1.1em; font-weight: bold;">085000092737</span></p>
                    <p style="margin: 0.25rem 0; color: #666; font-size: 0.9em;">Make sure to enter these details correctly!</p>
                </div>
            </div>
            
            <?php if ($payment): ?>
                <div class="payment-details">
                    <h3>Payment Details</h3>
                    <p><strong>Package:</strong> <?php echo htmlspecialchars($payment['package_name']); ?></p>
                    <p><strong>Amount:</strong> KSh <?php echo number_format($payment['amount'], 2); ?></p>
                    <p><strong>Payment ID:</strong> #<?php echo $payment['id']; ?></p>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="mpesa_message">M-Pesa Confirmation Message *</label>
                    <textarea id="mpesa_message" name="mpesa_message" required placeholder="Paste your complete M-Pesa SMS here. Example:&#10;&#10;XXXXXXXXXX Confirmed. You have received Ksh2,500.00 from JOHN DOE 254712345678 on 08/10/25 at 3:45 PM..."><?php echo htmlspecialchars($mpesaMessage ?? ''); ?></textarea>
                    <small style="color: #666;">Paste the ENTIRE M-Pesa confirmation message you received</small>
                </div>
                
                <div class="form-group">
                    <label for="mpesa_code">M-Pesa Transaction Code *</label>
                    <input type="text" id="mpesa_code" name="mpesa_code" value="<?php echo htmlspecialchars($mpesaCode ?? ''); ?>" placeholder="e.g., XXXXXXXXXX" required>
                    <small style="color: #666;">The code at the beginning of your M-Pesa message</small>
                </div>
                
                <div class="form-group">
                    <label for="phone_number">Phone Number Used *</label>
                    <input type="tel" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($phoneNumber ?? $user['phone'] ?? ''); ?>" placeholder="+254..." required>
                </div>
                
                <div class="form-group">
                    <label for="amount">Amount Paid *</label>
                    <input type="number" id="amount" name="amount" value="<?php echo $payment ? $payment['amount'] : ''; ?>" step="0.01" min="1" required>
                </div>
                
                <input type="hidden" name="package_id" value="<?php echo $payment ? $payment['package_id'] : ($_GET['package_id'] ?? 0); ?>">
                
                <div class="warning-box">
                    <p><i class="fas fa-exclamation-triangle"></i> <strong>Important:</strong></p>
                    <ul>
                        <li>Make sure the amount matches exactly what you paid</li>
                        <li>Submit within 24 hours of payment</li>
                        <li>Keep your M-Pesa message for reference</li>
                        <li>Admin will review within 1 hour during business hours</li>
                    </ul>
                </div>
                
                <button type="submit" class="btn-submit">
                    <i class="fas fa-paper-plane"></i> Submit for Verification
                </button>
            </form>
            
            <p style="text-align: center; margin-top: 2rem; color: #666;">
                Need help? <a href="/user/support.php" style="color: #8B0000;">Contact Support</a>
            </p>
        </div>
    </div>
    
    <script>
        // Extract M-Pesa code from message automatically
        document.getElementById('mpesa_message').addEventListener('input', function() {
            const message = this.value;
            // Try to extract M-Pesa code (usually starts with letters/numbers)
            const codeMatch = message.match(/^([A-Z0-9]{10})/);
            if (codeMatch) {
                document.getElementById('mpesa_code').value = codeMatch[1];
            }
            
            // Try to extract amount
            const amountMatch = message.match(/Ksh\s?([\d,]+\.?\d*)/i);
            if (amountMatch) {
                const amount = amountMatch[1].replace(/,/g, '');
                document.getElementById('amount').value = amount;
            }
        });
    </script>
</body>
</html>

