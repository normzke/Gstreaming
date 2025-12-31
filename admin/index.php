<?php
$page_title = 'Dashboard';
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../lib/functions.php';

// Check admin authentication
// Check admin authentication
requireAdmin();

$db = Database::getInstance();
$conn = $db->getConnection();

// Get comprehensive dashboard statistics
$stats = [];

// Total users
$userQuery = "SELECT COUNT(*) as total FROM users";
$userStmt = $conn->prepare($userQuery);
$userStmt->execute();
$stats['total_users'] = $userStmt->fetch()['total'];

// Active users (logged in within last 30 days)
$activeUsersQuery = "SELECT COUNT(*) as total FROM users WHERE last_login > NOW() - INTERVAL '30 days'";
$activeUsersStmt = $conn->prepare($activeUsersQuery);
$activeUsersStmt->execute();
$stats['active_users'] = $activeUsersStmt->fetch()['total'];

// New users this month
$newUsersQuery = "SELECT COUNT(*) as total FROM users WHERE DATE_TRUNC('month', created_at) = DATE_TRUNC('month', NOW())";
$newUsersStmt = $conn->prepare($newUsersQuery);
$newUsersStmt->execute();
$stats['new_users_month'] = $newUsersStmt->fetch()['total'];

// Active subscriptions
$subQuery = "SELECT COUNT(*) as total FROM user_subscriptions WHERE status = 'active' AND end_date > NOW()";
$subStmt = $conn->prepare($subQuery);
$subStmt->execute();
$stats['active_subscriptions'] = $subStmt->fetch()['total'];

// Total subscriptions (all time)
$totalSubsQuery = "SELECT COUNT(*) as total FROM user_subscriptions";
$totalSubsStmt = $conn->prepare($totalSubsQuery);
$totalSubsStmt->execute();
$stats['total_subscriptions'] = $totalSubsStmt->fetch()['total'];

// New subscriptions this month
$newSubsQuery = "SELECT COUNT(*) as total FROM user_subscriptions WHERE DATE_TRUNC('month', created_at) = DATE_TRUNC('month', NOW())";
$newSubsStmt = $conn->prepare($newSubsQuery);
$newSubsStmt->execute();
$stats['new_subscriptions_month'] = $newSubsStmt->fetch()['total'];

// Total revenue (all time)
$totalRevenueQuery = "SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE status = 'completed'";
$totalRevenueStmt = $conn->prepare($totalRevenueQuery);
$totalRevenueStmt->execute();
$stats['total_revenue'] = $totalRevenueStmt->fetch()['total'];

// Monthly revenue (this month)
$monthlyRevenueQuery = "SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE status = 'completed' AND DATE_TRUNC('month', created_at) = DATE_TRUNC('month', NOW())";
$monthlyRevenueStmt = $conn->prepare($monthlyRevenueQuery);
$monthlyRevenueStmt->execute();
$stats['monthly_revenue'] = $monthlyRevenueStmt->fetch()['total'];

// Today's revenue
$todayRevenueQuery = "SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE status = 'completed' AND DATE(created_at) = CURRENT_DATE";
$todayRevenueStmt = $conn->prepare($todayRevenueQuery);
$todayRevenueStmt->execute();
$stats['today_revenue'] = $todayRevenueStmt->fetch()['total'];

// Total payments
$totalPaymentsQuery = "SELECT COUNT(*) as total FROM payments";
$totalPaymentsStmt = $conn->prepare($totalPaymentsQuery);
$totalPaymentsStmt->execute();
$stats['total_payments'] = $totalPaymentsStmt->fetch()['total'];

// Completed payments
$completedPaymentsQuery = "SELECT COUNT(*) as total FROM payments WHERE status = 'completed'";
$completedPaymentsStmt = $conn->prepare($completedPaymentsQuery);
$completedPaymentsStmt->execute();
$stats['completed_payments'] = $completedPaymentsStmt->fetch()['total'];

