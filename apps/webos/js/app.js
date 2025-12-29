// BingeTV - Main App Logic for Tizen (index.html)

let currentTab = 'm3u';

// Initialize app
window.onload = function () {
    setTimeout(checkAutoLogin, 2000);
};

function checkAutoLogin() {
    const credentials = StorageManager.getCredentials();
    const autoLogin = StorageManager.isAutoLoginEnabled();

    if (credentials && autoLogin) {
        // Auto-login, go to main
        window.location.href = 'main.html';
    } else {
        // Show login screen
        document.getElementById('splashScreen').style.display = 'none';
        document.getElementById('loginScreen').style.display = 'block';

        // Load saved credentials
        if (credentials) {
            if (credentials.type === 'm3u') {
                document.getElementById('m3uUrl').value = credentials.url || '';
            } else if (credentials.type === 'xtream') {
                document.getElementById('serverUrl').value = credentials.server || '';
                document.getElementById('username').value = credentials.username || '';
            }
        }
    }
}

function showTab(tab) {
    currentTab = tab;

    // Update tab buttons
    document.querySelectorAll('.tab-button').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');

    // Show/hide tab content
    document.getElementById('m3uTab').style.display = tab === 'm3u' ? 'block' : 'none';
    document.getElementById('xtreamTab').style.display = tab === 'xtream' ? 'block' : 'none';
}

async function loadM3uPlaylist() {
    const url = document.getElementById('m3uUrl').value.trim();

    if (!url) {
        showError('Please enter M3U URL');
        return;
    }

    showLoading(true);
    hideError();

    try {
        const channels = await M3UParser.parsePlaylist(url);

        if (channels.length === 0) {
            showError('No channels found in playlist');
            showLoading(false);
            return;
        }

        // Extract categories
        const categories = [...new Set(channels.map(ch => ch.group))];

        // Save data
        StorageManager.saveChannels(channels);
        StorageManager.saveCategories(categories);
        StorageManager.saveCredentials('m3u', { url });

        const rememberMe = document.getElementById('rememberMe').checked;
        StorageManager.setAutoLogin(rememberMe);

        // Navigate to main
        window.location.href = 'main.html';

    } catch (error) {
        showError('Error loading playlist: ' + error.message);
        showLoading(false);
    }
}

async function loadXtreamPlaylist() {
    const server = document.getElementById('serverUrl').value.trim();
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value.trim();

    if (!server || !username || !password) {
        showError('Please fill all fields');
        return;
    }

    showLoading(true);
    hideError();

    try {
        const api = new XtreamAPI(server, username, password);

        // Test authentication
        await api.authenticate();

        // Get channels
        const channels = await api.getChannels();

        if (channels.length === 0) {
            showError('No channels found');
            showLoading(false);
            return;
        }

        // Get categories
        const categoriesData = await api.getCategories();
        const categories = categoriesData.map(cat => cat.category_name);

        // Save data
        StorageManager.saveChannels(channels);
        StorageManager.saveCategories(categories);
        StorageManager.saveCredentials('xtream', { server, username, password });

        const rememberMe = document.getElementById('rememberMe').checked;
        StorageManager.setAutoLogin(rememberMe);

        // Navigate to main
        window.location.href = 'main.html';

    } catch (error) {
        showError('Connection failed: ' + error.message);
        showLoading(false);
    }
}

function showLoading(show) {
    document.getElementById('loadingMessage').style.display = show ? 'block' : 'none';
}

function showError(message) {
    const errorEl = document.getElementById('errorMessage');
    errorEl.textContent = message;
    errorEl.style.display = 'block';
}

function hideError() {
    document.getElementById('errorMessage').style.display = 'none';
}
