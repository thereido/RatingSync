
var hideable = true;

document.addEventListener('DOMContentLoaded', function () {
});

function createFilmlist() {
    var listname = document.getElementById("filmlist-listname").value;
    if (listname == 0) {
        document.getElementById("filmlist-create-result").innerHTML = "";
        return;
    }
    
    var params = "&l="+listname;
    var filmIdEl = document.getElementById("filmlist-filmid");
    if (filmIdEl != null) {
        params = params + "&id=" + filmIdEl.value;
        var addThisEl = document.getElementById("filmlist-add-this");
        if (addThisEl != null && addThisEl.value == "0") {
            params = params + "&a=0";
        } else {
            params = params + "&a=1";
        }
    }

    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            window.location = "/php/userlist.php?l="+listname;
        }
    }
    xmlhttp.open("GET", "/php/src/ajax/api.php?action=createFilmlist"+params, true);
    xmlhttp.send();

    return false;
}

// Needs "contextData" JSON in the page
function showFilmDetail(filmId) {
    var filmEl = document.getElementById("rating-detail");
    var film = contextData.films.find( function (findFilm) { return findFilm.filmId == filmId; } );
    renderFilmDetail(film, filmEl);
}

function renderFilmDetail(film, filmEl) {
    var image = RS_URL_BASE + film.image;
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
    var titleNum = filmEl.getAttribute("data-titleNum");
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
    html = html + '<poster><img src="'+image+'" width="150px"/></poster>\n';
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
    html = html + '  <div id="streams-'+film.filmId+'" class="streams"></div>\n';
    html = html + '</detail>\n';

    filmEl.innerHTML = html;
    renderStars(film);
    renderStreams(film);
    renderFilmlists(film.filmlists, film.filmId);
}

function hideFilmDetail() {
    if (hideable) {
        el = document.getElementById("rating-detail");
        el.innerHTML = "";
    }
}