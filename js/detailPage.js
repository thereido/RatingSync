
function getFilmForDetailPage(filmId, uniqueName, imdbId, contentType, parentId, seasonNum, episodeNum) {
    const xmlhttp = new XMLHttpRequest();
    const callbackHandler = function () {
        detailPageCallback(xmlhttp);
    };
    getFilmFromRs(filmId, uniqueName, imdbId, contentType, parentId, seasonNum, episodeNum, xmlhttp, callbackHandler);
}

function detailPageCallback(xmlhttp) {
	if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
        const result = JSON.parse(xmlhttp.responseText);
        if (result.Success != "false" && result.filmId != "undefined") {
            const film = result;
            contextData.films.push(film);
            const filmEl = document.getElementById("detail-film");
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
                    const titleEl = document.getElementsByClassName("film-title")[0];
                    titleEl.innerHTML = "<a href='/php/detail.php?i=" + film.parentId + "&ct=" + CONTENT_TV_SERIES + "'>" + titleEl.innerHTML + "</a>";
                }
                
                getSeriesForDetailPage(film);
            } else {
                // For movies, get similar item for a other movies
                getSimilar(film);
            }
        }
	}
}

function getSeriesForDetailPage() {
    const seriesFilm = contextData.seriesFilm;
    const episodeFilm = contextData.episodeFilm;

    if (seriesFilm) {
        renderSeasons(seriesFilm);
        getEpisodesForDetailPage(seriesFilm.filmId);
    } else if (episodeFilm) {
        let params = "?action=getFilm";
        params = params + "&id=" + episodeFilm.parentId;
        params = params + "&ct=" + CONTENT_TV_SERIES;
        params = params + "&rsonly=1";
        const xmlhttp = new XMLHttpRequest();
        const callbackHandler = function () {
            detailPageSeriesCallback(xmlhttp);
        };
        xmlhttp.onreadystatechange = callbackHandler;
        xmlhttp.open("GET", RS_URL_API + params, true);
        xmlhttp.send();
    }
}

function detailPageSeriesCallback(xmlhttp) {
	if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
        const result = JSON.parse(xmlhttp.responseText);
        if (result.Success != "false" && result.filmId != "undefined") {
            const seriesFilm = result;
            renderSeasons(seriesFilm);
            getEpisodesForDetailPage(seriesFilm.filmId);
        }
    }
}

function renderSeasons(film) {
    const seasonsEl = document.getElementById("seasons");
    seasonsEl.removeAttribute("hidden");
    const seasonSelectEl = document.getElementById("seasonSel");
    var seasonCount = 0;

    for (let i = 0; i < film.seasonCount; i++) {
        var seasonCount = i + 1;
        const optionEl = document.createElement("option");
        const textnode = document.createTextNode(seasonCount);
        optionEl.appendChild(textnode);
        seasonSelectEl.appendChild(optionEl);
    }
    
    // Select to the episode's season
    const episodeFilm = contextData.episodeFilm;
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
    let params = "?action=getSeason";
    params = params + "&id=" + seriesFilmId;
    params = params + "&s=" + document.getElementById("seasonSel").value;
    const xmlhttp = new XMLHttpRequest();
    const callbackHandler = function () {
        detailPageEpisodesCallback(xmlhttp);
    };
    xmlhttp.onreadystatechange = callbackHandler;
	xmlhttp.open("GET", RS_URL_API + params, true);
    xmlhttp.send();
}

function detailPageEpisodesCallback(xmlhttp) {
	if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
        const result = JSON.parse(xmlhttp.responseText);
        if (result.Response != "False") {
            renderEpisodes(result);
            getEpisodeRatings(result);
        }
	}
}

function renderEpisodes(result) {
    const episodesEl = document.getElementById("episodes");
    episodesEl.innerHTML = "";
    const episodes = result.episodes;

    for (let i = 0; i < episodes.length; i++) {
        const episode = episodes[i];

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
        let episodeUrl = "/php/detail.php";
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
        const imageEl = document.createElement("img");
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
    const seasonNum = seasonJson.number;
    const episodes = seasonJson.episodes;
    if (!episodes || episodes.length == 0) {
        return;
    }
    let parentId = "";
    let params = "?action=getFilms";
    params += "&s=" + seasonNum;
    params += "&e="; // episode numbers for all episodes
    let delim = "";
    for (let i = 0; i < episodes.length; i++) {
        params += delim + episodes[i].number;
        delim = "+";
        if (!parentId || parentId == "" || parentId == "undefined") {
            parentId = episodes[i].seriesFilmId;
        }
    }
    params += "&pid=" + parentId;
    const xmlhttp = new XMLHttpRequest();
    const callbackHandler = function () {
        detailPageEpisodeRatingsCallback(xmlhttp);
    };
    xmlhttp.onreadystatechange = callbackHandler;
	xmlhttp.open("GET", RS_URL_API + params, true);
    xmlhttp.send();
}

function detailPageEpisodeRatingsCallback(xmlhttp) {
	if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
        const result = JSON.parse(xmlhttp.responseText);
        const films = result.films;

        for (let i=0; i<films.length; i++) {
            const film = films[i];

            let yourScore = "";
            const source = film.sources.find(function (findSource) {
                return findSource.name == "RatingSync";
            });
            if (source && source != "undefined") {
                if (source.rating && source.rating != "undefined") {
                    yourScore = source.rating.yourScore;
                }
            }

            const ratingEl = document.getElementById("detail-episode-rating-" + film.episodeNumber);
            if (ratingEl && yourScore) {
                ratingEl.innerHTML = "★" + yourScore;
            }
        }
	}
}

