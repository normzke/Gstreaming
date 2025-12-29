// BingeTV - Storage Manager for Tizen

class StorageManager {
    static saveCredentials(type, data) {
        localStorage.setItem('playlistType', type);
        if (type === 'm3u') {
            localStorage.setItem('m3uUrl', data.url);
        } else if (type === 'xtream') {
            localStorage.setItem('serverUrl', data.server);
            localStorage.setItem('username', data.username);
            localStorage.setItem('password', data.password);
        }
    }

    static getCredentials() {
        const type = localStorage.getItem('playlistType');
        if (!type) return null;

        if (type === 'm3u') {
            return {
                type: 'm3u',
                url: localStorage.getItem('m3uUrl')
            };
        } else if (type === 'xtream') {
            return {
                type: 'xtream',
                server: localStorage.getItem('serverUrl'),
                username: localStorage.getItem('username'),
                password: localStorage.getItem('password')
            };
        }
        return null;
    }

    static clearCredentials() {
        localStorage.removeItem('playlistType');
        localStorage.removeItem('m3uUrl');
        localStorage.removeItem('serverUrl');
        localStorage.removeItem('username');
        localStorage.removeItem('password');
    }

    static setAutoLogin(enabled) {
        localStorage.setItem('autoLogin', enabled ? 'true' : 'false');
    }

    static isAutoLoginEnabled() {
        return localStorage.getItem('autoLogin') === 'true';
    }

    static saveChannels(channels) {
        localStorage.setItem('channels', JSON.stringify(channels));
    }

    static getChannels() {
        const data = localStorage.getItem('channels');
        return data ? JSON.parse(data) : [];
    }

    static saveCategories(categories) {
        localStorage.setItem('categories', JSON.stringify(categories));
    }

    static getCategories() {
        const data = localStorage.getItem('categories');
        return data ? JSON.parse(data) : [];
    }

    static saveFavorites(favorites) {
        localStorage.setItem('favorites', JSON.stringify(favorites));
    }

    static getFavorites() {
        const data = localStorage.getItem('favorites');
        return data ? JSON.parse(data) : [];
    }

    static setGridColumns(columns) {
        localStorage.setItem('gridColumns', columns.toString());
    }

    static getGridColumns() {
        return parseInt(localStorage.getItem('gridColumns') || '5');
    }
}
