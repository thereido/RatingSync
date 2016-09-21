
document.addEventListener('DOMContentLoaded', function () {
    chrome.tabs.executeScript(null, {file: "getSearchTerms.js"}, function() { });
});

chrome.runtime.onMessage.addListener(function (request, sender) {
    if (request.action == "setSearchTerms") {
        searchFilm(request.search);
    } else if (request.action == "unsupportedUrl") {
        notSupported(null);
    }
});

function notFound(source)
{
    var msg = "<div align='center'>Unable to figure out which title you want to search</div>";
    document.getElementById("searchResult").innerHTML = msg;
}

function notSupported(source)
{
    var msg = "<div>Here are the sites currently supported<ul><li>IMDb</li><li>Netflix</li><li>xfinity</li><li>Rotten Tomatoes</li></ul></div>";
    document.getElementById("searchResult").innerHTML = msg;
}

function searchFilm(searchTerms)
{
    if (searchTerms.uniqueName == "undefined" && (searchTerms.title == "undefined" || !searchTerms.year == "undefined")) {
        notFound(searchTerms.source);
        return;
    }

    renderStatus('Searching...');
    var msg = "<div align='center'>";
    if (searchTerms.title != "undefined") {
        msg = msg + searchTerms.title + " (" + searchTerms.year + ")";
    } else {
        msg = msg + "IMDb id: " + searchTerms.uniqueName;
    }
    var msg = msg + "</div>";
    var searchResultElement = document.getElementById("searchResult");
    searchResultElement.innerHTML = msg;

	var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange = function () {
/*RT*/renderStatus('State change');
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
/*RT*/renderStatus('Status 200');
            var resultFilms = JSON.parse(xmlhttp.responseText);
/*RT*/renderStatus('JSON parsed');
            var film = resultFilms.match;
/*RT*/renderStatus('film match');
            contextData = JSON.parse('{"films":[' + xmlhttp.responseText + ']}');
            renderFilm(film, searchResultElement);
            renderStatus('');
            showStreams();
        } else if (xmlhttp.readyState == 4) {
            renderStatus('Not found');
        }
	}

    var params = "&json=1";
    if (searchTerms.uniqueName != "undefined") { params = params + "&q=" + searchTerms.uniqueName; }
    if (searchTerms.uniqueEpisode != "undefined") { params = params + "&ue=" + searchTerms.uniqueEpisode; }
    if (searchTerms.uniqueAlt != "undefined") { params = params + "&ua=" + searchTerms.uniqueAlt; }
    if (searchTerms.source != "undefined") { params = params + "&source=" + searchTerms.source; }
    if (searchTerms.title != "undefined") { params = params + "&t=" + encodeURIComponent(searchTerms.title); }
    if (searchTerms.year != "undefined") { params = params + "&y=" + searchTerms.year; }
    if (searchTerms.season != "undefined") { params = params + "&s=" + searchTerms.season; }
    if (searchTerms.episodeNumber != "undefined") { params = params + "&en=" + searchTerms.episodeNumber; }
    if (searchTerms.episodeTitle != "undefined") { params = params + "&et=" + searchTerms.episodeTitle; }
    if (searchTerms.contentType != "undefined") { params = params + "&ct=" + searchTerms.contentType; }
	xmlhttp.open("GET", RS_URL_API + "?action=getSearchFilm" + params, true);
	xmlhttp.send();
}

