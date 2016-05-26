
var RS_URL_BASE = "http://localhost:55887";
var RS_URL_API = RS_URL_BASE + "/php/src/ajax/api.php";
var IMDB_FILM_BASEURL = "http://www.imdb.com/title/";

var hideable = true;
var userlistsJson;

document.addEventListener('DOMContentLoaded', function () {
    addStarListeners(document);
});

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

		var mouseoverHandler = function () { showYourScore(uniqueName, score, 'new'); };
		var mouseoutHandler = function () { showYourScore(uniqueName, score, 'original'); };
		var clickHandler = function () { rateFilm(uniqueName, score, titleNum); };

        star.addEventListener("mouseover", mouseoverHandler);
        star.addEventListener("mouseout", mouseoutHandler);
        star.addEventListener("click", clickHandler);
	}
}

function rateFilm(uniqueName, score, titleNum) {
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            var film = JSON.parse(xmlhttp.responseText);
            updateContextDataFilm(film);
            renderStars(film);
        }
    }
    xmlhttp.open("GET", "/php/src/ajax/api.php?action=setRating&un=" + uniqueName + "&s=" + score + "&tn=" + titleNum + "&json=1", true);
    xmlhttp.send();
}

function showYourScore(uniqueName, hoverScore, mousemove) {
    var score = hoverScore;
    if (mousemove == "original") {
        score = document.getElementById("original-score-" + uniqueName).getAttribute("data-score");
    }

    if (score == "10") {
        score = "01";
    }
    document.getElementById("your-score-" + uniqueName).innerHTML = score;
}

function toggleHideFilmlists(elementId) {
	var el = document.getElementById(elementId);
    el.hidden = !el.hidden;
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
    xmlhttp.open("GET", "/php/src/ajax/api.php?action=setFilmlist&l=" + listname + "&id=" + filmId + "&c=" + addToList, true);
    xmlhttp.send();
}

function createFilmlist() {
    var listname = document.getElementById("filmlist-listname").value;
    if (listname == 0) {
        document.getElementById("filmlist-create-result").innerHTML = "";
        return;
    }
    
    var params = "&l="+listname;
    var filmIdEl = document.getElementById("filmlist-filmid");
    if (filmIdEl != null) {
        params = params + "&id=" + filmIdEl.value;
        var addThisEl = document.getElementById("filmlist-add-this");
        if (addThisEl != null && addThisEl.value == "0") {
            params = params + "&a=0";
        } else {
            params = params + "&a=1";
        }
    }

    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            window.location = "/php/userlist.php?l="+listname;
        }
    }
    xmlhttp.open("GET", "/php/src/ajax/api.php?action=createFilmlist"+params, true);
    xmlhttp.send();

    return false;
}

// Needs "contextData" JSON in the page
function showFilmDetail(filmId) {
    var filmEl = document.getElementById("rating-detail");
    var film = contextData.films.find( function (findFilm) { return findFilm.filmId == filmId; } );
    renderFilmDetail(film, filmEl);
}

function renderFilmDetail(film, filmEl) {
    var image = RS_URL_BASE + film.image;
    var imdb = film.sources.find( function (findSource) { return findSource.name == "IMDb"; } );
    var imdbFilmUrl = IMDB_FILM_BASEURL + imdb.uniqueName;
    var imdbLabel = "IMDb";
    var imdbScore = imdb.userScore;
    var rsSource = film.sources.find( function (findSource) { return findSource.name == "RatingSync"; } );
    var yourRatingDate = rsSource.rating.yourRatingDate;
    var dateStr = "";
    if (yourRatingDate && yourRatingDate != "undefined") {
        var reDate = new RegExp("([0-9]+)-([0-9]+)-([0-9]+)");
        var year = reDate.exec(yourRatingDate)[1];
        var month = reDate.exec(yourRatingDate)[2];
        var day = reDate.exec(yourRatingDate)[3];
        dateStr = "You rated this " + month + "/" + day + "/" + year;
    }

    var html = '';
    html = html + '<poster><img src="'+image+'" width="150px"/></poster>\n';
    html = html + '<detail>\n';
    html = html + '  <div class="film-line"><span class="film-title">'+film.title+'</span> ('+film.year+')</div>\n';
    html = html + '  <div align="left">\n';
    html = html + '    <div class="rating-stars" id="rating-stars-'+rsSource.uniqueName+'"></div>\n';
    html = html + '  </div>\n';
    html = html + '  <div class="rating-date">'+dateStr+'</div>\n';
    html = html + '  <div><a href="'+imdbFilmUrl+'" target="_blank">'+imdbLabel+':</a> '+imdbScore+'</div>\n';
    html = html + '  <div id="filmlist-container" align="left"></div>\n';
    html = html + '  <div id="streams" class="streams"></div>\n';
    html = html + '</detail>\n';

    filmEl.innerHTML = html;
    renderStars(film);
    renderStreams(film);
    renderFilmlists(film.filmlists, film.filmId);
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
        starsHtml = starsHtml + "<span class='rating-star' id='rate-" + uniqueName + "-" + starScore + "' data-uniquename='" + uniqueName + "' data-score='" + starScore + "'>☆</span>";
        emptyStars = emptyStars - 1;
        starScore = starScore - 1;
    }
    while (fullStars > 0) {
        starsHtml = starsHtml + "<span class='rating-star' id='rate-" + uniqueName + "-" + starScore + "' data-uniquename='" + uniqueName + "' data-score='" + starScore + "'>★</span>";
        fullStars = fullStars - 1;
        starScore = starScore - 1;
    }

    html = "";
    html = html + "    <score>\n";
    html = html + "      <of-possible>01/</of-possible><your-score id='your-score-" + uniqueName + "'>" + showYourScore + "</your-score>\n";
    html = html + "    </score>\n";
    html = html + "    " + starsHtml + "\n";
    html = html + "    <div id='original-score-" + uniqueName + "' data-score='" + showYourScore + "' hidden ></div>\n";

    ratingStarsEl.innerHTML = html;
    addStarListeners(ratingStarsEl);
}

