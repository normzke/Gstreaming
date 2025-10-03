<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../lib/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$page_title = 'Support';
include 'includes/header.php';
?>

<!-- Support Content -->
<div class="user-card">
    <div class="card-header">
        <h3 class="card-title">Support</h3>
        <p>Get help and support for your account</p>
    </div>
    <div class="card-body">
        <p>This page is being updated with the new layout. Content will be added here.</p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
