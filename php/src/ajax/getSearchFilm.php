<?php
namespace RatingSync;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "main.php";
require_once "getHtmlFilm.php";

$username = getUsername();

$searchQuery = array_value_by_key("q", $_GET);
if ($searchQuery == "undefined") {
    $searchQuery = null;
}

$searchTitle = array_value_by_key("t", $_GET);
if ($searchTitle == "undefined") {
    $searchTitle = null;
}

$searchYear = array_value_by_key("y", $_GET);
if ($searchYear == "undefined") {
    $searchYear = null;
}

$searchContentType = array_value_by_key("ct", $_GET);
if ($searchContentType == "undefined") {
    $searchContentType = null;
}

$withImage = array_value_by_key("i", $_GET);
if ($withImage == 0) {
    $withImage = false;
} else {
    $withImage = true;
}

$sourceName = Constants::SOURCE_RATINGSYNC;
$searchSource = array_value_by_key("source", $_GET);
if ($searchSource == "IM") {
    $sourceName = Constants::SOURCE_IMDB;
} else if ($searchSource == "NF") {
    $sourceName = Constants::SOURCE_NETFLIX;
} else if ($searchSource == "RT") {
    $sourceName = Constants::SOURCE_RT;
} else if ($searchSource == "XF") {
    $sourceName = Constants::SOURCE_XFINITY;
}
    
logDebug("Search: $sourceName $searchQuery $searchTitle $searchYear", "getSearchFilm.php ".__LINE__);
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