// Global State
let allChannels = [];
let categories = [];
let currentCategory = 'all';
let searchTerm = '';
let hls = null;
let favorites = JSON.parse(localStorage.getItem('bingetv_favorites') || '[]');
let currentChannelIndex = -1; // For channel switching
let currentPlayingChannel = null;

// Lazy Loading State
let filteredChannels = [];
let displayedCount = 0;
const BATCH_SIZE = 50;
let sentinelObserver = null;

// Initialization
document.addEventListener('DOMContentLoaded', () => {
    console.log("Player initializing...");
    // Setup Clock
    setInterval(updateClock, 1000);
    updateClock();

    // Setup Search
    setupSearch();

    // Load Content
    loadPlaylist();
});

// Playlist Management
window.deletePlaylist = function () {
    if (confirm('Are you sure you want to delete this playlist and its data?')) {
        localStorage.removeItem('bingetv_favorites');
        // Add other keys if needed
        window.location.href = 'logout.php';
    }
};

// Clock Logic
function updateClock() {
    const now = new Date();
    const timeString = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    const clockEl = document.getElementById('clock');
    if (clockEl) clockEl.textContent = timeString;
}

// Search Logic
function setupSearch() {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            searchTerm = e.target.value.toLowerCase();
            displayedCount = 0; // Reset scroll
            filterChannels();
        });

        // Keyboard shortcut for search
        document.addEventListener('keydown', (e) => {
            if (e.key === '/' && document.activeElement !== searchInput) {
                e.preventDefault();
                searchInput.focus();
            }
        });
    }
}

// Playlist Loading
async function loadPlaylist() {
    console.log('Fetching configuration...');
    const loadingEl = document.getElementById('loading');

    try {
        // 1. Get Config
        const configText = await fetchProxy('get_config');

        // 2. Get Categories
        loadingEl.innerHTML = '<i class="fas fa-spinner"></i><br><br>Loading Categories...';
        const categoriesData = await fetchProxy('get_live_categories');
        categories = categoriesData;
        renderCategories();

        // 3. Get Channels
        loadingEl.innerHTML = '<i class="fas fa-satellite-dish"></i><br><br>Loading Channels...';
        const streamsData = await fetchProxy('get_live_streams');

        // Process Channels
        allChannels = streamsData.map(stream => ({
            id: stream.stream_id,
            num: stream.num,
            name: stream.name,
            icon: stream.stream_icon,
            categoryId: stream.category_id,
            streamType: stream.stream_type,
            // Use Proxy for Stream to fix CORS/Mixed Content
            url: `api/playlist_proxy.php?action=stream&stream_id=${stream.stream_id}`
        }));

        console.log(`Loaded ${allChannels.length} channels.`);
        loadingEl.style.display = 'none';

        // Initial Render
        filterChannels();

    } catch (error) {
        console.error('Playlist Error:', error);
        loadingEl.innerHTML = `<i class="fas fa-exclamation-triangle"></i><br><br>${error.message}`;
        showError('Failed to load playlist. Please reload.');
    }
}

async function fetchProxy(action) {
    const url = `api/playlist_proxy.php?action=${action}`;
    const response = await fetch(url);
    if (!response.ok) throw new Error(`API Error: ${response.status}`);
    return await response.json();
}

// Rendering
function renderCategories() {
    const list = document.getElementById('categoryList');
    list.innerHTML = `
        <li class="category-item active" data-category="all" onclick="selectCategory('all', this)">
            <i class="fas fa-globe"></i> All Channels
        </li>
        <li class="category-item" data-category="favorites" onclick="selectCategory('favorites', this)">
            <i class="fas fa-star" style="color: gold;"></i> Favorites
        </li>
    ` + categories.map(cat => `
        <li class="category-item" data-category="${cat.category_id}" onclick="selectCategory('${cat.category_id}', this)">
            ${cat.category_name}
        </li>
    `).join('');
}

function selectCategory(catId, element) {
    // Update UI
    document.querySelectorAll('.category-item').forEach(el => el.classList.remove('active'));
    element.classList.add('active');

    // Update State
    currentCategory = catId;
    document.getElementById('searchInput').value = '';
    searchTerm = '';

    // Reset Scroll
    displayedCount = 0;
    filterChannels();
}

function filterChannels() {
    // Filter
    filteredChannels = allChannels.filter(ch => {
        const matchesSearch = !searchTerm || ch.name.toLowerCase().includes(searchTerm);
        if (!matchesSearch) return false;

        if (currentCategory === 'all') return true;
        if (currentCategory === 'favorites') return favorites.includes(ch.id); // Fixed logic
        return ch.categoryId === currentCategory;
    });

    // Reset Grid
    const grid = document.getElementById('channelGrid');
    grid.innerHTML = '';
    displayedCount = 0;

    // Check Empty
    if (filteredChannels.length === 0) {
        grid.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 50px; color: #666;">No channels found</div>';
        return;
    }

    // Setup Intersection Observer for Infinite Scroll
    if (sentinelObserver) sentinelObserver.disconnect();

    sentinelObserver = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting) {
            renderMoreChannels();
        }
    }, { root: grid, rootMargin: '200px' }); // Load 200px before reaching bottom

    // Add Sentinel Element
    const sentinel = document.createElement('div');
    sentinel.id = 'scroll-sentinel';
    sentinel.style.height = '10px';
    sentinel.style.gridColumn = '1/-1';
    grid.appendChild(sentinel);
    sentinelObserver.observe(sentinel);

    // Initial Batch
    renderMoreChannels();
}

