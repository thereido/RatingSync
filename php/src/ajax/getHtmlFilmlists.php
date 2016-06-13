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

?>