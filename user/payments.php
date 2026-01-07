<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit();
}

$db = Database::getInstance();
$conn = $db->getConnection();
$user = getCurrentUser();
$userId = $user['id'];

// Get all user payments
$paymentsQuery = "SELECT p.*, pk.name as package_name, pk.duration_days,
                  CASE 
                      WHEN p.status = 'completed' THEN '#10B981'
                      WHEN p.status = 'pending' THEN '#F59E0B'
                      WHEN p.status = 'failed' THEN '#EF4444'
                      ELSE '#6B7280'
                  END as status_color
                  FROM payments p
                  LEFT JOIN packages pk ON p.package_id = pk.id
                  WHERE p.user_id = ?
                  ORDER BY p.created_at DESC";
$paymentsStmt = $conn->prepare($paymentsQuery);
$paymentsStmt->execute([$userId]);
$payments = $paymentsStmt->fetchAll();

// Calculate totals
$totalSpent = 0;
$completedPayments = 0;
$pendingPayments = 0;

foreach ($payments as $payment) {
    if ($payment['status'] === 'completed') {
        $totalSpent += $payment['amount'];
        $completedPayments++;
    } elseif ($payment['status'] === 'pending') {
        $pendingPayments++;
    }
}

$page_title = 'Payment History';
include __DIR__ . '/includes/header.php';
?>

<!-- Payment Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-title">Total Spent</div>
            <div class="stat-icon">
                <i class="fas fa-money-bill-wave"></i>
            </div>
        </div>
        <div class="stat-value">KES <?php echo number_format($totalSpent, 0); ?></div>
        <div class="stat-change">
            Lifetime spending
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-title">Completed</div>
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
        <div class="stat-value"><?php echo $completedPayments; ?></div>
        <div class="stat-change positive">
            <i class="fas fa-arrow-up"></i>
            Successful payments
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-title">Pending</div>
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
        </div>
        <div class="stat-value"><?php echo $pendingPayments; ?></div>
        <div class="stat-change">
            Awaiting confirmation
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-title">Total Payments</div>
            <div class="stat-icon">
                <i class="fas fa-receipt"></i>
            </div>
        </div>
        <div class="stat-value"><?php echo count($payments); ?></div>
        <div class="stat-change">
            All transactions
        </div>
    </div>
</div>

<!-- Payment Actions -->
<div class="user-card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-plus-circle"></i>
            Quick Actions
        </h2>
    </div>
    <div class="card-body">
        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
            <a href="/user/subscriptions" class="btn btn-primary">
                <i class="fas fa-credit-card"></i>
                Pay Online (Card/M-Pesa)
            </a>
            <a href="/user/payments/submit-mpesa" class="btn btn-secondary">
                <i class="fas fa-mobile-alt"></i>
                Submit Manual M-Pesa
            </a>
        </div>
    </div>
</div>

<!-- Payment History -->
<div class="user-card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-history"></i>
            Payment History
        </h2>
    </div>
    <div class="card-body">
        <?php if (count($payments) > 0): ?>
            <div style="overflow-x: auto;">
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Package</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td>
                                    <div style="font-weight: 600;">
                                        <?php echo date('M d, Y', strtotime($payment['created_at'])); ?>
                                    </div>
                                    <div style="font-size: 0.85rem; color: var(--user-text-light);">
                                        <?php echo date('h:i A', strtotime($payment['created_at'])); ?>
                                    </div>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($payment['package_name'] ?? 'N/A'); ?></strong>
                                    <?php if ($payment['duration_days']): ?>
                                        <div style="font-size: 0.85rem; color: var(--user-text-light);">
                                            <?php echo round($payment['duration_days'] / 30); ?> month(s)
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong style="color: var(--user-primary); font-size: 1.1rem;">
                                        KES <?php echo number_format($payment['amount'], 0); ?>
                                    </strong>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <?php if (stripos($payment['payment_method'], 'paystack') !== false): ?>
                                            <i class="fas fa-credit-card" style="color: #09a5db;"></i>
                                        <?php else: ?>
                                            <i class="fas fa-mobile-alt" style="color: #10B981;"></i>
                                        <?php endif; ?>
                                        <span style="text-transform: capitalize;">
                                            <?php echo htmlspecialchars(str_replace(['_', 'paystack'], [' ', 'Paystack'], $payment['payment_method'] ?? 'M-Pesa')); ?>
                                        </span>
                                    </div>
                                    <?php if ($payment['mpesa_receipt_code']): ?>
                                        <div style="font-size: 0.8rem; color: var(--user-text-light);">
                                            <?php echo htmlspecialchars($payment['mpesa_receipt_code']); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span
                                        style="background: <?php echo $payment['status_color']; ?>; color: white; padding: 0.35rem 0.85rem; border-radius: 20px; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; display: inline-block;">
                                        <?php echo htmlspecialchars($payment['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($payment['status'] === 'pending'): ?>
                                        <a href="/user/payments/process?payment_id=<?php echo $payment['id']; ?>"
                                            class="btn btn-primary" style="font-size: 0.85rem; padding: 0.5rem 1rem;">
                                            <i class="fas fa-arrow-right"></i>
                                            Complete
                                        </a>
                                    <?php elseif ($payment['status'] === 'completed'): ?>
                                        <button class="btn btn-secondary" style="font-size: 0.85rem; padding: 0.5rem 1rem;"
                                            disabled>
                                            <i class="fas fa-check"></i>
                                            Paid
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-secondary" style="font-size: 0.85rem; padding: 0.5rem 1rem;"
                                            disabled>
                                            <i class="fas fa-times"></i>
                                            <?php echo ucfirst($payment['status']); ?>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 3rem 1rem;">
                <div style="font-size: 4rem; color: #CBD5E0; margin-bottom: 1rem;">
                    <i class="fas fa-receipt"></i>
                </div>
                <h3 style="color: var(--user-text); margin-bottom: 0.5rem;">No Payment History</h3>
                <p style="color: var(--user-text-light); margin-bottom: 2rem;">
                    You haven't made any payments yet
                </p>
                <a href="/user/subscriptions" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Subscribe to a Package
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    /* Mobile Responsive Styles for Payments Page */
    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr !important;
        }

        .user-table {
            font-size: 0.85rem;
        }

        .user-table th,
        .user-table td {
            padding: 0.5rem;
            font-size: 0.8rem;
        }

        /* Hide less important columns on mobile */
        .user-table th:nth-child(4),
        .user-table td:nth-child(4) {
            display: none;
        }

        .card-body {
            overflow-x: auto;
        }

        .btn {
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
        }
    }

    @media (max-width: 480px) {
        .stat-value {
            font-size: 1.5rem !important;
        }

        .stat-card {
            padding: 1rem;
        }

        /* Stack quick action buttons */
        .card-body>div {
            flex-direction: column !important;
        }

        .card-body .btn {
            width: 100% !important;
        }
    }
</style>

<?php include __DIR__ . '/includes/footer.php'; ?>