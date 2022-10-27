
function buildRatingElement(film, ratingDate = null)
{
    let rsUniqueName = "";
    const ratingIndex = -1;

    const rsSource = getSourceJson(film, "RatingSync");
    if (rsSource && rsSource != "undefined") {
        rsUniqueName = rsSource.uniqueName;
    }

    const starsEl = document.createElement("ratingStars");
    starsEl.setAttribute("class", "rating-stars");
    starsEl.setAttribute("id", `rating-stars-${rsUniqueName}-${ratingIndex}`);

    return starsEl;
}

function buildViewingHistoryElement(film)
{
    const filmId = getFilmId(film);

    const viewingHistoryEl = document.createElement("div");

    viewingHistoryEl.setAttribute("class", "viewing-history d-flex flex-row pb-2");
    viewingHistoryEl.setAttribute("id", `viewing-history-${filmId}`);

    viewingHistoryEl.appendChild( buildRatingHistoryElement(film) );
    viewingHistoryEl.appendChild( buildViewingButtonsElement(film) );

    return viewingHistoryEl;
}

function buildRatingHistoryElement(film)
{
    const filmId = getFilmId(film);
    const ratingHistoryEl = document.createElement("div");
    ratingHistoryEl.setAttribute("class", "mr-2");
    ratingHistoryEl.setAttribute("id", `rating-history-${filmId}`);
    ratingHistoryEl.hidden = true;

    const rsSource = getSourceJson(film, "RatingSync");
    const rsUniqueName = rsSource?.uniqueName;
    const activeRating = rsSource?.rating;
    const activeRatingDate = activeRating?.yourRatingDate;

    if ( ! validRatingScore(activeRating) && rsSource?.archiveCount < 1 ) {
        return ratingHistoryEl;
    }

    const dateStr = getRatingDateMessageText(activeRatingDate, film);

    const historyRatingDateBtnEl = document.createElement("button");
    const historyRatingDateEl = document.createElement("rating-date");
    const historyDropBtnEl = document.createElement("button");
    const historyToggleEl = document.createElement("span");
    const historyCaretEl = document.createElement("span");
    const historyFormEl = document.createElement("form");
    const historyFormInputFilmIdEl = document.createElement("input");
    const historyFormInputIndexEl = document.createElement("input");
    const historyMenuEl = document.createElement("div");

    historyRatingDateBtnEl.setAttribute("class", "rating-history btn pl-0 pr-1 py-0");
    historyRatingDateEl.setAttribute("class", "small");
    historyDropBtnEl.setAttribute("class", "rating-history btn-rating-history btn dropdown-toggle-split p-0 align-middle");
    historyToggleEl.setAttribute("class", "sr-only");
    historyCaretEl.setAttribute("class", "fas fa-caret-down");
    historyMenuEl.setAttribute("class", "dropdown-menu");

    historyRatingDateBtnEl.setAttribute("type", "button");
    historyRatingDateBtnEl.setAttribute("disabled", "true");
    historyRatingDateEl.setAttribute("id", `rating-date-${rsUniqueName}`);
    historyDropBtnEl.setAttribute("type", "button");
    historyDropBtnEl.setAttribute("id", `rating-history-menu-btn-${filmId}`);
    historyDropBtnEl.setAttribute("data-toggle", "dropdown");
    historyDropBtnEl.setAttribute("aria-expanded", "false");
    historyDropBtnEl.setAttribute("data-reference", "parent");
    historyFormEl.setAttribute("action", "/php/edit.php");
    historyFormEl.setAttribute("id", `rating-history-form-${filmId}`);
    historyFormInputFilmIdEl.setAttribute("id", `param-rating-history-filmid-${filmId}`);
    historyFormInputFilmIdEl.setAttribute("name", "i");
    historyFormInputFilmIdEl.setAttribute("hidden", "true");
    historyFormInputIndexEl.setAttribute("id", `param-rating-history-index-${filmId}`);
    historyFormInputIndexEl.setAttribute("name", "ri");
    historyFormInputIndexEl.setAttribute("hidden", "true");
    historyMenuEl.setAttribute("id", `rating-history-menu-ref-${filmId}`);
    historyMenuEl.setAttribute("aria-labelledby", `rating-history-menu-btn-${filmId}`);

    historyRatingDateEl.innerText = dateStr;
    historyToggleEl.innerText = "Toogle Dropdown";
    historyFormInputFilmIdEl.setAttribute("value", filmId);

    ratingHistoryEl.appendChild(historyRatingDateBtnEl);
    ratingHistoryEl.appendChild(historyDropBtnEl);
    ratingHistoryEl.appendChild(historyFormEl);
    historyRatingDateBtnEl.appendChild(historyRatingDateEl);
    historyDropBtnEl.appendChild(historyToggleEl);
    historyDropBtnEl.appendChild(historyCaretEl);
    historyFormEl.appendChild(historyFormInputFilmIdEl);
    historyFormEl.appendChild(historyFormInputIndexEl);
    historyFormEl.appendChild(historyMenuEl);

    return ratingHistoryEl;
}

