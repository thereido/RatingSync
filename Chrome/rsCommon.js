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
            var film;
            var response = JSON.parse(xmlhttp.responseText);
            if (response.Success && response.Success == "false") {
                film = getContextDataFilm(filmId);
                renderStatus('Error saving your rating');
            } else {
                film = response;
                updateContextDataFilmByFilmId(film);
                renderStatus('Rating Saved');
            }
            renderStars(film);
            renderRatingDate(film);
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

function renderRatingDate(film) {
    var rsSource = film.sources.find( function (findSource) { return findSource.name == "RatingSync"; } );
    var uniqueName = rsSource.uniqueName;
    var yourRatingDate = rsSource.rating.yourRatingDate;
    
    var ratingDateEl = document.getElementById("rating-date-" + uniqueName);
    if (!ratingDateEl) {
        return;
    }
    
    ratingDateEl.innerHTML = getRatingDateText(yourRatingDate);
}

function getRatingDateText(yourRatingDate) {
    var dateStr = "";
    if (yourRatingDate && yourRatingDate != "undefined") {
        var reDate = new RegExp("([0-9]+)-([0-9]+)-([0-9]+)");
        var ratingYear = reDate.exec(yourRatingDate)[1];
        var month = reDate.exec(yourRatingDate)[2];
        var day = reDate.exec(yourRatingDate)[3];
        dateStr = "You rated this " + month + "/" + day + "/" + ratingYear;
    }
    
    return dateStr;
}

function toggleFilmlist(listname, filmId, activeBtnId) {
    var defaultBtn = document.getElementById("filmlist-btn-default-" + filmId);
    var otherListsBtn = document.getElementById("filmlist-btn-others-" + filmId);
    var otherListsElement = document.getElementById("filmlists-" + filmId);
    if (defaultBtn) defaultBtn.disabled = true;
    if (otherListsBtn) otherListsBtn.disabled = true;
    
    var activeBtn = document.getElementById(activeBtnId);
    var checkmark = activeBtn.getElementsByTagName("span")[0];
    var filmIsInTheList = false;
    var addToList = 1; //yes
    if (checkmark.className == "far fa-check-circle checkmark-on") {
        filmIsInTheList = true;
        var addToList = 0; //no (remove)
    }

    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            filmIsInTheList = !filmIsInTheList;
            if (filmIsInTheList) {
                checkmark.className = "far fa-check-circle checkmark-on";
            } else {
                checkmark.className = "far fa-check-circle checkmark-off";
            }
            
            var film = JSON.parse(xmlhttp.responseText);
            renderFilmlists(film.filmlists, film.filmId);
            updateContextDataFilmByFilmId(film);

            if (defaultBtn) defaultBtn.disabled = false;
            if (otherListsBtn) otherListsBtn.disabled = false;
            if (otherListsElement) otherListsElement.disabled = false;
            if (otherListsElement) otherListsElement.hidden = true;
        }
    }
    xmlhttp.open("GET", RS_URL_API + "?action=setFilmlist&l=" + listname + "&id=" + filmId + "&c=" + addToList, true);
    xmlhttp.send();
}

function clickStar(filmId, uniqueName, score, titleNum) {
    var originalScore = document.getElementById("original-score-" + uniqueName).getAttribute('data-score');
    if (score == originalScore || (score == 10 && originalScore == "01")) {
        showConfirmationRating(filmId, uniqueName, score, titleNum);
    } else {
        rateFilm(filmId, uniqueName, score, titleNum);
    }
}

function showConfirmationRating(filmId, uniqueName, score, titleNum) {
    // Show confirmation dialog asking what to do
    
    // Hide the action area & show the dialog
    var actionAreaEl = document.getElementById("action-area-" + uniqueName);
    actionAreaEl.setAttribute("hidden", true);
    var confirmationEl = document.getElementById("rate-confirmation-" + uniqueName);
    confirmationEl.removeAttribute("hidden");

    // A function to undo the dialog content and show the action area
    var undoDialogFunc = function () {
            confirmationEl.setAttribute("hidden", true);
            confirmationEl.innerHTML = "";
            actionAreaEl.removeAttribute("hidden");
        }
    
    var rateButton = document.createElement("button");
    rateButton.setAttribute("type", "button");
    rateButton.setAttribute("class", "btn btn-primary");
    rateButton.innerHTML = "Rate " + score + " Again";
    var rateHandler = function () { undoDialogFunc(); rateFilm(filmId, uniqueName, score, titleNum); };
    rateButton.addEventListener("click", rateHandler);

    var removeButton = document.createElement("button");
    removeButton.setAttribute("type", "button");
    removeButton.setAttribute("class", "btn btn-secondary btn-sm");
    removeButton.innerHTML = "Remove Rating";
    var removeHandler = function () { undoDialogFunc(); rateFilm(filmId, uniqueName, 0, titleNum); };
    removeButton.addEventListener("click", removeHandler);

    var cancelButton = document.createElement("button");
    cancelButton.setAttribute("type", "button");
    cancelButton.setAttribute("class", "btn btn-link btn-sm");
    cancelButton.innerHTML = "Cancel";
    var cancelHandler = function () { undoDialogFunc(); };
    cancelButton.addEventListener("click", cancelHandler);

    var row1El = document.createElement("div");
    var row2El = document.createElement("div");
    row2El.setAttribute("class", "pt-1");

    row1El.append(rateButton);
    row2El.append(removeButton);
    row2El.append(cancelButton);
    confirmationEl.append(row1El);
    confirmationEl.append(row2El);
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
        return "far fa-check-circle checkmark-on";
    } else {
        return "far fa-check-circle checkmark-off";
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
		var clickHandler = function () { clickStar(filmId, uniqueName, score, titleNum); };

        star.addEventListener("mouseover", mouseoverHandler);
        star.addEventListener("mouseout", mouseoutHandler);
        star.addEventListener("click", clickHandler);
	}
}

