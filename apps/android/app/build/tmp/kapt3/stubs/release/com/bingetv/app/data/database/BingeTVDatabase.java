package com.bingetv.app.data.database;

@kotlin.Metadata(mv = {1, 9, 0}, k = 1, xi = 48, d1 = {"\u00002\n\u0002\u0018\u0002\n\u0002\u0018\u0002\n\u0002\b\u0002\n\u0002\u0018\u0002\n\u0000\n\u0002\u0018\u0002\n\u0000\n\u0002\u0018\u0002\n\u0000\n\u0002\u0018\u0002\n\u0000\n\u0002\u0018\u0002\n\u0000\n\u0002\u0018\u0002\n\u0002\b\u0002\b\'\u0018\u0000 \u000f2\u00020\u0001:\u0001\u000fB\u0005\u00a2\u0006\u0002\u0010\u0002J\b\u0010\u0003\u001a\u00020\u0004H&J\b\u0010\u0005\u001a\u00020\u0006H&J\b\u0010\u0007\u001a\u00020\bH&J\b\u0010\t\u001a\u00020\nH&J\b\u0010\u000b\u001a\u00020\fH&J\b\u0010\r\u001a\u00020\u000eH&\u00a8\u0006\u0010"}, d2 = {"Lcom/bingetv/app/data/database/BingeTVDatabase;", "Landroidx/room/RoomDatabase;", "()V", "categoryDao", "Lcom/bingetv/app/data/database/CategoryDao;", "channelDao", "Lcom/bingetv/app/data/database/ChannelDao;", "epgDao", "Lcom/bingetv/app/data/database/EpgDao;", "playlistDao", "Lcom/bingetv/app/data/database/PlaylistDao;", "userPreferencesDao", "Lcom/bingetv/app/data/database/UserPreferencesDao;", "watchHistoryDao", "Lcom/bingetv/app/data/database/WatchHistoryDao;", "Companion", "app_release"})
@androidx.room.Database(entities = {com.bingetv.app.data.database.ChannelEntity.class, com.bingetv.app.data.database.CategoryEntity.class, com.bingetv.app.data.database.EpgProgramEntity.class, com.bingetv.app.data.database.PlaylistEntity.class, com.bingetv.app.data.database.UserPreferencesEntity.class, com.bingetv.app.data.database.WatchHistoryEntity.class}, version = 2, exportSchema = true)
public abstract class BingeTVDatabase extends androidx.room.RoomDatabase {
    @kotlin.jvm.Volatile
    @org.jetbrains.annotations.Nullable
    private static volatile com.bingetv.app.data.database.BingeTVDatabase INSTANCE;
    @org.jetbrains.annotations.NotNull
    public static final com.bingetv.app.data.database.BingeTVDatabase.Companion Companion = null;
    
    public BingeTVDatabase() {
        super();
    }
    
    @org.jetbrains.annotations.NotNull
    public abstract com.bingetv.app.data.database.ChannelDao channelDao();
    
    @org.jetbrains.annotations.NotNull
    public abstract com.bingetv.app.data.database.CategoryDao categoryDao();
    
    @org.jetbrains.annotations.NotNull
    public abstract com.bingetv.app.data.database.EpgDao epgDao();
    
    @org.jetbrains.annotations.NotNull
    public abstract com.bingetv.app.data.database.PlaylistDao playlistDao();
    
    @org.jetbrains.annotations.NotNull
    public abstract com.bingetv.app.data.database.UserPreferencesDao userPreferencesDao();
    
    @org.jetbrains.annotations.NotNull
    public abstract com.bingetv.app.data.database.WatchHistoryDao watchHistoryDao();
    
    @kotlin.Metadata(mv = {1, 9, 0}, k = 1, xi = 48, d1 = {"\u0000\u001a\n\u0002\u0018\u0002\n\u0002\u0010\u0000\n\u0002\b\u0002\n\u0002\u0018\u0002\n\u0002\b\u0002\n\u0002\u0018\u0002\n\u0000\b\u0086\u0003\u0018\u00002\u00020\u0001B\u0007\b\u0002\u00a2\u0006\u0002\u0010\u0002J\u000e\u0010\u0005\u001a\u00020\u00042\u0006\u0010\u0006\u001a\u00020\u0007R\u0010\u0010\u0003\u001a\u0004\u0018\u00010\u0004X\u0082\u000e\u00a2\u0006\u0002\n\u0000\u00a8\u0006\b"}, d2 = {"Lcom/bingetv/app/data/database/BingeTVDatabase$Companion;", "", "()V", "INSTANCE", "Lcom/bingetv/app/data/database/BingeTVDatabase;", "getDatabase", "context", "Landroid/content/Context;", "app_release"})
    public static final class Companion {
        
        private Companion() {
            super();
        }
        
        @org.jetbrains.annotations.NotNull
        public final com.bingetv.app.data.database.BingeTVDatabase getDatabase(@org.jetbrains.annotations.NotNull
        android.content.Context context) {
            return null;
        }
    }
}