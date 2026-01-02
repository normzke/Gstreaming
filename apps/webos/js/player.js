// BingeTV - Enhanced Player for Tizen

class PlayerManager {
    static currentChannel = null;
    static allChannels = [];
    static currentIndex = 0;
    static videoPlayer = null;
    static overlayTimeout = null;

    // Initialize player
    static init() {
        this.videoPlayer = document.getElementById('videoPlayer');
        this.loadChannelData();
        this.setupEventListeners();
        this.startPlayback();
    }

    // Load channel data from URL params
    static loadChannelData() {
        const params = new URLSearchParams(window.location.search);
        const channelId = params.get('id');

        // Load all channels from storage
        this.allChannels = StorageManager.getChannels();

        // Find current channel
        if (channelId) {
            this.currentIndex = this.allChannels.findIndex(ch => ch.id === channelId);
            if (this.currentIndex === -1) this.currentIndex = 0;
        }

        this.currentChannel = this.allChannels[this.currentIndex];
    }

    // Setup event listeners
    static setupEventListeners() {
        // Video events
        this.videoPlayer.addEventListener('loadstart', () => this.showLoading(true));
        this.videoPlayer.addEventListener('canplay', () => this.showLoading(false));
        this.videoPlayer.addEventListener('playing', () => this.showLoading(false));
        this.videoPlayer.addEventListener('error', (e) => this.handleError(e));
        this.videoPlayer.addEventListener('stalled', () => this.showLoading(true));
        this.videoPlayer.addEventListener('waiting', () => this.showLoading(true));

        // Keyboard events
        document.addEventListener('keydown', (e) => this.handleKeyPress(e));

        // Click to toggle overlay
        this.videoPlayer.addEventListener('click', () => this.toggleOverlay());
    }

    // Start playback
    static startPlayback() {
        if (!this.currentChannel) {
            this.showError('No channel selected');
            return;
        }

        this.showLoading(true);
        this.updateOverlay();

        // Set video source
        this.videoPlayer.src = this.currentChannel.url;
        this.videoPlayer.play().catch(err => {
            console.error('Playback error:', err);
            this.handleError(err);
        });

        // Show overlay briefly
        this.showOverlay(true);
        this.autoHideOverlay();
    }

    // Handle keyboard input
    static handleKeyPress(e) {
        switch (e.key) {
            case 'ArrowUp':
                e.preventDefault();
                if (document.getElementById('trackMenu').style.display === 'flex') {
                    this.navigateTrackMenu('up');
                } else {
                    this.switchChannel('prev');
                }
                break;
            case 'ArrowDown':
                e.preventDefault();
                if (document.getElementById('trackMenu').style.display === 'flex') {
                    this.navigateTrackMenu('down');
                } else {
                    this.switchChannel('next');
                }
                break;
            case 'ArrowLeft':
            case 'ArrowRight':
                // For future seek support if needed
                break;
            case 'Enter':
            case 'OK':
                e.preventDefault();
                if (document.getElementById('trackMenu').style.display === 'flex') {
                    this.selectCurrentTrack();
                } else {
                    this.toggleControls();
                }
                break;
            case 'Menu':
            case 'Settings':
                e.preventDefault();
                this.showTrackMenu();
                break;
            case 'Back':
            case 'Escape':
                e.preventDefault();
                if (document.getElementById('trackMenu').style.display === 'flex') {
                    this.hideTrackMenu();
                } else if (document.getElementById('channelOverlay').style.display === 'flex') {
                    this.showOverlay(false);
                } else {
                    this.exitPlayer();
                }
                break;
        }
    }

    static toggleControls() {
        const overlay = document.getElementById('channelOverlay');
        const isVisible = overlay.style.display === 'flex';

        // In Android, Enter just shows the overlay/controls if they are hidden
        if (!isVisible) {
            this.showOverlay(true);
            this.autoHideOverlay();
        } else {
            // If already visible, maybe toggle play/pause?
            if (this.videoPlayer.paused) {
                this.videoPlayer.play();
            } else {
                this.videoPlayer.pause();
            }
        }
    }

    static showTrackMenu() {
        // Implementation for selecting audio/subtitle tracks
        // For standard HTML5 video, we can check audioTracks and textTracks
        const menu = document.getElementById('trackMenu');
        menu.style.display = 'flex';
        this.updateTrackMenu();
    }

    static hideTrackMenu() {
        document.getElementById('trackMenu').style.display = 'none';
    }

    static updateTrackMenu() {
        const list = document.getElementById('trackList');
        list.innerHTML = '';

        // Add Audio Tracks
        const audioHeader = document.createElement('h4');
        audioHeader.textContent = 'Audio Tracks';
        list.appendChild(audioHeader);

        if (this.videoPlayer.audioTracks) {
            for (let i = 0; i < this.videoPlayer.audioTracks.length; i++) {
                const track = this.videoPlayer.audioTracks[i];
                const btn = this.createTrackBtn(track.label || `Audio ${i + 1}`, 'audio', i, track.enabled);
                list.appendChild(btn);
            }
        } else {
            const p = document.createElement('p');
            p.textContent = 'Default Audio';
            list.appendChild(p);
        }

        // Add Subtitles
        const subHeader = document.createElement('h4');
        subHeader.textContent = 'Subtitles';
        list.appendChild(subHeader);

        if (this.videoPlayer.textTracks) {
            for (let i = 0; i < this.videoPlayer.textTracks.length; i++) {
                const track = this.videoPlayer.textTracks[i];
                if (track.kind === 'subtitles' || track.kind === 'captions') {
                    const btn = this.createTrackBtn(track.label || `Subtitle ${i + 1}`, 'text', i, track.mode === 'showing');
                    list.appendChild(btn);
                }
            }
        }
    }

