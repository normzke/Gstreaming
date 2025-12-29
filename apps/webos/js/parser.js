// BingeTV - M3U Parser for Tizen

class M3UParser {
    static async parsePlaylist(url) {
        try {
            const response = await fetch(url, {
                headers: {
                    'User-Agent': 'Lavf/58.76.100'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const text = await response.text();
            return this.parseM3UContent(text);
        } catch (error) {
            console.error('M3U Parse Error:', error);
            throw error;
        }
    }

    static parseM3UContent(content) {
        const channels = [];
        const lines = content.split('\n');

        let currentChannel = null;

        for (let i = 0; i < lines.length; i++) {
            const line = lines[i].trim();

            if (line.startsWith('#EXTINF:')) {
                // Parse channel info
                const nameMatch = line.match(/,(.+)$/);
                const logoMatch = line.match(/tvg-logo="([^"]+)"/);
                const groupMatch = line.match(/group-title="([^"]+)"/);

                currentChannel = {
                    name: nameMatch ? nameMatch[1].trim() : 'Unknown',
                    logo: logoMatch ? logoMatch[1] : '',
                    group: groupMatch ? groupMatch[1] : 'Uncategorized',
                    url: ''
                };
            } else if (line && !line.startsWith('#') && currentChannel) {
                // This is the stream URL
                currentChannel.url = line;
                currentChannel.id = this.generateId(currentChannel.name + currentChannel.url);
                channels.push(currentChannel);
                currentChannel = null;
            }
        }

        console.log(`Parsed ${channels.length} channels`);
        return channels;
    }

    static generateId(str) {
        let hash = 0;
        for (let i = 0; i < str.length; i++) {
            const char = str.charCodeAt(i);
            hash = ((hash << 5) - hash) + char;
            hash = hash & hash;
        }
        return Math.abs(hash).toString();
    }
}
