package com.bingetv.app.data.repository;

@kotlin.Metadata(mv = {1, 9, 0}, k = 1, xi = 48, d1 = {"\u0000B\n\u0002\u0018\u0002\n\u0002\u0010\u0000\n\u0000\n\u0002\u0018\u0002\n\u0002\b\u0002\n\u0002\u0018\u0002\n\u0002\u0010 \n\u0002\u0018\u0002\n\u0002\b\u0003\n\u0002\u0010\u0002\n\u0000\n\u0002\u0010\t\n\u0002\b\b\n\u0002\u0018\u0002\n\u0002\u0010\u000b\n\u0000\n\u0002\u0010\u000e\n\u0002\b\u0006\u0018\u00002\u00020\u0001B\r\u0012\u0006\u0010\u0002\u001a\u00020\u0003\u00a2\u0006\u0002\u0010\u0004J\u0019\u0010\u000b\u001a\u00020\f2\u0006\u0010\r\u001a\u00020\u000eH\u0086@\u00f8\u0001\u0000\u00a2\u0006\u0002\u0010\u000fJ\u0019\u0010\u0010\u001a\u00020\f2\u0006\u0010\u0011\u001a\u00020\bH\u0086@\u00f8\u0001\u0000\u00a2\u0006\u0002\u0010\u0012J\u0013\u0010\u0013\u001a\u0004\u0018\u00010\bH\u0086@\u00f8\u0001\u0000\u00a2\u0006\u0002\u0010\u0014J\u0019\u0010\u0015\u001a\u00020\u000e2\u0006\u0010\u0011\u001a\u00020\bH\u0086@\u00f8\u0001\u0000\u00a2\u0006\u0002\u0010\u0012J:\u0010\u0016\u001a\b\u0012\u0004\u0012\u00020\u00180\u00172\u0006\u0010\u0019\u001a\u00020\u001a2\u0006\u0010\u001b\u001a\u00020\u001a2\u0006\u0010\u001c\u001a\u00020\u001aH\u0086@\u00f8\u0001\u0001\u00f8\u0001\u0002\u00f8\u0001\u0000\u00f8\u0001\u0000\u00a2\u0006\u0004\b\u001d\u0010\u001eJ\u0019\u0010\u001f\u001a\u00020\f2\u0006\u0010\u0011\u001a\u00020\bH\u0086@\u00f8\u0001\u0000\u00a2\u0006\u0002\u0010\u0012R\u001d\u0010\u0005\u001a\u000e\u0012\n\u0012\b\u0012\u0004\u0012\u00020\b0\u00070\u0006\u00a2\u0006\b\n\u0000\u001a\u0004\b\t\u0010\nR\u000e\u0010\u0002\u001a\u00020\u0003X\u0082\u0004\u00a2\u0006\u0002\n\u0000\u0082\u0002\u000f\n\u0002\b\u0019\n\u0002\b!\n\u0005\b\u00a1\u001e0\u0001\u00a8\u0006 "}, d2 = {"Lcom/bingetv/app/data/repository/PlaylistRepository;", "", "playlistDao", "Lcom/bingetv/app/data/database/PlaylistDao;", "(Lcom/bingetv/app/data/database/PlaylistDao;)V", "allPlaylists", "Landroidx/lifecycle/LiveData;", "", "Lcom/bingetv/app/data/database/PlaylistEntity;", "getAllPlaylists", "()Landroidx/lifecycle/LiveData;", "activatePlaylist", "", "playlistId", "", "(JLkotlin/coroutines/Continuation;)Ljava/lang/Object;", "deletePlaylist", "playlist", "(Lcom/bingetv/app/data/database/PlaylistEntity;Lkotlin/coroutines/Continuation;)Ljava/lang/Object;", "getActivePlaylist", "(Lkotlin/coroutines/Continuation;)Ljava/lang/Object;", "insertPlaylist", "testXtreamConnection", "Lkotlin/Result;", "", "serverUrl", "", "username", "password", "testXtreamConnection-BWLJW6A", "(Ljava/lang/String;Ljava/lang/String;Ljava/lang/String;Lkotlin/coroutines/Continuation;)Ljava/lang/Object;", "updatePlaylist", "app_debug"})
public final class PlaylistRepository {
    @org.jetbrains.annotations.NotNull
    private final com.bingetv.app.data.database.PlaylistDao playlistDao = null;
    @org.jetbrains.annotations.NotNull
    private final androidx.lifecycle.LiveData<java.util.List<com.bingetv.app.data.database.PlaylistEntity>> allPlaylists = null;
    
    public PlaylistRepository(@org.jetbrains.annotations.NotNull
    com.bingetv.app.data.database.PlaylistDao playlistDao) {
        super();
    }
    
    @org.jetbrains.annotations.NotNull
    public final androidx.lifecycle.LiveData<java.util.List<com.bingetv.app.data.database.PlaylistEntity>> getAllPlaylists() {
        return null;
    }
    
    @org.jetbrains.annotations.Nullable
    public final java.lang.Object getActivePlaylist(@org.jetbrains.annotations.NotNull
    kotlin.coroutines.Continuation<? super com.bingetv.app.data.database.PlaylistEntity> $completion) {
        return null;
    }
    
    @org.jetbrains.annotations.Nullable
    public final java.lang.Object insertPlaylist(@org.jetbrains.annotations.NotNull
    com.bingetv.app.data.database.PlaylistEntity playlist, @org.jetbrains.annotations.NotNull
    kotlin.coroutines.Continuation<? super java.lang.Long> $completion) {
        return null;
    }
    
    @org.jetbrains.annotations.Nullable
    public final java.lang.Object updatePlaylist(@org.jetbrains.annotations.NotNull
    com.bingetv.app.data.database.PlaylistEntity playlist, @org.jetbrains.annotations.NotNull
    kotlin.coroutines.Continuation<? super kotlin.Unit> $completion) {
        return null;
    }
    
    @org.jetbrains.annotations.Nullable
    public final java.lang.Object deletePlaylist(@org.jetbrains.annotations.NotNull
    com.bingetv.app.data.database.PlaylistEntity playlist, @org.jetbrains.annotations.NotNull
    kotlin.coroutines.Continuation<? super kotlin.Unit> $completion) {
        return null;
    }
    
    @org.jetbrains.annotations.Nullable
    public final java.lang.Object activatePlaylist(long playlistId, @org.jetbrains.annotations.NotNull
    kotlin.coroutines.Continuation<? super kotlin.Unit> $completion) {
        return null;
    }
}