

document.addEventListener('DOMContentLoaded', function () {
    renderUserlistFilms();
});

function renderUserlistFilms() {
    for (var userlist_film_index = 0; userlist_film_index < contextData.films.length; userlist_film_index++) {
        var film = contextData.films[userlist_film_index];
        var posterExtEl = document.getElementById("poster-extension-" + film.filmId);

        renderPosterExtension(film, posterExtEl);
    }
}

function renderPosterExtension(film, posterExtEl) {
    var html = '';
    html = html + '<div id="streams-'+film.filmId+'" class="streams"></div>\n';

    posterExtEl.innerHTML = html;
    renderStreams(film, false);
}

function showFilmDropdownForUserlist(filmId) {
    var dropdownEl = document.getElementById("film-dropdown-" + filmId);
    var film = contextData.films.find( function (findFilm) { return findFilm.filmId == filmId; } );
    renderFilmDropdownForUserlist(film, dropdownEl);
}

function renderFilmDropdownForUserlist(film, dropdownEl) {
    dropdownEl.innerHTML = "";
    dropdownEl.appendChild(buildFilmDetailElement(film));
    dropdownEl.style.display = "block";

    renderStars(film);
    renderStreams(film, true);
    renderFilmlists(film.filmlists, film.filmId);
}

function hideFilmDropdownForUserlist(filmId, detailTimer) {
    el = document.getElementById("film-dropdown-" + filmId);
    el.style.display = "none";
    clearTimeout(detailTimer);
}