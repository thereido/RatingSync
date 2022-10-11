/**
 * A html file sourcing this javascript must...
 *   - Have element with id='alert-placeholder'
 *   - Have a javascript var contextData as JSON with films[]
 */
var userlistsJson;
var userlistsCallbacks = [];
var waitingForFilmlists = false;
var contextData = JSON.parse('{"films":[]}');

/**
 * renderAlert
 *
 * @param message
 * @param level Use ALERT_LEVEL enum
 * @param operationId If there will be multiple alerts as the operation progress (saving, done, etc), use a unique id for clear each stage.
 * @param timer in milliseconds. Zero means no timer. Default is 3 seconds, or 15 seconds for a warning, and no timer for a danger level.
 */
function renderAlert(message, level, operationId = null, timer = null) {
    const alertPlaceholder = document.getElementById('alert-placeholder');

    if (!alertPlaceholder) {
        return;
    }

    const wrapper = document.createElement('alert-wrapper');

    if (operationId) {
        const previousAlerts = document.getElementsByTagName('alert-wrapper');
        for (let i=0; i < previousAlerts.length; i++) {
            const alertEl = previousAlerts[i];
            if (operationId == alertEl.getAttribute("data-op-id")) {
                alertEl.remove();
            }
        }

        wrapper.setAttribute("data-op-id", operationId);
    }

    const newAlertEl = document.createElement("div");
    const messageEl = document.createElement("span");
    const closeBtnEl = document.createElement("button");
    const xEl = document.createElement("span");

    newAlertEl.setAttribute("class", `alert alert-${level} alert-dismissible fade in show`);
    newAlertEl.setAttribute("role", "alert");
    closeBtnEl.setAttribute("type", "button");
    closeBtnEl.setAttribute("class", "close");
    closeBtnEl.setAttribute("data-dismiss", "alert");
    closeBtnEl.setAttribute("aria-label", "Close");
    xEl.setAttribute("aria-hidden", "true");

    messageEl.innerHTML = message;
    xEl.innerHTML = "&times;";

    alertPlaceholder.append(wrapper);
    wrapper.appendChild(newAlertEl);
    newAlertEl.appendChild(messageEl);
    newAlertEl.appendChild(closeBtnEl);
    closeBtnEl.appendChild(xEl);

    if (timer == 0 || level == ALERT_LEVEL.danger) {
        return;
    }

    const defaultTimer = 3000;
    const defaultWarningTimer = 15000;
    if (!timer || timer < 0) {
        timer = level == ALERT_LEVEL.warning ? defaultWarningTimer : defaultTimer;
    }

    setTimeout(function () { clearAlert(newAlertEl); }, timer);
}

function clearAlert(el)
{
    if (el) {
        el.remove();
    }
}

function rateFilm(filmId, uniqueName, score, callback, newDate = null, originalDate = null, index = -1) {
    const operaterId = `rateFilm-${filmId}`;
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            let film;
            const response = JSON.parse(xmlhttp.responseText);
            if (response.Success && response.Success == "false") {
                film = getContextDataFilm(filmId);
                const filmMsg = rateFilmResponseTitle(film);
                const msg = `<strong>Unable to save your rating</strong>.<br>"${filmMsg}"`;
                renderAlert(msg, ALERT_LEVEL.warning, operaterId);
            } else {
                film = response;
                const filmMsg = rateFilmResponseTitle(film);
                updateContextDataFilmByFilmId(film);
                renderAlert(`<strong>Rating Saved</strong>.<br>"${filmMsg}"`, ALERT_LEVEL.success, operaterId);
            }
            callback(film, index);
        }
    }

    let params = "";
    params += "&json=1";
    params += `&fid=${filmId}`;
    params += `&un=${uniqueName}`;
    params += `&s=${score}`;
    params += newDate ? `&d=${newDate}` : "";
    params += originalDate ? `&od=${originalDate}` : "";
    xmlhttp.open("GET", RS_URL_API + "?action=setRating" + params, true);
    xmlhttp.send();
    renderAlert('Saving...', ALERT_LEVEL.info, operaterId, 0);
}

function rateFilmResponseTitle(film) {
    const seasonNumMsg = film.season ? ` S${film.season}` : "";
    const episodeNumMsg = film.episodeNumber ? ` E${film.episodeNumber}` : "";
    return film.title + seasonNumMsg + episodeNumMsg;
}

