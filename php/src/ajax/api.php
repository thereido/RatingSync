<?php
namespace RatingSync;

use DateTime;
use Exception;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "main.php";
require_once "getHtmlFilmlists.php";

// Constants
const DEFAULT_RESPONSE = "{}";

// Handle the action and prepare response
$response = ApiHandler::request(input: $_GET);

// Output response
echo empty($response) ? DEFAULT_RESPONSE : $response;

class ApiHandler
{
    static private ApiHandler $api;

    static public function request(array $input): string
    {
        if (empty(self::$api)) {
            self::$api = new ApiHandler();
        }

        $username   = getUsername();
        $action     = array_value_by_key("action", $input);

        return self::$api->handleApiAction($action, $input, $username);
    }

    /**
     * Handles API actions using a mapping approach.
     *
     * @param string $action
     * @param array $input
     * @param string $username
     * @return string
     */
    private function handleApiAction(string $action, array $input, string $username): string
    {
        logDebug("API action: $action, username: $username  " . self::fullUri(), "");

        // Map actions to their handler functions
        $actionsMap = [
            "addFilmBySearch"           => fn() => $this->addFilmBySearch($username, $input),
            "archiveRating"             => fn() => $this->archiveRating($username, $input),
            "createFilmlist"            => fn() => $this->createFilmlist($username, $input),
            "deleteFilmlist"            => fn() => $this->deleteFilmlist($username, $input),
            "getFilm"                   => fn() => $this->getFilm($username, $input),
            "getFilms"                  => fn() => $this->getFilms($username, $input),
            "getFilmsByList"            => fn() => $this->getFilmsByList($username, $input),
            "getRatings"                => fn() => $this->getRatings($username, $input),
            "getSearchFilm"             => fn() => $this->getSearchFilm($username, $input),
            "getSeason"                 => fn() => $this->getSeason($input),
            "getStream"                 => fn() => $this->getStream($input),
            "getUser"                   => fn() => $this->getUser($username),
            "getUserLists"              => fn() => $this->getUserLists($username),
            "renameFilmlist"            => fn() => $this->renameFilmlist($username, $input),
            "searchFilms"               => fn() => $this->searchFilms($username, $input),
            "setFilmlist"               => fn() => $this->setFilmlist($username, $input),
            "setNeverWatch"             => fn() => $this->setNeverWatch($username, $input),
            "setRating"                 => fn() => $this->setRating($username, $input),
            "setSeen"                   => fn() => $this->setSeen($username, $input),
            "setTheme"                  => fn() => $this->setTheme($username, $input),
            "updateFilmSource"          => fn() => $this->updateFilmSource($input),
            "validateNewUsername"       => fn() => $this->validateNewUsername($input),
        ];

        // Call the handler if the action exists, otherwise return default
        return $actionsMap[$action]() ?? DEFAULT_RESPONSE;
    }

    /**
     * @return string
     */
    static private function fullUri(): string
    {
        $protocol = "http";
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            $protocol = "https";
        }

        return "$protocol://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    static private function searchTerms($get): array
    {
        $searchQuery            = array_value_by_key("q", $get);
        $searchUniqueEpisode    = array_value_by_key("ue", $get);
        $searchUniqueAlt        = array_value_by_key("ua", $get);
        $searchTitle            = array_value_by_key("t", $get);
        $searchYear             = array_value_by_key("y", $get);
        $searchParentYear       = array_value_by_key("py", $get);
        $searchSeason           = array_value_by_key("s", $get);
        $searchEpisodeNumber    = array_value_by_key("en", $get);
        $searchEpisodeTitle     = array_value_by_key("et", $get);
        $searchContentType      = array_value_by_key("ct", $get);
        $searchSource           = array_value_by_key("source", $get);

        $sourceName = match ($searchSource) {
            "IM" => Constants::SOURCE_IMDB,
            "NF" => Constants::SOURCE_NETFLIX,
            "RT" => Constants::SOURCE_RT,
            "XF" => Constants::SOURCE_XFINITY,
            "H" => Constants::SOURCE_HULU,
            default => Constants::SOURCE_RATINGSYNC,
        };

        logDebug("Params q=$searchQuery, ue=$searchUniqueEpisode, ua=$searchUniqueAlt, t=$searchTitle, y=$searchYear, py=$searchParentYear, s=$searchSeason, en=$searchEpisodeNumber, et=$searchEpisodeTitle, ct=$searchContentType, source=$searchSource", __FUNCTION__ . " " . __LINE__);
        return array(
            'uniqueName'    => $searchQuery,
            'uniqueEpisode' => $searchUniqueEpisode,
            'uniqueAlt'     => $searchUniqueAlt,
            'sourceName'    => $sourceName,
            'title'         => htmlspecialchars_decode($searchTitle),
            'year'          => $searchYear,
            'parentYear'    => $searchParentYear,
            'season'        => $searchSeason,
            'episodeNumber' => $searchEpisodeNumber,
            'episodeTitle'  => htmlspecialchars_decode($searchEpisodeTitle),
            'contentType'   => $searchContentType);
    }

