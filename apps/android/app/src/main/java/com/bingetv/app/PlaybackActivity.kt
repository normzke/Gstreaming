package com.bingetv.app

import android.net.Uri
import android.os.Bundle
import android.view.View
import android.widget.ProgressBar
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import androidx.lifecycle.lifecycleScope
import com.bingetv.app.utils.Constants
import com.google.android.exoplayer2.ExoPlayer
import com.google.android.exoplayer2.MediaItem
import com.google.android.exoplayer2.PlaybackException
import com.google.android.exoplayer2.Player
import com.google.android.exoplayer2.ui.StyledPlayerView
import kotlinx.coroutines.launch
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.withContext
import com.google.android.exoplayer2.C
import com.google.android.exoplayer2.ui.TrackSelectionDialogBuilder

class PlaybackActivity : AppCompatActivity() {

    private lateinit var playerView: StyledPlayerView
    private lateinit var loadingIndicator: ProgressBar
    private var player: ExoPlayer? = null
    
    // State
    private var channelName: String? = null
    private var channelUrl: String? = null
    private var filterMode: String? = null
    private var filterCategoryId: String? = null
    
    // Overlay
    private lateinit var overlayContainer: android.view.ViewGroup
    private lateinit var overlayRecyclerView: androidx.recyclerview.widget.RecyclerView
    private lateinit var channelAdapter: com.bingetv.app.ui.adapters.ChannelGridAdapter
    private var allChannels = listOf<com.bingetv.app.data.database.ChannelEntity>()
    private lateinit var tracksButton: android.widget.Button
    
