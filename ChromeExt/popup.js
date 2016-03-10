
document.addEventListener('DOMContentLoaded', function () {
    getCurrentTabUrl(startPopup);
});

function getCurrentTabUrl(callback) {
  var queryInfo = {
    active: true,
    currentWindow: true
  };

  chrome.tabs.query(queryInfo, function(tabs) {
    var tab = tabs[0];
    var url = tab.url;
    console.assert(typeof url == 'string', 'tab.url should be a string');

    callback(url);
  });
}

function startPopup(url) {
    var uniqueName;
    var source;
	if (url && -1 < url.indexOf("imdb")) {
        var source = "IM";
		var index = url.indexOf("/tt");
        if (-1 < index) {
            var indexBegin = index + 1;
            var indexEnd = url.indexOf("/", indexBegin);
            uniqueName = url.substring(indexBegin, indexEnd);
        } else {
            notFound("IMDb");
        }
	}
    else if (url && -1 < url.indexOf("netflix")) {
        var source = "NF";
        var indexBegin = -1;
        var indexEnd;
		var index = url.indexOf("/title/");
        if (-1 < index) {
            indexBegin = index + 7;
            indexEnd = url.indexOf("?", indexBegin);
        } else {
            index = url.indexOf("jbv=");
            if (-1 < index) {
                indexBegin = index + 4;
                indexEnd = url.indexOf("&", indexBegin);
            }
        }

        if (indexBegin != -1) {
		    uniqueName = url.substring(indexBegin, indexEnd);
        } else {
            notFound("Netflix");
        }
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
        } else {
            notFound("RottenTomatoes");
        }
	}

    if (uniqueName) {
        searchFilm(uniqueName, source);
    } else if (!source) {
        var msg = "<div>Here are the sites currently supported<ul><li>IMDb</li><li>Rotten Tomatoes</li><li>Netflix</li></ul></div>";
        document.getElementById("searchResult").innerHTML = msg;
    }
}

function renderStatus(statusText) {
  document.getElementById('status').textContent = statusText;
}

function notFound(source)
{
    var msg = "<div>Unable to find title on this " + source + " page</div>";
    document.getElementById("searchResult").innerHTML = msg;
}

function searchFilm(searchTerm, source)
{
    renderStatus('Searching...');
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange = function () {
	    if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            var searchResultElement = document.getElementById("searchResult");
	        searchResultElement.innerHTML = xmlhttp.responseText;
            addStarListeners(searchResultElement);
    		renderStatus('');
	    } else if (xmlhttp.readyState == 4) {
	        renderStatus('Not found ' + searchTerm + ' at ' + source);
	    }
	}
	xmlhttp.open("GET", "http://192.168.1.105:55887/php/src/ajax/getSearchFilm.php?q=" + searchTerm + "&source=" + source + "&i=0", true);
	xmlhttp.send();
}

function addStarListeners(el) {
    var stars = el.getElementsByClassName("rating-star");
    for (i = 0; i < stars.length; i++) {
        addStarListener(stars[i].getAttribute("id"));
    }
}

function addStarListener(elementId) {    
	var star = document.getElementById(elementId);
	if (star != null) {
		var uniqueName = star.getAttribute('data-uniquename');
		var score = star.getAttribute('data-score');
		var titleNum = star.getAttribute('data-title-num');
		var withImage = star.getAttribute('data-image');
		star.addEventListener('click', function(){rateFilm(uniqueName, score, titleNum, withImage);});
	}
}

function rateFilm(uniqueName, score) {
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            var searchResultElement = document.getElementById("searchResult");
	        searchResultElement.innerHTML = xmlhttp.responseText;
            addStarListeners(searchResultElement);
    		renderStatus('Rating Saved');
        }else {
            /*RT*/// document.getElementById(uniqueName).innerHTML = xmlhttp.responseText;
        }
    }
    xmlhttp.open("GET", "http://192.168.1.105:55887/php/src/ajax/setRating.php?un=" + uniqueName + "&s=" + score + "&i=0", true);
    xmlhttp.send();
    renderStatus('Saving...');
}

