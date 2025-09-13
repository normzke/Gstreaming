<?php
/**
 * GStreaming System Test Script
 * Run this to test all system functionality before deployment
 */

require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/mpesa_integration.php';
require_once 'localhost-config.php';

// Test results storage
$testResults = [];
$passedTests = 0;
$totalTests = 0;

function runTest($testName, $testFunction) {
    global $testResults, $passedTests, $totalTests;
    
    $totalTests++;
    echo "<div class='test-item'>";
    echo "<h3>$testName</h3>";
    
    try {
        $result = $testFunction();
        if ($result['success']) {
            echo "<div class='test-result success'>✓ PASSED</div>";
            echo "<div class='test-message'>{$result['message']}</div>";
            $passedTests++;
        } else {
            echo "<div class='test-result error'>✗ FAILED</div>";
            echo "<div class='test-message'>{$result['message']}</div>";
        }
    } catch (Exception $e) {
        echo "<div class='test-result error'>✗ ERROR</div>";
        echo "<div class='test-message'>Exception: {$e->getMessage()}</div>";
    }
    
    echo "</div>";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GStreaming System Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .header {
            background: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .test-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .test-item {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 10px;
        }
        .test-result {
            font-weight: bold;
            padding: 5px 10px;
            border-radius: 3px;
            display: inline-block;
            margin-bottom: 10px;
        }
        .test-result.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .test-result.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .test-message {
            color: #666;
            font-size: 14px;
        }
        .summary {
            background: #e7f3ff;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
        }
        .setup-instructions {
            background: #fff3cd;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .setup-instructions h3 {
            color: #856404;
            margin-top: 0;
        }
        .setup-instructions ul {
            margin: 10px 0;
        }
        .setup-instructions li {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🚀 GStreaming System Test</h1>
        <p>Comprehensive system testing for localhost deployment</p>
    </div>

    <div class="setup-instructions">
        <h3>📋 Setup Instructions</h3>
        <p><strong>Before running tests, ensure:</strong></p>
        <ul>
            <li>✅ PostgreSQL is installed and running</li>
            <li>✅ Database 'gstreaming_db' is created</li>
            <li>✅ Database schema is imported (database/schema.sql)</li>
            <li>✅ PHP development server is running on port 4000</li>
            <li>✅ M-PESA sandbox credentials are configured</li>
        </ul>
        <p><strong>Start server:</strong> <code>php -S localhost:4000</code></p>
        <p><strong>Access URL:</strong> <a href="http://localhost:4000/GStreaming/">http://localhost:4000/GStreaming/</a></p>
    </div>

    <?php
    // Database Connection Test
    runTest('Database Connection', function() {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            $stmt = $conn->query('SELECT 1');
            return ['success' => true, 'message' => 'Database connection successful'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()];
        }
    });

    // Database Tables Test
    runTest('Database Tables', function() {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $tables = ['users', 'packages', 'channels', 'payments', 'user_subscriptions', 'user_streaming_access', 'mpesa_config', 'admin_users'];
            $missingTables = [];
            
            foreach ($tables as $table) {
                $stmt = $conn->query("SELECT 1 FROM information_schema.tables WHERE table_name = '$table'");
                if (!$stmt->fetch()) {
                    $missingTables[] = $table;
                }
            }
            
            if (empty($missingTables)) {
                return ['success' => true, 'message' => 'All required tables exist'];
            } else {
                return ['success' => false, 'message' => 'Missing tables: ' . implode(', ', $missingTables)];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Table check failed: ' . $e->getMessage()];
        }
    });

    // Sample Data Test
    runTest('Sample Data', function() {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            // Check packages
            $stmt = $conn->query('SELECT COUNT(*) as count FROM packages');
            $packageCount = $stmt->fetch()['count'];
            
            // Check channels
            $stmt = $conn->query('SELECT COUNT(*) as count FROM channels');
            $channelCount = $stmt->fetch()['count'];
            
            // Check admin user
            $stmt = $conn->query('SELECT COUNT(*) as count FROM admin_users');
            $adminCount = $stmt->fetch()['count'];
            
            if ($packageCount >= 4 && $channelCount >= 20 && $adminCount >= 1) {
                return ['success' => true, 'message' => "Sample data loaded: $packageCount packages, $channelCount channels, $adminCount admin users"];
            } else {
                return ['success' => false, 'message' => "Insufficient sample data: $packageCount packages, $channelCount channels, $adminCount admin users"];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Sample data check failed: ' . $e->getMessage()];
        }
    });

    // M-PESA Configuration Test
    runTest('M-PESA Configuration', function() {
        try {
            $mpesaConfig = testMpesaConfig();
            $mpesa = new MpesaIntegration($mpesaConfig);
            $configStatus = $mpesa->getConfigStatus();
            
            if ($configStatus['configured']) {
                return ['success' => true, 'message' => 'M-PESA configuration is complete'];
            } else {
                return ['success' => false, 'message' => 'M-PESA configuration incomplete. Missing: ' . implode(', ', $configStatus['missing'])];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'M-PESA configuration test failed: ' . $e->getMessage()];
        }
    });

    // File Permissions Test
    runTest('File Permissions', function() {
        $requiredFiles = [
            'config/config.php',
            'config/database.php',
            'includes/functions.php',
            'includes/mpesa_integration.php',
            'index.php',
            'subscribe.php',
            'dashboard.php',
            'channels.php',
            'admin/index.php',
            'admin/mpesa-config.php',
            'api/auth/register.php',
            'api/auth/login.php',
            'api/payment/initiate.php',
            'api/payment/status.php',
            'api/subscription/create.php',
            'api/mpesa/callback.php'
        ];
        
        $missingFiles = [];
        foreach ($requiredFiles as $file) {
            if (!file_exists($file)) {
                $missingFiles[] = $file;
            }
        }
        
        if (empty($missingFiles)) {
            return ['success' => true, 'message' => 'All required files exist'];
        } else {
            return ['success' => false, 'message' => 'Missing files: ' . implode(', ', $missingFiles)];
        }
    });

    // PHP Extensions Test
    runTest('PHP Extensions', function() {
        $requiredExtensions = ['pdo', 'pdo_pgsql', 'curl', 'json', 'openssl'];
        $missingExtensions = [];
        
        foreach ($requiredExtensions as $ext) {
            if (!extension_loaded($ext)) {
                $missingExtensions[] = $ext;
            }
        }
        
        if (empty($missingExtensions)) {
            return ['success' => true, 'message' => 'All required PHP extensions are loaded'];
        } else {
            return ['success' => false, 'message' => 'Missing extensions: ' . implode(', ', $missingExtensions)];
        }
    });

    // URL Accessibility Test
    runTest('URL Accessibility', function() {
        $baseUrl = 'http://localhost:4000/GStreaming/';
        $testUrls = [
            'index.php' => 'Homepage',
            'channels.php' => 'Channels page',
            'subscribe.php?package=1' => 'Subscription page',
            'admin/index.php' => 'Admin dashboard'
        ];
        
        $accessibleUrls = 0;
        $totalUrls = count($testUrls);
        
        foreach ($testUrls as $url => $name) {
            $fullUrl = $baseUrl . $url;
            $context = stream_context_create([
                'http' => [
                    'timeout' => 5,
                    'ignore_errors' => true
                ]
            ]);
            
            $response = @file_get_contents($fullUrl, false, $context);
            if ($response !== false && !empty($response)) {
                $accessibleUrls++;
            }
        }
        
        if ($accessibleUrls === $totalUrls) {
            return ['success' => true, 'message' => "All $totalUrls URLs are accessible"];
        } else {
            return ['success' => false, 'message' => "Only $accessibleUrls of $totalUrls URLs are accessible"];
        }
    });

    // API Endpoints Test
    runTest('API Endpoints', function() {
        $baseUrl = 'http://localhost:4000/GStreaming/api/';
        $apiEndpoints = [
            'auth/register.php',
            'auth/login.php',
            'payment/initiate.php',
            'payment/status.php',
            'subscription/create.php',
            'mpesa/callback.php'
        ];
        
        $accessibleApis = 0;
        $totalApis = count($apiEndpoints);
        
        foreach ($apiEndpoints as $endpoint) {
            $fullUrl = $baseUrl . $endpoint;
            $context = stream_context_create([
                'http' => [
                    'timeout' => 5,
                    'ignore_errors' => true
                ]
            ]);
            
            $response = @file_get_contents($fullUrl, false, $context);
            if ($response !== false) {
                $accessibleApis++;
            }
        }
        
        if ($accessibleApis === $totalApis) {
            return ['success' => true, 'message' => "All $totalApis API endpoints are accessible"];
        } else {
            return ['success' => false, 'message' => "Only $accessibleApis of $totalApis API endpoints are accessible"];
        }
    });

    // Admin Panel Test
    runTest('Admin Panel Access', function() {
        $adminUrl = 'http://localhost:4000/GStreaming/admin/';
        $adminPages = [
            'index.php' => 'Dashboard',
            'mpesa-config.php' => 'M-PESA Config',
            'channels.php' => 'Channels Management',
            'packages.php' => 'Packages Management'
        ];
        
        $accessiblePages = 0;
        $totalPages = count($adminPages);
        
        foreach ($adminPages as $page => $name) {
            $fullUrl = $adminUrl . $page;
            $context = stream_context_create([
                'http' => [
                    'timeout' => 5,
                    'ignore_errors' => true
                ]
            ]);
            
            $response = @file_get_contents($fullUrl, false, $context);
            if ($response !== false && !empty($response)) {
                $accessiblePages++;
            }
        }
        
        if ($accessiblePages === $totalPages) {
            return ['success' => true, 'message' => "All $totalPages admin pages are accessible"];
        } else {
            return ['success' => false, 'message' => "Only $accessiblePages of $totalPages admin pages are accessible"];
        }
    });
    ?>

    <div class="summary">
        <?php
        $percentage = $totalTests > 0 ? round(($passedTests / $totalTests) * 100) : 0;
        $status = $percentage >= 80 ? 'success' : ($percentage >= 60 ? 'warning' : 'error');
        ?>
        <h2>📊 Test Summary</h2>
        <p>Passed: <?php echo $passedTests; ?> / <?php echo $totalTests; ?> tests (<?php echo $percentage; ?>%)</p>
        
        <?php if ($percentage >= 80): ?>
            <p style="color: green;">🎉 System is ready for deployment!</p>
            <p><strong>Next Steps:</strong></p>
            <ul style="text-align: left; display: inline-block;">
                <li>✅ Configure M-PESA credentials in admin panel</li>
                <li>✅ Test subscription flow end-to-end</li>
                <li>✅ Test payment processing</li>
                <li>✅ Deploy to production server</li>
            </ul>
        <?php elseif ($percentage >= 60): ?>
            <p style="color: orange;">⚠️ System needs some fixes before deployment</p>
            <p>Please address the failed tests above and run the test again.</p>
        <?php else: ?>
            <p style="color: red;">❌ System has critical issues that need to be fixed</p>
            <p>Please review the failed tests and ensure all requirements are met.</p>
        <?php endif; ?>
    </div>

    <div class="setup-instructions">
        <h3>🔧 Quick Setup Guide</h3>
        <ol>
            <li><strong>Start PHP Server:</strong> <code>php -S localhost:4000</code></li>
            <li><strong>Access Homepage:</strong> <a href="http://localhost:4000/GStreaming/">http://localhost:4000/GStreaming/</a></li>
            <li><strong>Admin Login:</strong> <a href="http://localhost:4000/GStreaming/admin/">http://localhost:4000/GStreaming/admin/</a> (admin/admin123)</li>
            <li><strong>Configure M-PESA:</strong> Admin → M-PESA Config → Enter your sandbox credentials</li>
            <li><strong>Test Subscription:</strong> Homepage → Choose Package → Register → Payment → Streaming Access</li>
        </ol>
    </div>
</body>
</html>
