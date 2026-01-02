package com.bingetv.app.model

data class Channel(
    val name: String,
    val url: String,
    val logo: String? = null,
    val group: String? = null,
    val tvgId: String? = null,
    val tvgName: String? = null,
    val tvgLogo: String? = null,
    val tvgChno: String? = null,
    val tvgShift: String? = null,
    val radio: Boolean = false,
    val catchup: String? = null,
    val catchupDays: String? = null,
    val catchupSource: String? = null,
    val streamId: String? = null,
    val epgChannelId: String? = null
)

