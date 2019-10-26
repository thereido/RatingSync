<?php
/**
 * TmdbApi class
 */
namespace RatingSync;

require_once "MediaDbApiClient.php";
require_once "Site.php";

/**
 * Get data from the TMDb API website
 * - Search for films and tv shows
 * - Get details for each
 */
class TmdbApi extends \RatingSync\MediaDbApiClient
{
    const API_RESPONSE_FORMAT = "json";
    const BASE_API_URL = "https://api.themoviedb.org/3";
    const ID_PREFIXES = array(Film::CONTENT_FILM => "mv"
                              ,Film::CONTENT_TV_SERIES => "tv"
                              ,Film::CONTENT_TV_EPISODE => "ep"
                              ,Film::CONTENT_TV_SEASON => "ts"
                              ,Film::CONTENT_SHORTFILM => "sf"
                              ,Film::CONTENT_TV_MOVIE => "tm"
                             );
    const REQUEST_DETAIL_MOVIE = "movie";
    const REQUEST_DETAIL_SERIES = "tv_series";
    const REQUEST_DETAIL_EPISODE = "tv_episode";
    const REQUEST_FIND = "find";
    const REQUEST_SEARCH_MOVIE = "search_movie";
    const REQUEST_SEARCH_SERIES = "search_tv";
    const REQUEST_SEARCH_MULTI = "search_multi";

    public function __construct()
    {
        $this->sourceName = Constants::SOURCE_TMDBAPI;
        $this->cacheFileExtension = static::API_RESPONSE_FORMAT;
        $this->baseUrl = static::BASE_API_URL;
        $this->apiKey = Constants::TMDB_API_KEY;
    }

    /**
     * Return the API URL for the detail of a film.  
     *
     * @param \RatingSync\Film $film Film the URL goes to
     *
     * @return string API URL to film detail
     */
    protected function buildUrlFilmDetail($film)
    {
        $supportedContent = array(Film::CONTENT_FILM => 1, Film::CONTENT_TV_SERIES => 1, Film::CONTENT_TV_EPISODE => 1);
        $parentUniqueName = null;
        if (! $film instanceof Film ) {
            throw new \InvalidArgumentException(__CLASS__."::".__FUNCTION__.' must be given a Film object');
        } elseif ( empty($film->getContentType()) ) {
            throw new \InvalidArgumentException(__CLASS__."::".__FUNCTION__.' $film must have contentType');
        } elseif ( ! array_key_exists($film->getContentType(), $supportedContent) ) {
            throw new \InvalidArgumentException(__CLASS__."::".__FUNCTION__.' $film contentType (' . $film->getContentType() . ') is not supported');
        } elseif ( $film->getContentType() == Film::CONTENT_FILM || $film->getContentType() == Film::CONTENT_TV_SERIES ) {
            if ( empty($film->getUniqueName($this->sourceName)) ) {
                throw new \InvalidArgumentException(__CLASS__."::".__FUNCTION__.' must have unique attr (uniqueName, '.$this->sourceName.') for contentType ' . $film->getContentType());
            }
        } elseif ( $film->getContentType() == Film::CONTENT_TV_EPISODE ) {
            if ( empty($film->getSeason()) || empty($film->getEpisodeNumber()) ) {
                $msgSeasonNum = "season number (" . $film->getSeason() . ")";
                $msgEpisodeNum = "episode number (" . $film->getEpisodeNumber() . ")";
                throw new \InvalidArgumentException("Function ".__FUNCTION__." must have $msgSeasonNum and $msgEpisodeNum of the \$film");
            }
            if ( empty($film->getParentId()) ) {
                throw new \InvalidArgumentException(__CLASS__."::".__FUNCTION__.' $film parentId must not be empty for TV episode');
            }
            $parentUniqueName = $this->getEpisodeParentUniqueName($film);
            if (empty($parentUniqueName)) {
                throw new \Exception(__CLASS__."::".__FUNCTION__." unable to find the parent for this TV episode. A URL for a detail request for an episode needs the parent.");
            }
        }

        $url = static::BASE_API_URL;
        $uniqueName = $film->getUniqueName($this->sourceName);
        $contentType = $film->getContentType();
        if ($contentType == Film::CONTENT_FILM) {
            $url .= "/movie/" . $this->getSourceIdFromUniqueName($uniqueName);
        }
        else if ($contentType == Film::CONTENT_TV_SERIES) {
            $url .= "/tv/" . $this->getSourceIdFromUniqueName($uniqueName);
        }
        else if ($contentType == Film::CONTENT_TV_EPISODE) {
            $url .= "/tv/" . $this->getSourceIdFromUniqueName($parentUniqueName);
            $url .= "/season/" . $film->getSeason();
            $url .= "/episode/" . $film->getEpisodeNumber();
        }

        $url .= "?api_key=" . Constants::TMDB_API_KEY;

        return $url;
    }

