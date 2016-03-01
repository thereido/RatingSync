<?php
namespace RatingSync;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "main.php";
require_once "getHtmlRating.php";

$username = getUsername();

function getHtmlFilm($film, $titleNum = null) {
    if (empty($film)) {
        return "";
    }

    $image = $film->getImage();
    $title = $film->getTitle();
    $year = $film->getYear();
    $imdbLabel = "IMDb users";
    $imdbScore = $film->getRating(Constants::SOURCE_IMDB)->getUserScore();
    $yourRatingDate = $film->getRating(Constants::SOURCE_RATINGSYNC)->getYourRatingDate();
    $dateStr = null;
    if (!empty($yourRatingDate)) {
        $date = date_format($yourRatingDate, 'n/j/Y');
        $dateStr = "You rated this $date";
    }
    $titleNumStr = "";
    if (!empty($titleNum)) {
        $titleNumStr = "$titleNum. ";
    }
    
    $response  = "<table>\n";
    $response .= "  <tr>\n";
    $response .= "    <td>\n";
    $response .= "      <img src='$image' />\n";
    $response .= "    </td>\n";
    $response .= "    <td>\n";
    $response .= "      <table>\n";
    $response .= "        <tr>\n";
    $response .= "          <td class='film-title'>$titleNumStr$title ($year)</td>\n";
    $response .= "        </tr>\n";
    $response .= "        <tr>\n";
    $response .= "          <td align='left'>\n";
    $response .= getHtmlRating($film, $titleNum);
    $response .= "          </td>\n";
    $response .= "        </tr>\n";
    $response .= "        <tr>\n";
    $response .= "          <td class='rating-date'>$dateStr</td>\n";
    $response .= "        </tr>\n";
    $response .= "        <tr>\n";
    $response .= "          <td>$imdbLabel: $imdbScore</td>\n";
    $response .= "        </tr>\n";
    $response .= "      </table>\n";
    $response .= "    </td>\n";
    $response .= "  </tr>\n";
    $response .= "</table>\n";
    
    return $response;
}

?>