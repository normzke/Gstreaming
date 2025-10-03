<?php
require_once '../config/config.php';

// Destroy all session data
session_destroy();

// Clear remember me cookie if it exists
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/', '', true, true);
}

// Redirect to public home page
header('Location: ../public/index.php');
exit();
?>
