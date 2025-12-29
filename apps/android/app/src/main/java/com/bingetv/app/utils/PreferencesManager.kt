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
    
    companion object {
        private const val KEY_SERVER_URL = "server_url"
        private const val KEY_USERNAME = "username"
        private const val KEY_PASSWORD = "password"
        private const val KEY_M3U_URL = "m3u_url"
        private const val KEY_AUTO_LOGIN = "auto_login"
        private const val KEY_FIRST_LAUNCH = "first_launch"
        private const val KEY_ACTIVE_PLAYLIST = "active_playlist"
        private const val KEY_GRID_COLUMNS = "grid_columns"
        private const val KEY_LOGO_SIZE = "logo_size"
        private const val KEY_PARENTAL_CONTROL = "parental_control"
        private const val KEY_PARENTAL_PIN = "parental_pin"
    }
}
