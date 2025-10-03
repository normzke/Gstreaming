<?php
require_once 'config/config.php';
require_once 'lib/seo.php';

// Get SEO data
$seo_meta = SEO::getMetaTags('terms');
$canonical_url = SEO::getCanonicalUrl('terms');
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
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="https://bingetv.co.ke/css/main.css">
    <link rel="stylesheet" href="https://bingetv.co.ke/css/components.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .policy-page {
            padding: 80px 0;
            background: var(--background-color);
            min-height: 100vh;
        }
        
        .policy-content {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .policy-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .policy-header h1 {
            color: var(--primary-color);
            margin-bottom: 20px;
            font-family: 'Orbitron', sans-serif;
        }
        
        .policy-section {
            margin-bottom: 30px;
        }
        
        .policy-section h2 {
            color: var(--text-color-light);
            margin-bottom: 15px;
            font-size: 1.3rem;
        }
        
        .policy-section p {
            color: var(--text-color-secondary);
            line-height: 1.6;
            margin-bottom: 15px;
        }
        
        .policy-section ul {
            color: var(--text-color-secondary);
            line-height: 1.6;
            margin-left: 20px;
        }
        
        .back-link {
            text-align: center;
            margin-top: 40px;
        }
        
        .back-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }
        
        .back-link a:hover {
            text-decoration: underline;
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
                    <a href="https://bingetv.co.ke/register.php" class="nav-link btn-register">Get Started</a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Terms of Service Content -->
    <main class="policy-page">
        <div class="container">
            <div class="policy-content">
                <div class="policy-header">
                    <h1>Terms of Service</h1>
                    <p>Last updated: <?php echo date('F j, Y'); ?></p>
                </div>
                
                <div class="policy-section">
                    <h2>Acceptance of Terms</h2>
                    <p>By accessing and using BingeTV's streaming service, you accept and agree to be bound by the terms and provision of this agreement. If you do not agree to abide by the above, please do not use this service.</p>
                </div>
                
                <div class="policy-section">
                    <h2>Service Description</h2>
                    <p>BingeTV provides a streaming service that allows users to access live TV channels and on-demand content. The service is available through various devices including Smart TVs, mobile devices, and streaming devices.</p>
                </div>
                
                <div class="policy-section">
                    <h2>User Accounts</h2>
                    <p>To access our service, you must create an account. You are responsible for:</p>
                    <ul>
                        <li>Maintaining the confidentiality of your account credentials</li>
                        <li>All activities that occur under your account</li>
                        <li>Notifying us immediately of any unauthorized use</li>
                        <li>Providing accurate and complete information</li>
                    </ul>
                </div>
                
                <div class="policy-section">
                    <h2>Payment and Billing</h2>
                    <p>Subscription fees are charged in advance and are non-refundable except as required by law. We accept M-PESA payments and other approved payment methods. You are responsible for all applicable taxes.</p>
                </div>
                
                <div class="policy-section">
                    <h2>Acceptable Use</h2>
                    <p>You agree not to:</p>
                    <ul>
                        <li>Share your account credentials with others</li>
                        <li>Use the service for any illegal or unauthorized purpose</li>
                        <li>Attempt to gain unauthorized access to our systems</li>
                        <li>Interfere with or disrupt the service</li>
                        <li>Reverse engineer or attempt to extract source code</li>
                    </ul>
                </div>
                
                <div class="policy-section">
                    <h2>Content and Intellectual Property</h2>
                    <p>All content available through our service is protected by copyright and other intellectual property laws. You may not copy, distribute, or create derivative works without permission.</p>
                </div>
                
                <div class="policy-section">
                    <h2>Service Availability</h2>
                    <p>We strive to provide continuous service but cannot guarantee uninterrupted access. We may temporarily suspend service for maintenance, updates, or other reasons.</p>
                </div>
                
                <div class="policy-section">
                    <h2>Termination</h2>
                    <p>We may terminate or suspend your account at any time for violation of these terms. You may cancel your subscription at any time through your account dashboard.</p>
                </div>
                
                <div class="policy-section">
                    <h2>Limitation of Liability</h2>
                    <p>To the maximum extent permitted by law, BingeTV shall not be liable for any indirect, incidental, special, or consequential damages arising from your use of the service.</p>
                </div>
                
                <div class="policy-section">
                    <h2>Changes to Terms</h2>
                    <p>We reserve the right to modify these terms at any time. We will notify users of significant changes via email or through our service.</p>
                </div>
                
                <div class="policy-section">
                    <h2>Contact Information</h2>
                    <p>If you have any questions about these Terms of Service, please contact us at:</p>
                    <p>Email: legal@bingetv.co.ke<br>
                    Phone: +254 768 704 834<br>
                    Address: Nairobi, Kenya</p>
                </div>
                
                <div class="back-link">
                    <a href="/"><i class="fas fa-arrow-left"></i> Back to Home</a>
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
</body>
</html>
