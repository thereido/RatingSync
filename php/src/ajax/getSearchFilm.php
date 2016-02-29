<?php
namespace RatingSync;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "main.php";
require_once "getRating.php";

$username = getUsername();
$searchResponse = "";
if (array_key_exists("q", $_GET)) {
    $searchQuery = $_GET['q'];
    logDebug("Search: $searchQuery", "getSearchFilm.php ".__LINE__);
    $searchFilm = search($searchQuery, $username);
    if (!empty($searchFilm)) {
        $searchImage = $searchFilm->getImage();
        $searchTitle = $searchFilm->getTitle();
        $searchYear = $searchFilm->getYear();
        $searchRsRating = $searchFilm->getRating(Constants::SOURCE_RATINGSYNC);
        $searchRsScore = $searchRsRating->getYourScore();
        $searchImdbLabel = "IMDb users";
        $searchImdbScore = $searchFilm->getRating(Constants::SOURCE_IMDB)->getUserScore();
        $searchImdbYourScore = $searchFilm->getRating(Constants::SOURCE_IMDB)->getYourScore();
        if (!empty($searchImdbYourScore)) {
            $searchImdbLabel = "IMDb you";
            $searchImdbScore = $searchImdbYourScore;
        }
    }
}

if (!empty($searchFilm)) {
    $searchResponse  = "<table align='center'>\n";
    $searchResponse .= "  <tr>\n";
    $searchResponse .= "    <td>\n";
    $searchResponse .= "      <img src='$searchImage' />\n";
    $searchResponse .= "    </td>\n";
    $searchResponse .= "    <td>\n";
    $searchResponse .= "      <table>\n";
    $searchResponse .= "        <tr>\n";
    $searchResponse .= "          <td>$searchTitle ($searchYear)</td><td/>\n";
    $searchResponse .= "        </tr>\n";
    $searchResponse .= "        <tr>\n";
    $searchResponse .= "          <td colspan='2' align='left'>\n";
    $searchResponse .= getRatingHtml($searchRsRating);
    $searchResponse .= "          </td>\n";
    $searchResponse .= "        </tr>\n";
    $searchResponse .= "        <tr>\n";
    $searchResponse .= "          <td>$searchImdbLabel: </td>\n";
    $searchResponse .= "          <td>$searchImdbScore</td>\n";
    $searchResponse .= "        </tr>\n";
    $searchResponse .= "      </table>\n";
    $searchResponse .= "    </td>\n";
    $searchResponse .= "  </tr>\n";
    $searchResponse .= "</table>\n";
} elseif (!empty($searchQuery)) {
    $searchResponse .= "<p>No result</p>";
}

echo $searchResponse;

?>