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
}

$searchResponse = "<p>No result</p>";
if (!empty($searchFilm)) {
    $uniqueName = $searchFilm->getUniqueName(Constants::SOURCE_RATINGSYNC);
    $searchResponse  .= "<table align='center'><tr><td>\n";
    $searchResponse  .= "  <span id='$uniqueName'>";
    $searchResponse  .= getHtmlFilm($searchFilm);
    $searchResponse  .= "  <span>";
    $searchResponse  .= "</td></tr></table>\n";
}

echo $searchResponse;

?>