    /**
     * Search the api with the attr available in this $film. If there is a
     * single result, return the uniqueName. Supports Movie and TV Series. Does
     * NOT support TV Episode.
     * 
     * Attr combinations supported for Movie & TV Series
     *   - IMDb ID
     *   - TMDb ID
     *   - Title, Year, contentType
     *   - Title, Year
     * 
     * Attr combinations supported for TV Episode
     *   - IMDb ID
     *   - TMDb ID
     *
     * @param \RatingSync\Film $film
     *
     * @return string uniqueName or null
     */
    public function searchForUniqueName($film)
    {
        if (!($film instanceof Film)) {
            throw new \InvalidArgumentException('$film must be a RatingSync\Film object');
        }

        // Is the uniqueName already available?
        $uniqueName = $film->getUniqueName($this->sourceName);
        if (!empty($uniqueName)) {
            return $uniqueName;
        }

        $imdbId = $film->getUniqueName(Constants::SOURCE_IMDB);
        $title = $film->getTitle();
        $year = $film->getYear();
        $contentType = $film->getContentType();

        // Do a find request with the IMDb ID
        if (!empty($imdbId)) {
            $url = "/find/$imdbId" . "?external_source=imdb_id";
            $url .= "&api_key=" . $this->apiKey;
            $json = $this->apiRequest($url);
            $response = json_decode($json, true);

            $contentTypeFound = null;
            $result = $this->getFindResultFromResponse($response, $contentTypeFound);
            if (!empty($result)) {
                if (empty($contentType)) {
                    $contentType = $contentTypeFound;
                }

                // Don't use the result unless it matches the contentType expected
                if ($contentType == $contentTypeFound) {
                    $uniqueName = $this->getUniqueNameFromSourceId($result["id"], $contentType);
                }
            }
        }

        // We have title and year, but no contentType
        // Do a multi search by title and year
        //   NOT SUPPORTED (multi search not supported)
        if (empty($uniqueName) && empty($contentType) && !empty($title) && !empty($year)) {
            throw new \Exception(__CLASS__."::".__FUNCTION__." with a null \$film->contentType is not supported");
        }

        // We have contentType, title and year
        // Do a TV Series or Movie search by title and year
        if (empty($uniqueName) && !empty($contentType) && !empty($title) && !empty($year)) {
            $searchUrlType = null;
            $yearParamName = null;
            $contentTypeSupported = false;
            if ($contentType == Film::CONTENT_FILM) {
                $searchUrlType = "movie";
                $yearParamName = "year";
                $contentTypeSupported = true;
            } else if ($contentType == Film::CONTENT_TV_SERIES) {
                $searchUrlType = "tv";
                $yearParamName = "first_air_date_year";
                $contentTypeSupported = true;
            } else {
                // Only movies and tv shows are supported (no episodes or seasons)
            }

            if ($contentTypeSupported) {
                $url = "/search/$searchUrlType";
                $url .= "?query=" . htmlspecialchars($title);
                $url .= "&".$yearParamName."=" . htmlspecialchars($year);
                $url .= "&api_key=" . $this->apiKey;
                $json = $this->apiRequest($url);
                $response = json_decode($json, true);

                $result = $this->getSearchResultFromResponse($response, $title, $year, $contentType);
                if (!empty($result)) {
                    $uniqueName = $this->getUniqueNameFromSourceId($result["id"], $contentType);
                }
            }
        }

        return $uniqueName;
    }
    
