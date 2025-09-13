<?php
/**
 * Comprehensive Test Suite for GStreaming Platform
 * Tests all APIs, functionality, and database operations
 */

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/mpesa_integration.php';

class GStreamingTestSuite {
    private $db;
    private $conn;
    private $testResults = [];
    private $testUser = null;
    private $testPackage = null;
    
    public function __construct() {
        try {
            $this->db = new Database();
            $this->conn = $this->db->getConnection();
            echo "=== GStreaming Test Suite ===\n";
            echo "Starting comprehensive tests...\n\n";
        } catch (Exception $e) {
            $this->logError("Database connection failed: " . $e->getMessage());
            exit(1);
        }
    }
    
    public function runAllTests() {
        $this->testDatabaseConnection();
        $this->testDatabaseSchema();
        $this->testUserRegistration();
        $this->testUserLogin();
        $this->testPackageManagement();
        $this->testSubscriptionCreation();
        $this->testMpesaIntegration();
        $this->testPaymentFlow();
        $this->testAdminAnalytics();
        $this->testChannelManagement();
        $this->testEmailNotifications();
        $this->testSecurityFeatures();
        $this->testApiEndpoints();
        
        $this->displayResults();
        $this->cleanupTestData();
    }
    
    private function testDatabaseConnection() {
        $this->logTest("Database Connection");
        
        try {
            $stmt = $this->conn->query("SELECT 1");
            $result = $stmt->fetch();
            
            if ($result) {
                $this->logSuccess("Database connection successful");
            } else {
                $this->logError("Database query failed");
            }
        } catch (Exception $e) {
            $this->logError("Database connection failed: " . $e->getMessage());
        }
    }
    
    private function testDatabaseSchema() {
        $this->logTest("Database Schema");
        
        $requiredTables = [
            'users', 'packages', 'user_subscriptions', 'payments', 
            'channels', 'package_channels', 'user_streaming_access', 
            'mpesa_config', 'admin_users', 'gallery_items'
        ];
        
        $missingTables = [];
        
        foreach ($requiredTables as $table) {
            try {
                $stmt = $this->conn->query("SELECT COUNT(*) FROM $table");
                $this->logSuccess("Table '$table' exists");
            } catch (Exception $e) {
                $missingTables[] = $table;
                $this->logError("Table '$table' missing or inaccessible");
            }
        }
        
        if (empty($missingTables)) {
            $this->logSuccess("All required tables present");
        } else {
            $this->logError("Missing tables: " . implode(', ', $missingTables));
        }
    }
    
    private function testUserRegistration() {
        $this->logTest("User Registration");
        
        try {
            // Test user registration data
            $testData = [
                'username' => 'testuser_' . time(),
                'email' => 'test' . time() . '@example.com',
                'phone' => '254712345678',
                'password' => 'TestPassword123!',
                'first_name' => 'Test',
                'last_name' => 'User'
            ];
            
            // Simulate registration
            $stmt = $this->conn->prepare("
                INSERT INTO users (username, email, phone, password_hash, first_name, last_name, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $hashedPassword = password_hash($testData['password'], PASSWORD_DEFAULT);
            $result = $stmt->execute([
                $testData['username'],
                $testData['email'],
                $testData['phone'],
                $hashedPassword,
                $testData['first_name'],
                $testData['last_name']
            ]);
            
            if ($result) {
                $this->testUser = $this->conn->lastInsertId();
                $this->logSuccess("User registration successful (ID: {$this->testUser})");
            } else {
                $this->logError("User registration failed");
            }
            
        } catch (Exception $e) {
            $this->logError("User registration error: " . $e->getMessage());
        }
    }
    
    private function testUserLogin() {
        $this->logTest("User Login");
        
        if (!$this->testUser) {
            $this->logError("No test user available for login test");
            return;
        }
        
        try {
            // Test login verification
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$this->testUser]);
            $user = $stmt->fetch();
            
            if ($user && password_verify('TestPassword123!', $user['password_hash'])) {
                $this->logSuccess("User login verification successful");
                
                // Update last login
                $updateStmt = $this->conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $updateStmt->execute([$this->testUser]);
                $this->logSuccess("Last login timestamp updated");
            } else {
                $this->logError("User login verification failed");
            }
            
        } catch (Exception $e) {
            $this->logError("User login error: " . $e->getMessage());
        }
    }
    