function renderStreams(film, displaySearch) {
    var rsSource = getSourceJson(film, SOURCE_NAME.Internal);
    var rsUniqueName = rsSource.uniqueName;

    var streamsEl = document.getElementById("streams-"+film.filmId);
    var watchEl = document.createElement("DIV");
    var searchSourceEl = document.createElement("DIV");
    streamsEl.innerHTML = "";
    streamsEl.appendChild(watchEl);
    streamsEl.appendChild(searchSourceEl);

    var providers = validStreamProviders();
    for (var providerIndex = 0; providerIndex < providers.length; providerIndex++) {
        var sourceName = providers[providerIndex];

        var uniqueName;
        var uniqueEpisode = "";
        var uniqueAlt = "";
        var streamDate = "";
        var source = film.sources.find( function (findSource) { return findSource.name == sourceName; } );
        if (source) {
            uniqueName = source.uniqueName;
            uniqueEpisode = source.uniqueEpisode;
            uniqueAlt = source.uniqueAlt;
            streamDate = source.streamDate;
        }
        if (!uniqueName || uniqueName == null || uniqueName == "null" || uniqueName == "uniqueName") {
            uniqueName = "";
        }

        var streamLink = "";
        var searchLink = "";
        if (source && source.streamUrl && source.streamUrl != "undefined") {
            streamLink = source.streamUrl;
        } else {
            if (sourceName == "Netflix") {
                streamLink = "https://www.netflix.com/title/" + uniqueName;
                searchLink = "https://www.netflix.com/search/" + encodeURI(film.title);
            } else if (sourceName == "xfinity") {
                streamLink = "https://tv.xfinity.com/entity/" + uniqueName;
                searchLink = "https://tv.xfinity.com/search?q=" + encodeURI(film.title);
            }
        }

        var streamEl = document.createElement("DIV");
        streamEl.setAttribute("id", sourceName + "-" + rsUniqueName);
        streamEl.setAttribute("class", "stream");
        streamEl.setAttribute("data-film-id", film.filmId);
        streamEl.setAttribute("data-source-name", sourceName);
        streamEl.setAttribute("data-title", film.title);
        streamEl.setAttribute("data-year", film.year);
        streamEl.setAttribute("data-uniquename", uniqueName);
        streamEl.setAttribute("data-unique-episode", uniqueEpisode);
        streamEl.setAttribute("data-unique-alt", uniqueAlt);
        streamEl.setAttribute("data-stream-date", streamDate);
        if (uniqueName && uniqueName != "" && uniqueName != "undefined") {
            var html = "\n";
            html = html + "    <a href='" + streamLink + "' target='_blank'>\n";
            html = html + "      <div class='stream-icon icon-" + sourceName + "' title='Watch on " + sourceName + "'></div>\n";
            html = html + "    </a>\n";
            streamEl.innerHTML = html;
            watchEl.appendChild(streamEl);
        } else if (displaySearch) {
            var html = "\n";
            html = html + "    <a href='" + searchLink + "' target='_blank'>\n";
            html = html + "      <div class='stream-icon icon-" + sourceName + "' title='Search " + sourceName + "'></div>\n";
            html = html + "    </a>\n";
            streamEl.innerHTML = html;
            searchSourceEl.appendChild(streamEl);
        }
    }
}

function toggleFilmlist(listname, filmId, activeBtnId) {
    var defaultBtn = document.getElementById("filmlist-btn-default-" + filmId);
    var otherListsBtn = document.getElementById("filmlist-btn-others-" + filmId);
    var otherListsElement = document.getElementById("filmlists-" + filmId);
    if (defaultBtn) defaultBtn.disabled = true;
    if (otherListsBtn) otherListsBtn.disabled = true;
    
    var activeBtn = document.getElementById(activeBtnId);
    var checkmark = activeBtn.getElementsByTagName("span")[0];
    var filmIsInTheList = false;
    var addToList = 1; //yes
    if (checkmark.className == "far fa-check-circle checkmark-on") {
        filmIsInTheList = true;
        var addToList = 0; //no (remove)
    }

    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            filmIsInTheList = !filmIsInTheList;
            if (filmIsInTheList) {
                checkmark.className = "far fa-check-circle checkmark-on";
            } else {
                checkmark.className = "far fa-check-circle checkmark-off";
            }
            
            var film = JSON.parse(xmlhttp.responseText);
            renderFilmlists(film.filmlists, film.filmId);
            updateContextDataFilmByFilmId(film);

            if (defaultBtn) defaultBtn.disabled = false;
            if (otherListsBtn) otherListsBtn.disabled = false;
            if (otherListsElement) otherListsElement.disabled = false;
            if (otherListsElement) otherListsElement.hidden = true;
        }
    }
    xmlhttp.open("GET", RS_URL_API + "?action=setFilmlist&l=" + listname + "&id=" + filmId + "&c=" + addToList, true);
    xmlhttp.send();
}

