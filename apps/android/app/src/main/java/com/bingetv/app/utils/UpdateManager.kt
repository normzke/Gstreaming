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

    private const val UPDATE_URL = "https://raw.githubusercontent.com/bingetv/app/main/update.json" // Placeholder, should be user's server

    suspend fun checkForUpdates(context: Context, onComplete: () -> Unit) {
        try {
            // We use a temporary Retrofit instance or add to ApiClient
            val api = ApiClient.getXtreamApi("https://raw.githubusercontent.com/") // Point to a base URL
            // Actually, better to have a dedicated UpdateApi check
            
            val update = withContext(Dispatchers.IO) {
                // For now, let's pretend we fetch from a static URL
                // In a real scenario, this would be the user's domain
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
        // Implementation logic:
        // val api = ApiClient.getUpdateApi(UPDATE_URL)
        // val response = api.checkUpdate()
        // return if (response.isSuccessful) response.body() else null
        
        return null // Replace with actual API call when UPDATE_URL is ready
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
