<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('dashboard.php');
}

$errors = [];
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        // Validate required fields
        $requiredFields = ['first_name', 'last_name', 'email', 'phone', 'password', 'confirm_password'];
        $validationErrors = validateRequired($requiredFields, $_POST);
        
        if (!empty($validationErrors)) {
            $errors = array_merge($errors, $validationErrors);
        } else {
            // Sanitize input data
            $firstName = sanitizeInput($_POST['first_name']);
            $lastName = sanitizeInput($_POST['last_name']);
            $email = sanitizeInput($_POST['email']);
            $phone = sanitizeInput($_POST['phone']);
            $password = $_POST['password'];
            $confirmPassword = $_POST['confirm_password'];
            $packageId = $_GET['package'] ?? null;
            
            // Validate email
            if (!validateEmail($email)) {
                $errors[] = 'Please enter a valid email address.';
            }
            
            // Validate phone
            $validatedPhone = validatePhone($phone);
            if (!$validatedPhone) {
                $errors[] = 'Please enter a valid Kenyan phone number (e.g., 0712345678 or 254712345678).';
            }
            
            // Validate password
            if (strlen($password) < 8) {
                $errors[] = 'Password must be at least 8 characters long.';
            }
            
            if ($password !== $confirmPassword) {
                $errors[] = 'Passwords do not match.';
            }
            
            // Check if email already exists
            if (empty($errors)) {
                $db = new Database();
                $conn = $db->getConnection();
                
                $checkEmailQuery = "SELECT id FROM users WHERE email = :email";
                $checkStmt = $conn->prepare($checkEmailQuery);
                $checkStmt->bindParam(':email', $email);
                $checkStmt->execute();
                
                if ($checkStmt->fetch()) {
                    $errors[] = 'An account with this email already exists.';
                }
            }
            
            // Create user account
            if (empty($errors)) {
                try {
                    $conn->beginTransaction();
                    
                    // Insert user
                    $insertUserQuery = "INSERT INTO users (first_name, last_name, email, phone, password_hash, created_at) 
                                       VALUES (:first_name, :last_name, :email, :phone, :password_hash, NOW())";
                    
                    $insertStmt = $conn->prepare($insertUserQuery);
                    $insertStmt->bindParam(':first_name', $firstName);
                    $insertStmt->bindParam(':last_name', $lastName);
                    $insertStmt->bindParam(':email', $email);
                    $insertStmt->bindParam(':phone', $validatedPhone);
                    $insertStmt->bindParam(':password_hash', hashPassword($password));
                    
                    if ($insertStmt->execute()) {
                        $userId = $conn->lastInsertId();
                        
                        // Log user registration
                        logActivity($userId, 'user_registration', 'New user registered');
                        
                        // Create welcome notification
                        createNotification($userId, 'general', 'Welcome to GStreaming!', 
                            'Welcome to GStreaming! Your account has been created successfully. Complete your subscription to start streaming.');
                        
                        // Send welcome email
                        $emailBody = getEmailTemplate('welcome', [
                            'first_name' => $firstName,
                            'email' => $email
                        ]);
                        
                        if ($emailBody) {
                            sendEmail($email, 'Welcome to GStreaming!', $emailBody);
                        }
                        
                        // If package is selected, redirect to payment
                        if ($packageId) {
                            $conn->commit();
                            $_SESSION['user_id'] = $userId;
                            $_SESSION['user_email'] = $email;
                            $_SESSION['user_name'] = $firstName . ' ' . $lastName;
                            redirect("payment.php?package={$packageId}", 'Registration successful! Complete your subscription to start streaming.');
                        } else {
                            $conn->commit();
                            redirect('login.php', 'Registration successful! Please login to continue.');
                        }
                    } else {
                        $errors[] = 'Registration failed. Please try again.';
                    }
                } catch (Exception $e) {
                    $conn->rollback();
                    $errors[] = 'Registration failed. Please try again.';
                }
            }
        }
    }
}

