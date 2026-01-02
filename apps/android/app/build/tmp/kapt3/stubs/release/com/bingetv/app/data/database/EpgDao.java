package com.bingetv.app.data.database;

@kotlin.Metadata(mv = {1, 9, 0}, k = 1, xi = 48, d1 = {"\u00002\n\u0002\u0018\u0002\n\u0002\u0010\u0000\n\u0000\n\u0002\u0010\u0002\n\u0002\b\u0002\n\u0002\u0010\t\n\u0000\n\u0002\u0010 \n\u0002\u0018\u0002\n\u0002\b\u0003\n\u0002\u0010\u000e\n\u0000\n\u0002\u0018\u0002\n\u0002\b\u0003\bg\u0018\u00002\u00020\u0001J\b\u0010\u0002\u001a\u00020\u0003H\'J\u0010\u0010\u0004\u001a\u00020\u00032\u0006\u0010\u0005\u001a\u00020\u0006H\'J\u0016\u0010\u0007\u001a\b\u0012\u0004\u0012\u00020\t0\b2\u0006\u0010\n\u001a\u00020\u0006H\'J\u001a\u0010\u000b\u001a\u0004\u0018\u00010\t2\u0006\u0010\f\u001a\u00020\r2\u0006\u0010\u0005\u001a\u00020\u0006H\'J$\u0010\u000e\u001a\u000e\u0012\n\u0012\b\u0012\u0004\u0012\u00020\t0\b0\u000f2\u0006\u0010\f\u001a\u00020\r2\u0006\u0010\n\u001a\u00020\u0006H\'J\u0016\u0010\u0010\u001a\u00020\u00032\f\u0010\u0011\u001a\b\u0012\u0004\u0012\u00020\t0\bH\'\u00a8\u0006\u0012"}, d2 = {"Lcom/bingetv/app/data/database/EpgDao;", "", "deleteAllPrograms", "", "deleteOldPrograms", "time", "", "getAllActivePrograms", "", "Lcom/bingetv/app/data/database/EpgProgramEntity;", "currentTime", "getCurrentProgram", "channelId", "", "getProgramsForChannel", "Landroidx/lifecycle/LiveData;", "insertPrograms", "programs", "app_release"})
@androidx.room.Dao
public abstract interface EpgDao {
    
    @androidx.room.Query(value = "SELECT * FROM epg_programs WHERE channelId = :channelId AND endTime > :currentTime ORDER BY startTime ASC")
    @org.jetbrains.annotations.NotNull
    public abstract androidx.lifecycle.LiveData<java.util.List<com.bingetv.app.data.database.EpgProgramEntity>> getProgramsForChannel(@org.jetbrains.annotations.NotNull
    java.lang.String channelId, long currentTime);
    
    @androidx.room.Query(value = "SELECT * FROM epg_programs WHERE channelId = :channelId AND startTime <= :time AND endTime > :time LIMIT 1")
    @org.jetbrains.annotations.Nullable
    public abstract com.bingetv.app.data.database.EpgProgramEntity getCurrentProgram(@org.jetbrains.annotations.NotNull
    java.lang.String channelId, long time);
    
    @androidx.room.Query(value = "SELECT * FROM epg_programs WHERE endTime > :currentTime")
    @org.jetbrains.annotations.NotNull
    public abstract java.util.List<com.bingetv.app.data.database.EpgProgramEntity> getAllActivePrograms(long currentTime);
    
    @androidx.room.Insert(onConflict = 1)
    public abstract void insertPrograms(@org.jetbrains.annotations.NotNull
    java.util.List<com.bingetv.app.data.database.EpgProgramEntity> programs);
    
    @androidx.room.Query(value = "DELETE FROM epg_programs WHERE endTime < :time")
    public abstract void deleteOldPrograms(long time);
    
    @androidx.room.Query(value = "DELETE FROM epg_programs")
    public abstract void deleteAllPrograms();
}