    static createTrackBtn(label, type, index, isActive) {
        const div = document.createElement('div');
        div.className = `track-item ${isActive ? 'active' : ''}`;
        div.textContent = label;
        div.setAttribute('tabindex', '0');
        div.onclick = () => this.setTrack(type, index);
        return div;
    }

    static setTrack(type, index) {
        if (type === 'audio' && this.videoPlayer.audioTracks) {
            for (let i = 0; i < this.videoPlayer.audioTracks.length; i++) {
                this.videoPlayer.audioTracks[i].enabled = (i === index);
            }
        } else if (type === 'text' && this.videoPlayer.textTracks) {
            for (let i = 0; i < this.videoPlayer.textTracks.length; i++) {
                this.videoPlayer.textTracks[i].mode = (i === index ? 'showing' : 'disabled');
            }
        }
        this.hideTrackMenu();
    }

    static navigateTrackMenu(direction) {
        const items = document.querySelectorAll('.track-item');
        if (items.length === 0) return;

        let currentIndex = Array.from(items).indexOf(document.activeElement);
        if (direction === 'up') {
            currentIndex = (currentIndex <= 0) ? items.length - 1 : currentIndex - 1;
        } else {
            currentIndex = (currentIndex >= items.length - 1) ? 0 : currentIndex + 1;
        }
        items[currentIndex].focus();
    }

    static selectCurrentTrack() {
        if (document.activeElement && document.activeElement.classList.contains('track-item')) {
            document.activeElement.click();
        }
    }

    // Switch channel
    static switchChannel(direction) {
        if (direction === 'next') {
            this.currentIndex = (this.currentIndex + 1) % this.allChannels.length;
        } else {
            this.currentIndex = (this.currentIndex - 1 + this.allChannels.length) % this.allChannels.length;
        }

        this.currentChannel = this.allChannels[this.currentIndex];
        this.startPlayback();
    }

    // Toggle overlay
    static toggleOverlay() {
        const overlay = document.getElementById('channelOverlay');
        const isVisible = overlay.style.display === 'flex';

        if (isVisible) {
            this.showOverlay(false);
        } else {
            this.showOverlay(true);
            this.autoHideOverlay();
        }
    }

    // Show/hide overlay
    static showOverlay(show) {
        const overlay = document.getElementById('channelOverlay');
        overlay.style.display = show ? 'flex' : 'none';

        if (this.overlayTimeout) {
            clearTimeout(this.overlayTimeout);
            this.overlayTimeout = null;
        }
    }

    // Auto-hide overlay after 5 seconds
    static autoHideOverlay() {
        this.overlayTimeout = setTimeout(() => {
            this.showOverlay(false);
        }, 5000);
    }

    // Update overlay content
    static updateOverlay() {
        if (!this.currentChannel) return;

        document.getElementById('overlayLogo').src = this.currentChannel.logo || 'icon.png';
        document.getElementById('overlayName').textContent = this.currentChannel.name || 'Unknown Channel';
        document.getElementById('overlayCategory').textContent = this.currentChannel.group || 'Uncategorized';
        document.getElementById('overlayNumber').textContent = `Channel ${this.currentIndex + 1} of ${this.allChannels.length}`;
    }

    // Show/hide loading
    static showLoading(show) {
        const loading = document.getElementById('loadingIndicator');
        loading.style.display = show ? 'flex' : 'none';
    }

    // Handle errors
    static handleError(error) {
        console.error('Player error:', error);
        this.showLoading(false);

        let errorMessage = 'Unable to play this channel';

        if (error && error.target && error.target.error) {
            const mediaError = error.target.error;
            switch (mediaError.code) {
                case mediaError.MEDIA_ERR_ABORTED:
                    errorMessage = 'Playback aborted';
                    break;
                case mediaError.MEDIA_ERR_NETWORK:
                    errorMessage = 'Network error - check your connection';
                    break;
                case mediaError.MEDIA_ERR_DECODE:
                    errorMessage = 'Video format not supported';
                    break;
                case mediaError.MEDIA_ERR_SRC_NOT_SUPPORTED:
                    errorMessage = 'Source not supported or not found';
                    break;
            }
        }

        this.showError(errorMessage);
    }

    // Show error
    static showError(message) {
        document.getElementById('errorText').textContent = message;
        document.getElementById('errorMessage').style.display = 'flex';
    }

    // Hide error
    static hideError() {
        document.getElementById('errorMessage').style.display = 'none';
    }

    // Retry playback
    static retry() {
        this.hideError();
        this.startPlayback();
    }

    // Exit player
    static exit() {
        if (this.videoPlayer) {
            this.videoPlayer.pause();
            this.videoPlayer.src = '';
        }
        window.location.href = 'main.html';
    }
}

// Global functions
function retryPlayback() {
    PlayerManager.retry();
}

function exitPlayer() {
    PlayerManager.exit();
}

// Initialize on load
window.addEventListener('DOMContentLoaded', () => {
    PlayerManager.init();
});

// Cleanup on unload
window.addEventListener('beforeunload', () => {
    if (PlayerManager.videoPlayer) {
        PlayerManager.videoPlayer.pause();
        PlayerManager.videoPlayer.src = '';
    }
});
