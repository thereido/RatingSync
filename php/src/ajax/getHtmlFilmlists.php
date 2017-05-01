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
        $parentListsHtml .= '<a href="/php/userlist.php?l='.$ancestorListname.'">'.$ancestorListname.'</a>&nbsp;&rarr;&nbsp;'."\n";
    }

    // Content type filter
    $filterContentTypesChecked = array("featurefilms" => "checked", "tvseries" => "checked", "tvepisodes" => "checked", "shortfilms" => "checked");
    if (array_value_by_key("feature", $_POST) == "0") {
        $filterContentTypesChecked["featurefilms"] = "";
    }
    if (array_value_by_key("tvseries", $_POST) == "0") {
        $filterContentTypesChecked["tvseries"] = "";
    }
    if (array_value_by_key("tvepisodes", $_POST) == "0") {
        $filterContentTypesChecked["tvepisodes"] = "";
    }
    if (array_value_by_key("shorts", $_POST) == "0") {
        $filterContentTypesChecked["shortfilms"] = "";
    }
    $contentTypeHtml = "\n";
    $contentTypeHtml .= "    <div>\n";
    $contentTypeHtml .= "      <div class='checkbox-inline filmlist-checkbox-inline' onchange='changeContentTypeFilter()'>\n";
    $contentTypeHtml .= "        <label><input id='featurefilms' type='checkbox' value='Film::CONTENT_FILM' ".$filterContentTypesChecked["featurefilms"].">Movies</label>\n";
    $contentTypeHtml .= "      </div>\n";
    $contentTypeHtml .= "      <div class='checkbox-inline filmlist-checkbox-inline' onchange='changeContentTypeFilter()'>\n";
    $contentTypeHtml .= "        <label><input id='tvseries' type='checkbox' value='Film::CONTENT_TV_SERIES' ".$filterContentTypesChecked["tvseries"].">TV Series</label>\n";
    $contentTypeHtml .= "      </div>\n";
    $contentTypeHtml .= "      <div class='checkbox-inline filmlist-checkbox-inline' onchange='changeContentTypeFilter()'>\n";
    $contentTypeHtml .= "        <label><input id='tvepisodes' type='checkbox' value='Film::CONTENT_TV_EPISODE' ".$filterContentTypesChecked["tvepisodes"].">TV Episodes</label>\n";
    $contentTypeHtml .= "      </div>\n";
    $contentTypeHtml .= "      <div class='checkbox-inline filmlist-checkbox-inline' onchange='changeContentTypeFilter()'>\n";
    $contentTypeHtml .= "        <label><input id='shortfilms' type='checkbox' value='Film::CONTENT_SHORTFILM' ".$filterContentTypesChecked["shortfilms"].">Short Films</label>\n";
    $contentTypeHtml .= "      </div>\n";
    $contentTypeHtml .= "    </div>\n";
    if (!$offerFilter) {
        $contentTypeHtml = "";
    }

    $listFilterHtml = "";
    if ($currentListname != "Create New List" && count($listnames) > 1) {
        $listFilterHtml .= '<div class="rs-dropdown-checklist" onmouseleave="setFilmlistFilter();">'."\n";
        $listFilterHtml .= '  <button class="btn btn-md btn-primary" onclick="setFilmlistFilter();">Filter</button>'."\n";
        $listFilterHtml .= '  <div class="rs-dropdown-checklist-content" id="filmlist-filter">'."\n";
        $listFilterHtml .= '    <a href="javascript:void(0)" onClick="clearFilmlistFilter();">Clear filter</a>';
        $listFilterHtml .=      getHtmlFilmlistNamesForFilter($listnames, $currentListname);
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

function getHtmlFilmlistNamesForFilter($listnames, $currentListname, $level = 0) {
    $html = "";
    $filterLists = explode("%l", array_value_by_key("filterlists", $_POST));

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
            $checkmark = '<span class="'.$class.'" id="filmlist-filter-checkmark-'.$listname.'"></span> ';
            $onClick = "onClick=\"toggleFilmlistFilter('filmlist-filter-$listname', 'filmlist-filter-checkbox-$listname');\"";

            if ($currentListname == "$listname") {
                $checkmark = "&nbsp;&nbsp;&nbsp;&nbsp;";
                $onClick = "";
            }

            $html .= '    <input hidden type="checkbox" id="filmlist-filter-checkbox-'.$listname.'" data-listname="'.$listname.'" '.$checked.'>'."\n";
            $html .= '    <a href="javascript:void(0)" '.$onClick.' id="filmlist-filter-'.$listname.'">'.$prefix.$checkmark.$listname.'</a>'."\n";
            $html .= getHtmlFilmlistNamesForFilter($list["children"], $currentListname, $level+1);
        }
    }

    return $html;
}

function getHmtlFilmlistPagination($action) {
    $html = "";
    $html .= '<form name="pageForm" action="'.$action.'" method="post">';
    $html .= '    <input id="param-p" name="p" hidden>';
    $html .= '    <input id="param-feature" name="feature" hidden>';
    $html .= '    <input id="param-tvseries" name="tvseries" hidden>';
    $html .= '    <input id="param-tvepisodes" name="tvepisodes" hidden>';
    $html .= '    <input id="param-shorts" name="shorts" hidden>';
    $html .= '    <input id="param-filterlists" name="filterlists" hidden>';
    $html .= '    <ul id="pagination" class="pager" hidden>';
    $html .= '        <li id="previous"><a href="javascript:void(0);">Previous</a></li>';
    $html .= '        <li><select id="page-select" onchange="changePageNum()"></select></li>';
    $html .= '        <li id="next"><a href="javascript:void(0);">Next</a></li>';
    $html .= '    </ul>';
    $html .= '</form>';

    return $html;
}

?>