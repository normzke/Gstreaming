<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../lib/functions.php';
require_once '../lib/seo.php';
require_once '../lib/email.php';

// Get SEO data
$seo_meta = SEO::getMetaTags('forgot-password');
$og_tags = SEO::getOpenGraphTags('forgot-password');
$canonical_url = SEO::getCanonicalUrl('forgot-password');

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        $message = 'Please enter your email address.';
        $messageType = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
        $messageType = 'error';
    } else {
        $db = Database::getInstance();
        $conn = $db->getConnection();

        try {
            // Check if user exists
            $query = "SELECT id, first_name FROM users WHERE email = ? AND is_active = true";
            $stmt = $conn->prepare($query);
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $message = 'No account found with this email address.';
                $messageType = 'error';
            } else {
                // Generate reset token
                $resetToken = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

                // Store reset token
                $updateQuery = "UPDATE users SET password_reset_token = ?, password_reset_expires = ? WHERE id = ?";
                $updateStmt = $conn->prepare($updateQuery);
                $updateStmt->execute([$resetToken, $expires, $user['id']]);

                // Send reset email
                $emailSent = sendPasswordResetEmail($email, $resetToken, $user['first_name']);

                if ($emailSent) {
                    $message = 'Password reset instructions sent! Please check your email.';
                    $messageType = 'success';
                } else {
                    $message = 'Failed to send reset email. Please try again later.';
                    $messageType = 'error';
                }
            }
        } catch (Exception $e) {
            $message = 'An error occurred. Please try again.';
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
    
    <title><?php echo htmlspecialchars($seo_meta['title']); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($seo_meta['description']); ?>">
    <meta name="robots" content="noindex, nofollow">
    <link rel="canonical" href="<?php echo $canonical_url; ?>">
    
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/components.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .forgot-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #8B0000 0%, #660000 100%);
            padding: 20px;
        }
        
        .forgot-container {
            max-width: 500px;
            margin: 0 auto;
            width: 100%;
        }
        
        .forgot-card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        
        .forgot-header {
            text-align: center;
            margin-bottom: 24px;
        }
        
        .forgot-header h1 {
            color: #8B0000;
            font-family: 'Orbitron', sans-serif;
            font-weight: 900;
            margin: 0 0 8px 0;
        }
        
        .forgot-header p {
            color: #666;
            margin: 0;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 6px;
            color: #333;
            font-weight: 600;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 14px;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #8B0000;
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
        
        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .alert-success {
            background: #d1fae5;
            border: 1px solid #a7f3d0;
            color: #065f46;
        }
        
        .alert-error {
            background: #fee2e2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }
        
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .back-link a {
            color: #8B0000;
            text-decoration: none;
            font-weight: 600;
        }
        
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <main class="forgot-page">
        <div class="forgot-container">
            <div class="forgot-card">
                <div class="forgot-header">
                    <h1>Reset Password</h1>
                    <p>Enter your email address to receive password reset instructions</p>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?>">
                        <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="forgot-form">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                    </div>

                    <button type="submit" class="btn-primary">
                        <i class="fas fa-paper-plane"></i>
                        Send Reset Instructions
                    </button>
                </form>

                <div class="back-link">
                    <a href="login">
                        <i class="fas fa-arrow-left"></i>
                        Back to Login
                    </a>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
