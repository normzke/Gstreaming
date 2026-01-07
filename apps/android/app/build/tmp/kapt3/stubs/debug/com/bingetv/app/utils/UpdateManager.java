package com.bingetv.app.utils;

@kotlin.Metadata(mv = {1, 9, 0}, k = 1, xi = 48, d1 = {"\u00004\n\u0002\u0018\u0002\n\u0002\u0010\u0000\n\u0002\b\u0002\n\u0002\u0010\u000e\n\u0000\n\u0002\u0010\u0002\n\u0000\n\u0002\u0018\u0002\n\u0000\n\u0002\u0018\u0002\n\u0002\b\u0002\n\u0002\u0018\u0002\n\u0002\b\u0006\n\u0002\u0010\u000b\n\u0000\b\u00c6\u0002\u0018\u00002\u00020\u0001B\u0007\b\u0002\u00a2\u0006\u0002\u0010\u0002J\'\u0010\u0005\u001a\u00020\u00062\u0006\u0010\u0007\u001a\u00020\b2\f\u0010\t\u001a\b\u0012\u0004\u0012\u00020\u00060\nH\u0086@\u00f8\u0001\u0000\u00a2\u0006\u0002\u0010\u000bJ\u0013\u0010\f\u001a\u0004\u0018\u00010\rH\u0082@\u00f8\u0001\u0000\u00a2\u0006\u0002\u0010\u000eJ>\u0010\u000f\u001a\u00020\u00062\u0006\u0010\u0007\u001a\u00020\b2\u0006\u0010\u0010\u001a\u00020\u00042\u0006\u0010\u0011\u001a\u00020\u00042\u0006\u0010\u0012\u001a\u00020\u00042\u0006\u0010\u0013\u001a\u00020\u00142\f\u0010\t\u001a\b\u0012\u0004\u0012\u00020\u00060\nH\u0002R\u000e\u0010\u0003\u001a\u00020\u0004X\u0082T\u00a2\u0006\u0002\n\u0000\u0082\u0002\u0004\n\u0002\b\u0019\u00a8\u0006\u0015"}, d2 = {"Lcom/bingetv/app/utils/UpdateManager;", "", "()V", "UPDATE_URL", "", "checkForUpdates", "", "context", "Landroid/content/Context;", "onComplete", "Lkotlin/Function0;", "(Landroid/content/Context;Lkotlin/jvm/functions/Function0;Lkotlin/coroutines/Continuation;)Ljava/lang/Object;", "fetchUpdateInfo", "Lcom/bingetv/app/data/api/AppUpdate;", "(Lkotlin/coroutines/Continuation;)Ljava/lang/Object;", "showUpdateDialog", "version", "notes", "url", "mandatory", "", "app_debug"})
public final class UpdateManager {
    @org.jetbrains.annotations.NotNull
    private static final java.lang.String UPDATE_URL = "https://raw.githubusercontent.com/bingetv/app/main/update.json";
    @org.jetbrains.annotations.NotNull
    public static final com.bingetv.app.utils.UpdateManager INSTANCE = null;
    
    private UpdateManager() {
        super();
    }
    
    @org.jetbrains.annotations.Nullable
    public final java.lang.Object checkForUpdates(@org.jetbrains.annotations.NotNull
    android.content.Context context, @org.jetbrains.annotations.NotNull
    kotlin.jvm.functions.Function0<kotlin.Unit> onComplete, @org.jetbrains.annotations.NotNull
    kotlin.coroutines.Continuation<? super kotlin.Unit> $completion) {
        return null;
    }
    
    private final java.lang.Object fetchUpdateInfo(kotlin.coroutines.Continuation<? super com.bingetv.app.data.api.AppUpdate> $completion) {
        return null;
    }
    
    private final void showUpdateDialog(android.content.Context context, java.lang.String version, java.lang.String notes, java.lang.String url, boolean mandatory, kotlin.jvm.functions.Function0<kotlin.Unit> onComplete) {
    }
}