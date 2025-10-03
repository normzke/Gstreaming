<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../lib/functions.php';

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$db = new Database();
$conn = $db->getConnection();

$page_title = 'Subscriptions';
include 'includes/header.php';
?>

<?php
// Get pagination parameters
$page = (int)($_GET['page'] ?? 1);
$limit = 20;
$offset = ($page - 1) * $limit;

// Get filter parameters
$status_filter = $_GET['status'] ?? 'all';
$package_filter = $_GET['package'] ?? 'all';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Build query
$where_conditions = [];
$params = [];

if ($status_filter !== 'all') {
    $where_conditions[] = "us.status = ?";
    $params[] = $status_filter;
}

if ($package_filter !== 'all') {
    $where_conditions[] = "us.package_id = ?";
    $params[] = $package_filter;
}

if (!empty($date_from)) {
    $where_conditions[] = "DATE(us.created_at) >= ?";
    $params[] = $date_from;
}

if (!empty($date_to)) {
    $where_conditions[] = "DATE(us.created_at) <= ?";
    $params[] = $date_to;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total count
$countQuery = "SELECT COUNT(*) as total FROM user_subscriptions us {$where_clause}";
$countStmt = $conn->prepare($countQuery);
$countStmt->execute($params);
$total_subscriptions = $countStmt->fetch()['total'];
$total_pages = ceil($total_subscriptions / $limit);

// Get subscriptions
$subscriptionsQuery = "SELECT us.*, u.first_name, u.last_name, u.email, p.name as package_name, p.price, p.currency
                      FROM user_subscriptions us 
                      LEFT JOIN users u ON us.user_id = u.id 
                      LEFT JOIN packages p ON us.package_id = p.id 
                      {$where_clause} 
                      ORDER BY us.created_at DESC 
                      LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$subscriptionsStmt = $conn->prepare($subscriptionsQuery);
$subscriptionsStmt->execute($params);
$subscriptions = $subscriptionsStmt->fetchAll();

// Get packages for filter
$packagesQuery = "SELECT id, name FROM packages ORDER BY name";
$packagesStmt = $conn->prepare($packagesQuery);
$packagesStmt->execute();
$packages = $packagesStmt->fetchAll();

// Get subscription statistics
$statsQuery = "SELECT 
               COUNT(*) as total_subscriptions,
               COUNT(CASE WHEN status = 'active' THEN 1 END) as active_subscriptions,
               COUNT(CASE WHEN status = 'expired' THEN 1 END) as expired_subscriptions,
               COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_subscriptions,
               COALESCE(SUM(CASE WHEN status = 'active' THEN p.price ELSE 0 END), 0) as active_revenue
               FROM user_subscriptions us
               LEFT JOIN packages p ON us.package_id = p.id";
$statsStmt = $conn->prepare($statsQuery);
$statsStmt->execute();
$stats = $statsStmt->fetch();
?>

<?php
// Initialize message variables
$message = '';
$messageType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'cancel_subscription':
            $subscription_id = (int)($_POST['subscription_id'] ?? 0);
            if ($subscription_id > 0) {
                $updateQuery = "UPDATE user_subscriptions SET status = 'cancelled', updated_at = NOW() WHERE id = ?";
                $updateStmt = $conn->prepare($updateQuery);
                if ($updateStmt->execute([$subscription_id])) {
                    $message = 'Subscription cancelled successfully';
                    $messageType = 'success';
                } else {
                    $message = 'Error cancelling subscription';
                    $messageType = 'error';
                }
            }
            break;
    }
}
?>

<!-- Messages -->
<?php if ($message): ?>
<div class="admin-card" style="margin-bottom: 1.5rem;">
    <div class="card-body">
        <div class="alert alert-<?php echo $messageType; ?>" style="display: flex; align-items: center; gap: 0.5rem; padding: 1rem; border-radius: var(--admin-radius); background: <?php echo $messageType === 'success' ? '#D1FAE5' : '#FEE2E2'; ?>; color: <?php echo $messageType === 'success' ? '#065F46' : '#991B1B'; ?>;">
            <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <?php echo htmlspecialchars($message); ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Subscription Statistics -->
<div class="admin-card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title">Subscription Statistics</h3>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <div class="stat-item">
                <h4><?php echo number_format($stats['total_subscriptions']); ?></h4>
                <p>Total Subscriptions</p>
            </div>
            <div class="stat-item">
                <h4><?php echo number_format($stats['active_subscriptions']); ?></h4>
                <p>Active</p>
            </div>
            <div class="stat-item">
                <h4><?php echo number_format($stats['expired_subscriptions']); ?></h4>
                <p>Expired</p>
            </div>
            <div class="stat-item">
                <h4><?php echo number_format($stats['cancelled_subscriptions']); ?></h4>
                <p>Cancelled</p>
            </div>
            <div class="stat-item">
                <h4>KSh <?php echo number_format($stats['active_revenue'], 2); ?></h4>
                <p>Active Revenue</p>
            </div>
        </div>
    </div>
