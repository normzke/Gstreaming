// BingeTV - Series Detail Screen for Tizen/WebOS
// Port of Android SeriesDetailActivity.kt

class SeriesDetailManager {
    constructor() {
        this.seriesId = null;
        this.seriesName = '';
        this.seriesPoster = null;
        this.allEpisodes = {};
        this.sortedSeasons = [];
        this.currentSeason = '';
        this.credentials = null;
    }

    async init() {
        // Get URL parameters
        const params = new URLSearchParams(window.location.search);
        this.seriesId = parseInt(params.get('id'));
        this.seriesName = params.get('name') || 'Unknown Series';
        this.seriesPoster = params.get('poster');

        if (!this.seriesId) {
            this.showError('Invalid Series ID');
            return;
        }

        // Get credentials
        this.credentials = StorageManager.getCredentials();
        if (!this.credentials || this.credentials.type !== 'xtream') {
            this.showError('Xtream credentials not found');
            return;
        }

        // Setup UI
        document.getElementById('seriesTitle').textContent = this.seriesName;
        if (this.seriesPoster) {
            document.getElementById('seriesPoster').src = this.seriesPoster;
        }

        // Load series details
        await this.loadSeriesDetails();
    }

    async loadSeriesDetails() {
        this.showLoading(true);

        try {
            const { server, username, password } = this.credentials;

            // Attempt 1: Standard series_id parameter
            let response = await this.fetchSeriesInfo(server, username, password, 'series_id', this.seriesId);
            let jsonString = await response.text();
            console.log(`Attempt 1 (series_id=${this.seriesId}):`, jsonString.substring(0, 500));

            // Attempt 2: Fallback to 'id' parameter
            if (jsonString === '[]' || jsonString.includes('"error"') || jsonString.includes('API error')) {
                console.warn('Trying fallback id parameter');
                response = await this.fetchSeriesInfo(server, username, password, 'id', this.seriesId);
                jsonString = await response.text();
                console.log(`Attempt 2 (id=${this.seriesId}):`, jsonString.substring(0, 500));
            }

            // Attempt 3: Fallback to 'stream_id' parameter
            if (jsonString === '[]' || jsonString.includes('"error"') || jsonString.includes('API error')) {
                console.warn('Trying fallback stream_id parameter');
                response = await this.fetchSeriesInfo(server, username, password, 'stream_id', this.seriesId);
                jsonString = await response.text();
                console.log(`Attempt 3 (stream_id=${this.seriesId}):`, jsonString.substring(0, 500));
            }

            // Parse response
            if (jsonString && jsonString !== '[]' && jsonString !== '{"info":[]}') {
                const data = JSON.parse(jsonString);
                await this.processSeriesData(data);
            } else {
                // VOD Fallback
                console.warn('Series info empty, trying VOD fallback');
                await this.loadVODFallback(server, username, password);
            }

        } catch (error) {
            console.error('Error loading series details:', error);
            this.showError('Error loading series details');
        } finally {
            this.showLoading(false);
        }
    }

    async fetchSeriesInfo(server, username, password, paramName, paramValue) {
        const url = `${server}/player_api.php?username=${username}&password=${password}&action=get_series_info&${paramName}=${paramValue}`;
        return await fetch(url);
    }

    async processSeriesData(data) {
        let seriesInfo = null;
        let episodesMap = null;

        // Handle both object and array responses
        if (data.info) {
            seriesInfo = data.info;
        } else if (Array.isArray(data) && data.length > 0 && data[0].info) {
            seriesInfo = data[0].info;
        }

        if (data.episodes) {
            episodesMap = data.episodes;
        } else if (Array.isArray(data) && data.length > 0 && data[0].episodes) {
            episodesMap = data[0].episodes;
        }

        // Update UI with series info
        if (seriesInfo) {
            document.getElementById('seriesTitle').textContent = TextUtils.decodeText(seriesInfo.name) || this.seriesName;
            document.getElementById('seriesPlot').textContent = TextUtils.decodeText(seriesInfo.plot) || 'No plot available';
            document.getElementById('seriesGenre').textContent = TextUtils.decodeText(seriesInfo.genre) || 'N/A';
            document.getElementById('seriesRating').textContent = `IMDB: ${TextUtils.decodeText(seriesInfo.rating) || 'N/A'}`;
            document.getElementById('seriesCast').innerHTML = `<strong>Cast:</strong> ${TextUtils.decodeText(seriesInfo.cast) || 'N/A'}`;
            document.getElementById('seriesDirector').innerHTML = `<strong>Director:</strong> ${TextUtils.decodeText(seriesInfo.director) || 'N/A'}`;
            document.getElementById('seriesReleaseDate').textContent = TextUtils.decodeText(seriesInfo.releaseDate) || '';

            if (seriesInfo.cover) {
                document.getElementById('seriesPoster').src = seriesInfo.cover;
            }
        }

        // Process episodes
        if (episodesMap) {
            this.allEpisodes = episodesMap;
            this.sortedSeasons = Object.keys(episodesMap).sort((a, b) => {
                const numA = parseInt(a.match(/\d+/)?.[0]) || 999;
                const numB = parseInt(b.match(/\d+/)?.[0]) || 999;
                return numA - numB;
            });

            if (this.sortedSeasons.length > 0) {
                this.renderSeasons();
                this.selectSeason(this.sortedSeasons[0]);
                // Focus first season button
                setTimeout(() => {
                    const firstSeason = document.querySelector('.season-btn');
                    if (firstSeason) firstSeason.focus();
                }, 100);
            }
        } else {
            this.showError('No episodes found');
        }
    }

