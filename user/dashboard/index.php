<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../lib/functions.php';
require_once __DIR__ . '/../../lib/cache.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: ../../login.php');
    exit();
}

$db = new Database();
$conn = $db->getConnection();
CachedQueries::init($db);

// Get current user data
$user = getCurrentUser();
$userId = $user['id'];

// Get user subscription
$subscription = CachedQueries::getUserSubscription($userId);

// Get user notifications
$notifications = getUserNotifications($userId, 5);

// Get user payment history
$paymentQuery = "SELECT p.*, pk.name as package_name 
                 FROM payments p 
                 LEFT JOIN packages pk ON p.package_id = pk.id 
                 WHERE p.user_id = ? 
                 ORDER BY p.created_at DESC 
                 LIMIT 10";
$paymentStmt = $conn->prepare($paymentQuery);
$paymentStmt->execute([$userId]);
$payments = $paymentStmt->fetchAll();

// Get available packages for upgrade
$packagesQuery = "SELECT * FROM packages WHERE is_active = true ORDER BY price ASC";
$packagesStmt = $conn->prepare($packagesQuery);
$packagesStmt->execute();
$availablePackages = $packagesStmt->fetchAll();

// Handle subscription actions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'cancel_subscription') {
        $subscriptionId = $_POST['subscription_id'] ?? '';

        if ($subscriptionId) {
            try {
                $cancelQuery = "UPDATE user_subscriptions SET status = 'cancelled', updated_at = NOW() WHERE id = ? AND user_id = ?";
                $cancelStmt = $conn->prepare($cancelQuery);
                $cancelStmt->execute([$subscriptionId, $userId]);

                $message = 'Subscription cancelled successfully.';
                $messageType = 'success';

                // Log activity
                logActivity($userId, 'subscription_cancelled', 'User cancelled subscription');

            } catch (Exception $e) {
                $message = 'Failed to cancel subscription. Please try again.';
                $messageType = 'error';
            }
        } else {
            $message = 'Invalid subscription ID.';
            $messageType = 'error';
        }
    } elseif ($action === 'upgrade_subscription') {
        $packageId = $_POST['package_id'] ?? '';

        if ($packageId) {
            // Redirect to subscription page with package pre-selected
            header('Location: ../subscriptions/subscribe.php?package=' . $packageId);
            exit();
        } else {
            $message = 'Invalid package ID.';
            $messageType = 'error';
        }
    }
}

// Refresh subscription data
$subscription = CachedQueries::getUserSubscription($userId);

$page_title = 'Dashboard';
include '../includes/header.php';
?>

<!-- Welcome Section -->
<div class="user-card">
    <div class="card-header">
        <h2 class="card-title">Welcome back, <?php echo htmlspecialchars($user['first_name']); ?>!</h2>
    </div>
    <div class="card-body">
        <p>Manage your streaming experience and enjoy premium content</p>
    </div>
</div>

<!-- Messages -->
<?php if ($message): ?>
    <div class="user-card" style="margin-bottom: 1.5rem;">
        <div class="card-body">
            <div class="alert alert-<?php echo $messageType; ?>"
                style="display: flex; align-items: center; gap: 0.5rem; padding: 1rem; border-radius: var(--user-radius); background: <?php echo $messageType === 'success' ? '#D1FAE5' : '#FEE2E2'; ?>; color: <?php echo $messageType === 'success' ? '#065F46' : '#991B1B'; ?>;">
                <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Subscription Status -->
