<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../lib/functions.php';

// Require login
requireLogin();

$page_title = 'Payment History';
include '../includes/header.php';

$user = getCurrentUser();
$userId = $user['id'];

// Get all payments
$db = Database::getInstance();
$conn = $db->getConnection();

$query = "SELECT p.*, pk.name as package_name 
          FROM payments p 
          LEFT JOIN packages pk ON p.package_id = pk.id 
          WHERE p.user_id = ? 
          ORDER BY p.created_at DESC";
$stmt = $conn->prepare($query);
$stmt->execute([$userId]);
$payments = $stmt->fetchAll();
?>

<div class="user-card">
    <div class="card-header">
        <h2 class="card-title"><i class="fas fa-history"></i> Payment History</h2>
    </div>
    <div class="card-body">
        <?php if (!empty($payments)): ?>
            <div class="table-responsive">
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>Package</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Ref</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td>
                                    <?php echo htmlspecialchars($payment['package_name'] ?? 'Unknown'); ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($payment['currency'] ?? 'KES'); ?>
                                    <?php echo number_format($payment['amount'], 2); ?>
                                </td>
                                <td>
                                    <?php echo ucfirst($payment['payment_method']); ?>
                                </td>
                                <td>
                                    <span
                                        style="padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; background: <?php echo $payment['status'] === 'completed' ? '#D1FAE5' : ($payment['status'] === 'pending' ? '#FEF3C7' : '#FEE2E2'); ?>; color: <?php echo $payment['status'] === 'completed' ? '#065F46' : ($payment['status'] === 'pending' ? '#92400E' : '#991B1B'); ?>;">
                                        <?php echo ucfirst($payment['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo date('M j, Y H:i', strtotime($payment['created_at'])); ?>
                                </td>
                                <td><small>
                                        <?php echo htmlspecialchars($payment['mpesa_checkout_request_id'] ?? '-'); ?>
                                    </small></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 3rem; color: var(--user-text-light);">
                <i class="fas fa-receipt" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                <p>No payment history found.</p>
                <a href="../subscriptions/subscribe.php" class="btn btn-primary" style="margin-top: 1rem;">
                    <i class="fas fa-plus"></i> Make a Payment
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>