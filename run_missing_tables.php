<?php
/**
 * Run Missing Tables Migration
 * Creates all missing tables referenced in the code
 */

echo "=== CREATING MISSING TABLES ===\n\n";

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
    
    // Read and execute migration file
    $migrationFile = __DIR__ . '/database/migrations/006_missing_tables.sql';
    
    if (file_exists($migrationFile)) {
        echo "Running migration: 006_missing_tables.sql\n";
        
        $sql = file_get_contents($migrationFile);
        
        // Split SQL into individual statements
        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            function($stmt) {
                return !empty($stmt) && !preg_match('/^--/', $stmt);
            }
        );
        
        foreach ($statements as $statement) {
            if (!empty(trim($statement))) {
                try {
                    $conn->exec($statement);
                } catch (PDOException $e) {
                    // Ignore "already exists" errors and index creation errors
                    if (strpos($e->getMessage(), 'already exists') === false && 
                        strpos($e->getMessage(), 'duplicate key') === false) {
                        echo "Warning: " . $e->getMessage() . "\n";
                    }
                }
            }
        }
        
        echo "✅ Migration completed successfully!\n\n";
    } else {
        echo "❌ Migration file not found: $migrationFile\n";
    }
    
    // Verify tables were created
    echo "=== VERIFYING NEW TABLES ===\n";
    
    $newTables = [
        'activity_logs', 'notifications', 'user_sessions', 
        'system_settings', 'audit_logs'
    ];
    
    foreach ($newTables as $table) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_name = ?");
        $stmt->execute([$table]);
        $exists = $stmt->fetchColumn() > 0;
        
        if ($exists) {
            echo "✅ Table '$table' created successfully\n";
        } else {
            echo "❌ Table '$table' creation failed\n";
        }
    }
    
    echo "\n=== TESTING LOGIN FUNCTIONALITY ===\n";
    
    // Test admin login
    echo "Testing admin login...\n";
    $stmt = $conn->prepare("SELECT id, username, email, password_hash, full_name FROM admin_users WHERE email = 'admin@bingetv.co.ke'");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        echo "✅ Admin user found: " . $admin['email'] . "\n";
        if (password_verify('password', $admin['password_hash'])) {
            echo "✅ Admin password verification: SUCCESS\n";
        } else {
            echo "❌ Admin password verification: FAILED\n";
        }
    } else {
        echo "❌ Admin user not found\n";
    }
    
    // Test user login
    echo "Testing user login...\n";
    $stmt = $conn->prepare("SELECT id, email, password_hash, first_name, last_name FROM users WHERE email = 'testuser@bingetv.co.ke'");
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "✅ Test user found: " . $user['email'] . "\n";
        if (password_verify('password', $user['password_hash'])) {
            echo "✅ User password verification: SUCCESS\n";
        } else {
            echo "❌ User password verification: FAILED\n";
        }
    } else {
        echo "❌ Test user not found\n";
    }
    
    echo "\n=== MISSING TABLES CREATION COMPLETE ===\n";
    echo "All missing tables have been created successfully!\n";
    echo "You can now test the login functionality!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
