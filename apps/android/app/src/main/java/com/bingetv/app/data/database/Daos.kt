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
    suspend fun getChannelById(id: Long): ChannelEntity?
    
    @Insert(onConflict = OnConflictStrategy.REPLACE)
    suspend fun insertChannel(channel: ChannelEntity): Long
    
    @Insert(onConflict = OnConflictStrategy.REPLACE)
    suspend fun insertChannels(channels: List<ChannelEntity>)
    
    @Update
    suspend fun updateChannel(channel: ChannelEntity)
    
    @Delete
    suspend fun deleteChannel(channel: ChannelEntity)
    
    @Query("DELETE FROM channels")
    suspend fun deleteAllChannels()
    
    @Query("UPDATE channels SET isFavorite = :isFavorite WHERE id = :channelId")
    suspend fun updateFavoriteStatus(channelId: Long, isFavorite: Boolean)
}

@Dao
interface CategoryDao {
    @Query("SELECT * FROM categories ORDER BY sortOrder ASC, categoryName ASC")
    fun getAllCategories(): LiveData<List<CategoryEntity>>
    
    @Insert(onConflict = OnConflictStrategy.REPLACE)
    suspend fun insertCategory(category: CategoryEntity)
    
    @Insert(onConflict = OnConflictStrategy.REPLACE)
    suspend fun insertCategories(categories: List<CategoryEntity>)
    
    @Query("DELETE FROM categories")
    suspend fun deleteAllCategories()
}

@Dao
interface EpgDao {
    @Query("SELECT * FROM epg_programs WHERE channelId = :channelId AND endTime > :currentTime ORDER BY startTime ASC")
    fun getProgramsForChannel(channelId: String, currentTime: Long): LiveData<List<EpgProgramEntity>>
    
    @Query("SELECT * FROM epg_programs WHERE channelId = :channelId AND startTime <= :time AND endTime > :time LIMIT 1")
    suspend fun getCurrentProgram(channelId: String, time: Long): EpgProgramEntity?
    
    @Insert(onConflict = OnConflictStrategy.REPLACE)
    suspend fun insertPrograms(programs: List<EpgProgramEntity>)
    
    @Query("DELETE FROM epg_programs WHERE endTime < :time")
    suspend fun deleteOldPrograms(time: Long)
    
    @Query("DELETE FROM epg_programs")
    suspend fun deleteAllPrograms()
}

@Dao
interface PlaylistDao {
    @Query("SELECT * FROM playlists ORDER BY createdAt DESC")
    fun getAllPlaylists(): LiveData<List<PlaylistEntity>>
    
    @Query("SELECT * FROM playlists WHERE isActive = 1 LIMIT 1")
    suspend fun getActivePlaylist(): PlaylistEntity?
    
    @Insert(onConflict = OnConflictStrategy.REPLACE)
    suspend fun insertPlaylist(playlist: PlaylistEntity): Long
    
    @Update
    suspend fun updatePlaylist(playlist: PlaylistEntity)
    
    @Delete
    suspend fun deletePlaylist(playlist: PlaylistEntity)
    
    @Query("UPDATE playlists SET isActive = 0")
    suspend fun deactivateAllPlaylists()
    
    @Query("UPDATE playlists SET isActive = 1 WHERE id = :playlistId")
    suspend fun activatePlaylist(playlistId: Long)
}

@Dao
interface UserPreferencesDao {
    @Query("SELECT * FROM user_preferences WHERE id = 1 LIMIT 1")
    fun getPreferences(): LiveData<UserPreferencesEntity>
    
    @Query("SELECT * FROM user_preferences WHERE id = 1 LIMIT 1")
    suspend fun getPreferencesSync(): UserPreferencesEntity?
    
    @Insert(onConflict = OnConflictStrategy.REPLACE)
    suspend fun insertPreferences(preferences: UserPreferencesEntity)
    
    @Update
    suspend fun updatePreferences(preferences: UserPreferencesEntity)
}
