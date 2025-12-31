<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/functions.php';
require_once __DIR__ . '/../lib/seo.php';

// Get SEO data
$seo_meta = SEO::getMetaTags('refund');
$og_tags = SEO::getOpenGraphTags('refund');
$canonical_url = SEO::getCanonicalUrl('refund');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="">

    <!-- SEO Meta Tags -->
    <title><?php echo htmlspecialchars($seo_meta['title']); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($seo_meta['description']); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($seo_meta['keywords']); ?>">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?php echo $canonical_url; ?>">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/components.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .refund-page {
            min-height: 100vh;
            background: linear-gradient(135deg, #8B0000 0%, #660000 100%);
            padding: 120px 20px 40px;
        }

        .refund-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .refund-card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }

        .refund-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .refund-header h1 {
            color: #8B0000;
            font-family: 'Orbitron', sans-serif;
            font-weight: 900;
            margin-bottom: 10px;
        }

        .refund-header p {
            color: #666;
            margin: 0;
        }

        .refund-section {
            margin-bottom: 30px;
        }

        .refund-section h2 {
            color: #8B0000;
            font-size: 1.5rem;
            margin-bottom: 15px;
            border-bottom: 2px solid #8B0000;
            padding-bottom: 10px;
        }

        .refund-section p {
            line-height: 1.7;
            margin-bottom: 15px;
        }

        .refund-section ul {
            margin: 15px 0;
            padding-left: 20px;
        }

        .refund-section li {
            margin-bottom: 8px;
            line-height: 1.6;
        }

        .contact-info {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
        }

        .contact-info h3 {
            color: #8B0000;
            margin-bottom: 15px;
        }

        .contact-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .contact-item i {
            width: 20px;
            margin-right: 10px;
            color: #8B0000;
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
                <li class="nav-item"><a href="/" class="nav-link">Home</a></li>
                <li class="nav-item"><a href="channels" class="nav-link">Channels</a></li>
                <li class="nav-item"><a href="gallery" class="nav-link">Gallery</a></li>
                <li class="nav-item"><a href="support" class="nav-link">Support</a></li>
                <li class="nav-item"><a href="login" class="nav-link btn-login">Login</a></li>
                <li class="nav-item"><a href="register" class="nav-link btn-register">Get Started</a></li>
            </ul>

            <div class="hamburger">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>
        </div>
    </nav>

    <main class="refund-page">
        <div class="refund-container">
            <div class="refund-card">
                <div class="refund-header">
                    <h1>Refund Policy</h1>
                    <p>Your satisfaction is our priority</p>
                </div>

                <div class="refund-section">
                    <h2>7-Day Money-Back Guarantee</h2>
                    <p>We offer a 7-day money-back guarantee for all new subscribers. If you're not completely satisfied with BingeTV within the first 7 days of your subscription, we'll provide a full refund.</p>

                    <h3>Eligibility Requirements:</h3>
                    <ul>
                        <li>Refund requests must be made within 7 days of the initial subscription</li>
                        <li>You must not have violated our Terms of Service</li>
                        <li>Refund requests should be submitted through our support channels</li>
                        <li>Only the first subscription payment is eligible for refund</li>
                    </ul>
                </div>

                <div class="refund-section">
                    <h2>How to Request a Refund</h2>
                    <p>To request a refund, please contact our support team through any of the following methods:</p>

                    <div class="contact-info">
                        <h3>Contact Information</h3>
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <span>support@BingeTV.com</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <span>+254 768 704 834</span>
                        </div>
                        <div class="contact-item">
                            <i class="fab fa-whatsapp"></i>
                            <span>WhatsApp: +254 768 704 834</span>
                        </div>
                    </div>

                    <p>Please include the following information in your refund request:</p>
                    <ul>
                        <li>Your full name</li>
                        <li>Email address used for registration</li>
                        <li>Date of subscription</li>
                        <li>Reason for requesting refund</li>
                        <li>M-PESA transaction ID (if applicable)</li>
                    </ul>
                </div>

                <div class="refund-section">
                    <h2>Processing Time</h2>
                    <p>Refund requests are typically processed within 3-5 business days. Once approved, refunds will be processed through the same payment method used for the original transaction.</p>
                    <p>For M-PESA payments, refunds will be sent back to the phone number used for payment.</p>
                </div>

                <div class="refund-section">
                    <h2>Exceptions</h2>
                    <p>Please note that the following situations are not eligible for refunds:</p>
                    <ul>
                        <li>Subscriptions cancelled after the 7-day guarantee period</li>
                        <li>Violation of our Terms of Service or Acceptable Use Policy</li>
                        <li>Disputes related to content availability or quality issues (please contact support first)</li>
                        <li>Failed payments or chargebacks initiated by the user</li>
                    </ul>
                </div>

                <div class="refund-section">
                    <h2>Alternative Solutions</h2>
                    <p>Before requesting a refund, our support team may offer alternative solutions such as:</p>
                    <ul>
                        <li>Technical assistance with streaming issues</li>
                        <li>Account adjustments or credits</li>
                        <li>Package changes or upgrades</li>
                        <li>Extended trial periods</li>
                    </ul>
                </div>

                <div class="refund-section">
                    <p><strong>This policy is effective as of <?php echo date('F Y'); ?> and may be updated periodically. Please check this page for the most current information.</strong></p>
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
                        <li><a href="channels">Channels</a></li>
                        <li><a href="gallery">Gallery</a></li>
                        <li><a href="support">Support</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h4>Account</h4>
                    <ul class="footer-links">
                        <li><a href="login">Login</a></li>
                        <li><a href="register">Register</a></li>
                        <li><a href="packages">Packages</a></li>
                        <li><a href="support">Support</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> BingeTV. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="js/main.js"></script>
</body>
</html>
