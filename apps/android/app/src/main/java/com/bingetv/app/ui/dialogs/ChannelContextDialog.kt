package com.bingetv.app.ui.dialogs

import android.app.Dialog
import android.content.Context
import android.os.Bundle
import android.widget.Button
import android.widget.ImageView
import android.widget.TextView
import com.bingetv.app.R
import com.bingetv.app.data.database.ChannelEntity
import com.bingetv.app.utils.ImageLoader

class ChannelContextDialog(
    context: Context,
    private val channel: ChannelEntity,
    private val onToggleFavorite: (ChannelEntity) -> Unit,
    private val onPlayChannel: (ChannelEntity) -> Unit
) : Dialog(context, R.style.Theme_Leanback) {
    
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.dialog_channel_context)
        
        setupViews()
    }
    
    private fun setupViews() {
        val logoImage: ImageView = findViewById(R.id.channel_logo)
        val nameText: TextView = findViewById(R.id.channel_name)
        val categoryText: TextView = findViewById(R.id.channel_category)
        val playButton: Button = findViewById(R.id.play_button)
        val favoriteButton: Button = findViewById(R.id.favorite_button)
        val closeButton: Button = findViewById(R.id.close_button)
        
        // Display channel info
        ImageLoader.loadChannelLogo(context, channel.logoUrl, logoImage)
        nameText.text = channel.name
        categoryText.text = channel.category ?: "Uncategorized"
        
        // Update favorite button text
        favoriteButton.text = if (channel.isFavorite) {
            "Remove from Favorites"
        } else {
            "Add to Favorites"
        }
        
        // Button listeners
        playButton.setOnClickListener {
            onPlayChannel(channel)
            dismiss()
        }
        
        favoriteButton.setOnClickListener {
            onToggleFavorite(channel)
            dismiss()
        }
        
        closeButton.setOnClickListener {
            dismiss()
        }
    }
}
