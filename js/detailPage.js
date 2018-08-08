
function getFilmForDetailPage(filmId, imdbUniqueName) {
    var params = "?action=getFilm";
    params = params + "&id=" + filmId;
    params = params + "&imdb=" + imdbUniqueName;
    params = params + "&rsonly=0";
	var xmlhttp = new XMLHttpRequest();
    var callbackHandler = function () { detailPageCallback(xmlhttp); };
    xmlhttp.onreadystatechange = callbackHandler;
	xmlhttp.open("GET", RS_URL_API + params, true);
    xmlhttp.send();
}

function detailPageCallback(xmlhttp) {
	if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
        var result = JSON.parse(xmlhttp.responseText);
        if (result.Success != "false" && result.filmId != "undefined") {
            var film = result;
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
                    titleEl.innerHTML = "<a href='/php/detail.php?i=" + film.parentId + "'>" + titleEl.innerHTML + "</a>";
                }
                
                getSeriesForDetailPage(film);
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
    var episodes = result.Episodes;
    for (var i = 0; i < episodes.length; i++) {
        var episode = episodes[i];
        
        rowEl = document.createElement("div");
        rowEl.setAttribute("class", "row");
        episodeEl = document.createElement("detail-episode");
        episodeEl.setAttribute("id", "episode-" + episode.Episode); // episode number

        numberEl = document.createElement("span");
        numberEl.innerHTML = episode.Episode + ". ";

        linkEl = document.createElement("a");
        linkEl.setAttribute("href", "/php/detail.php?imdb=" + episode.imdbID);
        linkEl.setAttribute("data-imdb", episode.imdbID);
        linkEl.innerHTML = episode.Title;
        
        episodesEl.appendChild(rowEl);
        rowEl.appendChild(episodeEl);
        episodeEl.appendChild(numberEl);
        episodeEl.appendChild(linkEl);
    }
}

function getEpisodeRatings(seasonJson) {
    var episodes = seasonJson.Episodes;
    if (!episodes || episodes.length == 0) {
        return;
    }
    var delim = "";
    var params = "?action=getFilms";
    params += "&imdb=";
    for (var i = 0; i < episodes.length; i++) {
        params += delim + episodes[i].imdbID;
        delim = "+";
    }
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

            var episodeEl = document.getElementById("episode-" + film.episodeNumber);
            if (episodeEl && yourScore) {
                var ratingEl = document.createElement("detail-episode-rating");
                ratingEl.innerHTML = "★" + yourScore;
                episodeEl.appendChild(ratingEl);
            }
        }
	}
}

function changeSeasonNum() {
    getEpisodesForDetailPage(contextData.seriesFilmId);
}