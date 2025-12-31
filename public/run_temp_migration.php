<?php
// Temporary Database Migration Runner
// Accessed via browser to bypass SSH permission issues

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo '<!DOCTYPE html><html><head><title>DB Migration</title><style>body{font-family:sans-serif;padding:2rem;line-height:1.5;max-width:800px;margin:0 auto;background:#f5f5f5;} .card{background:white;padding:2rem;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);} .success{color:green;font-weight:bold;} .error{color:red;font-weight:bold;}</style></head><body><div class="card">';

echo "<h1>Database Migration Tool</h1>";

// Define paths
$configPath = '../config/config.php';
$dbPath = '../config/database.php';
$migrationPath = '../database/migrations/20251231_02_add_password_reset_columns.sql';

// Check files
if (!file_exists($configPath) || !file_exists($dbPath)) {
    die("<p class='error'>Critical Error: Configuration files not found relative to this script.</p></div></body></html>");
}

require_once $configPath;
require_once $dbPath;

echo "<p>Connected to configuration.</p>";

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    echo "<p>Database connection established.</p>";

    // Read migration
    if (!file_exists($migrationPath)) {
        throw new Exception("Migration file not found at: " . $migrationPath);
    }

    $sql = file_get_contents($migrationPath);
    if (empty($sql)) {
        throw new Exception("Migration file is empty");
    }

    echo "<p>Executing migration: 20251231_02_add_password_reset_columns.sql</p>";

    // Execute
    $conn->exec($sql);

    echo "<div class='success'>";
    echo "<p>✅ Migration completed successfully!</p>";
    echo "<p> The 'password_reset_token' columns have been added.</p>";
    echo "</div>";
    echo "<p>You can now use the Forgot Password feature.</p>";

} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<p>❌ Migration Failed:</p>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "</div>";
}

echo "</div></body></html>";
?>