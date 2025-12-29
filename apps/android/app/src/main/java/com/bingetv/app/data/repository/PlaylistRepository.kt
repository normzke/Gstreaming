package com.bingetv.app.data.repository

import androidx.lifecycle.LiveData
import com.bingetv.app.data.database.PlaylistDao
import com.bingetv.app.data.database.PlaylistEntity
import com.bingetv.app.data.api.ApiClient
import com.bingetv.app.data.api.XtreamCodesApi
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
}
