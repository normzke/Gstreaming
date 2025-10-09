<?php
// Updated: 2024-01-15 12:00:00
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/functions.php';
require_once __DIR__ . '/../lib/seo.php';

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
    $emailOrUsername = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    if (empty($emailOrUsername) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
                $db = new Database();
                $conn = $db->getConnection();
                
        try {
                // Get user by email OR username
            $userQuery = "SELECT * FROM users WHERE (email = ? OR username = ?) AND is_active = true";
            $userStmt = $conn->prepare($userQuery);
            $userStmt->execute([$emailOrUsername, $emailOrUsername]);
            $user = $userStmt->fetch();
            if (!$user) {
                error_log('LOGIN_FAIL: user_not_found emailOrUsername=' . $emailOrUsername);
            }
            $passwordOk = $user ? password_verify($password, $user['password_hash']) : false;
            if ($user && !$passwordOk) {
                error_log('LOGIN_FAIL: bad_password emailOrUsername=' . $emailOrUsername);
            }
            
            if ($user && $passwordOk) {
                // Update last login
                $updateQuery = "UPDATE users SET last_login = NOW() WHERE id = ?";
                $updateStmt = $conn->prepare($updateQuery);
                $updateStmt->execute([$user['id']]);

                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];

                // Handle remember me (graceful if table doesn't exist)
                if ($remember) {
                    try {
                        $token = bin2hex(random_bytes(32));
                        $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
                        
                        $tokenQuery = "INSERT INTO remember_tokens (user_id, token, expires_at) VALUES (?, ?, ?)";
                        $tokenStmt = $conn->prepare($tokenQuery);
                        $tokenStmt->execute([$user['id'], $token, $expires]);
                        
                        setcookie('remember_token', $token, strtotime('+30 days'), '/', '', true, true);
                    } catch (PDOException $e) {
                        // Table may not exist; skip remember feature silently
                        error_log('remember_tokens insert failed: ' . $e->getMessage());
                    }
                }

                // Check if user has an active subscription
                $subQuery = "SELECT * FROM user_subscriptions 
                            WHERE user_id = ? 
                            AND status = 'active' 
                            AND end_date > NOW() 
                            LIMIT 1";
                $subStmt = $conn->prepare($subQuery);
                $subStmt->execute([$user['id']]);
                $activeSubscription = $subStmt->fetch();

                // Redirect to saved destination or appropriate page
                if (!empty($_SESSION['post_login_redirect'])) {
                    $dest = $_SESSION['post_login_redirect'];
                    unset($_SESSION['post_login_redirect']);
                    header('Location: /' . ltrim($dest, '/'));
                    exit();
                } elseif (!$activeSubscription) {
                    // New user without subscription - go to subscriptions page
                    header('Location: user/subscriptions.php');
                    exit();
                } else {
                    // Existing user with subscription - go to dashboard
                    redirect('user/dashboard/', 'Welcome back!');
                }
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
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/components.css">
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
                <li class="nav-item"><a href="/" class="nav-link">Home</a></li>
                <li class="nav-item"><a href="channels.php" class="nav-link">Channels</a></li>
                <li class="nav-item"><a href="packages.php" class="nav-link">Packages</a></li>
                <li class="nav-item"><a href="gallery.php" class="nav-link">Gallery</a></li>
                <li class="nav-item"><a href="support.php" class="nav-link">Support</a></li>
                <li class="nav-item"><a href="login.php" class="nav-link btn-login active">Login</a></li>
                <li class="nav-item"><a href="register.php" class="nav-link btn-register">Get Started</a></li>
            </ul>
            <div class="hamburger">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>
        </div>
    </nav>

    <style>
        .auth-container { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #8B0000 0%, #660000 100%); padding: 20px; }
        .auth-card { background: white; border-radius: 15px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); padding: 40px; width: 100%; max-width: 480px; }
        .auth-header { text-align: center; margin-bottom: 24px; }
        .auth-header h1 { color: #8B0000; font-family: 'Orbitron', sans-serif; font-weight: 900; margin: 0 0 8px 0; }
        .auth-header p { color: #666; margin: 0; }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; margin-bottom: 6px; color: #333; font-weight: 600; }
        .input-group { position: relative; }
        .input-group input { width: 100%; padding: 12px 14px 12px 44px; border: 2px solid #e1e1e1; border-radius: 8px; font-size: 16px; }
        .input-group input:focus { outline: none; border-color: #8B0000; }
        .input-group i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #999; }
        .password-input-container { position: relative; }
        .password-input-container input { width: 100%; padding: 12px 44px 12px 14px; border: 2px solid #e1e1e1; border-radius: 8px; font-size: 16px; }
        .password-input-container input:focus { outline: none; border-color: #8B0000; }
        .password-toggle { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: #999; cursor: pointer; }
        .form-options { display: flex; align-items: center; justify-content: space-between; margin: 12px 0 20px; }
        .btn-primary { width: 100%; padding: 12px; background: linear-gradient(135deg, #8B0000, #660000); color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: transform 0.2s ease; }
        .btn-primary:hover { transform: translateY(-1px); }
        .alert.alert-error { background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 12px; border-radius: 8px; margin-bottom: 12px; }
        .form-footer { text-align: center; margin-top: 16px; color: #666; }
        .form-footer a { color: #8B0000; text-decoration: none; font-weight: 600; }
    </style>

    <main class="auth-container" style="padding-top:120px; padding-bottom:40px;">
        <div class="auth-card">
            <div class="auth-header">
                <h1>Login</h1>
                <p>Access your personalized streaming experience</p>
            </div>
            <form action="https://bingetv.co.ke/login.php" method="POST" class="auth-form" novalidate>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-error">
                        <p><?php echo htmlspecialchars($error); ?></p>
                    </div>
                <?php endif; ?>
                <div class="form-group">
                    <label for="email">Email or Username</label>
                    <div class="input-group">
                        <i class="fas fa-user"></i>
                        <input type="text" id="email" name="email" value="<?php echo htmlspecialchars($emailOrUsername ?? ''); ?>" placeholder="Enter your email or username" required autocomplete="username">
                    </div>
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
                <button type="submit" class="btn-primary">Login</button>
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
                            <span>support@BingeTV.com</span>
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
    <script src="../js/main.js"></script>
    <script src="../js/animations.js"></script>
    <script src="../js/enhanced.js"></script>
    <script>
        (function(){
            const hamburger = document.querySelector('.hamburger');
            const navMenu = document.querySelector('.nav-menu');
            if (!hamburger || !navMenu) return;
            const toggle = () => {
                hamburger.classList.toggle('active');
                navMenu.classList.toggle('active');
                const expanded = hamburger.classList.contains('active');
                hamburger.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            };
            hamburger.setAttribute('aria-label', 'Toggle navigation');
            hamburger.setAttribute('aria-expanded', 'false');
            hamburger.addEventListener('click', toggle);
            // Close on link click (mobile)
            document.querySelectorAll('.nav-link').forEach(link => {
                link.addEventListener('click', () => {
                    hamburger.classList.remove('active');
                    navMenu.classList.remove('active');
                    hamburger.setAttribute('aria-expanded', 'false');
                });
            });
            // Ensure menu state resets on resize
            window.addEventListener('resize', () => {
                if (window.innerWidth > 992) {
                    hamburger.classList.remove('active');
                    navMenu.classList.remove('active');
                    hamburger.setAttribute('aria-expanded', 'false');
                }
            });
        })();
    </script>
</body>
</html>
