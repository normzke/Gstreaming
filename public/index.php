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

// Get featured gallery items (only if DB is available)
$featuredGallery = [];
if ($conn) {
    $galleryQuery = "SELECT * FROM gallery_items WHERE is_featured = true ORDER BY sort_order LIMIT 6";
    $galleryStmt = $conn->prepare($galleryQuery);
    $galleryStmt->execute();
    $featuredGallery = $galleryStmt->fetchAll();
}

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
    <link
        href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="css/main.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css/components.css">

    <!-- Inline CSS for visibility fixes -->
    <style>
        .title-main,
        .title-sub,
        .hero-description {
            color: white !important;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5) !important;
        }

        .stat-number,
        .stat-label {
            color: white !important;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5) !important;
        }

        .feature-icon i,
        .device-icon i,
        .support-icon i,
        .stat-icon i {
            color: white !important;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5) !important;
        }

        .nav-logo i {
            color: white !important;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5) !important;
        }

        [class*="gradient-primary"] i,
        [class*="primary"] i {
            color: white !important;
        }

        /* Mobile menu display fix */
        @media (max-width: 768px) {
            .nav-menu {
                display: none;
            }

            .nav-menu.active {
                display: flex !important;
            }

            .hamburger {
                display: flex;
            }
        }
    </style>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Auto-Detect OS and Download Prompt -->
    <script>
        // Detect TV/Device Platform
        function detectPlatform() {
            const ua = navigator.userAgent.toLowerCase();
            const platform = {
                isAndroidTV: (ua.indexOf('android') !== -1 && (ua.indexOf('tv') !== -1 || ua.indexOf('smart-tv') !== -1)),
                isWebOS: (ua.indexOf('webos') !== -1 || ua.indexOf('lg') !== -1),
                isTizen: (ua.indexOf('tizen') !== -1 || (ua.indexOf('samsung') !== -1 && ua.indexOf('smart-tv') !== -1)),
                isTV: (ua.indexOf('tv') !== -1 || ua.indexOf('smart-tv') !== -1 || ua.indexOf('smarttv') !== -1)
            };

            if (platform.isAndroidTV) return 'android';
            if (platform.isWebOS) return 'webos';
            if (platform.isTizen) return 'tizen';
            if (platform.isTV) return 'tv';
            return 'unknown';
        }

        // Show download prompt if TV detected
        function showDownloadPrompt() {
            const platform = detectPlatform();
            if (platform === 'unknown' || platform === 'tv') return;

            // Check if user already dismissed
            if (localStorage.getItem('bingetv_download_dismissed') === platform) return;

            const platformNames = {
                'android': 'Android TV',
                'webos': 'LG Smart TV (WebOS)',
                'tizen': 'Samsung Smart TV (Tizen)'
            };

            const downloadUrls = {
                'android': '/apps/android/bingetv-android-tv.apk',
                'webos': '/apps/webos/com.bingetv.app_1.0.0_all.ipk',
                'tizen': '/apps/tizen/com.bingetv.app-1.0.0.tpk'
            };

            // Create modal
            const modal = document.createElement('div');
            modal.id = 'download-prompt-modal';
            modal.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.8);
                z-index: 10000;
                display: flex;
                align-items: center;
                justify-content: center;
                animation: fadeIn 0.3s;
            `;

            modal.innerHTML = `
                <div style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
                    border-radius: 20px;
                    padding: 40px;
                    max-width: 500px;
                    width: 90%;
                    box-shadow: 0 20px 60px rgba(0,168,255,0.3);
                    border: 2px solid #00A8FF;
                    text-align: center;
                    animation: slideUp 0.3s;">
                    <div style="font-size: 60px; margin-bottom: 20px;">📺</div>
                    <h2 style="color: #00A8FF; margin-bottom: 15px; font-size: 28px;">Get BingeTV App</h2>
                    <p style="color: #fff; margin-bottom: 30px; font-size: 16px;">
                        We detected you're using ${platformNames[platform]}. Download our app for the best experience!
                    </p>
                    <div style="display: flex; gap: 15px; justify-content: center;">
                        <a href="${downloadUrls[platform]}" 
                           style="background: #00A8FF; color: white; padding: 15px 30px; 
                                  border-radius: 8px; text-decoration: none; font-weight: bold;
                                  transition: all 0.3s;" 
                           onmouseover="this.style.background='#0099E6'; this.style.transform='scale(1.05)'"
                           onmouseout="this.style.background='#00A8FF'; this.style.transform='scale(1)'">
                            Download Now
                        </a>
                        <button onclick="dismissDownloadPrompt('${platform}')" 
                                style="background: transparent; color: #fff; padding: 15px 30px; 
                                       border: 2px solid #666; border-radius: 8px; cursor: pointer;
                                       font-weight: bold;">
                            Maybe Later
                        </button>
                    </div>
                </div>
            `;

            document.body.appendChild(modal);

            // Add animations
            const style = document.createElement('style');
            style.textContent = `
                @keyframes fadeIn {
                    from { opacity: 0; }
                    to { opacity: 1; }
                }
                @keyframes slideUp {
                    from { transform: translateY(50px); opacity: 0; }
                    to { transform: translateY(0); opacity: 1; }
                }
            `;
            document.head.appendChild(style);
        }

        function dismissDownloadPrompt(platform) {
            localStorage.setItem('bingetv_download_dismissed', platform);
            document.getElementById('download-prompt-modal').remove();
        }

        // Show prompt after page load
        window.addEventListener('load', function () {
            console.log('Page loaded, checking platform...');
            const platform = detectPlatform();
            console.log('Detected platform:', platform);
            setTimeout(showDownloadPrompt, 500); // Show after 500ms
        });
    </script>
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
                    <a href="/channels" class="nav-link">Channels</a>
                </li>
                <li class="nav-item">
                    <a href="#packages" class="nav-link">Packages</a>
                </li>
                <li class="nav-item">
                    <a href="#devices" class="nav-link">Devices</a>
                </li>
                <li class="nav-item">
                    <a href="/gallery" class="nav-link">Gallery</a>
                </li>
                <li class="nav-item">
                    <a href="/support" class="nav-link">Support</a>
                </li>
                <li class="nav-item">
                    <a href="/apps" class="nav-link">Apps</a>
                </li>
                <li class="nav-item">
                    <a href="/login" class="nav-link btn-login">Login</a>
                </li>
                <li class="nav-item">
                    <a href="/register" class="nav-link btn-register">Get Started</a>
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
                    frameborder="0" allow="autoplay; encrypted-media" allowfullscreen class="hero-video">
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
                        Watch live Premier League matches, National Geographic documentaries, and premium sports
                        content.
                        Stream in crystal clear HD on any device with M-PESA payment.
                    </p>

                    <div class="hero-stats">
                        <div class="stat-item" data-aos="fadeInUp" data-aos-delay="100">
                            <div class="stat-number counter" data-target="16000">0</div>
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
                    <h3>16,000+ Channels</h3>
                    <p>Access thousands of international and local channels including news, sports, movies, and
                        entertainment from around the world.</p>
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
                    <p>Stream seamlessly across all your devices - Smart TVs, Firestick, Roku, mobile phones, and
                        tablets.</p>
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
                    <p>Pay easily and securely using M-PESA Till and Paybill numbers. Instant activation after payment
                        confirmation.</p>
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
                    <p>Enjoy crystal clear HD and 4K streaming with minimal buffering and optimized for Kenyan internet
                        speeds.</p>
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
                    <p>Get help whenever you need it with our dedicated support team available round the clock via
                        multiple channels.</p>
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
                    <p>Your privacy and security are our top priorities with encrypted connections and reliable
                        streaming infrastructure.</p>
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

    <!-- Channel Groups Section -->
    <section id="channel-groups" class="features-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Channel Groups</h2>
                <p class="section-subtitle">Curated categories spanning 16,000+ channels worldwide</p>
            </div>
            <div class="user-card" style="padding:20px;">
                <details>
                    <summary style="cursor:pointer;font-weight:600;">View all channel groups</summary>
                    <div style="margin-top:16px;columns:3;column-gap:24px;">
                        <ul style="list-style:none;padding:0;margin:0;">
                            <li>USA Entertainment</li>
                            <li>USA Movies Channels</li>
                            <li>USA Religion</li>
                            <li>USA Documentary</li>
                            <li>USA Local - CBS</li>
                            <li>USA Latin</li>
                            <li>USA News</li>
                            <li>USA Family & Kids</li>
                            <li>24/7 Shows</li>
                            <li>24/7 Reality</li>
                            <li>24/7 Comedy</li>
                            <li>24/7 Documentary</li>
                            <li>24/7 Drama</li>
                            <li>24/7 Sci-fi</li>
                            <li>24/7 Mysteries</li>
                            <li>24/7 Action & Adventure</li>
                            <li>USA MLB</li>
                            <li>USA NFL - Sunday Ticket</li>
                            <li>USA Latin UNIVISION</li>
                            <li>USA Sports</li>
                            <li>USA Music</li>
                            <li>USA Local - MISC</li>
                            <li>Sport Match Center</li>
                            <li>USA Latin TELEMUNDO</li>
                            <li>USA Bein Sports</li>
                            <li>USA NBC Sports</li>
                            <li>USA FanDuel Sports</li>
                            <li>24/7 Family</li>
                            <li>24/7 War & Politics</li>
                            <li>24/7 Cartoon</li>
                            <li>24/7 Talk</li>
                            <li>24/7 Classic Show</li>
                            <li>24/7 News</li>
                            <li>USA Latin GALAVISION</li>
                            <li>USA Local - FOX</li>
                            <li>USA Local - ABC</li>
                            <li>USA Local - NBC</li>
                            <li>Sport Rugby</li>
                            <li>Sport Cycling</li>
                            <li>USA Local Channels ( Full List )</li>
                            <li>24/7 Fantasy</li>
                            <li>24/7 Adventure</li>
                            <li>24/7 Science Fiction</li>
                            <li>24/7 Action</li>
                            <li>Sport Cricket</li>
                            <li>Premium Shows</li>
                            <li>24/7 Kids & Family</li>
                            <li>24/7 Movie Categories</li>
                            <li>24/7 Actors and Actresses</li>
                            <li>USA Peacock Network</li>
                            <li>Radio</li>
                            <li>USA STIRR TV</li>
                            <li>24/7 Tv Series</li>
                            <li>24/7 Western</li>
                            <li>USA PPV Cinema</li>
                            <li>24/7 Cooking</li>
                            <li>24/7 Horror</li>
                            <li>24/7 Music</li>
                            <li>24/7 Crime</li>
                            <li>PPV-MMA/BOXING/WWE/UFC</li>
                            <li>USA NHL</li>
                            <li>USA NBA</li>
                            <li>Sport Golf</li>
                            <li>USA Latin UNIMAS</li>
                            <li>USA WNBA</li>
                            <li>24/7 Action & Crime</li>
                            <li>24/7 Anime</li>
                            <li>24/7 Christmas</li>
                            <li>24/7 Classic Tv Series</li>
                            <li>24/7 Comedy & Drama</li>
                            <li>24/7 Documentary Series</li>
                            <li>24/7 Kids</li>
                            <li>Kids</li>
                            <li>24/7 Movie Series</li>
                            <li>24/7 Netflix</li>
                            <li>24/7 Reality Shows</li>
                            <li>24/7 Soap</li>
                            <li>24/7 Sports Replay</li>
                            <li>24/7 SYFY</li>
                            <li>24/7 Teen Cartoons</li>
                            <li>24/7 Toddler</li>
                            <li>Pay Per View Events</li>
                            <li>USA NCAAF</li>
                            <li>Hockey Special Events</li>
                            <li>CHRISTMAS</li>
                            <li>ESPN Events 200 VIP channels</li>
                            <li>Sport NCAA Men Basketball</li>
                            <li>Sport Volley Ball</li>
                            <li>Sport Handball</li>
                            <li>Sport NCAA Women Basketball</li>
                            <li>Sport Softball</li>
                            <li>Sport NJCAA Men Basketball</li>
                            <li>Sport NJCAA Women Basketball</li>
                            <li>Sport NBA G League</li>
                            <li>Sport Tennis</li>
                            <li>24/7 Streaming</li>
                            <li>Sports Motorsports</li>
                            <li>USA MILB</li>
                            <li>Sport Lacrosse</li>
                            <li>PPV FLOSPORTS</li>
                            <li>Sport College Baseball</li>
                            <li>Soccer Special Events</li>
                            <li>USA Big Brother</li>
                            <li>CA: Canada FR</li>
                            <li>CA: Canada General</li>
                            <li>CA: Canada Entertainment</li>
                            <li>CA: Canada News</li>
                            <li>CA: Canada Sports</li>
                            <li>CA: Canada Kids</li>
                            <li>CA: Canada Local</li>
                            <li>CA: Canada Cinema</li>
                            <li>CA: Canada Super Sports</li>
                            <li>Sports DAZN</li>
                            <li>UK: Movies</li>
                            <li>UK: Music</li>
                            <li>UK: Entertainment</li>
                            <li>UK: Asia</li>
                            <li>UK: News</li>
                            <li>UK: Kids</li>
                            <li>UK: Documentary</li>
                            <li>UK: Sport</li>
                            <li>IRE: Ireland General</li>
                            <li>UK: Religion</li>
                            <li>UK: EPL Games</li>
                            <li>AR: Arab OSN VIP (Orbit Showtime Network)</li>
                            <li>Kurdistan</li>
                            <li>AR: Tunisia</li>
                            <li>AR: Yeman</li>
                            <li>AR: Christian</li>
                            <li>AR: United Arab Emirates UAE</li>
                            <li>AR: Arab BeIN sports VIP</li>
                            <li>AR: Arab BeIN VIP</li>
                            <li>AR: Arab News</li>
                            <li>AR: Arab Rotana</li>
                            <li>AR: Arab MBC</li>
                            <li>AR: Arab TARAB</li>
                            <li>AR: Arab Kids</li>
                            <li>AR: Arab Food Channels</li>
                            <li>AR: Algeria</li>
                            <li>AR: Bahrain</li>
                            <li>AR: Egypt</li>
                            <li>AR: Iraq</li>
                            <li>AR: Islamic Channels</li>
                            <li>AR: Jordan</li>
                            <li>AR: Kuwait</li>
                            <li>AR: Libya</li>
                            <li>AR: Morocco</li>
                            <li>AR: Palestine</li>
                            <li>AR: Sudan</li>
                            <li>AR: Mauritania</li>
                            <li>AR: Saudi Arabia KSA</li>
                            <li>AR: Syria</li>
                            <li>AR: Lebanon</li>
                            <li>AR: Oman</li>
                            <li>Syriacs Channels</li>
                            <li>AR: Qatar</li>
                            <li>AR: Arabic Sports</li>
                            <li>AR: Bein 4K</li>
                            <li>Paraguay</li>
                            <li>Peru</li>
                            <li>Panama</li>
                            <li>Bolivia</li>
                            <li>Colombia</li>
                            <li>Ecuador</li>
                            <li>El Salvador</li>
                            <li>Honduras</li>
                            <li>Chile</li>
                            <li>BR: Brazil Entertainment</li>
                            <li>BR: Brazil General</li>
                            <li>BR: Brazil Kids</li>
                            <li>BR: Brazil Sports</li>
                            <li>ARG: Argentina Entertainment</li>
                            <li>ARG: Argentina General</li>
                            <li>ARG: Argentina Kids</li>
                            <li>ARG: Argentina News</li>
                            <li>ARG: Argentina Music</li>
                            <li>ARG: Argentina Movies</li>
                            <li>ARG: Argentina Sports</li>
                            <li>ARG: Argentina Documentary</li>
                            <li>MX: Mexico Entertainment</li>
                            <li>MX: Mexico General</li>
                            <li>MX: Mexico News</li>
                            <li>MX: Mexico Sports</li>
                            <li>MX: Mexico Kids</li>
                            <li>Latino Sports</li>
                            <li>Latino All</li>
                            <li>Costa Rica</li>
                            <li>Guatemala</li>
                            <li>Cuba</li>
                            <li>Uruguay</li>
                            <li>Puerto Rico</li>
                            <li>Nicaragua</li>
                            <li>Venezuela</li>
                            <li>Dominican</li>
                            <li>Suriname</li>
                            <li>PT: Portugal News</li>
                            <li>ES: Spain</li>
                            <li>ES: Cine Tv</li>
                            <li>ES: Entretenimiento</li>
                            <li>ES: Musicales</li>
                            <li>ES: Infantil</li>
                            <li>ES: Deportes de España</li>
                            <li>ES: Spain Locales</li>
                            <li>FR: France General</li>
                            <li>FR: France Sports</li>
                            <li>FR: France Music</li>
                            <li>FR: France Cinema</li>
                            <li>FR: France Decouvertes</li>
                            <li>FR: France Info</li>
                            <li>FR: France Divertissement</li>
                            <li>FR: France Enfants</li>
                            <li>FR: Amazon Prime</li>
                            <li>Belgium</li>
                            <li>DE: Germany General</li>
                            <li>DE: Germany Kids</li>
                            <li>DE: Germany News</li>
                            <li>DE: Germany Cinema</li>
                            <li>DE: Germany Entertainment</li>
                            <li>DE: Germany Sport</li>
                            <li>DE: Germany Music</li>
                            <li>PT: Portugal General</li>
                            <li>PT: Portugal Kids</li>
                            <li>PT: Portugal Sport</li>
                            <li>PT: Portugal entertainment</li>
                            <li>NL: Netherland Sport</li>
                            <li>NL: Netherland General</li>
                            <li>NL: Netherland Entertainment</li>
                            <li>NL: Netherland Kids</li>
                            <li>NL: Netherland Music</li>
                            <li>Austria</li>
                            <li>Australia</li>
                            <li>IT: Italy General</li>
                            <li>IT: Sky Sports</li>
                            <li>IT: Bambini</li>
                            <li>IT: Canale Italia</li>
                            <li>IT: Intrattenimento</li>
                            <li>IT: Mediaset Premium</li>
                            <li>IT: Sky Primafila</li>
                            <li>IT: Musica</li>
                            <li>IT: Sky Cinema</li>
                            <li>IT: Cultura</li>
                            <li>IT: Eagle Cinema VIP</li>
                            <li>Israel</li>
                            <li>Philippines</li>
                            <li>China</li>
                            <li>Thailand</li>
                            <li>Vietnam</li>
                            <li>Indonesia</li>
                            <li>Malaysia</li>
                            <li>Korea</li>
                            <li>Afghanistan</li>
                            <li>Kazakhistan</li>
                            <li>Iran</li>
                            <li>CAR: Caribbean Entertainment</li>
                            <li>CAR: Caribbean Cinema</li>
                            <li>CAR: Caribbean Sport</li>
                            <li>CAR: Caribbean News</li>
                            <li>CAR: Caribbean General</li>
                            <li>CAR: Caribbean Kids</li>
                            <li>CAR: Caribbean Haiti</li>
                            <li>Malta</li>
                            <li>CH: Switzerland Sport</li>
                            <li>CH: Switzerland General</li>
                            <li>CH: Switzerland Cinema</li>
                            <li>CH: Switzerland Entertainment</li>
                            <li>CH: Switzerland Kids</li>
                            <li>DK: Denmark General</li>
                            <li>DK: Denmark Entertainment</li>
                            <li>DK: Denmark Kids</li>
                            <li>DK: Denmark Sport</li>
                            <li>Finland</li>
                            <li>SE: Sweden General</li>
                            <li>SE: Sweden Sport</li>
                            <li>SE: Sweden Entertainment</li>
                            <li>SE: Sweden Cinema</li>
                            <li>SE: Sweden Kids</li>
                            <li>Norway</li>
                            <li>HU: Hungary General</li>
                            <li>HU: Hungary Entertainment</li>
                            <li>HU: Hungary Cinema</li>
                            <li>HU: Hungary Kids</li>
                            <li>HU: Hungary Sport</li>
                            <li>Ukraine</li>
                            <li>PL: Poland Movies</li>
                            <li>PL: Poland Kids</li>
                            <li>PL: Poland General</li>
                            <li>PL: Poland Entertainment</li>
                            <li>PL: Poland Sports</li>
                            <li>PL: Poland News</li>
                            <li>Russia</li>
                            <li>CZ: Czech Entertainment</li>
                            <li>CZ: Czech Sport</li>
                            <li>CZ: Czech Cinema</li>
                            <li>CZ: Czech General</li>
                            <li>CZ: Czech Kids</li>
                            <li>HR: Croatia Entertainment</li>
                            <li>HR: Croatia Sport</li>
                            <li>HR: Croatia Kids</li>
                            <li>HR: Croatia General</li>
                            <li>HR: Croatia Cinema</li>
                            <li>RO: Romania Cinema</li>
                            <li>RO: Romania Kids</li>
                            <li>RO: Romania Entertainment</li>
                            <li>RO: Romania General</li>
                            <li>RO: Romania News</li>
                            <li>RO: Romania Sports</li>
                            <li>BG: Bulgaria Entertainment</li>
                            <li>BG: Bulgaria General</li>
                            <li>BG: Bulgaria Cinema</li>
                            <li>BG: Bulgaria Kids</li>
                            <li>BG: Bulgaria Sport</li>
                            <li>Slovakia</li>
                            <li>AL: Albania General</li>
                            <li>AL: Albania Entertainment</li>
                            <li>AL: Albania Cinema</li>
                            <li>AL: Albania News</li>
                            <li>AL: Albania Music</li>
                            <li>AL: Albania Sport</li>
                            <li>AL: Albania Kids</li>
                            <li>Armenia</li>
                            <li>Azerbaijan</li>
                            <li>BIH: Bosnia General</li>
                            <li>BIH: Bosnia News</li>
                            <li>BIH: Bosnia Entertainment</li>
                            <li>BIH: Bosnia Sport</li>
                            <li>BIH: Bosnia Music</li>
                            <li>BIH: Bosnia Kids</li>
                            <li>Macedonia</li>
                            <li>GR: Greece Cinema</li>
                            <li>GR: Greece General</li>
                            <li>GR: Greece Kids</li>
                            <li>GR: Greece Documentary</li>
                            <li>GR: Greece Music</li>
                            <li>TR: Turkey Sport</li>
                            <li>TR: Turkey General</li>
                            <li>TR: Turkey Entertainment</li>
                            <li>TR: Turkey BeIN</li>
                            <li>TR: Turkey News</li>
                            <li>TR: Turkey Kids</li>
                            <li>EX-YU</li>
                            <li>Slovenia</li>
                            <li>Cyprus</li>
                            <li>New Zealand Sky</li>
                            <li>IN: Indian Entertainment</li>
                            <li>IN: Indian Tamil</li>
                            <li>IN: Indian Music</li>
                            <li>IN: Indian Malayalam</li>
                            <li>IN: Indian Telugu</li>
                            <li>IN: Indian Marathi</li>
                            <li>IN: Indian Punjabi</li>
                            <li>IN: Indian Gujarat</li>
                            <li>IN: Indian South</li>
                            <li>IN: India Urdu</li>
                            <li>IN: India Kannada</li>
                            <li>PK: Pakistan General</li>
                            <li>PK: Pakistan News</li>
                            <li>PK: Pakistan Entertainment</li>
                            <li>PK: Pakistan Cinema</li>
                            <li>PK: Pakistan Sport</li>
                            <li>Bangladesh</li>
                            <li>Africa All</li>
                            <li>Africa DSTV</li>
                            <li>Africa Music</li>
                            <li>AF: Angola</li>
                            <li>AF: Benin</li>
                            <li>AF: Burkina Faso</li>
                            <li>AF: Cameroon</li>
                            <li>AF: Congo</li>
                            <li>AF: Côte d'Ivoire</li>
                            <li>AF: Ghana</li>
                            <li>AF: Ethiopia</li>
                            <li>AF: Guinea</li>
                            <li>AF: Kenya</li>
                            <li>AF: Mali</li>
                            <li>AF: Mozambique</li>
                            <li>AF: Nigeria</li>
                            <li>AF: Rwanda</li>
                            <li>AF: Uganda</li>
                            <li>AF: Tanzania</li>
                            <li>AF: South Africa</li>
                            <li>AF: Senegal</li>
                            <li>AF: Somal</li>
                            <li>Taiwan</li>
                            <li>GR: Greece Sports</li>
                            <li>Cambodia</li>
                            <li>PL: Poland ViaPlay</li>
                            <li>Japan</li>
                            <li>Montenegro</li>
                            <li>Belarus</li>
                            <li>Georgia</li>
                            <li>EX-YU: SkyLink</li>
                            <li>EX-YU: Pink</li>
                            <li>Africa Canal+</li>
                            <li>Mongolia</li>
                            <li>Serbia</li>
                            <li>Carib RUSH Sports</li>
                            <li>Africa Super Sports</li>
                        </ul>
                    </div>
                </details>
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
                    <button class="device-tab active" data-devices="1">
                        <i class="fas fa-mobile-alt"></i> 1 Device
                    </button>
                    <button class="device-tab" data-devices="2">
                        <i class="fas fa-laptop"></i> 2 Devices
                    </button>
                    <button class="device-tab" data-devices="3">
                        <i class="fas fa-tv"></i> 3 Devices
                    </button>
                    <button class="device-tab custom-device" data-devices="custom"
                        style="border: 2px dashed #8B0000; background: rgba(139, 0, 0, 0.05);">
                        <i class="fas fa-users"></i> Custom (4+)
                    </button>
                </div>
            </div>
            <div style="text-align: center; margin-top: 1rem;">
                <p style="color: #666; font-size: 0.9rem;">
                    <i class="fas fa-info-circle"></i> Need more than 3 devices? <a href="support.php"
                        style="color: #8B0000; font-weight: 600;">Contact us</a> for a custom package
                </p>
            </div>

            <!-- Pricing Cards -->
            <div class="packages-grid">
                <?php
                $index = 0;
                foreach ($packages as $pkg):
                    $index++;
                    $months = max(1, (int) round(($pkg['duration_days'] ?? 30) / 30));
                    $devices = (int) ($pkg['max_devices'] ?? 1);
                    $badge = $index === 1 ? 'Most Popular' : '';
                    ?>
                    <div class="package-card" data-base-price="<?php echo (float) $pkg['price']; ?>"
                        data-min-devices="<?php echo $devices; ?>" data-duration="<?php echo $months; ?>">
                        <?php if ($badge): ?>
                            <div class="package-badge"><?php echo $badge; ?></div>
                        <?php endif; ?>
                        <div class="package-header">
                            <h3 class="package-name"><?php echo htmlspecialchars($pkg['name']); ?></h3>
                            <div class="package-price">
                                <span class="currency"><?php echo htmlspecialchars($pkg['currency'] ?: 'KSh'); ?></span>
                                <span class="amount"
                                    data-price="<?php echo (float) $pkg['price']; ?>"><?php echo number_format((float) $pkg['price'], 0); ?></span>
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
                                <span class="devices-count"><?php echo $devices; ?>
                                    Device<?php echo $devices > 1 ? 's' : ''; ?></span>
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
                            <a href="user/subscriptions/subscribe.php?package=<?php echo (int) $pkg['id']; ?>"
                                class="btn btn-primary btn-full subscribe-btn"
                                data-package-id="<?php echo (int) $pkg['id']; ?>">
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
                    <h3>Download BingeTV Apps</h3>
                    <p>Our native streaming apps provide secure access to all 16,000+ channels on your Smart TV</p>

                    <div class="download-buttons"
                        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-top: 2rem;">
                        <!-- Google Play -->
                        <a href="https://play.google.com/store/apps/details?id=ibpro.smart.player" target="_blank"
                            class="download-btn android"
                            style="display: flex; align-items: center; gap: 0.75rem; padding: 1rem 1.5rem; background: linear-gradient(135deg, #3DDC84, #2BAF66); color: white; border-radius: 10px; text-decoration: none; transition: all 0.3s;">
                            <i class="fab fa-google-play" style="font-size: 2rem;"></i>
                            <div class="btn-text">
                                <span class="btn-label" style="display: block; font-size: 0.7rem; opacity: 0.9;">GET IT
                                    ON</span>
                                <span class="btn-platform"
                                    style="display: block; font-size: 1rem; font-weight: 700;">Google Play</span>
                            </div>
                        </a>

                        <!-- Apple App Store -->
                        <a href="https://apps.apple.com/app/ibo-pro-player/id6449647925" target="_blank"
                            class="download-btn ios"
                            style="display: flex; align-items: center; gap: 0.75rem; padding: 1rem 1.5rem; background: linear-gradient(135deg, #000000, #333333); color: white; border-radius: 10px; text-decoration: none; transition: all 0.3s;">
                            <i class="fab fa-apple" style="font-size: 2rem;"></i>
                            <div class="btn-text">
                                <span class="btn-label"
                                    style="display: block; font-size: 0.7rem; opacity: 0.9;">Download on the</span>
                                <span class="btn-platform"
                                    style="display: block; font-size: 1rem; font-weight: 700;">App Store</span>
                            </div>
                        </a>

                        <!-- LG Store -->
                        <a href="https://us.lgappstv.com/main/tvapp/detail?appId=1209143" target="_blank"
                            class="download-btn"
                            style="display: flex; align-items: center; gap: 0.75rem; padding: 1rem 1.5rem; background: linear-gradient(135deg, #A50034, #8B0000); color: white; border-radius: 10px; text-decoration: none; transition: all 0.3s;">
                            <i class="fas fa-tv" style="font-size: 2rem;"></i>
                            <div class="btn-text">
                                <span class="btn-label"
                                    style="display: block; font-size: 0.7rem; opacity: 0.9;">Available on</span>
                                <span class="btn-platform" style="display: block; font-size: 1rem; font-weight: 700;">LG
                                    Store</span>
                            </div>
                        </a>

                        <!-- Roku Store -->
                        <a href="https://channelstore.roku.com/details/8bb2a96953173808e85902295304a2e1:8490a219fcbeecc63b4cc16b6ba9be93/ibo-player-pro"
                            target="_blank" class="download-btn"
                            style="display: flex; align-items: center; gap: 0.75rem; padding: 1rem 1.5rem; background: linear-gradient(135deg, #662D91, #4E1A6B); color: white; border-radius: 10px; text-decoration: none; transition: all 0.3s;">
                            <i class="fas fa-stream" style="font-size: 2rem;"></i>
                            <div class="btn-text">
                                <span class="btn-label" style="display: block; font-size: 0.7rem; opacity: 0.9;">Add
                                    from</span>
                                <span class="btn-platform"
                                    style="display: block; font-size: 1rem; font-weight: 700;">Roku Store</span>
                            </div>
                        </a>

                        <!-- Microsoft Store -->
                        <a href="https://www.microsoft.com/store/apps/9MSNK97XPVRK" target="_blank" class="download-btn"
                            style="display: flex; align-items: center; gap: 0.75rem; padding: 1rem 1.5rem; background: linear-gradient(135deg, #0078D4, #005A9E); color: white; border-radius: 10px; text-decoration: none; transition: all 0.3s;">
                            <i class="fab fa-windows" style="font-size: 2rem;"></i>
                            <div class="btn-text">
                                <span class="btn-label"
                                    style="display: block; font-size: 0.7rem; opacity: 0.9;">Download from</span>
                                <span class="btn-platform"
                                    style="display: block; font-size: 1rem; font-weight: 700;">Microsoft</span>
                            </div>
                        </a>
                    </div>

                    <!-- Direct Downloads -->
                    <div
                        style="margin-top: 3rem; padding: 2rem; background: rgba(255,255,255,0.05); border-radius: 12px;">
                        <h4 style="margin: 0 0 1.5rem 0; text-align: center; color: white;">
                            <i class="fas fa-download"></i> Direct Downloads
                        </h4>

                        <div style="display: grid; gap: 1rem;">
                            <!-- Android/Fire TV APK -->
                            <div style="background: rgba(255,255,255,0.1); padding: 1.25rem; border-radius: 8px;">
                                <p
                                    style="margin: 0 0 0.75rem 0; color: white; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
                                    <i class="fab fa-android" style="color: #3DDC84;"></i> Android TV & Amazon Fire TV
                                    (APK)
                                </p>
                                <a href="apps/android/bingetv-android-tv.apk" download
                                    style="color: #FFD700; word-break: break-all; text-decoration: none; font-size: 0.9rem;">
                                    <?php echo SITE_URL; ?>/apps/android/bingetv-android-tv.apk
                                </a>
                                <p style="margin: 0.5rem 0 0 0; color: rgba(255,255,255,0.7); font-size: 0.85rem;">
                                    <i class="fas fa-info-circle"></i> Enable "Unknown Sources" in TV settings before
                                    installing
                                </p>
                            </div>

                            <!-- LG WebOS IPK -->
                            <div style="background: rgba(255,255,255,0.1); padding: 1.25rem; border-radius: 8px;">
                                <p
                                    style="margin: 0 0 0.75rem 0; color: white; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
                                    <i class="fas fa-tv" style="color: #A50034;"></i> LG Smart TV WebOS (IPK)
                                </p>
                                <a href="apps/webos/com.bingetv.app_1.0.0_all.ipk" download
                                    style="color: #FFD700; word-break: break-all; text-decoration: none; font-size: 0.9rem;">
                                    <?php echo SITE_URL; ?>/apps/webos/com.bingetv.app_1.0.0_all.ipk
                                </a>
                                <p style="margin: 0.5rem 0 0 0; color: rgba(255,255,255,0.7); font-size: 0.85rem;">
                                    <i class="fas fa-info-circle"></i> Enable "Developer Mode" in TV settings before
                                    installing
                                </p>
                            </div>

                            <!-- Samsung Tizen TPK -->
                            <div style="background: rgba(255,255,255,0.1); padding: 1.25rem; border-radius: 8px;">
                                <p
                                    style="margin: 0 0 0.75rem 0; color: white; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
                                    <i class="fas fa-tv" style="color: #1428A0;"></i> Samsung Smart TV Tizen (TPK)
                                </p>
                                <a href="apps/tizen/com.bingetv.app-1.0.0.tpk" download
                                    style="color: #FFD700; word-break: break-all; text-decoration: none; font-size: 0.9rem;">
                                    <?php echo SITE_URL; ?>/apps/tizen/com.bingetv.app-1.0.0.tpk
                                </a>
                                <p style="margin: 0.5rem 0 0 0; color: rgba(255,255,255,0.7); font-size: 0.85rem;">
                                    <i class="fas fa-info-circle"></i> Enable "Developer Mode" in TV settings before
                                    installing
                                </p>
                            </div>

                            <!-- Installation Help Link -->
                            <div style="text-align: center; margin-top: 0.5rem;">
                                <a href="apps.php"
                                    style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; background: linear-gradient(135deg, #8B0000, #660000); color: white; border-radius: 8px; text-decoration: none; font-weight: 600; transition: all 0.3s;">
                                    <i class="fas fa-question-circle"></i> View Installation Instructions
                                </a>
                            </div>
                        </div>
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
                                <img src="https://ui-avatars.com/api/?name=John+Mwangi&background=8B0000&color=FFFFFF&size=150"
                                    alt="John Mwangi">
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
                                    "BingeTV has completely transformed our family's entertainment experience. The
                                    picture quality is amazing and we can watch on all our devices. M-PESA payment makes
                                    it so convenient!"
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
                                <img src="https://ui-avatars.com/api/?name=Sarah+Wanjiku&background=8B0000&color=FFFFFF&size=150"
                                    alt="Sarah Wanjiku">
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
                                    "I love the variety of channels available. From international news to local content,
                                    everything is there. The customer support is excellent and always available when I
                                    need help."
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
                                <img src="https://ui-avatars.com/api/?name=Peter+Otieno&background=8B0000&color=FFFFFF&size=150"
                                    alt="Peter Otieno">
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
                                    "The setup was so easy! I just downloaded the app on my Firestick and was streaming
                                    within minutes. The sports channels are fantastic - never miss a game now."
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
                                <img src="https://ui-avatars.com/api/?name=Grace+Akinyi&background=8B0000&color=FFFFFF&size=150"
                                    alt="Grace Akinyi">
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
                                    "As a busy working mom, I appreciate how reliable the service is. My kids can watch
                                    their favorite shows without any interruptions. The family plan is perfect for us!"
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
                                    <iframe src="<?php echo htmlspecialchars($item['video_url']); ?>" frameborder="0"
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
                <a href="gallery.php" class="btn btn-primary">
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
                        <p>Getting started is easy! Simply choose a package that suits your needs, register for an
                            account, and pay using M-PESA. Once payment is confirmed, you'll receive your login
                            credentials and can start streaming immediately on any compatible device.</p>
                    </div>
                </div>

                <div class="faq-item" data-aos="fadeInUp" data-aos-delay="200">
                    <div class="faq-question">
                        <h3>Which devices are supported?</h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>BingeTV works on Smart TVs (Samsung, LG, Sony, TCL), Amazon Firestick, Roku, Android and iOS
                            devices, and computers. Simply download our gateway app from the appropriate app store or
                            our website.</p>
                    </div>
                </div>

                <div class="faq-item" data-aos="fadeInUp" data-aos-delay="300">
                    <div class="faq-question">
                        <h3>How does M-PESA payment work?</h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>You can pay using either our Till number or Paybill number. After selecting your package,
                            you'll receive the payment details via SMS. Once payment is confirmed, your account will be
                            activated automatically within minutes.</p>
                    </div>
                </div>

                <div class="faq-item" data-aos="fadeInUp" data-aos-delay="400">
                    <div class="faq-question">
                        <h3>Can I watch on multiple devices simultaneously?</h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Yes! Depending on your package, you can stream on 1, 3, 5, or up to 10 devices
                            simultaneously. Each device counts as one connection, so you can share your subscription
                            with family members.</p>
                    </div>
                </div>

                <div class="faq-item" data-aos="fadeInUp" data-aos-delay="500">
                    <div class="faq-question">
                        <h3>What internet speed do I need?</h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>For SD quality, we recommend at least 2 Mbps. For HD streaming, 5 Mbps is ideal. For 4K
                            content, you'll need 15+ Mbps. Our adaptive streaming automatically adjusts quality based on
                            your connection.</p>
                    </div>
                </div>

                <div class="faq-item" data-aos="fadeInUp" data-aos-delay="600">
                    <div class="faq-question">
                        <h3>Is there customer support available?</h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Absolutely! We provide 24/7 customer support through WhatsApp, live chat, phone, and email.
                            Our support team is always ready to help with any technical issues or questions you may
                            have.</p>
                    </div>
                </div>

                <div class="faq-item" data-aos="fadeInUp" data-aos-delay="700">
                    <div class="faq-question">
                        <h3>Can I cancel my subscription anytime?</h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Yes, you can cancel your subscription at any time. Your service will continue until the end
                            of your current billing period. You can manage your subscription and billing through your
                            user dashboard.</p>
                    </div>
                </div>

                <div class="faq-item" data-aos="fadeInUp" data-aos-delay="800">
                    <div class="faq-question">
                        <h3>Do you offer refunds?</h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>We offer a 7-day money-back guarantee for new subscribers. If you're not satisfied with our
                            service within the first week, contact our support team for a full refund.</p>
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
                    <a href="help.php" class="support-btn">Browse FAQ</a>
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
                        <li><a href="gallery.php">Gallery</a></li>
                        <li><a href="support.php">Support</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h4>Account</h4>
                    <ul class="footer-links">
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Register</a></li>
                        <li><a href="packages.php">Packages</a></li>
                        <li><a href="user/dashboard/">Dashboard</a></li>
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
                <div class="footer-bottom-content">
                    <p>&copy; <?php echo date('Y'); ?> BingeTV. All rights reserved.</p>
                    <div class="footer-bottom-links">
                        <a href="privacy.php">Privacy Policy</a>
                        <a href="terms.php">Terms of Service</a>
                        <a href="refund.php">Refund Policy</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Floating WhatsApp Button -->
    <div class="whatsapp-float">
        <a href="https://wa.me/254768704834?text=Hello%2C%20I%20need%20help%20with%20BingeTV" target="_blank"
            class="whatsapp-btn">
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