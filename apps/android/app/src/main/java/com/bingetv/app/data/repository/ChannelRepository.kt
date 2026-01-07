package com.bingetv.app.data.repository

import androidx.lifecycle.LiveData
import com.bingetv.app.data.database.*
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.withContext

class ChannelRepository(
    private val channelDao: ChannelDao,
    private val categoryDao: CategoryDao,
    private val watchHistoryDao: WatchHistoryDao
) {
    // LiveData
    val allChannels: LiveData<List<ChannelEntity>> = channelDao.getAllChannels()
    val allCategories: LiveData<List<CategoryEntity>> = categoryDao.getAllCategories()
    val favoriteChannels: LiveData<List<ChannelEntity>> = channelDao.getFavoriteChannels()
    val watchHistory: LiveData<List<WatchHistoryEntity>> = watchHistoryDao.getRecentHistory()
    
    // Get channels by category
    fun getChannelsByCategory(category: String): LiveData<List<ChannelEntity>> {
        return channelDao.getChannelsByCategory(category)
    }
    
    // Search channels
    fun searchChannels(query: String): LiveData<List<ChannelEntity>> {
        return channelDao.searchChannels(query)
    }
    
    // Insert channels
    suspend fun insertChannels(channels: List<ChannelEntity>) = withContext(Dispatchers.IO) {
        channelDao.insertChannels(channels)
    }
    
    // Insert categories
    suspend fun insertCategories(categories: List<CategoryEntity>) = withContext(Dispatchers.IO) {
        categoryDao.insertCategories(categories)
    }
    
    // Toggle favorite
    suspend fun toggleFavorite(channelId: Long, isFavorite: Boolean) = withContext(Dispatchers.IO) {
        channelDao.updateFavoriteStatus(channelId, isFavorite)
    }
    
    // Record History
    suspend fun recordHistory(streamId: String) = withContext(Dispatchers.IO) {
        // First remove existing to bring to top
        watchHistoryDao.deleteHistoryByStreamId(streamId)
        watchHistoryDao.insertHistory(WatchHistoryEntity(streamId = streamId))
    }

    suspend fun clearHistory() = withContext(Dispatchers.IO) {
        watchHistoryDao.deleteAllHistory()
    }
    
    // Clear all data
    suspend fun clearAllData() = withContext(Dispatchers.IO) {
        channelDao.deleteAllChannels()
        categoryDao.deleteAllCategories()
    }
    
    suspend fun getChannelCount(): Int = withContext(Dispatchers.IO) {
        channelDao.getChannelCount()
    }
    
    // Paging
    fun getChannelsPaged(mode: String): kotlinx.coroutines.flow.Flow<androidx.paging.PagingData<ChannelEntity>> {
        return androidx.paging.Pager(
            config = androidx.paging.PagingConfig(
                pageSize = 60,
                prefetchDistance = 120,
                enablePlaceholders = true,
                initialLoadSize = 180,
                maxSize = 600
            ),
            pagingSourceFactory = {
                when {
                    mode == "all" || mode == "live" -> channelDao.getAllChannelsPaged() // "live" defaults to all for now or needs filtering?
                    mode == "favorites" -> channelDao.getFavoriteChannelsPaged()
                    mode.startsWith("search:") -> channelDao.searchChannelsPaged(mode.removePrefix("search:"))
                    else -> channelDao.getChannelsByCategoryPaged(mode)
                }
            }
        ).flow
    }
    
    suspend fun getChannelsByStreamIds(ids: List<String>): List<ChannelEntity> = withContext(Dispatchers.IO) {
        channelDao.getChannelsByStreamIds(ids)
    }
}
