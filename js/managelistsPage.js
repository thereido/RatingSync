
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
    // Structure elements
    filmlistEl = document.createElement("div");
    childrenEl = document.createElement("div");

    // Append elements into the container element
    containerEl.appendChild(filmlistEl);
    containerEl.appendChild(childrenEl);

    // Attrs
    filmlistEl.setAttribute("id", "filmlist-" + encodeURI(filmlist.listname));

    renderFilmlistRow(filmlist, level);
    renderFilmlistChildrenRow(filmlist, level);
}

function renderFilmlistRow(filmlist, level) {
    var indentation = "";
    for (i = 1; i < level; i++) {
        indentation = indentation + "&nbsp;&nbsp;&nbsp;&nbsp;";
    }

    var filmlistEl = document.getElementById("filmlist-" + encodeURI(filmlist.listname));

    // Structure elements
    detailColEl = document.createElement("div");
    buttonColEl = document.createElement("div");
    indentationEl = document.createElement("span");

    // Content elements
    userlistPageLinkEl = document.createElement("a");
    listnameEl = document.createElement("span");
    itemCountEl = document.createElement("small");
    caretEl = document.createElement("button");
    buttonBoxEl = document.createElement("div");
    renameButtonEl = document.createElement("button");
    deleteButtonEl = document.createElement("button");

    // Append elements into the filmlists element
    filmlistEl.appendChild(detailColEl);
    filmlistEl.appendChild(buttonColEl);
    
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
    filmlistEl.setAttribute("data-level", level);
    listnameEl.setAttribute("id", "listname-label-" + encodeURI(filmlist.listname));
    userlistPageLinkEl.setAttribute("href", encodeURI("/php/userlist.php?l=" + filmlist.listname));
    caretEl.setAttribute("onClick", "toggleChildFilmlists('"+filmlist.listname+"')");
    if (filmlist.children.length == 0) {
        caretEl.setAttribute("hidden", "true");
    }
    renameButtonEl.setAttribute("id", "filmlist-rename-" + encodeURI(filmlist.listname));
    renameButtonEl.setAttribute("data-toggle", "modal");
    renameButtonEl.setAttribute("data-target", "#rename-modal");
    renameButtonEl.setAttribute("data-listname", filmlist.listname);
    deleteButtonEl.setAttribute("id", "filmlist-delete-" + encodeURI(filmlist.listname));
    deleteButtonEl.setAttribute("data-toggle", "modal");
    deleteButtonEl.setAttribute("data-target", "#delete-modal");
    deleteButtonEl.setAttribute("data-listname", filmlist.listname);

    // Content
    indentationEl.innerHTML = indentation;
    listnameEl.innerHTML = filmlist.listname;
    itemCountEl.innerHTML = filmlist.items.length + " titles";
    renameButtonEl.innerHTML = "Rename";
}

function renderFilmlistChildrenRow(filmlist, level) {
    // Attrs
    var childrenElId = "filmlist-children-" + encodeURI(filmlist.listname);
    childrenEl.setAttribute("id", childrenElId);
    childrenEl.setAttribute("hidden", "true");

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

function renameFilmlist() {
    var oldListname = document.getElementById("rename-old-listname").value;
    var newListname = document.getElementById("rename-new-listname").value;

    disableManageListButtons(oldListname, true);

    var params = "?action=renameFilmlist";
    params = params + "&oldl=" + encodeURI(oldListname);
    params = params + "&newl=" + encodeURI(newListname);
	var xmlhttp = new XMLHttpRequest();
    var callbackHandler = function () { renameFilmlistCallback(xmlhttp); };
    xmlhttp.onreadystatechange = callbackHandler;
	xmlhttp.open("GET", RS_URL_API + params, true);
	xmlhttp.send();
}

function renameFilmlistCallback(xmlhttp) {
    if (xmlhttp.readyState == 4) {
        var success = false;
        if (xmlhttp.status == 200) {
            result = JSON.parse(xmlhttp.responseText);
            if (result.Success == "true") {
                success = true;
            }
        }

        $('#rename-modal').modal('hide');

        var oldListname = document.getElementById("rename-old-listname").value;
        var newListname = document.getElementById("rename-new-listname").value;

	    if (success) {
            // Update the renamed filmlist on the Nav Lists menu
            var menuItemEl = document.getElementById("nav-lists-item-" + oldListname);
            menuItemEl.innerHTML = newListname;
            menuItemEl.setAttribute("id", "nav-lists-item-" + newListname);

            // Update the renamed filmlist in contextData
            contextData.filmlists = renameList(oldListname, newListname, contextData.filmlists);
            var renamedFilmlist = getFilmlistByName(newListname, contextData.filmlists);

            // Update the renamed filmlist on the page
            if (renamedFilmlist) {
                var filmlistEl = document.getElementById("filmlist-" + encodeURI(oldListname));
                var level = filmlistEl.getAttribute("data-level");
                filmlistEl.innerHTML = "";
                filmlistEl.setAttribute("id", "filmlist-" + encodeURI(newListname));
                renderFilmlistRow(renamedFilmlist, level);
            }

            // Update the id of the children element with the new listname
            var childrenEl = document.getElementById("filmlist-children-" + encodeURI(oldListname));
            childrenEl.setAttribute("id", "filmlist-children-" + encodeURI(newListname));
        }
        else {
            $('#rename-fail-modal').modal('show');
            disableManageListButtons(oldListname, false);
        }
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

function renameList(oldName, newName, lists) {
    var modifiedLists = [];
    for (var listIndex = 0; listIndex < lists.length; listIndex++) {
        var filmlist = lists[listIndex];

        // Rename the list if it's a match
        if (filmlist.listname == oldName) {
            filmlist.listname = newName;
        }

        // Iterate for child lists
        filmlist.children = renameList(oldName, newName, filmlist.children);

        modifiedLists.push(filmlist);
    }

    return modifiedLists;
}

function getFilmlistByName(listname, filmlists) {
    var match = null;
    for (var i = 0; i < filmlists.length; i++) {
        var filmlist = filmlists[i];
        if (filmlist.listname == listname) {
            match = filmlist;
            break;
        }

        match = getFilmlistByName(listname, filmlist.children);
        if (match) {
            break;
        }
    }

    return match;
}