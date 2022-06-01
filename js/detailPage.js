
function getFilmForDetailPage(filmId, uniqueName, imdbId, contentType, parentId, seasonNum, episodeNum) {
    var xmlhttp = new XMLHttpRequest();
    var callbackHandler = function () { detailPageCallback(xmlhttp); };
    getFilmFromRs(filmId, uniqueName, imdbId, contentType, parentId, seasonNum, episodeNum, xmlhttp, callbackHandler);
}

function detailPageCallback(xmlhttp) {
	if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
        var result = JSON.parse(xmlhttp.responseText);
        if (result.Success != "false" && result.filmId != "undefined") {
            var film = result;
            contextData.films.push(film);
            var filmEl = document.getElementById("detail-film");
            renderRsFilmDetails(film, filmEl);

            if (film.contentType == "TvSeries" || film.contentType == "TvEpisode") {
                // Set context data.  Series and Episode filmIds and film object.
                if (film.contentType == "TvSeries") {
                    // Series
                    contextData.seriesFilmId = film.filmId;
                    contextData.seriesFilm = film;
                } else {
                    // Episode
                    contextData.seriesFilmId = film.parentId;
                    contextData.episodeFilmId = film.filmId;
                    contextData.episodeFilm = film;

                    // Made the series title be a link
                    var titleEl = document.getElementsByClassName("film-title")[0];
                    titleEl.innerHTML = "<a href='/php/detail.php?i=" + film.parentId + "&ct=" + CONTENT_TV_SERIES + "'>" + titleEl.innerHTML + "</a>";
                }

                if (pageId != SITE_PAGE.Edit) {
                    getSeriesForDetailPage(film);
                }
            }
        }
	}
}

function getSeriesForDetailPage() {
    var seriesFilm = contextData.seriesFilm;
    var episodeFilm = contextData.episodeFilm;

    if (seriesFilm) {
        renderSeasons(seriesFilm);
        getEpisodesForDetailPage(seriesFilm.filmId);
    } else if (episodeFilm) {
        var params = "?action=getFilm";
        params = params + "&id=" + episodeFilm.parentId;
        params = params + "&ct=" + CONTENT_TV_SERIES;
        params = params + "&rsonly=1";
        var xmlhttp = new XMLHttpRequest();
        var callbackHandler = function () { detailPageSeriesCallback(xmlhttp); };
        xmlhttp.onreadystatechange = callbackHandler;
        xmlhttp.open("GET", RS_URL_API + params, true);
        xmlhttp.send();
    }
}

function detailPageSeriesCallback(xmlhttp) {
	if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
        var result = JSON.parse(xmlhttp.responseText);
        if (result.Success != "false" && result.filmId != "undefined") {
            var seriesFilm = result;
            renderSeasons(seriesFilm);
            getEpisodesForDetailPage(seriesFilm.filmId);
        }
    }
}

function renderSeasons(film) {
    var seasonsEl = document.getElementById("seasons");
    seasonsEl.removeAttribute("hidden");
    var seasonSelectEl = document.getElementById("seasonSel");
    var seasonCount = 0;

    for (var i = 0; i < film.seasonCount; i++) {
        var seasonCount = i + 1;
        var optionEl = document.createElement("option");
        var textnode = document.createTextNode(seasonCount);
        optionEl.appendChild(textnode);
        seasonSelectEl.appendChild(optionEl);
    }
    
    // Select to the episode's season
    var episodeFilm = contextData.episodeFilm;
    if (episodeFilm && episodeFilm.season > 0 && episodeFilm.season <= seasonCount) {
        seasonSelectEl.value = episodeFilm.season;
    } else {
        if (seasonNumParam == "") {
            seasonNumParam = 1;
        }
        seasonSelectEl.value = seasonNumParam;
    }
}

function getEpisodesForDetailPage(seriesFilmId) {
    var params = "?action=getSeason";
    params = params + "&id=" + seriesFilmId;
    params = params + "&s=" + document.getElementById("seasonSel").value;
	var xmlhttp = new XMLHttpRequest();
    var callbackHandler = function () { detailPageEpisodesCallback(xmlhttp); };
    xmlhttp.onreadystatechange = callbackHandler;
	xmlhttp.open("GET", RS_URL_API + params, true);
    xmlhttp.send();
}

function detailPageEpisodesCallback(xmlhttp) {
	if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
        var result = JSON.parse(xmlhttp.responseText);
        if (result.Response != "False") {
            renderEpisodes(result);
            getEpisodeRatings(result);
        }
	}
}

