package com.bingetv.app.data.database;

@kotlin.Metadata(mv = {1, 9, 0}, k = 1, xi = 48, d1 = {"\u00006\n\u0002\u0018\u0002\n\u0002\u0018\u0002\n\u0000\n\u0002\u0010\b\n\u0002\b\u0002\n\u0002\u0010\u000e\n\u0000\n\u0002\u0010\u000b\n\u0002\b/\n\u0002\u0010\u0000\n\u0002\b\u0003\n\u0002\u0010\u0002\n\u0000\n\u0002\u0018\u0002\n\u0002\b\u0002\b\u0087\b\u0018\u00002\u00020\u0001Bk\u0012\b\b\u0002\u0010\u0002\u001a\u00020\u0003\u0012\b\b\u0002\u0010\u0004\u001a\u00020\u0003\u0012\b\b\u0002\u0010\u0005\u001a\u00020\u0006\u0012\b\b\u0002\u0010\u0007\u001a\u00020\b\u0012\b\b\u0002\u0010\t\u001a\u00020\b\u0012\b\b\u0002\u0010\n\u001a\u00020\b\u0012\n\b\u0002\u0010\u000b\u001a\u0004\u0018\u00010\u0006\u0012\b\b\u0002\u0010\f\u001a\u00020\u0006\u0012\b\b\u0002\u0010\r\u001a\u00020\b\u0012\b\b\u0002\u0010\u000e\u001a\u00020\u0006\u00a2\u0006\u0002\u0010\u000fJ\t\u0010*\u001a\u00020\u0003H\u00c6\u0003J\t\u0010+\u001a\u00020\u0006H\u00c6\u0003J\t\u0010,\u001a\u00020\u0003H\u00c6\u0003J\t\u0010-\u001a\u00020\u0006H\u00c6\u0003J\t\u0010.\u001a\u00020\bH\u00c6\u0003J\t\u0010/\u001a\u00020\bH\u00c6\u0003J\t\u00100\u001a\u00020\bH\u00c6\u0003J\u000b\u00101\u001a\u0004\u0018\u00010\u0006H\u00c6\u0003J\t\u00102\u001a\u00020\u0006H\u00c6\u0003J\t\u00103\u001a\u00020\bH\u00c6\u0003Jo\u00104\u001a\u00020\u00002\b\b\u0002\u0010\u0002\u001a\u00020\u00032\b\b\u0002\u0010\u0004\u001a\u00020\u00032\b\b\u0002\u0010\u0005\u001a\u00020\u00062\b\b\u0002\u0010\u0007\u001a\u00020\b2\b\b\u0002\u0010\t\u001a\u00020\b2\b\b\u0002\u0010\n\u001a\u00020\b2\n\b\u0002\u0010\u000b\u001a\u0004\u0018\u00010\u00062\b\b\u0002\u0010\f\u001a\u00020\u00062\b\b\u0002\u0010\r\u001a\u00020\b2\b\b\u0002\u0010\u000e\u001a\u00020\u0006H\u00c6\u0001J\t\u00105\u001a\u00020\u0003H\u00d6\u0001J\u0013\u00106\u001a\u00020\b2\b\u00107\u001a\u0004\u0018\u000108H\u00d6\u0003J\t\u00109\u001a\u00020\u0003H\u00d6\u0001J\t\u0010:\u001a\u00020\u0006H\u00d6\u0001J\u0019\u0010;\u001a\u00020<2\u0006\u0010=\u001a\u00020>2\u0006\u0010?\u001a\u00020\u0003H\u00d6\u0001R\u001a\u0010\r\u001a\u00020\bX\u0086\u000e\u00a2\u0006\u000e\n\u0000\u001a\u0004\b\u0010\u0010\u0011\"\u0004\b\u0012\u0010\u0013R\u001a\u0010\f\u001a\u00020\u0006X\u0086\u000e\u00a2\u0006\u000e\n\u0000\u001a\u0004\b\u0014\u0010\u0015\"\u0004\b\u0016\u0010\u0017R\u001a\u0010\u0004\u001a\u00020\u0003X\u0086\u000e\u00a2\u0006\u000e\n\u0000\u001a\u0004\b\u0018\u0010\u0019\"\u0004\b\u001a\u0010\u001bR\u001e\u0010\u0002\u001a\u00020\u00038\u0006@\u0006X\u0087\u000e\u00a2\u0006\u000e\n\u0000\u001a\u0004\b\u001c\u0010\u0019\"\u0004\b\u001d\u0010\u001bR\u001a\u0010\u0005\u001a\u00020\u0006X\u0086\u000e\u00a2\u0006\u000e\n\u0000\u001a\u0004\b\u001e\u0010\u0015\"\u0004\b\u001f\u0010\u0017R\u001a\u0010\n\u001a\u00020\bX\u0086\u000e\u00a2\u0006\u000e\n\u0000\u001a\u0004\b \u0010\u0011\"\u0004\b!\u0010\u0013R\u001c\u0010\u000b\u001a\u0004\u0018\u00010\u0006X\u0086\u000e\u00a2\u0006\u000e\n\u0000\u001a\u0004\b\"\u0010\u0015\"\u0004\b#\u0010\u0017R\u001a\u0010\u0007\u001a\u00020\bX\u0086\u000e\u00a2\u0006\u000e\n\u0000\u001a\u0004\b$\u0010\u0011\"\u0004\b%\u0010\u0013R\u001a\u0010\t\u001a\u00020\bX\u0086\u000e\u00a2\u0006\u000e\n\u0000\u001a\u0004\b&\u0010\u0011\"\u0004\b\'\u0010\u0013R\u001a\u0010\u000e\u001a\u00020\u0006X\u0086\u000e\u00a2\u0006\u000e\n\u0000\u001a\u0004\b(\u0010\u0015\"\u0004\b)\u0010\u0017\u00a8\u0006@"}, d2 = {"Lcom/bingetv/app/data/database/UserPreferencesEntity;", "Landroid/os/Parcelable;", "id", "", "gridColumns", "logoSize", "", "showChannelNumbers", "", "showNowPlaying", "parentalControlEnabled", "parentalControlPin", "defaultQuality", "autoPlayNext", "theme", "(IILjava/lang/String;ZZZLjava/lang/String;Ljava/lang/String;ZLjava/lang/String;)V", "getAutoPlayNext", "()Z", "setAutoPlayNext", "(Z)V", "getDefaultQuality", "()Ljava/lang/String;", "setDefaultQuality", "(Ljava/lang/String;)V", "getGridColumns", "()I", "setGridColumns", "(I)V", "getId", "setId", "getLogoSize", "setLogoSize", "getParentalControlEnabled", "setParentalControlEnabled", "getParentalControlPin", "setParentalControlPin", "getShowChannelNumbers", "setShowChannelNumbers", "getShowNowPlaying", "setShowNowPlaying", "getTheme", "setTheme", "component1", "component10", "component2", "component3", "component4", "component5", "component6", "component7", "component8", "component9", "copy", "describeContents", "equals", "other", "", "hashCode", "toString", "writeToParcel", "", "parcel", "Landroid/os/Parcel;", "flags", "app_debug"})
@kotlinx.parcelize.Parcelize
@androidx.room.Entity(tableName = "user_preferences")
public final class UserPreferencesEntity implements android.os.Parcelable {
    @androidx.room.PrimaryKey
    private int id;
    private int gridColumns;
    @org.jetbrains.annotations.NotNull
    private java.lang.String logoSize;
    private boolean showChannelNumbers;
    private boolean showNowPlaying;
    private boolean parentalControlEnabled;
    @org.jetbrains.annotations.Nullable
    private java.lang.String parentalControlPin;
    @org.jetbrains.annotations.NotNull
    private java.lang.String defaultQuality;
    private boolean autoPlayNext;
    @org.jetbrains.annotations.NotNull
    private java.lang.String theme;
    
