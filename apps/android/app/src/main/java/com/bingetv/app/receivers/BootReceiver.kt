package com.bingetv.app.receivers

import android.content.BroadcastReceiver
import android.content.Context
import android.content.Intent
import com.bingetv.app.ui.splash.SplashActivity
import com.bingetv.app.utils.PreferencesManager

class BootReceiver : BroadcastReceiver() {
    override fun onReceive(context: Context, intent: Intent) {
        if (intent.action == Intent.ACTION_BOOT_COMPLETED || intent.action == "android.intent.action.QUICKBOOT_POWERON") {
            val prefs = PreferencesManager(context)
            if (prefs.isAutoStartBoot()) {
                val launchIntent = Intent(context, SplashActivity::class.java)
                launchIntent.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_BROUGHT_TO_FRONT) // Need NEW_TASK for receiver
                context.startActivity(launchIntent)
            }
        }
    }
}
