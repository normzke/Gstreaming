package com.bingetv.app.utils

import android.content.Context
import android.content.SharedPreferences
import androidx.security.crypto.EncryptedSharedPreferences
import androidx.security.crypto.MasterKey

class PreferencesManager(context: Context) {
    
    private val masterKey = MasterKey.Builder(context)
        .setKeyScheme(MasterKey.KeyScheme.AES256_GCM)
        .build()
    
    private val encryptedPrefs: SharedPreferences = EncryptedSharedPreferences.create(
        context,
        "bingetv_secure_prefs",
        masterKey,
        EncryptedSharedPreferences.PrefKeyEncryptionScheme.AES256_SIV,
        EncryptedSharedPreferences.PrefValueEncryptionScheme.AES256_GCM
    )
    
    private val normalPrefs: SharedPreferences = context.getSharedPreferences(
        "bingetv_prefs",
        Context.MODE_PRIVATE
    )
    
    // Secure credentials storage
    fun saveCredentials(serverUrl: String, username: String, password: String) {
        encryptedPrefs.edit().apply {
            putString(KEY_SERVER_URL, serverUrl)
            putString(KEY_USERNAME, username)
            putString(KEY_PASSWORD, password)
            apply()
        }
    }
    
    fun getServerUrl(): String? = encryptedPrefs.getString(KEY_SERVER_URL, null)
    fun getUsername(): String? = encryptedPrefs.getString(KEY_USERNAME, null)
    fun getPassword(): String? = encryptedPrefs.getString(KEY_PASSWORD, null)
    
    fun clearCredentials() {
        encryptedPrefs.edit().clear().apply()
    }
    
    // M3U URL
    fun saveM3uUrl(url: String) {
        encryptedPrefs.edit().putString(KEY_M3U_URL, url).apply()
    }
    
    fun getM3uUrl(): String? = encryptedPrefs.getString(KEY_M3U_URL, null)
    
    // Auto-login
    fun setAutoLogin(enabled: Boolean) {
        normalPrefs.edit().putBoolean(KEY_AUTO_LOGIN, enabled).apply()
    }
    
    fun isAutoLoginEnabled(): Boolean = normalPrefs.getBoolean(KEY_AUTO_LOGIN, false)
    
    // First launch
    fun isFirstLaunch(): Boolean = normalPrefs.getBoolean(KEY_FIRST_LAUNCH, true)
    
    fun setFirstLaunchComplete() {
        normalPrefs.edit().putBoolean(KEY_FIRST_LAUNCH, false).apply()
    }
    
    // Active playlist ID
    fun setActivePlaylistId(id: Long) {
        normalPrefs.edit().putLong(KEY_ACTIVE_PLAYLIST, id).apply()
    }
    
    fun getActivePlaylistId(): Long = normalPrefs.getLong(KEY_ACTIVE_PLAYLIST, -1)
    
    fun setLastLoadedPlaylistId(id: Long) {
        normalPrefs.edit().putLong(KEY_LAST_LOADED_PLAYLIST, id).apply()
    }
    
    fun getLastLoadedPlaylistId(): Long = normalPrefs.getLong(KEY_LAST_LOADED_PLAYLIST, -1)
    
    // Grid settings
    fun setGridColumns(columns: Int) {
        normalPrefs.edit().putInt(KEY_GRID_COLUMNS, columns).apply()
    }
    
    fun getGridColumns(): Int = normalPrefs.getInt(KEY_GRID_COLUMNS, 5)
    
    // Logo size
    fun setLogoSize(size: String) {
        normalPrefs.edit().putString(KEY_LOGO_SIZE, size).apply()
    }
    
    fun getLogoSize(): String = normalPrefs.getString(KEY_LOGO_SIZE, "medium") ?: "medium"
    
    // Parental control
    fun setParentalControlEnabled(enabled: Boolean) {
        normalPrefs.edit().putBoolean(KEY_PARENTAL_CONTROL, enabled).apply()
    }
    
