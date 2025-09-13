<?php
/**
 * Localhost Configuration for GStreaming
 * Use this file for local development and testing on port 4000
 */

// Localhost-specific configuration
define('LOCALHOST_MODE', true);
define('BASE_URL', 'http://localhost:4000/GStreaming/');
define('DB_HOST', 'localhost');
define('DB_NAME', 'gstreaming_db');
define('DB_USER', 'postgres'); // Change to your PostgreSQL username
define('DB_PASSWORD', 'password'); // Change to your PostgreSQL password

// M-PESA Sandbox Configuration for Testing
define('MPESA_CONSUMER_KEY', 'your_sandbox_consumer_key');
define('MPESA_CONSUMER_SECRET', 'your_sandbox_consumer_secret');
define('MPESA_SHORTCODE', '174379'); // Sandbox shortcode
define('MPESA_PASSKEY', 'your_sandbox_passkey');
define('MPESA_CALLBACK_URL', 'http://localhost:4000/GStreaming/api/mpesa/callback.php');
define('MPESA_INITIATOR_NAME', 'testapi');
define('MPESA_SECURITY_CREDENTIAL', 'your_encrypted_password');

// Email Configuration (for testing)
define('SMTP_HOST', 'localhost');
define('SMTP_PORT', 1025); // MailHog port for testing
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');
define('SMTP_FROM_EMAIL', 'noreply@gstreaming.local');
define('SMTP_FROM_NAME', 'GStreaming');

// Development settings
define('DEBUG_MODE', true);
define('LOG_ERRORS', true);
define('DISPLAY_ERRORS', true);

// Test phone numbers for M-PESA sandbox
define('TEST_PHONE_1', '254708374149');
define('TEST_PHONE_2', '254712345678');

// Override main config if in localhost mode
if (LOCALHOST_MODE) {
    // Update the main config constants
    if (!defined('DB_HOST')) {
        define('DB_HOST', DB_HOST);
        define('DB_NAME', DB_NAME);
        define('DB_USER', DB_USER);
        define('DB_PASSWORD', DB_PASSWORD);
    }
    
    if (!defined('BASE_URL')) {
        define('BASE_URL', BASE_URL);
    }
}

// Development helper functions
function debug_log($message, $data = null) {
    if (DEBUG_MODE) {
        $logMessage = date('Y-m-d H:i:s') . ' - ' . $message;
        if ($data !== null) {
            $logMessage .= ' - Data: ' . json_encode($data);
        }
        error_log($logMessage);
    }
}

function is_localhost() {
    return LOCALHOST_MODE;
}

function get_test_phone() {
    return TEST_PHONE_1;
}

// Override error reporting for development
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
}

// Database connection override for localhost
function getLocalhostConnection() {
    try {
        $pdo = new PDO("pgsql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        if (DEBUG_MODE) {
            die("Localhost Database connection failed: " . $e->getMessage());
        } else {
            die("Database connection failed");
        }
    }
}

// Test M-PESA configuration
function testMpesaConfig() {
    return [
        'consumer_key' => MPESA_CONSUMER_KEY,
        'consumer_secret' => MPESA_CONSUMER_SECRET,
        'shortcode' => MPESA_SHORTCODE,
        'passkey' => MPESA_PASSKEY,
        'callback_url' => MPESA_CALLBACK_URL,
        'environment' => 'sandbox',
        'test_phone' => get_test_phone()
    ];
}

// Sample data for testing
function getSampleChannels() {
    return [
        [
            'name' => 'Citizen TV',
            'description' => 'Kenya\'s leading news and entertainment channel',
            'logo_url' => 'https://logos-world.net/wp-content/uploads/2021/03/Citizen-TV-Logo.png',
            'category' => 'News',
            'country' => 'Kenya',
            'language' => 'English',
            'is_hd' => true,
            'is_active' => true,
            'sort_order' => 1
        ],
        [
            'name' => 'KBC TV',
            'description' => 'Kenya Broadcasting Corporation',
            'logo_url' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/8e/KBC_TV_logo.svg/1200px-KBC_TV_logo.svg.png',
            'category' => 'News',
            'country' => 'Kenya',
            'language' => 'English',
            'is_hd' => true,
            'is_active' => true,
            'sort_order' => 2
        ],
        [
            'name' => 'BBC World News',
            'description' => 'Global news and current affairs',
            'logo_url' => 'https://logos-world.net/wp-content/uploads/2021/03/BBC-World-News-Logo.png',
            'category' => 'News',
            'country' => 'UK',
            'language' => 'English',
            'is_hd' => true,
            'is_active' => true,
            'sort_order' => 10
        ]
    ];
}

// Development setup instructions
function getSetupInstructions() {
    return [
        'database' => [
            '1. Install PostgreSQL',
            '2. Create database: gstreaming_db',
            '3. Run database/schema.sql',
            '4. Update DB credentials in localhost-config.php'
        ],
        'mpesa' => [
            '1. Register at https://developer.safaricom.co.ke/',
            '2. Create a sandbox app',
            '3. Get Consumer Key, Secret, and Passkey',
            '4. Update M-PESA credentials in localhost-config.php'
        ],
        'server' => [
            '1. Start PHP development server: php -S localhost:4000',
            '2. Or use XAMPP/WAMP with port 4000',
            '3. Access: http://localhost:4000/GStreaming/',
            '4. Admin: http://localhost:4000/GStreaming/admin/ (admin/admin123)'
        ]
    ];
}
?>
