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

function rateChangedScore(filmId, uniqueName, score, callback) {
    const watchedCheckboxEl = document.getElementById(`confirm-watched-${filmId}`);
    const watched = watchedCheckboxEl.checked;

    rateFilm(filmId, uniqueName, score, watched, callback);
}

function rateFilm(filmId, uniqueName, score, watched, callback, newDate = null, originalDate = null, index = -1) {
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
            renderUpdatedSeenValue(film);
        }
    }

    let watchedParam = 0;
    if (watched) {
        watchedParam = 1;
    }

    let params = "";
    params += "&json=1";
    params += `&fid=${filmId}`;
    params += uniqueName ? `&un=${uniqueName}` : "";
    params += `&s=${score}`;
    params += `&w=${watchedParam}`;
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
    const watchedEl = document.getElementById(`rating-watched-${filmId}-${ratingIndex}`);
    let watched = watchedEl.classList.contains("watched-on");

    if ( ! (originalScore >= 0 || originalScore <= 10) ) {
        // There is no original rating. New ratings always get set to watched.
        watched = true;
    }

    if (active) {
        confirmRating(filmId, uniqueName, score, watched, originalScore);
    }
    else if (score != originalScore) {
        const originalDate = document.getElementById(`rate-${uniqueName}-${score}-${ratingIndex}`).getAttribute('data-date');
        const newDate = originalDate;
        rateFilm(filmId, uniqueName, score, watched, renderEditRating, newDate, originalDate, ratingIndex);
    }
}

function mouseoverStar(score, filmId, uniqueName, ratingIndex) {
    if ( ratingIndex && ratingIndex == -1 ) {
        highlightWatched(true, filmId, ratingIndex);
    }
    toggleHighlightStars(score, uniqueName, ratingIndex);
    setYourScoreElementValue(score, uniqueName, ratingIndex);
}

function mouseoutStar(score, filmId, uniqueName, ratingIndex) {
    if ( ratingIndex && ratingIndex == -1 ) {
        highlightWatched(false, filmId, ratingIndex);
    }
    toggleHighlightStars(score, uniqueName, ratingIndex);
    resetYourScoreElementValue(uniqueName, ratingIndex);
}

function toggleHighlightStars(score, uniqueName, ratingIndex) {
    const starsParent = document.getElementById(`rating-stars-${uniqueName}-${ratingIndex}`);
    toggleHighlightStars2(score, starsParent);
}

function toggleHighlightStars2(score, starsParent) {
    if (starsParent) {
        const starEls = starsParent.getElementsByClassName("rating-star");


        for (let i=0; i < score; i++) {
            starEls[i].toggleAttribute("star-highlight");
        }
    }
}

function highlightWatched(on, filmId, ratingIndex) {

    const watchedEl = document.getElementById(`rating-watched-${filmId}-${ratingIndex}`);

    if (on) {
        watchedEl.classList.add("highlight");
    }
    else {
        watchedEl.classList.remove("highlight");
    }
}

function clickWatched(filmId, active, ratingIndex) {
    const watchedEl = document.getElementById(`rating-watched-${filmId}-${ratingIndex}`);
    const uniqueName = watchedEl.getAttribute("data-uniquename");
    const originalScoreEl = document.getElementById(`original-score-${uniqueName}-${ratingIndex}`);
    const originalScore = originalScoreEl.getAttribute('data-score');
    const originalDateEl = document.getElementById(`original-date-${uniqueName}-${ratingIndex}`);
    const originalDate = originalDateEl.getAttribute('data-date');

    if ( watchedEl == null ) {
        return;
    }

    toggleButtonOnClass( watchedEl.getAttribute("id") );

    const turnOn = ! watchedEl.classList.contains("watched-on");
    const hasScore = originalScore > 0 || originalScore <= 10

    let callback = renderActiveRating;
    if ( ! active ) {
        callback = renderEditRating;
    }

    if ( !hasScore && turnOn ) {
        // This is new rating
        rateFilm(filmId, null, 0, turnOn, callback, null, null, ratingIndex);
    }
    else {
        // Clicked the eye on an existing rating
        if ( active ) {
            confirmWatchedActiveRating(filmId, uniqueName, turnOn, originalScore, originalDate);
        }
        else {
            rateFilm(filmId, null, originalScore, turnOn, callback, originalDate, originalDate, ratingIndex);
        }
    }
}

