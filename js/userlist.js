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

        const activePosterEl = document.getElementById("poster-focus-in-" + filmId);
        const inactivePosterEl = document.getElementById("poster-focus-out-" + filmId);
        const populatedPosterEl = activePosterEl?.hidden ? inactivePosterEl : activePosterEl;
        const itemRect = populatedPosterEl.getBoundingClientRect();
        var spaceOnTheLeft = itemRect.right - leftEnd;
        var spaceOnTheRight = rightEnd - itemRect.left;
        const dropToTheRight = spaceOnTheRight > 484 || spaceOnTheRight > spaceOnTheLeft;

        if (dropToTheRight) {
            // Dropdown to right
            dropdownClass = "detail-right";
        }

        dropdownEl.setAttribute("class", "film-dropdown-content " + dropdownClass);

        activePosterEl.classList.remove("detail-left");
        activePosterEl.classList.remove("detail-right");
        activePosterEl.classList.add(dropdownClass);
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
        const isEpisode = film.contentType == CONTENT_TV_EPISODE ? true : false;

        const filmItemEl = document.createElement("filmItem");
        const inactivePosterContainerEl = document.createElement("div");
        const activePosterContainerEl = document.createElement("div");
        const dropdownEl = document.createElement("div");

        filmItemEl.id = uniqueName;
        filmItemEl.classList.add("col");
        filmItemEl.setAttribute("data-film-id", filmId);
        inactivePosterContainerEl.id = `poster-focus-out-${filmId}`;
        inactivePosterContainerEl.setAttribute("class", `poster-container does-not-have-focus`);
        inactivePosterContainerEl.setAttribute("onMouseEnter", `detailTimer = setTimeout(function () { showFilmDropdownForUserlist(${filmId}); }, 500)`);
        inactivePosterContainerEl.setAttribute("onMouseLeave", `clearTimeout(detailTimer);`);
        activePosterContainerEl.id = `poster-focus-in-${filmId}`;
        activePosterContainerEl.setAttribute("class", `poster-container has-focus ${episodeClass["userlistfilm"]}`);
        activePosterContainerEl.setAttribute("onMouseLeave", `hideFilmDropdownForUserlist(${filmId}, detailTimer)`);
        activePosterContainerEl.hidden = true;
        dropdownEl.id = `film-dropdown-${filmId}`;
        dropdownEl.classList.add("film-dropdown-content");

        if ( isEpisode ) {
            filmItemEl.classList.add("episode");
            activePosterContainerEl.setAttribute("data-episode", isEpisode ? "true" : "false");
        }

        filmItemEl.appendChild(inactivePosterContainerEl);
        filmItemEl.appendChild(activePosterContainerEl);
        userlistRowEl.appendChild(filmItemEl);

        const posterEl = renderPoster(film, true, inactivePosterContainerEl);
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

    if (film.contentType == CONTENT_TV_EPISODE) {
        setPosterMode(film, false);
    }

    // Undo hovering for the WatchIt buttons
    const watchItContainerEl = document.getElementById(`watchit-btn-container-${filmId}`);
    if ( watchItContainerEl ) {
        watchItContainerEl.onmouseenter = null;
        watchItContainerEl.onmouseleave = null;
    }

    // Move the poster into the active poster container
    movePosterContainer(filmId, true);

    const dropdownEl = document.getElementById("film-dropdown-" + filmId);

    const activePosterContainerEl = document.getElementById(`poster-focus-in-${filmId}`);
    renderFilmDetail(film, dropdownEl);

    // If the default source has no data for this film get it now
    const defaultSource = film.sources.find( function (findSource) { return findSource.name == DATA_API_DEFAULT; } );
    if (!defaultSource || defaultSource == "undefined") {
        getFilmForDropdown(film);
    }

    resizeHeightToMatchElements(activePosterContainerEl, dropdownEl);

    // Blur all other posters to look like they are in the background
    activePosterContainerEl.classList.add("active");
    document.getElementById(`userlist-row`).classList.add("background");
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

    // Move the poster into the active poster container
    movePosterContainer(filmId, false);

    // Change the style classes on posters for episodes (put it back to normal,
    // because it was changed while hovering)
    if (film.contentType == CONTENT_TV_EPISODE) {
        setPosterMode(film, true);
    }

    const outerBoxEl = document.getElementById(`poster-focus-in-${filmId}`);
    const inactivePosterEl = document.getElementById(`poster-focus-out-${filmId}`);

    // Disable the film element's hover feature while the user is hovering on the WatchIt buttons
    const watchItContainerEl = document.getElementById(`watchit-btn-container-${filmId}`);
    if ( watchItContainerEl ) {
        watchItContainerEl.onmouseenter = outerBoxEl.onmouseleave;
        watchItContainerEl.onmouseleave = inactivePosterEl.onmouseenter;
    }

    // Poster might have been resized to match the dropdown. Put it back the
    // default height
    outerBoxEl.removeAttribute("style");

    // Undo the background style
    document.getElementById("userlist-row").classList.remove("background");
    outerBoxEl.classList.remove("active");
}

function renderEmptyList() {
    let msgEl = document.getElementById("empty-list");

    if (msgEl) {
        msgEl.classList.add("mt-3");
        let text = "Begin by using the Search bar to find titles you want to rate.";
        renderMsg(text, msgEl);
    }
}

function movePosterContainer(filmId, active) {

    let newContainerId = null;
    let oldContainerId = null;
    if ( active ) {
        newContainerId = `poster-focus-in-${filmId}`;
        oldContainerId = `poster-focus-out-${filmId}`;
    }
    else {
        newContainerId = `poster-focus-out-${filmId}`;
        oldContainerId = `poster-focus-in-${filmId}`;
    }

    const oldContainerEl = document.getElementById(oldContainerId);
    const newContainerEl = document.getElementById(newContainerId);

    if ( ! (oldContainerEl && newContainerEl) ) {
        return;
    }

    const posters = oldContainerEl.getElementsByTagName("poster");
    if ( posters.length == 0 ) {
        return;
    }

    const posterEl = posters[0];
    if ( posterEl?.getAttribute("data-filmid") != filmId ) {
        return;
    }

    oldContainerEl.removeChild(posterEl);
    newContainerEl.appendChild(posterEl);

    oldContainerEl.hidden = true;
    newContainerEl.removeAttribute("hidden");

}
