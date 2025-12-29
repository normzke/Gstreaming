package com.bingetv.app

import android.view.ViewGroup
import androidx.leanback.widget.ImageCardView
import androidx.leanback.widget.Presenter
import com.bingetv.app.model.Channel

class CardPresenter : Presenter() {
    private val CARD_WIDTH = 313
    private val CARD_HEIGHT = 176
    
    override fun onCreateViewHolder(parent: ViewGroup): ViewHolder {
        val cardView = ImageCardView(parent.context)
        cardView.isFocusable = true
        cardView.isFocusableInTouchMode = true
        cardView.setMainImageDimensions(CARD_WIDTH, CARD_HEIGHT)
        return ViewHolder(cardView)
    }
    
    override fun onBindViewHolder(viewHolder: ViewHolder, item: Any) {
        val channel = item as Channel
        val cardView = viewHolder.view as ImageCardView
        
        cardView.titleText = channel.name
        cardView.contentText = channel.group ?: "Channel"
        
        // Load logo if available
        if (!channel.logo.isNullOrEmpty()) {
            // You can use Glide or Picasso here to load images
            // For now, we'll just set the title
        }
    }
    
    override fun onUnbindViewHolder(viewHolder: ViewHolder) {
        val cardView = viewHolder.view as ImageCardView
        cardView.badgeImage = null
        cardView.mainImage = null
    }
}

