<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../lib/functions.php';

// Set content type
header('Content-Type: application/json');

// Check for API key authentication
$apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
$validApiKey = 'your-secret-api-key'; // Change this to a secure key

if ($apiKey !== $validApiKey) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Get confirmed orders that need external package generation
    $ordersQuery = "SELECT o.*, u.first_name, u.last_name, u.email, u.phone, 
                           pk.name as package_name, pk.price, pk.duration_days
                    FROM orders o 
                    LEFT JOIN users u ON o.user_id = u.id 
                    LEFT JOIN packages pk ON o.package_id = pk.id 
                    WHERE o.status = 'confirmed' 
                    ORDER BY o.created_at ASC";
    $ordersStmt = $conn->prepare($ordersQuery);
    $ordersStmt->execute();
    $orders = $ordersStmt->fetchAll();
    
    $response = [
        'success' => true,
        'orders' => []
    ];
    
    foreach ($orders as $order) {
        $response['orders'][] = [
            'order_id' => $order['id'],
            'user_name' => $order['first_name'] . ' ' . $order['last_name'],
            'user_email' => $order['email'],
            'user_phone' => $order['phone'],
            'package_name' => $order['package_name'],
            'package_price' => $order['price'],
            'duration_days' => $order['duration_days'],
            'callback_url' => 'https://bingetv.co.ke/api/external/callback.php',
            'created_at' => $order['created_at']
        ];
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log('External API Trigger Error: ' . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error'
    ]);
}
?>
