package com.bingetv.app.data.database;

@kotlin.Metadata(mv = {1, 9, 0}, k = 1, xi = 48, d1 = {"\u0000,\n\u0002\u0018\u0002\n\u0002\u0010\u0000\n\u0000\n\u0002\u0010\u0002\n\u0000\n\u0002\u0010\t\n\u0002\b\u0003\n\u0002\u0018\u0002\n\u0002\b\u0002\n\u0002\u0018\u0002\n\u0002\u0010 \n\u0002\b\u0003\bg\u0018\u00002\u00020\u0001J\u0010\u0010\u0002\u001a\u00020\u00032\u0006\u0010\u0004\u001a\u00020\u0005H\'J\b\u0010\u0006\u001a\u00020\u0003H\'J\u0010\u0010\u0007\u001a\u00020\u00032\u0006\u0010\b\u001a\u00020\tH\'J\n\u0010\n\u001a\u0004\u0018\u00010\tH\'J\u0014\u0010\u000b\u001a\u000e\u0012\n\u0012\b\u0012\u0004\u0012\u00020\t0\r0\fH\'J\u0010\u0010\u000e\u001a\u00020\u00052\u0006\u0010\b\u001a\u00020\tH\'J\u0010\u0010\u000f\u001a\u00020\u00032\u0006\u0010\b\u001a\u00020\tH\'\u00a8\u0006\u0010"}, d2 = {"Lcom/bingetv/app/data/database/PlaylistDao;", "", "activatePlaylist", "", "playlistId", "", "deactivateAllPlaylists", "deletePlaylist", "playlist", "Lcom/bingetv/app/data/database/PlaylistEntity;", "getActivePlaylist", "getAllPlaylists", "Landroidx/lifecycle/LiveData;", "", "insertPlaylist", "updatePlaylist", "app_debug"})
@androidx.room.Dao
public abstract interface PlaylistDao {
    
    @androidx.room.Query(value = "SELECT * FROM playlists ORDER BY createdAt DESC")
    @org.jetbrains.annotations.NotNull
    public abstract androidx.lifecycle.LiveData<java.util.List<com.bingetv.app.data.database.PlaylistEntity>> getAllPlaylists();
    
    @androidx.room.Query(value = "SELECT * FROM playlists WHERE isActive = 1 LIMIT 1")
    @org.jetbrains.annotations.Nullable
    public abstract com.bingetv.app.data.database.PlaylistEntity getActivePlaylist();
    
    @androidx.room.Insert(onConflict = 1)
    public abstract long insertPlaylist(@org.jetbrains.annotations.NotNull
    com.bingetv.app.data.database.PlaylistEntity playlist);
    
    @androidx.room.Update
    public abstract void updatePlaylist(@org.jetbrains.annotations.NotNull
    com.bingetv.app.data.database.PlaylistEntity playlist);
    
    @androidx.room.Delete
    public abstract void deletePlaylist(@org.jetbrains.annotations.NotNull
    com.bingetv.app.data.database.PlaylistEntity playlist);
    
    @androidx.room.Query(value = "UPDATE playlists SET isActive = 0")
    public abstract void deactivateAllPlaylists();
    
    @androidx.room.Query(value = "UPDATE playlists SET isActive = 1 WHERE id = :playlistId")
    public abstract void activatePlaylist(long playlistId);
}