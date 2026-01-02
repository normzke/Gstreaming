// BingeTV - Settings Manager for Tizen

class SettingsManager {
    static currentCategory = 'general';

    // Initialize settings
    static init() {
        this.showCategory('general');
        this.setupKeyboardNavigation();
    }

    // Show category content
    static showCategory(category) {
        this.currentCategory = category;

        // Update sidebar
        document.querySelectorAll('.settings-category').forEach(cat => {
            cat.classList.remove('active');
        });
        document.querySelector(`[data-category="${category}"]`).classList.add('active');

        // Load content
        const content = document.getElementById('settingsContent');
        content.innerHTML = this.getCategoryContent(category);

        // Initialize category-specific handlers
        this.initializeCategoryHandlers(category);
    }

    // Get category HTML content
    static getCategoryContent(category) {
        switch (category) {
            case 'general':
                return this.getGeneralContent();
            case 'playlists':
                return this.getPlaylistsContent();
            case 'epg':
                return this.getEpgContent();
            case 'appearance':
                return this.getAppearanceContent();
            case 'playback':
                return this.getPlaybackContent();
            case 'remote':
                return this.getRemoteContent();
            case 'account':
                return this.getAccountContent();
            default:
                return '<p>Category not found</p>';
        }
    }

    // General Settings
    static getGeneralContent() {
        const autoLogin = StorageManager.isAutoLoginEnabled();

        return `
            <div class="settings-section">
                <h3>General Settings</h3>
                
                <div class="setting-item">
                    <div class="setting-label">
                        <h4>Auto Login</h4>
                        <p>Automatically login with saved credentials</p>
                    </div>
                    <div class="setting-control">
                        <label class="toggle-switch">
                            <input type="checkbox" id="autoLogin" ${autoLogin ? 'checked' : ''}>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
            </div>
        `;
    }

    // Playlists Settings
    static getPlaylistsContent() {
        const credentials = StorageManager.getCredentials();
        const type = credentials?.type || 'none';

        return `
            <div class="settings-section">
                <h3>Playlist Management</h3>
                
                <div class="setting-item">
                    <div class="setting-label">
                        <h4>Current Playlist</h4>
                        <p>${type === 'm3u' ? 'M3U Playlist' : type === 'xtream' ? 'Xtream Codes' : 'None'}</p>
                    </div>
                    <div class="setting-control">
                        <button class="btn-secondary" onclick="SettingsManager.changePlaylist()">Change</button>
                        <button class="btn-secondary" onclick="SettingsManager.deletePlaylist()">Delete</button>
                    </div>
                </div>
                
                ${type === 'm3u' ? `
                <div class="setting-item">
                    <div class="setting-label">
                        <h4>M3U URL</h4>
                        <p>${credentials.url || 'Not set'}</p>
                    </div>
                </div>
                ` : ''}
                
                ${type === 'xtream' ? `
                <div class="setting-item">
                    <div class="setting-label">
                        <h4>Server</h4>
                        <p>${credentials.server || 'Not set'}</p>
                    </div>
                </div>
                <div class="setting-item">
                    <div class="setting-label">
                        <h4>Username</h4>
                        <p>${credentials.username || 'Not set'}</p>
                    </div>
                </div>
                ` : ''}
            </div>
        `;
    }

