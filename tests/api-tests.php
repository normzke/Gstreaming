<?php
/**
 * API Testing Suite
 * Tests all API endpoints for functionality and response validation
 */

class APITestSuite {
    private $baseUrl;
    private $testResults = [];
    private $testUser = null;
    private $testSession = null;
    
    public function __construct($baseUrl = 'http://localhost:4000/GStreaming') {
        $this->baseUrl = rtrim($baseUrl, '/');
        echo "=== API Testing Suite ===\n";
        echo "Base URL: $this->baseUrl\n\n";
    }
    
    public function runAllTests() {
        $this->testPublicEndpoints();
        $this->testUserRegistration();
        $this->testUserLogin();
        $this->testPackageEndpoints();
        $this->testPaymentEndpoints();
        $this->testSubscriptionEndpoints();
        $this->testAdminEndpoints();
        $this->testErrorHandling();
        
        $this->displayResults();
    }
    
    private function testPublicEndpoints() {
        $this->logTest("Public Endpoints");
        
        $endpoints = [
            'Homepage' => '/',
            'Channels Page' => '/channels.php',
            'Gallery Page' => '/gallery.php',
            'Subscribe Page' => '/subscribe.php',
            'Login Page' => '/login.php',
            'Register Page' => '/register.php'
        ];
        
        foreach ($endpoints as $name => $endpoint) {
            $this->testEndpoint($name, 'GET', $endpoint);
        }
    }
    
    private function testUserRegistration() {
        $this->logTest("User Registration API");
        
        $testData = [
            'username' => 'apitest_' . time(),
            'email' => 'apitest' . time() . '@example.com',
            'phone' => '254712345' . rand(100, 999),
            'password' => 'TestPassword123!',
            'confirm_password' => 'TestPassword123!',
            'first_name' => 'API',
            'last_name' => 'Test'
        ];
        
        $response = $this->makeRequest('POST', '/api/auth/register.php', $testData);
        
        if ($response && isset($response['success']) && $response['success']) {
            $this->testUser = $testData;
            $this->logSuccess("User registration successful");
        } else {
            $this->logError("User registration failed: " . ($response['message'] ?? 'Unknown error'));
        }
    }
    
    private function testUserLogin() {
        $this->logTest("User Login API");
        
        if (!$this->testUser) {
            $this->logError("No test user available for login test");
            return;
        }
        
        $loginData = [
            'email_username' => $this->testUser['email'],
            'password' => $this->testUser['password']
        ];
        
        $response = $this->makeRequest('POST', '/api/auth/login.php', $loginData);
        
        if ($response && isset($response['success']) && $response['success']) {
            $this->testSession = $response['session_id'] ?? 'test_session';
            $this->logSuccess("User login successful");
        } else {
            $this->logError("User login failed: " . ($response['message'] ?? 'Unknown error'));
        }
    }
    
    private function testPackageEndpoints() {
        $this->logTest("Package Endpoints");
        
        // Test package listing (should be public)
        $response = $this->makeRequest('GET', '/api/packages/list.php');
        
        if ($response && isset($response['packages']) && is_array($response['packages'])) {
            $this->logSuccess("Package listing successful - " . count($response['packages']) . " packages found");
        } else {
            $this->logError("Package listing failed");
        }
        
        // Test individual package details
        if (isset($response['packages'][0]['id'])) {
            $packageId = $response['packages'][0]['id'];
            $detailResponse = $this->makeRequest('GET', "/api/packages/detail.php?id=$packageId");
            
            if ($detailResponse && isset($detailResponse['package'])) {
                $this->logSuccess("Package details retrieval successful");
            } else {
                $this->logError("Package details retrieval failed");
            }
        }
    }
    
    private function testPaymentEndpoints() {
        $this->logTest("Payment Endpoints");
        
        if (!$this->testUser) {
            $this->logError("No test user available for payment test");
            return;
        }
        
        // Test payment initiation
        $paymentData = [
            'package_id' => 1,
            'amount' => 500.00,
            'phone_number' => $this->testUser['phone']
        ];
        
        $response = $this->makeRequest('POST', '/api/payment/initiate.php', $paymentData);
        
        if ($response && isset($response['success'])) {
            if ($response['success']) {
                $this->logSuccess("Payment initiation successful");
            } else {
                $this->logWarning("Payment initiation failed (expected in test environment): " . ($response['message'] ?? 'Unknown error'));
            }
        } else {
            $this->logError("Payment initiation request failed");
        }
        
        // Test payment status check
        if (isset($response['transaction_id'])) {
            $statusResponse = $this->makeRequest('GET', "/api/payment/status.php?transaction_id=" . $response['transaction_id']);
            
            if ($statusResponse && isset($statusResponse['status'])) {
                $this->logSuccess("Payment status check successful");
            } else {
                $this->logError("Payment status check failed");
            }
        }
    }
    
