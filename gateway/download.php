<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is logged in (optional for downloads)
$user = null;
if (isLoggedIn()) {
    $user = getCurrentUser();
}

$platform = $_GET['platform'] ?? 'android';
$deviceType = $_GET['device'] ?? '';

// Log download attempt
if ($user) {
    logActivity($user['id'], 'app_download', "Downloaded gateway app for {$platform}");
}

// Get device information
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$isMobile = preg_match('/Mobile|Android|iPhone|iPad/', $userAgent);
$isAndroid = preg_match('/Android/', $userAgent);
$isiOS = preg_match('/iPhone|iPad|iPod/', $userAgent);

// Auto-detect platform if not specified
if (!$platform || $platform === 'auto') {
    if ($isAndroid) {
        $platform = 'android';
    } elseif ($isiOS) {
        $platform = 'ios';
    } else {
        $platform = 'android'; // Default to Android
    }
}

// Gateway app information
$appInfo = [
    'android' => [
        'name' => 'GStreaming Gateway',
        'version' => '1.0.0',
        'size' => '15.2 MB',
        'filename' => 'gstreaming-gateway.apk',
        'description' => 'Gateway app for Android devices including Smart TVs, Firestick, and mobile devices'
    ],
    'ios' => [
        'name' => 'GStreaming Gateway',
        'version' => '1.0.0',
        'size' => '18.7 MB',
        'filename' => 'gstreaming-gateway.ipa',
        'description' => 'Gateway app for iOS devices including iPhone, iPad, and Apple TV'
    ],
    'web' => [
        'name' => 'GStreaming Web Player',
        'version' => '1.0.0',
        'size' => 'N/A',
        'filename' => 'web-player.html',
        'description' => 'Web-based player for browsers and compatible devices'
    ]
];