function renderFilm(film, element) {
    var image = RS_URL_BASE + film.image;
    
    var rsSource = film.sources.find( function (findSource) { return findSource.name == "RatingSync"; } );
    var uniqueName = rsSource.uniqueName;

    var imdb = film.sources.find( function (findSource) { return findSource.name == "IMDb"; } );
    var imdbLink = "";
    if (imdb && imdb.uniqueName) {
        var imdbLabel = "IMDb";
        var imdbFilmUrl = IMDB_FILM_BASEURL + imdb.uniqueName;
        var imdbScore = imdb.userScore;
        imdbLink = "<a href='" + imdbFilmUrl + "' target='_blank'>" + imdbLabel + ":</a> " + imdbScore;
    }

    
    var r = "";
    r = r + "<div id='" + uniqueName + "' align='center'>\n";
    r = r + "  <div class='film-line'><span class='film-title'>" + film.title + "</span> (" + film.year + ")</div>\n";
    r = r + "  <div class='rating-stars' id='rating-stars-"+uniqueName+"'></div>\n";
    r = r + "  <poster><img src='" + image + "' width='150px'/></poster>\n";
    r = r + "  <detail>\n";
    r = r + "    <div align='left'>" + imdbLink + "</div>\n";
    r = r + "    <div id='streams-"+film.filmId+"' class='streams'></div>\n";
    r = r + "    <div id='filmlist-container' align='left'></div>\n";
    r = r + "  </detail>\n";
    r = r + "</div>\n";

    element.innerHTML = r;
    renderStars(film);
    renderStreams(film);
    renderFilmlists(film.filmlists, film.filmId);

    return r;
}

// userlist (JSON) - all of the user's filmlists
// listnames - lists this film belongs in
function renderFilmlists(includedListnames, filmId) {
    if (!userlistsJson) {
        renderFilmlistsHandler = function () { renderFilmlists(includedListnames, filmId); };
        getFilmlists(renderFilmlistsHandler);
        return;
    }
    
    var defaultList = "Watchlist";
    var defaultListClass = "glyphicon glyphicon-check checkmark-off";
    var userlists = JSON.parse(userlistsJson);
    if (includedListnames === undefined) {
        includedListnames = [];
    }
    
    var listItemsHtml = "";
    for (var x = 0; x < userlists.length; x++) {
        var viewListsUrl = RS_URL_BASE + "/php/userlist.php?l=" + defaultList;
        var viewNewListUrl = RS_URL_BASE + "/php/userlist.php?nl=0";
        var currentUserlist = userlists[x].listname;
        if (currentUserlist == defaultList) {
            if (-1 != includedListnames.indexOf(currentUserlist)) {
                defaultListClass = "glyphicon glyphicon-check checkmark-on";
            }
        } else {
            var checkmarkClass = "glyphicon glyphicon-check checkmark-off";
            if (-1 != includedListnames.indexOf(currentUserlist)) {
                checkmarkClass = "glyphicon glyphicon-check checkmark-on";
            }
            
            listItemsHtml = listItemsHtml + "  <li class='btn-filmlist' id='filmlist-btn-"+currentUserlist+"-"+filmId+"' data-listname='"+currentUserlist+"' data-filmId='"+filmId+"'>";
            listItemsHtml = listItemsHtml + "      <span class='"+checkmarkClass+"' id='filmlist-checkmark-"+filmId+"'>&#10003;</span> "+currentUserlist;
            listItemsHtml = listItemsHtml + "  </li>";
        }
    }
    
    var html = "";
    html = html + "<div class='btn-group-vertical'>";
    html = html + "  <button class='btn btn-sm btn-primary' width='100%' id='filmlist-btn-default-"+filmId+"' data-listname='"+defaultList+"' data-filmId='"+filmId+"' type='button'><span class='"+defaultListClass+"' id='filmlist-checkmark-"+filmId+"'>&#10003;</span> "+defaultList+"</button>";
    html = html + "  <button class='btn btn-sm btn-primary' width='100%' id='filmlist-btn-others-"+filmId+"' type='button'>More lists \u25BC</button>";
    html = html + "</div>";
    html = html + "<div>";
    html = html + "  <ul class='film-filmlists rs-dropdown-menu' id='filmlists-"+filmId+"' hidden >";
    html = html +      listItemsHtml;
    html = html + "    <li class='divider'></li>";
    html = html + "    <li>";
    html = html + "      <a href='"+viewNewListUrl+"' target='_blank'>New List</a>";
    html = html + "    </li>";
    html = html + "    <li>";
    html = html + "      <a href='"+viewListsUrl+"' target='_blank'>View Lists</a>";
    html = html + "    </li>";
    html = html + "  </ul>";
    html = html + "</div>";

    var container = document.getElementById("filmlist-container");
    container.innerHTML = html;
    addFilmlistListeners(container, filmId);
}