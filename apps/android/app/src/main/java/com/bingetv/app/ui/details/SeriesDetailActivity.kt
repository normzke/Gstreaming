package com.bingetv.app.ui.details

import android.content.Intent
import android.os.Bundle
import android.util.Log
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.TextView
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import androidx.lifecycle.lifecycleScope
import androidx.recyclerview.widget.LinearLayoutManager
import androidx.recyclerview.widget.RecyclerView
import com.bingetv.app.PlaybackActivity
import com.bingetv.app.R
import com.bingetv.app.data.api.ApiClient
import com.bingetv.app.data.api.Episode
import com.bingetv.app.data.database.BingeTVDatabase
import com.bingetv.app.data.database.ChannelEntity
import com.bingetv.app.data.repository.PlaylistRepository
import com.bingetv.app.utils.Constants
import com.bingetv.app.utils.ImageLoader
import com.bingetv.app.utils.PreferencesManager
import com.bingetv.app.utils.TextUtils
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext

class SeriesDetailActivity : AppCompatActivity() {

    private lateinit var playlistRepository: PlaylistRepository
    private lateinit var prefsManager: PreferencesManager
    
    // UI Elements
    private lateinit var seasonRecycler: RecyclerView
    private lateinit var episodeRecycler: RecyclerView
    private lateinit var seasonAdapter: SeasonAdapter
    private lateinit var episodeAdapter: EpisodeAdapter
    
    private var seriesId: Int = 0
    private var seriesName: String = ""
    private var seriesPoserUrl: String? = null
    
    // Data
    private var allEpisodes: Map<String, List<Episode>> = emptyMap()
    private var sortedSeasons: List<String> = emptyList()
    private var currentSeason: String = ""
    
    companion object {
        const val EXTRA_SERIES_ID = "extra_series_id"
        const val EXTRA_SERIES_NAME = "extra_series_name"
        const val EXTRA_SERIES_POSTER = "extra_series_poster"
        private const val TAG = "SeriesDetailActivity"
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_series_detail)
        
        seriesId = intent.getIntExtra(EXTRA_SERIES_ID, 0)
        seriesName = intent.getStringExtra(EXTRA_SERIES_NAME) ?: "Unknown Series"
        seriesPoserUrl = intent.getStringExtra(EXTRA_SERIES_POSTER)
        
        if (seriesId == 0) {
            Toast.makeText(this, "Invalid Series ID", Toast.LENGTH_SHORT).show()
            finish()
            return
        }
        
        val dao = BingeTVDatabase.getDatabase(this).playlistDao()
        playlistRepository = PlaylistRepository(dao)
        prefsManager = PreferencesManager(this)
        
