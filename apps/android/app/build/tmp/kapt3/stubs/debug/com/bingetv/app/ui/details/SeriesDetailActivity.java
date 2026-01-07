package com.bingetv.app.ui.details;

@kotlin.Metadata(mv = {1, 9, 0}, k = 1, xi = 48, d1 = {"\u0000X\n\u0002\u0018\u0002\n\u0002\u0018\u0002\n\u0002\b\u0002\n\u0002\u0010$\n\u0002\u0010\u000e\n\u0002\u0010 \n\u0002\u0018\u0002\n\u0002\b\u0002\n\u0002\u0018\u0002\n\u0000\n\u0002\u0018\u0002\n\u0000\n\u0002\u0018\u0002\n\u0000\n\u0002\u0018\u0002\n\u0000\n\u0002\u0018\u0002\n\u0002\b\u0002\n\u0002\u0010\b\n\u0002\b\u0004\n\u0002\u0010\u0002\n\u0002\b\u0002\n\u0002\u0018\u0002\n\u0002\b\t\u0018\u0000 #2\u00020\u0001:\u0003#$%B\u0005\u00a2\u0006\u0002\u0010\u0002J\b\u0010\u0019\u001a\u00020\u001aH\u0002J\u0012\u0010\u001b\u001a\u00020\u001a2\b\u0010\u001c\u001a\u0004\u0018\u00010\u001dH\u0014J\u0010\u0010\u001e\u001a\u00020\u001a2\u0006\u0010\u001f\u001a\u00020\u0007H\u0002J\u0010\u0010 \u001a\u00020\u001a2\u0006\u0010!\u001a\u00020\u0005H\u0002J\b\u0010\"\u001a\u00020\u001aH\u0002R \u0010\u0003\u001a\u0014\u0012\u0004\u0012\u00020\u0005\u0012\n\u0012\b\u0012\u0004\u0012\u00020\u00070\u00060\u0004X\u0082\u000e\u00a2\u0006\u0002\n\u0000R\u000e\u0010\b\u001a\u00020\u0005X\u0082\u000e\u00a2\u0006\u0002\n\u0000R\u0012\u0010\t\u001a\u00060\nR\u00020\u0000X\u0082.\u00a2\u0006\u0002\n\u0000R\u000e\u0010\u000b\u001a\u00020\fX\u0082.\u00a2\u0006\u0002\n\u0000R\u000e\u0010\r\u001a\u00020\u000eX\u0082.\u00a2\u0006\u0002\n\u0000R\u000e\u0010\u000f\u001a\u00020\u0010X\u0082.\u00a2\u0006\u0002\n\u0000R\u0012\u0010\u0011\u001a\u00060\u0012R\u00020\u0000X\u0082.\u00a2\u0006\u0002\n\u0000R\u000e\u0010\u0013\u001a\u00020\fX\u0082.\u00a2\u0006\u0002\n\u0000R\u000e\u0010\u0014\u001a\u00020\u0015X\u0082\u000e\u00a2\u0006\u0002\n\u0000R\u000e\u0010\u0016\u001a\u00020\u0005X\u0082\u000e\u00a2\u0006\u0002\n\u0000R\u0010\u0010\u0017\u001a\u0004\u0018\u00010\u0005X\u0082\u000e\u00a2\u0006\u0002\n\u0000R\u0014\u0010\u0018\u001a\b\u0012\u0004\u0012\u00020\u00050\u0006X\u0082\u000e\u00a2\u0006\u0002\n\u0000\u00a8\u0006&"}, d2 = {"Lcom/bingetv/app/ui/details/SeriesDetailActivity;", "Landroidx/appcompat/app/AppCompatActivity;", "()V", "allEpisodes", "", "", "", "Lcom/bingetv/app/data/api/Episode;", "currentSeason", "episodeAdapter", "Lcom/bingetv/app/ui/details/SeriesDetailActivity$EpisodeAdapter;", "episodeRecycler", "Landroidx/recyclerview/widget/RecyclerView;", "playlistRepository", "Lcom/bingetv/app/data/repository/PlaylistRepository;", "prefsManager", "Lcom/bingetv/app/utils/PreferencesManager;", "seasonAdapter", "Lcom/bingetv/app/ui/details/SeriesDetailActivity$SeasonAdapter;", "seasonRecycler", "seriesId", "", "seriesName", "seriesPoserUrl", "sortedSeasons", "loadSeriesDetails", "", "onCreate", "savedInstanceState", "Landroid/os/Bundle;", "playEpisode", "episode", "selectSeason", "season", "setupUI", "Companion", "EpisodeAdapter", "SeasonAdapter", "app_debug"})
public final class SeriesDetailActivity extends androidx.appcompat.app.AppCompatActivity {
    private com.bingetv.app.data.repository.PlaylistRepository playlistRepository;
    private com.bingetv.app.utils.PreferencesManager prefsManager;
    private androidx.recyclerview.widget.RecyclerView seasonRecycler;
    private androidx.recyclerview.widget.RecyclerView episodeRecycler;
    private com.bingetv.app.ui.details.SeriesDetailActivity.SeasonAdapter seasonAdapter;
    private com.bingetv.app.ui.details.SeriesDetailActivity.EpisodeAdapter episodeAdapter;
    private int seriesId = 0;
    @org.jetbrains.annotations.NotNull
    private java.lang.String seriesName = "";
    @org.jetbrains.annotations.Nullable
    private java.lang.String seriesPoserUrl;
    @org.jetbrains.annotations.NotNull
    private java.util.Map<java.lang.String, ? extends java.util.List<com.bingetv.app.data.api.Episode>> allEpisodes;
    @org.jetbrains.annotations.NotNull
    private java.util.List<java.lang.String> sortedSeasons;
    @org.jetbrains.annotations.NotNull
    private java.lang.String currentSeason = "";
    @org.jetbrains.annotations.NotNull
    public static final java.lang.String EXTRA_SERIES_ID = "extra_series_id";
    @org.jetbrains.annotations.NotNull
    public static final java.lang.String EXTRA_SERIES_NAME = "extra_series_name";
    @org.jetbrains.annotations.NotNull
    public static final java.lang.String EXTRA_SERIES_POSTER = "extra_series_poster";
    @org.jetbrains.annotations.NotNull
    private static final java.lang.String TAG = "SeriesDetailActivity";
    @org.jetbrains.annotations.NotNull
    public static final com.bingetv.app.ui.details.SeriesDetailActivity.Companion Companion = null;
    
