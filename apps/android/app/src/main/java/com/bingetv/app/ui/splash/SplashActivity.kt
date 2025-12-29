package com.bingetv.app.ui.splash

import android.content.Intent
import android.os.Bundle
import android.os.Handler
import android.os.Looper
import androidx.appcompat.app.AppCompatActivity
import com.bingetv.app.R
import com.bingetv.app.ui.login.LoginActivity
import com.bingetv.app.ui.main.EnhancedMainActivity
import com.bingetv.app.utils.PreferencesManager
import com.bingetv.app.utils.Constants

class SplashActivity : AppCompatActivity() {
    
    private lateinit var prefsManager: PreferencesManager
    
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_splash)
        
        prefsManager = PreferencesManager(this)
        
        // Auto-navigate after splash delay
        Handler(Looper.getMainLooper()).postDelayed({
            navigateToNextScreen()
        }, Constants.SPLASH_DELAY_MS)
    }
    
    private fun navigateToNextScreen() {
        val intent = if (shouldAutoLogin()) {
            // Has saved credentials, go to main
            Intent(this, EnhancedMainActivity::class.java)
        } else {
            // No credentials, go to login
            Intent(this, LoginActivity::class.java)
        }
        
        startActivity(intent)
        finish()
    }
    
    private fun shouldAutoLogin(): Boolean {
        if (!prefsManager.isAutoLoginEnabled()) return false
        
        // Check if we have either M3U URL or Xtream credentials
        val hasM3u = !prefsManager.getM3uUrl().isNullOrEmpty()
        val hasXtream = !prefsManager.getServerUrl().isNullOrEmpty() &&
                       !prefsManager.getUsername().isNullOrEmpty() &&
                       !prefsManager.getPassword().isNullOrEmpty()
        
        return hasM3u || hasXtream
    }
}
