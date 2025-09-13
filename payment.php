<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/mpesa.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php', 'Please login to continue.');
}

// Get package details
$packageId = $_GET['package'] ?? null;
if (!$packageId) {
    redirect('index.php#packages', 'Please select a package first.');
}

$db = new Database();
$conn = $db->getConnection();

$packageQuery = "SELECT * FROM packages WHERE id = :id AND is_active = true";
$packageStmt = $conn->prepare($packageQuery);
$packageStmt->bindParam(':id', $packageId);
$packageStmt->execute();
$package = $packageStmt->fetch();

if (!$package) {
    redirect('index.php#packages', 'Package not found.');
}

$user = getCurrentUser();
$errors = [];
$success = false;

// Handle payment form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $phoneNumber = sanitizeInput($_POST['phone']);
        $amount = floatval($_POST['amount']);
        
        // Validate phone number
        $validatedPhone = validatePhone($phoneNumber);
        if (!$validatedPhone) {
            $errors[] = 'Please enter a valid Kenyan phone number.';
        }
        
        // Validate amount
        if ($amount != $package['price']) {
            $errors[] = 'Invalid amount.';
        }
        
        if (empty($errors)) {
            // Process M-PESA payment
            $paymentResult = processMPesaPayment($user['id'], $package['id'], $validatedPhone, $amount);
            
            if ($paymentResult['success']) {
                $_SESSION['payment_id'] = $paymentResult['payment_id'];
                $_SESSION['checkout_request_id'] = $paymentResult['checkout_request_id'];
                redirect('payment-status.php', 'Payment initiated successfully!');
            } else {
                $errors[] = $paymentResult['error'];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - GStreaming</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/components.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="payment-page">
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <i class="fas fa-satellite-dish"></i>
                <span class="logo-text">GStreaming</span>
            </div>
            
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link">
                        <i class="fas fa-user"></i>
                        <?php echo htmlspecialchars($user['first_name']); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="logout.php" class="nav-link btn-login">Logout</a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Payment Section -->
    <section class="payment-section">
        <div class="container">
            <div class="payment-container">
                <div class="payment-header">
                    <h1 class="payment-title">Complete Your Subscription</h1>
                    <p class="payment-subtitle">Secure payment via M-PESA</p>
                </div>
                
                <div class="payment-content">
                    <!-- Package Summary -->
                    <div class="package-summary-card">
                        <div class="package-header">
                            <h3><?php echo htmlspecialchars($package['name']); ?></h3>
                            <div class="package-features">
                                <?php 
                                $features = json_decode($package['features'], true);
                                if ($features):
                                    foreach ($features as $key => $value):
                                        if (is_bool($value)) {
                                            $value = $value ? 'Yes' : 'No';
                                        }
                                        echo '<div class="feature-item">';
                                        echo '<i class="fas fa-check"></i>';
                                        echo '<span>' . ucfirst(str_replace('_', ' ', $key)) . ': ' . $value . '</span>';
                                        echo '</div>';
                                    endforeach;
                                endif;
                                ?>
                            </div>
                        </div>
                        
                        <div class="package-price">
                            <span class="currency">KES</span>
                            <span class="amount"><?php echo number_format($package['price']); ?></span>
                            <span class="period">/month</span>
                        </div>
                    </div>
                    
                    <!-- Payment Form -->
                    <div class="payment-form-card">
                        <h3>Payment Details</h3>
                        
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="error-list">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" class="payment-form">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <input type="hidden" name="amount" value="<?php echo $package['price']; ?>">
                            
                            <div class="form-group">
                                <label for="phone" class="form-label">M-PESA Phone Number *</label>
                                <input type="tel" 
                                       id="phone" 
                                       name="phone" 
                                       class="form-input" 
                                       placeholder="0712345678 or 254712345678"
                                       value="<?php echo htmlspecialchars($user['phone']); ?>"
                                       required>
                                <small class="form-help">Enter the phone number linked to your M-PESA account</small>
                            </div>
                            
                            <div class="payment-method">
                                <h4>Payment Method</h4>
                                <div class="payment-options">
                                    <div class="payment-option active">
                                        <div class="payment-icon">
                                            <i class="fas fa-mobile-alt"></i>
                                        </div>
                                        <div class="payment-info">
                                            <h5>M-PESA</h5>
                                            <p>Pay via M-PESA Till or Paybill</p>
                                        </div>
                                        <div class="payment-radio">
                                            <input type="radio" name="payment_method" value="mpesa" checked>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="payment-summary">
                                <div class="summary-row">
                                    <span>Package:</span>
                                    <span><?php echo htmlspecialchars($package['name']); ?></span>
                                </div>
                                <div class="summary-row">
                                    <span>Duration:</span>
                                    <span><?php echo $package['duration_days']; ?> days</span>
                                </div>
                                <div class="summary-row">
                                    <span>Max Devices:</span>
                                    <span><?php echo $package['max_devices']; ?></span>
                                </div>
                                <div class="summary-row total">
                                    <span>Total Amount:</span>
                                    <span class="total-amount">KES <?php echo number_format($package['price']); ?></span>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-large btn-full">
                                <i class="fas fa-credit-card"></i>
                                Pay with M-PESA
                            </button>
                        </form>
                        
                        <div class="payment-security">
                            <div class="security-info">
                                <i class="fas fa-shield-alt"></i>
                                <div class="security-text">
                                    <h5>Secure Payment</h5>
                                    <p>Your payment is processed securely through M-PESA. We never store your payment details.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="payment-help">
                    <h4>Need Help?</h4>
                    <div class="help-options">
                        <a href="support.php" class="help-link">
                            <i class="fas fa-headset"></i>
                            Contact Support
                        </a>
                        <a href="help.php#payment" class="help-link">
                            <i class="fas fa-question-circle"></i>
                            Payment FAQ
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- JavaScript -->
    <script src="assets/js/main.js"></script>
    <script>
        // Format phone number as user types
        document.getElementById('phone').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            
            // Auto-format Kenyan phone numbers
            if (value.startsWith('254')) {
                // Already in 254 format
                e.target.value = value;
            } else if (value.startsWith('0')) {
                // Convert 07xxxxxxxx to 2547xxxxxxxx
                if (value.length <= 10) {
                    e.target.value = value;
                } else {
                    e.target.value = '254' + value.substring(1);
                }
            } else if (value.startsWith('7')) {
                // Add 254 prefix if starts with 7
                e.target.value = '254' + value;
            } else {
                e.target.value = value;
            }
        });
        
        // Form validation
        document.querySelector('.payment-form').addEventListener('submit', function(e) {
            const phone = document.getElementById('phone').value;
            const phoneRegex = /^(254|0)(7|1)[0-9]{8}$/;
            
            if (!phoneRegex.test(phone.replace(/\D/g, ''))) {
                e.preventDefault();
                alert('Please enter a valid Kenyan phone number');
                return false;
            }
        });
    </script>
</body>
</html>
