<?php
// Updated: 2024-01-15 12:00:00
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/functions.php';
require_once __DIR__ . '/../lib/session_manager.php';
require_once __DIR__ . '/../lib/seo.php';

// Session is started in config files

// Get SEO data
$seo_meta = SEO::getMetaTags('login');
$og_tags = SEO::getOpenGraphTags('login');
$canonical_url = SEO::getCanonicalUrl('login');

// Check if user is already logged in
if (isLoggedIn()) {
    redirect('/user/dashboard', 'You are already logged in!');
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
        $db = Database::getInstance();
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
                $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
                $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
                $_SESSION['last_activity'] = time();

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

                // Create device session with limit enforcement
                $deviceId = getDeviceId();
                $deviceType = getDeviceType();
                $deviceName = $_POST['device_name'] ?? ucfirst($deviceType) . ' Browser';

                $sessionResult = createUserSession($user['id'], $deviceId, $deviceName, $deviceType);

                if (!$sessionResult['success']) {
                    // Device limit exceeded
                    $error = $sessionResult['error'];

                    // Log the attempt
                    logActivity($user['id'], 'login_blocked', 'Device limit exceeded: ' . $deviceId);

                    // Don't proceed with login
                    unset($_SESSION['user_id']);
                    unset($_SESSION['user_email']);
                    unset($_SESSION['user_name']);
                } else {
                    // Store session token
                    $_SESSION['session_token'] = $sessionResult['session_token'];

                    // Log successful login
                    logActivity($user['id'], 'login_success', 'Device: ' . $deviceName);

                    // Log successful login
                    logActivity($user['id'], 'login_success', 'Device: ' . $deviceName);

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

                        // Validate internal redirect to prevent open redirect vulnerabilities
                        $dest = '/' . ltrim($dest, '/');

                        // Force session write before redirect
                        session_write_close();

                        header('Location: ' . $dest);
                        exit();
                    } elseif (!$activeSubscription) {
                        // New user without subscription - go to subscriptions page
                        session_write_close();
                        header('Location: /user/subscriptions');
                        exit();
                    } else {
                        // Existing user with subscription - go to dashboard
                        session_write_close();
                        redirect('/user/dashboard', 'Welcome back!');
                    }
                }
            } else {
                // This else block handles both user not found and bad password
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
    <link
        href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/components.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

</head>

<body>
    <!-- Navigation -->
    <?php include 'includes/navigation.php'; ?>

    <style>
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #8B0000 0%, #660000 100%);
            padding: 20px;
        }

        .auth-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 480px;
        }

        .auth-header {
            text-align: center;
            margin-bottom: 24px;
        }

        .auth-header h1 {
            color: #8B0000;
            font-family: 'Orbitron', sans-serif;
            font-weight: 900;
            margin: 0 0 8px 0;
        }

        .auth-header p {
            color: #666;
            margin: 0;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            color: #333;
            font-weight: 600;
        }

        .input-group {
            position: relative;
        }

        .input-group input {
            width: 100%;
            padding: 12px 14px 12px 44px;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            font-size: 16px;
        }

        .input-group input:focus {
            outline: none;
            border-color: #8B0000;
        }

        .input-group i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }

        .password-input-container {
            position: relative;
        }

        .password-input-container input {
            width: 100%;
            padding: 12px 44px 12px 14px;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            font-size: 16px;
        }

        .password-input-container input:focus {
            outline: none;
            border-color: #8B0000;
        }

        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            cursor: pointer;
        }

        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 12px 0 20px;
        }

        .btn-primary {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #8B0000, #660000);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
        }

        .alert.alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 12px;
        }

        .form-footer {
            text-align: center;
            margin-top: 16px;
            color: #666;
        }

        .form-footer a {
            color: #8B0000;
            text-decoration: none;
            font-weight: 600;
        }
    </style>

    <main class="auth-container" style="padding-top:120px; padding-bottom:40px;">
        <div class="auth-card">
            <div class="auth-header">
                <h1>Login</h1>
                <p>Access your personalized streaming experience</p>
            </div>
            <form action="/login" method="POST" class="auth-form" novalidate>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-error">
                        <p><?php echo htmlspecialchars($error); ?></p>
                    </div>
                <?php endif; ?>
                <div class="form-group">
                    <label for="email">Email or Username</label>
                    <div class="input-group">
                        <i class="fas fa-user"></i>
                        <input type="text" id="email" name="email"
                            value="<?php echo htmlspecialchars($emailOrUsername ?? ''); ?>"
                            placeholder="Enter your email or username" required autocomplete="username">
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
                    <a href="https://bingetv.co.ke/forgot-password" class="forgot-password">Forgot Password?</a>
                </div>
                <button type="submit" class="btn-primary">Login</button>
            </form>
            <div class="form-footer">
                <p>Don't have an account? <a href="https://bingetv.co.ke/register">Register here</a></p>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>
</body>

</html>