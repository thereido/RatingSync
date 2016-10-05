

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
    renderStreams(film);
}

function showFilmDropdownForUserlist(filmId) {
    var dropdownEl = document.getElementById("film-dropdown-" + filmId);
    var film = contextData.films.find( function (findFilm) { return findFilm.filmId == filmId; } );
    renderFilmDropdownForUserlist(film, dropdownEl);
}

function renderFilmDropdownForUserlist(film, dropdownEl) {
    var imdb = film.sources.find( function (findSource) { return findSource.name == "IMDb"; } );
    var imdbFilmUrl = IMDB_FILM_BASEURL + imdb.uniqueName;
    var imdbLabel = "IMDb";
    var imdbScore = imdb.userScore;
    var rsSource = film.sources.find( function (findSource) { return findSource.name == "RatingSync"; } );
    var yourRatingDate = rsSource.rating.yourRatingDate;
    var dateStr = "";
    if (yourRatingDate && yourRatingDate != "undefined") {
        var reDate = new RegExp("([0-9]+)-([0-9]+)-([0-9]+)");
        var year = reDate.exec(yourRatingDate)[1];
        var month = reDate.exec(yourRatingDate)[2];
        var day = reDate.exec(yourRatingDate)[3];
        dateStr = "You rated this " + month + "/" + day + "/" + year;
    }
    var titleNumStr = "";
    var titleNum = dropdownEl.getAttribute("data-titleNum");
    if (titleNum && titleNum != "undefined") {
        titleNumStr = titleNum + ". ";
    }

    var season = "";
    if (film.season) {
        season = "Season " + film.season;
    }
    var episodeNumber = "";
    if (film.episodeNumber) {
        episodeNumber = " - Episode " + film.episodeNumber;
    }

    var html = '';
    html = html + '<detail>\n';
    html = html + '  <div class="film-line">'+titleNumStr+'<span class="film-title">'+film.title+'</span> ('+film.year+')</div>\n';
    html = html + "  <div class='tv-episode-title'>" + film.episodeTitle + "</div>\n";
    html = html + "  <div><span class='tv-season'>" + season + "</span><span class='tv-episodenum'>" + episodeNumber + "</span></div>\n";
    html = html + '  <div align="left">\n';
    html = html + '    <div class="rating-stars" id="rating-stars-'+rsSource.uniqueName+'"></div>\n';
    html = html + '  </div>\n';
    html = html + '  <div class="rating-date">'+dateStr+'</div>\n';
    html = html + '  <div><a href="'+imdbFilmUrl+'" target="_blank">'+imdbLabel+':</a> '+imdbScore+'</div>\n';
    html = html + '  <div id="filmlist-container-'+film.filmId+'" align="left"></div>\n';
    html = html + '</detail>\n';

    dropdownEl.innerHTML = html;
    dropdownEl.style.display = "block";
    renderStars(film);
    renderFilmlists(film.filmlists, film.filmId);
}

function hideFilmDropdownForUserlist(filmId, detailTimer) {
    el = document.getElementById("film-dropdown-" + filmId);
    el.style.display = "none";
    clearTimeout(detailTimer);
}