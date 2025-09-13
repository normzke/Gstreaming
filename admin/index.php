<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check admin authentication
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$db = new Database();
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
$recentPaymentsQuery = "SELECT p.*, u.first_name, u.last_name, pk.name as package_name FROM payments p 
                       JOIN users u ON p.user_id = u.id 
                       LEFT JOIN user_subscriptions us ON p.subscription_id = us.id
                       LEFT JOIN packages pk ON us.package_id = pk.id
                       ORDER BY p.created_at DESC LIMIT 10";
$recentPaymentsStmt = $conn->prepare($recentPaymentsQuery);
$recentPaymentsStmt->execute();
$recentPayments = $recentPaymentsStmt->fetchAll();

// Top performing packages
$topPackagesQuery = "SELECT 
    p.name,
    p.price,
    COUNT(us.id) as active_subscriptions,
    COALESCE(SUM(pay.amount), 0) as revenue
    FROM packages p
    LEFT JOIN user_subscriptions us ON p.id = us.package_id AND us.status = 'active' AND us.end_date > NOW()
    LEFT JOIN payments pay ON us.id = pay.subscription_id AND pay.status = 'completed'
    GROUP BY p.id, p.name, p.price
    ORDER BY active_subscriptions DESC
    LIMIT 5";
$topPackagesStmt = $conn->prepare($topPackagesQuery);
$topPackagesStmt->execute();
$topPackages = $topPackagesStmt->fetchAll();

