
function buildFilmDetailElement(film) {
    var filmId = "";
    if (film.filmId) {
        filmId = film.filmId;
    }
    var title = "";
    if (film.title) {
        title = film.title;
    }
    var year = "";
    if (film.year) {
        year = film.year;
    }
    var episodeTitle = "";
    if (film.episodeTitle) {
        episodeTitle = film.episodeTitle;
    }
    var season = "";
    if (film.season) {
        season = "Season " + film.season;
    }
    var episodeNumber = "";
    if (film.episodeNumber) {
        episodeNumber = " - Episode " + film.episodeNumber;
    }

    var imdbUniqueName = "";
    var imdbLabel = "IMDb";
    var imdbFilmUrl = "";
    var imdbScore = "";
    var imdb = film.sources.find( function (findSource) { return findSource.name == "IMDb"; } );
    if (imdb && imdb != "undefined") {
        imdbUniqueName = imdb.uniqueName;
        imdbFilmUrl = IMDB_FILM_BASEURL + imdbUniqueName;
        imdbLabel = "IMDb";
        if (imdb.userScore) {
            imdbScore = imdb.userScore;
        }
    }

    var rsUniqueName = "";
    var dateStr = "";
    var rsSource = film.sources.find( function (findSource) { return findSource.name == "RatingSync"; } );
    if (rsSource && rsSource != "undefined") {
        rsUniqueName = rsSource.uniqueName;
        var yourRatingDate = rsSource.rating.yourRatingDate;
        if (yourRatingDate && yourRatingDate != "undefined") {
            var reDate = new RegExp("([0-9]+)-([0-9]+)-([0-9]+)");
            var ratingYear = reDate.exec(yourRatingDate)[1];
            var month = reDate.exec(yourRatingDate)[2];
            var day = reDate.exec(yourRatingDate)[3];
            dateStr = "You rated this " + month + "/" + day + "/" + ratingYear;
        }
    }

    var html = '\n';
    html = html + '  <div class="film-line"><span class="film-title">'+title+'</span> ('+year+')</div>\n';
    html = html + "  <episodeTitle class='tv-episode-title'>" + episodeTitle + "</episodeTitle>\n";
    html = html + "  <div><season class='tv-season'>" + season + "</season><episodeNumber class='tv-episodenum'>" + episodeNumber + "</episodeNumber></div>\n";
    html = html + '  <div align="left">\n';
    html = html + '    <ratingStars class="rating-stars" id="rating-stars-'+rsUniqueName+'"></ratingStars>\n';
    html = html + '  </div>\n';
    html = html + '  <ratingDate class="rating-date">'+dateStr+'</ratingDate>\n';
    html = html + '  <div><a href="'+imdbFilmUrl+'" target="_blank">'+imdbLabel+':</a>&nbsp;<imdbScore id="imdb-score-"'+imdbUniqueName+'>'+imdbScore+'</imdbScore></div>\n';
    html = html + '  <status></status>\n';
    html = html + '  <filmlistContainer id="filmlist-container-'+filmId+'" align="left"></filmlistContainer>\n';
    html = html + '  <streams id="streams-'+filmId+'" class="streams"></streams>\n';
    
    var detailEl = document.createElement("detail");
    detailEl.innerHTML = html;

    return detailEl;
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
    
    var html = "";
    html = html + "<div class='btn-group-vertical film-filmlists'>\n";
    html = html + "  <button class='btn btn-sm btn-primary' onClick='toggleFilmlist(\""+defaultListHtmlSafe+"\", "+filmId+", \"filmlist-btn-default-"+filmId+"\")' id='filmlist-btn-default-"+filmId+"' data-listname='"+defaultList+"' type='button'>\n";
    html = html + "    <span class='"+defaultListClass+"' id='filmlist-checkmark-"+filmId+"'></span> "+defaultList+"\n";
    html = html + "  </button>\n";
    html = html + "  <div class='btn-group'>\n";
    html = html + "    <button class='btn btn-sm btn-primary dropdown-toggle' id='filmlist-btn-others-"+filmId+"' data-toggle='dropdown' type='button'>\n";
    html = html + "      More lists <span class='caret'></span>\n";
    html = html + "    </button>";
    html = html + "    <ul class='dropdown-menu' id='filmlists-"+filmId+"' role='menu'  >\n";
    html = html +        listItemsHtml + "\n";
    html = html + "      <li class='divider'></li>\n";
    html = html + "      <li><a href='/php/userlist.php?id="+filmId+"'>New list</a></li>\n";
    html = html + "    </ul>\n";
    html = html + "  </div>\n";
    html = html + "</div>\n";

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

            html = html + "      <li class='filmlist' id='filmlist-"+listnameHtmlSafe+"-"+filmId+"'>\n";
            html = html + "        <a href='#' onClick='toggleFilmlist(\""+listnameHtmlSafe+"\", "+filmId+", \"filmlist-btn-"+listnameHtmlSafe+"-"+filmId+"\")' id='filmlist-btn-"+listnameHtmlSafe+"-"+filmId+"'>"+prefix+"<span class='"+checkmarkClass+"' id='filmlist-checkmark-"+filmId+"'></span> "+currentUserlist+"</a>\n";
            html = html + "      </li>\n";

            html = html + renderFilmlistItems(userlists[x].children, includedListnames, filmId, prefix + "&nbsp;&nbsp;&nbsp;&nbsp;");
        }
    }

    return html;
}