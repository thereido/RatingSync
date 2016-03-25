<?php
namespace RatingSync;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "main.php";
require_once "getHtmlFilm.php";
require_once "getHtmlFilmlists.php";

$username = getUsername();
$response = "";

$action = array_value_by_key("action", $_GET);
if ($action == "getSearchFilm") {
    $response = api_getSearchFilm($username);
} elseif ($action == "setRating") {
    $response = api_setRating($username);
} elseif ($action == "setFilmlist") {
    $response = api_setFilmlist($username);
}

echo $response;

function api_getSearchFilm($username)
{
    $searchQuery = array_value_by_key("q", $_GET);
    $searchTitle = array_value_by_key("t", $_GET);
    $searchYear = array_value_by_key("y", $_GET);
    $searchContentType = array_value_by_key("ct", $_GET);
    $asJson = array_value_by_key("json", $_GET);
    if ($asJson == 1) {
        $asJson = true;
    } else {
        $asJson = false;
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
    } else if ($searchSource == "H") {
        $sourceName = Constants::SOURCE_HULU;
    }
    
    logDebug("Search: $sourceName $searchQuery $searchTitle $searchYear", __FUNCTION__." ".__LINE__);
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

    $response = "<p>No result</p>";
    if ($asJson) {
        $response = "";
    }
    if (!empty($searchFilm)) {
        if ($asJson) {
            $response = $searchFilm->json_encode();
        } else {
            $uniqueName = $searchFilm->getUniqueName(Constants::SOURCE_RATINGSYNC);
            $filmHtml = getHtmlFilm($searchFilm);

            $response   = "<table align='center'><tr><td>\n";
            $response  .= "  <span id='$uniqueName'>";
            $response  .=      $filmHtml;
            $response  .= "  <span>";
            $response  .= "</td></tr></table>\n";
        }
    }

    return $response;
}

function api_setRating($username)
{
    $film = null;
    $titleNum = array_value_by_key("tn", $_GET);
    $uniqueName = array_value_by_key("un", $_GET);
    $score = array_value_by_key("s", $_GET);
    $asJson = false;
    if (array_value_by_key("json", $_GET) == 1) {
        $asJson = true;
    }

    if (!empty($uniqueName) && !empty($score)) {
        logDebug("uniqueName: $uniqueName, score: $score", __FUNCTION__." ".__LINE__);
        $film = setRating($uniqueName, $score);
    }

    if ($asJson) {
        $response = $film->json_encode();
    } else {
        if (is_null($titleNum)) {
            $titleNum = "";
        }

        $response = "";
        if (!empty($film)) {
            $response  .= getHtmlFilm($film, $titleNum);
        }
    }

    return $response;
}

function api_setFilmlist($username)
{
    $listname = array_value_by_key("l", $_GET);
    $filmId = array_value_by_key("id", $_GET);
    $checked = array_value_by_key("c", $_GET);
    $remove = false;
    if ($checked == 0) {
        $remove = true;
    }
    $filmlist = Filmlist::getListFromDb($username, $listname);
    $filmlist->setFilmlist($filmId, $remove);
    $filmlist->saveToDb();
}

?>