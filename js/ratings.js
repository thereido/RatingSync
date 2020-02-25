
var hideable = true;

// Needs "contextData" JSON in the page
function showFilmDetail(filmId) {
    var dropdownEl = document.getElementById("film-dropdown-" + filmId);
    var filmIndex = contextData.films.findIndex( function (findFilm) { return findFilm.filmId == filmId; } );
    if (filmIndex != -1) {
        var film = contextData.films[filmIndex];
        renderFilmDetail(film, dropdownEl);

        // If the default source has no data for this film get it now
        var defaultSource = film.sources.find( function (findSource) { return findSource.name == DATA_API_DEFAULT; } );
        if (!defaultSource || defaultSource == "undefined") {
            getFilmForDropdown(film);
        }
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

        var column = (filmIndex + 1) % 12;
        if (column == 0) {
            column = 12;
        }

        var film = films[filmIndex];
        var filmId = film.filmId;
        var rsSource = film.sources.find( function (findSource) { return findSource.name == "RatingSync"; } );
        var uniqueName = rsSource.uniqueName;

        // Title
        var title = film.title;
        var titleNoQuotes = title.replace(/\"/g, '\\\"').replace(/\'/g, "\\\'");

        // ContentType
        var contentTypeParam = "";
        if (film.contentType != "undefined") { contentTypeParam = "&ct=" + film.contentType; }

        // Image
        var image = "";
        var imageClass = "";
        if (rsSource.image) {
            var image = RS_URL_BASE + rsSource.image;

            if (film.contentType == CONTENT_TV_EPISODE) {
                imageClass = ' class="img-episode"';
            }
        }

        // Parent
        var parentIdParam = "";
        if (film.parentId != "undefined") { parentIdParam = "&pid=" + film.parentId; }

        // JavaScript
        var showFilmDetailJS = "showFilmDetail(" + filmId + ")";
        var onClick = "onClick='" + showFilmDetailJS + "'";
        var onMouseEnter = "onMouseEnter='detailTimer = setTimeout(function () { " + showFilmDetailJS + "; }, 500)'";
        var onMouseLeave = "onMouseLeave='hideFilmDropdownForUserlist(" + filmId + ", detailTimer)'";

        // HTML
        html = html + "  <div class='col-xs-6 col-sm-4 col-md-3 col-lg-2' id='" + uniqueName + "'>\n";
        html = html + "    <div class='userlist-film' " + onMouseEnter + " " + onMouseLeave + ">\n";
        html = html + "      <poster id='poster-" + uniqueName + "' data-filmId='" + filmId + "'>\n";
        html = html + "        <a href='/php/detail.php?i=" + filmId + parentIdParam + contentTypeParam + "'>\n";
        html = html + "          <img src='" + image + "' alt='" + titleNoQuotes + "' " + imageClass + onClick + " />\n";
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