    public SeriesDetailActivity() {
        super();
    }
    
    @java.lang.Override
    protected void onCreate(@org.jetbrains.annotations.Nullable
    android.os.Bundle savedInstanceState) {
    }
    
    private final void setupUI() {
    }
    
    private final void loadSeriesDetails() {
    }
    
    private final void selectSeason(java.lang.String season) {
    }
    
    private final void playEpisode(com.bingetv.app.data.api.Episode episode) {
    }
    
    @kotlin.Metadata(mv = {1, 9, 0}, k = 1, xi = 48, d1 = {"\u0000\u0014\n\u0002\u0018\u0002\n\u0002\u0010\u0000\n\u0002\b\u0002\n\u0002\u0010\u000e\n\u0002\b\u0004\b\u0086\u0003\u0018\u00002\u00020\u0001B\u0007\b\u0002\u00a2\u0006\u0002\u0010\u0002R\u000e\u0010\u0003\u001a\u00020\u0004X\u0086T\u00a2\u0006\u0002\n\u0000R\u000e\u0010\u0005\u001a\u00020\u0004X\u0086T\u00a2\u0006\u0002\n\u0000R\u000e\u0010\u0006\u001a\u00020\u0004X\u0086T\u00a2\u0006\u0002\n\u0000R\u000e\u0010\u0007\u001a\u00020\u0004X\u0082T\u00a2\u0006\u0002\n\u0000\u00a8\u0006\b"}, d2 = {"Lcom/bingetv/app/ui/details/SeriesDetailActivity$Companion;", "", "()V", "EXTRA_SERIES_ID", "", "EXTRA_SERIES_NAME", "EXTRA_SERIES_POSTER", "TAG", "app_debug"})
    public static final class Companion {
        
        private Companion() {
            super();
        }
    }
    
