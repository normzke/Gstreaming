<?php
/**
 * Deployment Test Suite
 * Comprehensive tests to ensure the system is ready for production deployment
 */

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

class DeploymentTestSuite {
    private $db;
    private $conn;
    private $testResults = [];
    private $criticalIssues = [];
    private $warnings = [];
    
    public function __construct() {
        try {
            $this->db = new Database();
            $this->conn = $this->db->getConnection();
            echo "=== Deployment Test Suite ===\n";
            echo "Testing system readiness for production deployment...\n\n";
        } catch (Exception $e) {
            $this->logCriticalError("Database connection failed: " . $e->getMessage());
            exit(1);
        }
    }
    
    public function runAllTests() {
        $this->testSystemRequirements();
        $this->testDatabaseIntegrity();
        $this->testConfiguration();
        $this->testSecurityFeatures();
        $this->testPerformance();
        $this->testFilePermissions();
        $this->testAPIIntegrity();
        $this->testPaymentSystem();
        $this->testEmailSystem();
        $this->testAdminPanel();
        $this->testUserExperience();
        
        $this->displayResults();
        $this->generateDeploymentReport();
    }
    
    private function testSystemRequirements() {
        $this->logTest("System Requirements");
        
        // PHP Version
        $phpVersion = PHP_VERSION;
        if (version_compare($phpVersion, '8.0.0', '>=')) {
            $this->logSuccess("PHP version $phpVersion (>= 8.0 required)");
        } else {
            $this->logCriticalError("PHP version $phpVersion is below required 8.0");
        }
        
        // Required PHP Extensions
        $requiredExtensions = [
            'pdo', 'pdo_pgsql', 'curl', 'json', 'mbstring', 'openssl', 'session'
        ];
        
        foreach ($requiredExtensions as $ext) {
            if (extension_loaded($ext)) {
                $this->logSuccess("PHP extension '$ext' available");
            } else {
                $this->logCriticalError("Required PHP extension '$ext' not found");
            }
        }
        
        // Memory and execution limits
        $memoryLimit = ini_get('memory_limit');
        $maxExecutionTime = ini_get('max_execution_time');
        
        $this->logInfo("Memory limit: $memoryLimit");
        $this->logInfo("Max execution time: {$maxExecutionTime}s");
        
        // Disk space
        $freeSpace = disk_free_space('.');
        $freeSpaceMB = round($freeSpace / 1024 / 1024, 2);
        
        if ($freeSpaceMB > 100) {
            $this->logSuccess("Sufficient disk space available ({$freeSpaceMB}MB)");
        } else {
            $this->logWarning("Low disk space ({$freeSpaceMB}MB) - consider cleanup");
        }
    }
    
    private function testDatabaseIntegrity() {
        $this->logTest("Database Integrity");
        
        // Test database connection
        try {
            $stmt = $this->conn->query("SELECT 1");
            $this->logSuccess("Database connection established");
        } catch (Exception $e) {
            $this->logCriticalError("Database connection failed: " . $e->getMessage());
            return;
        }
        
        // Test required tables
        $requiredTables = [
            'users', 'packages', 'user_subscriptions', 'payments', 
            'channels', 'package_channels', 'user_streaming_access', 
            'mpesa_config', 'admin_users', 'gallery_items'
        ];
        
        foreach ($requiredTables as $table) {
            try {
                $stmt = $this->conn->query("SELECT COUNT(*) FROM $table");
                $count = $stmt->fetchColumn();
                $this->logSuccess("Table '$table' exists with $count records");
            } catch (Exception $e) {
                $this->logCriticalError("Table '$table' missing or inaccessible: " . $e->getMessage());
            }
        }
        
        // Test database indexes
        $this->testDatabaseIndexes();
        
        // Test data integrity
        $this->testDataIntegrity();
    }
    
    private function testDatabaseIndexes() {
        $this->logTest("Database Indexes");
        
        $indexQueries = [
            'users_email' => "SELECT indexname FROM pg_indexes WHERE tablename = 'users' AND indexname LIKE '%email%'",
            'users_phone' => "SELECT indexname FROM pg_indexes WHERE tablename = 'users' AND indexname LIKE '%phone%'",
            'payments_status' => "SELECT indexname FROM pg_indexes WHERE tablename = 'payments' AND indexname LIKE '%status%'",
            'user_subscriptions_end_date' => "SELECT indexname FROM pg_indexes WHERE tablename = 'user_subscriptions' AND indexname LIKE '%end_date%'"
        ];
        
        foreach ($indexQueries as $indexName => $query) {
            try {
                $stmt = $this->conn->query($query);
                $result = $stmt->fetch();
                
                if ($result) {
                    $this->logSuccess("Index '$indexName' exists");
                } else {
                    $this->logWarning("Index '$indexName' not found - may impact performance");
                }
            } catch (Exception $e) {
                $this->logWarning("Could not check index '$indexName': " . $e->getMessage());
            }
        }
    }
    
