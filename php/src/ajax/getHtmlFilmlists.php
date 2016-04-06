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

    $response .= "$delimiter<span><a href='/php/userlist.php?nl=1'>New List</a></span>";
    
    return $response;
}

function getHtmlFilmlistsByFilm($film) {
    if (empty($film)) {
        return "";
    }
    
    $username = getUsername();
    $filmId = $film->getId();
    $classCheckmarkOn = "glyphicon glyphicon-check checkmark-on";
    $classCheckmarkOff = "glyphicon glyphicon-check checkmark-off";
    $defaultList = Constants::LIST_DEFAULT;
    $defaultListHtmlSafe = htmlentities($defaultList, ENT_COMPAT, "utf-8");
    $defaultListClass = $classCheckmarkOff;
    $response = "";
    $listItemsHtml = "";

    foreach (Filmlist::getUserListsFromDb($username) as $list) {
        $listname = $list->getListname();
        if ($list->getListname() == $defaultList) {
            if ($list->inList($filmId)) {
                $defaultListClass = $classCheckmarkOn;
            }
        } else {
            $listnameHtmlSafe = htmlentities($listname, ENT_COMPAT, "utf-8");
            $viewListUrl = "/php/userlist.php?l=$listnameHtmlSafe";
            $checkmarkClass = $classCheckmarkOff;
            if ($list->inList($filmId)) {
                $checkmarkClass = $classCheckmarkOn;
            }
            $listItemsHtml .= "        <li class='filmlist' id='filmlist-$listnameHtmlSafe-$filmId'>\n";
            $listItemsHtml .= "            <a href='#' onClick='toggleFilmlist(\"$listnameHtmlSafe\", $filmId, \"filmlist-btn-$listnameHtmlSafe-$filmId\")' id='filmlist-btn-$listnameHtmlSafe-$filmId'><span class='$checkmarkClass' id='filmlist-checkmark-$filmId'></span> $listname</a>\n";
            $listItemsHtml .= "        </li>\n";
        }
    }

    $listItemsHtml .= "        <li class='divider'></li>\n";
    $listItemsHtml .= "        <li><a href='/php/userlist.php?id=$filmId'>New list</a></li>\n";

    $response .= "<div class='btn-group-vertical film-filmlists'>\n";
    $response .= "    <button class='btn btn-sm btn-primary' onClick='toggleFilmlist(\"$defaultListHtmlSafe\", $filmId, \"filmlist-btn-default-$filmId\")' id='filmlist-btn-default-$filmId' data-listname='$defaultList' type='button'><span class='$defaultListClass' id='filmlist-checkmark-$filmId'></span> $defaultList</button>\n";
    $response .= "    <div class='btn-group'>\n";
    $response .= "      <button class='btn btn-sm btn-primary dropdown-toggle' id='filmlist-btn-others-$filmId' data-toggle='dropdown' type='button'>More lists <span class='caret'></span></button>\n";
    $response .= "      <ul class='dropdown-menu' id='filmlists-$filmId' role='menu'  >\n";
    $response .=          $listItemsHtml;
    $response .= "      </ul>\n";
    $response .= "    </div>\n";
    $response .= "</div>\n";
    
    return $response;
}

?>