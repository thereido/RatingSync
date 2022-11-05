
function getFilmForEditPage(filmId, uniqueName, imdbId, contentType, parentId, seasonNum, episodeNum) {
    getFilmForFilmPage(filmId, uniqueName, imdbId, contentType, parentId, seasonNum, episodeNum);
}

function renderRsFilmEdit(film, filmEl) {
    renderPosterWrapper(film, false);

    const editEl = buildFilmEditElement(film);
    let detailEl = filmEl.getElementsByTagName("detail")[0];
    if (detailEl) {
        detailEl.innerHTML = editEl.innerHTML;
    } else {
        filmEl.appendChild(editEl);
    }

    const filmId = getFilmId(film);

    renderOneRatingStars(film);
    renderEditRatings(filmId);

    addActiveButtonListeners(film);
}

function renderEditRatings(filmId) {
    const film = getContextDataFilm(filmId);
    const editRatingsEl = document.getElementById(`edit-ratings`);

    if (film == null) {
        editRatingsEl.innerHTML = "<H3>Cannot edit because the data is missing.</H3>";
        return;
    }

    const rsSource = film.sources.find(function (findSource) {  return findSource.name == "RatingSync"; });
    const uniqueName = rsSource.uniqueName;

    let ratingIndex = 1;
    for (let i=0; i < rsSource.archiveCount; i++) {
        renderOneRatingForEdit(editRatingsEl, film, uniqueName, false, ratingIndex);
        ratingIndex++;
    }
}

function buildFilmEditElement(film) {

    const detailEl = document.createElement("detail");

    detailEl.appendChild( buildTitleLineElement(film) );
    detailEl.appendChild( buildEpisodeTitleLineElement(film) );
    detailEl.appendChild( buildSeasonLineElement(film) );
    detailEl.appendChild( buildEditActiveRatingElement(film) );

    return detailEl;
}

function buildEditActiveRatingElement(film) {

    const rsSource = getSourceJson(film, "RatingSync");
    const uniqueName = rsSource.uniqueName;
    const activeRating = rsSource.rating;
    const ratingScore = activeRating.yourScore;
    const ratingDate = activeRating.yourRatingDate;

    if ( ratingDate == null ) {
        return document.createElement("div");
    }

    const starsContainerEl = document.createElement("div");
    const starsEl = buildRatingElement(film);
    const ratingDateLineEl = document.createElement("div")
    const ratingDateEl = document.createElement("rating-date");
    const dateStr = getRatingDateMessageText(ratingDate, film);
    const inputDateStr = formatDateInput(ratingDate);
    const editBtnEl = document.createElement("button");
    const buttonsEl = document.createElement("div");
    const deleteBtnEl = document.createElement("button");

    starsContainerEl.setAttribute("class", "mt-n2 pt-2");
    starsContainerEl.setAttribute("style", "line-height: 1");
    ratingDateLineEl.setAttribute("class", "rating-history")
    ratingDateEl.setAttribute("class", "small");
    ratingDateEl.setAttribute("id", `rating-date-${uniqueName}`);
    editBtnEl.setAttribute("class", "btn-edit btn far fa-solid fa-pencil fa-sm disableable");
    editBtnEl.setAttribute("id", "active-rating-edit");
    editBtnEl.setAttribute("data-toggle", "modal");
    editBtnEl.setAttribute("data-target", "#new-rating-modal");
    editBtnEl.setAttribute("onclick", `populateNewRatingModal(${ratingScore}, "${inputDateStr}", "${inputDateStr}")`);
    buttonsEl.setAttribute("class", "my-2");
    deleteBtnEl.setAttribute("id", `rating-delete-${film.filmId}`);
    deleteBtnEl.setAttribute("type", "button");
    deleteBtnEl.setAttribute("class", "btn btn-danger btn-sm mr-1 disableable");
    deleteBtnEl.hidden = true;

    ratingDateEl.innerHTML = dateStr;
    deleteBtnEl.innerHTML = "Delete";

    starsContainerEl.appendChild(starsEl);
    starsContainerEl.appendChild(ratingDateLineEl);
    starsContainerEl.appendChild(buttonsEl);
    ratingDateLineEl.appendChild(ratingDateEl);
    ratingDateLineEl.appendChild(editBtnEl);
    buttonsEl.appendChild( buildArchiveRatingButton(film.filmId, activeRating));
    buttonsEl.appendChild(deleteBtnEl);

    return starsContainerEl;
}

