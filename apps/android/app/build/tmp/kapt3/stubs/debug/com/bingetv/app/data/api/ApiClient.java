package com.bingetv.app.data.api;

@kotlin.Metadata(mv = {1, 9, 0}, k = 1, xi = 48, d1 = {"\u0000,\n\u0002\u0018\u0002\n\u0002\u0010\u0000\n\u0002\b\u0002\n\u0002\u0018\u0002\n\u0000\n\u0002\u0018\u0002\n\u0000\n\u0002\u0018\u0002\n\u0002\b\u0002\n\u0002\u0010\u000e\n\u0000\n\u0002\u0010\u0002\n\u0000\b\u00c6\u0002\u0018\u00002\u00020\u0001B\u0007\b\u0002\u00a2\u0006\u0002\u0010\u0002J\b\u0010\u0007\u001a\u00020\bH\u0002J\u000e\u0010\t\u001a\u00020\u00062\u0006\u0010\n\u001a\u00020\u000bJ\u0006\u0010\f\u001a\u00020\rR\u0010\u0010\u0003\u001a\u0004\u0018\u00010\u0004X\u0082\u000e\u00a2\u0006\u0002\n\u0000R\u0010\u0010\u0005\u001a\u0004\u0018\u00010\u0006X\u0082\u000e\u00a2\u0006\u0002\n\u0000\u00a8\u0006\u000e"}, d2 = {"Lcom/bingetv/app/data/api/ApiClient;", "", "()V", "retrofit", "Lretrofit2/Retrofit;", "xtreamApi", "Lcom/bingetv/app/data/api/XtreamCodesApi;", "getOkHttpClient", "Lokhttp3/OkHttpClient;", "getXtreamApi", "baseUrl", "", "reset", "", "app_debug"})
public final class ApiClient {
    @org.jetbrains.annotations.Nullable
    private static retrofit2.Retrofit retrofit;
    @org.jetbrains.annotations.Nullable
    private static com.bingetv.app.data.api.XtreamCodesApi xtreamApi;
    @org.jetbrains.annotations.NotNull
    public static final com.bingetv.app.data.api.ApiClient INSTANCE = null;
    
    private ApiClient() {
        super();
    }
    
    private final okhttp3.OkHttpClient getOkHttpClient() {
        return null;
    }
    
    @org.jetbrains.annotations.NotNull
    public final com.bingetv.app.data.api.XtreamCodesApi getXtreamApi(@org.jetbrains.annotations.NotNull
    java.lang.String baseUrl) {
        return null;
    }
    
    public final void reset() {
    }
}