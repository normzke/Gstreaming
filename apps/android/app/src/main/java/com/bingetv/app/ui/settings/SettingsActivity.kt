package com.bingetv.app.ui.settings

import android.content.Intent
import android.os.Bundle
import android.widget.Button
import android.widget.SeekBar
import android.widget.TextView
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import com.bingetv.app.R
import com.bingetv.app.ui.login.LoginActivity
import com.bingetv.app.utils.PreferencesManager
import com.bingetv.app.utils.Constants

class SettingsActivity : AppCompatActivity() {
    
    private lateinit var prefsManager: PreferencesManager
    
    // UI Components
    private lateinit var gridColumnsSeekBar: SeekBar
    private lateinit var gridColumnsText: TextView
    private lateinit var logoutButton: Button
    private lateinit var clearCacheButton: Button
    private lateinit var aboutButton: Button
    
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_settings)
        
        prefsManager = PreferencesManager(this)
        
        initializeViews()
        loadSettings()
        setupListeners()
    }
    
    private fun initializeViews() {
        gridColumnsSeekBar = findViewById(R.id.grid_columns_seekbar)
        gridColumnsText = findViewById(R.id.grid_columns_text)
        logoutButton = findViewById(R.id.logout_button)
        clearCacheButton = findViewById(R.id.clear_cache_button)
        aboutButton = findViewById(R.id.about_button)
    }
    
    private fun loadSettings() {
        val currentColumns = prefsManager.getGridColumns()
        gridColumnsSeekBar.progress = currentColumns - Constants.GRID_COLUMNS_MIN
        updateGridColumnsText(currentColumns)
    }
    
    private fun setupListeners() {
        gridColumnsSeekBar.setOnSeekBarChangeListener(object : SeekBar.OnSeekBarChangeListener {
            override fun onProgressChanged(seekBar: SeekBar?, progress: Int, fromUser: Boolean) {
                val columns = progress + Constants.GRID_COLUMNS_MIN
                updateGridColumnsText(columns)
            }
            
            override fun onStartTrackingTouch(seekBar: SeekBar?) {}
            
            override fun onStopTrackingTouch(seekBar: SeekBar?) {
                val columns = (seekBar?.progress ?: 0) + Constants.GRID_COLUMNS_MIN
                prefsManager.setGridColumns(columns)
                Toast.makeText(this@SettingsActivity, "Grid columns updated. Restart app to apply.", Toast.LENGTH_LONG).show()
            }
        })
        
        logoutButton.setOnClickListener {
            logout()
        }
        
        clearCacheButton.setOnClickListener {
            clearCache()
        }
        
        aboutButton.setOnClickListener {
            showAbout()
        }
    }
    
    private fun updateGridColumnsText(columns: Int) {
        gridColumnsText.text = "Grid Columns: $columns"
    }
    
    private fun logout() {
        // Clear credentials
        prefsManager.clearCredentials()
        prefsManager.setAutoLogin(false)
        
        // Navigate to login
        val intent = Intent(this, LoginActivity::class.java)
        intent.flags = Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_CLEAR_TASK
        startActivity(intent)
        finish()
    }
    
    private fun clearCache() {
        // Clear Glide cache
        com.bingetv.app.utils.ImageLoader.clearCache(this)
        Toast.makeText(this, "Cache cleared", Toast.LENGTH_SHORT).show()
    }
    
    private fun showAbout() {
        val message = """
            ${Constants.APP_NAME}
            Version ${Constants.APP_VERSION}
            
            Professional IPTV Player
            
            Features:
            • M3U Playlist Support
            • Xtream Codes API
            • EPG Support
            • Favorites
            • Search
            
            © 2024 BingeTV
        """.trimIndent()
        
        android.app.AlertDialog.Builder(this)
            .setTitle("About")
            .setMessage(message)
            .setPositiveButton("OK", null)
            .show()
    }
}