    /**
     * Get detail from the TMDb API and populate the $film param.
     * 
     * Responses from the TMDb API for movie, tv and episode. These api
     * responses are not complete, just the fields we use.
     * 
     * Movie - https://api.themoviedb.org/3/movie/76341?api_key={api_key}
     * {
     *   "genres": [
     *     {
     *       "id": 18,
     *       "name": "Drama"
     *     }
     *   ],
     *   "id": 550,
     *   "imdb_id": "tt0137523",
     *   "poster_path": null,
     *   "release_date": "1999-10-12",
     *   "title": "Fight Club",
     *   "vote_average": 7.8,
     * }
     */
    public function populateFilmDetail($filmJson, $film, $overwrite)
    {
//*RT* Not implemented yet. This is straight from OMDbApi.
/*RT*/logDebug("Begin populateFilmDetail()");
        if (is_null($film) || !($film instanceof Film) ) {
            throw new \InvalidArgumentException('arg1 must be a Film object');
        }

        // Get values from the API result
        $uniqueName = array_value_by_key("id", $filmJson, "N/A");
        $imdbId = array_value_by_key("imdb_id", $filmJson, "N/A");
        $parentId = null;
        $title = array_value_by_key("title", $filmJson, "N/A");
        $episodeTitle = null;
        $year = null;
        $releaseDate = array_value_by_key("release_date", $filmJson, "N/A");
        if (!empty($releaseDate)) { $year = substr($releaseDate, 0, 4); }
        $firstAirDate = array_value_by_key("first_air_date", $filmJson, "N/A");
        $image = array_value_by_key("poster_path", $filmJson, "N/A");
        $userScore = array_value_by_key("vote_average", $filmJson, "N/A");
        $seasonCount = array_value_by_key("totalSeasons", $filmJson, "N/A");
        $season = array_value_by_key("Season", $filmJson, "N/A");
        $episodeNum = array_value_by_key("Episode", $filmJson, "N/A");
        $genres = array_value_by_key("Genre", $filmJson);
        $directors = array_value_by_key("Director", $filmJson);
        $seriesID = array_value_by_key("seriesID", $filmJson, "N/A");

        if (empty($year) && !empty($firstAirDate)) {
            $year = substr($firstAirDate, 0, 4);
        }

        $contentType = Film::CONTENT_FILM;
        $type = array_value_by_key("Type", $filmJson);
        if ("series" == $type) { $contentType = Film::CONTENT_TV_SERIES; }
        if ("episode" == $type) { $contentType = Film::CONTENT_TV_EPISODE; }

        if ($contentType == Film::CONTENT_TV_EPISODE) {
            // In RatingSync title is the series title and episodeTitle is separate
            // In TMDbAPI title is the episode title
            $episodeTitle = $title;
            $title = null;

            // Get the series' title
            $searchTerms = array("uniqueName" => $seriesID, "sourceName" => Constants::SOURCE_TMDBAPI);
            $seriesSearchResult = search($searchTerms);
            if (!empty($seriesSearchResult) && !empty($seriesSearchResult["match"])) {
                $seriesFilm = $seriesSearchResult["match"];
                $parentId = $seriesFilm->getId();
                $title = $seriesFilm->getTitle();
            }
        }

        $metacriticScore = array_value_by_key("Metascore", $filmJson, "N/A");
        if (empty($metacriticScore) || !is_numeric($metacriticScore)) {
            $metacriticScore = null;
        } else {
            $metacriticScore = $metacriticScore*10/100;
        }

        // Get the existing values
        $existingUniqueName = $film->getUniqueName($this->sourceName);
        $existingParentId = $film->getParentId();
        $existingTitle = $film->getTitle();
        $existingEpisodeTitle = $film->getEpisodeTitle();
        $existingYear = $film->getYear();
        $existingTMDbImage = $film->getImage($this->sourceName);
        $existingContentType = $film->getContentType();
        $existingSeasonCount = $film->getSeasonCount();
        $existingSeason = $film->getSeason();
        $existingEpisodeNum = $film->getEpisodeNumber();
        $existingUserScore = $film->getUserScore($this->sourceName);
        $existingCriticScore = $film->getCriticScore($this->sourceName);
        $existingGenreCount = count($film->getGenres());
        $existingDirectorCount = count($film->getDirectors());

        // Init/Replace the values when appropiate
        if ($overwrite || is_null($existingUniqueName)) { $film->setImage($uniqueName, $this->sourceName); }
        if ($overwrite || is_null($existingParentId)) { $film->setParentId($parentId); }
        if ($overwrite || is_null($existingTitle)) { $film->setTitle($title); }
        if ($overwrite || is_null($existingEpisodeTitle)) { $film->setEpisodeTitle($episodeTitle); }
        if ($overwrite || is_null($existingYear)) { $film->setYear($year); }
        if ($overwrite || is_null($existingTMDbImage)) { $film->setImage($image, $this->sourceName); }
        if ($overwrite || is_null($existingContentType)) { $film->setContentType($contentType); }
        if ($overwrite || is_null($existingSeasonCount)) { $film->setSeasonCount($seasonCount); }
        if ($overwrite || is_null($existingSeason)) { $film->setSeason($season); }
        if ($overwrite || is_null($existingEpisodeNum)) { $film->setEpisodeNumber($episodeNum); }
        if ($overwrite || is_null($existingUserScore)) { $film->setUserScore($userScore, $this->sourceName); }
        if ($overwrite || is_null($existingCriticScore)) { $film->setCriticScore($metacriticScore, $this->sourceName); }

        if ($overwrite || $existingGenreCount == 0) {
            $film->removeAllGenres();
            if ("N/A" != $genres) {
                $genreTok = strtok($genres, ",");
                while ($genreTok !== false) {
                    $film->addGenre(trim($genreTok));
                    $genreTok = strtok(",");
                }
            }
        }

        $existingDirectorCount = count($film->getDirectors());
        if ($overwrite || $existingDirectorCount == 0) {
            $film->removeAllDirectors();
            if ("N/A" != $directors) {
                $directorTok = strtok($directors, ",");
                while ($directorTok !== false) {
                    $film->addDirector(trim($directorTok));
                    $directorTok = strtok(",");
                }
            }
        }

        // Copy data from TMDb to IMDb
        $existingIMDbUniqueName = $film->getUniqueName(Constants::SOURCE_IMDB);
        $existingIMDbImage = $film->getImage(Constants::SOURCE_IMDB);
        $existingIMDbUserScore = $film->getUserScore(Constants::SOURCE_IMDB);
        $existingIMDbCriticScore = $film->getCriticScore(Constants::SOURCE_IMDB);
        if ($overwrite || is_null($existingIMDbUniqueName)) { $film->setUniqueName($uniqueName, Constants::SOURCE_IMDB); }
        if ($overwrite || is_null($existingIMDbImage)) { $film->setImage($image, Constants::SOURCE_IMDB); }
        if ($overwrite || is_null($existingIMDbUserScore)) { $film->setUserScore($userScore, Constants::SOURCE_IMDB); }
        if ($overwrite || is_null($existingIMDbCriticScore)) { $film->setCriticScore($metacriticScore, Constants::SOURCE_IMDB); }
    }

