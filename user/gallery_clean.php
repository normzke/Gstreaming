<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../lib/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$page_title = 'Gallery';
include 'includes/header.php';
?>

<!-- Gallery Content -->
<div class="user-card">
    <div class="card-header">
        <h3 class="card-title">Gallery</h3>
        <p>Browse images and media content</p>
    </div>
    <div class="card-body">
        <p>This page is being updated with the new layout. Content will be added here.</p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
