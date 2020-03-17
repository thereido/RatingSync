<?php
namespace RatingSync;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "main.php";
require_once "getHtmlFilmlists.php";

$username = getUsername();
$response = "";

$action = array_value_by_key("action", $_GET);
logDebug("API action: $action, username: $username", "api.php ".__LINE__);
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
elseif ($action == "getFilms") {
    $response = api_getFilms($username, $_GET);
}
elseif ($action == "validateNewUsername") {
    $response = api_validateNewUsername();
}
elseif ($action == "getSeason") {
    $response = api_getSeason($username);
}
elseif ($action == "deleteFilmlist") {
    $response = api_deleteFilmlist($username);
}
elseif ($action == "renameFilmlist") {
    $response = api_renameFilmlist($username);
}

if (empty($response)) {
    $response = "{}";
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

    if (!empty($username) && !empty($filmId) && (!empty($score) || $score == 0)) {
        $film = setRating($filmId, $score);
    }

    if (empty($film)) {
        $response = '{"Success":"false"}';
    } else {
        $response = $film->json_encode();
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
    $parentId = array_value_by_key("pid", $get);
    $imdbId = array_value_by_key("imdb", $get);
    $uniqueName = array_value_by_key("un", $get);
    $contentType = array_value_by_key("ct", $get);
    $seasonNum = array_value_by_key("s", $get);
    $episodeNum = array_value_by_key("e", $get);
    $getFromRsDbOnly = array_value_by_key("rsonly", $get);
    logDebug("Params id=$filmId, pid=$parentId, imdbId=$imdbId, un=$uniqueName, ct=$contentType, s=$seasonNum, e=$episodeNum, rsonly=$getFromRsDbOnly", __FUNCTION__." ".__LINE__);
    
    if ($getFromRsDbOnly === "0") {
        $getFromRsDbOnly = false;
    } else {
        $getFromRsDbOnly = true;
    }
    
    $response = '{"Success":"false"}';
    $film = getFilmApi($username, $filmId, $imdbId, $uniqueName, $getFromRsDbOnly, $contentType, $seasonNum, $episodeNum, $parentId);
    
    if (!empty($film)) {
        $response = $film->json_encode();
    }

    return $response;
}

function getFilmApi($username, $filmId, $imdbId, $uniqueName, $getFromRsDbOnly, $contentType = null, $seasonNum = null, $episodeNum = null, $parentId = null)
{
    $film = null;
    $api = getMediaDbApiClient(Constants::DATA_API_DEFAULT);

    if (!empty($filmId)) {

        $film = Film::getFilmFromDb($filmId, $username);

    }
    else {

        $sourceName = $api->getSourceName();
        if (empty($uniqueName) && !empty($imdbId)) {
            $uniqueName = $imdbId;
            $sourceName = Constants::SOURCE_IMDB;
        }

        if (!empty($uniqueName)) {
            $film = $api->getFilmFromDb($uniqueName, $contentType, $username);
        }

        if (empty($film) && !empty($parentId) && !empty($seasonNum) && !empty($episodeNum)) {
            $film = $api->getEpisodeFromDb($parentId, $seasonNum, $episodeNum, $username);
        }

        if (empty($film) && !$getFromRsDbOnly) {
            $searchTerms = array();
            $searchTerms["imdbId"] = $imdbId;
            $searchTerms["uniqueName"] = $uniqueName;
            $searchTerms["sourceName"] = $sourceName;
            $searchTerms["contentType"] = $contentType;
            $searchTerms["season"] = $seasonNum;
            $searchTerms["episodeNumber"] = $episodeNum;
            $searchTerms["parentId"] = $parentId;
            
            // A search adds the film to the DB. Get the new film from the DB.
            $searchResponseJson = search($searchTerms, $username);
            $film = $api->getFilmFromDb($uniqueName, $contentType, $username);
        }
    }

    // Make sure the default API has a source and refresh the film
    // if it the data is stale
    if (!empty($film)) {
        // Source
        $sourceAdded = false;
        $uniqueName = $film->getUniqueName($api->getSourceName());
        if (empty($uniqueName)) {
            $api->getFilmDetailFromApi($film, true, 60);
            $uniqueName = $film->getUniqueName($api->getSourceName());
            if (!empty($uniqueName)) {
                $sourceAdded = true;
            }
        }

        // Refresh
        $refreshed = $film->refresh();

        // Save to the DB if any changes for made
        if ($refreshed || $sourceAdded) {
            $film->saveToDb($username);
        }
    }

    return $film;
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
    $filterlists = array_value_by_key("filterlists", $_GET);
    $filtergenres = array_value_by_key("filtergenres", $_GET);
    $filtergenreany = array_value_by_key("filtergenreany", $_GET);
    $filtercontenttypes = array_value_by_key("filtercontenttypes", $_GET);
    logDebug("Params ps=$pageSize, bp=$beginPage, sort=$sort (ignored), sortDirection=$sortDirection, filterlists=$filterlists, filtergenres=$filtergenres, filtergenreany=$filtergenreany, filtercontenttypes=$filtercontenttypes", __FUNCTION__." ".__LINE__);
    
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
    
    // Filter by contentType. If the param is empty return all types.
    // If the param non-empty return only types in the list.
    $filterContentTypesArr = array();
    if (!empty($filtercontenttypes)) {
        $filterContentTypesParam = explode("%c", $filtercontenttypes);
        if (!in_array(Film::CONTENT_FILM, $filterContentTypesParam)) {
            $filterContentTypesArr[Film::CONTENT_FILM] = false;
        }
        if (!in_array(Film::CONTENT_TV_SERIES, $filterContentTypesParam)) {
            $filterContentTypesArr[Film::CONTENT_TV_SERIES] = false;
        }
        if (!in_array(Film::CONTENT_TV_EPISODE, $filterContentTypesParam)) {
            $filterContentTypesArr[Film::CONTENT_TV_EPISODE] = false;
        }
        if (!in_array(Film::CONTENT_SHORTFILM, $filterContentTypesParam)) {
            $filterContentTypesArr[Film::CONTENT_SHORTFILM] = false;
        }
    }

    $site = new \RatingSync\RatingSyncSite($username);
    $site->setSortDirection($sortDirection);
    $site->setListFilter($filterListsArr);
    $site->setGenreFilter($filterGenresArr);
    $site->setGenreFilterMatchAny($filterGenresMatchAny);
    $site->setContentTypeFilter($filterContentTypesArr);
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
    $filterlists = array_value_by_key("filterlists", $_GET);
    $filtergenres = array_value_by_key("filtergenres", $_GET);
    $filtergenreany = array_value_by_key("filtergenreany", $_GET);
    $filtercontenttypes = array_value_by_key("filtercontenttypes", $_GET);
    logDebug("Params l=$listname, ps=$pageSize, bp=$beginPage, sort=$sort, sortDirection=$sortDirection, filterlists=$filterlists, filtergenres=$filtergenres, filtergenreany=$filtergenreany, filtercontenttypes=$filtercontenttypes", __FUNCTION__." ".__LINE__);
    
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
    
    // Filter by contentType. If the param is empty return all types.
    // If the param non-empty return only types in the list.
    $filterContentTypesArr = array();
    if (!empty($filtercontenttypes)) {
        $filterContentTypesParam = explode("%c", $filtercontenttypes);
        if (!in_array(Film::CONTENT_FILM, $filterContentTypesParam)) {
            $filterContentTypesArr[Film::CONTENT_FILM] = false;
        }
        if (!in_array(Film::CONTENT_TV_SERIES, $filterContentTypesParam)) {
            $filterContentTypesArr[Film::CONTENT_TV_SERIES] = false;
        }
        if (!in_array(Film::CONTENT_TV_EPISODE, $filterContentTypesParam)) {
            $filterContentTypesArr[Film::CONTENT_TV_EPISODE] = false;
        }
        if (!in_array(Film::CONTENT_SHORTFILM, $filterContentTypesParam)) {
            $filterContentTypesArr[Film::CONTENT_SHORTFILM] = false;
        }
    }
    
    $list = new Filmlist($username, $listname);
    $list->setSort($sort);
    $list->setSortDirection($sortDirection);
    $list->setListFilter($filterListsArr);
    $list->setGenreFilter($filterGenresArr);
    $list->setGenreFilterMatchAny($filterGenresMatchAny);
    $list->setContentFilter($filterContentTypesArr);
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

function api_getFilms($username, $params)
{
    $filmIdsParam = array_value_by_key("id", $params);
    $sourceIdContentTypesParam = array_value_by_key("sidcts", $params);
    $uniqueNameContentTypesParam = array_value_by_key("uncts", $params);
    $imdbIdContentTypesParam = array_value_by_key("imdbcts", $params);
    $seriesFilmId = array_value_by_key("pid", $params);
    $seasonNum = array_value_by_key("s", $params);
    $episodeParam = array_value_by_key("e", $params);
    logDebug("Params id=$filmIdsParam, sidcts=$sourceIdContentTypesParam, uncts=$uniqueNameContentTypesParam, imdbcts=$imdbIdContentTypesParam, pid=$seriesFilmId, s=$seasonNum, e=$episodeParam", __FUNCTION__." ".__LINE__);

    $idContentTypeParam = "";
    $idType = "Source IDs"; // 'IMDb IDs', 'Source IDs', or 'uniqueNames'
    if (!empty($sourceIdContentTypesParam)) {
        $idContentTypeParam = $sourceIdContentTypesParam;
        $idType = "Source IDs";
    } elseif (!empty($uniqueNameContentTypesParam)) {
        $idContentTypeParam = $uniqueNameContentTypesParam;
        $idType = "uniqueNames";
    } elseif (!empty($imdbIdContentTypesParam)) {
        $idContentTypeParam = $imdbIdContentTypesParam;
        $idType = "IMDb IDs";
    }

    $filmIds = array();
    if (!empty($filmIdsParam)) {
        $filmIds = explode(" ", $filmIdsParam);
    }
    $idContentTypeCombos = array();
    if (!empty($idContentTypeParam)) {
        $idContentTypeCombos = explode(" ", $idContentTypeParam);
    }

    $episodeNums = array();
    if (!empty($episodeParam) && !empty($seasonNum)) {
        $episodeNums = explode(" ", $episodeParam);
    }

    $getFromRsDbOnly = true;

    $api = getMediaDbApiClient(Constants::DATA_API_DEFAULT);
    $films = array();
    foreach ($filmIds as $filmId) {
        $film = null;
        try {
            $film = getFilmApi($username, $filmId, null, null, $getFromRsDbOnly);
        } catch (\Exception $e) {
            $errorMsg = "Error api.php::getFilmApi(\$username, $filmId, null, $getFromRsDbOnly) Called from api_getFilms()" . 
                        "\nException (" . $e->getCode() . ") " . $e->getMessage();
            logDebug($errorMsg, __FUNCTION__." ".__LINE__);
        }
        if (! empty($film)) {
            $films[] = $film;
        }
    }
    foreach ($idContentTypeCombos as $idAndContentType) {

        // idAndContentType delimiter is "_", id_contentType
        $sourceId = null;
        $contentType = null;
        $pieces = explode("_", $idAndContentType);
        if (count($pieces) > 1) {
            $sourceId = $pieces[0];
            if (count($pieces) > 1) {
                $contentType = $pieces[1];
            }
        }

        // Use sourceId as either imdbId or uniqueName
        $uniqueName = null;
        $imdbId = null;
        if ($idType == "IMDb IDs") {
            $imdbId = $sourceId;
        } elseif ($idType == "uniqueNames") {
            $uniqueName = $sourceId;
        } elseif ($idType == "Source IDs") {
            $uniqueName = $api->getUniqueNameFromSourceId($sourceId, $contentType);
        }

        // Call getFilmApi()
        $film = null;
        try {
            $film = getFilmApi($username, null, $imdbId, $uniqueName, $getFromRsDbOnly, $contentType);
        } catch (\Exception $e) {
            $errorMsg = "Error api.php::getFilmApi($username, null, $imdbId, $uniqueName, $getFromRsDbOnly, $contentType) Called from api_getFilms()" . 
                        "\nException (" . $e->getCode() . ") " . $e->getMessage();
            logDebug($errorMsg, __FUNCTION__." ".__LINE__);
        }

        // Add each film to the films array
        if (! empty($film)) {
            $films[] = $film;
        }
    }
    foreach ($episodeNums as $episodeNum) {
        $film = null;
        try {
            $film = $api->getEpisodeFromDb($seriesFilmId, $seasonNum, $episodeNum, $username);
        } catch (\Exception $e) {
            $errorMsg = "Error getEpisodeFromDb($seriesFilmId, $seasonNum, $episodeNum, \$username) Called from api_getFilms()" . 
                        "\nException (" . $e->getCode() . ") " . $e->getMessage();
            logDebug($errorMsg, __FUNCTION__." ".__LINE__);
        }
        if (!empty($film)) {
            $films[] = $film;
        }
    }
    
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

function api_getSeason($username)
{
    $filmId = array_value_by_key("id", $_GET);
    $seasonNum = array_value_by_key("s", $_GET);
    logDebug("Params id=$filmId, s=$seasonNum", __FUNCTION__." ".__LINE__);

    $dataApi = getMediaDbApiClient();
    $season = null;
    try {
        $season = $dataApi->getSeasonFromApi($filmId, $seasonNum);
    }
    catch (\Exception $e) {
        $errorMsg = "Error getSeasonFromApi(filmId=$filmId, seasonNum=$seasonNum)" . 
                    "\nException " . $e->getCode() . " " . $e->getMessage() .
                    "\n$e";
        logDebug($errorMsg, __FUNCTION__." ".__LINE__);
    }
    
    $response = json_encode(array("Response" => "False"));
    if (!empty($season)) {
        $response = $season->json_encode(true);
    }

    return $response;
}

function api_deleteFilmlist($username)
{
    $listname = array_value_by_key("l", $_GET);
    logDebug("Params l=$listname", __FUNCTION__." ".__LINE__);

    $result = '{ "Success": "false" }';
    if (!empty($username) && !empty($listname)) {
        $result = Filmlist::removeListFromDb($username, $listname, true);
        $success = $result["Success"];
        $deletedLists = $result["DeletedLists"];
    }

    return json_encode($result);
}

function api_renameFilmlist($username)
{
    $oldListname = array_value_by_key("oldl", $_GET);
    $newListname = array_value_by_key("newl", $_GET);
    logDebug("Params oldl=$oldListname, newl=$newListname", __FUNCTION__." ".__LINE__);

    $success = false;
    if (!empty($username) && !empty($oldListname) && !empty($newListname)) {
        $filmlist = new Filmlist($username, $oldListname);
        if (!empty($filmlist)) {
            $success = $filmlist->renameToDb($newListname);
        }
    }

    if ($success) {
        $response = '{"Success":"true"}';
    } else {
        $response = '{"Success":"false"}';
    }

    return $response;
}

?>