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
    $user_id = (int)($_GET['id'] ?? 0);
    
    if (!$user_id) {
        http_response_code(400);
        echo json_encode(['error' => 'User ID required']);
        exit();
    }
    
    $db = new Database();
    $conn = $db->getConnection();
    
    $query = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        http_response_code(404);
        echo json_encode(['error' => 'User not found']);
        exit();
    }
    
    echo json_encode($user);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
