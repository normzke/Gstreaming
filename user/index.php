<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../lib/functions.php';
require_once '../lib/seo.php';

// Get packages for display
$db = new Database();
$conn = $db->getConnection();

$packageQuery = "SELECT * FROM packages WHERE is_active = true ORDER BY sort_order, price ASC";
$packageStmt = $conn->prepare($packageQuery);
$packageStmt->execute();
$packages = $packageStmt->fetchAll();

// Get featured gallery items
$galleryQuery = "SELECT * FROM gallery_items WHERE is_featured = true ORDER BY sort_order LIMIT 6";
$galleryStmt = $conn->prepare($galleryQuery);
$galleryStmt->execute();
$featuredGallery = $galleryStmt->fetchAll();

// Get SEO data
$seo_meta = SEO::getMetaTags('home');
$og_tags = SEO::getOpenGraphTags('home');
$structured_data = SEO::getStructuredData('home');
$canonical_url = SEO::getCanonicalUrl('home');
$breadcrumb_data = SEO::getBreadcrumbData([
    ['name' => 'Home', 'url' => 'https://bingetv.co.ke/']
]);
$faq_data = SEO::getFAQData();
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
    <meta name="author" content="<?php echo htmlspecialchars($seo_meta['author']); ?>">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?php echo $canonical_url; ?>">
    
    <!-- Open Graph Tags -->
    <meta property="og:title" content="<?php echo htmlspecialchars($og_tags['og:title']); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($og_tags['og:description']); ?>">
    <meta property="og:url" content="<?php echo htmlspecialchars($og_tags['og:url']); ?>">
    <meta property="og:type" content="<?php echo htmlspecialchars($og_tags['og:type']); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($og_tags['og:image']); ?>">
    <meta property="og:site_name" content="<?php echo htmlspecialchars($og_tags['og:site_name']); ?>">
    <meta property="og:locale" content="<?php echo htmlspecialchars($og_tags['og:locale']); ?>">
    
    <!-- Twitter Card Tags -->
    <meta name="twitter:card" content="<?php echo htmlspecialchars($og_tags['twitter:card']); ?>">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($og_tags['twitter:title']); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($og_tags['twitter:description']); ?>">
    <meta name="twitter:image" content="<?php echo htmlspecialchars($og_tags['twitter:image']); ?>">
    <meta name="twitter:site" content="<?php echo htmlspecialchars($og_tags['twitter:site']); ?>">
    
    <!-- Additional SEO Meta Tags -->
    <meta name="geo.region" content="KE">
    <meta name="geo.placename" content="Kenya">
    <meta name="geo.position" content="-1.2921;36.8219">
    <meta name="ICBM" content="-1.2921, 36.8219">
    <meta name="language" content="en">
    <meta name="revisit-after" content="1 days">
    <meta name="rating" content="general">
    <meta name="distribution" content="global">
    <meta name="target" content="all">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="images/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="images/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="images/favicon-16x16.png">
    <link rel="manifest" href="images/site.webmanifest">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/components.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                    <a href="index.php" class="nav-link active">Home</a>
                </li>
                <li class="nav-item">
                    <a href="channels.php" class="nav-link">Channels</a>
                </li>
                <li class="nav-item">
                    <a href="gallery.php" class="nav-link">Gallery</a>
                </li>
                <li class="nav-item">
                    <a href="dashboard/" class="nav-link">Dashboard</a>
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

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-video-container">
            <iframe src="https://www.youtube.com/embed/9ZfN87gSjvI?autoplay=1&mute=1&loop=1&playlist=9ZfN87gSjvI&controls=0&showinfo=0&rel=0&iv_load_policy=3&modestbranding=1" 
                    frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
        </div>
        
        <div class="hero-content">
            <div class="container">
                <h1 class="hero-title">Never Miss Premier League & Premium Sports</h1>
                <p class="hero-description">
                    Watch 150+ channels including Premier League, National Geographic, ESPN, and more in stunning 4K quality. 
                    Pay securely with M-PESA and enjoy unlimited streaming on any device.
                </p>
                
                <div class="hero-stats">
                    <div class="stat-item">
                        <div class="stat-number" data-target="150">0</div>
                        <div class="stat-label">Channels</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number" data-target="20">0</div>
                        <div class="stat-label">Live Sports</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number" data-target="4">0</div>
                        <div class="stat-label">4K Quality</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number" data-target="99">0</div>
                        <div class="stat-label">% Uptime</div>
                    </div>
                </div>
                
                <div class="hero-actions">
                    <a href="/user/subscriptions/subscribe.php" class="btn btn-primary btn-large">Choose Your Plan</a>
                    <a href="/user/channels.php" class="btn btn-secondary btn-large">Browse Channels</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Packages Section -->
    <section id="packages" class="packages-section">
        <div class="container">
            <div class="section-header">
                <h2>Choose Your Perfect Plan</h2>
                <p>Flexible packages designed for every sports fan</p>
            </div>
            
            <div class="packages-grid">
                <?php foreach ($packages as $package): ?>
                    <div class="package-card">
                        <div class="package-header">
                            <h3><?php echo htmlspecialchars($package['name']); ?></h3>
                            <div class="package-price">
                                <span class="currency">KES</span>
                                <span class="amount"><?php echo number_format($package['price'], 0); ?></span>
                                <span class="period">/month</span>
                            </div>
                        </div>
                        
                        <div class="package-description">
                            <?php echo htmlspecialchars($package['description']); ?>
                        </div>
                        
                        <div class="package-features">
                            <div class="feature">
                                <i class="fas fa-mobile-alt"></i>
                                <span><?php echo $package['max_devices']; ?> device(s)</span>
                            </div>
                            <div class="feature">
                                <i class="fas fa-calendar"></i>
                                <span><?php echo $package['duration_days']; ?> days</span>
                            </div>
                            <div class="feature">
                                <i class="fas fa-hd-video"></i>
                                <span>HD Quality</span>
                            </div>
                        </div>
                        
                        <a href="package-selection.php" class="btn btn-primary btn-block">
                            Choose Plan
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Gallery Section -->
    <section id="gallery" class="gallery-section">
        <div class="container">
            <div class="section-header">
                <h2>Featured Content</h2>
                <p>Experience the best in sports and entertainment</p>
            </div>
            
            <div class="gallery-grid">
                <?php foreach ($featuredGallery as $item): ?>
                    <div class="gallery-item">
                        <?php if (isset($item['type']) && $item['type'] === 'video'): ?>
                            <div class="video-container">
                                <iframe src="<?php echo htmlspecialchars($item['video_url']); ?>" 
                                        frameborder="0" allowfullscreen></iframe>
                                <div class="video-quality-badge">HD</div>
                                <div class="video-duration">5:30</div>
                            </div>
                        <?php else: ?>
                            <div class="image-container">
                                <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['title']); ?>">
                            </div>
                        <?php endif; ?>
                        
                        <div class="gallery-content">
                            <h4><?php echo htmlspecialchars($item['title']); ?></h4>
                            <p><?php echo htmlspecialchars($item['description']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <div class="footer-logo">
                        <i class="fas fa-satellite-dish"></i>
                        <span>BingeTV</span>
                    </div>
                    <p>Premium TV streaming service for Kenya. Stream thousands of channels on any device.</p>
                    
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="#packages">Packages</a></li>
                        <li><a href="#devices">Supported Devices</a></li>
                        <li><a href="gallery.php">Gallery</a></li>
                        <li><a href="support.php">Support</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Account</h4>
                    <ul class="footer-links">
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Register</a></li>
                        <li><a href="dashboard/">Dashboard</a></li>
                        <li><a href="payments/payment.php">Billing</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Contact Info</h4>
                    <div class="contact-info">
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <span>support@bingetv.co.ke</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <span>+254 768 704 834</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Nairobi, Kenya</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2024 BingeTV. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- WhatsApp Button -->
    <div class="whatsapp-float">
        <a href="https://wa.me/254768704834?text=Hello%2C%20I%20need%20help%20with%20BingeTV" target="_blank" class="whatsapp-btn">
            <i class="fab fa-whatsapp"></i>
            <span class="whatsapp-text">Chat with us</span>
        </a>
    </div>

    <!-- JavaScript -->
    <script src="js/main.js"></script>
    <script src="js/animations.js"></script>
    <script src="js/enhanced.js"></script>
    
    <!-- Structured Data -->
    <script type="application/ld+json">
    <?php echo $structured_data; ?>
    </script>
    
    <script type="application/ld+json">
    <?php echo $breadcrumb_data; ?>
    </script>
    
    <script type="application/ld+json">
    <?php echo $faq_data; ?>
    </script>
</body>
</html>
