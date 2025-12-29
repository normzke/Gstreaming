package com.bingetv.app

import android.net.Uri
import android.os.Bundle
import androidx.fragment.app.FragmentActivity
import com.google.android.exoplayer2.ui.PlayerView

class PlaybackActivity : FragmentActivity() {
    private lateinit var playerView: PlayerView
    private lateinit var exoPlayerAdapter: ExoPlayerAdapter
    
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        
        val channelName = intent.getStringExtra("channel_name") ?: "Unknown Channel"
        val channelUrl = intent.getStringExtra("channel_url") ?: return
        
        playerView = PlayerView(this)
        setContentView(playerView)
        
        exoPlayerAdapter = ExoPlayerAdapter(this, playerView)
        exoPlayerAdapter.setDataSource(Uri.parse(channelUrl))
    }
    
    override fun onPause() {
        super.onPause()
        if (::exoPlayerAdapter.isInitialized) {
            exoPlayerAdapter.pause()
        }
    }
    
    override fun onResume() {
        super.onResume()
        if (::exoPlayerAdapter.isInitialized) {
            exoPlayerAdapter.play()
        }
    }
    
    override fun onDestroy() {
        super.onDestroy()
        if (::exoPlayerAdapter.isInitialized) {
            exoPlayerAdapter.release()
        }
    }
}

