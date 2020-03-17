
function getFilterParams() {
    var params = "";
    var paramArr = getFilterParamArray();

    // Sort
    if (paramArr["sort"] != "") { params = params + "&sort=" + paramArr["sort"]; }
    if (paramArr["direction"] != "") { params = params + "&direction=" + paramArr["direction"]; }

    // Filmlist Filter
    if (paramArr["filterlists"] != "") { params = params + "&filterlists=" + paramArr["filterlists"]; }

    // Genre Filter
    if (paramArr["filtergenreany"] != "") { params = params + "&filtergenreany=" + paramArr["filtergenreany"]; }
    if (paramArr["filtergenres"] != "") { params = params + "&filtergenres=" + paramArr["filtergenres"]; }

    // Content Type Filter
    if (paramArr["filtercontenttypes"] != "") { params = params + "&filtercontenttypes=" + paramArr["filtercontenttypes"]; }
    
    return params;
}

function getFilterParamArray() {
    var params = [];

    // Sort
    var sort = "";
    var direction = "";

    var sortSelect = document.getElementById("sort");
    if (sortSelect) {
        sort = sortSelect.value;
    }
    var directionInput = document.getElementById("direction");
    if (directionInput) {
        direction = directionInput.value;
    }

    params["sort"] = sort;
    params["direction"] = direction;

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

    params["feature"] = movies;
    params["tvseries"] = series;
    params["tvepisodes"] = episodes;
    params["shorts"] = shorts;

    // Filmlist Filter
    params["filterlists"] = getListFilterParam();

    // Genre Filter
    params["filtergenreany"] = getGenreFilterMatchAnyParam();
    params["filtergenres"] = getGenreFilterParam();

    // Content Type Filter
    params["filtercontenttypes"] = getContentTypeFilterParam();
    
    return params;
}

