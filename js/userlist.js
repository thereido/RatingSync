var currentScreenSize = screenSize(window.innerWidth);

window.onresize = function(event) {
    var newSize = screenSize(window.innerWidth);
    if (this.currentScreenSize != newSize) {
        this.currentScreenSize = newSize;
        sizeBreakpointCallback();
    }
};

function screenSize(width) {
    var size = "xs";
    if (width > 1200) { size = "xl"; }
    else if (width > 992) { size = "lg"; }
    else if (width > 768) { size = "md"; }
    else if (width > 576) { size = "sm"; }

    return size;
}

function sizeBreakpointCallback() {
    var rowEl = document.getElementById("userlist-row");
    var leftEnd = rowEl.getBoundingClientRect().left;
    var rightEnd = rowEl.getBoundingClientRect().right;

    var filmEls = document.getElementsByTagName("filmItem");
    for (var i = 0; i < filmEls.length; i++) {
        var filmEl = filmEls[i];
        var filmId = filmEl.getAttribute("data-film-id");
        var dropdownEl = document.getElementById("film-dropdown-" + filmId);
        var dropdownClass = "detail-left";

        var itemRect = filmEl.getBoundingClientRect();
        var spaceOnTheLeft = itemRect.right - leftEnd;
        var spaceOnTheRight = rightEnd - itemRect.left;
        if (spaceOnTheRight > 480 || spaceOnTheRight > spaceOnTheLeft) {
            // Dropdown to right
            dropdownClass = "detail-right";
        }

        dropdownEl.setAttribute("class", "film-dropdown-content " + dropdownClass);
    }
}

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
    html = html + "<div class='row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-5 row-cols-xl-6' id='userlist-row'>\n";
    for (var filmIndex = 0; filmIndex < films.length; filmIndex++) {
        var film = films[filmIndex];
        var filmId = film.filmId;
        var rsSource = film.sources.find(function (findSource) { return findSource.name == "RatingSync"; });
        var uniqueName = rsSource.uniqueName;

        // Title
        var title = film.title;
        var titleNoQuotes = title.replace(/\"/g, '\\\"').replace(/\'/g, "\\\'");

        // ContentType
        var contentTypeParam = "";
        if (film.contentType != "undefined") { contentTypeParam = "&ct=" + film.contentType; }

        // Image
        var image = "";
        if (rsSource.image) {
            var image = RS_URL_BASE + rsSource.image;
        }

        // Episode style classes
        var episodeClass = {image:"", userlistfilm:""};
        var isEpisode = "false";
        if (film.contentType == CONTENT_TV_EPISODE) {
            episodeClass["image"] = "img-episode";
            episodeClass["userlistfilm"] = "userlist-film-episode";
            isEpisode = "true";
        }

        // Parent
        var parentIdParam = "";
        if (film.parentId != "undefined") { parentIdParam = "&pid=" + film.parentId; }
        
        // JavaScript
        var showFilmDropdownForUserlistJS = "showFilmDetail(" + filmId + ")";
        var onMouseEnter = "onMouseEnter='detailTimer = setTimeout(function () { showFilmDropdownForUserlist(" + filmId + "); }, 500)'";
        var onMouseLeave = "onMouseLeave='hideFilmDropdownForUserlist(" + filmId + ", detailTimer)'";
        
        html = html + '  <filmItem class="col" id="' + uniqueName + '" data-film-id="' + filmId + '">' + '\n';
        html = html + '    <div class="userlist-film '+episodeClass["userlistfilm"]+'" id="userlist-film-'+filmId+'" data-episode="'+isEpisode+'" ' + onMouseEnter + ' ' + onMouseLeave + '>' + '\n';
        html = html + '      <poster id="poster-' + uniqueName + '" data-filmId="' + filmId + '">' + '\n';
        html = html + '        <a href="/php/detail.php?i=' + filmId + parentIdParam + contentTypeParam + '">' + '\n';
        html = html + '          <img src="' + image + '" alt="' + titleNoQuotes + '" class="'+episodeClass["image"]+'" />' + '\n';
        html = html + '        </a>' + '\n';
        html = html + '        <div id="film-dropdown-' + filmId + '" class="film-dropdown-content"></div>' + '\n';
        html = html + '      </poster>' + '\n';
        html = html + '    </div>' + '\n';
        html = html + '  </filmItem>' + '\n';
    }
    html = html + '</div>' + '\n';
    document.getElementById("film-table").innerHTML = html;
    
    sizeBreakpointCallback();

    renderPagination();
}

// Needs "contextData" JSON in the page
function showFilmDropdownForUserlist(filmId) {    
    var filmIndex = contextData.films.findIndex( function (findFilm) { return findFilm.filmId == filmId; } );
    if (filmIndex != -1) {
        var film = contextData.films[filmIndex];
        var dropdownEl = document.getElementById("film-dropdown-" + filmId);
        renderFilmDetail(film, dropdownEl);

        // If the default source has no data for this film get it now
        var defaultSource = film.sources.find( function (findSource) { return findSource.name == DATA_API_DEFAULT; } );
        if (!defaultSource || defaultSource == "undefined") {
            getFilmForDropdown(film);
        }

        // Resize the poster to match the dropdown. Sometimes the dropdown is taller
        // than the poster.
        var posterEl = document.getElementById("poster-rs" + filmId);
        var posterHeight = posterEl.getBoundingClientRect().height;
        var dropdownHeight = dropdownEl.getBoundingClientRect().height;
        if (dropdownHeight - 10 > posterHeight) {
            var newPosterHeight = dropdownHeight - 10;
            posterEl.setAttribute("style", "height: " + newPosterHeight + "px");

            // The film element for episodes are rounded, so the dropdown border
            // would not match the border. Temporarily use a regular class while\
            // the dropdown is shown.
            var filmEl = document.getElementById("userlist-film-" + filmId);
            if (filmEl.getAttribute("data-episode") == "true") {
                filmEl.setAttribute("class", "userlist-film");
            }
        }
    }
}

function hideFilmDropdownForUserlist(filmId, detailTimer) {
    el = document.getElementById("film-dropdown-" + filmId);
    el.style.display = "none";
    clearTimeout(detailTimer);

    // Poster might have been resized to match the dropdown. Put it back the
    // default height
    var posterEl = document.getElementById("poster-rs" + filmId);
    posterEl.removeAttribute("style");

    var filmEl = document.getElementById("userlist-film-" + filmId);
    if (filmEl.getAttribute("data-episode") == "true") {
        filmEl.setAttribute("class", "userlist-film userlist-film-episode");
    }
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
        if (addThisEl != null && addThisEl.checked == false) {
            params = params + "&a=0";
        } else {
            params = params + "&a=1";
        }
    }
    
    var parentListEl = document.getElementById("filmlist-parent");
    if (parentListEl != null) {
        var parentList = parentListEl.value;
        if (parentList != null && parentList != "---") {
            params = params + "&parent=" + parentList;
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