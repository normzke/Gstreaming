package com.bingetv.app.data.repository;

@kotlin.Metadata(mv = {1, 9, 0}, k = 1, xi = 48, d1 = {"\u0000N\n\u0002\u0018\u0002\n\u0002\u0010\u0000\n\u0000\n\u0002\u0018\u0002\n\u0000\n\u0002\u0018\u0002\n\u0002\b\u0002\n\u0002\u0018\u0002\n\u0002\u0010 \n\u0002\u0018\u0002\n\u0002\b\u0003\n\u0002\u0018\u0002\n\u0002\b\u0004\n\u0002\u0010\u0002\n\u0002\b\u0003\n\u0002\u0010\u000e\n\u0002\b\t\n\u0002\u0010\t\n\u0000\n\u0002\u0010\u000b\n\u0002\b\u0002\u0018\u00002\u00020\u0001B\u0015\u0012\u0006\u0010\u0002\u001a\u00020\u0003\u0012\u0006\u0010\u0004\u001a\u00020\u0005\u00a2\u0006\u0002\u0010\u0006J\u0011\u0010\u0012\u001a\u00020\u0013H\u0086@\u00f8\u0001\u0000\u00a2\u0006\u0002\u0010\u0014J\u001a\u0010\u0015\u001a\u000e\u0012\n\u0012\b\u0012\u0004\u0012\u00020\u000e0\t0\b2\u0006\u0010\u0016\u001a\u00020\u0017J\u001f\u0010\u0018\u001a\u00020\u00132\f\u0010\u0019\u001a\b\u0012\u0004\u0012\u00020\n0\tH\u0086@\u00f8\u0001\u0000\u00a2\u0006\u0002\u0010\u001aJ\u001f\u0010\u001b\u001a\u00020\u00132\f\u0010\u001c\u001a\b\u0012\u0004\u0012\u00020\u000e0\tH\u0086@\u00f8\u0001\u0000\u00a2\u0006\u0002\u0010\u001aJ\u001a\u0010\u001d\u001a\u000e\u0012\n\u0012\b\u0012\u0004\u0012\u00020\u000e0\t0\b2\u0006\u0010\u001e\u001a\u00020\u0017J!\u0010\u001f\u001a\u00020\u00132\u0006\u0010 \u001a\u00020!2\u0006\u0010\"\u001a\u00020#H\u0086@\u00f8\u0001\u0000\u00a2\u0006\u0002\u0010$R\u001d\u0010\u0007\u001a\u000e\u0012\n\u0012\b\u0012\u0004\u0012\u00020\n0\t0\b\u00a2\u0006\b\n\u0000\u001a\u0004\b\u000b\u0010\fR\u001d\u0010\r\u001a\u000e\u0012\n\u0012\b\u0012\u0004\u0012\u00020\u000e0\t0\b\u00a2\u0006\b\n\u0000\u001a\u0004\b\u000f\u0010\fR\u000e\u0010\u0004\u001a\u00020\u0005X\u0082\u0004\u00a2\u0006\u0002\n\u0000R\u000e\u0010\u0002\u001a\u00020\u0003X\u0082\u0004\u00a2\u0006\u0002\n\u0000R\u001d\u0010\u0010\u001a\u000e\u0012\n\u0012\b\u0012\u0004\u0012\u00020\u000e0\t0\b\u00a2\u0006\b\n\u0000\u001a\u0004\b\u0011\u0010\f\u0082\u0002\u0004\n\u0002\b\u0019\u00a8\u0006%"}, d2 = {"Lcom/bingetv/app/data/repository/ChannelRepository;", "", "channelDao", "Lcom/bingetv/app/data/database/ChannelDao;", "categoryDao", "Lcom/bingetv/app/data/database/CategoryDao;", "(Lcom/bingetv/app/data/database/ChannelDao;Lcom/bingetv/app/data/database/CategoryDao;)V", "allCategories", "Landroidx/lifecycle/LiveData;", "", "Lcom/bingetv/app/data/database/CategoryEntity;", "getAllCategories", "()Landroidx/lifecycle/LiveData;", "allChannels", "Lcom/bingetv/app/data/database/ChannelEntity;", "getAllChannels", "favoriteChannels", "getFavoriteChannels", "clearAllData", "", "(Lkotlin/coroutines/Continuation;)Ljava/lang/Object;", "getChannelsByCategory", "category", "", "insertCategories", "categories", "(Ljava/util/List;Lkotlin/coroutines/Continuation;)Ljava/lang/Object;", "insertChannels", "channels", "searchChannels", "query", "toggleFavorite", "channelId", "", "isFavorite", "", "(JZLkotlin/coroutines/Continuation;)Ljava/lang/Object;", "app_debug"})
public final class ChannelRepository {
    @org.jetbrains.annotations.NotNull
    private final com.bingetv.app.data.database.ChannelDao channelDao = null;
    @org.jetbrains.annotations.NotNull
    private final com.bingetv.app.data.database.CategoryDao categoryDao = null;
    @org.jetbrains.annotations.NotNull
    private final androidx.lifecycle.LiveData<java.util.List<com.bingetv.app.data.database.ChannelEntity>> allChannels = null;
    @org.jetbrains.annotations.NotNull
    private final androidx.lifecycle.LiveData<java.util.List<com.bingetv.app.data.database.CategoryEntity>> allCategories = null;
    @org.jetbrains.annotations.NotNull
    private final androidx.lifecycle.LiveData<java.util.List<com.bingetv.app.data.database.ChannelEntity>> favoriteChannels = null;
    
    public ChannelRepository(@org.jetbrains.annotations.NotNull
    com.bingetv.app.data.database.ChannelDao channelDao, @org.jetbrains.annotations.NotNull
    com.bingetv.app.data.database.CategoryDao categoryDao) {
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
    public final java.lang.Object clearAllData(@org.jetbrains.annotations.NotNull
    kotlin.coroutines.Continuation<? super kotlin.Unit> $completion) {
        return null;
    }
}