<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';

// Clear the remember me cookie if it exists
if (isset($_COOKIE['remember_admin'])) {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    // Clear the remember token from database
    if (isset($_SESSION['admin_id'])) {
        $stmt = $conn->prepare("UPDATE admin_users SET remember_token = NULL, token_expires_at = NULL WHERE id = ?");
        $stmt->execute([$_SESSION['admin_id']]);
    }

    // Clear the cookie
    setcookie('remember_admin', '', time() - 3600, '/', $_SERVER['HTTP_HOST'], true, true);
}

// Destroy all session data
session_unset();
session_destroy();

// Redirect to login page
session_write_close();
header('Location: /admin/login');
exit();
?>