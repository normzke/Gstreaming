<?php
// Test script to check M3U8 access
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/functions.php';

echo "=== USER AGENT BRUTE FORCE TEST ===\n\n";

if (!isLoggedIn()) {
    echo "❌ Please login first\n";
    exit;
}

$user = getCurrentUser();
$db = Database::getInstance();
$conn = $db->getConnection();
$stmt = $conn->prepare("SELECT tivimate_server, tivimate_username, tivimate_password FROM users WHERE id = ?");
$stmt->execute([$user['id']]);
$creds = $stmt->fetch();

$server = trim(rtrim($creds['tivimate_server'], '/'));
if (strpos($server, '://') === false)
    $server = 'http://' . $server;
$username = trim($creds['tivimate_username']);
$password = trim($creds['tivimate_password']);
$streamId = 861060;

// Test URL (TS format)
$url = "$server/live/$username/$password/$streamId.ts";
echo "Target URL: $url\n\n";

$userAgents = [
    'IPTVSmartersPro/1.1.1 (iPad; iOS 12.2; Scale/2.00)',
    'VLC/3.0.18 LibVLC/3.0.18',
    'Winamp/2.9',
    'ExoPlayer/2.18.7',
    'okhttp/4.9.0',
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    'TiviMate/4.7.0'
];

foreach ($userAgents as $ua) {
    echo "Testing UA: $ua\n";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_USERAGENT, $ua);

    $content = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "   HTTP Code: $httpCode\n";
    if ($httpCode == 200) {
        echo "   ✅ SUCCESS! Found working User-Agent.\n";
    } else {
        echo "   ❌ Failed\n";
    }
    echo "\n";
}
?>