package com.bingetv.app.data.repository;

@kotlin.Metadata(mv = {1, 9, 0}, k = 1, xi = 48, d1 = {"\u0000p\n\u0002\u0018\u0002\n\u0002\u0010\u0000\n\u0000\n\u0002\u0018\u0002\n\u0000\n\u0002\u0018\u0002\n\u0000\n\u0002\u0018\u0002\n\u0002\b\u0002\n\u0002\u0018\u0002\n\u0002\u0010 \n\u0002\u0018\u0002\n\u0002\b\u0003\n\u0002\u0018\u0002\n\u0002\b\u0004\n\u0002\u0018\u0002\n\u0002\b\u0002\n\u0002\u0010\u0002\n\u0002\b\u0003\n\u0002\u0010\b\n\u0002\b\u0002\n\u0002\u0010\u000e\n\u0002\b\u0004\n\u0002\u0018\u0002\n\u0002\u0018\u0002\n\u0002\b\f\n\u0002\u0010\t\n\u0000\n\u0002\u0010\u000b\n\u0002\b\u0002\u0018\u00002\u00020\u0001B\u001d\u0012\u0006\u0010\u0002\u001a\u00020\u0003\u0012\u0006\u0010\u0004\u001a\u00020\u0005\u0012\u0006\u0010\u0006\u001a\u00020\u0007\u00a2\u0006\u0002\u0010\bJ\u0011\u0010\u0017\u001a\u00020\u0018H\u0086@\u00f8\u0001\u0000\u00a2\u0006\u0002\u0010\u0019J\u0011\u0010\u001a\u001a\u00020\u0018H\u0086@\u00f8\u0001\u0000\u00a2\u0006\u0002\u0010\u0019J\u0011\u0010\u001b\u001a\u00020\u001cH\u0086@\u00f8\u0001\u0000\u00a2\u0006\u0002\u0010\u0019J\u001a\u0010\u001d\u001a\u000e\u0012\n\u0012\b\u0012\u0004\u0012\u00020\u00100\u000b0\n2\u0006\u0010\u001e\u001a\u00020\u001fJ%\u0010 \u001a\b\u0012\u0004\u0012\u00020\u00100\u000b2\f\u0010!\u001a\b\u0012\u0004\u0012\u00020\u001f0\u000bH\u0086@\u00f8\u0001\u0000\u00a2\u0006\u0002\u0010\"J\u001a\u0010#\u001a\u000e\u0012\n\u0012\b\u0012\u0004\u0012\u00020\u00100%0$2\u0006\u0010&\u001a\u00020\u001fJ\u001f\u0010\'\u001a\u00020\u00182\f\u0010(\u001a\b\u0012\u0004\u0012\u00020\f0\u000bH\u0086@\u00f8\u0001\u0000\u00a2\u0006\u0002\u0010\"J\u001f\u0010)\u001a\u00020\u00182\f\u0010*\u001a\b\u0012\u0004\u0012\u00020\u00100\u000bH\u0086@\u00f8\u0001\u0000\u00a2\u0006\u0002\u0010\"J\u0019\u0010+\u001a\u00020\u00182\u0006\u0010,\u001a\u00020\u001fH\u0086@\u00f8\u0001\u0000\u00a2\u0006\u0002\u0010-J\u001a\u0010.\u001a\u000e\u0012\n\u0012\b\u0012\u0004\u0012\u00020\u00100\u000b0\n2\u0006\u0010/\u001a\u00020\u001fJ!\u00100\u001a\u00020\u00182\u0006\u00101\u001a\u0002022\u0006\u00103\u001a\u000204H\u0086@\u00f8\u0001\u0000\u00a2\u0006\u0002\u00105R\u001d\u0010\t\u001a\u000e\u0012\n\u0012\b\u0012\u0004\u0012\u00020\f0\u000b0\n\u00a2\u0006\b\n\u0000\u001a\u0004\b\r\u0010\u000eR\u001d\u0010\u000f\u001a\u000e\u0012\n\u0012\b\u0012\u0004\u0012\u00020\u00100\u000b0\n\u00a2\u0006\b\n\u0000\u001a\u0004\b\u0011\u0010\u000eR\u000e\u0010\u0004\u001a\u00020\u0005X\u0082\u0004\u00a2\u0006\u0002\n\u0000R\u000e\u0010\u0002\u001a\u00020\u0003X\u0082\u0004\u00a2\u0006\u0002\n\u0000R\u001d\u0010\u0012\u001a\u000e\u0012\n\u0012\b\u0012\u0004\u0012\u00020\u00100\u000b0\n\u00a2\u0006\b\n\u0000\u001a\u0004\b\u0013\u0010\u000eR\u001d\u0010\u0014\u001a\u000e\u0012\n\u0012\b\u0012\u0004\u0012\u00020\u00150\u000b0\n\u00a2\u0006\b\n\u0000\u001a\u0004\b\u0016\u0010\u000eR\u000e\u0010\u0006\u001a\u00020\u0007X\u0082\u0004\u00a2\u0006\u0002\n\u0000\u0082\u0002\u0004\n\u0002\b\u0019\u00a8\u00066"}, d2 = {"Lcom/bingetv/app/data/repository/ChannelRepository;", "", "channelDao", "Lcom/bingetv/app/data/database/ChannelDao;", "categoryDao", "Lcom/bingetv/app/data/database/CategoryDao;", "watchHistoryDao", "Lcom/bingetv/app/data/database/WatchHistoryDao;", "(Lcom/bingetv/app/data/database/ChannelDao;Lcom/bingetv/app/data/database/CategoryDao;Lcom/bingetv/app/data/database/WatchHistoryDao;)V", "allCategories", "Landroidx/lifecycle/LiveData;", "", "Lcom/bingetv/app/data/database/CategoryEntity;", "getAllCategories", "()Landroidx/lifecycle/LiveData;", "allChannels", "Lcom/bingetv/app/data/database/ChannelEntity;", "getAllChannels", "favoriteChannels", "getFavoriteChannels", "watchHistory", "Lcom/bingetv/app/data/database/WatchHistoryEntity;", "getWatchHistory", "clearAllData", "", "(Lkotlin/coroutines/Continuation;)Ljava/lang/Object;", "clearHistory", "getChannelCount", "", "getChannelsByCategory", "category", "", "getChannelsByStreamIds", "ids", "(Ljava/util/List;Lkotlin/coroutines/Continuation;)Ljava/lang/Object;", "getChannelsPaged", "Lkotlinx/coroutines/flow/Flow;", "Landroidx/paging/PagingData;", "mode", "insertCategories", "categories", "insertChannels", "channels", "recordHistory", "streamId", "(Ljava/lang/String;Lkotlin/coroutines/Continuation;)Ljava/lang/Object;", "searchChannels", "query", "toggleFavorite", "channelId", "", "isFavorite", "", "(JZLkotlin/coroutines/Continuation;)Ljava/lang/Object;", "app_release"})
public final class ChannelRepository {
    @org.jetbrains.annotations.NotNull
    private final com.bingetv.app.data.database.ChannelDao channelDao = null;
    @org.jetbrains.annotations.NotNull
    private final com.bingetv.app.data.database.CategoryDao categoryDao = null;
    @org.jetbrains.annotations.NotNull
    private final com.bingetv.app.data.database.WatchHistoryDao watchHistoryDao = null;
    @org.jetbrains.annotations.NotNull
    private final androidx.lifecycle.LiveData<java.util.List<com.bingetv.app.data.database.ChannelEntity>> allChannels = null;
    @org.jetbrains.annotations.NotNull
    private final androidx.lifecycle.LiveData<java.util.List<com.bingetv.app.data.database.CategoryEntity>> allCategories = null;
    @org.jetbrains.annotations.NotNull
    private final androidx.lifecycle.LiveData<java.util.List<com.bingetv.app.data.database.ChannelEntity>> favoriteChannels = null;
    @org.jetbrains.annotations.NotNull
    private final androidx.lifecycle.LiveData<java.util.List<com.bingetv.app.data.database.WatchHistoryEntity>> watchHistory = null;
    
