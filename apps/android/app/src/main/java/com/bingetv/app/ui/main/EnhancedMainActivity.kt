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
import com.bingetv.app.utils.dpToPx
import kotlinx.coroutines.launch
import kotlinx.coroutines.isActive

class EnhancedMainActivity : AppCompatActivity() {
    
    private lateinit var prefsManager: PreferencesManager
    private lateinit var channelRepository: ChannelRepository
    private lateinit var playlistRepository: PlaylistRepository
    private lateinit var m3uParser: M3UParser
    private lateinit var database: BingeTVDatabase
    private var epgJob: kotlinx.coroutines.Job? = null
    
    // UI Components
    private lateinit var categoryRecyclerView: RecyclerView
    private lateinit var channelRecyclerView: RecyclerView
    private lateinit var loadingView: ProgressBar
    private lateinit var errorView: TextView
    
    // Nav Rail Buttons
    private lateinit var navSearch: ImageButton
    private lateinit var navHistory: ImageButton
    private lateinit var navLive: ImageButton
    private lateinit var navMovies: ImageButton
    private lateinit var navShows: ImageButton
    private lateinit var navRecordings: ImageButton
    private lateinit var navFavorites: ImageButton
    private lateinit var navSettings: ImageButton
    
    // Preview
    private lateinit var topPreviewContainer: FrameLayout
    private lateinit var previewPlayerView: com.google.android.exoplayer2.ui.StyledPlayerView
    private var previewPlayer: com.google.android.exoplayer2.ExoPlayer? = null
    
    // Adapters
    private lateinit var categoryAdapter: CategoryAdapter
    private lateinit var channelAdapter: ChannelGridAdapter
    
    // Data
    private var allChannels = listOf<ChannelEntity>()
    private var allCategories = listOf<CategoryEntity>()
    private var currentCategory: String? = null
    private var searchQuery: String? = null
    private var currentMode: String = "live"
    
