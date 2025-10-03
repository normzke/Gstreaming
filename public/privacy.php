<?php
require_once 'config/config.php';
require_once 'lib/seo.php';

// Get SEO data
$seo_meta = SEO::getMetaTags('privacy');
$canonical_url = SEO::getCanonicalUrl('privacy');
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

    <!-- Privacy Policy Content -->
    <main class="policy-page">
        <div class="container">
            <div class="policy-content">
                <div class="policy-header">
                    <h1>Privacy Policy</h1>
                    <p>Last updated: <?php echo date('F j, Y'); ?></p>
                </div>
                
                <div class="policy-section">
                    <h2>Information We Collect</h2>
                    <p>We collect information you provide directly to us, such as when you create an account, subscribe to our service, or contact us for support. This may include:</p>
                    <ul>
                        <li>Name and contact information</li>
                        <li>Email address and phone number</li>
                        <li>Payment information (processed securely through M-PESA)</li>
                        <li>Device information and usage data</li>
                    </ul>
                </div>
                
                <div class="policy-section">
                    <h2>How We Use Your Information</h2>
                    <p>We use the information we collect to:</p>
                    <ul>
                        <li>Provide and maintain our streaming service</li>
                        <li>Process payments and manage subscriptions</li>
                        <li>Send you important updates about our service</li>
                        <li>Provide customer support</li>
                        <li>Improve our service and develop new features</li>
                    </ul>
                </div>
                
                <div class="policy-section">
                    <h2>Information Sharing</h2>
                    <p>We do not sell, trade, or otherwise transfer your personal information to third parties without your consent, except as described in this policy. We may share your information in the following circumstances:</p>
                    <ul>
                        <li>With service providers who assist us in operating our service</li>
                        <li>When required by law or to protect our rights</li>
                        <li>In connection with a business transfer or acquisition</li>
                    </ul>
                </div>
                
                <div class="policy-section">
                    <h2>Data Security</h2>
                    <p>We implement appropriate security measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction. However, no method of transmission over the internet is 100% secure.</p>
                </div>
                
                <div class="policy-section">
                    <h2>Your Rights</h2>
                    <p>You have the right to:</p>
                    <ul>
                        <li>Access and update your personal information</li>
                        <li>Request deletion of your account and data</li>
                        <li>Opt out of marketing communications</li>
                        <li>Request a copy of your data</li>
                    </ul>
                </div>
                
                <div class="policy-section">
                    <h2>Contact Us</h2>
                    <p>If you have any questions about this Privacy Policy, please contact us at:</p>
                    <p>Email: privacy@bingetv.co.ke<br>
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
