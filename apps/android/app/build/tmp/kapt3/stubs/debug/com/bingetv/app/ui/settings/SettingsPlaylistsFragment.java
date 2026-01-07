package com.bingetv.app.ui.settings;

@kotlin.Metadata(mv = {1, 9, 0}, k = 1, xi = 48, d1 = {"\u00008\n\u0002\u0018\u0002\n\u0002\u0018\u0002\n\u0002\b\u0002\n\u0002\u0018\u0002\n\u0000\n\u0002\u0018\u0002\n\u0000\n\u0002\u0018\u0002\n\u0000\n\u0002\u0018\u0002\n\u0000\n\u0002\u0018\u0002\n\u0000\n\u0002\u0018\u0002\n\u0000\n\u0002\u0010\u0002\n\u0002\b\u0004\u0018\u00002\u00020\u0001:\u0001\u0013B\u0005\u00a2\u0006\u0002\u0010\u0002J&\u0010\u0007\u001a\u0004\u0018\u00010\b2\u0006\u0010\t\u001a\u00020\n2\b\u0010\u000b\u001a\u0004\u0018\u00010\f2\b\u0010\r\u001a\u0004\u0018\u00010\u000eH\u0016J\u001a\u0010\u000f\u001a\u00020\u00102\u0006\u0010\u0011\u001a\u00020\b2\b\u0010\r\u001a\u0004\u0018\u00010\u000eH\u0016J\b\u0010\u0012\u001a\u00020\u0010H\u0002R\u0012\u0010\u0003\u001a\u00060\u0004R\u00020\u0000X\u0082.\u00a2\u0006\u0002\n\u0000R\u000e\u0010\u0005\u001a\u00020\u0006X\u0082.\u00a2\u0006\u0002\n\u0000\u00a8\u0006\u0014"}, d2 = {"Lcom/bingetv/app/ui/settings/SettingsPlaylistsFragment;", "Landroidx/fragment/app/Fragment;", "()V", "adapter", "Lcom/bingetv/app/ui/settings/SettingsPlaylistsFragment$PlaylistsAdapter;", "repository", "Lcom/bingetv/app/data/repository/PlaylistRepository;", "onCreateView", "Landroid/view/View;", "inflater", "Landroid/view/LayoutInflater;", "container", "Landroid/view/ViewGroup;", "savedInstanceState", "Landroid/os/Bundle;", "onViewCreated", "", "view", "verifyPlaylists", "PlaylistsAdapter", "app_debug"})
public final class SettingsPlaylistsFragment extends androidx.fragment.app.Fragment {
    private com.bingetv.app.data.repository.PlaylistRepository repository;
    private com.bingetv.app.ui.settings.SettingsPlaylistsFragment.PlaylistsAdapter adapter;
    
    public SettingsPlaylistsFragment() {
        super();
    }
    
    @java.lang.Override
    @org.jetbrains.annotations.Nullable
    public android.view.View onCreateView(@org.jetbrains.annotations.NotNull
    android.view.LayoutInflater inflater, @org.jetbrains.annotations.Nullable
    android.view.ViewGroup container, @org.jetbrains.annotations.Nullable
    android.os.Bundle savedInstanceState) {
        return null;
    }
    
    @java.lang.Override
    public void onViewCreated(@org.jetbrains.annotations.NotNull
    android.view.View view, @org.jetbrains.annotations.Nullable
    android.os.Bundle savedInstanceState) {
    }
    
    private final void verifyPlaylists() {
    }
    
    @kotlin.Metadata(mv = {1, 9, 0}, k = 1, xi = 48, d1 = {"\u0000<\n\u0002\u0018\u0002\n\u0002\u0018\u0002\n\u0002\u0018\u0002\n\u0002\u0018\u0002\n\u0002\b\u0002\n\u0002\u0010 \n\u0002\u0018\u0002\n\u0000\n\u0002\u0010\u0002\n\u0002\b\u0002\n\u0002\u0018\u0002\n\u0000\n\u0002\u0010\b\n\u0002\b\u0005\n\u0002\u0018\u0002\n\u0002\b\u0007\b\u0086\u0004\u0018\u00002\u0010\u0012\f\u0012\n0\u0002R\u00060\u0000R\u00020\u00030\u0001:\u0001\u001aB\u0005\u00a2\u0006\u0002\u0010\u0004J\u0018\u0010\b\u001a\u00020\t2\u0006\u0010\n\u001a\u00020\u00072\u0006\u0010\u000b\u001a\u00020\fH\u0002J\b\u0010\r\u001a\u00020\u000eH\u0016J \u0010\u000f\u001a\u00020\t2\u000e\u0010\u0010\u001a\n0\u0002R\u00060\u0000R\u00020\u00032\u0006\u0010\u0011\u001a\u00020\u000eH\u0016J \u0010\u0012\u001a\n0\u0002R\u00060\u0000R\u00020\u00032\u0006\u0010\u0013\u001a\u00020\u00142\u0006\u0010\u0015\u001a\u00020\u000eH\u0016J\u0010\u0010\u0016\u001a\u00020\t2\u0006\u0010\u000b\u001a\u00020\fH\u0002J\u0018\u0010\u0017\u001a\u00020\t2\u0006\u0010\n\u001a\u00020\u00072\u0006\u0010\u000b\u001a\u00020\fH\u0002J\u0014\u0010\u0018\u001a\u00020\t2\f\u0010\u0019\u001a\b\u0012\u0004\u0012\u00020\u00070\u0006R\u0014\u0010\u0005\u001a\b\u0012\u0004\u0012\u00020\u00070\u0006X\u0082\u000e\u00a2\u0006\u0002\n\u0000\u00a8\u0006\u001b"}, d2 = {"Lcom/bingetv/app/ui/settings/SettingsPlaylistsFragment$PlaylistsAdapter;", "Landroidx/recyclerview/widget/RecyclerView$Adapter;", "Lcom/bingetv/app/ui/settings/SettingsPlaylistsFragment$PlaylistsAdapter$ViewHolder;", "Lcom/bingetv/app/ui/settings/SettingsPlaylistsFragment;", "(Lcom/bingetv/app/ui/settings/SettingsPlaylistsFragment;)V", "items", "", "Lcom/bingetv/app/data/database/PlaylistEntity;", "confirmDelete", "", "playlist", "context", "Landroid/content/Context;", "getItemCount", "", "onBindViewHolder", "holder", "position", "onCreateViewHolder", "parent", "Landroid/view/ViewGroup;", "viewType", "restartApp", "showPlaylistOptions", "submitList", "newItems", "ViewHolder", "app_debug"})
    public final class PlaylistsAdapter extends androidx.recyclerview.widget.RecyclerView.Adapter<com.bingetv.app.ui.settings.SettingsPlaylistsFragment.PlaylistsAdapter.ViewHolder> {
        @org.jetbrains.annotations.NotNull
        private java.util.List<com.bingetv.app.data.database.PlaylistEntity> items;
        