function changeSeasonNum() {
    getEpisodesForDetailPage(contextData.seriesFilmId);
}

function getSimilar(film) {
    let params = "?action=getSimilar";
    params = params + "&id=" + film.filmId;
    const xmlhttp = new XMLHttpRequest();
    const callbackHandler = function () {
        similarCallback(xmlhttp);
    };
    xmlhttp.onreadystatechange = callbackHandler;
    xmlhttp.open("GET", RS_URL_API + params, true);
    xmlhttp.send();
}

function similarCallback(xmlhttp) {
    if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
        const result = JSON.parse(xmlhttp.responseText);
        if (result.Response != "False") {
            contextData.similarFilms = result;
            renderSimilar();
        }
    }
}

function renderSimilar() {
    let similarFilms = contextData.similarFilms;
    const row = 0;
    let html = "\n";
    html = html + "<div class='row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-5 row-cols-xl-6' id='similar-row'>\n";
    for (let filmIndex = 0; filmIndex < similarFilms.length; filmIndex++) {
        const similarFilm = similarFilms[filmIndex];
        const uniqueName = similarFilm.uniqueName;

        // Title
        const title = similarFilm.title;
        const titleNoQuotes = title.replace(/\"/g, '\\\"').replace(/\'/g, "\\\'");

        // ContentType
        let contentTypeParam = "";
        if (similarFilm.contentType != "undefined") { contentTypeParam = "&ct=" + similarFilm.contentType; }

        // Image
        let image = "";
        if (similarFilm.poster) {
            image = IMAGE_PATH_TMDBAPI + "/w154" + similarFilm.poster;
        }

        // JavaScript
        const onMouseEnter = `onMouseEnter='detailTimer = setTimeout(function () { showFilmDropdownForSimilar(${uniqueName}); }, 500)'`;
        const onMouseLeave = `onMouseLeave='hideFilmDropdownForSimilar(${uniqueName}, detailTimer)'`;

        html = html + '  <filmItem class="col" id="' + uniqueName + '" data-unique-name="' + uniqueName + '">' + '\n';
        html = html + '    <div class="similar-film" id="similar-film-'+uniqueName+'" ' + onMouseEnter + ' ' + onMouseLeave + '>' + '\n';
        html = html + '      <poster id="poster-' + uniqueName + '" data-unique-name="' + uniqueName + '">' + '\n';
        html = html + '        <a href="/php/detail.php?sid=' + uniqueName + contentTypeParam + '">' + '\n';
        html = html + '          <img src="' + image + '" alt="' + titleNoQuotes + '" />' + '\n';
        html = html + '        </a>' + '\n';
        html = html + '        <div id="similarfilm-dropdown-' + uniqueName + '" class="film-dropdown-content"></div>' + '\n';
        html = html + '      </poster>' + '\n';
        html = html + '    </div>' + '\n';
        html = html + '  </filmItem>' + '\n';
    }
    html = html + '</div>' + '\n';
    document.getElementById("similar").innerHTML = html;

/*RT*
    sizeBreakpointCallback();

    renderPagination();
*RT*/
}

// Needs "contextData" JSON in the page
function showFilmDropdownForSimilar(uniqueName) {
    const filmIndex = contextData.similarFilms.findIndex(function (findFilm) { return findFilm.uniqueName == uniqueName; });

    if (filmIndex != -1) {
        const similarFilm = contextData.similarFilms[filmIndex];
        const dropdownEl = document.getElementById("similarfilm-dropdown-" + uniqueName);
        renderSimilarDetail(similarFilm, dropdownEl);

        // Change the style classes on posters for episodes
        const filmEl = document.getElementById("similar-film-" + uniqueName);
        const posterEl = document.getElementById("poster-" + uniqueName);

        // Resize the poster to match the dropdown. Sometimes the dropdown is taller
        // than the poster.
        const posterHeight = posterEl.getBoundingClientRect().height;
        const dropdownHeight = dropdownEl.getBoundingClientRect().height;
        if (dropdownHeight - 10 > posterHeight) {
            const newPosterHeight = dropdownHeight - 10;
            posterEl.setAttribute("style", "height: " + newPosterHeight + "px");
        }
    }
}

function hideFilmDropdownForSimilar(uniqueName, detailTimer) {
    el = document.getElementById("similarfilm-dropdown-" + uniqueName);
    el.style.display = "none";
    clearTimeout(detailTimer);

    const filmEl = document.getElementById("similar-film-" + uniqueName);
    const posterEl = document.getElementById("poster-" + uniqueName);
}

function renderSimilarDetail(similarFilm, dropdownEl) {
    dropdownEl.innerHTML = "";
    dropdownEl.appendChild(buildSimilarElement(similarFilm));
    dropdownEl.style.display = "block";
}