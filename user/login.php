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
        $requiredFields = ['email', 'password'];
        $validationErrors = validateRequired($requiredFields, $_POST);
        
        if (!empty($validationErrors)) {
            $errors = array_merge($errors, $validationErrors);
        } else {
            $email = sanitizeInput($_POST['email']);
            $password = $_POST['password'];
            $remember = isset($_POST['remember']);
            
            // Validate email
            if (!validateEmail($email)) {
                $errors[] = 'Please enter a valid email address.';
            }
            
            if (empty($errors)) {
                $db = new Database();
                $conn = $db->getConnection();

                $query = "SELECT * FROM users WHERE email = :email AND is_active = true";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':email', $email);
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user && verifyPassword($password, $user['password_hash'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];

                    // Log login activity
                    logActivity($user['id'], 'user_login', 'User logged in');

                    // Update last login time
                    $updateQuery = "UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = :id";
                    $updateStmt = $conn->prepare($updateQuery);
                    $updateStmt->bindParam(':id', $user['id']);
                    $updateStmt->execute();

                    // Set remember me cookie if requested
                    if ($remember) {
                        $token = bin2hex(random_bytes(32));
                        $expires = time() + (30 * 24 * 60 * 60); // 30 days
                        setcookie('remember_token', $token, $expires, '/', '', true, true);
                        
                        // Store token in database
                        $tokenQuery = "INSERT INTO user_tokens (user_id, token, expires_at) VALUES (?, ?, ?)";
                        $tokenStmt = $conn->prepare($tokenQuery);
                        $tokenStmt->execute([$user['id'], hash('sha256', $token), date('Y-m-d H:i:s', $expires)]);
                    }

                    redirect('dashboard/', 'Welcome back!');
                } else {
                    $errors[] = 'Invalid email or password.';
                }
            }
        }
    }
}

// Get SEO data
$seo_meta = SEO::getMetaTags('login');
$og_tags = SEO::getOpenGraphTags('login');
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
    <link rel="canonical" href="<?php echo SEO::getCanonicalUrl('login'); ?>">
    
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
        .login-page {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-color) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header .logo {
            font-family: 'Orbitron', sans-serif;
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .login-header h1 {
            color: var(--text-color-light);
            margin-bottom: 10px;
        }
        
        .login-header p {
            color: var(--text-color-secondary);
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
        
        .form-group input {
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
        
        .form-group input:focus {
            outline: none;
            border-color: var(--primary-color);
        }
        
        .form-group input::placeholder {
            color: var(--text-color-tertiary);
        }
        
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
        }
        
        .remember-me input {
            margin-right: 8px;
        }
        
        .forgot-password {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 0.9rem;
        }
        
        .forgot-password:hover {
            text-decoration: underline;
        }
        
        .login-btn {
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
        
        .login-btn:hover {
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
        
        .register-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .register-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }
        
        .register-link a:hover {
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
    </style>
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-header">
            <div class="logo">
                <i class="fas fa-satellite-dish"></i>
                BingeTV
            </div>
            <h1>Welcome Back</h1>
            <p>Sign in to your account to continue</p>
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
        
        <?php displayMessage(); ?>

        <form action="login.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required 
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                       placeholder="Enter your email">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-input">
                    <input type="password" id="password" name="password" required 
                           placeholder="Enter your password">
                    <button type="button" class="password-toggle" onclick="togglePassword('password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="form-options">
                <div class="remember-me">
                    <input type="checkbox" id="remember" name="remember" 
                           <?php echo isset($_POST['remember']) ? 'checked' : ''; ?>>
                    <label for="remember">Remember me</label>
                </div>
                <a href="forgot-password.php" class="forgot-password">Forgot Password?</a>
            </div>

            <button type="submit" class="login-btn">
                <i class="fas fa-sign-in-alt"></i>
                Sign In
            </button>
        </form>

        <div class="divider">
            <span>or</span>
        </div>

        <div class="register-link">
            <p>Don't have an account? <a href="register.php">Sign up here</a></p>
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
    </script>
</body>
</html>
