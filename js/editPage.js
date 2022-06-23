
function getFilmForEditPage(filmId, uniqueName, imdbId, contentType, parentId, seasonNum, episodeNum) {
    getFilmForDetailPage(filmId, uniqueName, imdbId, contentType, parentId, seasonNum, episodeNum);
}

function renderEditRatings(filmId) {
    const film = getContextDataFilm(filmId);
    const editEl = document.getElementById(`edit-ratings-${filmId}`);

    if (film == null) {
        editEl.innerHTML = "<H3>Cannot edit because the data is missing.</H3>";
        return;
    }

    const rsSource = film.sources.find(function (findSource) {  return findSource.name == "RatingSync"; });
    const uniqueName = rsSource.uniqueName;

    renderOneRatingForEdit(editEl, film, uniqueName, -1)

    let ratingIndex = 1;
    for (let i=0; i < rsSource.archiveCount; i++) {
        renderOneRatingForEdit(editEl, film, uniqueName, ratingIndex)
        ratingIndex++;
    }
}

function renderOneRatingForEdit(editEl, film, uniqueName, ratingIndex) {
    const rowEl = document.createElement("div");
    const starsEl = document.createElement("ratingStars");

    rowEl.setAttribute("class", "mt-n2 pt-2");
    rowEl.setAttribute("style", "line-height: 1");
    rowEl.setAttribute("data-index", ratingIndex);
    starsEl.setAttribute("id", `rating-stars-${uniqueName}-${ratingIndex}`);
    starsEl.setAttribute("class", "rating-stars");

    editEl.appendChild(rowEl);
    rowEl.appendChild(starsEl);

    renderStarsForOneRating(film, ratingIndex);
}