function addNewRatingListeners() {
    const filmId = document.getElementById("new-rating-filmid").valueOf();
    const uniqueName = "rs" + filmId;
    const yourScoreEl = document.getElementById("new-rating-your-score");
    const newRatingStarsEl = document.getElementById("new-rating-stars");
    const stars = newRatingStarsEl.getElementsByClassName("rating-star");
    for (let i = 0; i < stars.length; i++) {
        addNewRatingListener(stars[i], filmId, uniqueName, newRatingStarsEl, yourScoreEl);
    }
}

function addNewRatingListener(starEl, filmId, uniqueName, ratingStarsEl, yourScoreEl) {
    if (starEl != null) {
        const score = starEl.getAttribute('data-score');

        const mouseoverHandler = function () {  mouseoverNewStar(score, ratingStarsEl, yourScoreEl); };
        const mouseoutHandler = function () { mouseoutNewStar(score, ratingStarsEl, yourScoreEl); };
        const clickHandler = function () { clickNewStar(score, ratingStarsEl); };

        starEl.addEventListener("mouseover", mouseoverHandler);
        starEl.addEventListener("mouseout", mouseoutHandler);
        starEl.addEventListener("click", clickHandler);
    }
}

function mouseoverNewStar(score, starsParent, yourScoreEl) {
    toggleHighlightStars2(score, starsParent);
    setYourScoreElementValue2(score, yourScoreEl);
}

function mouseoutNewStar(score, starsParent, yourScoreEl) {
    const newRatingScore = document.getElementById("new-rating-score").value;
    toggleHighlightStars2(score, starsParent);
    setYourScoreElementValue2(newRatingScore, yourScoreEl)
}

function clickNewStar(score, ratingStarsEl) {
    const newRatingScoreEl = document.getElementById(`new-rating-score`);
    const stars = ratingStarsEl.getElementsByClassName("rating-star");
    const yourScoreEl = document.getElementById("new-rating-your-score");

    const originalScore = newRatingScoreEl.value;

    if ( score == originalScore ) {
        score = 0;
        const watchedEl = document.getElementById(`new-rating-watched`);
        if ( watchedEl ) {
            watchedEl.checked = true;
        }
    }

    for (let starsIndex = 0; starsIndex < stars.length; starsIndex++) {
        const star = stars[starsIndex];
        let starScore = star.getAttribute("data-score");
        starScore = parseInt( starScore );

        let fontClass = "fas"; // Solid
        if ( starScore > score ) {
            fontClass = "far"; // Regular
        }

        star.classList.remove(["fas"]);
        star.classList.remove(["far"]);
        star.classList.add([fontClass]);
    }

    newRatingScoreEl.value = score;
    setYourScoreElementValue2(score, yourScoreEl);
}

function editRatingCreate() {
    const filmId = document.getElementById("new-rating-filmid").value;
    const uniqueName = document.getElementById("new-rating-uniquename").value;
    const score = document.getElementById("new-rating-score").value;
    const date = document.getElementById("new-rating-date").value;
    const originalDate = document.getElementById("new-rating-original-date").value;
    const watchedEl = document.getElementById(`new-rating-watched`);

    const watched = watchedEl.checked;

    if ( score == "" || score < 0 || score > 10 || date < "1850-01-01" ) {
        return;
    }
    else if ( score == 0 && !watched ) {
        return;
    }

    disableEditPageButtons();
    rateFilm(filmId, uniqueName, score, watched, renderNewRating, date, originalDate);
}

