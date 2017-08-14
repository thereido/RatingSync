
var oldHeaderSearchQuery = "";

function toggleHidden(elementId) {
	var el = document.getElementById(elementId);
    el.hidden = !el.hidden;
}

function onKeyUpHeaderSearch(event) {
    var keyCode = ('which' in event) ? event.which : event.keyCode;
    if (keyCode == 38 || keyCode == 40) {
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
            if (keyCode == 38) {
                // Up arrow
                sibling = originallySelected.previousSibling;
                newSelectedEl = sibling;
                originallySelected.setAttribute("class", "search-suggestion-item"); // Not selected
            } else {
                // Down arrow (keycode 40)
                sibling = originallySelected.nextSibling;
            }
            if (sibling) {
                newSelectedEl = sibling;
                originallySelected.setAttribute("class", "search-suggestion-item"); // Not selected
                newSelectedEl.setAttribute("class", "search-suggestion-item suggestion-selected");
            }
        } else if (keyCode == 40) {
            // None selected. Select the first item
            var suggestionEls = suggestionBoxEl.getElementsByTagName("div");
            if (suggestionEls.length > 0) {
                newSelectedEl = suggestionEls[0];
                newSelectedEl.setAttribute("class", "search-suggestion-item suggestion-selected");
            }
        }

        if (newSelectedEl) {
	        selectedUniqueNameEl.value = newSelectedEl.getAttribute("data-imdb-uniquename");
        }
    } else {
        updateHeaderSearch();
    }
}

function updateHeaderSearch() {
    var query = document.getElementById("header-search-text").value.trim();
    if (query.length == 0) {
	    document.getElementById("selected-suggestion-uniquename").value = "";
	    var suggestionEl = document.getElementById("header-search-suggestion");
        suggestionEl.innerHTML = "";
        suggestionEl.hidden = true;
    } else if (query.length > 2 && query != oldHeaderSearchQuery) {
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
        formEl.action = "/php/search.php";
    }
}

function showHeaderSearchInput(searchQuery) {
    var inputEl = document.getElementById("header-search-text");
    if (inputEl) {
        inputEl.value = searchQuery;
    }
}