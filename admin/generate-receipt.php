<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../lib/functions.php';

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$db = new Database();
$conn = $db->getConnection();

$page_title = 'Generate Receipt';
include 'includes/header.php';
?>

<!-- Generate Receipt Management Content -->
<div class="admin-card">
    <div class="card-header">
        <h3 class="card-title">Generate Receipt</h3>
        <p>Generate and manage payment receipts</p>
    </div>
    <div class="card-body">
        <p>This page is being updated with the new layout. Content will be added here.</p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
