<?php
namespace RatingSync;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "main.php";
require_once "getHtmlFilm.php";

$film = null;
$titleNum = null;
if (array_key_exists("un", $_GET) && array_key_exists("s", $_GET)) {
    $uniqueName = $_GET['un'];
    $score = $_GET['s'];
    logDebug("uniqueName: $uniqueName, score: $score", "setRating.php ".__LINE__);
    $film = setRating($uniqueName, $score);
}
if (array_key_exists("tn", $_GET)) {
    $titleNum = $_GET['tn'];
}

$response = "";
if (!empty($film)) {
    $response  .= getHtmlFilm($film, $titleNum);
}

echo $response;

?>