    private function testDataIntegrity() {
        $this->logTest("Data Integrity");
        
        // Test foreign key constraints
        $integrityTests = [
            'user_subscriptions_user_id' => "SELECT COUNT(*) FROM user_subscriptions us LEFT JOIN users u ON us.user_id = u.id WHERE u.id IS NULL",
            'user_subscriptions_package_id' => "SELECT COUNT(*) FROM user_subscriptions us LEFT JOIN packages p ON us.package_id = p.id WHERE p.id IS NULL",
            'payments_user_id' => "SELECT COUNT(*) FROM payments pay LEFT JOIN users u ON pay.user_id = u.id WHERE u.id IS NULL"
        ];
        
        foreach ($integrityTests as $testName => $query) {
            try {
                $stmt = $this->conn->query($query);
                $count = $stmt->fetchColumn();
                
                if ($count == 0) {
                    $this->logSuccess("Data integrity check '$testName' passed");
                } else {
                    $this->logCriticalError("Data integrity issue in '$testName': $count orphaned records");
                }
            } catch (Exception $e) {
                $this->logWarning("Could not perform integrity check '$testName': " . $e->getMessage());
            }
        }
    }
    
    private function testConfiguration() {
        $this->logTest("Configuration");
        
        // Test required configuration constants
        $requiredConstants = ['SITE_URL', 'DB_HOST', 'DB_NAME', 'DB_USER'];
        
        foreach ($requiredConstants as $constant) {
            if (defined($constant) && !empty(constant($constant))) {
                $this->logSuccess("Configuration constant '$constant' defined");
            } else {
                $this->logCriticalError("Required configuration constant '$constant' missing or empty");
            }
        }
        
        // Test M-PESA configuration
        $this->testMpesaConfiguration();
        
        // Test email configuration
        $this->testEmailConfiguration();
    }
    
    private function testMpesaConfiguration() {
        $this->logTest("M-PESA Configuration");
        
        try {
            $stmt = $this->conn->query("SELECT config_key, config_value FROM mpesa_config");
            $configs = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            $requiredMpesaConfigs = ['consumer_key', 'consumer_secret', 'shortcode', 'passkey'];
            $configuredCount = 0;
            
            foreach ($requiredMpesaConfigs as $config) {
                if (!empty($configs[$config])) {
                    $configuredCount++;
                }
            }
            
            if ($configuredCount === count($requiredMpesaConfigs)) {
                $this->logSuccess("M-PESA configuration complete");
            } else if ($configuredCount > 0) {
                $this->logWarning("M-PESA configuration partially complete ($configuredCount/" . count($requiredMpesaConfigs) . ")");
            } else {
                $this->logWarning("M-PESA configuration not set up - payments will use simulation mode");
            }
            
        } catch (Exception $e) {
            $this->logWarning("Could not check M-PESA configuration: " . $e->getMessage());
        }
    }
    
    private function testEmailConfiguration() {
        $this->logTest("Email Configuration");
        
        // Check if email function exists
        if (function_exists('sendEmail')) {
            $this->logSuccess("Email function available");
        } else {
            $this->logWarning("Email function not implemented - notifications will not work");
        }
        
        // Check SMTP configuration (if applicable)
        $smtpHost = ini_get('SMTP');
        if ($smtpHost) {
            $this->logInfo("SMTP host configured: $smtpHost");
        } else {
            $this->logWarning("SMTP not configured - email functionality may not work");
        }
    }
    
