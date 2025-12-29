package com.bingetv.app.data.database;

@kotlin.Metadata(mv = {1, 9, 0}, k = 1, xi = 48, d1 = {"\u0000\u001e\n\u0002\u0018\u0002\n\u0002\u0010\u0000\n\u0000\n\u0002\u0018\u0002\n\u0002\u0018\u0002\n\u0002\b\u0002\n\u0002\u0010\u0002\n\u0002\b\u0003\bg\u0018\u00002\u00020\u0001J\u000e\u0010\u0002\u001a\b\u0012\u0004\u0012\u00020\u00040\u0003H\'J\n\u0010\u0005\u001a\u0004\u0018\u00010\u0004H\'J\u0010\u0010\u0006\u001a\u00020\u00072\u0006\u0010\b\u001a\u00020\u0004H\'J\u0010\u0010\t\u001a\u00020\u00072\u0006\u0010\b\u001a\u00020\u0004H\'\u00a8\u0006\n"}, d2 = {"Lcom/bingetv/app/data/database/UserPreferencesDao;", "", "getPreferences", "Landroidx/lifecycle/LiveData;", "Lcom/bingetv/app/data/database/UserPreferencesEntity;", "getPreferencesSync", "insertPreferences", "", "preferences", "updatePreferences", "app_debug"})
@androidx.room.Dao
public abstract interface UserPreferencesDao {
    
    @androidx.room.Query(value = "SELECT * FROM user_preferences WHERE id = 1 LIMIT 1")
    @org.jetbrains.annotations.NotNull
    public abstract androidx.lifecycle.LiveData<com.bingetv.app.data.database.UserPreferencesEntity> getPreferences();
    
    @androidx.room.Query(value = "SELECT * FROM user_preferences WHERE id = 1 LIMIT 1")
    @org.jetbrains.annotations.Nullable
    public abstract com.bingetv.app.data.database.UserPreferencesEntity getPreferencesSync();
    
    @androidx.room.Insert(onConflict = 1)
    public abstract void insertPreferences(@org.jetbrains.annotations.NotNull
    com.bingetv.app.data.database.UserPreferencesEntity preferences);
    
    @androidx.room.Update
    public abstract void updatePreferences(@org.jetbrains.annotations.NotNull
    com.bingetv.app.data.database.UserPreferencesEntity preferences);
}