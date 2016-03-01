<?php
namespace RatingSync;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "main.php";

function getHtmlRating($film, $titleNum = null) {
    $yourScore = $film->getYourScore(Constants::SOURCE_RATINGSYNC);
    $uniqueName = $film->getUniqueName(Constants::SOURCE_RATINGSYNC);
    $fullStars = $yourScore;
    $emptyStars = 10 - $yourScore;
    $starsHtml = "";
    $starScore = 10;
    while ($emptyStars > 0) {
        $starsHtml .= "<span onclick='rateFilm(\"$uniqueName\", \"$starScore\", \"$titleNum\")'>☆</span>";
        $emptyStars = $emptyStars - 1;
        $starScore = $starScore - 1;
    }
    while ($fullStars > 0) {
        $starsHtml .= "<span onclick='rateFilm(\"$uniqueName\", \"$starScore\", \"$titleNum\")'>★</span>";
        $fullStars = $fullStars - 1;
        $starScore = $starScore - 1;
    }

    $response  = "<div class='rating'>\n";
    $response .= "  $starsHtml\n";
    $response .= "</div>\n";

    return $response;
}

?>

<script>
function rateFilm(uniqueName, score, titleNum) {
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            document.getElementById(uniqueName).innerHTML = xmlhttp.responseText;
        }else {
            /*RT*/// document.getElementById(uniqueName).innerHTML = xmlhttp.responseText;
        }
    }
    xmlhttp.open("GET", "/php/src/ajax/setRating.php?un=" + uniqueName + "&s=" + score + "&tn=" + titleNum, true);
    xmlhttp.send();
}
</script>