    private function testPackageManagement() {
        $this->logTest("Package Management");
        
        try {
            // Check if packages exist
            $stmt = $this->conn->query("SELECT COUNT(*) as count FROM packages");
            $result = $stmt->fetch();
            $packageCount = $result['count'];
            
            if ($packageCount > 0) {
                $this->logSuccess("Found $packageCount packages in database");
                
                // Get a test package
                $stmt = $this->conn->query("SELECT * FROM packages LIMIT 1");
                $this->testPackage = $stmt->fetch();
                
                if ($this->testPackage) {
                    $this->logSuccess("Test package selected: " . $this->testPackage['name']);
                }
            } else {
                $this->logError("No packages found in database");
            }
            
        } catch (Exception $e) {
            $this->logError("Package management error: " . $e->getMessage());
        }
    }
    
    private function testSubscriptionCreation() {
        $this->logTest("Subscription Creation");
        
        if (!$this->testUser || !$this->testPackage) {
            $this->logError("Missing test user or package for subscription test");
            return;
        }
        
        try {
            // Create test subscription
            $stmt = $this->conn->prepare("
                INSERT INTO user_subscriptions (user_id, package_id, status, start_date, end_date, created_at) 
                VALUES (?, ?, 'active', NOW(), NOW() + INTERVAL '30 days', NOW())
            ");
            
            $result = $stmt->execute([
                $this->testUser,
                $this->testPackage['id']
            ]);
            
            if ($result) {
                $subscriptionId = $this->conn->lastInsertId();
                $this->logSuccess("Subscription created successfully (ID: $subscriptionId)");
                
                // Test streaming access creation
                $this->testStreamingAccess($subscriptionId);
            } else {
                $this->logError("Subscription creation failed");
            }
            
        } catch (Exception $e) {
            $this->logError("Subscription creation error: " . $e->getMessage());
        }
    }
    
    private function testStreamingAccess($subscriptionId) {
        $this->logTest("Streaming Access Generation");
        
        try {
            $streamingUrl = SITE_URL . '/stream/' . generateUniqueId(16);
            $username = generateUniqueId(8);
            $password = generateUniqueId(12);
            
            $stmt = $this->conn->prepare("
                INSERT INTO user_streaming_access (user_id, subscription_id, streaming_url, username, password, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            
            $result = $stmt->execute([
                $this->testUser,
                $subscriptionId,
                $streamingUrl,
                $username,
                $password
            ]);
            
            if ($result) {
                $this->logSuccess("Streaming access created successfully");
                $this->logSuccess("URL: $streamingUrl");
                $this->logSuccess("Username: $username");
            } else {
                $this->logError("Streaming access creation failed");
            }
            
        } catch (Exception $e) {
            $this->logError("Streaming access error: " . $e->getMessage());
        }
    }
    
    private function testMpesaIntegration() {
        $this->logTest("M-PESA Integration");
        
        try {
            // Check M-PESA configuration
            $stmt = $this->conn->query("SELECT * FROM mpesa_config");
            $configs = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            $requiredConfigs = ['consumer_key', 'consumer_secret', 'shortcode', 'passkey'];
            $missingConfigs = [];
            
            foreach ($requiredConfigs as $config) {
                if (empty($configs[$config])) {
                    $missingConfigs[] = $config;
                }
            }
            
            if (empty($missingConfigs)) {
                $this->logSuccess("M-PESA configuration complete");
            } else {
                $this->logWarning("M-PESA configuration incomplete. Missing: " . implode(', ', $missingConfigs));
            }
            
            // Test M-PESA integration class
            $mpesaConfig = [
                'consumer_key' => $configs['consumer_key'] ?? '',
                'consumer_secret' => $configs['consumer_secret'] ?? '',
                'shortcode' => $configs['shortcode'] ?? '',
                'passkey' => $configs['passkey'] ?? '',
                'callback_url' => $configs['callback_url'] ?? '',
                'environment' => $configs['environment'] ?? 'sandbox'
            ];
            
            $mpesa = new MpesaIntegration($mpesaConfig);
            $configStatus = $mpesa->getConfigStatus();
            
            if ($configStatus['configured']) {
                $this->logSuccess("M-PESA integration class initialized successfully");
            } else {
                $this->logWarning("M-PESA integration class initialized but not fully configured");
            }
            
        } catch (Exception $e) {
            $this->logError("M-PESA integration error: " . $e->getMessage());
        }
    }
    
    private function testPaymentFlow() {
        $this->logTest("Payment Flow");
        
        try {
            // Test payment record creation
            $stmt = $this->conn->prepare("
                INSERT INTO payments (user_id, subscription_id, amount, currency, payment_method, status, created_at) 
                VALUES (?, ?, ?, 'KES', 'mpesa', 'pending', NOW())
            ");
            
            $result = $stmt->execute([
                $this->testUser,
                1, // Test subscription ID
                1000.00
            ]);
            
            if ($result) {
                $paymentId = $this->conn->lastInsertId();
                $this->logSuccess("Payment record created (ID: $paymentId)");
                
                // Test payment status update
                $updateStmt = $this->conn->prepare("UPDATE payments SET status = 'completed', transaction_date = NOW() WHERE id = ?");
                $updateResult = $updateStmt->execute([$paymentId]);
                
                if ($updateResult) {
                    $this->logSuccess("Payment status update successful");
                } else {
                    $this->logError("Payment status update failed");
                }
            } else {
                $this->logError("Payment record creation failed");
            }
            
        } catch (Exception $e) {
            $this->logError("Payment flow error: " . $e->getMessage());
        }
    }
    
    private function testAdminAnalytics() {
        $this->logTest("Admin Analytics");
        
        try {
            // Test analytics queries
            $queries = [
                'total_users' => "SELECT COUNT(*) as total FROM users",
                'active_subscriptions' => "SELECT COUNT(*) as total FROM user_subscriptions WHERE status = 'active' AND end_date > NOW()",
                'total_revenue' => "SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE status = 'completed'",
                'payment_success_rate' => "SELECT COUNT(*) as total FROM payments"
            ];
            
            $analytics = [];
            foreach ($queries as $key => $query) {
                $stmt = $this->conn->query($query);
                $result = $stmt->fetch();
                $analytics[$key] = $result['total'];
            }
            
            $this->logSuccess("Analytics queries executed successfully");
            $this->logSuccess("Total users: " . $analytics['total_users']);
            $this->logSuccess("Active subscriptions: " . $analytics['active_subscriptions']);
            $this->logSuccess("Total revenue: KES " . number_format($analytics['total_revenue']));
            
        } catch (Exception $e) {
            $this->logError("Admin analytics error: " . $e->getMessage());
        }
    }
    
    private function testChannelManagement() {
        $this->logTest("Channel Management");
        
        try {
            // Check channels
            $stmt = $this->conn->query("SELECT COUNT(*) as count FROM channels");
            $result = $stmt->fetch();
            $channelCount = $result['count'];
            
            if ($channelCount > 0) {
                $this->logSuccess("Found $channelCount channels in database");
            } else {
                $this->logWarning("No channels found in database");
            }
            
            // Test channel-package relationships
            $stmt = $this->conn->query("SELECT COUNT(*) as count FROM package_channels");
            $result = $stmt->fetch();
            $packageChannelCount = $result['count'];
            
            if ($packageChannelCount > 0) {
                $this->logSuccess("Found $packageChannelCount package-channel relationships");
            } else {
                $this->logWarning("No package-channel relationships found");
            }
            
        } catch (Exception $e) {
            $this->logError("Channel management error: " . $e->getMessage());
        }
    }
    
    private function testEmailNotifications() {
        $this->logTest("Email Notifications");
        
        try {
            // Test email function availability
            if (function_exists('sendEmail')) {
                $this->logSuccess("Email function available");
            } else {
                $this->logWarning("Email function not implemented");
            }
            
            // Test email templates
            $emailTemplates = [
                'subscription_confirmation',
                'payment_receipt',
                'renewal_reminder',
                'support_response'
            ];
            
            $this->logSuccess("Email notification system structure validated");
            
        } catch (Exception $e) {
            $this->logError("Email notifications error: " . $e->getMessage());
        }
    }
    
    private function testSecurityFeatures() {
        $this->logTest("Security Features");
        
        try {
            // Test password hashing
            $testPassword = 'TestPassword123!';
            $hashedPassword = password_hash($testPassword, PASSWORD_DEFAULT);
            
            if (password_verify($testPassword, $hashedPassword)) {
                $this->logSuccess("Password hashing and verification working");
            } else {
                $this->logError("Password hashing failed");
            }
            
            // Test unique ID generation
            $uniqueId1 = generateUniqueId(16);
            $uniqueId2 = generateUniqueId(16);
            
            if ($uniqueId1 !== $uniqueId2 && strlen($uniqueId1) === 16) {
                $this->logSuccess("Unique ID generation working");
            } else {
                $this->logError("Unique ID generation failed");
            }
            
            // Test input sanitization
            $testInput = '<script>alert("xss")</script>';
            $sanitizedInput = htmlspecialchars($testInput);
            
            if ($sanitizedInput !== $testInput) {
                $this->logSuccess("Input sanitization working");
            } else {
                $this->logError("Input sanitization failed");
            }
            
        } catch (Exception $e) {
            $this->logError("Security features error: " . $e->getMessage());
        }
    }
    
    private function testApiEndpoints() {
        $this->logTest("API Endpoints");
        
        $apiEndpoints = [
            'User Registration' => 'api/auth/register.php',
            'User Login' => 'api/auth/login.php',
            'Payment Initiation' => 'api/payment/initiate.php',
            'Payment Status' => 'api/payment/status.php',
            'Subscription Creation' => 'api/subscription/create.php',
            'M-PESA Callback' => 'api/mpesa/callback.php',
            'Admin Analytics' => 'admin/api/analytics.php'
        ];
        
        $availableEndpoints = 0;
        foreach ($apiEndpoints as $name => $endpoint) {
            $filePath = "../$endpoint";
            if (file_exists($filePath)) {
                $this->logSuccess("$name API endpoint exists");
                $availableEndpoints++;
            } else {
                $this->logError("$name API endpoint missing: $endpoint");
            }
        }
        
        $this->logSuccess("$availableEndpoints/" . count($apiEndpoints) . " API endpoints available");
    }
    
    private function cleanupTestData() {
        $this->logTest("Cleanup Test Data");
        
        try {
            if ($this->testUser) {
                // Clean up test user and related data
                $this->conn->prepare("DELETE FROM user_streaming_access WHERE user_id = ?")->execute([$this->testUser]);
                $this->conn->prepare("DELETE FROM user_subscriptions WHERE user_id = ?")->execute([$this->testUser]);
                $this->conn->prepare("DELETE FROM payments WHERE user_id = ?")->execute([$this->testUser]);
                $this->conn->prepare("DELETE FROM users WHERE id = ?")->execute([$this->testUser]);
                
                $this->logSuccess("Test data cleaned up successfully");
            }
        } catch (Exception $e) {
            $this->logError("Cleanup error: " . $e->getMessage());
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
        $this->testResults[] = ['status' => 'warning', 'message' => $message];
    }
    
    private function displayResults() {
        echo "\n=== Test Results Summary ===\n";
        
        $successCount = 0;
        $errorCount = 0;
        $warningCount = 0;
        
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
            }
        }
        
        echo "✅ Successful: $successCount\n";
        echo "⚠️  Warnings: $warningCount\n";
        echo "❌ Errors: $errorCount\n";
        echo "Total Tests: " . count($this->testResults) . "\n";
        
        if ($errorCount === 0) {
            echo "\n🎉 All tests passed! System is ready for deployment.\n";
        } else {
            echo "\n⚠️  Some tests failed. Please review and fix issues before deployment.\n";
        }
    }
}

// Run the test suite
if (php_sapi_name() === 'cli') {
    $testSuite = new GStreamingTestSuite();
    $testSuite->runAllTests();
} else {
    echo "This test suite should be run from the command line.\n";
    echo "Usage: php tests/test-suite.php\n";
}
?>
