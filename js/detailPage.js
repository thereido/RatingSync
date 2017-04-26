
function getFilmForDetailPage(imdbUniqueName) {
    var params = "?action=getFilm";
    params = params + "&imdb=" + imdbUniqueName;
    params = params + "&rsonly=0";
	var xmlhttp = new XMLHttpRequest();
    var callbackHandler = function () { detailPageCallback(xmlhttp); };
    xmlhttp.onreadystatechange = callbackHandler;
	xmlhttp.open("GET", RS_URL_API + params, true);
	xmlhttp.send();
}

function detailPageCallback(xmlhttp) {
	if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
	    var result = JSON.parse(xmlhttp.responseText);
        if (result.Success != "false" && result.filmId != "undefined") {
            var film = result;
            var filmEl = document.getElementById("detail-film");
            renderRsFilmDetails(film, filmEl);
        }
	}
}