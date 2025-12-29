package com.bingetv.app.ui.splash;

@kotlin.Metadata(mv = {1, 9, 0}, k = 1, xi = 48, d1 = {"\u0000&\n\u0002\u0018\u0002\n\u0002\u0018\u0002\n\u0002\b\u0002\n\u0002\u0018\u0002\n\u0000\n\u0002\u0010\u0002\n\u0002\b\u0002\n\u0002\u0018\u0002\n\u0000\n\u0002\u0010\u000b\n\u0000\u0018\u00002\u00020\u0001B\u0005\u00a2\u0006\u0002\u0010\u0002J\b\u0010\u0005\u001a\u00020\u0006H\u0002J\u0012\u0010\u0007\u001a\u00020\u00062\b\u0010\b\u001a\u0004\u0018\u00010\tH\u0014J\b\u0010\n\u001a\u00020\u000bH\u0002R\u000e\u0010\u0003\u001a\u00020\u0004X\u0082.\u00a2\u0006\u0002\n\u0000\u00a8\u0006\f"}, d2 = {"Lcom/bingetv/app/ui/splash/SplashActivity;", "Landroidx/appcompat/app/AppCompatActivity;", "()V", "prefsManager", "Lcom/bingetv/app/utils/PreferencesManager;", "navigateToNextScreen", "", "onCreate", "savedInstanceState", "Landroid/os/Bundle;", "shouldAutoLogin", "", "app_debug"})
public final class SplashActivity extends androidx.appcompat.app.AppCompatActivity {
    private com.bingetv.app.utils.PreferencesManager prefsManager;
    
    public SplashActivity() {
        super();
    }
    
    @java.lang.Override
    protected void onCreate(@org.jetbrains.annotations.Nullable
    android.os.Bundle savedInstanceState) {
    }
    
    private final void navigateToNextScreen() {
    }
    
    private final boolean shouldAutoLogin() {
        return false;
    }
}