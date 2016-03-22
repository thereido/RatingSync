<?php
namespace RatingSync;

function getHtmlRatingStars($film, $titleNum = null, $withImage = true) {
    $yourScore = $film->getYourScore(Constants::SOURCE_RATINGSYNC);
    $uniqueName = $film->getUniqueName(Constants::SOURCE_RATINGSYNC);
    $fullStars = $yourScore;
    $emptyStars = 10 - $yourScore;
    $starsHtml = "";
    $starScore = 10;

    // Double digit score needs be reversed
    $showYourScore = $yourScore;
    if ($showYourScore == 10) {
        $showYourScore = "01";
    } elseif (empty($showYourScore)) {
        $showYourScore = "-";
    }

    if (is_numeric($titleNum)) {
        $titleNum = "data-title-num='$titleNum'";
    } else {
        $titleNum = "";
    }

    if ($withImage) {
        $withImage = "data-image='1'";
    } else {
        $withImage = "data-image='0'";
    }

    while ($emptyStars > 0) {
        $starsHtml .= "<span class='rating-star' id='rate-$uniqueName-$starScore' data-uniquename='$uniqueName' data-score='$starScore' $titleNum $withImage>☆</span>";
        $emptyStars = $emptyStars - 1;
        $starScore = $starScore - 1;
    }
    while ($fullStars > 0) {
        $starsHtml .= "<span class='rating-star' id='rate-$uniqueName-$starScore' data-uniquename='$uniqueName' data-score='$starScore' $titleNum $withImage>★</span>";
        $fullStars = $fullStars - 1;
        $starScore = $starScore - 1;
    }

    $response  = "<div class='rating-stars'>\n";
    $response .= "  <score><of-possible>01/</of-possible><your-score id='your-score-$uniqueName'>$showYourScore</your-score></score>";
    $response .= "  $starsHtml\n";
    $response .= "  <div id='original-score-$uniqueName' data-score='$showYourScore' hidden />";
    $response .= "</div>\n";

    return $response;
}

?>