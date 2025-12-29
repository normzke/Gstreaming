package com.bingetv.app

import android.app.Dialog
import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.view.WindowManager
import android.widget.EditText
import android.widget.Toast
import androidx.fragment.app.DialogFragment
import androidx.leanback.app.GuidedStepSupportFragment

class PlaylistInputDialogFragment : DialogFragment() {
    var onPlaylistLoaded: ((String) -> Unit)? = null
    
    override fun onCreateDialog(savedInstanceState: Bundle?): Dialog {
        val dialog = super.onCreateDialog(savedInstanceState)
        dialog.window?.setLayout(
            WindowManager.LayoutParams.MATCH_PARENT,
            WindowManager.LayoutParams.WRAP_CONTENT
        )
        return dialog
    }
    
    override fun onCreateView(
        inflater: LayoutInflater,
        container: ViewGroup?,
        savedInstanceState: Bundle?
    ): View? {
        val view = inflater.inflate(R.layout.dialog_playlist_input, container, false)
        val urlEditText = view.findViewById<EditText>(R.id.playlist_url_edit)
        val loadButton = view.findViewById<View>(R.id.load_button)
        val cancelButton = view.findViewById<View>(R.id.cancel_button)
        
        loadButton.setOnClickListener {
            val url = urlEditText.text.toString().trim()
            if (url.isNotEmpty()) {
                onPlaylistLoaded?.invoke(url)
                dismiss()
            } else {
                Toast.makeText(context, "Please enter a playlist URL", Toast.LENGTH_SHORT).show()
            }
        }
        
        cancelButton.setOnClickListener {
            dismiss()
        }
        
        return view
    }
}