<div class="user-card">
    <div class="card-header">
        <h2 class="card-title"><i class="fas fa-crown"></i> Subscription Status</h2>
    </div>
    <div class="card-body">
        <?php if ($subscription): ?>
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 2rem;">
                <div>
                    <h3 style="color: var(--user-text); font-size: 1.5rem; margin-bottom: 0.5rem;">
                        <?php echo htmlspecialchars($subscription['package_name']); ?></h3>
                    <p style="font-size: 2rem; font-weight: 700; color: var(--user-primary); margin-bottom: 0.5rem;">
                        <?php echo $subscription['currency']; ?>     <?php echo number_format($subscription['price'], 2); ?></p>
                    <p style="color: var(--user-text-light); margin-bottom: 0;">
                        <?php echo $subscription['duration_days']; ?> days</p>
                </div>
                <div style="text-align: right;">
                    <span
                        style="padding: 0.5rem 1rem; border-radius: 20px; font-weight: 600; text-transform: uppercase; font-size: 0.875rem; background: <?php echo $subscription['status'] === 'active' ? '#D1FAE5' : '#FEE2E2'; ?>; color: <?php echo $subscription['status'] === 'active' ? '#065F46' : '#991B1B'; ?>;">
                        <?php echo ucfirst($subscription['status']); ?>
                    </span>
                    <p style="margin-top: 0.5rem; color: var(--user-text-light); font-size: 0.875rem;">
                        <?php if ($subscription['status'] === 'active'): ?>
                            Expires: <?php echo date('M j, Y', strtotime($subscription['end_date'])); ?>
                        <?php else: ?>
                            Ended: <?php echo date('M j, Y', strtotime($subscription['end_date'])); ?>
                        <?php endif; ?>
                    </p>
                </div>
            </div>

            <?php if ($subscription['status'] === 'active'): ?>
                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <a href="../subscriptions/subscribe.php" class="btn btn-primary">
                        <i class="fas fa-sync"></i>
                        Renew Subscription
                    </a>
                    <a href="../channels.php" class="btn btn-secondary">
                        <i class="fas fa-tv"></i>
                        Start Streaming
                    </a>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div style="text-align: center;">
                <h3 style="color: var(--user-text); margin-bottom: 1rem;">No Active Subscription</h3>
                <p style="color: var(--user-text-light); margin-bottom: 1.5rem;">Subscribe to a package to start streaming
                    premium content</p>
                <a href="../subscriptions/subscribe.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Choose Package
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- TiviMate Streaming Credentials -->
<?php include 'tivimate_credentials_section.php'; ?>

