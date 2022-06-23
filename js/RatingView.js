
function buildRatingElement(film, ratingDate = null)
{
    const rsSource = getSourceJson(film, "RatingSync");
    if (!rsSource || rsSource == "undefined") {
        return null;
    }

    const rsUniqueName = rsSource.uniqueName;
    const ratingIndex = -1;

    const starsEl = document.createElement("ratingStars");
    starsEl.setAttribute("class", "rating-stars");
    starsEl.setAttribute("id", `rating-stars-${rsUniqueName}-${ratingIndex}`);

    return starsEl;
}

function buildRatingHistoryElement(film)
{
    const rsSource = getSourceJson(film, "RatingSync");
    if (!rsSource || rsSource == "undefined") {
        return null;
    }

    const filmId = getFilmId(film);
    const rsUniqueName = rsSource.uniqueName;
    const dateStr = getRatingDateMessageText(rsSource.rating.yourRatingDate);

    const historyEl = document.createElement("div");
    const historyFlexEl = document.createElement("div");
    const historyRatingDateBtnEl = document.createElement("button");
    const historyRatingDateEl = document.createElement("rating-date");
    const historyDropBtnEl = document.createElement("button");
    const historyToggleEl = document.createElement("span");
    const historyCaretEl = document.createElement("span");
    const historyFormEl = document.createElement("form");
    const historyFormInputFilmIdEl = document.createElement("input");
    const historyFormInputIndexEl = document.createElement("input");
    const historyMenuEl = document.createElement("div");

    historyEl.setAttribute("class", "btn-group rating-history");
    historyFlexEl.setAttribute("class", "d-flex flex-row");
    historyRatingDateBtnEl.setAttribute("class", "rating-history btn pl-0 pr-1 py-0");
    historyRatingDateEl.setAttribute("class", "small");
    historyDropBtnEl.setAttribute("class", "rating-history btn-rating-history btn dropdown-toggle-split py-0 px-1 align-middle");
    historyToggleEl.setAttribute("class", "sr-only");
    historyCaretEl.setAttribute("class", "fas fa-caret-down");
    historyMenuEl.setAttribute("class", "dropdown-menu");

    historyEl.setAttribute("id", `rating-history-${filmId}`);
    historyEl.setAttribute("hidden", "true");
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

    historyEl.appendChild(historyFlexEl);
    historyFlexEl.appendChild(historyRatingDateBtnEl);
    historyFlexEl.appendChild(historyDropBtnEl);
    historyFlexEl.appendChild(historyFormEl);
    historyRatingDateBtnEl.appendChild(historyRatingDateEl);
    historyDropBtnEl.appendChild(historyToggleEl);
    historyDropBtnEl.appendChild(historyCaretEl);
    historyFormEl.appendChild(historyFormInputFilmIdEl);
    historyFormEl.appendChild(historyFormInputIndexEl);
    historyFormEl.appendChild(historyMenuEl);

    return historyEl;
}

function renderRatingDate(film, ratingIndex = -1) {
    const rsSource = getSourceJson(film, "RatingSync");
    if (!rsSource || rsSource == "undefined") {
        return null;
    }

    const uniqueName = rsSource.uniqueName;
    const yourRatingDate = rsSource.rating.yourRatingDate;

    let dateElId = `rating-date-${uniqueName}-${ratingIndex}`;

    const ratingDateEl = document.getElementById(dateElId);
    if (!ratingDateEl) {
        return;
    }

    if ( pageId == SITE_PAGE.Edit ) {
        // This is the film edit page. Just show the date.
        ratingDateEl.innerHTML = formatRatingDate(yourRatingDate);
    }
    else {
        // This is a page with film details (like detail, ratings and userlist). Show the date with some more text.
        ratingDateEl.innerHTML = getRatingDateMessageText(yourRatingDate);
    }
}

function getRatingDateMessageText(yourRatingDate) {
    let dateMsgText;

    if (yourRatingDate && yourRatingDate != "undefined") {
        dateMsgText = "You rated this " + formatRatingDate(yourRatingDate);
    }
    else {
        dateMsgText = "Past ratings";
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
    renderStarsForOneRating(film);
    renderRatingDate(film);
}

function renderStarsForOneRating(film, ratingIndex = -1) {
    const rsSource = getSourceJson(film, "RatingSync");
    const uniqueName = rsSource.uniqueName;

    let rating;
    ratingIndex = parseInt(ratingIndex);
    if ( ratingIndex == NaN || ratingIndex < 1 ) {
        // Active rating
        rating = rsSource?.rating;
    }
    else {
        // Archived rating
        rating = rsSource?.archive[ratingIndex - 1];
    }

    const yourScore = rating?.yourScore;
    const ratingDate = rating?.yourRatingDate;

    const ratingStarsElId = `rating-stars-${uniqueName}-${ratingIndex}`;
    let ratingStarsEl = document.getElementById(ratingStarsElId);
    if (!ratingStarsEl) {
        return;
    }
    ratingStarsEl.innerHTML = "";

    // The score is shown backwards
    var showYourScore = yourScore;
    if (showYourScore == "10") {
        showYourScore = "01";
    } else if (showYourScore == null || showYourScore == "") {
        showYourScore = "-";
    }

    const scoreEl = document.createElement("score");
    const scoreInfoEl = document.createElement("div");
    const dateInfoEl = document.createElement("div");

    scoreEl.innerHTML = `<of-possible>01/</of-possible><your-score id="your-score-${uniqueName}-${ratingIndex}">${showYourScore}</your-score>\n`;
    scoreInfoEl.setAttribute("id", `original-score-${uniqueName}-${ratingIndex}`);
    scoreInfoEl.setAttribute("data-score", showYourScore);
    scoreInfoEl.setAttribute("hidden", true);
    dateInfoEl.setAttribute("id", `original-date-${uniqueName}-${ratingIndex}`);
    dateInfoEl.setAttribute("data-date", ratingDate);
    dateInfoEl.setAttribute("hidden", true);

    ratingStarsEl.appendChild(scoreEl);
    ratingStarsEl.appendChild(scoreInfoEl);
    ratingStarsEl.appendChild(dateInfoEl);

    var fullStars = yourScore;
    var emptyStars = 10 - yourScore;
    var starScore = 10;
    while (emptyStars > 0) {
        const starEl = buildStarElement(film.filmId, uniqueName, ratingDate, starScore, ratingIndex);
        starEl.setAttribute("class", "rating-star fa-star far fa-xs");
        ratingStarsEl.appendChild(starEl);
        emptyStars = emptyStars - 1;
        starScore = starScore - 1;
    }
    while (fullStars > 0) {
        const starEl = buildStarElement(film.filmId, uniqueName, ratingDate, starScore, ratingIndex);
        starEl.setAttribute("class", "rating-star fa-star fas fa-xs");
        ratingStarsEl.appendChild(starEl);
        fullStars = fullStars - 1;
        starScore = starScore - 1;
    }

    const active = ratingIndex < 0 ? true :  false;
    addStarListeners(ratingStarsEl, active);

    if ( pageId != SITE_PAGE.Edit && film.filmId > 0 ) {
        renderRatingHistory(film.filmId, rsSource);
    }
}

/**
 * Used as a callback from rateFilm()
 *
 * @param film
 * @param index
 */
function renderEditRating(film, index) {
    renderStarsForOneRating(film, index);
    renderRatingDate(film, index);
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

