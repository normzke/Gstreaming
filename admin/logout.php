<?php
require_once '../config/config.php';

// Destroy all session data
session_destroy();

// Clear remember me cookie if it exists
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/', '', true, true);
}

// Clear admin session cookie if it exists
if (isset($_COOKIE['admin_remember_token'])) {
    setcookie('admin_remember_token', '', time() - 3600, '/', '', true, true);
}

// Redirect to admin login page
header('Location: login.php');
exit();
?>