    // Dependencies
    private lateinit var database: com.bingetv.app.data.database.BingeTVDatabase
    private lateinit var repository: com.bingetv.app.data.repository.ChannelRepository
    private lateinit var prefsManager: com.bingetv.app.utils.PreferencesManager

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_playback)
        
        // Initialize DB
        database = com.bingetv.app.data.database.BingeTVDatabase.getDatabase(this)
        repository = com.bingetv.app.data.repository.ChannelRepository(database.channelDao(), database.categoryDao())
        
        prefsManager = com.bingetv.app.utils.PreferencesManager(this)

        // Retrieve Intent Extras
        channelName = intent.getStringExtra(Constants.EXTRA_CHANNEL_NAME)
        channelUrl = intent.getStringExtra(Constants.EXTRA_CHANNEL_URL)
        filterMode = intent.getStringExtra("EXTRA_MODE")
        filterCategoryId = intent.getStringExtra("EXTRA_CATEGORY_ID")
        
        android.util.Log.d(TAG, "onCreate: Channel=$channelName, Url=$channelUrl, Mode=$filterMode, Cat=$filterCategoryId")

        if (channelUrl.isNullOrEmpty()) {
            Toast.makeText(this, "Error: Invalid Channel URL", Toast.LENGTH_LONG).show()
            finish()
            return
        }

        playerView = findViewById(R.id.player_view)
        loadingIndicator = findViewById(R.id.loading_indicator)
        overlayContainer = findViewById(R.id.overlay_container)
        overlayRecyclerView = findViewById(R.id.overlay_recycler_view)
        tracksButton = findViewById(R.id.btn_tracks)
        
        tracksButton.setOnClickListener {
            showTracksMenu()
        }
        
        // Configure UI controls
        playerView.setShowSubtitleButton(true)
        playerView.setShowNextButton(true)
        playerView.setShowPreviousButton(true)
        playerView.setShowBuffering(StyledPlayerView.SHOW_BUFFERING_ALWAYS)
        
        // Enable settings button (includes track selection)
        // Note: setShowSettingsButton(true) might not exist in 2.18.7 directly on StyledPlayerView
        // but it can be enabled via the controller.
        
        setupOverlay()
        
        // Hide system UI for fullscreen
        hideSystemUi()
    }
    
    private fun setupOverlay() {
        channelAdapter = com.bingetv.app.ui.adapters.ChannelGridAdapter(
            onChannelClick = { channel ->
                switchChannel(channel)
            },
            onChannelLongClick = null,
            onChannelFocused = null
        )
        
        overlayRecyclerView.adapter = channelAdapter
        overlayRecyclerView.layoutManager = androidx.recyclerview.widget.LinearLayoutManager(this)
        
        // Load channels
        lifecycleScope.launch(Dispatchers.IO) {
            // Fetch categories first if needed for keyword matching, but for speed we'll use observing allChannels and filtering in memory
            // Ideally we should observe a filtered query, but keeping it simple for now as per previous logic
             val allCats = database.categoryDao().getAllCategoriesSync()
             
             withContext(Dispatchers.Main) {
                repository.allChannels.observe(this@PlaybackActivity) { channels ->
                    val filtered = filterChannels(channels, allCats)
                    allChannels = filtered
                    channelAdapter.submitList(filtered)
                }
             }
        }
    }
    
    private fun filterChannels(channels: List<com.bingetv.app.data.database.ChannelEntity>, categories: List<com.bingetv.app.data.database.CategoryEntity>): List<com.bingetv.app.data.database.ChannelEntity> {
        if (filterMode == null) return channels
        
        val movieKeywords = listOf("movie", "cinema", "vod", "film")
        val showKeywords = listOf("series", "show", "season", "tv")
        
        // Helper
        fun matchesKeywords(name: String?, keywords: List<String>): Boolean {
            return name != null && keywords.any { name.contains(it, true) }
        }

        val allowedCategoryIds = when (filterMode) {
             "movies" -> categories.filter { matchesKeywords(it.categoryName, movieKeywords) }.map { it.categoryId }.toSet()
             "shows" -> categories.filter { matchesKeywords(it.categoryName, showKeywords) }.map { it.categoryId }.toSet()
             else -> null
        }

        val modeChannels = when (filterMode) {
             "movies" -> channels.filter { ch -> 
                 (allowedCategoryIds != null && allowedCategoryIds.contains(ch.category)) || 
                 matchesKeywords(ch.category, movieKeywords) 
             }
             "shows" -> channels.filter { ch -> 
                 (allowedCategoryIds != null && allowedCategoryIds.contains(ch.category)) || 
                 matchesKeywords(ch.category, showKeywords) 
             }
             // Live/Recordings logic skipped for brevity, defaults to all if not matched
             else -> channels
        }
        
        // Category Filter
        return if (filterCategoryId != null && filterCategoryId != "all" && filterCategoryId != "favorites") {
             modeChannels.filter { it.category == filterCategoryId || it.categoryId == filterCategoryId }
        } else {
             modeChannels
        }
    }
    
    private fun switchChannel(channel: com.bingetv.app.data.database.ChannelEntity) {
        channelName = channel.name
        channelUrl = channel.streamUrl
        
        Toast.makeText(this, "Switching to: ${channel.name}", Toast.LENGTH_SHORT).show()
        
        val mediaItem = MediaItem.Builder()
            .setUri(Uri.parse(channelUrl))
            .build()
            
        player?.setMediaItem(mediaItem)
        player?.prepare()
        player?.play()
        
        // Close overlay? OR keep it open? Tivimate keeps it open usually until back.
        // I'll keep it open for "Preview" browsing feel.
    }
    
    private fun toggleOverlay() {
        if (overlayContainer.visibility == View.VISIBLE) {
            overlayContainer.visibility = View.GONE
            playerView.useController = true
            hideSystemUi()
            // Focus back to player
            playerView.requestFocus()
        } else {
            overlayContainer.visibility = View.VISIBLE
            playerView.useController = false // Hide player controls when overlay is active
            overlayRecyclerView.requestFocus()
        }
    }

    override fun onStart() {
        super.onStart()
        if (player == null) {
            initializePlayer()
        }
    }
    
    // ... [onResume, onPause, onStop remain same] ...

    // D-pad Support
    override fun dispatchKeyEvent(event: android.view.KeyEvent): Boolean {
        if (event.action == android.view.KeyEvent.ACTION_DOWN) {
            when (event.keyCode) {
                android.view.KeyEvent.KEYCODE_MENU -> {
                    toggleOverlay()
                    return true
                }
                android.view.KeyEvent.KEYCODE_BACK -> {
                    if (overlayContainer.visibility == View.VISIBLE) {
                        toggleOverlay()
                        return true
                    }
                }
                android.view.KeyEvent.KEYCODE_DPAD_CENTER, android.view.KeyEvent.KEYCODE_ENTER -> {
                    if (!playerView.isControllerFullyVisible) {
                        playerView.showController()
                        return true
                    }
                    return playerView.dispatchKeyEvent(event)
                }
                
                // D-pad Up/Down
                android.view.KeyEvent.KEYCODE_DPAD_UP -> {
                    if (overlayContainer.visibility == View.VISIBLE) {
                        return super.dispatchKeyEvent(event)
                    }
                    if (playerView.isControllerFullyVisible) {
                        return playerView.dispatchKeyEvent(event)
                    }
                    val action = prefsManager.getRemoteUpDownAction()
                    if (action == "channel") { 
                        switchNextChannel()
                        return true 
                    }
                    return playerView.dispatchKeyEvent(event)
                }
                android.view.KeyEvent.KEYCODE_DPAD_DOWN -> {
                    if (overlayContainer.visibility == View.VISIBLE) {
                        return super.dispatchKeyEvent(event)
                    }
                    if (playerView.isControllerFullyVisible) {
                        return playerView.dispatchKeyEvent(event)
                    }
                    val action = prefsManager.getRemoteUpDownAction()
                    if (action == "channel") { 
                        switchPrevChannel()
                        return true 
                    }
                    return playerView.dispatchKeyEvent(event)
                }
                
                // D-pad Left/Right
                android.view.KeyEvent.KEYCODE_DPAD_LEFT -> {
                    if (overlayContainer.visibility == View.VISIBLE) {
                        return super.dispatchKeyEvent(event)
                    }
                    if (playerView.isControllerFullyVisible) {
                        return playerView.dispatchKeyEvent(event)
                    }
                    val action = prefsManager.getRemoteLeftRightAction()
                    if (action == "channel") { 
                        switchPrevChannel()
                        return true 
                    }
                    return playerView.dispatchKeyEvent(event)
                }
                android.view.KeyEvent.KEYCODE_DPAD_RIGHT -> {
                    if (overlayContainer.visibility == View.VISIBLE) {
                        return super.dispatchKeyEvent(event)
                    }
                    if (playerView.isControllerFullyVisible) {
                        return playerView.dispatchKeyEvent(event)
                    }
                    val action = prefsManager.getRemoteLeftRightAction()
                    if (action == "channel") { 
                        switchNextChannel()
                        return true 
                    }
                    return playerView.dispatchKeyEvent(event)
                }
                
                // Always handle Media Keys
                android.view.KeyEvent.KEYCODE_MEDIA_NEXT, android.view.KeyEvent.KEYCODE_CHANNEL_UP -> {
                    switchNextChannel()
                    return true
                }
                android.view.KeyEvent.KEYCODE_MEDIA_PREVIOUS, android.view.KeyEvent.KEYCODE_CHANNEL_DOWN -> {
                    switchPrevChannel()
                    return true
                }
            }
        }
        
        // Pass events to player view as fallback
        return super.dispatchKeyEvent(event)
    }

    override fun onUserLeaveHint() {
        super.onUserLeaveHint()
        if (android.os.Build.VERSION.SDK_INT >= android.os.Build.VERSION_CODES.O) {
            if (prefsManager.isPipOnHome()) {
                // Need to ensure player is playing
                if (player != null && player?.isPlaying == true) {
                     enterPictureInPictureMode(android.app.PictureInPictureParams.Builder().build())
                }
            }
        }
    }

    private fun switchNextChannel() {
        if (allChannels.isEmpty()) return
        val currentIndex = allChannels.indexOfFirst { it.streamUrl == channelUrl }
        if (currentIndex != -1 && currentIndex < allChannels.size - 1) {
            switchChannel(allChannels[currentIndex + 1])
        } else if (currentIndex != -1) {
             // Loop?
             switchChannel(allChannels[0])
        }
    }
    
    private fun switchPrevChannel() {
        if (allChannels.isEmpty()) return
        val currentIndex = allChannels.indexOfFirst { it.streamUrl == channelUrl }
        if (currentIndex > 0) {
            switchChannel(allChannels[currentIndex - 1])
        } else if (currentIndex != -1) {
            switchChannel(allChannels[allChannels.size - 1])
        }
    }

    override fun onResume() {
        super.onResume()
        hideSystemUi()
        if (player == null) {
            initializePlayer()
        }
        player?.play()
    }

    override fun onPause() {
        super.onPause()
        player?.pause()
    }

    override fun onStop() {
        super.onStop()
        releasePlayer()
    }
    
    private fun initializePlayer() {
        try {
            android.util.Log.d(TAG, "Initializing Player with Robust Config")
            
            val userAgent = prefsManager.getUserAgent().ifEmpty { "TiviMate/4.7.0" } // Better compatibility
            val httpDataSourceFactory = com.google.android.exoplayer2.upstream.DefaultHttpDataSource.Factory()
                .setUserAgent(userAgent)
                .setAllowCrossProtocolRedirects(true)
            
            // 2. Renderers Factory
            val decoderPref = prefsManager.getVideoDecoder() // "hardware" or "software"
            val extensionMode = if (decoderPref == "software") {
                com.google.android.exoplayer2.DefaultRenderersFactory.EXTENSION_RENDERER_MODE_PREFER
            } else {
                com.google.android.exoplayer2.DefaultRenderersFactory.EXTENSION_RENDERER_MODE_ON
            }
            
            val renderersFactory = com.google.android.exoplayer2.DefaultRenderersFactory(this)
                .setExtensionRendererMode(extensionMode)
            
            // 3. Performance-Focused Load Control
            val bufferSize = prefsManager.getBufferSize()
            val (minBuffer, maxBuffer) = when (bufferSize) {
                "none" -> 2000 to 5000
                "small" -> 15000 to 30000
                "large" -> 120000 to 240000
                else -> 45000 to 90000 // default aggressive
            }

            val loadControl = com.google.android.exoplayer2.DefaultLoadControl.Builder()
                .setBufferDurationsMs(
                    minBuffer, 
                    maxBuffer, 
                    if (bufferSize == "none") 1500 else 3000, // minPlaybackStartBuffer
                    if (bufferSize == "none") 3000 else 6000  // minPlaybackAfterRebuffer
                )
                .setPrioritizeTimeOverSizeThresholds(true)
                .build()
                
            player = ExoPlayer.Builder(this, renderersFactory)
                .setMediaSourceFactory(com.google.android.exoplayer2.source.DefaultMediaSourceFactory(httpDataSourceFactory))
                .setLoadControl(loadControl)
                .build()
            
            playerView.player = player
            
            // Listener for loading state / errors
            player?.addListener(object : Player.Listener {
                override fun onPlaybackStateChanged(playbackState: Int) {
                    when (playbackState) {
                        Player.STATE_BUFFERING -> loadingIndicator.visibility = View.VISIBLE
                        Player.STATE_READY -> loadingIndicator.visibility = View.GONE
                        Player.STATE_ENDED -> { /* Do nothing for live stream */ }
                        Player.STATE_IDLE -> { /* Setup or error */ }
                    }
                }

                override fun onPlayerError(error: PlaybackException) {
                    android.util.Log.e(TAG, "Player Error: ${error.message}", error)
                    
                    // Auto-retry once for specific errors?
                    if (error.errorCode == PlaybackException.ERROR_CODE_DECODER_INIT_FAILED) {
                        Toast.makeText(this@PlaybackActivity, "Decoder Error - Retrying...", Toast.LENGTH_LONG).show()
                        player?.prepare()
                        player?.play()
                    } else {
                        loadingIndicator.visibility = View.GONE
                        Toast.makeText(this@PlaybackActivity, "Error: ${error.errorCodeName} - ${error.message}", Toast.LENGTH_LONG).show()
                    }
                }
            })

            val mediaItem = MediaItem.Builder()
                .setUri(Uri.parse(channelUrl))
                .build()

            player?.setMediaItem(mediaItem)
            player?.prepare()
            player?.playWhenReady = true
            
        } catch (e: Exception) {
            android.util.Log.e(TAG, "Error initializing player", e)
            Toast.makeText(this, "Player Init Error", Toast.LENGTH_SHORT).show()
        }
    }

    private fun releasePlayer() {
        android.util.Log.d(TAG, "Releasing Player")
        player?.release()
        player = null
    }
    
    // PiP Support


    override fun onPictureInPictureModeChanged(isInPictureInPictureMode: Boolean, newConfig: android.content.res.Configuration) {
        super.onPictureInPictureModeChanged(isInPictureInPictureMode, newConfig)
        if (isInPictureInPictureMode) {
            playerView.useController = false
            loadingIndicator.visibility = View.GONE
        } else {
            playerView.useController = true
            hideSystemUi()
        }
    }
    


    private fun hideSystemUi() {
        playerView.systemUiVisibility = (View.SYSTEM_UI_FLAG_LOW_PROFILE
                or View.SYSTEM_UI_FLAG_FULLSCREEN
                or View.SYSTEM_UI_FLAG_LAYOUT_STABLE
                or View.SYSTEM_UI_FLAG_IMMERSIVE_STICKY
                or View.SYSTEM_UI_FLAG_LAYOUT_HIDE_NAVIGATION
                or View.SYSTEM_UI_FLAG_HIDE_NAVIGATION)
    }

    private fun showTrackSelectionDialog(trackType: Int) {
        if (player == null) return
        
        val builder = TrackSelectionDialogBuilder(
            this,
            if (trackType == C.TRACK_TYPE_AUDIO) "Select Audio Track" else "Select Subtitles",
            player!!,
            trackType
        )
        builder.setAllowAdaptiveSelections(true)
        builder.build().show()
    }

    private fun showTracksMenu() {
        val items = arrayOf("Audio Tracks", "Subtitles")
        androidx.appcompat.app.AlertDialog.Builder(this)
            .setTitle("Playback Options")
            .setItems(items) { _, which ->
                when (which) {
                    0 -> showTrackSelectionDialog(C.TRACK_TYPE_AUDIO)
                    1 -> showTrackSelectionDialog(C.TRACK_TYPE_TEXT)
                }
            }
            .show()
    }

    companion object {
        private const val TAG = "PlaybackActivity"
    }
}