function renderEpisodes(result) {
    var episodesEl = document.getElementById("episodes");
    episodesEl.innerHTML = "";
    var episodes = result.episodes;

    for (var i = 0; i < episodes.length; i++) {
        var episode = episodes[i];

        // Create elements
        // episodes -> row -> episode -> link -> number, poster, detail
        rowEl = document.createElement("div");
        episodeEl = document.createElement("detail-episode");
        linkEl = document.createElement("a");
        numberEl = document.createElement("detail-episodeNumber");
        posterEl = document.createElement("episodeListPoster");
        episodeDetailEl = document.createElement("detail");
        
        // Append elements into the episodes element
        episodesEl.appendChild(rowEl);
        rowEl.appendChild(episodeEl);
        episodeEl.appendChild(linkEl);
        linkEl.appendChild(numberEl);
        linkEl.appendChild(posterEl);
        linkEl.appendChild(episodeDetailEl);
        
        // Row attrs
        rowEl.setAttribute("class", "row");

        // Link attrs
        var episodeUrl = "/php/detail.php";
        episodeUrl = episodeUrl + "?sid=" + episode.sourceId;
        episodeUrl = episodeUrl + "&ct=" + CONTENT_TV_EPISODE;
        episodeUrl = episodeUrl + "&pid=" + episode.seriesFilmId;
        episodeUrl = episodeUrl + "&season=" + episode.seasonNum;
        episodeUrl = episodeUrl + "&en=" + episode.number;
        linkEl.setAttribute("href", episodeUrl);
        linkEl.setAttribute("data-imdb", episode.sourceId);
        
        // Episode attrs
        episodeEl.setAttribute("class", "col-xl-6 col-lg-8 col-md-10 col-sm-12 px-0 mx-2");
        episodeEl.setAttribute("id", "episode-" + episode.number); // episode number
        
        // Number attrs
        numberEl.innerHTML = "" + episode.number;

        // Poster attrs
        var imageEl = document.createElement("img");
        if (episode.image) {
            imageEl.setAttribute("class", "img-episode");
        }
        imageEl.setAttribute("src", IMAGE_PATH_TMDBAPI + "/w92/" +episode.image);
        posterEl.appendChild(imageEl);

        // Detail attrs
        // detail -> title, rating
        episodeTitleEl = document.createElement("div");
        episodeTitleEl.innerHTML = episode.title;
        ratingEl = document.createElement("detail-episode-rating");
        ratingEl.setAttribute("id", "detail-episode-rating-" + episode.number);
        episodeDetailEl.appendChild(episodeTitleEl);
        episodeDetailEl.appendChild(ratingEl);
    }
}

function getEpisodeRatings(seasonJson) {
    var seasonNum = seasonJson.number;
    var episodes = seasonJson.episodes;
    if (!episodes || episodes.length == 0) {
        return;
    }
    var parentId = "";
    var params = "?action=getFilms";
    params += "&s=" + seasonNum;
    params += "&e="; // episode numbers for all episodes
    var delim = "";
    for (var i = 0; i < episodes.length; i++) {
        params += delim + episodes[i].number;
        delim = "+";
        if (!parentId || parentId == "" || parentId == "undefined") {
            parentId = episodes[i].seriesFilmId;
        }
    }
    params += "&pid=" + parentId;
	var xmlhttp = new XMLHttpRequest();
    var callbackHandler = function () { detailPageEpisodeRatingsCallback(xmlhttp); };
    xmlhttp.onreadystatechange = callbackHandler;
	xmlhttp.open("GET", RS_URL_API + params, true);
    xmlhttp.send();
}

function detailPageEpisodeRatingsCallback(xmlhttp) {
	if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
	    var result = JSON.parse(xmlhttp.responseText);
        var films = result.films;

        for (var i=0; i<films.length; i++) {
            var film = films[i];

            var yourScore = "";
            var source = film.sources.find( function (findSource) { return findSource.name == "RatingSync"; } );
            if (source && source != "undefined") {
                if (source.rating && source.rating != "undefined") {
                    yourScore = source.rating.yourScore;
                }
            }

            var ratingEl = document.getElementById("detail-episode-rating-" + film.episodeNumber);
            if (ratingEl && yourScore) {
                ratingEl.innerHTML = "★" + yourScore;
            }
        }
	}
}

function changeSeasonNum() {
    getEpisodesForDetailPage(contextData.seriesFilmId);
}