    static public function getFilmApi($username, $filmId, $imdbId, $uniqueName, $getFromRsDbOnly, $contentType = null, $seasonNum = null, $episodeNum = null, $parentId = null): ?Film
    {
        $film = null;
        $api = getMediaDbApiClient();

        if (!empty($filmId)) {

            try {
                $film = Film::getFilmFromDb($filmId, $username);
            } catch (Exception $e) {
                logError("Error getting film from DB. Film id=$filmId, username=$username", e: $e);
            }

        } else {

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
                search($searchTerms, $username);
                $film = $api->getFilmFromDb($uniqueName, $contentType, $username);
            }
        }

        // Make sure the default API has a source and refresh the film
        // if the data is stale
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

    private function addFilmBySearch(string $username, array $input): void
    {
        $searchTerms = ApiHandler::searchTerms($input);

        try {
            search($searchTerms, $username);
        } catch (Exception $e) {
            $errorMsg = "Error \RatingSync\search()" .
                "\nsearchTerms keys: " . implode(",", array_keys($searchTerms)) .
                "\nsearchTerms values: " . implode(",", $searchTerms);
            logError($errorMsg, e: $e);
        }
    }

    private function archiveRating(string $username, array $input): bool|string
    {
        $film       = null;
        $filmId     = array_value_by_key("fid", $input);
        $dateStr    = array_value_by_key("d", $input); // Format: 2000-02-28
        $archiveNum = array_value_by_key("archive", $input);
        logDebug("Params fid=$filmId, d=$dateStr, archive=$archiveNum", __FUNCTION__ . " " . __LINE__);

        $archiveIt = true;
        if ($archiveNum == 0) {
            $archiveIt = false;
        }

        if (!empty($username) && !empty($filmId) && !empty($dateStr)) {
            try {
                $date = new DateTime($dateStr);
                $success = Rating::archiveRatingToDb($filmId, $username, $date, $archiveIt);

                if ($success) {
                    $film = Film::getFilmFromDb($filmId, $username);
                }
            } catch (Exception $e) {
                logError("Exception archiving/activating a rating (filmId=$filmId, username=$username, rating date=$dateStr, archiveIt=$archiveIt)", e: $e);
            }
        }

        if (empty($film)) {
            $response = '{"Success":"false"}';
        } else {
            $response = $film->json_encode();
        }

        return $response;
    }

    private function createFilmlist(string $username, array $input): void
    {
        $listname   = array_value_by_key("l", $input);
        $filmId     = array_value_by_key("id", $input);
        $checked    = array_value_by_key("a", $input);
        $parent     = array_value_by_key("parent", $input);
        $add        = false;
        if ($checked == 1) {
            $add = true;
        }
        logDebug("Params l=$listname, id=$filmId, a=$checked, parent=$parent", __FUNCTION__ . " " . __LINE__);
        $filmlist = Filmlist::getListFromDb($username, $listname, $parent);
        if ($add) {
            $filmlist->addItem($filmId);
        }

        $filmlist->createToDb();
    }

