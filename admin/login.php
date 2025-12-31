<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/functions.php';

// Check if already logged in
// Check if already logged in
if (isAdmin()) {
    session_write_close();
    header('Location: index.php');
    exit();
}
// echo "DEBUG SESSION: " . print_r($_SESSION, true);

$db = Database::getInstance();
$conn = $db->getConnection();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCSRFToken($_POST['csrf_token'] ?? '');

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    if (empty($email) || empty($password)) {
        $errors[] = 'Please enter both email and password.';
    } else {
        $stmt = $conn->prepare("SELECT * FROM admin_users WHERE email = ? AND is_active = true LIMIT 1");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        if ($admin && verifyPassword($password, $admin['password_hash'])) {
            // Authentication successful
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['admin_name'] = $admin['full_name'];
            $_SESSION['is_admin'] = true;
            $_SESSION['admin_last_activity'] = time();
            $_SESSION['admin_role'] = 'Administrator'; // Default role
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
            $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
            $_SESSION['last_activity'] = time();

            // Regenerate session ID to prevent session fixation
            // session_regenerate_id(true);

            // Set secure session cookie if "Remember me" is checked
            if ($remember) {
                $token = bin2hex(random_bytes(32));
                $expires = time() + (86400 * 30); // 30 days

                // Store token in database
                $stmt = $conn->prepare("UPDATE admin_users SET remember_token = ?, token_expires_at = ? WHERE id = ?");
                $stmt->execute([$token, date('Y-m-d H:i:s', $expires), $admin['id']]);

                // Set secure cookie
                setcookie(
                    'remember_admin',
                    $token,
                    [
                        'expires' => $expires,
                        'path' => '/',
                        'domain' => $_SERVER['HTTP_HOST'],
                        'secure' => true,
                        'httponly' => true,
                        'samesite' => 'Strict'
                    ]
                );
            }

            // Log the login
            logActivity($admin['id'], 'admin_login', 'Admin logged in');

            // Redirect to intended URL or dashboard
            $redirect = $_SESSION['post_login_redirect'] ?? '/admin';
            unset($_SESSION['post_login_redirect']);

            // Force session write before redirect
            session_write_close();

            header('Location: ' . $redirect);
            exit();
        } else {
            // Log failed login attempt
            logActivity(0, 'failed_admin_login', 'Failed admin login attempt for email: ' . $email);
            $errors[] = 'Invalid email or password.';
        }
    }
}

// Generate CSRF token for the login form
$csrfToken = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - BingeTV</title>

    <!-- Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- CSS (reuse public styles for consistency) -->
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/components.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .admin-login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #8B0000 0%, #660000 100%);
            padding: 20px;
        }

        .admin-login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .admin-logo {
            margin-bottom: 30px;
        }

        .admin-logo i {
            font-size: 3rem;
            color: #8B0000;
            margin-bottom: 10px;
        }

        .admin-logo h1 {
            color: #8B0000;
            font-family: 'Orbitron', sans-serif;
            font-weight: 900;
            margin: 0;
        }

        .admin-logo p {
            color: #666;
            margin: 5px 0 0 0;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #8B0000;
        }

        .login-btn {
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

        .login-btn:hover {
            transform: translateY(-2px);
        }

        .error-message {
            background: #fee;
            color: #c33;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #c33;
        }

        .back-to-site {
            margin-top: 20px;
        }

        .back-to-site a {
            color: #8B0000;
            text-decoration: none;
            font-weight: 500;
        }

        .back-to-site a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="admin-login-container">
        <div class="admin-login-card">
            <div class="admin-logo">
                <i class="fas fa-satellite-dish"></i>
                <h1>BingeTV Admin</h1>
                <p>Administrative Dashboard</p>
            </div>

            <form action="login" method="POST" class="admin-login-form">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $error): ?>
                            <div><?php echo htmlspecialchars($error); ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control"
                        value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required autofocus>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-input">
                        <input type="password" id="password" name="password" class="form-control" required>
                        <button type="button" class="toggle-password" onclick="togglePassword('password')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group remember-me">
                    <input type="checkbox" id="remember" name="remember" value="1" <?php echo isset($_POST['remember']) ? 'checked' : ''; ?>>
                    <label for="remember">Remember me</label>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-sign-in-alt mr-2"></i> Sign In
                </button>

                <div class="text-center mt-3">
                    <a href="/forgot-password" class="forgot-password">Forgot your password?</a>
                </div>
            </form>

            <div class="back-to-site">
                <a href="/index">
                    <i class="fas fa-arrow-left"></i> Back to Main Site
                </a>
            </div>
        </div>
    </div>
</body>

</html>