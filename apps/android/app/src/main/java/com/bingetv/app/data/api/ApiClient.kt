package com.bingetv.app.data.api

import okhttp3.OkHttpClient
import okhttp3.logging.HttpLoggingInterceptor
import retrofit2.Retrofit
import retrofit2.converter.gson.GsonConverterFactory
import java.util.concurrent.TimeUnit

object ApiClient {
    
    private var retrofit: Retrofit? = null
    private var xtreamApi: XtreamCodesApi? = null
    
    private fun getOkHttpClient(): OkHttpClient {
        val logging = HttpLoggingInterceptor().apply {
            // Prevent OOM with large responses (playlists/series) by avoiding BODY logging
            level = HttpLoggingInterceptor.Level.HEADERS 
        }
        
        return OkHttpClient.Builder()
            .addInterceptor(logging)
            .connectTimeout(30, TimeUnit.SECONDS)
            .readTimeout(30, TimeUnit.SECONDS)
            .writeTimeout(30, TimeUnit.SECONDS)
            .followRedirects(true)
            .followSslRedirects(true)
            .build()
    }
    
    fun getXtreamApi(baseUrl: String): XtreamCodesApi {
        if (retrofit == null || retrofit?.baseUrl().toString() != baseUrl) {
            retrofit = Retrofit.Builder()
                .baseUrl(baseUrl)
                .client(getOkHttpClient())
                .addConverterFactory(GsonConverterFactory.create())
                .build()
            
            xtreamApi = retrofit?.create(XtreamCodesApi::class.java)
        }
        
        return xtreamApi!!
    }
    
    fun reset() {
        retrofit = null
        xtreamApi = null
    }
}