$currentApp = $appInfo[$platform] ?? $appInfo['android'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Download Gateway App - GStreaming</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="download-page">
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <i class="fas fa-satellite-dish"></i>
                <span class="logo-text">GStreaming</span>
            </div>
            
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="../index.php" class="nav-link">Home</a>
                </li>
                <li class="nav-item">
                    <a href="../index.php#packages" class="nav-link">Packages</a>
                </li>
                <li class="nav-item">
                    <a href="../index.php#devices" class="nav-link">Devices</a>
                </li>
                <li class="nav-item">
                    <a href="../gallery.php" class="nav-link">Gallery</a>
                </li>
                <?php if ($user): ?>
                    <li class="nav-item">
                        <a href="../dashboard.php" class="nav-link">Dashboard</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a href="../login.php" class="nav-link btn-login">Login</a>
                    </li>
                    <li class="nav-item">
                        <a href="../register.php" class="nav-link btn-register">Get Started</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <!-- Download Section -->
    <section class="download-section">
        <div class="container">
            <div class="download-container">
                <div class="download-header">
                    <h1>Download Gateway App</h1>
                    <p>Access thousands of streaming channels on your device</p>
                </div>
                
                <!-- Platform Detection -->
                <div class="platform-detection">
                    <div class="detected-platform">
                        <div class="platform-icon">
                            <i class="fab fa-<?php echo $platform === 'android' ? 'android' : ($platform === 'ios' ? 'apple' : 'chrome'); ?>"></i>
                        </div>
                        <div class="platform-info">
                            <h3><?php echo ucfirst($platform); ?> Device Detected</h3>
                            <p>We've detected you're using a <?php echo $platform; ?> device. Download the appropriate app below.</p>
                        </div>
                    </div>
                </div>
                
                <!-- App Download Cards -->
                <div class="download-cards">
                    <!-- Android -->
                    <div class="download-card <?php echo $platform === 'android' ? 'recommended' : ''; ?>">
                        <div class="card-header">
                            <div class="app-icon">
                                <i class="fab fa-android"></i>
                            </div>
                            <div class="app-info">
                                <h3><?php echo $appInfo['android']['name']; ?></h3>
                                <p>Version <?php echo $appInfo['android']['version']; ?> • <?php echo $appInfo['android']['size']; ?></p>
                            </div>
                            <?php if ($platform === 'android'): ?>
                                <div class="recommended-badge">
                                    <span>Recommended</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="card-content">
                            <p><?php echo $appInfo['android']['description']; ?></p>
                            
                            <div class="device-compatibility">
                                <h4>Compatible Devices:</h4>
                                <ul>
                                    <li><i class="fas fa-check"></i> Android Smart TVs</li>
                                    <li><i class="fas fa-check"></i> Amazon Firestick</li>
                                    <li><i class="fas fa-check"></i> Android TV Boxes</li>
                                    <li><i class="fas fa-check"></i> Android Phones & Tablets</li>
                                    <li><i class="fas fa-check"></i> Chromecast</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="card-footer">
                            <a href="downloads/<?php echo $appInfo['android']['filename']; ?>" 
                               class="btn btn-primary btn-large btn-full" 
                               download>
                                <i class="fas fa-download"></i>
                                Download for Android
                            </a>
                        </div>
                    </div>
                    
                    <!-- iOS -->
                    <div class="download-card <?php echo $platform === 'ios' ? 'recommended' : ''; ?>">
                        <div class="card-header">
                            <div class="app-icon">
                                <i class="fab fa-apple"></i>
                            </div>
                            <div class="app-info">
                                <h3><?php echo $appInfo['ios']['name']; ?></h3>
                                <p>Version <?php echo $appInfo['ios']['version']; ?> • <?php echo $appInfo['ios']['size']; ?></p>
                            </div>
                            <?php if ($platform === 'ios'): ?>
                                <div class="recommended-badge">
                                    <span>Recommended</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="card-content">
                            <p><?php echo $appInfo['ios']['description']; ?></p>
                            
                            <div class="device-compatibility">
                                <h4>Compatible Devices:</h4>
                                <ul>
                                    <li><i class="fas fa-check"></i> iPhone & iPad</li>
                                    <li><i class="fas fa-check"></i> Apple TV</li>
                                    <li><i class="fas fa-check"></i> macOS (via sideload)</li>
                                    <li><i class="fas fa-check"></i> AirPlay compatible devices</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="card-footer">
                            <a href="downloads/<?php echo $appInfo['ios']['filename']; ?>" 
                               class="btn btn-primary btn-large btn-full" 
                               download>
                                <i class="fas fa-download"></i>
                                Download for iOS
                            </a>
                        </div>
                    </div>
                    
                    <!-- Web Player -->
                    <div class="download-card web-player">
                        <div class="card-header">
                            <div class="app-icon">
                                <i class="fas fa-globe"></i>
                            </div>
                            <div class="app-info">
                                <h3><?php echo $appInfo['web']['name']; ?></h3>
                                <p>Web-based streaming solution</p>
                            </div>
                        </div>
                        
                        <div class="card-content">
                            <p>Access streaming channels directly through your web browser. No installation required.</p>
                            
                            <div class="device-compatibility">
                                <h4>Compatible Browsers:</h4>
                                <ul>
                                    <li><i class="fas fa-check"></i> Chrome (recommended)</li>
                                    <li><i class="fas fa-check"></i> Firefox</li>
                                    <li><i class="fas fa-check"></i> Safari</li>
                                    <li><i class="fas fa-check"></i> Edge</li>
                                    <li><i class="fas fa-check"></i> Opera</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="card-footer">
                            <a href="../player.php" class="btn btn-secondary btn-large btn-full">
                                <i class="fas fa-play"></i>
                                Launch Web Player
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Installation Instructions -->
                <div class="installation-guide">
                    <h3>Installation Instructions</h3>
                    
                    <div class="instructions-grid">
                        <div class="instruction-card">
                            <div class="instruction-number">1</div>
                            <div class="instruction-content">
                                <h4>Download the App</h4>
                                <p>Click the download button for your device type. The app will be saved to your device.</p>
                            </div>
                        </div>
                        
                        <div class="instruction-card">
                            <div class="instruction-number">2</div>
                            <div class="instruction-content">
                                <h4>Install the App</h4>
                                <p>Follow your device's installation process. You may need to enable "Unknown Sources" for Android devices.</p>
                            </div>
                        </div>
                        
                        <div class="instruction-card">
                            <div class="instruction-number">3</div>
                            <div class="instruction-content">
                                <h4>Login & Stream</h4>
                                <p>Open the app, login with your GStreaming account, and start enjoying thousands of channels!</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Device-Specific Instructions -->
                <div class="device-instructions">
                    <h3>Device-Specific Setup</h3>
                    
                    <div class="device-tabs">
                        <button class="tab-btn active" data-tab="firestick">Firestick</button>
                        <button class="tab-btn" data-tab="smart-tv">Smart TV</button>
                        <button class="tab-btn" data-tab="roku">Roku</button>
                        <button class="tab-btn" data-tab="mobile">Mobile</button>
                    </div>
                    
                    <div class="tab-content">
                        <div class="tab-panel active" id="firestick">
                            <h4>Amazon Firestick Setup</h4>
                            <ol>
                                <li>Go to Settings → My Fire TV → Developer Options</li>
                                <li>Turn on "Apps from Unknown Sources"</li>
                                <li>Download and install the Android APK file</li>
                                <li>Launch the app from your Apps library</li>
                                <li>Login with your GStreaming credentials</li>
                            </ol>
                        </div>
                        
                        <div class="tab-panel" id="smart-tv">
                            <h4>Android Smart TV Setup</h4>
                            <ol>
                                <li>Enable "Unknown Sources" in Security settings</li>
                                <li>Download the APK file to a USB drive</li>
                                <li>Connect USB to your TV and install the app</li>
                                <li>Alternatively, use the built-in browser to download</li>
                                <li>Launch and login to start streaming</li>
                            </ol>
                        </div>
                        
                        <div class="tab-panel" id="roku">
                            <h4>Roku Setup</h4>
                            <ol>
                                <li>Roku devices require sideloading via developer mode</li>
                                <li>Enable Developer Mode in your Roku settings</li>
                                <li>Use the web interface to upload the app</li>
                                <li>Install and launch the GStreaming app</li>
                                <li>Login and enjoy streaming</li>
                            </ol>
                        </div>
                        
                        <div class="tab-panel" id="mobile">
                            <h4>Mobile Device Setup</h4>
                            <ol>
                                <li>Download the appropriate app for your device</li>
                                <li>Allow installation from unknown sources (Android)</li>
                                <li>Install and open the app</li>
                                <li>Login with your GStreaming account</li>
                                <li>Cast to your TV using Chromecast or AirPlay</li>
                            </ol>
                        </div>
                    </div>
                </div>
                
                <!-- Support -->
                <div class="download-support">
                    <h3>Need Help?</h3>
                    <p>Having trouble downloading or installing the app? Our support team is here to help.</p>
                    
                    <div class="support-options">
                        <a href="../support.php" class="support-link">
                            <i class="fas fa-headset"></i>
                            Contact Support
                        </a>
                        <a href="../help.php" class="support-link">
                            <i class="fas fa-question-circle"></i>
                            Help Center
                        </a>
                        <a href="mailto:support@gstreaming.com" class="support-link">
                            <i class="fas fa-envelope"></i>
                            Email Support
                        </a>
                    </div>
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
                        <span>GStreaming</span>
                    </div>
                    <p>Premium TV streaming service for Kenya. Stream thousands of channels on any device.</p>
                </div>
            </div>
            
            <div class="footer-bottom">
                <div class="footer-bottom-content">
                    <p>&copy; <?php echo date('Y'); ?> GStreaming. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="../assets/js/main.js"></script>
    <script>
        // Tab functionality
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const tabId = this.getAttribute('data-tab');
                
                // Update active tab button
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                // Update active tab panel
                document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
                document.getElementById(tabId).classList.add('active');
            });
        });
        
        // Download tracking
        document.querySelectorAll('a[download]').forEach(link => {
            link.addEventListener('click', function() {
                // Track download in analytics
                if (typeof gtag !== 'undefined') {
                    gtag('event', 'app_download', {
                        'app_name': 'GStreaming Gateway',
                        'platform': '<?php echo $platform; ?>'
                    });
                }
            });
        });
    </script>
</body>
</html>
