<?php
require_once '../config/config.php';

// If specific package_id is provided from homepage, persist it for the next step
if (isset($_GET['package_id'])) {
    $_SESSION['selected_package_id'] = (int)$_GET['package_id'];
}

// Capture homepage device/month selections if present
if (isset($_GET['devices'])) {
    $_SESSION['selected_devices'] = max(1, (int)$_GET['devices']);
}
if (isset($_GET['months'])) {
    $_SESSION['selected_months'] = max(1, (int)$_GET['months']);
}

// Redirect to unified package selection page in public directory
header('Location: package-selection.php?from_homepage=1');
exit();
?>
