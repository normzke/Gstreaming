<?php
/**
 * Migration Runner for Migration 010: Manual M-Pesa Confirmation System
 * 
 * This script creates the manual_payment_submissions table for the manual M-Pesa confirmation feature.
 * Upload this file to the root directory and access it via browser once.
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

header('Content-Type: text/plain');

echo "=== Running Migration 010: Manual M-Pesa Confirmation System ===\n\n";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Read the migration file
    $migrationFile = __DIR__ . '/database/migrations/010_manual_mpesa_confirmations.sql';
    
    if (!file_exists($migrationFile)) {
        throw new Exception("Migration file not found: $migrationFile");
    }
    
    $sql = file_get_contents($migrationFile);
    
    if (empty($sql)) {
        throw new Exception("Migration file is empty");
    }
    
    echo "Migration file loaded successfully.\n";
    echo "Executing SQL...\n\n";
    
    // Execute the migration
    $conn->exec($sql);
    
    echo "✅ Migration 010 executed successfully!\n\n";
    
    // Verify the table was created
    $checkQuery = "SELECT table_name FROM information_schema.tables 
                   WHERE table_schema = 'public' 
                   AND table_name = 'manual_payment_submissions'";
    $result = $conn->query($checkQuery);
    
    if ($result->rowCount() > 0) {
        echo "✅ Table 'manual_payment_submissions' confirmed in database.\n\n";
        
        // Show table structure
        $structureQuery = "SELECT column_name, data_type, is_nullable, column_default 
                          FROM information_schema.columns 
                          WHERE table_name = 'manual_payment_submissions' 
                          ORDER BY ordinal_position";
        $structureResult = $conn->query($structureQuery);
        
        echo "Table Structure:\n";
        echo "----------------\n";
        while ($col = $structureResult->fetch(PDO::FETCH_ASSOC)) {
            echo "  {$col['column_name']} ({$col['data_type']}) ";
            echo $col['is_nullable'] == 'NO' ? 'NOT NULL' : 'NULL';
            if ($col['column_default']) {
                echo " DEFAULT {$col['column_default']}";
            }
            echo "\n";
        }
    } else {
        echo "⚠️  Warning: Table verification failed.\n";
    }
    
    echo "\n=== Migration Complete ===\n";
    echo "You can now delete this file (run_migration_010.php) for security.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

