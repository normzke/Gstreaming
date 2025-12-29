package com.bingetv.app.utils;

@kotlin.Metadata(mv = {1, 9, 0}, k = 1, xi = 48, d1 = {"\u0000.\n\u0002\u0018\u0002\n\u0002\u0010\u0000\n\u0002\b\u0002\n\u0002\u0018\u0002\n\u0000\n\u0002\u0010\u0002\n\u0000\n\u0002\u0018\u0002\n\u0002\b\u0002\n\u0002\u0010\u000e\n\u0000\n\u0002\u0018\u0002\n\u0002\b\u0004\b\u00c6\u0002\u0018\u00002\u00020\u0001B\u0007\b\u0002\u00a2\u0006\u0002\u0010\u0002J\u000e\u0010\u0005\u001a\u00020\u00062\u0006\u0010\u0007\u001a\u00020\bJ \u0010\t\u001a\u00020\u00062\u0006\u0010\u0007\u001a\u00020\b2\b\u0010\n\u001a\u0004\u0018\u00010\u000b2\u0006\u0010\f\u001a\u00020\rJ \u0010\u000e\u001a\u00020\u00062\u0006\u0010\u0007\u001a\u00020\b2\b\u0010\n\u001a\u0004\u0018\u00010\u000b2\u0006\u0010\f\u001a\u00020\rJ \u0010\u000f\u001a\u00020\u00062\u0006\u0010\u0007\u001a\u00020\b2\b\u0010\n\u001a\u0004\u0018\u00010\u000b2\u0006\u0010\f\u001a\u00020\rJ\u0018\u0010\u0010\u001a\u00020\u00062\u0006\u0010\u0007\u001a\u00020\b2\b\u0010\n\u001a\u0004\u0018\u00010\u000bR\u000e\u0010\u0003\u001a\u00020\u0004X\u0082\u0004\u00a2\u0006\u0002\n\u0000\u00a8\u0006\u0011"}, d2 = {"Lcom/bingetv/app/utils/ImageLoader;", "", "()V", "defaultOptions", "Lcom/bumptech/glide/request/RequestOptions;", "clearCache", "", "context", "Landroid/content/Context;", "loadChannelLogo", "url", "", "imageView", "Landroid/widget/ImageView;", "loadChannelLogoCircle", "loadEpgImage", "preloadImage", "app_debug"})
public final class ImageLoader {
    @org.jetbrains.annotations.NotNull
    private static final com.bumptech.glide.request.RequestOptions defaultOptions = null;
    @org.jetbrains.annotations.NotNull
    public static final com.bingetv.app.utils.ImageLoader INSTANCE = null;
    
    private ImageLoader() {
        super();
    }
    
    public final void loadChannelLogo(@org.jetbrains.annotations.NotNull
    android.content.Context context, @org.jetbrains.annotations.Nullable
    java.lang.String url, @org.jetbrains.annotations.NotNull
    android.widget.ImageView imageView) {
    }
    
    public final void loadChannelLogoCircle(@org.jetbrains.annotations.NotNull
    android.content.Context context, @org.jetbrains.annotations.Nullable
    java.lang.String url, @org.jetbrains.annotations.NotNull
    android.widget.ImageView imageView) {
    }
    
    public final void loadEpgImage(@org.jetbrains.annotations.NotNull
    android.content.Context context, @org.jetbrains.annotations.Nullable
    java.lang.String url, @org.jetbrains.annotations.NotNull
    android.widget.ImageView imageView) {
    }
    
    public final void preloadImage(@org.jetbrains.annotations.NotNull
    android.content.Context context, @org.jetbrains.annotations.Nullable
    java.lang.String url) {
    }
    
    public final void clearCache(@org.jetbrains.annotations.NotNull
    android.content.Context context) {
    }
}