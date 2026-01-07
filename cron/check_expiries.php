<?php
/**
 * Daily Cron Job for Subscription Management
 * - Checks for expiring subscriptions (sends renewal reminders)
 * - Checks for expired subscriptions (sends expiry notifications)
 * - Processes auto-renewals
 * 
 * Run this script once daily via cron
 * Example: 0 0 * * * /usr/bin/php /path/to/cron/check_expiries.php
 */

// Define root path
define('ROOT_PATH', __DIR__ . '/../');

// Load required files
require_once ROOT_PATH . 'config/config.php';
require_once ROOT_PATH . 'config/database.php';
require_once ROOT_PATH . 'lib/functions.php';
require_once ROOT_PATH . 'lib/payment-processor.php';
require_once ROOT_PATH . 'lib/email_notifications.php'; // Ensure email functions are available

// Enable error logging
ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', ROOT_PATH . 'logs/cron.log');

echo "Starting daily subscription check..." . PHP_EOL;

try {
    // Initialize processor
    $processor = new PaymentProcessor();

    // 1. Process Renewals (and send expiry warnings for those not auto-renewing?)
    // Note: processRenewals currently handles auto-renewals. 
    // We should also add logic to send warnings for ALL subscriptions expiring soon.

    // Check for subscriptions expiring in 7 days
    $conn = Database::getInstance()->getConnection();

    $warningQuery = "SELECT us.*, u.email, u.first_name, p.name as package_name 
                    FROM user_subscriptions us 
                    JOIN users u ON us.user_id = u.id 
                    JOIN packages p ON us.package_id = p.id 
                    WHERE us.status = 'active' 
                    AND us.end_date BETWEEN NOW() + INTERVAL '6 day' AND NOW() + INTERVAL '7 day'";

    $warningStmt = $conn->prepare($warningQuery);
    $warningStmt->execute();
    $warnings = $warningStmt->fetchAll();

    $warningCount = 0;
    foreach ($warnings as $sub) {
        $daysRemaining = 7; // Approximate
        sendSubscriptionExpiryWarningEmail(
            $sub['email'],
            $sub['first_name'],
            $sub['package_name'],
            $sub['end_date'],
            $daysRemaining
        );
        $warningCount++;
        echo "Sent expiry warning to user ID: " . $sub['user_id'] . PHP_EOL;
    }

    // 2. Process Auto-Renewals
    // This creates pending payments for auto-renewals
    $renewals = $processor->processRenewals();
    echo "Processed {$renewals} auto-renewals." . PHP_EOL;

    // 3. Process Expired Subscriptions
    // This marks them as expired and sends expiry emails
    $expired = $processor->processExpiredSubscriptions();
    echo "Processed {$expired} expired subscriptions." . PHP_EOL;

    echo "Daily check completed successfully." . PHP_EOL;

} catch (Exception $e) {
    error_log("Cron Error: " . $e->getMessage());
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
?>