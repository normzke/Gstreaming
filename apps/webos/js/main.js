// BingeTV - Main Screen Logic for Tizen (main.html)

let allChannels = [];
let currentCategory = 'all';
let selectedChannel = null;
let previewTimeout = null;
let previewPlayer = null;
let epgRefreshInterval = null;

// Initialize
window.onload = function () {
    initializeClock();
    initializeNavigation();
    loadChannels();
    initializePreviewPlayer();
    initializeTopPreview();
    startEpgRefresh();
    applyGridColumns();
    setupFocusOptimization();
    checkForUpdates();
};

async function checkForUpdates() {
    try {
        const response = await fetch('https://bingetv.co.ke/apps/update.json');
        const update = await response.json();

        // Placeholder check against local version - standardizing on 1.0.0
        const currentVersion = "1.0.0";

        if (update && update.tizen && update.tizen.version > currentVersion) {
            showUpdateModal(update.tizen);
        }
    } catch (e) {
        console.log('Update check failed (safe to ignore)');
    }
}

function showUpdateModal(info) {
    const modal = document.createElement('div');
    modal.className = 'modal';
    modal.style.display = 'flex';
    modal.innerHTML = `
        <div class="modal-content">
            <h2>New Update Available (${info.version})</h2>
            <p>${info.notes}</p>
            <button onclick="this.parentElement.parentElement.remove()" class="btn-primary">Later</button>
            <a href="${info.url}" class="btn-secondary" style="text-decoration:none; display:inline-block; padding:10px 20px; border-radius:4px; margin-top:10px;">Download Now</a>
        </div>
    `;
    document.body.appendChild(modal);
}

function setupFocusOptimization() {
    const navRail = document.querySelector('.nav-rail');
    const catSidebar = document.querySelector('.category-sidebar');
    const mainWrapper = document.querySelector('.main-content-wrapper');

    document.addEventListener('focusin', (e) => {
        const target = e.target;

        // Navigation Rail Focus
        if (target.closest('.nav-rail')) {
            navRail.classList.add('expanded');
            catSidebar.classList.remove('collapsed');
            mainWrapper.style.marginLeft = '200px';
        }
        // Category Sidebar Focus
        else if (target.closest('.category-sidebar')) {
            navRail.classList.remove('expanded');
            catSidebar.classList.remove('collapsed');
            mainWrapper.style.marginLeft = '80px';
        }
        // Channel Grid Focus
        else if (target.closest('.channel-grid-container')) {
            navRail.classList.remove('expanded');
            catSidebar.classList.add('collapsed');
            mainWrapper.style.marginLeft = '80px';
        }
    });
}

// ===== NAVIGATION RAIL =====
function initializeClock() {
    updateClock();
    setInterval(updateClock, 60000); // Update every minute
}

function updateClock() {
    const clock = document.getElementById('navClock');
    if (!clock) return;

    const now = new Date();
    const hours = now.getHours().toString().padStart(2, '0');
    const minutes = now.getMinutes().toString().padStart(2, '0');
    clock.textContent = `${hours}:${minutes}`;
}

function initializeNavigation() {
    document.querySelectorAll('.nav-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const mode = btn.getAttribute('data-mode');
            switchMode(mode);
        });
    });
}

function switchMode(mode) {
    // Update active state
    document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));
    const activeBtn = document.querySelector(`[data-mode="${mode}"]`);
    if (activeBtn) activeBtn.classList.add('active');

    // Handle mode-specific logic
    const movieKeywords = ["movie", "cinema", "vod", "film"];
    const showKeywords = ["series", "show", "season", "tv"];

    switch (mode) {
        case 'search':
            showSearch();
            break;
        case 'favorites':
            filterByCategory('favorites');
            break;
        case 'settings':
            showSettings();
            break;
        case 'movies':
            filterByMode(movieKeywords);
            break;
        case 'shows':
            filterByMode(showKeywords);
            break;
        case 'live':
        default:
            filterByCategory('all');
            break;
    }
}

function filterByMode(keywords) {
    const categories = StorageManager.getCategories();
    const filteredCats = categories.filter(cat =>
        keywords.some(k => cat.toLowerCase().includes(k))
    );

    // Update sidebar
    updateCategorySidebar(filteredCats);

    // Initial filter grid by all in this mode
    const filteredChannels = allChannels.filter(ch =>
        keywords.some(k => (ch.group || "").toLowerCase().includes(k))
    );
    displayChannels(filteredChannels);
}