    public ChannelRepository(@org.jetbrains.annotations.NotNull
    com.bingetv.app.data.database.ChannelDao channelDao, @org.jetbrains.annotations.NotNull
    com.bingetv.app.data.database.CategoryDao categoryDao, @org.jetbrains.annotations.NotNull
    com.bingetv.app.data.database.WatchHistoryDao watchHistoryDao) {
        super();
    }
    
    @org.jetbrains.annotations.NotNull
    public final androidx.lifecycle.LiveData<java.util.List<com.bingetv.app.data.database.ChannelEntity>> getAllChannels() {
        return null;
    }
    
    @org.jetbrains.annotations.NotNull
    public final androidx.lifecycle.LiveData<java.util.List<com.bingetv.app.data.database.CategoryEntity>> getAllCategories() {
        return null;
    }
    
    @org.jetbrains.annotations.NotNull
    public final androidx.lifecycle.LiveData<java.util.List<com.bingetv.app.data.database.ChannelEntity>> getFavoriteChannels() {
        return null;
    }
    
    @org.jetbrains.annotations.NotNull
    public final androidx.lifecycle.LiveData<java.util.List<com.bingetv.app.data.database.WatchHistoryEntity>> getWatchHistory() {
        return null;
    }
    
    @org.jetbrains.annotations.NotNull
    public final androidx.lifecycle.LiveData<java.util.List<com.bingetv.app.data.database.ChannelEntity>> getChannelsByCategory(@org.jetbrains.annotations.NotNull
    java.lang.String category) {
        return null;
    }
    