        setupUI()
        loadSeriesDetails()
    }
    
    private fun setupUI() {
        findViewById<TextView>(R.id.series_title).text = seriesName
        
        seasonRecycler = findViewById(R.id.seasons_recycler_view)
        episodeRecycler = findViewById(R.id.episodes_recycler_view)
        
        seasonRecycler.layoutManager = LinearLayoutManager(this, LinearLayoutManager.HORIZONTAL, false)
        episodeRecycler.layoutManager = LinearLayoutManager(this)
        
        seasonAdapter = SeasonAdapter { season ->
            selectSeason(season)
        }
        
        episodeAdapter = EpisodeAdapter { episode ->
            playEpisode(episode)
        }
        
        seasonRecycler.adapter = seasonAdapter
        episodeRecycler.adapter = episodeAdapter
        
        if (seriesPoserUrl != null) {
            ImageLoader.loadChannelLogo(this, seriesPoserUrl!!, findViewById(R.id.series_poster))
        }
    }
    
    private fun loadSeriesDetails() {
        lifecycleScope.launch {
            try {
                val playlist = playlistRepository.getActivePlaylist() ?: return@launch
                
                if (playlist.type == Constants.PLAYLIST_TYPE_XTREAM) {
                    withContext(Dispatchers.IO) {
                        val api = ApiClient.getXtreamApi(playlist.serverUrl!!)
                        var response = api.getSeriesInfo(
                            username = playlist.username ?: "",
                            password = playlist.password ?: "",
                            seriesId = seriesId.toString()
                        )
                        
                        var jsonString = if (response.isSuccessful && response.body() != null) response.body()!!.string() else ""
                        Log.d(TAG, "Attempt 1 (series_id=$seriesId) Status: ${response.code()}, Body: ${jsonString.take(500)}")
                        
                        // Fallback 1: Try with 'id' parameter
                        if (jsonString == "[]" || jsonString.isEmpty() || jsonString.contains("\"error\"") || jsonString.contains("API error")) {
                            Log.w(TAG, "trying fallback id=$seriesId because body was: $jsonString")
                            response = api.getSeriesInfoGenericRaw(
                                username = playlist.username ?: "",
                                password = playlist.password ?: "",
                                options = mapOf("id" to seriesId.toString())
                            )
                            if (response.isSuccessful && response.body() != null) {
                                jsonString = response.body()!!.string()
                                Log.d(TAG, "Attempt 2 (id=$seriesId) Status: ${response.code()}, Body: ${jsonString.take(500)}")
                            }
                        }
                        
                        // Fallback 2: Try with 'stream_id' parameter
                        if (jsonString == "[]" || jsonString.isEmpty() || jsonString.contains("\"error\"") || jsonString.contains("API error")) {
                            Log.w(TAG, "trying fallback stream_id=$seriesId because body was: $jsonString")
                            response = api.getSeriesInfoGenericRaw(
                                username = playlist.username ?: "",
                                password = playlist.password ?: "",
                                options = mapOf("stream_id" to seriesId.toString())
                            )
                            if (response.isSuccessful && response.body() != null) {
                                jsonString = response.body()!!.string()
                                Log.d(TAG, "Attempt 3 (stream_id=$seriesId) Status: ${response.code()}, Body: ${jsonString.take(500)}")
                            }
                        }
                        
                        val gson = com.google.gson.Gson()
                        var series: com.bingetv.app.data.api.SeriesInfo? = null
                        var episodesMap: Map<String, List<com.bingetv.app.data.api.Episode>>? = null

                        if (jsonString.isNotEmpty() && jsonString != "[]" && jsonString != "{\"info\":[]}") {
                            Log.e(TAG, "FINAL RAW JSON: $jsonString") 
                            val jsonElement = com.google.gson.JsonParser.parseString(jsonString)
                            
                            // 1. Try to parse as XtreamSeriesInfo (directly or in list)
                            if (jsonElement.isJsonObject) {
                                val obj = jsonElement.asJsonObject
                                if (obj.has("info") && obj.get("info").isJsonObject) {
                                    series = gson.fromJson(obj.get("info"), com.bingetv.app.data.api.SeriesInfo::class.java)
                                }
                                if (obj.has("episodes") && obj.get("episodes").isJsonObject) {
                                    val typeMap = object : com.google.gson.reflect.TypeToken<Map<String, List<com.bingetv.app.data.api.Episode>>>() {}.type
                                    episodesMap = gson.fromJson(obj.get("episodes"), typeMap)
                                }
                            } else if (jsonElement.isJsonArray) {
                                val array = jsonElement.asJsonArray
                                if (array.size() > 0 && array.get(0).isJsonObject) {
                                    val obj = array.get(0).asJsonObject
                                    if (obj.has("info") && obj.get("info").isJsonObject) {
                                        series = gson.fromJson(obj.get("info"), com.bingetv.app.data.api.SeriesInfo::class.java)
                                    }
                                    if (obj.has("episodes") && obj.get("episodes").isJsonObject) {
                                        val typeMap = object : com.google.gson.reflect.TypeToken<Map<String, List<com.bingetv.app.data.api.Episode>>>() {}.type
                                        episodesMap = gson.fromJson(obj.get("episodes"), typeMap)
                                    }
                                }
                            }
                        }

                        if (series != null) {
                            val finalSeries = series
                            withContext(Dispatchers.Main) {
                                findViewById<TextView>(R.id.series_title).text = TextUtils.decodeText(finalSeries.name).ifEmpty { seriesName }
                                findViewById<TextView>(R.id.series_plot).text = TextUtils.decodeText(finalSeries.plot).ifEmpty { "No Plot Available" }
                                findViewById<TextView>(R.id.series_genre).text = TextUtils.decodeText(finalSeries.genre ?: "N/A")
                                findViewById<TextView>(R.id.series_rating).text = "IMDB: ${TextUtils.decodeText(finalSeries.rating ?: "N/A")}"
                                findViewById<TextView>(R.id.series_cast).text = "Cast: ${TextUtils.decodeText(finalSeries.cast ?: "N/A")}"
                                findViewById<TextView>(R.id.series_director).text = "Director: ${TextUtils.decodeText(finalSeries.director ?: "N/A")}"
                                findViewById<TextView>(R.id.series_release_date).text = TextUtils.decodeText(finalSeries.releaseDate ?: "")
                                
                                if (!finalSeries.cover.isNullOrEmpty()) {
                                    ImageLoader.loadChannelLogo(this@SeriesDetailActivity, finalSeries.cover, findViewById(R.id.series_poster))
                                }

                                // Process Episodes
                                if (episodesMap != null) {
                                    allEpisodes = episodesMap
                                    sortedSeasons = allEpisodes.keys.sortedBy { it.filter { c -> c.isDigit() }.toIntOrNull() ?: 999 }
                                    
                                    if (sortedSeasons.isNotEmpty()) {
                                        seasonAdapter.submitList(sortedSeasons)
                                        selectSeason(sortedSeasons[0])
                                        seasonRecycler.requestFocus()
                                    }
                                } else {
                                    Log.w(TAG, "No episodes found for series $seriesId")
                                    Toast.makeText(this@SeriesDetailActivity, "No episodes found", Toast.LENGTH_SHORT).show()
                                }
                            }
                        } else {
                            // FALLBACK: Try VOD Info
                            Log.w(TAG, "Series info empty or malformed after all attempts. Trying VOD fallback for ID $seriesId")
                            val vodResponse = api.getStreamInfoRaw(playlist.username!!, playlist.password!!, vodId = seriesId)
                            
                            if (vodResponse.isSuccessful && vodResponse.body() != null) {
                                val vodJson = vodResponse.body()!!.string()
                                Log.d(TAG, "RAW VOD JSON: $vodJson")
                                val vodElement = com.google.gson.JsonParser.parseString(vodJson)
                                
                                var movieData: com.bingetv.app.data.api.MovieData? = null
                                var containerExt: String? = null
                                
                                if (vodElement.isJsonObject) {
                                    val vObj = vodElement.asJsonObject
                                    if (vObj.has("movie_data") && vObj.get("movie_data").isJsonObject) {
                                        movieData = gson.fromJson(vObj.get("movie_data"), com.bingetv.app.data.api.MovieData::class.java)
                                    }
                                    if (vObj.has("info") && vObj.get("info").isJsonObject) {
                                        val infoObj = vObj.get("info").asJsonObject
                                        if (infoObj.has("container_extension")) {
                                            containerExt = infoObj.get("container_extension").asString
                                        }
                                    }
                                }
                                
                                withContext(Dispatchers.Main) {
                                     if (movieData != null) {
                                         Log.d(TAG, "Found VOD info for Series ID $seriesId")
                                         findViewById<TextView>(R.id.series_title).text = TextUtils.decodeText(movieData.name ?: "").ifEmpty { seriesName }
                                         findViewById<TextView>(R.id.series_plot).text = TextUtils.decodeText(movieData.plot ?: movieData.description).ifEmpty { "No Plot Available" }
                                         findViewById<TextView>(R.id.series_genre).text = "VOD"
                                         findViewById<TextView>(R.id.series_rating).text = "IMDB: ${TextUtils.decodeText(movieData.rating ?: "N/A")}"
                                         findViewById<TextView>(R.id.series_cast).text = "Cast: ${TextUtils.decodeText(movieData.cast ?: "N/A")}"
                                         findViewById<TextView>(R.id.series_director).text = "Director: ${TextUtils.decodeText(movieData.director ?: "N/A")}"
                                         findViewById<TextView>(R.id.series_release_date).text = TextUtils.decodeText(movieData.year ?: "")
                                        
                                        if (!movieData.coverBig.isNullOrEmpty()) {
                                            ImageLoader.loadChannelLogo(this@SeriesDetailActivity, movieData.coverBig!!, findViewById(R.id.series_poster))
                                        }
                                        
                                        // Create fake "Movie" episode
                                        val fakeEpisode = com.bingetv.app.data.api.Episode(
                                            id = seriesId.toString(),
                                            title = "Watch Movie",
                                            extension = containerExt ?: "mp4",
                                            season = 1,
                                            episodeNum = 1
                                        )
                                        
                                        allEpisodes = mapOf("Movie" to listOf(fakeEpisode))
                                        sortedSeasons = listOf("Movie")
                                        seasonAdapter.submitList(sortedSeasons)
                                        selectSeason("Movie")
                                        seasonRecycler.requestFocus()
                                        
                                     } else {
                                         Log.w(TAG, "VOD movieData is null, creating minimal fallback for RAW file")
                                         // Fallback for RAW files: create minimal playable entry
                                         withContext(Dispatchers.Main) {
                                             findViewById<TextView>(R.id.series_title).text = TextUtils.decodeText(seriesName)
                                             findViewById<TextView>(R.id.series_plot).text = "No metadata available for this content. Click to play."
                                             findViewById<TextView>(R.id.series_genre).text = "RAW"
                                             findViewById<TextView>(R.id.series_rating).text = "N/A"
                                             findViewById<TextView>(R.id.series_cast).text = "N/A"
                                             findViewById<TextView>(R.id.series_director).text = "N/A"
                                             findViewById<TextView>(R.id.series_release_date).text = ""
                                             
                                             // Create playable episode even without metadata
                                             val fakeEpisode = com.bingetv.app.data.api.Episode(
                                                 id = seriesId.toString(),
                                                 title = "Play",
                                                 extension = containerExt ?: "mp4",
                                                 season = 1,
                                                 episodeNum = 1
                                             )
                                             
                                             allEpisodes = mapOf("Play" to listOf(fakeEpisode))
                                             sortedSeasons = listOf("Play")
                                             seasonAdapter.submitList(sortedSeasons)
                                             selectSeason("Play")
                                             seasonRecycler.requestFocus()
                                             
                                             Log.d(TAG, "Created minimal fallback entry for RAW file ID $seriesId")
                                         }
                                     }
                                }
                            } else {
                                Log.e(TAG, "VOD fallback request failed")
                                withContext(Dispatchers.Main) {
                                    Toast.makeText(this@SeriesDetailActivity, "Error loading details (VOD fail)", Toast.LENGTH_SHORT).show()
                                }
                            }
                        }
                    }
                }
            } catch (e: Exception) {
                Log.e(TAG, "Error loading series details", e)
                Toast.makeText(this@SeriesDetailActivity, "Error loading details", Toast.LENGTH_SHORT).show()
            }
        }
    }
    
    private fun selectSeason(season: String) {
        currentSeason = season
        seasonAdapter.setSelected(season)
        val episodes = allEpisodes[season] ?: emptyList()
        episodeAdapter.submitList(episodes)
    }
    
    private fun playEpisode(episode: Episode) {
        lifecycleScope.launch {
             val playlist = playlistRepository.getActivePlaylist() ?: return@launch
             
             // Construct URL - try standard format first
             // Format 1: http://server/series/user/pass/id.ext (most common)
             // Format 2: http://server/player_api.php?username=X&password=Y&action=get_series&series_id=Z (fallback)
             // PlaybackActivity will handle 405 errors and rotate User-Agents automatically
             val extension = episode.extension ?: "mkv"
             val url = "${playlist.serverUrl}/series/${playlist.username}/${playlist.password}/${episode.id}.$extension"
             
             val intent = Intent(this@SeriesDetailActivity, PlaybackActivity::class.java).apply {
                putExtra(Constants.EXTRA_CHANNEL_NAME, "${seriesName} - S${currentSeason}E${episode.episodeNum}")
                putExtra(Constants.EXTRA_CHANNEL_URL, url)
                // Use series cover as logo
                putExtra(Constants.EXTRA_CHANNEL_LOGO, seriesPoserUrl)
            }
            startActivity(intent)
        }
    }
    
    // --- Adapters ---
    
    inner class SeasonAdapter(private val onClick: (String) -> Unit) : RecyclerView.Adapter<SeasonAdapter.ViewHolder>() {
        private var items = listOf<String>()
        private var selectedSeason = ""
        
        fun submitList(list: List<String>) {
            items = list
            notifyDataSetChanged()
        }
        
        fun setSelected(season: String) {
            val old = items.indexOf(selectedSeason)
            selectedSeason = season
            val new = items.indexOf(selectedSeason)
            if (old != -1) notifyItemChanged(old)
            if (new != -1) notifyItemChanged(new)
        }
        
        inner class ViewHolder(view: View) : RecyclerView.ViewHolder(view) {
            val name: TextView = view.findViewById(R.id.season_name)
        }

        override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): ViewHolder {
            val view = LayoutInflater.from(parent.context).inflate(R.layout.item_season, parent, false)
            return ViewHolder(view)
        }

        override fun onBindViewHolder(holder: ViewHolder, position: Int) {
            val season = items[position]
            holder.name.text = "Season $season"
            holder.itemView.isSelected = (season == selectedSeason)
            holder.itemView.setOnClickListener { onClick(season) }
            
            // Focus visual update
            holder.itemView.setOnFocusChangeListener { _, hasFocus ->
               holder.itemView.scaleX = if (hasFocus) 1.1f else 1.0f
               holder.itemView.scaleY = if (hasFocus) 1.1f else 1.0f
            }
        }
        
        override fun getItemCount() = items.size
    }

    inner class EpisodeAdapter(private val onClick: (Episode) -> Unit) : RecyclerView.Adapter<EpisodeAdapter.ViewHolder>() {
        private var items = listOf<Episode>()
        
        fun submitList(list: List<Episode>) {
            items = list
            notifyDataSetChanged()
        }
        
        inner class ViewHolder(view: View) : RecyclerView.ViewHolder(view) {
            val num: TextView = view.findViewById(R.id.episode_number)
            val title: TextView = view.findViewById(R.id.episode_title)
        }

        override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): ViewHolder {
            val view = LayoutInflater.from(parent.context).inflate(R.layout.item_episode, parent, false)
            return ViewHolder(view)
        }

        override fun onBindViewHolder(holder: ViewHolder, position: Int) {
            val episode = items[position]
            holder.num.text = episode.episodeNum?.toString() ?: (position + 1).toString()
            holder.title.text = TextUtils.decodeText(episode.title)
            holder.itemView.setOnClickListener { onClick(episode) }
            
            holder.itemView.setOnFocusChangeListener { v, hasFocus ->
                v.setBackgroundResource(if (hasFocus) R.drawable.bg_card_focused else R.drawable.selector_item_background)
            }
        }

        override fun getItemCount() = items.size
    }
    
}