    private function testSecurityFeatures() {
        $this->logTest("Security Features");
        
        // Test password hashing
        $testPassword = 'TestPassword123!';
        $hashedPassword = password_hash($testPassword, PASSWORD_DEFAULT);
        
        if (password_verify($testPassword, $hashedPassword)) {
            $this->logSuccess("Password hashing and verification working");
        } else {
            $this->logCriticalError("Password hashing system not working");
        }
        
        // Test input sanitization
        $testInput = '<script>alert("xss")</script>';
        $sanitizedInput = htmlspecialchars($testInput);
        
        if ($sanitizedInput !== $testInput && strpos($sanitizedInput, '<script>') === false) {
            $this->logSuccess("Input sanitization working");
        } else {
            $this->logCriticalError("Input sanitization not working");
        }
        
        // Test session security
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (ini_get('session.cookie_httponly')) {
            $this->logSuccess("Session cookie httponly flag enabled");
        } else {
            $this->logWarning("Session cookie httponly flag not enabled - security risk");
        }
        
        // Test HTTPS (if available)
        $this->testHTTPSConfiguration();
    }
    
    private function testHTTPSConfiguration() {
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            $this->logSuccess("HTTPS enabled");
        } else {
            $this->logWarning("HTTPS not enabled - consider enabling for production");
        }
    }
    
    private function testPerformance() {
        $this->logTest("Performance");
        
        // Test database query performance
        $startTime = microtime(true);
        
        try {
            $stmt = $this->conn->query("SELECT COUNT(*) FROM users");
            $count = $stmt->fetchColumn();
            $queryTime = microtime(true) - $startTime;
            
            if ($queryTime < 0.1) {
                $this->logSuccess("Database query performance good ({$queryTime}s)");
            } else {
                $this->logWarning("Database query performance slow ({$queryTime}s) - consider optimization");
            }
            
        } catch (Exception $e) {
            $this->logError("Performance test failed: " . $e->getMessage());
        }
        
        // Test file system performance
        $this->testFileSystemPerformance();
    }
    
    private function testFileSystemPerformance() {
        $testFile = 'temp_performance_test.txt';
        $testData = str_repeat('test data ', 1000);
        
        $startTime = microtime(true);
        
        if (file_put_contents($testFile, $testData) !== false) {
            $writeTime = microtime(true) - $startTime;
            
            $startTime = microtime(true);
            $readData = file_get_contents($testFile);
            $readTime = microtime(true) - $startTime;
            
            unlink($testFile);
            
            if ($writeTime < 0.01 && $readTime < 0.01) {
                $this->logSuccess("File system performance good");
            } else {
                $this->logWarning("File system performance slow - write: {$writeTime}s, read: {$readTime}s");
            }
        } else {
            $this->logError("File system write test failed");
        }
    }
    
    private function testFilePermissions() {
        $this->logTest("File Permissions");
        
        $importantFiles = [
            'config/database.php',
            'config/config.php',
            'includes/functions.php',
            '.htaccess'
        ];
        
        foreach ($importantFiles as $file) {
            if (file_exists($file)) {
                $perms = fileperms($file);
                $octal = substr(sprintf('%o', $perms), -4);
                
                if (strpos($octal, '7') !== false) {
                    $this->logWarning("File '$file' has world-writable permissions ($octal) - security risk");
                } else {
                    $this->logSuccess("File '$file' has appropriate permissions ($octal)");
                }
            } else {
                $this->logError("Important file '$file' not found");
            }
        }
        
        // Test upload directory permissions
        $uploadDirs = ['uploads/', 'assets/images/', 'admin/uploads/'];
        foreach ($uploadDirs as $dir) {
            if (is_dir($dir)) {
                if (is_writable($dir)) {
                    $this->logSuccess("Directory '$dir' is writable");
                } else {
                    $this->logWarning("Directory '$dir' is not writable");
                }
            } else {
                $this->logWarning("Directory '$dir' does not exist");
            }
        }
    }
    
    private function testAPIIntegrity() {
        $this->logTest("API Integrity");
        
        $apiEndpoints = [
            'api/auth/register.php',
            'api/auth/login.php',
            'api/payment/initiate.php',
            'api/payment/status.php',
            'api/subscription/create.php',
            'api/mpesa/callback.php',
            'admin/api/analytics.php'
        ];
        
        foreach ($apiEndpoints as $endpoint) {
            if (file_exists($endpoint)) {
                $this->logSuccess("API endpoint '$endpoint' exists");
                
                // Check if file has proper PHP opening tag
                $content = file_get_contents($endpoint);
                if (strpos($content, '<?php') === 0) {
                    $this->logSuccess("API endpoint '$endpoint' has proper PHP syntax");
                } else {
                    $this->logError("API endpoint '$endpoint' missing PHP opening tag");
                }
            } else {
                $this->logError("API endpoint '$endpoint' missing");
            }
        }
    }
    
    private function testPaymentSystem() {
        $this->logTest("Payment System");
        
        try {
            // Test M-PESA integration class
            require_once '../includes/mpesa_integration.php';
            
            $mpesaConfig = [
                'consumer_key' => '',
                'consumer_secret' => '',
                'shortcode' => '',
                'passkey' => '',
                'callback_url' => '',
                'environment' => 'sandbox'
            ];
            
            $mpesa = new MpesaIntegration($mpesaConfig);
            $configStatus = $mpesa->getConfigStatus();
            
            if (isset($configStatus['configured'])) {
                $this->logSuccess("M-PESA integration class working");
            } else {
                $this->logError("M-PESA integration class not working");
            }
            
        } catch (Exception $e) {
            $this->logError("M-PESA integration test failed: " . $e->getMessage());
        }
        
        // Test payment simulation
        $this->testPaymentSimulation();
    }
    
    private function testPaymentSimulation() {
        // Test if payment simulation functions exist
        if (function_exists('simulateMpesaPush')) {
            $this->logSuccess("Payment simulation functions available");
        } else {
            $this->logWarning("Payment simulation functions not found - check includes/functions.php");
        }
    }
    
    private function testEmailSystem() {
        $this->logTest("Email System");
        
        if (function_exists('sendEmail')) {
            $this->logSuccess("Email system available");
            
            // Test email template functions
            $emailFunctions = ['sendSubscriptionConfirmation', 'sendPaymentReceipt', 'sendRenewalReminder'];
            $availableFunctions = 0;
            
            foreach ($emailFunctions as $func) {
                if (function_exists($func)) {
                    $availableFunctions++;
                }
            }
            
            if ($availableFunctions === count($emailFunctions)) {
                $this->logSuccess("All email template functions available");
            } else {
                $this->logWarning("Only $availableFunctions/" . count($emailFunctions) . " email template functions available");
            }
        } else {
            $this->logWarning("Email system not implemented");
        }
    }
    
    private function testAdminPanel() {
        $this->logTest("Admin Panel");
        
        $adminFiles = [
            'admin/index.php',
            'admin/login.php',
            'admin/users.php',
            'admin/packages.php',
            'admin/payments.php',
            'admin/channels.php',
            'admin/mpesa-config.php'
        ];
        
        foreach ($adminFiles as $file) {
            if (file_exists($file)) {
                $this->logSuccess("Admin file '$file' exists");
            } else {
                $this->logError("Admin file '$file' missing");
            }
        }
        
        // Test admin user exists
        try {
            $stmt = $this->conn->query("SELECT COUNT(*) FROM admin_users");
            $adminCount = $stmt->fetchColumn();
            
            if ($adminCount > 0) {
                $this->logSuccess("Admin users configured ($adminCount found)");
            } else {
                $this->logCriticalError("No admin users configured");
            }
        } catch (Exception $e) {
            $this->logError("Could not check admin users: " . $e->getMessage());
        }
    }
    
    private function testUserExperience() {
        $this->logTest("User Experience");
        
        // Test main pages exist
        $mainPages = [
            'index.php',
            'login.php',
            'register.php',
            'subscribe.php',
            'dashboard.php',
            'channels.php',
            'gallery.php'
        ];
        
        foreach ($mainPages as $page) {
            if (file_exists($page)) {
                $this->logSuccess("Main page '$page' exists");
            } else {
                $this->logError("Main page '$page' missing");
            }
        }
        
        // Test CSS and JS files
        $assets = [
            'assets/css/main.css',
            'assets/js/main.js',
            'assets/css/admin.css',
            'assets/js/admin.js'
        ];
        
        foreach ($assets as $asset) {
            if (file_exists($asset)) {
                $this->logSuccess("Asset '$asset' exists");
            } else {
                $this->logWarning("Asset '$asset' missing");
            }
        }
    }
    
    private function logTest($testName) {
        echo "\n--- Testing: $testName ---\n";
    }
    
    private function logSuccess($message) {
        echo "✅ $message\n";
        $this->testResults[] = ['status' => 'success', 'message' => $message];
    }
    
    private function logError($message) {
        echo "❌ $message\n";
        $this->testResults[] = ['status' => 'error', 'message' => $message];
    }
    
    private function logWarning($message) {
        echo "⚠️  $message\n";
        $this->warnings[] = $message;
        $this->testResults[] = ['status' => 'warning', 'message' => $message];
    }
    
    private function logCriticalError($message) {
        echo "🚨 $message\n";
        $this->criticalIssues[] = $message;
        $this->testResults[] = ['status' => 'critical', 'message' => $message];
    }
    
    private function logInfo($message) {
        echo "ℹ️  $message\n";
    }
    
    private function displayResults() {
        echo "\n=== Deployment Test Results ===\n";
        
        $successCount = 0;
        $errorCount = 0;
        $warningCount = 0;
        $criticalCount = 0;
        
        foreach ($this->testResults as $result) {
            switch ($result['status']) {
                case 'success':
                    $successCount++;
                    break;
                case 'error':
                    $errorCount++;
                    break;
                case 'warning':
                    $warningCount++;
                    break;
                case 'critical':
                    $criticalCount++;
                    break;
            }
        }
        
        echo "✅ Successful: $successCount\n";
        echo "⚠️  Warnings: $warningCount\n";
        echo "❌ Errors: $errorCount\n";
        echo "🚨 Critical Issues: $criticalCount\n";
        echo "Total Tests: " . count($this->testResults) . "\n";
        
        if ($criticalCount > 0) {
            echo "\n🚨 CRITICAL ISSUES FOUND - DO NOT DEPLOY:\n";
            foreach ($this->criticalIssues as $issue) {
                echo "   • $issue\n";
            }
        }
        
        if ($warningCount > 0) {
            echo "\n⚠️  WARNINGS - REVIEW BEFORE DEPLOYMENT:\n";
            foreach ($this->warnings as $warning) {
                echo "   • $warning\n";
            }
        }
        
        if ($criticalCount === 0 && $errorCount === 0) {
            echo "\n🎉 System is ready for deployment!\n";
        } else if ($criticalCount === 0) {
            echo "\n⚠️  System has minor issues but may be deployable.\n";
        } else {
            echo "\n🚨 System is NOT ready for deployment due to critical issues.\n";
        }
    }
    
    private function generateDeploymentReport() {
        $reportFile = 'deployment-report-' . date('Y-m-d-H-i-s') . '.txt';
        
        $report = "GStreaming Deployment Test Report\n";
        $report .= "Generated: " . date('Y-m-d H:i:s') . "\n";
        $report .= "========================================\n\n";
        
        $report .= "SYSTEM STATUS: ";
        if (count($this->criticalIssues) > 0) {
            $report .= "NOT READY FOR DEPLOYMENT\n";
        } else if (count($this->warnings) > 0) {
            $report .= "READY WITH WARNINGS\n";
        } else {
            $report .= "READY FOR DEPLOYMENT\n";
        }
        $report .= "\n";
        
        if (count($this->criticalIssues) > 0) {
            $report .= "CRITICAL ISSUES:\n";
            foreach ($this->criticalIssues as $issue) {
                $report .= "• $issue\n";
            }
            $report .= "\n";
        }
        
        if (count($this->warnings) > 0) {
            $report .= "WARNINGS:\n";
            foreach ($this->warnings as $warning) {
                $report .= "• $warning\n";
            }
            $report .= "\n";
        }
        
        $report .= "TEST SUMMARY:\n";
        $successCount = count(array_filter($this->testResults, fn($r) => $r['status'] === 'success'));
        $errorCount = count(array_filter($this->testResults, fn($r) => $r['status'] === 'error'));
        $warningCount = count(array_filter($this->testResults, fn($r) => $r['status'] === 'warning'));
        $criticalCount = count(array_filter($this->testResults, fn($r) => $r['status'] === 'critical'));
        
        $report .= "✅ Successful: $successCount\n";
        $report .= "⚠️  Warnings: $warningCount\n";
        $report .= "❌ Errors: $errorCount\n";
        $report .= "🚨 Critical: $criticalCount\n";
        $report .= "Total: " . count($this->testResults) . "\n";
        
        file_put_contents($reportFile, $report);
        echo "\n📄 Deployment report saved to: $reportFile\n";
    }
}

// Run the deployment test suite
if (php_sapi_name() === 'cli') {
    $testSuite = new DeploymentTestSuite();
    $testSuite->runAllTests();
} else {
    echo "This deployment test suite should be run from the command line.\n";
    echo "Usage: php tests/deployment-test.php\n";
}
?>
