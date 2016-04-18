
document.addEventListener('DOMContentLoaded', function () {
    addStarListeners(document);
});

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
		var withImage = star.getAttribute('data-image');

		var mouseoverHandler = function () { showYourScore(uniqueName, score, 'new'); };
		var mouseoutHandler = function () { showYourScore(uniqueName, score, 'original'); };
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
            document.getElementById(uniqueName).innerHTML = xmlhttp.responseText;
            addStarListeners(document.getElementById(uniqueName));
        }
    }
    xmlhttp.open("GET", "/php/src/ajax/api.php?action=setRating&un=" + uniqueName + "&s=" + score + "&tn=" + titleNum, true);
    xmlhttp.send();
}

function showYourScore(uniqueName, hoverScore, mousemove) {
    var score = hoverScore;
    if (mousemove == "original") {
        score = document.getElementById("original-score-" + uniqueName).getAttribute("data-score");
    }

    if (score == "10") {
        score = "01";
    }
    document.getElementById("your-score-" + uniqueName).innerHTML = score;
}

function toggleHideFilmlists(elementId) {
	var el = document.getElementById(elementId);
    el.hidden = !el.hidden;
}

function toggleFilmlist(listname, filmId, activeBtnId) {
    var defaultBtn = document.getElementById("filmlist-btn-default-" + filmId);
    var otherListsBtn = document.getElementById("filmlist-btn-others-" + filmId);
    var otherListsElement = document.getElementById("filmlists-" + filmId);
    defaultBtn.disabled = true;
    otherListsBtn.disabled = true;
    otherListsElement.disabled = true;
    
    var activeBtn = document.getElementById(activeBtnId);
    var checkmark = activeBtn.getElementsByTagName("span")[0];
    var filmIsInTheList = false;
    var addToList = 1; //yes
    if (checkmark.className == "glyphicon glyphicon-check checkmark-on") {
        filmIsInTheList = true;
        var addToList = 0; //no (remove)
    }

    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            filmIsInTheList = !filmIsInTheList;
            if (filmIsInTheList) {
                checkmark.className = "glyphicon glyphicon-check checkmark-on";
            } else {
                checkmark.className = "glyphicon glyphicon-check checkmark-off";
            }

            defaultBtn.disabled = false;
            otherListsBtn.disabled = false;
            otherListsElement.disabled = false;
            otherListsElement.hidden = true;
        }
    }
    xmlhttp.open("GET", "/php/src/ajax/api.php?action=setFilmlist&l=" + listname + "&id=" + filmId + "&c=" + addToList, true);
    xmlhttp.send();
}

function searchFilm() {
    if (document.getElementById("searchQuery").value == 0) {
        document.getElementById("searchResult").innerHTML = "";
    } else {
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function () {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                document.getElementById("searchResult").innerHTML = xmlhttp.responseText;
                addStarListeners(document.getElementById("searchResult"));
            }
        }
        xmlhttp.open("GET", "/php/src/ajax/api.php?action=getSearchFilm&q=" + document.getElementById("searchQuery").value, true);
        xmlhttp.send();
    }

    return false;
}

function searchFilms() {
    if (document.getElementById("searchQuery").value == 0) {
        document.getElementById("searchResult").innerHTML = "";
    } else {
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function () {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                document.getElementById("searchResult").innerHTML = xmlhttp.responseText;
                addStarListeners(document.getElementById("searchResult"));
            }
        }
        xmlhttp.open("GET", "/php/src/ajax/api.php?action=getSearchFilms&q=" + document.getElementById("searchQuery").value, true);
        xmlhttp.send();
    }

    return false;
}

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