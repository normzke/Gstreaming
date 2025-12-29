<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/functions.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../public/login.php');
    exit;
}

$db = new Database();
$conn = $db->getConnection();

// Handle form submissions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create_user':
                $username = $_POST['username'];
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $email = $_POST['email'];
                $subscription_tier = $_POST['subscription_tier'];
                $device_limit = $_POST['device_limit'];
                
                // Generate unique streaming token
                $streaming_token = bin2hex(random_bytes(32));
                $playlist_url = SITE_URL . "/api/playlist.php?token=" . $streaming_token;
                
                $stmt = $conn->prepare("
                    INSERT INTO users (username, password, email, subscription_tier, device_limit, 
                                     streaming_token, playlist_url, is_active, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW())
                ");
                
                if ($stmt->execute([$username, $password, $email, $subscription_tier, $device_limit, 
                                   $streaming_token, $playlist_url])) {
                    $message = "User created successfully! Streaming URL: " . $playlist_url;
                    $messageType = 'success';
                } else {
                    $message = "Error creating user";
                    $messageType = 'error';
                }
                break;
                
            case 'update_user':
                $user_id = $_POST['user_id'];
                $subscription_tier = $_POST['subscription_tier'];
                $device_limit = $_POST['device_limit'];
                $is_active = isset($_POST['is_active']) ? 1 : 0;
                
                $stmt = $conn->prepare("
                    UPDATE users 
                    SET subscription_tier = ?, device_limit = ?, is_active = ?
                    WHERE id = ?
                ");
                
                if ($stmt->execute([$subscription_tier, $device_limit, $is_active, $user_id])) {
                    $message = "User updated successfully!";
                    $messageType = 'success';
                } else {
                    $message = "Error updating user";
                    $messageType = 'error';
                }
                break;
                
            case 'regenerate_token':
                $user_id = $_POST['user_id'];
                $streaming_token = bin2hex(random_bytes(32));
                $playlist_url = SITE_URL . "/api/playlist.php?token=" . $streaming_token;
                
                $stmt = $conn->prepare("
                    UPDATE users 
                    SET streaming_token = ?, playlist_url = ?
                    WHERE id = ?
                ");
                
                if ($stmt->execute([$streaming_token, $playlist_url, $user_id])) {
                    $message = "New streaming URL generated: " . $playlist_url;
                    $messageType = 'success';
                } else {
                    $message = "Error regenerating token";
                    $messageType = 'error';
                }
                break;
                
            case 'add_device':
                $user_id = $_POST['user_id'];
                $device_name = $_POST['device_name'];
                $device_type = $_POST['device_type'];
                $mac_address = $_POST['mac_address'];
                
                $stmt = $conn->prepare("
                    INSERT INTO user_devices (user_id, device_name, device_type, mac_address, 
                                            last_active, is_active) 
                    VALUES (?, ?, ?, ?, NOW(), 1)
                ");
                
                if ($stmt->execute([$user_id, $device_name, $device_type, $mac_address])) {
                    $message = "Device added successfully!";
                    $messageType = 'success';
                } else {
                    $message = "Error adding device";
                    $messageType = 'error';
                }
                break;
                
            case 'delete_user':
                $user_id = $_POST['user_id'];
                $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                if ($stmt->execute([$user_id])) {
                    $message = "User deleted successfully!";
                    $messageType = 'success';
                } else {
                    $message = "Error deleting user";
                    $messageType = 'error';
                }
                break;
        }
    }
}

// Get all streaming users
$stmt = $conn->query("
    SELECT u.*, 
           COUNT(DISTINCT ud.id) as device_count,
           MAX(ud.last_active) as last_device_active
    FROM users u
    LEFT JOIN user_devices ud ON u.id = ud.user_id AND ud.is_active = 1
    GROUP BY u.id
    ORDER BY u.created_at DESC
");
$users = $stmt->fetchAll();

// Get subscription tiers
$tiers = [
    'basic' => 'Basic (1 device, SD)',
    'standard' => 'Standard (2 devices, HD)',
    'premium' => 'Premium (3 devices, 4K)',
    'family' => 'Family (5 devices, 8K)'
];

$device_types = ['Android TV', 'WebOS', 'Tizen', 'Fire TV', 'Apple TV', 'Web Browser', 'Mobile'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Streaming Users Management - BingeTV Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f0f1e 0%, #1a1a2e 100%);
            color: #fff;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .header {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            border: 2px solid #00A8FF;
            box-shadow: 0 10px 30px rgba(0,168,255,0.2);
        }
        
        .header h1 {
            color: #00A8FF;
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .header p {
            color: #ccc;
            font-size: 16px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: rgba(0,168,255,0.1);
            border: 2px solid rgba(0,168,255,0.3);
            border-radius: 12px;
            padding: 25px;
            text-align: center;
            transition: all 0.3s;
        }
        
        .stat-card:hover {
            border-color: #00A8FF;
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,168,255,0.3);
        }
        
        .stat-card i {
            font-size: 36px;
            color: #00A8FF;
            margin-bottom: 15px;
        }
        
        .stat-card .value {
            font-size: 32px;
            font-weight: bold;
            color: #fff;
            margin-bottom: 5px;
        }
        
        .stat-card .label {
            color: #ccc;
            font-size: 14px;
        }
        
        .message {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .message.success {
            background: rgba(0,255,0,0.1);
            border: 2px solid rgba(0,255,0,0.3);
            color: #0f0;
        }
        
        .message.error {
            background: rgba(255,0,0,0.1);
            border: 2px solid rgba(255,0,0,0.3);
            color: #f00;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: #00A8FF;
            color: white;
        }
        
        .btn-primary:hover {
            background: #0099E6;
            transform: scale(1.05);
        }
        
        .btn-success {
            background: #00ff00;
            color: #000;
        }
        
        .btn-danger {
            background: #ff4444;
            color: white;
        }
        
        .btn-warning {
            background: #ffaa00;
            color: #000;
        }
        
        .btn-sm {
            padding: 8px 16px;
            font-size: 14px;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.9);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal.active {
            display: flex;
        }
        
        .modal-content {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            border-radius: 15px;
            padding: 30px;
            max-width: 600px;
            width: 90%;
            border: 2px solid #00A8FF;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .modal-header h2 {
            color: #00A8FF;
            font-size: 24px;
        }
        
        .close-modal {
            background: none;
            border: none;
            color: #fff;
            font-size: 24px;
            cursor: pointer;
            padding: 0;
            width: 30px;
            height: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            color: #00A8FF;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            background: rgba(255,255,255,0.1);
            border: 2px solid rgba(0,168,255,0.3);
            border-radius: 8px;
            color: #fff;
            font-size: 16px;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #00A8FF;
            background: rgba(255,255,255,0.15);
        }
        
        .users-table {
            background: rgba(0,168,255,0.05);
            border-radius: 12px;
            overflow: hidden;
            border: 2px solid rgba(0,168,255,0.2);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        thead {
            background: rgba(0,168,255,0.2);
        }
        
        th {
            padding: 15px;
            text-align: left;
            color: #00A8FF;
            font-weight: 600;
            border-bottom: 2px solid rgba(0,168,255,0.3);
        }
        
        td {
            padding: 15px;
            border-bottom: 1px solid rgba(0,168,255,0.1);
        }
        
        tr:hover {
            background: rgba(0,168,255,0.1);
        }
        
        .badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }
        
        .badge-success {
            background: rgba(0,255,0,0.2);
            color: #0f0;
            border: 1px solid #0f0;
        }
        
        .badge-danger {
            background: rgba(255,0,0,0.2);
            color: #f00;
            border: 1px solid #f00;
        }
        
        .badge-warning {
            background: rgba(255,170,0,0.2);
            color: #ffaa00;
            border: 1px solid #ffaa00;
        }
        
        .badge-info {
            background: rgba(0,168,255,0.2);
            color: #00A8FF;
            border: 1px solid #00A8FF;
        }
        
        .streaming-url {
            font-family: monospace;
            font-size: 12px;
            color: #0f0;
            background: rgba(0,255,0,0.1);
            padding: 5px 10px;
            border-radius: 5px;
            display: inline-block;
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .copy-btn {
            background: none;
            border: none;
            color: #00A8FF;
            cursor: pointer;
            padding: 5px;
            margin-left: 5px;
        }
        
        .copy-btn:hover {
            color: #0099E6;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-satellite-dish"></i> TiviMate 8K Pro - Streaming Users</h1>
            <p>Manage user accounts, credentials, and streaming access</p>
        </div>
        
        <?php if ($message): ?>
        <div class="message <?php echo $messageType; ?>">
            <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>
        
        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-users"></i>
                <div class="value"><?php echo count($users); ?></div>
                <div class="label">Total Users</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-check-circle"></i>
                <div class="value"><?php echo count(array_filter($users, fn($u) => $u['is_active'])); ?></div>
                <div class="label">Active Users</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-mobile-alt"></i>
                <div class="value"><?php echo array_sum(array_column($users, 'device_count')); ?></div>
                <div class="label">Total Devices</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-crown"></i>
                <div class="value"><?php echo count(array_filter($users, fn($u) => $u['subscription_tier'] === 'premium' || $u['subscription_tier'] === 'family')); ?></div>
                <div class="label">Premium Users</div>
            </div>
        </div>
        
        <div style="margin-bottom: 20px;">
            <button class="btn btn-primary" onclick="openModal('createUserModal')">
                <i class="fas fa-plus"></i> Create New User
            </button>
            <a href="index.php" class="btn btn-warning">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
        
        <div class="users-table">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Tier</th>
                        <th>Devices</th>
                        <th>Streaming URL</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <span class="badge badge-info">
                                <?php echo strtoupper($user['subscription_tier'] ?? 'basic'); ?>
                            </span>
                        </td>
                        <td>
                            <?php echo $user['device_count']; ?> / <?php echo $user['device_limit'] ?? 1; ?>
                        </td>
                        <td>
                            <span class="streaming-url" title="<?php echo htmlspecialchars($user['playlist_url']); ?>">
                                <?php echo htmlspecialchars($user['playlist_url']); ?>
                            </span>
                            <button class="copy-btn" onclick="copyToClipboard('<?php echo htmlspecialchars($user['playlist_url']); ?>')">
                                <i class="fas fa-copy"></i>
                            </button>
                        </td>
                        <td>
                            <span class="badge badge-<?php echo $user['is_active'] ? 'success' : 'danger'; ?>">
                                <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                        <td><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="editUser(<?php echo $user['id']; ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-success" onclick="manageDevices(<?php echo $user['id']; ?>)">
                                <i class="fas fa-mobile-alt"></i>
                            </button>
                            <button class="btn btn-sm btn-warning" onclick="regenerateToken(<?php echo $user['id']; ?>)">
                                <i class="fas fa-sync"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteUser(<?php echo $user['id']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Create User Modal -->
    <div class="modal" id="createUserModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-user-plus"></i> Create New Streaming User</h2>
                <button class="close-modal" onclick="closeModal('createUserModal')">×</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="create_user">
                
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Username</label>
                    <input type="text" name="username" required>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Password</label>
                    <input type="password" name="password" required>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-crown"></i> Subscription Tier</label>
                    <select name="subscription_tier" required>
                        <?php foreach ($tiers as $key => $label): ?>
                        <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-mobile-alt"></i> Device Limit</label>
                    <input type="number" name="device_limit" value="1" min="1" max="10" required>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Create User
                </button>
            </form>
        </div>
    </div>
    
    <script>
        function openModal(modalId) {
            document.getElementById(modalId).classList.add('active');
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }
        
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('Streaming URL copied to clipboard!');
            });
        }
        
        function editUser(userId) {
            // Implement edit functionality
            alert('Edit user ' + userId);
        }
        
        function manageDevices(userId) {
            // Implement device management
            alert('Manage devices for user ' + userId);
        }
        
        function regenerateToken(userId) {
            if (confirm('Are you sure you want to regenerate the streaming URL? The old URL will stop working.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="regenerate_token">
                    <input type="hidden" name="user_id" value="${userId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function deleteUser(userId) {
            if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_user">
                    <input type="hidden" name="user_id" value="${userId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Close modal when clicking outside
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.classList.remove('active');
                }
            });
        });
    </script>
</body>
</html>
