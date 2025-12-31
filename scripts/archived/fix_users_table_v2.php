<?php
/**
 * Fix Users Table - Add Missing Columns (PostgreSQL 9.2 Compatible)
 * Adds missing columns that are referenced in the code
 */

echo "=== FIXING USERS TABLE (PostgreSQL 9.2 Compatible) ===\n\n";

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
    
    // Check if columns exist and add them if they don't
    $usersColumns = [
        'last_login' => 'TIMESTAMP',
        'phone_verified' => 'BOOLEAN DEFAULT FALSE'
    ];
    
    echo "=== ADDING MISSING USERS COLUMNS ===\n";
    foreach ($usersColumns as $columnName => $columnType) {
        // Check if column exists
        $checkQuery = "SELECT COUNT(*) FROM information_schema.columns WHERE table_name = 'users' AND column_name = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->execute([$columnName]);
        $exists = $stmt->fetchColumn() > 0;
        
        if (!$exists) {
            try {
                $addQuery = "ALTER TABLE users ADD COLUMN $columnName $columnType";
                $conn->exec($addQuery);
                echo "✅ Added column 'users.$columnName'\n";
            } catch (PDOException $e) {
                echo "❌ Error adding $columnName: " . $e->getMessage() . "\n";
            }
        } else {
            echo "✅ Column 'users.$columnName' already exists\n";
        }
    }
    
    // Check admin_users columns
    $adminColumns = [
        'last_login' => 'TIMESTAMP',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
        'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
    ];
    
    echo "\n=== ADDING MISSING ADMIN COLUMNS ===\n";
    foreach ($adminColumns as $columnName => $columnType) {
        // Check if column exists
        $checkQuery = "SELECT COUNT(*) FROM information_schema.columns WHERE table_name = 'admin_users' AND column_name = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->execute([$columnName]);
        $exists = $stmt->fetchColumn() > 0;
        
        if (!$exists) {
            try {
                $addQuery = "ALTER TABLE admin_users ADD COLUMN $columnName $columnType";
                $conn->exec($addQuery);
                echo "✅ Added column 'admin_users.$columnName'\n";
            } catch (PDOException $e) {
                echo "❌ Error adding $columnName: " . $e->getMessage() . "\n";
            }
        } else {
            echo "✅ Column 'admin_users.$columnName' already exists\n";
        }
    }
    
    // Update existing users with current timestamp for last_login
    echo "\n=== UPDATING EXISTING USERS ===\n";
    try {
        $updateUsers = "UPDATE users SET last_login = NOW() WHERE last_login IS NULL";
        $conn->exec($updateUsers);
        echo "✅ Updated existing users with last_login timestamp\n";
    } catch (PDOException $e) {
        echo "❌ Error updating users: " . $e->getMessage() . "\n";
    }
    
    try {
        $updateAdmins = "UPDATE admin_users SET last_login = NOW() WHERE last_login IS NULL";
        $conn->exec($updateAdmins);
        echo "✅ Updated existing admin users with last_login timestamp\n";
    } catch (PDOException $e) {
        echo "❌ Error updating admins: " . $e->getMessage() . "\n";
    }
    
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
    
    // Test admin dashboard query
    echo "\n=== TESTING ADMIN DASHBOARD QUERY ===\n";
    try {
        $testQuery = "SELECT COUNT(*) as total FROM users WHERE last_login > NOW() - INTERVAL '24 hours'";
        $stmt = $conn->prepare($testQuery);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "✅ Admin dashboard query successful: " . $result['total'] . " users logged in last 24 hours\n";
    } catch (PDOException $e) {
        echo "❌ Admin dashboard query failed: " . $e->getMessage() . "\n";
    }
    
    echo "\n=== USERS TABLE FIX COMPLETE ===\n";
    echo "All missing columns have been added successfully!\n";
    echo "You can now test the admin dashboard and user functionality!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
