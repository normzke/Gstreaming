package com.bingetv.app.parser

import com.bingetv.app.model.Channel
import com.google.gson.annotations.SerializedName

import com.bingetv.app.data.api.XtreamChannel

class XtreamCodesParser {
    fun parseChannels(response: List<XtreamChannel>, serverUrl: String, username: String, password: String): List<Channel> {
        return response.map { xtreamChannel ->
            Channel(
                name = xtreamChannel.name,
                url = "$serverUrl/live/$username/$password/${xtreamChannel.streamId}.ts",
                logo = xtreamChannel.streamIcon ?: "",
                group = xtreamChannel.categoryId ?: "Uncategorized",
                streamId = xtreamChannel.streamId.toString(),
                epgChannelId = xtreamChannel.epgChannelId
            )
        }
    }
}
