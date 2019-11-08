<?php
/**
 * MediaDbApiClient base class
 */
namespace RatingSync;

require_once "ApiClient.php";
require_once "Season.php";

interface iMediaDbApiClient
{
    public function getFilmBySearch($searchTerms);
    public function getSeasonFromApi($seriesFilmId, $seasonNum, $refreshCache = 60);
    public function getFilmDetailFromApi($film, $overwrite = true, $refreshCache = 60);
    public function getSourceName();
    public function getUniqueNameFromSourceId($sourceId, $contentType = null);
}

/**
 * Request data from an API. Specifically a API to a media db like OMDbApi
 * or TMDbApi. Search for films, tv shows, and episodes and get details.
 */
abstract class MediaDbApiClient extends \RatingSync\ApiClient implements \RatingSync\iMediaDbApiClient
{
    const ATTR_API_REQUEST_NAME = "api_request";
    const REQUEST_DETAIL = "detail";
    const REQUEST_DETAIL_MOVIE = "detail_movie";
    const REQUEST_DETAIL_SERIES = "detail_tv_series";
    const REQUEST_DETAIL_EPISODE = "detail_tv_episode";
    const REQUEST_DETAIL_SEASON = "detail_tv_season";

    protected $sourceName;
    protected $apiKey;
    protected $cacheFileExtension = "json";
    protected $defaultRefreshCache = 60; // minutes

    // Abstract function inherited by ApiClient
    abstract protected function jsonIndex($attrName, $requestName);

    /**
     * Validate that the child constructor is initiated
     *
     * @return bool true for valid, false otherwise
     */
    protected function validateAfterConstructor()
    {
        if (empty($this->sourceName)) {
            return false;
        }
        return true;
    }

    /**
     * Return the API URL for the detail of a film.  
     *
     * @param \RatingSync\Film $film Film the URL goes to
     *
     * @return string API URL to film detail
     */
    abstract protected function buildUrlFilmDetail($film);

    /**
     * Return the API URL for the detail of a TV season.  
     *
     * @param \RatingSync\Film $film Film the URL goes to
     *
     * @return string API URL to film detail
     */
    abstract protected function buildUrlSeasonDetail($uniqueName, $seasonNum);
    abstract protected function validateResponseSeasonDetail($seasonJson);

    abstract protected function populateFilmDetail($response, $film, $overwrite = true);
    abstract protected function populateSeason($seasonJson, $seriesId);
    abstract protected function searchForUniqueName($film);
    abstract protected function printResultToLog($filmJson, $requestName, $contentType);

    public function getSourceName()
    {
        return $this->sourceName;
    }

    /**
     * Return a cached api response if the cached file is fresh enough. The
     * $refreshCache param shows if it is fresh enough. If the file is out of
     * date return null.
     *
     * @param \RatingSync\Film $film         Film needed detail for
     * @param int|0            $refreshCache Use cache for files modified within mins from now. -1 means always use cache. Zero means never use cache.
     *
     * @return string File as a string. Null if the use cache is not used.
     */
    public function getFilmDetailFromCache($film, $refreshCache = null)
    {
        if (is_null($refreshCache)) {
            $refreshCache = $this->defaultRefreshCache;
        }

        if (Constants::USE_CACHE_NEVER == $refreshCache) {
            return null;
        }

        $filename = $this->getFilmDetailCacheFilename($film);
        return $this->readFromCache($filename, $refreshCache);
    }

    /**
     * Cache a film detail response in a local file
     *
     * @param string           $apiResult File as a string
     * @param \RatingSync\Film $film Film data about the page
     */
    public function cacheFilmDetail($apiResult, $film)
    {
        $filename = $this->getFilmDetailCacheFilename($film);
        $this->writeToCache($apiResult, $filename);
    }

    protected function getFilmDetailCacheFilename($film)
    {
        $filename = $this->sourceName . "_film_";
        $filename .= $film->getUniqueName($this->sourceName);
        if (!empty($film->getSeason())) {
            $filename .= "_" . $film->getSeason($this->sourceName);
        }
        if (!empty($film->getEpisodeNumber())) {
            $filename .= "_" . $film->getEpisodeNumber($this->sourceName);
        }
        $filename .= "." . $this->cacheFileExtension;

        return $filename;
    }

    /*
     * @param int|0 $refreshCache Use cache for files modified within mins from now. -1 means always use cache. Zero means never use cache.
     */
    public function getFilmDetailFromApi($film, $overwrite = true, $refreshCache = null)
    {
        if (is_null($refreshCache)) {
            $refreshCache = $this->defaultRefreshCache;
        }
        
        $filmJson = $this->getJsonFromApiForFilmDetail($film, $overwrite, $refreshCache);
        $this->populateFilmDetail($filmJson, $film, $overwrite);
    }

