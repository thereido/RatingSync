
function getFilterParams() {
    var paramArr = getFilterParamArray();

    // Content Filter
    var params = "";
    if (paramArr["feature"] == 0) { params = params + "&feature=0"; }
    if (paramArr["tvseries"] == 0) { params = params + "&tvseries=0"; }
    if (paramArr["tvepisodes"] == 0) { params = params + "&tvepisodes=0"; }
    if (paramArr["shorts"] == 0) { params = params + "&shorts=0"; }

    // Filmlist Filter
    if (paramArr["filterlists"] != "") { params = params + "&filterlists=" + paramArr["filterlists"]; }
    
    return params;
}

function getFilterParamArray() {
    // Content Filter
    var movies = 1;
    var series = 1;
    var episodes = 1;
    var shorts = 1;

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

    var params = [];
    params["feature"] = movies;
    params["tvseries"] = series;
    params["tvepisodes"] = episodes;
    params["shorts"] = shorts;

    // Filmlist Filter
    params["filterlists"] = getListFilterParam();
    
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

function getListFilterParam() {
    var listFilterParams = "";
    var listFilterDelimiter = "";
    var checkboxes = [];
    var listFilterEl = document.getElementById("filmlist-filter");
    if (listFilterEl) {
        checkboxes = listFilterEl.getElementsByTagName("input");
    }

    var i;
    for (i=0; i < checkboxes.length; i++) {
        if (checkboxes[i].type == "checkbox" && checkboxes[i].checked) {
            var listname = checkboxes[i].getAttribute("data-listname");
            listFilterParams = listFilterParams + listFilterDelimiter + listname;
            listFilterDelimiter = "%l";
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

function renderPagination() {
    var pageNum = contextData.beginPage;
    var pageSize = contextData.pageSize;
    var totalCount = contextData.totalCount;
    var previousPageNum = 0;
    var nextPageNum = 0;
    if (!pageNum || pageNum == "") {
        pageNum = 1;
    }
    pageNum = pageNum * 1;

    // Previous button
    var previousEl = document.getElementById("previous");
    if (pageNum > 1) {
        previousPageNum = pageNum - 1;
        previousEl.removeAttribute("class");
        previousEl.setAttribute("onclick", "submitPageForm(" + previousPageNum + ");");
    } else {
        previousEl.setAttribute("class", "disabled");
        previousEl.removeAttribute("onclick");
    }

    // Next button
    var nextEl = document.getElementById("next");
    if (totalCount > pageNum * pageSize) {
        nextPageNum = pageNum + 1;
        nextEl.removeAttribute("class");
        nextEl.setAttribute("onclick", "submitPageForm(" + nextPageNum + ");");
    } else {
        nextEl.setAttribute("class", "disabled");
        nextEl.removeAttribute("onclick");
    }

    // Page select
    var pageSelectEl = document.getElementById("page-select");
    pageSelectEl.innerHTML = "";
    for (var pageOption = 0; totalCount > pageOption * pageSize; pageOption++) {
        var optionEl = document.createElement("option");
        optionEl.value = pageOption + 1;
        optionEl.innerHTML = optionEl.value;
        if (pageOption+1 == pageNum) {
            optionEl.selected = true;
        }
        pageSelectEl.appendChild(optionEl);
    }
    
    var paginationEl = document.getElementById("pagination");
    if (previousPageNum != 0 || nextPageNum != 0) {
        paginationEl.hidden = false;
    } else {
        paginationEl.hidden = true;
    }
}   

function changePageNum() {
    var pageSelectEl = document.getElementById("page-select");
    submitPageForm(pageSelectEl.value);
}

function submitPageForm(pageNum) {
    var formEl = document.forms["pageForm"];
    var filterParamsArr = getFilterParamArray();

    formEl["param-p"].value = pageNum;
    formEl["param-feature"].value = filterParamsArr["feature"];
    formEl["param-tvseries"].value = filterParamsArr["tvseries"];
    formEl["param-tvepisodes"].value = filterParamsArr["tvepisodes"];
    formEl["param-shorts"].value = filterParamsArr["shorts"];
    formEl["param-filterlists"].value = filterParamsArr["filterlists"];

    document.forms["pageForm"].submit();
}