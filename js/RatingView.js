
function buildRatingElement(film, ratingDate = null)
{
    const rsSource = getSourceJson(film, "RatingSync");
    if (!rsSource || rsSource == "undefined") {
        return null;
    }

    const rsUniqueName = rsSource.uniqueName;

    const starsEl = document.createElement("ratingStars");
    starsEl.setAttribute("class", "rating-stars");
    starsEl.setAttribute("id", `rating-stars-${rsUniqueName}`);

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

function addListenersForEditStars(el)
{
    if (el == null) {
        return;
    }

    const stars = el.getElementsByClassName("rating-star");
    for (i = 0; i < stars.length; i++) {
        addListenersForOneEditStar(stars[i]);
    }
}

function addListenersForOneEditStar(starEl)
{
    if (starEl == null) {
        return;
    }

    const filmId = starEl.getAttribute('data-film-id');
    const uniqueName = starEl.getAttribute('data-uniquename');
    const score = starEl.getAttribute('data-score');

    const mouseoverHandler = function () { renderYourScore(uniqueName, score, 'new'); };
    const mouseoutHandler = function () { renderYourScore(uniqueName, score, 'original'); };
    const clickHandler = function () { clickStar(filmId, uniqueName, score, active); };

    starEl.addEventListener("mouseover", mouseoverHandler);
    starEl.addEventListener("mouseout", mouseoutHandler);
    starEl.addEventListener("click", clickHandler);
}

function renderRatingDate(film) {
    const rsSource = getSourceJson(film, "RatingSync");
    if (!rsSource || rsSource == "undefined") {
        return null;
    }

    const uniqueName = rsSource.uniqueName;
    const yourRatingDate = rsSource.rating.yourRatingDate;

    const ratingDateEl = document.getElementById("rating-date-" + uniqueName);
    if (!ratingDateEl) {
        return;
    }

    ratingDateEl.innerHTML = getRatingDateMessageText(yourRatingDate);
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

function renderStars(film, active = true) {
    const rsSource = getSourceJson(film, "RatingSync");
    var uniqueName = rsSource.uniqueName;
    var yourScore = rsSource.rating.yourScore;

    var ratingStarsEl = document.getElementById("rating-stars-" + uniqueName);
    if (!ratingStarsEl) {
        return;
    }

    // The score is shown backwards
    var showYourScore = yourScore;
    if (showYourScore == "10") {
        showYourScore = "01";
    } else if (showYourScore == null || showYourScore == "") {
        showYourScore = "-";
    }

    var starsHtml = "";
    var fullStars = yourScore;
    var emptyStars = 10 - yourScore;
    var starScore = 10;
    while (emptyStars > 0) {
        starsHtml = starsHtml + "<span class='rating-star fa-star far fa-xs' id='rate-" + uniqueName + "-" + starScore + "' data-film-id='" + film.filmId + "' data-uniquename='" + uniqueName + "' data-score='" + starScore + "'></span>";
        emptyStars = emptyStars - 1;
        starScore = starScore - 1;
    }
    while (fullStars > 0) {
        starsHtml = starsHtml + "<span class='rating-star fa-star fas fa-xs' id='rate-" + uniqueName + "-" + starScore + "' data-film-id='" + film.filmId + "' data-uniquename='" + uniqueName + "' data-score='" + starScore + "'></span>";
        fullStars = fullStars - 1;
        starScore = starScore - 1;
    }

    var html = "";
    html = html + "    <score>\n";
    html = html + "      <of-possible>01/</of-possible><your-score id='your-score-" + uniqueName + "'>" + showYourScore + "</your-score>\n";
    html = html + "    </score>\n";
    html = html +      starsHtml + "\n";
    html = html + "    <div id='original-score-" + uniqueName + "' data-score='" + showYourScore + "' hidden ></div>\n";

    ratingStarsEl.innerHTML = html;
    addStarListeners(ratingStarsEl, active);

    if (film.filmId > 0) {
        renderRatingHistory(film.filmId, rsSource);
    }
}