function clickStar(filmId, uniqueName, score, active, ratingIndex) {
    const originalScoreEl = document.getElementById(`original-score-${uniqueName}-${ratingIndex}`);
    const originalScore = originalScoreEl.getAttribute('data-score');

    if (active) {
        if (score == originalScore) {
            showConfirmationRating(filmId, uniqueName, score);
        } else {
            rateFilm(filmId, uniqueName, score, renderActiveRating, null, null, ratingIndex);
        }
    }
    else if (score != originalScore) {
        const originalDate = document.getElementById(`rate-${uniqueName}-${score}-${ratingIndex}`).getAttribute('data-date');
        const newDate = originalDate;
        rateFilm(filmId, uniqueName, score, renderEditRating, newDate, originalDate, ratingIndex);
    }
}

function mouseoverStar(score, uniqueName, ratingIndex) {
    toggleHighlightStars(score, uniqueName, ratingIndex);
    setYourScoreElementValue(score, uniqueName, ratingIndex);
}

function mouseoutStar(score, uniqueName, ratingIndex) {
    toggleHighlightStars(score, uniqueName, ratingIndex);
    resetYourScoreElementValue(uniqueName, ratingIndex);
}

function toggleHighlightStars(score, uniqueName, ratingIndex) {
    const starsParent = document.getElementById(`rating-stars-${uniqueName}-${ratingIndex}`);
    toggleHighlightStars2(score, starsParent);
}

function toggleHighlightStars2(score, starsParent) {
    if (starsParent) {
        const starEls = starsParent.children;

        for (let i=0; i < score; i++) {
            starEls[i].toggleAttribute("star-highlight");
        }
    }
}

function showConfirmationRating(filmId, uniqueName, score) {
    // Show confirmation dialog asking what to do
    
    // Hide the action area & show the dialog
    var actionAreaEl = document.getElementById("action-area-" + uniqueName);
    actionAreaEl.setAttribute("hidden", true);
    var confirmationEl = document.getElementById("rate-confirmation-" + uniqueName);
    confirmationEl.removeAttribute("hidden");

    // A function to undo the dialog content and show the action area
    var undoDialogFunc = function () {
            confirmationEl.setAttribute("hidden", true);
            confirmationEl.innerHTML = "";
            actionAreaEl.removeAttribute("hidden");
        }
    
    var rateButton = document.createElement("button");
    rateButton.setAttribute("type", "button");
    rateButton.setAttribute("class", "btn btn-primary");
    rateButton.innerHTML = "Rate " + score + " Again";
    var rateHandler = function () { undoDialogFunc(); rateFilm(filmId, uniqueName, score, renderActiveRating); };
    rateButton.addEventListener("click", rateHandler);

    var removeButton = document.createElement("button");
    removeButton.setAttribute("type", "button");
    removeButton.setAttribute("class", "btn btn-secondary btn-sm");
    removeButton.innerHTML = "Remove Rating";
    var removeHandler = function () { undoDialogFunc(); rateFilm(filmId, uniqueName, 0, renderActiveRating); };
    removeButton.addEventListener("click", removeHandler);

    var cancelButton = document.createElement("button");
    cancelButton.setAttribute("type", "button");
    cancelButton.setAttribute("class", "btn btn-link btn-sm");
    cancelButton.innerHTML = "Cancel";
    var cancelHandler = function () { undoDialogFunc(); };
    cancelButton.addEventListener("click", cancelHandler);

    var row1El = document.createElement("div");
    var row2El = document.createElement("div");
    row2El.setAttribute("class", "pt-1");

    row1El.append(rateButton);
    row2El.append(removeButton);
    row2El.append(cancelButton);
    confirmationEl.append(row1El);
    confirmationEl.append(row2El);
}

