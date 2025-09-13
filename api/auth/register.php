<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate input
    $required_fields = ['first_name', 'last_name', 'email', 'phone', 'password', 'confirm_password'];
    foreach ($required_fields as $field) {
        if (empty($input[$field])) {
            echo json_encode(['success' => false, 'message' => "Field $field is required"]);
            exit();
        }
    }
    
    // Validate email format
    if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit();
    }
    
    // Validate phone format (Kenyan format)
    if (!preg_match('/^254\d{9}$/', $input['phone'])) {
        echo json_encode(['success' => false, 'message' => 'Phone number must be in format 254XXXXXXXXX']);
        exit();
    }
    
    // Validate password
    if (strlen($input['password']) < 6) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
        exit();
    }
    
    if ($input['password'] !== $input['confirm_password']) {
        echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
        exit();
    }
    
    $db = new Database();
    $conn = $db->getConnection();
    
    // Check if email already exists
    $emailCheckQuery = "SELECT id FROM users WHERE email = ?";
    $emailCheckStmt = $conn->prepare($emailCheckQuery);
    $emailCheckStmt->execute([$input['email']]);
    
    if ($emailCheckStmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email already registered']);
        exit();
    }
    
    // Check if phone already exists
    $phoneCheckQuery = "SELECT id FROM users WHERE phone = ?";
    $phoneCheckStmt = $conn->prepare($phoneCheckQuery);
    $phoneCheckStmt->execute([$input['phone']]);
    
    if ($phoneCheckStmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Phone number already registered']);
        exit();
    }
    
    // Hash password
    $passwordHash = password_hash($input['password'], PASSWORD_DEFAULT);
    
    // Insert new user
    $insertQuery = "INSERT INTO users (first_name, last_name, email, phone, password_hash, country, is_active, email_verified) 
                   VALUES (?, ?, ?, ?, ?, 'Kenya', true, false)";
    $insertStmt = $conn->prepare($insertQuery);
    $insertStmt->execute([
        $input['first_name'],
        $input['last_name'],
        $input['email'],
        $input['phone'],
        $passwordHash
    ]);
    
    $userId = $conn->lastInsertId();
    
    // Start session
    session_start();
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_email'] = $input['email'];
    $_SESSION['user_name'] = $input['first_name'] . ' ' . $input['last_name'];
    
    // Send welcome email (optional)
    // sendWelcomeEmail($input['email'], $input['first_name']);
    
    echo json_encode([
        'success' => true,
        'message' => 'Registration successful',
        'user_id' => $userId
    ]);
    
} catch (Exception $e) {
    error_log('Registration error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Registration failed. Please try again.']);
}
?>
