<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/functions.php';
require_once __DIR__ . '/../lib/seo.php';

// Get SEO data
$seo_meta = SEO::getMetaTags('download');
$og_tags = SEO::getOpenGraphTags('download');
$canonical_url = SEO::getCanonicalUrl('download');

// Detect TV platform from User-Agent
function detectTVPlatform() {
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $userAgent = strtolower($userAgent);
    
    // Android TV detection
    if (strpos($userAgent, 'android') !== false && 
        (strpos($userAgent, 'tv') !== false || strpos($userAgent, 'smart-tv') !== false)) {
        return 'android';
    }
    
    // WebOS (LG) detection
    if (strpos($userAgent, 'webos') !== false || strpos($userAgent, 'lg') !== false) {
        return 'webos';
    }
    
    // Samsung Tizen detection
    if (strpos($userAgent, 'tizen') !== false || 
        (strpos($userAgent, 'samsung') !== false && strpos($userAgent, 'smart-tv') !== false)) {
        return 'tizen';
    }
    
    return 'unknown';
}

$detectedPlatform = detectTVPlatform();

// App download URLs - pointing to actual built files
$downloadUrls = [
    'android' => '/apps/android/BingeTV-debug.apk',  // Will be available after APK build
    'webos' => '/apps/webos/com.bingetv.app_1.0.0_all.ipk',
    'tizen' => '/apps/tizen/com.bingetv.app-1.0.0.tpk'
];

// Check if files exist
$fileExists = [
    'android' => file_exists(__DIR__ . '/apps/android/BingeTV-debug.apk'),
    'webos' => file_exists(__DIR__ . '/apps/webos/com.bingetv.app_1.0.0_all.ipk'),
    'tizen' => file_exists(__DIR__ . '/apps/tizen/com.bingetv.app-1.0.0.tpk')
];

// App information
$appInfo = [
    'android' => [
        'name' => 'BingeTV for Android TV',
        'version' => '1.0.0',
        'size' => $fileExists['android'] ? filesize(__DIR__ . '/apps/android/BingeTV-debug.apk') : '~15 MB',
        'format' => 'APK',
        'requirements' => 'Android 5.0+ (API 21+)',
        'description' => 'Stream your favorite channels on Android TV devices including NVIDIA Shield, Mi Box, and all Android TV devices.'
    ],
    'webos' => [
        'name' => 'BingeTV for LG Smart TV',
        'version' => '1.0.0',
        'size' => $fileExists['webos'] ? filesize(__DIR__ . '/apps/webos/com.bingetv.app_1.0.0_all.ipk') : '~5 MB',
        'format' => 'IPK',
        'requirements' => 'webOS 4.0+',
        'description' => 'Stream your favorite channels on LG Smart TVs with webOS platform.'
    ],
    'tizen' => [
        'name' => 'BingeTV for Samsung Smart TV',
        'version' => '1.0.0',
        'size' => $fileExists['tizen'] ? filesize(__DIR__ . '/apps/tizen/com.bingetv.app-1.0.0.tpk') : '~5 MB',
        'format' => 'TPK',
        'requirements' => 'Tizen 6.0+',
        'description' => 'Stream your favorite channels on Samsung Smart TVs with Tizen platform.'
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="https://bingetv.co.ke/">
    
    <title><?php echo htmlspecialchars($seo_meta['title'] ?? 'Download BingeTV - Smart TV Apps'); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($seo_meta['description'] ?? 'Download BingeTV app for your Smart TV'); ?>">
    
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .download-page {
            padding: 60px 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .download-header {
            text-align: center;
            margin-bottom: 60px;
        }
        
        .download-header h1 {
            font-size: 48px;
            color: #00A8FF;
            margin-bottom: 20px;
        }
        
        .platform-detected {
            background: linear-gradient(135deg, #00A8FF 0%, #0099E6 100%);
            color: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 40px;
            text-align: center;
        }
        
        .platforms-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .platform-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .platform-card.detected {
            border: 3px solid #00A8FF;
        }
        
        .download-btn {
            display: inline-block;
            width: 100%;
            padding: 15px 30px;
            background: #00A8FF;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-top: 10px;
            transition: all 0.3s;
        }
        
        .download-btn:hover {
            background: #0099E6;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,168,255,0.3);
        }
        
        .download-btn i {
            margin-right: 8px;
        }
        
        .platform-info {
            margin-bottom: 20px;
        }
        
        .platform-info p {
            margin: 8px 0;
            color: #666;
        }
        
        .platform-info strong {
            color: #333;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="download-page">
        <div class="download-header">
            <h1>Download BingeTV</h1>
        </div>
        
        <?php if ($detectedPlatform !== 'unknown'): ?>
        <div class="platform-detected">
            <h2>🎯 We detected your TV platform!</h2>
            <p>Your TV appears to be running <?php echo strtoupper($detectedPlatform); ?>.</p>
        </div>
        <?php endif; ?>
        
        <div class="platforms-grid">
            <?php foreach (['android', 'webos', 'tizen'] as $platform): ?>
            <div class="platform-card <?php echo $detectedPlatform === $platform ? 'detected' : ''; ?>">
                <h3>
                    <?php echo $appInfo[$platform]['name']; ?>
                    <?php if ($detectedPlatform === $platform): ?>
                        <span style="background: #00A8FF; color: white; padding: 3px 10px; border-radius: 12px; font-size: 12px; margin-left: 10px;">DETECTED</span>
                    <?php endif; ?>
                </h3>
                <div class="platform-info">
                    <p><strong>Version:</strong> <?php echo $appInfo[$platform]['version']; ?></p>
                    <p><strong>Format:</strong> <?php echo $appInfo[$platform]['format']; ?></p>
                    <p><strong>Size:</strong> 
                    <?php 
                    if ($fileExists[$platform]) {
                        $size = is_numeric($appInfo[$platform]['size']) 
                            ? $appInfo[$platform]['size'] 
                            : filesize(__DIR__ . '/apps/' . $platform . '/' . basename($downloadUrls[$platform]));
                        if ($size < 1024) {
                            echo number_format($size) . ' bytes';
                        } elseif ($size < 1024 * 1024) {
                            echo number_format($size / 1024, 2) . ' KB';
                        } else {
                            echo number_format($size / 1024 / 1024, 2) . ' MB';
                        }
                    } else {
                        echo 'Not built yet';
                    }
                    ?>
                </p>
                <?php if ($fileExists[$platform]): ?>
                <a href="<?php echo $downloadUrls[$platform]; ?>" class="download-btn" download>
                    <i class="fas fa-download"></i> Download Now
                </a>
                <?php else: ?>
                <a href="build-<?php echo $platform; ?>.php" class="download-btn" style="background: #666;">
                    <i class="fas fa-tools"></i> Build Instructions
                </a>
                <p style="color: #888; font-size: 12px; margin-top: 10px;">
                    <?php if ($platform === 'android'): ?>
                    Requires Java + Android SDK
                    <?php else: ?>
                    Run build script to create
                    <?php endif; ?>
                </p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>
