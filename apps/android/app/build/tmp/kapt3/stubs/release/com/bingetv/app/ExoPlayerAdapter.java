package com.bingetv.app;

@kotlin.Metadata(mv = {1, 9, 0}, k = 1, xi = 48, d1 = {"\u0000B\n\u0002\u0018\u0002\n\u0002\u0018\u0002\n\u0000\n\u0002\u0018\u0002\n\u0000\n\u0002\u0018\u0002\n\u0002\b\u0003\n\u0002\u0010\u000b\n\u0000\n\u0002\u0018\u0002\n\u0000\n\u0002\u0018\u0002\n\u0002\b\u0003\n\u0002\u0010\t\n\u0002\b\u0005\n\u0002\u0010\u0002\n\u0002\b\u0006\n\u0002\u0018\u0002\n\u0000\u0018\u00002\u00020\u0001B\u0015\u0012\u0006\u0010\u0002\u001a\u00020\u0003\u0012\u0006\u0010\u0004\u001a\u00020\u0005\u00a2\u0006\u0002\u0010\u0006J\b\u0010\u0010\u001a\u00020\u0011H\u0016J\b\u0010\u0012\u001a\u00020\u0011H\u0016J\b\u0010\u0013\u001a\u00020\u0011H\u0016J\b\u0010\u0014\u001a\u00020\tH\u0016J\b\u0010\u0015\u001a\u00020\tH\u0016J\b\u0010\u0016\u001a\u00020\u0017H\u0016J\b\u0010\u0018\u001a\u00020\u0017H\u0016J\u0006\u0010\u0019\u001a\u00020\u0017J\u0010\u0010\u001a\u001a\u00020\u00172\u0006\u0010\u001b\u001a\u00020\u0011H\u0016J\u000e\u0010\u001c\u001a\u00020\u00172\u0006\u0010\u001d\u001a\u00020\u001eR\u000e\u0010\u0007\u001a\u00020\u0003X\u0082\u0004\u00a2\u0006\u0002\n\u0000R\u000e\u0010\b\u001a\u00020\tX\u0082\u000e\u00a2\u0006\u0002\n\u0000R\u000e\u0010\n\u001a\u00020\u000bX\u0082\u0004\u00a2\u0006\u0002\n\u0000R\u0011\u0010\f\u001a\u00020\r\u00a2\u0006\b\n\u0000\u001a\u0004\b\u000e\u0010\u000fR\u000e\u0010\u0004\u001a\u00020\u0005X\u0082\u0004\u00a2\u0006\u0002\n\u0000\u00a8\u0006\u001f"}, d2 = {"Lcom/bingetv/app/ExoPlayerAdapter;", "Landroidx/leanback/media/PlayerAdapter;", "context", "Landroid/content/Context;", "playerView", "Lcom/google/android/exoplayer2/ui/PlayerView;", "(Landroid/content/Context;Lcom/google/android/exoplayer2/ui/PlayerView;)V", "appContext", "isReleased", "", "listener", "Lcom/google/android/exoplayer2/Player$Listener;", "player", "Lcom/google/android/exoplayer2/ExoPlayer;", "getPlayer", "()Lcom/google/android/exoplayer2/ExoPlayer;", "getBufferedPosition", "", "getCurrentPosition", "getDuration", "isPlaying", "isPrepared", "pause", "", "play", "release", "seekTo", "position", "setDataSource", "uri", "Landroid/net/Uri;", "app_release"})
public final class ExoPlayerAdapter extends androidx.leanback.media.PlayerAdapter {
    @org.jetbrains.annotations.NotNull
    private final com.google.android.exoplayer2.ui.PlayerView playerView = null;
    @org.jetbrains.annotations.NotNull
    private final android.content.Context appContext = null;
    @org.jetbrains.annotations.NotNull
    private final com.google.android.exoplayer2.ExoPlayer player = null;
    private boolean isReleased = false;
    @org.jetbrains.annotations.NotNull
    private final com.google.android.exoplayer2.Player.Listener listener = null;
    
    public ExoPlayerAdapter(@org.jetbrains.annotations.NotNull
    android.content.Context context, @org.jetbrains.annotations.NotNull
    com.google.android.exoplayer2.ui.PlayerView playerView) {
        super();
    }
    
    @org.jetbrains.annotations.NotNull
    public final com.google.android.exoplayer2.ExoPlayer getPlayer() {
        return null;
    }
    
    public final void setDataSource(@org.jetbrains.annotations.NotNull
    android.net.Uri uri) {
    }
    
    @java.lang.Override
    public boolean isPlaying() {
        return false;
    }
    
    @java.lang.Override
    public long getDuration() {
        return 0L;
    }
    
    @java.lang.Override
    public long getCurrentPosition() {
        return 0L;
    }
    
    @java.lang.Override
    public long getBufferedPosition() {
        return 0L;
    }
    
    @java.lang.Override
    public void play() {
    }
    
    @java.lang.Override
    public void pause() {
    }
    
    @java.lang.Override
    public void seekTo(long position) {
    }
    
    @java.lang.Override
    public boolean isPrepared() {
        return false;
    }
    
    public final void release() {
    }
}