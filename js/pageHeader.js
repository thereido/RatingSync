
var oldHeaderSearchQuery = "";
var searchDomain = "all"; // all, ratings, list, both

function toggleHidden(elementId) {
	var el = document.getElementById(elementId);
    el.hidden = !el.hidden;
}

function onKeyUpHeaderSearch(event) {
    var key = event.key;
    if (key == 'ArrowUp' || key == "ArrowDown") {
        // Up or Down arrow
        var newSelectedEl;
	    var selectedUniqueNameEl = document.getElementById("selected-suggestion-uniquename");
	    selectedUniqueNameEl.value = "";
        var suggestionBoxEl = document.getElementById("header-search-suggestion");
        var selectedEls = suggestionBoxEl.getElementsByClassName("suggestion-selected");
        if (selectedEls.length == 1) {
            // Unslect this and selected a sibling (prev for Up and next for Down)
            var originallySelected = selectedEls[0];
            newSelectedEl = originallySelected;
            var sibling;
            if (key == "ArrowUp") {
                // Up arrow
                sibling = originallySelected.previousSibling;
                newSelectedEl = sibling;
                originallySelected.setAttribute("class", ""); // Not selected
            } else {
                // Down arrow (keycode 40)
                sibling = originallySelected.nextSibling;
            }
            if (sibling) {
                newSelectedEl = sibling;
                originallySelected.setAttribute("class", ""); // Not selected
                newSelectedEl.setAttribute("class", "suggestion-selected");
            }
        } else if (key == "ArrowDown") {
            // None selected. Select the first item
            var suggestionEls = suggestionBoxEl.getElementsByTagName("a");
            if (suggestionEls.length > 0) {
                newSelectedEl = suggestionEls[0];
                newSelectedEl.setAttribute("class", "suggestion-selected");
            }
        }

        if (newSelectedEl) {
            var selectedItemEl = newSelectedEl.getElementsByClassName("search-suggestion-item")[0];
	        selectedUniqueNameEl.value = selectedItemEl.getAttribute("data-imdb-uniquename");
        }
    } else if (key == 'Escape') {
        document.getElementById("header-search-suggestion").hidden = true;
    } else {
        updateHeaderSearch();
    }
}

function updateHeaderSearch(changedSearchDomain) {
    var query = document.getElementById("header-search-text").value.trim();
	var suggestionEl = document.getElementById("header-search-suggestion");

    if (query.length == 0) {
	    document.getElementById("selected-suggestion-uniquename").value = "";
        suggestionEl.innerHTML = "";
        suggestionEl.hidden = true;
    } else if (query.length > 2 && (query != oldHeaderSearchQuery || changedSearchDomain)) {
        if (changedSearchDomain) {
            suggestionEl.innerHTML = "";
            suggestionEl.hidden = true;
        }

	    document.getElementById("selected-suggestion-uniquename").value = "";
	    var xmlhttp = new XMLHttpRequest();
        var callbackHandler = function () { searchSuggestionCallback(query, xmlhttp); };
        searchFilms(query, xmlhttp, callbackHandler);
    }
    oldHeaderSearchQuery = query;
}

function onSubmitHeaderSearch() {
    var formEl = document.getElementById("header-search-form");
    var selectedUniqueNameEl = document.getElementById("selected-suggestion-uniquename");
    if (selectedUniqueNameEl.value) {
        formEl.action = "/php/detail.php";
    } else {
        formEl.action = "/php/search.php?sd=" + searchDomain;
    }
}

function showHeaderSearchInput(searchQuery) {
    var inputEl = document.getElementById("header-search-text");
    if (inputEl) {
        inputEl.value = searchQuery;
    }
}

function onClickSearchDropdown(newSearchDomain) {
    if (newSearchDomain == searchDomain) {
        return;
    }

    // Update the search
    updateHeaderSearchDomain(newSearchDomain);
    updateHeaderSearch(true);
}

function updateHeaderSearchDomain(newSearchDomain) {
    searchDomain = newSearchDomain;

    var hintText = "";
    var clickedItem = "";
    if (newSearchDomain == "ratings") {
        hintText = "Search your ratings";
    } else if (newSearchDomain == "list") {
        hintText = "Search watchlist";
    } else if (newSearchDomain == "both") {
        hintText = "Search both";
    } else {
        hintText = "Search";
    }
    
    // Set hint text
    var searchTextEl = document.getElementById("header-search-text");
    searchTextEl.placeholder = hintText;
}