    // EPG Settings
    static getEpgContent() {
        const epgDays = localStorage.getItem('epgDays') || '2';
        const storeDesc = localStorage.getItem('storeDescriptions') === 'true';
        const updateStart = localStorage.getItem('epgUpdateOnStart') === 'true';
        const updateChange = localStorage.getItem('epgUpdateOnChange') === 'true';

        return `
            <div class="settings-section">
                <h3>EPG Settings</h3>
                
                <div class="setting-item">
                    <div class="setting-label">
                        <h4>EPG Days</h4>
                        <p>Number of days to fetch EPG data</p>
                    </div>
                    <div class="setting-control">
                        <select class="select-box" id="epgDays">
                            <option value="1" ${epgDays === '1' ? 'selected' : ''}>1 Day</option>
                            <option value="2" ${epgDays === '2' ? 'selected' : ''}>2 Days</option>
                            <option value="3" ${epgDays === '3' ? 'selected' : ''}>3 Days</option>
                            <option value="5" ${epgDays === '5' ? 'selected' : ''}>5 Days</option>
                            <option value="7" ${epgDays === '7' ? 'selected' : ''}>7 Days</option>
                        </select>
                    </div>
                </div>
                
                <div class="setting-item">
                    <div class="setting-label">
                        <h4>Store Descriptions</h4>
                        <p>Save program descriptions in database</p>
                    </div>
                    <div class="setting-control">
                        <label class="toggle-switch">
                            <input type="checkbox" id="storeDescriptions" ${storeDesc ? 'checked' : ''}>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
                
                <div class="setting-item">
                    <div class="setting-label">
                        <h4>Update on Start</h4>
                        <p>Update EPG when app starts</p>
                    </div>
                    <div class="setting-control">
                        <label class="toggle-switch">
                            <input type="checkbox" id="epgUpdateOnStart" ${updateStart ? 'checked' : ''}>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
                
                <div class="setting-item">
                    <div class="setting-label">
                        <h4>Update on Playlist Change</h4>
                        <p>Update EPG when playlist changes</p>
                    </div>
                    <div class="setting-control">
                        <label class="toggle-switch">
                            <input type="checkbox" id="epgUpdateOnChange" ${updateChange ? 'checked' : ''}>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
                
                <div class="setting-item">
                    <div class="setting-label">
                        <h4>EPG Actions</h4>
                        <p>Manually update or clear EPG data</p>
                    </div>
                    <div class="setting-control">
                        <button class="btn-primary" onclick="SettingsManager.updateEpg()">Update Now</button>
                        <button class="btn-secondary" onclick="SettingsManager.clearEpg()">Clear EPG</button>
                    </div>
                </div>
            </div>
        `;
    }

    // Appearance Settings
    static getAppearanceContent() {
        const gridColumns = StorageManager.getGridColumns();
        const transparency = localStorage.getItem('uiTransparency') || '0';
        const showClock = localStorage.getItem('showClock') === 'true';

        return `
            <div class="settings-section">
                <h3>Appearance Settings</h3>
                
                <div class="setting-item">
                    <div class="setting-label">
                        <h4>Grid Columns</h4>
                        <p>Number of columns in channel grid</p>
                    </div>
                    <div class="setting-control">
                        <div class="slider-container">
                            <input type="range" min="3" max="8" value="${gridColumns}" class="slider" id="gridColumns">
                            <span class="slider-value" id="gridColumnsValue">${gridColumns} columns</span>
                        </div>
                    </div>
                </div>
                
                <div class="setting-item">
                    <div class="setting-label">
                        <h4>UI Transparency</h4>
                        <p>Transparency level for UI elements</p>
                    </div>
                    <div class="setting-control">
                        <div class="slider-container">
                            <input type="range" min="0" max="100" value="${transparency}" class="slider" id="uiTransparency">
                            <span class="slider-value" id="transparencyValue">${transparency}%</span>
                        </div>
                    </div>
                </div>
                
                <div class="setting-item">
                    <div class="setting-label">
                        <h4>Show Clock</h4>
                        <p>Display clock in top bar</p>
                    </div>
                    <div class="setting-control">
                        <label class="toggle-switch">
                            <input type="checkbox" id="showClock" ${showClock ? 'checked' : ''}>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
            </div>
        `;
    }

