package com.bingetv.app.data.repository

import androidx.lifecycle.LiveData
import com.bingetv.app.data.database.PlaylistDao
import com.bingetv.app.data.database.PlaylistEntity
import com.bingetv.app.data.api.ApiClient
import com.bingetv.app.data.api.XtreamCodesApi
import com.bingetv.app.model.Channel
import com.bingetv.app.parser.XtreamCodesParser
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.withContext

class PlaylistRepository(private val playlistDao: PlaylistDao) {
    
    val allPlaylists: LiveData<List<PlaylistEntity>> = playlistDao.getAllPlaylists()
    
    suspend fun getActivePlaylist(): PlaylistEntity? = withContext(Dispatchers.IO) {
        playlistDao.getActivePlaylist()
    }
    
    suspend fun insertPlaylist(playlist: PlaylistEntity): Long = withContext(Dispatchers.IO) {
        playlistDao.insertPlaylist(playlist)
    }
    
    suspend fun updatePlaylist(playlist: PlaylistEntity) = withContext(Dispatchers.IO) {
        playlistDao.updatePlaylist(playlist)
    }
    
    suspend fun deletePlaylist(playlist: PlaylistEntity) = withContext(Dispatchers.IO) {
        playlistDao.deletePlaylist(playlist)
    }
    
    suspend fun activatePlaylist(playlistId: Long) = withContext(Dispatchers.IO) {
        playlistDao.deactivateAllPlaylists()
        playlistDao.activatePlaylist(playlistId)
    }
    
    suspend fun testXtreamConnection(serverUrl: String, username: String, password: String): Result<Boolean> {
        return withContext(Dispatchers.IO) {
            try {
                val api = ApiClient.getXtreamApi(serverUrl)
                val response = api.authenticate(username, password)
                
                if (response.isSuccessful && response.body() != null) {
                    val authResponse = response.body()!!
                    if (authResponse.userInfo.auth == 1) {
                        Result.success(true)
                    } else {
                        Result.failure(Exception("Authentication failed: ${authResponse.userInfo.message}"))
                    }
                } else {
                    Result.failure(Exception("Server error: ${response.code()}"))
                }
            } catch (e: Exception) {
                Result.failure(e)
            }
        }
    }

    suspend fun getXtreamPlaylist(serverUrl: String, username: String, password: String): List<Channel> {
        return withContext(Dispatchers.IO) {
            val api = ApiClient.getXtreamApi(serverUrl)
            
            // 1. Fetch Live Streams
            val liveResponse = try { api.getLiveStreams(username, password) } catch(e: Exception) { null }
            
            // 2. Fetch VOD Streams
            val vodResponse = try { api.getVodStreams(username, password) } catch(e: Exception) { null }
            
            // 3. Fetch Series
            val seriesResponse = try { api.getSeries(username, password) } catch(e: Exception) { null }

            val allItems = mutableListOf<com.bingetv.app.data.api.XtreamChannel>()
            
            if (liveResponse?.isSuccessful == true && liveResponse.body() != null) {
                allItems.addAll(liveResponse.body()!!)
            }
            if (vodResponse?.isSuccessful == true && vodResponse.body() != null) {
                // VOD streams
                allItems.addAll(vodResponse.body()!!)
            }
            if (seriesResponse?.isSuccessful == true && seriesResponse.body() != null) {
                // Series items. 
                // Note: Series won't have 'stream_type' set usually, so we might want to manually tag them if parser doesn't
                // But XtreamChannel model now handles basic compatibility.
                allItems.addAll(seriesResponse.body()!!)
            }
            
            if (allItems.isNotEmpty()) {
                val parser = XtreamCodesParser()
                parser.parseChannels(allItems, serverUrl, username, password)
            } else {
                 throw Exception("Failed to fetch any content (Live, VOD, or Series)")
            }
        }
    }
}
