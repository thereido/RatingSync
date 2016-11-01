
var hideable = true;

function createFilmlist() {
    var listname = document.getElementById("filmlist-listname").value;
    if (listname == 0) {
        document.getElementById("filmlist-create-result").innerHTML = "";
        return;
    }
    
    var params = "&l="+listname;
    var filmIdEl = document.getElementById("filmlist-filmid");
    if (filmIdEl != null) {
        params = params + "&id=" + filmIdEl.value;
        var addThisEl = document.getElementById("filmlist-add-this");
        if (addThisEl != null && addThisEl.value == "0") {
            params = params + "&a=0";
        } else {
            params = params + "&a=1";
        }
    }

    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            window.location = "/php/userlist.php?l="+listname;
        }
    }
    xmlhttp.open("GET", "/php/src/ajax/api.php?action=createFilmlist"+params, true);
    xmlhttp.send();

    return false;
}

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
    
    var pageNum = contextData.beginPage;
    var pageSize = contextData.pageSize;
    var totalRatings = contextData.totalRatings;
    var previousPageNum = 0;
    var nextPageNum = 0;
    if (!pageNum || pageNum == "") {
        pageNum = 1;
    }
    pageNum = pageNum * 1;

    // Previous button
    var previousEl = document.getElementById("previous");
    if (pageNum > 1) {
        previousPageNum = pageNum - 1;
        previousEl.removeAttribute("class");
        var previousAnchorEl = previousEl.getElementsByTagName("A")[0];
        previousAnchorEl.setAttribute("href", "./ratings.php?p=" + previousPageNum);
    } else {
        previousEl.setAttribute("class", "disabled");
    }

    // Next button
    var nextEl = document.getElementById("next");
    if (totalRatings > pageNum * pageSize) {
        nextPageNum = pageNum + 1;
        nextEl.removeAttribute("class");
        var nextAnchorEl = nextEl.getElementsByTagName("A")[0];
        nextAnchorEl.setAttribute("href", "./ratings.php?p=" + nextPageNum);
    } else {
        nextEl.setAttribute("class", "disabled");
    }

    if (previousPageNum != 0 || nextPageNum != 0) {
        var paginationEl = document.getElementById("pagination");
        paginationEl.removeAttribute("hidden");
    }
}   