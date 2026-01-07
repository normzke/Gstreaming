package com.bingetv.app.ui.adapters;

@kotlin.Metadata(mv = {1, 9, 0}, k = 1, xi = 48, d1 = {"\u0000B\n\u0002\u0018\u0002\n\u0002\u0018\u0002\n\u0002\u0018\u0002\n\u0002\u0018\u0002\n\u0000\n\u0002\u0018\u0002\n\u0002\u0010\u0002\n\u0002\b\u0004\n\u0002\u0010$\n\u0002\u0010\u000e\n\u0002\u0010 \n\u0002\u0018\u0002\n\u0002\b\u0003\n\u0002\u0010\b\n\u0002\b\u0002\n\u0002\u0018\u0002\n\u0002\b\u0006\u0018\u00002\u000e\u0012\u0004\u0012\u00020\u0002\u0012\u0004\u0012\u00020\u00030\u0001:\u0002\u0019\u001aBI\u0012\u0012\u0010\u0004\u001a\u000e\u0012\u0004\u0012\u00020\u0002\u0012\u0004\u0012\u00020\u00060\u0005\u0012\u0016\b\u0002\u0010\u0007\u001a\u0010\u0012\u0004\u0012\u00020\u0002\u0012\u0004\u0012\u00020\u0006\u0018\u00010\u0005\u0012\u0016\b\u0002\u0010\b\u001a\u0010\u0012\u0004\u0012\u00020\u0002\u0012\u0004\u0012\u00020\u0006\u0018\u00010\u0005\u00a2\u0006\u0002\u0010\tJ\u0018\u0010\u000f\u001a\u00020\u00062\u0006\u0010\u0010\u001a\u00020\u00032\u0006\u0010\u0011\u001a\u00020\u0012H\u0016J\u0018\u0010\u0013\u001a\u00020\u00032\u0006\u0010\u0014\u001a\u00020\u00152\u0006\u0010\u0016\u001a\u00020\u0012H\u0016J \u0010\u0017\u001a\u00020\u00062\u0018\u0010\u0018\u001a\u0014\u0012\u0004\u0012\u00020\f\u0012\n\u0012\b\u0012\u0004\u0012\u00020\u000e0\r0\u000bR \u0010\n\u001a\u0014\u0012\u0004\u0012\u00020\f\u0012\n\u0012\b\u0012\u0004\u0012\u00020\u000e0\r0\u000bX\u0082\u000e\u00a2\u0006\u0002\n\u0000R\u001a\u0010\u0004\u001a\u000e\u0012\u0004\u0012\u00020\u0002\u0012\u0004\u0012\u00020\u00060\u0005X\u0082\u0004\u00a2\u0006\u0002\n\u0000R\u001c\u0010\b\u001a\u0010\u0012\u0004\u0012\u00020\u0002\u0012\u0004\u0012\u00020\u0006\u0018\u00010\u0005X\u0082\u0004\u00a2\u0006\u0002\n\u0000R\u001c\u0010\u0007\u001a\u0010\u0012\u0004\u0012\u00020\u0002\u0012\u0004\u0012\u00020\u0006\u0018\u00010\u0005X\u0082\u0004\u00a2\u0006\u0002\n\u0000\u00a8\u0006\u001b"}, d2 = {"Lcom/bingetv/app/ui/adapters/ChannelPagingAdapter;", "Landroidx/paging/PagingDataAdapter;", "Lcom/bingetv/app/data/database/ChannelEntity;", "Lcom/bingetv/app/ui/adapters/ChannelPagingAdapter$ChannelViewHolder;", "onChannelClick", "Lkotlin/Function1;", "", "onChannelLongClick", "onChannelFocused", "(Lkotlin/jvm/functions/Function1;Lkotlin/jvm/functions/Function1;Lkotlin/jvm/functions/Function1;)V", "epgData", "", "", "", "Lcom/bingetv/app/data/database/EpgProgramEntity;", "onBindViewHolder", "holder", "position", "", "onCreateViewHolder", "parent", "Landroid/view/ViewGroup;", "viewType", "submitEpgData", "data", "ChannelDiffCallback", "ChannelViewHolder", "app_release"})
public final class ChannelPagingAdapter extends androidx.paging.PagingDataAdapter<com.bingetv.app.data.database.ChannelEntity, com.bingetv.app.ui.adapters.ChannelPagingAdapter.ChannelViewHolder> {
    @org.jetbrains.annotations.NotNull
    private final kotlin.jvm.functions.Function1<com.bingetv.app.data.database.ChannelEntity, kotlin.Unit> onChannelClick = null;
    @org.jetbrains.annotations.Nullable
    private final kotlin.jvm.functions.Function1<com.bingetv.app.data.database.ChannelEntity, kotlin.Unit> onChannelLongClick = null;
    @org.jetbrains.annotations.Nullable
    private final kotlin.jvm.functions.Function1<com.bingetv.app.data.database.ChannelEntity, kotlin.Unit> onChannelFocused = null;
    @org.jetbrains.annotations.NotNull
    private java.util.Map<java.lang.String, ? extends java.util.List<com.bingetv.app.data.database.EpgProgramEntity>> epgData;
    
