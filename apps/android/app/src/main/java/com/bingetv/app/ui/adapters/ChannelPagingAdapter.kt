package com.bingetv.app.ui.adapters

import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.ImageView
import android.widget.TextView
import androidx.paging.PagingDataAdapter
import androidx.recyclerview.widget.DiffUtil
import androidx.recyclerview.widget.RecyclerView
import com.bingetv.app.R
import com.bingetv.app.data.database.ChannelEntity
import com.bingetv.app.utils.ImageLoader

class ChannelPagingAdapter(
    private val onChannelClick: (ChannelEntity) -> Unit,
    private val onChannelLongClick: ((ChannelEntity) -> Unit)? = null,
    private val onChannelFocused: ((ChannelEntity) -> Unit)? = null
) : PagingDataAdapter<ChannelEntity, ChannelPagingAdapter.ChannelViewHolder>(ChannelDiffCallback()) {
    
    // EPG Logic
    private var epgData: Map<String, List<com.bingetv.app.data.database.EpgProgramEntity>> = emptyMap()
    
    fun submitEpgData(data: Map<String, List<com.bingetv.app.data.database.EpgProgramEntity>>) {
        epgData = data
        // Only refresh visible items to avoid expensive full list re-bind
        notifyItemRangeChanged(0, itemCount, "PAYLOAD_EPG_UPDATE")
    }

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): ChannelViewHolder {
        val view = LayoutInflater.from(parent.context)
            .inflate(R.layout.item_channel_card, parent, false)
        return ChannelViewHolder(view)
    }
    
    override fun onBindViewHolder(holder: ChannelViewHolder, position: Int) {
        val channel = getItem(position)
        
        if (channel == null) {
            // Placeholder binding
            holder.bindPlaceholder()
        } else {
            // Try to find EPG using epgChannelId key first, then streamId
            val programs = if (channel.epgChannelId != null) epgData[channel.epgChannelId] else null
            holder.bind(channel, programs)
            
            holder.itemView.setOnClickListener {
                onChannelClick(channel)
            }
            
            // Long-press for context menu
            holder.itemView.setOnLongClickListener {
                onChannelLongClick?.invoke(channel)
                true
            }
            
            // Focus animations and callback
            holder.itemView.setOnFocusChangeListener { view, hasFocus ->
                if (hasFocus) {
                    onChannelFocused?.invoke(channel)
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
    }
    
    class ChannelViewHolder(itemView: View) : RecyclerView.ViewHolder(itemView) {
        private val logoImage: ImageView = itemView.findViewById(R.id.channel_logo)
        private val nameText: TextView = itemView.findViewById(R.id.channel_name)
        private val favoriteIcon: ImageView = itemView.findViewById(R.id.favorite_icon)
        
        // EPG Views
        private val epgNow: TextView = itemView.findViewById(R.id.epg_now)
        private val epgNext: TextView = itemView.findViewById(R.id.epg_next)
        private val epgProgress: android.widget.ProgressBar = itemView.findViewById(R.id.epg_progress)
        
        fun bindPlaceholder() {
            nameText.text = "Loading..."
            logoImage.setImageDrawable(null)
            epgNow.text = ""
            epgNext.visibility = View.GONE
            favoriteIcon.visibility = View.GONE
        }
        
        fun bind(channel: ChannelEntity, epgList: List<com.bingetv.app.data.database.EpgProgramEntity>?) {
            nameText.text = channel.name
            
            // Load logo
            ImageLoader.loadChannelLogo(itemView.context, channel.logoUrl, logoImage)
            
            // Show favorite icon
            favoriteIcon.visibility = if (channel.isFavorite) View.VISIBLE else View.GONE
            
            // EPG Logic
            if (epgList.isNullOrEmpty()) {
                epgNow.text = "No Information"
                epgNext.visibility = View.GONE
                epgProgress.visibility = View.GONE
            } else {
                val now = System.currentTimeMillis()
                // Find current
                val current = epgList.find { now >= it.startTime && now < it.endTime }
                val next = epgList.find { it.startTime >= now } 
                
                if (current != null) {
                    epgNow.text = current.title
                    epgNow.visibility = View.VISIBLE
                    
                    // Progress
                    val total = current.endTime - current.startTime
                    if (total > 0) {
                        val progress = now - current.startTime
                        val percent = (progress.toFloat() / total * 100).toInt()
                        epgProgress.progress = percent
                        epgProgress.visibility = View.VISIBLE
                    } else {
                        epgProgress.visibility = View.GONE
                    }
                } else {
                    epgNow.text = "No Information"
                    epgProgress.visibility = View.GONE
                }
                
                if (next != null) {
                    val timeFormat = java.text.SimpleDateFormat("HH:mm", java.util.Locale.getDefault())
                    epgNext.text = "${timeFormat.format(java.util.Date(next.startTime))} ${next.title}"
                    epgNext.visibility = View.VISIBLE
                } else {
                    epgNext.visibility = View.GONE
                }
            }
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