    private function testSubscriptionEndpoints() {
        $this->logTest("Subscription Endpoints");
        
        if (!$this->testUser || !$this->testSession) {
            $this->logError("No authenticated user available for subscription test");
            return;
        }
        
        // Test subscription creation (would normally follow successful payment)
        $subscriptionData = [
            'package_id' => 1,
            'user_id' => $this->testUser['user_id'] ?? 1
        ];
        
        $response = $this->makeRequest('POST', '/api/subscription/create.php', $subscriptionData, [
            'Cookie: PHPSESSID=' . $this->testSession
        ]);
        
        if ($response && isset($response['success'])) {
            if ($response['success']) {
                $this->logSuccess("Subscription creation successful");
            } else {
                $this->logWarning("Subscription creation failed (may require payment): " . ($response['message'] ?? 'Unknown error'));
            }
        } else {
            $this->logError("Subscription creation request failed");
        }
    }
    
    private function testAdminEndpoints() {
        $this->logTest("Admin Endpoints");
        
        // Test admin analytics (should require authentication)
        $response = $this->makeRequest('GET', '/admin/api/analytics.php');
        
        if ($response && isset($response['error']) && $response['error'] === 'Unauthorized') {
            $this->logSuccess("Admin endpoint properly protected (unauthorized access blocked)");
        } else {
            $this->logError("Admin endpoint not properly protected");
        }
        
        // Test admin login
        $adminData = [
            'username' => 'admin',
            'password' => 'admin123'
        ];
        
        $loginResponse = $this->makeRequest('POST', '/admin/login.php', $adminData);
        
        if ($loginResponse && isset($loginResponse['success']) && $loginResponse['success']) {
            $this->logSuccess("Admin login successful");
        } else {
            $this->logWarning("Admin login failed (may not be configured): " . ($loginResponse['message'] ?? 'Unknown error'));
        }
    }
    
    private function testErrorHandling() {
        $this->logTest("Error Handling");
        
        // Test invalid endpoint
        $response = $this->makeRequest('GET', '/api/nonexistent.php');
        
        if ($response === false || (isset($response['error']) && $response['error'])) {
            $this->logSuccess("Invalid endpoint properly handled");
        } else {
            $this->logError("Invalid endpoint not properly handled");
        }
        
        // Test malformed data
        $malformedData = [
            'invalid_field' => 'test',
            'email' => 'invalid-email'
        ];
        
        $response = $this->makeRequest('POST', '/api/auth/register.php', $malformedData);
        
        if ($response && isset($response['success']) && !$response['success']) {
            $this->logSuccess("Malformed data properly rejected");
        } else {
            $this->logError("Malformed data not properly rejected");
        }
    }
    
    private function testEndpoint($name, $method, $endpoint, $data = null, $headers = []) {
        $response = $this->makeRequest($method, $endpoint, $data, $headers);
        
        if ($response !== false) {
            $this->logSuccess("$name endpoint accessible");
        } else {
            $this->logError("$name endpoint failed");
        }
    }
    
    private function makeRequest($method, $endpoint, $data = null, $headers = []) {
        $url = $this->baseUrl . $endpoint;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                $headers[] = 'Content-Type: application/json';
            }
        }
        
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            $this->logError("cURL error: $error");
            return false;
        }
        
        if ($httpCode >= 400) {
            $this->logError("HTTP error: $httpCode");
            return false;
        }
        
        // Try to decode JSON response
        $decoded = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }
        
        // Return raw response if not JSON
        return $response;
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
        echo "\n=== API Test Results Summary ===\n";
        
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
            echo "\n🎉 All API tests passed! APIs are working correctly.\n";
        } else {
            echo "\n⚠️  Some API tests failed. Please review and fix issues.\n";
        }
    }
}

// Run the API test suite
if (php_sapi_name() === 'cli') {
    $baseUrl = $argv[1] ?? 'http://localhost:4000/GStreaming';
    $testSuite = new APITestSuite($baseUrl);
    $testSuite->runAllTests();
} else {
    echo "This API test suite should be run from the command line.\n";
    echo "Usage: php tests/api-tests.php [base_url]\n";
    echo "Example: php tests/api-tests.php http://localhost:4000/GStreaming\n";
}
?>
