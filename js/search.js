
function updateSearch() {
    var query = document.getElementById("search-text").value.trim();
    if (query.length == 0) {
	    var searchResultEl = document.getElementById("search-result-tbody");
        searchResultEl.innerHTML = "";
    } else if (query != oldSearchQuery) {
        searchFilms(query);
    }
    oldSearchQuery = query;
}

function searchFilms(query) {
    // Search from OMDb API
    var params = "json=1";
    params = params + "&s=" + encodeURIComponent(query);
	var xmlhttp = new XMLHttpRequest();
    var callbackHandler = function () { searchCallback(xmlhttp, query); };
    xmlhttp.onreadystatechange = callbackHandler;
	xmlhttp.open("GET", "http://www.omdbapi.com/?" + params, true);
	xmlhttp.send();
}

function searchCallback(xmlhttp, query) {
	if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
	    var result = JSON.parse(xmlhttp.responseText);
	    var searchResultEl = document.getElementById("search-result-tbody");

        // OMDB
	    searchResultEl.innerHTML = "";
	    var suggestionCount = 0;
	    while (suggestionCount < 10 && result.Search && result.Search.length) {
	        var omdbFilm = result.Search[suggestionCount];

            var rowEl = document.createElement("DIV");
            rowEl.setAttribute("class", "row");
            rowEl.setAttribute("id", "search-" + omdbFilm.imdbID);
            searchResultEl.appendChild(rowEl);
            renderOmdbFilm(omdbFilm, rowEl);

            suggestionCount = suggestionCount + 1;
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
    
    var filmEl = document.createElement("DIV");
    filmEl.setAttribute("class", "col-xs-12 col-sm-12 col-md-12 col-lg-12");

    var posterEl = document.createElement("poster");
    posterEl.innerHTML = '<img src="'+image+'" width="150px"/>';

    var detailEl = buildFilmDetailElement(film);
    var statusEl = detailEl.getElementsByTagName("status")[0];
    if (statusEl) {
        statusEl.innerHTML = "...";
    }

    filmEl.appendChild(posterEl);
    filmEl.appendChild(detailEl);
    element.appendChild(filmEl);

    // Get RS data for this film if it is in the db
    getRatingSync(omdbFilm.imdbID, filmEl);
}

function getRatingSync(imdbUniqueName, filmEl) {
    var params = "?action=getFilm";
    params = params + "&imdb=" + imdbUniqueName;
	var xmlhttp = new XMLHttpRequest();
    var callbackHandler = function () { getRatingSyncCallback(xmlhttp, filmEl); };
    xmlhttp.onreadystatechange = callbackHandler;
	xmlhttp.open("GET", RS_URL_API + params, true);
	xmlhttp.send();
}

function getRatingSyncCallback(xmlhttp, filmEl) {
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