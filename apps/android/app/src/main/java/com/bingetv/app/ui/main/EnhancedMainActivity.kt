package com.bingetv.app.ui.main

import android.content.Intent
import android.os.Bundle
import android.view.View
import android.widget.*
import androidx.appcompat.app.AppCompatActivity
import androidx.lifecycle.lifecycleScope
import androidx.recyclerview.widget.GridLayoutManager
import androidx.recyclerview.widget.RecyclerView
import com.bingetv.app.R
import com.bingetv.app.data.database.BingeTVDatabase
import com.bingetv.app.data.database.ChannelEntity
import com.bingetv.app.data.database.CategoryEntity
import com.bingetv.app.data.repository.ChannelRepository
import com.bingetv.app.data.repository.PlaylistRepository
import com.bingetv.app.data.api.ApiClient
import com.bingetv.app.model.Channel
import com.bingetv.app.parser.M3UParser
import com.bingetv.app.ui.adapters.ChannelGridAdapter
import com.bingetv.app.ui.adapters.CategoryAdapter
import com.bingetv.app.ui.login.LoginActivity
import com.bingetv.app.PlaybackActivity
import com.bingetv.app.utils.PreferencesManager
import com.bingetv.app.utils.Constants
import com.bingetv.app.utils.show
import com.bingetv.app.utils.hide
import kotlinx.coroutines.launch

class EnhancedMainActivity : AppCompatActivity() {
    
    private lateinit var prefsManager: PreferencesManager
    private lateinit var channelRepository: ChannelRepository
    private lateinit var playlistRepository: PlaylistRepository
    private lateinit var m3uParser: M3UParser
    
    // UI Components
    private lateinit var categoryRecyclerView: RecyclerView
    private lateinit var channelRecyclerView: RecyclerView
    private lateinit var loadingView: ProgressBar
    private lateinit var errorView: TextView
    private lateinit var searchButton: ImageButton
    private lateinit var favoritesButton: ImageButton
    private lateinit var settingsButton: ImageButton
    
    // Adapters
    private lateinit var categoryAdapter: CategoryAdapter
    private lateinit var channelAdapter: ChannelGridAdapter
    
