package com.bingetv.app;

@kotlin.Metadata(mv = {1, 9, 0}, k = 1, xi = 48, d1 = {"\u0000>\n\u0002\u0018\u0002\n\u0002\u0018\u0002\n\u0002\b\u0002\n\u0002\u0018\u0002\n\u0000\n\u0002\u0010 \n\u0002\u0018\u0002\n\u0000\n\u0002\u0018\u0002\n\u0000\n\u0002\u0018\u0002\n\u0000\n\u0002\u0010\u0002\n\u0000\n\u0002\u0010\u000e\n\u0002\b\u0002\n\u0002\u0018\u0002\n\u0002\b\b\u0018\u00002\u00020\u0001:\u0002\u0018\u0019B\u0005\u00a2\u0006\u0002\u0010\u0002J\u0010\u0010\f\u001a\u00020\r2\u0006\u0010\u000e\u001a\u00020\u000fH\u0002J\u0012\u0010\u0010\u001a\u00020\r2\b\u0010\u0011\u001a\u0004\u0018\u00010\u0012H\u0014J\b\u0010\u0013\u001a\u00020\rH\u0014J\b\u0010\u0014\u001a\u00020\rH\u0002J\b\u0010\u0015\u001a\u00020\rH\u0002J\b\u0010\u0016\u001a\u00020\rH\u0002J\b\u0010\u0017\u001a\u00020\rH\u0002R\u000e\u0010\u0003\u001a\u00020\u0004X\u0082.\u00a2\u0006\u0002\n\u0000R\u0014\u0010\u0005\u001a\b\u0012\u0004\u0012\u00020\u00070\u0006X\u0082\u000e\u00a2\u0006\u0002\n\u0000R\u000e\u0010\b\u001a\u00020\tX\u0082\u0004\u00a2\u0006\u0002\n\u0000R\u000e\u0010\n\u001a\u00020\u000bX\u0082\u0004\u00a2\u0006\u0002\n\u0000\u00a8\u0006\u001a"}, d2 = {"Lcom/bingetv/app/MainActivity;", "Landroidx/fragment/app/FragmentActivity;", "()V", "browseFragment", "Landroidx/leanback/app/BrowseSupportFragment;", "channels", "", "Lcom/bingetv/app/model/Channel;", "parser", "Lcom/bingetv/app/parser/M3UParser;", "scope", "Lkotlinx/coroutines/CoroutineScope;", "loadPlaylist", "", "url", "", "onCreate", "savedInstanceState", "Landroid/os/Bundle;", "onDestroy", "setupEventListeners", "setupRowsAdapter", "setupUIElements", "showPlaylistInputDialog", "ItemViewClickedListener", "ItemViewSelectedListener", "app_debug"})
public final class MainActivity extends androidx.fragment.app.FragmentActivity {
    private androidx.leanback.app.BrowseSupportFragment browseFragment;
    @org.jetbrains.annotations.NotNull
    private final com.bingetv.app.parser.M3UParser parser = null;
    @org.jetbrains.annotations.NotNull
    private java.util.List<com.bingetv.app.model.Channel> channels;
    @org.jetbrains.annotations.NotNull
    private final kotlinx.coroutines.CoroutineScope scope = null;
    
    public MainActivity() {
        super();
    }
    
    @java.lang.Override
    protected void onCreate(@org.jetbrains.annotations.Nullable
    android.os.Bundle savedInstanceState) {
    }
    
    private final void setupUIElements() {
    }
    
    private final void setupEventListeners() {
    }
    
    private final void showPlaylistInputDialog() {
    }
    
    private final void loadPlaylist(java.lang.String url) {
    }
    
    private final void setupRowsAdapter() {
    }
    
    @java.lang.Override
    protected void onDestroy() {
    }
    
