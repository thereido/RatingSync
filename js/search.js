
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

function updateSearch() {
    var query = document.getElementById("search-text").value;
    if (query.length == 0) {
	    var searchResultEl = document.getElementById("search-result-tbody");
        searchResultEl.innerHTML = "";
    } else if (query != searchQuery) {
        searchQuery = query;
        searchFilms(query);
    }
}

function renderOmdbFilm(omdbFilm, element) {
    var imdbLabel = "IMDb";
    var imdbFilmUrl = IMDB_FILM_BASEURL + omdbFilm.imdbID;
    var imdbLink = "<a href='" + imdbFilmUrl + "' target='_blank'>" + imdbLabel + "</a>";

    var image = "";
    if (omdbFilm.Poster && omdbFilm.Poster != "N/A") {
        image = omdbFilm.Poster;
    }
    
    var html = '';
    html = html + '<poster><img src="'+image+'" width="150px"/></poster>\n';
    html = html + '<detail>\n';
    html = html + '  <div class="film-line"><span class="film-title">'+omdbFilm.Title+'</span> ('+omdbFilm.Year+')</div>\n';
    html = html + "  <episodeTitle class='tv-episode-title'></episodeTitle>\n";
    html = html + "  <div><season class='tv-season'></season><episodeNumber class='tv-episodenum'></episodeNumber></div>\n";
    html = html + '  <div align="left">\n';
    html = html + '    <ratingStars class="rating-stars" id="rating-stars"></ratingStars>\n';
    html = html + '  </div>\n';
    html = html + '  <ratingDate class="rating-date"></ratingDate>\n';
    html = html + '  <div><a href="'+imdbFilmUrl+'" target="_blank">'+imdbLabel+'</a><imdbScore id="imdb-score-"'+omdbFilm.imdbID+'</imdbScore></div>\n';
    html = html + '  <status class="search-status">...</status>\n';
    html = html + '  <filmlistContainer id="filmlist-container" align="left"></filmlistContainer>\n';
    html = html + '  <streams id="streams" class="streams"></streams>\n';
    html = html + '</detail>\n';
    
    var filmEl = document.createElement("DIV");
    filmEl.setAttribute("class", "col-xs-12 col-sm-12 col-md-12 col-lg-12");
    filmEl.innerHTML = html;
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
	if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
        var statusEl = filmEl.getElementsByTagName("status")[0];
        if (statusEl) {
            statusEl.innerHTML = "";
        }
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

    var imdb = film.sources.find( function (findSource) { return findSource.name == "IMDb"; } );
    var imdbScoreEl = filmEl.getElementsByTagName("imdbScore")[0];
    imdbScoreEl.innerHTML = ": " + imdb.userScore;

    if (film.episodeTitle) {
        var episodeTitleEl = filmEl.getElementsByTagName("episodeTitle")[0];
        episodeTitleEl.innerHTML = film.episodeTitle;
    }
    
    if (film.season) {
        var seasonEl = filmEl.getElementsByTagName("season")[0];
        seasonEl.innerHTML = "Season " + film.season;
    }
    
    if (film.episodeNumber) {
        var episodeNumberEl = filmEl.getElementsByTagName("episodeNumber")[0];
        episodeNumberEl.innerHTML = " - Episode " + film.episodeNumber;
    }

    var rsSource = film.sources.find( function (findSource) { return findSource.name == "RatingSync"; } );
    if (rsSource) {
        var yourRatingDate = rsSource.rating.yourRatingDate;
        var dateStr = "";
        if (yourRatingDate && yourRatingDate != "undefined") {
            var reDate = new RegExp("([0-9]+)-([0-9]+)-([0-9]+)");
            var year = reDate.exec(yourRatingDate)[1];
            var month = reDate.exec(yourRatingDate)[2];
            var day = reDate.exec(yourRatingDate)[3];
            var ratingDateEl = filmEl.getElementsByTagName("ratingDate")[0];
            ratingDateEl.innerHTML = "You rated this " + month + "/" + day + "/" + year;
        }
        
        var ratingStarsEl = filmEl.getElementsByTagName("ratingStars")[0];
        ratingStarsEl.setAttribute("id", "rating-stars-" + rsSource.uniqueName);
        renderStars(film);
    }
    
    var streamsEl = filmEl.getElementsByTagName("streams")[0];
    streamsEl.setAttribute("id", "streams-" + film.filmId);
    renderStreams(film);
    
    var filmlistContainerEl = filmEl.getElementsByTagName("filmlistContainer")[0];
    filmlistContainerEl.setAttribute("id", "filmlist-container-" + film.filmId);
    renderFilmlists(film.filmlists, film.filmId);
}