    protected function printResultToLog($filmJson) {
        $title = array_value_by_key("Title", $filmJson);
        $year = array_value_by_key("Year", $filmJson);
        $uniqueName = array_value_by_key("imdbID", $filmJson);
        $seriesID = array_value_by_key("seriesID", $filmJson);
        $msg = "TMDb API result: $title ($year)";
        $msg .= " imdbID/seriesID $uniqueName/$seriesID";
        logDebug($msg, __CLASS__."::".__FUNCTION__." ".__LINE__);
    }

    /**
     * Return URL within a website for searching films. The URL does not
     * include the base URL.  
     *
     * @param array $args See the child class version of args
     *
     * @return string URL of a rating page
     */
    public function getSearchUrl($args)
    {
//*RT* Not implemented yet. This is straight from OMDbApi.
//*RT* Is it needed?
        if (empty($args) || !is_array($args) || empty($args["query"]))
        {
            throw new \InvalidArgumentException('$args must be an array with key "query" (non-empty)');
        }
        
        $searchUrl = "&s=" . urlencode($args["query"]);

        return $searchUrl;
    }

    /**
     * Return URL for a search for one result.  
     *
     * @param RatingSync/Film $film Has the info for searching
     *
     * @return string URL of a rating page
     */
    public function buildUrlSearchForSingleFilm($film)
    {
//*RT* Not implemented yet. This is straight from OMDbApi.
//*RT* Is it needed?
        $uniqueName = $film->getUniqueName($this->sourceName);
        $title = $film->getTitle();
        $episodeTitle = $film->getEpisodeTitle();
        $year = $film->getYear();
        $contentType = $film->getContentType();

        if (empty($uniqueName)) {
            $uniqueNameIMDb = $film->getUniqueName(Constants::SOURCE_IMDB);
            if (!empty($uniqueNameIMDb)) {
                $uniqueName = $uniqueNameIMDb;
            }
        }
        
        if (empty($uniqueName) && (empty($title) || empty($year)) && (empty($episodeTitle) || empty($year)))
        {
            throw new \InvalidArgumentException('film param must have a uniqueName or a year and either title or episodeTitle.');
        }
        
        $filmUrl = "";
        if (!empty($uniqueName)) {
            // "Search" by IMDb ID
            $filmUrl .= "&i=$uniqueName";
        }
        elseif (!empty($year) && (!empty($title) || !empty($episodeTitle))) {
            // Year
            $filmUrl .= "&y=$year";

            // Title
            $titleToUse = $title;
            if (empty($titleToUse)) {
                $titleToUse = $episodeTitle;
            }
            elseif ($contentType == Film::CONTENT_TV_EPISODE && !empty($episodeTitle)) {
                $titleToUse = $episodeTitle;
            }
            $filmUrl .= "&t=" . urlencode($titleToUse);
                
            // Content Type
            if ($contentType == Film::CONTENT_TV_EPISODE) {
                $filmUrl .= "&type=episode";
            } elseif ($contentType == Film::CONTENT_TV_SERIES) {
                $filmUrl .= "&type=series";
            } elseif ($contentType == Film::CONTENT_FILM) {
                $filmUrl .= "&type=movie";
            }
        }

        return $filmUrl;
    }
    
