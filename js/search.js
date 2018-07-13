
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
    if (searchDomain == "ratings" || searchDomain == "list" || searchDomain == "both") {
        // Search from RatingSync API
        var params = "?action=searchFilms";
        params = params + "&sd=" + searchDomain;
        params = params + "&q=" + encodeURIComponent(query);
        var callbackHandler = function () { searchFilmsCallback(query, xmlhttp, callback); };
        xmlhttp.onreadystatechange = callbackHandler;
	    xmlhttp.open("GET", RS_URL_API + params, true);
	    xmlhttp.send();
    } else {
        // Search from OMDb API
        var params = "json=1";
        params = params + "&s=" + encodeURIComponent(query);
        params = params + "&apikey=" + OMDB_API_KEY;
        var callbackHandler = function () { searchFilmsCallback(query, xmlhttp, callback); };
        xmlhttp.onreadystatechange = callbackHandler;
	    xmlhttp.open("GET", "https://www.omdbapi.com/?" + params, true);
	    xmlhttp.send();
    }
}

function searchFilmsCallback(query, xmlhttp, callback) {
    if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
        callback(query, xmlhttp);
    }
}

function searchPageCallback(query, xmlhttp) {
	var result = JSON.parse(xmlhttp.responseText);
	var films = [];
	var searchResultEl = document.getElementById("search-result-tbody");
    var limit = 10;

	// Is the result from OMDB or RatingSync
    var dataFromOmdb = false;
	if (result.Search) {
	    dataFromOmdb = true;
	    films = result.Search;
	} else {
	    films = result.films;
	}

	searchResultEl.innerHTML = "";
	var suggestionCount = 0;
	while (suggestionCount < 10 && films && films.length > suggestionCount) {
	    var film = films[suggestionCount];

        // Get IMDb uniqueName
	    var imdbUniqueName = "";
        if (dataFromOmdb) {
            imdbUniqueName = film.imdbID;
        } else {
            imdbUniqueName = getUniqueName(film, "IMDb");
        }

        var rowEl = document.createElement("DIV");
        rowEl.setAttribute("class", "row");
        rowEl.setAttribute("id", "search-" + imdbUniqueName);
        searchResultEl.appendChild(rowEl);
        if (dataFromOmdb) {
            renderOmdbFilm(film, rowEl);
        } else {
            var filmEl = renderSearchResultFilm(film, rowEl);
            renderRsFilmDetails(film, filmEl);
        }

        suggestionCount = suggestionCount + 1;
	}
}

function searchSuggestionCallback(query, xmlhttp) {
	var result = JSON.parse(xmlhttp.responseText);
	var films = result.films;
	var suggestionBoxEl = document.getElementById("header-search-suggestion");
	var limit = 5;
	var fromOmdb = false;
	var imdbIds = [];

    if (result.Search && result.Search.length > 0) {
        // This is a result from OMDB. Convert to RatingSync style
	    films = convertOmdbToRs(result, limit);
	    fromOmdb = true;
    }

    var suggestionLabelEl = document.createElement("div");
    suggestionLabelEl.setAttribute("class", "search-suggestion-label");
    if (searchDomain == "ratings") {
        suggestionLabelEl.innerHTML = "Ratings";
    } else if (searchDomain == "list") {
        suggestionLabelEl.innerHTML = "Watchlist";
    } else if (searchDomain == "both") {
        suggestionLabelEl.innerHTML = "Ratings/Watchlist";
    }
    
    suggestionBoxEl.hidden = true;
    if (films && films.length > 0) {
        suggestionBoxEl.hidden = false;
    }

	suggestionBoxEl.innerHTML = "";
	suggestionBoxEl.appendChild(suggestionLabelEl);
	var suggestionCount = 0;
	while (suggestionCount < limit && films && films.length > suggestionCount) {
	    var film = films[suggestionCount];
        var imdbUniqueName = getUniqueName(film, "IMDb");
        if (imdbUniqueName != "") {
            imdbIds.push(imdbUniqueName);
        }

        var suggestionEl = document.createElement("a");
        suggestionBoxEl.appendChild(suggestionEl);
        renderSuggestionFilm(film, suggestionEl)

        suggestionCount = suggestionCount + 1;
	}
    
    if (fromOmdb) {
        var params = "?action=getFilms";
        params = params + "&imdb=";
        var delim = "";
	    for (i = 0; i < imdbIds.length; i++) {
	        params = params + delim + imdbIds[i];
	        delim = "+";
	    }
	    var xmlhttp = new XMLHttpRequest();
        var callbackHandler = function () { suggestionRatingCallback(xmlhttp); };
        xmlhttp.onreadystatechange = callbackHandler;
	    xmlhttp.open("GET", RS_URL_API + params, true);
	    xmlhttp.send();
    }
}