        public PlaylistsAdapter() {
            super();
        }
        
        public final void submitList(@org.jetbrains.annotations.NotNull
        java.util.List<com.bingetv.app.data.database.PlaylistEntity> newItems) {
        }
        
        @java.lang.Override
        @org.jetbrains.annotations.NotNull
        public com.bingetv.app.ui.settings.SettingsPlaylistsFragment.PlaylistsAdapter.ViewHolder onCreateViewHolder(@org.jetbrains.annotations.NotNull
        android.view.ViewGroup parent, int viewType) {
            return null;
        }
        
        @java.lang.Override
        public void onBindViewHolder(@org.jetbrains.annotations.NotNull
        com.bingetv.app.ui.settings.SettingsPlaylistsFragment.PlaylistsAdapter.ViewHolder holder, int position) {
        }
        
        private final void showPlaylistOptions(com.bingetv.app.data.database.PlaylistEntity playlist, android.content.Context context) {
        }
        
        private final void confirmDelete(com.bingetv.app.data.database.PlaylistEntity playlist, android.content.Context context) {
        }
        
        private final void restartApp(android.content.Context context) {
        }
        
        @java.lang.Override
        public int getItemCount() {
            return 0;
        }
        
        @kotlin.Metadata(mv = {1, 9, 0}, k = 1, xi = 48, d1 = {"\u0000\u001a\n\u0002\u0018\u0002\n\u0002\u0018\u0002\n\u0000\n\u0002\u0018\u0002\n\u0002\b\u0002\n\u0002\u0018\u0002\n\u0002\b\u0007\b\u0086\u0004\u0018\u00002\u00020\u0001B\r\u0012\u0006\u0010\u0002\u001a\u00020\u0003\u00a2\u0006\u0002\u0010\u0004R\u0011\u0010\u0005\u001a\u00020\u0006\u00a2\u0006\b\n\u0000\u001a\u0004\b\u0007\u0010\bR\u0011\u0010\t\u001a\u00020\u0006\u00a2\u0006\b\n\u0000\u001a\u0004\b\n\u0010\bR\u0011\u0010\u000b\u001a\u00020\u0006\u00a2\u0006\b\n\u0000\u001a\u0004\b\f\u0010\b\u00a8\u0006\r"}, d2 = {"Lcom/bingetv/app/ui/settings/SettingsPlaylistsFragment$PlaylistsAdapter$ViewHolder;", "Landroidx/recyclerview/widget/RecyclerView$ViewHolder;", "view", "Landroid/view/View;", "(Lcom/bingetv/app/ui/settings/SettingsPlaylistsFragment$PlaylistsAdapter;Landroid/view/View;)V", "name", "Landroid/widget/TextView;", "getName", "()Landroid/widget/TextView;", "status", "getStatus", "url", "getUrl", "app_debug"})
        public final class ViewHolder extends androidx.recyclerview.widget.RecyclerView.ViewHolder {
            @org.jetbrains.annotations.NotNull
            private final android.widget.TextView name = null;
            @org.jetbrains.annotations.NotNull
            private final android.widget.TextView url = null;
            @org.jetbrains.annotations.NotNull
            private final android.widget.TextView status = null;
            
            public ViewHolder(@org.jetbrains.annotations.NotNull
            android.view.View view) {
                super(null);
            }
            
            @org.jetbrains.annotations.NotNull
            public final android.widget.TextView getName() {
                return null;
            }
            
            @org.jetbrains.annotations.NotNull
            public final android.widget.TextView getUrl() {
                return null;
            }
            
            @org.jetbrains.annotations.NotNull
            public final android.widget.TextView getStatus() {
                return null;
            }
        }
    }
}