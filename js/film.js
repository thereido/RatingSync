
function buildFilmDetailElement(film) {
    const filmId = getFilmId(film);
    const title = getFilmTitle(film);
    const year = getFilmYear(film);

    let imdbRatingEl = null;
    const imdb = film.sources.find( function (findSource) { return findSource.name == "IMDb"; } );
    if (imdb && imdb != "undefined" && imdb.uniqueName) {
        const imdbUniqueName = imdb.uniqueName;

        let imdbScore = "";
        if (imdb.userScore) {
            imdbScore = imdb.userScore;
        }

        imdbRatingEl = document.createElement("a");
        const imdbImgEl = document.createElement("img");
        const imdbScoreEl = document.createElement("imdbScore");

        imdbRatingEl.setAttribute("href", IMDB_FILM_BASEURL + imdbUniqueName);
        imdbRatingEl.setAttribute("target", "_blank");
        imdbImgEl.setAttribute("src", `${RS_URL_BASE}/image/logo-rating-imdb.png`);
        imdbImgEl.setAttribute("alt", "IMDb Rating");
        imdbImgEl.setAttribute("height", "20px");
        imdbScoreEl.setAttribute("id", `imdb-score-${imdbUniqueName}`);
        imdbScoreEl.innerText = imdbScore;

        imdbRatingEl.appendChild(imdbImgEl);
        imdbRatingEl.appendChild(imdbScoreEl);
    }

    let tmdbRatingEl = null;
    const tmdb = film.sources.find( function (findSource) { return findSource.name == "TMDb"; } );
    if (tmdb && tmdb != "undefined" && tmdb.uniqueName) {
        const tmdbUniqueName = tmdb.uniqueName.substring(2);

        let tmdbScore = "";
        if (tmdb.userScore) {
            tmdbScore = tmdb.userScore;
        }

        let tmdbUrl = TMDB_FILM_BASEURL;
        if (film.contentType && film.contentType == CONTENT_TV_SERIES) {
            tmdbUrl = tmdbUrl + "tv/" + tmdbUniqueName;
        }
        else if (film.contentType && film.contentType == CONTENT_TV_EPISODE) {
            let parentUniqueName = "null";
            if (tmdb.parentUniqueName) {
                parentUniqueName = tmdb.parentUniqueName.substring(2);
            }
            tmdbUrl = tmdbUrl + "tv/" + parentUniqueName + "/season/" + film.season + "/episode/" + film.episodeNumber;
        }
        else {
            tmdbUrl = tmdbUrl + "movie/" + tmdbUniqueName;
        }

        tmdbRatingEl = document.createElement("a");
        const tmdbImgEl = document.createElement("img");
        const tmdbScoreEl = document.createElement("tmdbScore");

        tmdbRatingEl.setAttribute("href", tmdbUrl);
        tmdbRatingEl.setAttribute("target", "_blank");
        tmdbImgEl.setAttribute("src", `${RS_URL_BASE}/image/logo-rating-tmdb.png`);
        tmdbImgEl.setAttribute("alt", "TMDb Rating");
        tmdbImgEl.setAttribute("height", "20px");
        tmdbScoreEl.setAttribute("id", `tmdb-score-${tmdbUniqueName}`);
        tmdbScoreEl.innerText = tmdbScore;

        tmdbRatingEl.appendChild(tmdbImgEl);
        tmdbRatingEl.appendChild(tmdbScoreEl);
    }

    let thirdpartyBarEl = document.createElement("div");
    let thirdpartySlot0El = document.createElement("div");
    let thirdpartySlot1El = document.createElement("div");
    thirdpartySlot0El.setAttribute("class", "source-logo-1");
    thirdpartySlot1El.setAttribute("class", "source-logo-2");

    let nextSlotEl = thirdpartySlot0El;
    if (imdbRatingEl != null) {
        nextSlotEl.appendChild(imdbRatingEl);
        thirdpartyBarEl.appendChild(nextSlotEl);
        thirdpartyBarEl.append("\n");

        nextSlotEl = thirdpartySlot1El;
    }
    if (tmdbRatingEl != null) {
        nextSlotEl.appendChild(tmdbRatingEl);
        thirdpartyBarEl.appendChild(nextSlotEl);
        thirdpartyBarEl.append("\n");
    }

    const justWatchUrl = "https://www.justwatch.com/us/search?release_year_from=" + year + "&release_year_until=" + year + "&q=" + encodeURIComponent(title);
    const justWatchImage = RS_URL_BASE + "/image/logo-justwatch.png";

    let rsUniqueName = "";
    const rsSource = getSourceJson(film, "RatingSync");
    if (rsSource && rsSource != "undefined") {
        rsUniqueName = rsSource.uniqueName;
    }

    const detailEl = document.createElement("detail");
    const actionAreaEl = document.createElement("div");
    const starsContainerEl = document.createElement("div");
    const starsEl = buildRatingElement(film);
    const historyEl = buildViewingHistoryElement(film);
    const justwatchLinkEl = document.createElement("a");
    const justwatchImgEl = document.createElement("img");
    const statusEl = document.createElement("status");
    const filmlistEl = document.createElement("filmlistContainer");
    const streamsEl = document.createElement("streams");
    const rateConfirmEl = document.createElement("div");

    starsContainerEl.setAttribute("class", "mt-n2 pt-2");
    thirdpartyBarEl.setAttribute("class", "thirdparty-bar pb-1");
    streamsEl.setAttribute("class", "streams");

    actionAreaEl.setAttribute("id", `action-area-${rsUniqueName}`);
    starsContainerEl.setAttribute("style", "line-height: 1");
    thirdpartyBarEl.setAttribute("id", `thirdparty-bar-${rsUniqueName}`);
    justwatchLinkEl.setAttribute("href", justWatchUrl);
    justwatchLinkEl.setAttribute("target", "_blank");
    justwatchImgEl.setAttribute("src", justWatchImage);
    justwatchImgEl.setAttribute("alt", "JustWatch");
    justwatchImgEl.setAttribute("height", "20px");
    filmlistEl.setAttribute("id", `filmlist-container-${filmId}`);
    filmlistEl.setAttribute("align", "left");
    streamsEl.setAttribute("id", `streams-${filmId}`);
    rateConfirmEl.setAttribute("id", `rate-confirmation-${rsUniqueName}`);
    rateConfirmEl.setAttribute("hidden", "true");

    detailEl.appendChild( buildTitleLineElement(film) );
    detailEl.appendChild( buildEpisodeTitleLineElement(film) );
    detailEl.appendChild( buildSeasonLineElement(film) );
    detailEl.appendChild(actionAreaEl);
    detailEl.appendChild(rateConfirmEl);
    actionAreaEl.appendChild(starsContainerEl);
    actionAreaEl.appendChild(historyEl);
    actionAreaEl.appendChild(thirdpartyBarEl);
    actionAreaEl.appendChild(statusEl);
    actionAreaEl.appendChild(filmlistEl);
    actionAreaEl.appendChild(streamsEl);
    starsContainerEl.appendChild(starsEl);
    thirdpartyBarEl.appendChild(justwatchLinkEl);
    justwatchLinkEl.appendChild(justwatchImgEl);
    // IMDb and TMDb are appended to thirdpartyBarEl above

    return detailEl;
}

