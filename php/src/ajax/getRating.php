<?php
namespace RatingSync;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "main.php";

function getRatingHtml($rating) {
    $yourScore = $rating->getYourScore();
    $fullStars = $yourScore;
    $emptyStars = 10 - $yourScore;
    $starsHtml = "";
    while ($emptyStars > 0) {
        $starsHtml .= "<span>☆</span>";
        $emptyStars = $emptyStars - 1;
    }
    while ($fullStars > 0) {
        $starsHtml .= "<span>★</span>";
        $fullStars = $fullStars - 1;
    }

    $response  = "<div class='rating'>\n";
    $response .= "  $starsHtml\n";
    $response .= "</div>\n";

    return $response;
}

?>