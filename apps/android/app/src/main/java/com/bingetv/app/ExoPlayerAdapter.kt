package com.bingetv.app

import android.content.Context
import android.net.Uri
import androidx.leanback.media.PlayerAdapter
import com.google.android.exoplayer2.ExoPlayer
import com.google.android.exoplayer2.MediaItem
import com.google.android.exoplayer2.Player
import com.google.android.exoplayer2.ui.PlayerView
import com.google.android.exoplayer2.upstream.DefaultDataSource

class ExoPlayerAdapter(
    context: Context,
    private val playerView: PlayerView
) : PlayerAdapter() {

    private val appContext: Context = context.applicationContext
    val player: ExoPlayer = ExoPlayer.Builder(appContext).build()
    private var isReleased = false

    private val listener = object : Player.Listener {
        override fun onIsPlayingChanged(isPlaying: Boolean) {
            callback.onPlayStateChanged(this@ExoPlayerAdapter)
        }

        override fun onPlaybackStateChanged(playbackState: Int) {
            if (playbackState == Player.STATE_READY || playbackState == Player.STATE_BUFFERING) {
                callback.onPreparedStateChanged(this@ExoPlayerAdapter)
            }
        }
    }

    init {
        playerView.player = player
        player.addListener(listener)
    }

    fun setDataSource(uri: Uri) {
        if (isReleased) return
        val dataSourceFactory = DefaultDataSource.Factory(appContext)
        val mediaItem = MediaItem.fromUri(uri)
        player.setMediaItem(mediaItem)
        player.prepare()
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

    fun release() {
        if (!isReleased) {
            isReleased = true
            player.removeListener(listener)
            player.release()
            playerView.player = null
        }
    }
}