function showRatingConfirmationDialog(uniqueName) {

    // Hide the action area & show the dialog
    const actionAreaEl = document.getElementById("action-area-" + uniqueName);
    const confirmationEl = document.getElementById("rate-confirmation-" + uniqueName);

    actionAreaEl.setAttribute("hidden", true);
    confirmationEl.removeAttribute("hidden");

}

function hideRatingConfirmationDialog(uniqueName) {

    const actionAreaEl = document.getElementById("action-area-" + uniqueName);
    const confirmationEl = document.getElementById("rate-confirmation-" + uniqueName);

    confirmationEl.setAttribute("hidden", true);
    confirmationEl.innerHTML = "";
    actionAreaEl.removeAttribute("hidden");
}

function createButton(text, type = "primary", clickHandler = null) {

    const buttonEl = document.createElement("button");
    buttonEl.setAttribute("type", "button");
    buttonEl.setAttribute("class", "btn btn-" + type);

    if ( text ) {
        buttonEl.innerHTML = text;
    }

    if ( clickHandler ) {
        buttonEl.addEventListener("click", clickHandler);
    }

    return buttonEl;

}

function confirmRating(filmId, uniqueName, score, watched, originalScore) {

    const row1El = document.createElement("div");
    const row2El = document.createElement("div");
    row2El.setAttribute("class", "pt-1");

    const cancelHandler = function () { hideRatingConfirmationDialog(uniqueName); };
    const cancelButtonEl = createButton("Cancel", "link", cancelHandler);
    cancelButtonEl.classList.add("btn-sm");

    const confirmationEl = document.getElementById("rate-confirmation-" + uniqueName);
    const ratingHasScore = originalScore > 0;

    if ( score == originalScore ) {
        // Button - Rate score Again
        // Button - Remove
        // Button - Cancel

        showRatingConfirmationDialog(uniqueName);

        const rateHandler = function () { rateFilm(filmId, uniqueName, score, watched, renderActiveRating); hideRatingConfirmationDialog(uniqueName); };
        const rateButtonEl = createButton("Rate " + score + " Again", "primary", rateHandler);

        const removeHandler = function () { rateFilm(filmId, uniqueName, -1, watched, renderActiveRating); hideRatingConfirmationDialog(uniqueName); };
        const removeButtonEl = createButton("Remove Rating", "secondary", removeHandler);
        removeButtonEl.classList.add("btn-sm");

        row1El.append(rateButtonEl);
        row2El.append(removeButtonEl);
        row2El.append(cancelButtonEl);

    }
    else if ( ratingHasScore && !watched ) {
        // Checkbox - I watched it today
        // Button - Rate score

        showRatingConfirmationDialog(uniqueName);

        const watchedChxEl = document.createElement("input");
        const watchedChxLabelEl = document.createElement("label");

        watchedChxEl.setAttribute("id", `confirm-watched-${filmId}`);
        watchedChxEl.setAttribute("type", "checkbox");
        watchedChxEl.setAttribute("class", "switch");
        watchedChxEl.checked = true;
        watchedChxLabelEl.setAttribute("for", `confirm-watched-${filmId}`);
        watchedChxLabelEl.setAttribute("class", "ml-1");
        watchedChxLabelEl.innerHTML = "Watched it today";

        const rateHandler = function () { rateChangedScore(filmId, uniqueName, score, renderActiveRating); hideRatingConfirmationDialog(uniqueName); };
        const rateButtonEl = createButton("Rate " + score, "primary", rateHandler);

        row1El.append(watchedChxEl);
        row1El.append(watchedChxLabelEl);
        row2El.append(rateButtonEl);
        row2El.append(cancelButtonEl);

    }
    else {

        // No need to confirm. A new score and by default set watched to true.
        // The user can change to rating to not-watched later if they want to.
        rateFilm(filmId, uniqueName, score, true, renderActiveRating);
        return;

    }

    confirmationEl.append(row1El);
    confirmationEl.append(row2El);

}

