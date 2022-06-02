
function getFilmForEditPage(filmId, uniqueName, imdbId, contentType, parentId, seasonNum, episodeNum) {
    getFilmForDetailPage(filmId, uniqueName, imdbId, contentType, parentId, seasonNum, episodeNum);
}

function renderEditRatings(editEl, filmId) {
    const film = getContextDataFilm(filmId);

    if (film == null) {
        editEl.innerHTML = "<H3>Cannot edit because the data is missing.</H3>";
        return;
    }

    let ratingIndex = 0;
    const rsSource = film.sources.find(function (findSource) {  return findSource.name == "RatingSync"; });
    const uniqueName = rsSource.uniqueName;

    renderOneRatingForEdit(editEl, rsSource.rating, filmId, uniqueName, ratingIndex);
    ratingIndex++;

    for (let i=0; i < rsSource.archiveCount; i++) {
        const archivedRating = rsSource.archive[i];
        renderOneRatingForEdit(editEl, archivedRating, filmId, uniqueName, ratingIndex);
        ratingIndex++;
    }
}

function renderOneRatingForEdit(editEl, rating, filmId, uniqueName, ratingIndex) {
    const date = rating.yourRatingDate;
    const score = rating.yourScore;

    if (date == null && score == null) {
        return;
    }

    // The score is shown backwards
    let showYourScore = score;
    if (showYourScore == "10") {
        showYourScore = "01";
    } else if (showYourScore == null || showYourScore == "") {
        showYourScore = "-";
    }

    const starsContainerEl = document.createElement("div");
    const starsEl = document.createElement("ratingStars");
    const scoreEl = document.createElement("score");
    const scoreInfoEl = document.createElement("div");

    starsContainerEl.setAttribute("class", "mt-n2 pt-2");
    starsContainerEl.setAttribute("style", "line-height: 1");
    starsEl.setAttribute("id", `rating-stars-${uniqueName}`);
    starsEl.setAttribute("class", "rating-stars");
    scoreEl.innerHTML = `<of-possible>01/</of-possible><your-score id="your-score-${uniqueName}">${showYourScore}</your-score>\n`;
    scoreInfoEl.setAttribute("id", `original-score-${uniqueName}`);
    scoreInfoEl.setAttribute("data-score", showYourScore);
    scoreInfoEl.setAttribute("hidden", true);

    editEl.appendChild(starsContainerEl);
    starsContainerEl.appendChild(starsEl);
    starsEl.appendChild(scoreInfoEl);
    starsEl.appendChild(scoreEl);

    let fullStars = score;
    let emptyStars = 10 - score;
    let starScore = 10;
    while (emptyStars > 0) {
        const starEl = buildStarElement(filmId, uniqueName, date, starScore, ratingIndex);
        starEl.setAttribute("class", "rating-star fa-star far fa-xs");
        starsEl.appendChild(starEl);
        emptyStars = emptyStars - 1;
        starScore = starScore - 1;
    }
    while (fullStars > 0) {
        const starEl = buildStarElement(filmId, uniqueName, date, starScore, ratingIndex);
        starEl.setAttribute("class", "rating-star fa-star fas fa-xs");
        starsEl.appendChild(starEl);
        fullStars = fullStars - 1;
        starScore = starScore - 1;
    }

    addListenersForEditStars(starsEl);
}

function buildStarElement(filmId, uniqueName, date, score, ratingIndex) {
    const starEl = document.createElement("span");
    starEl.setAttribute("data-film-id", filmId);
    starEl.setAttribute("data-uniquename", uniqueName);
    starEl.setAttribute("data-date", date);
    starEl.setAttribute("data-index", ratingIndex);
    starEl.setAttribute("class", "rating-star fa-star fas fa-xs");
    starEl.setAttribute("id", `rate-${uniqueName}-${score}`);
    starEl.setAttribute("data-score", score);

    return starEl;
}
