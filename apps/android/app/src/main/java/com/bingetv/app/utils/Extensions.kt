package com.bingetv.app.utils

import android.view.View
import android.view.animation.AlphaAnimation
import android.view.animation.Animation
import android.view.animation.ScaleAnimation
import android.widget.Toast
import androidx.fragment.app.FragmentActivity
import java.text.SimpleDateFormat
import java.util.*

// View extensions
fun View.show() {
    visibility = View.VISIBLE
}

fun View.hide() {
    visibility = View.GONE
}

fun View.invisible() {
    visibility = View.INVISIBLE
}

fun View.fadeIn(duration: Long = 300) {
    val animation = AlphaAnimation(0f, 1f).apply {
        this.duration = duration
    }
    startAnimation(animation)
    show()
}

fun View.fadeOut(duration: Long = 300) {
    val animation = AlphaAnimation(1f, 0f).apply {
        this.duration = duration
        setAnimationListener(object : Animation.AnimationListener {
            override fun onAnimationStart(animation: Animation?) {}
            override fun onAnimationRepeat(animation: Animation?) {}
            override fun onAnimationEnd(animation: Animation?) {
                hide()
            }
        })
    }
    startAnimation(animation)
}

fun View.scaleUp(duration: Long = 200) {
    val animation = ScaleAnimation(
        1f, 1.1f,
        1f, 1.1f,
        Animation.RELATIVE_TO_SELF, 0.5f,
        Animation.RELATIVE_TO_SELF, 0.5f
    ).apply {
        this.duration = duration
        fillAfter = true
    }
    startAnimation(animation)
}

fun View.scaleDown(duration: Long = 200) {
    val animation = ScaleAnimation(
        1.1f, 1f,
        1.1f, 1f,
        Animation.RELATIVE_TO_SELF, 0.5f,
        Animation.RELATIVE_TO_SELF, 0.5f
    ).apply {
        this.duration = duration
        fillAfter = true
    }
    startAnimation(animation)
}

// Toast extensions
fun FragmentActivity.showToast(message: String, duration: Int = Toast.LENGTH_SHORT) {
    Toast.makeText(this, message, duration).show()
}

fun FragmentActivity.showLongToast(message: String) {
    Toast.makeText(this, message, Toast.LENGTH_LONG).show()
}

// Time formatting
fun Long.toTimeString(): String {
    val sdf = SimpleDateFormat("HH:mm", Locale.getDefault())
    return sdf.format(Date(this))
}

fun Long.toDateTimeString(): String {
    val sdf = SimpleDateFormat("MMM dd, HH:mm", Locale.getDefault())
    return sdf.format(Date(this))
}

fun Long.toFullDateTimeString(): String {
    val sdf = SimpleDateFormat("MMM dd, yyyy HH:mm", Locale.getDefault())
    return sdf.format(Date(this))
}

// Duration formatting
fun Long.toDurationString(): String {
    val hours = this / 3600000
    val minutes = (this % 3600000) / 60000
    return when {
        hours > 0 -> "${hours}h ${minutes}m"
        minutes > 0 -> "${minutes}m"
        else -> "< 1m"
    }
}

// String extensions
fun String.isValidUrl(): Boolean {
    return this.startsWith("http://") || this.startsWith("https://")
}

fun String.isValidM3uUrl(): Boolean {
    return isValidUrl() && (this.contains(".m3u") || this.contains("get.php"))
}

// Number extensions
fun Int.dpToPx(context: android.content.Context): Int {
    return (this * context.resources.displayMetrics.density).toInt()
}

fun Int.pxToDp(context: android.content.Context): Int {
    return (this / context.resources.displayMetrics.density).toInt()
}
