<?php
/**
 * MediaDbApiClient base class
 */
namespace RatingSync;

require_once "ApiClient.php";

interface iMediaDbApiClient
{
    public function getFilmBySearch($searchTerms);
    public function getSeason($seriesFilmId, $seasonNum, $refreshCache = 60);
    public function getFilmDetailFromApi($film, $overwrite = true, $refreshCache = Constants::USE_CACHE_NEVER);
}

/**
 * Request data from an API. Specifically a API to a media db like OMDbApi
 * or TMDbApi. Search for films, tv shows, and episodes and get details.
 */
abstract class MediaDbApiClient extends \RatingSync\ApiClient implements \RatingSync\iMediaDbApiClient
{
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

    abstract protected function populateFilmDetail($response, $film, $overwrite);
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
        if (empty($uniqueName)) {
            $film->setTitle($title);
            $film->setYear($year);
            $film->setContentType($contentType);
            $film->setSeason($season);
            $film->setEpisodeNumber($episodeNumber);
            $film->setEpisodeTitle($episodeTitle);
        } else {
            $film->setUniqueName($uniqueName, $this->sourceName);
        }

        try {
            $this->getFilmDetailFromApi($film, true, Constants::USE_CACHE_ALWAYS);
        } catch (\Exception $e) {
            logDebug("Exception " . $e->getCode() . " " . $e->getMessage(), __CLASS__."::".__FUNCTION__." ".__LINE__);
            $film = null;
        }
        
        return $film;
    }

    /**
     * Search api for a unique film. Set attrs in the $film param. If a
     * unique film is found, then the child class should at least set
     * uniqueName. Also set any other attrs available in the search response.
     *
     * @param \RatingSync\Film $film
     *
     * @return boolean success/failure
     */
    public function searchApiForUniqueFilm($film)
    {
        return false;
    }
}

?>