<?php
// Test script to check playlist proxy functionality
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/functions.php';

echo "=== PLAYLIST PROXY TEST ===\n\n";

// Check if logged in
echo "1. Login Status: " . (isLoggedIn() ? "✅ Logged in" : "❌ Not logged in") . "\n";

if (!isLoggedIn()) {
    echo "   Session ID: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'Not set') . "\n";
    echo "   Please login first\n";
    exit;
}

// Get user
$user = getCurrentUser();
if (!$user) {
    echo "2. User Data: ❌ Could not get user\n";
    exit;
}

echo "2. User Data: ✅ Found\n";
echo "   User ID: {$user['id']}\n";
echo "   Username: {$user['username']}\n";

// Check streaming credentials
$db = Database::getInstance();
$conn = $db->getConnection();
$stmt = $conn->prepare("SELECT tivimate_server, tivimate_username, tivimate_password FROM users WHERE id = ?");
$stmt->execute([$user['id']]);
$creds = $stmt->fetch();

echo "\n3. Streaming Credentials:\n";
if ($creds && !empty($creds['tivimate_server'])) {
    echo "   Server: ✅ {$creds['tivimate_server']}\n";
    echo "   Username: ✅ {$creds['tivimate_username']}\n";
    echo "   Password: ✅ " . (empty($creds['tivimate_password']) ? '❌ Empty' : '✅ Set') . "\n";

    // Test stream URL construction
    $server = trim(rtrim($creds['tivimate_server'], '/'));
    if (strpos($server, '://') === false) {
        $server = 'http://' . $server;
    }
    $username = trim($creds['tivimate_username']);
    $password = trim($creds['tivimate_password']);
    $streamId = 861059;

    $streamUrl = $server . "/get.php?username=" . urlencode($username)
        . "&password=" . urlencode($password)
        . "&type=m3u_plus&output=ts&stream_id=" . urlencode($streamId);

    echo "\n4. Test Stream URL:\n";
    echo "   $streamUrl\n";

    echo "\n5. Testing Stream Access:\n";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $streamUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_NOBODY, true); // HEAD request
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($httpCode === 200) {
        echo "   ✅ Stream accessible (HTTP $httpCode)\n";
    } else {
        echo "   ❌ Stream error (HTTP $httpCode)\n";
        if ($curlError) {
            echo "   cURL Error: $curlError\n";
        }
    }

} else {
    echo "   ❌ No credentials found\n";
    echo "   Please set your IPTV credentials in user settings\n";
}

echo "\n=== TEST COMPLETE ===\n";
?>