function getFilmlists(callback) {
    if (userlistsJson) {
        callback();
    }
    else {
        userlistsCallbacks.push(callback);
        if (!waitingForFilmlists) {
            // Get all of the user's filmlists
            var xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function () {
                if (xmlhttp.readyState == 4) {
                    if (xmlhttp.status == 200) {
                        userlistsJson = xmlhttp.responseText;
                        while (userlistsCallbacks.length > 0) {
                             var cb = userlistsCallbacks.pop();
                             cb();
                        }
                    }
                    waitingForFilmlists = false;
                }
            }
            xmlhttp.open("GET", RS_URL_API + "?action=getUserLists", true);
            waitingForFilmlists = true;
            xmlhttp.send();
        }
    }
}

function getCheckmarkClass(checked) {
    if (checked) {
        return "far fa-check-circle checkmark-on";
    } else {
        return "far fa-check-circle checkmark-off";
    }
}

function getDefaultList() {
    return "Watchlist";
}

function toggleHidden(elementId) {
    const el = document.getElementById(elementId);
    el.hidden = !el.hidden;
}

function addFilmlistListeners(el, filmId) {
    // Default list button
	var defaultListBtn = document.getElementById("filmlist-btn-default-"+filmId);
	if (defaultListBtn != null) {
	    var listname = defaultListBtn.getAttribute('data-listname');
        var clickDefaultListHandler = function () { toggleFilmlist(listname, filmId, defaultListBtn.getAttribute("id")); };
        defaultListBtn.addEventListener("click", clickDefaultListHandler);
	}
}

function addFilmlistListener(elementId) {
	var button = document.getElementById(elementId);
	if (button != null) {
		var listname = button.getAttribute('data-listname');
		var filmId = button.getAttribute('data-filmId');

        var clickHandler = function () { toggleFilmlist(listname, filmId, elementId); };
        button.addEventListener("click", clickHandler);
	}
}

function addStarListeners(el, active) {
    var stars = el.getElementsByClassName("rating-star");
    for (i = 0; i < stars.length; i++) {
        addStarListener(stars[i], active);
    }
}

function addStarListener(starEl, active) {
	if (starEl != null) {
		const filmId = starEl.getAttribute('data-film-id');
		const uniqueName = starEl.getAttribute('data-uniquename');
		const score = starEl.getAttribute('data-score');
        const ratingIndex = starEl.getAttribute("data-index");

		const mouseoverHandler = function () {  mouseoverStar(score, uniqueName, ratingIndex); };
		const mouseoutHandler = function () { mouseoutStar(score, uniqueName, ratingIndex); };
		const clickHandler = function () { clickStar(filmId, uniqueName, score, active, ratingIndex); };

        starEl.addEventListener("mouseover", mouseoverHandler);
        starEl.addEventListener("mouseout", mouseoutHandler);
        starEl.addEventListener("click", clickHandler);
	}
}

function addWatchItButtonListeners(filmId) {
    const seenBtnId = `seen-btn-${filmId}`;
    const neverBtnId = `never-watch-btn-${filmId}`;

    const seenBtnEl = document.getElementById(seenBtnId);
    const neverBtnEl = document.getElementById(neverBtnId);

    if ( seenBtnEl ) {
        const seenBtnHandler = function () { toggleSeen(seenBtnEl, filmId); };
        seenBtnEl.addEventListener("click", seenBtnHandler);
    }

    if ( neverBtnEl ) {
        const neverWatchBtnHandler = function () { toggleNeverWatchIt(neverBtnEl, filmId); };
        neverBtnEl.addEventListener("click", neverWatchBtnHandler);
    }
}

function validStreamProviders() {
    return [];
}

function getContextDataFilmIndex(filmId) {
    var index = contextData.films.findIndex(function (findFilm) { return findFilm.filmId == filmId; });
    return index;
}

function getContextDataFilm(filmId) {
    var film = JSON.parse("{}");
    var index = getContextDataFilmIndex(filmId);

    if (-1 != index) {
        film = contextData.films[index];
    }

    return film;
}

function updateContextDataFilmByFilmId(updateFilm) {
    var filmId = updateFilm.filmId;
    var index = getContextDataFilmIndex(filmId);

    if (-1 != index) {
        contextData.films[index] = updateFilm;
    }
}

function updateContextDataFilmByUniqueName(updateFilm, sourceName) {
    var uniqueName = getUniqueName(updateFilm, sourceName);
    var index = contextData.films.findIndex(function (findFilm) { return getUniqueName(findFilm, sourceName) == uniqueName; });

    if (-1 != index) {
        contextData.films[index] = updateFilm;
    }
}

