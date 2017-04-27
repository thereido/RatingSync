
function checkFilterFromUrl() {
    var url = window.location.href;
    if (-1 < url.indexOf("feature=0")) {
        document.getElementById("featurefilms").removeAttribute("checked");
    } else if (-1 < url.indexOf("tvseries=0")) {
        document.getElementById("tvseries").removeAttribute("checked");
    } else if (-1 < url.indexOf("tvepisodes=0")) {
        document.getElementById("tvepisodes").removeAttribute("checked");
    } else if (-1 < url.indexOf("shorts=0")) {
        document.getElementById("shortfilms").removeAttribute("checked");
    }
}

function getFilterParams() {
    // Content Filter
    var movies = true;
    var series = true;
    var episodes = true;
    var shorts = true;

    var featureCheckbox = document.getElementById("featurefilms");
    if (featureCheckbox && !featureCheckbox.checked) {
        movies = 0;
    }
    var seriesCheckbox = document.getElementById("tvseries");
    if (seriesCheckbox && !seriesCheckbox.checked) {
        series = 0;
    }
    var episodesCheckbox = document.getElementById("tvepisodes");
    if (episodesCheckbox && !episodesCheckbox.checked) {
        episodes = 0;
    }
    var shortCheckbox = document.getElementById("shortfilms");
    if (shortCheckbox && !shortCheckbox.checked) {
        shorts = 0;
    }

    var params = "";
    if (!movies) { params = params + "&feature=" + movies; }
    if (!series) { params = params + "&tvseries=" + series; }
    if (!episodes) { params = params + "&tvepisodes=" + episodes; }
    if (!shorts) { params = params + "&shorts=" + shorts; }

    // Filmlist Filter
    params = params + getFilmlistFilterParams();
    
    return params;
}

function getFilmlistFilterParams() {
    var listFilterParams = "";
    var checkboxes = [];
    var listFilterEl = document.getElementById("filmlist-filter");
    if (listFilterEl) {
        checkboxes = listFilterEl.getElementsByTagName("input");
    }

    var i;
    for (i=0; i < checkboxes.length; i++) {
        if (checkboxes[i].type == "checkbox" && checkboxes[i].checked) {
            if (listFilterParams == "") {
                listFilterParams = "&filterlists=";
            } else {
                listFilterParams = listFilterParams + "%l";
            }
            var listname = checkboxes[i].getAttribute("data-listname");
            listFilterParams = listFilterParams + listname;
        }
    }

    return listFilterParams;
}

function clearFilmlistFilter() {
    var filterEl = document.getElementById("filmlist-filter");
    var checkboxes = filterEl.getElementsByTagName("input");
    var checkmarks = filterEl.getElementsByClassName("glyphicon-check");

    var i;
    for (i=0; i < checkboxes.length; i++) {
        if (checkboxes[i].type == "checkbox") {
            checkboxes[i].checked = false;
        }
    }
    for (i=0; i < checkmarks.length; i++) {
        checkmarks[i].className = "glyphicon glyphicon-check checkmark-off";
    }
}

function toggleFilmlistFilter(btnId, checkboxId) {
    var button = document.getElementById(btnId);
    var checkmark = button.getElementsByTagName("span")[0];
    var checkbox = document.getElementById(checkboxId);

    // Toggle the hidden checkbox
    checkbox.checked = !checkbox.checked;

    // Update the class for the checkmark
    if (checkbox.checked) {
        checkmark.className = "glyphicon glyphicon-check checkmark-on";
    } else {
        checkmark.className = "glyphicon glyphicon-check checkmark-off";
    }
}

function setFilmlistFilter() {
    var newFilmlistFilterParams = getFilmlistFilterParams();
    if (prevFilmlistFilterParams != newFilmlistFilterParams) {
        prevFilmlistFilterParams = newFilmlistFilterParams;

        var url = window.location.href;
        if (-1 < url.indexOf("ratings.php")) {
            getRsRatings(defaultPageSize, 1);
        } else {
            getFilmsForFilmlist(defaultPageSize, 1);
        }
    }
}