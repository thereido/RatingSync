<?php
namespace RatingSync;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "main.php";
require_once "getHtmlFilm.php";
require_once "getHtmlFilmlists.php";

$username = getUsername();
$response = "";

$action = array_value_by_key("action", $_GET);
logDebug("API action: $action", "api.php");
if ($action == "getSearchFilm") {
    $response = api_getSearchFilm($username);
} elseif ($action == "getSearchFilms") {
    $response = api_getSearchFilms($username);
} elseif ($action == "setRating") {
    $response = api_setRating($username);
} elseif ($action == "setFilmlist") {
    $response = api_setFilmlist($username);
} elseif ($action == "getUserLists") {
    $response = api_getUserLists($username);
} elseif ($action == "createFilmlist") {
    $response = api_createFilmlist($username);
} elseif ($action == "addFilmBySearch") {
    $response = api_addFilmBySearch($username);
} elseif ($action == "updateFilmSource") {
    $response = api_updateFilmSource($username);
} elseif ($action == "getStream") {
    $response = api_getStream($username);
}

echo $response;

function api_getSearchFilm($username)
{
    $searchTerms = getApiSearchTerms();
    $asJson = array_value_by_key("json", $_GET);
    if ($asJson == 1) {
        $asJson = true;
    } else {
        $asJson = false;
    }
    logDebug("extra Params json=$asJson", __FUNCTION__." ".__LINE__);

    $searchFilm = null;
    try {
        $searchFilm = search($searchTerms, $username);
    } catch (\Exception $e) {
        $errorMsg = "Error \RatingSync\search()" . 
                    "\nsearchTerms keys: " . implode(",", array_keys($searchTerms)) .
                    "\nsearchTerms values: " . implode(",", $searchTerms) .
                    "\nException " . $e->getCode() . " " . $e->getMessage();
        logDebug($errorMsg, __FUNCTION__." ".__LINE__);
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

function api_getSearchFilms($username)
{
    $response = "";
    $films = array();
    $searchQuery = array_value_by_key("q", $_GET);
    logDebug("Params q=$searchQuery", __FUNCTION__." ".__LINE__);

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
    logDebug("Params un=$uniqueName, s=$score, tn=$titleNum, json=$asJson", __FUNCTION__." ".__LINE__);

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
    logDebug("Params l=$listname, id=$filmId, c=$checked", __FUNCTION__." ".__LINE__);
    $filmlist = Filmlist::getListFromDb($username, $listname);
    $filmlist->setFilmlist($filmId, $remove);
    $filmlist->saveToDb();
}

/**
 * return {[{"listname": "name1", "username": "username", "items":[filmId1, filmId2, ...]}], ...}
 */
function api_getUserLists($username)
{
    $json = Filmlist::getUserListsFromDb($username, true);
    return $json;
}

function api_createFilmlist($username)
{
    $listname = array_value_by_key("l", $_GET);
    $filmId = array_value_by_key("id", $_GET);
    $checked = array_value_by_key("a", $_GET);
    $add = false;
    if ($checked == 1) {
        $add = true;
    }
    logDebug("Params l=$listname, id=$filmId, a=$checked", __FUNCTION__." ".__LINE__);
    $filmlist = Filmlist::getListFromDb($username, $listname);
    if ($add) {
        $filmlist->setFilmlist($filmId);
    }
    $filmlist->saveToDb();
}

function api_addFilmBySearch($username)
{
    $searchTerms = getApiSearchTerms();

    $searchFilm = null;
    try {
        $searchFilm = search($searchTerms, $username);
    } catch (\Exception $e) {
        $errorMsg = "Error \RatingSync\search()" . 
                    "\nsearchTerms keys: " . implode(",", array_keys($searchTerms)) .
                    "\nsearchTerms values: " . implode(",", $searchTerms) .
                    "\nException " . $e->getCode() . " " . $e->getMessage();
        logDebug($errorMsg, __FUNCTION__." ".__LINE__);
    }
}

function getApiSearchTerms()
{
    $searchQuery = array_value_by_key("q", $_GET);
    $searchUniqueEpisode = array_value_by_key("ue", $_GET);
    $searchUniqueAlt = array_value_by_key("ua", $_GET);
    $searchTitle = array_value_by_key("t", $_GET);
    $searchYear = array_value_by_key("y", $_GET);
    $searchContentType = array_value_by_key("ct", $_GET);

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
    
    logDebug("Params q=$searchQuery, ue=$searchUniqueEpisode, ua=$searchUniqueAlt, t=$searchTitle, y=$searchYear, ct=$searchContentType, source=$searchSource", __FUNCTION__." ".__LINE__);
    $searchTerms = array('uniqueName' => $searchQuery,
                         'uniqueEpisode' => $searchUniqueEpisode,
                         'uniqueAlt' => $searchUniqueAlt,
                         'sourceName' => $sourceName,
                         'title' => htmlspecialchars_decode($searchTitle),
                         'year' => $searchYear,
                         'contentType' => $searchContentType);

    return $searchTerms;
}

function api_updateFilmSource($username)
{
    $filmId = array_value_by_key("filmid", $_GET);
    $sourceName = array_value_by_key("source", $_GET);
    $streamUrl = array_value_by_key("su", $_GET);
    $uniqueName = array_value_by_key("un", $_GET);
    $uniqueEpisode = array_value_by_key("ue", $_GET);
    $uniqueAlt = array_value_by_key("ua", $_GET);
    logDebug("Params filmid=$filmId, source=$sourceName, su=$streamUrl, un=$uniqueName, ue=$uniqueEpisode, ua=$uniqueAlt", __FUNCTION__." ".__LINE__);

    if ($streamUrl == "none") {
        $streamUrl = null;
    }
    
    $source = new Source($sourceName, $filmId);
    $source->setUniqueName($uniqueName);
    $source->setUniqueEpisode($uniqueEpisode);
    $source->setUniqueAlt($uniqueAlt);
    $source->setStreamUrl($streamUrl);
    $source->refreshStreamDate();
    $source->saveFilmSourceToDb($filmId);
}

function api_getStream($username)
{
    $filmId = array_value_by_key("id", $_GET);
    $sourceName = array_value_by_key("source", $_GET);
    logDebug("Params filmid=$filmId, source=$sourceName", __FUNCTION__." ".__LINE__);
    
    $film = Film::getFilmFromDb($filmId);
    $source = $film->getSource($sourceName);
    $source->createSourceToDb($film);
    $streamUrl = $source->getStreamUrl();
    
    $response = "NONE";
    if (!empty($streamUrl)) {
        $response = $streamUrl;
    }

    return $response;
}

?>