<?php
require_once __DIR__ . '/../config/config.php';

// Destroy all session data
session_unset();
session_destroy();

// Clear remember me cookie if it exists
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/', '', true, true);
}

// Redirect to public home page
header('Location: /login');
session_write_close();
exit();
?>