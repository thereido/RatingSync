
var RS_BASEURL = "http://192.168.1.105:55887";
var IMDB_FILM_BASEURL = "http://www.imdb.com/title/";

document.addEventListener('DOMContentLoaded', function () {
    chrome.tabs.executeScript(null, {file: "getSearchTerms.js"}, function() { });
});

function renderStatus(statusText) {
  document.getElementById('status').textContent = statusText;
}

function notFound(source)
{
    var msg = "<div>Unable to find title on this " + source + " page</div>";
    document.getElementById("searchResult").innerHTML = msg;
}

function notSupported(source)
{
    var msg = "<div>Here are the sites currently supported<ul><li>IMDb</li><li>Rotten Tomatoes</li><li>Netflix</li></ul></div>";
    document.getElementById("searchResult").innerHTML = msg;
}

function searchFilm(searchTerms)
{
    renderStatus('Searching...');
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange = function () {
	    if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            var searchResultElement = document.getElementById("searchResult");
	        searchResultElement.innerHTML = renderFilm(xmlhttp.responseText);
            addStarListeners(searchResultElement);
    		renderStatus('');
	    } else if (xmlhttp.readyState == 4) {
	        renderStatus('Not found ' + searchTerm + ' at ' + source);
	    }
	}

    var params = "&json=1";
    if (searchTerms.uniqueName != "undefined") { params = params + "&q=" + searchTerms.uniqueName; }
    if (searchTerms.source != "undefined") { params = params + "&source=" + searchTerms.source; }
    if (searchTerms.title != "undefined") { params = params + "&t=" + searchTerms.title; }
    if (searchTerms.year != "undefined") { params = params + "&y=" + searchTerms.year; }
    if (searchTerms.contentType != "undefined") { params = params + "&ct=" + searchTerms.contentType; }
	xmlhttp.open("GET", RS_BASEURL + "/php/src/ajax/api.php?action=getSearchFilm" + params, true);
	xmlhttp.send();
}

function addStarListeners(el) {
    var stars = el.getElementsByClassName("rating-star");
    for (i = 0; i < stars.length; i++) {
        addStarListener(stars[i].getAttribute("id"));
    }
}

function addStarListener(elementId) {    
	var star = document.getElementById(elementId);
	if (star != null) {
		var uniqueName = star.getAttribute('data-uniquename');
		var score = star.getAttribute('data-score');
		var titleNum = star.getAttribute('data-title-num');

		var mouseoverHandler = function () { renderYourScore(uniqueName, score, 'new'); };
		var mouseoutHandler = function () { renderYourScore(uniqueName, score, 'original'); };
		var clickHandler = function () { rateFilm(uniqueName, score, titleNum); };

        star.addEventListener("mouseover", mouseoverHandler);
        star.addEventListener("mouseout", mouseoutHandler);
        star.addEventListener("click", clickHandler);
	}
}

function rateFilm(uniqueName, score, titleNum) {
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            var searchResultElement = document.getElementById("searchResult");
	        searchResultElement.innerHTML = renderFilm(xmlhttp.responseText);
            addStarListeners(searchResultElement);
    		renderStatus('Rating Saved');
        }
    }
    xmlhttp.open("GET", RS_BASEURL + "/php/src/ajax/api.php?action=setRating&json=1&un=" + uniqueName + "&s=" + score, true);
    xmlhttp.send();
    renderStatus('Saving...');
}

function renderYourScore(uniqueName, hoverScore, mousemove) {
    var score = hoverScore;
    if (mousemove == "original") {
        score = document.getElementById("original-score-" + uniqueName).getAttribute("data-score");
    }

    if (score == "10") {
        score = "01";
    }
    document.getElementById("your-score-" + uniqueName).innerHTML = score;
}

function renderFilm(json) {
    var r = "";
    var film = JSON.parse(json);
    var title = film.title;
    var year = film.year;
    var image = RS_BASEURL + film.image;

    var rs = film.sources.RatingSync;
    var uniqueName = rs.uniqueName;
    var yourScore = rs.rating.yourScore;
    var showYourScore = yourScore;
    if (showYourScore == "10") {
        showYourScore = "01";
    } else if (showYourScore == null || showYourScore == "") {
        showYourScore = "-";
    }

    var imdb = film.sources.IMDb;
    var imdbFilmUrl = IMDB_FILM_BASEURL + imdb.uniqueName;
    var imdbLabel = "IMDb";
    var imdbScore = imdb.userScore;

    var starsHtml = renderStars(rs);
    var filmlistsHtml = renderFilmlists(film.filmlists);
    r = r + "<div id='" + uniqueName + "' align='center'>";
    r = r + "  <div class='film-line'><span class='film-title'>" + title + "</span> (" + year + ")</div>";
    r = r + "  <div class='rating-stars'>";
    r = r + "    <score>";
    r = r + "      <of-possible>01/</of-possible><your-score id='your-score-" + uniqueName + "'>" + showYourScore + "</your-score>";
    r = r + "    </score>";
    r = r + "    " + starsHtml;
    r = r + "    <div id='original-score-" + uniqueName + "' data-score='" + showYourScore + "' hidden ></div>";
    r = r + "  </div>";
    r = r + "  <poster><img src='" + image + "' width='150px'/></poster>";
    r = r + "  <detail>";
    r = r + "    <div><a href='" + imdbFilmUrl + "' target='_blank'>" + imdbLabel + ":</a> " + imdbScore + "</div>";
    r = r + "    " + filmlistsHtml;
    r = r + "  </detail>";
    r = r + "</div>";

    return r;
}

function renderStars(rs) {
    var uniqueName = rs.uniqueName;
    var yourScore = rs.rating.yourScore;
    var fullStars = yourScore;
    var emptyStars = 10 - yourScore;
    var starScore = 10;
    
    var starsHtml = "";
    while (emptyStars > 0) {
        starsHtml = starsHtml + "<span class='rating-star' id='rate-" + uniqueName + "-" + starScore + "' data-uniquename='" + uniqueName + "' data-score='" + starScore + "'>☆</span>";
        emptyStars = emptyStars - 1;
        starScore = starScore - 1;
    }
    while (fullStars > 0) {
        starsHtml = starsHtml + "<span class='rating-star' id='rate-" + uniqueName + "-" + starScore + "' data-uniquename='" + uniqueName + "' data-score='" + starScore + "'>★</span>";
        fullStars = fullStars - 1;
        starScore = starScore - 1;
    }

    return starsHtml;
}

function renderFilmlists(listnames) {
    // Get user filmlists

    // Show them and onClick for add/remove

    // Show a button link to page for creating a new list

    return "<button type='button'>Wishlist</button>";
}

chrome.runtime.onMessage.addListener(function (request, sender) {
    if (request.action == "setSearchTerms") {
        searchFilm(request.search);
    }
});