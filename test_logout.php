<?php
// Test logout functionality
echo "<h2>Testing Logout Functionality</h2>";

echo "<h3>Admin Logout Test:</h3>";
echo "<p><a href='admin/logout.php' target='_blank'>Test Admin Logout</a></p>";
echo "<p>Expected: Redirect to admin/login.php</p>";

echo "<h3>User Logout Test:</h3>";
echo "<p><a href='user/logout.php' target='_blank'>Test User Logout</a></p>";
echo "<p>Expected: Redirect to public/index.php</p>";

echo "<h3>Logout Files Status:</h3>";
echo "<p>Admin logout.php: " . (file_exists('admin/logout.php') ? '✅ Exists' : '❌ Missing') . "</p>";
echo "<p>User logout.php: " . (file_exists('user/logout.php') ? '✅ Exists' : '❌ Missing') . "</p>";

echo "<h3>File Contents Check:</h3>";
echo "<h4>Admin logout.php:</h4>";
echo "<pre>" . htmlspecialchars(file_get_contents('admin/logout.php')) . "</pre>";

echo "<h4>User logout.php:</h4>";
echo "<pre>" . htmlspecialchars(file_get_contents('user/logout.php')) . "</pre>";
?>
