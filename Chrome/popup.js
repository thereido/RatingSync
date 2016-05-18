
var RS_URL_BASE = "http://localhost:55887";
var RS_URL_API = RS_URL_BASE + "/php/src/ajax/api.php";
var IMDB_FILM_BASEURL = "http://www.imdb.com/title/";

document.addEventListener('DOMContentLoaded', function () {
    chrome.tabs.executeScript(null, {file: "getSearchTerms.js"}, function() { });
});

chrome.runtime.onMessage.addListener(function (request, sender) {
    if (request.action == "setSearchTerms") {
        searchFilm(request.search);
    } else if (request.action == "unsupportedUrl") {
        notSupported(null);
    }
});

function renderStatus(statusText) {
  document.getElementById('status').textContent = statusText;
}

function notFound(source)
{
    var msg = "<div align='center'>Unable to figure out which title you want to search</div>";
    document.getElementById("searchResult").innerHTML = msg;
}

function notSupported(source)
{
    var msg = "<div>Here are the sites currently supported<ul><li>IMDb</li><li>Netflix</li><li>xfinity</li><li>Rotten Tomatoes</li></ul></div>";
    document.getElementById("searchResult").innerHTML = msg;
}

function searchFilm(searchTerms)
{
    if (searchTerms.uniqueName == "undefined" && (searchTerms.title == "undefined" || !searchTerms.year == "undefined")) {
        notFound(searchTerms.source);
        return;
    }

    renderStatus('Searching...');
    var msg = "<div align='center'>";
    if (searchTerms.title != "undefined") {
        msg = msg + searchTerms.title + " (" + searchTerms.year + ")";
    } else {
        msg = msg + "IMDb id: " + searchTerms.uniqueName;
    }
    var msg = msg + "</div>";
    var searchResultElement = document.getElementById("searchResult");
    searchResultElement.innerHTML = msg;

	var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange = function () {
	    if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
	        searchResultElement.innerHTML = renderFilm(xmlhttp.responseText);
	        addStarListeners(searchResultElement);
	        renderStatus('');
	        showStreams();
	    } else if (xmlhttp.readyState == 4) {
	        renderStatus('Not found');
	    }
	}

    var params = "&json=1";
    if (searchTerms.uniqueName != "undefined") { params = params + "&q=" + searchTerms.uniqueName; }
    if (searchTerms.uniqueEpisode != "undefined") { params = params + "&ue=" + searchTerms.uniqueEpisode; }
    if (searchTerms.uniqueAlt != "undefined") { params = params + "&ua=" + searchTerms.uniqueAlt; }
    if (searchTerms.source != "undefined") { params = params + "&source=" + searchTerms.source; }
    if (searchTerms.title != "undefined") { params = params + "&t=" + encodeURIComponent(searchTerms.title); }
    if (searchTerms.year != "undefined") { params = params + "&y=" + searchTerms.year; }
    if (searchTerms.contentType != "undefined") { params = params + "&ct=" + searchTerms.contentType; }
	xmlhttp.open("GET", RS_URL_API + "?action=getSearchFilm" + params, true);
	xmlhttp.send();
}

function rateFilm(uniqueName, score, titleNum) {
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            var searchResultElement = document.getElementById("searchResult");
	        searchResultElement.innerHTML = renderFilm(xmlhttp.responseText);
            addStarListeners(searchResultElement);
    		renderStatus('Rating Saved');
            showStreams();
        }
    }
    xmlhttp.open("GET", RS_URL_API + "?action=setRating&json=1&un=" + uniqueName + "&s=" + score, true);
    xmlhttp.send();
    renderStatus('Saving...');
}

function renderYourScore(uniqueName, hoverScore, mousemove) {
    var score = hoverScore;
    if (mousemove == "original") {
        score = document.getElementById("original-score-" + uniqueName).getAttribute("data-score");
    }

    if (score == "10") {
        score = "01";
    }
    document.getElementById("your-score-" + uniqueName).innerHTML = score;
}

