<?php
// Updated: 2024-01-15 12:00:00
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'lib/functions.php';
require_once 'lib/seo.php';

// Session is started in config files

// Get SEO data
$seo_meta = SEO::getMetaTags('login');
$og_tags = SEO::getOpenGraphTags('login');
$canonical_url = SEO::getCanonicalUrl('login');

// Check if user is already logged in
if (isLoggedIn()) {
    redirect('user/dashboard/', 'You are already logged in!');
}

$error = '';
$success = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $db = new Database();
        $conn = $db->getConnection();
        
        try {
            // Get user by email
            $userQuery = "SELECT * FROM users WHERE email = ? AND is_active = true";
            $userStmt = $conn->prepare($userQuery);
            $userStmt->execute([$email]);
            $user = $userStmt->fetch();
            
            if ($user && password_verify($password, $user['password_hash'])) {
                // Update last login
                $updateQuery = "UPDATE users SET last_login = NOW() WHERE id = ?";
                $updateStmt = $conn->prepare($updateQuery);
                $updateStmt->execute([$user['id']]);
                
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                
                // Handle remember me
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
                    
                    $tokenQuery = "INSERT INTO remember_tokens (user_id, token, expires_at) VALUES (?, ?, ?)";
                    $tokenStmt = $conn->prepare($tokenQuery);
                    $tokenStmt->execute([$user['id'], $token, $expires]);
                    
                    setcookie('remember_token', $token, strtotime('+30 days'), '/', '', true, true);
                }
                
                // Redirect to dashboard
                redirect('user/dashboard/', 'Welcome back!');
            } else {
                $error = 'Invalid email or password.';
            }
        } catch (Exception $e) {
            $error = 'Login failed. Please try again.';
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
    <title><?php echo htmlspecialchars($seo_meta['title']); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($seo_meta['description']); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($seo_meta['keywords']); ?>">
    <link rel="canonical" href="<?php echo $canonical_url; ?>">
    <link rel="icon" type="image/x-icon" href="https://bingetv.co.ke/images/favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://bingetv.co.ke/css/main.css">
    <link rel="stylesheet" href="https://bingetv.co.ke/css/components.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <i class="fas fa-satellite-dish"></i>
                <span class="logo-text">BingeTV</span>
            </div>
            <ul class="nav-menu">
                <li class="nav-item"><a href="https://bingetv.co.ke/" class="nav-link">Home</a></li>
                <li class="nav-item"><a href="https://bingetv.co.ke/channels.php" class="nav-link">Channels</a></li>
                <li class="nav-item"><a href="https://bingetv.co.ke/packages.php" class="nav-link">Packages</a></li>
                <li class="nav-item"><a href="https://bingetv.co.ke/gallery.php" class="nav-link">Gallery</a></li>
                <li class="nav-item"><a href="https://bingetv.co.ke/support.php" class="nav-link">Support</a></li>
                <li class="nav-item"><a href="https://bingetv.co.ke/login.php" class="nav-link btn-login active">Login</a></li>
                <li class="nav-item"><a href="https://bingetv.co.ke/register.php" class="nav-link btn-register">Get Started</a></li>
            </ul>
            <div class="hamburger">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>
        </div>
    </nav>

    <main class="container content-minimal form-page">
        <div class="form-card">
            <div class="form-header">
                <h1>Login to Your Account</h1>
                <p>Access your personalized streaming experience</p>
            </div>
            <form action="https://bingetv.co.ke/login_new.php" method="POST" class="auth-form">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-error">
                        <p><?php echo htmlspecialchars($error); ?></p>
                    </div>
                <?php endif; ?>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-input-container">
                        <input type="password" id="password" name="password" required>
                        <span class="password-toggle"><i class="fas fa-eye-slash"></i></span>
                    </div>
                </div>
                <div class="form-options">
                    <div class="remember-me">
                        <input type="checkbox" id="remember" name="remember" <?php echo ($remember ?? false) ? 'checked' : ''; ?>>
                        <label for="remember">Remember Me</label>
                    </div>
                    <a href="https://bingetv.co.ke/forgot-password.php" class="forgot-password">Forgot Password?</a>
                </div>
                <button type="submit" class="btn btn-primary btn-full">Login</button>
            </form>
            <div class="form-footer">
                <p>Don't have an account? <a href="https://bingetv.co.ke/register.php">Register here</a></p>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <div class="footer-logo">
                        <i class="fas fa-satellite-dish"></i>
                        <span>BingeTV</span>
                    </div>
                    <p>Premium TV streaming service for Kenya. Stream thousands of channels on any device.</p>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="https://bingetv.co.ke/packages.php">Packages</a></li>
                        <li><a href="https://bingetv.co.ke/#devices">Supported Devices</a></li>
                        <li><a href="https://bingetv.co.ke/gallery.php">Gallery</a></li>
                        <li><a href="https://bingetv.co.ke/support.php">Support</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Account</h4>
                    <ul class="footer-links">
                        <li><a href="https://bingetv.co.ke/login.php">Login</a></li>
                        <li><a href="https://bingetv.co.ke/register.php">Register</a></li>
                        <li><a href="https://bingetv.co.ke/packages.php">Packages</a></li>
                        <li><a href="https://bingetv.co.ke/support.php">Support</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contact Info</h4>
                    <div class="contact-info">
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <span>support@gstreaming.com</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <span>+254 768 704 834</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Nairobi, Kenya</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <div class="footer-bottom-content">
                    <p>&copy; <?php echo date('Y'); ?> BingeTV. All rights reserved.</p>
                    <div class="footer-bottom-links">
                        <a href="https://bingetv.co.ke/privacy.php">Privacy Policy</a>
                        <a href="https://bingetv.co.ke/terms.php">Terms of Service</a>
                        <a href="https://bingetv.co.ke/refund.php">Refund Policy</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <div class="whatsapp-float">
        <a href="https://wa.me/254768704834?text=Hello%2C%20I%20need%20help%20with%20BingeTV" target="_blank" class="whatsapp-btn">
            <i class="fab fa-whatsapp"></i>
            <span class="whatsapp-text">Chat with us</span>
        </a>
    </div>
    <script src="https://bingetv.co.ke/js/main.js"></script>
    <script src="https://bingetv.co.ke/js/animations.js"></script>
    <script src="https://bingetv.co.ke/js/enhanced.js"></script>
</body>
</html>
