// BingeTV - Text Utilities for Tizen/WebOS
// Port of Android TextUtils.kt with Base64 decoding and validation

class TextUtils {
    /**
     * Decodes Base64 encoded text if it matches specific patterns or prefixes.
     * Prevents false positives by validating the decoded output.
     */
    static decodeText(text) {
        if (!text || text.trim() === '') return '';

        const trimmed = text.trim();

        // 1. Explicit prefix check
        let cleanText = trimmed;
        if (cleanText.toLowerCase().startsWith('base64:')) {
            cleanText = cleanText.substring(7).trim();
            return this.tryDecode(cleanText) || trimmed;
        }

        // 2. Strict pattern check for potential Base64
        // Base64 strings are usually 4-char aligned, end with ==, and contain specific chars.
        // We only attempt auto-decode if it looks very much like Base64 to avoid false positives.
        if (this.isLikelyBase64(cleanText)) {
            const decoded = this.tryDecode(cleanText);
            if (decoded !== null) return decoded;
        }

        return trimmed;
    }

    static isLikelyBase64(text) {
        // Increase minimum length for auto-detection to 32 chars
        if (text.length < 32) return false;

        // Base64 strings should not have spaces or underscores in common IPTV naming contexts
        if (text.includes(' ') || text.includes('_')) return false;

        // Check if it contains only Base64 valid characters
        const base64Regex = /^[a-zA-Z0-9+/]*={0,2}$/;
        if (!base64Regex.test(text)) return false;

        // HEURISTIC: Require high entropy/case-mixing or specific padding
        const hasPadding = text.endsWith('=');
        const isVeryLong = text.length > 64;

        return hasPadding || isVeryLong;
    }

    static tryDecode(text) {
        // Try standard Base64 decoding
        try {
            const decoded = atob(text);

            // VALIDATION: For auto-decoding, we now require 100% printable characters
            if (this.isPrintable(decoded)) {
                return decoded;
            }
        } catch (e) {
            // Ignore decode errors
        }

        // Try URL-safe Base64 (replace - with + and _ with /)
        try {
            const urlSafeText = text.replace(/-/g, '+').replace(/_/g, '/');
            const decoded = atob(urlSafeText);

            if (this.isPrintable(decoded)) {
                return decoded;
            }
        } catch (e) {
            // Ignore decode errors
        }

        return null;
    }

    static isPrintable(text) {
        if (!text || text.length === 0) return false;

        // Count strictly printable characters
        for (let i = 0; i < text.length; i++) {
            const char = text[i];
            const code = text.charCodeAt(i);

            // Check for control characters (excluding whitespace)
            if (code < 32 && code !== 9 && code !== 10 && code !== 13) {
                return false;
            }

            // Check if character is letter, digit, whitespace, or common punctuation
            if (!this.isLetterOrDigit(char) && !this.isWhitespace(char) && !this.isCommonPunctuation(char)) {
                return false;
            }
        }

        return true; // 100% of characters are printable
    }

    static isLetterOrDigit(char) {
        return /[a-zA-Z0-9]/.test(char);
    }

    static isWhitespace(char) {
        return /\s/.test(char);
    }

    static isCommonPunctuation(char) {
        const punctuation = ".,!?:;()'-_\"/\\@#%&*+=<>[]{}|~";
        return punctuation.includes(char);
    }
}
