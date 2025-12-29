<?php
/**
 * BingeTV Playlist API
 * Generates M3U playlists for authenticated users
 */

header('Content-Type: application/x-mpegurl');
header('Content-Disposition: attachment; filename="bingetv_playlist.m3u"');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

$db = new Database();
$conn = $db->getConnection();

// Authenticate user
$user = null;
$authenticated = false;

// Check for token authentication
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE streaming_token = ? AND is_active = 1");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    if ($user) {
        $authenticated = true;
    }
}

// Check for MAC address authentication
if (!$authenticated && isset($_GET['mac'])) {
    $mac = $_GET['mac'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE mac_address = ? AND is_active = 1");
    $stmt->execute([$mac]);
    $user = $stmt->fetch();
    if ($user) {
        $authenticated = true;
    }
}

// Check for user_id authentication (for logged-in users)
if (!$authenticated && isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND is_active = 1");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    if ($user) {
        $authenticated = true;
    }
}

if (!$authenticated) {
    header('HTTP/1.1 401 Unauthorized');
    echo "#EXTM3U\n";
    echo "#EXTINF:-1,Unauthorized - Invalid credentials\n";
    echo "http://error.bingetv.co.ke/unauthorized\n";
    exit;
}

// Get user's subscription tier
$subscription_tier = $user['subscription_tier'] ?? 'basic';

// Get channels based on subscription
$stmt = $conn->prepare("
    SELECT * FROM channels 
    WHERE is_active = 1 
    AND (requires_subscription = ? OR requires_subscription = 'basic')
    ORDER BY sort_order ASC, category ASC, name ASC
");
$stmt->execute([$subscription_tier]);
$channels = $stmt->fetchAll();

// Generate M3U playlist
echo "#EXTM3U\n";
echo "#EXTINF:-1,BingeTV - Welcome " . htmlspecialchars($user['username']) . "\n";
echo "#EXTINF:-1,Subscription: " . strtoupper($subscription_tier) . "\n";
echo "#EXTINF:-1,Total Channels: " . count($channels) . "\n";
echo "\n";

// Add channels
foreach ($channels as $channel) {
    $tvg_id = $channel['tvg_id'] ?? '';
    $tvg_name = $channel['tvg_name'] ?? $channel['name'];
    $tvg_logo = $channel['logo_url'] ?? '';
    $group_title = $channel['group_title'] ?? $channel['category'];

    echo "#EXTINF:-1";

    if ($tvg_id)
        echo " tvg-id=\"$tvg_id\"";
    if ($tvg_name)
        echo " tvg-name=\"" . htmlspecialchars($tvg_name) . "\"";
    if ($tvg_logo)
        echo " tvg-logo=\"$tvg_logo\"";
    if ($group_title)
        echo " group-title=\"" . htmlspecialchars($group_title) . "\"";

    echo "," . htmlspecialchars($channel['name']) . "\n";
    echo $channel['stream_url'] . "\n";
}

// Log playlist access
try {
    $stmt = $conn->prepare("
        INSERT INTO streaming_logs (user_id, channel_name, started_at, device_type) 
        VALUES (?, 'Playlist Access', NOW(), ?)
    ");
    $device_type = $_GET['device'] ?? 'Unknown';
    $stmt->execute([$user['id'], $device_type]);
} catch (Exception $e) {
    // Silent fail for logging
}
?>