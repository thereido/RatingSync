
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
    var movies = true;
    var series = true;
    var episodes = true;
    var shorts = true;
    if (!document.getElementById("featurefilms").checked) {
        movies = 0;
    }
    if (!document.getElementById("tvseries").checked) {
        series = 0;
    }
    if (!document.getElementById("tvepisodes").checked) {
        episodes = 0;
    }
    if (!document.getElementById("shortfilms").checked) {
        shorts = 0;
    }

    var params = "";
    if (!movies) { params = params + "&feature=" + movies; }
    if (!series) { params = params + "&tvseries=" + series; }
    if (!episodes) { params = params + "&tvepisodes=" + episodes; }
    if (!shorts) { params = params + "&shorts=" + shorts; }

    return params;
}