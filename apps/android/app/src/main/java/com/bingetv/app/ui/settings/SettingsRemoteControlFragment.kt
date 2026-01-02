package com.bingetv.app.ui.settings

import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.AdapterView
import android.widget.ArrayAdapter
import android.widget.Spinner
import androidx.fragment.app.Fragment
import com.bingetv.app.R
import com.bingetv.app.utils.PreferencesManager

class SettingsRemoteControlFragment : Fragment() {

    private lateinit var prefsManager: PreferencesManager
    
    override fun onCreateView(inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?): View? {
        return inflater.inflate(R.layout.fragment_settings_remote, container, false)
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)
        prefsManager = PreferencesManager(requireContext())
        
        val spinnerLR = view.findViewById<Spinner>(R.id.spinner_remote_lr)
        val spinnerUD = view.findViewById<Spinner>(R.id.spinner_remote_ud)
        
        // Left/Right options
        val lrOptions = arrayOf("Seek", "Change Channel", "Volume")
        val lrValues = arrayOf("seek", "channel", "volume")
        setupSpinner(spinnerLR, lrOptions, lrValues) { prefsManager.setRemoteLeftRightAction(it) }
        
        val currentLR = prefsManager.getRemoteLeftRightAction() // "seek"
        setSpinnerSelection(spinnerLR, lrValues, currentLR)

        // Up/Down options
        val udOptions = arrayOf("Change Channel", "Volume")
        val udValues = arrayOf("channel", "volume")
        setupSpinner(spinnerUD, udOptions, udValues) { prefsManager.setRemoteUpDownAction(it) }
        
        val currentUD = prefsManager.getRemoteUpDownAction() // "channel"
        setSpinnerSelection(spinnerUD, udValues, currentUD)
    }
    
    private fun setupSpinner(spinner: Spinner, labels: Array<String>, values: Array<String>, onSelect: (String) -> Unit) {
        val adapter = ArrayAdapter(requireContext(), android.R.layout.simple_spinner_item, labels)
        adapter.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item)
        spinner.adapter = adapter
        
        spinner.onItemSelectedListener = object : AdapterView.OnItemSelectedListener {
            override fun onItemSelected(parent: AdapterView<*>?, view: View?, position: Int, id: Long) {
                onSelect(values[position])
            }
            override fun onNothingSelected(parent: AdapterView<*>?) {}
        }
    }
    
    private fun setSpinnerSelection(spinner: Spinner, values: Array<String>, current: String) {
        val index = values.indexOf(current).takeIf { it >= 0 } ?: 0
        spinner.setSelection(index)
    }
}