function updateCategorySidebar(categories) {
    const sidebar = document.getElementById('categorySidebar');
    // Keep first two static items
    const staticItems = Array.from(sidebar.querySelectorAll('.category-item')).slice(0, 2);
    sidebar.innerHTML = '';
    staticItems.forEach(item => sidebar.appendChild(item));

    categories.forEach(category => {
        const div = document.createElement('div');
        div.className = 'category-item';
        div.setAttribute('data-category', category);
        div.textContent = category;
        div.onclick = () => filterByCategory(category);
        div.setAttribute('tabindex', '0');
        sidebar.appendChild(div);
    });
}

// ===== TOP PREVIEW PLAYER =====
function initializeTopPreview() {
    // Top preview player is always visible, update on channel focus
}

function updateTopPreview(channel) {
    const video = document.getElementById('topPreviewVideo');
    const logo = document.getElementById('topPreviewLogo');
    const name = document.getElementById('topPreviewName');
    const now = document.getElementById('topPreviewNow');
    const next = document.getElementById('topPreviewNext');
    const progress = document.getElementById('topPreviewProgress');

    if (!video || !logo || !name) return;

    // Update channel info
    logo.src = channel.logo || 'icon.png';
    logo.onerror = () => { logo.src = 'icon.png'; };
    name.textContent = channel.name;

    // Load preview video
    video.src = channel.url;
    video.play().catch(err => console.log('Top preview play failed:', err));

    // Update EPG (placeholder - can be enhanced with real EPG data)
    if (now) now.textContent = 'Now Playing';
    if (next) next.textContent = 'Next: --';
    if (progress) progress.style.width = '0%';
}

function loadChannels() {
    allChannels = StorageManager.getChannels();
    const categories = StorageManager.getCategories();

    if (allChannels.length === 0) {
        // No channels, go back to login
        window.location.href = 'index.html';
        return;
    }

    // Populate categories
    const sidebar = document.getElementById('categorySidebar');
    categories.forEach(category => {
        const div = document.createElement('div');
        div.className = 'category-item';
        div.setAttribute('data-category', category);
        div.textContent = category;
        div.onclick = () => filterByCategory(category);
        sidebar.appendChild(div);
    });

    // Display channels
    displayChannels(allChannels);
}

function displayChannels(channels) {
    const grid = document.getElementById('channelGrid');
    grid.innerHTML = '';

    channels.forEach(channel => {
        const card = createChannelCard(channel);
        grid.appendChild(card);
    });
}

function createChannelCard(channel) {
    const card = document.createElement('div');
    card.className = 'channel-card';
    card.setAttribute('tabindex', '0');
    card.onclick = () => playChannel(channel);
    card.oncontextmenu = (e) => {
        e.preventDefault();
        showChannelContext(channel);
    };

    // Update top preview on focus
    card.onfocus = () => {
        updateTopPreview(channel);
        showPreview(channel); // Keep bottom preview too
    };
    card.onblur = () => hidePreview();

    const logo = document.createElement('img');
    logo.className = 'channel-logo';
    logo.src = channel.logo || 'icon.png';
    logo.onerror = () => { logo.src = 'icon.png'; };

    const name = document.createElement('div');
    name.className = 'channel-name';
    name.textContent = channel.name;

    card.appendChild(logo);
    card.appendChild(name);

    // EPG Info Container
    const epgContainer = document.createElement('div');
    epgContainer.className = 'epg-card-info';

    const epgNow = document.createElement('div');
    epgNow.className = 'epg-now-text';
    epgNow.textContent = 'No Program Info';

    const epgProgress = document.createElement('div');
    epgProgress.className = 'epg-card-progress';
    const epgProgressBar = document.createElement('div');
    epgProgressBar.className = 'epg-card-progress-bar';
    epgProgress.appendChild(epgProgressBar);

    epgContainer.appendChild(epgNow);
    epgContainer.appendChild(epgProgress);
    card.appendChild(epgContainer);

    // Add favorite badge
    if (FavoritesManager.isFavorite(channel.id)) {
        const badge = document.createElement('div');
        badge.className = 'favorite-badge';
        badge.textContent = '⭐';
        card.appendChild(badge);
    }

    return card;
}

