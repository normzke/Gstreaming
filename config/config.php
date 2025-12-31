<?php
/**
 * Application Configuration
 * 
 * Security and environment settings for BingeTV
 */

// Ensure errors are displayed in development
if (!defined('ENVIRONMENT')) {
    $isLocalhost = (isset($_SERVER['HTTP_HOST']) &&
        (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false ||
            strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false));
    define('ENVIRONMENT', $isLocalhost ? 'development' : 'production');
}

// Error reporting based on environment
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');

// Create logs directory if it doesn't exist
if (!is_dir(__DIR__ . '/../logs')) {
    mkdir(__DIR__ . '/../logs', 0755, true);
}

// Log startup errors
error_log("BingeTV application started at " . date('Y-m-d H:i:s'));

// Auto-detect localhost mode
if (!defined('IS_LOCALHOST')) {
    $isLocalhost = (isset($_SERVER['HTTP_HOST']) &&
        (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false ||
            strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false));
    define('IS_LOCALHOST', $isLocalhost);
}

// Set security headers
function setSecurityHeaders()
{
    // Prevent clickjacking
    header('X-Frame-Options: DENY');

    // Enable XSS protection
    header('X-XSS-Protection: 1; mode=block');

    // Prevent MIME type sniffing
    header('X-Content-Type-Options: nosniff');

    // Referrer policy
    header('Referrer-Policy: strict-origin-when-cross-origin');

    // Content Security Policy
    $csp = [
        "default-src 'self' https:;",
        "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://code.jquery.com https://cdnjs.cloudflare.com;",
        "style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://fonts.googleapis.com;",
        "img-src 'self' data: https:;",
        "font-src 'self' https://cdnjs.cloudflare.com https://fonts.gstatic.com;",
        "connect-src 'self' https://api.mpesa.co.ke;",
        "frame-src 'self' https://www.youtube.com https://youtube.com;",
        "frame-ancestors 'none';",
        "form-action 'self';",
        "base-uri 'self';",
    ];

    header("Content-Security-Policy: " . implode(' ', $csp));

    // Permissions Policy (replaces Feature-Policy)
    header("Permissions-Policy: geolocation=(), microphone=(), camera=()");

    // HSTS (only on production with HTTPS)
    if (!headers_sent() && !IS_LOCALHOST && isset($_SERVER['HTTPS'])) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
    }
}

// Set security headers
setSecurityHeaders();

// Site Configuration
define('SITE_NAME', 'BingeTV');


// Set base URL
if (!defined('SITE_URL')) {
    if (IS_LOCALHOST) {
        define('SITE_URL', 'http://localhost:4000/BingeTV');
    } else {
        define('SITE_URL', 'https://' . $_SERVER['HTTP_HOST']);
    }
}

// Set timezone
date_default_timezone_set('Africa/Nairobi');

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', !IS_LOCALHOST);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_lifetime', 0);
ini_set('session.gc_maxlifetime', 3600); // 1 hour

// Use custom session save path
$sessionSavePath = __DIR__ . '/../sessions';
if (!is_dir($sessionSavePath)) {
    mkdir($sessionSavePath, 0700, true);
}
ini_set('session.save_path', $sessionSavePath);

// Start secure session
function startSecureSession()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start([
            'cookie_httponly' => true,
            'cookie_secure' => !IS_LOCALHOST,
            'cookie_samesite' => 'Strict',
            'use_strict_mode' => true,
            'use_only_cookies' => true,
            'cookie_lifetime' => 0,
            'gc_maxlifetime' => 3600
        ]);
    }
}

startSecureSession();

define('SITE_EMAIL', 'support@bingetv.co.ke');

// Database Configuration
define('DB_HOST', '/var/run/postgresql');
define('DB_NAME', 'fieldte5_bingetv');
define('DB_USER', 'fieldte5_bingetv1');
define('DB_PASSWORD', 'Normas@4340');
define('DB_PORT', '5432');

