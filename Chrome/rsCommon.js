/**
 * A html file sourcing this javascript must...
 *   - Have element with id='status'
 *   - Have a javascript var contextData as JSON with films[]
 */
var userlistsJson;
var userlistsCallbacks = [];
var waitingForFilmlists = false;
var contextData = JSON.parse('{"films":[]}');

function renderStatus(statusText) {
    var statusEl = document.getElementById('status');
    if (statusEl) {
        statusEl.textContent = statusText;
    }
}

function rateFilm(filmId, uniqueName, score, titleNum) {
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            var film = JSON.parse(xmlhttp.responseText);
            updateContextDataFilm(film);
            renderStars(film);
            renderStatus('Rating Saved');
        }
    }
    var params = "";
    params = params + "&json=1";
    params = params + "&fid=" + filmId;
    params = params + "&un=" + uniqueName;
    params = params + "&s=" + score;
    if (titleNum && titleNum != "undefined") {
        params = params + "&tn=" + titleNum;
    }
    xmlhttp.open("GET", RS_URL_API + "?action=setRating" + params, true);
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

function renderStars(film) {
    var rsSource = film.sources.find( function (findSource) { return findSource.name == "RatingSync"; } );
    var uniqueName = rsSource.uniqueName;
    var yourScore = rsSource.rating.yourScore;

    var ratingStarsEl = document.getElementById("rating-stars-" + uniqueName);
    if (!ratingStarsEl) {
        return;
    }
    
    // The score is shown backwards
    var showYourScore = yourScore;
    if (showYourScore == "10") {
        showYourScore = "01";
    } else if (showYourScore == null || showYourScore == "") {
        showYourScore = "-";
    }
    
    var starsHtml = "";
    var fullStars = yourScore;
    var emptyStars = 10 - yourScore;
    var starScore = 10;
    while (emptyStars > 0) {
        starsHtml = starsHtml + "<span class='rating-star' id='rate-" + uniqueName + "-" + starScore + "' data-film-id='" + film.filmId + "' data-uniquename='" + uniqueName + "' data-score='" + starScore + "'>☆</span>";
        emptyStars = emptyStars - 1;
        starScore = starScore - 1;
    }
    while (fullStars > 0) {
        starsHtml = starsHtml + "<span class='rating-star' id='rate-" + uniqueName + "-" + starScore + "' data-film-id='" + film.filmId + "' data-uniquename='" + uniqueName + "' data-score='" + starScore + "'>★</span>";
        fullStars = fullStars - 1;
        starScore = starScore - 1;
    }

    var html = "";
    html = html + "    <score>\n";
    html = html + "      <of-possible>01/</of-possible><your-score id='your-score-" + uniqueName + "'>" + showYourScore + "</your-score>\n";
    html = html + "    </score>\n";
    html = html + "    " + starsHtml + "\n";
    html = html + "    <div id='original-score-" + uniqueName + "' data-score='" + showYourScore + "' hidden ></div>\n";

    ratingStarsEl.innerHTML = html;
    addStarListeners(ratingStarsEl);
}

function renderStreams(film, displaySearch) {
    var rsSource = film.sources.find( function (findSource) { return findSource.name == "RatingSync"; } );
    var rsUniqueName = rsSource.uniqueName;

    var streamsEl = document.getElementById("streams-"+film.filmId);
    var watchEl = document.createElement("DIV");
    var searchSourceEl = document.createElement("DIV");
    streamsEl.innerHTML = "";
    streamsEl.appendChild(watchEl);
    streamsEl.appendChild(searchSourceEl);

    var providers = validStreamProviders();
    for (var providerIndex = 0; providerIndex < providers.length; providerIndex++) {
        var sourceName = providers[providerIndex];

        var uniqueName;
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
        if (!uniqueName || uniqueName == null || uniqueName == "null" || uniqueName == "uniqueName") {
            uniqueName = "";
        }

        var streamLink = "";
        var searchLink = "";
        if (source && source.streamUrl && source.streamUrl != "undefined") {
            streamLink = source.streamUrl;
        } else {
            if (sourceName == "Netflix") {
                streamLink = "https://www.netflix.com/title/" + uniqueName;
                searchLink = "https://www.netflix.com/search/" + encodeURI(film.title);
            } else if (sourceName == "xfinity") {
                streamLink = "https://tv.xfinity.com/entity/" + uniqueName;
                searchLink = "https://tv.xfinity.com/search?q=" + encodeURI(film.title);
            }
        }

        var streamEl = document.createElement("DIV");
        streamEl.setAttribute("id", sourceName + "-" + rsUniqueName);
        streamEl.setAttribute("class", "stream");
        streamEl.setAttribute("data-film-id", film.filmId);
        streamEl.setAttribute("data-source-name", sourceName);
        streamEl.setAttribute("data-title", film.title);
        streamEl.setAttribute("data-year", film.year);
        streamEl.setAttribute("data-uniquename", uniqueName);
        streamEl.setAttribute("data-unique-episode", uniqueEpisode);
        streamEl.setAttribute("data-unique-alt", uniqueAlt);
        streamEl.setAttribute("data-stream-date", streamDate);
        if (uniqueName && uniqueName != "" && uniqueName != "undefined") {
            var html = "\n";
            html = html + "    <a href='" + streamLink + "' target='_blank'>\n";
            html = html + "      <div class='stream-icon icon-" + sourceName + "' title='Watch on " + sourceName + "'></div>\n";
            html = html + "    </a>\n";
            streamEl.innerHTML = html;
            watchEl.appendChild(streamEl);
        } else if (displaySearch) {
            var html = "\n";
            html = html + "    <a href='" + searchLink + "' target='_blank'>\n";
            html = html + "      <div class='stream-icon icon-" + sourceName + "' title='Search " + sourceName + "'></div>\n";
            html = html + "    </a>\n";
            streamEl.innerHTML = html;
            searchSourceEl.appendChild(streamEl);
        }
    }
}

