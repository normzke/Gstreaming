package com.bingetv.app.ui.settings

import android.content.Intent
import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.TextView
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import androidx.fragment.app.Fragment
import androidx.recyclerview.widget.LinearLayoutManager
import androidx.recyclerview.widget.RecyclerView
import com.bingetv.app.R
import com.bingetv.app.ui.login.LoginActivity
import com.bingetv.app.utils.PreferencesManager
import com.bingetv.app.utils.Constants

class SettingsActivity : AppCompatActivity() {
    
    private lateinit var categoriesList: RecyclerView
    private val categories = listOf(
        "General",
        "Playlists",
        "EPG",
        "Appearance",
        "Playback",
        "Remote Control",
        "Account"
    )
    
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_settings)
        
        categoriesList = findViewById(R.id.settings_categories_list)
        categoriesList.layoutManager = LinearLayoutManager(this)
        categoriesList.adapter = CategoriesAdapter(categories) { index ->
            loadFragment(index)
        }
        
        // Default to General
        if (savedInstanceState == null) {
            loadFragment(0)
            categoriesList.post {
                categoriesList.findViewHolderForAdapterPosition(0)?.itemView?.requestFocus()
            }
        }
    }
    
    private fun loadFragment(index: Int) {
        val category = categories[index]
        val fragment: Fragment = when (index) {
            0 -> SettingsGeneralFragment()
            1 -> SettingsPlaylistsFragment()
            2 -> SettingsEpgFragment()
            3 -> SettingsAppearanceFragment()
            4 -> SettingsPlaybackFragment()
            5 -> SettingsRemoteControlFragment()
            6 -> SettingsAccountFragment()
            else -> SettingsPlaceholderFragment.newInstance(category)
        }
        
        supportFragmentManager.beginTransaction()
            .replace(R.id.settings_content_frame, fragment)
            .commit()
    }
    
    inner class CategoriesAdapter(
        private val items: List<String>,
        private val onItemClick: (Int) -> Unit
    ) : RecyclerView.Adapter<CategoriesAdapter.ViewHolder>() {
        
        private var selectedIndex = 0
        
        inner class ViewHolder(view: View) : RecyclerView.ViewHolder(view) {
            val textView: TextView = view.findViewById(R.id.category_name)
            
            init {
                view.setOnClickListener {
                    val pos = adapterPosition
                    if (pos != RecyclerView.NO_POSITION) {
                        setSelectedIndex(pos)
                        onItemClick(pos)
                    }
                }
                view.setOnFocusChangeListener { _, hasFocus ->
                    if (hasFocus) {
                        // Optional: Visualize focus beyond default selector
                        // For now default selector is fine
                    }
                }
            }
        }
        
        override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): ViewHolder {
            val view = LayoutInflater.from(parent.context)
                .inflate(R.layout.item_settings_category, parent, false)
            return ViewHolder(view)
        }
        
        override fun onBindViewHolder(holder: ViewHolder, position: Int) {
            holder.textView.text = items[position]
            holder.itemView.isSelected = (position == selectedIndex)
            if (position == selectedIndex) {
                 holder.textView.setTextColor(getColor(R.color.bingetv_red))
            } else {
                 holder.textView.setTextColor(getColor(R.color.text_primary))
            }
        }
        
        override fun getItemCount() = items.size
        
        fun setSelectedIndex(index: Int) {
            val prev = selectedIndex
            selectedIndex = index
            notifyItemChanged(prev)
            notifyItemChanged(selectedIndex)
        }
    }
}
