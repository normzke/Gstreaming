package com.bingetv.app

import android.os.Bundle
import androidx.fragment.app.FragmentActivity

class PlaylistBrowserActivity : FragmentActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        // This activity can be used for browsing multiple playlists
        // For now, redirect to MainActivity
        finish()
    }
}