    public function getSeason($seriesFilmId, $seasonNum, $refreshCache = 60)
    {
//*RT* Not implemented yet. This is straight from OMDbApi.
//*RT* Is it needed?
        if (is_null($seriesFilmId) || !is_numeric($seriesFilmId) ) {
            throw new \InvalidArgumentException('arg1 must be numeric');
        } else if (is_null($seasonNum) || !is_numeric($seasonNum) ) {
            throw new \InvalidArgumentException('arg2 must be numeric');
        }

        $resultAsArray = null;

        $film = Film::getFilmFromDb($seriesFilmId);
        if (empty($film)) {
            return false;
        }
        $uniqueName = $film->getUniqueName($this->sourceName);

        $seasonPage = $this->getSeasonPageFromCache($seriesFilmId, $seasonNum, $refreshCache);
        if (empty($seasonPage) && !empty($uniqueName)) {
            try {
                $seasonUrl = "&i=" . $uniqueName . "&Season=" . $seasonNum;
                $seasonPage = $this->http->getPage($seasonUrl);
                $resultAsArray =  json_decode($seasonPage, true);
        
                if (!empty($resultAsArray) && $resultAsArray["Response"] != "False") {
                    $this->cacheSeasonPage($seasonPage, $seriesFilmId, $seasonNum);
                }
            } catch (\Exception $e) {
                logDebug($e, __FUNCTION__." ".__LINE__);
                throw $e;
            }
        } else {
            $resultAsArray =  json_decode($seasonPage, true);
        }

        if (empty($resultAsArray) || !is_array($resultAsArray) || $resultAsArray["Response"] == "False") {
            $errorMsg = "TMDb API request failed. Title=".$film->getTitle();
            $errorMsg .= ", Season=" . $seasonNum;
            $errorMsg .= ", UniqueName=" . $uniqueName;
            logDebug($errorMsg, __CLASS__."::".__FUNCTION__." ".__LINE__, true, $resultAsArray);
            throw new \Exception('TMDbApi season failed');
        }

        return $resultAsArray;
    }

    /**
     * Return a cached film page if the cached file is fresh enough. The $refreshCache param
     * shows if it is fresh enough. If the file is out of date return null.
     *
     * @param \RatingSync\Film $film         Film needed detail for
     * @param int              $seasonNum    Season looking for within the film (series)
     * @param int|0            $refreshCache Use cache for files modified within mins from now. -1 means always use cache. Zero means never use cache.
     *
     * @return string File as a string. Null if the use cache is not used.
     */
    public function getSeasonPageFromCache($seriesFilmId, $seasonNum, $refreshCache = Constants::USE_CACHE_NEVER)
    {
//*RT* Not implemented yet. This is straight from OMDbApi.
//*RT* Is it needed?
        if (Constants::USE_CACHE_NEVER == $refreshCache) {
            return null;
        }
        
        $filename = Constants::cacheFilePath() . $this->sourceName;
        $filename .= "_series_" . $seriesFilmId;
        $filename .= "_season_" . $seasonNum;
        $filename .= ".html";

        if (!file_exists($filename) || (filesize($filename) == 0)) {
            return null;
        }

        $fileDateString = filemtime($filename);
        if (!$fileDateString) {
            return null;
        }

        $filestamp = date("U", $fileDateString);
        $refresh = true;
        if (Constants::USE_CACHE_ALWAYS == $refreshCache || ($filestamp >= (time() - ($refreshCache * 60)))) {
            $refresh = false;
        }
        
        if (!$refresh) {
            return file_get_contents($filename);
        } else {
            return null;
        }
    }

