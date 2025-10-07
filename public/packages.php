<?php
require_once '../config/config.php';

// If specific package_id is provided from homepage, persist it for the next step
if (isset($_GET['package_id'])) {
    $_SESSION['selected_package_id'] = (int)$_GET['package_id'];
}

// Redirect to unified package selection page in user portal
header('Location: ../user/package-selection.php?from_homepage=1');
exit();
?>
