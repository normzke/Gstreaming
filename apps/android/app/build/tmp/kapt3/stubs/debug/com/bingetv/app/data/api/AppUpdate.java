package com.bingetv.app.data.api;

@kotlin.Metadata(mv = {1, 9, 0}, k = 1, xi = 48, d1 = {"\u0000 \n\u0002\u0018\u0002\n\u0002\u0010\u0000\n\u0000\n\u0002\u0010\b\n\u0000\n\u0002\u0010\u000e\n\u0002\b\u0003\n\u0002\u0010\u000b\n\u0002\b\u0013\b\u0086\b\u0018\u00002\u00020\u0001B/\u0012\u0006\u0010\u0002\u001a\u00020\u0003\u0012\u0006\u0010\u0004\u001a\u00020\u0005\u0012\u0006\u0010\u0006\u001a\u00020\u0005\u0012\u0006\u0010\u0007\u001a\u00020\u0005\u0012\b\b\u0002\u0010\b\u001a\u00020\t\u00a2\u0006\u0002\u0010\nJ\t\u0010\u0012\u001a\u00020\u0003H\u00c6\u0003J\t\u0010\u0013\u001a\u00020\u0005H\u00c6\u0003J\t\u0010\u0014\u001a\u00020\u0005H\u00c6\u0003J\t\u0010\u0015\u001a\u00020\u0005H\u00c6\u0003J\t\u0010\u0016\u001a\u00020\tH\u00c6\u0003J;\u0010\u0017\u001a\u00020\u00002\b\b\u0002\u0010\u0002\u001a\u00020\u00032\b\b\u0002\u0010\u0004\u001a\u00020\u00052\b\b\u0002\u0010\u0006\u001a\u00020\u00052\b\b\u0002\u0010\u0007\u001a\u00020\u00052\b\b\u0002\u0010\b\u001a\u00020\tH\u00c6\u0001J\u0013\u0010\u0018\u001a\u00020\t2\b\u0010\u0019\u001a\u0004\u0018\u00010\u0001H\u00d6\u0003J\t\u0010\u001a\u001a\u00020\u0003H\u00d6\u0001J\t\u0010\u001b\u001a\u00020\u0005H\u00d6\u0001R\u0016\u0010\b\u001a\u00020\t8\u0006X\u0087\u0004\u00a2\u0006\b\n\u0000\u001a\u0004\b\b\u0010\u000bR\u0016\u0010\u0007\u001a\u00020\u00058\u0006X\u0087\u0004\u00a2\u0006\b\n\u0000\u001a\u0004\b\f\u0010\rR\u0016\u0010\u0006\u001a\u00020\u00058\u0006X\u0087\u0004\u00a2\u0006\b\n\u0000\u001a\u0004\b\u000e\u0010\rR\u0016\u0010\u0002\u001a\u00020\u00038\u0006X\u0087\u0004\u00a2\u0006\b\n\u0000\u001a\u0004\b\u000f\u0010\u0010R\u0016\u0010\u0004\u001a\u00020\u00058\u0006X\u0087\u0004\u00a2\u0006\b\n\u0000\u001a\u0004\b\u0011\u0010\r\u00a8\u0006\u001c"}, d2 = {"Lcom/bingetv/app/data/api/AppUpdate;", "", "versionCode", "", "versionName", "", "updateUrl", "releaseNotes", "isMandatory", "", "(ILjava/lang/String;Ljava/lang/String;Ljava/lang/String;Z)V", "()Z", "getReleaseNotes", "()Ljava/lang/String;", "getUpdateUrl", "getVersionCode", "()I", "getVersionName", "component1", "component2", "component3", "component4", "component5", "copy", "equals", "other", "hashCode", "toString", "app_debug"})
public final class AppUpdate {
    @com.google.gson.annotations.SerializedName(value = "versionCode")
    private final int versionCode = 0;
    @com.google.gson.annotations.SerializedName(value = "versionName")
    @org.jetbrains.annotations.NotNull
    private final java.lang.String versionName = null;
    @com.google.gson.annotations.SerializedName(value = "updateUrl")
    @org.jetbrains.annotations.NotNull
    private final java.lang.String updateUrl = null;
    @com.google.gson.annotations.SerializedName(value = "releaseNotes")
    @org.jetbrains.annotations.NotNull
    private final java.lang.String releaseNotes = null;
    @com.google.gson.annotations.SerializedName(value = "isMandatory")
    private final boolean isMandatory = false;
    
    public AppUpdate(int versionCode, @org.jetbrains.annotations.NotNull
    java.lang.String versionName, @org.jetbrains.annotations.NotNull
    java.lang.String updateUrl, @org.jetbrains.annotations.NotNull
    java.lang.String releaseNotes, boolean isMandatory) {
        super();
    }
    
    public final int getVersionCode() {
        return 0;
    }
    
    @org.jetbrains.annotations.NotNull
    public final java.lang.String getVersionName() {
        return null;
    }
    
    @org.jetbrains.annotations.NotNull
    public final java.lang.String getUpdateUrl() {
        return null;
    }
    
    @org.jetbrains.annotations.NotNull
    public final java.lang.String getReleaseNotes() {
        return null;
    }
    
    public final boolean isMandatory() {
        return false;
    }
    
    public final int component1() {
        return 0;
    }
    
    @org.jetbrains.annotations.NotNull
    public final java.lang.String component2() {
        return null;
    }
    
    @org.jetbrains.annotations.NotNull
    public final java.lang.String component3() {
        return null;
    }
    
    @org.jetbrains.annotations.NotNull
    public final java.lang.String component4() {
        return null;
    }
    
    public final boolean component5() {
        return false;
    }
    
    @org.jetbrains.annotations.NotNull
    public final com.bingetv.app.data.api.AppUpdate copy(int versionCode, @org.jetbrains.annotations.NotNull
    java.lang.String versionName, @org.jetbrains.annotations.NotNull
    java.lang.String updateUrl, @org.jetbrains.annotations.NotNull
    java.lang.String releaseNotes, boolean isMandatory) {
        return null;
    }
    
    @java.lang.Override
    public boolean equals(@org.jetbrains.annotations.Nullable
    java.lang.Object other) {
        return false;
    }
    
    @java.lang.Override
    public int hashCode() {
        return 0;
    }
    
    @java.lang.Override
    @org.jetbrains.annotations.NotNull
    public java.lang.String toString() {
        return null;
    }
}