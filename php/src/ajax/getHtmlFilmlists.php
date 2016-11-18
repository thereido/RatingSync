<?php
namespace RatingSync;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "main.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "Constants.php";

function getHtmlFilmlistsHeader($currentListname = "") {
    $username = getUsername();
    $buttonsHtml = "";

    // First list is always Your Ratings
    $disabled = "disabled";
    if ("Your Ratings" != $currentListname) {
        $disabled = "";
    }
    $buttonsHtml .= "    <a href='/php/ratings.php' role='button' class='btn btn-primary' $disabled>Your Ratings</a>\n";

    // Add all other lists
    foreach (Filmlist::getUserListsFromDb($username) as $list) {
        $listname = $list->getListname();
        $safeListname = htmlentities($listname, ENT_COMPAT, "utf-8");
        $disabled = "disabled";
        if ($listname != $currentListname) {
            $disabled = "";
        }
        $buttonsHtml .= "    <a href='/php/userlist.php?l=$safeListname' role='button' class='btn btn-primary' $disabled>$listname</a>\n";
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
    if (empty($currentListname)) {
        $contentTypeHtml = "";
    }
    
    $html = "\n";
    $html .= "<div class='well well-sm'>\n";
    $html .= "  <h2>$currentListname</h2>\n";
    $html .= "  <div class='btn-toolbar' role='toolbar'>\n";
    $html .= "    <div class='btn-group btn-group-md' role='group'>\n";
    $html .= "      $buttonsHtml";
    $html .= "    </div>\n";
    $html .= "    <div class='btn-group btn-group-md' role='group'>\n";
    $html .= "      <a href='/php/userlist.php?nl=1' role='button' class='btn btn-primary'>New List</a>\n";
    $html .= "    </div>\n";
    $html .= "  </div>\n";
    $html .=    $contentTypeHtml;
    $html .= "</div>\n";
    
    return $html;
}

?>