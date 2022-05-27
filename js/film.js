
function buildFilmDetailElement(film) {
    var filmId = getFilmId(film);
    var title = getFilmTitle(film);
    var year = getFilmYear(film);
    var episodeTitle = getFilmEpisodeTitle(film);
    var season = "";
    if (film.season) {
        season = "Season " + film.season;
    }
    var episodeNumber = "";
    if (film.episodeNumber) {
        episodeNumber = " - Episode " + film.episodeNumber;
    }

    var imdbRatingHtml = "";
    var imdb = film.sources.find( function (findSource) { return findSource.name == "IMDb"; } );
    if (imdb && imdb != "undefined" && imdb.uniqueName) {
        imdbUniqueName = imdb.uniqueName;

        var imdbScore = "";
        if (imdb.userScore) {
            imdbScore = imdb.userScore;
        }

        var el = '';
        el = el + '        <a href="'+IMDB_FILM_BASEURL + imdbUniqueName+'" target="_blank">\n';
        el = el + '          <img src="'+RS_URL_BASE + "/image/logo-rating-imdb.png"+'" alt="IMDb Rating" height="20px"/>\n';
        el = el + '          <imdbScore id="imdb-score-'+imdbUniqueName+'">'+imdbScore+'</imdbScore>\n'
        el = el + '        </a>\n'

        imdbRatingHtml = el;
    }

    var tmdbRatingHtml = "";
    var tmdb = film.sources.find( function (findSource) { return findSource.name == "TMDb"; } );
    if (tmdb && tmdb != "undefined" && tmdb.uniqueName) {
        tmdbUniqueName = tmdb.uniqueName.substring(2); 

        var tmdbScore = "";
        if (tmdb.userScore) {
            tmdbScore = tmdb.userScore;
        }

        var tmdbUrl = TMDB_FILM_BASEURL;
        if (film.contentType && film.contentType == CONTENT_TV_SERIES) {
            tmdbUrl = tmdbUrl + "tv/" + tmdbUniqueName;
        }
        else if (film.contentType && film.contentType == CONTENT_TV_EPISODE) {
            parentUniqueName = "null";
            if (tmdb.parentUniqueName) {
                parentUniqueName = tmdb.parentUniqueName.substring(2);
            }
            tmdbUrl = tmdbUrl + "tv/" + parentUniqueName + "/season/" + film.season + "/episode/" + film.episodeNumber;
        }
        else {
            tmdbUrl = tmdbUrl + "movie/" + tmdbUniqueName;
        }

        var el = '';
        el = el + '        <a href="'+tmdbUrl+'" target="_blank">\n';
        el = el + '          <img src="'+RS_URL_BASE + "/image/logo-rating-tmdb.png"+'" alt="TMDb Rating" height="20px"/>\n';
        el = el + '          <tmdbScore id="tmdb-score-'+tmdbUniqueName+'">'+tmdbScore+'</tmdbScore>\n'
        el = el + '        </a>\n'

        tmdbRatingHtml = el;
    }

    var thirdPartyBar = "";
    var sourceLogoClass = "source-logo-1";
    if (imdbRatingHtml != "") {
        thirdPartyBar = thirdPartyBar + '<div class="' + sourceLogoClass + '">\n';
        thirdPartyBar = thirdPartyBar + imdbRatingHtml + "\n";
        thirdPartyBar = thirdPartyBar + '</div>\n';

        sourceLogoClass = "source-logo-2";
    }
    if (tmdbRatingHtml != "") {
        thirdPartyBar = thirdPartyBar + '<div class="' + sourceLogoClass + '">\n';
        thirdPartyBar = thirdPartyBar + tmdbRatingHtml + "\n";
        thirdPartyBar = thirdPartyBar + '</div>\n';
    }

    var justWatchUrl = "https://www.justwatch.com/us/search?release_year_from="+year+"&release_year_until="+year+"&q=" + encodeURIComponent(title);
    var justWatchImage = RS_URL_BASE + "/image/logo-justwatch.png";

    var rsUniqueName = "";
    var dateStr = "";
    var rsSource = film.sources.find( function (findSource) { return findSource.name == "RatingSync"; } );
    if (rsSource && rsSource != "undefined") {
        rsUniqueName = rsSource.uniqueName;
        var yourRatingDate = rsSource.rating.yourRatingDate;
        dateStr = getRatingDateText(yourRatingDate);
    }

    var contentTypeText = "";
    if (film.contentType && film.contentType == CONTENT_TV_SERIES) {
        contentTypeText = " TV";
    }

    var historyHtml = "";
    historyHtml = historyHtml + '    <div class="btn-group rating-history">\n';
    historyHtml = historyHtml + '      <div class="d-flex flex-row">\n';
    historyHtml = historyHtml + '          <button type="button" class="rating-history btn pl-0 pr-1 py-0" disabled>\n';
    historyHtml = historyHtml + '            <rating-date class="small" id="rating-date-'+rsUniqueName+'">'+dateStr+'</rating-date>\n';
    historyHtml = historyHtml + '          </button>\n';
    historyHtml = historyHtml + '          <button type="button" class="rating-history btn-rating-history btn dropdown-toggle-split py-0 px-1 align-middle" id="dropdownMenuReference" data-toggle="dropdown" aria-expanded="false" data-reference="parent">\n';
    historyHtml = historyHtml + '            <span class="sr-only">Toggle Dropdown</span>\n';
    historyHtml = historyHtml + '            <span class="fas fa-caret-down" aria-hidden="true"></span>\n';
    historyHtml = historyHtml + '          </button>\n';
    historyHtml = historyHtml + '          <div id="rating-history-'+rsUniqueName+'" class="dropdown-menu" aria-labelledby="dropdownMenuReference">\n';
    historyHtml = historyHtml + '          </div>\n';
    historyHtml = historyHtml + '      </div>\n';
    historyHtml = historyHtml + '    </div>\n';

    var html = '\n';
    html = html + '  <div class="film-line"><span class="film-title">'+title+'</span> ('+year+')'+contentTypeText+'</div>\n';
    html = html + "  <episodeTitle class='tv-episode-title'>" + episodeTitle + "</episodeTitle>\n";
    html = html + "  <div><season class='tv-season'>" + season + "</season><episodeNumber class='tv-episodenum'>" + episodeNumber + "</episodeNumber></div>\n";
    html = html + '  <div id="action-area-'+rsUniqueName+'">\n';
    html = html + '    <div class="mt-n2 pt-2" style="line-height: 1">\n';
    html = html + '      <ratingStars class="rating-stars" id="rating-stars-'+rsUniqueName+'"></ratingStars>\n';
    html = html + '    </div>\n';
    html = html +      historyHtml;
    html = html + '    <div class="thirdparty-bar pb-1 mt-2">\n';
    html = html +        thirdPartyBar + '\n';
    html = html + '      <a href="'+justWatchUrl+'" target="_blank"><img src="'+justWatchImage+'" alt="JustWatch" height="20px"/></a>'
    html = html + '    </div>\n';
    html = html + '    <status></status>\n';
    html = html + '    <filmlistContainer id="filmlist-container-'+filmId+'" align="left"></filmlistContainer>\n';
    html = html + '    <streams id="streams-'+filmId+'" class="streams"></streams>\n';
    html = html + '  </div>\n';
    html = html + '  <div id="rate-confirmation-'+rsUniqueName+'" hidden>\n';
    html = html + '  </div>\n';

    var detailEl = document.createElement("detail");
    detailEl.innerHTML = html;

    return detailEl;
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
        var season = "";
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