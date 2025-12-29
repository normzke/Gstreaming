// BingeTV - Favorites Manager for Tizen

class FavoritesManager {
    static toggleFavorite(channelId) {
        let favorites = StorageManager.getFavorites();
        const index = favorites.indexOf(channelId);

        if (index > -1) {
            favorites.splice(index, 1);
        } else {
            favorites.push(channelId);
        }

        StorageManager.saveFavorites(favorites);
        return favorites.includes(channelId);
    }

    static isFavorite(channelId) {
        const favorites = StorageManager.getFavorites();
        return favorites.includes(channelId);
    }

    static getFavoriteChannels(allChannels) {
        const favorites = StorageManager.getFavorites();
        return allChannels.filter(ch => favorites.includes(ch.id));
    }
}
