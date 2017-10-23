<?php
namespace RatingSync;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "main.php";
require_once "getHtmlFilmlists.php";

$username = getUsername();
$response = "";

$action = array_value_by_key("action", $_GET);
logDebug("API action: $action, username: $username", "api.php");
if ($action == "getSearchFilm") {
    $response = api_getSearchFilm($username, $_GET);
}
elseif ($action == "setRating") {
    $response = api_setRating($username);
}
elseif ($action == "setFilmlist") {
    $response = api_setFilmlist($username);
}
elseif ($action == "getUserLists") {
    $response = api_getUserLists($username);
}
elseif ($action == "createFilmlist") {
    $response = api_createFilmlist($username);
}
elseif ($action == "addFilmBySearch") {
    $response = api_addFilmBySearch($username, $_GET);
}
elseif ($action == "updateFilmSource") {
    $response = api_updateFilmSource($username);
}
elseif ($action == "getStream") {
    $response = api_getStream($username);
}
elseif ($action == "getFilm") {
    $response = api_getFilm($username, $_GET);
}
elseif ($action == "getUser") {
    $response = api_getUser($username);
}
elseif ($action == "getRatings") {
    $response = api_getRatings($username);
}
elseif ($action == "getFilmsByList") {
    $response = api_getFilmsByList($username);
}
elseif ($action == "searchFilms") {
    $response = api_searchFilms($username);
}
elseif ($action == "validateNewUsername") {
    $response = api_validateNewUsername();
}

echo $response;

function api_getSearchFilm($username, $get)
{
    $searchTerms = getApiSearchTerms($get);

    $searchFilm = null;
    try {
        $resultFilms = search($searchTerms, $username);
        $matchFilm = array_value_by_key('match', $resultFilms);
        $parentFilm = array_value_by_key('parent', $resultFilms);
    } catch (\Exception $e) {
        $errorMsg = "Error \RatingSync\search()" . 
                    "\nsearchTerms keys: " . implode(",", array_keys($searchTerms)) .
                    "\nsearchTerms values: " . implode(",", $searchTerms) .
                    "\nException " . $e->getCode() . " " . $e->getMessage();
        logDebug($errorMsg, __FUNCTION__." ".__LINE__);
    }
    
    $responseArr = array();
    $matchAsArray = "";
    $parentAsArray = "";
    if (!empty($matchFilm)) {
        $responseArr['match'] = $matchFilm->asArray();
    }
    if (!empty($parentFilm)) {
        $responseArr['parent'] = $parentFilm->asArray();
    }
    $responseJson = json_encode($responseArr);
    
    return $responseJson;
}

function api_setRating($username)
{
    $film = null;
    $titleNum = array_value_by_key("tn", $_GET);
    $filmId = array_value_by_key("fid", $_GET);
    $uniqueName = array_value_by_key("un", $_GET);
    $score = array_value_by_key("s", $_GET);
    logDebug("Params fid=$filmId, un=$uniqueName, s=$score, tn=$titleNum", __FUNCTION__." ".__LINE__);

    if (!empty($uniqueName) && !empty($score)) {
        logDebug("filmId: $filmId, uniqueName: $uniqueName, score: $score", __FUNCTION__." ".__LINE__);
        $film = setRating($filmId, $score);
    }

    $response = $film->json_encode();

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
    if ($remove) {
        $filmlist->removeItem($filmId, true);
    } else {
        $filmlist->addItem($filmId, true);
    }

    $film = Film::getFilmFromDb($filmId, $username);
    $response = $film->json_encode();

    return $response;
}

/**
 * return {[{"listname": "name1", "username": "username", "items":[filmId1, filmId2, ...]}], ...}
 */
function api_getUserLists($username)
{
    $lists = Filmlist::getUserListsFromDbByParent($username, true);
    return json_encode($lists);
}

function api_createFilmlist($username)
{
    $listname = array_value_by_key("l", $_GET);
    $filmId = array_value_by_key("id", $_GET);
    $checked = array_value_by_key("a", $_GET);
    $parent = array_value_by_key("parent", $_GET);
    $add = false;
    if ($checked == 1) {
        $add = true;
    }
    logDebug("Params l=$listname, id=$filmId, a=$checked, parent=$parent", __FUNCTION__." ".__LINE__);
    $filmlist = Filmlist::getListFromDb($username, $listname, $parent);
    if ($add) {
        $filmlist->addItem($filmId);
    }

    $filmlist->createToDb();
}

