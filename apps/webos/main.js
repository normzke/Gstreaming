// BingeTV - Main Screen Logic for Tizen (main.html)

let allChannels = [];
let currentCategory = 'all';
let selectedChannel = null;
let previewTimeout = null;
let previewPlayer = null;
let epgRefreshInterval = null;

// Initialize
window.onload = function () {
    loadChannels();
    initializePreviewPlayer();
    startEpgRefresh();
    applyGridColumns();
};

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

    // Preview on focus
    card.onfocus = () => showPreview(channel);
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
