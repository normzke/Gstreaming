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

// Handle form submissions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'update_payment_status':
                $payment_id = (int)$_POST['payment_id'];
                $status = sanitizeInput($_POST['status']);
                
                $updateQuery = "UPDATE payments SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
                $updateStmt = $conn->prepare($updateQuery);
                $updateStmt->execute([$status, $payment_id]);
                
                $message = 'Payment status updated successfully!';
                $messageType = 'success';
                break;
                
            case 'refund_payment':
                $payment_id = (int)$_POST['payment_id'];
                $refund_amount = (float)$_POST['refund_amount'];
                $refund_reason = sanitizeInput($_POST['refund_reason']);
                
                // Update payment status to refunded
                $updateQuery = "UPDATE payments SET status = 'refunded', refund_amount = ?, refund_reason = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
                $updateStmt = $conn->prepare($updateQuery);
                $updateStmt->execute([$refund_amount, $refund_reason, $payment_id]);
                
                $message = 'Payment refunded successfully!';
                $messageType = 'success';
                break;
        }
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $messageType = 'error';
    }
}

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
    $where_conditions[] = "p.status = ?";
    $params[] = $status_filter;
}

if (!empty($date_from)) {
    $where_conditions[] = "DATE(p.created_at) >= ?";
    $params[] = $date_from;
}

if (!empty($date_to)) {
    $where_conditions[] = "DATE(p.created_at) <= ?";
    $params[] = $date_to;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total count
$countQuery = "SELECT COUNT(*) as total FROM payments p {$where_clause}";
$countStmt = $conn->prepare($countQuery);
$countStmt->execute($params);
$total_payments = $countStmt->fetch()['total'];
$total_pages = ceil($total_payments / $limit);

// Get payments
$paymentsQuery = "SELECT p.*, u.first_name, u.last_name, u.email, u.phone, pk.name as package_name
                  FROM payments p 
                  LEFT JOIN users u ON p.user_id = u.id 
                  LEFT JOIN packages pk ON p.package_id = pk.id 
                  {$where_clause} 
                  ORDER BY p.created_at DESC 
                  LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$paymentsStmt = $conn->prepare($paymentsQuery);
$paymentsStmt->execute($params);
$payments = $paymentsStmt->fetchAll();

// Get payment statistics
$statsQuery = "SELECT 
               COUNT(*) as total_payments,
               COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_payments,
               COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_payments,
               COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed_payments,
               COALESCE(SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END), 0) as total_revenue,
               COALESCE(AVG(CASE WHEN status = 'completed' THEN amount ELSE NULL END), 0) as avg_payment
               FROM payments";
$statsStmt = $conn->prepare($statsQuery);
$statsStmt->execute();
$stats = $statsStmt->fetch();

$page_title = 'Payments';
include 'includes/header.php';
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

<!-- Payment Statistics -->
<div class="admin-card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title">Payment Statistics</h3>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <div class="stat-item">
                <h4><?php echo number_format($stats['total_payments']); ?></h4>
                <p>Total Payments</p>
            </div>
            <div class="stat-item">
                <h4><?php echo number_format($stats['completed_payments']); ?></h4>
                <p>Completed</p>
            </div>
            <div class="stat-item">
                <h4><?php echo number_format($stats['pending_payments']); ?></h4>
                <p>Pending</p>
            </div>
            <div class="stat-item">
                <h4><?php echo number_format($stats['failed_payments']); ?></h4>
                <p>Failed</p>
            </div>
            <div class="stat-item">
                <h4>KSh <?php echo number_format($stats['total_revenue'], 2); ?></h4>
                <p>Total Revenue</p>
            </div>
            <div class="stat-item">
                <h4>KSh <?php echo number_format($stats['avg_payment'], 2); ?></h4>
                <p>Average Payment</p>
            </div>
        </div>
    </div>
</div>

<!-- Filter Form -->
<div class="admin-card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title">Filter Payments</h3>
    </div>
    <div class="card-body">
        <form method="GET" class="filter-form" style="display: flex; gap: 1rem; align-items: end;">
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Status</option>
                    <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="failed" <?php echo $status_filter === 'failed' ? 'selected' : ''; ?>>Failed</option>
                    <option value="refunded" <?php echo $status_filter === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
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

<!-- Payments Table -->
<div class="admin-card">
    <div class="card-header">
        <h3 class="card-title">All Payments (<?php echo number_format($total_payments); ?> total)</h3>
    </div>
    <div class="card-body">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Package</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Method</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($payments as $payment): ?>
                <tr>
                    <td>#<?php echo $payment['id']; ?></td>
                    <td>
                        <div>
                            <strong><?php echo htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']); ?></strong>
                            <br>
                            <small class="text-muted"><?php echo htmlspecialchars($payment['email']); ?></small>
                        </div>
                    </td>
                    <td><?php echo htmlspecialchars($payment['package_name'] ?: '-'); ?></td>
                    <td>
                        <strong><?php echo $payment['currency']; ?> <?php echo number_format($payment['amount'], 2); ?></strong>
                    </td>
                    <td>
                        <span class="badge badge-<?php echo $payment['status'] === 'completed' ? 'success' : ($payment['status'] === 'pending' ? 'warning' : 'danger'); ?>">
                            <?php echo ucfirst($payment['status']); ?>
                        </span>
                    </td>
                    <td><?php echo htmlspecialchars($payment['payment_method'] ?: '-'); ?></td>
                    <td><?php echo date('M j, Y H:i', strtotime($payment['created_at'])); ?></td>
                    <td>
                        <button class="btn btn-secondary" onclick="viewPayment(<?php echo $payment['id']; ?>)" title="View Details">
                            <i class="fas fa-eye"></i>
                        </button>
                        <?php if ($payment['status'] === 'pending'): ?>
                        <button class="btn btn-success" onclick="updateStatus(<?php echo $payment['id']; ?>, 'completed')" title="Mark Complete">
                            <i class="fas fa-check"></i>
                        </button>
                        <?php endif; ?>
                        <?php if ($payment['status'] === 'completed'): ?>
                        <button class="btn btn-warning" onclick="refundPayment(<?php echo $payment['id']; ?>)" title="Refund">
                            <i class="fas fa-undo"></i>
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
function viewPayment(id) {
    console.log('View payment:', id);
}

function updateStatus(id, status) {
    if (confirm('Are you sure you want to update this payment status?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="update_payment_status">
            <input type="hidden" name="payment_id" value="${id}">
            <input type="hidden" name="status" value="${status}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function refundPayment(id) {
    const amount = prompt('Enter refund amount:');
    if (amount && !isNaN(amount)) {
        const reason = prompt('Enter refund reason:');
        if (reason) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="refund_payment">
                <input type="hidden" name="payment_id" value="${id}">
                <input type="hidden" name="refund_amount" value="${amount}">
                <input type="hidden" name="refund_reason" value="${reason}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }
}
</script>

<?php include 'includes/footer.php'; ?>
