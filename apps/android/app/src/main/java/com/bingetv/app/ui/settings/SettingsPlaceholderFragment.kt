package com.bingetv.app.ui.settings

import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.TextView
import androidx.fragment.app.Fragment
import com.bingetv.app.R

class SettingsPlaceholderFragment : Fragment() {
    
    companion object {
        private const val ARG_TITLE = "title"
        
        fun newInstance(title: String): SettingsPlaceholderFragment {
            val fragment = SettingsPlaceholderFragment()
            val args = Bundle()
            args.putString(ARG_TITLE, title)
            fragment.arguments = args
            return fragment
        }
    }

    override fun onCreateView(inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?): View? {
        return inflater.inflate(R.layout.fragment_settings_placeholder, container, false)
    }
    
    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)
        val title = arguments?.getString(ARG_TITLE) ?: "Feature"
        view.findViewById<TextView>(R.id.text_placeholder).text = "$title Coming Soon"
    }
}
