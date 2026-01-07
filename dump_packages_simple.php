<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (file_exists('config/config.php')) {
    require_once 'config/config.php';
} else {
    die("Error: config/config.php not found\n");
}

echo "Database Host: " . DB_HOST . "\n";
echo "Database Name: " . DB_NAME . "\n";

try {
    // If DB_HOST is a path, it's a socket
    $host_part = (strpos(DB_HOST, '/') === 0) ? "host=" . DB_HOST : "host=" . DB_HOST;
    $dsn = "pgsql:" . $host_part . ";dbname=" . DB_NAME . ";port=" . DB_PORT;

    $pdo = new PDO($dsn, DB_USER, DB_PASSWORD);

    $stmt = $pdo->query("SELECT id, name, price, description, is_active FROM packages ORDER BY id ASC");
    $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "ID | Name | Price | Active | Description\n";
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
        echo "Fallback failed: " . $e2->getMessage() . "\n";
    }
}