</div>

<!-- Filter Form -->
<div class="admin-card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title">Filter Subscriptions</h3>
    </div>
    <div class="card-body">
        <form method="GET" class="filter-form" style="display: flex; gap: 1rem; align-items: end;">
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Status</option>
                    <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="expired" <?php echo $status_filter === 'expired' ? 'selected' : ''; ?>>Expired</option>
                    <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </div>
            <div class="form-group">
                <label for="package">Package</label>
                <select id="package" name="package">
                    <option value="all" <?php echo $package_filter === 'all' ? 'selected' : ''; ?>>All Packages</option>
                    <?php foreach ($packages as $package): ?>
                    <option value="<?php echo $package['id']; ?>" <?php echo $package_filter == $package['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($package['name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="date_from">From Date</label>
                <input type="date" id="date_from" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>">
            </div>
            <div class="form-group">
                <label for="date_to">To Date</label>
                <input type="date" id="date_to" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>">
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i>
                    Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Subscriptions Table -->
<div class="admin-card">
    <div class="card-header">
        <h3 class="card-title">All Subscriptions (<?php echo number_format($total_subscriptions); ?> total)</h3>
    </div>
    <div class="card-body">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Package</th>
                    <th>Status</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($subscriptions as $subscription): ?>
                <tr>
                    <td>#<?php echo $subscription['id']; ?></td>
                    <td>
                        <div>
                            <strong><?php echo htmlspecialchars($subscription['first_name'] . ' ' . $subscription['last_name']); ?></strong>
                            <br>
                            <small class="text-muted"><?php echo htmlspecialchars($subscription['email']); ?></small>
                        </div>
                    </td>
                    <td>
                        <div>
                            <strong><?php echo htmlspecialchars($subscription['package_name']); ?></strong>
                            <br>
                            <small class="text-muted"><?php echo $subscription['currency']; ?> <?php echo number_format($subscription['price'], 2); ?></small>
                        </div>
                    </td>
                    <td>
                        <span class="badge badge-<?php echo $subscription['status'] === 'active' ? 'success' : ($subscription['status'] === 'expired' ? 'warning' : 'danger'); ?>">
                            <?php echo ucfirst($subscription['status']); ?>
                        </span>
                    </td>
                    <td><?php echo date('M j, Y', strtotime($subscription['start_date'])); ?></td>
                    <td><?php echo date('M j, Y', strtotime($subscription['end_date'])); ?></td>
                    <td>
                        <button class="btn btn-secondary" onclick="viewSubscription(<?php echo $subscription['id']; ?>)" title="View Details">
                            <i class="fas fa-eye"></i>
                        </button>
                        <?php if ($subscription['status'] === 'active'): ?>
                        <button class="btn btn-warning" onclick="cancelSubscription(<?php echo $subscription['id']; ?>)" title="Cancel">
                            <i class="fas fa-times"></i>
                        </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<?php if ($total_pages > 1): ?>
<div class="admin-card">
    <div class="card-body">
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>&status=<?php echo urlencode($status_filter); ?>&package=<?php echo urlencode($package_filter); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>" class="btn btn-secondary">
                    <i class="fas fa-chevron-left"></i>
                    Previous
                </a>
            <?php endif; ?>
            
            <span class="pagination-info">
                Page <?php echo $page; ?> of <?php echo $total_pages; ?>
            </span>
            
            <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page + 1; ?>&status=<?php echo urlencode($status_filter); ?>&package=<?php echo urlencode($package_filter); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>" class="btn btn-secondary">
                    Next
                    <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
.stat-item {
    text-align: center;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: var(--admin-radius);
}

.stat-item h4 {
    margin: 0 0 0.5rem 0;
    font-size: 1.5rem;
    color: var(--admin-primary);
}

.stat-item p {
    margin: 0;
    color: var(--admin-text-light);
    font-size: 0.875rem;
}

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

.badge-warning {
    background: #FEF3C7;
    color: #92400E;
}

.badge-danger {
    background: #FEE2E2;
    color: #991B1B;
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
</style>

<script>
function viewSubscription(id) {
    console.log('View subscription:', id);
}

function cancelSubscription(id) {
    if (confirm('Are you sure you want to cancel this subscription?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="cancel_subscription">
            <input type="hidden" name="subscription_id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include 'includes/footer.php'; ?>