    fun isParentalControlEnabled(): Boolean = normalPrefs.getBoolean(KEY_PARENTAL_CONTROL, false)
    
    fun setParentalPin(pin: String) {
        encryptedPrefs.edit().putString(KEY_PARENTAL_PIN, pin).apply()
    }
    
    fun getParentalPin(): String? = encryptedPrefs.getString(KEY_PARENTAL_PIN, null)
    

    
    // General
    fun setAutoStartBoot(enabled: Boolean) = normalPrefs.edit().putBoolean(KEY_AUTO_START_BOOT, enabled).apply()
    fun isAutoStartBoot(): Boolean = normalPrefs.getBoolean(KEY_AUTO_START_BOOT, false)
    
    fun setTurnOnLastChannel(enabled: Boolean) = normalPrefs.edit().putBoolean(KEY_TURN_ON_LAST_CHANNEL, enabled).apply()
    fun isTurnOnLastChannel(): Boolean = normalPrefs.getBoolean(KEY_TURN_ON_LAST_CHANNEL, false)
    
    fun setPipOnHome(enabled: Boolean) = normalPrefs.edit().putBoolean(KEY_PIP_ON_HOME, enabled).apply()
    fun isPipOnHome(): Boolean = normalPrefs.getBoolean(KEY_PIP_ON_HOME, false)
    
    fun setConfirmExit(enabled: Boolean) = normalPrefs.edit().putBoolean(KEY_CONFIRM_EXIT, enabled).apply()
    fun isConfirmExit(): Boolean = normalPrefs.getBoolean(KEY_CONFIRM_EXIT, true)
    
    fun setUserAgent(ua: String) = normalPrefs.edit().putString(KEY_USER_AGENT, ua).apply()
    fun getUserAgent(): String = normalPrefs.getString(KEY_USER_AGENT, "BingeTV/1.0") ?: "BingeTV/1.0"
    
    // Playback
    fun setBufferSize(size: String) = normalPrefs.edit().putString(KEY_BUFFER_SIZE, size).apply()
    fun getBufferSize(): String = normalPrefs.getString(KEY_BUFFER_SIZE, "medium") ?: "medium"
    
    fun setAudioDecoder(type: String) = normalPrefs.edit().putString(KEY_AUDIO_DECODER, type).apply()
    fun getAudioDecoder(): String = normalPrefs.getString(KEY_AUDIO_DECODER, "hardware") ?: "hardware"
    
    fun setVideoDecoder(type: String) = normalPrefs.edit().putString(KEY_VIDEO_DECODER, type).apply()
    fun getVideoDecoder(): String = normalPrefs.getString(KEY_VIDEO_DECODER, "hardware") ?: "hardware"
    
    fun setAfrEnabled(enabled: Boolean) = normalPrefs.edit().putBoolean(KEY_AFR, enabled).apply()
    fun isAfrEnabled(): Boolean = normalPrefs.getBoolean(KEY_AFR, false)
    
    // EPG
    fun setEpgDays(days: Int) = normalPrefs.edit().putInt(KEY_EPG_DAYS, days).apply()
    fun getEpgDays(): Int = normalPrefs.getInt(KEY_EPG_DAYS, 2)
    
    fun setStoreDescriptions(enabled: Boolean) = normalPrefs.edit().putBoolean(KEY_STORE_DESCRIPTIONS, enabled).apply()
    fun isStoreDescriptions(): Boolean = normalPrefs.getBoolean(KEY_STORE_DESCRIPTIONS, true)
    
    fun setEpgUpdateOnStart(enabled: Boolean) = normalPrefs.edit().putBoolean(KEY_EPG_UPDATE_START, enabled).apply()
    fun isEpgUpdateOnStart(): Boolean = normalPrefs.getBoolean(KEY_EPG_UPDATE_START, true)
    
