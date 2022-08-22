
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