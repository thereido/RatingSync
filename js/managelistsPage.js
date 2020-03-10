
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
        if (addThisEl != null && addThisEl.checked == false) {
            params = params + "&a=0";
        } else {
            params = params + "&a=1";
        }
    }
    
    var parentListEl = document.getElementById("filmlist-parent");
    if (parentListEl != null) {
        var parentList = parentListEl.value;
        if (parentList != null && parentList != "---") {
            params = params + "&parent=" + parentList;
        }
    }

    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            window.location = "/php/managelists.php";
        }
    }
    xmlhttp.open("GET", "/php/src/ajax/api.php?action=createFilmlist"+params, true);
    xmlhttp.send();

    return false;
}

function getFilmlists() {
    var params = "?action=getUserLists";
	var xmlhttp = new XMLHttpRequest();
    var callbackHandler = function () { getFilmlistsCallback(xmlhttp); };
    xmlhttp.onreadystatechange = callbackHandler;
	xmlhttp.open("GET", RS_URL_API + params, true);
	xmlhttp.send();
}

function getFilmlistsCallback(xmlhttp) {
	if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
        contextData.filmlists = JSON.parse(xmlhttp.responseText);
        renderFilmlists();
	}
}

function renderFilmlists() {
    var filmlistsEl = document.getElementById("filmlists");
    filmlistsEl.innerHTML = "";

    var lists = contextData.filmlists;
    for (var i = 0; i < lists.length; i++) {
        var filmlist = lists[i];

        // Structure elements
        var cardEl = document.createElement("div");
        var cardColEl = document.createElement("div");

        // Append elements into the filmlists element
        filmlistsEl.appendChild(cardEl);
        cardEl.appendChild(cardColEl);
        
        // Style classes
        cardEl.setAttribute("class", "row mx-0");
        cardColEl.setAttribute("class", "col");

        renderFilmlist(filmlist, cardColEl, 1);
    }
}

function renderFilmlist(filmlist, containerEl, level) {
    var indentation = "";
    for (i = 1; i < level; i++) {
        indentation = indentation + "&nbsp;&nbsp;&nbsp;&nbsp;";
    }

    // Structure elements
    filmlistEl = document.createElement("div");
    detailColEl = document.createElement("div");
    buttonColEl = document.createElement("div");
    indentationEl = document.createElement("span");
    childrenEl = document.createElement("div");

    // Content elements
    userlistPageLinkEl = document.createElement("a");
    listnameEl = document.createElement("span");
    itemCountEl = document.createElement("small");
    caretEl = document.createElement("button");
    buttonBoxEl = document.createElement("div");
    renameButtonEl = document.createElement("button");
    deleteButtonEl = document.createElement("button");

    // Append elements into the filmlists element
    containerEl.appendChild(filmlistEl);
    filmlistEl.appendChild(detailColEl);
    filmlistEl.appendChild(buttonColEl);
    containerEl.appendChild(childrenEl);
    
    detailColEl.appendChild(indentationEl);
    detailColEl.appendChild(userlistPageLinkEl);
    userlistPageLinkEl.appendChild(listnameEl);
    userlistPageLinkEl.appendChild(itemCountEl);
    detailColEl.appendChild(caretEl);
    buttonColEl.appendChild(buttonBoxEl);
    if (filmlist.listname != getDefaultList()) {
        buttonBoxEl.appendChild(renameButtonEl);
        buttonBoxEl.appendChild(deleteButtonEl);
    }
    
    // Style classes
    filmlistEl.setAttribute("class", "row border py-2");
    detailColEl.setAttribute("class", "col filmlist");
    buttonColEl.setAttribute("class", "col-auto ml-auto");
    itemCountEl.setAttribute("class", "text-secondary ml-1");
    caretEl.setAttribute("class", "ml-3 btn btn-light fas fa-angle-down fa-xs border");
    renameButtonEl.setAttribute("class", "btn btn-secondary mx-1 far fa-xs");
    deleteButtonEl.setAttribute("class", "btn btn-danger far fa-trash-alt fa-xs");

    // Attrs
    var filmlistElId = "filmlist-" + encodeURI(filmlist.listname);
    var childrenElId = "filmlist-children-" + encodeURI(filmlist.listname);
    filmlistEl.setAttribute("id", filmlistElId);
    userlistPageLinkEl.setAttribute("href", encodeURI("/php/userlist.php?l=" + filmlist.listname));
    caretEl.setAttribute("onClick", "toggleChildFilmlists('"+filmlist.listname+"')");
    if (filmlist.children.length == 0) {
        caretEl.setAttribute("hidden", "true");
    }
    renameButtonEl.setAttribute("id", "filmlist-rename-" + encodeURI(filmlist.listname));
    deleteButtonEl.setAttribute("id", "filmlist-delete-" + encodeURI(filmlist.listname));
    deleteButtonEl.setAttribute("data-toggle", "modal");
    deleteButtonEl.setAttribute("data-target", "#delete-modal");
    deleteButtonEl.setAttribute("data-listname", filmlist.listname);
    childrenEl.setAttribute("id", childrenElId);
    childrenEl.setAttribute("hidden", "true");

    // Hide Rename button until it is implemented
    renameButtonEl.setAttribute("hidden", "true");

    // Content
    indentationEl.innerHTML = indentation;
    listnameEl.innerHTML = filmlist.listname;
    itemCountEl.innerHTML = filmlist.items.length + " titles";
    renameButtonEl.innerHTML = "Rename";

    var children = filmlist.children;
    for (var i = 0; i < children.length; i++) {
        var containerForMyChildren = document.getElementById("filmlist-children-" + encodeURI(filmlist.listname));
        renderFilmlist(children[i], containerForMyChildren, level + 1);
    }
}

