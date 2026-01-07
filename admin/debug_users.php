<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../lib/functions.php';

global $conn;
if (!$conn) {
    if (class_exists('Database')) {
        $conn = Database::getInstance()->getConnection();
    }
}

echo "<h1>Debug Streaming Users Query</h1>";

try {
    $sql = "
SELECT u.*,
COUNT(DISTINCT ud.id) as device_count,
MAX(ud.last_active) as last_device_active
FROM users u
LEFT JOIN user_devices ud ON u.id = ud.user_id AND ud.is_active = TRUE
GROUP BY u.id
ORDER BY u.created_at DESC
    ";

    echo "<pre>$sql</pre>";

    $stmt = $conn->query($sql);
    $users = $stmt->fetchAll();

    echo "<h3>Result Count: " . count($users) . "</h3>";
    echo "<pre>";
    print_r($users);
    echo "</pre>";

} catch (Exception $e) {
    echo "<h2 style='color:red;'>Database Error: " . $e->getMessage() . "</h2>";
}
?>