    @org.jetbrains.annotations.NotNull
    public final androidx.lifecycle.LiveData<java.util.List<com.bingetv.app.data.database.ChannelEntity>> searchChannels(@org.jetbrains.annotations.NotNull
    java.lang.String query) {
        return null;
    }
    
    @org.jetbrains.annotations.Nullable
    public final java.lang.Object insertChannels(@org.jetbrains.annotations.NotNull
    java.util.List<com.bingetv.app.data.database.ChannelEntity> channels, @org.jetbrains.annotations.NotNull
    kotlin.coroutines.Continuation<? super kotlin.Unit> $completion) {
        return null;
    }
    
    @org.jetbrains.annotations.Nullable
    public final java.lang.Object insertCategories(@org.jetbrains.annotations.NotNull
    java.util.List<com.bingetv.app.data.database.CategoryEntity> categories, @org.jetbrains.annotations.NotNull
    kotlin.coroutines.Continuation<? super kotlin.Unit> $completion) {
        return null;
    }
    
    @org.jetbrains.annotations.Nullable
    public final java.lang.Object toggleFavorite(long channelId, boolean isFavorite, @org.jetbrains.annotations.NotNull
    kotlin.coroutines.Continuation<? super kotlin.Unit> $completion) {
        return null;
    }
    
    @org.jetbrains.annotations.Nullable
    public final java.lang.Object recordHistory(@org.jetbrains.annotations.NotNull
    java.lang.String streamId, @org.jetbrains.annotations.NotNull
    kotlin.coroutines.Continuation<? super kotlin.Unit> $completion) {
        return null;
    }
    
    @org.jetbrains.annotations.Nullable
    public final java.lang.Object clearHistory(@org.jetbrains.annotations.NotNull
    kotlin.coroutines.Continuation<? super kotlin.Unit> $completion) {
        return null;
    }
    
    @org.jetbrains.annotations.Nullable
    public final java.lang.Object clearAllData(@org.jetbrains.annotations.NotNull
    kotlin.coroutines.Continuation<? super kotlin.Unit> $completion) {
        return null;
    }
    
    @org.jetbrains.annotations.Nullable
    public final java.lang.Object getChannelCount(@org.jetbrains.annotations.NotNull
    kotlin.coroutines.Continuation<? super java.lang.Integer> $completion) {
        return null;
    }
    
    @org.jetbrains.annotations.NotNull
    public final kotlinx.coroutines.flow.Flow<androidx.paging.PagingData<com.bingetv.app.data.database.ChannelEntity>> getChannelsPaged(@org.jetbrains.annotations.NotNull
    java.lang.String mode) {
        return null;
    }
    
    @org.jetbrains.annotations.Nullable
    public final java.lang.Object getChannelsByStreamIds(@org.jetbrains.annotations.NotNull
    java.util.List<java.lang.String> ids, @org.jetbrains.annotations.NotNull
    kotlin.coroutines.Continuation<? super java.util.List<com.bingetv.app.data.database.ChannelEntity>> $completion) {
        return null;
    }
}