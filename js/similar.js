
function buildSimilarElement(similarFilm) {
    let title = getFilmTitle(similarFilm);
    let year = getFilmYear(similarFilm);
    let uniqueName = getUniqueName(similarFilm);

    let imdbRatingHtml = "";
    let tmdbRatingHtml = "";
    if (similarFilm.sourceName === "TMDb") {
        let tmdbUrl = TMDB_FILM_BASEURL;
        let tmdbUniqueName = uniqueName;
        let tmdbScore = getScore(similarFilm);
        if (similarFilm.contentType && similarFilm.contentType == CONTENT_TV_SERIES) {
            tmdbUrl = tmdbUrl + "tv/" + tmdbUniqueName;
        } else {
            tmdbUrl = tmdbUrl + "movie/" + tmdbUniqueName;
        }

        let el = '';
        el = el + '        <a href="'+tmdbUrl+'" target="_blank">\n';
        el = el + '          <img src="'+RS_URL_BASE + "/image/logo-rating-tmdb.png"+'" alt="TMDb Rating" height="20px"/>\n';
        el = el + '          <tmdbScore id="tmdb-score-'+tmdbUniqueName+'">'+tmdbScore+'</tmdbScore>\n'
        el = el + '        </a>\n'

        tmdbRatingHtml = el;
    }

    let thirdPartyBar = "";
    let sourceLogoClass = "source-logo-1";
    if (imdbRatingHtml != "") {
        thirdPartyBar = thirdPartyBar + '<div class="' + sourceLogoClass + '">\n';
        thirdPartyBar = thirdPartyBar + imdbRatingHtml + "\n";
        thirdPartyBar = thirdPartyBar + '</div>\n';

        sourceLogoClass = "source-logo-2";
    }
    if (tmdbRatingHtml != "") {
        thirdPartyBar = thirdPartyBar + '<div class="' + sourceLogoClass + '">\n';
        thirdPartyBar = thirdPartyBar + tmdbRatingHtml + "\n";
        thirdPartyBar = thirdPartyBar + '</div>\n';
    }

    let justWatchUrl = "https://www.justwatch.com/us/search?release_year_from="+year+"&release_year_until="+year+"&q=" + encodeURIComponent(title);
    let justWatchImage = RS_URL_BASE + "/image/logo-justwatch.png";

    let contentTypeText = "";
    if (similarFilm.contentType && similarFilm.contentType == CONTENT_TV_SERIES) {
        contentTypeText = " TV";
    }

    let html = '\n';
    html = html + '  <div class="film-line"><span class="film-title">'+title+'</span> ('+year+')'+contentTypeText+'</div>\n';
    html = html + '  <div id="action-area-'+uniqueName+'">\n';
    html = html + '    <div class="thirdparty-bar pb-1 mt-2">\n';
    html = html +        thirdPartyBar + '\n';
    html = html + '      <a href="'+justWatchUrl+'" target="_blank"><img src="'+justWatchImage+'" alt="JustWatch" height="20px"/></a>'
    html = html + '    </div>\n';
    html = html + '    <status></status>\n';
    html = html + '  </div>\n';
    html = html + '  </div>\n';

    let similarFilmEl = document.createElement("detail");
    similarFilmEl.innerHTML = html;

    return similarFilmEl;
}

function getUniqueName(similarFilm) {
    let uniqueName = "";
    if (similarFilm.uniqueName) {
        uniqueName = similarFilm.uniqueName;
    }

    return uniqueName;
}

function getScore(similarFilm) {
    let score = "";
    if (similarFilm.score) {
        score = similarFilm.score;
    }

    return score;
}