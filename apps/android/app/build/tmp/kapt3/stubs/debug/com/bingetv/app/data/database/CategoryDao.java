package com.bingetv.app.data.database;

@kotlin.Metadata(mv = {1, 9, 0}, k = 1, xi = 48, d1 = {"\u0000 \n\u0002\u0018\u0002\n\u0002\u0010\u0000\n\u0000\n\u0002\u0010\u0002\n\u0000\n\u0002\u0018\u0002\n\u0002\u0010 \n\u0002\u0018\u0002\n\u0002\b\u0006\bg\u0018\u00002\u00020\u0001J\b\u0010\u0002\u001a\u00020\u0003H\'J\u0014\u0010\u0004\u001a\u000e\u0012\n\u0012\b\u0012\u0004\u0012\u00020\u00070\u00060\u0005H\'J\u000e\u0010\b\u001a\b\u0012\u0004\u0012\u00020\u00070\u0006H\'J\u0016\u0010\t\u001a\u00020\u00032\f\u0010\n\u001a\b\u0012\u0004\u0012\u00020\u00070\u0006H\'J\u0010\u0010\u000b\u001a\u00020\u00032\u0006\u0010\f\u001a\u00020\u0007H\'\u00a8\u0006\r"}, d2 = {"Lcom/bingetv/app/data/database/CategoryDao;", "", "deleteAllCategories", "", "getAllCategories", "Landroidx/lifecycle/LiveData;", "", "Lcom/bingetv/app/data/database/CategoryEntity;", "getAllCategoriesSync", "insertCategories", "categories", "insertCategory", "category", "app_debug"})
@androidx.room.Dao
public abstract interface CategoryDao {
    
    @androidx.room.Query(value = "SELECT * FROM categories ORDER BY sortOrder ASC, categoryName ASC")
    @org.jetbrains.annotations.NotNull
    public abstract androidx.lifecycle.LiveData<java.util.List<com.bingetv.app.data.database.CategoryEntity>> getAllCategories();
    
    @androidx.room.Query(value = "SELECT * FROM categories ORDER BY sortOrder ASC, categoryName ASC")
    @org.jetbrains.annotations.NotNull
    public abstract java.util.List<com.bingetv.app.data.database.CategoryEntity> getAllCategoriesSync();
    
    @androidx.room.Insert(onConflict = 1)
    public abstract void insertCategory(@org.jetbrains.annotations.NotNull
    com.bingetv.app.data.database.CategoryEntity category);
    
    @androidx.room.Insert(onConflict = 1)
    public abstract void insertCategories(@org.jetbrains.annotations.NotNull
    java.util.List<com.bingetv.app.data.database.CategoryEntity> categories);
    
    @androidx.room.Query(value = "DELETE FROM categories")
    public abstract void deleteAllCategories();
}