    /**
     * Cache a season result in json in a local file
     *
     * @param string    $page           File as a string
     * @param int       $seriesFilmId   DB film_id of the series
     * @param int       $seasonNum      Season number
     */
    public function cacheSeasonPage($page, $seriesFilmId, $seasonNum)
    {
//*RT* Not implemented yet. This is straight from OMDbApi.
//*RT* Is it needed?
        $filename = Constants::cacheFilePath() . $this->sourceName;
        $filename .= "_series_" . $seriesFilmId;
        $filename .= "_season_" . $seasonNum;
        $filename .= ".html";
        $fp = fopen($filename, "w");
        fwrite($fp, $page);
        fclose($fp);
    }

    /**
     * TMDb Find response is an array of results by media type (movie, person,
     *  and tv show). Each result by type is an array.
     *   Response:
     *       [
     *           movie_results:
     *               [
     *                   0 [id, title, release_date...]
     *               ],
     *           person_results: [],
     *           tv_results: []
     *       ]
     * Only one array of results (like movie_results) should be non-empty and
     * that should only have one item (like movie_results[0]). This function
     * returns the non-empty array (movie_results, tv_results, tv_episode_results,
     * or null);
     * The $contentType param will be set based on the results.
     * 
     * TMDb Response: https://developers.themoviedb.org/3/find/find-by-id
     * 
     * Movie fields: poster_path, release_date, genre_ids, id, original_title, title, vote_average...
     * Series fields: poster_path, first_air_date, genre_ids, id, original_name, name, vote_average...
     * 
     * @param array  $response    The response from a "find" request to TmdbApi
     * @param string &$contentType This reference param is set based on the response if the results are found
     * 
     * @return array The "Find" data from TDMb for one item. Null if no result was found.
     */
    protected function getFindResultFromResponse($response, &$contentType = null)
    {
        $mediaResult = null;

        $movieResults = $response["movie_results"];
        $seriesResults = $response["tv_results"];
        $episodeResults = $response["tv_episode_results"];

        if (is_array($movieResults) && count($movieResults) > 0) {
            $contentType = Film::CONTENT_FILM;
            $mediaResult = $movieResults[0];
        }
        elseif (is_array($seriesResults) && count($seriesResults) > 0) {
            $contentType = Film::CONTENT_TV_SERIES;
            $mediaResult = $seriesResults[0];
        }
        elseif (is_array($episodeResults) && count($episodeResults) > 0) {
            $contentType = Film::CONTENT_TV_EPISODE;
            $mediaResult = $episodeResults[0];
        }

        return $mediaResult;
    }

    /**
     * TMDb Search response has some basic info at the top level (total results,
     * current page, and total pages). The rest is an array of results for each
     * match.
     *   Response:
     *       page
     *       total_results
     *       total_pages
     *       results
     *           [
     *               0: {...},
     *               1: {...},
     *               ...
     *           ]
     * 
     * The result matching exactly the title and year with be returned. 
     * 
     * TMDb Responses:
     * https://developers.themoviedb.org/3/search/search-movies
     * https://developers.themoviedb.org/3/search/search-tv-shows
     * https://developers.themoviedb.org/3/search/multi-search
     * 
     * Movie fields: poster_path, id, original_title, genre_ids, title, vote_average, release_date...
     * Series fields: 
     * 
     * @param array  $response   The response from a search request to TmdbApi
     * @param string $title
     * @param int    $year
     * @param string $searchType Options are Film::CONTENT_FILM, Film::CONTENT_TV_SERIES, "multi", null -> multi)
     * 
     * @return array Result matching the title and year. Null if no result was found.
     */
    protected function getSearchResultFromResponse($response, $title, $year, $searchType)
    {
        $matchingResult = null;

        $results = array();
        if ($response["total_results"] > 0) {
            $results = $response["results"];
        }
        
        $titleIndexes = array();
        $titleIndexes[Film::CONTENT_FILM] = "title";
        $titleIndexes[Film::CONTENT_TV_SERIES] = "name";
        $titleIndexes["multi"] = "title";

        $yearIndexes = array();
        $yearIndexes[Film::CONTENT_FILM] = "release_date";
        $yearIndexes[Film::CONTENT_TV_SERIES] = "first_air_date";
        $yearIndexes["multi"] = "release_date";
        

        foreach ($results as $result) {
            $resultTitle = array_value_by_key($titleIndexes[$searchType], $result);
            $resultYear = array_value_by_key($yearIndexes[$searchType], $result);
            if (!empty($resultYear)) {
                $resultYear = substr($resultYear, 0, 4);
            }

            if ($title == $resultTitle && $year == $resultYear) {
                $matchingResult = $result;
                break;
            }
        }

        return $matchingResult;
    }

