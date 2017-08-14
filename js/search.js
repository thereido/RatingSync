
function fullSearch(query) {
    if (query.length == 0) {
	    var searchResultEl = document.getElementById("search-result-tbody");
        searchResultEl.innerHTML = "";
    } else if (query != oldSearchQuery) {
	    var xmlhttp = new XMLHttpRequest();
        var callbackHandler = function () { searchPageCallback(query, xmlhttp); };
        searchFilms(query, xmlhttp, callbackHandler);
    }
}

function searchFilms(query, xmlhttp, callback) {
    // Search from OMDb API
    var params = "json=1";
    params = params + "&s=" + encodeURIComponent(query);
    params = params + "&apikey=" + OMDB_API_KEY;
    var callbackHandler = function () { searchFilmsCallback(query, xmlhttp, callback); };
    xmlhttp.onreadystatechange = callbackHandler;
	xmlhttp.open("GET", "http://www.omdbapi.com/?" + params, true);
	xmlhttp.send();
}

function searchFilmsCallback(query, xmlhttp, callback) {
    if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
        callback(query, xmlhttp);
    }
}

function searchPageCallback(query, xmlhttp) {
	var result = JSON.parse(xmlhttp.responseText);
	var searchResultEl = document.getElementById("search-result-tbody");

    // OMDB
	searchResultEl.innerHTML = "";
	var suggestionCount = 0;
	while (suggestionCount < 10 && result.Search && result.Search.length > suggestionCount) {
	    var omdbFilm = result.Search[suggestionCount];

        var rowEl = document.createElement("DIV");
        rowEl.setAttribute("class", "row");
        rowEl.setAttribute("id", "search-" + omdbFilm.imdbID);
        searchResultEl.appendChild(rowEl);
        renderOmdbFilm(omdbFilm, rowEl);

        suggestionCount = suggestionCount + 1;
	}
}

function searchSuggestionCallback(query, xmlhttp) {
	var result = JSON.parse(xmlhttp.responseText);
	var suggestionEl = document.getElementById("header-search-suggestion");

    if (result.Search && result.Search.length > 0) {
        suggestionEl.hidden = false;
    }

    // OMDB
	suggestionEl.innerHTML = "";
	var suggestionCount = 0;
	while (suggestionCount < 5 && result.Search && result.Search.length > suggestionCount) {
	    var omdbFilm = result.Search[suggestionCount];
	    var itemEl = document.createElement("div");
	    itemEl.setAttribute("class", "search-suggestion-item");
	    itemEl.setAttribute("data-imdb-uniquename", omdbFilm.imdbID);
	    var imageHtml = '<img src="'+omdbFilm.Poster+'"/>';
	    itemEl.innerHTML = '<a href="/php/detail.php?imdb='+omdbFilm.imdbID+'">' + imageHtml + omdbFilm.Title + ' (' + omdbFilm.Year + ')</a>';
        suggestionEl.appendChild(itemEl);

        suggestionCount = suggestionCount + 1;
	}
}

/**
 * param omdbFilm JSON from OMDB API
 *   Title
 *   Year
 *   imdbID
 *   Type - movie/series
 *   Poster - IMDb URL to image
 */
