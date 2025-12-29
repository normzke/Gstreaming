package com.bingetv.app.utils;

@kotlin.Metadata(mv = {1, 9, 0}, k = 1, xi = 48, d1 = {"\u0000B\n\u0002\u0018\u0002\n\u0002\u0010\u0000\n\u0000\n\u0002\u0018\u0002\n\u0002\b\u0002\n\u0002\u0018\u0002\n\u0000\n\u0002\u0018\u0002\n\u0002\b\u0002\n\u0002\u0010\u0002\n\u0000\n\u0002\u0010\t\n\u0000\n\u0002\u0010\b\n\u0000\n\u0002\u0010\u000e\n\u0002\b\u0006\n\u0002\u0010\u000b\n\u0002\b\u0016\u0018\u0000 -2\u00020\u0001:\u0001-B\r\u0012\u0006\u0010\u0002\u001a\u00020\u0003\u00a2\u0006\u0002\u0010\u0004J\u0006\u0010\n\u001a\u00020\u000bJ\u0006\u0010\f\u001a\u00020\rJ\u0006\u0010\u000e\u001a\u00020\u000fJ\u0006\u0010\u0010\u001a\u00020\u0011J\b\u0010\u0012\u001a\u0004\u0018\u00010\u0011J\b\u0010\u0013\u001a\u0004\u0018\u00010\u0011J\b\u0010\u0014\u001a\u0004\u0018\u00010\u0011J\b\u0010\u0015\u001a\u0004\u0018\u00010\u0011J\b\u0010\u0016\u001a\u0004\u0018\u00010\u0011J\u0006\u0010\u0017\u001a\u00020\u0018J\u0006\u0010\u0019\u001a\u00020\u0018J\u0006\u0010\u001a\u001a\u00020\u0018J\u001e\u0010\u001b\u001a\u00020\u000b2\u0006\u0010\u001c\u001a\u00020\u00112\u0006\u0010\u001d\u001a\u00020\u00112\u0006\u0010\u001e\u001a\u00020\u0011J\u000e\u0010\u001f\u001a\u00020\u000b2\u0006\u0010 \u001a\u00020\u0011J\u000e\u0010!\u001a\u00020\u000b2\u0006\u0010\"\u001a\u00020\rJ\u000e\u0010#\u001a\u00020\u000b2\u0006\u0010$\u001a\u00020\u0018J\u0006\u0010%\u001a\u00020\u000bJ\u000e\u0010&\u001a\u00020\u000b2\u0006\u0010\'\u001a\u00020\u000fJ\u000e\u0010(\u001a\u00020\u000b2\u0006\u0010)\u001a\u00020\u0011J\u000e\u0010*\u001a\u00020\u000b2\u0006\u0010$\u001a\u00020\u0018J\u000e\u0010+\u001a\u00020\u000b2\u0006\u0010,\u001a\u00020\u0011R\u000e\u0010\u0005\u001a\u00020\u0006X\u0082\u0004\u00a2\u0006\u0002\n\u0000R\u000e\u0010\u0007\u001a\u00020\bX\u0082\u0004\u00a2\u0006\u0002\n\u0000R\u000e\u0010\t\u001a\u00020\u0006X\u0082\u0004\u00a2\u0006\u0002\n\u0000\u00a8\u0006."}, d2 = {"Lcom/bingetv/app/utils/PreferencesManager;", "", "context", "Landroid/content/Context;", "(Landroid/content/Context;)V", "encryptedPrefs", "Landroid/content/SharedPreferences;", "masterKey", "Landroidx/security/crypto/MasterKey;", "normalPrefs", "clearCredentials", "", "getActivePlaylistId", "", "getGridColumns", "", "getLogoSize", "", "getM3uUrl", "getParentalPin", "getPassword", "getServerUrl", "getUsername", "isAutoLoginEnabled", "", "isFirstLaunch", "isParentalControlEnabled", "saveCredentials", "serverUrl", "username", "password", "saveM3uUrl", "url", "setActivePlaylistId", "id", "setAutoLogin", "enabled", "setFirstLaunchComplete", "setGridColumns", "columns", "setLogoSize", "size", "setParentalControlEnabled", "setParentalPin", "pin", "Companion", "app_debug"})
public final class PreferencesManager {
    @org.jetbrains.annotations.NotNull
    private final androidx.security.crypto.MasterKey masterKey = null;
    @org.jetbrains.annotations.NotNull
    private final android.content.SharedPreferences encryptedPrefs = null;
    @org.jetbrains.annotations.NotNull
    private final android.content.SharedPreferences normalPrefs = null;
    @org.jetbrains.annotations.NotNull
    private static final java.lang.String KEY_SERVER_URL = "server_url";
    @org.jetbrains.annotations.NotNull
    private static final java.lang.String KEY_USERNAME = "username";
    @org.jetbrains.annotations.NotNull
    private static final java.lang.String KEY_PASSWORD = "password";
    @org.jetbrains.annotations.NotNull
    private static final java.lang.String KEY_M3U_URL = "m3u_url";
    @org.jetbrains.annotations.NotNull
    private static final java.lang.String KEY_AUTO_LOGIN = "auto_login";
    @org.jetbrains.annotations.NotNull
    private static final java.lang.String KEY_FIRST_LAUNCH = "first_launch";
    @org.jetbrains.annotations.NotNull
    private static final java.lang.String KEY_ACTIVE_PLAYLIST = "active_playlist";
    @org.jetbrains.annotations.NotNull
    private static final java.lang.String KEY_GRID_COLUMNS = "grid_columns";
    @org.jetbrains.annotations.NotNull
    private static final java.lang.String KEY_LOGO_SIZE = "logo_size";
    @org.jetbrains.annotations.NotNull
    private static final java.lang.String KEY_PARENTAL_CONTROL = "parental_control";
    @org.jetbrains.annotations.NotNull
    private static final java.lang.String KEY_PARENTAL_PIN = "parental_pin";
    @org.jetbrains.annotations.NotNull
    public static final com.bingetv.app.utils.PreferencesManager.Companion Companion = null;
    