    /*
     * @param int|0 $refreshCache Use cache for files modified within mins from now. -1 means always use cache. Zero means never use cache.
     */
    public function getJsonFromApiForFilmDetail($film, $overwrite = true, $refreshCache = null)
    {
        if (is_null($film) || !($film instanceof Film) ) {
            throw new \InvalidArgumentException('arg1 must be a Film object');
        }

        if (is_null($refreshCache)) {
            $refreshCache = $this->defaultRefreshCache;
        }
        
        $filmJson = null;
        
        // Use it from cache if it is fresh
        $response = $this->getFilmDetailFromCache($film, $refreshCache);
        if ( !empty($response) ) {
            $filmJson = json_decode($response, true);
        }

        if (empty($filmJson)) {
            // Go to the API

            // If there is NO uniqueName, use a search with other attrs to get
            // at least a uniqueName
            $uniqueName = $film->getUniqueName($this->sourceName);
            if (empty($uniqueName)) {
                $uniqueName = $this->searchForUniqueName($film);
                $film->setUniqueName($uniqueName, $this->sourceName);
            }

            // If there is uniqueName at this point, go for full detail
            if (!empty($uniqueName)) {
                try {
                    $response = $this->apiRequest($this->buildUrlFilmDetail($film), null, false, false);
                    if ( !empty($response) ) {
                        $filmJson = json_decode($response, true);
                    }
                    if ( !empty($filmJson) &&  !empty($filmJson["id"])) {
                        $filmJson[self::ATTR_API_REQUEST_NAME] = self::REQUEST_DETAIL;
                        $response = json_encode($filmJson);
                        $this->cacheFilmDetail($response, $film);
                    }
                } catch (\Exception $e) {
                    logDebug($e, __FUNCTION__." ".__LINE__);
                    throw $e;
                }
            }
        }
        
        if ( empty($filmJson) || empty($filmJson["id"]) ) {
            $errorMsg = $this->sourceName . " request failed. Title=".$film->getTitle();
            $errorMsg .= ", Episode Title=".$film->getEpisodeTitle();
            $errorMsg .= ", Year=" . $film->getYear();
            $errorMsg .= ", UniqueName=" . $film->getUniqueName($this->sourceName);

            if (!empty($filmJson)) {
                $errorMsg .= "\nJSON response...\n$filmJson";
            }

            logDebug($errorMsg, __CLASS__."::".__FUNCTION__." ".__LINE__, true, $filmJson);
            throw new \Exception($this->sourceName . ' film detail request failed');
        }
        $this->printResultToLog($filmJson, self::REQUEST_DETAIL, $film->getContentType());

        return $filmJson;
    }

    public function getFilmBySearch($searchTerms)
    {
        if (empty($searchTerms) || !is_array($searchTerms)) {
            throw new \InvalidArgumentException('Function '.__CLASS__.__FUNCTION__.' must have a searchTerms array');
        }
        
        $parentId = array_value_by_key("parentId", $searchTerms);
        $uniqueName = array_value_by_key("uniqueName", $searchTerms);
        $title = array_value_by_key("title", $searchTerms);
        $year = array_value_by_key("year", $searchTerms);
        $contentType = array_value_by_key("contentType", $searchTerms);
        $season = array_value_by_key("season", $searchTerms);
        $episodeNumber = array_value_by_key("episodeNumber", $searchTerms);
        $episodeTitle = array_value_by_key("episodeTitle", $searchTerms);
        
        if (empty($uniqueName) && (empty($title) || empty($year))) {
            throw new \InvalidArgumentException('Function '.__FUNCTION__.' searchTerms must have uniqueName or (title and year)');
        }

        $film = new Film();
        $imdbId = null;
        $film->setParentId($parentId);
        if ($this->isThisIdFromImdb($uniqueName)) {
            $imdbId = $uniqueName;
            $film->setUniqueName($imdbId, Constants::SOURCE_IMDB);
        } else {
            $film->setUniqueName($uniqueName, $this->sourceName);
        }
        $film->setTitle($title);
        $film->setYear($year);
        $film->setContentType($contentType);
        $film->setSeason($season);
        $film->setEpisodeNumber($episodeNumber);
        $film->setEpisodeTitle($episodeTitle);

        try {
            $this->getFilmDetailFromApi($film, true, Constants::USE_CACHE_ALWAYS);
        } catch (\Exception $e) {
            logDebug("Exception " . $e->getCode() . " " . $e->getMessage(), __CLASS__."::".__FUNCTION__." ".__LINE__);
            $film = null;
        }

        // getFilmDetailFromApi over writes the IMDb source
        if (!empty($imdbId)) {
            $film->setUniqueName($imdbId, Constants::SOURCE_IMDB);
        }
        
        return $film;
    }
    
