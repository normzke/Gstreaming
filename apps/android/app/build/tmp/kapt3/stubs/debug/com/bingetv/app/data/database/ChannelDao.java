package com.bingetv.app.data.database;

@kotlin.Metadata(mv = {1, 9, 0}, k = 1, xi = 48, d1 = {"\u0000D\n\u0002\u0018\u0002\n\u0002\u0010\u0000\n\u0000\n\u0002\u0010\u0002\n\u0002\b\u0002\n\u0002\u0018\u0002\n\u0000\n\u0002\u0018\u0002\n\u0002\u0010 \n\u0000\n\u0002\u0018\u0002\n\u0002\u0010\b\n\u0002\b\u0002\n\u0002\u0010\t\n\u0002\b\u0003\n\u0002\u0010\u000e\n\u0002\b\u000f\n\u0002\u0010\u000b\n\u0000\bg\u0018\u00002\u00020\u0001J\b\u0010\u0002\u001a\u00020\u0003H\'J\u0010\u0010\u0004\u001a\u00020\u00032\u0006\u0010\u0005\u001a\u00020\u0006H\'J\u0014\u0010\u0007\u001a\u000e\u0012\n\u0012\b\u0012\u0004\u0012\u00020\u00060\t0\bH\'J\u0014\u0010\n\u001a\u000e\u0012\u0004\u0012\u00020\f\u0012\u0004\u0012\u00020\u00060\u000bH\'J\u0012\u0010\r\u001a\u0004\u0018\u00010\u00062\u0006\u0010\u000e\u001a\u00020\u000fH\'J\b\u0010\u0010\u001a\u00020\fH\'J\u001c\u0010\u0011\u001a\u000e\u0012\n\u0012\b\u0012\u0004\u0012\u00020\u00060\t0\b2\u0006\u0010\u0012\u001a\u00020\u0013H\'J\u001c\u0010\u0014\u001a\u000e\u0012\u0004\u0012\u00020\f\u0012\u0004\u0012\u00020\u00060\u000b2\u0006\u0010\u0012\u001a\u00020\u0013H\'J\u001c\u0010\u0015\u001a\b\u0012\u0004\u0012\u00020\u00060\t2\f\u0010\u0016\u001a\b\u0012\u0004\u0012\u00020\u00130\tH\'J\u0014\u0010\u0017\u001a\u000e\u0012\n\u0012\b\u0012\u0004\u0012\u00020\u00060\t0\bH\'J\u0014\u0010\u0018\u001a\u000e\u0012\u0004\u0012\u00020\f\u0012\u0004\u0012\u00020\u00060\u000bH\'J\u0010\u0010\u0019\u001a\u00020\u000f2\u0006\u0010\u0005\u001a\u00020\u0006H\'J\u0016\u0010\u001a\u001a\u00020\u00032\f\u0010\u001b\u001a\b\u0012\u0004\u0012\u00020\u00060\tH\'J\u001c\u0010\u001c\u001a\u000e\u0012\n\u0012\b\u0012\u0004\u0012\u00020\u00060\t0\b2\u0006\u0010\u001d\u001a\u00020\u0013H\'J\u001c\u0010\u001e\u001a\u000e\u0012\u0004\u0012\u00020\f\u0012\u0004\u0012\u00020\u00060\u000b2\u0006\u0010\u001d\u001a\u00020\u0013H\'J\u0010\u0010\u001f\u001a\u00020\u00032\u0006\u0010\u0005\u001a\u00020\u0006H\'J\u0018\u0010 \u001a\u00020\u00032\u0006\u0010!\u001a\u00020\u000f2\u0006\u0010\"\u001a\u00020#H\'\u00a8\u0006$"}, d2 = {"Lcom/bingetv/app/data/database/ChannelDao;", "", "deleteAllChannels", "", "deleteChannel", "channel", "Lcom/bingetv/app/data/database/ChannelEntity;", "getAllChannels", "Landroidx/lifecycle/LiveData;", "", "getAllChannelsPaged", "Landroidx/paging/PagingSource;", "", "getChannelById", "id", "", "getChannelCount", "getChannelsByCategory", "category", "", "getChannelsByCategoryPaged", "getChannelsByStreamIds", "streamIds", "getFavoriteChannels", "getFavoriteChannelsPaged", "insertChannel", "insertChannels", "channels", "searchChannels", "query", "searchChannelsPaged", "updateChannel", "updateFavoriteStatus", "channelId", "isFavorite", "", "app_debug"})
@androidx.room.Dao
public abstract interface ChannelDao {
    
