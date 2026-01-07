<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../lib/functions.php';

header('Content-Type: application/json');

$db = Database::getInstance();
$conn = $db->getConnection();

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$type = $input['type'] ?? '';

if ($type === 'credentials') {
    $username = $input['username'] ?? '';
    $password = $input['password'] ?? '';

    if (empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Username and password required']);
        exit;
    }

    // Check main website credentials
    $stmt = $conn->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND is_active = true");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();

    $isAuthenticated = false;

    if ($user && password_verify($password, $user['password_hash'])) {
        $isAuthenticated = true;
    } else {
        // Fallback: Check TiviMate/Xtream credentials (plaintext)
        $stmtTm = $conn->prepare("SELECT * FROM users WHERE tivimate_username = ? AND is_active = true");
        $stmtTm->execute([$username]);
        $userTm = $stmtTm->fetch();

        if ($userTm && isset($userTm['tivimate_password']) && $userTm['tivimate_password'] === $password) {
            $user = $userTm;
            $isAuthenticated = true;
        }
    }

    if ($isAuthenticated && $user) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user'] = $user;
        $_SESSION['logged_in'] = true;

        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email']
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
    }

} elseif ($type === 'mac') {
    $macAddress = $input['mac_address'] ?? '';

    if (empty($macAddress)) {
        echo json_encode(['success' => false, 'message' => 'MAC address required']);
        exit;
    }

    // Normalize MAC address (remove colons, dashes, spaces)
    $macAddress = strtoupper(preg_replace('/[^A-F0-9]/', '', $macAddress));
    $macFormatted = implode(':', str_split($macAddress, 2));

    $stmt = $conn->prepare("SELECT * FROM users WHERE (mac_address = ? OR mac_address = ?) AND is_active = true");
    $stmt->execute([$macAddress, $macFormatted]);
    $user = $stmt->fetch();

    if ($user) {
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user'] = $user;
        $_SESSION['logged_in'] = true;

        echo json_encode([
            'success' => true,
            'message' => 'MAC authentication successful',
            'user' => [
                'id' => $user['id'],
                'username' => $user['username']
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid MAC address']);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid authentication type']);
}
?>