package com.bingetv.app.utils

import android.content.Context
import android.content.Intent
import android.net.Uri
import androidx.appcompat.app.AlertDialog
import com.bingetv.app.BuildConfig
import com.bingetv.app.data.api.ApiClient
import com.bingetv.app.data.api.UpdateApi
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.withContext

object UpdateManager {

    private const val UPDATE_JSON_Url = "https://bingetv.co.ke/update.json" // Full URL for Intent
    private const val BASE_URL = "https://bingetv.co.ke/" // Base URL for Retrofit

    suspend fun checkForUpdates(context: Context, onComplete: () -> Unit) {
        try {
            val update = withContext(Dispatchers.IO) {
                fetchUpdateInfo()
            }

            if (update != null && update.versionCode > BuildConfig.VERSION_CODE) {
                withContext(Dispatchers.Main) {
                    showUpdateDialog(context, update.versionName, update.releaseNotes, update.updateUrl, update.isMandatory, onComplete)
                }
            } else {
                onComplete()
            }
        } catch (e: Exception) {
            e.printStackTrace()
            onComplete()
        }
    }

    private suspend fun fetchUpdateInfo(): com.bingetv.app.data.api.AppUpdate? {
        try {
             // Use the new getUpdateApi method with the Base URL
             val api = ApiClient.getUpdateApi(BASE_URL)
             val response = api.checkUpdate() // Calls update.json
             return if (response.isSuccessful) response.body() else null
        } catch (e: Exception) {
            return null
        }
    }

    private fun showUpdateDialog(
        context: Context,
        version: String,
        notes: String,
        url: String,
        mandatory: Boolean,
        onComplete: () -> Unit
    ) {
        val builder = AlertDialog.Builder(context)
            .setTitle("New Update Available ($version)")
            .setMessage(notes)
            .setPositiveButton("Update Now") { _, _ ->
                val intent = Intent(Intent.ACTION_VIEW, Uri.parse(url))
                context.startActivity(intent)
                if (mandatory) {
                    // Force exit if they don't update? Standard for TV apps
                    (context as? android.app.Activity)?.finish()
                } else {
                    onComplete()
                }
            }
        
        if (!mandatory) {
            builder.setNegativeButton("Later") { _, _ -> onComplete() }
        } else {
            builder.setCancelable(false)
        }

        builder.show()
    }
}
