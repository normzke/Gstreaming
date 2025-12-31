<?php
/**
 * Run Migration 009: Add Username Column
 * 
 * This script adds the username column to the users table
 * Safe to run multiple times (uses IF NOT EXISTS)
 */

require_once __DIR__ . '/config/database.php';

echo "<h2>BingeTV Database Migration 009</h2>";
echo "<p>Adding username column to users table...</p>";

$db = new Database();
$conn = $db->getConnection();

if (!$conn) {
    die("<p style='color:red;'>❌ Database connection failed</p>");
}

echo "<p>✅ Database connected</p>";

try {
    // Read migration file
    $migrationFile = __DIR__ . '/database/migrations/009_add_username_column.sql';
    
    if (!file_exists($migrationFile)) {
        throw new Exception("Migration file not found: $migrationFile");
    }
    
    $sql = file_get_contents($migrationFile);
    echo "<p>✅ Migration file loaded</p>";
    
    // Execute migration
    $conn->beginTransaction();
    
    // Split by semicolon and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $executedCount = 0;
    foreach ($statements as $statement) {
        // Skip comments and empty statements
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue;
        }
        
        try {
            $conn->exec($statement);
            $executedCount++;
        } catch (PDOException $e) {
            // Some statements might fail if already exists, that's ok
            echo "<p style='color:orange;'>⚠️ Statement skipped: " . substr($statement, 0, 50) . "...</p>";
        }
    }
    
    $conn->commit();
    
    echo "<p>✅ Executed $executedCount SQL statements</p>";
    
    // Verify username column exists
    $verifyQuery = "SELECT column_name, data_type, is_nullable 
                    FROM information_schema.columns 
                    WHERE table_name = 'users' AND column_name = 'username'";
    $stmt = $conn->query($verifyQuery);
    $column = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($column) {
        echo "<p style='color:green;'>✅ <strong>SUCCESS!</strong> Username column added:</p>";
        echo "<ul>";
        echo "<li>Column: " . $column['column_name'] . "</li>";
        echo "<li>Type: " . $column['data_type'] . "</li>";
        echo "<li>Nullable: " . $column['is_nullable'] . "</li>";
        echo "</ul>";
        
        // Check how many users have usernames
        $countStmt = $conn->query("SELECT COUNT(*) as total FROM users WHERE username IS NOT NULL");
        $count = $countStmt->fetch(PDO::FETCH_ASSOC);
        echo "<p>Users with usernames: " . $count['total'] . "</p>";
        
        echo "<hr>";
        echo "<h3>✅ Migration Complete!</h3>";
        echo "<p>Registration will now work with username field.</p>";
        echo "<p><a href='register.php' style='padding:10px 20px;background:#8B0000;color:white;text-decoration:none;border-radius:5px;'>Go to Registration</a></p>";
        
    } else {
        echo "<p style='color:red;'>❌ Username column not found after migration</p>";
    }
    
} catch (Exception $e) {
    $conn->rollback();
    echo "<p style='color:red;'>❌ Migration failed: " . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . " Line: " . $e->getLine() . "</p>";
}

echo "<hr>";
echo "<p><small>After successful migration, you can delete this file for security.</small></p>";
?>

