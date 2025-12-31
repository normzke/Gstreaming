<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Test basic PHP functionality
echo "<h1>BingeTV Test Page</h1>";
echo "PHP Version: " . phpversion() . "<br>";

// Test database connection
try {
    require_once __DIR__ . '/../config/config.php';
    
    $dsn = "pgsql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";port=" . DB_PORT;
    $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    
    echo "Database connection successful!<br>";
    
    // Test a simple query
    $stmt = $pdo->query("SELECT 1 as test");
    $result = $stmt->fetch();
    echo "Database query test: " . ($result['test'] == 1 ? 'Success' : 'Failed') . "<br>";
    
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage() . "<br>";
}

// Test file permissions
$testFile = __DIR__ . '/test_write.txt';
if (file_put_contents($testFile, 'test') !== false) {
    echo "File write test: Success<br>";
    unlink($testFile);
} else {
    echo "File write test: Failed - Check directory permissions<br>";
}

// Display PHP info
// phpinfo();
?>
