package com.bingetv.app.ui.main

import android.content.Intent
import android.os.Bundle
import android.view.View
import android.widget.*
import androidx.appcompat.app.AppCompatActivity
import androidx.lifecycle.lifecycleScope
import androidx.recyclerview.widget.GridLayoutManager
import androidx.recyclerview.widget.LinearLayoutManager
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
import com.bingetv.app.utils.TextUtils
import com.bingetv.app.utils.show
import com.bingetv.app.utils.hide
import com.bingetv.app.utils.dpToPx
import com.bingetv.app.utils.dpToPx
import kotlinx.coroutines.launch
import kotlinx.coroutines.isActive
import androidx.paging.PagingData
import kotlinx.coroutines.flow.collectLatest
import com.bingetv.app.ui.adapters.ChannelPagingAdapter

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
    private lateinit var channelAdapter: ChannelPagingAdapter
    private var pagingJob: kotlinx.coroutines.Job? = null
    
    // Data
    // private var allChannels = listOf<ChannelEntity>() // Removed for Paging
    private var allCategories = listOf<CategoryEntity>()
    private var currentCategory: String? = null
    private var searchQuery: String? = null
    private var currentMode: String = "live"
    
    // Layout Constants (initialized in onCreate)
    private var RAIL_WIDTH_FULL: Int = 0
    private var CAT_WIDTH_FULL: Int = 0
    private var MINI_WIDTH: Int = 0
    private var COLLAPSED_WIDTH: Int = 0
    
    // Focus Persistence
    private var lastFocusedChannelId: Long? = null
    private var lastFocusedCategoryId: String? = null
    private var currentWatchHistory: List<com.bingetv.app.data.database.WatchHistoryEntity> = emptyList()

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
        
        // Debug Focus
        window.decorView.viewTreeObserver.addOnGlobalFocusChangeListener { oldFocus, newFocus ->
            val oldName = try { if (oldFocus != null) resources.getResourceEntryName(oldFocus.id) else "null" } catch (e: Exception) { "no-id" }
            val newName = try { if (newFocus != null) resources.getResourceEntryName(newFocus.id) else "null" } catch (e: Exception) { "no-id" }
            android.util.Log.d("FocusDebug", "Focus changed: $oldName -> $newName")
        }
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
        channelRepository = ChannelRepository(
            database.channelDao(), 
            database.categoryDao(),
            database.watchHistoryDao()
        )
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
            // Wait a bit after startup to avoid competing with main playlist load
            kotlinx.coroutines.delay(5000)
            while (isActive) {
                try {
                    val now = System.currentTimeMillis()
                    // Fetch only programs that are active now or soon (limit to reduce overhead)
                    val programs = database.epgDao().getAllActiveProgramsLimited(now, now + 8 * 3600 * 1000) // next 8 hours
                    
                    if (programs.isNotEmpty()) {
                        val map = programs.groupBy { it.channelId }
                        kotlinx.coroutines.withContext(kotlinx.coroutines.Dispatchers.Main) {
                            if (::channelAdapter.isInitialized) {
                                channelAdapter.submitEpgData(map)
                            }
                        }
                    }
                } catch (e: Exception) {
                    android.util.Log.e(TAG, "EPG Refresher error", e)
                }
                kotlinx.coroutines.delay(120000) // Increase to 2 minutes to reduce DB pressure
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
        categoryAdapter = CategoryAdapter(
            onCategoryClick = { category ->
                onCategorySelected(category)
            },
            onCategoryFocused = { category ->
                lastFocusedCategoryId = category.categoryId
            }
        )
        categoryRecyclerView.apply {
            adapter = categoryAdapter
            layoutManager = androidx.recyclerview.widget.LinearLayoutManager(this@EnhancedMainActivity)
        }
        
        // Channel grid
        val gridColumns = prefsManager.getGridColumns()
        channelAdapter = ChannelPagingAdapter(
            onChannelClick = { channel ->
                playChannel(channel)
            },
            onChannelLongClick = { channel ->
                showChannelContextMenu(channel)
            },
            onChannelFocused = { channel ->
                lastFocusedChannelId = channel.id
                loadPreview(channel)
            }
        )
        channelRecyclerView.apply {
            adapter = channelAdapter
            layoutManager = GridLayoutManager(this@EnhancedMainActivity, gridColumns)
        }
    }
    
    private var previewJob: kotlinx.coroutines.Job? = null
    private var autoPlayJob: kotlinx.coroutines.Job? = null

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
                             val api = ApiClient.getXtreamApi(playlist.serverUrl!!)
                             val streamIdInt = channel.streamId.toIntOrNull() ?: 0
                             
                             if (currentMode == "movies") {
                                 previewEpgNow.text = "Fetching Movie Info..."
                                 val response = api.getStreamInfo(playlist.username!!, playlist.password!!, vodId = streamIdInt)
                                 if (response.isSuccessful && response.body()?.movieData != null) {
                                      val movie = response.body()!!.movieData!!
                                      val plot = TextUtils.decodeText(movie.plot ?: movie.description).ifEmpty { "No Plot Available" }
                                      val cast = TextUtils.decodeText(movie.cast).ifEmpty { "Unknown" }
                                      val rating = TextUtils.decodeText(movie.rating ?: "N/A")
                                      val year = TextUtils.decodeText(movie.year ?: "")
                                      
                                      previewEpgNow.text = plot
                                      previewEpgNext.text = "Year: $year | IMDB: $rating\nCast: $cast"
                                     previewEpgNext.visibility = View.VISIBLE
                                     previewEpgProgress.visibility = View.GONE
                                     return@launch // Skip further EPG processing
                                 }
                              } else if (currentMode == "shows") {
                                  previewEpgNow.text = "Fetching Series Info..."
                                  val response = api.getSeriesInfo(playlist.username!!, playlist.password!!, seriesId = streamIdInt.toString())
                                  if (response.isSuccessful && response.body() != null) {
                                      val jsonString = response.body()!!.string()
                                      val gson = com.google.gson.Gson()
                                      var series: com.bingetv.app.data.api.SeriesInfo? = null
                                      try {
                                          val data = gson.fromJson(jsonString, com.bingetv.app.data.api.XtreamSeriesInfo::class.java)
                                          series = data.info
                                      } catch (e: Exception) {
                                          try {
                                               val listType = object : com.google.gson.reflect.TypeToken<List<com.bingetv.app.data.api.XtreamSeriesInfo>>() {}.type
                                               val list = gson.fromJson<List<com.bingetv.app.data.api.XtreamSeriesInfo>>(jsonString, listType)
                                               if (!list.isNullOrEmpty()) series = list[0].info
                                          } catch (e2: Exception) {}
                                      }

                                      if (series != null) {
                                          val decodedPlot = TextUtils.decodeText(series.plot).ifEmpty { "No Plot Available" }
                                          val decodedCast = TextUtils.decodeText(series.cast ?: "Unknown")
                                          val decodedRating = TextUtils.decodeText(series.rating ?: "N/A")
                                          val decodedRelease = TextUtils.decodeText(series.releaseDate ?: "")
                                          
                                          previewEpgNow.text = decodedPlot
                                          previewEpgNext.text = "Release: $decodedRelease | IMDB: $decodedRating\nCast: $decodedCast"
                                          previewEpgNext.visibility = View.VISIBLE
                                          previewEpgProgress.visibility = View.GONE
                                          return@launch
                                      }
                                  }
                              }
 else if (streamIdInt != 0) {
                                previewEpgNow.text = "Loading EPG..."
                                val response = api.getShortEpg(playlist.username!!, playlist.password!!, streamId = streamIdInt)
                                if (response.isSuccessful && response.body() != null) {
                                    val listings = response.body()!!.values.flatten()
                                    if (listings.isNotEmpty()) {
                                        val entities = listings.map { 
                                            com.bingetv.app.data.database.EpgProgramEntity(
                                                channelId = channel.epgChannelId ?: channel.streamId,
                                                title = TextUtils.decodeText(it.title),
                                                description = TextUtils.decodeText(it.description),
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
                    
                    if (current != null) {
                        previewEpgNow.text = TextUtils.decodeText(current.title)
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
                    
                    if (epgPrograms.isNotEmpty()) {
                        val futurePrograms = epgPrograms.filter { it.startTime > now }
                            .sortedBy { it.startTime }
                            .take(10)
                        
                        if (futurePrograms.isNotEmpty()) {
                            val timeFormat = java.text.SimpleDateFormat("HH:mm", java.util.Locale.getDefault())
                            val sb = StringBuilder()
                            futurePrograms.forEach { prog ->
                                sb.append("${timeFormat.format(java.util.Date(prog.startTime))} - ${TextUtils.decodeText(prog.title)}\n")
                            }
                            previewEpgNext.text = sb.toString().trim()
                            previewEpgNext.visibility = View.VISIBLE
                        } else {
                            previewEpgNext.visibility = View.GONE
                        }
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
                
                // 4. AUTO-PLAY TIMER: If they stay for X seconds, play full screen
                autoPlayJob?.cancel()
                autoPlayJob = lifecycleScope.launch {
                    kotlinx.coroutines.delay(30000) // 30s wait as requested
                    android.util.Log.d(TAG, "Auto-playing channel after dwell: ${channel.name}")
                    playChannel(channel)
                }
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
        navHistory.setOnClickListener { showHistory() }
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
        // Observe channel data - REMOVED for Paging
        // channelRepository.allChannels.observe(this) { ... }
        // Instead we rely on updateChannelDisplay() calling Pager logic
        
        channelRepository.allCategories.observe(this) { categories ->
            allCategories = categories
            updateDisplay()
        }

        channelRepository.watchHistory.observe(this) { history ->
            currentWatchHistory = history
            if (currentCategory == "history") {
                updateChannelDisplay()
            }
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
                            // Focus in Grid -> Hide Rail, Hide Cats
                            if (navRail.width != COLLAPSED_WIDTH || catRecycler.width != COLLAPSED_WIDTH) {
                                kotlinx.coroutines.delay(50)
                                animateViewWidth(navRail, COLLAPSED_WIDTH)
                                animateViewWidth(catRecycler, COLLAPSED_WIDTH)
                            }
                        }
                        isInside(newFocus, catRecycler) -> {
                            // Focus in Cats -> Peek Rail, Show Cats
                            if (catRecycler.width != CAT_WIDTH_FULL) {
                                animateViewWidth(navRail, MINI_WIDTH)
                                animateViewWidth(catRecycler, CAT_WIDTH_FULL)
                            }
                        }
                        isInside(newFocus, navRail) -> {
                            // Focus in Nav -> Show Rail, Show Cats
                            // GUARD: If we came directly from Grid (skipped cats), this might be a reset glitch.
                            // Only expand if we were previously in Categories or are explicitly focused on a nav item
                            
                            val isResetGlitch = chanRecycler.hasFocus() // If Recycler THINKS it has focus but global says Nav
                            if (!isResetGlitch) {
                                 animateViewWidth(navRail, RAIL_WIDTH_FULL)
                                 animateViewWidth(catRecycler, CAT_WIDTH_FULL)
                            }
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
            
            // 1. Sidebar -> Categories (Force logic)
            if (focused != null && isViewInSidebar(focused)) {
                if (event.keyCode == android.view.KeyEvent.KEYCODE_DPAD_RIGHT) {
                    android.util.Log.d(TAG, "Sidebar -> Forcing Focus to Categories")
                    categoryRecyclerView.requestFocus()
                    return true
                }
            }

            // 2. Grid (Left Edge) -> Categories
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
                                 android.util.Log.d(TAG, "Navigating LEFT from Grid to Categories")
                                 categoryRecyclerView.requestFocus()
                                 
                                 // Extra assurance: If the recycler didn't pick up focus, force it onto the first visible child
                                 if (!categoryRecyclerView.hasFocus()) {
                                     categoryRecyclerView.getChildAt(0)?.requestFocus()
                                 }
                                 return true
                             }
                         }
                     }
                 }
            }
            
            // 3. Categories (Left Edge) -> Sidebar
            if (focused != null && categoryRecyclerView.hasFocus()) {
                 if (event.keyCode == android.view.KeyEvent.KEYCODE_DPAD_LEFT) {
                      // Left Edge of Cats -> Focus current mode button in rail
                      android.util.Log.d(TAG, "Navigating LEFT from Categories to Rail")
                      val railId = when(currentMode) {
                          "movies" -> R.id.nav_movies
                          "shows" -> R.id.nav_shows
                          "history" -> R.id.nav_history
                          "favorites" -> R.id.nav_favorites
                          else -> R.id.nav_live
                      }
                      findViewById<View>(railId).requestFocus()
                      return true
                 }
                 
                 // 4. Categories (Bottom Edge) -> Guard
                 if (event.keyCode == android.view.KeyEvent.KEYCODE_DPAD_DOWN) {
                     val lm = categoryRecyclerView.layoutManager as? LinearLayoutManager
                     if (lm != null) {
                         val focusedChild = lm.findContainingItemView(focused)
                         if (focusedChild != null) {
                             val pos = lm.getPosition(focusedChild)
                             if (pos == (categoryAdapter.itemCount - 1)) {
                                 android.util.Log.d(TAG, "Categories DOWN Guard - Staying in list")
                                 return true // Consume to prevent jumping to grid
                             }
                         }
                     }
                 }
            }
        }
        return super.dispatchKeyEvent(event)
    }

    private fun isViewInSidebar(view: View): Boolean {
        var p = view.parent
        while (p != null) {
            if (p is View && (p as View).id == R.id.nav_rail) return true
            p = p.parent
        }
        return false
    }
    
    private fun showSearchDialog() {
        val input = EditText(this)
        input.inputType = android.text.InputType.TYPE_CLASS_TEXT
        input.hint = "Search channel..."
        input.setPadding(40, 40, 40, 40)
        
        android.app.AlertDialog.Builder(this)
            .setTitle("Search")
            .setView(input)
            .setPositiveButton("Search") { _, _ ->
                 searchQuery = input.text.toString()
                 updateDisplay()
            }
            .setNegativeButton("Clear") { _, _ ->
                 searchQuery = null
                 updateDisplay()
            }
            .setNeutralButton("Cancel", null)
            .show()
        
        input.requestFocus()
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
                
                // Optimization: Check if data already exists for this playlist
                val lastLoadedId = prefsManager.getLastLoadedPlaylistId()
                val channelCount = kotlinx.coroutines.withContext(kotlinx.coroutines.Dispatchers.IO) {
                    channelRepository.getChannelCount()
                }
                
                if (playlist.id == lastLoadedId && channelCount > 0 && false) { // FORCED REFRESH FOR DEBUGGING
                    android.util.Log.d(TAG, "Playlist ${playlist.id} data already loaded ($channelCount channels). Skipping fetch.")
                    updateDisplay()
                    loadingView.hide()
                    return@launch
                }

                when (playlist.type) {
                    Constants.PLAYLIST_TYPE_M3U -> loadM3uPlaylist(playlist.m3uUrl!!, playlist.id)
                    Constants.PLAYLIST_TYPE_XTREAM -> loadXtreamPlaylist(
                        playlist.serverUrl!!,
                        playlist.username!!,
                        playlist.password!!,
                        playlist.id
                    )
                }
                
            } catch (e: Exception) {
                android.util.Log.e(TAG, "Error in loadPlaylist: ${e.message}", e)
                showError("Error loading playlist: ${e.message}")
            }
        }
    }
    
    private suspend fun loadM3uPlaylist(url: String, playlistId: Long) {
        try {
            // Perform parsing and entity conversion on background thread
            kotlinx.coroutines.withContext(kotlinx.coroutines.Dispatchers.Default) {
                android.util.Log.d(TAG, "Parsing M3U playlist from: $url")
                val channels = m3uParser.parsePlaylist(url)
                
                android.util.Log.d(TAG, "Parsed ${channels.size} channels")
                
                if (channels.isEmpty()) {
                    kotlinx.coroutines.withContext(kotlinx.coroutines.Dispatchers.Main) {
                        android.util.Log.w(TAG, "Playlist is empty")
                        showError("No channels found")
                    }
                    return@withContext
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
                
                prefsManager.setLastLoadedPlaylistId(playlistId)
                
                android.util.Log.d(TAG, "Successfully saved ${channels.size} channels and ${categories.size} categories")
                
                // Switch back to Main for UI updates if needed (checkAutoPlay might touch UI?)
                kotlinx.coroutines.withContext(kotlinx.coroutines.Dispatchers.Main) {
                     checkAutoPlay(channelEntities)
                     loadingView.hide()
                }
            }
            
        } catch (e: Exception) {
            android.util.Log.e(TAG, "Error parsing M3U playlist", e)
            showError("Error parsing playlist: ${e.message}")
        }
    }
    
    private suspend fun loadXtreamPlaylist(serverUrl: String, username: String, password: String, playlistId: Long) {
        try {
            // Move heavy fetching and parsing to IO/Default thread
            try {
                kotlinx.coroutines.withContext(kotlinx.coroutines.Dispatchers.IO) {
                    android.util.Log.d(TAG, "Connecting to Xtream codes server: $serverUrl")
                    val api = ApiClient.getXtreamApi(serverUrl)
                    
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
                     
                     // DEBUG: Log series IDs to verify matching
                     try {
                         val rawResponse = api.getSeriesRaw(username, password)
                         if (rawResponse.isSuccessful && rawResponse.body() != null) {
                             val json = rawResponse.body()!!.string()
                             android.util.Log.d(TAG, "RAW SERIES LIST (first 2000): ${json.take(2000)}")
                         }
                     } catch(e: Exception) {
                         android.util.Log.e(TAG, "Failed to log raw series", e)
                     }
                     
                     val xtreamCategories = liveCats + vodCats + seriesCats
                    val xtreamChannels = liveChans + vodChans + series
                    
                    android.util.Log.d(TAG, "Received ${xtreamCategories.size} categories and ${xtreamChannels.size} items")
                    
                    // CPU intensive mapping
                    val categoryEntities = xtreamCategories.mapIndexed { index, cat ->
                        CategoryEntity(
                            categoryId = cat.categoryId,
                            categoryName = cat.categoryName,
                            sortOrder = index
                        )
                    }
                    
                    val channelEntities = xtreamChannels.mapIndexed { index, ch ->
                        val extension = ch.containerExtension ?: "ts"
                        val type = ch.streamType ?: "live"
                        val streamUrl = when {
                            type == "movie" -> "$serverUrl/movie/$username/$password/${ch.streamId}.$extension"
                            type == "series" -> "$serverUrl/series/$username/$password/${ch.streamId}.$extension"
                            else -> "$serverUrl/$username/$password/${ch.streamId}"
                        }
                        
                        ChannelEntity(
                            streamId = ch.streamId.toString(),
                            name = ch.name,
                            streamUrl = streamUrl,
                            logoUrl = ch.streamIcon,
                            category = ch.categoryId, // FIX: Populate category column for DAO filtering
                            categoryId = ch.categoryId,
                            epgChannelId = ch.epgChannelId,
                            sortOrder = index
                        )
                    }
                    
                    // Database operations
                    channelRepository.clearAllData()
                    channelRepository.insertChannels(channelEntities)
                    channelRepository.insertCategories(categoryEntities)
                    
                    prefsManager.setLastLoadedPlaylistId(playlistId)
                    
                    android.util.Log.d(TAG, "Saved Extream playlist data")
                    
                    // UI Updates back on Main
                    kotlinx.coroutines.withContext(kotlinx.coroutines.Dispatchers.Main) {
                        checkAutoPlay(channelEntities)
                        loadingView.hide()
                    }
                }
            } catch (e: Exception) {
               throw e // Rethrow to be caught by outer try/catch
            }
            
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
            
            val historyCategory = CategoryEntity(
                categoryId = "history",
                categoryName = "History",
                sortOrder = -1 // Between Favorites and All
            )
            
            val displayCategories = listOf(allCategory, historyCategory, favoritesCategory) + filteredCategories
            
            kotlinx.coroutines.withContext(kotlinx.coroutines.Dispatchers.Main) {
                categoryAdapter.submitList(displayCategories) {
                    val targetId = lastFocusedCategoryId ?: return@submitList
                    val position = displayCategories.indexOfFirst { it.categoryId == targetId }
                    if (position != -1) {
                         categoryRecyclerView.postDelayed({
                             val holder = categoryRecyclerView.findViewHolderForAdapterPosition(position)
                             holder?.itemView?.requestFocus()
                         }, 100)
                    }
                }
            }
        }
    }
    
    private fun updateChannelDisplay() {
        val query = searchQuery
        val mode = currentMode
        val categoryId = currentCategory
        
        // Cancel previous paging job
        pagingJob?.cancel()
        
        pagingJob = lifecycleScope.launch {
             // Determine paging source "mode" string
             val pagingMode = if (!query.isNullOrEmpty()) {
                 "search:$query"
             } else if (categoryId == "favorites") {
                 "favorites"
             } else if (categoryId == "history") {
                 "history" // Need special handling or Repo support
             } else if (categoryId == "all" || categoryId == null) {
                 // Current Repo `getChannelsPaged` handles: "all", "favorites", "search:", else category.
                 // It misses "movies", "shows" filtering which was done in memory.
                 // Ideally we need getChannelsInternalByType(type).
                 // For now, if mode is movies/shows, we might be showing ALL unless we fix Repo logic.
                 // Let's assume for now user selects a category.
                 if (mode == "live") "all" else mode
             } else {
                 categoryId // Specific category
             }

             if (pagingMode == "history") {
                 // Manual history load
                 channelRepository.watchHistory.value?.let { history ->
                     val ids = history.map { it.streamId }
                     val channels = channelRepository.getChannelsByStreamIds(ids)
                     // Sort by history order
                     val sorted = history.mapNotNull { h -> channels.find { it.streamId == h.streamId } }
                     channelAdapter.submitData(PagingData.from(sorted))
                 }
             } else {
                 channelRepository.getChannelsPaged(pagingMode).collectLatest { pagingData ->
                     channelAdapter.submitData(pagingData)
                 }
             }
        }
    }
    
    private fun onCategorySelected(category: CategoryEntity) {
        currentCategory = category.categoryId
        updateChannelDisplay()
    }
    
    private fun showFavorites() {
        currentCategory = "favorites"
        updateDisplay()
    }
    
    private fun showHistory() {
        currentCategory = "history"
        updateDisplay()
    }
    
    private fun playChannel(channel: ChannelEntity) {
        autoPlayJob?.cancel()
        lifecycleScope.launch {
            channelRepository.recordHistory(channel.streamId)
        }
        
        // --- NEW: Series Handling ---
        if (currentMode == "shows") {
             try {
                 val seriesId = channel.streamId.toIntOrNull()
                 if (seriesId != null) {
                     val intent = Intent(this, com.bingetv.app.ui.details.SeriesDetailActivity::class.java).apply {
                         putExtra(com.bingetv.app.ui.details.SeriesDetailActivity.EXTRA_SERIES_ID, seriesId)
                         putExtra(com.bingetv.app.ui.details.SeriesDetailActivity.EXTRA_SERIES_NAME, channel.name)
                         putExtra(com.bingetv.app.ui.details.SeriesDetailActivity.EXTRA_SERIES_POSTER, channel.logoUrl)
                     }
                     startActivity(intent)
                     return
                 }
             } catch (e: Exception) {
                 android.util.Log.e(TAG, "Error parsing Series ID", e)
             }
        }
        // -----------------------------
        
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
                    @Suppress("DEPRECATION")
                    val value = extras.get(key)
                    android.util.Log.d(TAG, "Intent Extra: $key = $value")
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

}