    public function getSeasonFromApi($seriesFilmId, $seasonNum, $refreshCache = null)
    {
        if (is_null($seriesFilmId) || !is_numeric($seriesFilmId) ) {
            throw new \InvalidArgumentException("\$seriesFilmId ($seriesFilmId) must be numeric");
        } else if (is_null($seasonNum) || !is_numeric($seasonNum) ) {
            throw new \InvalidArgumentException("\$seasonNum ($seasonNum) must be numeric");
        }

        if (is_null($refreshCache)) {
            $refreshCache = $this->defaultRefreshCache;
        }
        
        $seasonJson = null;
        $validationMsg = null;

        $film = Film::getFilmFromDb($seriesFilmId);
        if (empty($film)) {
            return false;
        }
        $uniqueName = $film->getUniqueName($this->sourceName);

        $response = $this->getSeasonFromCache($seriesFilmId, $seasonNum, $refreshCache);
        if (empty($response) && !empty($uniqueName)) {
            try {
                $url = $this->buildUrlSeasonDetail($uniqueName, $seasonNum);
                $response = $this->apiRequest($url, null, false, false);
                $seasonJson =  json_decode($response, true);
                $validationMsg = $this->validateResponseSeasonDetail($seasonJson);
        
                if ($validationMsg == "Success") {
                    $this->cacheSeason($response, $seriesFilmId, $seasonNum);
                }
                else {
                    $errorMsg = "TMDb API 'Season Detail' request failed.";
                    $errorMsg .= " Title=".$film->getTitle();
                    $errorMsg .= ", Season=" . $seasonNum;
                    $errorMsg .= ", UniqueName=" . $uniqueName;
                    $errorMsg .= "\n$validationMsg";
                    logDebug($errorMsg, __CLASS__."::".__FUNCTION__." ".__LINE__);
                    throw new \Exception("TMDbApi season failed. $validationMsg");
                }
            } catch (\Exception $e) {
                logDebug($e, __FUNCTION__." ".__LINE__);
                throw $e;
            }
        } else {
            $seasonJson =  json_decode($response, true);
        }

        $season = $this->populateSeason($seasonJson, $seriesFilmId);

        return $season;
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
    public function getSeasonFromCache($seriesFilmId, $seasonNum, $refreshCache = null)
    {
        if (is_null($refreshCache)) {
            $refreshCache = $this->defaultRefreshCache;
        }
        
        if (Constants::USE_CACHE_NEVER == $refreshCache) {
            return null;
        }

        $filename = $this->getSeasonCacheFilename($seriesFilmId, $seasonNum);
        return $this->readFromCache($filename, $refreshCache);
    }

    /**
     * Cache a season result in json in a local file
     *
     * @param string    $apiResult      File as a string
     * @param int       $seriesFilmId   DB film_id of the series
     * @param int       $seasonNum      Season number
     */
    public function cacheSeason($apiResult, $seriesFilmId, $seasonNum)
    {
        $filename = $this->getSeasonCacheFilename($seriesFilmId, $seasonNum);
        $this->writeToCache($apiResult, $filename);
    }

    protected function getSeasonCacheFilename($seriesFilmId, $seasonNum)
    {
        $filename = $this->sourceName;
        $filename .= "_series_" . $seriesFilmId;
        $filename .= "_season_" . $seasonNum;
        $filename .= "." . $this->cacheFileExtension;

        return $filename;
    }

    public function getFilmFromDb($sourceId, $contentType = null, $username = null)
    {
        $sourceName = $this->sourceName;
        if ($this->isThisIdFromImdb($sourceId)) {
            $sourceName = Constants::SOURCE_IMDB;
        }

        $uniqueName = $sourceId;
        $film = Film::getFilmFromDbByUniqueName($uniqueName, $sourceName, $username);

        return $film;
    }

    protected function isThisIdFromImdb($sourceId)
    {
        $isThisIdFromImdb = false;
        if (preg_match('/(^tt\d{7}\d*$)/i', $sourceId, $matches)) {
            $isThisIdFromImdb = true;
        }

        return $isThisIdFromImdb;
    }

    /**
     * SourceId from some sources (like TMDb) are not uniqueName. They are
     * unique within a content type, but for example, a TV series might use
     * the same sourceId as Movie. Each API client offers this function to
     * give the uniqueName based the sourceId and contentType.
     * 
     * @return string A prefix based on the contentType + the sourceId
     */
    public function getUniqueNameFromSourceId($sourceId, $contentType = null)
    {
        return $sourceId;
    }
}

?>