function toggleChildFilmlists(listname) {
    var childrenEl = document.getElementById("filmlist-children-" + encodeURI(listname));
    var hidden = childrenEl.getAttribute("hidden");

    if (hidden) {
        childrenEl.removeAttribute("hidden");
    }
    else {
        childrenEl.setAttribute("hidden", "true");
    }
}

function deleteFilmlist() {
    var listname = document.getElementById("delete-listname").value;
    disableManageListButtons(listname, true);

    var params = "?action=deleteFilmlist";
    params = params + "&l=" + encodeURI(listname);
	var xmlhttp = new XMLHttpRequest();
    var callbackHandler = function () { deleteFilmlistCallback(xmlhttp, listname); };
    xmlhttp.onreadystatechange = callbackHandler;
	xmlhttp.open("GET", RS_URL_API + params, true);
	xmlhttp.send();
}

function deleteFilmlistCallback(xmlhttp, listname) {
    if (xmlhttp.readyState == 4) {
        var success = false;
        if (xmlhttp.status == 200) {
            result = JSON.parse(xmlhttp.responseText);
            if (result.Success == "true") {
                success = true;
            }
        }

        $('#delete-modal').modal('hide');

        // Remove the deleted filmlist from the Nav Lists menu
        var deletedLists = result.DeletedLists;
        if (deletedLists) {
            for (i = 0; i < deletedLists.length; i++) {
                var menuItemEl = document.getElementById("nav-lists-item-" + deletedLists[i]);
                menuItemEl.remove();
            }
        }

	    if (success) {
            // Remove the deleted filmlist from the page
            var filmlistEl = document.getElementById("filmlist-" + encodeURI(listname));
            var childrenEl = document.getElementById("filmlist-children-" + encodeURI(listname));
            filmlistEl.remove();
            childrenEl.remove();
        }
        else {
            $('#delete-fail-modal').modal('show');
            disableManageListButtons(listname, false);
        }
	}
}

function disableManageListButtons(listname, isDisabled = true) {
    var renameButtonEl = document.getElementById("filmlist-rename-" + encodeURI(listname));
    var deleteButtonEl = document.getElementById("filmlist-delete-" + encodeURI(listname));

    renameButtonEl.disabled = isDisabled;
    deleteButtonEl.disabled = isDisabled;
}