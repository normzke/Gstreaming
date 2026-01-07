package com.bingetv.app.ui.login;

@kotlin.Metadata(mv = {1, 9, 0}, k = 1, xi = 48, d1 = {"\u0000l\n\u0002\u0018\u0002\n\u0002\u0018\u0002\n\u0002\b\u0002\n\u0002\u0018\u0002\n\u0000\n\u0002\u0018\u0002\n\u0000\n\u0002\u0018\u0002\n\u0000\n\u0002\u0018\u0002\n\u0000\n\u0002\u0018\u0002\n\u0002\b\u0002\n\u0002\u0018\u0002\n\u0000\n\u0002\u0018\u0002\n\u0000\n\u0002\u0018\u0002\n\u0000\n\u0002\u0018\u0002\n\u0002\b\u0002\n\u0002\u0018\u0002\n\u0002\b\u0004\n\u0002\u0010\u0002\n\u0002\b\u0003\n\u0002\u0010\u000e\n\u0002\b\u0004\n\u0002\u0018\u0002\n\u0002\b\u0005\n\u0002\u0010\u000b\n\u0000\u0018\u00002\u00020\u0001B\u0005\u00a2\u0006\u0002\u0010\u0002J\b\u0010\u001c\u001a\u00020\u001dH\u0002J\b\u0010\u001e\u001a\u00020\u001dH\u0002J\u0010\u0010\u001f\u001a\u00020\u001d2\u0006\u0010 \u001a\u00020!H\u0002J\b\u0010\"\u001a\u00020\u001dH\u0002J\b\u0010#\u001a\u00020\u001dH\u0002J\u0012\u0010$\u001a\u00020\u001d2\b\u0010%\u001a\u0004\u0018\u00010&H\u0014J\b\u0010\'\u001a\u00020\u001dH\u0002J\u0010\u0010(\u001a\u00020\u001d2\u0006\u0010)\u001a\u00020!H\u0002J\u0010\u0010*\u001a\u00020\u001d2\u0006\u0010+\u001a\u00020,H\u0002R\u000e\u0010\u0003\u001a\u00020\u0004X\u0082.\u00a2\u0006\u0002\n\u0000R\u000e\u0010\u0005\u001a\u00020\u0006X\u0082.\u00a2\u0006\u0002\n\u0000R\u000e\u0010\u0007\u001a\u00020\bX\u0082.\u00a2\u0006\u0002\n\u0000R\u000e\u0010\t\u001a\u00020\nX\u0082.\u00a2\u0006\u0002\n\u0000R\u000e\u0010\u000b\u001a\u00020\fX\u0082.\u00a2\u0006\u0002\n\u0000R\u000e\u0010\r\u001a\u00020\fX\u0082.\u00a2\u0006\u0002\n\u0000R\u000e\u0010\u000e\u001a\u00020\u000fX\u0082.\u00a2\u0006\u0002\n\u0000R\u000e\u0010\u0010\u001a\u00020\u0011X\u0082.\u00a2\u0006\u0002\n\u0000R\u000e\u0010\u0012\u001a\u00020\u0013X\u0082.\u00a2\u0006\u0002\n\u0000R\u000e\u0010\u0014\u001a\u00020\u0015X\u0082.\u00a2\u0006\u0002\n\u0000R\u000e\u0010\u0016\u001a\u00020\fX\u0082.\u00a2\u0006\u0002\n\u0000R\u000e\u0010\u0017\u001a\u00020\u0018X\u0082.\u00a2\u0006\u0002\n\u0000R\u000e\u0010\u0019\u001a\u00020\fX\u0082.\u00a2\u0006\u0002\n\u0000R\u000e\u0010\u001a\u001a\u00020\u0006X\u0082.\u00a2\u0006\u0002\n\u0000R\u000e\u0010\u001b\u001a\u00020\bX\u0082.\u00a2\u0006\u0002\n\u0000\u00a8\u0006-"}, d2 = {"Lcom/bingetv/app/ui/login/LoginActivity;", "Landroidx/appcompat/app/AppCompatActivity;", "()V", "errorText", "Landroid/widget/TextView;", "m3uLayout", "Landroid/widget/LinearLayout;", "m3uLoadButton", "Landroid/widget/Button;", "m3uParser", "Lcom/bingetv/app/parser/M3UParser;", "m3uUrlInput", "Landroid/widget/EditText;", "passwordInput", "playlistRepository", "Lcom/bingetv/app/data/repository/PlaylistRepository;", "prefsManager", "Lcom/bingetv/app/utils/PreferencesManager;", "progressBar", "Landroid/widget/ProgressBar;", "rememberMeCheckbox", "Landroid/widget/CheckBox;", "serverUrlInput", "tabGroup", "Landroid/widget/RadioGroup;", "usernameInput", "xtreamLayout", "xtreamLoadButton", "handleM3uInput", "", "initializeComponents", "loadM3uPlaylist", "url", "", "loadXtreamPlaylist", "navigateToMain", "onCreate", "savedInstanceState", "Landroid/os/Bundle;", "setupListeners", "showError", "message", "showLoading", "show", "", "app_debug"})
public final class LoginActivity extends androidx.appcompat.app.AppCompatActivity {
    private com.bingetv.app.utils.PreferencesManager prefsManager;
    private com.bingetv.app.data.repository.PlaylistRepository playlistRepository;
    private com.bingetv.app.parser.M3UParser m3uParser;
    private android.widget.RadioGroup tabGroup;
    private android.widget.LinearLayout m3uLayout;
    private android.widget.LinearLayout xtreamLayout;
    private android.widget.EditText m3uUrlInput;
    private android.widget.Button m3uLoadButton;
    private android.widget.EditText serverUrlInput;
    private android.widget.EditText usernameInput;
    private android.widget.EditText passwordInput;
    private android.widget.Button xtreamLoadButton;
    private android.widget.CheckBox rememberMeCheckbox;
    private android.widget.ProgressBar progressBar;
    private android.widget.TextView errorText;
    
    public LoginActivity() {
        super();
    }
    
    @java.lang.Override
    protected void onCreate(@org.jetbrains.annotations.Nullable
    android.os.Bundle savedInstanceState) {
    }
    
    private final void initializeComponents() {
    }
    
    private final void setupListeners() {
    }
    
    private final void handleM3uInput() {
    }
    
    private final void loadM3uPlaylist(java.lang.String url) {
    }
    
    private final void loadXtreamPlaylist() {
    }
    
    private final void showLoading(boolean show) {
    }
    
    private final void showError(java.lang.String message) {
    }
    
    private final void navigateToMain() {
    }
}