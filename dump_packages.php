<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Try to find the correct path to config
$possible_paths = [
    'config/config.php',
    '../config/config.php',
    __DIR__ . '/config/config.php'
];

$config_loaded = false;
foreach ($possible_paths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $config_loaded = true;
        break;
    }
}

if (!$config_loaded) {
    die("Error: Could not load config.php\n");
}

if (file_exists('config/database.php')) {
    require_once 'config/database.php';
} else if (file_exists('../config/database.php')) {
    require_once '../config/database.php';
}

echo "Database Host: " . (defined('DB_HOST') ? DB_HOST : 'NOT DEFINED') . "\n";
echo "Database Name: " . (defined('DB_NAME') ? DB_NAME : 'NOT DEFINED') . "\n";

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    $query = "SELECT id, name, price, description, is_active FROM packages ORDER BY id ASC";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $packages = $stmt->fetchAll();

    echo "ID | Name | Price | Active | Description (first 50 chars)\n";
    echo str_repeat("-", 100) . "\n";
    foreach ($packages as $pkg) {
        printf(
            "%d | %-20s | %10s | %s | %s\n",
            $pkg['id'],
            $pkg['name'],
            $pkg['price'],
            $pkg['is_active'] ? 'YES' : 'NO',
            substr($pkg['description'] ?? '', 0, 50)
        );
    }
} catch (Exception $e) {
    echo "Primary connection failed: " . $e->getMessage() . "\n";
    echo "Attempting fallback to localhost...\n";

    try {
        $dsn = "pgsql:host=localhost;dbname=" . DB_NAME . ";port=5432";
        $pdo = new PDO($dsn, DB_USER, DB_PASSWORD);
        $stmt = $pdo->query("SELECT id, name, price, description, is_active FROM packages ORDER BY id ASC");
        $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "ID | Name | Price | Active | Description (first 50 chars)\n";
        echo str_repeat("-", 100) . "\n";
        foreach ($packages as $pkg) {
            printf(
                "%d | %-20s | %10s | %s | %s\n",
                $pkg['id'],
                $pkg['name'],
                $pkg['price'],
                $pkg['is_active'] ? 'YES' : 'NO',
                substr($pkg['description'] ?? '', 0, 50)
            );
        }
    } catch (Exception $e2) {
        echo "Fallback failed as well: " . $e2->getMessage() . "\n";
    }
}
