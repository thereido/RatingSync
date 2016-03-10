<?php
namespace RatingSync;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "main.php";
require_once "getHtmlFilm.php";

$username = getUsername();
$searchResponse = "";
if (array_key_exists("q", $_GET)) {
    $searchQuery = $_GET['q'];
    logDebug("Search: $searchQuery", "getSearchFilm.php ".__LINE__);
    $searchFilm = search($searchQuery, $username);

    $sourceName = Constants::SOURCE_RATINGSYNC;
    if (array_key_exists("source", $_GET)) {
        $searchSource = $_GET['source'];
        if ($searchSource == "IM") {
            $sourceName = Constants::SOURCE_IMDB;
        } else if ($searchSource == "NF") {
            $sourceName = Constants::SOURCE_NETFLIX;
        } else if ($searchSource == "RT") {
            $sourceName = Constants::SOURCE_RT;
        }
    }

    $withImage = true;
    if (array_key_exists("i", $_GET)) {
        $i = $_GET['i'];
        if ($i != 1) {
            $withImage = false;
        }
    }
}

$searchResponse = "<p>No result</p>";
if (!empty($searchFilm)) {
    $uniqueName = $searchFilm->getUniqueName(Constants::SOURCE_RATINGSYNC);
    $filmHtml = getHtmlFilm($searchFilm, null, $withImage);

    $searchResponse   = "<table align='center'><tr><td>\n";
    $searchResponse  .= "  <span id='$uniqueName'>";
    $searchResponse  .=      $filmHtml;
    $searchResponse  .= "  <span>";
    $searchResponse  .= "</td></tr></table>\n";
}

echo $searchResponse;

?>