    // Data
    private var allChannels = listOf<ChannelEntity>()
    private var currentCategory: String? = null
    
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_main_enhanced)
        
        initializeComponents()
        setupRecyclerViews()
        setupListeners()
        loadPlaylist()
    }
    
    private fun initializeComponents() {
        prefsManager = PreferencesManager(this)
        val database = BingeTVDatabase.getDatabase(this)
        channelRepository = ChannelRepository(database.channelDao(), database.categoryDao())
        playlistRepository = PlaylistRepository(database.playlistDao())
        m3uParser = M3UParser()
        
        // Find views
        categoryRecyclerView = findViewById(R.id.category_recycler_view)
        channelRecyclerView = findViewById(R.id.channel_recycler_view)
        loadingView = findViewById(R.id.loading_view)
        errorView = findViewById(R.id.error_view)
        searchButton = findViewById(R.id.search_button)
        favoritesButton = findViewById(R.id.favorites_button)
        settingsButton = findViewById(R.id.settings_button)
    }
    
    private fun setupRecyclerViews() {
        // Category sidebar
        categoryAdapter = CategoryAdapter { category ->
            onCategorySelected(category)
        }
        categoryRecyclerView.apply {
            adapter = categoryAdapter
            layoutManager = androidx.recyclerview.widget.LinearLayoutManager(this@EnhancedMainActivity)
        }
        
        // Channel grid
        val gridColumns = prefsManager.getGridColumns()
        channelAdapter = ChannelGridAdapter(
            onChannelClick = { channel ->
                playChannel(channel)
            },
            onChannelLongClick = { channel ->
                showChannelContextMenu(channel)
            }
        )
        channelRecyclerView.apply {
            adapter = channelAdapter
            layoutManager = GridLayoutManager(this@EnhancedMainActivity, gridColumns)
        }
    }
    
    private fun showChannelContextMenu(channel: ChannelEntity) {
        val dialog = com.bingetv.app.ui.dialogs.ChannelContextDialog(
            this,
            channel,
            onToggleFavorite = { ch ->
                toggleFavorite(ch)
            },
            onPlayChannel = { ch ->
                playChannel(ch)
            }
        )
        dialog.show()
    }
    
    private fun toggleFavorite(channel: ChannelEntity) {
        lifecycleScope.launch {
            channelRepository.toggleFavorite(channel.id, !channel.isFavorite)
            Toast.makeText(
                this@EnhancedMainActivity,
                if (!channel.isFavorite) "Added to favorites" else "Removed from favorites",
                Toast.LENGTH_SHORT
            ).show()
        }
    }
    
    private fun setupListeners() {
        searchButton.setOnClickListener {
            showSearchDialog()
        }
        
        favoritesButton.setOnClickListener {
            showFavorites()
        }
        
        settingsButton.setOnClickListener {
            val intent = Intent(this, com.bingetv.app.ui.settings.SettingsActivity::class.java)
            startActivity(intent)
        }
        
        // Observe channel data
        channelRepository.allChannels.observe(this) { channels ->
            allChannels = channels
            updateChannelDisplay()
        }
        
        channelRepository.allCategories.observe(this) { categories ->
            updateCategoryDisplay(categories)
        }
    }
    
    private fun showSearchDialog() {
        if (allChannels.isEmpty()) {
            Toast.makeText(this, "No channels to search", Toast.LENGTH_SHORT).show()
            return
        }
        
        val searchDialog = com.bingetv.app.ui.dialogs.SearchDialog(this, allChannels) { channel ->
            playChannel(channel)
        }
        searchDialog.show()
    }
    
    private fun loadPlaylist() {
        loadingView.show()
        errorView.hide()
        
        lifecycleScope.launch {
            try {
                val playlist = playlistRepository.getActivePlaylist()
                
                if (playlist == null) {
                    // No playlist, go back to login
                    navigateToLogin()
                    return@launch
                }
                
                when (playlist.type) {
                    Constants.PLAYLIST_TYPE_M3U -> loadM3uPlaylist(playlist.m3uUrl!!)
                    Constants.PLAYLIST_TYPE_XTREAM -> loadXtreamPlaylist(
                        playlist.serverUrl!!,
                        playlist.username!!,
                        playlist.password!!
                    )
                }
                
            } catch (e: Exception) {
                showError("Error loading playlist: ${e.message}")
            }
        }
    }
    
    private suspend fun loadM3uPlaylist(url: String) {
        try {
            val channels = m3uParser.parsePlaylist(url)
            
            if (channels.isEmpty()) {
                showError("No channels found")
                return
            }
            
            // Convert to entities and save
            val channelEntities = channels.mapIndexed { index, channel ->
                ChannelEntity(
                    streamId = channel.url,
                    name = channel.name,
                    streamUrl = channel.url,
                    logoUrl = channel.logo,
                    category = channel.group,
                    sortOrder = index
                )
            }
            
            // Extract categories
            val categories = channels.mapNotNull { it.group }.distinct().mapIndexed { index, name ->
                CategoryEntity(
                    categoryId = name,
                    categoryName = name,
                    sortOrder = index
                )
            }
            
            // Save to database
            channelRepository.clearAllData()
            channelRepository.insertChannels(channelEntities)
            channelRepository.insertCategories(categories)
            
            loadingView.hide()
            
        } catch (e: Exception) {
            showError("Error parsing playlist: ${e.message}")
        }
    }
    
    private suspend fun loadXtreamPlaylist(serverUrl: String, username: String, password: String) {
        try {
            val api = ApiClient.getXtreamApi(serverUrl)
            
            // Get categories
            val categoriesResponse = api.getLiveCategories(username, password)
            if (!categoriesResponse.isSuccessful) {
                showError("Failed to load categories")
                return
            }
            
            // Get channels
            val channelsResponse = api.getLiveStreams(username, password)
            if (!channelsResponse.isSuccessful) {
                showError("Failed to load channels")
                return
            }
            
            val xtreamCategories = categoriesResponse.body() ?: emptyList()
            val xtreamChannels = channelsResponse.body() ?: emptyList()
            
            // Convert to entities
            val categoryEntities = xtreamCategories.mapIndexed { index, cat ->
                CategoryEntity(
                    categoryId = cat.categoryId,
                    categoryName = cat.categoryName,
                    sortOrder = index
                )
            }
            
            val channelEntities = xtreamChannels.mapIndexed { index, ch ->
                val streamUrl = "$serverUrl/live/$username/$password/${ch.streamId}.ts"
                ChannelEntity(
                    streamId = ch.streamId.toString(),
                    name = ch.name,
                    streamUrl = streamUrl,
                    logoUrl = ch.streamIcon,
                    category = ch.categoryId,
                    epgChannelId = ch.epgChannelId,
                    sortOrder = index
                )
            }
            
            // Save to database
            channelRepository.clearAllData()
            channelRepository.insertCategories(categoryEntities)
            channelRepository.insertChannels(channelEntities)
            
            loadingView.hide()
            
        } catch (e: Exception) {
            showError("Error loading Xtream playlist: ${e.message}")
        }
    }
    
    private fun updateCategoryDisplay(categories: List<CategoryEntity>) {
        val allCategory = CategoryEntity(
            categoryId = "all",
            categoryName = "All Channels",
            sortOrder = -1
        )
        val favoritesCategory = CategoryEntity(
            categoryId = "favorites",
            categoryName = "Favorites",
            sortOrder = -2
        )
        
        val displayCategories = listOf(allCategory, favoritesCategory) + categories
        categoryAdapter.submitList(displayCategories)
    }
    
    private fun updateChannelDisplay() {
        val filteredChannels = when (currentCategory) {
            null, "all" -> allChannels
            "favorites" -> allChannels.filter { it.isFavorite }
            else -> allChannels.filter { it.category == currentCategory }
        }
        
        channelAdapter.submitList(filteredChannels)
    }
    
    private fun onCategorySelected(category: CategoryEntity) {
        currentCategory = category.categoryId
        updateChannelDisplay()
    }
    
    private fun showFavorites() {
        currentCategory = "favorites"
        updateChannelDisplay()
    }
    
    private fun playChannel(channel: ChannelEntity) {
        val intent = Intent(this, PlaybackActivity::class.java).apply {
            putExtra(Constants.EXTRA_CHANNEL_NAME, channel.name)
            putExtra(Constants.EXTRA_CHANNEL_URL, channel.streamUrl)
            putExtra(Constants.EXTRA_CHANNEL_LOGO, channel.logoUrl)
        }
        startActivity(intent)
    }
    
    private fun showError(message: String) {
        loadingView.hide()
        errorView.text = message
        errorView.show()
    }
    
    private fun navigateToLogin() {
        val intent = Intent(this, LoginActivity::class.java)
        startActivity(intent)
        finish()
    }
    
    override fun onBackPressed() {
        // Prevent going back to login
        moveTaskToBack(true)
    }
}
