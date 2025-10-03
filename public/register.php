<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'lib/functions.php';
require_once 'lib/seo.php';

// Start session
// Session is started in config files

// Get SEO data
$seo_meta = SEO::getMetaTags('register');
$og_tags = SEO::getOpenGraphTags('register');
$canonical_url = SEO::getCanonicalUrl('register');

// Check if user is already logged in
if (isLoggedIn()) {
    redirect('user/dashboard/', 'You are already logged in!');
}

$error = '';
$success = '';

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $packageId = (int)($_GET['package'] ?? 0);
    
    // Validation
    if (empty($firstName) || empty($lastName) || empty($email) || empty($phone) || empty($password)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } else {
        $db = new Database();
        $conn = $db->getConnection();
        
        try {
            $conn->beginTransaction();
            
            // Check if email already exists
            $checkQuery = "SELECT id FROM users WHERE email = ?";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->execute([$email]);
            
            if ($checkStmt->fetch()) {
                $error = 'An account with this email already exists.';
            } else {
                // Create user
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $userQuery = "INSERT INTO users (first_name, last_name, email, phone, password, is_active, created_at) VALUES (?, ?, ?, ?, ?, true, NOW())";
                $userStmt = $conn->prepare($userQuery);
                $userStmt->execute([$firstName, $lastName, $email, $phone, $hashedPassword]);
                
                        $userId = $conn->lastInsertId();
                        
                // Set session variables
                $_SESSION['user_id'] = $userId;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_name'] = $firstName . ' ' . $lastName;
                        
                        // If package is selected, redirect to payment
                        if ($packageId) {
                            $conn->commit();
                    redirect("user/payment.php?package={$packageId}", 'Registration successful! Complete your subscription to start streaming.');
                        } else {
                            $conn->commit();
                    redirect('user/dashboard/', 'Registration successful! Welcome to BingeTV.');
                }
            }
        } catch (Exception $e) {
            $conn->rollback();
            $error = 'Registration failed. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="https://bingetv.co.ke/">
    
    <!-- SEO Meta Tags -->
    <title><?php echo htmlspecialchars($seo_meta['title']); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($seo_meta['description']); ?>">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?php echo $canonical_url; ?>">
    
    <!-- Open Graph Tags -->
    <meta property="og:title" content="<?php echo htmlspecialchars($og_tags['og:title']); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($og_tags['og:description']); ?>">
    <meta property="og:url" content="<?php echo htmlspecialchars($og_tags['og:url']); ?>">
    <meta property="og:type" content="<?php echo htmlspecialchars($og_tags['og:type']); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($og_tags['og:image']); ?>">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="https://bingetv.co.ke/css/main.css">
    <link rel="stylesheet" href="https://bingetv.co.ke/css/components.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .register-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, rgba(0, 212, 255, 0.1) 0%, rgba(124, 58, 237, 0.1) 100%);
            padding: 2rem 0;
        }
        
        .register-container {
            max-width: 500px;
            margin: 0 auto;
            width: 100%;
        }
        
        .register-card {
            background: var(--white);
            border-radius: var(--radius-2xl);
            padding: var(--spacing-3xl);
            box-shadow: var(--shadow-xl);
            position: relative;
            overflow: hidden;
        }
        
        .register-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--gradient-primary);
        }
        
        .register-header {
            text-align: center;
            margin-bottom: var(--spacing-2xl);
        }
        
        .register-header h1 {
            color: var(--text-primary);
            margin-bottom: var(--spacing-sm);
        }
        
        .register-header p {
            color: var(--text-secondary);
            margin: 0;
        }
        
        .package-notice {
            background: var(--bg-accent);
            border: 1px solid var(--primary-color);
            border-radius: var(--radius-lg);
            padding: var(--spacing-md);
            margin-top: var(--spacing-md);
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            color: var(--primary-color);
            font-weight: 500;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--spacing-lg);
        }
        
        .form-group {
            margin-bottom: var(--spacing-lg);
        }
        
        .form-group label {
            display: block;
            margin-bottom: var(--spacing-sm);
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .input-group {
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .input-group i {
            position: absolute;
            left: var(--spacing-md);
            color: var(--text-muted);
            z-index: 1;
        }
        
        .input-group input {
            width: 100%;
            padding: var(--spacing-md) var(--spacing-md) var(--spacing-md) 3rem;
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-lg);
            font-size: 1rem;
            transition: all var(--transition-normal);
            background: var(--white);
        }
        
        .input-group input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(139, 0, 0, 0.1);
        }
        
        .password-toggle {
            position: absolute;
            right: var(--spacing-md);
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            z-index: 1;
            padding: var(--spacing-sm);
        }
        
        .password-toggle:hover {
            color: var(--primary-color);
        }
        
        .password-strength {
            margin-top: var(--spacing-sm);
        }
        
        .strength-bar {
            width: 100%;
            height: 4px;
            background: var(--gray-200);
            border-radius: var(--radius-sm);
            overflow: hidden;
            margin-bottom: var(--spacing-xs);
        }
        
        .strength-fill {
            height: 100%;
            width: 0%;
            transition: all var(--transition-normal);
            border-radius: var(--radius-sm);
        }
        
        .strength-text {
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .checkbox-label {
            display: flex;
            align-items: flex-start;
            cursor: pointer;
            font-size: 0.875rem;
            color: var(--text-secondary);
            line-height: 1.5;
        }
        
        .checkbox-label input[type="checkbox"] {
            margin-right: var(--spacing-sm);
            margin-top: 2px;
        }
        
        .checkbox-label a {
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .checkbox-label a:hover {
            text-decoration: underline;
        }
        
        .register-footer {
            text-align: center;
            margin-top: var(--spacing-xl);
        }
        
        .register-footer p {
            margin: var(--spacing-sm) 0;
            color: var(--text-secondary);
        }
        
        .register-footer a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }
        
        .register-footer a:hover {
            text-decoration: underline;
        }
        
        .alert {
            padding: var(--spacing-md);
            border-radius: var(--radius-lg);
            margin-bottom: var(--spacing-lg);
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
        }
        
        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
        }
        
        .alert-success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #16a34a;
        }
        
        @media (max-width: 768px) {
            .register-page {
                padding: 1rem;
            }
            
            .register-card {
                padding: var(--spacing-xl);
            }
            
            .form-row {
                grid-template-columns: 1fr;
                gap: var(--spacing-md);
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <i class="fas fa-satellite-dish"></i>
                <span class="logo-text">BingeTV</span>
            </div>
            
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="/" class="nav-link">Home</a>
                </li>
                <li class="nav-item">
                    <a href="https://bingetv.co.ke/channels.php" class="nav-link">Channels</a>
                </li>
                <li class="nav-item">
                    <a href="https://bingetv.co.ke/gallery.php" class="nav-link">Gallery</a>
                </li>
                <li class="nav-item">
                    <a href="https://bingetv.co.ke/support.php" class="nav-link">Support</a>
                </li>
                <li class="nav-item">
                    <a href="https://bingetv.co.ke/login.php" class="nav-link btn-login">Login</a>
                </li>
                <li class="nav-item">
                    <a href="https://bingetv.co.ke/register.php" class="nav-link active">Get Started</a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Register Content -->
    <main class="register-page">
        <div class="container">
            <div class="register-container">
                <div class="register-card">
                    <div class="register-header">
                        <h1>Create Your Account</h1>
                        <p>Join thousands of satisfied BingeTV subscribers</p>
                        <?php if ($packageId): ?>
                            <div class="package-notice">
                                <i class="fas fa-info-circle"></i>
                                <span>You're registering for a selected package</span>
                            </div>
                            <?php endif; ?>
                    </div>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <?php echo htmlspecialchars($success); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="register-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name">First Name *</label>
                                <div class="input-group">
                                    <i class="fas fa-user"></i>
                                    <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="last_name">Last Name *</label>
                                <div class="input-group">
                                    <i class="fas fa-user"></i>
                                    <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <div class="input-group">
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone Number *</label>
                            <div class="input-group">
                                <i class="fas fa-phone"></i>
                                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" placeholder="+254 700 000 000" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                        <div class="form-group">
                                <label for="password">Password *</label>
                                <div class="input-group">
                                    <i class="fas fa-lock"></i>
                                    <input type="password" id="password" name="password" required>
                                <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                                <div class="password-strength">
                                    <div class="strength-bar">
                                        <div class="strength-fill" id="strength-fill"></div>
                                    </div>
                                    <span class="strength-text" id="strength-text">Password strength</span>
                                </div>
                        </div>
                        
                        <div class="form-group">
                                <label for="confirm_password">Confirm Password *</label>
                                <div class="input-group">
                                    <i class="fas fa-lock"></i>
                                    <input type="password" id="confirm_password" name="confirm_password" required>
                                <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="terms" value="1" required>
                                <span class="checkmark"></span>
                                I agree to the <a href="https://bingetv.co.ke/terms.php" target="_blank">Terms of Service</a> and <a href="https://bingetv.co.ke/privacy.php" target="_blank">Privacy Policy</a>
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-full">
                            <i class="fas fa-user-plus"></i>
                            Create Account
                        </button>
                    </form>
                    
                    <div class="register-footer">
                        <p>Already have an account? <a href="https://bingetv.co.ke/login.php">Sign in here</a></p>
                        <p><a href="/">← Back to Homepage</a></p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <div class="footer-logo">
                        <i class="fas fa-satellite-dish"></i>
                        <span>BingeTV</span>
                    </div>
                    <p>Premium TV streaming service for Kenya.</p>
                </div>
                
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="/">Home</a></li>
                        <li><a href="https://bingetv.co.ke/channels.php">Channels</a></li>
                        <li><a href="https://bingetv.co.ke/gallery.php">Gallery</a></li>
                        <li><a href="https://bingetv.co.ke/support.php">Support</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Account</h4>
                    <ul class="footer-links">
                        <li><a href="https://bingetv.co.ke/login.php">Login</a></li>
                        <li><a href="https://bingetv.co.ke/register.php">Register</a></li>
                        <li><a href="packages.php">Packages</a></li>
                        <li><a href="https://bingetv.co.ke/support.php">Support</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> BingeTV. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="https://bingetv.co.ke/js/main.js"></script>
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
            const strengthFill = document.getElementById('strength-fill');
            const strengthText = document.getElementById('strength-text');
            
            let strength = 0;
            let strengthLabel = 'Weak';
            let strengthColor = '#ef4444';
            
            if (password.length >= 8) strength++;
            if (password.match(/[a-z]/)) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;
            
            if (strength >= 4) {
                strengthLabel = 'Strong';
                strengthColor = '#10b981';
            } else if (strength >= 2) {
                strengthLabel = 'Medium';
                strengthColor = '#f59e0b';
            }
            
            strengthFill.style.width = (strength * 20) + '%';
            strengthFill.style.backgroundColor = strengthColor;
            strengthText.textContent = strengthLabel;
            strengthText.style.color = strengthColor;
        });
        
        // Password confirmation checker
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (confirmPassword && password !== confirmPassword) {
                this.style.borderColor = '#ef4444';
            } else {
                this.style.borderColor = '#d1d5db';
            }
        });
    </script>
</body>
</html>