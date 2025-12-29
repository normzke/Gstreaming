package com.bingetv.app.ui.dialogs

import android.app.Dialog
import android.content.Context
import android.os.Bundle
import android.text.Editable
import android.text.TextWatcher
import android.widget.EditText
import androidx.recyclerview.widget.GridLayoutManager
import androidx.recyclerview.widget.RecyclerView
import com.bingetv.app.R
import com.bingetv.app.data.database.ChannelEntity
import com.bingetv.app.ui.adapters.ChannelGridAdapter

class SearchDialog(
    context: Context,
    private val allChannels: List<ChannelEntity>,
    private val onChannelSelected: (ChannelEntity) -> Unit
) : Dialog(context, R.style.Theme_Leanback) {
    
    private lateinit var searchInput: EditText
    private lateinit var resultsRecyclerView: RecyclerView
    private lateinit var adapter: ChannelGridAdapter
    
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.dialog_search)
        
        searchInput = findViewById(R.id.search_input)
        resultsRecyclerView = findViewById(R.id.results_recycler_view)
        
        setupRecyclerView()
        setupSearch()
    }
    
    private fun setupRecyclerView() {
        adapter = ChannelGridAdapter { channel ->
            onChannelSelected(channel)
            dismiss()
        }
        
        resultsRecyclerView.apply {
            this.adapter = this@SearchDialog.adapter
            layoutManager = GridLayoutManager(context, 4)
        }
    }
    
    private fun setupSearch() {
        searchInput.addTextChangedListener(object : TextWatcher {
            override fun beforeTextChanged(s: CharSequence?, start: Int, count: Int, after: Int) {}
            override fun onTextChanged(s: CharSequence?, start: Int, before: Int, count: Int) {}
            
            override fun afterTextChanged(s: Editable?) {
                val query = s.toString().trim()
                if (query.length >= 2) {
                    performSearch(query)
                } else {
                    adapter.submitList(emptyList())
                }
            }
        })
        
        searchInput.requestFocus()
    }
    
    private fun performSearch(query: String) {
        val results = allChannels.filter { channel ->
            channel.name.contains(query, ignoreCase = true) ||
            channel.category?.contains(query, ignoreCase = true) == true
        }
        adapter.submitList(results)
    }
}
