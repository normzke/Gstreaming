<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../lib/functions.php';
require_once '../lib/cache.php';

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$db = Database::getInstance();
$conn = $db->getConnection();
CachedQueries::init($db);

// Handle form submissions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        switch ($action) {
            case 'update_user':
                $user_id = (int) $_POST['user_id'];
                $first_name = sanitizeInput($_POST['first_name']);
                $last_name = sanitizeInput($_POST['last_name']);
                $email = sanitizeInput($_POST['email']);
                $phone = sanitizeInput($_POST['phone']);
                $is_active = isset($_POST['is_active']) ? 1 : 0;

                $updateQuery = "UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, is_active = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
                $updateStmt = $conn->prepare($updateQuery);
                $updateStmt->execute([$first_name, $last_name, $email, $phone, $is_active, $user_id]);

                // Invalidate cache
                CachedQueries::invalidateUserCache($user_id);

                $message = 'User updated successfully!';
                $messageType = 'success';
                break;

            case 'delete_user':
                $user_id = (int) $_POST['user_id'];

                // Check if user has active subscription
                $subQuery = "SELECT COUNT(*) as count FROM user_subscriptions WHERE user_id = ? AND status = 'active' AND end_date > NOW()";
                $subStmt = $conn->prepare($subQuery);
                $subStmt->execute([$user_id]);
                $activeSubs = $subStmt->fetch()['count'];

                if ($activeSubs > 0) {
                    $message = 'Cannot delete user with active subscription!';
                    $messageType = 'error';
                } else {
                    $deleteQuery = "DELETE FROM users WHERE id = ?";
                    $deleteStmt = $conn->prepare($deleteQuery);
                    $deleteStmt->execute([$user_id]);

                    $message = 'User deleted successfully!';
                    $messageType = 'success';
                }
                break;

            case 'reset_password':
                $user_id = (int) $_POST['user_id'];
                $new_password = 'BingeTV2024!';
                $password_hash = password_hash($new_password, PASSWORD_DEFAULT);

                $updateQuery = "UPDATE users SET password_hash = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
                $updateStmt = $conn->prepare($updateQuery);
                $updateStmt->execute([$password_hash, $user_id]);

                $message = "Password reset successfully! New password: {$new_password}";
                $messageType = 'success';
                break;
        }
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Get pagination parameters
$page = (int) ($_GET['page'] ?? 1);
$limit = 20;
$offset = ($page - 1) * $limit;

// Get search parameters
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? 'all';

// Build query
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(first_name ILIKE ? OR last_name ILIKE ? OR email ILIKE ? OR phone ILIKE ?)";
    $search_term = "%{$search}%";
    $params = array_merge($params, [$search_term, $search_term, $search_term, $search_term]);
}

if ($status_filter !== 'all') {
    $where_conditions[] = "is_active = ?";
    $params[] = ($status_filter === 'active') ? 1 : 0;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total count
$countQuery = "SELECT COUNT(*) as total FROM users {$where_clause}";
$countStmt = $conn->prepare($countQuery);
$countStmt->execute($params);
$total_users = $countStmt->fetch()['total'];
$total_pages = ceil($total_users / $limit);

// Get users
$usersQuery = "SELECT u.*, 
               (SELECT COUNT(*) FROM user_subscriptions us WHERE us.user_id = u.id) as subscription_count,
               (SELECT COUNT(*) FROM payments p WHERE p.user_id = u.id AND p.status = 'completed') as payment_count
               FROM users u 
               {$where_clause} 
               ORDER BY u.created_at DESC 
               LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$usersStmt = $conn->prepare($usersQuery);
$usersStmt->execute($params);
$users = $usersStmt->fetchAll();

$page_title = 'Users';
include 'includes/header.php';
?>

<!-- Messages -->
<?php if ($message): ?>
    <div class="admin-card" style="margin-bottom: 1.5rem;">
        <div class="card-body">
            <div class="alert alert-<?php echo $messageType; ?>"
                style="display: flex; align-items: center; gap: 0.5rem; padding: 1rem; border-radius: var(--admin-radius); background: <?php echo $messageType === 'success' ? '#D1FAE5' : '#FEE2E2'; ?>; color: <?php echo $messageType === 'success' ? '#065F46' : '#991B1B'; ?>;">
                <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Search and Filter -->
<div class="admin-card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title">Search & Filter Users</h3>
    </div>
    <div class="card-body">
        <form method="GET" class="filter-form" style="display: flex; gap: 1rem; align-items: end;">
            <div class="form-group" style="flex: 1;">
                <label for="search">Search</label>
                <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>"
                    placeholder="Search users...">
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Status</option>
                    <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactive
                    </option>
                </select>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i>
                    Search
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Users Management Content -->
<div class="admin-card">
    <div class="card-header">
        <h3 class="card-title">All Users (<?php echo number_format($total_users); ?> total)</h3>
        <p>Manage user accounts and subscriptions</p>
    </div>
    <div class="card-body">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Subscriptions</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td>
                            <div>
                                <strong><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></strong>
                                <br>
                                <small class="text-muted">ID: <?php echo $user['id']; ?></small>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['phone'] ?: '-'); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $user['is_active'] ? 'success' : 'danger'; ?>">
                                <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-info">
                                <?php echo $user['subscription_count']; ?> subs
                            </span>
                        </td>
                        <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <button class="btn btn-secondary" onclick="viewUser(this)" data-id="<?php echo $user['id']; ?>"
                                data-name="<?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>"
                                data-email="<?php echo htmlspecialchars($user['email']); ?>"
                                data-phone="<?php echo htmlspecialchars($user['phone'] ?: '-'); ?>"
                                data-status="<?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>"
                                data-subs="<?php echo $user['subscription_count']; ?>"
                                data-joined="<?php echo date('M j, Y', strtotime($user['created_at'])); ?>">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-warning" onclick="resetPassword(<?php echo $user['id']; ?>)"
                                title="Reset Password">
                                <i class="fas fa-key"></i>
                            </button>
                            <button class="btn btn-danger" onclick="deleteUser(<?php echo $user['id']; ?>)"
                                title="Delete User">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</div>

