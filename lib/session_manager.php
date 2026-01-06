<?php
/**
 * Session Management Functions
 * Handles device tracking and concurrent session limits
 */

/**
 * Create or update user session
 * Returns session token or false if device limit exceeded
 */
function createUserSession($userId, $deviceId, $deviceName = '', $deviceType = 'web')
{
    global $conn;
    if (!$conn) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
    }

    // Get user's package limits
    $subscription = getUserSubscription($userId);
    if (!$subscription) {
        return ['success' => false, 'error' => 'No active subscription'];
    }

    $maxDevices = $subscription['max_devices'] ?? 1;

    // Clean expired sessions first
    cleanExpiredSessions();

    // Check if device already has a session
    $checkQuery = "SELECT session_token FROM user_sessions 
                   WHERE user_id = ? AND device_id = ? AND is_active = true 
                   AND expires_at > NOW()";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->execute([$userId, $deviceId]);
    $existingSession = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if ($existingSession) {
        // Update existing session
        $updateQuery = "UPDATE user_sessions 
                       SET last_activity = NOW(), 
                           expires_at = NOW() + INTERVAL '7 days',
                           ip_address = ?,
                           user_agent = ?
                       WHERE session_token = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->execute([
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? '',
            $existingSession['session_token']
        ]);

        return [
            'success' => true,
            'session_token' => $existingSession['session_token'],
            'message' => 'Session refreshed'
        ];
    }

    // Count active sessions
    $countQuery = "SELECT COUNT(*) FROM user_sessions 
                   WHERE user_id = ? AND is_active = true 
                   AND expires_at > NOW() 
                   AND last_activity > NOW() - INTERVAL '30 minutes'";
    $countStmt = $conn->prepare($countQuery);
    $countStmt->execute([$userId]);
    $activeCount = $countStmt->fetchColumn();

    // Check device limit
    if ($activeCount >= $maxDevices) {
        return [
            'success' => false,
            'error' => "Device limit reached. Your plan allows {$maxDevices} device(s). Please logout from another device or upgrade your plan.",
            'active_devices' => $activeCount,
            'max_devices' => $maxDevices
        ];
    }

    // Create new session
    $sessionToken = bin2hex(random_bytes(32));
    $insertQuery = "INSERT INTO user_sessions 
                    (user_id, session_token, device_id, device_name, device_type, 
                     ip_address, user_agent, last_activity, expires_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW() + INTERVAL '7 days')";
    $insertStmt = $conn->prepare($insertQuery);
    $insertStmt->execute([
        $userId,
        $sessionToken,
        $deviceId,
        $deviceName,
        $deviceType,
        $_SERVER['REMOTE_ADDR'] ?? '',
        $_SERVER['HTTP_USER_AGENT'] ?? ''
    ]);

    logActivity($userId, 'session_created', "New session from {$deviceType}: {$deviceName}");

    return [
        'success' => true,
        'session_token' => $sessionToken,
        'message' => 'Session created successfully'
    ];
}

/**
 * Validate session token
 */
function validateSession($sessionToken)
{
    global $conn;
    if (!$conn) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
    }

    $query = "SELECT us.*, u.username, u.email 
              FROM user_sessions us
              JOIN users u ON us.user_id = u.id
              WHERE us.session_token = ? 
              AND us.is_active = true 
              AND us.expires_at > NOW()
              AND us.last_activity > NOW() - INTERVAL '30 minutes'";
    $stmt = $conn->prepare($query);
    $stmt->execute([$sessionToken]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($session) {
        // Update last activity
        $updateQuery = "UPDATE user_sessions SET last_activity = NOW() WHERE session_token = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->execute([$sessionToken]);

        return $session;
    }

    return false;
}

/**
 * Terminate session
 */
function terminateSession($sessionToken)
{
    global $conn;
    if (!$conn) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
    }

    $query = "UPDATE user_sessions SET is_active = false WHERE session_token = ?";
    $stmt = $conn->prepare($query);
    return $stmt->execute([$sessionToken]);
}

/**
 * Get active sessions for user
 */
function getUserActiveSessions($userId)
{
    global $conn;
    if (!$conn) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
    }

    $query = "SELECT id, device_name, device_type, ip_address, last_activity, created_at
              FROM user_sessions 
              WHERE user_id = ? AND is_active = true 
              AND expires_at > NOW()
              ORDER BY last_activity DESC";
    $stmt = $conn->prepare($query);
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Terminate specific device session
 */
function terminateDeviceSession($userId, $sessionId)
{
    global $conn;
    if (!$conn) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
    }

    $query = "UPDATE user_sessions SET is_active = false 
              WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($query);
    return $stmt->execute([$sessionId, $userId]);
}

/**
 * Clean expired sessions (called periodically)
 */
function cleanExpiredSessions()
{
    global $conn;
    if (!$conn) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
    }

    $query = "DELETE FROM user_sessions 
              WHERE expires_at < NOW() 
              OR last_activity < NOW() - INTERVAL '24 hours'";
    return $conn->exec($query);
}

/**
 * Generate device ID from request
 */
function getDeviceId()
{
    // Try to get MAC address from request (for native apps)
    if (isset($_GET['mac']) && !empty($_GET['mac'])) {
        return sanitizeInput($_GET['mac']);
    }

    // For web browsers, create fingerprint from user agent + IP
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

    // Create a hash-based device ID
    return hash('sha256', $userAgent . $ip);
}

/**
 * Get device type from user agent
 */
function getDeviceType()
{
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

    if (stripos($userAgent, 'Android') !== false && stripos($userAgent, 'TV') !== false) {
        return 'android';
    } elseif (stripos($userAgent, 'Tizen') !== false) {
        return 'tizen';
    } elseif (stripos($userAgent, 'webOS') !== false) {
        return 'webos';
    } elseif (stripos($userAgent, 'Mobile') !== false) {
        return 'mobile';
    } else {
        return 'web';
    }
}
?>