    /**
     * uniqueNames for TMDb items in the RatingSync db use a 2 letter prefix to
     * the IDs used by TMDb. The prefix shows identifies the type of content.
     * This because IDs from TMDb are not unique. A movie and a TV series can
     * have the same ID. In the RatingSync db a item must have a unique
     * uniqueName within a source.
     * 
     * @return string A prefix based on the contentType + the sourceId
     */
    protected function getUniqueNameFromSourceId($sourceId, $contentType)
    {
        if (empty($contentType)) {
            throw new \InvalidArgumentException(__CLASS__."::".__FUNCTION__." param contentType must not be null");
        } elseif (!Film::validContentType($contentType)) {
            throw new \InvalidArgumentException(__CLASS__."::".__FUNCTION__." param contentType ($contentType) is invalid");
        }

        $prefix = static::ID_PREFIXES[$contentType];
        
        return $prefix . $sourceId;
    }

    /**
     * uniqueNames for TMDb items in the RatingSync db use a 2 letter prefix to
     * the IDs used by TMDb. The prefix shows identifies the type of content.
     * This because IDs from TMDb are not unique. A movie and a TV series can
     * have the same ID. In the RatingSync db a item must have a unique
     * uniqueName within a source.
     * 
     * @return string Remove the prefix of the uniqueName, leave the ID from TMDb
     */
    protected function getSourceIdFromUniqueName($uniqueName)
    {
        if (empty($uniqueName)) {
            throw new \InvalidArgumentException(__CLASS__."::".__FUNCTION__." param uniqueName must not be null");
        } elseif (!is_string($uniqueName)) {
            throw new \InvalidArgumentException(__CLASS__."::".__FUNCTION__." param uniqueName ($uniqueName) must be a string");
        }

        return substr($uniqueName, 2);
    }

    /**
     * Return TMDbApi uniqueName of the parent (TV series) of this episode 
     *
     * @param \RatingSync\Film $film A TV episode
     *
     * @return string parent uniqueName of an episode
     */
    protected function getEpisodeParentUniqueName($film)
    {
        $supportedContent = array(Film::CONTENT_FILM => 1, Film::CONTENT_TV_SERIES => 1, Film::CONTENT_TV_EPISODE => 1);
        if (! $film instanceof Film ) {
            throw new \InvalidArgumentException(__CLASS__."::".__FUNCTION__.' must be given a Film object');
        } elseif ( empty($film->getContentType()) || $film->getContentType() != Film::CONTENT_TV_EPISODE ) {
            throw new \InvalidArgumentException(__CLASS__."::".__FUNCTION__.' $film must be a TV Episode (contentType='.$film->getContentType().')');
        } elseif ( empty($film->getParentId()) ) {
            throw new \InvalidArgumentException(__CLASS__."::".__FUNCTION__.' $film parentId must not be empty');
        }
        
        $parentUniqueName = null;
        $parentFilm = Film::getFilmParentFromDb($film);
        if (!empty($parentFilm)) {
            $parentUniqueName = $parentFilm->getUniqueName($this->sourceName);
        }

        return $parentUniqueName;
    }