function renderOmdbFilm(omdbFilm, element) {
    var image = "";
    if (omdbFilm.Poster && omdbFilm.Poster != "N/A") {
        image = omdbFilm.Poster;
    }

    // Build a JSON film from the omdbFilm
    var filmStr =       '{ ';
    filmStr = filmStr + '"title":"' +omdbFilm.Title+ '"';
    filmStr = filmStr + ', "year":"' +omdbFilm.Year+ '"';
    filmStr = filmStr + ', "image":"' +image+ '"';
    filmStr = filmStr + ', "sources": [';
    filmStr = filmStr +                 '{ "name":"IMDb", "uniqueName":"' +omdbFilm.imdbID+ '" }';
    filmStr = filmStr +              ']';
    filmStr = filmStr + ' }';
    var film = JSON.parse(filmStr);
    
    var filmEl = document.createElement("DIV");
    filmEl.setAttribute("class", "col-xs-12 col-sm-12 col-md-12 col-lg-12");

    var posterEl = document.createElement("poster");
    posterEl.innerHTML = '<img src="'+image+'" width="150px"/>';

    var detailEl = buildFilmDetailElement(film);
    var statusEl = detailEl.getElementsByTagName("status")[0];
    if (statusEl) {
        statusEl.innerHTML = '<img src="/image/processing.gif" alt="Please wait Icon" width="28" height="28">';
    }

    filmEl.appendChild(posterEl);
    filmEl.appendChild(detailEl);
    element.appendChild(filmEl);

    // Get RS data for this film if it is in the db
    getRatingSync(omdbFilm, filmEl, true);
}

function getRatingSync(omdbFilm, filmEl, detailFromRsOnly) {
    var imdbUniqueName = omdbFilm.imdbID;
    var rsOnly = "1";
    if (!detailFromRsOnly) {
        rsOnly = "0";
    }

    var params = "?action=getFilm";
    params = params + "&imdb=" + imdbUniqueName;
    params = params + "&rsonly=" + rsOnly;
	var xmlhttp = new XMLHttpRequest();
    var callbackHandler = function () { getRatingSyncCallback(xmlhttp, filmEl, omdbFilm); };
    xmlhttp.onreadystatechange = callbackHandler;
	xmlhttp.open("GET", RS_URL_API + params, true);
	xmlhttp.send();
}

function getRatingSyncCallback(xmlhttp, filmEl, omdbFilm) {
    if (xmlhttp.readyState == 4) {
        var statusEl = filmEl.getElementsByTagName("status")[0];
        if (statusEl) {
            statusEl.innerHTML = "";
        }
    }
	if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
	    var result = JSON.parse(xmlhttp.responseText);
        if (result.Success != "false" && result.filmId != "undefined") {
            var film = result;
            renderRsFilmDetails(film, filmEl);
        } else {
            renderNoRsFilmDetails(filmEl, omdbFilm);
        }
	}
}

function renderRsFilmDetails(film, filmEl) {
    var imageEl = filmEl.getElementsByTagName("poster")[0].getElementsByTagName("img")[0];
    imageEl.setAttribute("src", RS_URL_BASE + film.image);

    var newDetailEl = buildFilmDetailElement(film);
    var detailEl = filmEl.getElementsByTagName("detail")[0];
    if (detailEl) {
        detailEl.innerHTML = newDetailEl.innerHTML;
    } else {
        filmEl.appendChild(newDetailEl);
    }
    
    renderStars(film);
    renderStreams(film, true);
    renderFilmlists(film.filmlists, film.filmId);
}

function renderNoRsFilmDetails(filmEl, omdbFilm) {
    var imdbUniqueName = omdbFilm.imdbID;
    var imdbFilmUrl = IMDB_FILM_BASEURL + imdbUniqueName;

    var html = '\n';
    html = html + '<div id="seemore-'+imdbUniqueName+'"><a href="javascript:void(0);">More</a></div>';

    var seeMoreEl = document.createElement("seemore");
    seeMoreEl.innerHTML = html;
    seeMoreEl.onclick = function() { onClickSeeMore(omdbFilm, filmEl, seeMoreEl); };

    var detailEl = filmEl.getElementsByTagName("detail")[0];
    if (detailEl) {
        detailEl.appendChild(seeMoreEl);
    } else {
        filmEl.appendChild(seeMoreEl);
    }
}

function onClickSeeMore(omdbFilm, filmEl, seeMoreEl) {
    seeMoreEl.innerHTML = '<img src="/image/processing.gif" alt="Please wait Icon" width="28" height="28">';
    getRatingSync(omdbFilm, filmEl, false);
}