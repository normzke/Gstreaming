package com.bingetv.app.ui.settings

import android.content.Intent
import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.Button
import android.widget.Toast
import androidx.fragment.app.Fragment
import com.bingetv.app.R
import com.bingetv.app.ui.login.LoginActivity
import com.bingetv.app.utils.PreferencesManager

class SettingsAccountFragment : Fragment() {

    private lateinit var prefsManager: PreferencesManager
    
    override fun onCreateView(inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?): View? {
        return inflater.inflate(R.layout.fragment_settings_account, container, false)
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)
        prefsManager = PreferencesManager(requireContext())
        
        view.findViewById<Button>(R.id.logout_button).setOnClickListener { logout() }
        view.findViewById<Button>(R.id.clear_cache_button).setOnClickListener { clearCache() }
    }
    
    private fun logout() {
        prefsManager.clearCredentials()
        prefsManager.setAutoLogin(false)
        
        val intent = Intent(requireContext(), LoginActivity::class.java)
        intent.flags = Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_CLEAR_TASK
        startActivity(intent)
        requireActivity().finish()
    }
    
    private fun clearCache() {
        try {
            com.bingetv.app.utils.ImageLoader.clearCache(requireContext())
            Toast.makeText(context, "Cache cleared", Toast.LENGTH_SHORT).show()
        } catch (e: Exception) {
            Toast.makeText(context, "Error clearing cache", Toast.LENGTH_SHORT).show()
        }
    }
}
