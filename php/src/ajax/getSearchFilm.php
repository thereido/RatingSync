<?php
namespace RatingSync;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "main.php";
require_once "getHtmlFilm.php";

$username = getUsername();

$searchQuery = "";
if (array_key_exists("q", $_GET) && $_GET['q'] != "undefined") {
    $searchQuery = $_GET['q'];
}

$searchTitle = "";
if (array_key_exists("t", $_GET) && $_GET['t'] != "undefined") {
    $searchTitle = $_GET['t'];
}

$searchYear = "";
if (array_key_exists("y", $_GET) && $_GET['y'] != "undefined") {
    $searchYear = $_GET['y'];
}

$searchContentType = "";
if (array_key_exists("ct", $_GET) && $_GET['ct'] != "undefined") {
    $searchContentType = $_GET['ct'];
}

$withImage = true;
if (array_key_exists("i", $_GET) && $_GET['i'] != "undefined") {
    $i = $_GET['i'];
    if ($i != 1) {
        $withImage = false;
    }
}

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
    
logDebug("Search: $searchQuery", "getSearchFilm.php ".__LINE__);
$searchTerms = array('uniqueName' => $searchQuery,
                     'sourceName' => $sourceName,
                     'title' => $searchTitle,
                     'year' => $searchYear,
                     'contentType' => $searchContentType);
$searchFilm = null;
try {
    $searchFilm = search($searchTerms, $username);
} catch (\Exception $e) {
    $errorMsg = "Error \RatingSync\search()" . 
                "\nsearchTerms keys: " . implode(",", array_keys($searchTerms)) .
                "\nsearchTerms values: " . implode(",", $searchTerms) .
                "\nException " . $e->getCode() . " " . $e->getMessage();
    logDebug($errorMsg, "getSearchFilm.php ".__LINE__);
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