    @kotlin.Metadata(mv = {1, 9, 0}, k = 1, xi = 48, d1 = {"\u00008\n\u0002\u0018\u0002\n\u0002\u0018\u0002\n\u0002\u0018\u0002\n\u0002\u0018\u0002\n\u0000\n\u0002\u0018\u0002\n\u0002\u0018\u0002\n\u0002\u0010\u0002\n\u0002\b\u0002\n\u0002\u0010 \n\u0000\n\u0002\u0010\b\n\u0002\b\u0005\n\u0002\u0018\u0002\n\u0002\b\u0005\b\u0086\u0004\u0018\u00002\u0010\u0012\f\u0012\n0\u0002R\u00060\u0000R\u00020\u00030\u0001:\u0001\u0016B\u0019\u0012\u0012\u0010\u0004\u001a\u000e\u0012\u0004\u0012\u00020\u0006\u0012\u0004\u0012\u00020\u00070\u0005\u00a2\u0006\u0002\u0010\bJ\b\u0010\u000b\u001a\u00020\fH\u0016J \u0010\r\u001a\u00020\u00072\u000e\u0010\u000e\u001a\n0\u0002R\u00060\u0000R\u00020\u00032\u0006\u0010\u000f\u001a\u00020\fH\u0016J \u0010\u0010\u001a\n0\u0002R\u00060\u0000R\u00020\u00032\u0006\u0010\u0011\u001a\u00020\u00122\u0006\u0010\u0013\u001a\u00020\fH\u0016J\u0014\u0010\u0014\u001a\u00020\u00072\f\u0010\u0015\u001a\b\u0012\u0004\u0012\u00020\u00060\nR\u0014\u0010\t\u001a\b\u0012\u0004\u0012\u00020\u00060\nX\u0082\u000e\u00a2\u0006\u0002\n\u0000R\u001a\u0010\u0004\u001a\u000e\u0012\u0004\u0012\u00020\u0006\u0012\u0004\u0012\u00020\u00070\u0005X\u0082\u0004\u00a2\u0006\u0002\n\u0000\u00a8\u0006\u0017"}, d2 = {"Lcom/bingetv/app/ui/details/SeriesDetailActivity$EpisodeAdapter;", "Landroidx/recyclerview/widget/RecyclerView$Adapter;", "Lcom/bingetv/app/ui/details/SeriesDetailActivity$EpisodeAdapter$ViewHolder;", "Lcom/bingetv/app/ui/details/SeriesDetailActivity;", "onClick", "Lkotlin/Function1;", "Lcom/bingetv/app/data/api/Episode;", "", "(Lcom/bingetv/app/ui/details/SeriesDetailActivity;Lkotlin/jvm/functions/Function1;)V", "items", "", "getItemCount", "", "onBindViewHolder", "holder", "position", "onCreateViewHolder", "parent", "Landroid/view/ViewGroup;", "viewType", "submitList", "list", "ViewHolder", "app_debug"})
    public final class EpisodeAdapter extends androidx.recyclerview.widget.RecyclerView.Adapter<com.bingetv.app.ui.details.SeriesDetailActivity.EpisodeAdapter.ViewHolder> {
        @org.jetbrains.annotations.NotNull
        private final kotlin.jvm.functions.Function1<com.bingetv.app.data.api.Episode, kotlin.Unit> onClick = null;
        @org.jetbrains.annotations.NotNull
        private java.util.List<com.bingetv.app.data.api.Episode> items;
        
        public EpisodeAdapter(@org.jetbrains.annotations.NotNull
        kotlin.jvm.functions.Function1<? super com.bingetv.app.data.api.Episode, kotlin.Unit> onClick) {
            super();
        }
        
        public final void submitList(@org.jetbrains.annotations.NotNull
        java.util.List<com.bingetv.app.data.api.Episode> list) {
        }
        
        @java.lang.Override
        @org.jetbrains.annotations.NotNull
        public com.bingetv.app.ui.details.SeriesDetailActivity.EpisodeAdapter.ViewHolder onCreateViewHolder(@org.jetbrains.annotations.NotNull
        android.view.ViewGroup parent, int viewType) {
            return null;
        }
        
        @java.lang.Override
        public void onBindViewHolder(@org.jetbrains.annotations.NotNull
        com.bingetv.app.ui.details.SeriesDetailActivity.EpisodeAdapter.ViewHolder holder, int position) {
        }
        
        @java.lang.Override
        public int getItemCount() {
            return 0;
        }
        
