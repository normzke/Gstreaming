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

// Get analytics data
$analytics = [];

// Revenue analytics
$revenueQuery = "SELECT 
    DATE_TRUNC('month', created_at) as month,
    COUNT(*) as total_payments,
    COALESCE(SUM(amount), 0) as total_revenue,
    COALESCE(AVG(amount), 0) as avg_payment
    FROM payments 
    WHERE status = 'completed' 
    AND created_at >= NOW() - INTERVAL '12 months'
    GROUP BY DATE_TRUNC('month', created_at)
    ORDER BY month";
$revenueStmt = $conn->prepare($revenueQuery);
$revenueStmt->execute();
$analytics['revenue'] = $revenueStmt->fetchAll();

// User growth analytics
$userGrowthQuery = "SELECT 
    DATE_TRUNC('month', created_at) as month,
    COUNT(*) as new_users
    FROM users 
    WHERE created_at >= NOW() - INTERVAL '12 months'
    GROUP BY DATE_TRUNC('month', created_at)
    ORDER BY month";
$userGrowthStmt = $conn->prepare($userGrowthQuery);
$userGrowthStmt->execute();
$analytics['user_growth'] = $userGrowthStmt->fetchAll();

// Package performance
$packagePerfQuery = "SELECT 
    p.name as package_name,
    COUNT(us.id) as subscription_count,
    COALESCE(SUM(pay.amount), 0) as total_revenue
    FROM packages p
    LEFT JOIN user_subscriptions us ON p.id = us.package_id
    LEFT JOIN payments pay ON us.id = pay.subscription_id AND pay.status = 'completed'
    GROUP BY p.id, p.name
    ORDER BY subscription_count DESC";
$packagePerfStmt = $conn->prepare($packagePerfQuery);
$packagePerfStmt->execute();
$analytics['package_performance'] = $packagePerfStmt->fetchAll();

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
$analytics['payment_methods'] = $paymentMethodsStmt->fetchAll();

$page_title = 'Analytics';
include 'includes/header.php';
?>

<!-- Analytics Overview -->
<div class="admin-card">
    <div class="card-header">
        <h2 class="card-title">Analytics Dashboard</h2>
        <p>Comprehensive insights into your streaming platform performance</p>
    </div>
    <div class="card-body">
        <div class="analytics-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
            <!-- Revenue Chart -->
            <div class="chart-container">
                <h3>Revenue Trends</h3>
                <canvas id="revenueChart" width="400" height="200"></canvas>
            </div>
            
            <!-- User Growth Chart -->
            <div class="chart-container">
                <h3>User Growth</h3>
                <canvas id="userGrowthChart" width="400" height="200"></canvas>
            </div>
            
            <!-- Package Performance Chart -->
            <div class="chart-container">
                <h3>Package Performance</h3>
                <canvas id="packageChart" width="400" height="200"></canvas>
            </div>
            
            <!-- Payment Methods Chart -->
            <div class="chart-container">
                <h3>Payment Methods</h3>
                <canvas id="paymentMethodsChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Analytics Tables -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-top: 2rem;">
    <!-- Package Performance Table -->
    <div class="admin-card">
        <div class="card-header">
            <h3 class="card-title">Package Performance</h3>
        </div>
        <div class="card-body">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Package</th>
                        <th>Subscriptions</th>
                        <th>Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($analytics['package_performance'] as $package): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($package['package_name']); ?></td>
                        <td><?php echo number_format($package['subscription_count']); ?></td>
                        <td>KSh <?php echo number_format($package['total_revenue'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Payment Methods Table -->
    <div class="admin-card">
        <div class="card-header">
            <h3 class="card-title">Payment Methods</h3>
        </div>
        <div class="card-body">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Method</th>
                        <th>Count</th>
                        <th>Total Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($analytics['payment_methods'] as $method): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($method['payment_method']); ?></td>
                        <td><?php echo number_format($method['count']); ?></td>
                        <td>KSh <?php echo number_format($method['total_amount'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Revenue Chart
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_map(function($item) {
            return date('M Y', strtotime($item['month']));
        }, $analytics['revenue'])); ?>,
        datasets: [{
            label: 'Revenue (KSh)',
            data: <?php echo json_encode(array_map(function($item) {
                return (float)$item['total_revenue'];
            }, $analytics['revenue'])); ?>,
            borderColor: '#8B0000',
            backgroundColor: 'rgba(139, 0, 0, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

// User Growth Chart
const userGrowthCtx = document.getElementById('userGrowthChart').getContext('2d');
new Chart(userGrowthCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_map(function($item) {
            return date('M Y', strtotime($item['month']));
        }, $analytics['user_growth'])); ?>,
        datasets: [{
            label: 'New Users',
            data: <?php echo json_encode(array_map(function($item) {
                return (int)$item['new_users'];
            }, $analytics['user_growth'])); ?>,
            backgroundColor: 'rgba(139, 0, 0, 0.8)',
            borderColor: '#8B0000',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

// Package Performance Chart
const packageCtx = document.getElementById('packageChart').getContext('2d');
new Chart(packageCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(array_map(function($item) {
            return $item['package_name'];
        }, $analytics['package_performance'])); ?>,
        datasets: [{
            data: <?php echo json_encode(array_map(function($item) {
                return (int)$item['subscription_count'];
            }, $analytics['package_performance'])); ?>,
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
        maintainAspectRatio: false
    }
});

// Payment Methods Chart
const paymentMethodsCtx = document.getElementById('paymentMethodsChart').getContext('2d');
new Chart(paymentMethodsCtx, {
    type: 'pie',
    data: {
        labels: <?php echo json_encode(array_map(function($item) {
            return $item['payment_method'];
        }, $analytics['payment_methods'])); ?>,
        datasets: [{
            data: <?php echo json_encode(array_map(function($item) {
                return (int)$item['count'];
            }, $analytics['payment_methods'])); ?>,
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
        maintainAspectRatio: false
    }
});
</script>

<style>
.analytics-grid {
    margin-bottom: 2rem;
}

.chart-container {
    background: white;
    border-radius: var(--admin-radius);
    padding: 1.5rem;
    box-shadow: var(--admin-shadow);
    border: 1px solid var(--admin-border);
}

.chart-container h3 {
    margin-bottom: 1rem;
    color: var(--admin-text);
    font-size: 1.125rem;
    font-weight: 600;
}

.chart-container canvas {
    max-height: 200px;
}

@media (max-width: 768px) {
    .analytics-grid {
        grid-template-columns: 1fr;
    }
    
    .admin-card {
        margin-bottom: 1rem;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