    // Playback Settings
    static getPlaybackContent() {
        const bufferSize = localStorage.getItem('bufferSize') || 'medium';
        const audioDecoder = localStorage.getItem('audioDecoder') || 'hardware';
        const afrEnabled = localStorage.getItem('afrEnabled') === 'true';

        return `
            <div class="settings-section">
                <h3>Playback Settings</h3>
                
                <div class="setting-item">
                    <div class="setting-label">
                        <h4>Buffer Size</h4>
                        <p>Video buffer size for streaming</p>
                    </div>
                    <div class="setting-control">
                        <select class="select-box" id="bufferSize">
                            <option value="small" ${bufferSize === 'small' ? 'selected' : ''}>Small</option>
                            <option value="medium" ${bufferSize === 'medium' ? 'selected' : ''}>Medium</option>
                            <option value="large" ${bufferSize === 'large' ? 'selected' : ''}>Large</option>
                            <option value="none" ${bufferSize === 'none' ? 'selected' : ''}>None</option>
                        </select>
                    </div>
                </div>
                
                <div class="setting-item">
                    <div class="setting-label">
                        <h4>Audio Decoder</h4>
                        <p>Hardware or software audio decoding</p>
                    </div>
                    <div class="setting-control">
                        <select class="select-box" id="audioDecoder">
                            <option value="hardware" ${audioDecoder === 'hardware' ? 'selected' : ''}>Hardware</option>
                            <option value="software" ${audioDecoder === 'software' ? 'selected' : ''}>Software</option>
                        </select>
                    </div>
                </div>
                
                <div class="setting-item">
                    <div class="setting-label">
                        <h4>Auto Frame Rate (AFR)</h4>
                        <p>Match display refresh rate to content</p>
                    </div>
                    <div class="setting-control">
                        <label class="toggle-switch">
                            <input type="checkbox" id="afrEnabled" ${afrEnabled ? 'checked' : ''}>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
            </div>
        `;
    }

    // Remote Control Settings
    static getRemoteContent() {
        return `
            <div class="settings-section">
                <h3>Remote Control Settings</h3>
                
                <div class="setting-item">
                    <div class="setting-label">
                        <h4>D-Pad Navigation</h4>
                        <p>Configure directional pad behavior</p>
                    </div>
                    <div class="setting-control">
                        <span style="color: #94a3b8;">Default configuration active</span>
                    </div>
                </div>
                
                <div class="setting-item">
                    <div class="setting-label">
                        <h4>Channel Switching</h4>
                        <p>Use Up/Down to switch channels during playback</p>
                    </div>
                    <div class="setting-control">
                        <label class="toggle-switch">
                            <input type="checkbox" id="channelSwitching" checked>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
            </div>
        `;
    }

    // Account Settings
    static getAccountContent() {
        const credentials = StorageManager.getCredentials();
        const type = credentials?.type || 'none';

        return `
            <div class="settings-section">
                <h3>Account Settings</h3>
                
                <div class="setting-item">
                    <div class="setting-label">
                        <h4>Login Type</h4>
                        <p>${type === 'm3u' ? 'M3U Playlist' : type === 'xtream' ? 'Xtream Codes' : 'Not logged in'}</p>
                    </div>
                </div>
                
                <div class="setting-item">
                    <div class="setting-label">
                        <h4>Logout</h4>
                        <p>Clear credentials and return to login</p>
                    </div>
                    <div class="setting-control">
                        <button class="btn-secondary" onclick="SettingsManager.logout()">Logout</button>
                    </div>
                </div>
            </div>
        `;
    }

    // Initialize category-specific handlers
    static initializeCategoryHandlers(category) {
        switch (category) {
            case 'general':
                this.initGeneralHandlers();
                break;
            case 'epg':
                this.initEpgHandlers();
                break;
            case 'appearance':
                this.initAppearanceHandlers();
                break;
            case 'playback':
                this.initPlaybackHandlers();
                break;
            case 'remote':
                this.initRemoteHandlers();
                break;
        }
    }

    // General handlers
    static initGeneralHandlers() {
        const autoLoginToggle = document.getElementById('autoLogin');
        if (autoLoginToggle) {
            autoLoginToggle.addEventListener('change', (e) => {
                StorageManager.setAutoLogin(e.target.checked);
            });
        }
    }

    // EPG handlers
    static initEpgHandlers() {
        const epgDays = document.getElementById('epgDays');
        const storeDesc = document.getElementById('storeDescriptions');
        const updateStart = document.getElementById('epgUpdateOnStart');
        const updateChange = document.getElementById('epgUpdateOnChange');

        if (epgDays) {
            epgDays.addEventListener('change', (e) => {
                localStorage.setItem('epgDays', e.target.value);
            });
        }

        if (storeDesc) {
            storeDesc.addEventListener('change', (e) => {
                localStorage.setItem('storeDescriptions', e.target.checked);
            });
        }

        if (updateStart) {
            updateStart.addEventListener('change', (e) => {
                localStorage.setItem('epgUpdateOnStart', e.target.checked);
            });
        }

        if (updateChange) {
            updateChange.addEventListener('change', (e) => {
                localStorage.setItem('epgUpdateOnChange', e.target.checked);
            });
        }
    }

