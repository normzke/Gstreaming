<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../lib/functions.php';

// Redirect if already logged in as admin
if (isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit();
}

$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validate required fields
    if (empty($email) || empty($password)) {
        $errors[] = 'Email and password are required.';
    } else {
        // Validate email format
        if (!validateEmail($email)) {
            $errors[] = 'Please enter a valid email address.';
        } else {
            $db = new Database();
            $conn = $db->getConnection();
            
            // Get admin user by email
            $query = "SELECT * FROM admin_users WHERE email = ? AND is_active = true";
            $stmt = $conn->prepare($query);
            $stmt->execute([$email]);
            $admin = $stmt->fetch();
            
            if ($admin && verifyPassword($password, $admin['password_hash'])) {
                // Login successful
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['admin_name'] = $admin['full_name'];
                $_SESSION['admin_role'] = 'super_admin';
                
                // Log admin login activity
                logActivity($admin['id'], 'admin_login', 'Admin logged in');
                
                // Redirect to admin dashboard
                header('Location: index.php');
                exit();
            } else {
                $errors[] = 'Invalid email or password.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - BingeTV</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
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
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
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
            
            <?php if (!empty($errors)): ?>
                <div class="error-message">
                    <?php foreach ($errors as $error): ?>
                        <div><?php echo htmlspecialchars($error); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo htmlspecialchars($email ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i> Login to Admin
                </button>
            </form>
            
            <div class="back-to-site">
                <a href="../index.php">
                    <i class="fas fa-arrow-left"></i> Back to Main Site
                </a>
            </div>
        </div>
    </div>
</body>
</html>