function toggleFilmlist(listname, filmId, activeBtnId) {
    var defaultBtn = document.getElementById("filmlist-btn-default-" + filmId);
    var otherListsBtn = document.getElementById("filmlist-btn-others-" + filmId);
    var otherListsElement = document.getElementById("filmlists-" + filmId);
    if (defaultBtn) defaultBtn.disabled = true;
    if (otherListsBtn) otherListsBtn.disabled = true;
    if (otherListsElement) otherListsElement.disabled = true;
    
    var activeBtn = document.getElementById(activeBtnId);
    var checkmark = activeBtn.getElementsByTagName("span")[0];
    var filmIsInTheList = false;
    var addToList = 1; //yes
    if (checkmark.className == "glyphicon glyphicon-check checkmark-on") {
        filmIsInTheList = true;
        var addToList = 0; //no (remove)
    }

    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            filmIsInTheList = !filmIsInTheList;
            if (filmIsInTheList) {
                checkmark.className = "glyphicon glyphicon-check checkmark-on";
            } else {
                checkmark.className = "glyphicon glyphicon-check checkmark-off";
            }
            
            var film = JSON.parse(xmlhttp.responseText);
            renderFilmlists(film.filmlists, film.filmId);
            updateContextDataFilm(film);

            if (defaultBtn) defaultBtn.disabled = false;
            if (otherListsBtn) otherListsBtn.disabled = false;
            if (otherListsElement) otherListsElement.disabled = false;
            if (otherListsElement) otherListsElement.hidden = true;
        }
    }
    xmlhttp.open("GET", RS_URL_API + "?action=setFilmlist&l=" + listname + "&id=" + filmId + "&c=" + addToList, true);
    xmlhttp.send();
}

function getFilmlists(callback) {
    if (userlistsJson) {
        callback();
    }
    else {
        userlistsCallbacks.push(callback);
        if (!waitingForFilmlists) {
            // Get all of the user's filmlists
            var xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function () {
                if (xmlhttp.readyState == 4) {
                    if (xmlhttp.status == 200) {
                        userlistsJson = xmlhttp.responseText;
                        while (userlistsCallbacks.length > 0) {
                             var cb = userlistsCallbacks.pop();
                             cb();
                        }
                    }
                    waitingForFilmlists = false;
                }
            }
            xmlhttp.open("GET", RS_URL_API + "?action=getUserLists", true);
            waitingForFilmlists = true;
            xmlhttp.send();
        }
    }
}

function getCheckmarkClass(checked) {
    if (checked) {
        return "glyphicon glyphicon-check checkmark-on";
    } else {
        return "glyphicon glyphicon-check checkmark-off";
    }
}

function getDefaultList() {
    return "Watchlist";
}

function toggleHideFilmlists(elementId) {
	var el = document.getElementById(elementId);
    el.hidden = !el.hidden;
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

function addStarListeners(el) {
    var stars = el.getElementsByClassName("rating-star");
    for (i = 0; i < stars.length; i++) {
        addStarListener(stars[i].getAttribute("id"));
    }
}

function addStarListener(elementId) {    
	var star = document.getElementById(elementId);
	if (star != null) {
		var filmId = star.getAttribute('data-film-id');
		var uniqueName = star.getAttribute('data-uniquename');
		var score = star.getAttribute('data-score');
		var titleNum = star.getAttribute('data-title-num');

		var mouseoverHandler = function () { renderYourScore(uniqueName, score, 'new'); };
		var mouseoutHandler = function () { renderYourScore(uniqueName, score, 'original'); };
		var clickHandler = function () { rateFilm(filmId, uniqueName, score, titleNum); };

        star.addEventListener("mouseover", mouseoverHandler);
        star.addEventListener("mouseout", mouseoutHandler);
        star.addEventListener("click", clickHandler);
	}
}

function validStreamProviders() {
    return ["Netflix", "xfinity"];
}

function updateContextDataFilm(updateFilm) {
    var filmId = updateFilm.filmId;
    var index = contextData.films.findIndex(function (findFilm) { return findFilm.filmId == filmId; });

    if (-1 != index) {
        contextData.films[index] = updateFilm;
    }
}