<?php
// Remote Migration Runner
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

echo "Starting database migration...\n";

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    // 1. Run tivimate columns migration
    echo "Running 20251231_01_add_tivimate_columns.sql...\n";
    $sql = file_get_contents(__DIR__ . '/../database/migrations/20251231_01_add_tivimate_columns.sql');
    if ($sql) {
        $conn->exec($sql);
        echo "Success!\n";
    } else {
        echo "Error: Could not read file.\n";
    }

    // 2. Run user portal tables migration (photos, support)
    echo "Running 20251230_01_user_portal_tables.sql...\n";
    $sql = file_get_contents(__DIR__ . '/../database/migrations/20251230_01_user_portal_tables.sql');
    if ($sql) {
        $conn->exec($sql);
        echo "Success!\n";
    } else {
        echo "Error: Could not read file.\n";
    }

    echo "\nAll migrations completed successfully.\n";

} catch (Exception $e) {
    echo "Migration Failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>