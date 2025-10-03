<?php
// Test what's actually on the server
echo "=== TESTING SERVER FILE CONTENT ===\n";
echo "Line 8: " . file_get_contents('https://bingetv.co.ke/login.php') . "\n";
?>
