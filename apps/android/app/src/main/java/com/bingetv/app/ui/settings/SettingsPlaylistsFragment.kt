package com.bingetv.app.ui.settings

import android.content.Intent
import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.Button
import android.widget.TextView
import android.widget.Toast
import androidx.fragment.app.Fragment
import androidx.lifecycle.lifecycleScope
import androidx.recyclerview.widget.LinearLayoutManager
import androidx.recyclerview.widget.RecyclerView
import com.bingetv.app.R
import com.bingetv.app.data.database.BingeTVDatabase
import com.bingetv.app.data.database.PlaylistEntity
import com.bingetv.app.data.repository.PlaylistRepository
import com.bingetv.app.ui.login.LoginActivity
import kotlinx.coroutines.launch

class SettingsPlaylistsFragment : Fragment() {

    private lateinit var repository: PlaylistRepository
    private lateinit var adapter: PlaylistsAdapter

    override fun onCreateView(inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?): View? {
        return inflater.inflate(R.layout.fragment_settings_playlists, container, false)
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)
        
        val dao = BingeTVDatabase.getDatabase(requireContext()).playlistDao()
        repository = PlaylistRepository(dao)
        
        val recyclerView = view.findViewById<RecyclerView>(R.id.playlists_recycler_view)
        recyclerView.layoutManager = LinearLayoutManager(context)
        adapter = PlaylistsAdapter()
        recyclerView.adapter = adapter
        
        repository.allPlaylists.observe(viewLifecycleOwner) { playlists ->
            adapter.submitList(playlists)
        }
        
        view.findViewById<Button>(R.id.btn_add_playlist).setOnClickListener {
            // Launch Login Activity to add new
            val intent = Intent(requireContext(), LoginActivity::class.java)
            // intent.putExtra("MODE", "ADD") // Optional if needed
            startActivity(intent)
        }
        
        view.findViewById<Button>(R.id.btn_update_playlists).setOnClickListener {
            Toast.makeText(context, "Verifying playlists...", Toast.LENGTH_SHORT).show()
            verifyPlaylists()
        }
    }
    
    private fun verifyPlaylists() {
        lifecycleScope.launch {
            val playlists = repository.allPlaylists.value ?: return@launch
            var failedCount = 0
            
            for (playlist in playlists) {
                if (playlist.type == "xtream" && playlist.serverUrl != null) {
                    val result = repository.testXtreamConnection(playlist.serverUrl, playlist.username!!, playlist.password!!)
                    if (result.isFailure) failedCount++
                }
            }
            
            if (failedCount > 0) {
                 Toast.makeText(context, "$failedCount playlists failed verification", Toast.LENGTH_LONG).show()
            } else {
                 Toast.makeText(context, "All playlists verified", Toast.LENGTH_SHORT).show()
            }
        }
    }
    
    inner class PlaylistsAdapter : RecyclerView.Adapter<PlaylistsAdapter.ViewHolder>() {
        
        private var items = listOf<PlaylistEntity>()
        
        fun submitList(newItems: List<PlaylistEntity>) {
            items = newItems
            notifyDataSetChanged()
        }
        
        inner class ViewHolder(view: View) : RecyclerView.ViewHolder(view) {
            val name: TextView = view.findViewById(R.id.playlist_name)
            val url: TextView = view.findViewById(R.id.playlist_url)
            val status: TextView = view.findViewById(R.id.playlist_status)
        }
        
        override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): ViewHolder {
            val view = LayoutInflater.from(parent.context)
                .inflate(R.layout.item_playlist_setting, parent, false)
            return ViewHolder(view)
        }
        
        override fun onBindViewHolder(holder: ViewHolder, position: Int) {
            val item = items[position]
            holder.name.text = item.name
            holder.url.text = item.serverUrl ?: item.m3uUrl ?: "Unknown URL"
            holder.status.text = if (item.isActive) "Active" else "Inactive"
            holder.status.setTextColor(
                if (item.isActive) holder.itemView.context.getColor(R.color.bingetv_red)
                else holder.itemView.context.getColor(R.color.text_secondary)
            )
            
            holder.itemView.setOnClickListener {
                showPlaylistOptions(item, holder.itemView.context)
            }
        }

        private fun showPlaylistOptions(playlist: PlaylistEntity, context: android.content.Context) {
            val options = if (playlist.isActive) {
                arrayOf("Reload Playlist", "Edit Details", "Delete")
            } else {
                arrayOf("Activate", "Edit Details", "Delete")
            }

            android.app.AlertDialog.Builder(context)
                .setTitle(playlist.name)
                .setItems(options) { _, which ->
                    val selection = options[which]
                    when (selection) {
                        "Activate" -> {
                            lifecycleScope.launch {
                                repository.activatePlaylist(playlist.id)
                                com.bingetv.app.utils.PreferencesManager(context).setLastLoadedPlaylistId(-1) // Force reload
                                Toast.makeText(context, "Activated. Restarting...", Toast.LENGTH_SHORT).show()
                                restartApp(context)
                            }
                        }
                        "Reload Playlist" -> {
                             lifecycleScope.launch {
                                repository.activatePlaylist(playlist.id)
                                com.bingetv.app.utils.PreferencesManager(context).setLastLoadedPlaylistId(-1) // Force reload
                                Toast.makeText(context, "Reloading...", Toast.LENGTH_SHORT).show()
                                restartApp(context)
                             }
                        }
                        "Edit Details" -> {
                            val intent = Intent(context, LoginActivity::class.java)
                            intent.putExtra("EDIT_PLAYLIST_ID", playlist.id) 
                            context.startActivity(intent)
                        }
                        "Delete" -> {
                            confirmDelete(playlist, context)
                        }
                    }
                }
                .show()
        }

        private fun confirmDelete(playlist: PlaylistEntity, context: android.content.Context) {
             android.app.AlertDialog.Builder(context)
                .setTitle("Delete Playlist")
                .setMessage("Are you sure you want to delete '${playlist.name}'?")
                .setPositiveButton("Delete") { _, _ ->
                    lifecycleScope.launch {
                        repository.deletePlaylist(playlist)
                        Toast.makeText(context, "Deleted", Toast.LENGTH_SHORT).show()
                    }
                }
                .setNegativeButton("Cancel", null)
                .show()
        }
        
        private fun restartApp(context: android.content.Context) {
            val intent = android.content.Intent(context, com.bingetv.app.ui.splash.SplashActivity::class.java)
            intent.addFlags(android.content.Intent.FLAG_ACTIVITY_NEW_TASK or android.content.Intent.FLAG_ACTIVITY_CLEAR_TASK)
            context.startActivity(intent)
            if (context is android.app.Activity) {
                context.finish()
            }
            Runtime.getRuntime().exit(0)
        }

        override fun getItemCount() = items.size
    }
}