function buildTitleLineElement(film) {
    const title = getFilmTitle(film);
    const year = getFilmYear(film);

    const filmLineEl = document.createElement("div");
    const filmTitleEl = document.createElement("span");
    const filmYearEl = document.createElement("span");

    filmLineEl.setAttribute("class", "film-line");
    filmTitleEl.setAttribute("class", "film-title");

    let contentTypeText = "";
    if (film.contentType && film.contentType == CONTENT_TV_SERIES) {
        contentTypeText = " TV";
    }

    filmTitleEl.innerText = title;
    filmYearEl.innerText = ` (${year})${contentTypeText}`;

    filmLineEl.appendChild(filmTitleEl);
    filmLineEl.appendChild(filmYearEl);

    return filmLineEl;
}

function buildEpisodeTitleLineElement(film) {
    const episodeTitleEl = document.createElement("episodeTitle");
    episodeTitleEl.setAttribute("class", "tv-episode-title");
    episodeTitleEl.innerText = getFilmEpisodeTitle(film);

    return episodeTitleEl;
}

function buildSeasonLineElement(film) {
    let season = getFilmSeason(film)
    if (season.length > 0) {
        season = "Season " + season;
    }
    let episodeNumber = getFilmEpisodeNum(film);
    if (episodeNumber) {
        episodeNumber = " - Episode " + episodeNumber;
    }

    const seasonEpisodeLineEl = document.createElement("div");
    const seasonEl = document.createElement("season");
    const episodeNumberEl = document.createElement("episodeNumber");

    seasonEl.setAttribute("class", "tv-season");
    episodeNumberEl.setAttribute("class", "tv-episodenum");

    seasonEl.innerText = season;
    episodeNumberEl.innerText = episodeNumber;

    seasonEpisodeLineEl.appendChild(seasonEl);
    seasonEpisodeLineEl.appendChild(episodeNumberEl);

    return seasonEpisodeLineEl;
}

    function getFilmId(film) {
        var filmId = "";
        if (film.filmId) {
            filmId = film.filmId;
        }

        return filmId;
    }

    function getFilmParentId(film) {
        var parentId = "";
        if (film.parentId) {
            parentId = film.parentId;
        }

        return parentId;
    }

    function getFilmContentType(film) {
        var contentType = "";
        if (film.contentType) {
            contentType = film.contentType;
        }

        return contentType;
    }

    function getFilmTitle(film) {
        var title = "";
        if (film.title) {
            title = film.title;
        }

        return title;
    }

    function getFilmYear(film) {
        var year = "";
        if (film.year) {
            year = film.year;
        }

        return year;
    }

    function getFilmSeason(film) {
        let season = "";
        if (film.season) {
            season = film.season;
        }

        return season;
    }

    function getFilmEpisodeTitle(film) {
        var episodeTitle = "";
        if (film.episodeTitle) {
            episodeTitle = film.episodeTitle;
        }

        return episodeTitle;
    }

    function getFilmEpisodeNum(film) {
        var episodeNumber = "";
        if (film.episodeNumber) {
            episodeNumber = film.episodeNumber;
        }

        return episodeNumber;
    }

    function getSourceJson(film, sourceName) {
        if (!film || !(film.sources)) {
            return null;
        }

        return film.sources.find(
            function (findSource) {
                return findSource.name == sourceName;
            }
        );
    }

    // userlist (JSON) - all of the user's filmlists
    // listnames - lists this film belongs in
    function renderFilmlists(includedListnames, filmId) {
        if (!userlistsJson) {
            renderFilmlistsHandler = function () { renderFilmlists(includedListnames, filmId); };
            getFilmlists(renderFilmlistsHandler);
            return;
        }

        var defaultList = getDefaultList();
        var defaultListHtmlSafe = defaultList;
        var defaultListClass = getCheckmarkClass(false);
        if (includedListnames === undefined) {
            includedListnames = [];
        }
        if (-1 != includedListnames.indexOf(defaultList)) {
            defaultListClass = getCheckmarkClass(true);
        }

        var userlists = JSON.parse(userlistsJson);
        listItemsHtml = renderFilmlistItems(userlists, includedListnames, filmId, "");

        var html = '';
        html = html + '<div class="btn-group-vertical film-filmlists">' + "\n";
        html = html + '  <button class="btn btn-sm btn-primary" onClick="toggleFilmlist(\''+defaultListHtmlSafe+'\', '+filmId+', \'filmlist-btn-default-'+filmId+'\')" id="filmlist-btn-default-'+filmId+'" data-listname="'+defaultList+'" type="button">' + "\n";
        html = html + '    <span class="'+defaultListClass+'" id="filmlist-checkmark-'+filmId+'"></span> '+defaultList+ "\n";
        html = html + '  </button>' + "\n";
        html = html + '  <div class="btn-group">' + "\n";
        html = html + '    <button class="btn btn-sm btn-primary dropdown-toggle" id="filmlist-btn-others-'+filmId+'" data-toggle="dropdown" type="button" aria-haspopup="true" aria-expanded="false">' + "\n";
        html = html + '      More lists <span class="caret"></span>' + "\n";
        html = html + '    </button>' + "\n";
        html = html + '    <div class="dropdown-menu" id="filmlists-'+filmId+'">' + "\n";
        html = html +        listItemsHtml + "\n";
        html = html + '      <div class="dropdown-divider"></div>' + "\n";
        html = html + '      <a class="dropdown-item" href="/php/managelists.php?nl=1&id='+filmId+'">New list</a>' + "\n";
        html = html + '    </div>' + "\n";
        html = html + '  </div>' + "\n";
        html = html + '</div>' + "\n";

        var container = document.getElementById("filmlist-container-"+filmId);
        container.innerHTML = html;
        addFilmlistListeners(container, filmId);
    }

    function renderFilmlistItems(userlists, includedListnames, filmId, prefix) {
        var html = "";
        for (var x = 0; x < userlists.length; x++) {
            var currentUserlist = userlists[x].listname;
            if (currentUserlist != getDefaultList()) {
                listnameHtmlSafe = currentUserlist;
                var checkmarkClass = getCheckmarkClass(false);
                if (-1 != includedListnames.indexOf(currentUserlist)) {
                    checkmarkClass = getCheckmarkClass(true);
                }

                html = html + "      <a class='dropdown-item' href='#' onClick='toggleFilmlist(\""+listnameHtmlSafe+"\", "+filmId+", \"filmlist-btn-"+listnameHtmlSafe+"-"+filmId+"\")' id='filmlist-btn-"+listnameHtmlSafe+"-"+filmId+"'>"+prefix+"<span class='"+checkmarkClass+"' id='filmlist-checkmark-"+filmId+"'></span> "+currentUserlist+"</a>\n";

                html = html + renderFilmlistItems(userlists[x].children, includedListnames, filmId, prefix + "&nbsp;&nbsp;&nbsp;&nbsp;");
            }
        }

        return html;
    }