    public ChannelPagingAdapter(@org.jetbrains.annotations.NotNull
    kotlin.jvm.functions.Function1<? super com.bingetv.app.data.database.ChannelEntity, kotlin.Unit> onChannelClick, @org.jetbrains.annotations.Nullable
    kotlin.jvm.functions.Function1<? super com.bingetv.app.data.database.ChannelEntity, kotlin.Unit> onChannelLongClick, @org.jetbrains.annotations.Nullable
    kotlin.jvm.functions.Function1<? super com.bingetv.app.data.database.ChannelEntity, kotlin.Unit> onChannelFocused) {
        super(null, null, null);
    }
    
    public final void submitEpgData(@org.jetbrains.annotations.NotNull
    java.util.Map<java.lang.String, ? extends java.util.List<com.bingetv.app.data.database.EpgProgramEntity>> data) {
    }
    
    @java.lang.Override
    @org.jetbrains.annotations.NotNull
    public com.bingetv.app.ui.adapters.ChannelPagingAdapter.ChannelViewHolder onCreateViewHolder(@org.jetbrains.annotations.NotNull
    android.view.ViewGroup parent, int viewType) {
        return null;
    }
    
    @java.lang.Override
    public void onBindViewHolder(@org.jetbrains.annotations.NotNull
    com.bingetv.app.ui.adapters.ChannelPagingAdapter.ChannelViewHolder holder, int position) {
    }
    
    @kotlin.Metadata(mv = {1, 9, 0}, k = 1, xi = 48, d1 = {"\u0000\u0018\n\u0002\u0018\u0002\n\u0002\u0018\u0002\n\u0002\u0018\u0002\n\u0002\b\u0002\n\u0002\u0010\u000b\n\u0002\b\u0004\u0018\u00002\b\u0012\u0004\u0012\u00020\u00020\u0001B\u0005\u00a2\u0006\u0002\u0010\u0003J\u0018\u0010\u0004\u001a\u00020\u00052\u0006\u0010\u0006\u001a\u00020\u00022\u0006\u0010\u0007\u001a\u00020\u0002H\u0016J\u0018\u0010\b\u001a\u00020\u00052\u0006\u0010\u0006\u001a\u00020\u00022\u0006\u0010\u0007\u001a\u00020\u0002H\u0016\u00a8\u0006\t"}, d2 = {"Lcom/bingetv/app/ui/adapters/ChannelPagingAdapter$ChannelDiffCallback;", "Landroidx/recyclerview/widget/DiffUtil$ItemCallback;", "Lcom/bingetv/app/data/database/ChannelEntity;", "()V", "areContentsTheSame", "", "oldItem", "newItem", "areItemsTheSame", "app_release"})
    public static final class ChannelDiffCallback extends androidx.recyclerview.widget.DiffUtil.ItemCallback<com.bingetv.app.data.database.ChannelEntity> {
        
        public ChannelDiffCallback() {
            super();
        }
        
        @java.lang.Override
        public boolean areItemsTheSame(@org.jetbrains.annotations.NotNull
        com.bingetv.app.data.database.ChannelEntity oldItem, @org.jetbrains.annotations.NotNull
        com.bingetv.app.data.database.ChannelEntity newItem) {
            return false;
        }
        
