<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../lib/functions.php';

// disable gzip
if (function_exists('apache_setenv')) {
    apache_setenv('no-gzip', 1);
}
ini_set('zlib.output_compression', 0);
set_time_limit(0); // Unlimited execution time for streams

if (!isLoggedIn()) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user = $_SESSION['user'];
$action = $_GET['action'] ?? 'get_live_streams';
$categoryId = $_GET['category_id'] ?? null;
$streamId = $_GET['stream_id'] ?? null;

// Cache Configuration for API calls
$CACHE_DIR = __DIR__ . '/../../uploads/cache/';
if (!file_exists($CACHE_DIR)) {
    mkdir($CACHE_DIR, 0777, true);
}
$CACHE_DURATION = 3600; // 1 hour

$db = Database::getInstance();
/** @var PDO $conn */
$conn = $db->getConnection();

// Fetch fresh user data
$stmt = $conn->prepare("SELECT tivimate_server, tivimate_username, tivimate_password FROM users WHERE id = ?");
$stmt->execute([$user['id']]);
$freshUser = $stmt->fetch();

if (!$freshUser || empty($freshUser['tivimate_server'])) {
    http_response_code(404);
    echo json_encode(['error' => 'No streaming credentials found']);
    exit;
}

$server = trim(rtrim($freshUser['tivimate_server'], '/'));
if (strpos($server, '://') === false) {
    $server = 'http://' . $server;
}
$username = trim($freshUser['tivimate_username']);
$password = trim($freshUser['tivimate_password']);

// ---------------------------------------------------------
// STREAM PROXY HANDLER (For Playback)
// ---------------------------------------------------------
if ($action === 'stream' && $streamId) {
    // Log the stream request for debugging
    error_log("[STREAM PROXY] Stream ID: $streamId, User: {$user['id']}");

    // 1. Disable Buffering for Real-Time Streaming
    if (ob_get_level())
        ob_end_clean();
    header('X-Accel-Buffering: no'); // Nginx

    // 2. Construct Stream URL
    // Default to .m3u8 for HLS/Web Player compatibility
    $ext = $_GET['ext'] ?? 'm3u8';
    $streamUrl = "$server/live/$username/$password/$streamId.$ext";

    error_log("[STREAM PROXY] Fetching: $streamUrl");

    // 3. Open Stream with optimized headers
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $streamUrl);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    // Optimization: Larger chunks (512KB) & No Timeout for 4K stability
    curl_setopt($ch, CURLOPT_BUFFERSIZE, 512 * 1024);
    curl_setopt($ch, CURLOPT_TIMEOUT, 0);

    // Mimic TiviMate (Best compatibility)
    curl_setopt($ch, CURLOPT_USERAGENT, 'TiviMate/4.7.0');

    // Forward Content-Type properly
    if ($ext === 'm3u8') {
        header("Content-Type: application/vnd.apple.mpegurl");
    } else {
        header("Content-Type: video/mp2t");
    }

    // Stream Pass-Through with manual flush
    curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($ch, $chunk) {
        echo $chunk;
        flush();
        return strlen($chunk);
    });

    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($result === false || $httpCode !== 200) {
        error_log("[STREAM PROXY] ERROR - HTTP Code: $httpCode, cURL Error: $curlError");
        http_response_code(502);
        header("Content-Type: application/json");
        echo json_encode([
            'error' => 'Stream unavailable',
            'stream_url' => $streamUrl,
            'http_code' => $httpCode,
            'curl_error' => $curlError
        ]);
    }

    exit;
}

// ---------------------------------------------------------
// CONFIG HANDLER
// ---------------------------------------------------------
if ($action === 'get_config') {
    header('Content-Type: application/json');
    echo json_encode([
        'server' => $server,
        'username' => $username,
        'password' => $password,
        'proxy_enabled' => true
    ]);
    exit;
}

// ---------------------------------------------------------
// API PROXY HANDLER (Cached)
// ---------------------------------------------------------
$cacheFile = $CACHE_DIR . md5($action . $categoryId . $username) . '.json';
$isCached = false;

// Check Cache
if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $CACHE_DURATION)) {
    $response = file_get_contents($cacheFile);
    $isCached = true;
} else {
    // Fetch from Provider
    $apiUrl = "$server/player_api.php?username=" . urlencode($username) . "&password=" . urlencode($password) . "&action=" . urlencode($action);
    if ($categoryId) {
        $apiUrl .= "&category_id=" . urlencode($categoryId);
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    // Use TiviMate User-Agent for API calls too
    curl_setopt($ch, CURLOPT_USERAGENT, 'TiviMate/4.7.0');

    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && !empty($response)) {
        file_put_contents($cacheFile, $response);
    } else {
        if (file_exists($cacheFile)) {
            $response = file_get_contents($cacheFile);
        } else {
            http_response_code(502);
            echo json_encode(['error' => 'Provider Error', 'details' => curl_error($ch)]);
            exit;
        }
    }
}

// Forward JSON
header('Content-Type: application/json');
header('X-Cache-Status: ' . ($isCached ? 'HIT' : 'MISS'));
echo $response;
exit;
?>