function renderFilm(json) {
    var r = "";
    var film = JSON.parse(json);
    var filmId = film.filmId;
    var title = film.title;
    var year = film.year;
    var image = RS_URL_BASE + film.image;
    
    var rs = film.sources.find( function (findSource) { return findSource.name == "RatingSync"; } );
    var uniqueName = rs.uniqueName;
    var yourScore = rs.rating.yourScore;
    var showYourScore = yourScore;
    if (showYourScore == "10") {
        showYourScore = "01";
    } else if (showYourScore == null || showYourScore == "") {
        showYourScore = "-";
    }

    var imdb = film.sources.find( function (findSource) { return findSource.name == "IMDb"; } );
    var imdbFilmUrl = IMDB_FILM_BASEURL + imdb.uniqueName;
    var imdbLabel = "IMDb";
    var imdbScore = imdb.userScore;

    var starsHtml = renderStars(rs);
    r = r + "<div id='" + uniqueName + "' align='center'>\n";
    r = r + "  <div class='film-line'><span class='film-title'>" + title + "</span> (" + year + ")</div>\n";
    r = r + "  <div class='rating-stars'>\n";
    r = r + "    <score>\n";
    r = r + "      <of-possible>01/</of-possible><your-score id='your-score-" + uniqueName + "'>" + showYourScore + "</your-score>\n";
    r = r + "    </score>\n";
    r = r + "    " + starsHtml + "\n";
    r = r + "    <div id='original-score-" + uniqueName + "' data-score='" + showYourScore + "' hidden ></div>\n";
    r = r + "  </div>\n";
    r = r + "  <poster><img src='" + image + "' width='150px'/></poster>\n";
    r = r + "  <detail>\n";
    r = r + "    <div align='left'><a href='" + imdbFilmUrl + "' target='_blank'>" + imdbLabel + ":</a> " + imdbScore + "</div>\n";
    r = r + "    <div id='streams' class='streams'>\n";
    r = r + "      " + renderStreams(film) + "\n";
    r = r + "    </div>\n";
    r = r + "    <div id='filmlist-container' align='left'></div>\n";
    r = r + "  </detail>\n";
    r = r + "</div>\n";

    getFilmlists(film.filmlists, filmId);

    return r;
}

function renderStars(rs) {
    var uniqueName = rs.uniqueName;
    var yourScore = rs.rating.yourScore;
    var fullStars = yourScore;
    var emptyStars = 10 - yourScore;
    var starScore = 10;
    
    var starsHtml = "";
    while (emptyStars > 0) {
        starsHtml = starsHtml + "<span class='rating-star' id='rate-" + uniqueName + "-" + starScore + "' data-uniquename='" + uniqueName + "' data-score='" + starScore + "'>☆</span>";
        emptyStars = emptyStars - 1;
        starScore = starScore - 1;
    }
    while (fullStars > 0) {
        starsHtml = starsHtml + "<span class='rating-star' id='rate-" + uniqueName + "-" + starScore + "' data-uniquename='" + uniqueName + "' data-score='" + starScore + "'>★</span>";
        fullStars = fullStars - 1;
        starScore = starScore - 1;
    }

    return starsHtml;
}

function getFilmlists(listnames, filmId) {
    // Get all of the user's filmlists
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            var userlists = xmlhttp.responseText;
            renderFilmlists(userlists, listnames, filmId);
        }
    }
    xmlhttp.open("GET", RS_URL_API + "?action=getUserLists", true);
    xmlhttp.send();
}