function validStreamProviders() {
    return [];
}

function getContextDataFilmIndex(filmId) {
    var index = contextData.films.findIndex(function (findFilm) { return findFilm.filmId == filmId; });
    return index;
}

function getContextDataFilm(filmId) {
    var film = JSON.parse("{}");
    var index = getContextDataFilmIndex(filmId);

    if (-1 != index) {
        film = contextData.films[index];
    }

    return film;
}

function updateContextDataFilmByFilmId(updateFilm) {
    var filmId = updateFilm.filmId;
    var index = getContextDataFilmIndex(filmId);

    if (-1 != index) {
        contextData.films[index] = updateFilm;
    }
}

function updateContextDataFilmByUniqueName(updateFilm, sourceName) {
    var uniqueName = getUniqueName(updateFilm, sourceName);
    var index = contextData.films.findIndex(function (findFilm) { return getUniqueName(findFilm, sourceName) == uniqueName; });

    if (-1 != index) {
        contextData.films[index] = updateFilm;
    }
}

function getFilmFromRs(filmId, uniqueName, imdbId, contentType, parentId, seasonNum, episodeNum, xmlhttp, callback) {
    var params = "?action=getFilm";
    params = params + "&id=" + filmId;
    if (imdbId && imdbId != "undefined") { params = params + "&imdb=" + imdbId; }
    if (uniqueName && uniqueName != "undefined") { params = params + "&un=" + uniqueName; }
    params = params + "&ct=" + contentType;
    if (parentId && parentId != "undefined") { params = params + "&pid=" + parentId; }
    if (seasonNum && seasonNum != "undefined") { params = params + "&s=" + seasonNum; }
    if (episodeNum && episodeNum != "undefined") { params = params + "&e=" + episodeNum; }
    params = params + "&rsonly=0";

    var callbackHandler = function () { getFilmFromRsCallback(xmlhttp, callback); };
    xmlhttp.onreadystatechange = callbackHandler;
    xmlhttp.open("GET", RS_URL_API + params, true);
    xmlhttp.send();
}

function getFilmFromRsCallback(xmlhttp, callback) {
    if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
        callback(xmlhttp);
    }
}

function getFilmForDropdown(film) {
    var filmId = film.filmId;
    var uniqueName;
    var defaultSource = film.sources.find( function (findSource) { return findSource.name == DATA_API_DEFAULT; } );
    if (defaultSource && defaultSource != "undefined") {
        uniqueName = defaultSource.uniqueName;
    }
    var imdbId;
    var imdbSource = film.sources.find( function (findSource) { return findSource.name == SOURCE_IMDB; } );
    if (imdbSource && imdbSource != "undefined") {
        imdbId = imdbSource.uniqueName;
    }
    var contentType = film.contentType;
    var parentId = film.parentId;
    var seasonNum = film.seasonNum;
    var episodeNum = film.episodeNum;
    var xmlhttp = new XMLHttpRequest();
    var callbackHandler = function () { filmDropdownCallback(xmlhttp); };
    getFilmFromRs(filmId, uniqueName, imdbId, contentType, parentId, seasonNum, episodeNum, xmlhttp, callbackHandler);
}

function filmDropdownCallback(xmlhttp) {
	if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
        var result = JSON.parse(xmlhttp.responseText);
        if (result.Success != "false" && result.filmId != "undefined") {
            var film = result;
            var filmId = film.filmId;
            var filmIndex = contextData.films.findIndex( function (findFilm) { return findFilm.filmId == filmId; } );
            if (filmIndex != -1) {
                contextData.films[filmIndex] = film;
            } else {
                contextData.films.push(film);
            }
            var dropdownEl = document.getElementById("film-dropdown-" + film.filmId);
            renderFilmDetail(film, dropdownEl);
        }
	}
}

function renderFilmDetail(film, dropdownEl) {
    dropdownEl.innerHTML = "";
    dropdownEl.appendChild(buildFilmDetailElement(film));
    dropdownEl.style.display = "block";

    renderStars(film);
    renderStreams(film, true);
    renderFilmlists(film.filmlists, film.filmId);
}