// Payment methods breakdown
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - GStreaming</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/admin-analytics.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="admin-page">
    <!-- Admin Navigation -->
    <nav class="admin-navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <i class="fas fa-satellite-dish"></i>
                <span class="logo-text">GStreaming Admin</span>
            </div>
            
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="index.php" class="nav-link active">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a href="users.php" class="nav-link">Users</a>
                </li>
                <li class="nav-item">
                    <a href="packages.php" class="nav-link">Packages</a>
                </li>
                <li class="nav-item">
                    <a href="payments.php" class="nav-link">Payments</a>
                </li>
                <li class="nav-item">
                    <a href="channels.php" class="nav-link">Channels</a>
                </li>
                <li class="nav-item">
                    <a href="gallery.php" class="nav-link">Gallery</a>
                </li>
                <li class="nav-item">
                    <a href="mpesa-config.php" class="nav-link">M-PESA Config</a>
                </li>
                <li class="nav-item">
                    <a href="settings.php" class="nav-link">Settings</a>
                </li>
                <li class="nav-item">
                    <a href="../logout.php" class="nav-link">Logout</a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Admin Dashboard -->
    <main class="admin-main">
        <div class="container">
            <div class="admin-header">
                <h1>Admin Dashboard</h1>
                <p>Manage your streaming platform</p>
            </div>
            
            <!-- Comprehensive Statistics Cards -->
            <div class="stats-grid">
                <!-- User Statistics -->
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo number_format($stats['total_users']); ?></div>
                        <div class="stat-label">Total Users</div>
                        <div class="stat-change positive">+<?php echo $stats['new_users_month']; ?> this month</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo number_format($stats['active_users']); ?></div>
                        <div class="stat-label">Active Users (30d)</div>
                        <div class="stat-change">Recently active</div>
                    </div>
                </div>
                
                <!-- Subscription Statistics -->
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-tv"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo number_format($stats['active_subscriptions']); ?></div>
                        <div class="stat-label">Active Subscriptions</div>
                        <div class="stat-change positive">+<?php echo $stats['new_subscriptions_month']; ?> this month</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo number_format($stats['total_subscriptions']); ?></div>
                        <div class="stat-label">Total Subscriptions</div>
                        <div class="stat-change">All time</div>
                    </div>
                </div>
                
                <!-- Revenue Statistics -->
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-number">KES <?php echo number_format($stats['total_revenue']); ?></div>
                        <div class="stat-label">Total Revenue</div>
                        <div class="stat-change">All time</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-month"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-number">KES <?php echo number_format($stats['monthly_revenue']); ?></div>
                        <div class="stat-label">Monthly Revenue</div>
                        <div class="stat-change">This month</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-number">KES <?php echo number_format($stats['today_revenue']); ?></div>
                        <div class="stat-label">Today's Revenue</div>
                        <div class="stat-change">Today</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-user-dollar"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-number">KES <?php echo number_format($stats['avg_revenue_per_user']); ?></div>
                        <div class="stat-label">Avg Revenue/User</div>
                        <div class="stat-change">Lifetime value</div>
                    </div>
                </div>
                
                <!-- Payment Statistics -->
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo number_format($stats['total_payments']); ?></div>
                        <div class="stat-label">Total Payments</div>
                        <div class="stat-change">All time</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo number_format($stats['completed_payments']); ?></div>
                        <div class="stat-label">Completed Payments</div>
                        <div class="stat-change positive"><?php echo $stats['payment_success_rate']; ?>% success rate</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo number_format($stats['pending_payments']); ?></div>
                        <div class="stat-label">Pending Payments</div>
                        <div class="stat-change warning">Awaiting confirmation</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo number_format($stats['failed_payments']); ?></div>
                        <div class="stat-label">Failed Payments</div>
                        <div class="stat-change error">Need attention</div>
                    </div>
                </div>
            </div>
            
            <!-- Analytics Charts -->
            <div class="analytics-section">
                <div class="charts-grid">
                    <!-- Revenue Trend Chart -->
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3><i class="fas fa-chart-line"></i> Revenue Trends</h3>
                            <p>Monthly revenue for the last 6 months</p>
                        </div>
                        <div class="chart-container">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>
                    
                    <!-- User Growth Chart -->
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3><i class="fas fa-users"></i> User Growth</h3>
                            <p>New user registrations by month</p>
                        </div>
                        <div class="chart-container">
                            <canvas id="userGrowthChart"></canvas>
                        </div>
                    </div>
                    
                    <!-- Package Performance Chart -->
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3><i class="fas fa-crown"></i> Package Performance</h3>
                            <p>Active subscriptions by package</p>
                        </div>
                        <div class="chart-container">
                            <canvas id="packageChart"></canvas>
                        </div>
                    </div>
                    
                    <!-- Payment Methods Chart -->
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3><i class="fas fa-credit-card"></i> Payment Methods</h3>
                            <p>Payment distribution by method</p>
                        </div>
                        <div class="chart-container">
                            <canvas id="paymentMethodChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Performance Metrics -->
            <div class="performance-section">
                <div class="metrics-grid">
                    <!-- Top Performing Packages -->
                    <div class="metrics-card">
                        <div class="metrics-header">
                            <h3><i class="fas fa-trophy"></i> Top Performing Packages</h3>
                        </div>
                        <div class="metrics-content">
                            <?php foreach ($topPackages as $index => $package): ?>
                                <div class="metric-item">
                                    <div class="metric-rank">#<?php echo $index + 1; ?></div>
                                    <div class="metric-info">
                                        <div class="metric-name"><?php echo htmlspecialchars($package['name']); ?></div>
                                        <div class="metric-details">
                                            <span class="metric-value"><?php echo $package['active_subscriptions']; ?> active</span>
                                            <span class="metric-value">KES <?php echo number_format($package['revenue']); ?> revenue</span>
                                        </div>
                                    </div>
                                    <div class="metric-price">KES <?php echo number_format($package['price']); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Payment Status Breakdown -->
                    <div class="metrics-card">
                        <div class="metrics-header">
                            <h3><i class="fas fa-chart-pie"></i> Payment Status</h3>
                        </div>
                        <div class="metrics-content">
                            <div class="status-breakdown">
                                <div class="status-item success">
                                    <div class="status-icon"><i class="fas fa-check-circle"></i></div>
                                    <div class="status-info">
                                        <div class="status-label">Completed</div>
                                        <div class="status-value"><?php echo number_format($stats['completed_payments']); ?></div>
                                        <div class="status-percentage"><?php echo $stats['payment_success_rate']; ?>%</div>
                                    </div>
                                </div>
                                
                                <div class="status-item warning">
                                    <div class="status-icon"><i class="fas fa-clock"></i></div>
                                    <div class="status-info">
                                        <div class="status-label">Pending</div>
                                        <div class="status-value"><?php echo number_format($stats['pending_payments']); ?></div>
                                        <div class="status-percentage">
                                            <?php echo $stats['total_payments'] > 0 ? round(($stats['pending_payments'] / $stats['total_payments']) * 100, 1) : 0; ?>%
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="status-item error">
                                    <div class="status-icon"><i class="fas fa-times-circle"></i></div>
                                    <div class="status-info">
                                        <div class="status-label">Failed</div>
                                        <div class="status-value"><?php echo number_format($stats['failed_payments']); ?></div>
                                        <div class="status-percentage">
                                            <?php echo $stats['total_payments'] > 0 ? round(($stats['failed_payments'] / $stats['total_payments']) * 100, 1) : 0; ?>%
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Dashboard Content -->
            <div class="dashboard-content">
                <!-- Recent Users -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>Recent Users</h3>
                        <a href="users.php" class="btn btn-secondary btn-sm">View All</a>
                    </div>
                    
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Joined</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentUsers as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                    <td><?php echo formatDate($user['created_at'], 'M j, Y'); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $user['is_active'] ? 'active' : 'inactive'; ?>">
                                            <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Recent Payments -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>Recent Payments</h3>
                        <a href="payments.php" class="btn btn-secondary btn-sm">View All</a>
                    </div>
                    
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Package</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentPayments as $payment): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($payment['package_name'] ?: 'N/A'); ?></td>
                                    <td><?php echo formatCurrency($payment['amount']); ?></td>
                                    <td><?php echo strtoupper($payment['payment_method']); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $payment['status']; ?>">
                                            <?php echo ucfirst($payment['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo formatDate($payment['created_at'], 'M j, Y'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="quick-actions">
                    <h3>Quick Actions</h3>
                    <div class="actions-grid">
                        <a href="users.php?action=add" class="action-card">
                            <div class="action-icon">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="action-content">
                                <h4>Add User</h4>
                                <p>Create a new user account</p>
                            </div>
                        </a>
                        
                        <a href="packages.php?action=add" class="action-card">
                            <div class="action-icon">
                                <i class="fas fa-plus"></i>
                            </div>
                            <div class="action-content">
                                <h4>Add Package</h4>
                                <p>Create a new subscription package</p>
                            </div>
                        </a>
                        
                        <a href="channels.php?action=add" class="action-card">
                            <div class="action-icon">
                                <i class="fas fa-broadcast-tower"></i>
                            </div>
                            <div class="action-content">
                                <h4>Add Channel</h4>
                                <p>Add a new streaming channel</p>
                            </div>
                        </a>
                        
                        <a href="gallery.php?action=add" class="action-card">
                            <div class="action-icon">
                                <i class="fas fa-images"></i>
                            </div>
                            <div class="action-content">
                                <h4>Add Gallery Item</h4>
                                <p>Upload new content to gallery</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- JavaScript -->
    <script src="../assets/js/main.js"></script>
    <script src="assets/js/admin.js"></script>
    
    <script>
        // Chart data from PHP
        const revenueData = <?php echo json_encode(array_map(function($item) {
            return [
                'month' => date('M Y', strtotime($item['month'])),
                'revenue' => (float)$item['revenue']
            ];
        }, $revenueTrends)); ?>;
        
        const userGrowthData = <?php echo json_encode(array_map(function($item) {
            return [
                'month' => date('M Y', strtotime($item['month'])),
                'users' => (int)$item['new_users']
            ];
        }, $userGrowthTrends)); ?>;
        
        const packageData = <?php echo json_encode(array_map(function($item) {
            return [
                'name' => $item['name'],
                'subscriptions' => (int)$item['active_subscriptions']
            ];
        }, $topPackages)); ?>;
        
        const paymentMethodData = <?php echo json_encode(array_map(function($item) {
            return [
                'method' => $item['payment_method'],
                'count' => (int)$item['count'],
                'amount' => (float)$item['total_amount']
            ];
        }, $paymentMethods)); ?>;
        
        // Revenue Trend Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: revenueData.map(item => item.month),
                datasets: [{
                    label: 'Revenue (KES)',
                    data: revenueData.map(item => item.revenue),
                    borderColor: '#00d4ff',
                    backgroundColor: 'rgba(0, 212, 255, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'KES ' + value.toLocaleString();
                            }
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
                    backgroundColor: '#10b981',
                    borderColor: '#059669',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
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
                    data: packageData.map(item => item.subscriptions),
                    backgroundColor: [
                        '#00d4ff',
                        '#10b981',
                        '#f59e0b',
                        '#ef4444',
                        '#8b5cf6'
                    ],
                    borderWidth: 0
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
                labels: paymentMethodData.map(item => item.method.toUpperCase()),
                datasets: [{
                    data: paymentMethodData.map(item => item.count),
                    backgroundColor: [
                        '#00d4ff',
                        '#10b981',
                        '#f59e0b',
                        '#ef4444'
                    ],
                    borderWidth: 0
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
        
        // Auto-refresh dashboard data every 5 minutes
        setInterval(function() {
            location.reload();
        }, 300000);
    </script>
</body>
</html>