// userlist (JSON) - all of the user's filmlists
// listnames - lists this film belongs in
function renderFilmlists(userlistsJson, includedListnames, filmId) {
    var defaultList = "Watchlist";
    var defaultListClass = "checkmark-off";
    var userlists = JSON.parse(userlistsJson);
    if (includedListnames === undefined) {
        includedListnames = [];
    }
    
    var listItemsHtml = "";
    for (var x = 0; x < userlists.length; x++) {
        var viewListsUrl = RS_URL_BASE + "/php/userlist.php?l=" + defaultList;
        var viewNewListUrl = RS_URL_BASE + "/php/userlist.php?nl=0";
        var currentUserlist = userlists[x].listname;
        if (currentUserlist == defaultList) {
            if (-1 != includedListnames.indexOf(currentUserlist)) {
                defaultListClass = "checkmark-on";
            }
        } else {
            var checkmarkClass = "checkmark-off";
            if (-1 != includedListnames.indexOf(currentUserlist)) {
                checkmarkClass = "checkmark-on";
            }
            
            listItemsHtml = listItemsHtml + "  <li class='btn-filmlist' id='filmlist-btn-"+currentUserlist+"-"+filmId+"' data-listname='"+currentUserlist+"' data-filmId='"+filmId+"'>";
            listItemsHtml = listItemsHtml + "      <span class='"+checkmarkClass+"' id='filmlist-checkmark-"+filmId+"'>&#10003;</span> "+currentUserlist;
            listItemsHtml = listItemsHtml + "  </li>";
        }
    }
    
    var html = "";
    html = html + "<div class='btn-group-vertical'>";
    html = html + "  <button class='btn btn-sm btn-primary' width='100%' id='filmlist-btn-default-"+filmId+"' data-listname='"+defaultList+"' data-filmId='"+filmId+"' type='button'><span class='"+defaultListClass+"' id='filmlist-checkmark-"+filmId+"'>&#10003;</span> "+defaultList+"</button>";
    html = html + "  <button class='btn btn-sm btn-primary' width='100%' id='filmlist-btn-others-"+filmId+"' type='button'>More lists \u25BC</button>";
    html = html + "</div>";
    html = html + "<div>";
    html = html + "  <ul class='film-filmlists rs-dropdown-menu' id='filmlists-"+filmId+"' hidden >";
    html = html +      listItemsHtml;
    html = html + "    <li class='divider'></li>";
    html = html + "    <li>";
    html = html + "      <a href='"+viewNewListUrl+"' target='_blank'>New List</a>";
    html = html + "    </li>";
    html = html + "    <li>";
    html = html + "      <a href='"+viewListsUrl+"' target='_blank'>View Lists</a>";
    html = html + "    </li>";
    html = html + "  </ul>";
    html = html + "</div>";

    var container = document.getElementById("filmlist-container");
    container.innerHTML = html;
    addFilmlistListeners(container, filmId);
}

function renderStreams(film) {
    var filmId = film.filmId;
    var title = film.title;
    var year = film.year;
    var rsSource = film.sources.find( function (findSource) { return findSource.name == "RatingSync"; } );
    var rsUniqueName = rsSource.uniqueName;

    var streamsHtml = "";
    var providers = validStreamProviders();
    for (var providerIndex = 0; providerIndex < providers.length; providerIndex++) {
        var sourceName = providers[providerIndex];

        var uniqueName = "";
        var uniqueEpisode = "";
        var uniqueAlt = "";
        var streamDate = "";
        var source = film.sources.find( function (findSource) { return findSource.name == sourceName; } );
        if (source) {
            uniqueName = source.uniqueName;
            uniqueEpisode = source.uniqueEpisode;
            uniqueAlt = source.uniqueAlt;
            streamDate = source.streamDate;
        }

        streamsHtml = streamsHtml + "  <div class='stream' id='" + sourceName + "-" + rsUniqueName + "' data-film-id='" + filmId + "' data-source-name='" + sourceName + "' data-title='" + title + "' data-year='" + year + "' data-uniquename='" + uniqueName + "' data-unique-episode='" + uniqueEpisode + "' data-unique-alt='" + uniqueAlt + "' data-stream-date='" + streamDate + "'>\n";
        if (source && source.streamUrl && source.streamUrl != "undefined") {
            streamsHtml = streamsHtml + "    <a href='" + source.streamUrl + "' target='_blank'>\n";
            streamsHtml = streamsHtml + "      <div class='stream-icon icon-" + sourceName + "' title='Watch on " + sourceName + "'></div>\n";
            streamsHtml = streamsHtml + "    </a>\n";
        }
        streamsHtml = streamsHtml + "  </div>\n";
    }

    return streamsHtml;
}