        @kotlin.Metadata(mv = {1, 9, 0}, k = 1, xi = 48, d1 = {"\u0000\u001a\n\u0002\u0018\u0002\n\u0002\u0018\u0002\n\u0000\n\u0002\u0018\u0002\n\u0002\b\u0002\n\u0002\u0018\u0002\n\u0002\b\u0005\b\u0086\u0004\u0018\u00002\u00020\u0001B\r\u0012\u0006\u0010\u0002\u001a\u00020\u0003\u00a2\u0006\u0002\u0010\u0004R\u0011\u0010\u0005\u001a\u00020\u0006\u00a2\u0006\b\n\u0000\u001a\u0004\b\u0007\u0010\bR\u0011\u0010\t\u001a\u00020\u0006\u00a2\u0006\b\n\u0000\u001a\u0004\b\n\u0010\b\u00a8\u0006\u000b"}, d2 = {"Lcom/bingetv/app/ui/details/SeriesDetailActivity$EpisodeAdapter$ViewHolder;", "Landroidx/recyclerview/widget/RecyclerView$ViewHolder;", "view", "Landroid/view/View;", "(Lcom/bingetv/app/ui/details/SeriesDetailActivity$EpisodeAdapter;Landroid/view/View;)V", "num", "Landroid/widget/TextView;", "getNum", "()Landroid/widget/TextView;", "title", "getTitle", "app_debug"})
        public final class ViewHolder extends androidx.recyclerview.widget.RecyclerView.ViewHolder {
            @org.jetbrains.annotations.NotNull
            private final android.widget.TextView num = null;
            @org.jetbrains.annotations.NotNull
            private final android.widget.TextView title = null;
            
            public ViewHolder(@org.jetbrains.annotations.NotNull
            android.view.View view) {
                super(null);
            }
            
            @org.jetbrains.annotations.NotNull
            public final android.widget.TextView getNum() {
                return null;
            }
            
            @org.jetbrains.annotations.NotNull
            public final android.widget.TextView getTitle() {
                return null;
            }
        }
    }
    
    @kotlin.Metadata(mv = {1, 9, 0}, k = 1, xi = 48, d1 = {"\u0000:\n\u0002\u0018\u0002\n\u0002\u0018\u0002\n\u0002\u0018\u0002\n\u0002\u0018\u0002\n\u0000\n\u0002\u0018\u0002\n\u0002\u0010\u000e\n\u0002\u0010\u0002\n\u0002\b\u0002\n\u0002\u0010 \n\u0002\b\u0002\n\u0002\u0010\b\n\u0002\b\u0005\n\u0002\u0018\u0002\n\u0002\b\u0007\b\u0086\u0004\u0018\u00002\u0010\u0012\f\u0012\n0\u0002R\u00060\u0000R\u00020\u00030\u0001:\u0001\u0019B\u0019\u0012\u0012\u0010\u0004\u001a\u000e\u0012\u0004\u0012\u00020\u0006\u0012\u0004\u0012\u00020\u00070\u0005\u00a2\u0006\u0002\u0010\bJ\b\u0010\f\u001a\u00020\rH\u0016J \u0010\u000e\u001a\u00020\u00072\u000e\u0010\u000f\u001a\n0\u0002R\u00060\u0000R\u00020\u00032\u0006\u0010\u0010\u001a\u00020\rH\u0016J \u0010\u0011\u001a\n0\u0002R\u00060\u0000R\u00020\u00032\u0006\u0010\u0012\u001a\u00020\u00132\u0006\u0010\u0014\u001a\u00020\rH\u0016J\u000e\u0010\u0015\u001a\u00020\u00072\u0006\u0010\u0016\u001a\u00020\u0006J\u0014\u0010\u0017\u001a\u00020\u00072\f\u0010\u0018\u001a\b\u0012\u0004\u0012\u00020\u00060\nR\u0014\u0010\t\u001a\b\u0012\u0004\u0012\u00020\u00060\nX\u0082\u000e\u00a2\u0006\u0002\n\u0000R\u001a\u0010\u0004\u001a\u000e\u0012\u0004\u0012\u00020\u0006\u0012\u0004\u0012\u00020\u00070\u0005X\u0082\u0004\u00a2\u0006\u0002\n\u0000R\u000e\u0010\u000b\u001a\u00020\u0006X\u0082\u000e\u00a2\u0006\u0002\n\u0000\u00a8\u0006\u001a"}, d2 = {"Lcom/bingetv/app/ui/details/SeriesDetailActivity$SeasonAdapter;", "Landroidx/recyclerview/widget/RecyclerView$Adapter;", "Lcom/bingetv/app/ui/details/SeriesDetailActivity$SeasonAdapter$ViewHolder;", "Lcom/bingetv/app/ui/details/SeriesDetailActivity;", "onClick", "Lkotlin/Function1;", "", "", "(Lcom/bingetv/app/ui/details/SeriesDetailActivity;Lkotlin/jvm/functions/Function1;)V", "items", "", "selectedSeason", "getItemCount", "", "onBindViewHolder", "holder", "position", "onCreateViewHolder", "parent", "Landroid/view/ViewGroup;", "viewType", "setSelected", "season", "submitList", "list", "ViewHolder", "app_debug"})
    public final class SeasonAdapter extends androidx.recyclerview.widget.RecyclerView.Adapter<com.bingetv.app.ui.details.SeriesDetailActivity.SeasonAdapter.ViewHolder> {
        @org.jetbrains.annotations.NotNull
        private final kotlin.jvm.functions.Function1<java.lang.String, kotlin.Unit> onClick = null;
        @org.jetbrains.annotations.NotNull
        private java.util.List<java.lang.String> items;
        @org.jetbrains.annotations.NotNull
        private java.lang.String selectedSeason = "";
        
