
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
    if (checkmark.className == "checkmark-on") {
        filmIsInTheList = true;
        var addToList = 0; //no (remove)
    }

    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            filmIsInTheList = !filmIsInTheList;
            if (filmIsInTheList) {
                checkmark.className = "checkmark-on";
            } else {
                checkmark.className = "checkmark-off";
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
/*RT*
function filmlistCheckboxOnChange(elementId) {
	var checkbox = document.getElementById(elementId);
	var filmId = checkbox.getAttribute('data-filmid');
	var listname = checkbox.getAttribute('data-listname');
	var checked = checkbox.checked;
	var checkParam = 0;
    if (checked) {
        checkParam = 1;
    }

	checkbox.disabled = true;
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
	        checkbox.disabled = false;
        }
    }
    xmlhttp.open("GET", "/php/src/ajax/api.php?action=setFilmlist&l=" + listname + "&id=" + filmId + "&c=" + checkParam, true);
    xmlhttp.send();
}
*RT*/

function searchFilm() {
    if (document.getElementById("searchQuery").value == 0) {
        document.getElementById("searchResult").innerHTML = "";
    } else {
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function () {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                /*RT*/// document.getElementById("debug").innerHTML = "readyState=" + xmlhttp.readyState + " status=" + xmlhttp.status;
                document.getElementById("searchResult").innerHTML = xmlhttp.responseText;
                addStarListeners(document.getElementById("searchResult"));
            }
            else {
                /*RT*/// document.getElementById("debug").innerHTML = "readyState=" + xmlhttp.readyState + " status=" + xmlhttp.status;
                /*RT*/// document.getElementById("searchResult").innerHTML = xmlhttp.responseText;
            }
        }
        xmlhttp.open("GET", "/php/src/ajax/api.php?action=getSearchFilm&q=" + document.getElementById("searchQuery").value, true);
        xmlhttp.send();
    }

    return false;
}