    // Layout Constants (initialized in onCreate)
    private var RAIL_WIDTH_FULL: Int = 0
    private var CAT_WIDTH_FULL: Int = 0
    private var MINI_WIDTH: Int = 0
    private var COLLAPSED_WIDTH: Int = 0

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_main_enhanced)
        
        // Initialize widths
        RAIL_WIDTH_FULL = 80.dpToPx()
        CAT_WIDTH_FULL = 220.dpToPx()
        MINI_WIDTH = 48.dpToPx()
        COLLAPSED_WIDTH = 2.dpToPx()
        
        initializeComponents()
        setupRecyclerViews()
        setupListeners()
        loadPlaylist()
    }
    
    private fun setMode(mode: String) {
        currentMode = mode
        currentCategory = "all"
        updateDisplay()
    }

    private fun updateDisplay() {
        try {
            updateCategoryDisplay(allCategories)
            updateChannelDisplay()
        } catch (e: Exception) {
            android.util.Log.e(TAG, "Error updating display", e)
            Toast.makeText(this, "Error refreshing channels", Toast.LENGTH_SHORT).show()
        }
    }

    private fun initializeComponents() {
        prefsManager = PreferencesManager(this)
        database = BingeTVDatabase.getDatabase(this)
        channelRepository = ChannelRepository(database.channelDao(), database.categoryDao())
        playlistRepository = PlaylistRepository(database.playlistDao())
        m3uParser = M3UParser()
        
        // Find views
        categoryRecyclerView = findViewById(R.id.category_recycler_view)
        channelRecyclerView = findViewById(R.id.channel_recycler_view)
        loadingView = findViewById(R.id.loading_view)
        errorView = findViewById(R.id.error_view)
        
        // Nav Rail
        navSearch = findViewById(R.id.nav_search)
        navHistory = findViewById(R.id.nav_history)
        navLive = findViewById(R.id.nav_live)
        navMovies = findViewById(R.id.nav_movies)
        navShows = findViewById(R.id.nav_shows)
        navRecordings = findViewById(R.id.nav_recordings)
        navFavorites = findViewById(R.id.nav_favorites)
        navSettings = findViewById(R.id.nav_settings)
        
        // Preview
        topPreviewContainer = findViewById(R.id.top_preview_container)
        previewPlayerView = findViewById(R.id.preview_player_view)
    }
    
    private fun setupPreviewPlayer() {
        previewPlayer = com.google.android.exoplayer2.ExoPlayer.Builder(this).build()
        previewPlayerView.player = previewPlayer
        previewPlayerView.useController = false // Hide controls for preview
    }
    
    private fun releasePreviewPlayer() {
        previewPlayer?.release()
        previewPlayer = null
    }

    override fun onStart() {
        super.onStart()
        setupPreviewPlayer()
        startEpgRefresher()
    }

    override fun onStop() {
        super.onStop()
        releasePreviewPlayer()
        stopEpgRefresher()
    }
    
    override fun onDestroy() {
        super.onDestroy()
        releasePreviewPlayer()
    }
    override fun onResume() {
        super.onResume()
        applyAppearanceSettings()
    }
    
    private fun startEpgRefresher() {
        stopEpgRefresher()
        epgJob = lifecycleScope.launch(kotlinx.coroutines.Dispatchers.IO) {
            while (isActive) {
                try {
                    val now = System.currentTimeMillis()
                    val programs = database.epgDao().getAllActivePrograms(now)
                    // Group by channelId
                    val map = programs.groupBy { it.channelId }
                    
                    kotlinx.coroutines.withContext(kotlinx.coroutines.Dispatchers.Main) {
                        if (::channelAdapter.isInitialized) {
                            channelAdapter.submitEpgData(map)
                        }
                    }
                } catch (e: Exception) {
                    e.printStackTrace()
                }
                kotlinx.coroutines.delay(60000) // 1 minute
            }
        }
    }
    
    private fun stopEpgRefresher() {
        epgJob?.cancel()
        epgJob = null
    }

    private fun applyAppearanceSettings() {
        // Clock
        val clock = findViewById<android.widget.TextView>(R.id.clock_view)
        clock.visibility = if (prefsManager.isShowClock()) android.view.View.VISIBLE else android.view.View.GONE
        
        // Transparency
        val transparency = prefsManager.getUiTransparency()
        val alpha = ((100 - transparency) * 255) / 100
        
        findViewById<android.view.View>(R.id.nav_rail).background?.alpha = alpha
        findViewById<android.view.View>(R.id.category_recycler_view).background?.alpha = alpha
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
            },
            onChannelFocused = { channel ->
                loadPreview(channel)
            }
        )
        channelRecyclerView.apply {
            adapter = channelAdapter
            layoutManager = GridLayoutManager(this@EnhancedMainActivity, gridColumns)
        }
    }
    
    private var previewJob: kotlinx.coroutines.Job? = null

    private fun loadPreview(channel: ChannelEntity) {
        // Debounce: Cancel previous request
        previewJob?.cancel()
        
        previewJob = lifecycleScope.launch {
            // Wait for focus to settle
            kotlinx.coroutines.delay(600)
            
            // Show container if hidden
            if (topPreviewContainer.visibility != View.VISIBLE) {
                topPreviewContainer.visibility = View.VISIBLE
            }
            
            // Update channel info
            val previewChannelName = findViewById<TextView>(R.id.preview_channel_name)
            val previewChannelLogo = findViewById<ImageView>(R.id.preview_channel_logo)
            val previewEpgNow = findViewById<TextView>(R.id.preview_epg_now)
            val previewEpgNext = findViewById<TextView>(R.id.preview_epg_next)
            val previewEpgProgress = findViewById<android.widget.ProgressBar>(R.id.preview_epg_progress)
            
            previewChannelName.text = channel.name
            
            // Load channel logo
            if (!channel.logoUrl.isNullOrEmpty()) {
                com.bingetv.app.utils.ImageLoader.loadChannelLogo(this@EnhancedMainActivity, channel.logoUrl, previewChannelLogo)
            } else {
                previewChannelLogo.setImageResource(R.drawable.ic_placeholder_channel)
            }
            
            // Load EPG data
            try {
                val now = System.currentTimeMillis()
                val epgDao = database.epgDao()
                
                // 1. Try local lookup
                var epgPrograms = kotlinx.coroutines.withContext(kotlinx.coroutines.Dispatchers.IO) {
                    epgDao.getAllActivePrograms(now).filter { 
                        it.channelId == (channel.epgChannelId ?: channel.streamId)
                    }
                }
                
                // 2. If empty, try fetching from server
                if (epgPrograms.isEmpty()) {
                    val playlist = playlistRepository.getActivePlaylist()
                    if (playlist != null && playlist.type == Constants.PLAYLIST_TYPE_XTREAM) {
                        try {
                            previewEpgNow.text = "Loading EPG..."
                            val api = ApiClient.getXtreamApi(playlist.serverUrl!!)
                            val streamIdInt = channel.streamId.toIntOrNull() ?: 0
                            if (streamIdInt != 0) {
                                val response = api.getShortEpg(playlist.username!!, playlist.password!!, streamId = streamIdInt)
                                if (response.isSuccessful && response.body() != null) {
                                    val listings = response.body()!!.values.flatten()
                                    if (listings.isNotEmpty()) {
                                        val entities = listings.map { 
                                            com.bingetv.app.data.database.EpgProgramEntity(
                                                channelId = channel.epgChannelId ?: channel.streamId,
                                                title = decodeEpgText(it.title),
                                                description = decodeEpgText(it.description),
                                                startTime = it.startTimestamp * 1000,
                                                endTime = it.stopTimestamp * 1000
                                            )
                                        }
                                        kotlinx.coroutines.withContext(kotlinx.coroutines.Dispatchers.IO) {
                                            epgDao.insertPrograms(entities)
                                        }
                                        // Refresh grid immediately
                                        startEpgRefresher()
                                        // Refresh programs list for current preview
                                        epgPrograms = entities
                                    }
                                }
                            }
                        } catch (e: Exception) {
                            android.util.Log.e(TAG, "Dynamic EPG Fetch failed", e)
                        }
                    }
                }

                // 3. Display Result
                if (epgPrograms.isNotEmpty()) {
                    val current = epgPrograms.firstOrNull { now >= it.startTime && now < it.endTime }
                    val next = epgPrograms.sortedBy { it.startTime }.firstOrNull { it.startTime >= now }
                    
                    if (current != null) {
                        previewEpgNow.text = current.title
                        val total = current.endTime - current.startTime
                        if (total > 0) {
                            val percent = (((now - current.startTime).toFloat() / total) * 100).toInt()
                            previewEpgProgress.progress = percent
                            previewEpgProgress.visibility = View.VISIBLE
                        } else {
                            previewEpgProgress.visibility = View.GONE
                        }
                    } else {
                        previewEpgNow.text = "No current program"
                        previewEpgProgress.visibility = View.GONE
                    }
                    
                    if (next != null) {
                        val timeFormat = java.text.SimpleDateFormat("HH:mm", java.util.Locale.getDefault())
                        previewEpgNext.text = "Next: ${timeFormat.format(java.util.Date(next.startTime))} - ${next.title}"
                        previewEpgNext.visibility = View.VISIBLE
                    } else {
                        previewEpgNext.visibility = View.GONE
                    }
                } else {
                    previewEpgNow.text = "No Information Available"
                    previewEpgNext.visibility = View.GONE
                    previewEpgProgress.visibility = View.GONE
                }
            } catch (e: Exception) {
                android.util.Log.e(TAG, "Error in loadPreview EPG section", e)
            }
            
            // Prepare media item for video preview
            try {
                val mediaItem = com.google.android.exoplayer2.MediaItem.Builder()
                    .setUri(channel.streamUrl)
                    .build()
                
                // Setup player
                previewPlayer?.setMediaItem(mediaItem)
                previewPlayer?.prepare()
                previewPlayer?.playWhenReady = true
                android.util.Log.d(TAG, "Preview started for: ${channel.name}")
            } catch (e: Exception) {
                android.util.Log.e(TAG, "Error loading preview", e)
            }
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
        navSearch.setOnClickListener { showSearchDialog() }
        navFavorites.setOnClickListener { showFavorites() }
        navSettings.setOnClickListener { 
            startActivity(Intent(this, com.bingetv.app.ui.settings.SettingsActivity::class.java))
        }
        
        // Mode switching logic (Placeholders for now, effectively same grid)
        navLive.setOnClickListener { 
            setMode("live")
            Toast.makeText(this, "Live TV", Toast.LENGTH_SHORT).show()
        }
        
        navMovies.setOnClickListener {
            setMode("movies")
            Toast.makeText(this, "Movies", Toast.LENGTH_SHORT).show()
        }
        
        navShows.setOnClickListener {
            setMode("shows")
            Toast.makeText(this, "TV Shows", Toast.LENGTH_SHORT).show()
        }
        
        navRecordings.setOnClickListener {
             setMode("recordings")
             Toast.makeText(this, "Recordings (Empty)", Toast.LENGTH_SHORT).show()
        }
        
        // Observe channel data
        channelRepository.allChannels.observe(this) { channels ->
            allChannels = channels
            updateDisplay()
        }
        
        channelRepository.allCategories.observe(this) { categories ->
            allCategories = categories
            updateDisplay()
        }
        
        setupFocusOptimization()
    }
    
    private fun setupFocusOptimization() {
        val root = findViewById<View>(android.R.id.content) ?: return
        root.viewTreeObserver.addOnGlobalFocusChangeListener { _, newFocus ->
            if (newFocus == null) return@addOnGlobalFocusChangeListener
            
            lifecycleScope.launch {
                try {
                    val navRail = findViewById<View>(R.id.nav_rail)
                    val catRecycler = categoryRecyclerView
                    val chanRecycler = channelRecyclerView
                    
                    if (navRail == null) return@launch

                    fun isInside(view: View, goal: View?): Boolean {
                         if (goal == null) return false
                         var v: View? = view
                         while (v != null) {
                             if (v == goal) return true
                             val p = v.parent
                             v = if (p is View) p else null
                         }
                         return false
                    }
                    
                    when {
                        isInside(newFocus, chanRecycler) -> {
                            // Focus in Grid -> Hide Rail, Peek Cats
                            kotlinx.coroutines.delay(50)
                            animateViewWidth(navRail, COLLAPSED_WIDTH)
                            animateViewWidth(catRecycler, MINI_WIDTH)
                        }
                        isInside(newFocus, catRecycler) -> {
                            // Focus in Cats -> Peek Rail, Show Cats
                            animateViewWidth(navRail, MINI_WIDTH)
                            animateViewWidth(catRecycler, CAT_WIDTH_FULL)
                        }
                        isInside(newFocus, navRail) -> {
                            // Focus in Nav -> Show Rail, Show Cats
                            animateViewWidth(navRail, RAIL_WIDTH_FULL)
                            animateViewWidth(catRecycler, CAT_WIDTH_FULL)
                        }
                    }
                } catch (e: Exception) {
                    android.util.Log.e(TAG, "Focus optimization error", e)
                }
            }
        }
    }

    private fun animateViewWidth(view: View?, width: Int) {
        if (view == null) return
        if (view.width == width) return
        
        // Remove existing animator if any to prevent conflicts
        view.getTag(R.id.tag_animator)?.let { (it as? android.animation.ValueAnimator)?.cancel() }
        
        val anim = android.animation.ValueAnimator.ofInt(view.width, width)
        anim.addUpdateListener { valueAnimator ->
            val value = valueAnimator.animatedValue as Int
            val layoutParams = view.layoutParams
            if (layoutParams != null) {
                layoutParams.width = value
                view.requestLayout()
            }
        }
        anim.duration = 100
        view.setTag(R.id.tag_animator, anim)
        anim.start()
    }
    

    override fun dispatchKeyEvent(event: android.view.KeyEvent): Boolean {
        if (event.action == android.view.KeyEvent.ACTION_DOWN) {
            val focused = currentFocus
            if (focused != null && channelRecyclerView.hasFocus()) {
                 if (event.keyCode == android.view.KeyEvent.KEYCODE_DPAD_LEFT) {
                     val layoutManager = channelRecyclerView.layoutManager as? GridLayoutManager
                     if (layoutManager != null) {
                         val focusedChild = layoutManager.findContainingItemView(focused)
                         if (focusedChild != null) {
                             val pos = layoutManager.getPosition(focusedChild)
                             val spanCount = layoutManager.spanCount
                             if (pos % spanCount == 0) {
                                 // Left Edge of Grid -> Focus Categories
                                 categoryRecyclerView.requestFocus()
                                 return true
                             }
                         }
                     }
                 }
            }
             if (focused != null && categoryRecyclerView.hasFocus()) {
                 if (event.keyCode == android.view.KeyEvent.KEYCODE_DPAD_LEFT) {
                      // Left Edge of Cats -> Focus Nav Rail button
                      findViewById<View>(R.id.nav_live).requestFocus()
                      return true
                 }
            }
        }
        return super.dispatchKeyEvent(event)
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
    
    companion object {
        private const val TAG = "EnhancedMainActivity"
    }

    private fun loadPlaylist() {
        android.util.Log.d(TAG, "Starting playlist load")
        loadingView.show()
        errorView.hide()
        
        lifecycleScope.launch {
            try {
                val playlist = playlistRepository.getActivePlaylist()
                
                if (playlist == null) {
                    android.util.Log.d(TAG, "No active playlist found, redirecting to login")
                    navigateToLogin()
                    return@launch
                }
                
                android.util.Log.d(TAG, "Loading playlist: ${playlist.name} (Type: ${playlist.type})")
                
                when (playlist.type) {
                    Constants.PLAYLIST_TYPE_M3U -> loadM3uPlaylist(playlist.m3uUrl!!)
                    Constants.PLAYLIST_TYPE_XTREAM -> loadXtreamPlaylist(
                        playlist.serverUrl!!,
                        playlist.username!!,
                        playlist.password!!
                    )
                }
                
            } catch (e: Exception) {
                android.util.Log.e(TAG, "Error in loadPlaylist: ${e.message}", e)
                showError("Error loading playlist: ${e.message}")
            }
        }
    }
    
    private suspend fun loadM3uPlaylist(url: String) {
        try {
            android.util.Log.d(TAG, "Parsing M3U playlist from: $url")
            val channels = m3uParser.parsePlaylist(url)
            
            android.util.Log.d(TAG, "Parsed ${channels.size} channels")
            
            if (channels.isEmpty()) {
                android.util.Log.w(TAG, "Playlist is empty")
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
            
            android.util.Log.d(TAG, "Successfully saved ${channels.size} channels and ${categories.size} categories")
            
            checkAutoPlay(channelEntities)
            
            loadingView.hide()
            
        } catch (e: Exception) {
            android.util.Log.e(TAG, "Error parsing M3U playlist", e)
            showError("Error parsing playlist: ${e.message}")
        }
    }
    
    private suspend fun loadXtreamPlaylist(serverUrl: String, username: String, password: String) {
        try {
            android.util.Log.d(TAG, "Connecting to Xtream codes server: $serverUrl")
            val api = ApiClient.getXtreamApi(serverUrl)
            
            // Get categories
            // Get categories (Live + VOD + Series)
            val liveCatsDeferred = api.getLiveCategories(username, password)
            val vodCatsResponse = try { api.getVodCategories(username, password) } catch(e: Exception) { null }
            val seriesCatsResponse = try { api.getSeriesCategories(username, password) } catch(e: Exception) { null }
            
            // Get streams (Live + VOD + Series)
            val liveStreamsDeferred = api.getLiveStreams(username, password)
            val vodStreamsResponse = try { api.getVodStreams(username, password) } catch(e: Exception) { null }
            val seriesResponse = try { api.getSeries(username, password) } catch(e: Exception) { null }
            
            val liveCats = liveCatsDeferred.body() ?: emptyList()
            val vodCats = vodCatsResponse?.body() ?: emptyList()
            val seriesCats = seriesCatsResponse?.body() ?: emptyList()
            
            val liveChans = liveStreamsDeferred.body() ?: emptyList()
            val vodChans = vodStreamsResponse?.body() ?: emptyList()
            val series = seriesResponse?.body() ?: emptyList()
            
            val xtreamCategories = liveCats + vodCats + seriesCats
            val xtreamChannels = liveChans + vodChans + series
            
            android.util.Log.d(TAG, "Received ${xtreamCategories.size} categories (Live: ${liveCats.size}, VOD: ${vodCats.size}, Series: ${seriesCats.size}) and ${xtreamChannels.size} items (Live: ${liveChans.size}, VOD: ${vodChans.size}, Series: ${series.size})")
            
            // Convert to entities
            val categoryEntities = xtreamCategories.mapIndexed { index, cat ->
                CategoryEntity(
                    categoryId = cat.categoryId,
                    categoryName = cat.categoryName,
                    sortOrder = index
                )
            }
            
            // Map category IDs to names for easier filtering
            val catMap = xtreamCategories.associate { it.categoryId to it.categoryName }
            
            val channelEntities = xtreamChannels.mapIndexed { index, ch ->
                // Determine format
                val extension = ch.containerExtension ?: "ts"
                val type = ch.streamType ?: "live"
                val streamUrl = when {
                    type == "movie" -> "$serverUrl/movie/$username/$password/${ch.streamId}.$extension"
                    type == "series" -> "$serverUrl/series/$username/$password/${ch.streamId}.$extension" // Or just pass ID for series handling
                    // Check if it looks like a VOD based on extension if type missing?
                    extension != "ts" && extension != "m3u8" -> "$serverUrl/movie/$username/$password/${ch.streamId}.$extension"
                    else -> "$serverUrl/live/$username/$password/${ch.streamId}.$extension"
                }

                ChannelEntity(
                    streamId = ch.streamId.toString(),
                    name = ch.name,
                    streamUrl = streamUrl,
                    logoUrl = ch.streamIcon,
                    category = catMap[ch.categoryId] ?: "Uncategorized", // Use Name, fallback to "Uncategorized"
                    categoryId = ch.categoryId, // Store ID too if needed (added field to Entity recently?)
                    epgChannelId = ch.epgChannelId,
                    sortOrder = index
                )
            }
            
            // Save to database
            channelRepository.clearAllData()
            channelRepository.insertCategories(categoryEntities)
            channelRepository.insertChannels(channelEntities)
            
            android.util.Log.d(TAG, "Successfully saved Xtream data")
            
            checkAutoPlay(channelEntities)
            
            loadingView.hide()
            
        } catch (e: Exception) {
            android.util.Log.e(TAG, "Error loading Xtream playlist", e)
            showError("Error loading Xtream playlist: ${e.message}")
        }
    }
    
    private fun updateCategoryDisplay(categories: List<CategoryEntity>) {
        val mode = currentMode
        
        lifecycleScope.launch(kotlinx.coroutines.Dispatchers.Default) {
            // Filter categories based on mode
            val filteredCategories = when (mode) {
                "movies" -> categories.filter { it.categoryName.contains("movie", true) || it.categoryName.contains("cinema", true) || it.categoryName.contains("vod", true) || it.categoryName.contains("film", true) }
                "shows" -> categories.filter { it.categoryName.contains("series", true) || it.categoryName.contains("show", true) || it.categoryName.contains("season", true) }
                "recordings" -> emptyList()
                else -> categories
            }
    
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
            
            val displayCategories = listOf(allCategory, favoritesCategory) + filteredCategories
            
            kotlinx.coroutines.withContext(kotlinx.coroutines.Dispatchers.Main) {
                categoryAdapter.submitList(displayCategories)
            }
        }
    }
    
    private fun updateChannelDisplay() {
        // Capture state on main thread
        val query = searchQuery
        val mode = currentMode
        val categoryId = currentCategory
        val currentChannels = allChannels
        val currentCategories = allCategories
        
        val startTime = System.currentTimeMillis()
        android.util.Log.d(TAG, "updateChannelDisplay START: mode=$mode, cat=$categoryId, total=${currentChannels.size}")
        
        lifecycleScope.launch(kotlinx.coroutines.Dispatchers.Default) {
            try {
                // Helper to check if a name matches keywords
                fun matchesKeywords(name: String?, keywords: List<String>): Boolean {
                    return name != null && keywords.any { name.contains(it, true) }
                }
                
                // 1. Search Filter
                if (!query.isNullOrEmpty()) {
                    val q = query.lowercase()
                    val searchResults = currentChannels.filter { 
                        it.name.contains(q, ignoreCase = true) || 
                        (it.category?.contains(q, ignoreCase = true) == true)
                    }
                    kotlinx.coroutines.withContext(kotlinx.coroutines.Dispatchers.Main) {
                        channelAdapter.submitList(searchResults)
                    }
                    return@launch
                }
                
                val movieKeywords = listOf("movie", "cinema", "vod", "film")
                val showKeywords = listOf("series", "show", "season", "tv")
        
                // 2. Get Allowed Category IDs based on the current mode
                val allowedCategoryIds = when (mode) {
                    "movies" -> currentCategories.filter { matchesKeywords(it.categoryName, movieKeywords) }.map { it.categoryId }.toSet()
                    "shows" -> currentCategories.filter { matchesKeywords(it.categoryName, showKeywords) }.map { it.categoryId }.toSet()
                    else -> null
                }
        
                // 3. Filter Channels
                val modeChannels = when (mode) {
                    "movies" -> currentChannels.filter { ch ->
                        // Match if category ID is allowed OR if category string itself (M3U) matches keywords
                        (allowedCategoryIds != null && allowedCategoryIds.contains(ch.category)) || 
                        matchesKeywords(ch.category, movieKeywords)
                    }
                    "shows" -> currentChannels.filter { ch ->
                        (allowedCategoryIds != null && allowedCategoryIds.contains(ch.category)) || 
                        matchesKeywords(ch.category, showKeywords)
                    }
                    "recordings" -> emptyList()
                    else -> currentChannels
                }
        
                val filteredChannels = when (categoryId) {
                    null, "all" -> modeChannels
                    "favorites" -> modeChannels.filter { it.isFavorite }
                    else -> modeChannels.filter { it.category == categoryId || it.categoryId == categoryId }
                }
                
                val diffTime = System.currentTimeMillis() - startTime
                android.util.Log.d(TAG, "updateChannelDisplay FILTERED: ${filteredChannels.size} items in ${diffTime}ms")

                kotlinx.coroutines.withContext(kotlinx.coroutines.Dispatchers.Main) {
                    channelAdapter.submitList(filteredChannels)
                }
            } catch (e: Exception) {
                android.util.Log.e(TAG, "Error filtering channels", e)
            }
        }
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
        try {
            android.util.Log.d(TAG, "playChannel called for: ${channel.name}")
            android.util.Log.d(TAG, "Stream URL: ${channel.streamUrl}")
            android.util.Log.d(TAG, "Channel ID: ${channel.id}, Category: ${channel.category}")
            
            // Save as last channel
            prefsManager.setLastChannelId(channel.streamId)
            
            val intent = Intent(this, PlaybackActivity::class.java).apply {
                putExtra(Constants.EXTRA_CHANNEL_NAME, channel.name)
                putExtra(Constants.EXTRA_CHANNEL_URL, channel.streamUrl)
                putExtra(Constants.EXTRA_CHANNEL_LOGO, channel.logoUrl)
                putExtra("EXTRA_MODE", currentMode)
                putExtra("EXTRA_CATEGORY_ID", currentCategory)
                // Add flag usually helps with context issues, though Activity context is fine
                addFlags(Intent.FLAG_ACTIVITY_NEW_TASK) 
            }
            
            // Log intent extras
            val extras = intent.extras
            if (extras != null) {
                for (key in extras.keySet()) {
                    android.util.Log.d(TAG, "Intent Extra: $key = ${extras.get(key)}")
                }
            }
            
            startActivity(intent)
        } catch (e: Exception) {
            android.util.Log.e(TAG, "CRITICAL ERROR starting PlaybackActivity", e)
            Toast.makeText(this, "Error starting playback: ${e.message}", Toast.LENGTH_LONG).show()
        }
    }
    
    private fun showError(message: String) {
        android.util.Log.e(TAG, "Showing error: $message")
        loadingView.hide()
        errorView.text = message
        errorView.show()
    }
    
    private fun navigateToLogin() {
        val intent = Intent(this, LoginActivity::class.java)
        startActivity(intent)
        finish()
    }
    
    private var lastBackPressTime: Long = 0
    
    override fun onBackPressed() {
        if (prefsManager.isConfirmExit()) {
            val currentTime = System.currentTimeMillis()
            if (currentTime - lastBackPressTime > 2000) {
                lastBackPressTime = currentTime
                Toast.makeText(this, "Press back again to exit", Toast.LENGTH_SHORT).show()
                return
            }
        }
        moveTaskToBack(true)
    }
    
    private fun checkAutoPlay(channels: List<ChannelEntity>) {
        if (prefsManager.isTurnOnLastChannel()) {
            val lastId = prefsManager.getLastChannelId()
            if (lastId != null) {
                android.util.Log.d(TAG, "Checking AutoPlay for ID: $lastId")
                val channel = channels.find { it.streamId == lastId }
                if (channel != null) {
                    android.util.Log.d(TAG, "AutoPlay found channel: ${channel.name}, playing...")
                    playChannel(channel)
                }
            }
        }
    }

    private fun decodeEpgText(text: String?): String {
        if (text.isNullOrEmpty()) return ""
        
        var cleanText = text.trim()
        if (cleanText.startsWith("base64:", ignoreCase = true)) {
            cleanText = cleanText.substring(7).trim()
        }
        
        // Try multiple Base64 variants
        val flags = listOf(
            android.util.Base64.DEFAULT,
            android.util.Base64.URL_SAFE,
            android.util.Base64.NO_WRAP,
            android.util.Base64.NO_PADDING
        )
        
        for (flag in flags) {
            try {
                val decoded = android.util.Base64.decode(cleanText, flag).decodeToString()
                // Check if result looks like a real string (at least 1 alphanumeric or space)
                if (decoded.isNotBlank() && decoded.any { it.isLetterOrDigit() }) {
                    return decoded
                }
            } catch (e: Exception) {
                // Continue to next flag
            }
        }
        
        // If all decoding fails, return original
        return text
    }
}
