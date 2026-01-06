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
    error_log("[PLAYLIST PROXY] Unauthorized access attempt");
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user = getCurrentUser();
if (!$user) {
    error_log("[PLAYLIST PROXY] Could not get current user");
    http_response_code(403);
    echo json_encode(['error' => 'User not found']);
    exit;
}

$action = $_GET['action'] ?? 'get_live_streams';
$categoryId = $_GET['category_id'] ?? null;
$streamId = $_GET['stream_id'] ?? null;

error_log("[PLAYLIST PROXY] Action: $action, User ID: {$user['id']}, Stream ID: $streamId");

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
    error_log("[PLAYLIST PROXY] No streaming credentials for user {$user['id']}");
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
// STREAM PROXY HANDLER (For M3U8 Playlists)
// ---------------------------------------------------------
if ($action === 'stream' && $streamId) {
    error_log("[STREAM PROXY] Stream ID: $streamId, User: {$user['id']}");

    // Disable ALL buffering
    while (ob_get_level()) {
        ob_end_clean();
    }

    header('X-Accel-Buffering: no');
    @apache_setenv('no-gzip', 1);
    @ini_set('zlib.output_compression', 0);

    // Construct M3U8 playlist URL
    $streamUrl = $server . "/live/" . urlencode($username)
        . "/" . urlencode($password)
        . "/" . urlencode($streamId) . ".m3u8";

    error_log("[STREAM PROXY] Fetching M3U8: $streamUrl");

    // Fetch the M3U8 playlist
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $streamUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_USERAGENT, 'TiviMate/4.7.0');

    $m3u8Content = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($httpCode !== 200 || empty($m3u8Content)) {
        error_log("[STREAM PROXY] ERROR - HTTP $httpCode: $curlError");
        http_response_code(502);
        header("Content-Type: application/json");
        echo json_encode([
            'error' => 'Stream unavailable',
            'http_code' => $httpCode,
            'curl_error' => $curlError
        ]);
        exit;
    }

    // Return M3U8 playlist with proper content type
    header("Content-Type: application/vnd.apple.mpegurl");
    header("Cache-Control: no-cache");
    header("Access-Control-Allow-Origin: *");

    echo $m3u8Content;
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