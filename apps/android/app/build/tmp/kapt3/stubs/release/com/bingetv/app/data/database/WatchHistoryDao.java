package com.bingetv.app.data.database;

@kotlin.Metadata(mv = {1, 9, 0}, k = 1, xi = 48, d1 = {"\u0000(\n\u0002\u0018\u0002\n\u0002\u0010\u0000\n\u0000\n\u0002\u0010\u0002\n\u0002\b\u0002\n\u0002\u0010\u000e\n\u0000\n\u0002\u0018\u0002\n\u0002\u0010 \n\u0002\u0018\u0002\n\u0002\b\u0003\bg\u0018\u00002\u00020\u0001J\b\u0010\u0002\u001a\u00020\u0003H\'J\u0010\u0010\u0004\u001a\u00020\u00032\u0006\u0010\u0005\u001a\u00020\u0006H\'J\u0014\u0010\u0007\u001a\u000e\u0012\n\u0012\b\u0012\u0004\u0012\u00020\n0\t0\bH\'J\u0010\u0010\u000b\u001a\u00020\u00032\u0006\u0010\f\u001a\u00020\nH\'\u00a8\u0006\r"}, d2 = {"Lcom/bingetv/app/data/database/WatchHistoryDao;", "", "deleteAllHistory", "", "deleteHistoryByStreamId", "streamId", "", "getRecentHistory", "Landroidx/lifecycle/LiveData;", "", "Lcom/bingetv/app/data/database/WatchHistoryEntity;", "insertHistory", "history", "app_release"})
@androidx.room.Dao
public abstract interface WatchHistoryDao {
    
    @androidx.room.Query(value = "SELECT * FROM watch_history ORDER BY watchedAt DESC LIMIT 50")
    @org.jetbrains.annotations.NotNull
    public abstract androidx.lifecycle.LiveData<java.util.List<com.bingetv.app.data.database.WatchHistoryEntity>> getRecentHistory();
    
    @androidx.room.Insert(onConflict = 1)
    public abstract void insertHistory(@org.jetbrains.annotations.NotNull
    com.bingetv.app.data.database.WatchHistoryEntity history);
    
    @androidx.room.Query(value = "DELETE FROM watch_history")
    public abstract void deleteAllHistory();
    
    @androidx.room.Query(value = "DELETE FROM watch_history WHERE streamId = :streamId")
    public abstract void deleteHistoryByStreamId(@org.jetbrains.annotations.NotNull
    java.lang.String streamId);
}