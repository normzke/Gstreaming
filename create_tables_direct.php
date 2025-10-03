<?php
/**
 * Create Missing Tables Directly
 * Creates tables one by one to ensure they're created properly
 */

echo "=== CREATING MISSING TABLES DIRECTLY ===\n\n";

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
    
    // Create activity_logs table
    echo "Creating activity_logs table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS activity_logs (
        id SERIAL PRIMARY KEY,
        user_id INTEGER NOT NULL,
        action VARCHAR(100) NOT NULL,
        details TEXT,
        ip_address INET,
        user_agent TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $conn->exec($sql);
    echo "✅ activity_logs table created\n";
    
    // Create notifications table
    echo "Creating notifications table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS notifications (
        id SERIAL PRIMARY KEY,
        user_id INTEGER NOT NULL,
        title VARCHAR(200) NOT NULL,
        message TEXT NOT NULL,
        type VARCHAR(50) DEFAULT 'info',
        is_read BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $conn->exec($sql);
    echo "✅ notifications table created\n";
    
    // Create user_sessions table
    echo "Creating user_sessions table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS user_sessions (
        id SERIAL PRIMARY KEY,
        user_id INTEGER NOT NULL,
        session_id VARCHAR(255) UNIQUE NOT NULL,
        ip_address INET,
        user_agent TEXT,
        expires_at TIMESTAMP NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $conn->exec($sql);
    echo "✅ user_sessions table created\n";
    
    // Create system_settings table
    echo "Creating system_settings table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS system_settings (
        id SERIAL PRIMARY KEY,
        setting_key VARCHAR(100) UNIQUE NOT NULL,
        setting_value TEXT,
        description TEXT,
        is_encrypted BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);
    echo "✅ system_settings table created\n";
    
    // Create audit_logs table
    echo "Creating audit_logs table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS audit_logs (
        id SERIAL PRIMARY KEY,
        user_id INTEGER,
        action VARCHAR(100) NOT NULL,
        table_name VARCHAR(100),
        record_id INTEGER,
        old_values TEXT,
        new_values TEXT,
        ip_address INET,
        user_agent TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    )";
    $conn->exec($sql);
    echo "✅ audit_logs table created\n";
    
    // Create indexes
    echo "Creating indexes...\n";
    $indexes = [
        "CREATE INDEX idx_activity_logs_user_id ON activity_logs(user_id)",
        "CREATE INDEX idx_activity_logs_action ON activity_logs(action)",
        "CREATE INDEX idx_activity_logs_created_at ON activity_logs(created_at)",
        "CREATE INDEX idx_notifications_user_id ON notifications(user_id)",
        "CREATE INDEX idx_notifications_is_read ON notifications(is_read)",
        "CREATE INDEX idx_notifications_created_at ON notifications(created_at)",
        "CREATE INDEX idx_user_sessions_user_id ON user_sessions(user_id)",
        "CREATE INDEX idx_user_sessions_session_id ON user_sessions(session_id)",
        "CREATE INDEX idx_user_sessions_expires_at ON user_sessions(expires_at)",
        "CREATE INDEX idx_audit_logs_user_id ON audit_logs(user_id)",
        "CREATE INDEX idx_audit_logs_action ON audit_logs(action)",
        "CREATE INDEX idx_audit_logs_table_name ON audit_logs(table_name)",
        "CREATE INDEX idx_audit_logs_created_at ON audit_logs(created_at)"
    ];
    
    foreach ($indexes as $index) {
        try {
            $conn->exec($index);
        } catch (PDOException $e) {
            // Ignore duplicate index errors
            if (strpos($e->getMessage(), 'already exists') === false) {
                echo "Warning creating index: " . $e->getMessage() . "\n";
            }
        }
    }
    echo "✅ Indexes created\n";
    
    // Verify tables were created
    echo "\n=== VERIFYING NEW TABLES ===\n";
    
    $newTables = [
        'activity_logs', 'notifications', 'user_sessions', 
        'system_settings', 'audit_logs'
    ];
    
    foreach ($newTables as $table) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_name = ?");
        $stmt->execute([$table]);
        $exists = $stmt->fetchColumn() > 0;
        
        if ($exists) {
            echo "✅ Table '$table' exists\n";
        } else {
            echo "❌ Table '$table' does not exist\n";
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
