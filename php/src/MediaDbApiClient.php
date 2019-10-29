<?php
/**
 * MediaDbApiClient base class
 */
namespace RatingSync;

require_once "ApiClient.php";

interface iMediaDbApiClient
{
    public function getFilmBySearch($searchTerms);
    public function getSeasonFromApi($seriesFilmId, $seasonNum, $refreshCache = 60);
    public function getFilmDetailFromApi($film, $overwrite = true, $refreshCache = Constants::USE_CACHE_NEVER);
}

/**
 * Request data from an API. Specifically a API to a media db like OMDbApi
 * or TMDbApi. Search for films, tv shows, and episodes and get details.
 */
abstract class MediaDbApiClient extends \RatingSync\ApiClient implements \RatingSync\iMediaDbApiClient
{
    const ATTR_API_REQUEST_NAME = "api_request";
    const REQUEST_DETAIL = "detail";

    protected $sourceName;
    protected $apiKey;
    protected $cacheFileExtension = "json";

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
    abstract protected function searchForUniqueName($film);
    abstract protected function printResultToLog($filmJson);

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
    public function getFilmDetailFromCache($film, $refreshCache = Constants::USE_CACHE_NEVER)
    {
        if (Constants::USE_CACHE_NEVER == $refreshCache) {
            return null;
        }

        $filename = $this->getFilmDetailCacheFilename($film);
        return $this->readFromCache($filename);
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
        if (!empty($film->getSeason($this->sourceName))) {
            $filename .= "_" . $film->getSeason($this->sourceName);
        }
        if (!empty($film->getEpisodeNumber($this->sourceName))) {
            $filename .= "_" . $film->getEpisodeNumber($this->sourceName);
        }
        $filename .= "." . $this->cacheFileExtension;

        return $filename;
    }

    /*
     * @param int|0 $refreshCache Use cache for files modified within mins from now. -1 means always use cache. Zero means never use cache.
     */
    public function getFilmDetailFromApi($film, $overwrite = true, $refreshCache = Constants::USE_CACHE_NEVER)
    {
        $filmJson = $this->getJsonFromApiForFilmDetail($film, $overwrite, $refreshCache);
        $this->populateFilmDetail($filmJson, $film, $overwrite);
    }

    /*
     * @param int|0 $refreshCache Use cache for files modified within mins from now. -1 means always use cache. Zero means never use cache.
     */
    public function getJsonFromApiForFilmDetail($film, $overwrite = true, $refreshCache = Constants::USE_CACHE_NEVER)
    {
        if (is_null($film) || !($film instanceof Film) ) {
            throw new \InvalidArgumentException('arg1 must be a Film object');
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
        $this->printResultToLog($filmJson);

        return $filmJson;
    }

    public function getFilmBySearch($searchTerms)
    {
        if (empty($searchTerms) || !is_array($searchTerms)) {
            throw new \InvalidArgumentException('Function '.__CLASS__.__FUNCTION__.' must have a searchTerms array');
        }
        
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
        $film->setUniqueName($uniqueName, $this->sourceName);
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
        
        return $film;
    }
    
    public function getSeasonFromApi($seriesFilmId, $seasonNum, $refreshCache = 60)
    {
        if (is_null($seriesFilmId) || !is_numeric($seriesFilmId) ) {
            throw new \InvalidArgumentException("\$seriesFilmId ($seriesFilmId) must be numeric");
        } else if (is_null($seasonNum) || !is_numeric($seasonNum) ) {
            throw new \InvalidArgumentException("\$seasonNum ($seasonNum) must be numeric");
        }

        $seasonJson = null;
        $validationMsg = null;

        $film = Film::getFilmFromDb($seriesFilmId);
        if (empty($film)) {
            return false;
        }
        $uniqueName = $film->getUniqueName($this->sourceName);

        $seasonPage = $this->getSeasonPageFromCache($seriesFilmId, $seasonNum, $refreshCache);
        if (empty($seasonPage) && !empty($uniqueName)) {
            try {
                $url = $this->buildUrlSeasonDetail($uniqueName, $seasonNum);
                $response = $this->apiRequest($url, null, false, false);
                $seasonJson =  json_decode($response, true);
                $validationMsg = $this->validateResponseSeasonDetail($seasonJson);
        
                if ($validationMsg == "Success") {
                    $this->cacheSeasonPage($response, $seriesFilmId, $seasonNum);
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
        $filename = Constants::cacheFilePath() . $this->sourceName;
        $filename .= "_series_" . $seriesFilmId;
        $filename .= "_season_" . $seasonNum;
        $filename .= ".html";
        $fp = fopen($filename, "w");
        fwrite($fp, $page);
        fclose($fp);
    }
}

?>