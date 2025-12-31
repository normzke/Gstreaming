<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../lib/functions.php';

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$db = Database::getInstance();
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

                // TiviMate Credentials
                $tivimate_server = $_POST['tivimate_server'] ?? '';
                $tivimate_username = $_POST['tivimate_username'] ?? '';
                $tivimate_password = $_POST['tivimate_password'] ?? '';
                $tivimate_expires_at = $_POST['tivimate_expires_at'] ?? null;
                $tivimate_active = isset($_POST['tivimate_active']) ? 1 : 0;

                // Generate unique streaming token
                $streaming_token = bin2hex(random_bytes(32));
                $playlist_url = SITE_URL . "/api/playlist.php?token=" . $streaming_token;

                $stmt = $conn->prepare("
INSERT INTO users (username, password, email, subscription_tier, device_limit,
streaming_token, playlist_url, is_active, created_at,
tivimate_server, tivimate_username, tivimate_password,
tivimate_expires_at, tivimate_active)
VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW(), ?, ?, ?, ?, ?)
");

                if (
                    $stmt->execute([
                        $username,
                        $password,
                        $email,
                        $subscription_tier,
                        $device_limit,
                        $streaming_token,
                        $playlist_url,
                        $tivimate_server,
                        $tivimate_username,
                        $tivimate_password,
                        $tivimate_expires_at,
                        $tivimate_active
                    ])
                ) {
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

                // TiviMate Credentials
                $tivimate_server = $_POST['tivimate_server'] ?? '';
                $tivimate_username = $_POST['tivimate_username'] ?? '';
                $tivimate_password = $_POST['tivimate_password'] ?? '';
                $tivimate_expires_at = $_POST['tivimate_expires_at'] ?? null;
                $tivimate_active = isset($_POST['tivimate_active']) ? 1 : 0;

                $stmt = $conn->prepare("
UPDATE users
SET subscription_tier = ?, device_limit = ?, is_active = ?,
tivimate_server = ?, tivimate_username = ?, tivimate_password = ?,
tivimate_expires_at = ?, tivimate_active = ?
WHERE id = ?
");

                if (
                    $stmt->execute([
                        $subscription_tier,
                        $device_limit,
                        $is_active,
                        $tivimate_server,
                        $tivimate_username,
                        $tivimate_password,
                        $tivimate_expires_at,
                        $tivimate_active,
                        $user_id
                    ])
                ) {
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
$users = [];
$error_msg = '';

try {
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
} catch (Exception $e) {
    $error_msg = "Database error: " . $e->getMessage();
}

// Get subscription tiers
$tiers = [
    'basic' => 'Basic (1 device, SD)',
    'standard' => 'Standard (2 devices, HD)',
    'premium' => 'Premium (3 devices, 4K)',
    'family' => 'Family (5 devices, 8K)'
];

$device_types = ['Android TV', 'WebOS', 'Tizen', 'Fire TV', 'Apple TV', 'Web Browser', 'Mobile'];

$page_title = 'Streaming Users';
include 'includes/header.php';
?>

<div class="admin-main">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Streaming Users</h1>
            <p class="text-muted mb-0">Manage user streaming access, tokens, and active devices</p>
        </div>
        <div>
            <button class="btn btn-primary" data-modal="createUserModal">
                <i class="fas fa-plus mr-2"></i> Add New User
            </button>
        </div>
    </div>
</div>
<button class="btn btn-primary" onclick="openModal('createUserModal')">
    <i class="fas fa-plus"></i> Create New User
</button>
</div>

<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card.streaming-stat {
        background: white;
        border-radius: var(--admin-radius);
        padding: 1.5rem;
        box-shadow: var(--admin-shadow);
        border-left: 4px solid var(--admin-primary);
        text-align: center;
        transition: transform 0.2s;
    }

    .stat-card.streaming-stat:hover {
        transform: translateY(-5px);
    }

    .stat-card.streaming-stat i {
        font-size: 2rem;
        color: var(--admin-primary);
        margin-bottom: 1rem;
    }

    .stat-card.streaming-stat .value {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--admin-text);
        margin-bottom: 0.25rem;
    }

    .stat-card.streaming-stat .label {
        color: var(--admin-text-light);
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .streaming-url-box {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: var(--admin-bg);
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        max-width: 250px;
    }

    .streaming-url {
        font-family: monospace;
        font-size: 0.875rem;
        color: var(--admin-primary);
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .copy-btn {
        background: none;
        border: none;
        color: var(--admin-text-light);
        cursor: pointer;
        padding: 0.25rem;
        transition: color 0.2s;
    }

    .copy-btn:hover {
        color: var(--admin-primary);
    }

    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1100;
        align-items: center;
        justify-content: center;
    }

    .modal.active {
        display: flex;
    }

    .modal-content {
        background: white;
        border-radius: var(--admin-radius);
        padding: 2rem;
        max-width: 600px;
        width: 90%;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        max-height: 90vh;
        overflow-y: auto;
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        border-bottom: 1px solid var(--admin-border);
        padding-bottom: 1rem;
    }

    .modal-header h2 {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--admin-text);
        margin: 0;
    }

    .close-modal {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: var(--admin-text-light);
    }

    .form-group {
        margin-bottom: 1rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        font-size: 0.875rem;
    }

    .form-group input,
    .form-group select {
        width: 100%;
        padding: 0.625rem;
        border: 1px solid var(--admin-border);
        border-radius: var(--admin-radius);
        font-family: inherit;
    }

    .checkbox-group {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-top: 1rem;
    }

    .checkbox-group input[type="checkbox"] {
        width: auto;
    }
</style>

<div class="header-actions mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h2 class="h4 mb-1">Streaming Users</h2>
        <p class="text-muted small mb-0">Manage user accounts and streaming access</p>
    </div>
    <button class="btn btn-primary" onclick="openModal('createUserModal')">
        <i class="fas fa-plus"></i> Create New User
    </button>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show">
        <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?> mr-2"></i>
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
<?php endif; ?>

<div class="stats-grid">
    <div class="stat-card streaming-stat">
        <i class="fas fa-users"></i>
        <div class="value"><?php echo count($users); ?></div>
        <div class="label">Total Users</div>
    </div>
    <div class="stat-card streaming-stat">
        <i class="fas fa-check-circle"></i>
        <div class="value"><?php echo count(array_filter($users, fn($u) => $u['is_active'])); ?></div>
        <div class="label">Active Users</div>
    </div>
    <div class="stat-card streaming-stat">
        <i class="fas fa-mobile-alt"></i>
        <div class="value"><?php echo array_sum(array_column($users, 'device_count')); ?></div>
        <div class="label">Living Devices</div>
    </div>
    <div class="stat-card streaming-stat">
        <i class="fas fa-crown"></i>
        <div class="value">
            <?php echo count(array_filter($users, fn($u) => ($u['subscription_tier'] ?? '') === 'premium' || ($u['subscription_tier'] ?? '') === 'family')); ?>
        </div>
        <div class="label">Premium Plans</div>
    </div>
</div>

<div class="admin-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="admin-table mb-0">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Contact</th>
                        <th>Plan</th>
                        <th>Devices</th>
                        <th>Streaming URL</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <div class="font-weight-bold"><?php echo htmlspecialchars($user['username']); ?></div>
                                <small class="text-muted">ID: #<?php echo $user['id']; ?></small>
                            </td>
                            <td>
                                <div class="small"><?php echo htmlspecialchars($user['email']); ?></div>
                                <div class="text-muted smallest">Joined:
                                    <?php echo date('M d, Y', strtotime($user['created_at'])); ?></div>
                            </td>
                            <td>
                                <span class="badge badge-info">
                                    <?php echo strtoupper($user['subscription_tier'] ?? 'basic'); ?>
                                </span>
                            </td>
                            <td>
                                <div class="small"><?php echo $user['device_count']; ?> /
                                    <?php echo $user['device_limit'] ?? 1; ?></div>
                                <div class="progress progress-xs mt-1" style="height: 4px;">
                                    <div class="progress-bar bg-success"
                                        style="width: <?php echo (($user['device_count'] / ($user['device_limit'] ?: 1)) * 100); ?>%">
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="streaming-url-box">
                                    <span class="streaming-url"
                                        title="<?php echo htmlspecialchars($user['playlist_url']); ?>">
                                        <?php echo htmlspecialchars($user['playlist_url']); ?>
                                    </span>
                                    <button class="copy-btn"
                                        onclick="copyToClipboard('<?php echo htmlspecialchars($user['playlist_url']); ?>')"
                                        title="Copy URL">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo $user['is_active'] ? 'success' : 'danger'; ?>">
                                    <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="editUser(<?php echo $user['id']; ?>)"
                                        title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-success"
                                        onclick="manageDevices(<?php echo $user['id']; ?>)" title="Devices">
                                        <i class="fas fa-mobile-alt"></i>
                                    </button>
                                    <button class="btn btn-outline-warning"
                                        onclick="regenerateToken(<?php echo $user['id']; ?>)" title="Regenerate Token">
                                        <i class="fas fa-sync"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" onclick="deleteUser(<?php echo $user['id']; ?>)"
                                        title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No streaming users found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create User Modal -->
<div class="modal" id="createUserModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Create New Streaming User</h2>
            <button class="close-modal" onclick="closeModal('createUserModal')">&times;</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="create_user">

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Subscription Tier</label>
                        <select name="subscription_tier" class="form-control" required>
                            <?php foreach ($tiers as $key => $label): ?>
                                <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Device Limit</label>
                        <input type="number" name="device_limit" class="form-control" value="1" min="1" max="10"
                            required>
                    </div>
                </div>
            </div>

            <div class="mt-4 p-3 bg-light rounded border">
                <h6 class="text-primary mb-3"><i class="fas fa-satellite-dish mr-2"></i>TiviMate Credentials (Optional)
                </h6>
                <div class="form-group">
                    <label class="small">Server URL</label>
                    <input type="text" name="tivimate_server" class="form-control form-control-sm"
                        placeholder="http://example.com:8080">
                </div>
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label class="small">Username</label>
                            <input type="text" name="tivimate_username" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label class="small">Password</label>
                            <input type="text" name="tivimate_password" class="form-control form-control-sm">
                        </div>
                    </div>
                </div>
                <div class="checkbox-group">
                    <input type="checkbox" name="tivimate_active" id="tivimate_active" checked>
                    <label for="tivimate_active" class="small mb-0">Activate TiviMate profile</label>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary btn-block">Create User Account</button>
            </div>
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
            const toast = document.createElement('div');
            toast.className = 'alert alert-info position-fixed';
            toast.style.cssText = 'bottom: 20px; right: 20px; z-index: 2000; min-width: 250px;';
            toast.innerHTML = '<i class="fas fa-check-circle mr-2"></i> Streaming URL copied!';
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 2000);
        });
    }

    function editUser(userId) { alert('Edit functionality to be implemented for ID: ' + userId); }
    function manageDevices(userId) { alert('Device management to be implemented for ID: ' + userId); }

    function regenerateToken(userId) {
        if (confirm('Regenerate streaming URL? Existing players will stop working immediately.')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `<input type="hidden" name="action" value="regenerate_token"><input type="hidden" name="user_id" value="${userId}">`;
            document.body.appendChild(form);
            form.submit();
        }
    }

    function deleteUser(userId) {
        if (confirm('Permanently delete this user? This cannot be undone.')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `<input type="hidden" name="action" value="delete_user"><input type="hidden" name="user_id" value="${userId}">`;
            document.body.appendChild(form);
            form.submit();
        }
    }

    window.onclick = function (event) {
        if (event.target.classList.contains('modal')) {
            event.target.classList.remove('active');
        }
    };
</script>

</div><!-- /.admin-main -->
</div><!-- /.admin-layout -->

<!-- Include modals and other HTML here -->

<?php include 'includes/footer.php'; ?>