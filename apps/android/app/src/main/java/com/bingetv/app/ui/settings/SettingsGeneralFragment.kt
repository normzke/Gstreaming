package com.bingetv.app.ui.settings

import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.CheckBox
import androidx.fragment.app.Fragment
import com.bingetv.app.R
import com.bingetv.app.utils.PreferencesManager

class SettingsGeneralFragment : Fragment() {

    private lateinit var prefsManager: PreferencesManager
    
    override fun onCreateView(inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?): View? {
        return inflater.inflate(R.layout.fragment_settings_general, container, false)
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)
        prefsManager = PreferencesManager(requireContext())
        
        val checkAutoStart = view.findViewById<CheckBox>(R.id.check_auto_start)
        val checkLastChannel = view.findViewById<CheckBox>(R.id.check_last_channel)
        val checkPip = view.findViewById<CheckBox>(R.id.check_pip)
        val checkConfirmExit = view.findViewById<CheckBox>(R.id.check_confirm_exit)
        
        // Load values
        checkAutoStart.isChecked = prefsManager.isAutoStartBoot()
        checkLastChannel.isChecked = prefsManager.isTurnOnLastChannel()
        checkPip.isChecked = prefsManager.isPipOnHome()
        checkConfirmExit.isChecked = prefsManager.isConfirmExit()
        
        // Set listeners
        checkAutoStart.setOnCheckedChangeListener { _, isChecked -> prefsManager.setAutoStartBoot(isChecked) }
        checkLastChannel.setOnCheckedChangeListener { _, isChecked -> prefsManager.setTurnOnLastChannel(isChecked) }
        checkPip.setOnCheckedChangeListener { _, isChecked -> prefsManager.setPipOnHome(isChecked) }
        checkConfirmExit.setOnCheckedChangeListener { _, isChecked -> prefsManager.setConfirmExit(isChecked) }
    }
}
