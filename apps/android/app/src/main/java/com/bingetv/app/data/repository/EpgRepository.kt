package com.bingetv.app.data.repository

import androidx.lifecycle.LiveData
import com.bingetv.app.data.database.EpgDao
import com.bingetv.app.data.database.EpgProgramEntity
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.withContext

class EpgRepository(private val epgDao: EpgDao) {
    
    fun getProgramsForChannel(channelId: String, currentTime: Long): LiveData<List<EpgProgramEntity>> {
        return epgDao.getProgramsForChannel(channelId, currentTime)
    }
    
    suspend fun getCurrentProgram(channelId: String, time: Long): EpgProgramEntity? = withContext(Dispatchers.IO) {
        epgDao.getCurrentProgram(channelId, time)
    }
    
    suspend fun insertPrograms(programs: List<EpgProgramEntity>) = withContext(Dispatchers.IO) {
        epgDao.insertPrograms(programs)
    }
    
    suspend fun cleanOldPrograms() = withContext(Dispatchers.IO) {
        val currentTime = System.currentTimeMillis()
        epgDao.deleteOldPrograms(currentTime - (24 * 60 * 60 * 1000)) // Keep last 24 hours
    }
    
    suspend fun clearAllPrograms() = withContext(Dispatchers.IO) {
        epgDao.deleteAllPrograms()
    }
}
