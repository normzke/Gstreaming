package com.bingetv.app.utils

object Constants {
    
    // App
    const val APP_NAME = "BingeTV"
    const val APP_VERSION = "2.0.0"
    
    // Playlist Types
    const val PLAYLIST_TYPE_XTREAM = "xtream"
    const val PLAYLIST_TYPE_M3U = "m3u"
    
    // Grid Columns
    const val GRID_COLUMNS_MIN = 3
    const val GRID_COLUMNS_MAX = 8
    const val GRID_COLUMNS_DEFAULT = 5
    
    // Logo Sizes
    const val LOGO_SIZE_SMALL = "small"
    const val LOGO_SIZE_MEDIUM = "medium"
    const val LOGO_SIZE_LARGE = "large"
    
    // Cache
    const val CACHE_SIZE_MB = 100L
    const val IMAGE_CACHE_SIZE_MB = 50L
    
    // Timeouts
    const val NETWORK_TIMEOUT_SECONDS = 30L
    const val EPG_REFRESH_INTERVAL_HOURS = 6L
    
    // Intent Extras
    const val EXTRA_CHANNEL_ID = "channel_id"
    const val EXTRA_CHANNEL_NAME = "channel_name"
    const val EXTRA_CHANNEL_URL = "channel_url"
    const val EXTRA_CHANNEL_LOGO = "channel_logo"
    const val EXTRA_CATEGORY = "category"
    
    // Shared Preferences Keys
    const val PREF_THEME = "theme"
    const val PREF_SHOW_CHANNEL_NUMBERS = "show_channel_numbers"
    const val PREF_SHOW_NOW_PLAYING = "show_now_playing"
    const val PREF_AUTO_PLAY_NEXT = "auto_play_next"
    const val PREF_DEFAULT_QUALITY = "default_quality"
    
    // Themes
    const val THEME_DARK = "dark"
    const val THEME_LIGHT = "light"
    const val THEME_AUTO = "auto"
    
    // Quality Options
    const val QUALITY_AUTO = "auto"
    const val QUALITY_HD = "hd"
    const val QUALITY_SD = "sd"
    const val QUALITY_LOW = "low"
    
    // Parental Control
    const val PIN_LENGTH = 4
    const val MAX_PIN_ATTEMPTS = 3
    
    // EPG
    const val EPG_DAYS_AHEAD = 7
    const val EPG_DAYS_BEHIND = 1
    
    // Player
    const val PLAYER_BUFFER_SIZE_MS = 50000
    const val PLAYER_MIN_BUFFER_MS = 15000
    const val PLAYER_MAX_BUFFER_MS = 50000
    
    // Animation Durations
    const val ANIM_DURATION_SHORT = 200L
    const val ANIM_DURATION_MEDIUM = 300L
    const val ANIM_DURATION_LONG = 500L
    
    // Splash Screen
    const val SPLASH_DELAY_MS = 2000L
    
    // Search
    const val SEARCH_DEBOUNCE_MS = 300L
    const val SEARCH_MIN_CHARS = 2
}