    public UserPreferencesEntity(int id, int gridColumns, @org.jetbrains.annotations.NotNull
    java.lang.String logoSize, boolean showChannelNumbers, boolean showNowPlaying, boolean parentalControlEnabled, @org.jetbrains.annotations.Nullable
    java.lang.String parentalControlPin, @org.jetbrains.annotations.NotNull
    java.lang.String defaultQuality, boolean autoPlayNext, @org.jetbrains.annotations.NotNull
    java.lang.String theme) {
        super();
    }
    
    public final int getId() {
        return 0;
    }
    
    public final void setId(int p0) {
    }
    
    public final int getGridColumns() {
        return 0;
    }
    
    public final void setGridColumns(int p0) {
    }
    
    @org.jetbrains.annotations.NotNull
    public final java.lang.String getLogoSize() {
        return null;
    }
    
    public final void setLogoSize(@org.jetbrains.annotations.NotNull
    java.lang.String p0) {
    }
    
    public final boolean getShowChannelNumbers() {
        return false;
    }
    
    public final void setShowChannelNumbers(boolean p0) {
    }
    
    public final boolean getShowNowPlaying() {
        return false;
    }
    
    public final void setShowNowPlaying(boolean p0) {
    }
    
    public final boolean getParentalControlEnabled() {
        return false;
    }
    
