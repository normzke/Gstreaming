<?php
/**
 * BingeTV Caching System
 * High-performance caching for thousands of concurrent users
 */

class Cache
{
    private static $cache_dir = __DIR__ . '/../cache/';
    private static $default_ttl = 3600; // 1 hour

    /**
     * Initialize cache directory
     */
    public static function init()
    {
        if (!is_dir(self::$cache_dir)) {
            mkdir(self::$cache_dir, 0755, true);
        }
    }

    /**
     * Get cached data
     */
    public static function get($key)
    {
        $file = self::$cache_dir . md5($key) . '.cache';

        if (!file_exists($file)) {
            return false;
        }

        $data = unserialize(file_get_contents($file));

        // Check if expired
        if ($data['expires'] < time()) {
            unlink($file);
            return false;
        }

        return $data['value'];
    }

    /**
     * Set cached data
     */
    public static function set($key, $value, $ttl = null)
    {
        self::init();

        $ttl = $ttl ?? self::$default_ttl;
        $file = self::$cache_dir . md5($key) . '.cache';

        $data = [
            'value' => $value,
            'expires' => time() + $ttl,
            'created' => time()
        ];

        return file_put_contents($file, serialize($data)) !== false;
    }

    /**
     * Delete cached data
     */
    public static function delete($key)
    {
        $file = self::$cache_dir . md5($key) . '.cache';

        if (file_exists($file)) {
            return unlink($file);
        }

        return true;
    }

    /**
     * Clear all cache
     */
    public static function clear()
    {
        self::init();

        $files = glob(self::$cache_dir . '*.cache');
        foreach ($files as $file) {
            unlink($file);
        }

        return true;
    }

    /**
     * Get cache statistics
     */
    public static function stats()
    {
        self::init();

        $files = glob(self::$cache_dir . '*.cache');
        $total_size = 0;
        $expired_count = 0;
        $valid_count = 0;

        foreach ($files as $file) {
            $total_size += filesize($file);

            $data = unserialize(file_get_contents($file));
            if ($data['expires'] < time()) {
                $expired_count++;
            } else {
                $valid_count++;
            }
        }

        return [
            'total_files' => count($files),
            'valid_files' => $valid_count,
            'expired_files' => $expired_count,
            'total_size' => $total_size,
            'total_size_mb' => round($total_size / 1024 / 1024, 2)
        ];
    }

    /**
     * Clean expired cache files
     */
    public static function clean()
    {
        self::init();

        $files = glob(self::$cache_dir . '*.cache');
        $cleaned = 0;

        foreach ($files as $file) {
            $data = unserialize(file_get_contents($file));
            if ($data['expires'] < time()) {
                unlink($file);
                $cleaned++;
            }
        }

        return $cleaned;
    }
}

/**
 * Cached database queries
 */
class CachedQueries
{
    private static $db;

    public static function init($database)
    {
        self::$db = $database;
    }

    /**
     * Get channels with caching
     */
    public static function getChannels($category = null, $country = null)
    {
        $cache_key = "channels_" . ($category ?? 'all') . "_" . ($country ?? 'all');

        $cached = Cache::get($cache_key);
        if ($cached !== false) {
            return $cached;
        }

        $conn = self::$db->getConnection();
        $query = "SELECT * FROM channels WHERE is_active = true";
        $params = [];

        if ($category) {
            $query .= " AND category = ?";
            $params[] = $category;
        }

        if ($country) {
            $query .= " AND country = ?";
            $params[] = $country;
        }

        $query .= " ORDER BY sort_order ASC, name ASC";

        $stmt = $conn->prepare($query);
        $stmt->execute($params);
        $channels = $stmt->fetchAll();

        // Cache for 1 hour
        Cache::set($cache_key, $channels, 3600);

        return $channels;
    }

    /**
     * Get packages with caching
     */
    public static function getPackages()
    {
        $cache_key = "packages_all";

        $cached = Cache::get($cache_key);
        if ($cached !== false) {
            return $cached;
        }

        $conn = self::$db->getConnection();
        $query = "SELECT * FROM packages WHERE is_active = true ORDER BY sort_order ASC, price ASC";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $packages = $stmt->fetchAll();

        // Cache for 2 hours
        Cache::set($cache_key, $packages, 7200);

        return $packages;
    }

    /**
     * Get gallery items with caching
     */
    public static function getGalleryItems($featured_only = false)
    {
        $cache_key = "gallery_" . ($featured_only ? 'featured' : 'all');

        $cached = Cache::get($cache_key);
        if ($cached !== false) {
            return $cached;
        }

        $conn = self::$db->getConnection();
        $query = "SELECT * FROM gallery";

        if ($featured_only) {
            $query .= " WHERE is_featured = true";
        }

        $query .= " ORDER BY sort_order ASC, created_at DESC";

        $stmt = $conn->prepare($query);
        $stmt->execute();
        $items = $stmt->fetchAll();

        // Cache for 30 minutes
        Cache::set($cache_key, $items, 1800);

        return $items;
    }

    /**
     * Get user subscription with caching
     */
    public static function getUserSubscription($user_id)
    {
        $cache_key = "user_subscription_{$user_id}";

        $cached = Cache::get($cache_key);
        if ($cached !== false) {
            return $cached;
        }

        $conn = self::$db->getConnection();
        $query = "SELECT us.*, p.name as package_name, p.price, p.duration_days 
                  FROM user_subscriptions us 
                  JOIN packages p ON us.package_id = p.id 
                  WHERE us.user_id = ? 
                  ORDER BY us.end_date DESC, us.created_at DESC LIMIT 1";

        $stmt = $conn->prepare($query);
        $stmt->execute([$user_id]);
        $subscription = $stmt->fetch();

        // Cache for 5 minutes
        Cache::set($cache_key, $subscription, 300);

        return $subscription;
    }

    /**
     * Get dashboard statistics with caching
     */
    public static function getDashboardStats()
    {
        $cache_key = "dashboard_stats";

        $cached = Cache::get($cache_key);
        if ($cached !== false) {
            return $cached;
        }

        $conn = self::$db->getConnection();
        $stats = [];

        // Total users
        $query = "SELECT COUNT(*) as total FROM users";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $stats['total_users'] = $stmt->fetch()['total'];

        // Active subscriptions
        $query = "SELECT COUNT(*) as total FROM user_subscriptions WHERE status = 'active' AND end_date > NOW()";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $stats['active_subscriptions'] = $stmt->fetch()['total'];

        // Total revenue
        $query = "SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE status = 'completed'";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $stats['total_revenue'] = $stmt->fetch()['total'];

        // Cache for 10 minutes
        Cache::set($cache_key, $stats, 600);

        return $stats;
    }

    /**
     * Invalidate user-related cache
     */
    public static function invalidateUserCache($user_id)
    {
        Cache::delete("user_subscription_{$user_id}");
        Cache::delete("dashboard_stats");
    }

    /**
     * Invalidate content cache
     */
    public static function invalidateContentCache()
    {
        Cache::delete("channels_all_all");
        Cache::delete("packages_all");
        Cache::delete("gallery_featured");
        Cache::delete("gallery_all");
        Cache::delete("dashboard_stats");
    }
}

// Initialize cache
Cache::init();
?>