function getFilmFromRs(filmId, uniqueName, imdbId, contentType, parentId, seasonNum, episodeNum, xmlhttp, callback) {
    var params = "?action=getFilm";
    params = params + "&id=" + filmId;
    if (imdbId && imdbId != "undefined") { params = params + "&imdb=" + imdbId; }
    if (uniqueName && uniqueName != "undefined") { params = params + "&un=" + uniqueName; }
    params = params + "&ct=" + contentType;
    if (parentId && parentId != "undefined") { params = params + "&pid=" + parentId; }
    if (seasonNum && seasonNum != "undefined") { params = params + "&s=" + seasonNum; }
    if (episodeNum && episodeNum != "undefined") { params = params + "&e=" + episodeNum; }
    params = params + "&rsonly=0";

    var callbackHandler = function () { getFilmFromRsCallback(xmlhttp, callback); };
    xmlhttp.onreadystatechange = callbackHandler;
    xmlhttp.open("GET", RS_URL_API + params, true);
    xmlhttp.send();
}

function getFilmFromRsCallback(xmlhttp, callback) {
    if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
        callback(xmlhttp);
    }
}

function getFilmForDropdown(film) {
    var filmId = film.filmId;
    var uniqueName;
    var defaultSource = film.sources.find( function (findSource) { return findSource.name == DATA_API_DEFAULT; } );
    if (defaultSource && defaultSource != "undefined") {
        uniqueName = defaultSource.uniqueName;
    }
    var imdbId;
    var imdbSource = film.sources.find( function (findSource) { return findSource.name == SOURCE_NAME.IMDb; } );
    if (imdbSource && imdbSource != "undefined") {
        imdbId = imdbSource.uniqueName;
    }
    var contentType = film.contentType;
    var parentId = film.parentId;
    var seasonNum = film.seasonNum;
    var episodeNum = film.episodeNum;
    var xmlhttp = new XMLHttpRequest();
    var callbackHandler = function () { filmDropdownCallback(xmlhttp); };
    getFilmFromRs(filmId, uniqueName, imdbId, contentType, parentId, seasonNum, episodeNum, xmlhttp, callbackHandler);
}

function filmDropdownCallback(xmlhttp) {
	if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
        var result = JSON.parse(xmlhttp.responseText);
        if (result.Success != "false" && result.filmId != "undefined") {
            var film = result;
            var filmId = film.filmId;
            var filmIndex = contextData.films.findIndex( function (findFilm) { return findFilm.filmId == filmId; } );
            if (filmIndex != -1) {
                contextData.films[filmIndex] = film;
            } else {
                contextData.films.push(film);
            }
            var dropdownEl = document.getElementById("film-dropdown-" + film.filmId);
            renderFilmDetail(film, dropdownEl);
        }
	}
}

function renderFilmDetail(film, dropdownEl) {
    dropdownEl.innerHTML = "";
    dropdownEl.appendChild(buildFilmDetailElement(film));
    dropdownEl.style.display = "block";

    renderOneRatingStars(film);
    renderStreams(film, true);
    renderFilmlists(film.filmlists, film?.filmId);
    addWatchItButtonListeners(film?.filmId);
}

function renderMsg(message, element) {
    if (element) {
        if (message && message.length > 0) {
            element.innerHTML = message;
            element.hidden = false;
        } else {
            element.hidden = true;
        }
    }
}

