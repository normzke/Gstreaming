<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../lib/functions.php';
require_once '../lib/email.php';
require_once '../lib/email_notifications.php';

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
                $existing_user_id = $_POST['existing_user_id'] ?? '';

                $subscription_tier = $_POST['subscription_tier'];
                $device_limit = $_POST['device_limit'];

                // Xtream Codes / TiviMate Credentials
                $tivimate_server = $_POST['tivimate_server'] ?? '';
                $tivimate_username = $_POST['tivimate_username'] ?? '';
                $tivimate_password = $_POST['tivimate_password'] ?? '';
                $tivimate_expires_at = $_POST['tivimate_expires_at'] ?? null;
                $tivimate_active = isset($_POST['tivimate_active']) ? 1 : 0;

                // Prepare Xtream Details & M3U URL
                $xtreamDetails = null;
                $generatedM3u = '';

                if (!empty($tivimate_server) && !empty($tivimate_username) && !empty($tivimate_password)) {
                    $server = rtrim($tivimate_server, '/');
                    if (!preg_match("~^(?:f|ht)tps?://~i", $server)) {
                        $server = "http://" . $server;
                    }
                    $generatedM3u = "{$server}/get.php?username={$tivimate_username}&password={$tivimate_password}&type=m3u_plus&output=ts";

                    $xtreamDetails = [
                        'server' => $server,
                        'username' => $tivimate_username,
                        'password' => $tivimate_password,
                        'm3u_url' => $generatedM3u
                    ];
                }

                // Use manual playlist URL if provided, otherwise generated, otherwise internal
                if (!empty($_POST['playlist_url'])) {
                    $playlist_url = $_POST['playlist_url'];
                    if ($xtreamDetails) {
                        $xtreamDetails['m3u_url'] = $playlist_url;
                    }
                } else {
                    $playlist_url = !empty($generatedM3u) ? $generatedM3u : (SITE_URL . "/api/playlist.php?token=" . bin2hex(random_bytes(32)));
                }

                $streaming_token = bin2hex(random_bytes(32));
                if (strpos($playlist_url, 'api/playlist.php') !== false) {
                    $playlist_url = SITE_URL . "/api/playlist.php?token=" . $streaming_token;
                }

                if (!empty($existing_user_id)) {
                    // Update existing user
                    $phone = $_POST['phone'] ?? '';

                    // Build Update Query
                    $updateSql = "UPDATE users 
                        SET subscription_tier = ?, device_limit = ?, 
                            streaming_token = COALESCE(NULLIF(streaming_token, ''), ?), 
                            playlist_url = COALESCE(NULLIF(playlist_url, ''), ?),
                            is_active = TRUE,
                            tivimate_server = ?, tivimate_username = ?, tivimate_password = ?, 
                            tivimate_expires_at = ?, tivimate_active = ?";

                    $params = [
                        $subscription_tier,
                        $device_limit,
                        $streaming_token,
                        $playlist_url,
                        $tivimate_server,
                        $tivimate_username,
                        $tivimate_password,
                        $tivimate_expires_at,
                        $tivimate_active
                    ];

                    if (!empty($phone)) {
                        $updateSql .= ", phone = ?";
                        $params[] = $phone;
                    }

                    $updateSql .= " WHERE id = ?";
                    $params[] = $existing_user_id;

                    $stmt = $conn->prepare($updateSql);

                    if ($stmt->execute($params)) {
                        // Fetch fresh details
                        $uStmt = $conn->prepare("SELECT username, email, phone, playlist_url, first_name FROM users WHERE id = ?");
                        $uStmt->execute([$existing_user_id]);
                        $user = $uStmt->fetch();

                        $finalUrl = $user['playlist_url'];
                        $targetPhone = $user['phone']; // Use DB phone

                        // Send Email
                        if (function_exists('sendSubscriptionActivationEmail')) {
                            sendSubscriptionActivationEmail(
                                $user['email'],
                                $user['first_name'] ?? $user['username'],
                                $subscription_tier,
                                date('Y-m-d', strtotime('+30 days')),
                                $finalUrl,
                                $user['username'],
                                'Used existing password',
                                $xtreamDetails
                            );
                        }

                        $message = "User updated! Streaming URL: " . $finalUrl;
                        $messageType = 'success';

                        // Prepare WhatsApp Link
                        if ($targetPhone) {
                            $name = $user['first_name'] ?? $user['username'];
                            if ($xtreamDetails) {
                                $waMsg = "Hello $name, your BingeTV account is ready!\n\n*Xtream Codes Login:*\nDomain: {$xtreamDetails['server']}\nUser: {$xtreamDetails['username']}\nPass: {$xtreamDetails['password']}\n\n*M3U Link:*\n{$xtreamDetails['m3u_url']}\n\nEnjoy!";
                            } else {
                                $waMsg = "Hello $name, your BingeTV account is ready! \n\nStreaming URL: $finalUrl \n\nEnjoy!";
                            }
                            $whatsapp_link = "https://wa.me/" . preg_replace('/[^0-9]/', '', $targetPhone) . "?text=" . urlencode($waMsg);
                        }
                    } else {
                        $message = "Error updating user";
                        $messageType = 'error';
                    }

                } else {
                    // Create New User
                    $username = $_POST['username'];
                    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $email = $_POST['email'];
                    $phone = $_POST['phone'] ?? '';

                    $stmt = $conn->prepare("
                        INSERT INTO users (username, password, email, phone, subscription_tier, device_limit,
                        streaming_token, playlist_url, is_active, created_at,
                        tivimate_server, tivimate_username, tivimate_password,
                        tivimate_expires_at, tivimate_active)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, TRUE, NOW(), ?, ?, ?, ?, ?)
                    ");

                    if (
                        $stmt->execute([
                            $username,
                            $password,
                            $email,
                            $phone,
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
                        // Send Email
                        if (function_exists('sendSubscriptionActivationEmail')) {
                            $emailResult = sendSubscriptionActivationEmail(
                                $email,
                                $username,
                                $subscription_tier,
                                date('Y-m-d', strtotime('+30 days')),
                                $playlist_url,
                                $username,
                                $_POST['password'],
                                $xtreamDetails
                            );
                            if (!$emailResult) {
                                error_log("Failed to send streaming activation email to $email");
                                $message .= " (Email failed to send)";
                            }
                        } else {
                            error_log("sendSubscriptionActivationEmail function NOT found!");
                            $message .= " (Email function missing)";
                        }

                        $message = "User created! Streaming URL: " . $playlist_url . ($xtreamDetails ? " (Xtream Codes Added)" : "");
                        $messageType = 'success';

                        if ($phone) {
                            if ($xtreamDetails) {
                                $waMsg = "Hello $username, your BingeTV account is ready!\n\n*Xtream Codes Login:*\nDomain: {$xtreamDetails['server']}\nUser: {$xtreamDetails['username']}\nPass: {$xtreamDetails['password']}\n\n*M3U Link:*\n{$xtreamDetails['m3u_url']}\n\nEnjoy!";
                            } else {
                                $waMsg = "Hello $username, your BingeTV account is ready! \n\nUsername: $username \nPassword: " . $_POST['password'] . "\nStreaming URL: " . $playlist_url . "\n\nEnjoy!";
                            }
                            $whatsapp_link = "https://wa.me/" . preg_replace('/[^0-9]/', '', $phone) . "?text=" . urlencode($waMsg);
                        }
                    } else {
                        $message = "Error creating user";
                        $messageType = 'error';
                    }
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
                // Device management temporarily disabled due to missing table
                $message = "Device management is currently disabled.";
                $messageType = 'warning';
                break;

            case 'delete_user':
                $user_id = $_POST['user_id'];
                // Instead of deleting the user, we revoke streaming access
                $stmt = $conn->prepare("UPDATE users SET streaming_token = NULL, playlist_url = NULL, tivimate_server = NULL, tivimate_username = NULL, tivimate_password = NULL WHERE id = ?");
                if ($stmt->execute([$user_id])) {
                    $message = "Streaming access revoked successfully!";
                    $messageType = 'success';
                } else {
                    $message = "Error revoking access";
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
0 as device_count,
NULL as last_device_active
FROM users u
WHERE u.streaming_token IS NOT NULL AND u.streaming_token != ''
ORDER BY u.created_at DESC
");
    $users = $stmt->fetchAll();
} catch (Exception $e) {
    $error_msg = "Database error: " . $e->getMessage();
}

// Get all users for dropdown
$all_users = [];
try {
    $stmt = $conn->query("SELECT id, username, email FROM users ORDER BY username ASC");
    $all_users = $stmt->fetchAll();
} catch (Exception $e) {
    // Silent error, empty list
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

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h4 mb-1">Streaming Users</h2>
        <p class="text-muted small mb-0">Manage user streaming access, tokens, and active devices</p>
    </div>
    <div>
        <button class="btn btn-primary" onclick="addNewUser()"><i class="fas fa-plus mr-2"></i>Add User</button>
    </div>
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


<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show">
        <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?> mr-2"></i>
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>

    <?php if (!empty($whatsapp_link)): ?>
        <div style="margin-bottom: 20px; text-align: center;">
            <a href="<?php echo $whatsapp_link; ?>" target="_blank" class="btn btn-success"
                style="background-color: #25D366; border-color: #25D366; color: white; display: inline-flex; align-items: center; gap: 8px;">
                <i class="fab fa-whatsapp" style="font-size: 1.2em;"></i> Send Credentials via WhatsApp
            </a>
        </div>
    <?php endif; ?>
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
                        <tr data-user='<?php echo htmlspecialchars(json_encode($user), ENT_QUOTES, 'UTF-8'); ?>'>
                            <td>
                                <div class="font-weight-bold"><?php echo htmlspecialchars($user['username']); ?></div>
                                <small class="text-muted">ID: #<?php echo $user['id']; ?></small>
                            </td>
                            <td>
                                <div class="small"><?php echo htmlspecialchars($user['email']); ?></div>
                                <div class="text-muted smallest">Joined:
                                    <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-info">
                                    <?php echo strtoupper($user['subscription_tier'] ?? 'basic'); ?>
                                </span>
                            </td>
                            <td>
                                <div class="small"><?php echo $user['device_count']; ?> /
                                    <?php echo $user['device_limit'] ?? 1; ?>
                                </div>
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
                                    <button class="btn btn-sm btn-outline-primary" onclick="editUser(this)" title="Edit">
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

            <div class="form-group">
                <label>Link to Existing User (Optional)</label>
                <select name="existing_user_id" id="existing_user_id" class="form-control"
                    onchange="toggleUserFields(this.value)">
                    <option value="">-- Create New User --</option>
                    <?php foreach ($all_users as $u): ?>
                        <option value="<?php echo $u['id']; ?>">
                            <?php echo htmlspecialchars($u['username'] . ' (' . $u['email'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="text-muted">Select a user to enable streaming for them. Leave empty to create a new
                    user.</small>
            </div>

            <div id="new_user_fields">
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
                    <label>Phone Number (for WhatsApp)</label>
                    <input type="text" name="phone" class="form-control" placeholder="+254...">
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
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
                <h6 class="text-primary mb-3"><i class="fas fa-satellite-dish mr-2"></i>Xtream Codes API Details</h6>
                <p class="small text-muted mb-3">Enter the external provider details (Domain/Username/Password) to
                    auto-generate M3U links and include them in user notifications.</p>
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
                <div class="form-group">
                    <label class="small">M3U / SmartTV URL (Auto-generated or Manual)</label>
                    <textarea name="playlist_url" class="form-control form-control-sm" rows="3"
                        placeholder="http://server.com/get.php?username=..."></textarea>
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
    function toggleUserFields(val) {
        const fields = document.getElementById('new_user_fields');
        const inputs = fields.querySelectorAll('input');
        if (val) {
            fields.style.display = 'none';
            inputs.forEach(i => i.removeAttribute('required'));
        } else {
            fields.style.display = 'block';
            inputs.forEach(i => i.setAttribute('required', 'required'));
        }
    }

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

    function editUser(btn) {
        const tr = btn.closest('tr');
        if (!tr || !tr.dataset.user) {
            console.error('No user data found');
            return;
        }

        try {
            const data = JSON.parse(tr.dataset.user);

            // Populate form fields
            const form = document.querySelector('#createUserModal form');
            form.querySelector('input[name="username"]').value = data.username || '';
            form.querySelector('input[name="password"]').value = ''; // Don't populate password
            form.querySelector('input[name="email"]').value = data.email || '';
            form.querySelector('input[name="phone"]').value = data.phone || '';
            form.querySelector('select[name="subscription_tier"]').value = data.subscription_tier || 'basic';
            form.querySelector('input[name="device_limit"]').value = data.device_limit || 1;

            // Xtream Details
            form.querySelector('input[name="tivimate_server"]').value = data.tivimate_server || '';
            form.querySelector('input[name="tivimate_username"]').value = data.tivimate_username || '';
            form.querySelector('input[name="tivimate_password"]').value = data.tivimate_password || '';
            form.querySelector('textarea[name="playlist_url"]').value = data.playlist_url || '';
            form.querySelector('input[name="tivimate_active"]').checked = (data.tivimate_active == 1);

            // Set ID
            document.getElementById('existing_user_id').value = data.id;
            // Hide standard fields as we are editing existing user
            toggleUserFields(data.id);

            // UI Text
            document.querySelector('#createUserModal h2').textContent = 'Edit Streaming User';
            form.querySelector('button[type="submit"]').textContent = 'Update User';

            openModal('createUserModal');

        } catch (e) {
            console.error('Error parsing user data', e);
            alert('Error loading user data');
        }
    }

    function addNewUser() {
        const form = document.querySelector('#createUserModal form');
        form.reset();
        document.getElementById('existing_user_id').value = '';
        document.querySelector('#createUserModal h2').textContent = 'Create New Streaming User';
        form.querySelector('button[type="submit"]').textContent = 'Create User Account';
        form.querySelector('textarea[name="playlist_url"]').value = '';
        // Show standard fields for new user
        toggleUserFields('');
        openModal('createUserModal');
    }

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

    // Auto-generate M3U URL
    const inputs = ['tivimate_server', 'tivimate_username', 'tivimate_password'];
    inputs.forEach(name => {
        const el = document.querySelector(`input[name="${name}"]`);
        if (el) {
            el.addEventListener('input', updateM3uUrl);
        }
    });

    function updateM3uUrl() {
        const server = document.querySelector('input[name="tivimate_server"]').value.trim().replace(/\/$/, '');
        const user = document.querySelector('input[name="tivimate_username"]').value.trim();
        const pass = document.querySelector('input[name="tivimate_password"]').value.trim();
        const m3uField = document.querySelector('textarea[name="playlist_url"]');

        if (server && user && pass) {
            let cleanServer = server;
            if (!/^https?:\/\//i.test(cleanServer)) {
                cleanServer = 'http://' + cleanServer;
            }
            m3uField.value = `${cleanServer}/get.php?username=${user}&password=${pass}&type=m3u_plus&output=ts`;
        }
    }
</script>

</div><!-- /.admin-main -->
</div><!-- /.admin-layout -->

<!-- Include modals and other HTML here -->

<?php include 'includes/footer.php'; ?>