function buildViewingButtonsElement(film) {
    const filmId = film?.filmId;
    const watchItOptionsEl = document.createElement("span");
    const seenBtnEl = document.createElement("button");
    const neverBtnEl = document.createElement("button");

    seenBtnEl.setAttribute("id", `seen-btn-${filmId}`);
    seenBtnEl.setAttribute("class", "watchit-buttons btn-toggle far fa-eye fa-sm");
    neverBtnEl.setAttribute("id", `never-watch-btn-${filmId}`);
    neverBtnEl.setAttribute("class", "watchit-buttons btn-toggle fas fa-ban fa-sm ml-2");

    if ( film?.user?.seen ) {
        seenBtnEl.classList.add("btn-toggle-on");
    }

    if ( film?.user?.neverWatch ) {
        neverBtnEl.classList.add("btn-toggle-on");
    }

    watchItOptionsEl.appendChild(seenBtnEl);
    watchItOptionsEl.appendChild(neverBtnEl);

    return watchItOptionsEl;
}

function renderRatingDate(film) {
    const rsSource = getSourceJson(film, "RatingSync");
    if (!rsSource || rsSource == "undefined") {
        return null;
    }

    const uniqueName = rsSource.uniqueName;
    const yourRatingDate = rsSource.rating.yourRatingDate;

    let dateElId = `rating-date-${uniqueName}`;

    const ratingDateEl = document.getElementById(dateElId);
    if (!ratingDateEl) {
        return;
    }

    ratingDateEl.innerHTML = getRatingDateMessageText(yourRatingDate, film);
}

function getRatingDateMessageText(yourRatingDate, film) {
    let dateMsgText;

    const seen = film?.user?.seen;
    const seenDate = film?.user?.seenDate;

    if (yourRatingDate && yourRatingDate != "undefined") {
        dateMsgText = "Rated on " + formatRatingDate(yourRatingDate);
    }
    else if (seen && seen == true && seenDate?.length > 0) {
        dateMsgText = `Watched on ${seenDate}`;
    }
    else {
        dateMsgText = "Past viewings";
    }

    return dateMsgText;
}

function formatRatingDate(date) {
    let dateStr = new String();
    if (date && date != "undefined") {
        const reDate = new RegExp("([0-9]+)-([0-9]+)-([0-9]+)");
        const ratingYear = reDate.exec(date)[1];
        const month = reDate.exec(date)[2];
        const day = reDate.exec(date)[3];
        dateStr = month + "/" + day + "/" + ratingYear;
    }

    return dateStr;
}

/**
 * Used as a callback from rateFilm()
 *
 * @param film
 * @param index Ignored
 */
function renderActiveRating(film, index) {
    renderOneRatingStars(film);
    renderRatingDate(film);
    renderWatchItButtons(film, index);
}

