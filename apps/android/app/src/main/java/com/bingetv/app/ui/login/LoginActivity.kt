package com.bingetv.app.ui.login

import android.content.Intent
import android.os.Bundle
import android.view.View
import android.widget.*
import androidx.appcompat.app.AppCompatActivity
import androidx.lifecycle.lifecycleScope
import com.bingetv.app.R
import com.bingetv.app.data.database.BingeTVDatabase
import com.bingetv.app.data.database.PlaylistEntity
import com.bingetv.app.data.repository.PlaylistRepository
import com.bingetv.app.parser.M3UParser
import com.bingetv.app.ui.main.EnhancedMainActivity
import com.bingetv.app.utils.PreferencesManager
import com.bingetv.app.utils.Constants
import com.bingetv.app.utils.isValidM3uUrl
import com.bingetv.app.utils.isValidUrl
import kotlinx.coroutines.launch

class LoginActivity : AppCompatActivity() {
    
    private lateinit var prefsManager: PreferencesManager
    private lateinit var playlistRepository: PlaylistRepository
    private lateinit var m3uParser: M3UParser
    
    // UI Components
    private lateinit var tabGroup: RadioGroup
    private lateinit var m3uLayout: LinearLayout
    private lateinit var xtreamLayout: LinearLayout
    
    // M3U inputs
    private lateinit var m3uUrlInput: EditText
    private lateinit var m3uLoadButton: Button
    
    // Xtream inputs
    private lateinit var serverUrlInput: EditText
    private lateinit var usernameInput: EditText
    private lateinit var passwordInput: EditText
    private lateinit var xtreamLoadButton: Button
    
    // Common
    private lateinit var rememberMeCheckbox: CheckBox
    private lateinit var progressBar: ProgressBar
    private lateinit var errorText: TextView
    
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_login)
        
        initializeComponents()
        setupListeners()
    }
    
    private fun initializeComponents() {
        prefsManager = PreferencesManager(this)
        val database = BingeTVDatabase.getDatabase(this)
        playlistRepository = PlaylistRepository(database.playlistDao())
        m3uParser = M3UParser()
        
        // Find views
        tabGroup = findViewById(R.id.tab_group)
        m3uLayout = findViewById(R.id.m3u_layout)
        xtreamLayout = findViewById(R.id.xtream_layout)
        
        m3uUrlInput = findViewById(R.id.m3u_url_input)
        m3uLoadButton = findViewById(R.id.m3u_load_button)
        
        serverUrlInput = findViewById(R.id.server_url_input)
        usernameInput = findViewById(R.id.username_input)
        passwordInput = findViewById(R.id.password_input)
        xtreamLoadButton = findViewById(R.id.xtream_load_button)
        
        rememberMeCheckbox = findViewById(R.id.remember_me_checkbox)
        progressBar = findViewById(R.id.progress_bar)
        errorText = findViewById(R.id.error_text)
        
        // Load saved values
        prefsManager.getM3uUrl()?.let { m3uUrlInput.setText(it) }
        prefsManager.getServerUrl()?.let { serverUrlInput.setText(it) }
        prefsManager.getUsername()?.let { usernameInput.setText(it) }
        rememberMeCheckbox.isChecked = prefsManager.isAutoLoginEnabled()
    }
    
    private fun setupListeners() {
        tabGroup.setOnCheckedChangeListener { _, checkedId ->
            when (checkedId) {
                R.id.tab_m3u -> {
                    m3uLayout.visibility = View.VISIBLE
                    xtreamLayout.visibility = View.GONE
                }
                R.id.tab_xtream -> {
                    m3uLayout.visibility = View.GONE
                    xtreamLayout.visibility = View.VISIBLE
                }
            }
        }
        
        m3uLoadButton.setOnClickListener { loadM3uPlaylist() }
        xtreamLoadButton.setOnClickListener { loadXtreamPlaylist() }
    }
    
    private fun loadM3uPlaylist() {
        val url = m3uUrlInput.text.toString().trim()
        
        if (url.isEmpty()) {
            showError("Please enter M3U URL")
            return
        }
        
        if (!url.isValidM3uUrl()) {
            showError("Invalid M3U URL format")
            return
        }
        
        showLoading(true)
        lifecycleScope.launch {
            try {
                // Test parsing
                val channels = m3uParser.parsePlaylist(url)
                
                if (channels.isEmpty()) {
                    showError("No channels found in playlist")
                    showLoading(false)
                    return@launch
                }
                
                // Save playlist
                val playlist = PlaylistEntity(
                    name = "M3U Playlist",
                    type = Constants.PLAYLIST_TYPE_M3U,
                    m3uUrl = url,
                    isActive = true
                )
                val playlistId = playlistRepository.insertPlaylist(playlist)
                
                // Save preferences
                prefsManager.saveM3uUrl(url)
                prefsManager.setAutoLogin(rememberMeCheckbox.isChecked)
                prefsManager.setActivePlaylistId(playlistId)
                
                // Navigate to main
                navigateToMain()
                
            } catch (e: Exception) {
                showError("Error loading playlist: ${e.message}")
                showLoading(false)
            }
        }
    }
    
    private fun loadXtreamPlaylist() {
        val serverUrl = serverUrlInput.text.toString().trim()
        val username = usernameInput.text.toString().trim()
        val password = passwordInput.text.toString().trim()
        
        if (serverUrl.isEmpty() || username.isEmpty() || password.isEmpty()) {
            showError("Please fill all fields")
            return
        }
        
        if (!serverUrl.isValidUrl()) {
            showError("Invalid server URL")
            return
        }
        
        showLoading(true)
        lifecycleScope.launch {
            try {
                // Test connection
                val result = playlistRepository.testXtreamConnection(serverUrl, username, password)
                
                if (result.isSuccess) {
                    // Save playlist
                    val playlist = PlaylistEntity(
                        name = "Xtream Codes",
                        type = Constants.PLAYLIST_TYPE_XTREAM,
                        serverUrl = serverUrl,
                        username = username,
                        password = password,
                        isActive = true
                    )
                    val playlistId = playlistRepository.insertPlaylist(playlist)
                    
                    // Save preferences
                    prefsManager.saveCredentials(serverUrl, username, password)
                    prefsManager.setAutoLogin(rememberMeCheckbox.isChecked)
                    prefsManager.setActivePlaylistId(playlistId)
                    
                    // Navigate to main
                    navigateToMain()
                } else {
                    showError("Connection failed: ${result.exceptionOrNull()?.message}")
                    showLoading(false)
                }
                
            } catch (e: Exception) {
                showError("Error: ${e.message}")
                showLoading(false)
            }
        }
    }
    
    private fun showLoading(show: Boolean) {
        progressBar.visibility = if (show) View.VISIBLE else View.GONE
        m3uLoadButton.isEnabled = !show
        xtreamLoadButton.isEnabled = !show
    }
    
    private fun showError(message: String) {
        errorText.text = message
        errorText.visibility = View.VISIBLE
    }
    
    private fun navigateToMain() {
        val intent = Intent(this, EnhancedMainActivity::class.java)
        startActivity(intent)
        finish()
    }
}
