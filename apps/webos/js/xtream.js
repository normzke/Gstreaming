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

    async getCategories(action = 'get_live_categories') {
        try {
            const url = `${this.server}/player_api.php?username=${this.username}&password=${this.password}&action=${action}`;
            const response = await fetch(url);
            return await response.json();
        } catch (error) {
            console.error(`Get Categories (${action}) Error:`, error);
            return [];
        }
    }

    async getStreams(action = 'get_live_streams', type = 'live') {
        try {
            const url = `${this.server}/player_api.php?username=${this.username}&password=${this.password}&action=${action}`;
            const response = await fetch(url);
            const streams = await response.json();

            // Convert to our format
            return streams.map(stream => {
                const streamId = stream.stream_id || stream.series_id || stream.id;
                const extension = stream.container_extension || 'mp4';
                let streamUrl = '';

                if (type === 'live') {
                    streamUrl = `${this.server}/live/${this.username}/${this.password}/${streamId}.ts`;
                } else if (type === 'movie') {
                    streamUrl = `${this.server}/movie/${this.username}/${this.password}/${streamId}.${extension}`;
                } else if (type === 'series') {
                    // Series usually requires getting episode info first, but for grid we link to details
                    streamUrl = '';
                }

                return {
                    id: streamId.toString(),
                    name: stream.name,
                    logo: stream.stream_icon || stream.cover || '',
                    group: stream.category_id || 'Uncategorized',
                    url: streamUrl,
                    type: type,
                    rating: stream.rating || '',
                    plot: stream.plot || ''
                };
            });
        } catch (error) {
            console.error(`Get Streams (${action}) Error:`, error);
            return [];
        }
    }

    async getInfo(id, type = 'movie') {
        try {
            const action = type === 'movie' ? 'get_vod_info' : 'get_series_info';
            const param = type === 'movie' ? 'vod_id' : 'series_id';
            const url = `${this.server}/player_api.php?username=${this.username}&password=${this.password}&action=${action}&${param}=${id}`;
            const response = await fetch(url);
            return await response.json();
        } catch (error) {
            console.error(`Get Info (${type}) Error:`, error);
            return null;
        }
    }
}
