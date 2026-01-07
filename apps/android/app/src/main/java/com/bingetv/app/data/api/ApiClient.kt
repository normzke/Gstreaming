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
            // Use HEADERS only to prevent OOM with large responses (playlists/EPG can be 10MB+)
            level = HttpLoggingInterceptor.Level.HEADERS 
        }
        
        return OkHttpClient.Builder()
            .addInterceptor(logging)
            .addInterceptor { chain ->
                val request = chain.request().newBuilder()
                    .header("User-Agent", "IPTVSmarters")
                    .build()
                chain.proceed(request)
            }
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

    fun getUpdateApi(baseUrl: String): UpdateApi {
        val client = Retrofit.Builder()
            .baseUrl(baseUrl)
            .client(getOkHttpClient())
            .addConverterFactory(GsonConverterFactory.create())
            .build()
            
        return client.create(UpdateApi::class.java)
    }
    
    fun reset() {
        retrofit = null
        xtreamApi = null
    }
}
