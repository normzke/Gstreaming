package com.bingetv.app.data.database

import androidx.room.Entity
import androidx.room.PrimaryKey
import android.os.Parcelable
import kotlinx.parcelize.Parcelize

@Parcelize
@Entity(tableName = "channels")
data class ChannelEntity(
    @PrimaryKey(autoGenerate = true)
    val id: Long = 0,
    val streamId: String,
    val name: String,
    val streamUrl: String,
    val logoUrl: String? = null,
    val category: String? = null,
    val categoryId: String? = null,
    val tvgId: String? = null,
    val tvgName: String? = null,
    val tvgLogo: String? = null,
    val tvgChno: String? = null,
    val epgChannelId: String? = null,
    val isFavorite: Boolean = false,
    val isLocked: Boolean = false,
    val sortOrder: Int = 0,
    val addedAt: Long = System.currentTimeMillis()
) : Parcelable

@Parcelize
@Entity(tableName = "categories")
data class CategoryEntity(
    @PrimaryKey(autoGenerate = true)
    val id: Long = 0,
    val categoryId: String,
    val categoryName: String,
    val parentId: String? = null,
    val sortOrder: Int = 0
) : Parcelable

@Entity(tableName = "epg_programs")
data class EpgProgramEntity(
    @PrimaryKey(autoGenerate = true)
    val id: Long = 0,
    val channelId: String,
    val title: String,
    val description: String? = null,
    val startTime: Long,
    val endTime: Long,
    val category: String? = null,
    val icon: String? = null,
    val rating: String? = null
)

@Entity(tableName = "playlists")
data class PlaylistEntity(
    @PrimaryKey(autoGenerate = true)
    val id: Long = 0,
    val name: String,
    val type: String, // "xtream" or "m3u"
    val serverUrl: String? = null,
    val username: String? = null,
    val password: String? = null,
    val m3uUrl: String? = null,
    val isActive: Boolean = false,
    val lastSync: Long = 0,
    val createdAt: Long = System.currentTimeMillis()
)

@Entity(tableName = "user_preferences")
data class UserPreferencesEntity(
    @PrimaryKey
    val id: Int = 1,
    val gridColumns: Int = 5,
    val logoSize: String = "medium", // small, medium, large
    val showChannelNumbers: Boolean = true,
    val showNowPlaying: Boolean = true,
    val parentalControlEnabled: Boolean = false,
    val parentalControlPin: String? = null,
    val defaultQuality: String = "auto",
    val autoPlayNext: Boolean = false,
    val theme: String = "dark" // dark, light, auto
)
