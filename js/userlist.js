
function getFilmsForFilmlist(pageSize, beginPage) {
    var params = "?action=getFilmsByList";
    params = params + "&l=" + encodeURIComponent(listname);
    params = params + "&ps=" + pageSize;
    params = params + "&bp=" + beginPage;
    params = params + getFilterParams();
	var xmlhttp = new XMLHttpRequest();
    var callbackHandler = function () { getFilmsForFilmlistCallback(xmlhttp); };
    xmlhttp.onreadystatechange = callbackHandler;
	xmlhttp.open("GET", RS_URL_API + params, true);
	xmlhttp.send();
}

function getFilmsForFilmlistCallback(xmlhttp) {
    if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
	    contextData = JSON.parse(xmlhttp.responseText);
	    renderUserlistFilms();
	}
}

function renderUserlistFilms() {
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
        var rsSource = film.sources.find( function (findSource) { return findSource.name == "RatingSync"; } );
        var image = RS_URL_BASE + rsSource.image;
        var uniqueName = rsSource.uniqueName;
        var showFilmDropdownForUserlistJS = "showFilmDetail(" + filmId + ")";
        var onMouseEnter = "onMouseEnter='detailTimer = setTimeout(function () { showFilmDropdownForUserlist(" + filmId + "); }, 500)'";
        var onMouseLeave = "onMouseLeave='hideFilmDropdownForUserlist(" +filmId+ ", detailTimer)'";
        html = html + "  <div class='col-xs-6 col-sm-4 col-md-3 col-lg-2' id='" +uniqueName+ "'>\n";
        html = html + "    <div class='userlist-film' " +onMouseEnter+ " " +onMouseLeave+ ">\n";
        html = html + "      <poster id='poster-" + uniqueName + "' data-filmId='" + filmId + "'>\n";
        html = html + "        <img src='" + image + "' alt='" + titleNoQuotes + "' />\n";
        html = html + "        <div id='film-dropdown-"+filmId+"' class='film-dropdown-content film-dropdown-col-"+column+"'></div>\n";
        html = html + "      </poster>\n";
        html = html + "      <div class='below-poster' id='poster-extension-"+filmId+"' data-filmId='"+filmId+"'>\n";
        html = html + "        <div id='streams-"+film.filmId+"' class='streams'></div>\n'";
        html = html + "      </div>\n";
        html = html + "    </div>\n";
        html = html + "  </div>\n";
        
        if (filmIndex % 12 == 11 || filmIndex == films.length-1) {
            html = html + "</div>\n";
        }
    }
    document.getElementById("film-table").innerHTML = html;
    
    for (var filmIndex = 0; filmIndex < films.length; filmIndex++) {
        var film = films[filmIndex];
        renderStreams(film, false);
    }
    
    var currentPageNum = contextData.beginPage;
    var pageSize = contextData.pageSize;
    var totalCount = contextData.totalCount;
    var previousPageNum = 0;
    var nextPageNum = 0;
    if (!currentPageNum || currentPageNum == "") {
        currentPageNum = 1;
    }
    currentPageNum = currentPageNum * 1;

    // Previous button
    var previousEl = document.getElementById("previous");
    if (currentPageNum > 1) {
        previousPageNum = currentPageNum - 1;
        previousEl.removeAttribute("class");
        var previousAnchorEl = previousEl.getElementsByTagName("A")[0];
        previousAnchorEl.setAttribute("href", "./userlist.php?l=" + listname + "&p=" + previousPageNum + getFilterParams());
    } else {
        previousEl.setAttribute("class", "disabled");
    }

    // Next button
    var nextEl = document.getElementById("next");
    if (totalCount > currentPageNum * pageSize) {
        nextPageNum = currentPageNum + 1;
        nextEl.removeAttribute("class");
        var nextAnchorEl = nextEl.getElementsByTagName("A")[0];
        nextAnchorEl.setAttribute("href", "./userlist.php?l=" + listname + "&p=" + nextPageNum + getFilterParams());
    } else {
        nextEl.setAttribute("class", "disabled");
    }

    if (previousPageNum != 0 || nextPageNum != 0) {
        var paginationEl = document.getElementById("pagination");
        paginationEl.removeAttribute("hidden");
    }
}

function showFilmDropdownForUserlist(filmId) {
    var dropdownEl = document.getElementById("film-dropdown-" + filmId);
    var film = contextData.films.find( function (findFilm) { return findFilm.filmId == filmId; } );
    renderFilmDropdownForUserlist(film, dropdownEl);
}

function renderFilmDropdownForUserlist(film, dropdownEl) {
    dropdownEl.innerHTML = "";
    dropdownEl.appendChild(buildFilmDetailElement(film));
    dropdownEl.style.display = "block";

    renderStars(film);
    renderStreams(film, true);
    renderFilmlists(film.filmlists, film.filmId);
}

function hideFilmDropdownForUserlist(filmId, detailTimer) {
    el = document.getElementById("film-dropdown-" + filmId);
    el.style.display = "none";
    clearTimeout(detailTimer);
}

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

function changeContentTypeFilter() {
    getFilmsForFilmlist(defaultPageSize, 1);
}