function confirmWatchedActiveRating(filmId, uniqueName, turnOnWatched, originalScore, originalDate) {

    const row1El = document.createElement("div");
    const row2El = document.createElement("div");
    row2El.setAttribute("class", "pt-1");

    const cancelHandler = function () { hideRatingConfirmationDialog(uniqueName); };
    const cancelButtonEl = createButton("Cancel", "link", cancelHandler);
    cancelButtonEl.classList.add("btn-sm");

    const confirmationEl = document.getElementById("rate-confirmation-" + uniqueName);

    const ratingHasScore = originalScore > 0;

    if ( turnOnWatched && !ratingHasScore ) {
        // New rating with no score. No need to confirm.
        rateFilm(filmId, uniqueName, 0, turnOnWatched, renderActiveRating);
        return;
    }

    showRatingConfirmationDialog(uniqueName);

    if ( turnOnWatched ) {
        // Button - Rate Without A Score
        // Button - Mark Rating As Watched
        // Button - Cancel

        const rateNoScoreHandler = function () { rateFilm(filmId, uniqueName, 0, turnOnWatched, renderActiveRating); hideRatingConfirmationDialog(uniqueName); };
        const rateNoScoreButtonEl = createButton(`Rate Without A Score`, "primary", rateNoScoreHandler);

        const markRatingAsWatchedHandler = function () { rateFilm(filmId, uniqueName, originalScore, turnOnWatched, renderActiveRating, originalDate, originalDate); hideRatingConfirmationDialog(uniqueName); };
        const markRatingAsWatchedButtonEl = createButton(`Mark Rating As Watched`, "primary", markRatingAsWatchedHandler);

        row1El.append(rateNoScoreButtonEl);
        row2El.append(markRatingAsWatchedButtonEl);
        row2El.append(cancelButtonEl);

    }
    else {
        // Turn Off Watched

        if ( ratingHasScore ) {
            // Button - Rate Without A Score
            // Button - Mark Rating As Unwatched
            // Button - Cancel

            const rateNoScoreHandler = function () { rateFilm(filmId, uniqueName, 0, true, renderActiveRating); hideRatingConfirmationDialog(uniqueName); };
            const rateNoScoreButtonEl = createButton(`Rate Without A Score`, "primary", rateNoScoreHandler);

            const turnOffWatchedHandler = function () { rateFilm(filmId, uniqueName, originalScore, false, renderActiveRating, originalDate, originalDate); hideRatingConfirmationDialog(uniqueName); };
            const turnOffWatchedButtonEl = createButton(`Unwatched Rating`, "primary", turnOffWatchedHandler);

            row1El.append(rateNoScoreButtonEl);
            row2El.append(turnOffWatchedButtonEl);

        }
        else {
            // Button - Watched Again
            // Button - Remove Viewing
            // Button - Cancel

            const watchedAgainHandler = function () { rateFilm(filmId, uniqueName, 0, true, renderActiveRating); hideRatingConfirmationDialog(uniqueName); };
            const watchedAgainButtonEl = createButton(`Watched Again`, "primary", watchedAgainHandler);

            const removeHandler = function () { rateFilm(filmId, uniqueName, -1, turnOnWatched, renderActiveRating, originalDate, originalDate); hideRatingConfirmationDialog(uniqueName); };
            const removeButtonEl = createButton("Remove Viewing", "secondary", removeHandler);
            removeButtonEl.classList.add("btn-sm");

            row1El.append(watchedAgainButtonEl);
            row2El.append(removeButtonEl);

        }

        row2El.append(cancelButtonEl);

    }

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

function toggleButtonOnClass(elementId) {
    const el = document.getElementById(elementId);
    if ( el == null ) {
        return;
    }

    el.toggleAttribute("btn-toggle-on");
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
    const stars = el.getElementsByClassName("rating-star");
    for (i = 0; i < stars.length; i++) {
        addStarListener(stars[i], active);
    }

    addWatchedListeners(el, active);
}

function addWatchedListeners(ratingStarsEl, active) {

    const eyes = ratingStarsEl.getElementsByClassName("rating-watched");
    if ( eyes.length > 0 ) {
        const watchedEl = eyes[0];
        addWatchedListener(watchedEl, active);
    }

}

function addStarListener(starEl, active) {
	if (starEl != null) {
		const filmId = starEl.getAttribute('data-film-id');
		const uniqueName = starEl.getAttribute('data-uniquename');
		const score = starEl.getAttribute('data-score');
        const ratingIndex = starEl.getAttribute("data-index");

		const mouseoverHandler = function () {  mouseoverStar(score, filmId, uniqueName, ratingIndex); };
		const mouseoutHandler = function () { mouseoutStar(score, filmId, uniqueName, ratingIndex); };
		const clickHandler = function () { clickStar(filmId, uniqueName, score, active, ratingIndex); };

        starEl.addEventListener("mouseover", mouseoverHandler);
        starEl.addEventListener("mouseout", mouseoutHandler);
        starEl.addEventListener("click", clickHandler);
	}
}

function addWatchedListener(watchedEl, active) {
    if ( watchedEl == null ) {
        return;
    }

    const filmId = watchedEl.getAttribute('data-film-id');
    const ratingIndex = watchedEl.getAttribute("data-index");

    const mouseoverHandler = function () { highlightWatched(true, filmId, ratingIndex); };
    const mouseoutHandler = function () { highlightWatched(false, filmId, ratingIndex); };
    const clickHandler = function () { clickWatched(filmId, active, ratingIndex); };

    watchedEl.addEventListener("mouseover", mouseoverHandler);
    watchedEl.addEventListener("mouseout", mouseoutHandler);
    watchedEl.addEventListener("click", clickHandler);
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

function getPosterParentElement(film) {
    if ( pageId == SITE_PAGE.Detail ) {
        return document.getElementById("detail-poster-container");
    }
    else if ( pageId == SITE_PAGE.Edit ) {
        return document.getElementById("detail-poster-container");
    }
    else if ( pageId == SITE_PAGE.Search ) {
        const searchUniqueName = getUniqueName(film, DATA_API_DEFAULT);
        return document.getElementById(`search-poster-${searchUniqueName}`);
    }
    else {
        return null;
    }
}

function renderPosterWrapper(film, overlay) {
    const posterParentEl = getPosterParentElement(film);

    if ( posterParentEl ) {
        if ( film?.filmId ) {
            renderPoster(film, overlay, posterParentEl);
        }
        else {
            renderNoRsPoster(film, posterParentEl);
        }
    }
}

function renderPoster(film, overlay, parentEl) {
    const filmId = film?.filmId;
    if ( ! filmId || filmId == "undefined") {
        return;
    }

    const isEpisode = film.contentType == CONTENT_TV_EPISODE;
    const posterClass = overlay ? "watchit-overlay" : "watchit-normal";
    const internalUniqueName = getUniqueName(film, SOURCE_NAME.Internal);
    let href = `/php/detail.php?i=${filmId}&ct=${film.contentType}`;
    const imageUrl = RS_URL_BASE + film.image;

    if ( isEpisode ) {
        href += `pid=${film.parentId}`;
    }

    const seenToggled = film.user?.seen ? "btn-toggle-on" : "";
    const neverWatchToggled = film.user?.neverWatch ? "btn-toggle-on" : "";

    let imageAltText = film.episodeTitle ? film.episodeTitle : film.title;
    imageAltText = imageAltText.replace(/\"/g, '\\\"').replace(/\'/g, "\\\'");

    const posterEl = document.createElement("poster");
    const tableEl = document.createElement("table");
    const tableBodyEl = document.createElement("tbody");
    const tableRowEl = document.createElement("tr");
    const paddingColumnLeftEl = document.createElement("td");
    const contentColumnEl = document.createElement("td");
    const paddingColumnRightEl = document.createElement("td");
    const linkEl = document.createElement("a");
    const imageEl = document.createElement("img");
    const foolishEl = document.createElement("span"); // This only needed for spacing between the image and the bottom border
    const watchItContainerEl = document.createElement("watchit-btn-container");
    const flexWrapperEl = document.createElement("div");
    const buttonContainerEl = document.createElement("div");
    const seenBtnEl = document.createElement("i");
    const neverWatchBtnEl = document.createElement("i");

    posterEl.id = `poster-${internalUniqueName}`;
    posterEl.setAttribute("class", posterClass);
    posterEl.setAttribute("data-filmid", filmId);
    paddingColumnLeftEl.setAttribute("class", "pad-left pl-1 pr-0");
    contentColumnEl.setAttribute("class", "align-middle p-0"); // vertical centering
    paddingColumnRightEl.setAttribute("class", "pad-right pl-1 pr-0");
    linkEl.id = `poster-image-${filmId}`;
    linkEl.href = href;
    linkEl.style = `background-image: url('${imageUrl}');`;
    imageEl.src = imageUrl;
    imageEl.alt = imageAltText;
    foolishEl.setAttribute("class", "px-1");
    watchItContainerEl.id = `watchit-btn-container-${filmId}`;
    watchItContainerEl.setAttribute("class", posterClass);
    flexWrapperEl.setAttribute("class", "d-flex");
    buttonContainerEl.id = `poster-btn-container-${filmId}`;
    seenBtnEl.id = `seen-btn-${filmId}`;
    seenBtnEl.setAttribute("class", `fas fa-eye fa-xs ${seenToggled}`);
    neverWatchBtnEl.id = `never-watch-btn-${filmId}`;
    neverWatchBtnEl.setAttribute("class", `fas fa-ban fa-xs pl-1 ${neverWatchToggled}`);

    // Disable the parent element's hover feature while the user is hovering on the WatchIt buttons
    watchItContainerEl.onmouseenter = parentEl.onmouseleave;
    watchItContainerEl.onmouseleave = parentEl.onmouseenter;

    if ( overlay ) {
        imageEl.hidden = true;
    }
    else {
        foolishEl.hidden = true;
    }

    if ( isEpisode ) {
        imageEl.setAttribute("class", "img-episode");
        linkEl.classList.add("img-episode");
    }

    posterEl.appendChild(tableEl);
    tableEl.appendChild(tableBodyEl);
    tableBodyEl.appendChild(tableRowEl);
    //tableRowEl.appendChild(paddingColumnLeftEl);
    tableRowEl.appendChild(contentColumnEl);
    //tableRowEl.appendChild(paddingColumnRightEl);
    contentColumnEl.appendChild(linkEl);
    contentColumnEl.appendChild(watchItContainerEl);
    linkEl.appendChild(imageEl);
    linkEl.appendChild(foolishEl);
    watchItContainerEl.appendChild(flexWrapperEl);
    flexWrapperEl.appendChild(buttonContainerEl);
    buttonContainerEl.appendChild(seenBtnEl);
    buttonContainerEl.appendChild(neverWatchBtnEl);

    const existingPosterEls = parentEl.getElementsByTagName("poster");
    if ( existingPosterEls.length > 0 ) {
        parentEl.removeChild( existingPosterEls[0] );
    }
    parentEl.appendChild(posterEl);

    addWatchItButtonListeners(film?.filmId);

    return posterEl;
}

function renderNoRsPoster(film, parentEl) {
    const sourceJson = getSourceJson(film, DATA_API_DEFAULT);
    const imageUrl = sourceJson?.image;

    const posterEl = document.createElement("poster");
    const imageEl = document.createElement("img");

    imageEl.src = imageUrl;
    imageEl.alt = film.episodeTitle ? film.episodeTitle : film.title;

    posterEl.appendChild(imageEl);
    parentEl.appendChild(posterEl);
}

function setPosterMode(film, overlay) {

    const filmId = film.filmId;
    const internalUniqueName = getUniqueName(film, SOURCE_NAME.Internal);
    const posterEl = document.getElementById(`poster-${internalUniqueName}`);
    const linkEl = document.getElementById(`poster-image-${filmId}`);
    const imageEl = linkEl.getElementsByTagName("img")[0];
    const foolishEl = linkEl.getElementsByTagName("span")[0];
    const watchItContainerEl = document.getElementById(`watchit-btn-container-${filmId}`);

    if ( overlay ) {

        imageEl.hidden = true;
        foolishEl.removeAttribute("hidden");

    }
    else {

        imageEl.removeAttribute("hidden");
        foolishEl.hidden = true;

    }

    const watchItClass = overlay ? "watchit-overlay" : "watchit-normal";

    posterEl.classList.remove("watchit-normal");
    posterEl.classList.remove("watchit-overlay");
    posterEl.classList.add(watchItClass);
    watchItContainerEl.classList.remove("watchit-normal");
    watchItContainerEl.classList.remove("watchit-overlay");
    watchItContainerEl.classList.add(watchItClass);

}

function renderUpdatedSeenValue(film) {
    const filmId = film?.filmId;

    if ( ! filmId ) {
        return;
    }

    const seenBtnEl = document.getElementById(`seen-btn-${filmId}`);

    if ( seenBtnEl ) {
        const toggleClass = "btn-toggle-on";
        seenBtnEl.classList.remove(toggleClass);

        if ( film?.user?.seen ) {
            seenBtnEl.classList.add(toggleClass);
        }
    }

}

function resizeHeightToMatchElements(a, b) {

    if ( ! ( a && b )) {
        return false;
    }

    // Resize the shorter element to match the height of the taller element
    const heightA = a.getBoundingClientRect().height;
    const heightB = b.getBoundingClientRect().height;

    if ( heightA == heightB ) {
        return false;
    }

    let tallerEl  = heightA > heightB ? a : b;
    let shorterEl = heightA < heightB ? a : b;

    const newHeight = tallerEl.getBoundingClientRect().height;
    let style = shorterEl.getAttribute("style") + "; height: " + newHeight + "px";
    shorterEl.setAttribute("style", style);
    shorterEl.classList.add("resized");

    return true;

}

