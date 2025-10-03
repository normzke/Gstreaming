<?php
require_once 'config/config.php';
require_once 'config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    echo "Checking payments table structure...\n";
    
    $query = "SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'payments' ORDER BY ordinal_position";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $columns = $stmt->fetchAll();
    
    echo "Payments table columns:\n";
    foreach ($columns as $col) {
        echo "- " . $col['column_name'] . " (" . $col['data_type'] . ")\n";
    }
    
    // Check if package_id column exists
    $hasPackageId = false;
    foreach ($columns as $col) {
        if ($col['column_name'] === 'package_id') {
            $hasPackageId = true;
            break;
        }
    }
    
    if (!$hasPackageId) {
        echo "\n❌ package_id column missing. Adding it...\n";
        
        $alterQuery = "ALTER TABLE payments ADD COLUMN package_id INTEGER REFERENCES packages(id)";
        $conn->exec($alterQuery);
        echo "✅ package_id column added successfully!\n";
    } else {
        echo "\n✅ package_id column exists\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
