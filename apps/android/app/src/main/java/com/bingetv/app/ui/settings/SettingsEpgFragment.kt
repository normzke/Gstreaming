package com.bingetv.app.ui.settings

import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.AdapterView
import android.widget.ArrayAdapter
import android.widget.Button
import android.widget.CheckBox
import android.widget.Spinner
import android.widget.Toast
import androidx.fragment.app.Fragment
import com.bingetv.app.R
import com.bingetv.app.utils.PreferencesManager
// Import Dao if possible, or skip clear if tricky via Repo

import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.GlobalScope
import kotlinx.coroutines.launch

class SettingsEpgFragment : Fragment() {

    private lateinit var prefsManager: PreferencesManager
    
    override fun onCreateView(inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?): View? {
        return inflater.inflate(R.layout.fragment_settings_epg, container, false)
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)
        prefsManager = PreferencesManager(requireContext())
        
        val spinnerDays = view.findViewById<Spinner>(R.id.spinner_epg_days)
        val checkDesc = view.findViewById<CheckBox>(R.id.check_store_desc)
        val checkStart = view.findViewById<CheckBox>(R.id.check_update_start)
        val checkChange = view.findViewById<CheckBox>(R.id.check_update_change)
        
        // Days Spinner
        val daysOptions = arrayOf(1, 2, 3, 5, 7)
        val daysAdapter = ArrayAdapter(requireContext(), android.R.layout.simple_spinner_item, daysOptions)
        daysAdapter.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item)
        spinnerDays.adapter = daysAdapter
        
        val currentDays = prefsManager.getEpgDays()
        val daysIndex = daysOptions.indexOf(currentDays).takeIf { it >= 0 } ?: 1
        spinnerDays.setSelection(daysIndex)
        
        spinnerDays.onItemSelectedListener = object : AdapterView.OnItemSelectedListener {
            override fun onItemSelected(parent: AdapterView<*>?, view: View?, position: Int, id: Long) {
                prefsManager.setEpgDays(daysOptions[position])
            }
            override fun onNothingSelected(parent: AdapterView<*>?) {}
        }
        
        // Toggles
        checkDesc.isChecked = prefsManager.isStoreDescriptions()
        checkDesc.setOnCheckedChangeListener { _, isChecked -> prefsManager.setStoreDescriptions(isChecked) }
        
        checkStart.isChecked = prefsManager.isEpgUpdateOnStart()
        checkStart.setOnCheckedChangeListener { _, isChecked -> prefsManager.setEpgUpdateOnStart(isChecked) }
        
        checkChange.isChecked = prefsManager.isEpgUpdateOnPlaylistChange()
        checkChange.setOnCheckedChangeListener { _, isChecked -> prefsManager.setEpgUpdateOnPlaylistChange(isChecked) }
        
        // Buttons
        view.findViewById<Button>(R.id.btn_update_epg).setOnClickListener {
            Toast.makeText(context, "EPG Update Scheduled", Toast.LENGTH_SHORT).show()
        }
        
        view.findViewById<Button>(R.id.btn_clear_epg).setOnClickListener {
            // Need DB access
             Toast.makeText(context, "Clear EPG implementation pending DB access", Toast.LENGTH_SHORT).show()
             // Ideally: AppDatabase.getDatabase(context).epgDao().deleteAll()
        }
    }
}