<!-- View User Modal -->
<div id="viewUserModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>User Details</h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="user-details-grid">
                <div class="detail-item">
                    <label>Full Name</label>
                    <p id="view_name"></p>
                </div>
                <div class="detail-item">
                    <label>Email Address</label>
                    <p id="view_email"></p>
                </div>
                <div class="detail-item">
                    <label>Phone Number</label>
                    <p id="view_phone"></p>
                </div>
                <div class="detail-item">
                    <label>Status</label>
                    <p id="view_status"></p>
                </div>
                <div class="detail-item">
                    <label>Subscriptions</label>
                    <p id="view_subs"></p>
                </div>
                <div class="detail-item">
                    <label>Joined Date</label>
                    <p id="view_joined"></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pagination -->
<?php if ($total_pages > 1): ?>
    <div class="admin-card">
        <div class="card-body">
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>"
                        class="btn btn-secondary">
                        <i class="fas fa-chevron-left"></i>
                        Previous
                    </a>
                <?php endif; ?>

                <span class="pagination-info">
                    Page <?php echo $page; ?> of <?php echo $total_pages; ?>
                </span>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>"
                        class="btn btn-secondary">
                        Next
                        <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<style>
    .filter-form {
        display: flex;
        gap: 1rem;
        align-items: end;
    }

    .filter-form .form-group {
        margin-bottom: 0;
    }

    .pagination {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 1rem;
        margin-top: 1rem;
    }

    .pagination-info {
        color: var(--admin-text-light);
        font-size: 0.875rem;
    }

    .badge {
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .badge-success {
        background: #D1FAE5;
        color: #065F46;
    }

    .badge-danger {
        background: #FEE2E2;
        color: #991B1B;
    }

    .badge-info {
        background: #DBEAFE;
        color: #1E40AF;
    }

    .badge-warning {
        background: #FEF3C7;
        color: #92400E;
    }

    .text-muted {
        color: var(--admin-text-light);
    }

    .form-group {
        margin-bottom: 1rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: var(--admin-text);
    }

    .form-group input,
    .form-group select {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid var(--admin-border);
        border-radius: var(--admin-radius);
        font-size: 1rem;
    }

    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        border-color: var(--admin-primary);
        box-shadow: 0 0 0 3px rgba(139, 0, 0, 0.1);
    }

    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        border-color: var(--admin-primary);
        box-shadow: 0 0 0 3px rgba(139, 0, 0, 0.1);
    }

    /* Modal Styles */
    .modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
    }

    .modal.active {
        display: flex;
    }

    .modal-content {
        background: white;
        border-radius: var(--admin-radius);
        width: 90%;
        max-width: 500px;
        max-height: 90vh;
        overflow-y: auto;
    }

    .modal-header {
        padding: 1.5rem;
        border-bottom: 1px solid var(--admin-border);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: var(--admin-text-light);
    }

    .modal-body {
        padding: 1.5rem;
    }

    .user-details-grid {
        display: grid;
        gap: 1.5rem;
    }

    .detail-item label {
        display: block;
        font-size: 0.875rem;
        color: var(--admin-text-light);
        margin-bottom: 0.25rem;
    }

    .detail-item p {
        font-weight: 600;
        color: var(--admin-text);
        margin: 0;
    }
</style>

<script>
    function viewUser(btn) {
        const dataset = btn.dataset;
        document.getElementById('view_name').textContent = dataset.name;
        document.getElementById('view_email').textContent = dataset.email;
        document.getElementById('view_phone').textContent = dataset.phone;
        document.getElementById('view_status').textContent = dataset.status;
        document.getElementById('view_subs').textContent = dataset.subs + ' active subscriptions';
        document.getElementById('view_joined').textContent = dataset.joined;

        document.getElementById('viewUserModal').classList.add('active');
    }

    function closeModal() {
        document.getElementById('viewUserModal').classList.remove('active');
    }

    function resetPassword(id) {
        if (confirm('Are you sure you want to reset this user\'s password?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
            <input type="hidden" name="action" value="reset_password">
            <input type="hidden" name="user_id" value="${id}">
        `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    function deleteUser(id) {
        if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
            <input type="hidden" name="action" value="delete_user">
            <input type="hidden" name="user_id" value="${id}">
        `;
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>

<?php include 'includes/footer.php'; ?>