function renderOneRatingStars(film, ratingIndex = -1) {
    const rsSource = getSourceJson(film, "RatingSync");
    const uniqueName = rsSource.uniqueName;

    const rating = getRatingFromSource(rsSource, ratingIndex);

    const yourScore = rating?.yourScore;
    const ratingDate = rating?.yourRatingDate;

    const ratingStarsElId = `rating-stars-${uniqueName}-${ratingIndex}`;
    let ratingStarsEl = document.getElementById(ratingStarsElId);
    if (!ratingStarsEl) {
        return;
    }
    ratingStarsEl.innerHTML = "";

    let toggleOn = "";
    if ( rating.watched ) {
        toggleOn = "watched-on";
    }

    // Create Elements
    const originalScoreEl = document.createElement("div");
    const originalDateEl = document.createElement("div");
    const watchedEl = document.createElement("rating-watched");

    // Set Attributes
    originalScoreEl.setAttribute("id", `original-score-${uniqueName}-${ratingIndex}`);
    originalScoreEl.setAttribute("data-score", yourScore);
    originalScoreEl.setAttribute("hidden", true);
    originalDateEl.setAttribute("id", `original-date-${uniqueName}-${ratingIndex}`);
    originalDateEl.setAttribute("data-date", ratingDate);
    originalDateEl.setAttribute("hidden", true);
    watchedEl.setAttribute("id", `rating-watched-${film.filmId}-${ratingIndex}`);
    watchedEl.setAttribute("class", `rating-watched ${toggleOn} far fa-eye fa-xs mr-1`);
    watchedEl.setAttribute("data-film-id", `${film.filmId}`);
    watchedEl.setAttribute("data-uniquename", `${uniqueName}`);
    watchedEl.setAttribute("data-index", `${ratingIndex}`);

    // Star Values
    var fullStars = yourScore;
    var emptyStars = 10 - yourScore;
    var starScore = 1;
    while (fullStars > 0) {
        const starEl = buildStarElement(film.filmId, uniqueName, ratingDate, starScore, ratingIndex);
        starEl.setAttribute("class", "rating-star fa-star fas fa-xs");
        ratingStarsEl.appendChild(starEl);
        fullStars = fullStars - 1;
        starScore++;
    }
    while (emptyStars > 0) {
        const starEl = buildStarElement(film.filmId, uniqueName, ratingDate, starScore, ratingIndex);
        starEl.setAttribute("class", "rating-star fa-star far fa-xs");
        if ( yourScore == null && rating.watched ) {
            starEl.classList.add("no-score");
        }
        ratingStarsEl.appendChild(starEl);
        emptyStars = emptyStars - 1;
        starScore++;
    }

    // Score Values
    const scoreEl = buildScoreElement(yourScore, uniqueName, ratingIndex);

    // Append Elements
    ratingStarsEl.insertBefore(watchedEl, ratingStarsEl.children[0]);
    ratingStarsEl.appendChild(scoreEl);
    ratingStarsEl.appendChild(originalScoreEl);
    ratingStarsEl.appendChild(originalDateEl);

    const active = ratingIndex < 0 ? true :  false;
    if ( ( pageId != SITE_PAGE.Edit || ! active ) && film.filmId > 0 ) {
        addStarListeners(ratingStarsEl, active);
    }

    if ( pageId != SITE_PAGE.Edit && film.filmId > 0 ) {
        renderRatingHistory(film, rsSource);
    }
}

