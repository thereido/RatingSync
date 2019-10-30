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
    const REQUEST_CREDITS = "credits";
    const ATTR_CREDITS_CREDITS = "credits_credits";
    const ATTR_CREDITS_CAST = "credits_cast";
    const ATTR_CREDITS_CREW = "credits_crew";
    const ATTR_CONTENT_TYPE = "search_content_type";

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

    protected function buildUrlSeasonDetail($uniqueName, $seasonNum)
    {
        if ( empty($uniqueName) ) {
            throw new \InvalidArgumentException(__CLASS__."::".__FUNCTION__.' $uniqueName must not be empty');
        } elseif ( empty($seasonNum) ) {
            throw new \InvalidArgumentException(__CLASS__."::".__FUNCTION__.' $seasonNum must not be empty');
        }

        $url = static::BASE_API_URL;
        $url .= "/tv/" . $this->getSourceIdFromUniqueName($uniqueName);
        $url .= "/season/$seasonNum";
        $url .= "?api_key=" . Constants::TMDB_API_KEY;

        return $url;
    }

    protected function validateResponseSeasonDetail($json)
    {
        $errorMsg = $this->getErrorMessageFromResponse($json);

        if (empty($errorMsg) && !array_key_exists("id", $json)) {
            $errorMsg = "Response appears to be invalid because there is no 'id' in the response.";
        }

        if (empty($errorMsg)) {
            return "Success";
        } else {
            return $errorMsg;
        }
    }

    protected function validateResponseCredits($json)
    {
        $errorMsg = $this->getErrorMessageFromResponse($json);

        if (empty($errorMsg) && !array_key_exists("id", $json)) {
            $errorMsg = "Response appears to be invalid because there is no 'id' in the response.";
        }

        if (empty($errorMsg)) {
            return "Success";
        } else {
            return $errorMsg;
        }
    }

    protected function getErrorMessageFromResponse($json)
    {
        $errorMsg = null;
        if (empty($json) && !is_array($json)) {
            $errorMsg = "Response from json_decode() failed";
        } elseif (array_key_exists("status_code", $json)) {
            $statusCode = $json["status_code"];
            $statusMsg = $json["status_message"];
            $errorMsg = "Status from the API... Status code: $statusCode, Status message: $statusMsg";
        }

        return $errorMsg;
    }

    /**
     * Search the api with the attr available in this $film. If there is a
     * single result, return the uniqueName. Supports Movie and TV Series. Does
     * NOT support TV Episode.
     * 
     * NOTICE: If $film contentType is empty, it will be set when results are
     *         found.
     * 
     * Attr combinations supported for Movie & TV Series
     *   - IMDb ID
     *   - TMDb ID
     *   - Title, Year, contentType
     *   - Title, Year
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
            $response = $this->apiRequest($url);
            $json = json_decode($response, true);

            $contentTypeFound = null;
            $result = $this->getFindResultFromResponse($json, $contentTypeFound);
            if (!empty($result)) {
                if (empty($contentType)) {
                    $contentType = $contentTypeFound;
                    $film->setContentType($contentType);
                }

                // Don't use the result unless it matches the contentType expected
                if ($contentType == $contentTypeFound) {
                    $uniqueName = $this->getUniqueNameFromSourceId($result["id"], $contentType);
                }
            }
        }

        // We have contentType, title and year
        // Do a TV Series or Movie search by title and year
        if (empty($uniqueName) && !empty($contentType) && !empty($title) && !empty($year)) {
            $searchUrlType = null;
            $yearParamName = null;
            $requestName = null;
            $contentTypeSupported = false;
            if ($contentType == Film::CONTENT_FILM) {
                $searchUrlType = "movie";
                $yearParamName = "year";
                $requestName = static::REQUEST_SEARCH_MOVIE;
                $contentTypeSupported = true;
            } else if ($contentType == Film::CONTENT_TV_SERIES) {
                $searchUrlType = "tv";
                $yearParamName = "first_air_date_year";
                $requestName = static::REQUEST_SEARCH_SERIES;
                $contentTypeSupported = true;
            } else {
                // Only movies and tv shows are supported (no episodes or seasons)
            }

            if ($contentTypeSupported) {
                $url = "/search/$searchUrlType";
                $url .= "?query=" . htmlspecialchars($title);
                $url .= "&".$yearParamName."=" . htmlspecialchars($year);
                $url .= "&api_key=" . $this->apiKey;
                $response = $this->apiRequest($url);
                $json = json_decode($response, true);

                $jsonResult = $this->getSearchResultFromResponse($json, $title, $year, $requestName);
                if (!empty($jsonResult)) {
                    $uniqueName = $this->getUniqueNameFromSourceId($jsonResult["id"], $contentType);
                }
            }
        }

        // We have title and year, but no contentType
        // Do a multi search by title and year
        if (empty($uniqueName) && empty($contentType) && !empty($title) && !empty($year)) {
            $url = "/search/multi";
            $url .= "?query=" . htmlspecialchars($title);
            $url .= "&api_key=" . $this->apiKey;
            $response = $this->apiRequest($url);
            $json = json_decode($response, true);

            $jsonResult = $this->getSearchResultFromResponse($json, $title, $year, self::REQUEST_SEARCH_MULTI);
            if (!empty($jsonResult)) {
                $sourceContentType = $this->jsonValue($jsonResult, self::ATTR_CONTENT_TYPE, self::REQUEST_SEARCH_MULTI);
                if ($sourceContentType == "movie") {
                    $contentType = Film::CONTENT_FILM;
                } elseif ($sourceContentType == "tv") {
                    $contentType = Film::CONTENT_TV_SERIES;
                }

                $uniqueName = $this->getUniqueNameFromSourceId($jsonResult["id"], $contentType);
                $film->setContentType($contentType);
            }
        }

        return $uniqueName;
    }
    
    /**
     * Get detail from the TMDb API and populate the $film param.
     * 
     * Responses from the TMDb API for movie, tv and episode. These api
     * responses are not complete, just the fields we currently use.
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
     * 
     * TV Series - https://api.themoviedb.org/3/tv/1399?api_key={api_key}
     * {
     *   "first_air_date":	"2011-04-17",
     *   "genres": [
     *     {
     *       "id": 18,
     *       "name": "Drama"
     *     }
     *   ],
     *   "id":	1399,
     *   "last_air_date":	"2019-05-19"
     *   "name":	"Game of Thrones",
     *   "number_of_seasons":	8,
     *   "original_name":	"Game of Thrones",
     *   "poster_path":	"/u3bZgnGQ9T01sWNhyveQz0wH0Hl.jpg",
     *   "status":	"Ended",
     *   "vote_average":	8.1
     * }
     * 
     * TV Episode - https://api.themoviedb.org/3/tv/1399/season/2/episode/4?api_key={api_key}
     * {
     *   "air_date":	"2012-04-22",
     *   "episode_number":	4,
     *   "name":	"Garden of Bones",
     *   "id":	63069,
     *   "season_number":	2,
     *   "still_path":	"/4j2j97GFao2NX4uAtMbr0Qhx2K2.jpg",
     *   "vote_average":	8.216
     * }
     */
    public function populateFilmDetail($json, $film, $overwrite = true)
    {
        if (is_null($film) || !($film instanceof Film) ) {
            throw new \InvalidArgumentException("\$film param must be a Film object");
        } elseif (empty($film->getContentType())) {
            throw new \InvalidArgumentException("contentType must not be empty in the \$film param");
        } elseif ($film->getContentType() == Film::CONTENT_TV_EPISODE) {
            if (empty($film->getParentId())) {
                throw new \InvalidArgumentException("parentId must not be empty in the \$film param when \$film is a TV episode");
            }
        } elseif (!is_array($json)) {
            throw new \InvalidArgumentException("\$json param ($json) must be an array");
        }

        $contentType = $film->getContentType();

        // This function is used by different kinds of request, which affects
        // the format of the responses. Before calling this function someone
        // adds an attr to the response to show which request this response 
        // goes with.
        $requestName = array_value_by_key(self::ATTR_API_REQUEST_NAME, $json);
        if ($requestName == self::REQUEST_DETAIL) {
            if ($contentType == Film::CONTENT_FILM) {
                $requestName = self::REQUEST_DETAIL_MOVIE;
            }
            elseif ($contentType == Film::CONTENT_TV_SERIES) {
                $requestName = self::REQUEST_DETAIL_SERIES;
            }
            elseif ($contentType == Film::CONTENT_TV_EPISODE) {
                $requestName = self::REQUEST_DETAIL_EPISODE;
            }
        }

        // Get values from the API result
                        // Film object attrs
        $title =        $this->jsonValue($json, Film::ATTR_TITLE, $requestName);
        $year =         $this->jsonValue($json, Film::ATTR_YEAR, $requestName);
        $parentId =     $film->getParentId();
        $seasonCount =  $this->jsonValue($json, Film::ATTR_SEASON_COUNT, $requestName);
        $season =       $this->jsonValue($json, Film::ATTR_SEASON_NUM, $requestName);
        $episodeNum =   $this->jsonValue($json, Film::ATTR_EPISODE_NUM, $requestName);
        $episodeTitle = $this->jsonValue($json, Film::ATTR_EPISODE_TITLE, $requestName);
        $genres =       $this->jsonValue($json, Film::ATTR_GENRES, $requestName);
        $directors =    $this->jsonValue($json, Film::ATTR_DIRECTORS, $requestName);
                        // Source object attrs
        $sourceId =     $this->jsonValue($json, Source::ATTR_UNIQUE_NAME, $requestName);
        $uniqueName =   $this->getUniqueNameFromSourceId($sourceId, $contentType);
        $criticScore =  $this->jsonValue($json, Source::ATTR_CRITIC_SCORE, $requestName); // Not available
        $userScore =    $this->jsonValue($json, Source::ATTR_USER_SCORE, $requestName);
        $image =        $this->jsonValue($json, Source::ATTR_IMAGE, $requestName);
                        // IMDb attrs
        $imdbId =       $this->jsonValue($json, Film::ATTR_IMDB_ID, $requestName);
        $imdbUserScore = null;
        if (!empty($imdbId)) {
            $imdbUserScore = $userScore;
        }

        // Some content types need special steps to get certain attrs
        if ($contentType == Film::CONTENT_FILM || $contentType == Film::CONTENT_TV_SERIES) {
            // Get directors
            $creditsJson = $this->getCreditsFromApi($film);
            $directors = $this->jsonValue($creditsJson, Film::ATTR_DIRECTORS, self::REQUEST_CREDITS);
        }
        elseif ($contentType == Film::CONTENT_TV_EPISODE) {
            // For a episode, "title" is the series' title. Episode title has
            // it's own attr (episodeTitle). The request does not give us the
            // series' title, so get it from the db
            $seriesFilm = Film::getFilmParentFromDb($film);
            if (!empty($seriesFilm)) {
                $title = $seriesFilm->getTitle();
            }
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
        if ($overwrite || is_null($existingUniqueName)) { $film->setUniqueName($uniqueName, $this->sourceName); }
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

        if ($overwrite || $existingGenreCount == 0) {
            $film->removeAllGenres();
            if (is_array($genres)) {
                foreach ($genres as $genre) {
                    $film->addGenre($genre);
                }
            }
        }

        if ($overwrite || $existingDirectorCount == 0) {
            $film->removeAllDirectors();
            if (is_array($directors)) {
                foreach ($directors as $director) {
                    $film->addDirector($director);
                }
            }
        }

        // Copy data from TMDb to IMDb
        $existingIMDbUniqueName = $film->getUniqueName(Constants::SOURCE_IMDB);
        $existingIMDbUserScore = $film->getUserScore(Constants::SOURCE_IMDB);
        if ($overwrite || is_null($existingIMDbUniqueName)) { $film->setUniqueName($imdbId, Constants::SOURCE_IMDB); }
        if ($overwrite || is_null($existingIMDbUserScore)) { $film->setUserScore($imdbUserScore, Constants::SOURCE_IMDB); }
    }

    protected function printResultToLog($filmJson) {
        $title = array_value_by_key("Title", $filmJson);
        $year = array_value_by_key("Year", $filmJson);
        $msg = "TMDb API result: $title ($year)";
        logDebug($msg, __CLASS__."::".__FUNCTION__." ".__LINE__);
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
    protected function getSearchResultFromResponse($response, $title, $year, $requestName)
    {
        $matchingResult = null;

        $results = array();
        if ($response["total_results"] > 0) {
            $results = $response["results"];
        }        

        foreach ($results as $result) {
            $attrNameTitle = Film::ATTR_TITLE;
            $attrNameYear = Film::ATTR_YEAR;
            if ($requestName == static::REQUEST_SEARCH_MULTI) {
                $resultMediaType = $this->jsonValue($result, self::ATTR_CONTENT_TYPE, $requestName);
                if ($resultMediaType == "movie") {
                    $attrNameTitle = $attrNameTitle . "_" . Film::CONTENT_FILM;
                    $attrNameYear = $attrNameYear . "_" . Film::CONTENT_FILM;
                } elseif ($resultMediaType == "tv") {
                    $attrNameTitle = $attrNameTitle . "_" . Film::CONTENT_TV_SERIES;
                    $attrNameYear = $attrNameYear . "_" . Film::CONTENT_TV_SERIES;
                }
            }

            $resultTitle = $this->jsonValue($result, $attrNameTitle, $requestName);
            $resultYear = $this->jsonValue($result, $attrNameYear, $requestName);

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
    
    /**
     * Return JSON from a request to the API to get credits for a movie. This
     * only supports movies (no TV series or episodes).
     */
    public function getCreditsFromApi($film)
    {
        if (! $film instanceof Film ) {
            throw new \InvalidArgumentException(__CLASS__."::".__FUNCTION__." must be given a Film object");
        } elseif ( empty($film->getContentType()) || !in_array($film->getContentType(), array(Film::CONTENT_FILM, Film::CONTENT_TV_SERIES)) ) {
            throw new \InvalidArgumentException(__CLASS__."::".__FUNCTION__." \$film must be a movie or tv series (contentType=".$film->getContentType().")");
        } elseif ( empty($film->getUniqueName($this->sourceName)) ) {
            throw new \InvalidArgumentException(__CLASS__."::".__FUNCTION__." \$film must have a uniqueName (".$film->getUniqueName($this->sourceName).") for ".$this->sourceName);
        }
        
        $uniqueName = $film->getUniqueName($this->sourceName);
        $contentType = $film->getContentType();
        $validationMsg = "";
        $url = "/";
        if ($contentType == Film::CONTENT_FILM) {
            $url .= "movie/";
        } elseif ($contentType == Film::CONTENT_TV_SERIES) {
            $url .= "tv/";
        }
        $url .= $this->getSourceIdFromUniqueName($uniqueName);
        $url .= "/credits";
        $url .= "?api_key=" . Constants::TMDB_API_KEY;

        try {

            $response = $this->apiRequest($url);
            $json = json_decode($response, true);
            $validationMsg = $this->validateResponseCredits($json);
        
            if (empty($json) || !is_array($json) || $validationMsg != "Success") {
                throw new \Exception($validationMsg);
            }

        } catch (\Exception $e) {
            $errorMsg = "TMDb API 'Credits' request failed. $url";
            $errorMsg .= "\n$e";
            logDebug($errorMsg, __CLASS__."::".__FUNCTION__." ".__LINE__);
            throw $e;
        }

        return $json;
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
            $tmdbIndexes[self::ATTR_CREDITS_CREW] = null;
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
            $tmdbIndexes[self::ATTR_CREDITS_CREW] = null;

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
            $tmdbIndexes[self::ATTR_CREDITS_CREW] = "crew";
        }
        elseif ($requestName == static::REQUEST_FIND) {

        }
        elseif ($requestName == static::REQUEST_SEARCH_MOVIE) {
            $tmdbIndexes[self::ATTR_CONTENT_TYPE] = "media_type";
            $tmdbIndexes[Film::ATTR_TITLE] = "title";
            $tmdbIndexes[Film::ATTR_YEAR] = "release_date";
        }
        elseif ($requestName == static::REQUEST_SEARCH_SERIES) {
            $tmdbIndexes[self::ATTR_CONTENT_TYPE] = "media_type";
            $tmdbIndexes[Film::ATTR_TITLE] = "name";
            $tmdbIndexes[Film::ATTR_YEAR] = "first_air_date";
        }
        elseif ($requestName == static::REQUEST_SEARCH_MULTI) {
            $tmdbIndexes[self::ATTR_CONTENT_TYPE] = "media_type";
            $tmdbIndexes[Film::ATTR_TITLE . "_" . Film::CONTENT_FILM] = "title";
            $tmdbIndexes[Film::ATTR_TITLE . "_" . Film::CONTENT_TV_SERIES] = "name";
            $tmdbIndexes[Film::ATTR_YEAR . "_" . Film::CONTENT_FILM] = "release_date";
            $tmdbIndexes[Film::ATTR_YEAR . "_" . Film::CONTENT_TV_SERIES] = "first_air_date";
        }
        elseif ($requestName == static::REQUEST_CREDITS) {
            $tmdbIndexes[Film::ATTR_DIRECTORS] = null;
            $tmdbIndexes[self::ATTR_CREDITS_CREDITS] = "credits";
            $tmdbIndexes[self::ATTR_CREDITS_CAST] = "cast";
            $tmdbIndexes[self::ATTR_CREDITS_CREW] = "crew";
        }

        $tmdbIndexes[self::ATTR_API_REQUEST_NAME] = self::ATTR_API_REQUEST_NAME;

        return array_value_by_key($attrName, $tmdbIndexes);
    }
    
    public function jsonValue($json, $attrName, $requestName)
    {
        $value = null;

        if ($attrName == Film::ATTR_YEAR) {
            $dateStr = parent::jsonValue($json, $attrName, self::REQUEST_DETAIL_MOVIE);
            if (empty($dateStr)) { $dateStr = parent::jsonValue($json, $attrName, self::REQUEST_DETAIL_SERIES); }
            if (empty($dateStr)) { $dateStr = parent::jsonValue($json, $attrName, self::REQUEST_DETAIL_EPISODE); }
            if (empty($dateStr)) { $dateStr = parent::jsonValue($json, $attrName, self::REQUEST_FIND); }
            if (empty($dateStr)) { $dateStr = parent::jsonValue($json, $attrName, self::REQUEST_SEARCH_MOVIE); }
            if (empty($dateStr)) { $dateStr = parent::jsonValue($json, $attrName, self::REQUEST_SEARCH_SERIES); }
            if (empty($dateStr)) { $dateStr = parent::jsonValue($json, $attrName, self::REQUEST_SEARCH_MULTI); }

            if (!empty($dateStr)) {
                $value = substr($dateStr, 0, 4);
            }
        }
        elseif ($attrName == Film::ATTR_GENRES) {
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
        elseif ($attrName == Film::ATTR_DIRECTORS) {
            $directors = null;
            $crew = parent::jsonValue($json, self::ATTR_CREDITS_CREW, $requestName);

            if (!empty($crew)) {
                foreach ($crew as $crewMember) {
                    if ($crewMember["job"] == "Director") {
                        if (is_null($directors)) {
                            $directors = array();
                        }
                        $directors[] = $crewMember["name"];
                    }
                }
            }

            $value = $directors;
        }
        else {
            $value = parent::jsonValue($json, $attrName, $requestName);
        }

        return $value;
    }
}

?>