function renderStreams(film) {
    var rsSource = film.sources.find( function (findSource) { return findSource.name == "RatingSync"; } );
    var rsUniqueName = rsSource.uniqueName;

    var html = "";
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

        html = html + "  <div class='stream' id='" + sourceName + "-" + rsUniqueName + "' data-film-id='" + film.filmId + "' data-source-name='" + sourceName + "' data-title='" + film.title + "' data-year='" + film.year + "' data-uniquename='" + uniqueName + "' data-unique-episode='" + uniqueEpisode + "' data-unique-alt='" + uniqueAlt + "' data-stream-date='" + streamDate + "'>\n";
        if (source && source.streamUrl && source.streamUrl != "undefined") {
            html = html + "    <a href='" + source.streamUrl + "' target='_blank'>\n";
            html = html + "      <div class='stream-icon icon-" + sourceName + "' title='Watch on " + sourceName + "'></div>\n";
            html = html + "    </a>\n";
        }
        html = html + "  </div>\n";
    }
    
    var el = document.getElementById("streams");
    if (el) {
        el.innerHTML = html;
    }
}

function getFilmlists(listnames, filmId) {
    // Get all of the user's filmlists
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            userlistsJson = xmlhttp.responseText;
            if (userlistsJson) {
                renderFilmlists(listnames, filmId);
            }
        }
    }
    xmlhttp.open("GET", RS_URL_API + "?action=getUserLists", true);
    xmlhttp.send();
}

// userlist (JSON) - all of the user's filmlists
// listnames - lists this film belongs in
function renderFilmlists(includedListnames, filmId) {
    if (!userlistsJson) {
        getFilmlists(includedListnames, filmId);
        return;
    }
    
    var defaultList = "Watchlist";
    var defaultListHtmlSafe = defaultList;
    var classCheckmarkOn = "glyphicon glyphicon-check checkmark-on";
    var classCheckmarkOff = "glyphicon glyphicon-check checkmark-off";
    var defaultListClass = classCheckmarkOff;
    var userlists = JSON.parse(userlistsJson);
    if (includedListnames === undefined) {
        includedListnames = [];
    }
    
    var listItemsHtml = "";
    for (var x = 0; x < userlists.length; x++) {
        var currentUserlist = userlists[x].listname;
        if (currentUserlist == defaultList) {
            if (-1 != includedListnames.indexOf(currentUserlist)) {
                defaultListClass = classCheckmarkOn;
            }
        } else {
            listnameHtmlSafe = currentUserlist;
            var checkmarkClass = classCheckmarkOff;
            if (-1 != includedListnames.indexOf(currentUserlist)) {
                checkmarkClass = classCheckmarkOn;
            }

            listItemsHtml = listItemsHtml + "      <li class='filmlist' id='filmlist-"+listnameHtmlSafe+"-"+filmId+"'>\n";
            listItemsHtml = listItemsHtml + "        <a href='#' onClick='toggleFilmlist(\""+listnameHtmlSafe+"\", "+filmId+", \"filmlist-btn-"+listnameHtmlSafe+"-"+filmId+"\")' id='filmlist-btn-"+listnameHtmlSafe+"-"+filmId+"'><span class='"+checkmarkClass+"' id='filmlist-checkmark-"+filmId+"'></span> "+currentUserlist+"</a>\n";
            listItemsHtml = listItemsHtml + "      </li>\n";
        }
    }
    listItemsHtml = listItemsHtml + "      <li class='divider'></li>\n";
    listItemsHtml = listItemsHtml + "      <li><a href='/php/userlist.php?id="+filmId+"'>New list</a></li>\n";
    
    var html = "";
    html = html + "<div class='btn-group-vertical film-filmlists'>\n";
    html = html + "  <button class='btn btn-sm btn-primary' onClick='toggleFilmlist(\""+defaultListHtmlSafe+"\", "+filmId+", \"filmlist-btn-default-"+filmId+"\")' id='filmlist-btn-default-"+filmId+"' data-listname='"+defaultList+"' type='button'>\n";
    html = html + "    <span class='"+defaultListClass+"' id='filmlist-checkmark-"+filmId+"'></span> "+defaultList+"\n";
    html = html + "  </button>\n";
    html = html + "  <div class='btn-group'>\n";
    html = html + "    <button class='btn btn-sm btn-primary dropdown-toggle' id='filmlist-btn-others-"+filmId+"' data-toggle='dropdown' type='button'>\n";
    html = html + "      More lists <span class='caret'></span>\n";
    html = html + "    </button>";
    html = html + "    <ul class='dropdown-menu' id='filmlists-"+filmId+"' role='menu'  >\n";
    html = html +        listItemsHtml + "\n";
    html = html + "    </ul>\n";
    html = html + "  </div>\n";
    html = html + "</div>\n";

    var container = document.getElementById("filmlist-container");
    container.innerHTML = html;
    addFilmlistListeners(container, filmId);
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

function hideFilmDetail() {
    if (hideable) {
        el = document.getElementById("rating-detail");
        el.innerHTML = "";
    }
}

function validStreamProviders() {
    return ["Netflix", "xfinity"];
}

function updateContextDataFilm(updateFilm) {
    var filmId = updateFilm.filmId;
    var index = contextData.films.findIndex(function (findFilm) { return findFilm.filmId == filmId; });

    contextData.films[index] = updateFilm;
}