function api_addFilmBySearch($username, $get)
{
    $searchTerms = getApiSearchTerms($get);

    $searchFilm = null;
    try {
        $resultFilms = search($searchTerms, $username);
    } catch (\Exception $e) {
        $errorMsg = "Error \RatingSync\search()" . 
                    "\nsearchTerms keys: " . implode(",", array_keys($searchTerms)) .
                    "\nsearchTerms values: " . implode(",", $searchTerms) .
                    "\nException " . $e->getCode() . " " . $e->getMessage();
        logDebug($errorMsg, __FUNCTION__." ".__LINE__);
    }
}

function getApiSearchTerms($get)
{
    $searchQuery = array_value_by_key("q", $get);
    $searchUniqueEpisode = array_value_by_key("ue", $get);
    $searchUniqueAlt = array_value_by_key("ua", $get);
    $searchTitle = array_value_by_key("t", $get);
    $searchYear = array_value_by_key("y", $get);
    $searchParentYear = array_value_by_key("py", $get);
    $searchSeason = array_value_by_key("s", $get);
    $searchEpisodeNumber = array_value_by_key("en", $get);
    $searchEpisodeTitle = array_value_by_key("et", $get);
    $searchContentType = array_value_by_key("ct", $get);

    $sourceName = Constants::SOURCE_RATINGSYNC;
    $searchSource = array_value_by_key("source", $get);
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
    
    logDebug("Params q=$searchQuery, ue=$searchUniqueEpisode, ua=$searchUniqueAlt, t=$searchTitle, y=$searchYear, py=$searchParentYear, s=$searchSeason, en=$searchEpisodeNumber, et=$searchEpisodeTitle, ct=$searchContentType, source=$searchSource", __FUNCTION__." ".__LINE__);
    $searchTerms = array('uniqueName' => $searchQuery,
                         'uniqueEpisode' => $searchUniqueEpisode,
                         'uniqueAlt' => $searchUniqueAlt,
                         'sourceName' => $sourceName,
                         'title' => htmlspecialchars_decode($searchTitle),
                         'year' => $searchYear,
                         'parentYear' => $searchParentYear,
                         'season' => $searchSeason,
                         'episodeNumber' => $searchEpisodeNumber,
                         'episodeTitle' => htmlspecialchars_decode($searchEpisodeTitle),
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

function api_getFilm($username, $get)
{
    $filmId = array_value_by_key("id", $get);
    $imdbUniqueName = array_value_by_key("imdb", $get);
    $getFromRsDbOnly = array_value_by_key("rsonly", $get);
    logDebug("Params id=$filmId, imdb=$imdbUniqueName, rsonly=$getFromRsDbOnly", __FUNCTION__." ".__LINE__);
    
    if ($getFromRsDbOnly === "0") {
        $getFromRsDbOnly = false;
    } else {
        $getFromRsDbOnly = true;
    }
    
    $response = '{"Success":"false"}';
    $film = null;
    if (!empty($filmId)) {
        $film = Film::getFilmFromDb($filmId, $username);
    } elseif (!empty($imdbUniqueName)) {
        $film = Film::getFilmFromDbByImdb($imdbUniqueName, $username);
        if (empty($film) && !$getFromRsDbOnly) {
            $searchTerms = array();
            $searchTerms["uniqueName"] = $imdbUniqueName;
            $searchTerms["sourceName"] = "IMDb";
            $searchResponseJson = search($searchTerms, $username);
            $film = Film::getFilmFromDbByImdb($imdbUniqueName, $username);
        }
    }
    
    if (!empty($film)) {
        $response = $film->json_encode();
    }

    return $response;
}

function api_getUser($username)
{
    logDebug("Params (none)", __FUNCTION__." ".__LINE__);

    $userArr = array("username" => $username);
    $response = json_encode($userArr);

    return $response;
}

function api_getRatings($username)
{
    $pageSize = array_value_by_key("ps", $_GET);
    $beginPage = array_value_by_key("bp", $_GET);
    $sort = array_value_by_key("sort", $_GET);
    $sortDirection = array_value_by_key("direction", $_GET);
    $feature = array_value_by_key("feature", $_GET);
    $tvseries = array_value_by_key("tvseries", $_GET);
    $tvepisodes = array_value_by_key("tvepisodes", $_GET);
    $shorts = array_value_by_key("shorts", $_GET);
    $filterlists = array_value_by_key("filterlists", $_GET);
    $filtergenres = array_value_by_key("filtergenres", $_GET);
    $filtergenreany = array_value_by_key("filtergenreany", $_GET);
    logDebug("Params ps=$pageSize, bp=$beginPage, sort=$sort (ignored), sortDirection=$sortDirection, feature=$feature, tvseries=$tvseries, tvepisodes=$tvepisodes, shorts=$shorts, filterlists=$filterlists, filtergenres=$filtergenres, filtergenreany=$filtergenreany", __FUNCTION__." ".__LINE__);
    
    if (empty($pageSize)) {
        $pageSize = null;
    }
    if (empty($beginPage)) {
        $beginPage = 1;
    }

    if (strtolower($sort) == "pos") {
        $sort = Filmlist::SORT_POSITION;
    } elseif (strtolower($sort) == "mod") {
        $sort = Filmlist::SORT_ADDED;
    } elseif (!Filmlist::validSortDirection($sort)) {
        $sort = Filmlist::SORT_ADDED;
    }

    if (strtolower($sortDirection) == "desc") {
        $sortDirection = Filmlist::SORTDIR_DESC;
    } elseif (strtolower($sortDirection) == "asc") {
        $sortDirection = Filmlist::SORTDIR_ASC;
    } elseif (!Filmlist::validSortDirection($sortDirection)) {
        $sortDirection = Filmlist::SORTDIR_DESC;
    }

    $filterArr = array();
    if ($feature === "0") {
        $filterArr[Film::CONTENT_FILM] = false;
    }
    if ($tvseries === "0") {
        $filterArr[Film::CONTENT_TV_SERIES] = false;
    }
    if ($tvepisodes === "0") {
        $filterArr[Film::CONTENT_TV_EPISODE] = false;
    }
    if ($shorts === "0") {
        $filterArr[Film::CONTENT_SHORTFILM] = false;
    }

    // Filter by other lists. Return only films in this list that
    // are also in at least one of the lists being used with the filter
    $filterListsArr = null;
    if (!empty($filterlists)) {
        $filterListsArr = explode("%l", $filterlists);
    }

    // Filter by genres. Return only films in at least one of the genres
    $filterGenresArr = array();
    if (!empty($filtergenres)) {
        $filterGenresArr = explode("%g", $filtergenres);
    }
    $filterGenresMatchAny = true;
    if ($filtergenreany === "0") {
        $filterGenresMatchAny = false;
    }

    $site = new \RatingSync\RatingSyncSite($username);
    $site->setSortDirection($sortDirection);
    $site->setContentTypeFilter($filterArr);
    $site->setListFilter($filterListsArr);
    $site->setGenreFilter($filterGenresArr);
    $site->setGenreFilterMatchAny($filterGenresMatchAny);
    $films = $site->getRatings($pageSize, $beginPage);
    $totalRatings = $site->countRatings();
    
    $response = '{';
    $response .= '"totalCount":"' .$totalRatings. '"';
    $response .= ', "pageSize":"' .$pageSize. '"';
    $response .= ', "beginPage":"' .$beginPage. '"';
    $response .= ', "films":[';
    $delimeter = "";
    foreach($films as $film) {
        $response .= $delimeter . $film->json_encode(true);
        $delimeter = ",";
    }
    $response .= ']}';

    return $response;
}

function api_getFilmsByList($username)
{
    $listname = array_value_by_key("l", $_GET);
    $pageSize = array_value_by_key("ps", $_GET);
    $beginPage = array_value_by_key("bp", $_GET);
    $sort = array_value_by_key("sort", $_GET);
    $sortDirection = array_value_by_key("direction", $_GET);
    $feature = array_value_by_key("feature", $_GET);
    $tvseries = array_value_by_key("tvseries", $_GET);
    $tvepisodes = array_value_by_key("tvepisodes", $_GET);
    $shorts = array_value_by_key("shorts", $_GET);
    $filterlists = array_value_by_key("filterlists", $_GET);
    $filtergenres = array_value_by_key("filtergenres", $_GET);
    $filtergenreany = array_value_by_key("filtergenreany", $_GET);
    logDebug("Params l=$listname, ps=$pageSize, bp=$beginPage, sort=$sort, sortDirection=$sortDirection, feature=$feature, tvseries=$tvseries, tvepisodes=$tvepisodes, shorts=$shorts, filterlists=$filterlists, filtergenres=$filtergenres, filtergenreany=$filtergenreany", __FUNCTION__." ".__LINE__);
    
    if (empty($pageSize)) {
        $pageSize = null;
    }
    if (empty($beginPage)) {
        $beginPage = 1;
    }

    if (strtolower($sort) == "pos") {
        $sort = Filmlist::SORT_POSITION;
    } elseif (strtolower($sort) == "mod") {
        $sort = Filmlist::SORT_ADDED;
    } elseif (!Filmlist::validSortDirection($sort)) {
        $sort = Filmlist::SORT_POSITION;
    }

    if (strtolower($sortDirection) == "desc") {
        $sortDirection = Filmlist::SORTDIR_DESC;
    } elseif (strtolower($sortDirection) == "asc") {
        $sortDirection = Filmlist::SORTDIR_ASC;
    } elseif (!Filmlist::validSortDirection($sortDirection)) {
        $sortDirection = Filmlist::SORTDIR_DESC;
    }

    $filterArr = array();
    if ($feature === "0") {
        $filterArr[Film::CONTENT_FILM] = false;
    }
    if ($tvseries === "0") {
        $filterArr[Film::CONTENT_TV_SERIES] = false;
    }
    if ($tvepisodes === "0") {
        $filterArr[Film::CONTENT_TV_EPISODE] = false;
    }
    if ($shorts === "0") {
        $filterArr[Film::CONTENT_SHORTFILM] = false;
    }

    // Filter by other lists. Return only films in this list that
    // are also in at least one of the lists being used with the filter
    $filterListsArr = array();
    if (!empty($filterlists)) {
        $filterListsArr = explode("%l", $filterlists);
    }

    // Filter by genres. Return only films in at least one of the genres
    $filterGenresArr = array();
    if (!empty($filtergenres)) {
        $filterGenresArr = explode("%g", $filtergenres);
    }
    $filterGenresMatchAny = true;
    if ($filtergenreany === "0") {
        $filterGenresMatchAny = false;
    }
    
    $list = new Filmlist($username, $listname);
    $list->setSort($sort);
    $list->setSortDirection($sortDirection);
    $list->setContentFilter($filterArr);
    $list->setListFilter($filterListsArr);
    $list->setGenreFilter($filterGenresArr);
    $list->setGenreFilterMatchAny($filterGenresMatchAny);
    $list->initFromDb();
    $films = $list->getFilms($pageSize, $beginPage);
    $totalCount = $list->count();
    
    $response = '{';
    $response .= '"totalCount":"' .$totalCount. '"';
    $response .= ', "pageSize":"' .$pageSize. '"';
    $response .= ', "beginPage":"' .$beginPage. '"';
    $response .= ', "films":[';
    $delimeter = "";
    foreach($films as $film) {
        $response .= $delimeter . $film->json_encode(true);
        $delimeter = ",";
    }
    $response .= ']}';

    return $response;
}

function api_searchFilms($username)
{
    $searchDomain = array_value_by_key("sd", $_GET);
    $listname = array_value_by_key("list", $_GET);
    $query = array_value_by_key("q", $_GET);
    logDebug("Params sd=$searchDomain, list=$listname, q=$query", __FUNCTION__." ".__LINE__);

    $site = new RatingSyncSite($username);
    $limit = 5;
    // Search domains supported: ratings, list, both
    $films = $site->search($query, $searchDomain, $listname, $limit);
    
    $response = '{';
    $response .= '"films":[';
    $delimeter = "";
    foreach($films as $film) {
        $response .= $delimeter . $film->json_encode(true);
        $delimeter = ",";
    }
    $response .= ']}';

    return $response;
}

function api_validateNewUsername()
{
    $newUsername = array_value_by_key("u", $_GET);
    logDebug("Params u=$newUsername", __FUNCTION__." ".__LINE__);

    $isValid = "false";
    if (!RatingSyncSite::usernameExists($newUsername)) {
        $isValid = "true";
    }
    $response = '{"valid":"' . $isValid . '"}';

    return $response;
}

?>