// Alternative constant names for compatibility
define('DB_DATABASE', DB_NAME);
define('DB_USERNAME', DB_USER);

// PDO options
$pdo_options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::ATTR_PERSISTENT => true,
    PDO::ATTR_TIMEOUT => 30
];

// Create database connection
try {
    $dsn = "pgsql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";port=" . DB_PORT;
    $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, $pdo_options);
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    if (ENVIRONMENT === 'development') {
        die("Database connection failed: " . $e->getMessage());
    } else {
        die("A database error occurred. Please try again later.");
    }
}

// M-PESA Configuration
define('MPESA_ENVIRONMENT', ENVIRONMENT === 'production' ? 'production' : 'sandbox');

// Load M-PESA credentials from environment or use defaults
if (file_exists(__DIR__ . '/mpesa_config.php')) {
    require_once __DIR__ . '/mpesa_config.php';
} else {
    // Default values (should be overridden in production)
    define('MPESA_CONSUMER_KEY', getenv('MPESA_CONSUMER_KEY') ?: 'your_consumer_key');
    define('MPESA_CONSUMER_SECRET', getenv('MPESA_CONSUMER_SECRET') ?: 'your_consumer_secret');
    define('MPESA_SHORTCODE', getenv('MPESA_SHORTCODE') ?: 'your_shortcode');
    define('MPESA_PASSKEY', getenv('MPESA_PASSKEY') ?: 'your_passkey');
    define('MPESA_TILL_NUMBER', getenv('MPESA_TILL_NUMBER') ?: 'your_till_number');
    define('MPESA_PAYBILL_NUMBER', getenv('MPESA_PAYBILL_NUMBER') ?: 'your_paybill_number');
}

// M-PESA API Endpoints
define('MPESA_AUTH_URL', MPESA_ENVIRONMENT === 'production' ?
    'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials' :
    'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials');

define('MPESA_STK_PUSH_URL', MPESA_ENVIRONMENT === 'production' ?
    'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest' :
    'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest');

// Email Configuration
define('SMTP_HOST', 'mail.bingetv.co.ke');
define('SMTP_PORT', 465);
define('SMTP_USERNAME', 'support@bingetv.co.ke');
define('SMTP_PASSWORD', 'Normas@4340');
define('SMTP_ENCRYPTION', 'ssl');
define('SMTP_FROM_EMAIL', 'support@bingetv.co.ke');
define('SMTP_FROM_NAME', 'BingeTV Support');

// Email Templates Directory
define('EMAIL_TEMPLATES_DIR', __DIR__ . '/../emails/');

// Email sending function
function sendEmail($to, $subject, $template, $data = [])
{
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=utf-8',
        'From: ' . SMTP_FROM_NAME . ' <' . SMTP_FROM_EMAIL . '>',
        'Reply-To: ' . SMTP_FROM_EMAIL,
        'X-Mailer: PHP/' . phpversion()
    ];

    // Load email template
    $templateFile = EMAIL_TEMPLATES_DIR . $template . '.php';
    if (!file_exists($templateFile)) {
        error_log("Email template not found: " . $template);
        return false;
    }

    // Extract variables for the template
    extract($data);

    // Start output buffering
    ob_start();
    include $templateFile;
    $message = ob_get_clean();

    // Send email
    return mail($to, $subject, $message, implode("\r\n", $headers));
}


// Security
define('JWT_SECRET', 'your_jwt_secret_key');
define('ENCRYPTION_KEY', 'your_encryption_key');

// File Upload
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 10485760); // 10MB

// Timezone already set above
// date_default_timezone_set('Africa/Nairobi');

// Error Reporting already handled at the top based on ENVIRONMENT
// error_reporting(0);
// ini_set('display_errors', 0);

// Session already started via startSecureSession() function call above
// if (session_status() === PHP_SESSION_NONE) {
//     session_start();
// }
?>