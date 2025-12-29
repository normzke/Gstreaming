package com.bingetv.app.ui.adapters;

@kotlin.Metadata(mv = {1, 9, 0}, k = 1, xi = 48, d1 = {"\u0000.\n\u0002\u0018\u0002\n\u0002\u0018\u0002\n\u0002\u0018\u0002\n\u0002\u0018\u0002\n\u0000\n\u0002\u0018\u0002\n\u0002\u0010\u0002\n\u0002\b\u0005\n\u0002\u0010\b\n\u0002\b\u0002\n\u0002\u0018\u0002\n\u0002\b\u0004\u0018\u00002\u000e\u0012\u0004\u0012\u00020\u0002\u0012\u0004\u0012\u00020\u00030\u0001:\u0002\u0011\u0012B1\u0012\u0012\u0010\u0004\u001a\u000e\u0012\u0004\u0012\u00020\u0002\u0012\u0004\u0012\u00020\u00060\u0005\u0012\u0016\b\u0002\u0010\u0007\u001a\u0010\u0012\u0004\u0012\u00020\u0002\u0012\u0004\u0012\u00020\u0006\u0018\u00010\u0005\u00a2\u0006\u0002\u0010\bJ\u0018\u0010\t\u001a\u00020\u00062\u0006\u0010\n\u001a\u00020\u00032\u0006\u0010\u000b\u001a\u00020\fH\u0016J\u0018\u0010\r\u001a\u00020\u00032\u0006\u0010\u000e\u001a\u00020\u000f2\u0006\u0010\u0010\u001a\u00020\fH\u0016R\u001a\u0010\u0004\u001a\u000e\u0012\u0004\u0012\u00020\u0002\u0012\u0004\u0012\u00020\u00060\u0005X\u0082\u0004\u00a2\u0006\u0002\n\u0000R\u001c\u0010\u0007\u001a\u0010\u0012\u0004\u0012\u00020\u0002\u0012\u0004\u0012\u00020\u0006\u0018\u00010\u0005X\u0082\u0004\u00a2\u0006\u0002\n\u0000\u00a8\u0006\u0013"}, d2 = {"Lcom/bingetv/app/ui/adapters/ChannelGridAdapter;", "Landroidx/recyclerview/widget/ListAdapter;", "Lcom/bingetv/app/data/database/ChannelEntity;", "Lcom/bingetv/app/ui/adapters/ChannelGridAdapter$ChannelViewHolder;", "onChannelClick", "Lkotlin/Function1;", "", "onChannelLongClick", "(Lkotlin/jvm/functions/Function1;Lkotlin/jvm/functions/Function1;)V", "onBindViewHolder", "holder", "position", "", "onCreateViewHolder", "parent", "Landroid/view/ViewGroup;", "viewType", "ChannelDiffCallback", "ChannelViewHolder", "app_debug"})
public final class ChannelGridAdapter extends androidx.recyclerview.widget.ListAdapter<com.bingetv.app.data.database.ChannelEntity, com.bingetv.app.ui.adapters.ChannelGridAdapter.ChannelViewHolder> {
    @org.jetbrains.annotations.NotNull
    private final kotlin.jvm.functions.Function1<com.bingetv.app.data.database.ChannelEntity, kotlin.Unit> onChannelClick = null;
    @org.jetbrains.annotations.Nullable
    private final kotlin.jvm.functions.Function1<com.bingetv.app.data.database.ChannelEntity, kotlin.Unit> onChannelLongClick = null;
    
    public ChannelGridAdapter(@org.jetbrains.annotations.NotNull
    kotlin.jvm.functions.Function1<? super com.bingetv.app.data.database.ChannelEntity, kotlin.Unit> onChannelClick, @org.jetbrains.annotations.Nullable
    kotlin.jvm.functions.Function1<? super com.bingetv.app.data.database.ChannelEntity, kotlin.Unit> onChannelLongClick) {
        super(null);
    }
    
