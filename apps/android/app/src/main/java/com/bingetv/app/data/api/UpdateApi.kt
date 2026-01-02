package com.bingetv.app.data.api

import com.google.gson.annotations.SerializedName
import retrofit2.Response
import retrofit2.http.GET

data class AppUpdate(
    @SerializedName("versionCode") val versionCode: Int,
    @SerializedName("versionName") val versionName: String,
    @SerializedName("updateUrl") val updateUrl: String,
    @SerializedName("releaseNotes") val releaseNotes: String,
    @SerializedName("isMandatory") val isMandatory: Boolean = false
)

interface UpdateApi {
    @GET("update.json")
    suspend fun checkUpdate(): Response<AppUpdate>
}