// Pending payments
$pendingQuery = "SELECT COUNT(*) as total FROM payments WHERE status = 'pending'";
$pendingStmt = $conn->prepare($pendingQuery);
$pendingStmt->execute();
$stats['pending_payments'] = $pendingStmt->fetch()['total'];

// Failed payments
$failedQuery = "SELECT COUNT(*) as total FROM payments WHERE status = 'failed'";
$failedStmt = $conn->prepare($failedQuery);
$failedStmt->execute();
$stats['failed_payments'] = $failedStmt->fetch()['total'];

// Payment success rate
$successRate = $stats['total_payments'] > 0 ? ($stats['completed_payments'] / $stats['total_payments']) * 100 : 0;
$stats['payment_success_rate'] = round($successRate, 1);

// Average revenue per user
$avgRevenuePerUser = $stats['total_users'] > 0 ? $stats['total_revenue'] / $stats['total_users'] : 0;
$stats['avg_revenue_per_user'] = round($avgRevenuePerUser, 2);

// Revenue trends (last 6 months)
$revenueTrendQuery = "SELECT 
    DATE_TRUNC('month', created_at) as month,
    COALESCE(SUM(amount), 0) as revenue
    FROM payments 
    WHERE status = 'completed' 
    AND created_at >= NOW() - INTERVAL '6 months'
    GROUP BY DATE_TRUNC('month', created_at)
    ORDER BY month";
$revenueTrendStmt = $conn->prepare($revenueTrendQuery);
$revenueTrendStmt->execute();
$revenueTrends = $revenueTrendStmt->fetchAll();

// User growth trends (last 6 months)
$userGrowthQuery = "SELECT 
    DATE_TRUNC('month', created_at) as month,
    COUNT(*) as new_users
    FROM users 
    WHERE created_at >= NOW() - INTERVAL '6 months'
    GROUP BY DATE_TRUNC('month', created_at)
    ORDER BY month";
$userGrowthStmt = $conn->prepare($userGrowthQuery);
$userGrowthStmt->execute();
$userGrowthTrends = $userGrowthStmt->fetchAll();

// Package performance
$packageStatsQuery = "SELECT 
    p.name as package_name,
    COUNT(us.id) as subscription_count,
    COALESCE(SUM(pay.amount), 0) as total_revenue
    FROM packages p
    LEFT JOIN user_subscriptions us ON p.id = us.package_id
    LEFT JOIN payments pay ON us.id = pay.subscription_id AND pay.status = 'completed'
    GROUP BY p.id, p.name
    ORDER BY subscription_count DESC";
$packageStatsStmt = $conn->prepare($packageStatsQuery);
$packageStatsStmt->execute();
$packageStats = $packageStatsStmt->fetchAll();

// Recent users
$recentUsersQuery = "SELECT * FROM users ORDER BY created_at DESC LIMIT 10";
$recentUsersStmt = $conn->prepare($recentUsersQuery);
$recentUsersStmt->execute();
$recentUsers = $recentUsersStmt->fetchAll();

// Recent payments
$recentPaymentsQuery = "SELECT p.amount, p.status, p.created_at, u.first_name, u.last_name 
    FROM payments p 
                       JOIN users u ON p.user_id = u.id 
    ORDER BY p.created_at DESC 
    LIMIT 10";
$recentPaymentsStmt = $conn->prepare($recentPaymentsQuery);
$recentPaymentsStmt->execute();
$recentPayments = $recentPaymentsStmt->fetchAll();

// Payment methods distribution
$paymentMethodsQuery = "SELECT 
    payment_method,
    COUNT(*) as count,
    COALESCE(SUM(amount), 0) as total_amount
    FROM payments 
    WHERE status = 'completed'
    GROUP BY payment_method
    ORDER BY count DESC";
$paymentMethodsStmt = $conn->prepare($paymentMethodsQuery);
$paymentMethodsStmt->execute();
$paymentMethods = $paymentMethodsStmt->fetchAll();

