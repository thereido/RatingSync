
var hideable = true;

// Needs "contextData" JSON in the page
function showFilmDetail(filmId) {
    var filmEl = document.getElementById("rating-detail");
    var film = contextData.films.find( function (findFilm) { return findFilm.filmId == filmId; } );
    renderFilmDetail(film, filmEl);
}

function renderFilmDetail(film, filmEl) {
    var image = RS_URL_BASE + film.image;
    var posterEl = document.createElement("poster");
    posterEl.innerHTML = '<img src="'+image+'" width="150px"/>';
    filmEl.innerHTML = "";
    filmEl.appendChild(posterEl);
    filmEl.appendChild(buildFilmDetailElement(film));

    renderStars(film);
    renderStreams(film, true);
    renderFilmlists(film.filmlists, film.filmId);
}

function hideFilmDetail() {
    if (hideable) {
        el = document.getElementById("rating-detail");
        el.innerHTML = "";
    }
}

function getRsRatings(pageSize, beginPage) {
    var params = "?action=getRatings";
    params = params + "&ps=" + pageSize;
    params = params + "&bp=" + beginPage;
    params = params + getFilterParams();
	var xmlhttp = new XMLHttpRequest();
    var callbackHandler = function () { getRsRatingsCallback(xmlhttp); };
    xmlhttp.onreadystatechange = callbackHandler;
	xmlhttp.open("GET", RS_URL_API + params, true);
	xmlhttp.send();
}

function getRsRatingsCallback(xmlhttp) {
    if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
	    contextData = JSON.parse(xmlhttp.responseText);
	    renderRatings();
	}
}

function renderRatings() {
    var films = contextData.films;
    var row = 0;
    var html = "\n";
    for (var filmIndex = 0; filmIndex < films.length; filmIndex++) {
        if (filmIndex % 12 == 0) {
            html = html + "<div class='row'>\n";
        }

        var film = films[filmIndex];
        var filmId = film.filmId;
        var title = film.title;
        var titleNoQuotes = title.replace(/\"/g, '\\\"').replace(/\'/g, "\\\'");
        var rsSource = film.sources.find( function (findSource) { return findSource.name == "RatingSync"; } );
        var image = RS_URL_BASE + rsSource.image;
        var showFilmDetailJS = "showFilmDetail(" + filmId + ")";
        var uniqueName = rsSource.uniqueName;
        var onClick = "onClick='" + showFilmDetailJS + "'";
        var onMouseEnter = "onMouseEnter='detailTimer = setTimeout(function () { " + showFilmDetailJS + "; }, 500)'";
        var onMouseLeave = "onMouseLeave='clearTimeout(detailTimer)'";
        html = html + "  <div class='col-xs-6 col-sm-4 col-md-3 col-lg-2' id='" + uniqueName + "'>\n";
        html = html + "    <poster id='poster-" + uniqueName + "' data-filmId='" + filmId + "'>\n";
        html = html + "      <img src='" + image + "' alt='" + titleNoQuotes + "' " + onClick + " " + onMouseEnter + " " + onMouseLeave + " />\n";
        html = html + "    </poster>\n";
        html = html + "  </div>\n";

        if (filmIndex % 12 == 11 || filmIndex == films.length-1) {
            html = html + "</div>\n";
        }
    }
    document.getElementById("film-table").innerHTML = html;

    renderPagination();
}

function changeContentTypeFilter() {
    getRsRatings(defaultPageSize, currentPageNum);
}