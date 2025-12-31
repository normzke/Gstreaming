<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../lib/functions.php';
require_once '../lib/seo.php';

// Enhanced TV platform detection from User-Agent
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$detected_platform = 'unknown';
$is_tv_browser = false;

// Android TV detection
if (stripos($user_agent, 'Android') !== false && stripos($user_agent, 'TV') !== false) {
    $detected_platform = 'android';
    $is_tv_browser = true;
}
// Fire TV detection
elseif (stripos($user_agent, 'AFT') !== false || stripos($user_agent, 'AFTM') !== false) {
    $detected_platform = 'android';
    $is_tv_browser = true;
}
// WebOS detection
elseif (stripos($user_agent, 'Web0S') !== false || stripos($user_agent, 'webOS') !== false || stripos($user_agent, 'NetCast') !== false) {
    $detected_platform = 'webos';
    $is_tv_browser = true;
}
// Tizen detection
elseif (stripos($user_agent, 'Tizen') !== false || stripos($user_agent, 'SmartTV') !== false) {
    $detected_platform = 'tizen';
    $is_tv_browser = true;
}

$seo_meta = class_exists('SEO') ? SEO::getMetaTags('apps') : ['title' => 'Apps', 'keywords' => '', 'description' => ''];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Base HREF to ensure relative links work from /apps/ clean URL -->
    <base href="https://bingetv.co.ke/">

    <title>Download BingeTV Apps - Stream on Any TV Platform</title>
    <meta name="description"
        content="Download BingeTV streaming apps for Android TV, LG WebOS, Samsung Tizen, and more. Stream 8K content on your Smart TV.">

    <!-- Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- CSS -->
    <link rel="stylesheet" href="css/main.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css/components.css">
    <link rel="stylesheet" href="css/responsive-fixes.css?v=<?php echo time(); ?>">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #0f172a;
            color: #ffffff;
            min-height: 100vh;
            padding-top: 80px;
            /* Space for fixed navbar */
        }

        /* Renamed to avoid conflicts with global header */
        .apps-hero {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            padding: 40px 20px;
            text-align: center;
            border-bottom: 3px solid #8B0000;
            box-shadow: 0 10px 30px rgba(139, 0, 0, 0.1);
        }

        .apps-hero h1 {
            font-family: 'Orbitron', sans-serif;
            background: #0f172a;
            color: #ffffff;
            min-height: 100vh;
            padding-top: 80px;
            /* Account for fixed navbar */
        }

        .apps-hero {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            padding: 40px 20px;
            text-align: center;
            border-bottom: 3px solid #8B0000;
            box-shadow: 0 10px 30px rgba(139, 0, 0, 0.1);
        }

        .apps-hero h1 {
            font-family: 'Orbitron', sans-serif;
            font-size: 48px;
            color: #8B0000;
            margin-bottom: 15px;
            text-shadow: 0 2px 4px rgba(139, 0, 0, 0.1);
        }

        .apps-hero p {
            font-size: 20px;
            color: #6b7280;
            max-width: 800px;
            margin: 0 auto;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .platform-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            margin-bottom: 60px;
        }

        .platform-card {
            background: #1e293b;
            border: 2px solid #334155;
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
        }

        .platform-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(139, 0, 0, 0.1) 0%, transparent 70%);
            opacity: 0;
            transition: opacity 0.4s;
        }

        .platform-card:hover::before {
            opacity: 1;
        }

        .platform-card:hover {
            transform: translateY(-10px) scale(1.02);
            border-color: #ef4444;
            box-shadow: 0 20px 40px rgba(139, 0, 0, 0.2);
        }

        .platform-card.detected {
            border-color: #ef4444;
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(30, 41, 59, 1) 100%);
        }

        .platform-icon {
            font-size: 64px;
            color: #ef4444;
            margin-bottom: 20px;
            display: block;
            text-align: center;
        }

        .platform-card.detected .platform-icon {
            color: #f87171;
        }

        .platform-name {
            font-size: 28px;
            font-weight: bold;
            color: #f1f5f9;
            margin-bottom: 10px;
            text-align: center;
        }

        .platform-desc {
            color: #94a3b8;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
        }

        .platform-features {
            list-style: none;
            margin-bottom: 25px;
        }

        .platform-features li {
            padding: 8px 0;
            color: #cbd5e1;
            font-size: 14px;
        }

        .platform-features li i {
            color: #ef4444;
            margin-right: 10px;
        }

        .download-btn {
            display: block;
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);
            color: #ffffff;
            text-align: center;
            text-decoration: none;
            border-radius: 12px;
            font-weight: bold;
            font-size: 16px;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            box-shadow: 0 5px 15px rgba(239, 68, 68, 0.3);
        }

        .download-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 25px rgba(239, 68, 68, 0.5);
        }

        .download-btn i {
            margin-right: 8px;
        }

        .detected-badge {
            background: #ef4444;
            color: #ffffff;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 15px;
        }

        .section-title {
            font-family: 'Orbitron', sans-serif;
            font-size: 36px;
            color: #f1f5f9;
            margin-bottom: 30px;
            text-align: center;
        }

        .instructions {
            background: #1e293b;
            border: 2px solid #334155;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 40px;
        }

        .instructions h3 {
            color: #f87171;
            margin-bottom: 15px;
            font-size: 24px;
        }

        .instructions ol {
            color: #cbd5e1;
            padding-left: 25px;
        }

        .instructions li {
            margin-bottom: 10px;
            line-height: 1.6;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .feature-box {
            background: rgba(0, 168, 255, 0.1);
            border: 2px solid rgba(0, 168, 255, 0.2);
            border-radius: 12px;
            padding: 25px;
            text-align: center;
            transition: all 0.3s;
        }

        .feature-box:hover {
            border-color: #8B0000;
            transform: translateY(-5px);
        }

        .feature-box i {
            font-size: 40px;
            color: #8B0000;
            margin-bottom: 15px;
        }

        .feature-box h4 {
            color: #1a1a2e;
            margin-bottom: 10px;
            font-size: 18px;
        }

        .feature-box p {
            color: #aaa;
            font-size: 14px;
        }

        .qr-code {
            text-align: center;
            margin-top: 20px;
        }

        .qr-code img {
            width: 150px;
            height: 150px;
            border: 3px solid #8B0000;
            border-radius: 10px;
            padding: 10px;
            background: white;
        }

        .qr-code p {
            margin-top: 10px;
            color: #aaa;
            font-size: 12px;
        }

        .back-btn {
            display: inline-block;
            padding: 12px 30px;
            background: rgba(0, 168, 255, 0.2);
            color: #8B0000;
            text-decoration: none;
            border-radius: 8px;
            border: 2px solid #8B0000;
            font-weight: 600;
            transition: all 0.3s;
            margin-bottom: 30px;
        }

        .back-btn:hover {
            background: #8B0000;
            color: #1a1a2e;
        }

        /* Auto-download modal */
        .auto-download-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.98);
            z-index: 10000;
            display: none;
            align-items: center;
            justify-content: center;
        }

        .auto-download-modal.active {
            display: flex;
        }

        .modal-content {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            border: 3px solid #8B0000;
            border-radius: 20px;
            padding: 40px;
            max-width: 600px;
            width: 90%;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 168, 255, 0.5);
        }

        .modal-content h2 {
            color: #8B0000;
            font-size: 32px;
            margin-bottom: 20px;
            font-family: 'Orbitron', sans-serif;
        }

        .modal-content p {
            color: #6b7280;
            font-size: 18px;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .download-progress {
            width: 100%;
            height: 8px;
            background: rgba(0, 168, 255, 0.2);
            border-radius: 4px;
            overflow: hidden;
            margin: 20px 0;
        }

        .download-progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #8B0000, #A52A2A);
            width: 0%;
            transition: width 0.3s;
            animation: progress 3s ease-in-out forwards;
        }

        @keyframes progress {
            0% {
                width: 0%;
            }

            100% {
                width: 100%;
            }
        }

        .installation-steps {
            text-align: left;
            background: rgba(0, 168, 255, 0.1);
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }

        .installation-steps h3 {
            color: #8B0000;
            margin-bottom: 15px;
        }

        .installation-steps ol {
            color: #6b7280;
            padding-left: 20px;
        }

        .installation-steps li {
            margin-bottom: 10px;
            line-height: 1.5;
        }

        .installation-steps strong {
            color: #A52A2A;
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 32px;
            }

            .platform-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <?php include __DIR__ . '/includes/navigation.php'; ?>

    <?php if ($is_tv_browser && $detected_platform !== 'unknown'): ?>
        <!-- Auto-download Modal for TV Browsers -->
        <div class="auto-download-modal active" id="autoDownloadModal">
            <div class="modal-content">
                <h2><i class="fas fa-download"></i> Downloading BingeTV</h2>
                <p>Your
                    <?php echo ucfirst($detected_platform === 'android' ? 'Android TV' : ($detected_platform === 'webos' ? 'LG WebOS' : 'Samsung Tizen')); ?>
                    has been detected!
                </p>
                <p>The app is downloading automatically...</p>

                <div class="download-progress">
                    <div class="download-progress-bar"></div>
                </div>

                <div class="installation-steps">
                    <h3><i class="fas fa-info-circle"></i> Installation Steps:</h3>
                    <ol>
                        <?php if ($detected_platform === 'android'): ?>
                            <li>Go to <strong>Settings > Security & Restrictions</strong></li>
                            <li>Enable <strong>Unknown Sources</strong> or <strong>Install Unknown Apps</strong></li>
                            <li>Open the downloaded file from your <strong>Downloads</strong> folder</li>
                            <li>Click <strong>Install</strong></li>
                            <li>Open <strong>BingeTV</strong> from your app drawer</li>
                            <li>Enter your M3U playlist URL and start streaming!</li>
                        <?php elseif ($detected_platform === 'webos'): ?>
                            <li>Go to <strong>Settings > General > About This TV</strong></li>
                            <li>Enable <strong>Developer Mode</strong></li>
                            <li>The IPK file has been downloaded</li>
                            <li>Use file manager to locate and install the IPK</li>
                            <li>Launch <strong>BingeTV</strong> from your apps</li>
                            <li>Enter your M3U playlist URL and enjoy!</li>
                        <?php else: // Tizen ?>
                            <li>Go to <strong>Settings > Support > Device Care</strong></li>
                            <li>Enable <strong>Developer Mode</strong></li>
                            <li>The TPK file has been downloaded</li>
                            <li>Navigate to the file and install</li>
                            <li>Open <strong>BingeTV</strong> from Smart Hub</li>
                            <li>Enter your M3U playlist URL and stream!</li>
                        <?php endif; ?>
                    </ol>
                </div>

                <button class="btn btn-primary" onclick="closeModal()"
                    style="margin-top: 20px; padding: 15px 40px; font-size: 18px;">
                    <i class="fas fa-check"></i> Got It!
                </button>
            </div>
        </div>
    <?php endif; ?>
    <div class="apps-hero">
        <h1><i class="fas fa-satellite-dish"></i> BingeTV Apps</h1>
        <p>Download our streaming apps for your Smart TV platform and enjoy unlimited entertainment in stunning 8K
            quality</p>
    </div>

    <div class="container">
        <a href="index" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Home</a>


        <h2 class="section-title">Choose Your Platform</h2>

        <div class="platform-grid">
            <!-- Android TV -->
            <div class="platform-card <?php echo $detected_platform === 'android' ? 'detected' : ''; ?>">
                <?php if ($detected_platform === 'android'): ?>
                    <span class="detected-badge"><i class="fas fa-check-circle"></i> Detected</span>
                <?php endif; ?>

                <i class="fab fa-android platform-icon"></i>
                <h3 class="platform-name">Android TV</h3>
                <p class="platform-desc">For Android TV, Fire TV, and Android TV boxes</p>

                <ul class="platform-features">
                    <li><i class="fas fa-check"></i> Native Android TV interface</li>
                    <li><i class="fas fa-check"></i> Voice search support</li>
                    <li><i class="fas fa-check"></i> Google Cast integration</li>
                    <li><i class="fas fa-check"></i> Picture-in-Picture mode</li>
                    <li><i class="fas fa-check"></i> 4K/8K streaming</li>
                </ul>

                <a href="apps/android/bingetv-android-tv.apk" class="download-btn" download>
                    <i class="fas fa-download"></i> Download APK
                </a>

                <div class="qr-code">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo urlencode(SITE_URL . '/apps/android/bingetv-android-tv.apk'); ?>"
                        alt="QR Code">
                    <p>Scan to download</p>
                </div>
            </div>

            <!-- WebOS (LG) -->
            <div class="platform-card <?php echo $detected_platform === 'webos' ? 'detected' : ''; ?>">
                <?php if ($detected_platform === 'webos'): ?>
                    <span class="detected-badge"><i class="fas fa-check-circle"></i> Detected</span>
                <?php endif; ?>

                <i class="fas fa-tv platform-icon"></i>
                <h3 class="platform-name">LG WebOS</h3>
                <p class="platform-desc">For LG Smart TVs (webOS 4.0+)</p>

                <ul class="platform-features">
                    <li><i class="fas fa-check"></i> Magic Remote support</li>
                    <li><i class="fas fa-check"></i> LG TV optimized UI</li>
                    <li><i class="fas fa-check"></i> Quick access from home</li>
                    <li><i class="fas fa-check"></i> Smooth navigation</li>
                    <li><i class="fas fa-check"></i> 4K/8K streaming</li>
                </ul>

                <a href="apps/webos/com.bingetv.app_1.0.0_all.ipk" class="download-btn" download>
                    <i class="fas fa-download"></i> Download IPK
                </a>

                <div class="qr-code">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo urlencode(SITE_URL . '/apps/webos/com.bingetv.app_1.0.0_all.ipk'); ?>"
                        alt="QR Code">
                    <p>Scan to download</p>
                </div>
            </div>

            <!-- Samsung Tizen -->
            <div class="platform-card <?php echo $detected_platform === 'tizen' ? 'detected' : ''; ?>">
                <?php if ($detected_platform === 'tizen'): ?>
                    <span class="detected-badge"><i class="fas fa-check-circle"></i> Detected</span>
                <?php endif; ?>

                <i class="fas fa-tv platform-icon"></i>
                <h3 class="platform-name">Samsung Tizen</h3>
                <p class="platform-desc">For Samsung Smart TVs (Tizen 6.0+)</p>

                <ul class="platform-features">
                    <li><i class="fas fa-check"></i> Samsung Smart Hub</li>
                    <li><i class="fas fa-check"></i> One Remote support</li>
                    <li><i class="fas fa-check"></i> Bixby voice control</li>
                    <li><i class="fas fa-check"></i> QLED optimized</li>
                    <li><i class="fas fa-check"></i> 4K/8K streaming</li>
                </ul>

                <a href="apps/tizen/com.bingetv.app-1.0.0.tpk" class="download-btn" download>
                    <i class="fas fa-download"></i> Download TPK
                </a>

                <div class="qr-code">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo urlencode(SITE_URL . '/apps/tizen/com.bingetv.app-1.0.0.tpk'); ?>"
                        alt="QR Code">
                    <p>Scan to download</p>
                </div>
            </div>
        </div>

        <h2 class="section-title">App Features</h2>

        <div class="features-grid">
            <div class="feature-box">
                <i class="fas fa-film"></i>
                <h4>Unlimited Content</h4>
                <p>Access thousands of channels, movies, and TV shows</p>
            </div>

            <div class="feature-box">
                <i class="fas fa-hd-video"></i>
                <h4>8K Streaming</h4>
                <p>Crystal clear 4K and 8K quality streaming</p>
            </div>

            <div class="feature-box">
                <i class="fas fa-heart"></i>
                <h4>Favorites</h4>
                <p>Save your favorite channels for quick access</p>
            </div>

            <div class="feature-box">
                <i class="fas fa-search"></i>
                <h4>Smart Search</h4>
                <p>Find content quickly with powerful search</p>
            </div>

            <div class="feature-box">
                <i class="fas fa-mobile-alt"></i>
                <h4>Multi-Device</h4>
                <p>Stream on multiple devices simultaneously</p>
            </div>

            <div class="feature-box">
                <i class="fas fa-shield-alt"></i>
                <h4>Secure</h4>
                <p>Encrypted streaming with secure authentication</p>
            </div>
        </div>

        <h2 class="section-title">Installation Instructions</h2>

        <div class="instructions">
            <h3><i class="fab fa-android"></i> Android TV / Fire TV</h3>
            <ol>
                <li>Download the APK file to a USB drive or use the QR code</li>
                <li>On your TV, go to <strong>Settings > Security & Restrictions</strong></li>
                <li>Enable <strong>Unknown Sources</strong> or <strong>Install Unknown Apps</strong></li>
                <li>Use a file manager to navigate to the APK file</li>
                <li>Click the APK to install</li>
                <li>Open BingeTV from your app drawer</li>
                <li>Enter your credentials and start streaming!</li>
            </ol>
        </div>

        <div class="instructions">
            <h3><i class="fas fa-tv"></i> LG WebOS</h3>
            <ol>
                <li>Download the IPK file to a USB drive</li>
                <li>On your LG TV, go to <strong>Settings > General > About This TV</strong></li>
                <li>Enable <strong>Developer Mode</strong></li>
                <li>Insert USB drive with IPK file</li>
                <li>Use LG Content Store or file manager to install</li>
                <li>Launch BingeTV from your apps</li>
                <li>Sign in and enjoy!</li>
            </ol>
        </div>

        <div class="instructions">
            <h3><i class="fas fa-tv"></i> Samsung Tizen</h3>
            <ol>
                <li>Download the TPK file to a USB drive</li>
                <li>On your Samsung TV, go to <strong>Settings > Support > Device Care</strong></li>
                <li>Enable <strong>Developer Mode</strong></li>
                <li>Insert USB drive with TPK file</li>
                <li>Navigate to the file and install</li>
                <li>Open BingeTV from Smart Hub</li>
                <li>Log in with your credentials</li>
            </ol>
        </div>

        <div class="instructions">
            <h3><i class="fas fa-question-circle"></i> Need Help?</h3>
            <p style="color: #6b7280; margin-bottom: 15px;">
                If you encounter any issues during installation or usage, please contact our support team:
            </p>
            <ul style="list-style: none; padding: 0;">
                <li style="margin-bottom: 10px;"><i class="fas fa-envelope"
                        style="color: #8B0000; margin-right: 10px;"></i> Email: support@bingetv.co.ke</li>
                <li style="margin-bottom: 10px;"><i class="fas fa-phone"
                        style="color: #8B0000; margin-right: 10px;"></i> Phone: +254 768 704 834</li>
                <li><i class="fas fa-clock" style="color: #8B0000; margin-right: 10px;"></i> Support Hours: 24/7</li>
            </ul>
        </div>
    </div>

    <script>
        // Auto-download functionality for TV browsers
        <?php if ($is_tv_browser && $detected_platform !== 'unknown'): ?>
            window.addEventListener('DOMContentLoaded', function () {
                const downloadUrl = <?php
                if ($detected_platform === 'android') {
                    echo "'/apps/android/bingetv-android-tv.apk'";
                } elseif ($detected_platform === 'webos') {
                    echo "'/apps/webos/com.bingetv.app_1.0.0_all.ipk'";
                } else {
                    echo "'/apps/tizen/com.bingetv.app-1.0.0.tpk'";
                }
                ?>;

                // Add manual download button to modal
                const modal = document.getElementById('autoDownloadModal');
                if (modal) {
                    const downloadBtn = document.createElement('a');
                    downloadBtn.href = downloadUrl;
                    downloadBtn.download = downloadUrl.split('/').pop();
                    downloadBtn.className = 'btn btn-primary';
                    downloadBtn.style.cssText = 'display: inline-block; margin: 20px auto; padding: 15px 40px; font-size: 20px; background: #8B0000; color: white; text-decoration: none; border-radius: 8px;';
                    downloadBtn.innerHTML = '<i class="fas fa-download"></i> Click Here to Download';

                    const modalContent = modal.querySelector('.modal-content');
                    if (modalContent) {
                        modalContent.appendChild(downloadBtn);
                    }
                }

                // Try automatic download
                setTimeout(function () {
                    try {
                        // Method 1: Create download link
                        const link = document.createElement('a');
                        link.href = downloadUrl;
                        link.download = downloadUrl.split('/').pop();
                        link.setAttribute('download', downloadUrl.split('/').pop());
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);

                        console.log('Auto-download triggered for: ' + downloadUrl);

                        // Show success message
                        setTimeout(function () {
                            alert('Download started! Check your Downloads folder. If download didn\'t start, use the "Click Here to Download" button above.');
                        }, 2000);
                    } catch (error) {
                        console.error('Auto-download failed:', error);
                        alert('Please use the "Click Here to Download" button to download the app.');
                    }
                }, 1500); // 1.5 second delay to show the modal first
            });
        <?php endif; ?>

        function closeModal() {
            const modal = document.getElementById('autoDownloadModal');
            if (modal) {
                modal.classList.remove('active');
                <!-- Footer -->
                <?php include 'includes/footer.php'; ?>
</body >
</html >