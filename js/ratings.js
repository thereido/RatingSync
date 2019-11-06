
var hideable = true;

// Needs "contextData" JSON in the page
function showFilmDetail(filmId) {
    var dropdownEl = document.getElementById("film-dropdown-" + filmId);
    var film = contextData.films.find( function (findFilm) { return findFilm.filmId == filmId; } );
    renderFilmDetail(film, dropdownEl);
}

function renderFilmDetail(film, dropdownEl) {
    dropdownEl.innerHTML = "";
    dropdownEl.appendChild(buildFilmDetailElement(film));
    dropdownEl.style.display = "block";

    renderStars(film);
    renderStreams(film, true);
    renderFilmlists(film.filmlists, film.filmId);
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

        var column = (filmIndex + 1) % 12;
        if (column == 0) {
            column = 12;
        }

        var film = films[filmIndex];
        var filmId = film.filmId;
        var title = film.title;
        var titleNoQuotes = title.replace(/\"/g, '\\\"').replace(/\'/g, "\\\'");
        var contentTypeParam = "";
        if (film.contentType != "undefined") { contentTypeParam = "&ct=" + film.contentType; }
        var rsSource = film.sources.find( function (findSource) { return findSource.name == "RatingSync"; } );
        var image = RS_URL_BASE + rsSource.image;
        var showFilmDetailJS = "showFilmDetail(" + filmId + ")";
        var uniqueName = rsSource.uniqueName;
        var onClick = "onClick='" + showFilmDetailJS + "'";
        var onMouseEnter = "onMouseEnter='detailTimer = setTimeout(function () { " + showFilmDetailJS + "; }, 500)'";
        var onMouseLeave = "onMouseLeave='hideFilmDropdownForUserlist(" + filmId + ", detailTimer)'";
        html = html + "  <div class='col-xs-6 col-sm-4 col-md-3 col-lg-2' id='" + uniqueName + "'>\n";
        html = html + "    <div class='userlist-film' " + onMouseEnter + " " + onMouseLeave + ">\n";
        html = html + "      <poster id='poster-" + uniqueName + "' data-filmId='" + filmId + "'>\n";
        html = html + "        <a href='/php/detail.php?i=" + filmId + contentTypeParam + "'>\n";
        html = html + "          <img src='" + image + "' alt='" + titleNoQuotes + "' " + onClick + " />\n";
        html = html + "        </a>\n";
        html = html + "        <div id='film-dropdown-" + filmId + "' class='film-dropdown-content film-dropdown-col-" + column + "'></div>\n";
        html = html + "      </poster>\n";
        html = html + "    </div>\n";
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