    fun setEpgUpdateOnPlaylistChange(enabled: Boolean) = normalPrefs.edit().putBoolean(KEY_EPG_UPDATE_PLAYLIST_CHANGE, enabled).apply()
    fun isEpgUpdateOnPlaylistChange(): Boolean = normalPrefs.getBoolean(KEY_EPG_UPDATE_PLAYLIST_CHANGE, true)

    // Last Channel Storage
    fun setLastChannelId(id: String) = normalPrefs.edit().putString(KEY_LAST_CHANNEL_ID, id).apply()
    fun getLastChannelId(): String? = normalPrefs.getString(KEY_LAST_CHANNEL_ID, null)
    
    // Remote
    fun setRemoteLeftRightAction(action: String) = normalPrefs.edit().putString(KEY_REMOTE_LEFT_RIGHT, action).apply()
    fun getRemoteLeftRightAction(): String = normalPrefs.getString(KEY_REMOTE_LEFT_RIGHT, "seek") ?: "seek"
    
    fun setRemoteUpDownAction(action: String) = normalPrefs.edit().putString(KEY_REMOTE_UP_DOWN, action).apply()
    fun getRemoteUpDownAction(): String = normalPrefs.getString(KEY_REMOTE_UP_DOWN, "channel") ?: "channel"
    
    // Appearance Extra
    fun setUiTransparency(percent: Int) = normalPrefs.edit().putInt(KEY_UI_TRANSPARENCY, percent).apply()
    fun getUiTransparency(): Int = normalPrefs.getInt(KEY_UI_TRANSPARENCY, 0)
    
    fun setShowClock(enabled: Boolean) = normalPrefs.edit().putBoolean(KEY_SHOW_CLOCK, enabled).apply()
    fun isShowClock(): Boolean = normalPrefs.getBoolean(KEY_SHOW_CLOCK, true)

    companion object {
        // ... (Existing keys)
        private const val KEY_SERVER_URL = "server_url"
        private const val KEY_USERNAME = "username"
        private const val KEY_PASSWORD = "password"
        private const val KEY_M3U_URL = "m3u_url"
        private const val KEY_AUTO_LOGIN = "auto_login"
        private const val KEY_FIRST_LAUNCH = "first_launch"
        private const val KEY_ACTIVE_PLAYLIST = "active_playlist"
        private const val KEY_LAST_LOADED_PLAYLIST = "last_loaded_playlist"
        private const val KEY_GRID_COLUMNS = "grid_columns"
        private const val KEY_LOGO_SIZE = "logo_size"
        private const val KEY_PARENTAL_CONTROL = "parental_control"
        private const val KEY_PARENTAL_PIN = "parental_pin"
        
        // General
        private const val KEY_AUTO_START_BOOT = "auto_start_boot"
        private const val KEY_TURN_ON_LAST_CHANNEL = "turn_on_last_channel"
        private const val KEY_PIP_ON_HOME = "pip_on_home"
        private const val KEY_CONFIRM_EXIT = "confirm_exit"
        private const val KEY_USER_AGENT = "user_agent"
        
        // Playback
        private const val KEY_BUFFER_SIZE = "buffer_size"
        private const val KEY_AUDIO_DECODER = "audio_decoder"
        private const val KEY_VIDEO_DECODER = "video_decoder"
        private const val KEY_AFR = "afr"
        
        // EPG
        private const val KEY_EPG_DAYS = "epg_days"
        private const val KEY_STORE_DESCRIPTIONS = "store_descriptions"
        private const val KEY_LAST_CHANNEL_ID = "last_channel_id"
        private const val KEY_EPG_UPDATE_START = "epg_update_start"
        private const val KEY_EPG_UPDATE_PLAYLIST_CHANGE = "epg_update_change"
        
        // Remote
        private const val KEY_REMOTE_LEFT_RIGHT = "remote_left_right"
        private const val KEY_REMOTE_UP_DOWN = "remote_up_down"
        
        // Appearance
        private const val KEY_UI_TRANSPARENCY = "ui_transparency"
        private const val KEY_SHOW_CLOCK = "show_clock"
    }
}