function renderMoreChannels() {
    const grid = document.getElementById('channelGrid');
    const sentinel = document.getElementById('scroll-sentinel');

    // Determine range
    const start = displayedCount;
    const end = Math.min(start + BATCH_SIZE, filteredChannels.length);

    if (start >= end) {
        return; // No more
    }

    const chunk = filteredChannels.slice(start, end);
    const fragment = document.createDocumentFragment();

    chunk.forEach(ch => {
        const isFav = favorites.includes(ch.id);
        const card = document.createElement('div');
        card.className = 'channel-card';
        card.onclick = () => playChannel(ch); // Use arrow function
        card.innerHTML = `
            <button class="favorite-btn ${isFav ? 'active' : ''}" onclick="toggleFavorite(event, '${ch.id}')">
                <i class="fas fa-heart"></i>
            </button>
            <img class="channel-logo" src="${ch.icon || 'assets/images/default_channel.png'}" 
                 loading="lazy" 
                 onerror="this.style.display='none'">
            <div class="channel-name">${ch.name}</div>
        `;
        fragment.appendChild(card);
    });

    // Insert before sentinel
    grid.insertBefore(fragment, sentinel);
    displayedCount = end;
}

// Playback Logic
function playChannel(channel) {
    // Show Video Area
    const videoArea = document.getElementById('videoArea');
    const channelGrid = document.getElementById('channelGrid');
    const videoPlayer = document.getElementById('video-player');

    videoArea.style.display = 'block';
    channelGrid.style.display = 'none';

    document.getElementById('currentChannelName').textContent = channel.name;
    document.getElementById('currentChannelDesc').textContent = 'Loading...';

    if (Hls.isSupported()) {
        if (hls) hls.destroy();

        const config = {
            enableWorker: true,
            lowLatencyMode: true,
            backBufferLength: 90,
            maxBufferLength: 60, // Increased for 4K stability (matches Android)
            maxMaxBufferLength: 120,
            liveSyncDurationCount: 3,
            startLevel: -1 // Auto
        };

        hls = new Hls(config);
        hls.loadSource(channel.url);
        hls.attachMedia(videoPlayer);

        hls.on(Hls.Events.MANIFEST_PARSED, () => {
            videoPlayer.play().catch(e => console.error("Auto-play blocked", e));
            document.getElementById('currentChannelDesc').textContent = 'Live';
        });

        hls.on(Hls.Events.ERROR, (event, data) => {
            if (data.fatal) {
                switch (data.type) {
                    case Hls.ErrorTypes.NETWORK_ERROR:
                        hls.startLoad();
                        break;
                    case Hls.ErrorTypes.MEDIA_ERROR:
                        hls.recoverMediaError();
                        break;
                    default:
                        hls.destroy();
                        break;
                }
            }
        });
    } else if (videoPlayer.canPlayType('application/vnd.apple.mpegurl')) {
        videoPlayer.src = channel.url;
        videoPlayer.play();
    }

    // Handle Escape Key and Arrow Keys for channel switching
    videoPlayer.focus();

    // Store current channel for switching
    currentPlayingChannel = channel;
    currentChannelIndex = filteredChannels.findIndex(ch => ch.id === channel.id);
}

// Channel Switching with Arrow Keys
document.addEventListener('keydown', (e) => {
    const videoArea = document.getElementById('videoArea');
    if (videoArea.style.display === 'block') {
        if (e.key === 'ArrowUp' || e.key === 'ArrowDown') {
            e.preventDefault();
            switchChannel(e.key === 'ArrowUp' ? -1 : 1);
        } else if (e.key === 'Escape') {
            stopPlayer();
        } else if (e.key === 't' || e.key === 'T') {
            // Track selection menu
            showTrackMenu();
        }
    }
});

function switchChannel(direction) {
    if (filteredChannels.length === 0) return;

    currentChannelIndex += direction;

    // Wrap around
    if (currentChannelIndex < 0) {
        currentChannelIndex = filteredChannels.length - 1;
    } else if (currentChannelIndex >= filteredChannels.length) {
        currentChannelIndex = 0;
    }

    const nextChannel = filteredChannels[currentChannelIndex];
    playChannel(nextChannel);
}