function getFilmlistFilterParams() {
    var params = "";

    var listFilterParam = getListFilterParam();
    if (listFilterParam != "") {
        params = params + "&filterlists=" + listFilterParam;
    }

    var genreFilterParam = getGenreFilterParam();
    if (genreFilterParam != "") {
        params = params + "&filtergenres=" + genreFilterParam;
    }

    params = params + "&filtergenreany=" + getGenreFilterMatchAnyParam();

    var contentTypeFilterParam = getContentTypeFilterParam();
    if (contentTypeFilterParam != "") {
        params = params + "&filtercontenttypes=" + contentTypeFilterParam;
    }

    var sortParam = getSortParam();
    if (sortParam != "") {
        params = params + "&sort=" + sortParam;
    }

    var directionParam = getDirectionParam();
    if (directionParam != "") {
        params = params + "&direction=" + directionParam;
    }

    return params;
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

function getGenreFilterParam() {
    var genreFilterParams = "";
    var genreFilterDelimiter = "";
    var checkboxes = [];
    var listFilterEl = document.getElementById("genre-filter");
    if (listFilterEl) {
        checkboxes = listFilterEl.getElementsByTagName("input");
    }

    for (var i=0; i < checkboxes.length; i++) {
        if (checkboxes[i].type == "checkbox" && checkboxes[i].checked) {
            var genre = checkboxes[i].getAttribute("data-genre");
            genreFilterParams = genreFilterParams + genreFilterDelimiter + genre;
            genreFilterDelimiter = "%g";
        }
    }

    return genreFilterParams;
}

function getGenreFilterMatchAnyParam() {
    var genreMatchAny = "1";
    var genreMatchAnyRadio = document.getElementById("genre-filter-matchany");
    if (genreMatchAnyRadio && !genreMatchAnyRadio.checked) {
        genreMatchAny = "0";
    }
    
    return genreMatchAny;
}

function getContentTypeFilterParam() {
    var filterParams = "";
    var filterDelimiter = "";
    var checkboxes = [];
    var filterEl = document.getElementById("contenttype-filter");
    if (filterEl) {
        checkboxes = filterEl.getElementsByTagName("input");
    }

    for (var i=0; i < checkboxes.length; i++) {
        if (checkboxes[i].type == "checkbox" && checkboxes[i].checked) {
            var contentType = checkboxes[i].getAttribute("data-contenttype");
            filterParams = filterParams + filterDelimiter + contentType;
            filterDelimiter = "%c";
        }
    }

    return filterParams;
}

function clearFilmlistFilter() {
    var filterEl = document.getElementById("filmlist-filter");
    var checkboxes = filterEl.getElementsByTagName("input");
    var checkmarks = filterEl.getElementsByClassName("fa-check-circle");

    var i;
    for (i=0; i < checkboxes.length; i++) {
        if (checkboxes[i].type == "checkbox") {
            checkboxes[i].checked = false;
        }
    }
    for (i=0; i < checkmarks.length; i++) {
        checkmarks[i].className = "far fa-check-circle checkmark-off";
    }
}

function clearGenreFilter() {
    var filterEl = document.getElementById("genre-filter");
    var checkboxes = filterEl.getElementsByTagName("input");
    var checkmarks = filterEl.getElementsByClassName("fa-check-circle");

    var i;
    for (i=0; i < checkboxes.length; i++) {
        if (checkboxes[i].type == "checkbox") {
            checkboxes[i].checked = false;
        }
    }
    for (i=0; i < checkmarks.length; i++) {
        checkmarks[i].className = "far fa-check-circle checkmark-off";
    }
}

function clearContentTypeFilter() {
    var filterEl = document.getElementById("contenttype-filter");
    var checkboxes = filterEl.getElementsByTagName("input");
    var checkmarks = filterEl.getElementsByClassName("fa-check-circle");

    var i;
    for (i=0; i < checkboxes.length; i++) {
        if (checkboxes[i].type == "checkbox") {
            checkboxes[i].checked = false;
        }
    }
    for (i=0; i < checkmarks.length; i++) {
        checkmarks[i].className = "far fa-check-circle checkmark-off";
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
        checkmark.className = "far fa-check-circle checkmark-on";
    } else {
        checkmark.className = "far fa-check-circle checkmark-off";
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
        previousEl.disabled = false;
        previousEl.setAttribute("onclick", "submitPageForm(" + previousPageNum + ");");
    } else {
        previousEl.disabled = true;
        previousEl.removeAttribute("onclick");
    }

    // Next button
    var nextEl = document.getElementById("next");
    if (totalCount > pageNum * pageSize) {
        nextPageNum = pageNum + 1;
        nextEl.disabled = false;
        nextEl.setAttribute("onclick", "submitPageForm(" + nextPageNum + ");");
    } else {
        nextEl.disabled = true;
        nextEl.removeAttribute("onclick");
    }

    // Page select
    var pageSelectEl = document.getElementById("page-select");
    pageSelectEl.innerHTML = "";
    for (var pageOption = 0; totalCount > pageOption * pageSize; pageOption++) {
        var optionEl = document.createElement("li");
        optionNum = pageOption + 1;
        optionEl.innerHTML = optionNum;
        optionEl.setAttribute("onClick", "submitPageForm("+optionNum+")");
        pageSelectEl.appendChild(optionEl);
    }
    document.getElementById("page-select-label").innerHTML = pageNum;
    
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
    formEl["param-sort"].value = filterParamsArr["sort"];
    formEl["param-direction"].value = filterParamsArr["direction"];
    formEl["param-filterlists"].value = filterParamsArr["filterlists"];
    formEl["param-filtergenreany"].value = filterParamsArr["filtergenreany"];
    formEl["param-filtergenres"].value = filterParamsArr["filtergenres"];
    formEl["param-filtercontenttypes"].value = filterParamsArr["filtercontenttypes"];

    document.forms["pageForm"].submit();
}

function getSortParam() {
    var sort = "";
    var sortSelect = document.getElementById("sort");
    if (sortSelect) {
        sort = sortSelect.value;
    }
    
    return sort;
}

function getDirectionParam() {
    var direction = "";
    var directionEl = document.getElementById("direction");
    if (directionEl) {
        direction = directionEl.value;
    }
    
    return direction;
}

function onChangeSort() {
    // Set sort direction to default
    setSortDirection();
    
    setFilmlistFilter();
}

function setSortDirection(direction) {
    var directionEl = document.getElementById("direction");
    var imageEl = document.getElementById("direction-image");

    // Use default (desc) unless it is specifcally setting to asc
    if (!direction || direction != "asc") {
        direction = "desc";
    }

    if (directionEl) {
        directionEl.value = direction;
    }

    if (imageEl) {
        if (direction == "asc") {
            imageEl.setAttribute("src", "/image/sort-asc.png");
            imageEl.setAttribute("alt", "Ascending order");
        } else {
            imageEl.setAttribute("src", "/image/sort-desc.png");
            imageEl.setAttribute("alt", "Descending order");
        }
    }
}

function toggleSortDirection() {
    var direction = "desc";
    var directionEl = document.getElementById("direction");

    if (directionEl && directionEl.value == "desc") {
        direction = "asc";
    }

    setSortDirection(direction);
    setFilmlistFilter();
}

function changeContentTypeFilter() {
    var url = window.location.href;
    if (-1 < url.indexOf("ratings.php")) {
        getRsRatings(defaultPageSize, 1);
    } else {
        getFilmsForFilmlist(defaultPageSize, 1);
    }
}