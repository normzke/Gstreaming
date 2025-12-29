// BingeTV - Xtream Codes API for Tizen

class XtreamAPI {
    constructor(server, username, password) {
        this.server = server.replace(/\/$/, '');
        this.username = username;
        this.password = password;
    }

    async authenticate() {
        try {
            const url = `${this.server}/player_api.php?username=${this.username}&password=${this.password}`;
            const response = await fetch(url);
            const data = await response.json();

            if (data.user_info && data.user_info.auth === 1) {
                return { success: true, data };
            } else {
                throw new Error('Authentication failed');
            }
        } catch (error) {
            console.error('Xtream Auth Error:', error);
            throw error;
        }
    }

    async getCategories() {
        try {
            const url = `${this.server}/player_api.php?username=${this.username}&password=${this.password}&action=get_live_categories`;
            const response = await fetch(url);
            return await response.json();
        } catch (error) {
            console.error('Get Categories Error:', error);
            return [];
        }
    }

    async getChannels() {
        try {
            const url = `${this.server}/player_api.php?username=${this.username}&password=${this.password}&action=get_live_streams`;
            const response = await fetch(url);
            const streams = await response.json();

            // Convert to our format
            return streams.map(stream => ({
                id: stream.stream_id.toString(),
                name: stream.name,
                logo: stream.stream_icon || '',
                group: stream.category_id || 'Uncategorized',
                url: `${this.server}/live/${this.username}/${this.password}/${stream.stream_id}.ts`
            }));
        } catch (error) {
            console.error('Get Channels Error:', error);
            return [];
        }
    }
}
