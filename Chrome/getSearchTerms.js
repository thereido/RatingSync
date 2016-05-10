
var url = window.location.href;
var sourceName = getSourceName(url);

if (!sourceName) {
    // Do nothing
}
else if (sourceName == "RS") {
    // Do nothing
}
else {
    var searchTerms = getSearchTerms(sourceName, document);
    chrome.runtime.sendMessage({
        action: "setSearchTerms",
        search: searchTerms
    });
}

function getSourceName(url) {
    if (!url) {
        return;
    } else if (-1 < url.indexOf("192.168")) {
        return "RS";
    } else if (-1 < url.indexOf("imdb")) {
        return "IM";
    } else if (-1 < url.indexOf("netflix")) {
        return "NF";
    } else if (-1 < url.indexOf("rottentomatoes")) {
        return "RT";
    } else if (-1 < url.indexOf("xfinitytv")) {
        return "XF";
    } else if (-1 < url.indexOf("hulu")) {
        return "H";
    }
}

function getSearchTerms(source, document_root) {

    var searchTerms;
    var uniqueName;
    var uniqueEpisode;
    var uniqueAlt;
    var title;
    var year;
    var contentType;

    var url = window.location.href;
	if (source == "IM") {
		var index = url.indexOf("/tt");
        if (-1 < index) {
            var indexBegin = index + 1;
            var indexEnd = url.indexOf("/", indexBegin);
            uniqueName = url.substring(indexBegin, indexEnd);
        }
	}
    else if (source == "NF") {        
        var regex = new RegExp("jbv=([0-9]+)");
        if (regex.test(url)) {
            uniqueName = regex.exec(url)[1];
        } else {
            regex = new RegExp("/title/([0-9]+)");
            if (regex.test(url)) {
                uniqueName = regex.exec(url)[1];
                titleEl = document_root.getElementsByClassName("title has-jawbone-nav-transition")[0];
                yearEl = document_root.getElementsByClassName("year")[0];
                if (titleEl) {
                    titleHtml = titleEl.innerHTML;
                    regex = new RegExp("<img alt=\"([^\"]+)");
                    if (regex.test(titleHtml)) {
                        title = regex.exec(titleHtml)[1];
                    } else {
                        title = titleHtml;
                    }
                }
                if (yearEl) {
                    year = yearEl.innerHTML;
                }
            }
        }
	}
    else if (source == "RT") {
        var titleElement;
        var titleRegex;
        var yearRegex;
        var indexBegin = -1;
        var indexEnd;
		var index = url.indexOf("/m/");
        if (-1 < index) {
            indexBegin = index + 3;
            indexEnd = url.indexOf("/", indexBegin);
            titleElement = document_root.getElementById("movie-title");
            titleRegex = new RegExp("[ ]?([^<]+)<span");
        } else {
            index = url.indexOf("/tv/");
            if (-1 < index) {
                indexBegin = index + 4;
                indexEnd = url.indexOf("/", indexBegin);
                titleElement = document_root.getElementById("super_series_header");
                titleRegex = new RegExp("<h1>[ ]?([^<]+)<span");
            }
        }

        if (indexBegin != -1) {
		    uniqueName = url.substring(indexBegin, indexEnd);
        }

        var html = "";
        if (titleElement) {
            html = titleElement.innerHTML;
        }
        if (titleRegex || titleRegex.test(html)) {
            title = titleRegex.exec(html)[1];
        }
        yearRegex = new RegExp(">..([0-9][0-9][0-9][0-9])");
        if (yearRegex || yearRegex.test(html)) {
            year = yearRegex.exec(html)[1];
        }
	}
    else if (source == "XF") {
        var indexBegin = -1;
        var indexEnd;
		var index = url.indexOf("/watch/");
        if (-1 < index) {
            indexBegin = index + 7;
            indexEnd = url.indexOf("/", indexBegin);
            if (indexBegin != -1) {
                uniqueAlt = url.substring(indexBegin, indexEnd);

                indexBegin = url.indexOf("/", indexEnd) + 1;
                indexEnd = url.indexOf("/", indexBegin);
                if (indexBegin != -1) {
                    uniqueName = url.substring(indexBegin, indexEnd);
                }
            }
        }
        index = url.indexOf("episode=");
        if (-1 < index) {
            indexBegin = index + 8;
            uniqueEpisode = url.substring(indexBegin);
        }

        var html = document_root.getElementsByClassName("entity-info")[0].innerHTML;
        var reTitle = new RegExp("<meta property=\"og:title\" content=\"(.+)\"\/>");
        if (reTitle.test(html)) {
            title = reTitle.exec(html)[1];
        }
        var reYear = new RegExp("<span itemprop=\"startDate\"[^>]*>([^<]*)<");
        if (reYear.test(html)) {
            year = reYear.exec(html)[1];
        }
	}
    else if (source == "H") {
        var indexBegin = -1;
        var indexEnd;
		var index = url.indexOf("/watch/");
        if (-1 < index) {
            indexBegin = index + 7;
            indexEnd = url.indexOf("/", indexBegin);
        }

        if (indexBegin != -1) {
            if (indexBegin < indexEnd) {
                uniqueName = url.substring(indexBegin, indexEnd);
            } else {
                uniqueName = url.substring(indexBegin);
            }
        }

        var html = document_root.getElementsByClassName("episode-title")[0].innerHTML;
        var reTitle = new RegExp("(.*) .[0-9]{4}.");
        if (reTitle.test(html)) {
            title = reTitle.exec(html)[1];
        }
        var reYear = new RegExp("([0-9]{4}).$");
        if (reYear.test(html)) {
            year = reYear.exec(html)[1];
        }
	}
    
    var text = '';
    text = text + '{"uniqueName": "' + uniqueName + '", ';
    text = text + '"uniqueEpisode": "' + uniqueEpisode + '", ';
    text = text + '"uniqueAlt": "' + uniqueAlt + '", ';
    text = text + '"source": "' + source + '", ';
    text = text + '"title": "' + title + '", ';
    text = text + '"year": "' + year + '", ';
    text = text + '"contentType": "' + contentType + '"}';
    var json = JSON.parse(text);
    return json;
}