        public SeasonAdapter(@org.jetbrains.annotations.NotNull
        kotlin.jvm.functions.Function1<? super java.lang.String, kotlin.Unit> onClick) {
            super();
        }
        
        public final void submitList(@org.jetbrains.annotations.NotNull
        java.util.List<java.lang.String> list) {
        }
        
        public final void setSelected(@org.jetbrains.annotations.NotNull
        java.lang.String season) {
        }
        
        @java.lang.Override
        @org.jetbrains.annotations.NotNull
        public com.bingetv.app.ui.details.SeriesDetailActivity.SeasonAdapter.ViewHolder onCreateViewHolder(@org.jetbrains.annotations.NotNull
        android.view.ViewGroup parent, int viewType) {
            return null;
        }
        
        @java.lang.Override
        public void onBindViewHolder(@org.jetbrains.annotations.NotNull
        com.bingetv.app.ui.details.SeriesDetailActivity.SeasonAdapter.ViewHolder holder, int position) {
        }
        
        @java.lang.Override
        public int getItemCount() {
            return 0;
        }
        
        @kotlin.Metadata(mv = {1, 9, 0}, k = 1, xi = 48, d1 = {"\u0000\u001a\n\u0002\u0018\u0002\n\u0002\u0018\u0002\n\u0000\n\u0002\u0018\u0002\n\u0002\b\u0002\n\u0002\u0018\u0002\n\u0002\b\u0003\b\u0086\u0004\u0018\u00002\u00020\u0001B\r\u0012\u0006\u0010\u0002\u001a\u00020\u0003\u00a2\u0006\u0002\u0010\u0004R\u0011\u0010\u0005\u001a\u00020\u0006\u00a2\u0006\b\n\u0000\u001a\u0004\b\u0007\u0010\b\u00a8\u0006\t"}, d2 = {"Lcom/bingetv/app/ui/details/SeriesDetailActivity$SeasonAdapter$ViewHolder;", "Landroidx/recyclerview/widget/RecyclerView$ViewHolder;", "view", "Landroid/view/View;", "(Lcom/bingetv/app/ui/details/SeriesDetailActivity$SeasonAdapter;Landroid/view/View;)V", "name", "Landroid/widget/TextView;", "getName", "()Landroid/widget/TextView;", "app_debug"})
        public final class ViewHolder extends androidx.recyclerview.widget.RecyclerView.ViewHolder {
            @org.jetbrains.annotations.NotNull
            private final android.widget.TextView name = null;
            
            public ViewHolder(@org.jetbrains.annotations.NotNull
            android.view.View view) {
                super(null);
            }
            
            @org.jetbrains.annotations.NotNull
            public final android.widget.TextView getName() {
                return null;
            }
        }
    }
}