function renderOneRatingForEdit(parentEl, film, uniqueName, active, ratingIndex) {

    // Create elements
    const level1RowEl = document.createElement("div");
    const level2ColEl = document.createElement("div");
    const ratingRowEl = document.createElement("div");
    const dateColEl = document.createElement("div");
    const ratingInfoColEl = document.createElement("div");
    const buttonsColEl = document.createElement("div");
    const dateEl = document.createElement("span");
    const ratingStarsEl = document.createElement("rating-stars");
    const scoreEl = document.createElement("score");
    const deleteBtnEl = document.createElement("button");

    // Get rating info
    const filmId = film.filmId;
    const rsSource = getSourceJson(film, "RatingSync");
    const rating = getRatingFromSource(rsSource, ratingIndex);
    const score = rating?.yourScore;
    const ratingDate = rating?.yourRatingDate;
    const ratingDateFormatted = formatRatingDate(ratingDate);

    // Setup hidden elements for original score & date
    const hiddenOriginalScoreEl = document.createElement("div");
    const hiddenOriginalDateEl = document.createElement("div");
    hiddenOriginalScoreEl.setAttribute("id", `original-score-${uniqueName}-${ratingIndex}`);
    hiddenOriginalScoreEl.setAttribute("hidden", "true");
    hiddenOriginalScoreEl.setAttribute("data-score", `${score}`);
    hiddenOriginalDateEl.setAttribute("id", `original-date-${uniqueName}-${ratingIndex}`);
    hiddenOriginalDateEl.setAttribute("hidden", "true");
    hiddenOriginalDateEl.setAttribute("data-date", `${ratingDate}`);

    // Set attributes
    level1RowEl.setAttribute("class", "row mx-0");
    level2ColEl.setAttribute("class", "col");
    ratingRowEl.setAttribute("class", "row border");
    dateColEl.setAttribute("class", "col edit-rating-date my-auto pr-0");
    ratingInfoColEl.setAttribute("class", "col pl-2 pr-0");
    buttonsColEl.setAttribute("class", "col-auto my-auto");
    dateEl.setAttribute("class", "fa-md");
    ratingStarsEl.setAttribute("id", `rating-stars-${uniqueName}-${ratingIndex}`);
    ratingStarsEl.setAttribute("class", "rating-stars");
    deleteBtnEl.setAttribute("id", `rating-delete-${filmId}-${ratingIndex}`);
    deleteBtnEl.setAttribute("class", "btn btn-danger far fa-trash-alt fa-md disableable");
    deleteBtnEl.setAttribute("onclick", `showConfirmationDeleteRating(${film.filmId}, "${uniqueName}", "${ratingDate}", ${active}, ${ratingIndex})`);

    // Setup archive button
    let archiveBtnEl = null;
    if ( parentEl.childElementCount == 0 && ! active ) {
        // This is the first rating and it is not active. If there is no active rating offer to activate/un-archive it.
        const activeRating = rsSource?.rating;
        const thereIsAnActiveRating = activeRating?.yourRatingDate;
        if ( ! thereIsAnActiveRating ) {
            archiveBtnEl = buildUnarchiveRatingButton(film.filmId, rating, ratingIndex);
        }
    }

    // Append elements
    parentEl.appendChild(level1RowEl);
    level1RowEl.appendChild(level2ColEl);
    level1RowEl.appendChild(hiddenOriginalScoreEl);
    level1RowEl.appendChild(hiddenOriginalDateEl);
    level2ColEl.appendChild(ratingRowEl);
    ratingRowEl.appendChild(dateColEl);
    ratingRowEl.appendChild(ratingInfoColEl);
    ratingRowEl.appendChild(buttonsColEl);
    dateColEl.appendChild(dateEl);
    ratingInfoColEl.appendChild(ratingStarsEl);
    ratingInfoColEl.appendChild(scoreEl);
    if ( archiveBtnEl ) {
        buttonsColEl.appendChild( archiveBtnEl );
    }
    buttonsColEl.appendChild(deleteBtnEl);

    // Values
    dateEl.innerHTML = ratingDateFormatted;

    renderOneRatingStars(film, ratingIndex);
    addWatchItButtonListeners(film?.filmId);
}

/**
 * Used as a callback from rateFilm()
 *
 * @param film
 * @param index
 */
function renderEditRating(film, index) {
    renderOneRatingStars(film, index);
    renderRatingDate(film);
    addWatchItButtonListeners(film?.filmId);
}

function buildStarElement(filmId, uniqueName, date, score, ratingIndex) {
    const starEl = document.createElement("span");
    starEl.setAttribute("data-film-id", filmId);
    starEl.setAttribute("data-uniquename", uniqueName);
    starEl.setAttribute("data-date", date);
    starEl.setAttribute("data-index", ratingIndex);
    starEl.setAttribute("class", "rating-star fa-star fas fa-xs");
    starEl.setAttribute("id", `rate-${uniqueName}-${score}-${ratingIndex}`);
    starEl.setAttribute("data-score", score);

    return starEl;
}

function buildScoreElement(score, uniqueName, ratingIndex) {
    const scoreEl = document.createElement("score");
    const yourScoreEl = document.createElement("yourScore");
    const ofPossibleEl = document.createElement("of-possible");

    scoreEl.setAttribute("id", `score-${uniqueName}-${ratingIndex}`);
    scoreEl.setAttribute("class", "pl-1");
    yourScoreEl.setAttribute("id", `your-score-${uniqueName}-${ratingIndex}`);

    scoreEl.appendChild(yourScoreEl);
    scoreEl.appendChild(ofPossibleEl);

    setYourScoreElementValue(score, uniqueName, ratingIndex, yourScoreEl);
    ofPossibleEl.innerHTML = "/10";

    return scoreEl;
}

function setYourScoreElementValue(score, uniqueName, ratingIndex, yourScoreEl = null) {
    if (yourScoreEl == null) {
        yourScoreEl = document.getElementById(`your-score-${uniqueName}-${ratingIndex}`);
    }

    setYourScoreElementValue2(score, yourScoreEl);
}

function setYourScoreElementValue2(score, yourScoreEl) {
    if (yourScoreEl == null) {
        return;
    }

    const scoreDigit1 = document.createElement("span");
    const scoreDigit2 = document.createElement("span");

    scoreDigit1.setAttribute("class", "score-invisible");

    if ( isNaN(score) || score < 1 ) {
        score = "-";
    }
    else if (score == 10) {
        score = 0;
        scoreDigit1.setAttribute("class", "score-visible");
    }

    scoreDigit1.innerHTML = "1";
    scoreDigit2.innerHTML = `${score}`

    yourScoreEl.innerHTML = "";
    yourScoreEl.appendChild(scoreDigit1);
    yourScoreEl.appendChild(scoreDigit2);
}

