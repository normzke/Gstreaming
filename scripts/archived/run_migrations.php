<?php
/**
 * Run database migrations for BingeTV
 * Execute this script to apply all pending migrations
 */

require_once 'config/config.php';
require_once 'config/database.php';

try {
    $db = new PDO('pgsql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASSWORD);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Starting database migrations...\n";

    // Migration 1: Create remember_tokens table
    if (!tableExists($db, 'remember_tokens')) {
        echo "Creating remember_tokens table...\n";
        $sql = "
        CREATE TABLE remember_tokens (
            id SERIAL PRIMARY KEY,
            user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
            token VARCHAR(64) UNIQUE NOT NULL,
            expires_at TIMESTAMP NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        CREATE INDEX idx_remember_tokens_token ON remember_tokens(token);
        CREATE INDEX idx_remember_tokens_user_id ON remember_tokens(user_id);
        CREATE INDEX idx_remember_tokens_expires_at ON remember_tokens(expires_at);
        ";
        $db->exec($sql);
        echo "✅ Created remember_tokens table\n";
    } else {
        echo "⏭️  remember_tokens table already exists\n";
    }

    // Migration 2: Add email verification fields to users table
    echo "Adding email verification fields to users table...\n";

    // Check if columns exist and add them if they don't
    $columnsToAdd = [
        'email_verification_token' => 'VARCHAR(64)',
        'email_verification_expires' => 'TIMESTAMP'
    ];

    foreach ($columnsToAdd as $column => $type) {
        if (!columnExists($db, 'users', $column)) {
            $sql = "ALTER TABLE users ADD COLUMN $column $type";
            $db->exec($sql);
            echo "✅ Added column: $column\n";
        } else {
            echo "⏭️  Column $column already exists\n";
        }
    }

    // Create indexes if they don't exist
    $indexesToCreate = [
        'idx_users_email_verification_token' => 'email_verification_token',
        'idx_users_email_verification_expires' => 'email_verification_expires'
    ];

    foreach ($indexesToCreate as $indexName => $column) {
        if (!indexExists($db, $indexName)) {
            $sql = "CREATE INDEX $indexName ON users($column)";
            $db->exec($sql);
            echo "✅ Created index: $indexName\n";
        } else {
            echo "⏭️  Index $indexName already exists\n";
        }
    }

    // Update existing users to have email_verified = true if they were already active
    $sql = "UPDATE users SET email_verified = true WHERE is_active = true AND email_verified = false";
    $db->exec($sql);
    echo "✅ Updated existing users with email_verified = true\n";

    echo "🎉 All migrations completed successfully!\n";

} catch (Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}

/**
 * Check if table exists
 */
function tableExists($db, $tableName) {
    try {
        $db->query("SELECT 1 FROM $tableName LIMIT 1");
        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Check if column exists in table
 */
function columnExists($db, $tableName, $columnName) {
    try {
        $stmt = $db->query("SELECT $columnName FROM $tableName LIMIT 1");
        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Check if index exists
 */
function indexExists($db, $indexName) {
    try {
        $stmt = $db->query("SELECT 1 FROM pg_indexes WHERE indexname = '$indexName'");
        return $stmt->fetch() !== false;
    } catch (Exception $e) {
        return false;
    }
}
?>
