package com.bingetv.app.parser;

@kotlin.Metadata(mv = {1, 9, 0}, k = 1, xi = 48, d1 = {"\u0000*\n\u0002\u0018\u0002\n\u0002\u0010\u0000\n\u0002\b\u0002\n\u0002\u0018\u0002\n\u0000\n\u0002\u0010$\n\u0002\u0010\u000e\n\u0002\b\u0002\n\u0002\u0010 \n\u0002\u0018\u0002\n\u0002\b\u0004\u0018\u0000 \u000e2\u00020\u0001:\u0001\u000eB\u0005\u00a2\u0006\u0002\u0010\u0002J\u001c\u0010\u0005\u001a\u000e\u0012\u0004\u0012\u00020\u0007\u0012\u0004\u0012\u00020\u00070\u00062\u0006\u0010\b\u001a\u00020\u0007H\u0002J\u001f\u0010\t\u001a\b\u0012\u0004\u0012\u00020\u000b0\n2\u0006\u0010\f\u001a\u00020\u0007H\u0086@\u00f8\u0001\u0000\u00a2\u0006\u0002\u0010\rR\u000e\u0010\u0003\u001a\u00020\u0004X\u0082\u0004\u00a2\u0006\u0002\n\u0000\u0082\u0002\u0004\n\u0002\b\u0019\u00a8\u0006\u000f"}, d2 = {"Lcom/bingetv/app/parser/M3UParser;", "", "()V", "client", "Lokhttp3/OkHttpClient;", "extractAttributes", "", "", "line", "parsePlaylist", "", "Lcom/bingetv/app/model/Channel;", "url", "(Ljava/lang/String;Lkotlin/coroutines/Continuation;)Ljava/lang/Object;", "Companion", "app_release"})
public final class M3UParser {
    @org.jetbrains.annotations.NotNull
    private final okhttp3.OkHttpClient client = null;
    private static final java.util.regex.Pattern ATTRIBUTE_PATTERN = null;
    @org.jetbrains.annotations.NotNull
    public static final com.bingetv.app.parser.M3UParser.Companion Companion = null;
    
    public M3UParser() {
        super();
    }
    
    @org.jetbrains.annotations.Nullable
    public final java.lang.Object parsePlaylist(@org.jetbrains.annotations.NotNull
    java.lang.String url, @org.jetbrains.annotations.NotNull
    kotlin.coroutines.Continuation<? super java.util.List<com.bingetv.app.model.Channel>> $completion) {
        return null;
    }
    
    private final java.util.Map<java.lang.String, java.lang.String> extractAttributes(java.lang.String line) {
        return null;
    }
    
    @kotlin.Metadata(mv = {1, 9, 0}, k = 1, xi = 48, d1 = {"\u0000\u0014\n\u0002\u0018\u0002\n\u0002\u0010\u0000\n\u0002\b\u0002\n\u0002\u0018\u0002\n\u0002\b\u0002\b\u0086\u0003\u0018\u00002\u00020\u0001B\u0007\b\u0002\u00a2\u0006\u0002\u0010\u0002R\u0016\u0010\u0003\u001a\n \u0005*\u0004\u0018\u00010\u00040\u0004X\u0082\u0004\u00a2\u0006\u0002\n\u0000\u00a8\u0006\u0006"}, d2 = {"Lcom/bingetv/app/parser/M3UParser$Companion;", "", "()V", "ATTRIBUTE_PATTERN", "Ljava/util/regex/Pattern;", "kotlin.jvm.PlatformType", "app_release"})
    public static final class Companion {
        
        private Companion() {
            super();
        }
    }
}