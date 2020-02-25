
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
        // Search from the default data API
        var searchUrl = "";
        var searchParams = "";
        if (DATA_API_DEFAULT == SOURCE_OMDBAPI) {

            searchUrl = URL_SEARCH_OMDB + "&apikey=" + OMDB_API_KEY;
            searchParams = "&s=" + encodeURIComponent(query);
            var imdbIdIndex = query.trim().search(/^tt\d{7}\d*$/i); // "tt" followed by at least 7 digits
            if (imdbIdIndex > -1) {
                searchParams = "&i=" + query.trim();
            }
            searchUrl = searchUrl + searchParams;

        }
        else if (DATA_API_DEFAULT == SOURCE_TMDBAPI) {
            
            var imdbIdIndex = query.trim().search(/^tt\d{7}\d*$/i); // "tt" followed by at least 7 digits
            if (imdbIdIndex > -1) {
                // Search using an IMDb ID
                var imdbId = query.trim();
                searchUrl = URL_FIND_TMDB + "/" + imdbId + "?external_source=imdb_id";
            } else {
                // Mulit Search
                searchUrl = URL_SEARCH_TMDB + "&query=" + encodeURIComponent(query);
            }

            searchUrl = searchUrl + "&api_key=" + TMDB_API_KEY;

        }
        
        var callbackHandler = function () { searchFilmsCallback(query, xmlhttp, callback); };
        xmlhttp.onreadystatechange = callbackHandler;
	    xmlhttp.open("GET", searchUrl, true);
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

    if (result.films) {
        films = result.films;
        fromOtherSource = false;
    } else {
        films = convertSourceDataListToRs(result, DATA_API_DEFAULT, limit);
	    fromOtherSource = true;
    }

    contextData.films = films;

	searchResultEl.innerHTML = "";
    var suggestionCount = 0;
	while (suggestionCount < 50 && films && films.length > suggestionCount) {
	    var film = films[suggestionCount];

        var uniqueName = getUniqueName(film, DATA_API_DEFAULT);

        var rowEl = document.createElement("DIV");
        rowEl.setAttribute("class", "row");
        rowEl.setAttribute("id", "search-" + uniqueName);
        searchResultEl.appendChild(rowEl);
        if (fromOtherSource) {
            var filmEl = renderSearchResultFilm(film, rowEl);
            getRatingSync(film, filmEl, true);
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
	var fromOtherSource = true;
	var uniqueNameAndContentTypes = [];

    if (result.films) {
        fromOtherSource = false;
    } else {
	    films = convertSourceDataListToRs(result, DATA_API_DEFAULT, limit);
	    fromOtherSource = true;
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
        var uniqueName = getUniqueName(film, DATA_API_DEFAULT);
        if (uniqueName != "") {
            var uniqueNameAndContentType = uniqueName;
            if (film.contentType) {
                uniqueNameAndContentType += "_" + film.contentType;
            }
            uniqueNameAndContentTypes.push(uniqueNameAndContentType);
        }

        var suggestionEl = document.createElement("a");
        suggestionBoxEl.appendChild(suggestionEl);
        renderSuggestionFilm(film, suggestionEl)

        suggestionCount = suggestionCount + 1;
	}
    
    if (fromOtherSource) {
        var params = "?action=getFilms";
        params = params + "&uncts="; // sourceId/contentType combos
        var delim = "";
	    for (i = 0; i < uniqueNameAndContentTypes.length; i++) {
	        params = params + delim + uniqueNameAndContentTypes[i];
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
    var uniqueName = getUniqueName(film, DATA_API_DEFAULT);
        
	var itemEl = document.createElement("div");
	var posterEl = document.createElement("poster");
	var detailEl = document.createElement("div");
	itemEl.appendChild(posterEl);
	itemEl.appendChild(detailEl);
    suggestionEl.appendChild(itemEl);

    // Poster
    var posterImageEl = document.createElement("img");
	posterImageEl.setAttribute("class", "suggestion-poster rounded");
	posterImageEl.setAttribute("src", film.image);
	posterEl.appendChild(posterImageEl);

    // Detail - title line
	detailEl.setAttribute("class", "suggestion-item-detail");
    var titleLineEl = document.createElement("div");
    var contentTypeText = "";
    if (film.contentType == CONTENT_TV_SERIES) {
        contentTypeText = " TV";
    }
	titleLineEl.innerHTML = film.title + ' (' + film.year + ')' + contentTypeText;
	detailEl.appendChild(titleLineEl);

    // Detail - ratings line
	var ratingsLineEl = document.createElement("div");
	ratingsLineEl.setAttribute("id", "suggestion-rating-" + uniqueName);
    renderSuggestionRatings(film, ratingsLineEl);
	detailEl.appendChild(ratingsLineEl);

    // Link & Item
    var contentTypeParam = "";
    if (film.contentType != "undefined") { contentTypeParam = "&ct=" + film.contentType; }
	suggestionEl.setAttribute("href", "/php/detail.php?un=" + uniqueName + contentTypeParam);
	itemEl.setAttribute("class", "search-suggestion-item");
	itemEl.setAttribute("data-uniquename", uniqueName);
	itemEl.setAttribute("data-contenttype", film.contentType);
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
            var uniqueName = getUniqueName(film, DATA_API_DEFAULT);
	        var ratingsLineEl = document.getElementById("suggestion-rating-" + uniqueName);
            if (ratingsLineEl) {
                renderSuggestionRatings(film, ratingsLineEl);
            }
        }
    }
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

function getRatingSync(film, filmEl, detailFromRsOnly) {
    var uniqueName = getUniqueName(film, DATA_API_DEFAULT);
    var contentType = film.contentType;
    var rsOnly = "1";
    if (!detailFromRsOnly) {
        rsOnly = "0";
    }

    var params = "?action=getFilm";
    params += "&un=" + uniqueName;
    if (contentType && contentType != "undefined") { params += "&ct=" + contentType; }
    params += "&rsonly=" + rsOnly;
	var xmlhttp = new XMLHttpRequest();
    var callbackHandler = function () { getRatingSyncCallback(xmlhttp, filmEl, film); };
    xmlhttp.onreadystatechange = callbackHandler;
	xmlhttp.open("GET", RS_URL_API + params, true);
	xmlhttp.send();
}

function getRatingSyncCallback(xmlhttp, filmEl, film) {
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
            updateContextDataFilmByUniqueName(film, DATA_API_DEFAULT);
            renderRsFilmDetails(film, filmEl);
        } else {
            renderNoRsFilmDetails(filmEl, film);
        }
	}
}