include 'includes/header.php';
?>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-title">Total Users</div>
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
        </div>
        <div class="stat-value"><?php echo number_format($stats['total_users']); ?></div>
        <div class="stat-change positive">
            <i class="fas fa-arrow-up"></i>
            <?php echo $stats['new_users_month']; ?> this month
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-title">Active Users</div>
            <div class="stat-icon">
                <i class="fas fa-user-check"></i>
            </div>
        </div>
        <div class="stat-value"><?php echo number_format($stats['active_users']); ?></div>
        <div class="stat-change positive">
            <i class="fas fa-arrow-up"></i>
            Last 30 days
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-title">Total Revenue</div>
            <div class="stat-icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
        </div>
        <div class="stat-value">KSh <?php echo number_format($stats['total_revenue'], 2); ?></div>
        <div class="stat-change positive">
            <i class="fas fa-arrow-up"></i>
            KSh <?php echo number_format($stats['monthly_revenue'], 2); ?> this month
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-title">Active Subscriptions</div>
            <div class="stat-icon">
                <i class="fas fa-credit-card"></i>
            </div>
        </div>
        <div class="stat-value"><?php echo number_format($stats['active_subscriptions']); ?></div>
        <div class="stat-change positive">
            <i class="fas fa-arrow-up"></i>
            <?php echo $stats['new_subscriptions_month']; ?> new this month
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-title">Payment Success Rate</div>
            <div class="stat-icon">
                <i class="fas fa-percentage"></i>
            </div>
        </div>
        <div class="stat-value"><?php echo $stats['payment_success_rate']; ?>%</div>
        <div class="stat-change positive">
            <i class="fas fa-arrow-up"></i>
            <?php echo $stats['completed_payments']; ?> completed
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-title">Today's Revenue</div>
            <div class="stat-icon">
                <i class="fas fa-calendar-day"></i>
            </div>
        </div>
        <div class="stat-value">KSh <?php echo number_format($stats['today_revenue'], 2); ?></div>
        <div class="stat-change positive">
            <i class="fas fa-arrow-up"></i>
            Today
        </div>
    </div>
</div>

