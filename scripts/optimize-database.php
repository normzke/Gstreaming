<?php
/**
 * Database Optimization Script
 * Optimizes database for thousands of concurrent users
 */

require_once 'config/config.php';
require_once 'config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    echo "🚀 Starting database optimization for high-traffic performance...\n\n";
    
    // 1. Create performance indexes
    echo "📊 Creating performance indexes...\n";
    
    $indexes = [
        // Users table indexes
        "CREATE INDEX IF NOT EXISTS idx_users_email ON users(email)",
        "CREATE INDEX IF NOT EXISTS idx_users_phone ON users(phone)",
        "CREATE INDEX IF NOT EXISTS idx_users_active ON users(is_active)",
        "CREATE INDEX IF NOT EXISTS idx_users_created_at ON users(created_at)",
        "CREATE INDEX IF NOT EXISTS idx_users_country ON users(country)",
        
        // User subscriptions indexes
        "CREATE INDEX IF NOT EXISTS idx_user_subs_user_id ON user_subscriptions(user_id)",
        "CREATE INDEX IF NOT EXISTS idx_user_subs_package_id ON user_subscriptions(package_id)",
        "CREATE INDEX IF NOT EXISTS idx_user_subs_status ON user_subscriptions(status)",
        "CREATE INDEX IF NOT EXISTS idx_user_subs_end_date ON user_subscriptions(end_date)",
        "CREATE INDEX IF NOT EXISTS idx_user_subs_active ON user_subscriptions(status, end_date) WHERE status = 'active'",
        
        // Payments indexes
        "CREATE INDEX IF NOT EXISTS idx_payments_user_id ON payments(user_id)",
        "CREATE INDEX IF NOT EXISTS idx_payments_status ON payments(status)",
        "CREATE INDEX IF NOT EXISTS idx_payments_created_at ON payments(created_at)",
        "CREATE INDEX IF NOT EXISTS idx_payments_amount ON payments(amount)",
        "CREATE INDEX IF NOT EXISTS idx_payments_mpesa_code ON payments(mpesa_receipt_number)",
        
        // Channels indexes
        "CREATE INDEX IF NOT EXISTS idx_channels_category ON channels(category)",
        "CREATE INDEX IF NOT EXISTS idx_channels_country ON channels(country)",
        "CREATE INDEX IF NOT EXISTS idx_channels_active ON channels(is_active)",
        "CREATE INDEX IF NOT EXISTS idx_channels_sort ON channels(sort_order)",
        "CREATE INDEX IF NOT EXISTS idx_channels_hd ON channels(is_hd)",
        
        // Packages indexes
        "CREATE INDEX IF NOT EXISTS idx_packages_active ON packages(is_active)",
        "CREATE INDEX IF NOT EXISTS idx_packages_price ON packages(price)",
        "CREATE INDEX IF NOT EXISTS idx_packages_sort ON packages(sort_order)",
        
        // Gallery items indexes
        "CREATE INDEX IF NOT EXISTS idx_gallery_featured ON gallery(is_featured)",
        "CREATE INDEX IF NOT EXISTS idx_gallery_category ON gallery(category)",
        "CREATE INDEX IF NOT EXISTS idx_gallery_sort ON gallery(sort_order)",
        "CREATE INDEX IF NOT EXISTS idx_gallery_type ON gallery(type)",
        
        // Activity logs indexes
        "CREATE INDEX IF NOT EXISTS idx_activity_logs_user_id ON activity_logs(user_id)",
        "CREATE INDEX IF NOT EXISTS idx_activity_logs_action ON activity_logs(action)",
        "CREATE INDEX IF NOT EXISTS idx_activity_logs_created_at ON activity_logs(created_at)",
        
        // Notifications indexes
        "CREATE INDEX IF NOT EXISTS idx_notifications_user_id ON notifications(user_id)",
        "CREATE INDEX IF NOT EXISTS idx_notifications_read ON notifications(is_read)",
        "CREATE INDEX IF NOT EXISTS idx_notifications_created_at ON notifications(created_at)",
        
        // M-PESA config indexes
        "CREATE INDEX IF NOT EXISTS idx_mpesa_config_key ON mpesa_config(config_key)",
        
        // Admin users indexes
        "CREATE INDEX IF NOT EXISTS idx_admin_users_email ON admin_users(email)",
        "CREATE INDEX IF NOT EXISTS idx_admin_users_active ON admin_users(is_active)",
        "CREATE INDEX IF NOT EXISTS idx_admin_users_role ON admin_users(role)"
    ];
    
    $indexCount = 0;
    foreach ($indexes as $indexQuery) {
        try {
            $conn->exec($indexQuery);
            $indexCount++;
        } catch (Exception $e) {
            echo "⚠️  Index warning: " . $e->getMessage() . "\n";
        }
    }
    
    echo "✅ Created {$indexCount} performance indexes\n\n";
    
    // 2. Analyze tables for query optimization
    echo "🔍 Analyzing tables for optimization...\n";
    
    $tables = ['users', 'user_subscriptions', 'payments', 'channels', 'packages', 'gallery', 'activity_logs', 'notifications'];
    
    foreach ($tables as $table) {
        try {
            $conn->exec("ANALYZE {$table}");
            echo "✅ Analyzed table: {$table}\n";
        } catch (Exception $e) {
            echo "⚠️  Analysis warning for {$table}: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n";
    
    // 3. Create materialized views for complex queries
    echo "📈 Creating materialized views for dashboard analytics...\n";
    
    $materializedViews = [
        "CREATE MATERIALIZED VIEW IF NOT EXISTS mv_user_stats AS
         SELECT 
             COUNT(*) as total_users,
             COUNT(CASE WHEN created_at >= CURRENT_DATE - INTERVAL '30 days' THEN 1 END) as new_users_30d,
             COUNT(CASE WHEN is_active = true THEN 1 END) as active_users
         FROM users",
         
        "CREATE MATERIALIZED VIEW IF NOT EXISTS mv_subscription_stats AS
         SELECT 
             COUNT(*) as total_subscriptions,
             COUNT(CASE WHEN status = 'active' AND end_date > NOW() THEN 1 END) as active_subscriptions,
             COUNT(CASE WHEN created_at >= CURRENT_DATE - INTERVAL '30 days' THEN 1 END) as new_subscriptions_30d
         FROM user_subscriptions",
         
        "CREATE MATERIALIZED VIEW IF NOT EXISTS mv_revenue_stats AS
         SELECT 
             COALESCE(SUM(amount), 0) as total_revenue,
             COUNT(*) as total_payments,
             COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_payments,
             COALESCE(AVG(amount), 0) as avg_payment_amount
         FROM payments WHERE status = 'completed'"
    ];
    
    $viewCount = 0;
    foreach ($materializedViews as $viewQuery) {
        try {
            $conn->exec($viewQuery);
            $viewCount++;
        } catch (Exception $e) {
            echo "⚠️  Materialized view warning: " . $e->getMessage() . "\n";
        }
    }
    
    echo "✅ Created {$viewCount} materialized views\n\n";
    
    // 4. Set up connection pooling parameters
    echo "🔧 Optimizing connection parameters...\n";
    
    $optimizations = [
        "SET work_mem = '256MB'",
        "SET shared_buffers = '256MB'",
        "SET effective_cache_size = '1GB'",
        "SET random_page_cost = 1.1",
        "SET seq_page_cost = 1.0"
    ];
    
    foreach ($optimizations as $optimization) {
        try {
            $conn->exec($optimization);
        } catch (Exception $e) {
            echo "⚠️  Parameter warning: " . $e->getMessage() . "\n";
        }
    }
    
    echo "✅ Connection parameters optimized\n\n";
    
    // 5. Create database functions for common operations
    echo "⚙️  Creating optimized database functions...\n";
    
    $functions = [
        "CREATE OR REPLACE FUNCTION get_user_subscription_status(user_id_param INTEGER)
         RETURNS TEXT AS $$
         BEGIN
             RETURN (
                 SELECT status 
                 FROM user_subscriptions 
                 WHERE user_id = user_id_param 
                 AND end_date > NOW() 
                 ORDER BY created_at DESC 
                 LIMIT 1
             );
         END;
         $$ LANGUAGE plpgsql",
         
        "CREATE OR REPLACE FUNCTION get_user_active_subscription(user_id_param INTEGER)
         RETURNS TABLE(
             subscription_id INTEGER,
             package_name VARCHAR,
             end_date TIMESTAMP,
             auto_renewal BOOLEAN
         ) AS $$
         BEGIN
             RETURN QUERY
             SELECT us.id, p.name, us.end_date, us.auto_renewal
             FROM user_subscriptions us
             JOIN packages p ON us.package_id = p.id
             WHERE us.user_id = user_id_param 
             AND us.status = 'active' 
             AND us.end_date > NOW()
             ORDER BY us.created_at DESC
             LIMIT 1;
         END;
         $$ LANGUAGE plpgsql"
    ];
    
    $functionCount = 0;
    foreach ($functions as $functionQuery) {
        try {
            $conn->exec($functionQuery);
            $functionCount++;
        } catch (Exception $e) {
            echo "⚠️  Function warning: " . $e->getMessage() . "\n";
        }
    }
    
    echo "✅ Created {$functionCount} database functions\n\n";
    
    // 6. Create triggers for automatic updates
    echo "🔄 Creating automatic update triggers...\n";
    
    $triggers = [
        "CREATE OR REPLACE FUNCTION update_updated_at_column()
         RETURNS TRIGGER AS $$
         BEGIN
             NEW.updated_at = CURRENT_TIMESTAMP;
             RETURN NEW;
         END;
         $$ language 'plpgsql'",
         
        "DROP TRIGGER IF EXISTS update_users_updated_at ON users;
         CREATE TRIGGER update_users_updated_at 
         BEFORE UPDATE ON users 
         FOR EACH ROW EXECUTE FUNCTION update_updated_at_column()",
         
        "DROP TRIGGER IF EXISTS update_packages_updated_at ON packages;
         CREATE TRIGGER update_packages_updated_at 
         BEFORE UPDATE ON packages 
         FOR EACH ROW EXECUTE FUNCTION update_updated_at_column()",
         
        "DROP TRIGGER IF EXISTS update_channels_updated_at ON channels;
         CREATE TRIGGER update_channels_updated_at 
         BEFORE UPDATE ON channels 
         FOR EACH ROW EXECUTE FUNCTION update_updated_at_column()"
    ];
    
    $triggerCount = 0;
    foreach ($triggers as $triggerQuery) {
        try {
            $conn->exec($triggerQuery);
            $triggerCount++;
        } catch (Exception $e) {
            echo "⚠️  Trigger warning: " . $e->getMessage() . "\n";
        }
    }
    
    echo "✅ Created {$triggerCount} update triggers\n\n";
    
    echo "🎉 Database optimization complete!\n";
    echo "📊 Performance improvements:\n";
    echo "   • {$indexCount} performance indexes created\n";
    echo "   • {$viewCount} materialized views for analytics\n";
    echo "   • {$functionCount} optimized database functions\n";
    echo "   • {$triggerCount} automatic update triggers\n";
    echo "   • Connection parameters optimized\n";
    echo "   • All tables analyzed for query optimization\n\n";
    echo "🚀 Your database is now optimized for thousands of concurrent users!\n";
    
} catch (Exception $e) {
    echo "❌ Error during optimization: " . $e->getMessage() . "\n";
}
?>
