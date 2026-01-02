package com.bingetv.app.player

import android.content.Context
import android.net.Uri
import android.view.Surface
import android.view.SurfaceHolder
import androidx.leanback.media.PlayerAdapter
import androidx.leanback.media.SurfaceHolderGlueHost
import com.google.android.exoplayer2.ExoPlayer
import com.google.android.exoplayer2.MediaItem
import com.google.android.exoplayer2.Player
import com.google.android.exoplayer2.upstream.DefaultDataSource
import com.google.android.exoplayer2.upstream.DefaultHttpDataSource

class ExoPlayerAdapter(private val context: Context) : PlayerAdapter(), SurfaceHolder.Callback {

    val player: ExoPlayer = ExoPlayer.Builder(context).build()
    private var isReleased = false
    private var surfaceHolderGlueHost: SurfaceHolderGlueHost? = null

    private val listener = object : Player.Listener {
        override fun onIsPlayingChanged(isPlaying: Boolean) {
            callback.onPlayStateChanged(this@ExoPlayerAdapter)
        }

        override fun onPlaybackStateChanged(playbackState: Int) {
            if (playbackState == Player.STATE_READY || playbackState == Player.STATE_BUFFERING) {
                callback.onPreparedStateChanged(this@ExoPlayerAdapter)
            }
        }
        
        override fun onPlayerError(error: com.google.android.exoplayer2.PlaybackException) {
            // Re-broadcast error to any listeners
            callback.onError(this@ExoPlayerAdapter, error.errorCode, error.message)
        }
    }

    init {
        player.addListener(listener)
    }

    fun setDataSource(uri: Uri) {
        if (isReleased) return
        
        // Use TiviMate User-Agent to avoid blocks
        val userAgent = "TiviMate/4.7.0"
        
        val httpDataSourceFactory = DefaultHttpDataSource.Factory()
            .setUserAgent(userAgent)
            .setAllowCrossProtocolRedirects(true)
            .setConnectTimeoutMs(30000)
            .setReadTimeoutMs(30000)
            
        val dataSourceFactory = DefaultDataSource.Factory(context, httpDataSourceFactory)
        
        val mediaItem = MediaItem.fromUri(uri)
        player.setMediaItem(mediaItem)
        player.prepare()
        player.playWhenReady = true
    }

    override fun isPlaying(): Boolean {
        return !isReleased && player.isPlaying
    }

    override fun getDuration(): Long {
        return if (isReleased) -1 else player.duration
    }

    override fun getCurrentPosition(): Long {
        return if (isReleased) -1 else player.currentPosition
    }
    
    override fun getBufferedPosition(): Long {
        return if (isReleased) -1 else player.bufferedPosition
    }

    override fun play() {
        if (isReleased) return
        player.play()
    }

    override fun pause() {
        if (isReleased) return
        player.pause()
    }

    override fun seekTo(position: Long) {
        if (isReleased) return
        player.seekTo(position)
    }
    
    override fun isPrepared(): Boolean {
        return !isReleased && (player.playbackState == Player.STATE_READY || player.playbackState == Player.STATE_BUFFERING)
    }
    
    // SurfaceHolder.Callback implementation
    override fun surfaceCreated(holder: SurfaceHolder) {
        setSurface(holder.surface)
    }

    override fun surfaceChanged(holder: SurfaceHolder, format: Int, width: Int, height: Int) {
        // Do nothing
    }

    override fun surfaceDestroyed(holder: SurfaceHolder) {
        setSurface(null)
    }
    
    fun setSurface(surface: Surface?) {
        if (!isReleased) {
            player.setVideoSurface(surface)
        }
    }

    fun release() {
        if (!isReleased) {
            isReleased = true
            player.removeListener(listener)
            player.release()
        }
    }
    
    override fun onAttachedToHost(host: androidx.leanback.media.PlaybackGlueHost?) {
        if (host is SurfaceHolderGlueHost) {
            surfaceHolderGlueHost = host
            surfaceHolderGlueHost?.setSurfaceHolderCallback(this)
        }
    }
    
    override fun onDetachedFromHost() {
        surfaceHolderGlueHost?.setSurfaceHolderCallback(null)
        surfaceHolderGlueHost = null
        release()
    }
}
