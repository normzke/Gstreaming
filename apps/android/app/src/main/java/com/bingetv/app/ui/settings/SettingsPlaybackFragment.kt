package com.bingetv.app.ui.settings

import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.AdapterView
import android.widget.ArrayAdapter
import android.widget.CheckBox
import android.widget.Spinner
import androidx.fragment.app.Fragment
import com.bingetv.app.R
import com.bingetv.app.utils.PreferencesManager

class SettingsPlaybackFragment : Fragment() {

    private lateinit var prefsManager: PreferencesManager
    
    override fun onCreateView(inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?): View? {
        return inflater.inflate(R.layout.fragment_settings_playback, container, false)
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)
        prefsManager = PreferencesManager(requireContext())
        
        val spinnerBuffer = view.findViewById<Spinner>(R.id.spinner_buffer_size)
        val spinnerAudio = view.findViewById<Spinner>(R.id.spinner_audio_decoder)
        val checkAfr = view.findViewById<CheckBox>(R.id.check_afr)
        
        // Buffer Size
        val bufferOptions = arrayOf("Small", "Medium", "Large", "None")
        val bufferAdapter = ArrayAdapter(requireContext(), android.R.layout.simple_spinner_item, bufferOptions)
        bufferAdapter.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item)
        spinnerBuffer.adapter = bufferAdapter
        
        val savedBuffer = prefsManager.getBufferSize()
        val bufferIndex = bufferOptions.indexOfFirst { it.equals(savedBuffer, ignoreCase = true) }.takeIf { it >= 0 } ?: 1 // Default Medium
        spinnerBuffer.setSelection(bufferIndex)
        
        spinnerBuffer.onItemSelectedListener = object : AdapterView.OnItemSelectedListener {
            override fun onItemSelected(parent: AdapterView<*>?, view: View?, position: Int, id: Long) {
                prefsManager.setBufferSize(bufferOptions[position].lowercase())
            }
            override fun onNothingSelected(parent: AdapterView<*>?) {}
        }
        
        // Audio Decoder
        val decoderOptions = arrayOf("Hardware", "Software")
        val decoderAdapter = ArrayAdapter(requireContext(), android.R.layout.simple_spinner_item, decoderOptions)
        decoderAdapter.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item)
        spinnerAudio.adapter = decoderAdapter
        
        val savedAudio = prefsManager.getAudioDecoder()
        val audioIndex = decoderOptions.indexOfFirst { it.equals(savedAudio, ignoreCase = true) }.takeIf { it >= 0 } ?: 0 // Default HW
        spinnerAudio.setSelection(audioIndex)
        
        spinnerAudio.onItemSelectedListener = object : AdapterView.OnItemSelectedListener {
             override fun onItemSelected(parent: AdapterView<*>?, view: View?, position: Int, id: Long) {
                prefsManager.setAudioDecoder(decoderOptions[position].lowercase())
            }
            override fun onNothingSelected(parent: AdapterView<*>?) {}
        }

        // AFR
        checkAfr.isChecked = prefsManager.isAfrEnabled()
        checkAfr.setOnCheckedChangeListener { _, isChecked -> prefsManager.setAfrEnabled(isChecked) }
    }
}