function resetYourScoreElementValue(uniqueName, ratingIndex) {
    const originalScore = document.getElementById(`original-score-${uniqueName}-${ratingIndex}`).getAttribute("data-score");

    setYourScoreElementValue(originalScore, uniqueName, ratingIndex);
}

function getRatingFromSource(source, ratingIndex = -1) {
    let rating;
    ratingIndex = parseInt(ratingIndex);
    if ( isNaN(ratingIndex) || ratingIndex < 1 ) {
        // Active rating
        rating = source?.rating;
    }
    else {
        // Archived rating
        rating = source?.archive[ratingIndex - 1];
    }

    return rating;
}

// date format: YYYY-MM-DD
function populateNewRatingModal(score, date, originalDate = "") {

    const scoreInputEl = document.getElementById("new-rating-score");
    const dateInputEl = document.getElementById("new-rating-date");
    const originalDateInputEl = document.getElementById("new-rating-original-date");
    const yourScoreEl = document.getElementById("new-rating-your-score");

    scoreInputEl.value = score;
    dateInputEl.value = date;
    originalDateInputEl.value = originalDate;
    setYourScoreElementValue2(score, yourScoreEl);

    for (let i = 0; i < 10; i++) {

        const starScore = i + 1;
        const starEl = document.getElementById("new-rating-star-" + starScore);

        starEl.classList.remove("fas", "far");

        let starClass = "far";
        if ( i < score ) {
            starClass = "fas";
        }

        starEl.classList.add(starClass);
    }
}

function toggleSeen(btnEl, filmId) {
    if ( ! btnEl )  {
        return;
    }

    const turnOn = ! btnEl.classList.contains("btn-toggle-on");

    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            const response = JSON.parse(xmlhttp.responseText);
            if ( response?.Success != "false" ) {
                const film = response;
                updateContextDataFilmByFilmId(film);
            } else {
                renderAlert("Unable to toggle the \"watched\" button.", ALERT_LEVEL.warning);
                btnEl.classList.toggle("btn-toggle-on");
            }
        }
    }

    let params = "";
    params += `&fid=${filmId}`;
    params += `&seen=${turnOn}`;
    xmlhttp.open("GET", RS_URL_API + "?action=setSeen" + params, true);
    xmlhttp.send();

    btnEl.classList.toggle("btn-toggle-on");
}

function toggleNeverWatchIt(btnEl, filmId) {
    if ( ! btnEl )  {
        return;
    }

    const turnOn = ! btnEl.classList.contains("btn-toggle-on");

    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            const response = JSON.parse(xmlhttp.responseText);
            if ( response?.Success != "false" ) {
                const film = response;
                updateContextDataFilmByFilmId(film);
            } else {
                renderAlert("Unable to toggle the \"do not watch it\" button.", ALERT_LEVEL.warning);
                btnEl.classList.toggle("btn-toggle-on");
            }
        }
    }

    let params = "";
    params += `&fid=${filmId}`;
    params += `&never=${turnOn}`;
    xmlhttp.open("GET", RS_URL_API + "?action=setNeverWatch" + params, true);
    xmlhttp.send();

    btnEl.classList.toggle("btn-toggle-on");
}

function validRatingScore(rating)
{
    if ( rating?.yourScore > 0 ) {
        return true;
    }
    else {
        return false;
    }
}

function renderWatchItButtons(film) {
    if (!film || !film.filmId) {
        return null;
    }

    const filmId = film.filmId;
    const seenBtnEl = document.getElementById(`seen-btn-${filmId}`);
    const neverWatchBtnEl = document.getElementById(`never-watch-btn-${filmId}`);

    if ( film.user?.seen && film.user?.seen == true ) {
        seenBtnEl?.classList.add("btn-toggle-on");
    }
    else {
        seenBtnEl?.classList.remove("btn-toggle-on");
    }

    if ( film.user?.neverWatch && film.user?.neverWatch == true ) {
        neverWatchBtnEl?.classList.add("btn-toggle-on");
    }
    else {
        neverWatchBtnEl?.classList.remove("btn-toggle-on");
    }
}

