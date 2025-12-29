package com.bingetv.app.ui.adapters

import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.TextView
import androidx.recyclerview.widget.DiffUtil
import androidx.recyclerview.widget.ListAdapter
import androidx.recyclerview.widget.RecyclerView
import com.bingetv.app.R
import com.bingetv.app.data.database.CategoryEntity

class CategoryAdapter(
    private val onCategoryClick: (CategoryEntity) -> Unit
) : ListAdapter<CategoryEntity, CategoryAdapter.CategoryViewHolder>(CategoryDiffCallback()) {
    
    private var selectedPosition = 0
    
    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): CategoryViewHolder {
        val view = LayoutInflater.from(parent.context)
            .inflate(R.layout.item_category, parent, false)
        return CategoryViewHolder(view)
    }
    
    override fun onBindViewHolder(holder: CategoryViewHolder, position: Int) {
        val category = getItem(position)
        holder.bind(category, position == selectedPosition)
        
        holder.itemView.setOnClickListener {
            val previousPosition = selectedPosition
            selectedPosition = position
            notifyItemChanged(previousPosition)
            notifyItemChanged(selectedPosition)
            onCategoryClick(category)
        }
        
        holder.itemView.setOnFocusChangeListener { _, hasFocus ->
            if (hasFocus) {
                val previousPosition = selectedPosition
                selectedPosition = position
                notifyItemChanged(previousPosition)
                notifyItemChanged(selectedPosition)
            }
        }
    }
    
    class CategoryViewHolder(itemView: View) : RecyclerView.ViewHolder(itemView) {
        private val nameText: TextView = itemView.findViewById(R.id.category_name)
        
        fun bind(category: CategoryEntity, isSelected: Boolean) {
            nameText.text = category.categoryName
            
            // Update selection state
            itemView.isSelected = isSelected
            if (isSelected) {
                itemView.setBackgroundResource(R.color.background_selected)
                nameText.setTextColor(itemView.context.getColor(R.color.bingetv_red))
            } else {
                itemView.setBackgroundResource(R.color.background_card)
                nameText.setTextColor(itemView.context.getColor(R.color.text_primary))
            }
        }
    }
    
    class CategoryDiffCallback : DiffUtil.ItemCallback<CategoryEntity>() {
        override fun areItemsTheSame(oldItem: CategoryEntity, newItem: CategoryEntity): Boolean {
            return oldItem.id == newItem.id
        }
        
        override fun areContentsTheSame(oldItem: CategoryEntity, newItem: CategoryEntity): Boolean {
            return oldItem == newItem
        }
    }
}