    @kotlin.Metadata(mv = {1, 9, 0}, k = 1, xi = 48, d1 = {"\u0000*\n\u0002\u0018\u0002\n\u0002\u0018\u0002\n\u0002\b\u0002\n\u0002\u0010\u0002\n\u0000\n\u0002\u0018\u0002\n\u0000\n\u0002\u0010\u0000\n\u0000\n\u0002\u0018\u0002\n\u0000\n\u0002\u0018\u0002\n\u0000\b\u0082\u0004\u0018\u00002\u00020\u0001B\u0005\u00a2\u0006\u0002\u0010\u0002J0\u0010\u0003\u001a\u00020\u00042\b\u0010\u0005\u001a\u0004\u0018\u00010\u00062\b\u0010\u0007\u001a\u0004\u0018\u00010\b2\b\u0010\t\u001a\u0004\u0018\u00010\n2\b\u0010\u000b\u001a\u0004\u0018\u00010\fH\u0016\u00a8\u0006\r"}, d2 = {"Lcom/bingetv/app/MainActivity$ItemViewClickedListener;", "Landroidx/leanback/widget/OnItemViewClickedListener;", "(Lcom/bingetv/app/MainActivity;)V", "onItemClicked", "", "itemViewHolder", "Landroidx/leanback/widget/Presenter$ViewHolder;", "item", "", "rowViewHolder", "Landroidx/leanback/widget/RowPresenter$ViewHolder;", "row", "Landroidx/leanback/widget/Row;", "app_debug"})
    final class ItemViewClickedListener implements androidx.leanback.widget.OnItemViewClickedListener {
        
        public ItemViewClickedListener() {
            super();
        }
        
        @java.lang.Override
        public void onItemClicked(@org.jetbrains.annotations.Nullable
        androidx.leanback.widget.Presenter.ViewHolder itemViewHolder, @org.jetbrains.annotations.Nullable
        java.lang.Object item, @org.jetbrains.annotations.Nullable
        androidx.leanback.widget.RowPresenter.ViewHolder rowViewHolder, @org.jetbrains.annotations.Nullable
        androidx.leanback.widget.Row row) {
        }
    }
    
    @kotlin.Metadata(mv = {1, 9, 0}, k = 1, xi = 48, d1 = {"\u0000*\n\u0002\u0018\u0002\n\u0002\u0018\u0002\n\u0002\b\u0002\n\u0002\u0010\u0002\n\u0000\n\u0002\u0018\u0002\n\u0000\n\u0002\u0010\u0000\n\u0000\n\u0002\u0018\u0002\n\u0000\n\u0002\u0018\u0002\n\u0000\b\u0082\u0004\u0018\u00002\u00020\u0001B\u0005\u00a2\u0006\u0002\u0010\u0002J0\u0010\u0003\u001a\u00020\u00042\b\u0010\u0005\u001a\u0004\u0018\u00010\u00062\b\u0010\u0007\u001a\u0004\u0018\u00010\b2\b\u0010\t\u001a\u0004\u0018\u00010\n2\b\u0010\u000b\u001a\u0004\u0018\u00010\fH\u0016\u00a8\u0006\r"}, d2 = {"Lcom/bingetv/app/MainActivity$ItemViewSelectedListener;", "Landroidx/leanback/widget/OnItemViewSelectedListener;", "(Lcom/bingetv/app/MainActivity;)V", "onItemSelected", "", "itemViewHolder", "Landroidx/leanback/widget/Presenter$ViewHolder;", "item", "", "rowViewHolder", "Landroidx/leanback/widget/RowPresenter$ViewHolder;", "row", "Landroidx/leanback/widget/Row;", "app_debug"})
    final class ItemViewSelectedListener implements androidx.leanback.widget.OnItemViewSelectedListener {
        
        public ItemViewSelectedListener() {
            super();
        }
        
        @java.lang.Override
        public void onItemSelected(@org.jetbrains.annotations.Nullable
        androidx.leanback.widget.Presenter.ViewHolder itemViewHolder, @org.jetbrains.annotations.Nullable
        java.lang.Object item, @org.jetbrains.annotations.Nullable
        androidx.leanback.widget.RowPresenter.ViewHolder rowViewHolder, @org.jetbrains.annotations.Nullable
        androidx.leanback.widget.Row row) {
        }
    }
}