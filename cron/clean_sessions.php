<?php
/**
 * Cron job to clean expired sessions
 * Run every 30 minutes via cPanel cron interface
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/session_manager.php';

try {
    $deleted = cleanExpiredSessions();
    echo date('Y-m-d H:i:s') . " - Cleaned {$deleted} expired sessions\n";
} catch (Exception $e) {
    echo date('Y-m-d H:i:s') . " - Error: " . $e->getMessage() . "\n";
    exit(1);
}

exit(0);
