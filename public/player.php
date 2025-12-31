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
            $playlistUrl = $userData['playlist_url'];
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
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- HLS.js for HLS streaming support -->
    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #000;
            color: #fff;
            overflow: hidden;
            height: 100vh;
        }
        
        /* Login Modal */
        .login-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.95);
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            border-radius: 20px;
            padding: 40px;
            max-width: 450px;
            width: 90%;
            border: 2px solid #00A8FF;
            box-shadow: 0 20px 60px rgba(0,168,255,0.3);
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header h1 {
            color: #00A8FF;
            font-family: 'Orbitron', sans-serif;
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .login-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
        }
        
        .login-tab {
            flex: 1;
            padding: 12px;
            background: rgba(0,168,255,0.1);
            border: 2px solid transparent;
            border-radius: 8px;
            color: #fff;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s;
        }
        
        .login-tab.active {
            background: #00A8FF;
            border-color: #00A8FF;
        }
        
        .login-form {
            display: none;
        }
        
        .login-form.active {
            display: block;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            color: #00A8FF;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            background: rgba(255,255,255,0.1);
            border: 2px solid rgba(0,168,255,0.3);
            border-radius: 8px;
            color: #fff;
            font-size: 16px;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #00A8FF;
            background: rgba(255,255,255,0.15);
        }
        
        .btn-login {
            width: 100%;
            padding: 15px;
            background: #00A8FF;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }
        
        .btn-login:hover {
            background: #0099E6;
            transform: scale(1.02);
        }
        
        .error-message {
            color: #ff4444;
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
        }
        
        /* Player Interface */
        .player-container {
            display: none;
            height: 100vh;
            flex-direction: column;
        }
        
        .player-container.active {
            display: flex;
        }
        
        .player-header {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #00A8FF;
        }
        
        .player-logo {
            font-family: 'Orbitron', sans-serif;
            font-size: 24px;
            color: #00A8FF;
            font-weight: 900;
        }
        
        .player-controls {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .player-main {
            flex: 1;
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 0;
            overflow: hidden;
        }
        
        .sidebar {
            background: rgba(26,26,46,0.95);
            border-right: 2px solid #00A8FF;
            overflow-y: auto;
            padding: 20px;
        }
        
        .sidebar-section {
            margin-bottom: 30px;
        }
        
        .sidebar-section h3 {
            color: #00A8FF;
            font-size: 18px;
            margin-bottom: 15px;
            font-family: 'Orbitron', sans-serif;
        }
        
        .category-list {
            list-style: none;
        }
        
        .category-item {
            padding: 12px;
            margin-bottom: 8px;
            background: rgba(0,168,255,0.1);
            border-left: 3px solid transparent;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .category-item:hover {
            background: rgba(0,168,255,0.2);
            border-left-color: #00A8FF;
            transform: translateX(5px);
        }
        
        .category-item.active {
            background: rgba(0,168,255,0.3);
            border-left-color: #00A8FF;
        }
        
        .video-area {
            position: relative;
            background: #000;
        }
        
        #video-player {
            width: 100%;
            height: 100%;
        }
        
        .video-info {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.9), transparent);
            padding: 30px;
        }
        
        .video-info h2 {
            color: #00A8FF;
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .video-info p {
            color: #ccc;
            font-size: 14px;
        }
        
        .channel-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            padding: 20px;
            overflow-y: auto;
            max-height: calc(100vh - 200px);
        }
        
        .channel-card {
            background: rgba(0,168,255,0.1);
            border: 2px solid rgba(0,168,255,0.3);
            border-radius: 12px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
        }
        
        .channel-card:hover {
            background: rgba(0,168,255,0.2);
            border-color: #00A8FF;
            transform: scale(1.05);
        }
        
        .channel-logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 10px;
            border-radius: 8px;
            object-fit: cover;
            background: rgba(0,168,255,0.1);
        }
        
        .channel-logo[src=""],
        .channel-logo:not([src]) {
            display: none;
        }
        
        /* Lazy loading for images */
        .channel-logo {
            loading: lazy;
        }
        
        /* Search functionality */
        .search-container {
            padding: 15px 20px;
            border-bottom: 1px solid rgba(0,168,255,0.3);
        }
        
        .search-input {
            width: 100%;
            padding: 10px 15px;
            background: rgba(255,255,255,0.1);
            border: 2px solid rgba(0,168,255,0.3);
            border-radius: 8px;
            color: #fff;
            font-size: 14px;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #00A8FF;
            background: rgba(255,255,255,0.15);
        }
        
        .search-input::placeholder {
            color: rgba(255,255,255,0.5);
        }
        
        /* Favorites */
        .favorite-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(0,0,0,0.6);
            border: none;
            color: #fff;
            padding: 8px;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .favorite-btn:hover {
            background: rgba(0,168,255,0.8);
        }
        
        .favorite-btn.active {
            color: #ff4444;
        }
        
        .channel-card {
            position: relative;
        }
        
        /* Loading skeleton */
        .skeleton {
            background: linear-gradient(90deg, rgba(0,168,255,0.1) 25%, rgba(0,168,255,0.2) 50%, rgba(0,168,255,0.1) 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
        }
        
        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
        
        .channel-name {
            color: #fff;
            font-size: 14px;
            font-weight: 500;
        }
        
        .loading {
            text-align: center;
            padding: 40px;
            color: #00A8FF;
        }
        
        .loading i {
            font-size: 48px;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: rgba(0,0,0,0.3);
        }
        
        ::-webkit-scrollbar-thumb {
            background: #00A8FF;
            border-radius: 4px;
        }
    </style>
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
            <div class="player-controls">
                <span style="color: #00A8FF;">Welcome, <?php echo htmlspecialchars($user['username'] ?? 'User'); ?></span>
                <a href="logout" style="color: #fff; text-decoration: none; margin-left: 20px;">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
        
        <div class="player-main">
            <div class="sidebar">
                <div class="search-container">
                    <input type="text" class="search-input" id="searchInput" placeholder="Search channels..." onkeyup="filterChannels()">
                </div>
                <div class="sidebar-section">
                    <h3><i class="fas fa-list"></i> Categories</h3>
                    <ul class="category-list" id="categoryList">
                        <li class="category-item active" data-category="all">All Channels</li>
                        <li class="category-item" data-category="live">Live TV</li>
                        <li class="category-item" data-category="movies">Movies</li>
                        <li class="category-item" data-category="shows">TV Shows</li>
                        <li class="category-item" data-category="sports">Sports</li>
                    </ul>
                </div>
                <div class="sidebar-section">
                    <h3><i class="fas fa-heart"></i> Favorites</h3>
                    <ul class="category-list">
                        <li class="category-item" data-category="favorites" onclick="showFavorites()">My Favorites</li>
                    </ul>
                </div>
            </div>
            
            <div class="channel-grid" id="channelGrid">
                <div class="loading">
                    <i class="fas fa-spinner"></i>
                    <p>Loading playlist...</p>
                </div>
            </div>
        </div>
        
        <div class="video-area" id="videoArea" style="display: none;">
            <video id="video-player" controls autoplay></video>
            <div class="video-info" id="videoInfo">
                <h2 id="currentChannelName"></h2>
                <p id="currentChannelDesc"></p>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <script>
        const playlistUrl = '<?php echo htmlspecialchars($playlistUrl ?? ''); ?>';
        const isAuthenticated = <?php echo $isAuthenticated ? 'true' : 'false'; ?>;
        
        // Login Functions
        function switchTab(tab) {
            document.querySelectorAll('.login-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.login-form').forEach(f => f.classList.remove('active'));
            
            if (tab === 'credentials') {
                document.querySelector('.login-tab:first-child').classList.add('active');
                document.getElementById('credentialsForm').classList.add('active');
            } else {
                document.querySelector('.login-tab:last-child').classList.add('active');
                document.getElementById('macForm').classList.add('active');
            }
        }
        
        async function loginWithCredentials(e) {
            e.preventDefault();
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const errorDiv = document.getElementById('credentialsError');
            
            try {
                const response = await fetch('api/auth.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ username, password, type: 'credentials' })
                });
                
                const data = await response.json();
                if (data.success) {
                    window.location.reload();
                } else {
                    errorDiv.textContent = data.message || 'Invalid credentials';
                }
            } catch (error) {
                errorDiv.textContent = 'Login failed. Please try again.';
            }
        }
        
        async function loginWithMAC(e) {
            e.preventDefault();
            const mac = document.getElementById('macAddress').value;
            const errorDiv = document.getElementById('macError');
            
            try {
                const response = await fetch('api/auth.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ mac_address: mac, type: 'mac' })
                });
                
                const data = await response.json();
                if (data.success) {
                    window.location.href = `player.php?mac=${encodeURIComponent(mac)}`;
                } else {
                    errorDiv.textContent = data.message || 'Invalid MAC address';
                }
            } catch (error) {
                errorDiv.textContent = 'Login failed. Please try again.';
            }
        }
        
        // Player Functions
        if (isAuthenticated && playlistUrl) {
            let channels = [];
            let currentCategory = 'all';
            
            // Parse M3U Playlist
            async function loadPlaylist() {
                try {
                    const response = await fetch(playlistUrl);
                    const text = await response.text();
                    channels = parseM3U(text);
                    renderChannels();
                } catch (error) {
                    document.getElementById('channelGrid').innerHTML = 
                        '<div class="loading"><p style="color: #ff4444;">Error loading playlist</p></div>';
                }
            }
            
            function parseM3U(content) {
                const channels = [];
                const lines = content.split('\n');
                let currentChannel = null;
                
                for (let i = 0; i < lines.length; i++) {
                    const line = lines[i].trim();
                    
                    if (line.startsWith('#EXTINF:')) {
                        const extinf = line.substring(8);
                        const nameMatch = extinf.match(/,(.+)$/);
                        const attrs = {};
                        const attrMatches = extinf.matchAll(/([a-zA-Z0-9-]+)="([^"]+)"/g);
                        for (const match of attrMatches) {
                            attrs[match[1]] = match[2];
                        }
                        
                        currentChannel = {
                            name: nameMatch ? nameMatch[1] : 'Unknown',
                            url: '',
                            logo: attrs['tvg-logo'] || attrs['logo'] || '',
                            group: attrs['group-title'] || attrs['group'] || 'Uncategorized',
                            category: getCategoryFromGroup(attrs['group-title'] || attrs['group'] || '')
                        };
                    } else if (line && !line.startsWith('#') && currentChannel) {
                        currentChannel.url = line;
                        channels.push(currentChannel);
                        currentChannel = null;
                    }
                }
                
                return channels;
            }
            
            function getCategoryFromGroup(group) {
                const lower = group.toLowerCase();
                if (lower.includes('movie')) return 'movies';
                if (lower.includes('show') || lower.includes('series')) return 'shows';
                if (lower.includes('sport')) return 'sports';
                return 'live';
            }
            
            let favorites = JSON.parse(localStorage.getItem('bingetv_favorites') || '[]');
            let searchTerm = '';
            
            function renderChannels() {
                let filtered = channels;
                
                // Apply category filter
                if (currentCategory !== 'all' && currentCategory !== 'favorites') {
                    filtered = filtered.filter(c => c.category === currentCategory);
                }
                
                // Apply favorites filter
                if (currentCategory === 'favorites') {
                    filtered = filtered.filter(c => favorites.includes(c.url));
                }
                
                // Apply search filter
                if (searchTerm) {
                    const term = searchTerm.toLowerCase();
                    filtered = filtered.filter(c => 
                        c.name.toLowerCase().includes(term) || 
                        (c.group && c.group.toLowerCase().includes(term))
                    );
                }
                
                const grid = document.getElementById('channelGrid');
                
                if (filtered.length === 0) {
                    grid.innerHTML = '<div class="loading"><p>No channels found</p></div>';
                    return;
                }
                
                grid.innerHTML = filtered.map(channel => {
                    const isFavorite = favorites.includes(channel.url);
                    return `
                        <div class="channel-card" onclick="playChannel('${channel.url.replace(/'/g, "\\'")}', '${channel.name.replace(/'/g, "\\'")}')">
                            <button class="favorite-btn ${isFavorite ? 'active' : ''}" 
                                    onclick="event.stopPropagation(); toggleFavorite('${channel.url.replace(/'/g, "\\'")}')">
                                <i class="fas fa-heart"></i>
                            </button>
                            ${channel.logo ? `<img src="${channel.logo}" alt="${channel.name}" class="channel-logo" loading="lazy" onerror="this.style.display='none'">` : '<div class="channel-logo" style="display: flex; align-items: center; justify-content: center; font-size: 24px;"><i class="fas fa-tv"></i></div>'}
                            <div class="channel-name">${channel.name}</div>
                            ${channel.group ? `<div style="font-size: 12px; color: #888; margin-top: 5px;">${channel.group}</div>` : ''}
                        </div>
                    `;
                }).join('');
            }
            
            function filterChannels() {
                searchTerm = document.getElementById('searchInput').value;
                renderChannels();
            }
            
            function toggleFavorite(url) {
                const index = favorites.indexOf(url);
                if (index > -1) {
                    favorites.splice(index, 1);
                } else {
                    favorites.push(url);
                }
                localStorage.setItem('bingetv_favorites', JSON.stringify(favorites));
                renderChannels();
            }
            
            function showFavorites() {
                document.querySelectorAll('.category-item').forEach(item => item.classList.remove('active'));
                event.target.closest('.category-item').classList.add('active');
                currentCategory = 'favorites';
                renderChannels();
            }
            
            let hls = null;
            
            function playChannel(url, name) {
                const videoArea = document.getElementById('videoArea');
                const channelGrid = document.getElementById('channelGrid');
                const videoPlayer = document.getElementById('video-player');
                
                videoArea.style.display = 'block';
                channelGrid.style.display = 'none';
                
                document.getElementById('currentChannelName').textContent = name;
                document.getElementById('currentChannelDesc').textContent = 'Loading...';
                
                // Clean up previous HLS instance
                if (hls) {
                    hls.destroy();
                    hls = null;
                }
                
                // Check if URL is HLS (.m3u8)
                if (url.includes('.m3u8') || url.includes('m3u8')) {
                    // Use HLS.js for HLS streams
                    if (Hls.isSupported()) {
                        hls = new Hls({
                            enableWorker: true,
                            lowLatencyMode: true,
                            backBufferLength: 90,
                            maxBufferLength: 30,
                            maxMaxBufferLength: 60,
                            startLevel: -1,
                            debug: false
                        });
                        
                        hls.loadSource(url);
                        hls.attachMedia(videoPlayer);
                        
                        hls.on(Hls.Events.MANIFEST_PARSED, () => {
                            videoPlayer.play().catch(e => {
                                console.error('Play error:', e);
                                showError('Unable to play stream. Please try another channel.');
                            });
                            document.getElementById('currentChannelDesc').textContent = 'Now Playing';
                        });
                        
                        hls.on(Hls.Events.ERROR, (event, data) => {
                            if (data.fatal) {
                                switch(data.type) {
                                    case Hls.ErrorTypes.NETWORK_ERROR:
                                        console.error('Network error, trying to recover...');
                                        hls.startLoad();
                                        break;
                                    case Hls.ErrorTypes.MEDIA_ERROR:
                                        console.error('Media error, trying to recover...');
                                        hls.recoverMediaError();
                                        break;
                                    default:
                                        console.error('Fatal error, destroying HLS instance');
                                        hls.destroy();
                                        showError('Stream error. Please try another channel.');
                                        break;
                                }
                            }
                        });
                    } else if (videoPlayer.canPlayType('application/vnd.apple.mpegurl')) {
                        // Native HLS support (Safari)
                        videoPlayer.src = url;
                        videoPlayer.addEventListener('loadedmetadata', () => {
                            videoPlayer.play().catch(e => {
                                console.error('Play error:', e);
                                showError('Unable to play stream.');
                            });
                            document.getElementById('currentChannelDesc').textContent = 'Now Playing';
                        });
                    } else {
                        showError('HLS streaming not supported in this browser.');
                    }
                } else {
                    // Direct stream (MP4, WebM, etc.)
                    videoPlayer.src = url;
                    videoPlayer.load();
                    videoPlayer.play().catch(e => {
                        console.error('Play error:', e);
                        showError('Unable to play stream. Please try another channel.');
                    });
                    document.getElementById('currentChannelDesc').textContent = 'Now Playing';
                }
                
                // Handle video errors
                videoPlayer.addEventListener('error', (e) => {
                    console.error('Video error:', e);
                    showError('Error playing channel. Please try another channel.');
                });
            }
            
            function showError(message) {
                const errorDiv = document.createElement('div');
                errorDiv.style.cssText = 'position: fixed; top: 20px; right: 20px; background: #ff4444; color: white; padding: 15px 20px; border-radius: 8px; z-index: 10000; max-width: 400px;';
                errorDiv.textContent = message;
                document.body.appendChild(errorDiv);
                setTimeout(() => errorDiv.remove(), 5000);
            }
            
            // Cleanup on page unload
            window.addEventListener('beforeunload', () => {
                if (hls) {
                    hls.destroy();
                }
            });
            
            // Category selection
            document.querySelectorAll('.category-item').forEach(item => {
                item.addEventListener('click', function() {
                    if (this.dataset.category === 'favorites') {
                        showFavorites.call(this);
                        return;
                    }
                    document.querySelectorAll('.category-item').forEach(i => i.classList.remove('active'));
                    this.classList.add('active');
                    currentCategory = this.dataset.category;
                    document.getElementById('searchInput').value = '';
                    searchTerm = '';
                    renderChannels();
                });
            });
            
            // Keyboard shortcuts
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && document.getElementById('videoArea').style.display === 'block') {
                    document.getElementById('videoArea').style.display = 'none';
                    document.getElementById('channelGrid').style.display = 'grid';
                    if (hls) {
                        hls.destroy();
                        hls = null;
                    }
                    const videoPlayer = document.getElementById('video-player');
                    videoPlayer.pause();
                    videoPlayer.src = '';
                }
                if (e.key === '/' && e.target.tagName !== 'INPUT') {
                    e.preventDefault();
                    document.getElementById('searchInput').focus();
                }
            });
            
            // Load playlist on page load
            loadPlaylist();
        }
    </script>
</body>
</html>

