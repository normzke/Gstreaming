package com.bingetv.app.utils;

@kotlin.Metadata(mv = {1, 9, 0}, k = 1, xi = 48, d1 = {"\u0000\"\n\u0002\u0018\u0002\n\u0002\u0010\u0000\n\u0002\b\u0002\n\u0002\u0010\u000e\n\u0002\b\u0003\n\u0002\u0010\u000b\n\u0000\n\u0002\u0010\f\n\u0002\b\u0004\b\u00c6\u0002\u0018\u00002\u00020\u0001B\u0007\b\u0002\u00a2\u0006\u0002\u0010\u0002J\u0010\u0010\u0005\u001a\u00020\u00042\b\u0010\u0006\u001a\u0004\u0018\u00010\u0004J\u0010\u0010\u0007\u001a\u00020\b2\u0006\u0010\t\u001a\u00020\nH\u0002J\u0010\u0010\u000b\u001a\u00020\b2\u0006\u0010\u0006\u001a\u00020\u0004H\u0002J\u0010\u0010\f\u001a\u00020\b2\u0006\u0010\u0006\u001a\u00020\u0004H\u0002J\u0012\u0010\r\u001a\u0004\u0018\u00010\u00042\u0006\u0010\u0006\u001a\u00020\u0004H\u0002R\u000e\u0010\u0003\u001a\u00020\u0004X\u0082T\u00a2\u0006\u0002\n\u0000\u00a8\u0006\u000e"}, d2 = {"Lcom/bingetv/app/utils/TextUtils;", "", "()V", "TAG", "", "decodeText", "text", "isCommonPunctuation", "", "c", "", "isLikelyBase64", "isPrintable", "tryDecode", "app_release"})
public final class TextUtils {
    @org.jetbrains.annotations.NotNull
    private static final java.lang.String TAG = "TextUtils";
    @org.jetbrains.annotations.NotNull
    public static final com.bingetv.app.utils.TextUtils INSTANCE = null;
    
    private TextUtils() {
        super();
    }
    
    /**
     * Decodes Base64 encoded text if it matches specific patterns or prefixes.
     * Prevents false positives by validating the decoded output.
     */
    @org.jetbrains.annotations.NotNull
    public final java.lang.String decodeText(@org.jetbrains.annotations.Nullable
    java.lang.String text) {
        return null;
    }
    
    private final boolean isLikelyBase64(java.lang.String text) {
        return false;
    }
    
    private final java.lang.String tryDecode(java.lang.String text) {
        return null;
    }
    
    private final boolean isPrintable(java.lang.String text) {
        return false;
    }
    
    private final boolean isCommonPunctuation(char c) {
        return false;
    }
}