// Track Selection Menu
function showTrackMenu() {
    const videoPlayer = document.getElementById('video-player');

    // Get available tracks
    const audioTracks = videoPlayer.audioTracks || [];
    const textTracks = videoPlayer.textTracks || [];

    let menuHTML = '<div style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: rgba(0,0,0,0.9); padding: 30px; border-radius: 12px; z-index: 1000; min-width: 300px;">';
    menuHTML += '<h3 style="color: #00A8FF; margin-bottom: 20px;">Track Selection</h3>';

    // Audio Tracks
    menuHTML += '<h4 style="color: #fff; margin: 15px 0 10px 0;">Audio Tracks</h4>';
    if (audioTracks.length > 0) {
        for (let i = 0; i < audioTracks.length; i++) {
            const track = audioTracks[i];
            const active = track.enabled ? ' (Active)' : '';
            menuHTML += `<button onclick="selectAudioTrack(${i})" style="display: block; width: 100%; padding: 10px; margin: 5px 0; background: ${track.enabled ? '#00A8FF' : '#333'}; border: none; color: white; cursor: pointer; border-radius: 5px;">${track.label || 'Audio ' + (i + 1)}${active}</button>`;
        }
    } else {
        menuHTML += '<p style="color: #999;">Default Audio</p>';
    }

    // Subtitle Tracks
    menuHTML += '<h4 style="color: #fff; margin: 15px 0 10px 0;">Subtitles</h4>';
    if (textTracks.length > 0) {
        menuHTML += '<button onclick="selectSubtitleTrack(-1)" style="display: block; width: 100%; padding: 10px; margin: 5px 0; background: #333; border: none; color: white; cursor: pointer; border-radius: 5px;">Off</button>';
        for (let i = 0; i < textTracks.length; i++) {
            const track = textTracks[i];
            if (track.kind === 'subtitles' || track.kind === 'captions') {
                const active = track.mode === 'showing' ? ' (Active)' : '';
                menuHTML += `<button onclick="selectSubtitleTrack(${i})" style="display: block; width: 100%; padding: 10px; margin: 5px 0; background: ${track.mode === 'showing' ? '#00A8FF' : '#333'}; border: none; color: white; cursor: pointer; border-radius: 5px;">${track.label || 'Subtitle ' + (i + 1)}${active}</button>`;
            }
        }
    } else {
        menuHTML += '<p style="color: #999;">No subtitles available</p>';
    }

    menuHTML += '<button onclick="closeTrackMenu()" style="display: block; width: 100%; padding: 10px; margin-top: 20px; background: #8B0000; border: none; color: white; cursor: pointer; border-radius: 5px;">Close</button>';
    menuHTML += '</div>';

    const menu = document.createElement('div');
    menu.id = 'trackMenu';
    menu.innerHTML = menuHTML;
    document.body.appendChild(menu);
}

window.selectAudioTrack = function (index) {
    const videoPlayer = document.getElementById('video-player');
    if (videoPlayer.audioTracks) {
        for (let i = 0; i < videoPlayer.audioTracks.length; i++) {
            videoPlayer.audioTracks[i].enabled = (i === index);
        }
    }
    closeTrackMenu();
};

window.selectSubtitleTrack = function (index) {
    const videoPlayer = document.getElementById('video-player');
    if (videoPlayer.textTracks) {
        for (let i = 0; i < videoPlayer.textTracks.length; i++) {
            videoPlayer.textTracks[i].mode = (i === index) ? 'showing' : 'disabled';
        }
    }
    closeTrackMenu();
};

window.closeTrackMenu = function () {
    const menu = document.getElementById('trackMenu');
    if (menu) menu.remove();
};

function stopPlayer() {
    const videoArea = document.getElementById('videoArea');
    const channelGrid = document.getElementById('channelGrid');
    const videoPlayer = document.getElementById('video-player');

    if (hls) {
        hls.destroy();
        hls = null;
    }
    videoPlayer.pause();
    videoPlayer.src = '';

    videoArea.style.display = 'none';
    channelGrid.style.display = 'grid';
}

// Favorites Functionality
function toggleFavorite(e, channelId) {
    e.stopPropagation();
    const index = favorites.indexOf(channelId);
    if (index === -1) {
        favorites.push(channelId);
        e.currentTarget.classList.add('active');
    } else {
        favorites.splice(index, 1);
        e.currentTarget.classList.remove('active');
    }
    localStorage.setItem('bingetv_favorites', JSON.stringify(favorites));

    // If viewing favorites, refresh
    if (currentCategory === 'favorites') {
        filterChannels();
    }
}

// Error Message
function showError(msg) {
    const err = document.createElement('div');
    err.style.cssText = `
        position: fixed; top: 20px; right: 20px;
        background: #8B0000; color: white;
        padding: 15px 25px; border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.5);
        z-index: 99999; animation: slideIn 0.3s ease-out;
    `;
    err.textContent = msg;
    document.body.appendChild(err);
    setTimeout(() => err.remove(), 4000);
}

// Global exposure for PHP to call
window.loadPlaylist = loadPlaylist;
window.stopPlayer = stopPlayer;