function disableEditPageButtons() {
    const buttonEls = document.getElementsByClassName("disableable");

    enableElements(buttonEls, false);
}

function renderNewRating(filmId, index) {

    $('#new-rating-modal').modal('hide');

    $( document ).ready(function() {
        setTimeout(function () {
                location.reload(true);
            },
            2000);
    });

}

function editRatingDelete(filmId, ratingDate, force = false, ratingIndex = null) {
    const ratingDateEncoded = encodeURIComponent(ratingDate);
    const operaterId = `deleteRating-${filmId}-${ratingDateEncoded}`;
    const xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            let film;
            const response = JSON.parse(xmlhttp.responseText);
            if (response.Success && response.Success == "false") {
                film = getContextDataFilm(filmId);
                const filmMsg = rateFilmResponseTitle(film);
                const msg = `<strong>Unable to delete the rating</strong>.`;
                renderAlert(msg, ALERT_LEVEL.warning, operaterId);
            } else {
                film = response;
                const filmMsg = rateFilmResponseTitle(film);
                updateContextDataFilmByFilmId(film);
                renderAlert(`<strong>Rating deleted</strong>.`, ALERT_LEVEL.success, operaterId);
            }

            $( document ).ready(function() {
                setTimeout(function () {
                        location.reload(true);
                    },
                    2000);
            });
        }
    }

    let forceParam = "false";
    if ( force ) {
        forceParam = "true";
    }

    let params = "";
    params += `&fid=${filmId}`;
    params += `&s=-1`;
    params += ratingDateEncoded ? `&d=${ratingDateEncoded}` : "";
    params += ratingDateEncoded ? `&od=${ratingDateEncoded}` : "";
    params += `&force=${forceParam}`;
    xmlhttp.open("GET", RS_URL_API + "?action=setRating" + params, true);
    disableEditPageButtons(filmId, ratingIndex);
    xmlhttp.send();
}

function showConfirmationDeleteRating(filmId, uniqueName, ratingDate, active, ratingIndex) {

    // Hide the delete button
    const deleteBtnEl = document.getElementById(`rating-delete-${filmId}-${ratingIndex}`)
    deleteBtnEl.setAttribute("hidden", true);

    const cancelButton = document.createElement("button");
    const archiveButton = document.createElement("button");
    const confirmButton = document.createElement("button");
    const buttonParentEl = deleteBtnEl.parentElement;

    // A function to undo the dialog content and show the action area
    const undoDialogFunc = function () {
        cancelButton.setAttribute("hidden", true);
        archiveButton.setAttribute("hidden", true);
        confirmButton.setAttribute("hidden", true);
        deleteBtnEl.removeAttribute("hidden");
    }

    cancelButton.setAttribute("id", `rating-cancel-${uniqueName}-${ratingIndex}`);
    cancelButton.setAttribute("type", "button");
    cancelButton.setAttribute("class", "btn btn-outline-secondary btn-sm mr-1 disableable");
    cancelButton.innerHTML = "Cancel";
    const cancelHandler = function () { undoDialogFunc(); };
    cancelButton.addEventListener("click", cancelHandler);

    archiveButton.setAttribute("id", `rating-archive-${uniqueName}-${ratingIndex}`);
    archiveButton.setAttribute("type", "button");
    archiveButton.setAttribute("class", "btn btn-sm btn-warning mr-1 disableable");
    archiveButton.innerHTML = "Archive";
    const archiveHandler = function () { undoDialogFunc(); editRatingDelete(filmId, `${ratingDate}`, false, `${ratingIndex}`); };
    archiveButton.addEventListener("click", archiveHandler);

    confirmButton.setAttribute("id", `rating-confirm-${uniqueName}-${ratingIndex}`);
    confirmButton.setAttribute("type", "button");
    confirmButton.setAttribute("class", "btn btn-danger btn-sm disableable");
    confirmButton.innerHTML = "Delete";
    const confirmHandler = function () { undoDialogFunc(); editRatingDelete(filmId, `${ratingDate}`, true, `${ratingIndex}`); };
    confirmButton.addEventListener("click", confirmHandler);

    buttonParentEl.append(cancelButton);
    if ( active ) {
        buttonParentEl.append(archiveButton);
    }
    buttonParentEl.append(confirmButton);
}

