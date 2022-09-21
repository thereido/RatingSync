
function getFilmForEditPage(filmId, uniqueName, imdbId, contentType, parentId, seasonNum, episodeNum) {
    getFilmForFilmPage(filmId, uniqueName, imdbId, contentType, parentId, seasonNum, episodeNum);
}

function renderRsFilmEdit(film, filmEl) {
    renderRsFilmPoster(film, filmEl);

    const editEl = buildFilmEditElement(film);
    let detailEl = filmEl.getElementsByTagName("detail")[0];
    if (detailEl) {
        detailEl.innerHTML = editEl.innerHTML;
    } else {
        filmEl.appendChild(editEl);
    }

    const filmId = getFilmId(film);

    renderEditRatings(filmId);
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

    rateFilm(filmId, uniqueName, score, renderNewRating, date);

}

function renderNewRating(filmId, index) {

    $('#new-rating-modal').modal('hide');
    location.reload(true);

}

function editRatingDelete(filmId, uniqueName, ratingDate) {
    const ratingDateEncoded = encodeURIComponent(ratingDate);
    const operaterId = `deleteRating-${filmId}-${ratingDateEncoded}`;
    var xmlhttp = new XMLHttpRequest();
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
            location.reload();
        }
    }
    let params = "";
    params += `&fid=${filmId}`;
    params += `&un=${uniqueName}`;
    params += `&s=0`;
    params += ratingDateEncoded ? `&d=${ratingDateEncoded}` : "";
    params += ratingDateEncoded ? `&od=${ratingDateEncoded}` : "";
    xmlhttp.open("GET", RS_URL_API + "?action=setRating" + params, true);
    xmlhttp.send();
}