    @java.lang.Override
    @org.jetbrains.annotations.NotNull
    public com.bingetv.app.ui.adapters.ChannelGridAdapter.ChannelViewHolder onCreateViewHolder(@org.jetbrains.annotations.NotNull
    android.view.ViewGroup parent, int viewType) {
        return null;
    }
    
    @java.lang.Override
    public void onBindViewHolder(@org.jetbrains.annotations.NotNull
    com.bingetv.app.ui.adapters.ChannelGridAdapter.ChannelViewHolder holder, int position) {
    }
    
    @kotlin.Metadata(mv = {1, 9, 0}, k = 1, xi = 48, d1 = {"\u0000\u0018\n\u0002\u0018\u0002\n\u0002\u0018\u0002\n\u0002\u0018\u0002\n\u0002\b\u0002\n\u0002\u0010\u000b\n\u0002\b\u0004\u0018\u00002\b\u0012\u0004\u0012\u00020\u00020\u0001B\u0005\u00a2\u0006\u0002\u0010\u0003J\u0018\u0010\u0004\u001a\u00020\u00052\u0006\u0010\u0006\u001a\u00020\u00022\u0006\u0010\u0007\u001a\u00020\u0002H\u0016J\u0018\u0010\b\u001a\u00020\u00052\u0006\u0010\u0006\u001a\u00020\u00022\u0006\u0010\u0007\u001a\u00020\u0002H\u0016\u00a8\u0006\t"}, d2 = {"Lcom/bingetv/app/ui/adapters/ChannelGridAdapter$ChannelDiffCallback;", "Landroidx/recyclerview/widget/DiffUtil$ItemCallback;", "Lcom/bingetv/app/data/database/ChannelEntity;", "()V", "areContentsTheSame", "", "oldItem", "newItem", "areItemsTheSame", "app_debug"})
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
    
    @kotlin.Metadata(mv = {1, 9, 0}, k = 1, xi = 48, d1 = {"\u0000,\n\u0002\u0018\u0002\n\u0002\u0018\u0002\n\u0000\n\u0002\u0018\u0002\n\u0002\b\u0002\n\u0002\u0018\u0002\n\u0002\b\u0002\n\u0002\u0018\u0002\n\u0000\n\u0002\u0010\u0002\n\u0000\n\u0002\u0018\u0002\n\u0000\u0018\u00002\u00020\u0001B\r\u0012\u0006\u0010\u0002\u001a\u00020\u0003\u00a2\u0006\u0002\u0010\u0004J\u000e\u0010\n\u001a\u00020\u000b2\u0006\u0010\f\u001a\u00020\rR\u000e\u0010\u0005\u001a\u00020\u0006X\u0082\u0004\u00a2\u0006\u0002\n\u0000R\u000e\u0010\u0007\u001a\u00020\u0006X\u0082\u0004\u00a2\u0006\u0002\n\u0000R\u000e\u0010\b\u001a\u00020\tX\u0082\u0004\u00a2\u0006\u0002\n\u0000\u00a8\u0006\u000e"}, d2 = {"Lcom/bingetv/app/ui/adapters/ChannelGridAdapter$ChannelViewHolder;", "Landroidx/recyclerview/widget/RecyclerView$ViewHolder;", "itemView", "Landroid/view/View;", "(Landroid/view/View;)V", "favoriteIcon", "Landroid/widget/ImageView;", "logoImage", "nameText", "Landroid/widget/TextView;", "bind", "", "channel", "Lcom/bingetv/app/data/database/ChannelEntity;", "app_debug"})
    public static final class ChannelViewHolder extends androidx.recyclerview.widget.RecyclerView.ViewHolder {
        @org.jetbrains.annotations.NotNull
        private final android.widget.ImageView logoImage = null;
        @org.jetbrains.annotations.NotNull
        private final android.widget.TextView nameText = null;
        @org.jetbrains.annotations.NotNull
        private final android.widget.ImageView favoriteIcon = null;
        
        public ChannelViewHolder(@org.jetbrains.annotations.NotNull
        android.view.View itemView) {
            super(null);
        }
        
        public final void bind(@org.jetbrains.annotations.NotNull
        com.bingetv.app.data.database.ChannelEntity channel) {
        }
    }
}