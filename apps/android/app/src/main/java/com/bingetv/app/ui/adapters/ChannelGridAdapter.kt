package com.bingetv.app.ui.adapters

import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.ImageView
import android.widget.TextView
import androidx.recyclerview.widget.DiffUtil
import androidx.recyclerview.widget.ListAdapter
import androidx.recyclerview.widget.RecyclerView
import com.bingetv.app.R
import com.bingetv.app.data.database.ChannelEntity
import com.bingetv.app.utils.ImageLoader

class ChannelGridAdapter(
    private val onChannelClick: (ChannelEntity) -> Unit,
    private val onChannelLongClick: ((ChannelEntity) -> Unit)? = null
) : ListAdapter<ChannelEntity, ChannelGridAdapter.ChannelViewHolder>(ChannelDiffCallback()) {
    
    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): ChannelViewHolder {
        val view = LayoutInflater.from(parent.context)
            .inflate(R.layout.item_channel_card, parent, false)
        return ChannelViewHolder(view)
    }
    
    override fun onBindViewHolder(holder: ChannelViewHolder, position: Int) {
        val channel = getItem(position)
        holder.bind(channel)
        
        holder.itemView.setOnClickListener {
            onChannelClick(channel)
        }
        
        // Long-press for context menu
        holder.itemView.setOnLongClickListener {
            onChannelLongClick?.invoke(channel)
            true
        }
        
        // Focus animations
        holder.itemView.setOnFocusChangeListener { view, hasFocus ->
            if (hasFocus) {
                view.animate()
                    .scaleX(1.1f)
                    .scaleY(1.1f)
                    .setDuration(200)
                    .start()
            } else {
                view.animate()
                    .scaleX(1.0f)
                    .scaleY(1.0f)
                    .setDuration(200)
                    .start()
            }
        }
    }
    
    class ChannelViewHolder(itemView: View) : RecyclerView.ViewHolder(itemView) {
        private val logoImage: ImageView = itemView.findViewById(R.id.channel_logo)
        private val nameText: TextView = itemView.findViewById(R.id.channel_name)
        private val favoriteIcon: ImageView = itemView.findViewById(R.id.favorite_icon)
        
        fun bind(channel: ChannelEntity) {
            nameText.text = channel.name
            
            // Load logo
            ImageLoader.loadChannelLogo(itemView.context, channel.logoUrl, logoImage)
            
            // Show favorite icon
            favoriteIcon.visibility = if (channel.isFavorite) View.VISIBLE else View.GONE
        }
    }
    
    class ChannelDiffCallback : DiffUtil.ItemCallback<ChannelEntity>() {
        override fun areItemsTheSame(oldItem: ChannelEntity, newItem: ChannelEntity): Boolean {
            return oldItem.id == newItem.id
        }
        
        override fun areContentsTheSame(oldItem: ChannelEntity, newItem: ChannelEntity): Boolean {
            return oldItem == newItem
        }
    }
}