        @java.lang.Override
        public boolean areContentsTheSame(@org.jetbrains.annotations.NotNull
        com.bingetv.app.data.database.ChannelEntity oldItem, @org.jetbrains.annotations.NotNull
        com.bingetv.app.data.database.ChannelEntity newItem) {
            return false;
        }
    }
    
    @kotlin.Metadata(mv = {1, 9, 0}, k = 1, xi = 48, d1 = {"\u0000@\n\u0002\u0018\u0002\n\u0002\u0018\u0002\n\u0000\n\u0002\u0018\u0002\n\u0002\b\u0002\n\u0002\u0018\u0002\n\u0002\b\u0002\n\u0002\u0018\u0002\n\u0000\n\u0002\u0018\u0002\n\u0002\b\u0003\n\u0002\u0010\u0002\n\u0000\n\u0002\u0018\u0002\n\u0000\n\u0002\u0010 \n\u0002\u0018\u0002\n\u0002\b\u0002\u0018\u00002\u00020\u0001B\r\u0012\u0006\u0010\u0002\u001a\u00020\u0003\u00a2\u0006\u0002\u0010\u0004J\u001e\u0010\u000e\u001a\u00020\u000f2\u0006\u0010\u0010\u001a\u00020\u00112\u000e\u0010\u0012\u001a\n\u0012\u0004\u0012\u00020\u0014\u0018\u00010\u0013J\u0006\u0010\u0015\u001a\u00020\u000fR\u000e\u0010\u0005\u001a\u00020\u0006X\u0082\u0004\u00a2\u0006\u0002\n\u0000R\u000e\u0010\u0007\u001a\u00020\u0006X\u0082\u0004\u00a2\u0006\u0002\n\u0000R\u000e\u0010\b\u001a\u00020\tX\u0082\u0004\u00a2\u0006\u0002\n\u0000R\u000e\u0010\n\u001a\u00020\u000bX\u0082\u0004\u00a2\u0006\u0002\n\u0000R\u000e\u0010\f\u001a\u00020\u000bX\u0082\u0004\u00a2\u0006\u0002\n\u0000R\u000e\u0010\r\u001a\u00020\u0006X\u0082\u0004\u00a2\u0006\u0002\n\u0000\u00a8\u0006\u0016"}, d2 = {"Lcom/bingetv/app/ui/adapters/ChannelPagingAdapter$ChannelViewHolder;", "Landroidx/recyclerview/widget/RecyclerView$ViewHolder;", "itemView", "Landroid/view/View;", "(Landroid/view/View;)V", "epgNext", "Landroid/widget/TextView;", "epgNow", "epgProgress", "Landroid/widget/ProgressBar;", "favoriteIcon", "Landroid/widget/ImageView;", "logoImage", "nameText", "bind", "", "channel", "Lcom/bingetv/app/data/database/ChannelEntity;", "epgList", "", "Lcom/bingetv/app/data/database/EpgProgramEntity;", "bindPlaceholder", "app_release"})
    public static final class ChannelViewHolder extends androidx.recyclerview.widget.RecyclerView.ViewHolder {
        @org.jetbrains.annotations.NotNull
        private final android.widget.ImageView logoImage = null;
        @org.jetbrains.annotations.NotNull
        private final android.widget.TextView nameText = null;
        @org.jetbrains.annotations.NotNull
        private final android.widget.ImageView favoriteIcon = null;
        @org.jetbrains.annotations.NotNull
        private final android.widget.TextView epgNow = null;
        @org.jetbrains.annotations.NotNull
        private final android.widget.TextView epgNext = null;
        @org.jetbrains.annotations.NotNull
        private final android.widget.ProgressBar epgProgress = null;
        
        public ChannelViewHolder(@org.jetbrains.annotations.NotNull
        android.view.View itemView) {
            super(null);
        }
        
        public final void bindPlaceholder() {
        }
        
        public final void bind(@org.jetbrains.annotations.NotNull
        com.bingetv.app.data.database.ChannelEntity channel, @org.jetbrains.annotations.Nullable
        java.util.List<com.bingetv.app.data.database.EpgProgramEntity> epgList) {
        }
    }
}