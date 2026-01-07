package com.bingetv.app.utils

import android.util.Base64
import android.util.Log

object TextUtils {
    private const val TAG = "TextUtils"

    /**
     * Decodes Base64 encoded text if it matches specific patterns or prefixes.
     * Prevents false positives by validating the decoded output.
     */
    fun decodeText(text: String?): String {
        if (text.isNullOrEmpty()) return ""
        
        val trimmed = text.trim()
        
        // 1. Explicit prefix check
        var cleanText = trimmed
        if (cleanText.startsWith("base64:", ignoreCase = true)) {
            cleanText = cleanText.substring(7).trim()
            return tryDecode(cleanText) ?: trimmed
        }

        // 2. Strict pattern check for potential Base64
        // Base64 strings are usually 4-char aligned, end with ==, and contain specific chars.
        // We only attempt auto-decode if it looks very much like Base64 to avoid false positives.
        if (isLikelyBase64(cleanText)) {
            val decoded = tryDecode(cleanText)
            if (decoded != null) return decoded
        }

        return trimmed
    }

    private fun isLikelyBase64(text: String): Boolean {
        // Increase minimum length for auto-detection to 32 chars
        if (text.length < 32) return false 
        
        // Base64 strings should not have spaces or underscores in common IPTV naming contexts
        if (text.contains(" ") || text.contains("_")) return false

        // Check if it contains only Base64 valid characters
        val base64Regex = Regex("^[a-zA-Z0-9+/]*={0,2}$")
        if (!base64Regex.matches(text)) return false

        // HEURISTIC: Require high entropy/case-mixing or specific padding
        val hasPadding = text.endsWith("=")
        val isVeryLong = text.length > 64
        
        return hasPadding || isVeryLong
    }

    private fun tryDecode(text: String): String? {
        val flags = listOf(
            Base64.DEFAULT,
            Base64.URL_SAFE,
            Base64.NO_WRAP,
            Base64.NO_PADDING
        )
        
        for (flag in flags) {
            try {
                val bytes = Base64.decode(text, flag)
                val decoded = bytes.decodeToString()
                
                // VALIDATION: For auto-decoding, we now require 100% printable characters
                if (isPrintable(decoded)) {
                    return decoded
                }
            } catch (e: Exception) {
                // Ignore and try next flag
            }
        }
        return null
    }

    private fun isPrintable(text: String): Boolean {
        if (text.isEmpty()) return false
        
        // Count strictly printable characters
        for (char in text) {
            if (char.isISOControl() && !char.isWhitespace()) return false
            if (!(char.isLetterOrDigit() || char.isWhitespace() || isCommonPunctuation(char))) {
                return false // Found a non-printable/weird character
            }
        }
        
        return true // 100% of characters are printable
    }

    private fun isCommonPunctuation(c: Char): Boolean {
        val punctuation = ".,!?:;()'-_\"/\\@#%&*+=<>[]{}|~"
        return punctuation.contains(c)
    }
}
