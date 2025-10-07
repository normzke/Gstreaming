<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../lib/functions.php';
require_once '../lib/seo.php';

// Get packages for display (guard against DB connection failure)
$db = new Database();
$conn = $db->getConnection();
$packages = [];
if ($conn) {
    $packageQuery = "SELECT * FROM packages WHERE is_active = true ORDER BY sort_order, price ASC";
    $packageStmt = $conn->prepare($packageQuery);
    $packageStmt->execute();
    $packages = $packageStmt->fetchAll();
}

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
    <base href="https://bingetv.co.ke/">
    
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
    <link rel="stylesheet" href="https://bingetv.co.ke/css/main.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://bingetv.co.ke/css/components.css">
    
    <!-- Inline CSS for visibility fixes -->
    <style>
        .title-main, .title-sub, .hero-description {
            color: white !important;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5) !important;
        }
        
        .stat-number, .stat-label {
            color: white !important;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5) !important;
        }
        
        .feature-icon i, .device-icon i, .support-icon i, .stat-icon i {
            color: white !important;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5) !important;
        }
        
        .nav-logo i {
            color: white !important;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5) !important;
        }
        
        [class*="gradient-primary"] i, [class*="primary"] i {
            color: white !important;
        }
    </style>
    
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
                    <a href="#home" class="nav-link">Home</a>
                </li>
                <li class="nav-item">
                    <a href="https://bingetv.co.ke/channels.php" class="nav-link">Channels</a>
                </li>
                <li class="nav-item">
                    <a href="#packages" class="nav-link">Packages</a>
                </li>
                <li class="nav-item">
                    <a href="#devices" class="nav-link">Devices</a>
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
            
            <div class="hamburger">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="hero-background">
            <div class="hero-overlay"></div>
            <!-- YouTube Background Video -->
            <div class="hero-video-container">
                <iframe 
                    src="https://www.youtube.com/embed/9ZfN87gSjvI?autoplay=1&mute=1&loop=1&playlist=9ZfN87gSjvI&controls=0&showinfo=0&rel=0&iv_load_policy=3&modestbranding=1&fs=0&cc_load_policy=0&start=0&end=30" 
                    frameborder="0" 
                    allow="autoplay; encrypted-media" 
                    allowfullscreen
                    class="hero-video">
                </iframe>
            </div>
        </div>
        
        <div class="hero-content">
            <div class="container">
                <div class="hero-text">
                    <h1 class="hero-title">
                        <span class="title-main">Never Miss</span>
                        <span class="title-highlight">Premier League</span>
                        <span class="title-sub">& Premium Sports</span>
                    </h1>
                    
                    <p class="hero-description">
                        Watch live Premier League matches, National Geographic documentaries, and premium sports content. 
                        Stream in crystal clear HD on any device with M-PESA payment.
                    </p>
                    
                    <div class="hero-stats">
                        <div class="stat-item" data-aos="fadeInUp" data-aos-delay="100">
                            <div class="stat-number counter" data-target="150">0</div>
                            <div class="stat-label">Channels</div>
                        </div>
                        <div class="stat-item" data-aos="fadeInUp" data-aos-delay="200">
                            <div class="stat-number counter" data-target="20">0</div>
                            <div class="stat-label">Live Sports</div>
                        </div>
                        <div class="stat-item" data-aos="fadeInUp" data-aos-delay="300">
                            <div class="stat-number counter" data-target="4">0</div>
                            <div class="stat-label">K Quality</div>
                        </div>
                        <div class="stat-item" data-aos="fadeInUp" data-aos-delay="400">
                            <div class="stat-number counter" data-target="99">0</div>
                            <div class="stat-label">% Uptime</div>
                        </div>
                    </div>
                    
                    <div class="hero-buttons">
                        <a href="package-selection.php?from_homepage=1" class="btn btn-primary btn-large">
                            <i class="fas fa-play"></i>
                            Start Streaming Now
                        </a>
                        <a href="#gallery" class="btn btn-secondary btn-large">
                            <i class="fas fa-video"></i>
                            Watch Preview
                        </a>
                    </div>
                </div>
                
                <div class="hero-visual">
                    <div class="device-showcase">
                        <div class="device tv">
                            <i class="fas fa-tv"></i>
                            <span>Smart TV</span>
                        </div>
                        <div class="device firestick">
                            <i class="fab fa-amazon"></i>
                            <span>Firestick</span>
                        </div>
                        <div class="device roku">
                            <i class="fas fa-stream"></i>
                            <span>Roku</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="scroll-indicator">
            <div class="scroll-arrow">
                <i class="fas fa-chevron-down"></i>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Why Choose BingeTV?</h2>
                <p class="section-subtitle">Experience the best in TV streaming with our premium features</p>
            </div>
            
            <div class="features-grid">
                <div class="feature-card" data-aos="fadeInUp" data-aos-delay="100">
                    <div class="feature-icon">
                        <i class="fas fa-satellite-dish"></i>
                    </div>
                    <h3>1000+ Channels</h3>
                    <p>Access thousands of international and local channels including news, sports, movies, and entertainment from around the world.</p>
                    <ul class="feature-list">
                        <li><i class="fas fa-check"></i> International News Channels</li>
                        <li><i class="fas fa-check"></i> Premium Sports Coverage</li>
                        <li><i class="fas fa-check"></i> Latest Movies & Series</li>
                        <li><i class="fas fa-check"></i> Kids & Family Content</li>
                    </ul>
                </div>
                
                <div class="feature-card" data-aos="fadeInUp" data-aos-delay="200">
                    <div class="feature-icon">
                        <i class="fas fa-tv"></i>
                    </div>
                    <h3>Multi-Device Support</h3>
                    <p>Stream seamlessly across all your devices - Smart TVs, Firestick, Roku, mobile phones, and tablets.</p>
                    <ul class="feature-list">
                        <li><i class="fas fa-check"></i> Smart TV Compatible</li>
                        <li><i class="fas fa-check"></i> Firestick & Roku Ready</li>
                        <li><i class="fas fa-check"></i> Mobile & Tablet Apps</li>
                        <li><i class="fas fa-check"></i> Cross-Platform Sync</li>
                    </ul>
                </div>
                
                <div class="feature-card" data-aos="fadeInUp" data-aos-delay="300">
                    <div class="feature-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h3>M-PESA Integration</h3>
                    <p>Pay easily and securely using M-PESA Till and Paybill numbers. Instant activation after payment confirmation.</p>
                    <ul class="feature-list">
                        <li><i class="fas fa-check"></i> Till Number Payment</li>
                        <li><i class="fas fa-check"></i> Paybill Integration</li>
                        <li><i class="fas fa-check"></i> Instant Activation</li>
                        <li><i class="fas fa-check"></i> Secure Transactions</li>
                    </ul>
                </div>
                
                <div class="feature-card" data-aos="fadeInUp" data-aos-delay="400">
                    <div class="feature-icon">
                        <i class="fas fa-hd-video"></i>
                    </div>
                    <h3>Premium Quality</h3>
                    <p>Enjoy crystal clear HD and 4K streaming with minimal buffering and optimized for Kenyan internet speeds.</p>
                    <ul class="feature-list">
                        <li><i class="fas fa-check"></i> HD & 4K Quality</li>
                        <li><i class="fas fa-check"></i> Optimized Streaming</li>
                        <li><i class="fas fa-check"></i> Low Buffering</li>
                        <li><i class="fas fa-check"></i> Adaptive Bitrate</li>
                    </ul>
                </div>
                
                <div class="feature-card" data-aos="fadeInUp" data-aos-delay="500">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3>24/7 Support</h3>
                    <p>Get help whenever you need it with our dedicated support team available round the clock via multiple channels.</p>
                    <ul class="feature-list">
                        <li><i class="fas fa-check"></i> Live Chat Support</li>
                        <li><i class="fas fa-check"></i> WhatsApp Support</li>
                        <li><i class="fas fa-check"></i> Phone Support</li>
                        <li><i class="fas fa-check"></i> Email Support</li>
                    </ul>
                </div>
                
                <div class="feature-card" data-aos="fadeInUp" data-aos-delay="600">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Secure & Reliable</h3>
                    <p>Your privacy and security are our top priorities with encrypted connections and reliable streaming infrastructure.</p>
                    <ul class="feature-list">
                        <li><i class="fas fa-check"></i> Encrypted Connections</li>
                        <li><i class="fas fa-check"></i> Privacy Protection</li>
                        <li><i class="fas fa-check"></i> Reliable Infrastructure</li>
                        <li><i class="fas fa-check"></i> Data Security</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="stats-section">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-card" data-aos="fadeInUp" data-aos-delay="100">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number counter" data-target="25000">0</div>
                        <div class="stat-label">Happy Customers</div>
                    </div>
                </div>
                
                <div class="stat-card" data-aos="fadeInUp" data-aos-delay="200">
                    <div class="stat-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number counter" data-target="4.9">0</div>
                        <div class="stat-label">Customer Rating</div>
                    </div>
                </div>
                
                <div class="stat-card" data-aos="fadeInUp" data-aos-delay="300">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number counter" data-target="99.9">0</div>
                        <div class="stat-label">Uptime %</div>
                    </div>
                </div>
                
                <div class="stat-card" data-aos="fadeInUp" data-aos-delay="400">
                    <div class="stat-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number counter" data-target="24">0</div>
                        <div class="stat-label">Support Hours</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Packages Section -->
    <section id="packages" class="packages-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Choose Your Perfect Package</h2>
                <p class="section-subtitle">Flexible plans designed for every Kenyan household</p>
            </div>
            
            <!-- Device Selection Tabs -->
            <div class="pricing-tabs">
                <div class="device-tabs">
                    <button class="device-tab active" data-devices="1">1 Device</button>
                    <button class="device-tab" data-devices="3">3 Devices</button>
                    <button class="device-tab" data-devices="5">5 Devices</button>
                    <button class="device-tab" data-devices="10">10 Devices</button>
                </div>
                
                <!-- Duration Selection for Each Device Count -->
                <div class="duration-tabs-container">
                    <div class="duration-tabs" data-devices="1">
                        <button class="duration-tab active" data-duration="1">1 Month</button>
                        <button class="duration-tab" data-duration="3">3 Months</button>
                        <button class="duration-tab" data-duration="6">6 Months</button>
                        <button class="duration-tab" data-duration="12">12 Months</button>
                    </div>
                    
                    <div class="duration-tabs" data-devices="3" style="display: none;">
                        <button class="duration-tab active" data-duration="1">1 Month</button>
                        <button class="duration-tab" data-duration="3">3 Months</button>
                        <button class="duration-tab" data-duration="6">6 Months</button>
                        <button class="duration-tab" data-duration="12">12 Months</button>
                    </div>
                    
                    <div class="duration-tabs" data-devices="5" style="display: none;">
                        <button class="duration-tab active" data-duration="1">1 Month</button>
                        <button class="duration-tab" data-duration="3">3 Months</button>
                        <button class="duration-tab" data-duration="6">6 Months</button>
                        <button class="duration-tab" data-duration="12">12 Months</button>
                    </div>
                    
                    <div class="duration-tabs" data-devices="10" style="display: none;">
                        <button class="duration-tab active" data-duration="1">1 Month</button>
                        <button class="duration-tab" data-duration="3">3 Months</button>
                        <button class="duration-tab" data-duration="6">6 Months</button>
                        <button class="duration-tab" data-duration="12">12 Months</button>
                    </div>
                </div>
            </div>
            
            <!-- Pricing Cards -->
            <div class="packages-grid">
                <?php 
                $index = 0;
                foreach ($packages as $pkg): 
                    $index++;
                    $months = max(1, (int)round(($pkg['duration_days'] ?? 30) / 30));
                    $devices = (int)($pkg['max_devices'] ?? 1);
                    $badge = $index === 1 ? 'Most Popular' : '';
                ?>
                <div class="package-card" data-devices="<?php echo $devices; ?>" data-duration="<?php echo $months; ?>">
                    <?php if ($badge): ?>
                    <div class="package-badge"><?php echo $badge; ?></div>
                    <?php endif; ?>
                    <div class="package-header">
                        <h3 class="package-name"><?php echo htmlspecialchars($pkg['name']); ?></h3>
                        <div class="package-price">
                            <span class="currency"><?php echo htmlspecialchars($pkg['currency'] ?: 'KSh'); ?></span>
                            <span class="amount" data-price="<?php echo (float)$pkg['price']; ?>"><?php echo number_format((float)$pkg['price'], 0); ?></span>
                            <span class="period">/<?php echo $months > 1 ? $months . ' mo' : 'month'; ?></span>
                        </div>
                    </div>
                    
                    <div class="package-features">
                        <div class="feature-item">
                            <i class="fas fa-check"></i>
                            <span>All Channels Included</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check"></i>
                            <span><?php echo $devices; ?> Device<?php echo $devices > 1 ? 's' : ''; ?></span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check"></i>
                            <span>M-PESA Payment</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check"></i>
                            <span>24/7 Support</span>
                        </div>
                    </div>
                    
                    <div class="package-footer">
                        <a href="packages.php?from_homepage=1&package_id=<?php echo (int)$pkg['id']; ?>" class="btn btn-primary btn-full">
                            <i class="fas fa-credit-card"></i>
                            Subscribe Now
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="packages-note">
                <div class="note-content">
                    <i class="fas fa-shield-alt"></i>
                    <p>All packages include M-PESA payment integration, 24/7 support, and instant activation</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Devices Section -->
    <section id="devices" class="devices-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Stream on Any Device</h2>
                <p class="section-subtitle">Download our gateway app and enjoy streaming everywhere</p>
            </div>
            
            <div class="devices-grid">
                <div class="device-card">
                    <div class="device-icon">
                        <i class="fas fa-tv"></i>
                    </div>
                    <h3>Smart TV</h3>
                    <p>Works on all Smart TV brands - Samsung, LG, Sony, TCL and more</p>
                    <div class="device-features">
                        <span class="feature-tag">4K Support</span>
                        <span class="feature-tag">HD Ready</span>
                    </div>
                </div>
                
                <div class="device-card">
                    <div class="device-icon">
                        <i class="fab fa-amazon"></i>
                    </div>
                    <h3>Amazon Firestick</h3>
                    <p>Transform your regular TV into a smart streaming device</p>
                    <div class="device-features">
                        <span class="feature-tag">Easy Setup</span>
                        <span class="feature-tag">Voice Remote</span>
                    </div>
                </div>
                
                <div class="device-card">
                    <div class="device-icon">
                        <i class="fas fa-stream"></i>
                    </div>
                    <h3>Roku</h3>
                    <p>Simple, reliable streaming with thousands of channels</p>
                    <div class="device-features">
                        <span class="feature-tag">User Friendly</span>
                        <span class="feature-tag">Affordable</span>
                    </div>
                </div>
                
                <div class="device-card">
                    <div class="device-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h3>Mobile & Tablet</h3>
                    <p>Stream on your Android and iOS devices</p>
                    <div class="device-features">
                        <span class="feature-tag">Cross Platform</span>
                        <span class="feature-tag">Offline Viewing</span>
                    </div>
                </div>
            </div>
            
            <div class="gateway-app">
                <div class="gateway-content">
                    <h3>Download Gateway App</h3>
                    <p>Our lightweight gateway app provides secure access to all your streaming channels</p>
                    
                    <div class="download-buttons">
                        <a href="gateway/download.php?platform=android" class="download-btn android">
                            <i class="fab fa-android"></i>
                            <div class="btn-text">
                                <span class="btn-label">Download for</span>
                                <span class="btn-platform">Android</span>
                            </div>
                        </a>
                        
                        <a href="gateway/download.php?platform=ios" class="download-btn ios">
                            <i class="fab fa-apple"></i>
                            <div class="btn-text">
                                <span class="btn-label">Download for</span>
                                <span class="btn-platform">iOS</span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">What Our Customers Say</h2>
                <p class="section-subtitle">Real reviews from satisfied BingeTV subscribers across Kenya</p>
            </div>
            
            <div class="testimonials-carousel">
                <div class="testimonial-track">
                    <div class="testimonial-slide active" data-slide="1">
                        <div class="testimonial-content">
                            <div class="testimonial-image">
                                <img src="https://ui-avatars.com/api/?name=John+Mwangi&background=8B0000&color=FFFFFF&size=150" alt="John Mwangi">
                            </div>
                            <div class="testimonial-text">
                                <div class="stars">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                                <blockquote>
                                    "BingeTV has completely transformed our family's entertainment experience. The picture quality is amazing and we can watch on all our devices. M-PESA payment makes it so convenient!"
                                </blockquote>
                                <div class="testimonial-author">
                                    <h4>John Mwangi</h4>
                                    <p>Nairobi, Kenya</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="testimonial-slide" data-slide="2">
                        <div class="testimonial-content">
                            <div class="testimonial-image">
                                <img src="https://ui-avatars.com/api/?name=Sarah+Wanjiku&background=8B0000&color=FFFFFF&size=150" alt="Sarah Wanjiku">
                            </div>
                            <div class="testimonial-text">
                                <div class="stars">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                                <blockquote>
                                    "I love the variety of channels available. From international news to local content, everything is there. The customer support is excellent and always available when I need help."
                                </blockquote>
                                <div class="testimonial-author">
                                    <h4>Sarah Wanjiku</h4>
                                    <p>Mombasa, Kenya</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="testimonial-slide" data-slide="3">
                        <div class="testimonial-content">
                            <div class="testimonial-image">
                                <img src="https://ui-avatars.com/api/?name=Peter+Otieno&background=8B0000&color=FFFFFF&size=150" alt="Peter Otieno">
                            </div>
                            <div class="testimonial-text">
                                <div class="stars">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                                <blockquote>
                                    "The setup was so easy! I just downloaded the app on my Firestick and was streaming within minutes. The sports channels are fantastic - never miss a game now."
                                </blockquote>
                                <div class="testimonial-author">
                                    <h4>Peter Otieno</h4>
                                    <p>Kisumu, Kenya</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="testimonial-slide" data-slide="4">
                        <div class="testimonial-content">
                            <div class="testimonial-image">
                                <img src="https://ui-avatars.com/api/?name=Grace+Akinyi&background=8B0000&color=FFFFFF&size=150" alt="Grace Akinyi">
                            </div>
                            <div class="testimonial-text">
                                <div class="stars">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                                <blockquote>
                                    "As a busy working mom, I appreciate how reliable the service is. My kids can watch their favorite shows without any interruptions. The family plan is perfect for us!"
                                </blockquote>
                                <div class="testimonial-author">
                                    <h4>Grace Akinyi</h4>
                                    <p>Nakuru, Kenya</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial-nav">
                    <button class="nav-btn prev-btn" onclick="changeSlide(-1)">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button class="nav-btn next-btn" onclick="changeSlide(1)">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
                
                <div class="testimonial-dots">
                    <span class="dot active" onclick="currentSlide(1)"></span>
                    <span class="dot" onclick="currentSlide(2)"></span>
                    <span class="dot" onclick="currentSlide(3)"></span>
                    <span class="dot" onclick="currentSlide(4)"></span>
                </div>
            </div>
        </div>
    </section>

    <!-- Gallery Section -->
    <section id="gallery" class="gallery-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Featured Content</h2>
                <p class="section-subtitle">Preview some of the amazing channels and content available</p>
            </div>
            
            <div class="gallery-grid">
                <?php if (!empty($featuredGallery)): ?>
                    <?php foreach ($featuredGallery as $item): ?>
                    <div class="gallery-item">
                        <?php if (isset($item['type']) && $item['type'] === 'video'): ?>
                            <div class="video-container">
                                <iframe src="<?php echo htmlspecialchars($item['video_url']); ?>" 
                                        frameborder="0" 
                                        allowfullscreen></iframe>
                            </div>
                        <?php else: ?>
                            <div class="image-container">
                                <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['title']); ?>">
                                <div class="image-overlay">
                                    <i class="fas fa-play"></i>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="gallery-content">
                            <h4><?php echo htmlspecialchars($item['title']); ?></h4>
                            <p><?php echo htmlspecialchars($item['description']); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="gallery-placeholder">
                        <i class="fas fa-video"></i>
                        <h3>Gallery Coming Soon</h3>
                        <p>We're preparing amazing content previews for you!</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="gallery-cta">
                <a href="https://bingetv.co.ke/gallery.php" class="btn btn-primary">
                    <i class="fas fa-images"></i>
                    View Full Gallery
                </a>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Frequently Asked Questions</h2>
                <p class="section-subtitle">Get answers to common questions about BingeTV</p>
            </div>
            
            <div class="faq-container">
                <div class="faq-item" data-aos="fadeInUp" data-aos-delay="100">
                    <div class="faq-question">
                        <h3>How do I get started with BingeTV?</h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Getting started is easy! Simply choose a package that suits your needs, register for an account, and pay using M-PESA. Once payment is confirmed, you'll receive your login credentials and can start streaming immediately on any compatible device.</p>
                    </div>
                </div>
                
                <div class="faq-item" data-aos="fadeInUp" data-aos-delay="200">
                    <div class="faq-question">
                        <h3>Which devices are supported?</h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>BingeTV works on Smart TVs (Samsung, LG, Sony, TCL), Amazon Firestick, Roku, Android and iOS devices, and computers. Simply download our gateway app from the appropriate app store or our website.</p>
                    </div>
                </div>
                
                <div class="faq-item" data-aos="fadeInUp" data-aos-delay="300">
                    <div class="faq-question">
                        <h3>How does M-PESA payment work?</h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>You can pay using either our Till number or Paybill number. After selecting your package, you'll receive the payment details via SMS. Once payment is confirmed, your account will be activated automatically within minutes.</p>
                    </div>
                </div>
                
                <div class="faq-item" data-aos="fadeInUp" data-aos-delay="400">
                    <div class="faq-question">
                        <h3>Can I watch on multiple devices simultaneously?</h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Yes! Depending on your package, you can stream on 1, 3, 5, or up to 10 devices simultaneously. Each device counts as one connection, so you can share your subscription with family members.</p>
                    </div>
                </div>
                
                <div class="faq-item" data-aos="fadeInUp" data-aos-delay="500">
                    <div class="faq-question">
                        <h3>What internet speed do I need?</h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>For SD quality, we recommend at least 2 Mbps. For HD streaming, 5 Mbps is ideal. For 4K content, you'll need 15+ Mbps. Our adaptive streaming automatically adjusts quality based on your connection.</p>
                    </div>
                </div>
                
                <div class="faq-item" data-aos="fadeInUp" data-aos-delay="600">
                    <div class="faq-question">
                        <h3>Is there customer support available?</h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Absolutely! We provide 24/7 customer support through WhatsApp, live chat, phone, and email. Our support team is always ready to help with any technical issues or questions you may have.</p>
                    </div>
                </div>
                
                <div class="faq-item" data-aos="fadeInUp" data-aos-delay="700">
                    <div class="faq-question">
                        <h3>Can I cancel my subscription anytime?</h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Yes, you can cancel your subscription at any time. Your service will continue until the end of your current billing period. You can manage your subscription and billing through your user dashboard.</p>
                    </div>
                </div>
                
                <div class="faq-item" data-aos="fadeInUp" data-aos-delay="800">
                    <div class="faq-question">
                        <h3>Do you offer refunds?</h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>We offer a 7-day money-back guarantee for new subscribers. If you're not satisfied with our service within the first week, contact our support team for a full refund.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Support Section -->
    <section id="support" class="support-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">24/7 Support</h2>
                <p class="section-subtitle">We're here to help you with any questions or issues</p>
            </div>
            
            <div class="support-grid">
                <div class="support-card">
                    <div class="support-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3>Live Chat</h3>
                    <p>Get instant help from our support team</p>
                    <a href="support.php?type=chat" class="support-btn">Start Chat</a>
                </div>
                
                <div class="support-card">
                    <div class="support-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <h3>Email Support</h3>
                    <p>Send us a detailed message and we'll respond quickly</p>
                    <a href="support.php?type=email" class="support-btn">Send Email</a>
                </div>
                
                <div class="support-card">
                    <div class="support-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <h3>Phone Support</h3>
                    <p>Call us for urgent technical assistance</p>
                    <a href="tel:+254700000000" class="support-btn">Call Now</a>
                </div>
                
                <div class="support-card">
                    <div class="support-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <h3>Help Center</h3>
                    <p>Browse our comprehensive knowledge base</p>
                    <a href="https://bingetv.co.ke/help.php" class="support-btn">Browse FAQ</a>
                </div>
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
                
                <div class="footer-section">
                    <h4>Contact Info</h4>
                    <div class="contact-info">
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <span>support@BingeTV.com</span>
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
                <div class="footer-bottom-content">
                    <p>&copy; <?php echo date('Y'); ?> BingeTV. All rights reserved.</p>
                    <div class="footer-bottom-links">
                        <a href="https://bingetv.co.ke/privacy.php">Privacy Policy</a>
                        <a href="https://bingetv.co.ke/terms.php">Terms of Service</a>
                        <a href="refund.php">Refund Policy</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Floating WhatsApp Button -->
    <div class="whatsapp-float">
        <a href="https://wa.me/254768704834?text=Hello%2C%20I%20need%20help%20with%20BingeTV" target="_blank" class="whatsapp-btn">
            <i class="fab fa-whatsapp"></i>
            <span class="whatsapp-text">Chat with us</span>
        </a>
    </div>

    <!-- JavaScript -->
    <script src="https://bingetv.co.ke/js/main.js"></script>
    <script src="https://bingetv.co.ke/js/animations.js"></script>
    <script src="https://bingetv.co.ke/js/enhanced.js"></script>
    
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
