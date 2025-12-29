package com.bingetv.app.ui.dialogs;

@kotlin.Metadata(mv = {1, 9, 0}, k = 1, xi = 48, d1 = {"\u0000*\n\u0002\u0018\u0002\n\u0002\u0018\u0002\n\u0000\n\u0002\u0018\u0002\n\u0000\n\u0002\u0018\u0002\n\u0000\n\u0002\u0018\u0002\n\u0002\u0010\u0002\n\u0002\b\u0004\n\u0002\u0018\u0002\n\u0002\b\u0002\u0018\u00002\u00020\u0001B=\u0012\u0006\u0010\u0002\u001a\u00020\u0003\u0012\u0006\u0010\u0004\u001a\u00020\u0005\u0012\u0012\u0010\u0006\u001a\u000e\u0012\u0004\u0012\u00020\u0005\u0012\u0004\u0012\u00020\b0\u0007\u0012\u0012\u0010\t\u001a\u000e\u0012\u0004\u0012\u00020\u0005\u0012\u0004\u0012\u00020\b0\u0007\u00a2\u0006\u0002\u0010\nJ\u0012\u0010\u000b\u001a\u00020\b2\b\u0010\f\u001a\u0004\u0018\u00010\rH\u0014J\b\u0010\u000e\u001a\u00020\bH\u0002R\u000e\u0010\u0004\u001a\u00020\u0005X\u0082\u0004\u00a2\u0006\u0002\n\u0000R\u001a\u0010\t\u001a\u000e\u0012\u0004\u0012\u00020\u0005\u0012\u0004\u0012\u00020\b0\u0007X\u0082\u0004\u00a2\u0006\u0002\n\u0000R\u001a\u0010\u0006\u001a\u000e\u0012\u0004\u0012\u00020\u0005\u0012\u0004\u0012\u00020\b0\u0007X\u0082\u0004\u00a2\u0006\u0002\n\u0000\u00a8\u0006\u000f"}, d2 = {"Lcom/bingetv/app/ui/dialogs/ChannelContextDialog;", "Landroid/app/Dialog;", "context", "Landroid/content/Context;", "channel", "Lcom/bingetv/app/data/database/ChannelEntity;", "onToggleFavorite", "Lkotlin/Function1;", "", "onPlayChannel", "(Landroid/content/Context;Lcom/bingetv/app/data/database/ChannelEntity;Lkotlin/jvm/functions/Function1;Lkotlin/jvm/functions/Function1;)V", "onCreate", "savedInstanceState", "Landroid/os/Bundle;", "setupViews", "app_debug"})
public final class ChannelContextDialog extends android.app.Dialog {
    @org.jetbrains.annotations.NotNull
    private final com.bingetv.app.data.database.ChannelEntity channel = null;
    @org.jetbrains.annotations.NotNull
    private final kotlin.jvm.functions.Function1<com.bingetv.app.data.database.ChannelEntity, kotlin.Unit> onToggleFavorite = null;
    @org.jetbrains.annotations.NotNull
    private final kotlin.jvm.functions.Function1<com.bingetv.app.data.database.ChannelEntity, kotlin.Unit> onPlayChannel = null;
    
    public ChannelContextDialog(@org.jetbrains.annotations.NotNull
    android.content.Context context, @org.jetbrains.annotations.NotNull
    com.bingetv.app.data.database.ChannelEntity channel, @org.jetbrains.annotations.NotNull
    kotlin.jvm.functions.Function1<? super com.bingetv.app.data.database.ChannelEntity, kotlin.Unit> onToggleFavorite, @org.jetbrains.annotations.NotNull
    kotlin.jvm.functions.Function1<? super com.bingetv.app.data.database.ChannelEntity, kotlin.Unit> onPlayChannel) {
        super(null);
    }
    
    @java.lang.Override
    protected void onCreate(@org.jetbrains.annotations.Nullable
    android.os.Bundle savedInstanceState) {
    }
    
    private final void setupViews() {
    }
}