function renderSuggestionFilm(film, suggestionEl) {
    var imdbUniqueName = getUniqueName(film, "IMDb");
        
	var itemEl = document.createElement("div");
	var posterEl = document.createElement("poster");
	var detailEl = document.createElement("div");
	itemEl.appendChild(posterEl);
	itemEl.appendChild(detailEl);
    suggestionEl.appendChild(itemEl);

    // Poster
    var posterImageEl = document.createElement("img");
	posterImageEl.setAttribute("class", "suggestion-poster");
	posterImageEl.setAttribute("src", film.image);
	posterEl.appendChild(posterImageEl);

    // Detail - title line
	detailEl.setAttribute("class", "suggestion-item-detail");
	var titleLineEl = document.createElement("div");
	titleLineEl.innerHTML = film.title + ' (' + film.year + ')';
	detailEl.appendChild(titleLineEl);

    // Detail - ratings line
	var ratingsLineEl = document.createElement("div");
	ratingsLineEl.setAttribute("id", "suggestion-rating-" + imdbUniqueName);
	renderSuggestionRatings(film, ratingsLineEl);
	detailEl.appendChild(ratingsLineEl);

    // Link & Item
	suggestionEl.setAttribute("href", "/php/detail.php?imdb=" + imdbUniqueName);
	itemEl.setAttribute("class", "search-suggestion-item");
	itemEl.setAttribute("data-imdb-uniquename", imdbUniqueName);
}

function renderSuggestionRatings(film, ratingsLineEl) {
    var rsSource = film.sources.find( function (findSource) { return findSource.name == "RatingSync"; } );
    if (rsSource && rsSource != "undefined") {
        var score = null;
        if (rsSource.rating && rsSource.rating != "undefined") {
            score = rsSource.rating.yourScore;
        }
        if (score != null) {
            var rsRatingEl = document.createElement("span");
            rsRatingEl.setAttribute("class", "search-suggestion-rating-rs");
            rsRatingEl.innerHTML = '<span class="rating-star">★</span>' + score;
            ratingsLineEl.appendChild(rsRatingEl);
        }
    }
}

function suggestionRatingCallback(xmlhttp) {
    if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
	    var result = JSON.parse(xmlhttp.responseText);
        var films = result.films;

        for (var i=0; i<films.length; i++) {
            var film = films[i];
            var imdbUniqueName = getUniqueName(film, "IMDb");
	        var ratingsLineEl = document.getElementById("suggestion-rating-" + imdbUniqueName);
            if (ratingsLineEl) {
                renderSuggestionRatings(film, ratingsLineEl);
            }
        }
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

    var filmEl = renderSearchResultFilm(film, element);

    // Get RS data for this film if it is in the db
    getRatingSync(omdbFilm, filmEl, true);
}

function renderSearchResultFilm(film, element) {
    var filmEl = document.createElement("DIV");
    filmEl.setAttribute("class", "col-xs-12 col-sm-12 col-md-12 col-lg-12");

    var posterEl = document.createElement("poster");
    posterEl.innerHTML = '<img src="'+film.image+'" alt="'+film.title+'"/>';

    var detailEl = buildFilmDetailElement(film);
    var statusEl = detailEl.getElementsByTagName("status")[0];
    if (statusEl) {
        statusEl.innerHTML = '<img src="/image/processing.gif" alt="Please wait Icon" width="28" height="28">';
    }

    filmEl.appendChild(posterEl);
    filmEl.appendChild(detailEl);
    element.appendChild(filmEl);

    return filmEl;
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

function convertOmdbToRs(omdbSearchResult, limit) {
    var films = { "films":[] }.films;
    var suggestionCount = 0;
    while (suggestionCount < limit && omdbSearchResult.Search && omdbSearchResult.Search.length > suggestionCount) {
	    var omdbFilm = omdbSearchResult.Search[suggestionCount];
        var rsFilm = {};
        rsFilm.image = omdbFilm.Poster;
        rsFilm.title = omdbFilm.Title;
        rsFilm.year = omdbFilm.Year;
        rsFilm.sources = [{ "name": "IMDb", "image": omdbFilm.Poster, "uniqueName": omdbFilm.imdbID }];
        films[suggestionCount] = rsFilm;

        suggestionCount = suggestionCount + 1;
    }

    return films;
}

function getUniqueName(film, sourceName)
{
	var imdbUniqueName = "";
    var source = film.sources.find( function (findSource) { return findSource.name == sourceName; } );
    if (source && source != "undefined") {
        imdbUniqueName = source.uniqueName;
    }

    return imdbUniqueName;
}