function toggleFilmlist(listname, filmId, activeBtnId) {
    var defaultBtn = document.getElementById("filmlist-btn-default-" + filmId);
    var otherListsBtn = document.getElementById("filmlist-btn-others-" + filmId);
    var otherListsElement = document.getElementById("filmlists-" + filmId);
    defaultBtn.disabled = true;
    otherListsBtn.disabled = true;
    otherListsElement.disabled = true;
    
    var activeBtn = document.getElementById(activeBtnId);
    var checkmark = activeBtn.getElementsByTagName("span")[0];
    var filmIsInTheList = false;
    var addToList = 1; //yes
    if (checkmark.className == "checkmark-on") {
        filmIsInTheList = true;
        var addToList = 0; //no (remove)
    }

    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            filmIsInTheList = !filmIsInTheList;
            if (filmIsInTheList) {
                checkmark.className = "checkmark-on";
            } else {
                checkmark.className = "checkmark-off";
            }

            defaultBtn.disabled = false;
            otherListsBtn.disabled = false;
            otherListsElement.disabled = false;
            otherListsElement.hidden = true;
        }
    }
    xmlhttp.open("GET", RS_URL_API + "?action=setFilmlist&l=" + listname + "&id=" + filmId + "&c=" + addToList, true);
    xmlhttp.send();
}

function toggleHideFilmlists(elementId) {
	var el = document.getElementById(elementId);
    el.hidden = !el.hidden;
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

		var mouseoverHandler = function () { renderYourScore(uniqueName, score, 'new'); };
		var mouseoutHandler = function () { renderYourScore(uniqueName, score, 'original'); };
		var clickHandler = function () { rateFilm(uniqueName, score, titleNum); };

        star.addEventListener("mouseover", mouseoverHandler);
        star.addEventListener("mouseout", mouseoutHandler);
        star.addEventListener("click", clickHandler);
	}
}

function addFilmlistListeners(el, filmId) {
    // Default list button
	var defaultListBtn = document.getElementById("filmlist-btn-default-"+filmId);
	if (defaultListBtn != null) {
	    var listname = defaultListBtn.getAttribute('data-listname');
        var clickDefaultListHandler = function () { toggleFilmlist(listname, filmId, defaultListBtn.getAttribute("id")); };
        defaultListBtn.addEventListener("click", clickDefaultListHandler);
	}

    // "Others" button
	var otherListsBtn = document.getElementById("filmlist-btn-others-"+filmId);
	if (otherListsBtn != null) {
        var clickOtherListsHandler = function () { toggleHideFilmlists('filmlists-'+filmId); };
        otherListsBtn.addEventListener("click", clickOtherListsHandler);
	}

    // Other lists buttons
    var buttons = el.getElementsByClassName("btn-filmlist");
    for (i = 0; i < buttons.length; i++) {
        addFilmlistListener(buttons[i].getAttribute("id"));
    }
}

function addFilmlistListener(elementId) {
	var button = document.getElementById(elementId);
	if (button != null) {
		var listname = button.getAttribute('data-listname');
		var filmId = button.getAttribute('data-filmId');

        var clickHandler = function () { toggleFilmlist(listname, filmId, elementId); };
        button.addEventListener("click", clickHandler);
	}
}

function validStreamProviders() {
    return ["Netflix", "xfinity"];
}