<!-- Quick Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-title">Channels Available</div>
            <div class="stat-icon">
                <i class="fas fa-tv"></i>
            </div>
        </div>
        <div class="stat-value">500+</div>
        <div class="stat-change positive">
            <i class="fas fa-arrow-up"></i>
            Premium content
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-title">Max Devices</div>
            <div class="stat-icon">
                <i class="fas fa-mobile-alt"></i>
            </div>
        </div>
        <div class="stat-value"><?php echo $subscription ? $subscription['max_devices'] : 'N/A'; ?></div>
        <div class="stat-change positive">
            <i class="fas fa-arrow-up"></i>
            Simultaneous
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-title">Subscription Days</div>
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
        </div>
        <div class="stat-value"><?php echo $subscription ? $subscription['duration_days'] : 'N/A'; ?></div>
        <div class="stat-change positive">
            <i class="fas fa-arrow-up"></i>
            Duration
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-title">Total Payments</div>
            <div class="stat-icon">
                <i class="fas fa-credit-card"></i>
            </div>
        </div>
        <div class="stat-value"><?php echo count($payments); ?></div>
        <div class="stat-change positive">
            <i class="fas fa-arrow-up"></i>
            Transactions
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
    <!-- Recent Payments -->
    <div class="user-card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-money-bill-wave"></i> Recent Payments</h3>
            <a href="payments/" class="btn btn-secondary">View All</a>
        </div>
        <div class="card-body">
            <?php if (!empty($payments)): ?>
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>Package</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($payment['package_name']); ?></td>
                                <td><?php echo $payment['currency']; ?>         <?php echo number_format($payment['amount'], 2); ?></td>
                                <td>
                                    <span
                                        style="padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; background: <?php echo $payment['status'] === 'completed' ? '#D1FAE5' : ($payment['status'] === 'pending' ? '#FEF3C7' : '#FEE2E2'); ?>; color: <?php echo $payment['status'] === 'completed' ? '#065F46' : ($payment['status'] === 'pending' ? '#92400E' : '#991B1B'); ?>;">
                                        <?php echo ucfirst($payment['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($payment['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div style="text-align: center; padding: 2rem; color: var(--user-text-light);">
                    <i class="fas fa-receipt" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                    <p>No payments yet</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Notifications -->
    <div class="user-card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-bell"></i> Notifications</h3>
        </div>
        <div class="card-body">
            <?php if (!empty($notifications)): ?>
                <div style="space-y: 1rem;">
                    <?php foreach ($notifications as $notification): ?>
                        <div style="display: flex; gap: 1rem; padding: 1rem 0; border-bottom: 1px solid var(--user-border);">
                            <div
                                style="width: 40px; height: 40px; border-radius: 50%; background: rgba(139, 0, 0, 0.1); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <i class="fas fa-<?php echo $notification['type'] === 'success' ? 'check-circle' : 'info-circle'; ?>"
                                    style="color: var(--user-primary);"></i>
                            </div>
                            <div style="flex: 1;">
                                <h4 style="margin: 0 0 0.25rem 0; color: var(--user-text);">
                                    <?php echo htmlspecialchars($notification['title']); ?></h4>
                                <p style="margin: 0 0 0.5rem 0; color: var(--user-text-light); font-size: 0.875rem;">
                                    <?php echo htmlspecialchars($notification['message']); ?></p>
                                <span
                                    style="font-size: 0.75rem; color: #A0AEC0;"><?php echo timeAgo($notification['created_at']); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 2rem; color: var(--user-text-light);">
                    <i class="fas fa-bell-slash" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                    <p>No notifications</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Available Packages -->
<?php if (!$subscription || $subscription['status'] !== 'active'): ?>
    <div class="user-card">
        <div class="card-header">
            <h2 class="card-title">Available Packages</h2>
            <p style="color: var(--user-text-light); margin: 0;">Choose the perfect package for your streaming needs</p>
        </div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                <?php foreach ($availablePackages as $package): ?>
                    <div
                        style="background: white; border-radius: var(--user-radius); padding: 2rem; box-shadow: var(--user-shadow); border: 1px solid var(--user-border);">
                        <div style="text-align: center; margin-bottom: 1.5rem;">
                            <h3 style="color: var(--user-text); font-size: 1.5rem; margin-bottom: 1rem;">
                                <?php echo htmlspecialchars($package['name']); ?></h3>
                            <div style="display: flex; align-items: baseline; justify-content: center; gap: 0.25rem;">
                                <span
                                    style="font-size: 1.25rem; color: var(--user-text-light);"><?php echo $package['currency']; ?></span>
                                <span
                                    style="font-size: 2.5rem; font-weight: 700; color: var(--user-primary);"><?php echo number_format($package['price'], 2); ?></span>
                            </div>
                        </div>
                        <div style="margin-bottom: 2rem;">
                            <p style="color: var(--user-text-light); margin-bottom: 1.5rem; text-align: center;">
                                <?php echo htmlspecialchars($package['description']); ?></p>
                            <ul style="list-style: none; margin: 0; padding: 0;">
                                <li style="padding: 0.5rem 0; display: flex; align-items: center; gap: 0.75rem;">
                                    <i class="fas fa-check" style="color: #10B981; width: 20px;"></i>
                                    <?php echo $package['duration_days']; ?> days access
                                </li>
                                <li style="padding: 0.5rem 0; display: flex; align-items: center; gap: 0.75rem;">
                                    <i class="fas fa-check" style="color: #10B981; width: 20px;"></i>
                                    Up to <?php echo $package['max_devices']; ?> devices
                                </li>
                                <li style="padding: 0.5rem 0; display: flex; align-items: center; gap: 0.75rem;">
                                    <i class="fas fa-check" style="color: #10B981; width: 20px;"></i>
                                    500+ premium channels
                                </li>
                                <li style="padding: 0.5rem 0; display: flex; align-items: center; gap: 0.75rem;">
                                    <i class="fas fa-check" style="color: #10B981; width: 20px;"></i>
                                    HD & 4K quality
                                </li>
                            </ul>
                        </div>
                        <div style="text-align: center;">
                            <a href="../subscriptions/subscribe.php?package=<?php echo $package['id']; ?>"
                                class="btn btn-primary">
                                <i class="fas fa-credit-card"></i>
                                Subscribe Now
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>