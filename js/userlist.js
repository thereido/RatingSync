

document.addEventListener('DOMContentLoaded', function () {
    renderUserlistFilms();
});

function renderUserlistFilms() {
    for (var userlist_film_index = 0; userlist_film_index < contextData.films.length; userlist_film_index++) {
        var film = contextData.films[userlist_film_index];
        var rsSource = film.sources.find( function (findSource) { return findSource.name == "RatingSync"; } );
        var uniqueName = rsSource.uniqueName;
        var filmEl = document.getElementById(uniqueName);

        renderFilmDetail(film, filmEl);
    }
}