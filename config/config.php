<?php
/**
 * Application Configuration
 */

// Site Configuration
define('SITE_NAME', 'BingeTV');

// Auto-detect localhost mode
$isLocalhost = (isset($_SERVER['HTTP_HOST']) && 
                (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || 
                 strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false));

if ($isLocalhost) {
    define('SITE_URL', 'http://localhost:4000/BingeTV');
    define('LOCALHOST_MODE', true);
} else {
    define('SITE_URL', 'https://bingetv.co.ke');
    define('LOCALHOST_MODE', false);
}

define('SITE_EMAIL', 'support@bingetv.co.ke');

// Database Configuration (prefer local socket for cPanel)
define('DB_HOST', '/var/run/postgresql');
define('DB_NAME', 'fieldte5_bingetv');
define('DB_USER', 'fieldte5_bingetv1');
define('DB_PASSWORD', 'Normas@4340');
define('DB_PORT', '5432');

// Alternative constant names for compatibility
define('DB_DATABASE', 'fieldte5_bingetv');
define('DB_USERNAME', 'fieldte5_bingetv1');

// M-PESA Configuration
define('MPESA_ENVIRONMENT', 'sandbox'); // sandbox or production
define('MPESA_CONSUMER_KEY', 'your_consumer_key');
define('MPESA_CONSUMER_SECRET', 'your_consumer_secret');
define('MPESA_SHORTCODE', 'your_shortcode');
define('MPESA_PASSKEY', 'your_passkey');
define('MPESA_TILL_NUMBER', 'your_till_number');
define('MPESA_PAYBILL_NUMBER', 'your_paybill_number');

// Email Configuration
define('SMTP_HOST', 'mail.bingetv.co.ke');
define('SMTP_PORT', 465);
define('SMTP_USERNAME', 'support@bingetv.co.ke');
define('SMTP_PASSWORD', 'Normas@4340');
define('SMTP_ENCRYPTION', 'ssl');
define('SMTP_FROM_EMAIL', 'support@bingetv.co.ke');
define('SMTP_FROM_NAME', 'BingeTV Support');

// Security
define('JWT_SECRET', 'your_jwt_secret_key');
define('ENCRYPTION_KEY', 'your_encryption_key');

// File Upload
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 10485760); // 10MB

// Timezone
date_default_timezone_set('Africa/Nairobi');

// Error Reporting (disable in production)
error_reporting(0);
ini_set('display_errors', 0);

// Start session
if (session_status() === PHP_SESSION_NONE) { session_start(); }
?>
