<?php
require_once 'config/config.php';
require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

echo "<h2>Checking Table Structures</h2>";

// Check mpesa_config table structure
echo "<h3>mpesa_config table structure:</h3>";
try {
    $query = "SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'mpesa_config' ORDER BY ordinal_position";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $columns = $stmt->fetchAll();
    
    if (empty($columns)) {
        echo "<p style='color: red;'>Table mpesa_config does not exist</p>";
    } else {
        echo "<table border='1'><tr><th>Column</th><th>Type</th></tr>";
        foreach ($columns as $col) {
            echo "<tr><td>{$col['column_name']}</td><td>{$col['data_type']}</td></tr>";
        }
        echo "</table>";
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

// Check social_media_config table structure
echo "<h3>social_media_config table structure:</h3>";
try {
    $query = "SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'social_media_config' ORDER BY ordinal_position";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $columns = $stmt->fetchAll();
    
    if (empty($columns)) {
        echo "<p style='color: red;'>Table social_media_config does not exist</p>";
    } else {
        echo "<table border='1'><tr><th>Column</th><th>Type</th></tr>";
        foreach ($columns as $col) {
            echo "<tr><td>{$col['column_name']}</td><td>{$col['data_type']}</td></tr>";
        }
        echo "</table>";
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
