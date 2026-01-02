package com.bingetv.app.data.repository;

@kotlin.Metadata(mv = {1, 9, 0}, k = 1, xi = 48, d1 = {"\u0000:\n\u0002\u0018\u0002\n\u0002\u0010\u0000\n\u0000\n\u0002\u0018\u0002\n\u0002\b\u0002\n\u0002\u0010\u0002\n\u0002\b\u0003\n\u0002\u0018\u0002\n\u0000\n\u0002\u0010\u000e\n\u0000\n\u0002\u0010\t\n\u0002\b\u0002\n\u0002\u0018\u0002\n\u0002\u0010 \n\u0002\b\u0005\u0018\u00002\u00020\u0001B\r\u0012\u0006\u0010\u0002\u001a\u00020\u0003\u00a2\u0006\u0002\u0010\u0004J\u0011\u0010\u0005\u001a\u00020\u0006H\u0086@\u00f8\u0001\u0000\u00a2\u0006\u0002\u0010\u0007J\u0011\u0010\b\u001a\u00020\u0006H\u0086@\u00f8\u0001\u0000\u00a2\u0006\u0002\u0010\u0007J#\u0010\t\u001a\u0004\u0018\u00010\n2\u0006\u0010\u000b\u001a\u00020\f2\u0006\u0010\r\u001a\u00020\u000eH\u0086@\u00f8\u0001\u0000\u00a2\u0006\u0002\u0010\u000fJ\"\u0010\u0010\u001a\u000e\u0012\n\u0012\b\u0012\u0004\u0012\u00020\n0\u00120\u00112\u0006\u0010\u000b\u001a\u00020\f2\u0006\u0010\u0013\u001a\u00020\u000eJ\u001f\u0010\u0014\u001a\u00020\u00062\f\u0010\u0015\u001a\b\u0012\u0004\u0012\u00020\n0\u0012H\u0086@\u00f8\u0001\u0000\u00a2\u0006\u0002\u0010\u0016R\u000e\u0010\u0002\u001a\u00020\u0003X\u0082\u0004\u00a2\u0006\u0002\n\u0000\u0082\u0002\u0004\n\u0002\b\u0019\u00a8\u0006\u0017"}, d2 = {"Lcom/bingetv/app/data/repository/EpgRepository;", "", "epgDao", "Lcom/bingetv/app/data/database/EpgDao;", "(Lcom/bingetv/app/data/database/EpgDao;)V", "cleanOldPrograms", "", "(Lkotlin/coroutines/Continuation;)Ljava/lang/Object;", "clearAllPrograms", "getCurrentProgram", "Lcom/bingetv/app/data/database/EpgProgramEntity;", "channelId", "", "time", "", "(Ljava/lang/String;JLkotlin/coroutines/Continuation;)Ljava/lang/Object;", "getProgramsForChannel", "Landroidx/lifecycle/LiveData;", "", "currentTime", "insertPrograms", "programs", "(Ljava/util/List;Lkotlin/coroutines/Continuation;)Ljava/lang/Object;", "app_release"})
public final class EpgRepository {
    @org.jetbrains.annotations.NotNull
    private final com.bingetv.app.data.database.EpgDao epgDao = null;
    
    public EpgRepository(@org.jetbrains.annotations.NotNull
    com.bingetv.app.data.database.EpgDao epgDao) {
        super();
    }
    
    @org.jetbrains.annotations.NotNull
    public final androidx.lifecycle.LiveData<java.util.List<com.bingetv.app.data.database.EpgProgramEntity>> getProgramsForChannel(@org.jetbrains.annotations.NotNull
    java.lang.String channelId, long currentTime) {
        return null;
    }
    
    @org.jetbrains.annotations.Nullable
    public final java.lang.Object getCurrentProgram(@org.jetbrains.annotations.NotNull
    java.lang.String channelId, long time, @org.jetbrains.annotations.NotNull
    kotlin.coroutines.Continuation<? super com.bingetv.app.data.database.EpgProgramEntity> $completion) {
        return null;
    }
    
    @org.jetbrains.annotations.Nullable
    public final java.lang.Object insertPrograms(@org.jetbrains.annotations.NotNull
    java.util.List<com.bingetv.app.data.database.EpgProgramEntity> programs, @org.jetbrains.annotations.NotNull
    kotlin.coroutines.Continuation<? super kotlin.Unit> $completion) {
        return null;
    }
    
    @org.jetbrains.annotations.Nullable
    public final java.lang.Object cleanOldPrograms(@org.jetbrains.annotations.NotNull
    kotlin.coroutines.Continuation<? super kotlin.Unit> $completion) {
        return null;
    }
    
    @org.jetbrains.annotations.Nullable
    public final java.lang.Object clearAllPrograms(@org.jetbrains.annotations.NotNull
    kotlin.coroutines.Continuation<? super kotlin.Unit> $completion) {
        return null;
    }
}