    async loadVODFallback(server, username, password) {
        try {
            const url = `${server}/player_api.php?username=${username}&password=${password}&action=get_vod_info&vod_id=${this.seriesId}`;
            const response = await fetch(url);
            const data = await response.json();

            if (data.movie_data) {
                const movie = data.movie_data;
                document.getElementById('seriesTitle').textContent = TextUtils.decodeText(movie.name) || this.seriesName;
                document.getElementById('seriesPlot').textContent = TextUtils.decodeText(movie.plot || movie.description) || 'No plot available';
                document.getElementById('seriesGenre').textContent = 'VOD';
                document.getElementById('seriesRating').textContent = `IMDB: ${TextUtils.decodeText(movie.rating) || 'N/A'}`;
                document.getElementById('seriesCast').innerHTML = `<strong>Cast:</strong> ${TextUtils.decodeText(movie.cast) || 'N/A'}`;
                document.getElementById('seriesDirector').innerHTML = `<strong>Director:</strong> ${TextUtils.decodeText(movie.director) || 'N/A'}`;
                document.getElementById('seriesReleaseDate').textContent = TextUtils.decodeText(movie.year) || '';

                if (movie.coverBig) {
                    document.getElementById('seriesPoster').src = movie.coverBig;
                }

                // Create fake "Movie" episode
                const extension = data.info?.container_extension || 'mp4';
                this.allEpisodes = {
                    'Movie': [{
                        id: this.seriesId.toString(),
                        title: 'Watch Movie',
                        extension: extension,
                        season: 1,
                        episode_num: 1
                    }]
                };
                this.sortedSeasons = ['Movie'];
                this.renderSeasons();
                this.selectSeason('Movie');
            } else {
                // Minimal fallback for raw files
                this.allEpisodes = {
                    'Play': [{
                        id: this.seriesId.toString(),
                        title: 'Play',
                        extension: 'mp4',
                        season: 1,
                        episode_num: 1
                    }]
                };
                this.sortedSeasons = ['Play'];
                this.renderSeasons();
                this.selectSeason('Play');
            }
        } catch (error) {
            console.error('VOD fallback failed:', error);
            this.showError('Error loading content');
        }
    }

    renderSeasons() {
        const seasonList = document.getElementById('seasonList');
        seasonList.innerHTML = '';

        this.sortedSeasons.forEach((season, index) => {
            const btn = document.createElement('button');
            btn.className = 'season-btn';
            btn.textContent = `Season ${season}`;
            btn.setAttribute('tabindex', '0');
            btn.onclick = () => this.selectSeason(season);
            btn.onfocus = () => btn.classList.add('focused');
            btn.onblur = () => btn.classList.remove('focused');
            seasonList.appendChild(btn);
        });
    }

    selectSeason(season) {
        this.currentSeason = season;

        // Update season button states
        document.querySelectorAll('.season-btn').forEach(btn => {
            btn.classList.remove('selected');
            if (btn.textContent === `Season ${season}`) {
                btn.classList.add('selected');
            }
        });

        // Render episodes
        const episodes = this.allEpisodes[season] || [];
        this.renderEpisodes(episodes);

        document.getElementById('episodeSectionTitle').textContent = `Season ${season} Episodes`;
    }

    renderEpisodes(episodes) {
        const episodeList = document.getElementById('episodeList');
        episodeList.innerHTML = '';

        episodes.forEach((episode, index) => {
            const card = document.createElement('div');
            card.className = 'episode-card';
            card.setAttribute('tabindex', '0');

            card.innerHTML = `
                <div class="episode-number">E${episode.episode_num || (index + 1)}</div>
                <div class="episode-title">${TextUtils.decodeText(episode.title)}</div>
            `;

            card.onclick = () => this.playEpisode(episode);
            card.onfocus = () => {
                card.style.transform = 'scale(1.05)';
                card.style.borderColor = '#00A8FF';
            };
            card.onblur = () => {
                card.style.transform = 'scale(1)';
                card.style.borderColor = '#334155';
            };

            episodeList.appendChild(card);
        });
    }

    playEpisode(episode) {
        const { server, username, password } = this.credentials;
        const extension = episode.extension || 'mkv';
        const url = `${server}/series/${username}/${password}/${episode.id}.${extension}`;

        // Navigate to player
        const episodeName = `${this.seriesName} - S${this.currentSeason}E${episode.episode_num}`;
        window.location.href = `player.html?id=${episode.id}&name=${encodeURIComponent(episodeName)}&url=${encodeURIComponent(url)}`;
    }

    showLoading(show) {
        document.getElementById('loadingIndicator').style.display = show ? 'flex' : 'none';
    }

    showError(message) {
        document.getElementById('errorText').textContent = message;
        document.getElementById('errorMessage').style.display = 'flex';
    }
}

// Initialize on load
window.addEventListener('DOMContentLoaded', () => {
    const manager = new SeriesDetailManager();
    manager.init();
});

// Back button handling
window.addEventListener('keydown', (e) => {
    if (e.key === 'Back' || e.key === 'Escape') {
        history.back();
    }
});