    @androidx.room.Query(value = "SELECT * FROM channels ORDER BY sortOrder ASC, name ASC")
    @org.jetbrains.annotations.NotNull
    public abstract androidx.lifecycle.LiveData<java.util.List<com.bingetv.app.data.database.ChannelEntity>> getAllChannels();
    
    @androidx.room.Query(value = "SELECT * FROM channels ORDER BY sortOrder ASC, name ASC")
    @org.jetbrains.annotations.NotNull
    public abstract androidx.paging.PagingSource<java.lang.Integer, com.bingetv.app.data.database.ChannelEntity> getAllChannelsPaged();
    
    @androidx.room.Query(value = "SELECT * FROM channels WHERE category = :category ORDER BY sortOrder ASC, name ASC")
    @org.jetbrains.annotations.NotNull
    public abstract androidx.lifecycle.LiveData<java.util.List<com.bingetv.app.data.database.ChannelEntity>> getChannelsByCategory(@org.jetbrains.annotations.NotNull
    java.lang.String category);
    
    @androidx.room.Query(value = "SELECT * FROM channels WHERE category = :category ORDER BY sortOrder ASC, name ASC")
    @org.jetbrains.annotations.NotNull
    public abstract androidx.paging.PagingSource<java.lang.Integer, com.bingetv.app.data.database.ChannelEntity> getChannelsByCategoryPaged(@org.jetbrains.annotations.NotNull
    java.lang.String category);
    
    @androidx.room.Query(value = "SELECT * FROM channels WHERE isFavorite = 1 ORDER BY sortOrder ASC, name ASC")
    @org.jetbrains.annotations.NotNull
    public abstract androidx.lifecycle.LiveData<java.util.List<com.bingetv.app.data.database.ChannelEntity>> getFavoriteChannels();
    
    @androidx.room.Query(value = "SELECT * FROM channels WHERE isFavorite = 1 ORDER BY sortOrder ASC, name ASC")
    @org.jetbrains.annotations.NotNull
    public abstract androidx.paging.PagingSource<java.lang.Integer, com.bingetv.app.data.database.ChannelEntity> getFavoriteChannelsPaged();
    
    @androidx.room.Query(value = "SELECT * FROM channels WHERE name LIKE \'%\' || :query || \'%\' OR category LIKE \'%\' || :query || \'%\'")
    @org.jetbrains.annotations.NotNull
    public abstract androidx.lifecycle.LiveData<java.util.List<com.bingetv.app.data.database.ChannelEntity>> searchChannels(@org.jetbrains.annotations.NotNull
    java.lang.String query);
    
    @androidx.room.Query(value = "SELECT * FROM channels WHERE name LIKE \'%\' || :query || \'%\' OR category LIKE \'%\' || :query || \'%\'")
    @org.jetbrains.annotations.NotNull
    public abstract androidx.paging.PagingSource<java.lang.Integer, com.bingetv.app.data.database.ChannelEntity> searchChannelsPaged(@org.jetbrains.annotations.NotNull
    java.lang.String query);
    
    @androidx.room.Query(value = "SELECT * FROM channels WHERE id = :id")
    @org.jetbrains.annotations.Nullable
    public abstract com.bingetv.app.data.database.ChannelEntity getChannelById(long id);
    
    @androidx.room.Query(value = "SELECT * FROM channels WHERE streamId IN (:streamIds)")
    @org.jetbrains.annotations.NotNull
    public abstract java.util.List<com.bingetv.app.data.database.ChannelEntity> getChannelsByStreamIds(@org.jetbrains.annotations.NotNull
    java.util.List<java.lang.String> streamIds);
    
    @androidx.room.Insert(onConflict = 1)
    public abstract long insertChannel(@org.jetbrains.annotations.NotNull
    com.bingetv.app.data.database.ChannelEntity channel);
    
    @androidx.room.Insert(onConflict = 1)
    public abstract void insertChannels(@org.jetbrains.annotations.NotNull
    java.util.List<com.bingetv.app.data.database.ChannelEntity> channels);
    
    @androidx.room.Update
    public abstract void updateChannel(@org.jetbrains.annotations.NotNull
    com.bingetv.app.data.database.ChannelEntity channel);
    
    @androidx.room.Delete
    public abstract void deleteChannel(@org.jetbrains.annotations.NotNull
    com.bingetv.app.data.database.ChannelEntity channel);
    
    @androidx.room.Query(value = "DELETE FROM channels")
    public abstract void deleteAllChannels();
    
    @androidx.room.Query(value = "UPDATE channels SET isFavorite = :isFavorite WHERE id = :channelId")
    public abstract void updateFavoriteStatus(long channelId, boolean isFavorite);
    
    @androidx.room.Query(value = "SELECT COUNT(*) FROM channels")
    public abstract int getChannelCount();
}