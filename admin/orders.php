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

$page_title = 'Orders';
include 'includes/header.php';
?>

<?php
// Get pagination parameters
$page = (int)($_GET['page'] ?? 1);
$limit = 20;
$offset = ($page - 1) * $limit;

// Get filter parameters
$status_filter = $_GET['status'] ?? 'all';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Build query
$where_conditions = [];
$params = [];

if ($status_filter !== 'all') {
    $where_conditions[] = "o.status = ?";
    $params[] = $status_filter;
}

if (!empty($date_from)) {
    $where_conditions[] = "DATE(o.created_at) >= ?";
    $params[] = $date_from;
}

if (!empty($date_to)) {
    $where_conditions[] = "DATE(o.created_at) <= ?";
    $params[] = $date_to;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total count
$countQuery = "SELECT COUNT(*) as total FROM orders o {$where_clause}";
$countStmt = $conn->prepare($countQuery);
$countStmt->execute($params);
$total_orders = $countStmt->fetch()['total'];
$total_pages = ceil($total_orders / $limit);

// Get orders
$ordersQuery = "SELECT o.*, u.first_name, u.last_name, u.email, p.name as package_name, p.price, p.currency
                FROM orders o 
                LEFT JOIN users u ON o.user_id = u.id 
                LEFT JOIN packages p ON o.package_id = p.id 
                {$where_clause} 
                ORDER BY o.created_at DESC 
                LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$ordersStmt = $conn->prepare($ordersQuery);
$ordersStmt->execute($params);
$orders = $ordersStmt->fetchAll();

// Get order statistics
$statsQuery = "SELECT 
               COUNT(*) as total_orders,
               COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_orders,
               COUNT(CASE WHEN status = 'confirmed' THEN 1 END) as confirmed_orders,
               COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_orders,
               COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_orders,
               COALESCE(SUM(CASE WHEN status = 'completed' THEN p.price ELSE 0 END), 0) as total_revenue
               FROM orders o
               LEFT JOIN packages p ON o.package_id = p.id";
$statsStmt = $conn->prepare($statsQuery);
$statsStmt->execute();
$stats = $statsStmt->fetch();
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

<!-- Order Statistics -->
<div class="admin-card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title">Order Statistics</h3>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <div class="stat-item">
                <h4><?php echo number_format($stats['total_orders']); ?></h4>
                <p>Total Orders</p>
            </div>
            <div class="stat-item">
                <h4><?php echo number_format($stats['pending_orders']); ?></h4>
                <p>Pending</p>
            </div>
            <div class="stat-item">
                <h4><?php echo number_format($stats['confirmed_orders']); ?></h4>
                <p>Confirmed</p>
            </div>
            <div class="stat-item">
                <h4><?php echo number_format($stats['completed_orders']); ?></h4>
                <p>Completed</p>
            </div>
            <div class="stat-item">
                <h4><?php echo number_format($stats['cancelled_orders']); ?></h4>
                <p>Cancelled</p>
            </div>
            <div class="stat-item">
                <h4>KSh <?php echo number_format($stats['total_revenue'], 2); ?></h4>
                <p>Total Revenue</p>
            </div>
        </div>
    </div>
</div>

<!-- Filter Form -->
<div class="admin-card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title">Filter Orders</h3>
    </div>
    <div class="card-body">
        <form method="GET" class="filter-form" style="display: flex; gap: 1rem; align-items: end;">
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Status</option>
                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                    <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
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

<!-- Orders Table -->
<div class="admin-card">
    <div class="card-header">
        <h3 class="card-title">All Orders (<?php echo number_format($total_orders); ?> total)</h3>
    </div>
    <div class="card-body">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Package</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Order Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td>#<?php echo $order['id']; ?></td>
                    <td>
                        <div>
                            <strong><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></strong>
                            <br>
                            <small class="text-muted"><?php echo htmlspecialchars($order['email']); ?></small>
                        </div>
                    </td>
                    <td>
                        <div>
                            <strong><?php echo htmlspecialchars($order['package_name']); ?></strong>
                            <br>
                            <small class="text-muted"><?php echo $order['currency']; ?> <?php echo number_format($order['price'], 2); ?></small>
                        </div>
                    </td>
                    <td>
                        <strong><?php echo $order['currency']; ?> <?php echo number_format($order['total_amount'], 2); ?></strong>
                    </td>
                    <td>
                        <span class="badge badge-<?php echo $order['status'] === 'completed' ? 'success' : ($order['status'] === 'pending' ? 'warning' : ($order['status'] === 'confirmed' ? 'info' : 'danger')); ?>">
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                    </td>
                    <td><?php echo date('M j, Y H:i', strtotime($order['created_at'])); ?></td>
                    <td>
                        <button class="btn btn-secondary" onclick="viewOrder(<?php echo $order['id']; ?>)" title="View Details">
                            <i class="fas fa-eye"></i>
                        </button>
                        <?php if ($order['status'] === 'pending'): ?>
                        <button class="btn btn-success" onclick="confirmOrder(<?php echo $order['id']; ?>)" title="Confirm Order">
                            <i class="fas fa-check"></i>
                        </button>
                        <?php endif; ?>
                        <?php if ($order['status'] === 'confirmed'): ?>
                        <button class="btn btn-primary" onclick="completeOrder(<?php echo $order['id']; ?>)" title="Mark Complete">
                            <i class="fas fa-check-double"></i>
                        </button>
                        <?php endif; ?>
                        <?php if (in_array($order['status'], ['pending', 'confirmed'])): ?>
                        <button class="btn btn-danger" onclick="cancelOrder(<?php echo $order['id']; ?>)" title="Cancel Order">
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
                <a href="?page=<?php echo $page - 1; ?>&status=<?php echo urlencode($status_filter); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>" class="btn btn-secondary">
                    <i class="fas fa-chevron-left"></i>
                    Previous
                </a>
            <?php endif; ?>
            
            <span class="pagination-info">
                Page <?php echo $page; ?> of <?php echo $total_pages; ?>
            </span>
            
            <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page + 1; ?>&status=<?php echo urlencode($status_filter); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>" class="btn btn-secondary">
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

.badge-info {
    background: #DBEAFE;
    color: #1E40AF;
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
function viewOrder(id) {
    console.log('View order:', id);
}

function confirmOrder(id) {
    if (confirm('Are you sure you want to confirm this order?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="confirm_order">
            <input type="hidden" name="order_id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function completeOrder(id) {
    if (confirm('Are you sure you want to mark this order as complete?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="complete_order">
            <input type="hidden" name="order_id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function cancelOrder(id) {
    if (confirm('Are you sure you want to cancel this order?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="cancel_order">
            <input type="hidden" name="order_id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include 'includes/footer.php'; ?>
