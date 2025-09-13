<?php
/**
 * GStreaming - Core Functions
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

/**
 * Hash password securely
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
 * Generate secure random token
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

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
 * Validate email address
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Validate phone number (Kenyan format)
 */
function validatePhone($phone) {
    // Remove any non-digit characters
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Check if it's a valid Kenyan phone number
    if (preg_match('/^(254|0)(7|1)[0-9]{8}$/', $phone)) {
        // Convert to 254 format
        if (substr($phone, 0, 1) === '0') {
            $phone = '254' . substr($phone, 1);
        }
        return $phone;
    }
    
    return false;
}

/**
 * Generate unique user ID
 */
function generateUserId() {
    return 'USR' . time() . rand(1000, 9999);
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
    
    $db = new Database();
    $conn = $db->getConnection();
    
    $query = "SELECT * FROM users WHERE id = :user_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    
    return $stmt->fetch();
}

/**
 * Get user subscription
 */
function getUserSubscription($userId) {
    $db = new Database();
    $conn = $db->getConnection();
    
    $query = "SELECT us.*, p.name as package_name, p.price, p.features 
              FROM user_subscriptions us 
              JOIN packages p ON us.package_id = p.id 
              WHERE us.user_id = :user_id 
              AND us.status = 'active' 
              AND us.end_date > NOW() 
              ORDER BY us.end_date DESC 
              LIMIT 1";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();
    
    return $stmt->fetch();
}

/**
 * Check if subscription is active
 */
function isSubscriptionActive($userId) {
    $subscription = getUserSubscription($userId);
    return $subscription !== false;
}

/**
 * Format currency (Kenyan Shillings)
 */
function formatCurrency($amount) {
    return 'KES ' . number_format($amount, 2);
}

/**
 * Format date
 */
function formatDate($date, $format = 'Y-m-d H:i:s') {
    return date($format, strtotime($date));
}

/**
 * Send email notification
 */
function sendEmail($to, $subject, $body, $isHTML = true) {
    // Simple email sending function
    // In production, use PHPMailer or similar
    
    $headers = "From: " . SITE_EMAIL . "\r\n";
    $headers .= "Reply-To: " . SITE_EMAIL . "\r\n";
    
    if ($isHTML) {
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    }
    
    return mail($to, $subject, $body, $headers);
}

/**
 * Log activity
 */
function logActivity($userId, $action, $details = '') {
    $db = new Database();
    $conn = $db->getConnection();
    
    $query = "INSERT INTO activity_logs (user_id, action, details, ip_address, user_agent) 
              VALUES (:user_id, :action, :details, :ip_address, :user_agent)";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $userId);
    $stmt->bindParam(':action', $action);
    $stmt->bindParam(':details', $details);
    $stmt->bindParam(':ip_address', $_SERVER['REMOTE_ADDR']);
    $stmt->bindParam(':user_agent', $_SERVER['HTTP_USER_AGENT']);
    
    return $stmt->execute();
}

/**
 * Create notification
 */
function createNotification($userId, $type, $title, $message) {
    $db = new Database();
    $conn = $db->getConnection();
    
    $query = "INSERT INTO notifications (user_id, type, title, message) 
              VALUES (:user_id, :type, :title, :message)";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $userId);
    $stmt->bindParam(':type', $type);
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':message', $message);
    
    return $stmt->execute();
}

/**
 * Get user notifications
 */
function getUserNotifications($userId, $limit = 10) {
    $db = new Database();
    $conn = $db->getConnection();
    
    $query = "SELECT * FROM notifications 
              WHERE user_id = :user_id 
              ORDER BY created_at DESC 
              LIMIT :limit";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $userId);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll();
}

/**
 * Mark notification as read
 */
function markNotificationAsRead($notificationId) {
    $db = new Database();
    $conn = $db->getConnection();
    
    $query = "UPDATE notifications SET is_read = true WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $notificationId);
    
    return $stmt->execute();
}

/**
 * Check for expiring subscriptions
 */
function checkExpiringSubscriptions() {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Check subscriptions expiring in 3 days
    $query = "SELECT us.*, u.email, u.first_name, p.name as package_name, p.price 
              FROM user_subscriptions us 
              JOIN users u ON us.user_id = u.id 
              JOIN packages p ON us.package_id = p.id 
              WHERE us.status = 'active' 
              AND us.end_date BETWEEN NOW() AND NOW() + INTERVAL '3 days'
              AND us.auto_renewal = true";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    $expiringSubscriptions = $stmt->fetchAll();
    
    foreach ($expiringSubscriptions as $subscription) {
        // Create notification
        createNotification(
            $subscription['user_id'],
            'renewal',
            'Subscription Renewal Reminder',
            "Your {$subscription['package_name']} subscription expires on " . formatDate($subscription['end_date'], 'F j, Y') . ". Renew now to continue enjoying uninterrupted streaming!"
        );
        
        // Send email reminder
        $emailBody = getEmailTemplate('renewal_reminder', [
            'first_name' => $subscription['first_name'],
            'expiry_date' => formatDate($subscription['end_date'], 'F j, Y'),
            'package_name' => $subscription['package_name'],
            'price' => $subscription['price'],
            'currency' => 'KES'
        ]);
        
        sendEmail($subscription['email'], 'Subscription Renewal Reminder', $emailBody);
    }
    
    return count($expiringSubscriptions);
}

/**
 * Get email template
 */
function getEmailTemplate($templateName, $variables = []) {
    $db = new Database();
    $conn = $db->getConnection();
    
    $query = "SELECT * FROM email_templates WHERE name = :name";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':name', $templateName);
    $stmt->execute();
    
    $template = $stmt->fetch();
    
    if (!$template) {
        return false;
    }
    
    $body = $template['body'];
    
    // Replace variables
    foreach ($variables as $key => $value) {
        $body = str_replace('{{' . $key . '}}', $value, $body);
    }
    
    return $body;
}

/**
 * Upload file
 */
function uploadFile($file, $directory = 'uploads/') {
    $uploadPath = UPLOAD_PATH . $directory;
    
    if (!is_dir($uploadPath)) {
        mkdir($uploadPath, 0755, true);
    }
    
    $fileName = time() . '_' . basename($file['name']);
    $targetPath = $uploadPath . $fileName;
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $directory . $fileName;
    }
    
    return false;
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
function redirect($url, $message = '', $type = 'success') {
    if ($message) {
        $_SESSION['message'] = $message;
        $_SESSION['message_type'] = $type;
    }
    header('Location: ' . $url);
    exit();
}

/**
 * Display message
 */
function displayMessage() {
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $type = $_SESSION['message_type'] ?? 'success';
        
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
        
        $alertClass = $type === 'error' ? 'alert-danger' : 'alert-success';
        
        echo "<div class='alert {$alertClass}' role='alert'>{$message}</div>";
    }
}

/**
 * Validate required fields
 */
function validateRequired($fields, $data) {
    $errors = [];
    
    foreach ($fields as $field) {
        if (!isset($data[$field]) || empty(trim($data[$field]))) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
        }
    }
    
    return $errors;
}

/**
 * Get client IP address
 */
function getClientIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

/**
 * Check if request is AJAX
 */
function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * JSON response
 */
function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

/**
 * Generate invoice number
 */
function generateInvoiceNumber() {
    return 'INV-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
}

/**
 * Generate receipt number
 */
function generateReceiptNumber() {
    return 'RCP-' . date('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
}
?>
