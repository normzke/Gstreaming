<?php
/**
 * BingeTV Core Functions
 * Essential utility functions for the application
 */

/**
 * Sanitize input data
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current user data
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    global $conn;
    if (!$conn) {
        $db = new Database();
        $conn = $db->getConnection();
    }
    
    $query = "SELECT * FROM users WHERE id = ? AND is_active = true";
    $stmt = $conn->prepare($query);
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Format date for display
 */
function formatDate($date, $format = 'M j, Y') {
    return date($format, strtotime($date));
}

/**
 * Format currency
 */
function formatCurrency($amount, $currency = 'KES') {
    return $currency . ' ' . number_format($amount, 0);
}

/**
 * Generate random string
 */
function generateRandomString($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Send email notification
 */
function sendEmail($to, $subject, $message, $headers = '') {
    if (empty($headers)) {
        $headers = 'From: ' . SITE_EMAIL . "\r\n" .
                   'Reply-To: ' . SITE_EMAIL . "\r\n" .
                   'X-Mailer: PHP/' . phpversion();
    }
    
    return mail($to, $subject, $message, $headers);
}

/**
 * Log activity
 */
function logActivity($userId, $action, $details = '') {
    // Activity logging temporarily disabled per requirements
    return;
    global $conn;
    if (!$conn) {
        $db = new Database();
        $conn = $db->getConnection();
    }

    // Try to log with provided userId; if FK fails (e.g., admin users not in users table), fallback gracefully
    try {
        $query = "INSERT INTO activity_logs (user_id, action, details, created_at) VALUES (?, ?, ?, CURRENT_TIMESTAMP)";
        $stmt = $conn->prepare($query);
        $stmt->execute([$userId, $action, $details]);
    } catch (PDOException $e) {
        // PostgreSQL FK violation SQLSTATE is 23503
        if ($e->getCode() === '23503' || stripos($e->getMessage(), 'foreign key') !== false) {
            try {
                // Attempt to insert without user linkage if schema allows NULL user_id
                $fallback = "INSERT INTO activity_logs (user_id, action, details, created_at) VALUES (NULL, ?, ?, CURRENT_TIMESTAMP)";
                $stmt = $conn->prepare($fallback);
                $stmt->execute([$action, $details]);
            } catch (PDOException $_) {
                // If fallback also fails, swallow to avoid breaking primary flow
            }
        } else {
            // Re-throw unexpected DB errors
            throw $e;
        }
    }
}

/**
 * Get user notifications
 */
function getUserNotifications($userId, $limit = 10) {
    global $conn;
    if (!$conn) {
        $db = new Database();
        $conn = $db->getConnection();
    }
    
    $query = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$userId, $limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Create notification
 */
function createNotification($userId, $title, $message, $type = 'info') {
    global $conn;
    if (!$conn) {
        $db = new Database();
        $conn = $db->getConnection();
    }
    
    $query = "INSERT INTO notifications (user_id, title, message, type, is_read, created_at) VALUES (?, ?, ?, ?, false, CURRENT_TIMESTAMP)";
    $stmt = $conn->prepare($query);
    $stmt->execute([$userId, $title, $message, $type]);
}

/**
 * Validate email
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number
 */
function isValidPhone($phone) {
    // Basic phone validation for Kenyan numbers
    $phone = preg_replace('/[^0-9+]/', '', $phone);
    return preg_match('/^(\+254|0)[0-9]{9}$/', $phone);
}

/**
 * Hash password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Redirect with message
 */
function redirectWithMessage($url, $message, $type = 'info') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
    header('Location: ' . $url);
    exit();
}

/**
 * Get flash message
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'info';
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

/**
 * Check if user has active subscription
 */
function hasActiveSubscription($userId) {
    global $conn;
    if (!$conn) {
        $db = new Database();
        $conn = $db->getConnection();
    }
    
    $query = "SELECT COUNT(*) FROM user_subscriptions WHERE user_id = ? AND status = 'active' AND end_date > NOW()";
    $stmt = $conn->prepare($query);
    $stmt->execute([$userId]);
    return $stmt->fetchColumn() > 0;
}

/**
 * Get user subscription
 */
function getUserSubscription($userId) {
    global $conn;
    if (!$conn) {
        $db = new Database();
        $conn = $db->getConnection();
    }
    
    $query = "SELECT us.*, p.name as package_name, p.price, p.duration_days, p.max_devices 
              FROM user_subscriptions us 
              JOIN packages p ON us.package_id = p.id 
              WHERE us.user_id = ? AND us.status = 'active' 
              ORDER BY us.created_at DESC LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->execute([$userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Clean old sessions
 */
function cleanOldSessions() {
    global $conn;
    if (!$conn) {
        $db = new Database();
        $conn = $db->getConnection();
    }
    
    // Clean old user tokens
    $query = "DELETE FROM user_tokens WHERE expires_at < NOW()";
    $conn->exec($query);
    
    // Clean old activity logs (older than 1 year)
    $query = "DELETE FROM activity_logs WHERE created_at < NOW() - INTERVAL '1 year'";
    $conn->exec($query);
}

/**
 * Get site statistics
 */
function getSiteStats() {
    global $conn;
    if (!$conn) {
        $db = new Database();
        $conn = $db->getConnection();
    }
    
    $stats = [];
    
    // Total users
    $query = "SELECT COUNT(*) FROM users WHERE is_active = true";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $stats['total_users'] = $stmt->fetchColumn();
    
    // Active subscriptions
    $query = "SELECT COUNT(*) FROM user_subscriptions WHERE status = 'active' AND end_date > NOW()";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $stats['active_subscriptions'] = $stmt->fetchColumn();
    
    // Total revenue
    $query = "SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'completed'";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $stats['total_revenue'] = $stmt->fetchColumn();
    
    // Monthly revenue
    $query = "SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'completed' AND DATE_TRUNC('month', created_at) = DATE_TRUNC('month', NOW())";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $stats['monthly_revenue'] = $stmt->fetchColumn();
    
    return $stats;
}

/**
 * Validate required fields
 */
function validateRequired($fields, $data) {
    $errors = [];
    foreach ($fields as $field) {
        if (empty($data[$field])) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required.';
        }
    }
    return $errors;
}

/**
 * Validate email format (alias for isValidEmail for compatibility)
 */
function validateEmail($email) {
    return isValidEmail($email);
}

/**
 * Validate phone number (alias for isValidPhone for compatibility)
 */
function validatePhone($phone) {
    return isValidPhone($phone);
}

/**
 * Display flash message
 */
function displayMessage() {
    $flash = getFlashMessage();
    if ($flash) {
        $type = $flash['type'];
        $message = $flash['message'];
        echo "<div class='alert alert-{$type}'>{$message}</div>";
    }
}

/**
 * Redirect with message
 */
function redirect($url, $message = '') {
    if ($message) {
        redirectWithMessage($url, $message);
    } else {
        header('Location: ' . $url);
        exit();
    }
}

/**
 * Get email template
 */
function getEmailTemplate($template, $data = []) {
    $templatePath = __DIR__ . '/templates/emails/' . $template . '.php';
    
    if (!file_exists($templatePath)) {
        return false;
    }
    
    // Extract variables for template
    extract($data);
    
    ob_start();
    include $templatePath;
    return ob_get_clean();
}

/**
 * Process M-PESA payment
 */
function processMPesaPayment($userId, $packageId, $phone, $amount) {
    global $conn;
    if (!$conn) {
        $db = new Database();
        $conn = $db->getConnection();
    }
    
    try {
        // Create payment record
        $paymentQuery = "INSERT INTO payments (user_id, package_id, amount, payment_method, mpesa_phone, status, created_at) 
                        VALUES (?, ?, ?, 'mpesa', ?, 'pending', CURRENT_TIMESTAMP)";
        $paymentStmt = $conn->prepare($paymentQuery);
        $paymentStmt->execute([$userId, $packageId, $amount, $phone]);
        
        $paymentId = $conn->lastInsertId();
        
        // Generate checkout request ID
        $checkoutRequestId = 'ws_CO_' . time() . '_' . $paymentId;
        
        // Update payment with checkout request ID
        $updateQuery = "UPDATE payments SET mpesa_checkout_request_id = ? WHERE id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->execute([$checkoutRequestId, $paymentId]);
        
        // Log activity
        logActivity($userId, 'payment_initiated', "M-PESA payment initiated for package {$packageId}");
        
        return [
            'success' => true,
            'payment_id' => $paymentId,
            'checkout_request_id' => $checkoutRequestId
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => 'Payment processing failed: ' . $e->getMessage()
        ];
    }
}
?>