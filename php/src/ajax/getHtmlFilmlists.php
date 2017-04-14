<?php
namespace RatingSync;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "main.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "Constants.php";

function getHtmlFilmlistsHeader($listnames, $currentListname = "", $displayListname = "", $offerFilter = true) {
    $username = getUsername();
    if ($displayListname == "") {
        $displayListname = $currentListname;
    }

    // Parent lists
    $parentListsHtml = "";
    $ancestorListnames = Filmlist::getAncestorListnames($currentListname);
    for ($ancestorIndex = count($ancestorListnames)-1; $ancestorIndex >= 0; $ancestorIndex--) {
        $ancestorListname = $ancestorListnames[$ancestorIndex];
        $parentListsHtml .= '<a href="/php/userlist.php?l='.$ancestorListname.'">'.$ancestorListname.'</a>&nbsp;->&nbsp;'."\n";
    }

    // Content type filter
    $contentTypeHtml = "\n";
    $contentTypeHtml .= "    <div>\n";
    $contentTypeHtml .= "      <div class='checkbox-inline filmlist-checkbox-inline' onchange='changeContentTypeFilter()'>\n";
    $contentTypeHtml .= "        <label><input id='featurefilms' type='checkbox' value='Film::CONTENT_FILM' checked>Movies</label>\n";
    $contentTypeHtml .= "      </div>\n";
    $contentTypeHtml .= "      <div class='checkbox-inline filmlist-checkbox-inline' onchange='changeContentTypeFilter()'>\n";
    $contentTypeHtml .= "        <label><input id='tvseries' type='checkbox' value='Film::CONTENT_TV_SERIES' checked>TV Series</label>\n";
    $contentTypeHtml .= "      </div>\n";
    $contentTypeHtml .= "      <div class='checkbox-inline filmlist-checkbox-inline' onchange='changeContentTypeFilter()'>\n";
    $contentTypeHtml .= "        <label><input id='tvepisodes' type='checkbox' value='Film::CONTENT_TV_EPISODE' checked>TV Episodes</label>\n";
    $contentTypeHtml .= "      </div>\n";
    $contentTypeHtml .= "      <div class='checkbox-inline filmlist-checkbox-inline' onchange='changeContentTypeFilter()'>\n";
    $contentTypeHtml .= "        <label><input id='shortfilms' type='checkbox' value='Film::CONTENT_SHORTFILM' checked>Short Films</label>\n";
    $contentTypeHtml .= "      </div>\n";
    $contentTypeHtml .= "    </div>\n";
    if (!$offerFilter) {
        $contentTypeHtml = "";
    }

    $listFilterHtml = "";
    if ($currentListname == "Watchlist" && count($listnames) > 1) {
        $listFilterHtml .= '<div class="rs-dropdown-checklist" onmouseleave="setFilmlistFilter();">'."\n";
        $listFilterHtml .= '  <button class="btn btn-md btn-primary" onclick="setFilmlistFilter();">Filter</button>'."\n";
        $listFilterHtml .= '  <div class="rs-dropdown-checklist-content" id="filmlist-filter">'."\n";
        $listFilterHtml .= '    <a href="javascript:void(0)" onClick="clearFilmlistFilter();">Clear filter</a>';
        $listFilterHtml .=      getHtmlFilmlistNamesForFilter($listnames);
        $listFilterHtml .= '  </div>'."\n";
        $listFilterHtml .= '</div>'."\n";
    }
    
    $html = "\n";
    $html .= "<div class='well well-sm'>\n";
    $html .=    $parentListsHtml;
    $html .= "  <h2>$displayListname</h2>\n";
    $html .=    $contentTypeHtml;
    $html .=    $listFilterHtml;
    $html .= "</div>\n";

    return $html;
}

function getHtmlFilmlistSelectOptions($listnames, $indent = 0) {
    $username = getUsername();
    $response = "";
    
    foreach ($listnames as $list) {
        $indentSpacing = "";
        $count = $indent;
        while ($count > 0) {
            $indentSpacing .= "&nbsp;&nbsp;";
            $count = $count - 1;
        }

        $listname = $list["listname"];
        $response .= "<option value='$listname'>" . $indentSpacing . $listname . "</option>";
        $response .= getHtmlFilmlistSelectOptions($list["children"], $indent+1);
    }

    return $response;
}

function getHtmlFilmlistNamesForFilter($listnames, $level = 0) {
    $html = "";
    $filterLists = explode("%", array_value_by_key("filterlists", $_GET));

    $prefix = "";
    for ($levelIndex = $level; $levelIndex > 0; $levelIndex--) {
        $prefix .= "&nbsp;&nbsp;&nbsp;&nbsp;";
    }

    foreach ($listnames as $list) {
        $listname = $list["listname"];
        if ($listname != "Watchlist") {
            $checked = "";
            $class = "glyphicon glyphicon-check checkmark-off";
            if (in_array($listname, $filterLists)) {
                $checked = "checked";
                $class = "glyphicon glyphicon-check checkmark-on";
            }
            $onClick = "toggleFilmlistFilter('filmlist-filter-$listname', 'filmlist-filter-checkbox-$listname');";

            $html .= '    <input hidden type="checkbox" id="filmlist-filter-checkbox-'.$listname.'" data-listname="'.$listname.'" '.$checked.'>'."\n";
            $html .= '    <a href="javascript:void(0)" onClick="'.$onClick.'" id="filmlist-filter-'.$listname.'">'.$prefix.'<span class="'.$class.'" id="filmlist-filter-checkmark-'.$listname.'"></span> '.$listname.'</a>'."\n";
            $html .= getHtmlFilmlistNamesForFilter($list["children"], $level+1);
        }
    }

    return $html;
}

?>