function filterByCategory(category) {
    currentCategory = category;

    // Update active category
    document.querySelectorAll('.category-item').forEach(item => {
        item.classList.remove('active');
        if (item.getAttribute('data-category') === category) {
            item.classList.add('active');
        }
    });

    // Filter channels
    let filtered;
    if (category === 'all') {
        filtered = allChannels;
    } else if (category === 'favorites') {
        filtered = FavoritesManager.getFavoriteChannels(allChannels);
    } else {
        filtered = allChannels.filter(ch => ch.group === category);
    }

    displayChannels(filtered);
}

function playChannel(channel) {
    // Navigate to enhanced player with channel ID
    window.location.href = `player.html?id=${encodeURIComponent(channel.id)}`;
}

function showSearch() {
    document.getElementById('searchModal').style.display = 'flex';
    document.getElementById('searchInput').focus();
}

function closeSearch() {
    document.getElementById('searchModal').style.display = 'none';
    document.getElementById('searchInput').value = '';
    document.getElementById('searchResults').innerHTML = '';
}

function performSearch() {
    const query = document.getElementById('searchInput').value;
    const results = SearchManager.searchChannels(query, allChannels);

    const resultsGrid = document.getElementById('searchResults');
    resultsGrid.innerHTML = '';

    results.forEach(channel => {
        const card = createChannelCard(channel);
        resultsGrid.appendChild(card);
    });
}

function showFavorites() {
    filterByCategory('favorites');
}

function showSettings() {
    window.location.href = 'settings.html';
}

function showChannelContext(channel) {
    selectedChannel = channel;

    document.getElementById('contextLogo').src = channel.logo || 'icon.png';
    document.getElementById('contextName').textContent = channel.name;
    document.getElementById('contextCategory').textContent = channel.group;

    const isFav = FavoritesManager.isFavorite(channel.id);
    document.getElementById('contextFavoriteBtn').textContent =
        isFav ? 'Remove from Favorites' : 'Add to Favorites';

    document.getElementById('contextMenu').style.display = 'flex';
}

function closeContextMenu() {
    document.getElementById('contextMenu').style.display = 'none';
    selectedChannel = null;
}

function playFromContext() {
    if (selectedChannel) {
        playChannel(selectedChannel);
    }
}

function toggleFavoriteFromContext() {
    if (selectedChannel) {
        FavoritesManager.toggleFavorite(selectedChannel.id);
        closeContextMenu();

        // Refresh display
        filterByCategory(currentCategory);
    }
}

// ===== PREVIEW PLAYER =====
function initializePreviewPlayer() {
    previewPlayer = document.getElementById('previewVideo');
}

function showPreview(channel) {
    const previewEnabled = localStorage.getItem('previewEnabled') !== 'false';
    if (!previewEnabled) return;

    // Clear any existing timeout
    if (previewTimeout) {
        clearTimeout(previewTimeout);
    }

    // Set timeout to show preview after 2 seconds
    previewTimeout = setTimeout(() => {
        const previewContainer = document.getElementById('previewPlayer');
        const previewName = document.getElementById('previewChannelName');

        previewName.textContent = channel.name;
        previewPlayer.src = channel.url;
        previewContainer.style.display = 'block';

        previewPlayer.play().catch(err => {
            console.log('Preview play failed:', err);
        });
    }, 2000);
}

function hidePreview() {
    if (previewTimeout) {
        clearTimeout(previewTimeout);
        previewTimeout = null;
    }

    const previewContainer = document.getElementById('previewPlayer');
    previewContainer.style.display = 'none';

    if (previewPlayer) {
        previewPlayer.pause();
        previewPlayer.src = '';
    }
}

// ===== EPG AUTO-REFRESH =====
function startEpgRefresh() {
    const epgUpdateOnStart = localStorage.getItem('epgUpdateOnStart') === 'true';

    if (epgUpdateOnStart) {
        // Refresh EPG every 6 hours
        epgRefreshInterval = setInterval(() => {
            console.log('Auto-refreshing EPG data...');
            // EPG refresh logic would go here
            // For now, just log it
        }, 6 * 60 * 60 * 1000); // 6 hours
    }
}

function stopEpgRefresh() {
    if (epgRefreshInterval) {
        clearInterval(epgRefreshInterval);
        epgRefreshInterval = null;
    }
}

// ===== GRID CUSTOMIZATION =====
function applyGridColumns() {
    const gridColumns = StorageManager.getGridColumns();
    const channelGrid = document.getElementById('channelGrid');

    if (channelGrid) {
        channelGrid.style.gridTemplateColumns = `repeat(${gridColumns}, 1fr)`;
    }
}

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    hidePreview();
    stopEpgRefresh();
});