function renderRatingHistory(film, rsSource) {
    const filmId = film.filmId;
    let ratingHistoryEl = document.getElementById(`rating-history-${filmId}`);

    const activeRating = rsSource.rating;
    if ( ! validRatingScore(activeRating) && rsSource?.archiveCount < 1 ) {
        return;
    }

    const uniqueName = rsSource.uniqueName;
    const archive = rsSource.archive;
    let ratingHistoryMenuEl = document.getElementById(`rating-history-menu-ref-${filmId}`);

    if ( ! ratingHistoryMenuEl ) {
        const newRatingHistoryEl = buildRatingHistoryElement(film);

        const viewingHistoryEl = document.getElementById(`viewing-history-${filmId}`);
        viewingHistoryEl.replaceChild( newRatingHistoryEl, ratingHistoryEl );

        ratingHistoryEl = document.getElementById(`rating-history-${filmId}`);
        ratingHistoryMenuEl = document.getElementById(`rating-history-menu-ref-${filmId}`);
    }

    ratingHistoryMenuEl.innerHTML = "";
    let ratingIndex = 0;

    const historyLineEl = document.createElement("div");
    const historyEditBtnEl = document.createElement("button");

    historyLineEl.setAttribute("class", "d-flex flex-row-reverse px-1");
    historyEditBtnEl.setAttribute("class", "btn-edit btn far fa-solid fa-pencil fa-sm pr-0");
    historyEditBtnEl.setAttribute("type", "submit");
    historyEditBtnEl.setAttribute("id", `rating-history-edit-${filmId}`);

    historyLineEl.appendChild(historyEditBtnEl);
    ratingHistoryMenuEl.appendChild(historyLineEl);

    if (activeRating.yourScore > 0) {
        renderHistoryLine(ratingHistoryMenuEl, filmId, activeRating, ratingIndex);
        ratingIndex++;
    }

    for (let i = 0; i < rsSource.archiveCount; i++) {
        const archivedRating = archive[i];
        renderHistoryLine(ratingHistoryMenuEl, filmId, archivedRating, ratingIndex);
        ratingIndex++;
    }

    if (ratingIndex > 0) {
        ratingHistoryEl.removeAttribute("hidden");
    }
    else {
        // When there is no rating history shown the view looks better with the thirdparty bar with a top margin
        let thirdpartybarEl = document.getElementById(`thirdparty-bar-${uniqueName}`);
        const barClass = thirdpartybarEl.getAttribute("class");
        thirdpartybarEl.setAttribute("class", `${barClass} mt-2`);
    }
}

function renderHistoryLine(parentEl, filmId, rating, ratingIndex) {
    const score = rating.yourScore;
    const date = rating.yourRatingDate;

    const fullStarClass =  "star fas fa-xs align-middle fa-star";
    const halfStarClass =  "star far fa-xs align-middle fa-star-half-stroke";
    const emptyStarClass = "star far fa-xs align-middle fa-star";

    const fullStarCount = Math.round(score / -2) * -1;
    const halfStarCount = score % 2;

    const historyLineEl = document.createElement("div");
    const flexContainerEl = document.createElement("div");
    const starsEl = document.createElement("div");
    const dateAndButtonEl = document.createElement("div");
    const dateEl = document.createElement("span");

    historyLineEl.setAttribute("class", "container-flex py-1");
    flexContainerEl.setAttribute("class", "d-flex justify-content-between");
    starsEl.setAttribute("class", "px-1");
    dateAndButtonEl.setAttribute("class", "px-1");
    dateEl.setAttribute("class", "small");

    for (let i = 0; i < 5; i++) {
        const starEl = document.createElement("i");
        let starClass = fullStarClass;

        if (i >= fullStarCount) {
            if (i >= fullStarCount + halfStarCount) {
                starClass = emptyStarClass;
            }
            else {
                starClass = halfStarClass;
            }
        }

        starEl.setAttribute("class", starClass);
        starsEl.appendChild(starEl);
    }

    dateEl.innerHTML = formatRatingDate(date);

    parentEl.appendChild(historyLineEl);
    historyLineEl.appendChild(flexContainerEl);
    flexContainerEl.appendChild(starsEl);
    flexContainerEl.appendChild(dateAndButtonEl);
    dateAndButtonEl.appendChild(dateEl);
}

function formatDateInput(date) {
    let dateStr = new String();
    if (date && date != "undefined") {
        const reDate = new RegExp("([0-9]+)-([0-9]+)-([0-9]+)");
        const ratingYear = reDate.exec(date)[1];
        let month = reDate.exec(date)[2];
        let day = reDate.exec(date)[3];

        if (month.length == 1) {
            month = "0" + month;
        }

        if (day.length == 1) {
            day = "0" + day;
        }

        dateStr = ratingYear + "-" + month + "-" + day;
    }

    return dateStr;
}

// If enable is false then the elements get disabled. Otherwise they get enabled.
function enableElements(elements, enable = true) {
    for (const elementsKey in elements) {
        enableElement(elements[elementsKey], enable);
    }
}

// If enable is false the element gets disabled. Otherwise it gets enabled.
function enableElement(element, enable = true) {
    if ( element ) {
        element.disabled = ! enable;
    }
}