<!-- Analytics Charts Section -->
<div class="admin-card" style="margin-bottom: 2rem;">
    <div class="card-header">
        <h3 class="card-title">Analytics Dashboard</h3>
    </div>
    <div class="card-body">
        <div class="charts-grid"
            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 2rem;">
            <!-- Revenue Trend Chart -->
            <div class="chart-card"
                style="background: white; border-radius: var(--admin-radius); padding: 1.5rem; box-shadow: var(--admin-shadow);">
                <div class="chart-header" style="margin-bottom: 1rem;">
                    <h3 style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                        <i class="fas fa-chart-line" style="color: var(--admin-primary);"></i>
                        Revenue Trends
                    </h3>
                    <p style="color: var(--admin-text-light); margin: 0;">Monthly revenue for the last 6 months</p>
                </div>
                <div class="chart-container" style="position: relative; height: 300px;">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <!-- User Growth Chart -->
            <div class="chart-card"
                style="background: white; border-radius: var(--admin-radius); padding: 1.5rem; box-shadow: var(--admin-shadow);">
                <div class="chart-header" style="margin-bottom: 1rem;">
                    <h3 style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                        <i class="fas fa-users" style="color: var(--admin-primary);"></i>
                        User Growth
                    </h3>
                    <p style="color: var(--admin-text-light); margin: 0;">New user registrations by month</p>
                </div>
                <div class="chart-container" style="position: relative; height: 300px;">
                    <canvas id="userGrowthChart"></canvas>
                </div>
            </div>

            <!-- Package Performance Chart -->
            <div class="chart-card"
                style="background: white; border-radius: var(--admin-radius); padding: 1.5rem; box-shadow: var(--admin-shadow);">
                <div class="chart-header" style="margin-bottom: 1rem;">
                    <h3 style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                        <i class="fas fa-crown" style="color: var(--admin-primary);"></i>
                        Package Performance
                    </h3>
                    <p style="color: var(--admin-text-light); margin: 0;">Active subscriptions by package</p>
                </div>
                <div class="chart-container" style="position: relative; height: 300px;">
                    <canvas id="packageChart"></canvas>
                </div>
            </div>

            <!-- Payment Methods Chart -->
            <div class="chart-card"
                style="background: white; border-radius: var(--admin-radius); padding: 1.5rem; box-shadow: var(--admin-shadow);">
                <div class="chart-header" style="margin-bottom: 1rem;">
                    <h3 style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                        <i class="fas fa-credit-card" style="color: var(--admin-primary);"></i>
                        Payment Methods
                    </h3>
                    <p style="color: var(--admin-text-light); margin: 0;">Payment distribution by method</p>
                </div>
                <div class="chart-container" style="position: relative; height: 300px;">
                    <canvas id="paymentMethodChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Content Grid -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
    <!-- Recent Users -->
    <div class="admin-card">
        <div class="card-header">
            <h3 class="card-title">Recent Users</h3>
            <a href="users" class="btn btn-secondary">View All</a>
        </div>
        <div class="card-body">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Joined</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentUsers as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Payments -->
    <div class="admin-card">
        <div class="card-header">
            <h3 class="card-title">Recent Payments</h3>
            <a href="payments" class="btn btn-secondary">View All</a>
        </div>
        <div class="card-body">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentPayments as $payment): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']); ?></td>
                            <td>KSh <?php echo number_format($payment['amount'], 2); ?></td>
                            <td>
                                <span
                                    class="badge badge-<?php echo $payment['status'] == 'completed' ? 'success' : ($payment['status'] == 'pending' ? 'warning' : 'danger'); ?>">
                                    <?php echo ucfirst($payment['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M j, Y', strtotime($payment['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="admin-card">
    <div class="card-header">
        <h3 class="card-title">Quick Actions</h3>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <a href="packages" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Manage Packages
            </a>
            <a href="users" class="btn btn-secondary">
                <i class="fas fa-users"></i>
                View Users
            </a>
            <a href="payments" class="btn btn-success">
                <i class="fas fa-money-bill-wave"></i>
                View Payments
            </a>
            <a href="analytics" class="btn btn-primary">
                <i class="fas fa-chart-bar"></i>
                View Analytics
            </a>
        </div>
    </div>
</div>

<style>
    /* Statistics Cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border-radius: var(--admin-radius);
        padding: 1.5rem;
        box-shadow: var(--admin-shadow);
        border: 1px solid var(--admin-border);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        position: relative;
        overflow: hidden;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    .stat-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
    }

    .stat-title {
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--admin-text-light);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        line-height: 1.2;
        word-wrap: break-word;
        overflow-wrap: break-word;
        hyphens: auto;
    }

    .stat-icon {
        width: 40px;
        height: 40px;
        background: var(--admin-primary);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.125rem;
        flex-shrink: 0;
        margin-left: 0.5rem;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: var(--admin-text);
        margin-bottom: 0.5rem;
        line-height: 1.2;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }

    .stat-change {
        display: flex;
        align-items: center;
        gap: 0.25rem;
        font-size: 0.75rem;
        font-weight: 500;
        line-height: 1.2;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }

    .stat-change.positive {
        color: #059669;
    }

    .stat-change.negative {
        color: #DC2626;
    }

    .stat-change.neutral {
        color: var(--admin-text-light);
    }

    .stat-change i {
        font-size: 0.625rem;
    }

    /* Charts Grid */
    .charts-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 2rem;
    }

    .chart-card {
        background: white;
        border-radius: var(--admin-radius);
        padding: 1.5rem;
        box-shadow: var(--admin-shadow);
        border: 1px solid var(--admin-border);
    }

    .chart-header {
        margin-bottom: 1rem;
    }

    .chart-header h3 {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--admin-text);
    }

    .chart-header p {
        color: var(--admin-text-light);
        margin: 0;
        font-size: 0.875rem;
    }

    .chart-container {
        position: relative;
        height: 300px;
    }

    /* Recent Activity */
    .recent-activity {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .activity-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: var(--admin-radius);
        border: 1px solid var(--admin-border);
    }

    .activity-icon {
        width: 40px;
        height: 40px;
        background: var(--admin-primary);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .activity-content {
        flex: 1;
        min-width: 0;
    }

    .activity-title {
        font-weight: 500;
        color: var(--admin-text);
        margin-bottom: 0.25rem;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }

    .activity-meta {
        font-size: 0.75rem;
        color: var(--admin-text-light);
        word-wrap: break-word;
        overflow-wrap: break-word;
    }

    /* Badges */
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

    /* Responsive Design */
    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .charts-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .stat-card {
            padding: 1rem;
        }

        .stat-value {
            font-size: 1.5rem;
        }

        .chart-container {
            height: 250px;
        }
    }

    @media (max-width: 480px) {
        .stat-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }

        .stat-icon {
            margin-left: 0;
            align-self: flex-end;
        }

        .stat-value {
            font-size: 1.25rem;
        }
    }
</style>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Chart data from PHP
    const revenueData = <?php echo json_encode(array_map(function ($item) {
        return [
            'month' => date('M Y', strtotime($item['month'])),
            'revenue' => (float) $item['revenue']
        ];
    }, $revenueTrends)); ?>;

    const userGrowthData = <?php echo json_encode(array_map(function ($item) {
        return [
            'month' => date('M Y', strtotime($item['month'])),
            'users' => (int) $item['new_users']
        ];
    }, $userGrowthTrends)); ?>;

    const packageData = <?php echo json_encode(array_map(function ($item) {
        return [
            'name' => $item['package_name'],
            'count' => (int) $item['subscription_count']
        ];
    }, $packageStats)); ?>;

    const paymentMethodData = <?php echo json_encode(array_map(function ($item) {
        return [
            'method' => $item['payment_method'],
            'count' => (int) $item['count']
        ];
    }, $paymentMethods)); ?>;

    // Revenue Trend Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: revenueData.map(item => item.month),
            datasets: [{
                label: 'Revenue (KSh)',
                data: revenueData.map(item => item.revenue),
                borderColor: '#8B0000',
                backgroundColor: 'rgba(139, 0, 0, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20
                    }
                }
            }
        }
    });

    // User Growth Chart
    const userGrowthCtx = document.getElementById('userGrowthChart').getContext('2d');
    new Chart(userGrowthCtx, {
        type: 'bar',
        data: {
            labels: userGrowthData.map(item => item.month),
            datasets: [{
                label: 'New Users',
                data: userGrowthData.map(item => item.users),
                backgroundColor: 'rgba(139, 0, 0, 0.8)',
                borderColor: '#8B0000',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20
                    }
                }
            }
        }
    });

    // Package Performance Chart
    const packageCtx = document.getElementById('packageChart').getContext('2d');
    new Chart(packageCtx, {
        type: 'doughnut',
        data: {
            labels: packageData.map(item => item.name),
            datasets: [{
                data: packageData.map(item => item.count),
                backgroundColor: [
                    '#8B0000',
                    '#DC143C',
                    '#B22222',
                    '#CD5C5C',
                    '#F08080'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20
                    }
                }
            }
        }
    });

    // Payment Methods Chart
    const paymentMethodCtx = document.getElementById('paymentMethodChart').getContext('2d');
    new Chart(paymentMethodCtx, {
        type: 'pie',
        data: {
            labels: paymentMethodData.map(item => item.method),
            datasets: [{
                data: paymentMethodData.map(item => item.count),
                backgroundColor: [
                    '#8B0000',
                    '#DC143C',
                    '#B22222',
                    '#CD5C5C',
                    '#F08080'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20
                    }
                }
            }
        }
    });
</script>

<?php include 'includes/footer.php'; ?>