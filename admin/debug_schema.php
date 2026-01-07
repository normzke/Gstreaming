<?php
require_once '../config/config.php';
require_once '../config/database.php';

try {
    // Determine DB Type (Postgres or MySQL)
    // The previous error message "Undefined column: 7 ERROR: column... LINE 3" looks like Postgres style error.
    // Also user context said PostgreSQL.

    // Attempt to fetch columns
    $stmt = $conn->query("SELECT * FROM users LIMIT 1");
    $rowCount = $stmt->rowCount();
    $columnCount = $stmt->columnCount();

    echo "<h1>Database Schema Debug</h1>";
    echo "<p>DB Connection: Success</p>";
    echo "<p>Columns in 'users' table:</p>";
    echo "<ul>";

    for ($i = 0; $i < $columnCount; $i++) {
        $meta = $stmt->getColumnMeta($i);
        echo "<li>" . $meta['name'] . " (" . $meta['native_type'] . ")</li>";
    }
    echo "</ul>";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>