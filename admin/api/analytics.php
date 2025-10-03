<?php
/**
 * Admin Analytics API
 * Provides real-time analytics data for the admin dashboard
 */

require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../lib/functions.php';

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

$db = new Database();
$conn = $db->getConnection();

try {
    $action = $_GET['action'] ?? 'dashboard';
    
    switch ($action) {
        case 'dashboard':
            $analytics = getDashboardAnalytics($conn);
            echo json_encode($analytics);
            break;
            
        case 'revenue_trends':
            $period = $_GET['period'] ?? '6months';
            $trends = getRevenueTrends($conn, $period);
            echo json_encode($trends);
            break;
            
        case 'user_growth':
            $period = $_GET['period'] ?? '6months';
            $growth = getUserGrowth($conn, $period);
            echo json_encode($growth);
            break;
            
        case 'package_performance':
            $performance = getPackagePerformance($conn);
            echo json_encode($performance);
            break;
            
        case 'payment_methods':
            $methods = getPaymentMethods($conn);
            echo json_encode($methods);
            break;
            
        case 'real_time_stats':
            $stats = getRealTimeStats($conn);
            echo json_encode($stats);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

function getDashboardAnalytics($conn) {
    $analytics = [];
    
    // Total users
    $stmt = $conn->query("SELECT COUNT(*) as total FROM users");
    $analytics['total_users'] = $stmt->fetch()['total'];
    
    // Active users (last 30 days)
    $stmt = $conn->query("SELECT COUNT(*) as total FROM users WHERE last_login > NOW() - INTERVAL '30 days'");
    $analytics['active_users'] = $stmt->fetch()['total'];
    
    // New users this month
    $stmt = $conn->query("SELECT COUNT(*) as total FROM users WHERE DATE_TRUNC('month', created_at) = DATE_TRUNC('month', NOW())");
    $analytics['new_users_month'] = $stmt->fetch()['total'];
    
    // Active subscriptions
    $stmt = $conn->query("SELECT COUNT(*) as total FROM user_subscriptions WHERE status = 'active' AND end_date > NOW()");
    $analytics['active_subscriptions'] = $stmt->fetch()['total'];
    
    // Total revenue
    $stmt = $conn->query("SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE status = 'completed'");
    $analytics['total_revenue'] = $stmt->fetch()['total'];
    
    // Monthly revenue
    $stmt = $conn->query("SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE status = 'completed' AND DATE_TRUNC('month', created_at) = DATE_TRUNC('month', NOW())");
    $analytics['monthly_revenue'] = $stmt->fetch()['total'];
    
    // Today's revenue
    $stmt = $conn->query("SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE status = 'completed' AND DATE(created_at) = CURRENT_DATE");
    $analytics['today_revenue'] = $stmt->fetch()['total'];
    
    // Payment statistics
    $stmt = $conn->query("SELECT COUNT(*) as total FROM payments");
    $totalPayments = $stmt->fetch()['total'];
    
    $stmt = $conn->query("SELECT COUNT(*) as total FROM payments WHERE status = 'completed'");
    $completedPayments = $stmt->fetch()['total'];
    
    $stmt = $conn->query("SELECT COUNT(*) as total FROM payments WHERE status = 'pending'");
    $analytics['pending_payments'] = $stmt->fetch()['total'];
    
    $stmt = $conn->query("SELECT COUNT(*) as total FROM payments WHERE status = 'failed'");
    $analytics['failed_payments'] = $stmt->fetch()['total'];
    
    $analytics['total_payments'] = $totalPayments;
    $analytics['completed_payments'] = $completedPayments;
    $analytics['payment_success_rate'] = $totalPayments > 0 ? round(($completedPayments / $totalPayments) * 100, 1) : 0;
    
    return $analytics;
}

function getRevenueTrends($conn, $period) {
    $interval = match($period) {
        '1month' => "1 month",
        '3months' => "3 months",
        '6months' => "6 months",
        '1year' => "1 year",
        default => "6 months"
    };
    
    $stmt = $conn->prepare("
        SELECT 
            DATE_TRUNC('month', created_at) as month,
            COALESCE(SUM(amount), 0) as revenue
        FROM payments 
        WHERE status = 'completed' 
        AND created_at >= NOW() - INTERVAL ?
        GROUP BY DATE_TRUNC('month', created_at)
        ORDER BY month
    ");
    $stmt->execute([$interval]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUserGrowth($conn, $period) {
    $interval = match($period) {
        '1month' => "1 month",
        '3months' => "3 months",
        '6months' => "6 months",
        '1year' => "1 year",
        default => "6 months"
    };
    
    $stmt = $conn->prepare("
        SELECT 
            DATE_TRUNC('month', created_at) as month,
            COUNT(*) as new_users
        FROM users 
        WHERE created_at >= NOW() - INTERVAL ?
        GROUP BY DATE_TRUNC('month', created_at)
        ORDER BY month
    ");
    $stmt->execute([$interval]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getPackagePerformance($conn) {
    $stmt = $conn->query("
        SELECT 
            p.name,
            p.price,
            COUNT(us.id) as active_subscriptions,
            COALESCE(SUM(pay.amount), 0) as revenue
        FROM packages p
        LEFT JOIN user_subscriptions us ON p.id = us.package_id AND us.status = 'active' AND us.end_date > NOW()
        LEFT JOIN payments pay ON us.id = pay.subscription_id AND pay.status = 'completed'
        GROUP BY p.id, p.name, p.price
        ORDER BY active_subscriptions DESC
    ");
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getPaymentMethods($conn) {
    $stmt = $conn->query("
        SELECT 
            payment_method,
            COUNT(*) as count,
            COALESCE(SUM(amount), 0) as total_amount
        FROM payments 
        WHERE status = 'completed'
        GROUP BY payment_method
        ORDER BY count DESC
    ");
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getRealTimeStats($conn) {
    $stats = [];
    
    // Today's stats
    $stmt = $conn->query("SELECT COUNT(*) as total FROM users WHERE DATE(created_at) = CURRENT_DATE");
    $stats['new_users_today'] = $stmt->fetch()['total'];
    
    $stmt = $conn->query("SELECT COUNT(*) as total FROM user_subscriptions WHERE DATE(created_at) = CURRENT_DATE");
    $stats['new_subscriptions_today'] = $stmt->fetch()['total'];
    
    $stmt = $conn->query("SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE status = 'completed' AND DATE(created_at) = CURRENT_DATE");
    $stats['revenue_today'] = $stmt->fetch()['total'];
    
    // This week's stats
    $stmt = $conn->query("SELECT COUNT(*) as total FROM users WHERE created_at >= DATE_TRUNC('week', NOW())");
    $stats['new_users_week'] = $stmt->fetch()['total'];
    
    $stmt = $conn->query("SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE status = 'completed' AND created_at >= DATE_TRUNC('week', NOW())");
    $stats['revenue_week'] = $stmt->fetch()['total'];
    
    // Online users (last 24 hours)
    $stmt = $conn->query("SELECT COUNT(*) as total FROM users WHERE last_login > NOW() - INTERVAL '24 hours'");
    $stats['online_users'] = $stmt->fetch()['total'];
    
    // Expiring subscriptions (next 7 days)
    $stmt = $conn->query("SELECT COUNT(*) as total FROM user_subscriptions WHERE end_date BETWEEN NOW() AND NOW() + INTERVAL '7 days' AND status = 'active'");
    $stats['expiring_subscriptions'] = $stmt->fetch()['total'];
    
    return $stats;
}
?>
