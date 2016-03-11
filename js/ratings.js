
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
		star.addEventListener('click', function(){rateFilm(uniqueName, score, titleNum, withImage);});
	}
}

function rateFilm(uniqueName, score, titleNum, withImage) {
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            document.getElementById(uniqueName).innerHTML = xmlhttp.responseText;
            addStarListeners(document.getElementById(uniqueName));
        } else {
            /*RT*/// document.getElementById(uniqueName).innerHTML = xmlhttp.responseText;
        }
    }
    xmlhttp.open("GET", "/php/src/ajax/setRating.php?un=" + uniqueName + "&s=" + score + "&tn=" + titleNum + "&i=" + withImage, true);
    xmlhttp.send();
}

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
        xmlhttp.open("GET", "/php/src/ajax/getSearchFilm.php?q=" + document.getElementById("searchQuery").value, true);
        xmlhttp.send();
    }

    return false;
}