    public PreferencesManager(@org.jetbrains.annotations.NotNull
    android.content.Context context) {
        super();
    }
    
    public final void saveCredentials(@org.jetbrains.annotations.NotNull
    java.lang.String serverUrl, @org.jetbrains.annotations.NotNull
    java.lang.String username, @org.jetbrains.annotations.NotNull
    java.lang.String password) {
    }
    
    @org.jetbrains.annotations.Nullable
    public final java.lang.String getServerUrl() {
        return null;
    }
    
    @org.jetbrains.annotations.Nullable
    public final java.lang.String getUsername() {
        return null;
    }
    
    @org.jetbrains.annotations.Nullable
    public final java.lang.String getPassword() {
        return null;
    }
    
    public final void clearCredentials() {
    }
    
    public final void saveM3uUrl(@org.jetbrains.annotations.NotNull
    java.lang.String url) {
    }
    
    @org.jetbrains.annotations.Nullable
    public final java.lang.String getM3uUrl() {
        return null;
    }
    
    public final void setAutoLogin(boolean enabled) {
    }
    
    public final boolean isAutoLoginEnabled() {
        return false;
    }
    
    public final boolean isFirstLaunch() {
        return false;
    }
    
    public final void setFirstLaunchComplete() {
    }
    
    public final void setActivePlaylistId(long id) {
    }
    
    public final long getActivePlaylistId() {
        return 0L;
    }
    
    public final void setGridColumns(int columns) {
    }
    
    public final int getGridColumns() {
        return 0;
    }
    
    public final void setLogoSize(@org.jetbrains.annotations.NotNull
    java.lang.String size) {
    }
    
    @org.jetbrains.annotations.NotNull
    public final java.lang.String getLogoSize() {
        return null;
    }
    
    public final void setParentalControlEnabled(boolean enabled) {
    }
    
    public final boolean isParentalControlEnabled() {
        return false;
    }
    
    public final void setParentalPin(@org.jetbrains.annotations.NotNull
    java.lang.String pin) {
    }
    
    @org.jetbrains.annotations.Nullable
    public final java.lang.String getParentalPin() {
        return null;
    }
    
    @kotlin.Metadata(mv = {1, 9, 0}, k = 1, xi = 48, d1 = {"\u0000\u0014\n\u0002\u0018\u0002\n\u0002\u0010\u0000\n\u0002\b\u0002\n\u0002\u0010\u000e\n\u0002\b\u000b\b\u0086\u0003\u0018\u00002\u00020\u0001B\u0007\b\u0002\u00a2\u0006\u0002\u0010\u0002R\u000e\u0010\u0003\u001a\u00020\u0004X\u0082T\u00a2\u0006\u0002\n\u0000R\u000e\u0010\u0005\u001a\u00020\u0004X\u0082T\u00a2\u0006\u0002\n\u0000R\u000e\u0010\u0006\u001a\u00020\u0004X\u0082T\u00a2\u0006\u0002\n\u0000R\u000e\u0010\u0007\u001a\u00020\u0004X\u0082T\u00a2\u0006\u0002\n\u0000R\u000e\u0010\b\u001a\u00020\u0004X\u0082T\u00a2\u0006\u0002\n\u0000R\u000e\u0010\t\u001a\u00020\u0004X\u0082T\u00a2\u0006\u0002\n\u0000R\u000e\u0010\n\u001a\u00020\u0004X\u0082T\u00a2\u0006\u0002\n\u0000R\u000e\u0010\u000b\u001a\u00020\u0004X\u0082T\u00a2\u0006\u0002\n\u0000R\u000e\u0010\f\u001a\u00020\u0004X\u0082T\u00a2\u0006\u0002\n\u0000R\u000e\u0010\r\u001a\u00020\u0004X\u0082T\u00a2\u0006\u0002\n\u0000R\u000e\u0010\u000e\u001a\u00020\u0004X\u0082T\u00a2\u0006\u0002\n\u0000\u00a8\u0006\u000f"}, d2 = {"Lcom/bingetv/app/utils/PreferencesManager$Companion;", "", "()V", "KEY_ACTIVE_PLAYLIST", "", "KEY_AUTO_LOGIN", "KEY_FIRST_LAUNCH", "KEY_GRID_COLUMNS", "KEY_LOGO_SIZE", "KEY_M3U_URL", "KEY_PARENTAL_CONTROL", "KEY_PARENTAL_PIN", "KEY_PASSWORD", "KEY_SERVER_URL", "KEY_USERNAME", "app_debug"})
    public static final class Companion {
        
        private Companion() {
            super();
        }
    }
}