function renderRsFilmDetails(film, filmEl) {
    var posterEl = filmEl.getElementsByTagName("poster")[0];
    var imageEl = posterEl.getElementsByTagName("img")[0];
    posterEl.removeChild(posterEl.getElementsByTagName("img")[0]);

    var filmId = getFilmId(film);
    var parentId = getFilmParentId(film);
    var contentType = getFilmContentType(film);
    
    if (film.image) {
        imageEl.setAttribute("src", RS_URL_BASE + film.image);

        if (contentType == CONTENT_TV_EPISODE) {
            imageEl.setAttribute("class", "img-episode");
        }
    }

    if (filmId != "") {
        var linkEl = document.createElement("a");
        var contentTypeParam = "";
        if (contentType != "undefined") { contentTypeParam = "&ct=" + contentType; }
        var parentIdParam = "";
        if (parentId != "") { parentIdParam = "&pid=" + parentId; }

        linkEl.setAttribute("href", "/php/detail.php?i=" + filmId + parentIdParam + contentTypeParam);
        linkEl.appendChild(imageEl);
        posterEl.appendChild(linkEl);
    } else {
        posterEl.appendChild(imageEl);
    }

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

function renderNoRsFilmDetails(filmEl, film) {
    var uniqueName = getUniqueName(film, DATA_API_DEFAULT);

    var html = '\n';
    html = html + '<div id="seemore-'+uniqueName+'"><a href="javascript:void(0);">More</a></div>';

    var seeMoreEl = document.createElement("seemore");
    seeMoreEl.innerHTML = html;
    seeMoreEl.onclick = function() { onClickSeeMore(film, filmEl, seeMoreEl); };

    var detailEl = filmEl.getElementsByTagName("detail")[0];
    if (detailEl) {
        detailEl.appendChild(seeMoreEl);
    } else {
        filmEl.appendChild(seeMoreEl);
    }
}

function onClickSeeMore(film, filmEl, seeMoreEl) {
    seeMoreEl.innerHTML = '<img src="/image/processing.gif" alt="Please wait Icon" width="28" height="28">';
    getRatingSync(film, filmEl, false);
}

function convertSourceDataListToRs(sourceSearchResult, sourceName, limit) {
    var films = { "films":[] }.films;
    if (sourceName == SOURCE_OMDBAPI) {
        while (films.length < limit && sourceSearchResult.Search && sourceSearchResult.Search.length > films.length) {
            var sourceFilm = sourceSearchResult.Search[films.length];
            films[films.length] = convertSourceDataItemToRs(sourceFilm, sourceName);
        }
    
        if (sourceSearchResult.Title) {
            // The result is a single item
            films[films.length] = convertSourceDataItemToRs(sourceSearchResult, sourceName);
        }
    }
    else if (sourceName == SOURCE_TMDBAPI) {
        var results = [];
        var contentType = null;
        var searchResults = sourceSearchResult.results;
        var movieResults = sourceSearchResult.movie_results;
        var seriesResults = sourceSearchResult.movie_results;
        if (searchResults && searchResults != "undefined") {
            results = searchResults;
        }
        else if (movieResults && movieResults != "undefined" && movieResults.length > 0) {
            results = movieResults;
            contentType = "movie";
        }
        else if (seriesResults && seriesResults != "undefined" && seriesResults.length > 0) {
            results = seriesResults;
            contentType = "tv";
        }

        while (films.length < limit && results && results.length > films.length) {
            var sourceFilm = results[films.length];
            if (contentType && (!sourceFilm.media_type || sourceFilm.media_type == "undefined")) {
                sourceFilm.media_type = contentType;
            }
            films[films.length] = convertSourceDataItemToRs(sourceFilm, sourceName);
        }
    }

    return films;
}

function convertSourceDataItemToRs(sourceFilm, sourceName) {
    var rsFilm = {};
    if (sourceName == SOURCE_OMDBAPI) {

        // Film attrs all contentTypes use the same name
        var imdbId = sourceFilm.imdbID;
        rsFilm.image = sourceFilm.Poster;
        rsFilm.title = sourceFilm.Title;
        rsFilm.year = sourceFilm.Year;

        // Film attrs with name specific by contentType
        var contentType = sourceFilm.Type;
        if (contentType == "movie") {
            rsFilm.contentType = CONTENT_FILM;
        } else if (contentType == "series") {
            rsFilm.contentType = CONTENT_TV_SERIES;
        }

        // Source attrs
        rsFilm.sources = [
                          { "name": "OMDb",
                            "image": rsFilm.image,
                            "uniqueName": imdbId
                          },
                          { "name": "IMDb",
                            "image": rsFilm.image,
                            "uniqueName": imdbId
                          }
                         ];
    }
    else if (sourceName == SOURCE_TMDBAPI) {

        // Film attrs all contentTypes use the same name
        var sourceId = sourceFilm.id;
        rsFilm.image = sourceFilm.poster_path;

        // Film attrs with name specific by contentType
        var uniqueName = "";
        var contentType = sourceFilm.media_type;
        if (contentType == "movie") {
            rsFilm.contentType = CONTENT_FILM;
            uniqueName = "mv" + sourceId;
            rsFilm.title = sourceFilm.title;
            rsFilm.year = sourceFilm.release_date;
        } else if (contentType == "tv") {
            rsFilm.contentType = CONTENT_TV_SERIES;
            uniqueName = "tv" + sourceId;
            rsFilm.title = sourceFilm.name;
            rsFilm.year = sourceFilm.first_air_date;
        }
        
        // Parse year. Original format is yyyy-mm-dd.
        if (rsFilm.year) {
            rsFilm.year = rsFilm.year.substring(0, 4);
        }

        // Prepend the image base url to the image filename
        rsFilm.image = IMAGE_PATH_TMDBAPI + "/w92" + rsFilm.image;

        // Source attrs
        rsFilm.sources = [{ "name": "TMDb",
                            "image": rsFilm.image,
                            "uniqueName": uniqueName
                         }];
    }

    return rsFilm;
}

function getUniqueName(film, sourceName)
{
	var uniqueName = "";
    var source = film.sources.find( function (findSource) { return findSource.name == sourceName; } );
    if (source && source != "undefined") {
        uniqueName = source.uniqueName;
    }

    return uniqueName;
}