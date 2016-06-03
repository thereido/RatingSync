
var refreshStreamsInMins = 1;

chrome.runtime.onMessage.addListener(onMessage);
chrome.tabs.onUpdated.addListener(onUpdated);

function onMessage(request, sender) {
    if (request.action == "forwardToParent") {
        if (request.parentTabId != "") {
            chrome.tabs.sendMessage(request.parentTabId, request);
        }
        if (request.subject == "streamInfoReady") {
            onStreamInfoReady(request.streamResponse);
        }
    }
    else if (request.action == "setSearchTerms") {
        //requestAddFilm(request.search);
    }
    else if (request.action == "createProviderTab") {
        senderTabId = "";
        if (sender.tab) {
            senderTabId = sender.tab.id;
        }
        var onCreateProviderHandler = function (tab) { onCreateProvider(tab, request.streamInfo, senderTabId); };
        chrome.tabs.create({url: request.url, active: false}, onCreateProviderHandler);
    }
}

function onUpdated(tabId, changeInfo, tab) {
    if (changeInfo.status && tab.url && changeInfo.status == "complete") {
        var url = tab.url;
        var source = "";
        if (-1 < url.indexOf("imdb") && -1 < url.indexOf("/tt")) {
            source = "IM";
        } else if (-1 < url.indexOf("netflix") && (-1 < url.indexOf("/title/") || -1 < url.indexOf("jbv="))) {
            source = "NF";
        } else if (-1 < url.indexOf("rottentomatoes") && (-1 < url.indexOf("/m/") || -1 < url.indexOf("/tv/"))) {
            source = "RT";
        } else if (-1 < url.indexOf("xfinitytv") && -1 < url.indexOf("/watch/")) {
            source = "XF";
        } else if (-1 < url.indexOf("hulu") && -1 < url.indexOf("/watch/")) {
            source = "H";
        } else if (-1 < tab.url.indexOf("//localhost") && -1 < tab.url.indexOf("userlist")) {
            chrome.tabs.executeScript(tab.id, {file: "showStreams.js"}, function () { chrome.tabs.sendMessage(tab.id, { action: "showStreams" }); } );
        }

        if (0 < source.length) {
            //chrome.tabs.executeScript(tab.id, {file: "getSearchTerms.js"});
        }
    }
}

function requestAddFilm(searchTerms) {
    if (searchTerms &&
        searchTerms.uniqueName != "undefined" &&
        searchTerms.title != "undefined" &&
        searchTerms.year != "undefined")
    {
        var xmlhttp = new XMLHttpRequest();
        var params = "&json=1";
        if (searchTerms.uniqueName != "undefined") { params = params + "&q=" + searchTerms.uniqueName; }
        if (searchTerms.uniqueEpisode != "undefined") { params = params + "&ue=" + searchTerms.uniqueEpisode; }
        if (searchTerms.uniqueAlt != "undefined") { params = params + "&ua=" + searchTerms.uniqueAlt; }
        if (searchTerms.source != "undefined") { params = params + "&source=" + searchTerms.source; }
        if (searchTerms.title != "undefined") { params = params + "&t=" + encodeURIComponent(searchTerms.title); }
        if (searchTerms.year != "undefined") { params = params + "&y=" + searchTerms.year; }
        if (searchTerms.contentType != "undefined") { params = params + "&ct=" + searchTerms.contentType; }
	    xmlhttp.open("GET", RS_URL_API + "?action=addFilmBySearch" + params, true);
	    xmlhttp.send();
    }
}

function onCreateProvider(tab, streamInfo, parentTabId) {
    chrome.tabs.executeScript(tab.id, {file: "getStream.js"}, function () { chrome.tabs.sendMessage(tab.id, { action: "initTab", parentTabId: parentTabId, streamInfo: streamInfo }); } );
}

function onStreamInfoReady(streamResponse) {
    if (!streamResponse || !streamResponse.streamInfo) {
        return;
    }
    
    var streamInfo = streamResponse.streamInfo;
    if (!streamResponse.streamInfo.filmId || !streamResponse.streamInfo.sourceName) {
        return;
    }

    var streamUrl = streamResponse.streamUrl;
    if (!streamUrl || streamUrl == "undefined") {
        streamUrl = "none";
    }

    var params = "&filmid=" + streamInfo.filmId;
    params = params + "&source=" + streamInfo.sourceName;
    if (streamUrl) { params = params + "&su=" + streamUrl; }
    if (streamInfo.uniqueName) { params = params + "&un=" + streamInfo.uniqueName; }
    if (streamInfo.uniqueEpisode) { params = params + "&ue=" + streamInfo.uniqueEpisode; }
    if (streamInfo.uniqueAlt) { params = params + "&ua=" + streamInfo.uniqueAlt; }
    
    if (streamResponse.unReachable == "false") {
	    var xmlhttp = new XMLHttpRequest();
	    xmlhttp.open("GET", RS_URL_API + "?action=updateFilmSource" + params, true);
	    xmlhttp.send();
    }
}