package com.bingetv.app.ui.dialogs;

@kotlin.Metadata(mv = {1, 9, 0}, k = 1, xi = 48, d1 = {"\u0000J\n\u0002\u0018\u0002\n\u0002\u0018\u0002\n\u0000\n\u0002\u0018\u0002\n\u0000\n\u0002\u0010 \n\u0002\u0018\u0002\n\u0000\n\u0002\u0018\u0002\n\u0002\u0010\u0002\n\u0002\b\u0002\n\u0002\u0018\u0002\n\u0000\n\u0002\u0018\u0002\n\u0000\n\u0002\u0018\u0002\n\u0002\b\u0002\n\u0002\u0018\u0002\n\u0002\b\u0002\n\u0002\u0010\u000e\n\u0002\b\u0003\u0018\u00002\u00020\u0001B/\u0012\u0006\u0010\u0002\u001a\u00020\u0003\u0012\f\u0010\u0004\u001a\b\u0012\u0004\u0012\u00020\u00060\u0005\u0012\u0012\u0010\u0007\u001a\u000e\u0012\u0004\u0012\u00020\u0006\u0012\u0004\u0012\u00020\t0\b\u00a2\u0006\u0002\u0010\nJ\u0012\u0010\u0011\u001a\u00020\t2\b\u0010\u0012\u001a\u0004\u0018\u00010\u0013H\u0014J\u0010\u0010\u0014\u001a\u00020\t2\u0006\u0010\u0015\u001a\u00020\u0016H\u0002J\b\u0010\u0017\u001a\u00020\tH\u0002J\b\u0010\u0018\u001a\u00020\tH\u0002R\u000e\u0010\u000b\u001a\u00020\fX\u0082.\u00a2\u0006\u0002\n\u0000R\u0014\u0010\u0004\u001a\b\u0012\u0004\u0012\u00020\u00060\u0005X\u0082\u0004\u00a2\u0006\u0002\n\u0000R\u001a\u0010\u0007\u001a\u000e\u0012\u0004\u0012\u00020\u0006\u0012\u0004\u0012\u00020\t0\bX\u0082\u0004\u00a2\u0006\u0002\n\u0000R\u000e\u0010\r\u001a\u00020\u000eX\u0082.\u00a2\u0006\u0002\n\u0000R\u000e\u0010\u000f\u001a\u00020\u0010X\u0082.\u00a2\u0006\u0002\n\u0000\u00a8\u0006\u0019"}, d2 = {"Lcom/bingetv/app/ui/dialogs/SearchDialog;", "Landroid/app/Dialog;", "context", "Landroid/content/Context;", "allChannels", "", "Lcom/bingetv/app/data/database/ChannelEntity;", "onChannelSelected", "Lkotlin/Function1;", "", "(Landroid/content/Context;Ljava/util/List;Lkotlin/jvm/functions/Function1;)V", "adapter", "Lcom/bingetv/app/ui/adapters/ChannelGridAdapter;", "resultsRecyclerView", "Landroidx/recyclerview/widget/RecyclerView;", "searchInput", "Landroid/widget/EditText;", "onCreate", "savedInstanceState", "Landroid/os/Bundle;", "performSearch", "query", "", "setupRecyclerView", "setupSearch", "app_debug"})
public final class SearchDialog extends android.app.Dialog {
    @org.jetbrains.annotations.NotNull
    private final java.util.List<com.bingetv.app.data.database.ChannelEntity> allChannels = null;
    @org.jetbrains.annotations.NotNull
    private final kotlin.jvm.functions.Function1<com.bingetv.app.data.database.ChannelEntity, kotlin.Unit> onChannelSelected = null;
    private android.widget.EditText searchInput;
    private androidx.recyclerview.widget.RecyclerView resultsRecyclerView;
    private com.bingetv.app.ui.adapters.ChannelGridAdapter adapter;
    
    public SearchDialog(@org.jetbrains.annotations.NotNull
    android.content.Context context, @org.jetbrains.annotations.NotNull
    java.util.List<com.bingetv.app.data.database.ChannelEntity> allChannels, @org.jetbrains.annotations.NotNull
    kotlin.jvm.functions.Function1<? super com.bingetv.app.data.database.ChannelEntity, kotlin.Unit> onChannelSelected) {
        super(null);
    }
    
    @java.lang.Override
    protected void onCreate(@org.jetbrains.annotations.Nullable
    android.os.Bundle savedInstanceState) {
    }
    
    private final void setupRecyclerView() {
    }
    
    private final void setupSearch() {
    }
    
    private final void performSearch(java.lang.String query) {
    }
}