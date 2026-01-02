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
import androidx.lifecycle.lifecycleScope
import kotlinx.coroutines.launch
import kotlinx.coroutines.delay
import kotlinx.coroutines.Dispatchers

class SplashActivity : AppCompatActivity() {
    
    private lateinit var prefsManager: PreferencesManager
    
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_splash)
        
        prefsManager = PreferencesManager(this)
        
        // 1. Check for updates first
        lifecycleScope.launch {
            // Give splash a minimum display time
            delay(1000) 
            
            com.bingetv.app.utils.UpdateManager.checkForUpdates(this@SplashActivity) {
                // 2. Navigate after update check or if skipped
                navigateToNextScreen()
            }
        }
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
