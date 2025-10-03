<?php
/**
 * BingeTV Performance Monitoring
 * Monitor and optimize application performance
 */

class PerformanceMonitor {
    private static $start_time;
    private static $queries = [];
    private static $memory_usage = [];
    
    /**
     * Start performance monitoring
     */
    public static function start() {
        self::$start_time = microtime(true);
        self::$memory_usage['start'] = memory_get_usage(true);
    }
    
    /**
     * Log database query
     */
    public static function logQuery($query, $execution_time) {
        self::$queries[] = [
            'query' => $query,
            'time' => $execution_time,
            'memory' => memory_get_usage(true)
        ];
    }
    
    /**
     * Get performance statistics
     */
    public static function getStats() {
        $end_time = microtime(true);
        $total_time = $end_time - self::$start_time;
        
        $memory_peak = memory_get_peak_usage(true);
        $memory_current = memory_get_usage(true);
        
        $query_count = count(self::$queries);
        $total_query_time = array_sum(array_column(self::$queries, 'time'));
        
        return [
            'execution_time' => round($total_time, 4),
            'memory_usage' => [
                'start' => self::$memory_usage['start'],
                'current' => $memory_current,
                'peak' => $memory_peak,
                'current_mb' => round($memory_current / 1024 / 1024, 2),
                'peak_mb' => round($memory_peak / 1024 / 1024, 2)
            ],
            'queries' => [
                'count' => $query_count,
                'total_time' => round($total_query_time, 4),
                'average_time' => $query_count > 0 ? round($total_query_time / $query_count, 4) : 0,
                'list' => self::$queries
            ],
            'performance_score' => self::calculatePerformanceScore($total_time, $query_count, $memory_peak)
        ];
    }
    
    /**
     * Calculate performance score (0-100)
     */
    private static function calculatePerformanceScore($execution_time, $query_count, $memory_peak) {
        $score = 100;
        
        // Penalize slow execution time
        if ($execution_time > 2.0) $score -= 30;
        elseif ($execution_time > 1.0) $score -= 20;
        elseif ($execution_time > 0.5) $score -= 10;
        
        // Penalize too many queries
        if ($query_count > 20) $score -= 25;
        elseif ($query_count > 10) $score -= 15;
        elseif ($query_count > 5) $score -= 5;
        
        // Penalize high memory usage
        $memory_mb = $memory_peak / 1024 / 1024;
        if ($memory_mb > 128) $score -= 20;
        elseif ($memory_mb > 64) $score -= 10;
        elseif ($memory_mb > 32) $score -= 5;
        
        return max(0, $score);
    }
    
    /**
     * Log performance data
     */
    public static function logPerformance($page, $stats) {
        $log_data = [
            'timestamp' => date('Y-m-d H:i:s'),
            'page' => $page,
            'execution_time' => $stats['execution_time'],
            'memory_peak_mb' => $stats['memory_usage']['peak_mb'],
            'query_count' => $stats['queries']['count'],
            'performance_score' => $stats['performance_score'],
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];
        
        $log_file = __DIR__ . '/../logs/performance.log';
        file_put_contents($log_file, json_encode($log_data) . "\n", FILE_APPEND | LOCK_EX);
    }
}

/**
 * Database Query Optimizer
 */
class QueryOptimizer {
    private static $slow_queries = [];
    
    /**
     * Monitor and optimize slow queries
     */
    public static function monitorQuery($query, $execution_time) {
        if ($execution_time > 0.1) { // Queries slower than 100ms
            self::$slow_queries[] = [
                'query' => $query,
                'time' => $execution_time,
                'timestamp' => time()
            ];
        }
    }
    
    /**
     * Get slow query recommendations
     */
    public static function getRecommendations() {
        $recommendations = [];
        
        foreach (self::$slow_queries as $slow_query) {
            $query = $slow_query['query'];
            
            // Check for missing indexes
            if (preg_match('/WHERE.*=.*\?/', $query)) {
                $recommendations[] = "Consider adding an index for the WHERE clause in: " . substr($query, 0, 100) . "...";
            }
            
            // Check for SELECT *
            if (preg_match('/SELECT \* FROM/', $query)) {
                $recommendations[] = "Avoid SELECT * - specify only needed columns in: " . substr($query, 0, 100) . "...";
            }
            
            // Check for ORDER BY without LIMIT
            if (preg_match('/ORDER BY.*(?!LIMIT)/', $query)) {
                $recommendations[] = "Consider adding LIMIT to ORDER BY query: " . substr($query, 0, 100) . "...";
            }
        }
        
        return array_unique($recommendations);
    }
}

/**
 * Memory Optimizer
 */
class MemoryOptimizer {
    /**
     * Optimize memory usage
     */
    public static function optimize() {
        // Clear any unused variables
        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }
        
        // Clear opcache if available
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
    }
    
    /**
     * Check memory usage and warn if high
     */
    public static function checkMemoryUsage() {
        $memory_usage = memory_get_usage(true);
        $memory_limit = ini_get('memory_limit');
        
        // Convert memory limit to bytes
        $limit_bytes = self::convertToBytes($memory_limit);
        
        $usage_percentage = ($memory_usage / $limit_bytes) * 100;
        
        if ($usage_percentage > 80) {
            error_log("High memory usage: " . round($usage_percentage, 2) . "% of limit");
            return false;
        }
        
        return true;
    }
    
    /**
     * Convert memory limit string to bytes
     */
    private static function convertToBytes($memory_limit) {
        $unit = strtolower(substr($memory_limit, -1));
        $value = (int) $memory_limit;
        
        switch ($unit) {
            case 'g': return $value * 1024 * 1024 * 1024;
            case 'm': return $value * 1024 * 1024;
            case 'k': return $value * 1024;
            default: return $value;
        }
    }
}

/**
 * Response Optimizer
 */
class ResponseOptimizer {
    /**
     * Enable GZIP compression
     */
    public static function enableCompression() {
        if (!ob_get_level() && !headers_sent()) {
            if (extension_loaded('zlib') && !ob_get_level()) {
                ob_start('ob_gzhandler');
            }
        }
    }
    
    /**
     * Set optimal headers
     */
    public static function setOptimalHeaders() {
        // Remove unnecessary headers
        header_remove('X-Powered-By');
        header_remove('Server');
        
        // Set cache headers
        if (!headers_sent()) {
            header('Cache-Control: public, max-age=3600');
            header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT');
        }
    }
    
    /**
     * Minify HTML output
     */
    public static function minifyOutput($buffer) {
        // Remove comments
        $buffer = preg_replace('/<!--(.|\s)*?-->/', '', $buffer);
        
        // Remove extra whitespace
        $buffer = preg_replace('/\s+/', ' ', $buffer);
        $buffer = preg_replace('/>\s+</', '><', $buffer);
        
        return trim($buffer);
    }
}

// Initialize performance monitoring
PerformanceMonitor::start();
MemoryOptimizer::checkMemoryUsage();
ResponseOptimizer::enableCompression();
?>
