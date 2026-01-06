<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/functions.php';
require_once __DIR__ . '/../lib/seo.php';

// Check if user is logged in or has valid MAC/streaming link
$isAuthenticated = false;
$user = null;
$playlistUrl = null;
$macAddress = null;

// Check session login
if (isLoggedIn()) {
    $isAuthenticated = true;
    $user = $_SESSION['user'] ?? null;

    // Get user's playlist URL from database
    $db = Database::getInstance();
    $conn = $db->getConnection();
    if ($conn && $user) {
        $stmt = $conn->prepare("SELECT playlist_url, mac_address FROM users WHERE id = ?");
        $stmt->execute([$user['id']]);
        $userData = $stmt->fetch();
        if ($userData) {
            // Use local proxy to avoid CORS/Mixed Content issues
            $playlistUrl = 'api/playlist_proxy.php';
            $macAddress = $userData['mac_address'];
        }
    }
}

// Check MAC address authentication
if (!$isAuthenticated && isset($_GET['mac'])) {
    $macAddress = $_GET['mac'];
    $db = Database::getInstance();
    $conn = $db->getConnection();
    if ($conn) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE mac_address = ? AND is_active = true");
        $stmt->execute([$macAddress]);
        $user = $stmt->fetch();
        if ($user) {
            $isAuthenticated = true;
            $playlistUrl = $user['playlist_url'];
        }
    }
}

// Check streaming link authentication (TiviMate format)
if (!$isAuthenticated && isset($_GET['stream'])) {
    $streamLink = $_GET['stream'];
    $db = Database::getInstance();
    $conn = $db->getConnection();
    if ($conn) {
        // Check if stream link matches user's playlist URL pattern
        $stmt = $conn->prepare("SELECT * FROM users WHERE playlist_url LIKE ? AND is_active = true");
        $stmt->execute(['%' . $streamLink . '%']);
        $user = $stmt->fetch();
        if ($user) {
            $isAuthenticated = true;
            $playlistUrl = $user['playlist_url'];
        }
    }
}

// Get SEO data
$seo_meta = SEO::getMetaTags('player');
$canonical_url = SEO::getCanonicalUrl('player');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="https://bingetv.co.ke/">

    <title>BingeTV Player - Stream Your Content</title>
    <meta name="description" content="BingeTV Player - Stream live channels, shows, and movies">

    <!-- Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Styles -->
    <link rel="stylesheet" href="assets/css/player.css">

    <!-- HLS.js (Self Hosted) -->
    <script src="assets/js/hls.min.js"></script>

    <!-- Config Injection -->
    <script>
        window.serverUrl = "<?php echo rtrim($user['tivimate_server'] ?? '', '/'); ?>";
        window.username = "<?php echo $user['tivimate_username'] ?? ''; ?>";
        window.password = "<?php echo $user['tivimate_password'] ?? ''; ?>";
        window.isAuthenticated = <?php echo $isAuthenticated ? 'true' : 'false'; ?>;
        window.playlistUrl = '<?php echo htmlspecialchars($playlistUrl ?? ''); ?>';
    </script>
</head>

<body>
    <?php if (!$isAuthenticated): ?>
        <!-- Login Modal -->
        <div class="login-modal" id="loginModal">
            <div class="login-container">
                <div class="login-header">
                    <h1><i class="fas fa-satellite-dish"></i> BingeTV</h1>
                    <p style="color: #ccc;">Sign in to access your content</p>
                </div>

                <div class="login-tabs">
                    <div class="login-tab active" onclick="switchTab('credentials')">Username/Password</div>
                    <div class="login-tab" onclick="switchTab('mac')">MAC Address</div>
                </div>

                <!-- Username/Password Form -->
                <form class="login-form active" id="credentialsForm" onsubmit="loginWithCredentials(event)">
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Username</label>
                        <input type="text" id="username" required autocomplete="username">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-lock"></i> Password</label>
                        <input type="password" id="password" required autocomplete="current-password">
                    </div>
                    <button type="submit" class="btn-login">Sign In</button>
                    <div class="error-message" id="credentialsError"></div>
                </form>

                <!-- MAC Address Form -->
                <form class="login-form" id="macForm" onsubmit="loginWithMAC(event)">
                    <div class="form-group">
                        <label><i class="fas fa-network-wired"></i> MAC Address</label>
                        <input type="text" id="macAddress" placeholder="XX:XX:XX:XX:XX:XX" required>
                    </div>
                    <button type="submit" class="btn-login">Sign In with MAC</button>
                    <div class="error-message" id="macError"></div>
                </form>
            </div>
        </div>
    <?php else: ?>
        <!-- Player Interface -->
        <div class="player-container active" id="playerContainer">
            <div class="player-header">
                <div class="player-logo">
                    <i class="fas fa-satellite-dish"></i> BingeTV
                </div>
                <div class="clock" id="clock">00:00</div>
                <div class="player-controls">
                    <span style="color: var(--text-muted);">Welcome,
                        <strong
                            style="color: var(--primary);"><?php echo htmlspecialchars($user['username'] ?? 'User'); ?></strong></span>

                    <!-- Playlist Management -->
                    <a href="logout.php" title="Add another playlist"
                        style="color: #fff; text-decoration: none; margin-left: 20px;">
                        <i class="fas fa-plus-circle"></i> Add Playlist
                    </a>
                    <a href="#" onclick="deletePlaylist()" title="Delete current playlist data"
                        style="color: #ff4444; text-decoration: none; margin-left: 20px;">
                        <i class="fas fa-trash-alt"></i> Delete
                    </a>
                    <a href="logout.php" style="color: #fff; text-decoration: none; margin-left: 20px;">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>

            <div class="player-main">
                <div class="sidebar">
                    <div class="search-container">
                        <input type="text" class="search-input" id="searchInput" placeholder="Search channels...">
                    </div>
                    <div class="sidebar-section">
                        <h3><i class="fas fa-list"></i> Categories</h3>
                        <ul class="category-list" id="categoryList">
                            <!-- Categories populated by JS -->
                        </ul>
                    </div>
                </div>

                <!-- Video Area (Hidden by default) -->
                <div class="video-area" id="videoArea" style="display: none;">
                    <video id="video-player" controls preload="auto"></video>
                    <button onclick="stopPlayer()"
                        style="position: absolute; top: 20px; right: 20px; background: rgba(0,0,0,0.7); border: none; color: white; padding: 10px 20px; border-radius: 5px; cursor: pointer; z-index: 100;">
                        <i class="fas fa-times"></i> Close
                    </button>
                    <div class="video-info">
                        <h2 id="currentCategoryTitle">Streaming</h2>
                        <h2 id="currentChannelName">Select a Channel</h2>
                        <p id="currentChannelDesc"></p>
                    </div>
                </div>

                <!-- Channel Grid -->
                <div class="channel-grid" id="channelGrid">
                    <!-- Channels populated by JS -->
                    <div class="loading" id="loading">
                        <i class="fas fa-circle-notch fa-spin"></i>
                        <p style="margin-top: 20px;">Connecting to server...</p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Scripts -->
    <script src="assets/js/auth.js"></script>
    <script src="assets/js/player.js?v=<?php echo time(); ?>"></script>
</body>

</html>