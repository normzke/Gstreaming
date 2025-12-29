// M3U Playlist Parser for Samsung Tizen
class M3UParser {
    constructor() {
        this.channels = [];
    }

    async parsePlaylist(url) {
        try {
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'User-Agent': 'BingeTV/1.0'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const text = await response.text();
            return this.parseM3UContent(text);
        } catch (error) {
            console.error('Error fetching playlist:', error);
            throw error;
        }
    }

    parseM3UContent(content) {
        const channels = [];
        const lines = content.split('\n');
        
        let currentChannel = null;
        let currentName = null;
        let currentUrl = null;
        let currentLogo = null;
        let currentGroup = null;
        let currentTvgId = null;
        let currentTvgName = null;
        let currentTvgLogo = null;
        let currentTvgChno = null;
        let currentTvgShift = null;
        let isRadio = false;
        let catchup = null;
        let catchupDays = null;
        let catchupSource = null;

        for (let i = 0; i < lines.length; i++) {
            const line = lines[i].trim();

            if (line.startsWith('#EXTM3U')) {
                continue;
            }

            if (line.startsWith('#EXTINF:')) {
                // Parse EXTINF line
                const extinfContent = line.substring(8);
                
                // Extract attributes
                const attributes = this.extractAttributes(extinfContent);
                
                // Extract name (after attributes or after duration)
                const nameMatch = extinfContent.match(/,(.+)$/);
                if (nameMatch) {
                    currentName = nameMatch[1].trim();
                }
                
                // Extract attributes
                currentLogo = attributes['tvg-logo'] || attributes['logo'];
                currentGroup = attributes['group-title'] || attributes['group'];
                currentTvgId = attributes['tvg-id'];
                currentTvgName = attributes['tvg-name'];
                currentTvgLogo = attributes['tvg-logo'];
                currentTvgChno = attributes['tvg-chno'];
                currentTvgShift = attributes['tvg-shift'];
                isRadio = attributes.hasOwnProperty('radio') || 
                         (attributes['type'] && attributes['type'].toLowerCase() === 'radio');
                catchup = attributes['catchup'];
                catchupDays = attributes['catchup-days'];
                catchupSource = attributes['catchup-source'];
            } else if (line && !line.startsWith('#')) {
                // This is the URL line
                currentUrl = line;

                if (currentName && currentUrl) {
                    channels.push({
                        name: currentName || 'Unknown',
                        url: currentUrl,
                        logo: currentLogo || currentTvgLogo,
                        group: currentGroup,
                        tvgId: currentTvgId,
                        tvgName: currentTvgName,
                        tvgLogo: currentTvgLogo,
                        tvgChno: currentTvgChno,
                        tvgShift: currentTvgShift,
                        radio: isRadio,
                        catchup: catchup,
                        catchupDays: catchupDays,
                        catchupSource: catchupSource
                    });
                }

                // Reset for next channel
                currentName = null;
                currentUrl = null;
                currentLogo = null;
                currentGroup = null;
                currentTvgId = null;
                currentTvgName = null;
                currentTvgLogo = null;
                currentTvgChno = null;
                currentTvgShift = null;
                isRadio = false;
                catchup = null;
                catchupDays = null;
                catchupSource = null;
            }
        }

        return channels;
    }

    extractAttributes(line) {
        const attributes = {};
        const pattern = /([a-zA-Z0-9-]+)="([^"]+)"/g;
        let match;

        while ((match = pattern.exec(line)) !== null) {
            attributes[match[1]] = match[2];
        }

        return attributes;
    }
}

