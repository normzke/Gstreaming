<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../lib/functions.php';

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

try {
    $package_id = (int)($_GET['id'] ?? 0);
    
    if (!$package_id) {
        http_response_code(400);
        echo json_encode(['error' => 'Package ID required']);
        exit();
    }
    
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $query = "SELECT * FROM packages WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$package_id]);
    $package = $stmt->fetch();
    
    if (!$package) {
        http_response_code(404);
        echo json_encode(['error' => 'Package not found']);
        exit();
    }
    
    echo json_encode($package);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