    // Appearance handlers
    static initAppearanceHandlers() {
        const gridColumns = document.getElementById('gridColumns');
        const gridColumnsValue = document.getElementById('gridColumnsValue');
        const transparency = document.getElementById('uiTransparency');
        const transparencyValue = document.getElementById('transparencyValue');
        const showClock = document.getElementById('showClock');

        if (gridColumns) {
            gridColumns.addEventListener('input', (e) => {
                gridColumnsValue.textContent = `${e.target.value} columns`;
            });
            gridColumns.addEventListener('change', (e) => {
                StorageManager.setGridColumns(parseInt(e.target.value));
                alert('Grid columns updated. Restart app to apply.');
            });
        }

        if (transparency) {
            transparency.addEventListener('input', (e) => {
                transparencyValue.textContent = `${e.target.value}%`;
            });
            transparency.addEventListener('change', (e) => {
                localStorage.setItem('uiTransparency', e.target.value);
            });
        }

        if (showClock) {
            showClock.addEventListener('change', (e) => {
                localStorage.setItem('showClock', e.target.checked);
            });
        }
    }

    // Playback handlers
    static initPlaybackHandlers() {
        const bufferSize = document.getElementById('bufferSize');
        const audioDecoder = document.getElementById('audioDecoder');
        const afrEnabled = document.getElementById('afrEnabled');

        if (bufferSize) {
            bufferSize.addEventListener('change', (e) => {
                localStorage.setItem('bufferSize', e.target.value);
            });
        }

        if (audioDecoder) {
            audioDecoder.addEventListener('change', (e) => {
                localStorage.setItem('audioDecoder', e.target.value);
            });
        }

        if (afrEnabled) {
            afrEnabled.addEventListener('change', (e) => {
                localStorage.setItem('afrEnabled', e.target.checked);
            });
        }
    }

    // Remote handlers
    static initRemoteHandlers() {
        const channelSwitching = document.getElementById('channelSwitching');
        if (channelSwitching) {
            channelSwitching.addEventListener('change', (e) => {
                localStorage.setItem('channelSwitching', e.target.checked);
            });
        }
    }

    // Actions
    static changePlaylist() {
        if (confirm('Return to login to change playlist?')) {
            window.location.href = 'index.html';
        }
    }

    static deletePlaylist() {
        if (confirm('Delete current playlist and credentials?')) {
            StorageManager.clearCredentials();
            window.location.href = 'index.html';
        }
    }

    static updateEpg() {
        alert('EPG update scheduled. This feature will be implemented in the next version.');
    }

    static clearEpg() {
        if (confirm('Clear all EPG data?')) {
            // Clear EPG from localStorage if stored there
            localStorage.removeItem('epgData');
            alert('EPG data cleared.');
        }
    }

    static logout() {
        if (confirm('Logout and return to login screen?')) {
            StorageManager.clearCredentials();
            window.location.href = 'index.html';
        }
    }

    // Keyboard navigation
    static setupKeyboardNavigation() {
        document.addEventListener('keydown', (e) => {
            const categories = ['general', 'playlists', 'epg', 'appearance', 'playback', 'remote', 'account'];
            const currentIndex = categories.indexOf(this.currentCategory);

            if (e.key === 'ArrowUp' && currentIndex > 0) {
                e.preventDefault();
                this.showCategory(categories[currentIndex - 1]);
            } else if (e.key === 'ArrowDown' && currentIndex < categories.length - 1) {
                e.preventDefault();
                this.showCategory(categories[currentIndex + 1]);
            }
        });
    }
}

// Close settings
function closeSettings() {
    window.location.href = 'main.html';
}

// Show category
function showCategory(category) {
    SettingsManager.showCategory(category);
}

// Initialize on load
window.addEventListener('DOMContentLoaded', () => {
    SettingsManager.init();
});
