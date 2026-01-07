package com.bingetv.app.player;

@kotlin.Metadata(mv = {1, 9, 0}, k = 1, xi = 48, d1 = {"\u0000d\n\u0002\u0018\u0002\n\u0002\u0018\u0002\n\u0002\u0018\u0002\n\u0000\n\u0002\u0018\u0002\n\u0002\b\u0002\n\u0002\u0010\u000b\n\u0000\n\u0002\u0018\u0002\n\u0000\n\u0002\u0018\u0002\n\u0002\b\u0003\n\u0002\u0018\u0002\n\u0000\n\u0002\u0010\t\n\u0002\b\u0005\n\u0002\u0010\u0002\n\u0000\n\u0002\u0018\u0002\n\u0002\b\b\n\u0002\u0018\u0002\n\u0002\b\u0002\n\u0002\u0018\u0002\n\u0002\b\u0002\n\u0002\u0018\u0002\n\u0000\n\u0002\u0010\b\n\u0002\b\u0005\u0018\u00002\u00020\u00012\u00020\u0002B\r\u0012\u0006\u0010\u0003\u001a\u00020\u0004\u00a2\u0006\u0002\u0010\u0005J\b\u0010\u0010\u001a\u00020\u0011H\u0016J\b\u0010\u0012\u001a\u00020\u0011H\u0016J\b\u0010\u0013\u001a\u00020\u0011H\u0016J\b\u0010\u0014\u001a\u00020\u0007H\u0016J\b\u0010\u0015\u001a\u00020\u0007H\u0016J\u0012\u0010\u0016\u001a\u00020\u00172\b\u0010\u0018\u001a\u0004\u0018\u00010\u0019H\u0016J\b\u0010\u001a\u001a\u00020\u0017H\u0016J\b\u0010\u001b\u001a\u00020\u0017H\u0016J\b\u0010\u001c\u001a\u00020\u0017H\u0016J\u0006\u0010\u001d\u001a\u00020\u0017J\u0010\u0010\u001e\u001a\u00020\u00172\u0006\u0010\u001f\u001a\u00020\u0011H\u0016J\u000e\u0010 \u001a\u00020\u00172\u0006\u0010!\u001a\u00020\"J\u0010\u0010#\u001a\u00020\u00172\b\u0010$\u001a\u0004\u0018\u00010%J(\u0010&\u001a\u00020\u00172\u0006\u0010\'\u001a\u00020(2\u0006\u0010)\u001a\u00020*2\u0006\u0010+\u001a\u00020*2\u0006\u0010,\u001a\u00020*H\u0016J\u0010\u0010-\u001a\u00020\u00172\u0006\u0010\'\u001a\u00020(H\u0016J\u0010\u0010.\u001a\u00020\u00172\u0006\u0010\'\u001a\u00020(H\u0016R\u000e\u0010\u0003\u001a\u00020\u0004X\u0082\u0004\u00a2\u0006\u0002\n\u0000R\u000e\u0010\u0006\u001a\u00020\u0007X\u0082\u000e\u00a2\u0006\u0002\n\u0000R\u000e\u0010\b\u001a\u00020\tX\u0082\u0004\u00a2\u0006\u0002\n\u0000R\u0011\u0010\n\u001a\u00020\u000b\u00a2\u0006\b\n\u0000\u001a\u0004\b\f\u0010\rR\u0010\u0010\u000e\u001a\u0004\u0018\u00010\u000fX\u0082\u000e\u00a2\u0006\u0002\n\u0000\u00a8\u0006/"}, d2 = {"Lcom/bingetv/app/player/ExoPlayerAdapter;", "Landroidx/leanback/media/PlayerAdapter;", "Landroid/view/SurfaceHolder$Callback;", "context", "Landroid/content/Context;", "(Landroid/content/Context;)V", "isReleased", "", "listener", "Lcom/google/android/exoplayer2/Player$Listener;", "player", "Lcom/google/android/exoplayer2/ExoPlayer;", "getPlayer", "()Lcom/google/android/exoplayer2/ExoPlayer;", "surfaceHolderGlueHost", "Landroidx/leanback/media/SurfaceHolderGlueHost;", "getBufferedPosition", "", "getCurrentPosition", "getDuration", "isPlaying", "isPrepared", "onAttachedToHost", "", "host", "Landroidx/leanback/media/PlaybackGlueHost;", "onDetachedFromHost", "pause", "play", "release", "seekTo", "position", "setDataSource", "uri", "Landroid/net/Uri;", "setSurface", "surface", "Landroid/view/Surface;", "surfaceChanged", "holder", "Landroid/view/SurfaceHolder;", "format", "", "width", "height", "surfaceCreated", "surfaceDestroyed", "app_debug"})
public final class ExoPlayerAdapter extends androidx.leanback.media.PlayerAdapter implements android.view.SurfaceHolder.Callback {
    @org.jetbrains.annotations.NotNull
    private final android.content.Context context = null;
    @org.jetbrains.annotations.NotNull
    private final com.google.android.exoplayer2.ExoPlayer player = null;
    private boolean isReleased = false;
    @org.jetbrains.annotations.Nullable
    private androidx.leanback.media.SurfaceHolderGlueHost surfaceHolderGlueHost;
    @org.jetbrains.annotations.NotNull
    private final com.google.android.exoplayer2.Player.Listener listener = null;
    
    public ExoPlayerAdapter(@org.jetbrains.annotations.NotNull
    android.content.Context context) {
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
    
    @java.lang.Override
    public void surfaceCreated(@org.jetbrains.annotations.NotNull
    android.view.SurfaceHolder holder) {
    }
    
    @java.lang.Override
    public void surfaceChanged(@org.jetbrains.annotations.NotNull
    android.view.SurfaceHolder holder, int format, int width, int height) {
    }
    
    @java.lang.Override
    public void surfaceDestroyed(@org.jetbrains.annotations.NotNull
    android.view.SurfaceHolder holder) {
    }
    
    public final void setSurface(@org.jetbrains.annotations.Nullable
    android.view.Surface surface) {
    }
    
    public final void release() {
    }
    
    @java.lang.Override
    public void onAttachedToHost(@org.jetbrains.annotations.Nullable
    androidx.leanback.media.PlaybackGlueHost host) {
    }
    
    @java.lang.Override
    public void onDetachedFromHost() {
    }
}