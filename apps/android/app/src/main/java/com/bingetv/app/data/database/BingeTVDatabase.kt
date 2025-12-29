package com.bingetv.app.data.database

import android.content.Context
import androidx.room.Database
import androidx.room.Room
import androidx.room.RoomDatabase

@Database(
    entities = [
        ChannelEntity::class,
        CategoryEntity::class,
        EpgProgramEntity::class,
        PlaylistEntity::class,
        UserPreferencesEntity::class
    ],
    version = 1,
    exportSchema = true
)
abstract class BingeTVDatabase : RoomDatabase() {
    abstract fun channelDao(): ChannelDao
    abstract fun categoryDao(): CategoryDao
    abstract fun epgDao(): EpgDao
    abstract fun playlistDao(): PlaylistDao
    abstract fun userPreferencesDao(): UserPreferencesDao

    companion object {
        @Volatile
        private var INSTANCE: BingeTVDatabase? = null

        fun getDatabase(context: Context): BingeTVDatabase {
            return INSTANCE ?: synchronized(this) {
                val instance = Room.databaseBuilder(
                    context.applicationContext,
                    BingeTVDatabase::class.java,
                    "bingetv_database"
                )
                    .fallbackToDestructiveMigration()
                    .build()
                INSTANCE = instance
                instance
            }
        }
    }
}