    public final void setParentalControlEnabled(boolean p0) {
    }
    
    @org.jetbrains.annotations.Nullable
    public final java.lang.String getParentalControlPin() {
        return null;
    }
    
    public final void setParentalControlPin(@org.jetbrains.annotations.Nullable
    java.lang.String p0) {
    }
    
    @org.jetbrains.annotations.NotNull
    public final java.lang.String getDefaultQuality() {
        return null;
    }
    
    public final void setDefaultQuality(@org.jetbrains.annotations.NotNull
    java.lang.String p0) {
    }
    
    public final boolean getAutoPlayNext() {
        return false;
    }
    
    public final void setAutoPlayNext(boolean p0) {
    }
    
    @org.jetbrains.annotations.NotNull
    public final java.lang.String getTheme() {
        return null;
    }
    
    public final void setTheme(@org.jetbrains.annotations.NotNull
    java.lang.String p0) {
    }
    
    public UserPreferencesEntity() {
        super();
    }
    
    public final int component1() {
        return 0;
    }
    
    @org.jetbrains.annotations.NotNull
    public final java.lang.String component10() {
        return null;
    }
    
    public final int component2() {
        return 0;
    }
    
    @org.jetbrains.annotations.NotNull
    public final java.lang.String component3() {
        return null;
    }
    
    public final boolean component4() {
        return false;
    }
    
    public final boolean component5() {
        return false;
    }
    
    public final boolean component6() {
        return false;
    }
    
    @org.jetbrains.annotations.Nullable
    public final java.lang.String component7() {
        return null;
    }
    
    @org.jetbrains.annotations.NotNull
    public final java.lang.String component8() {
        return null;
    }
    
    public final boolean component9() {
        return false;
    }
    
    @org.jetbrains.annotations.NotNull
    public final com.bingetv.app.data.database.UserPreferencesEntity copy(int id, int gridColumns, @org.jetbrains.annotations.NotNull
    java.lang.String logoSize, boolean showChannelNumbers, boolean showNowPlaying, boolean parentalControlEnabled, @org.jetbrains.annotations.Nullable
    java.lang.String parentalControlPin, @org.jetbrains.annotations.NotNull
    java.lang.String defaultQuality, boolean autoPlayNext, @org.jetbrains.annotations.NotNull
    java.lang.String theme) {
        return null;
    }
    
    @java.lang.Override
    public int describeContents() {
        return 0;
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
    
    @java.lang.Override
    public void writeToParcel(@org.jetbrains.annotations.NotNull
    android.os.Parcel parcel, int flags) {
    }
}