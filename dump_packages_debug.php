<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/config.php';

try {
    $dsn = "pgsql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";port=" . DB_PORT;
    $pdo = new PDO($dsn, DB_USER, DB_PASSWORD);

    $stmt = $pdo->query("SELECT id, name, price, sort_order, is_active, description FROM packages ORDER BY sort_order, price ASC");
    $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "ID | Name | Price | Sort | Active | Description (partial)\n";
    echo str_repeat("-", 100) . "\n";
    foreach ($packages as $pkg) {
        printf(
            "%d | %-25s | %8s | %4d | %s | %s\n",
            $pkg['id'],
            $pkg['name'],
            $pkg['price'],
            $pkg['sort_order'],
            $pkg['is_active'] ? 'Y' : 'N',
            substr($pkg['description'] ?? '', 0, 40)
        );
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
