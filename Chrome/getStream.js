
chrome.runtime.onMessage.addListener(onMessage);

function onMessage(request, sender) {
    if (request.action == "initTab") {
        sendStreamResponse(request.parentTabId, getStream(request.streamInfo));
        window.close();
    }
}

function getStream(streamInfo) {
    var streamUrl;

    var url = window.location.href;
    if (-1 < url.indexOf("netflix")) {
        if (-1 < url.indexOf("/title/" + streamInfo.uniqueName)) {
            streamUrl = url;
        } else if (-1 < url.indexOf("/search/")) {
            // A dvdStreamingTitle element means the title is not available
            if (document.getElementsByClassName("dvdStreamingTitle").length == 0) {
                // Match title (but not year)
                var reUniqueNameByTitle = new RegExp('"title":{"value":"' + streamInfo.title + '".*?"id":([0-9]+)');
                var html = document.getElementsByTagName("body")[0].innerHTML;
                if (reUniqueNameByTitle.test(html)) {
                    streamInfo.uniqueName = reUniqueNameByTitle.exec(html)[1];
                    streamUrl = "https://www.netflix.com/title/" + streamInfo.uniqueName;
                }
            }
        }
    } else if (-1 < url.indexOf("xfinitytv")) {
        if (-1 < url.indexOf("/watch/" + streamInfo.uniqueAlt)) {
/*
"id" : "7182391626371629112",
"avenues" : {"In Theaters": "", "On TV": "", "On Demand": "false"},
"isOnline" : false,
*/
            // Instead of getting tab for xfinity... go to an api call to RatingSync server.
            // It can get the stream. Netflix was only because curl can't do it.
            var re = new RegExp('<tr id="' + streamInfo.uniqueName + '" class="online active">');
            if (available) {
                streamUrl = url;
            }
        }
    }

    var text = '{"streamInfo": ' + JSON.stringify(streamInfo) + ',"streamUrl": "' + streamUrl + '"}';
    var json = JSON.parse(text);
    return json;
}

function sendStreamResponse(parentTabId, streamResponse) {
    chrome.runtime.sendMessage( { action: "forwardToParent", subject: "streamInfoReady", streamResponse: streamResponse, parentTabId: parentTabId } );
}