// Get package details if package ID is provided
$package = null;
if (isset($_GET['package'])) {
    $db = new Database();
    $conn = $db->getConnection();
    
    $packageQuery = "SELECT * FROM packages WHERE id = :id AND is_active = true";
    $packageStmt = $conn->prepare($packageQuery);
    $packageStmt->bindParam(':id', $_GET['package']);
    $packageStmt->execute();
    $package = $packageStmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - GStreaming</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/components.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="auth-page">
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <i class="fas fa-satellite-dish"></i>
                <span class="logo-text">GStreaming</span>
            </div>
            
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="index.php" class="nav-link">Home</a>
                </li>
                <li class="nav-item">
                    <a href="index.php#packages" class="nav-link">Packages</a>
                </li>
                <li class="nav-item">
                    <a href="login.php" class="nav-link btn-login">Login</a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Registration Form -->
    <section class="auth-section">
        <div class="container">
            <div class="auth-container">
                <div class="auth-card">
                    <div class="auth-header">
                        <h1 class="auth-title">Create Account</h1>
                        <p class="auth-subtitle">
                            <?php if ($package): ?>
                                Complete your registration and subscribe to <strong><?php echo htmlspecialchars($package['name']); ?></strong>
                            <?php else: ?>
                                Join thousands of satisfied customers
                            <?php endif; ?>
                        </p>
                    </div>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="error-list">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <?php displayMessage(); ?>
                    
                    <form method="POST" class="auth-form">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name" class="form-label">First Name *</label>
                                <input type="text" 
                                       id="first_name" 
                                       name="first_name" 
                                       class="form-input" 
                                       value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>"
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label for="last_name" class="form-label">Last Name *</label>
                                <input type="text" 
                                       id="last_name" 
                                       name="last_name" 
                                       class="form-input" 
                                       value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>"
                                       required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="email" class="form-label">Email Address *</label>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   class="form-input" 
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone" class="form-label">Phone Number *</label>
                            <input type="tel" 
                                   id="phone" 
                                   name="phone" 
                                   class="form-input" 
                                   placeholder="0712345678 or 254712345678"
                                   value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                                   required>
                            <small class="form-help">Enter your Kenyan phone number</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="password" class="form-label">Password *</label>
                            <div class="password-input">
                                <input type="password" 
                                       id="password" 
                                       name="password" 
                                       class="form-input" 
                                       required>
                                <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <small class="form-help">Minimum 8 characters</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password" class="form-label">Confirm Password *</label>
                            <div class="password-input">
                                <input type="password" 
                                       id="confirm_password" 
                                       name="confirm_password" 
                                       class="form-input" 
                                       required>
                                <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <?php if ($package): ?>
                        <div class="package-summary">
                            <h3>Selected Package</h3>
                            <div class="package-info">
                                <div class="package-name"><?php echo htmlspecialchars($package['name']); ?></div>
                                <div class="package-price">
                                    <span class="currency">KES</span>
                                    <span class="amount"><?php echo number_format($package['price']); ?></span>
                                    <span class="period">/month</span>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="terms" required>
                                <span class="checkbox-text">
                                    I agree to the <a href="terms.php" target="_blank">Terms of Service</a> 
                                    and <a href="privacy.php" target="_blank">Privacy Policy</a>
                                </span>
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-full">
                            <i class="fas fa-user-plus"></i>
                            <?php echo $package ? 'Register & Subscribe' : 'Create Account'; ?>
                        </button>
                    </form>
                    
                    <div class="auth-footer">
                        <p>Already have an account? <a href="login.php">Login here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- JavaScript -->
    <script src="assets/js/main.js"></script>
    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const button = input.nextElementSibling;
            const icon = button.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
        
        // Form validation
        document.querySelector('.auth-form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
            
            if (password.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters long!');
                return false;
            }
        });
    </script>
</body>
</html>
