<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../lib/functions.php';
require_once '../lib/seo.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard/');
    exit();
}

$errors = [];
$success = '';

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
                        createNotification($userId, 'general', 'Welcome to BingeTV!', 
                            'Welcome to BingeTV! Your account has been created successfully. Complete your subscription to start streaming.');
                        
                        // Send welcome email
                        $emailBody = getEmailTemplate('welcome', [
                            'first_name' => $firstName,
                            'email' => $email
                        ]);
                        
                        if ($emailBody) {
                            sendEmail($email, 'Welcome to BingeTV!', $emailBody);
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

// Get SEO data
$seo_meta = SEO::getMetaTags('register');
$og_tags = SEO::getOpenGraphTags('register');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- SEO Meta Tags -->
    <title><?php echo htmlspecialchars($seo_meta['title']); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($seo_meta['description']); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($seo_meta['keywords']); ?>">
    <meta name="robots" content="<?php echo htmlspecialchars($seo_meta['robots']); ?>">
    <link rel="canonical" href="<?php echo SEO::getCanonicalUrl('register'); ?>">
    
    <!-- Open Graph Tags -->
    <meta property="og:title" content="<?php echo htmlspecialchars($og_tags['og:title']); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($og_tags['og:description']); ?>">
    <meta property="og:url" content="<?php echo htmlspecialchars($og_tags['og:url']); ?>">
    <meta property="og:type" content="<?php echo htmlspecialchars($og_tags['og:type']); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($og_tags['og:image']); ?>">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/components.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .register-page {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-color) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .register-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 500px;
        }
        
        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .register-header .logo {
            font-family: 'Orbitron', sans-serif;
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .register-header h1 {
            color: var(--text-color-light);
            margin-bottom: 10px;
        }
        
        .register-header p {
            color: var(--text-color-secondary);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-color-light);
            font-weight: 500;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        
        .password-input {
            position: relative;
        }
        
        .password-input input {
            padding-right: 45px;
        }
        
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-color-secondary);
            cursor: pointer;
            padding: 5px;
        }
        
        .password-toggle:hover {
            color: var(--primary-color);
        }
        
        .form-help {
            display: block;
            margin-top: 5px;
            font-size: 0.8rem;
            color: var(--text-color-secondary);
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary-color);
        }
        
        .form-group input::placeholder {
            color: var(--text-color-tertiary);
        }
        
        .password-strength {
            margin-top: 5px;
            font-size: 0.8rem;
        }
        
        .strength-weak { color: #dc3545; }
        .strength-medium { color: #ffc107; }
        .strength-strong { color: #28a745; }
        
        .terms-checkbox {
            display: flex;
            align-items: flex-start;
            margin-bottom: 25px;
        }
        
        .terms-checkbox input {
            margin-right: 10px;
            margin-top: 3px;
        }
        
        .terms-checkbox label {
            font-size: 0.9rem;
            color: var(--text-color-secondary);
        }
        
        .terms-checkbox a {
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .terms-checkbox a:hover {
            text-decoration: underline;
        }
        
        .register-btn {
            width: 100%;
            padding: 12px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        
        .register-btn:hover {
            background: var(--primary-dark);
        }
        
        .divider {
            text-align: center;
            margin: 25px 0;
            position: relative;
            color: var(--text-color-secondary);
        }
        
        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: var(--border-color);
        }
        
        .divider span {
            background: white;
            padding: 0 15px;
        }
        
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .login-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
        
        .package-summary {
            background: #f8f9fa;
            border: 2px solid var(--primary-color);
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .package-summary h3 {
            color: var(--primary-color);
            margin-bottom: 15px;
            text-align: center;
        }
        
        .package-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .package-name {
            font-weight: 600;
            color: var(--text-color-light);
        }
        
        .package-price {
            text-align: right;
        }
        
        .currency {
            font-size: 0.9rem;
            color: var(--text-color-secondary);
        }
        
        .amount {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .period {
            font-size: 0.9rem;
            color: var(--text-color-secondary);
        }
        
        @media (max-width: 600px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .package-info {
                flex-direction: column;
                text-align: center;
            }
            
            .package-price {
                margin-top: 10px;
            }
        }
    </style>
</head>
<body class="register-page">
    <div class="register-container">
        <div class="register-header">
            <div class="logo">
                <i class="fas fa-satellite-dish"></i>
                BingeTV
            </div>
            <h1>Create Account</h1>
            <p>Join BingeTV and start streaming today</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success-message">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="first_name">First Name *</label>
                    <input type="text" id="first_name" name="first_name" required 
                           value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>"
                           placeholder="Enter your first name">
                </div>
                
                <div class="form-group">
                    <label for="last_name">Last Name *</label>
                    <input type="text" id="last_name" name="last_name" required 
                           value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>"
                           placeholder="Enter your last name">
                </div>
            </div>

            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" id="email" name="email" required 
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                       placeholder="Enter your email address">
            </div>

            <div class="form-group">
                <label for="phone">Phone Number *</label>
                <input type="tel" id="phone" name="phone" required 
                       placeholder="0712345678 or 254712345678"
                       value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                <small class="form-help">Enter your Kenyan phone number</small>
            </div>

            <div class="form-group">
                <label for="country">Country</label>
                <select id="country" name="country" required>
                    <option value="Kenya" <?php echo ($_POST['country'] ?? '') === 'Kenya' ? 'selected' : ''; ?>>Kenya</option>
                    <option value="Uganda" <?php echo ($_POST['country'] ?? '') === 'Uganda' ? 'selected' : ''; ?>>Uganda</option>
                    <option value="Tanzania" <?php echo ($_POST['country'] ?? '') === 'Tanzania' ? 'selected' : ''; ?>>Tanzania</option>
                    <option value="Rwanda" <?php echo ($_POST['country'] ?? '') === 'Rwanda' ? 'selected' : ''; ?>>Rwanda</option>
                    <option value="Other" <?php echo ($_POST['country'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>

            <div class="form-group">
                <label for="password">Password *</label>
                <div class="password-input">
                <input type="password" id="password" name="password" required 
                       placeholder="Create a strong password">
                    <button type="button" class="password-toggle" onclick="togglePassword('password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <small class="form-help">Minimum 8 characters</small>
                <div id="password-strength" class="password-strength"></div>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password *</label>
                <div class="password-input">
                <input type="password" id="confirm_password" name="confirm_password" required 
                       placeholder="Confirm your password">
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

            <div class="terms-checkbox">
                <input type="checkbox" id="terms" name="terms" required>
                <label for="terms">
                    I agree to the <a href="terms.php" target="_blank">Terms of Service</a> 
                    and <a href="privacy.php" target="_blank">Privacy Policy</a>
                </label>
            </div>

            <button type="submit" class="register-btn">
                <i class="fas fa-user-plus"></i>
                <?php echo $package ? 'Register & Subscribe' : 'Create Account'; ?>
            </button>
        </form>

        <div class="divider">
            <span>or</span>
        </div>

        <div class="login-link">
            <p>Already have an account? <a href="login.php">Sign in here</a></p>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="js/main.js"></script>
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
        
        // Password strength checker
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthDiv = document.getElementById('password-strength');
            
            if (password.length === 0) {
                strengthDiv.textContent = '';
                return;
            }
            
            let strength = 0;
            if (password.length >= 8) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            if (strength < 3) {
                strengthDiv.textContent = 'Weak password';
                strengthDiv.className = 'password-strength strength-weak';
            } else if (strength < 5) {
                strengthDiv.textContent = 'Medium strength';
                strengthDiv.className = 'password-strength strength-medium';
            } else {
                strengthDiv.textContent = 'Strong password';
                strengthDiv.className = 'password-strength strength-strong';
            }
        });
        
        // Password confirmation checker
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (confirmPassword.length > 0) {
                if (password === confirmPassword) {
                    this.style.borderColor = '#28a745';
                } else {
                    this.style.borderColor = '#dc3545';
                }
            } else {
                this.style.borderColor = '';
            }
        });
        
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
