<?php
namespace RatingSync;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "main.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "Constants.php";

function getHtmlFilmlistsHeader($currentListname = null) {
    $username = getUsername();
    $response = "";
    $delimiter = "";

    $link = "Your Ratings";
    if ($link != $currentListname) {
        $link = "<a href='/php/ratings.php'>$link</a>";
    }
    $response .= "<span class='filmlist' id='list-header-ratings'>$link</span>";
    $delimiter = " | ";

    foreach (Filmlist::getUserListsFromDb($username) as $list) {
        $listname = $list->getListname();
        $safeListname = htmlentities($listname, ENT_COMPAT, "utf-8");
        $link = $listname;
        if ($listname != $currentListname) {
            $link = "<a href='/php/userlist.php?l=$safeListname'>$listname</a>";
        }

        $response .= "$delimiter<span class='filmlist-header' id='list-$safeListname'>$link</span>";
        $delimiter = " | ";
    }
    
    return $response;
}

function getHtmlFilmlistsByFilm($film) {
    if (empty($film)) {
        return "";
    }
    
    $username = getUsername();
    $filmId = $film->getId();
    $defaultList = Constants::LIST_DEFAULT;
    $defaultListHtmlSafe = htmlentities($defaultList, ENT_COMPAT, "utf-8");
    $defaultListClass = "checkmark-off";
    $response = "";
    $listItemsHtml = "";

    foreach (Filmlist::getUserListsFromDb($username) as $list) {
        $listname = $list->getListname();
        if ($list->getListname() == $defaultList) {
            if ($list->inList($filmId)) {
                $defaultListClass = "checkmark-on";
            }
        } else {
            $listnameHtmlSafe = htmlentities($listname, ENT_COMPAT, "utf-8");
            $viewListUrl = "/php/userlist.php?l=$listnameHtmlSafe";
            $checkmarkClass = "checkmark-off";
            if ($list->inList($filmId)) {
                $checkmarkClass = "checkmark-on";
            }

            $listItemsHtml .= "        <div class='filmlist' id='filmlist-$listnameHtmlSafe-$filmId'>\n";
            $listItemsHtml .= "            <span><button class='btn btn-sm btn-secondary' onClick='toggleFilmlist(\"$listnameHtmlSafe\", $filmId, \"filmlist-btn-$listnameHtmlSafe-$filmId\")' id='filmlist-btn-$listnameHtmlSafe-$filmId' type='button'><span class='$checkmarkClass' id='filmlist-checkmark-$filmId'>&#10003;</span> $listname</button></span>\n";
            $listItemsHtml .= "            <span><a href='$viewListUrl'>»</a><span>\n";
            $listItemsHtml .= "        </div>\n";
        }
    }

    $response .= "<div>\n";
    $response .= "    <button class='btn btn-sm btn-primary' onClick='toggleFilmlist(\"$defaultListHtmlSafe\", $filmId, \"filmlist-btn-default-$filmId\")' id='filmlist-btn-default-$filmId' data-listname='$defaultList' type='button'><span class='$defaultListClass' id='filmlist-checkmark-$filmId'>&#10003;</span> $defaultList</button>\n";
    $response .= "    <button class='btn btn-sm btn-primary' onClick='toggleHideFilmlists(\"filmlists-$filmId\")' id='filmlist-btn-others-$filmId' type='button'>»</button>\n";
    $response .= "    <div class='film-filmlists' id='filmlists-$filmId' hidden >\n";
    $response .=          $listItemsHtml;
    $response .= "    </div>\n";
    $response .= "</div>\n";
    
    return $response;
}

?>