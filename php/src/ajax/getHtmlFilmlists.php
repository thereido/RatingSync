<?php
namespace RatingSync;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "main.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "Constants.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "Genre.php";

function getHtmlFilmlistsHeader($listnames, $sortDirection, $currentListname = "", $displayListname = "", $offerFilter = true) {
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

    // List filter
    $listFilterHtml = "";
    if ($currentListname != "Create New List" && count($listnames) > 1) {
        $listFilterHtml .= '<div class="d-inline-flex rs-dropdown-checklist" onmouseleave="setFilmlistFilter();">'."\n";
        $listFilterHtml .= '  <button class="btn btn-md btn-primary" onclick="setFilmlistFilter();">Lists</button>'."\n";
        $listFilterHtml .= '  <div class="rs-dropdown-checklist-content" id="filmlist-filter">'."\n";
        $listFilterHtml .= '    <a href="javascript:void(0)" onClick="clearFilmlistFilter();">Clear filter</a>'."\n";
        $listFilterHtml .= '    <div class="dropdown-divider"></div>'."\n";
        $listFilterHtml .=      getHtmlFilmlistNamesForFilter($listnames, $currentListname) ."\n";;
        $listFilterHtml .= '  </div>'."\n";
        $listFilterHtml .= '</div>'."\n";
    }
    
    // Genre filter
    $filterGenreAnyChecked = array("all" => "checked", "any" => "");
    if (array_value_by_key("filtergenreany", $_POST) == "1") {
        $filterGenreAnyChecked["all"] = "";
        $filterGenreAnyChecked["any"] = "checked";
    }

    $genreFilterHtml = "";
    $genres = Genre::getGenresFromDb();
    if ($currentListname != "Create New List" && count($genres) > 1) {
        $genreFilterHtml .= '<div class="d-inline-flex rs-dropdown-checklist" onmouseleave="setFilmlistFilter();">'."\n";
        $genreFilterHtml .= '  <button class="btn btn-md btn-primary" onclick="setFilmlistFilter();">Genres</button>'."\n";
        $genreFilterHtml .= '  <div class="rs-dropdown-checklist-content" id="genre-filter">'."\n";
        $genreFilterHtml .= '    <checklist-line><input type="radio" name="genreMatchAny" id="genre-filter-matchall" '.$filterGenreAnyChecked["all"].'>Must match all</checklist-line>'."\n";
        $genreFilterHtml .= '    <checklist-line><input type="radio" name="genreMatchAny" id="genre-filter-matchany" '.$filterGenreAnyChecked["any"].'>Match any</checklist-line>'."\n";
        $genreFilterHtml .= '    <a href="javascript:void(0)" onClick="clearGenreFilter();">Clear filter</a>'."\n";
        $genreFilterHtml .= '    <div class="dropdown-divider"></div>'."\n";
        $genreFilterHtml .=      getHtmlGenresForFilter($genres);
        $genreFilterHtml .= '  </div>'."\n";
        $genreFilterHtml .= '</div>'."\n";
    }

    // Content type filter
    $contentFilterHtml = "";
    if ($currentListname != "Create New List") {
        $contentFilterHtml .= '<div class="d-inline-flex rs-dropdown-checklist" onmouseleave="setFilmlistFilter();">'."\n";
        $contentFilterHtml .= '  <button class="btn btn-md btn-primary" onclick="setFilmlistFilter();">Types</button>'."\n";
        $contentFilterHtml .= '  <div class="rs-dropdown-checklist-content" id="contenttype-filter">'."\n";
        $contentFilterHtml .= '    <a href="javascript:void(0)" onClick="clearContentTypeFilter();">Clear filter</a>'."\n";
        $contentFilterHtml .= '    <div class="dropdown-divider"></div>'."\n";
        $contentFilterHtml .=      getHtmlContentTypeForFilter();
        $contentFilterHtml .= '  </div>'."\n";
        $contentFilterHtml .= '</div>'."\n";
    }

    // Sort
    $hiddenSort = "";
    if ($displayListname == Constants::RATINGS_PAGE_LABEL) {
        $hiddenSort = "hidden";
    }
    $sortImage = "/image/sort-$sortDirection.png";
    $sortHtml = "";
    $sortHtml .= '<select class="ml-auto"  id="sort" onchange="onChangeSort();" '.$hiddenSort.'>'."\n";
    $sortHtml .= '  <option value="pos">Position</option>'."\n";
    $sortHtml .= '  <option value="mod">Added</option>'."\n";
    $sortHtml .= '</select>'."\n";
    $sortHtml .= '<input type="text" id="direction" value="' . $sortDirection . '" hidden>'."\n";
    $sortHtml .= '<a href="javascript:void(0);"><img id="direction-image" height="25px" alt="Ascending order" src="' . $sortImage . '" onclick="toggleSortDirection();"></a>'."\n";
    
    $html = "\n";
    $html .= '' . "\n";
    $html .= "<div class='card bg-light mt-3'>\n";
    $html .= '  <div class="card-body">' . "\n";
    $html .=      $parentListsHtml;
    $html .= '    <h2>'.$displayListname.'</h2>' . "\n";
    $html .= '    <div class="row align-items-center">' . "\n";
    $html .= '      <div class="col">' . "\n";
    $html .=          $listFilterHtml;
    $html .=          $genreFilterHtml;
    $html .=          $contentFilterHtml;
    $html .= '      </div>' . "\n";
    $html .= '      <div class="col-auto">' . "\n";
    $html .=          $sortHtml;
    $html .= '      </div>' . "\n";
    $html .= '    </div>' . "\n";
    $html .= "  </div>\n";
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
            $class = "far fa-check-circle checkmark-off";
            if (in_array($listname, $filterLists)) {
                $checked = "checked";
                $class = "far fa-check-circle checkmark-on";
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

function getHtmlGenresForFilter($genres) {
    $html = "";
    $filterGenres = explode("%g", array_value_by_key("filtergenres", $_POST));

    foreach ($genres as $genre) {
        $checked = "";
        $class = "far fa-check-circle checkmark-off";
        if (in_array($genre, $filterGenres)) {
            $checked = "checked";
            $class = "far fa-check-circle checkmark-on";
        }
        $checkmark = '<span class="'.$class.'" id="genre-filter-checkmark-'.$genre.'"></span> ';
        $onClick = "onClick=\"toggleFilmlistFilter('genre-filter-$genre', 'genre-filter-checkbox-$genre');\"";

        $html .= '    <input hidden type="checkbox" id="genre-filter-checkbox-'.$genre.'" data-genre="'.$genre.'" '.$checked.'>'."\n";
        $html .= '    <a href="javascript:void(0)" '.$onClick.' id="genre-filter-'.$genre.'">'.$checkmark.$genre.'</a>'."\n";
    }

    return $html;
}

function getHtmlContentTypeForFilter() {
    $html = "";
    $filter = explode("%c", array_value_by_key("filtercontenttypes", $_POST));

    $contentTypes = array();
    $contentTypes[Film::CONTENT_FILM] = "Movies";
    $contentTypes[Film::CONTENT_TV_SERIES] = "TV Series";
    $contentTypes[Film::CONTENT_TV_EPISODE] = "TV Episodes";
    $contentTypes[Film::CONTENT_SHORTFILM] = "Short Films";
    foreach (array_keys($contentTypes) as $contentType) {
        $checked = "";
        $class = "far fa-check-circle checkmark-off";
        if (in_array($contentType, $filter)) {
            $checked = "checked";
            $class = "far fa-check-circle checkmark-on";
        }
        $checkmark = '<span class="'.$class.'" id="contenttype-filter-checkmark-'.$contentType.'"></span> ';
        $onClick = "onClick=\"toggleFilmlistFilter('contenttype-filter-$contentType', 'contenttype-filter-checkbox-$contentType');\"";

        $html .= '    <input hidden type="checkbox" id="contenttype-filter-checkbox-'.$contentType.'" data-contenttype="'.$contentType.'" '.$checked.'>'."\n";
        $html .= '    <a href="javascript:void(0)" '.$onClick.' id="contenttype-filter-'.$contentType.'">'.$checkmark.$contentTypes[$contentType].'</a>'."\n";
    }

    return $html;
}

function getHmtlFilmlistPagination($action) {
    $html = "";
    $html .= '<form name="pageForm" action="'.$action.'" method="post">';
    $html .= '    <input id="param-p" name="p" hidden>';
    $html .= '    <input id="param-sort" name="sort" hidden>';
    $html .= '    <input id="param-direction" name="direction" hidden>';
    $html .= '    <input id="param-filterlists" name="filterlists" hidden>';
    $html .= '    <input id="param-filtergenreany" name="filtergenreany" hidden>';
    $html .= '    <input id="param-filtergenres" name="filtergenres" hidden>';
    $html .= '    <input id="param-filtercontenttypes" name="filtercontenttypes" hidden>';
    $html .= '    <ul id="pagination" class="pager" hidden>';
    $html .= '        <li id="previous"><a href="javascript:void(0);">Previous</a></li>';
    $html .= '        <li><select id="page-select" onchange="changePageNum()"></select></li>';
    $html .= '        <li id="next"><a href="javascript:void(0);">Next</a></li>';
    $html .= '    </ul>';
    $html .= '</form>';

    return $html;
}

?>