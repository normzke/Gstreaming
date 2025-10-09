<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../lib/functions.php';
require_once '../lib/seo.php';

// Note: This page can be accessed directly, but we encourage users to start from the homepage

$db = new Database();
$conn = $db->getConnection();

// Get packages
$packagesQuery = "SELECT * FROM packages WHERE is_active = true ORDER BY sort_order, price ASC";
$packagesStmt = $conn->prepare($packagesQuery);
$packagesStmt->execute();
$packages = $packagesStmt->fetchAll();

// Handle package selection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $packageId = (int)($_POST['package_id'] ?? 0);
    
    if ($packageId) {
        // Store selected package in session
        session_start();
        $_SESSION['selected_package'] = $packageId;
        
        // Check if user is logged in
        if (isLoggedIn()) {
            // Redirect to subscription page
            header('Location: subscriptions/subscribe.php');
        } else {
            // Redirect to registration with package info
            header('Location: register.php?package=' . $packageId);
        }
        exit();
    }
}

// Get SEO data
$seo_meta = SEO::getMetaTags('packages');
$og_tags = SEO::getOpenGraphTags('packages');
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
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?php echo SEO::getCanonicalUrl('packages'); ?>">
    
    <!-- Open Graph Tags -->
    <meta property="og:title" content="<?php echo htmlspecialchars($og_tags['og:title']); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($og_tags['og:description']); ?>">
    <meta property="og:url" content="<?php echo htmlspecialchars($og_tags['og:url']); ?>">
    <meta property="og:type" content="<?php echo htmlspecialchars($og_tags['og:type']); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($og_tags['og:image']); ?>">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/components.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .packages-page {
            background: var(--background-color);
            min-height: 100vh;
            padding: 40px 0;
        }
        
        .packages-header {
            text-align: center;
            margin-bottom: 50px;
        }
        
        .packages-header h1 {
            color: var(--primary-color);
            margin-bottom: 20px;
            font-family: 'Orbitron', sans-serif;
        }
        
        .packages-header p {
            color: var(--text-color-secondary);
            font-size: 1.2rem;
        }
        
        .packages-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .package-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            text-align: center;
        }
        
        .package-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .package-card.popular {
            border: 3px solid var(--primary-color);
            transform: scale(1.05);
        }
        
        .package-card.popular::before {
            content: 'Most Popular';
            position: absolute;
            top: -15px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--primary-color);
            color: white;
            padding: 5px 20px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .package-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-color-light);
            margin-bottom: 10px;
        }
        
        .package-price {
            font-size: 2.5rem;
            font-weight: 900;
            color: var(--primary-color);
            margin-bottom: 5px;
        }
        
        .package-period {
            color: var(--text-color-secondary);
            font-size: 1rem;
            margin-bottom: 20px;
        }
        
        .package-description {
            color: var(--text-color-secondary);
            margin-bottom: 25px;
            line-height: 1.6;
        }
        
        .package-features {
            margin-bottom: 25px;
            text-align: left;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            color: var(--text-color-light);
        }
        
        .feature-item i {
            color: var(--primary-color);
            margin-right: 10px;
            width: 20px;
        }
        
        .select-package-btn {
            width: 100%;
            padding: 15px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        
        .select-package-btn:hover {
            background: var(--primary-dark);
        }
        
        .login-prompt {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .login-prompt a {
            color: var(--primary-color);
            font-weight: 600;
            text-decoration: none;
        }
        
        .login-prompt a:hover {
            text-decoration: underline;
        }
        
        .homepage-cta {
            background: #e7f3ff;
            border: 2px solid #b3d9ff;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }
        
        .homepage-cta .btn {
            margin-bottom: 10px;
        }
        
        .homepage-cta p {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
        }
        
        @media (max-width: 768px) {
            .packages-grid {
                grid-template-columns: 1fr;
            }
            
            .package-card.popular {
                transform: none;
            }
        }
    </style>
</head>
<body class="packages-page">
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <i class="fas fa-satellite-dish"></i>
                <span class="logo-text">BingeTV</span>
            </div>
            
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="index.php" class="nav-link">Home</a>
                </li>
                <li class="nav-item">
                    <a href="channels.php" class="nav-link">Channels</a>
                </li>
                <li class="nav-item">
                    <a href="gallery.php" class="nav-link">Gallery</a>
                </li>
                <li class="nav-item">
                    <a href="login.php" class="nav-link">Login</a>
                </li>
                <li class="nav-item">
                    <a href="register.php" class="nav-link btn btn-primary">Sign Up</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="packages-header">
            <h1>Choose Your Perfect Plan</h1>
            <p>Select the package that best fits your streaming needs</p>
            
            <!-- Back to Homepage CTA -->
            <div class="homepage-cta">
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-home"></i>
                    Back to Homepage
                </a>
                <p>Start your journey with our comprehensive overview</p>
            </div>
        </div>

        <!-- Login Prompt -->
        <div class="login-prompt">
            <i class="fas fa-info-circle"></i>
            Already have an account? <a href="login.php">Login here</a> to manage your subscription.
        </div>

        <!-- Packages -->
        <div class="packages-grid">
            <?php foreach ($packages as $package): ?>
                <div class="package-card <?php echo $package['sort_order'] == 2 ? 'popular' : ''; ?>">
                    <h3 class="package-name"><?php echo htmlspecialchars($package['name']); ?></h3>
                    <div class="package-price">KES <?php echo number_format($package['price'], 0); ?></div>
                    <div class="package-period">per month</div>
                    
                    <div class="package-description">
                        <?php echo htmlspecialchars($package['description']); ?>
                    </div>
                    
                    <div class="package-features">
                        <div class="feature-item">
                            <i class="fas fa-mobile-alt"></i>
                            <span><?php echo $package['max_devices']; ?> device(s)</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-calendar"></i>
                            <span><?php echo $package['duration_days']; ?> days access</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-hd-video"></i>
                            <span>HD Quality</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-headset"></i>
                            <span>24/7 Support</span>
                        </div>
                    </div>
                    
                    <form method="POST">
                        <input type="hidden" name="package_id" value="<?php echo $package['id']; ?>">
                        <button type="submit" class="select-package-btn">
                            Select This Plan
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

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
                        <li><a href="index.php">Home</a></li>
                        <li><a href="channels.php">Channels</a></li>
                        <li><a href="gallery.php">Gallery</a></li>
                        <li><a href="package-selection.php">Packages</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Account</h4>
                    <ul class="footer-links">
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Register</a></li>
                        <li><a href="package-selection.php">Subscribe</a></li>
                        <li><a href="support.php">Support</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2024 BingeTV. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="../js/main.js"></script>
</body>
</html>