    protected function jsonIndex($attrName, $requestName)
    {
        $tmdbIndexes = array();

        if ($requestName == static::REQUEST_DETAIL_MOVIE) {
            $tmdbIndexes[Film::ATTR_IMDB_ID] = "imdb_id";
            $tmdbIndexes[Film::ATTR_PARENT_ID] = null;
            $tmdbIndexes[Film::ATTR_TITLE] = "title";
            $tmdbIndexes[Film::ATTR_YEAR] = "release_date";
            $tmdbIndexes[Film::ATTR_CONTENT_TYPE] = null;
            $tmdbIndexes[Film::ATTR_SEASON_COUNT] = null;
            $tmdbIndexes[Film::ATTR_SEASON_NUM] = null;
            $tmdbIndexes[Film::ATTR_EPISODE_NUM] = null;
            $tmdbIndexes[Film::ATTR_EPISODE_TITLE] = null;
            $tmdbIndexes[Film::ATTR_GENRES] = "genres";
            $tmdbIndexes[Film::ATTR_DIRECTORS] = null;
            $tmdbIndexes[Source::ATTR_UNIQUE_NAME] = "id";
            $tmdbIndexes[Source::ATTR_IMAGE] = "poster_path";
            $tmdbIndexes[Source::ATTR_CRITIC_SCORE] = null;
            $tmdbIndexes[Source::ATTR_USER_SCORE] = "vote_average";
        }
        elseif ($requestName == static::REQUEST_DETAIL_SERIES) {
            $tmdbIndexes[Film::ATTR_IMDB_ID] = "imdb_id";
            $tmdbIndexes[Film::ATTR_PARENT_ID] = null;
            $tmdbIndexes[Film::ATTR_TITLE] = "name";
            $tmdbIndexes[Film::ATTR_YEAR] = "first_air_date";
            $tmdbIndexes[Film::ATTR_CONTENT_TYPE] = null;
            $tmdbIndexes[Film::ATTR_SEASON_COUNT] = "number_of_seasons";
            $tmdbIndexes[Film::ATTR_SEASON_NUM] = null;
            $tmdbIndexes[Film::ATTR_EPISODE_NUM] = null;
            $tmdbIndexes[Film::ATTR_EPISODE_TITLE] = null;
            $tmdbIndexes[Film::ATTR_GENRES] = "genres";
            $tmdbIndexes[Film::ATTR_DIRECTORS] = null;
            $tmdbIndexes[Source::ATTR_UNIQUE_NAME] = "id";
            $tmdbIndexes[Source::ATTR_IMAGE] = "poster_path";
            $tmdbIndexes[Source::ATTR_CRITIC_SCORE] = null;
            $tmdbIndexes[Source::ATTR_USER_SCORE] = "vote_average";

        }
        elseif ($requestName == static::REQUEST_DETAIL_EPISODE) {
            $tmdbIndexes[Film::ATTR_IMDB_ID] = null;
            $tmdbIndexes[Film::ATTR_PARENT_ID] = null;
            $tmdbIndexes[Film::ATTR_TITLE] = null;
            $tmdbIndexes[Film::ATTR_YEAR] = "air_date";
            $tmdbIndexes[Film::ATTR_CONTENT_TYPE] = null;
            $tmdbIndexes[Film::ATTR_SEASON_COUNT] = null;
            $tmdbIndexes[Film::ATTR_SEASON_NUM] = "season_number";
            $tmdbIndexes[Film::ATTR_EPISODE_NUM] = "episode_number";
            $tmdbIndexes[Film::ATTR_EPISODE_TITLE] = "name";
            $tmdbIndexes[Film::ATTR_GENRES] = null;
            $tmdbIndexes[Film::ATTR_DIRECTORS] = null;
            $tmdbIndexes[Source::ATTR_UNIQUE_NAME] = "id";
            $tmdbIndexes[Source::ATTR_IMAGE] = "still_path";
            $tmdbIndexes[Source::ATTR_CRITIC_SCORE] = null;
            $tmdbIndexes[Source::ATTR_USER_SCORE] = "vote_average";
        }
        elseif ($requestName == static::REQUEST_FIND) {

        }
        elseif ($requestName == static::REQUEST_SEARCH_MOVIE) {

        }
        elseif ($requestName == static::REQUEST_SEARCH_SERIES) {

        }
        elseif ($requestName == static::REQUEST_SEARCH_MULTI) {

        }

        return array_value_by_key($attrName, $tmdbIndexes);
    }
    
    public function jsonValue($json, $attrName, $requestName)
    {
        $value = null;

        if ($attrName == Film::ATTR_GENRES) {
            $genres = null;
            $tmdbGenres = parent::jsonValue($json, $attrName, $requestName);
            if (is_array($tmdbGenres)) {
                $genres = array();
                foreach ($tmdbGenres as $genreArray) {
                    $genres[] = $genreArray["name"];
                }
            }

            $value = $genres;
        }
        else {
            $value = parent::jsonValue($json, $attrName, $requestName);
        }

        return $value;
    }
}

?>