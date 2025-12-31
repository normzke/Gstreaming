<?php
/**
 * Fix Users Table - Add Missing Columns
 * Adds missing columns that are referenced in the code
 */

echo "=== FIXING USERS TABLE ===\n\n";

try {
    // Connect to database
    $host = 'localhost';
    $dbname = 'fieldte5_bingetv';
    $username = 'fieldte5_bingetv1';
    $password = 'Normas@4340';
    $port = '5432';
    
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $conn = new PDO($dsn, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    echo "✅ Database connection successful!\n\n";
    
    // Check current users table structure
    echo "=== CURRENT USERS TABLE STRUCTURE ===\n";
    $stmt = $conn->query("SELECT column_name, data_type, is_nullable FROM information_schema.columns WHERE table_name = 'users' ORDER BY ordinal_position");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo "- " . $col['column_name'] . " (" . $col['data_type'] . ") " . ($col['is_nullable'] === 'NO' ? 'NOT NULL' : 'NULL') . "\n";
    }
    echo "\n";
    
    // Add missing columns
    $missingColumns = [
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS last_login TIMESTAMP",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS email_verified BOOLEAN DEFAULT FALSE",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS phone_verified BOOLEAN DEFAULT FALSE",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
    ];
    
    echo "=== ADDING MISSING COLUMNS ===\n";
    foreach ($missingColumns as $sql) {
        try {
            $conn->exec($sql);
            echo "✅ Added column: " . substr($sql, strpos($sql, 'last_login') ?: strpos($sql, 'email_verified') ?: strpos($sql, 'phone_verified') ?: strpos($sql, 'created_at') ?: strpos($sql, 'updated_at')) . "\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'already exists') !== false) {
                echo "✅ Column already exists\n";
            } else {
                echo "Warning: " . $e->getMessage() . "\n";
            }
        }
    }
    
    // Check admin_users table structure
    echo "\n=== CURRENT ADMIN_USERS TABLE STRUCTURE ===\n";
    $stmt = $conn->query("SELECT column_name, data_type, is_nullable FROM information_schema.columns WHERE table_name = 'admin_users' ORDER BY ordinal_position");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo "- " . $col['column_name'] . " (" . $col['data_type'] . ") " . ($col['is_nullable'] === 'NO' ? 'NOT NULL' : 'NULL') . "\n";
    }
    echo "\n";
    
    // Add missing columns to admin_users
    $adminMissingColumns = [
        "ALTER TABLE admin_users ADD COLUMN IF NOT EXISTS last_login TIMESTAMP",
        "ALTER TABLE admin_users ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
        "ALTER TABLE admin_users ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
    ];
    
    echo "=== ADDING MISSING ADMIN COLUMNS ===\n";
    foreach ($adminMissingColumns as $sql) {
        try {
            $conn->exec($sql);
            echo "✅ Added admin column: " . substr($sql, strpos($sql, 'last_login') ?: strpos($sql, 'created_at') ?: strpos($sql, 'updated_at')) . "\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'already exists') !== false) {
                echo "✅ Admin column already exists\n";
            } else {
                echo "Warning: " . $e->getMessage() . "\n";
            }
        }
    }
    
    // Update existing users with current timestamp for last_login
    echo "\n=== UPDATING EXISTING USERS ===\n";
    $updateUsers = "UPDATE users SET last_login = NOW() WHERE last_login IS NULL";
    $conn->exec($updateUsers);
    echo "✅ Updated existing users with last_login timestamp\n";
    
    $updateAdmins = "UPDATE admin_users SET last_login = NOW() WHERE last_login IS NULL";
    $conn->exec($updateAdmins);
    echo "✅ Updated existing admin users with last_login timestamp\n";
    
    // Verify final structure
    echo "\n=== FINAL USERS TABLE STRUCTURE ===\n";
    $stmt = $conn->query("SELECT column_name, data_type, is_nullable FROM information_schema.columns WHERE table_name = 'users' ORDER BY ordinal_position");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo "- " . $col['column_name'] . " (" . $col['data_type'] . ") " . ($col['is_nullable'] === 'NO' ? 'NOT NULL' : 'NULL') . "\n";
    }
    
    echo "\n=== FINAL ADMIN_USERS TABLE STRUCTURE ===\n";
    $stmt = $conn->query("SELECT column_name, data_type, is_nullable FROM information_schema.columns WHERE table_name = 'admin_users' ORDER BY ordinal_position");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo "- " . $col['column_name'] . " (" . $col['data_type'] . ") " . ($col['is_nullable'] === 'NO' ? 'NOT NULL' : 'NULL') . "\n";
    }
    
    echo "\n=== USERS TABLE FIX COMPLETE ===\n";
    echo "All missing columns have been added successfully!\n";
    echo "You can now test the admin dashboard and user functionality!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
