<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../lib/functions.php';

echo "<h1>Migration: Add Streaming Columns</h1>";

global $conn;

if (!$conn) {
    // Fallback if global $conn is not set, try to get from Database class
    $db = Database::getInstance();
    $conn = $db->getConnection();
}

if (!$conn) {
    die("Error: Could not obtain database connection.");
}

$columnsStart = [
    'streaming_token' => 'TEXT',
    'playlist_url' => 'TEXT',
    'tivimate_server' => 'TEXT',
    'tivimate_username' => 'TEXT',
    'tivimate_password' => 'TEXT',
    'tivimate_expires_at' => 'TIMESTAMP',
    'tivimate_active' => 'INT DEFAULT 1',
    'subscription_tier' => "VARCHAR(50) DEFAULT 'free'",
    'device_limit' => 'INT DEFAULT 1',
    'is_active' => 'INT DEFAULT 1'
];

foreach ($columnsStart as $col => $def) {
    try {
        // Build generic ADD COLUMN (Postgres/MySQL compatible-ish)
        // Postgres supports IF NOT EXISTS
        $sql = "ALTER TABLE users ADD COLUMN IF NOT EXISTS $col $def";

        $conn->exec($sql);
        echo "<p>Added/Checked column: <strong>$col</strong></p>";
    } catch (PDOException $e) {
        // If "IF NOT EXISTS" fails (e.g. old MySQL), we catch 'Duplicate column' error
        if (stripos($e->getMessage(), 'duplicate') !== false || stripos($e->getMessage(), 'exists') !== false) {
            echo "<p>Column $col already exists.</p>";
        } else {
            // Try without IF NOT EXISTS for older DBs
            try {
                $sql = "ALTER TABLE users ADD COLUMN $col $def";
                $conn->exec($sql);
                echo "<p>Added column: <strong>$col</strong> (fallback)</p>";
            } catch (PDOException $e2) {
                echo "<p style='color:red'>Error adding $col: " . $e2->getMessage() . "</p>";
            }
        }
    }
}

echo "<p>Migration Complete.</p>";
?>