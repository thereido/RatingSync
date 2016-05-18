
chrome.runtime.onMessage.addListener(onMessage);

function onMessage(request, sender) {
    if (request.action == "initTab") {
        sendStreamResponse(request.parentTabId, getStream(request.streamInfo));
        window.close();
    }
}

function getStream(streamInfo) {
    var streamUrl;
    var unReachable = "false";

    var url = window.location.href;
    if (-1 < url.indexOf("netflix")) {
        unReachable = "true";
        if (-1 < url.indexOf("/Login?")) {
            unReachable = "true";
        } else if (-1 < url.indexOf("/title/" + streamInfo.uniqueName)) {
            unReachable = "false";
            streamUrl = url;
        } else if (-1 < url.indexOf("/search/")) {
            unReachable = "false";
            // A dvdStreamingTitle element means the title is not available
            if (document.getElementsByClassName("dvdStreamingTitle").length == 0) {
                // Match title (but not year)
                var reUniqueNameByTitle = new RegExp('"title":{"value":"' + decodeURI(streamInfo.title) + '".*?"id":([0-9]+)', "i");
                var html = document.getElementsByTagName("body")[0].innerHTML;
                if (reUniqueNameByTitle.test(html)) {
                    streamInfo.uniqueName = reUniqueNameByTitle.exec(html)[1];
                    streamUrl = "https://www.netflix.com/title/" + streamInfo.uniqueName;
                }
            }
        }
    }
    
    var text = '{"streamInfo": ' + JSON.stringify(streamInfo) + ',"streamUrl": "' + streamUrl + '","unReachable": "' + unReachable + '"}';
    var json = JSON.parse(text);
    return json;
}

function sendStreamResponse(parentTabId, streamResponse) {
    chrome.runtime.sendMessage( { action: "forwardToParent", subject: "streamInfoReady", streamResponse: streamResponse, parentTabId: parentTabId } );
}