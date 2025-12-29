package com.bingetv.app.utils

import android.content.Context
import android.widget.ImageView
import com.bumptech.glide.Glide
import com.bumptech.glide.load.engine.DiskCacheStrategy
import com.bumptech.glide.request.RequestOptions
import com.bingetv.app.R

object ImageLoader {
    
    private val defaultOptions = RequestOptions()
        .diskCacheStrategy(DiskCacheStrategy.ALL)
        .placeholder(R.drawable.ic_placeholder_channel)
        .error(R.drawable.ic_placeholder_channel)
        .centerCrop()
    
    fun loadChannelLogo(context: Context, url: String?, imageView: ImageView) {
        Glide.with(context)
            .load(url)
            .apply(defaultOptions)
            .into(imageView)
    }
    
    fun loadChannelLogoCircle(context: Context, url: String?, imageView: ImageView) {
        Glide.with(context)
            .load(url)
            .apply(defaultOptions.circleCrop())
            .into(imageView)
    }
    
    fun loadEpgImage(context: Context, url: String?, imageView: ImageView) {
        Glide.with(context)
            .load(url)
            .apply(
                RequestOptions()
                    .diskCacheStrategy(DiskCacheStrategy.ALL)
                    .placeholder(R.drawable.ic_placeholder_epg)
                    .error(R.drawable.ic_placeholder_epg)
                    .centerCrop()
            )
            .into(imageView)
    }
    
    fun preloadImage(context: Context, url: String?) {
        Glide.with(context)
            .load(url)
            .preload()
    }
    
    fun clearCache(context: Context) {
        Glide.get(context).clearMemory()
        Thread {
            Glide.get(context).clearDiskCache()
        }.start()
    }
}
