package com.bingetv.app.data.database

import androidx.lifecycle.LiveData
import androidx.room.*

@Dao
interface ChannelDao {
    @Query("SELECT * FROM channels ORDER BY sortOrder ASC, name ASC")
    fun getAllChannels(): LiveData<List<ChannelEntity>>
    
    @Query("SELECT * FROM channels WHERE category = :category ORDER BY sortOrder ASC, name ASC")
    fun getChannelsByCategory(category: String): LiveData<List<ChannelEntity>>
    
    @Query("SELECT * FROM channels WHERE isFavorite = 1 ORDER BY sortOrder ASC, name ASC")
    fun getFavoriteChannels(): LiveData<List<ChannelEntity>>
    
    @Query("SELECT * FROM channels WHERE name LIKE '%' || :query || '%' OR category LIKE '%' || :query || '%'")
    fun searchChannels(query: String): LiveData<List<ChannelEntity>>
    
    @Query("SELECT * FROM channels WHERE id = :id")
    fun getChannelById(id: Long): ChannelEntity?
    
    @Insert(onConflict = OnConflictStrategy.REPLACE)
    fun insertChannel(channel: ChannelEntity): Long
    
    @Insert(onConflict = OnConflictStrategy.REPLACE)
    fun insertChannels(channels: List<ChannelEntity>)
    
    @Update
    fun updateChannel(channel: ChannelEntity)
    
    @Delete
    fun deleteChannel(channel: ChannelEntity)
    
    @Query("DELETE FROM channels")
    fun deleteAllChannels()
    
    @Query("UPDATE channels SET isFavorite = :isFavorite WHERE id = :channelId")
    fun updateFavoriteStatus(channelId: Long, isFavorite: Boolean)
}

@Dao
interface CategoryDao {
    @Query("SELECT * FROM categories ORDER BY sortOrder ASC, categoryName ASC")
    fun getAllCategories(): LiveData<List<CategoryEntity>>
    
    @Query("SELECT * FROM categories ORDER BY sortOrder ASC, categoryName ASC")
    fun getAllCategoriesSync(): List<CategoryEntity>
    
    @Insert(onConflict = OnConflictStrategy.REPLACE)
    fun insertCategory(category: CategoryEntity)
    
    @Insert(onConflict = OnConflictStrategy.REPLACE)
    fun insertCategories(categories: List<CategoryEntity>)
    
    @Query("DELETE FROM categories")
    fun deleteAllCategories()
}

@Dao
interface EpgDao {
    @Query("SELECT * FROM epg_programs WHERE channelId = :channelId AND endTime > :currentTime ORDER BY startTime ASC")
    fun getProgramsForChannel(channelId: String, currentTime: Long): LiveData<List<EpgProgramEntity>>
    
    @Query("SELECT * FROM epg_programs WHERE channelId = :channelId AND startTime <= :time AND endTime > :time LIMIT 1")
    fun getCurrentProgram(channelId: String, time: Long): EpgProgramEntity?
    
    @Query("SELECT * FROM epg_programs WHERE endTime > :currentTime")
    fun getAllActivePrograms(currentTime: Long): List<EpgProgramEntity>
    
    @Insert(onConflict = OnConflictStrategy.REPLACE)
    fun insertPrograms(programs: List<EpgProgramEntity>)
    
    @Query("DELETE FROM epg_programs WHERE endTime < :time")
    fun deleteOldPrograms(time: Long)
    
    @Query("DELETE FROM epg_programs")
    fun deleteAllPrograms()
}

@Dao
interface PlaylistDao {
    @Query("SELECT * FROM playlists ORDER BY createdAt DESC")
    fun getAllPlaylists(): LiveData<List<PlaylistEntity>>
    
    @Query("SELECT * FROM playlists WHERE isActive = 1 LIMIT 1")
    fun getActivePlaylist(): PlaylistEntity?
    
    @Insert(onConflict = OnConflictStrategy.REPLACE)
    fun insertPlaylist(playlist: PlaylistEntity): Long
    
    @Update
    fun updatePlaylist(playlist: PlaylistEntity)
    
    @Delete
    fun deletePlaylist(playlist: PlaylistEntity)
    
    @Query("UPDATE playlists SET isActive = 0")
    fun deactivateAllPlaylists()
    
    @Query("UPDATE playlists SET isActive = 1 WHERE id = :playlistId")
    fun activatePlaylist(playlistId: Long)
}

@Dao
interface UserPreferencesDao {
    @Query("SELECT * FROM user_preferences WHERE id = 1 LIMIT 1")
    fun getPreferences(): LiveData<UserPreferencesEntity>
    
    @Query("SELECT * FROM user_preferences WHERE id = 1 LIMIT 1")
    fun getPreferencesSync(): UserPreferencesEntity?
    
    @Insert(onConflict = OnConflictStrategy.REPLACE)
    fun insertPreferences(preferences: UserPreferencesEntity)
    
    @Update
    fun updatePreferences(preferences: UserPreferencesEntity)
}

@Dao
interface WatchHistoryDao {
    @Query("SELECT * FROM watch_history ORDER BY watchedAt DESC LIMIT 50")
    fun getRecentHistory(): LiveData<List<WatchHistoryEntity>>
    
    @Insert(onConflict = OnConflictStrategy.REPLACE)
    fun insertHistory(history: WatchHistoryEntity)
    
    @Query("DELETE FROM watch_history")
    fun deleteAllHistory()
    
    @Query("DELETE FROM watch_history WHERE streamId = :streamId")
    fun deleteHistoryByStreamId(streamId: String)
}
