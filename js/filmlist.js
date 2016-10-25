
// userlist (JSON) - all of the user's filmlists
// listnames - lists this film belongs in
function renderFilmlists(includedListnames, filmId) {
    if (!userlistsJson) {
        renderFilmlistsHandler = function () { renderFilmlists(includedListnames, filmId); };
        getFilmlists(renderFilmlistsHandler);
        return;
    }
    
    var defaultList = "Watchlist";
    var defaultListHtmlSafe = defaultList;
    var classCheckmarkOn = "glyphicon glyphicon-check checkmark-on";
    var classCheckmarkOff = "glyphicon glyphicon-check checkmark-off";
    var defaultListClass = classCheckmarkOff;
    var userlists = JSON.parse(userlistsJson);
    if (includedListnames === undefined) {
        includedListnames = [];
    }
    
    var listItemsHtml = "";
    for (var x = 0; x < userlists.length; x++) {
        var currentUserlist = userlists[x].listname;
        if (currentUserlist == defaultList) {
            if (-1 != includedListnames.indexOf(currentUserlist)) {
                defaultListClass = classCheckmarkOn;
            }
        } else {
            listnameHtmlSafe = currentUserlist;
            var checkmarkClass = classCheckmarkOff;
            if (-1 != includedListnames.indexOf(currentUserlist)) {
                checkmarkClass = classCheckmarkOn;
            }

            listItemsHtml = listItemsHtml + "      <li class='filmlist' id='filmlist-"+listnameHtmlSafe+"-"+filmId+"'>\n";
            listItemsHtml = listItemsHtml + "        <a href='#' onClick='toggleFilmlist(\""+listnameHtmlSafe+"\", "+filmId+", \"filmlist-btn-"+listnameHtmlSafe+"-"+filmId+"\")' id='filmlist-btn-"+listnameHtmlSafe+"-"+filmId+"'><span class='"+checkmarkClass+"' id='filmlist-checkmark-"+filmId+"'></span> "+currentUserlist+"</a>\n";
            listItemsHtml = listItemsHtml + "      </li>\n";
        }
    }
    listItemsHtml = listItemsHtml + "      <li class='divider'></li>\n";
    listItemsHtml = listItemsHtml + "      <li><a href='/php/userlist.php?id="+filmId+"'>New list</a></li>\n";
    
    var html = "";
    html = html + "<div class='btn-group-vertical film-filmlists'>\n";
    html = html + "  <button class='btn btn-sm btn-primary' onClick='toggleFilmlist(\""+defaultListHtmlSafe+"\", "+filmId+", \"filmlist-btn-default-"+filmId+"\")' id='filmlist-btn-default-"+filmId+"' data-listname='"+defaultList+"' type='button'>\n";
    html = html + "    <span class='"+defaultListClass+"' id='filmlist-checkmark-"+filmId+"'></span> "+defaultList+"\n";
    html = html + "  </button>\n";
    html = html + "  <div class='btn-group'>\n";
    html = html + "    <button class='btn btn-sm btn-primary dropdown-toggle' id='filmlist-btn-others-"+filmId+"' data-toggle='dropdown' type='button'>\n";
    html = html + "      More lists <span class='caret'></span>\n";
    html = html + "    </button>";
    html = html + "    <ul class='dropdown-menu' id='filmlists-"+filmId+"' role='menu'  >\n";
    html = html +        listItemsHtml + "\n";
    html = html + "    </ul>\n";
    html = html + "  </div>\n";
    html = html + "</div>\n";

    var container = document.getElementById("filmlist-container-"+filmId);
    container.innerHTML = html;
    addFilmlistListeners(container, filmId);
}