
function getSearchTerms(document_root) {

    var searchTerms;
    var uniqueName;
    var source;
    var title;
    var year;

    var url = window.location.href;
	if (url && -1 < url.indexOf("imdb")) {
        var source = "IM";
		var index = url.indexOf("/tt");
        if (-1 < index) {
            var indexBegin = index + 1;
            var indexEnd = url.indexOf("/", indexBegin);
            uniqueName = url.substring(indexBegin, indexEnd);
        }
	}
    else if (url && -1 < url.indexOf("netflix")) {
        var source = "NF";
        var indexBegin = -1;
        var indexEnd;
        var index = url.indexOf("jbv=");
        if (-1 < index) {
            indexBegin = index + 4;
            indexEnd = url.indexOf("&", indexBegin);
        } else {
            index = url.indexOf("/title/");
            if (-1 < index) {
                indexBegin = index + 7;
                indexEnd = url.indexOf("?", indexBegin);
            }
        }

        if (indexBegin != -1) {
            if (indexBegin < indexEnd) {
                uniqueName = url.substring(indexBegin, indexEnd);
            } else {
                uniqueName = url.substring(indexBegin);
            }
        }

        year = document_root.getElementsByClassName("year")[0].innerHTML;
        title = document_root.getElementsByClassName("title has-jawbone-nav-transition")[0].innerHTML;
	}
    else if (url && -1 < url.indexOf("rottentomatoes")) {
        var source = "RT";
        var indexBegin = -1;
        var indexEnd;
		var index = url.indexOf("/m/");
        if (-1 < index) {
            indexBegin = index + 3;
            indexEnd = url.indexOf("/", indexBegin);
        } else {
            index = url.indexOf("/tv/");
            if (-1 < index) {
                indexBegin = index + 4;
                indexEnd = url.indexOf("/", indexBegin);
            }
        }

        if (indexBegin != -1) {
		    uniqueName = url.substring(indexBegin, indexEnd);
        }
	}

    var text = '{"uniqueName": "' + uniqueName + '", "source": "' + source + '", "title": "' + title +  '", "year": "' + year + '"}';
    var json = JSON.parse(text);
    return json;
}

chrome.runtime.sendMessage({
    action: "setSearchTerms",
    search: getSearchTerms(document)
});