    private function deleteFilmlist(string $username, array $input): bool|string
    {
        $listname = array_value_by_key("l", $input);
        logDebug("Params l=$listname", __FUNCTION__ . " " . __LINE__);

        $result = '{ "Success": "false" }';
        if (!empty($username) && !empty($listname)) {
            $result = Filmlist::removeListFromDb($username, $listname, true);
        }

        return json_encode($result);
    }

    private function getFilm(string $username, array $input): bool|string
    {
        $filmId             = array_value_by_key("id", $input);
        $parentId           = array_value_by_key("pid", $input);
        $imdbId             = array_value_by_key("imdb", $input);
        $uniqueName         = array_value_by_key("un", $input);
        $contentType        = array_value_by_key("ct", $input);
        $seasonNum          = array_value_by_key("s", $input);
        $episodeNum         = array_value_by_key("e", $input);
        $getFromRsDbOnly    = array_value_by_key("rsonly", $input);
        logDebug("Params id=$filmId, pid=$parentId, imdbId=$imdbId, un=$uniqueName, ct=$contentType, s=$seasonNum, e=$episodeNum, rsonly=$getFromRsDbOnly", __FUNCTION__ . " " . __LINE__);

        if ($getFromRsDbOnly === "0") {
            $getFromRsDbOnly = false;
        } else {
            $getFromRsDbOnly = true;
        }

        $response = '{"Success":"false"}';
        $film = self::getFilmApi($username, $filmId, $imdbId, $uniqueName, $getFromRsDbOnly, $contentType, $seasonNum, $episodeNum, $parentId);

        if (!empty($film)) {
            $response = $film->json_encode();
        }

        return $response;
    }

