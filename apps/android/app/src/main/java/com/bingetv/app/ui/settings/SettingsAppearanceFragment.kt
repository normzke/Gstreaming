package com.bingetv.app.ui.settings

import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.SeekBar
import android.widget.TextView
import android.widget.Toast
import android.widget.CheckBox
import androidx.fragment.app.Fragment
import com.bingetv.app.R
import com.bingetv.app.utils.Constants
import com.bingetv.app.utils.PreferencesManager

class SettingsAppearanceFragment : Fragment() {

    private lateinit var prefsManager: PreferencesManager
    private lateinit var gridColumnsText: TextView
    private lateinit var gridColumnsSeekBar: SeekBar

    override fun onCreateView(inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?): View? {
        return inflater.inflate(R.layout.fragment_settings_appearance, container, false)
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)
        prefsManager = PreferencesManager(requireContext())
        
        gridColumnsText = view.findViewById(R.id.grid_columns_text)
        gridColumnsSeekBar = view.findViewById(R.id.grid_columns_seekbar)
        
        val currentColumns = prefsManager.getGridColumns()
        gridColumnsSeekBar.progress = currentColumns - Constants.GRID_COLUMNS_MIN
        updateGridColumnsText(currentColumns)
        
        gridColumnsSeekBar.setOnSeekBarChangeListener(object : SeekBar.OnSeekBarChangeListener {
            override fun onProgressChanged(seekBar: SeekBar?, progress: Int, fromUser: Boolean) {
                val columns = progress + Constants.GRID_COLUMNS_MIN
                updateGridColumnsText(columns)
            }
            override fun onStartTrackingTouch(seekBar: SeekBar?) {}
            override fun onStopTrackingTouch(seekBar: SeekBar?) {
                val columns = (seekBar?.progress ?: 0) + Constants.GRID_COLUMNS_MIN
                prefsManager.setGridColumns(columns)
                Toast.makeText(context, "Grid columns updated. Restart app to apply.", Toast.LENGTH_SHORT).show()
            }
        })
        
        // Transparency
        val transparencyText = view.findViewById<TextView>(R.id.transparency_text)
        val transparencySeekBar = view.findViewById<SeekBar>(R.id.transparency_seekbar)
        val checkClock = view.findViewById<CheckBox>(R.id.check_show_clock)
        
        val currentTrans = prefsManager.getUiTransparency()
        transparencySeekBar.progress = currentTrans
        transparencyText.text = "UI Transparency: $currentTrans%"
        
        transparencySeekBar.setOnSeekBarChangeListener(object : SeekBar.OnSeekBarChangeListener {
            override fun onProgressChanged(seekBar: SeekBar?, progress: Int, fromUser: Boolean) {
                transparencyText.text = "UI Transparency: $progress%"
            }
            override fun onStartTrackingTouch(seekBar: SeekBar?) {}
            override fun onStopTrackingTouch(seekBar: SeekBar?) {
                prefsManager.setUiTransparency(seekBar?.progress ?: 0)
            }
        })
        
        // Clock
        checkClock.isChecked = prefsManager.isShowClock()
        checkClock.setOnCheckedChangeListener { _, isChecked -> prefsManager.setShowClock(isChecked) }
    }
    
    private fun updateGridColumnsText(columns: Int) {
        gridColumnsText.text = "Grid Columns: $columns"
    }
}