function buildArchiveRatingButton(filmId, rating) {

    const btnEl = document.createElement("button");
    btnEl.setAttribute("id", `rating-archive-${filmId}`);
    btnEl.setAttribute("type", "button");
    btnEl.setAttribute("class", "btn btn-info btn-sm mr-1 disableable");
    btnEl.innerHTML = "Archive";

    return btnEl;
}

function buildUnarchiveRatingButton(filmId, rating, ratingIndex = -1) {

    const btnEl = document.createElement("button");
    btnEl.setAttribute("id", `rating-activate-${filmId}-${ratingIndex}`);
    btnEl.setAttribute("type", "button");
    btnEl.setAttribute("class", "btn btn-info btn-sm mr-1");
    btnEl.innerHTML = "Un-Archive";
    const archiveHandler = function () { archiveRating(filmId, renderNewRating, rating.yourRatingDate, false, ratingIndex); };
    btnEl.addEventListener("click", archiveHandler);

    return btnEl;
}

function addActiveButtonListeners(film) {

    const filmId = film?.filmId;
    const rsSource = getSourceJson(film, "RatingSync");
    const activeRating = rsSource?.rating;
    const ratingDate = activeRating?.yourRatingDate;

    if ( ratingDate == null || ratingDate == "undefined" ) {
        return;
    }

    const archiveBtnEl = document.getElementById(`rating-archive-${filmId}`);
    const deleteBtnEl = document.getElementById(`rating-delete-${filmId}`);

    const archiveHandler = function () { archiveRating(filmId, renderNewRating, `${ratingDate}`, true); };
    archiveBtnEl.addEventListener("click", archiveHandler);

    const deleteHandler = function () { editRatingDelete(filmId, `${ratingDate}`, true); };
    deleteBtnEl.addEventListener("click", deleteHandler);
}

function archiveRating(filmId, callback, date, archiveIt = true, ratingIndex = null) {
    const operaterId = `archiveRating-${filmId}-${ratingIndex}`;
    const xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            let film;
            let actionName = "archive";
            if ( ! archiveIt ) actionName = "activate"
            const response = JSON.parse(xmlhttp.responseText);
            if (response.Success && response.Success == "false") {
                film = getContextDataFilm(filmId);
                const filmMsg = rateFilmResponseTitle(film);
                const msg = `<strong>Unable to ${actionName} your rating</strong>.<br>"${filmMsg}"`;
                renderAlert(msg, ALERT_LEVEL.warning, operaterId);
            } else {
                film = response;
                const filmMsg = rateFilmResponseTitle(film);
                updateContextDataFilmByFilmId(film);
                renderAlert(`<strong>Rating ${actionName}d</strong>.<br>"${filmMsg}"`, ALERT_LEVEL.success, operaterId);
            }
            callback(film, ratingIndex);
        }
    }

    let archiveParam = "0";
    if ( archiveIt ) {
        archiveParam = "1";
    }

    let params = "";
    params += "&json=1";
    params += `&fid=${filmId}`;
    params += date ? `&d=${date}` : "";
    params += `&archive=${archiveParam}`;
    xmlhttp.open("GET", RS_URL_API + "?action=archiveRating" + params, true);
    disableEditPageButtons(filmId, ratingIndex);

    xmlhttp.send();
    renderAlert('Saving...', ALERT_LEVEL.info, operaterId, 0);
}