    private function getFilms(string $username, array $input): string
    {
        $filmIdsParam                   = array_value_by_key("id", $input);
        $sourceIdContentTypesParam      = array_value_by_key("sidcts", $input);
        $uniqueNameContentTypesParam    = array_value_by_key("uncts", $input);
        $imdbIdContentTypesParam        = array_value_by_key("imdbcts", $input);
        $seriesFilmId                   = array_value_by_key("pid", $input);
        $seasonNum                      = array_value_by_key("s", $input);
        $episodeParam                   = array_value_by_key("e", $input);
        logDebug("Params id=$filmIdsParam, sidcts=$sourceIdContentTypesParam, uncts=$uniqueNameContentTypesParam, imdbcts=$imdbIdContentTypesParam, pid=$seriesFilmId, s=$seasonNum, e=$episodeParam", __FUNCTION__ . " " . __LINE__);

        $idContentTypeParam = "";
        $idType = null; // 'IMDb IDs', 'Source IDs', or 'uniqueNames'
        if (!empty($sourceIdContentTypesParam)) {
            $idContentTypeParam = $sourceIdContentTypesParam;
            $idType = "Source IDs";
        } elseif (!empty($uniqueNameContentTypesParam)) {
            $idContentTypeParam = $uniqueNameContentTypesParam;
            $idType = "uniqueNames";
        } elseif (!empty($imdbIdContentTypesParam)) {
            $idContentTypeParam = $imdbIdContentTypesParam;
            $idType = "IMDb IDs";
        } else {
            $idType = "Source IDs";
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

        $api = getMediaDbApiClient();
        $films = array();
        foreach ($filmIds as $filmId) {
            $film = null;
            try {
                $film = self::getFilmApi($username, $filmId, null, null, getFromRsDbOnly: true);
            } catch (Exception $e) {
                $errorMsg = "Error api.php::getFilmApi(\$username, $filmId, null, true) Called from api_getFilms()" .
                    "\nException (" . $e->getCode() . ") " . $e->getMessage();
                logDebug($errorMsg, __FUNCTION__ . " " . __LINE__);
            }
            if (!empty($film)) {
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
                $contentType = $pieces[1];
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
                $film = self::getFilmApi($username, null, $imdbId, $uniqueName, getFromRsDbOnly: true, contentType: $contentType);
            } catch (Exception $e) {
                $errorMsg = "Error api.php::getFilmApi($username, null, $imdbId, $uniqueName, true, $contentType) Called from api_getFilms()" .
                    "\nException (" . $e->getCode() . ") " . $e->getMessage();
                logDebug($errorMsg, __FUNCTION__ . " " . __LINE__);
            }

            // Add each film to the films array
            if (!empty($film)) {
                $films[] = $film;
            }
        }
        foreach ($episodeNums as $episodeNum) {
            $film = null;
            try {
                $film = $api->getEpisodeFromDb($seriesFilmId, $seasonNum, $episodeNum, $username);
            } catch (Exception $e) {
                $errorMsg = "Error getEpisodeFromDb($seriesFilmId, $seasonNum, $episodeNum, \$username) Called from api_getFilms()" .
                    "\nException (" . $e->getCode() . ") " . $e->getMessage();
                logDebug($errorMsg, __FUNCTION__ . " " . __LINE__);
            }
            if (!empty($film)) {
                $films[] = $film;
            }
        }

        $response = '{';
        $response .= '"films":[';
        $delimiter = "";
        foreach ($films as $film) {
            $response .= $delimiter . $film->json_encode(true);
            $delimiter = ",";
        }
        $response .= ']}';

        return $response;
    }

    private function getFilmsByList(string $username, array $input): string
    {
        $listname           = array_value_by_key("l", $input);
        $pageSize           = array_value_by_key("ps", $input);
        $beginPage          = array_value_by_key("bp", $input);
        $sort               = array_value_by_key("sort", $input);
        $sortDirection      = array_value_by_key("direction", $input);
        $filterlists        = array_value_by_key("filterlists", $input);
        $filtergenres       = array_value_by_key("filtergenres", $input);
        $filtergenreany     = array_value_by_key("filtergenreany", $input);
        $filtercontenttypes = array_value_by_key("filtercontenttypes", $input);
        logDebug("Params l=$listname, ps=$pageSize, bp=$beginPage, sort=$sort, sortDirection=$sortDirection, filterlists=$filterlists, filtergenres=$filtergenres, filtergenreany=$filtergenreany, filtercontenttypes=$filtercontenttypes", __FUNCTION__ . " " . __LINE__);

        if (empty($pageSize)) {
            $pageSize = null;
        }
        if (empty($beginPage)) {
            $beginPage = 1;
        }

        $sort = ListSortField::convert($sort);
        if ($sort == null) {
            $sort = ListSortField::position;
        }

        $sortDirection = SqlSortDirection::convert($sortDirection);
        if ($sortDirection == null) {
            $sortDirection = SqlSortDirection::descending;
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
        $response .= '"totalCount":"' . $totalCount . '"';
        $response .= ', "pageSize":"' . $pageSize . '"';
        $response .= ', "beginPage":"' . $beginPage . '"';
        $response .= ', "films":[';
        $delimiter = "";
        foreach ($films as $film) {
            $response .= $delimiter . $film->json_encode(false);
            $delimiter = ",";
        }
        $response .= ']}';

        return $response;
    }

    private function getRatings(string $username, array $input): string
    {
        $pageSize           = array_value_by_key("ps", $input);
        $beginPage          = array_value_by_key("bp", $input);
        $sort               = array_value_by_key("sort", $input);
        $sortDirection      = array_value_by_key("direction", $input);
        $filterlists        = array_value_by_key("filterlists", $input);
        $filtergenres       = array_value_by_key("filtergenres", $input);
        $filtergenreany     = array_value_by_key("filtergenreany", $input);
        $filtercontenttypes = array_value_by_key("filtercontenttypes", $input);
        logDebug("Params ps=$pageSize, bp=$beginPage, sort=$sort, sortDirection=$sortDirection, filterlists=$filterlists, filtergenres=$filtergenres, filtergenreany=$filtergenreany, filtercontenttypes=$filtercontenttypes", __FUNCTION__ . " " . __LINE__);

        if (empty($pageSize)) {
            $pageSize = null;
        }
        if (empty($beginPage)) {
            $beginPage = 1;
        }

        $sort = RatingSortField::convert($sort);
        if ($sort == null) {
            $sort = RatingSortField::date;
        }

        $sortDirection = SqlSortDirection::convert($sortDirection);
        if ($sortDirection == null) {
            $sortDirection = SqlSortDirection::descending;
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

        $site = new RatingSyncSite($username);
        $site->setSort($sort);
        $site->setSortDirection($sortDirection);
        $site->setListFilter($filterListsArr);
        $site->setGenreFilter($filterGenresArr);
        $site->setGenreFilterMatchAny($filterGenresMatchAny);
        $site->setContentTypeFilter($filterContentTypesArr);
        $films = $site->getRatedFilms($pageSize, $beginPage);
        $totalRatings = $site->countRatings();

        $response = '{';
        $response .= '"totalCount":"' . $totalRatings . '"';
        $response .= ', "pageSize":"' . $pageSize . '"';
        $response .= ', "beginPage":"' . $beginPage . '"';
        $response .= ', "films":[';
        $delimiter = "";
        foreach ($films as $film) {
            $response .= $delimiter . $film->json_encode(false);
            $delimiter = ",";
        }
        $response .= ']}';

        return $response;
    }

    private function getSearchFilm(string $username, array $input): string
    {
        $searchTerms = ApiHandler::searchTerms($input);

        try {
            $resultFilms    = search($searchTerms, $username);
            $matchFilm      = array_value_by_key('match', $resultFilms);
            $parentFilm     = array_value_by_key('parent', $resultFilms);
        } catch (Exception $e) {
            $errorMsg = "Error during search" .
                "\nSearch Terms Keys: " . implode(",", array_keys($searchTerms)) .
                "\nSearch Terms Values: " . implode(",", $searchTerms);
            logError(message: $errorMsg, e: $e);
            return json_encode([]); // Return empty JSON on error
        }

        $responseArr = array();
        if (!empty($matchFilm)) {
            $responseArr['match'] = $matchFilm->asArray();
        }
        if (!empty($parentFilm)) {
            $responseArr['parent'] = $parentFilm->asArray();
        }

        return json_encode($responseArr);
    }

    /**
     * @return false|string
     */
    private function getSeason(array $input): bool|string
    {
        $filmId     = array_value_by_key("id", $input);
        $seasonNum  = array_value_by_key("s", $input);
        logDebug("Params id=$filmId, s=$seasonNum", __FUNCTION__ . " " . __LINE__);

        $dataApi = getMediaDbApiClient();
        $season = null;
        try {
            $season = $dataApi->getSeasonFromApi($filmId, $seasonNum);
        } catch (Exception $e) {
            $errorMsg = "Error getSeasonFromApi(filmId=$filmId, seasonNum=$seasonNum)" .
                "\nException " . $e->getCode() . " " . $e->getMessage() .
                "\n$e";
            logDebug($errorMsg, __FUNCTION__ . " " . __LINE__);
        }

        $response = json_encode(array("Response" => "False"));
        if (!empty($season)) {
            $response = $season->json_encode(true);
        }

        return $response;
    }

    private function getStream(array $input): string
    {
        $response   = "NONE";
        $filmId     = array_value_by_key("id", $input);
        $sourceName = array_value_by_key("source", $input);
        logDebug("Params filmid=$filmId, source=$sourceName", __FUNCTION__ . " " . __LINE__);

        try {
            $film = Film::getFilmFromDb($filmId);
        } catch (Exception $e) {
            logError("Error getting film from DB. Film id=$filmId", e: $e);
            return $response;
        }
        $source = $film->getSource($sourceName);
        $source->createSourceToDb($film);
        $streamUrl = $source->getStreamUrl();

        if (!empty($streamUrl)) {
            $response = $streamUrl;
        }

        return $response;
    }

    private function getUser(string $username): bool|string
    {
        logDebug("Params (none)", __FUNCTION__ . " " . __LINE__);

        $userArr = array("username" => $username);
        return json_encode($userArr);
    }

    /**
     * return {[{"listname": "name1", "username": "username", "items":[filmId1, filmId2, ...]}], ...}
     */
    private function getUserLists(string $username): bool|string
    {
        $lists = Filmlist::getUserListsFromDbByParent($username, true);
        return json_encode($lists);
    }

    /**
     * @param string $username
     * @param array $input
     * @return string
     */
    private function renameFilmlist(string $username, array $input): string
    {
        $oldListname    = array_value_by_key("oldl", $input);
        $newListname    = array_value_by_key("newl", $input);
        logDebug("Params oldl=$oldListname, newl=$newListname", __FUNCTION__ . " " . __LINE__);

        $success = false;
        if (!empty($username) && !empty($oldListname) && !empty($newListname)) {
            $filmlist = new Filmlist($username, $oldListname);
            $success = $filmlist->renameToDb($newListname);
        }

        if ($success) {
            $response = '{"Success":"true"}';
        } else {
            $response = '{"Success":"false"}';
        }

        return $response;
    }

    private function searchFilms(string $username, array $input): string
    {
        $searchDomain   = array_value_by_key("sd", $input);
        $listname       = array_value_by_key("list", $input);
        $query          = array_value_by_key("q", $input);
        logDebug("Params sd=$searchDomain, list=$listname, q=$query", __FUNCTION__ . " " . __LINE__);

        $site = new RatingSyncSite($username);
        $limit = 5;
        // Search domains supported: ratings, list, both
        $films = $site->search($query, $searchDomain, $listname, $limit);

        $response = '{';
        $response .= '"films":[';
        $delimiter = "";
        foreach ($films as $film) {
            $response .= $delimiter . $film->json_encode(true);
            $delimiter = ",";
        }
        $response .= ']}';

        return $response;
    }

    private function setFilmlist(string $username, array $input): bool|string
    {
        $listname   = array_value_by_key("l", $input);
        $filmId     = array_value_by_key("id", $input);
        $checked    = array_value_by_key("c", $input);
        $remove     = false;
        if ($checked == 0) {
            $remove = true;
        }
        logDebug("Params l=$listname, id=$filmId, c=$checked", __FUNCTION__ . " " . __LINE__);
        $filmlist = Filmlist::getListFromDb($username, $listname);
        if ($remove) {
            $filmlist->removeItem($filmId, true);
        } else {
            $filmlist->addItem($filmId, true);
        }

        try {
            $film = Film::getFilmFromDb($filmId, $username);
        } catch (Exception $e) {
            logError("Error \RatingSync\Film::getFilmFromDb() ", e: $e);
            return '{"Success":"false"}';
        }

        return $film->json_encode();
    }

    private function setNeverWatch(string $username, array $input): string
    {
        $film       = null;
        $filmId     = array_value_by_key("fid", $input);
        $neverWatch = array_value_by_key("never", $input);
        logDebug("Params fid=$filmId, never=$neverWatch", __FUNCTION__ . " " . __LINE__);

        $neverWatchBool = false;
        if ($neverWatch == 1 || $neverWatch == "true") {
            $neverWatchBool = true;
        }

        if (!empty($username) && !empty($filmId) && !empty($neverWatch)) {
            try {

                $filmInfo = UserSpecificFilmInfo::getFromDb($username, $filmId);
                $film = $filmInfo->setNeverWatchToDb($neverWatchBool, new DateTime());

            } catch (Exception $e) {
                logError("Exception setting whether the user never plans to watch the title (filmId=$filmId, username=$username, never=$neverWatch)", e: $e);
            }
        }

        if (empty($film)) {
            $response = '{"Success":"false"}';
        } else {
            $response = $film->json_encode();
        }

        return $response;
    }

    private function setRating(string $username, array $input): bool|string
    {
        $film = null;
        $filmId             = array_value_by_key("fid", $input);
        $score              = array_value_by_key("s", $input);
        $watchedParam       = array_value_by_key("w", $input);
        $dateStr            = array_value_by_key("d", $input); // Format: 2000-02-28
        $originalDateStr    = array_value_by_key("od", $input); // Format: 2000-02-28
        $force              = array_value_by_key("force", $input);
        logDebug("Params fid=$filmId, s=$score, w=$watchedParam, d=$dateStr, od=$originalDateStr, force=$force", __FUNCTION__ . " " . __LINE__);

        if ($score == "null") {
            $score = null;
        } else {
            try {
                $score = intval($score); // On failure, it returns 0
                $score = SetRatingScoreValue::create($score);
            } catch (Exception) {
                $score = null;
            }
        }

        if ($dateStr == "null") {
            $dateStr = null;
        }

        $watched = false;
        if ($watchedParam == 1 || $watchedParam == "true") {
            $watched = true;
        }

        $forceDelete = false;
        if ($force == 1 || $force == "true") {
            $forceDelete = true;
        }

        if (!empty($username) && !empty($filmId) && $score instanceof SetRatingScoreValue) {
            $film = setRating($filmId, $score, $watched, $dateStr, $originalDateStr, $forceDelete);
        }

        if (empty($film)) {
            $response = '{"Success":"false"}';
        } else {
            $response = $film->json_encode();
        }

        return $response;
    }

    private function setSeen(string $username, array $input): string
    {
        $film   = null;
        $filmId = array_value_by_key("fid", $input);
        $seen   = array_value_by_key("seen", $input);
        logDebug("Params fid=$filmId, seen=$seen", __FUNCTION__ . " " . __LINE__);

        $seenBool = false;
        if ($seen == 1 || $seen == "true") {
            $seenBool = true;
        }

        if (!empty($username) && !empty($filmId) && !empty($seen)) {
            try {

                $filmInfo = UserSpecificFilmInfo::getFromDb($username, $filmId);
                $film = $filmInfo->setSeenToDb($seenBool, new DateTime());

            } catch (Exception $e) {
                logError("Exception setting whether the user has seen this title (filmId=$filmId, username=$username, seen=$seen)", e: $e);
            }
        }

        if (empty($film)) {
            $response = '{"Success":"false"}';
        } else {
            $response = $film->json_encode();
        }

        return $response;
    }

    private function setTheme(string $username, array $input): string
    {
        $themeId    = array_value_by_key("i", $input);
        logDebug("Params i=$themeId", __FUNCTION__ . " " . __LINE__);

        $success = false;

        try {

            $user = userMgr()->findViewWithUsername($username);
            if ($user === false) throw new Exception("Invalid user username ($username)");

            $userViewIsSet = $user->setTheme($themeId);

            $userId = null;
            if ($userViewIsSet) {

                try {
                    $userId = userMgr()->save($user);
                } catch (EntityInvalidSaveException $e) {
                    logError($e->getMessage(), e: $e);
                    // FIXME We should put the message in the response
                }

            }

            if (!is_null($userId)) {
                $success = true;
            }

        } catch (Exception $e) {
            logError($e->getMessage(), e: $e);
            //$success = false;
        }

        if ($success) {
            return '{"Success":"true"}';
        } else {
            return '{"Success":"false"}';
        }
    }

    private function updateFilmSource(array $input): void
    {
        $filmId         = array_value_by_key("filmid", $input);
        $sourceName     = array_value_by_key("source", $input);
        $streamUrl      = array_value_by_key("su", $input);
        $uniqueName     = array_value_by_key("un", $input);
        $uniqueEpisode  = array_value_by_key("ue", $input);
        $uniqueAlt      = array_value_by_key("ua", $input);
        logDebug("Params filmid=$filmId, source=$sourceName, su=$streamUrl, un=$uniqueName, ue=$uniqueEpisode, ua=$uniqueAlt", __FUNCTION__ . " " . __LINE__);

        if ($streamUrl == "none") {
            $streamUrl = null;
        }

        $source = new Source($sourceName, $filmId);
        $source->setUniqueName($uniqueName);
        $source->setUniqueEpisode($uniqueEpisode);
        $source->setUniqueAlt($uniqueAlt);
        $source->setStreamUrl($streamUrl);
        $source->refreshStreamDate();
        try {
            $source->saveFilmSourceToDb($filmId);
        } catch (Exception $e) {
            logError("Error saving film source to DB.", e: $e);
        }
    }

    private function validateNewUsername(array $input): string
    {
        $newUsername = array_value_by_key("u", $input);
        logDebug("Params u=$newUsername", __FUNCTION__ . " " . __LINE__);

        $isValid = "false";
        if (!RatingSyncSite::usernameExists($newUsername)) {
            $isValid = "true";
        }

        return '{"valid":"' . $isValid . '"}';
    }
}