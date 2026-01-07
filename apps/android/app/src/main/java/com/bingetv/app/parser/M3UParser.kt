package com.bingetv.app.parser

import android.util.Log
import com.bingetv.app.model.Channel
import okhttp3.OkHttpClient
import okhttp3.Request
import java.io.BufferedReader
import java.io.InputStreamReader
import java.util.concurrent.TimeUnit
import java.util.regex.Pattern

class M3UParser {
    private val client = OkHttpClient.Builder()
        .connectTimeout(60, TimeUnit.SECONDS)
        .readTimeout(60, TimeUnit.SECONDS)
        .writeTimeout(60, TimeUnit.SECONDS)
        .followRedirects(true)
        .followSslRedirects(true)
        .build()

    suspend fun parsePlaylist(url: String): List<Channel> {
        val channels = mutableListOf<Channel>()
        
        try {
            Log.d("M3UParser", "Loading playlist from: $url")
            
            val request = Request.Builder()
                .url(url)
                .header("User-Agent", "TiviMate/4.7.0")
                .header("Accept", "*/*")
                .build()
            
            val response = client.newCall(request).execute()
            
            if (!response.isSuccessful) {
                Log.e("M3UParser", "HTTP error: ${response.code} ${response.message}")
                throw Exception("HTTP error: ${response.code} ${response.message}")
            }
            
            val body = response.body ?: throw Exception("Empty response body")
            val contentType = response.header("Content-Type")
            Log.d("M3UParser", "Response content type: $contentType")
            
            val reader = BufferedReader(InputStreamReader(body.byteStream()))
            var line: String?
            var lineCount = 0
            var currentName: String? = null
            var currentUrl: String? = null
            var currentLogo: String? = null
            var currentGroup: String? = null
            var currentTvgId: String? = null
            var currentTvgName: String? = null
            var currentTvgLogo: String? = null
            var currentTvgChno: String? = null
            var currentTvgShift: String? = null
            var isRadio = false
            var catchup: String? = null
            var catchupDays: String? = null
            var catchupSource: String? = null
            
            while (reader.readLine().also { line = it } != null) {
                lineCount++
                val trimmedLine = line?.trim() ?: continue
                
                if (trimmedLine.isEmpty()) continue
                
                if (trimmedLine.startsWith("#EXTM3U")) {
                    Log.d("M3UParser", "Found M3U header")
                    continue
                }
                
                try {
                    if (trimmedLine.startsWith("#EXTINF:")) {
                        // Parse EXTINF line
                        val extinfContent = trimmedLine.substring(8)
                        
                        // Extract attributes
                        val attributes = extractAttributes(extinfContent)
                        
                        // Extract name (after comma)
                        val nameMatch = Pattern.compile(",(.+)$").matcher(extinfContent)
                        if (nameMatch.find()) {
                            currentName = nameMatch.group(1)?.trim()
                        }
                        
                        // Extract attributes
                        currentLogo = attributes["tvg-logo"] ?: attributes["logo"]
                        currentGroup = attributes["group-title"] ?: attributes["group"]
                        currentTvgId = attributes["tvg-id"]
                        currentTvgName = attributes["tvg-name"]
                        currentTvgLogo = attributes["tvg-logo"]
                        currentTvgChno = attributes["tvg-chno"]
                        currentTvgShift = attributes["tvg-shift"]
                        isRadio = attributes.containsKey("radio") || 
                                 (attributes["type"]?.equals("radio", ignoreCase = true) == true)
                        catchup = attributes["catchup"]
                        catchupDays = attributes["catchup-days"]
                        catchupSource = attributes["catchup-source"]
                        
                    } else if (trimmedLine.isNotEmpty() && !trimmedLine.startsWith("#")) {
                        // This is the URL line
                        currentUrl = trimmedLine
                        
                        if (currentName != null && currentUrl != null) {
                            channels.add(
                                Channel(
                                    name = currentName ?: "Unknown",
                                    url = currentUrl!!,
                                    logo = currentLogo ?: currentTvgLogo,
                                    group = currentGroup ?: "Uncategorized",
                                    tvgId = currentTvgId,
                                    tvgName = currentTvgName,
                                    tvgLogo = currentTvgLogo,
                                    tvgChno = currentTvgChno,
                                    tvgShift = currentTvgShift,
                                    radio = isRadio,
                                    catchup = catchup,
                                    catchupDays = catchupDays,
                                    catchupSource = catchupSource
                                )
                            )
                        }
                        
                        // Reset for next channel
                        currentName = null
                        currentUrl = null
                        currentLogo = null
                        currentGroup = null
                        currentTvgId = null
                        currentTvgName = null
                        currentTvgLogo = null
                        currentTvgChno = null
                        currentTvgShift = null
                        isRadio = false
                        catchup = null
                        catchupDays = null
                        catchupSource = null
                    }
                } catch (e: Exception) {
                    // Log error but continue parsing other lines
                    Log.w("M3UParser", "Error parsing line $lineCount: $trimmedLine", e)
                }
            }
            
            reader.close()
            Log.d("M3UParser", "Parsed $lineCount lines, found ${channels.size} channels")
        } catch (e: Exception) {
            Log.e("M3UParser", "Error parsing playlist", e)
            throw e
        }
        
        return channels
    }
    
    private fun extractAttributes(line: String): Map<String, String> {
        val attributes = mutableMapOf<String, String>()
        val matcher = ATTRIBUTE_PATTERN.matcher(line)
        
        while (matcher.find()) {
            val key = matcher.group(1)
            val value = matcher.group(2)
            if (key != null && value != null) {
                attributes[key] = value
            }
        }
        
        return attributes
    }

    companion object {
        private val ATTRIBUTE_PATTERN = Pattern.compile("([a-zA-Z0-9-]+)=\"([^\"]*)\"")
    }
}

