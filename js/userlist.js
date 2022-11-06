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

        var itemRect = document.getElementById("userlist-film-" + filmId).getBoundingClientRect();
        var spaceOnTheLeft = itemRect.right - leftEnd;
        var spaceOnTheRight = rightEnd - itemRect.left;
        if (spaceOnTheRight > 504 || spaceOnTheRight > spaceOnTheLeft) {
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
    let films = contextData.films;

    // Clear the table
    const filmTableEl = document.getElementById("film-table");
    filmTableEl.innerHTML = "";

    if (!films || (films.length < 1)) {
        renderEmptyList();
    }

    const userlistRowEl = document.createElement("div");
    userlistRowEl.id = "userlist-row";
    userlistRowEl.setAttribute("class", "row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-5 row-cols-xl-6");

    filmTableEl.appendChild(userlistRowEl);

    for (var filmIndex = 0; filmIndex < films.length; filmIndex++) {
        const film = films[filmIndex];
        const filmId = film.filmId;
        const rsSource = film.sources.find(function (findSource) { return findSource.name == "RatingSync"; });
        const uniqueName = rsSource.uniqueName;

        // Episode style classes
        const episodeClass = {image:"", userlistfilm:""};
        const isEpisode = film.contentType == CONTENT_TV_EPISODE ? "true" : "false";

        const filmItemEl = document.createElement("filmItem");
        const userlistFilmEl = document.createElement("div");
        const dropdownEl = document.createElement("div");

        filmItemEl.id = uniqueName;
        filmItemEl.classList.add("col");
        filmItemEl.setAttribute("data-film-id", filmId);
        userlistFilmEl.id = `userlist-film-${filmId}`;
        userlistFilmEl.setAttribute("class", `userlist-film ${episodeClass["userlistfilm"]}`);
        userlistFilmEl.setAttribute("data-episode", isEpisode);
        userlistFilmEl.setAttribute("onMouseEnter", `detailTimer = setTimeout(function () { showFilmDropdownForUserlist(${filmId}); }, 500)`);
        userlistFilmEl.setAttribute("onMouseLeave", `hideFilmDropdownForUserlist(${filmId}, detailTimer)`);
        dropdownEl.id = `film-dropdown-${filmId}`;
        dropdownEl.classList.add("film-dropdown-content");

        filmItemEl.appendChild(userlistFilmEl);
        userlistRowEl.appendChild(filmItemEl);

        const posterEl = renderPoster(film, true, userlistFilmEl);
        posterEl.appendChild(dropdownEl);

    }
    
    sizeBreakpointCallback();

    renderPagination();
}

// Needs "contextData" JSON in the page
function showFilmDropdownForUserlist(filmId) {
    const filmIndex = contextData.films.findIndex( function (findFilm) { return findFilm.filmId == filmId; } );

    if ( filmIndex == -1 ) {
        return;
    }

    const film = contextData.films[filmIndex];

    const filmEl = document.getElementById(`userlist-film-${filmId}`);
    let posterEl = filmEl.getElementsByTagName("poster")[0];
    if (film.contentType == CONTENT_TV_EPISODE) {
        setPosterMode(film, false);
    }

    // This line must be after the call to reRenderPoster()
    let dropdownEl = document.getElementById("film-dropdown-" + filmId);

    renderFilmDetail(film, dropdownEl);

    // If the default source has no data for this film get it now
    const defaultSource = film.sources.find( function (findSource) { return findSource.name == DATA_API_DEFAULT; } );
    if (!defaultSource || defaultSource == "undefined") {
        getFilmForDropdown(film);
    }

    // Resize the poster to match the dropdown. Sometimes the dropdown is taller
    // than the poster.
    const posterHeight = posterEl.getBoundingClientRect().height;
    const dropdownHeight = dropdownEl.getBoundingClientRect().height;
    if (dropdownHeight - 10 > posterHeight) {
        const newPosterHeight = dropdownHeight - 10;
        posterEl.setAttribute("style", "height: " + newPosterHeight + "px");

        // The film element for episodes are rounded, so the dropdown border
        // would not match the border. Temporarily use a regular class while
        // the dropdown is shown.
        if (filmEl.getAttribute("data-episode") == "true") {
            filmEl.setAttribute("class", "userlist-film");
        }
    }
}

function hideFilmDropdownForUserlist(filmId, detailTimer) {
    const filmIndex = contextData.films.findIndex( function (findFilm) { return findFilm.filmId == filmId; } );

    if ( filmIndex == -1 ) {
        return;
    }

    const film = contextData.films[filmIndex];

    el = document.getElementById("film-dropdown-" + filmId);
    el.style.display = "none";
    clearTimeout(detailTimer);

    const filmEl = document.getElementById(`userlist-film-${filmId}`);

    // Change the style classes on posters for episodes (put it back to normal,
    // because it was changed while hovering)
    if (film.contentType == CONTENT_TV_EPISODE) {
        setPosterMode(film, true);
    }

    // This line must be after the call to reRenderPoster()
    let posterEl = filmEl.getElementsByTagName("poster")[0];

    // Poster might have been resized to match the dropdown. Put it back the
    // default height
    posterEl.removeAttribute("style");
}

function renderEmptyList() {
    let msgEl = document.getElementById("empty-list");

    if (msgEl) {
        msgEl.classList.add("mt-3");
        let text = "Begin by using the Search bar to find titles you want to rate.";
        renderMsg(text, msgEl);
    }
}
