package com.bingetv.app

import android.content.Intent
import android.os.Bundle
import android.util.Log
import android.view.View
import androidx.core.content.ContextCompat
import androidx.fragment.app.FragmentActivity
import androidx.leanback.app.BrowseSupportFragment
import androidx.leanback.widget.*
import com.bingetv.app.model.Channel
import com.bingetv.app.parser.M3UParser
import kotlinx.coroutines.*
import android.widget.Toast

class MainActivity : FragmentActivity() {
    private lateinit var browseFragment: BrowseSupportFragment
    private val parser = M3UParser()
    private var channels: List<Channel> = emptyList()
    private val scope = CoroutineScope(Dispatchers.Main + SupervisorJob())
    
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_main)
        
        browseFragment = BrowseSupportFragment()
        supportFragmentManager.beginTransaction()
            .replace(R.id.main_browse_fragment, browseFragment)
            .commitNow()
        
        setupUIElements()
        setupEventListeners()
        
        // Show playlist input dialog
        showPlaylistInputDialog()
    }
    
    private fun setupUIElements() {
        browseFragment.title = getString(R.string.app_name)
        browseFragment.headersState = BrowseSupportFragment.HEADERS_ENABLED
        browseFragment.isHeadersTransitionOnBackEnabled = true
        browseFragment.brandColor = ContextCompat.getColor(this, R.color.lb_default_brand_color)
    }
    
    private fun setupEventListeners() {
        browseFragment.onItemViewClickedListener = ItemViewClickedListener()
        browseFragment.onItemViewSelectedListener = ItemViewSelectedListener()
    }
    
    private fun showPlaylistInputDialog() {
        val dialog = PlaylistInputDialogFragment()
        dialog.onPlaylistLoaded = { url ->
            loadPlaylist(url)
        }
        dialog.show(supportFragmentManager, "PlaylistInputDialog")
    }
    
    private fun loadPlaylist(url: String) {
        if (url.isBlank()) {
            Toast.makeText(this, "Please enter a valid playlist URL", Toast.LENGTH_SHORT).show()
            return
        }
        
        scope.launch {
            try {
                channels = withContext(Dispatchers.IO) {
                    parser.parsePlaylist(url)
                }
                
                if (channels.isEmpty()) {
                    Toast.makeText(this@MainActivity, "No channels found in playlist", Toast.LENGTH_SHORT).show()
                    return@launch
                }
                
                setupRowsAdapter()
                Toast.makeText(this@MainActivity, "Loaded ${channels.size} channels", Toast.LENGTH_SHORT).show()
            } catch (e: Exception) {
                Log.e("MainActivity", "Error loading playlist from URL: $url", e)
                Toast.makeText(this@MainActivity, "Error loading playlist: ${e.message}", Toast.LENGTH_LONG).show()
                e.printStackTrace()
            }
        }
    }
    
    private fun setupRowsAdapter() {
        val rowsAdapter = ArrayObjectAdapter(ListRowPresenter())
        
        // Group channels by category
        val groupedChannels = channels.groupBy { it.group ?: "All Channels" }
        
        groupedChannels.forEach { (groupName, groupChannels) ->
            val listRowAdapter = ArrayObjectAdapter(CardPresenter())
            groupChannels.forEach { channel ->
                listRowAdapter.add(channel)
            }
            
            val header = HeaderItem(groupName.hashCode().toLong(), groupName)
            rowsAdapter.add(ListRow(header, listRowAdapter))
        }
        
        browseFragment.adapter = rowsAdapter
    }
    
    private inner class ItemViewClickedListener : OnItemViewClickedListener {
        override fun onItemClicked(
            itemViewHolder: Presenter.ViewHolder?,
            item: Any?,
            rowViewHolder: RowPresenter.ViewHolder?,
            row: Row?
        ) {
            if (item is Channel) {
                val intent = Intent(this@MainActivity, PlaybackActivity::class.java)
                intent.putExtra("channel_name", item.name)
                intent.putExtra("channel_url", item.url)
                intent.putExtra("channel_logo", item.logo)
                startActivity(intent)
            }
        }
    }
    
    private inner class ItemViewSelectedListener : OnItemViewSelectedListener {
        override fun onItemSelected(
            itemViewHolder: Presenter.ViewHolder?,
            item: Any?,
            rowViewHolder: RowPresenter.ViewHolder?,
            row: Row?
        ) {
            // Handle item selection if needed
        }
    }
    
    override fun onDestroy() {
        super.onDestroy()
        scope.cancel()
    }
}
