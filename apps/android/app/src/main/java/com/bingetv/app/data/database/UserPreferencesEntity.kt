package com.bingetv.app.data.database

import androidx.room.Entity
import androidx.room.PrimaryKey
import android.os.Parcelable
import kotlinx.parcelize.Parcelize

@Parcelize
@Entity(tableName = "user_preferences")
data class UserPreferencesEntity(
    @PrimaryKey
    var id: Int = 1,
    var gridColumns: Int = 5,
    var logoSize: String = "medium", // small, medium, large
    var showChannelNumbers: Boolean = true,
    var showNowPlaying: Boolean = true,
    var parentalControlEnabled: Boolean = false,
    var parentalControlPin: String? = null,
    var defaultQuality: String = "auto",
    var autoPlayNext: Boolean = false,
    var theme: String = "dark" // dark, light, auto
) : Parcelable
