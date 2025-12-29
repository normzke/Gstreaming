// BingeTV - Search Functionality for Tizen

class SearchManager {
    static searchChannels(query, channels) {
        if (!query || query.length < 2) {
            return [];
        }

        const lowerQuery = query.toLowerCase();
        return channels.filter(channel =>
            channel.name.toLowerCase().includes(lowerQuery) ||
            (channel.group && channel.group.toLowerCase().includes(lowerQuery))
        );
    }
}
