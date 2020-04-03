<?php
namespace RatingSync;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "main.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "Constants.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "Genre.php";

function getHtmlUserlistHeader($listnames, $sort, $sortDirection, $currentListname = "", $displayListname = "", $offerFilter = true) {
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
    if (count($listnames) > 1) {
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
    if (count($genres) > 1) {
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
    $contentFilterHtml .= '<div class="d-inline-flex rs-dropdown-checklist" onmouseleave="setFilmlistFilter();">'."\n";
    $contentFilterHtml .= '  <button class="btn btn-md btn-primary" onclick="setFilmlistFilter();">Types</button>'."\n";
    $contentFilterHtml .= '  <div class="rs-dropdown-checklist-content" id="contenttype-filter">'."\n";
    $contentFilterHtml .= '    <a href="javascript:void(0)" onClick="clearContentTypeFilter();">Clear filter</a>'."\n";
    $contentFilterHtml .= '    <div class="dropdown-divider"></div>'."\n";
    $contentFilterHtml .=      getHtmlContentTypeForFilter();
    $contentFilterHtml .= '  </div>'."\n";
    $contentFilterHtml .= '</div>'."\n";

    // Sort
    $isRatingsPage = false;
    if ($displayListname == Constants::RATINGS_PAGE_LABEL) {
        $isRatingsPage = true;
    }
    $sortOptionsHtml = "";
    $hiddenSort = "";
    if ($isRatingsPage) {
        $selectedDate = $sort == "date" ? "selected" : "";
        $selectedScore = $sort == "score" ? "selected" : "";
        $sortOptionsHtml .= '    <option value="date" '.$selectedDate.'>Date</option>'."\n";
        $sortOptionsHtml .= '    <option value="score" '.$selectedScore.'>Stars</option>'."\n";
    } else {
        $hiddenSort = "hidden";
        $selectedPos = $sort == "pos" ? "selected" : "";
        $selectedMod = $sort == "mod" ? "selected" : "";
        $sortOptionsHtml .= '    <option value="pos" '.$selectedPos.'>Position</option>'."\n";
        $sortOptionsHtml .= '    <option value="mod" '.$selectedMod.'>Added</option>'."\n";
    }
    $sortHtml = "";
    $sortHtml  = '<select class="ml-auto mt-3"  id="sort" onchange="onChangeSort(\'desc\', '.$isRatingsPage.');" '.$hiddenSort.'>'."\n";
    $sortHtml .=    $sortOptionsHtml;
    $sortHtml .= '</select>'."\n";
    $sortHtml .= '<a href="javascript:void(0);" style="text-decoration: none; color: inherit;">'."\n";
    $sortHtml .= '  '.getHtmlSortDirectionIcon("direction-image-asc-ratings",  true,  "asc",  $sortDirection, $isRatingsPage)."\n";
    $sortHtml .= '  '.getHtmlSortDirectionIcon("direction-image-desc-ratings", true,  "desc", $sortDirection, $isRatingsPage)."\n";
    $sortHtml .= '  '.getHtmlSortDirectionIcon("direction-image-asc-list",     false, "asc",  $sortDirection, $isRatingsPage)."\n";
    $sortHtml .= '  '.getHtmlSortDirectionIcon("direction-image-desc-list",    false, "desc", $sortDirection, $isRatingsPage)."\n";
    $sortHtml .= '</a>'."\n";
    $sortHtml .= '<input type="text" id="direction" value="' . $sortDirection . '" hidden>'."\n";
    $sortHtml .= '<input type="text" id="sort" value="' . $sort . '" hidden>'."\n";

    $html = "\n";
    $html .= '' . "\n";
    $html .= "<div class='card mt-3'>\n";
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

function getHtmlSortDirectionIcon($id, $forRatingsPage, $direction, $currentDirection, $isRatingsPage) {
    // These are attributes for the new element
    //   class     - font awesome image
    //   name     - onClick function will need to know which icons to hide or show
    //   direction - asc/desc
    //   page      - page name: ratings/list
    //   onClick   - do the sorting and hide/show the right icons
    //   title     - text to show when the user hovers
    //   hidden    - "hidden" or "". Only one icon is showing at a time.

    // class
    $upOrDown = "down";
    if ($direction == "asc") {
        $upOrDown = "up";
    }

    $class = "class='fas fa-sort-amount-$upOrDown'";

    // name
    $name = "name='direction-image'";

    // direction attribute
    $directionAttr = "data-direction='$direction'";

    // page
    $page = "data-page='list'";
    if ($forRatingsPage) {
        $page = "data-page='ratings'";
    }

    // onClick
    $iconIsForRatingsPage = $forRatingsPage ? "true" : "false";
    $onClick = 'onClick="toggleSortDirection(\''.$iconIsForRatingsPage.'\');"';
    
    // title
    $title = "Descending order";
    if ($direction == "asc") {
        $title = "Ascending order";
    }
    $title = "title='$title'";

    // hidden
    $hidden = "hidden";
    if ($forRatingsPage == $isRatingsPage && $direction == $currentDirection) {
        $hidden = "";
    }

    $html = "<i $class $name $directionAttr $page $onClick $title $hidden></i>"."\n";
    
    return $html;
}

function getHmtlFilmlistPagination($action) {
    $html = "";
    $html .= '  <form name="pageForm" class="form-inline" action="'.$action.'" method="post">'."\n";
    $html .= '    <input id="param-p" name="p" hidden>';
    $html .= '    <input id="param-sort" name="sort" hidden>';
    $html .= '    <input id="param-direction" name="direction" hidden>';
    $html .= '    <input id="param-filterlists" name="filterlists" hidden>';
    $html .= '    <input id="param-filtergenreany" name="filtergenreany" hidden>';
    $html .= '    <input id="param-filtergenres" name="filtergenres" hidden>';
    $html .= '    <input id="param-filtercontenttypes" name="filtercontenttypes" hidden>';
    $html .= '    <div id="pagination" class="mx-auto my-3">'."\n";
    $html .= '      <div class="input-group">'."\n";
    $html .= '        <div class="input-group-prepend">'."\n";
    $html .= '          <button id="previous" class="input-group-text btn" aria-disabled="true">Previous</button>'."\n";
    $html .= '        </div>'."\n";
    $html .= '        <button type="button" class="input-group-text btn btn-default dropdown-toggle rounded-0" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'."\n";
    $html .= '          <span id="page-select-label"></span><span class="caret"></span>'."\n";
    $html .= '        </button>'."\n";
    $html .= '        <ul id="page-select" class="dropdown-menu pagination-options" aria-labelledby="paginationDropdown">'."\n";
    $html .= '        </ul>'."\n";
    $html .= '        <div class="input-group-append">'."\n";
    $html .= '          <button id="next" class="input-group-text btn">Next</button>'."\n";
    $html .= '        </div>'."\n";
    $html .= '      </div>'."\n";
    $html .= '    </div>'."\n";
    $html .= '  </form>'."\n";

    return $html;
}

?>