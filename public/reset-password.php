<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../lib/functions.php';
require_once '../lib/seo.php';

// Get SEO data
// Manually set title/description if not in SEO class yet, or assume it is handled by 'forgot-password' logic or default
// Ideally we add 'reset-password' to SEO class commit later, but for now hardcode or use default
$pageTitle = 'Reset Password - BingeTV';

$message = '';
$messageType = '';
$validToken = false;
$user_id = null;

$token = $_GET['token'] ?? '';

if (empty($token)) {
    header('Location: login');
    exit();
}

$db = Database::getInstance();
$conn = $db->getConnection();

// Validate token
try {
    $current_time = date('Y-m-d H:i:s');
    $query = "SELECT id FROM users WHERE password_reset_token = ? AND password_reset_expires > ? AND is_active = true";
    $stmt = $conn->prepare($query);
    $stmt->execute([$token, $current_time]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $validToken = true;
        $user_id = $user['id'];
    } else {
        $message = 'Invalid or expired password reset token.';
        $messageType = 'error';
    }
} catch (PDOException $e) {
    $message = 'An error occurred validating your request.';
    $messageType = 'error';
}

// Handle Password Reset
if ($validToken && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (strlen($password) < 6) {
        $message = 'Password must be at least 6 characters.';
        $messageType = 'error';
    } elseif ($password !== $confirm_password) {
        $message = 'Passwords do not match.';
        $messageType = 'error';
    } else {
        try {
            require_once '../includes/auth.php'; // For Auth class or hashing functions
            // Assuming Auth methods are available or use lib/functions.php hashPassword
            $hashed_password = hashPassword($password);

            $updateQuery = "UPDATE users SET password_hash = ?, password_reset_token = NULL, password_reset_expires = NULL WHERE id = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->execute([$hashed_password, $user_id]);

            $message = 'Password has been reset successfully! You can now login.';
            $messageType = 'success';
            $validToken = false; // Disable form

            // Redirect to login after short delay
            header("refresh:3;url=login");

        } catch (Exception $e) {
            $message = 'Failed to reset password. Please try again.';
            $messageType = 'error';
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
    <title>
        <?php echo htmlspecialchars($pageTitle); ?>
    </title>

    <link
        href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/components.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .reset-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #8B0000 0%, #660000 100%);
            padding: 20px;
        }

        .reset-card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 100%;
        }

        .reset-header {
            text-align: center;
            margin-bottom: 24px;
        }

        .reset-header h1 {
            color: #8B0000;
            font-family: 'Orbitron', sans-serif;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
        }

        .btn-primary {
            width: 100%;
            padding: 12px;
            background: #8B0000;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
        }

        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>

<body>
    <div class="reset-page">
        <div class="reset-card">
            <div class="reset-header">
                <h1>Set New Password</h1>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if ($validToken): ?>
                <form method="POST">
                    <div class="form-group">
                        <label for="password">New Password</label>
                        <input type="password" id="password" name="password" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                    </div>
                    <button type="submit" class="btn-primary">Reset Password</button>
                </form>
            <?php elseif ($messageType === 'success'): ?>
                <p style="text-align: center;">Redirecting to login...</p>
                <div style="text-align: center; margin-top: 15px;">
                    <a href="login" style="color: #8B0000; text-decoration: none; font-weight: 600;">Go to Login Now</a>
                </div>
            <?php else: ?>
                <div style="text-align: center; margin-top: 15px;">
                    <a href="login" style="color: #8B0000; text-decoration: none; font-weight: 600;">Return to Login</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>