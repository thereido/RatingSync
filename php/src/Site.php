<?php
/**
 * Site class. A source of rating account, usually a website like
 * IMDb or Jinni.
 */
namespace RatingSync;

require_once "Constants.php";

/**
 * Communicate to/from the website
 * - Search for films and tv shows
 * - Get details for each and rate it
 * - Export/Import ratings.
 */
abstract class Site
{
    public $http;
    protected $username;
    protected $sourceName;
    protected $dateFormat = "n/j/y";

    public function __construct($username)
    {
        if (! (is_string($username) && 0 < strlen($username)) ) {
            throw new \InvalidArgumentException('$username must be non-empty');
        }
        $this->username = $username;
    }

    /**
     * Validate that the child constructor is initiated
     *
     * @return bool true for valid, false otherwise
     */
    protected function validateAfterConstructor()
    {
        if (empty($this->username)) {
            return false;
        }
        if (empty($this->http)) {
            return false;
        }
        if (empty($this->sourceName)) {
            return false;
        }
        return true;
    }

    /**
     * Return the film detail page's URL within a website. The URL does not
     * include the base URL.  
     *
     * @param \RatingSync\Film $film Film the URL goes to
     *
     * @return string URL of a film detail page
     */
    abstract protected function getFilmDetailPageUrl($film);

    /**
     * Regular expression to find the film title in film detail HTML page
     *
     * @return string Regular expression to find the film title in film detail HTML page
     */
    abstract protected function getDetailPageRegexForTitle();

    /**
     * Regular expression to find the film year in film detail HTML page
     *
     * @return string Regular expression to find the film year in film detail HTML page
     */
    abstract protected function getDetailPageRegexForYear();

    /**
     * Regular expression to find the image in film detail HTML page
     *
     * @return string Regular expression to find the image in film detail HTML page
     */
    abstract protected function getDetailPageRegexForImage();

    /**
     * Regular expression to find Content Type in film detail HTML page
     *
     * @return string Regular expression to find Content Type in film detail HTML page
     */
    abstract protected function getDetailPageRegexForContentType();

    /**
     * Regular expression to find the film or tv series season in film detail HTML page
     *
     * @return string Regular expression to find the filmor tv series season in film detail HTML page
     */
    abstract protected function getDetailPageRegexForSeason();

    /**
     * Regular expression to find the tv episode title in film detail HTML page
     *
     * @return string Regular expression to find the tv episode title in film detail HTML page
     */
    abstract protected function getDetailPageRegexForEpisodeTitle();

    /**
     * Regular expression to find the tv episode number in film detail HTML page
     *
     * @return string Regular expression to find the tv episode number in film detail HTML page
     */
    abstract protected function getDetailPageRegexForEpisodeNumber();

    /**
     * Regular expression to find Film Id in film detail HTML page
     *
     * @return string Regular expression to find Film Id in film detail HTML page
     */
    abstract protected function getDetailPageRegexForUniqueName();

    /**
     * Regular expression to find uniqueEpisode in film detail HTML page
     *
     * @return string Regular expression to find uniqueEpisode in film detail HTML page
     */
    abstract protected function getDetailPageRegexForUniqueEpisode();

    /**
     * Regular expression to find uniqueAlt in film detail HTML page
     *
     * @return string Regular expression to find uniqueAlt in film detail HTML page
     */
    abstract protected function getDetailPageRegexForUniqueAlt();
    
    /**
     * Get the genres from html of the film's detail page. Set the value
     * in the Film param.
     *
     * @param string $page      HTML of the film detail page
     * @param Film   $film      Set the image link in this Film object
     * @param bool   $overwrite Only overwrite data if 1) $overwrite=true OR/AND 2) data is null
     *
     * @return bool true is value is written to the Film object
     */
    abstract protected function parseDetailPageForGenres($page, $film, $overwrite);

    /**
     * Get the directors from html of the film's detail page. Set the value
     * in the Film param.
     *
     * @param string $page      HTML of the film detail page
     * @param Film   $film      Set the image link in this Film object
     * @param bool   $overwrite Only overwrite data if 1) $overwrite=true OR/AND 2) data is null
     *
     * @return bool true is value is written to the Film object
     */
    abstract protected function parseDetailPageForDirectors($page, $film, $overwrite);

    /**
     * Return URL within a website for searching films. The URL does not
     * include the base URL.  
     *
     * @param array $args See the child class version of args
     *
     * @return string URL of a rating page
     */
    abstract protected function getSearchUrl($args);

    /**
     * Return a cached film page if the cached file is fresh enough. The $refreshCache param
     * shows if it is fresh enough. If the file is out of date return null.
     *
     * @param \RatingSync\Film $film         Film needed detail for
     * @param int|0            $refreshCache Use cache for files modified within mins from now. -1 means always use cache. Zero means never use cache.
     *
     * @return string File as a string. Null if the use cache is not used.
     */
    public function getFilmDetailPageFromCache($film, $refreshCache = Constants::USE_CACHE_NEVER)
    {
        if (Constants::USE_CACHE_NEVER == $refreshCache) {
            return null;
        }
        
        $filename = Constants::cacheFilePath() . $this->sourceName . "_" . $this->username . "_film_";
        $filename .= $film->getUniqueName($this->sourceName);
        if (!empty($film->getSeason($this->sourceName))) {
            $filename .= "_" . $film->getSeason($this->sourceName);
        }
        if (!empty($film->getEpisodeNumber($this->sourceName))) {
            $filename .= "_" . $film->getEpisodeNumber($this->sourceName);
        }
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
     * Cache a film detail page in a local file
     *
     * @param string           $page File as a string
     * @param \RatingSync\Film $film Film data about the page
     */
    public function cacheFilmDetailPage($page, $film)
    {
        $filename = Constants::cacheFilePath() . $this->sourceName . "_" . $this->username . "_film_";
        $filename .= $film->getUniqueName($this->sourceName);
        if (!empty($film->getSeason($this->sourceName))) {
            $filename .= "_" . $film->getSeason($this->sourceName);
        }
        if (!empty($film->getEpisodeNumber($this->sourceName))) {
            $filename .= "_" . $film->getEpisodeNumber($this->sourceName);
        }
        $filename .= ".html";
        $fp = fopen($filename, "w");
        fwrite($fp, $page);
        fclose($fp);
    }

    /*
     * @param int|0 $refreshCache Use cache for files modified within mins from now. -1 means always use cache. Zero means never use cache.
     */
    public function getFilmDetailFromWebsite($film, $overwrite = true, $refreshCache = Constants::USE_CACHE_NEVER)
    {
        if (is_null($film) || !($film instanceof Film) ) {
            throw new \InvalidArgumentException('arg1 must be a Film object');
        }
        
        $page = $this->getFilmDetailPage($film, $refreshCache, true);

        if (empty($page)) {
            return;
        }
        $this->parseDetailPageForTitle($page, $film, $overwrite);
        $this->parseDetailPageForFilmYear($page, $film, $overwrite);
        $this->parseDetailPageForImage($page, $film, $overwrite);
        $this->parseDetailPageForContentType($page, $film, $overwrite);
        $this->parseDetailPageForSeason($page, $film, $overwrite);
        $this->parseDetailPageForEpisodeNumber($page, $film, $overwrite);
        $this->parseDetailPageForEpisodeTitle($page, $film, $overwrite);
        $this->parseDetailPageForGenres($page, $film, $overwrite);
        $this->parseDetailPageForDirectors($page, $film, $overwrite);
        $this->parseFilmSource($page, $film, $overwrite);

        $this->parseDetailPageFurther($page, $film, $overwrite);
    }

    public function parseFilmSource($page, $film, $overwrite = true)
    {
        if (is_null($film) || !($film instanceof Film) ) {
            throw new \InvalidArgumentException('arg1 must be a Film object');
        }
        if (empty($page)) {
            return;
        }

        $this->parseDetailPageForUniqueName($page, $film, $overwrite);
        $this->parseDetailPageForUniqueEpisode($page, $film, $overwrite);
        $this->parseDetailPageForUniqueAlt($page, $film, $overwrite);
    }
    
    /**
     * For child class to parse other attributes from the detail page
     */
    public function parseDetailPageFurther($page, $film, $overwrite = true)
    {
    }

    public function getFilmDetailPage($film, $refreshCache = Constants::USE_CACHE_NEVER, $search = false) {
        if (is_null($film) || !($film instanceof Film) ) {
            throw new \InvalidArgumentException('arg1 must be a Film object');
        }
        
        $page = $this->getFilmDetailPageFromCache($film, $refreshCache);
        if (empty($page)) {
            $uniqueName = $film->getUniqueName($this->sourceName);
            if (empty($uniqueName) && $search) {
                if ($this->searchWebsiteForUniqueFilm($film)) {
                    $uniqueName = $film->getUniqueName($this->sourceName);
                }
            }
            if (!empty($uniqueName)) {
                try {
                    $page = $this->http->getPage($this->getFilmDetailPageUrl($film));
                    $this->cacheFilmDetailPage($page, $film);
                } catch (\Exception $e) {
                    logDebug($e, __FUNCTION__." ".__LINE__);
                    throw $e;
                }
            }
        }
        
        return $page;
    }

    public function getFilmByUniqueName($uniqueName, $uniqueEpisode = null, $uniqueAlt = null)
    {
        if ( empty($uniqueName) ) {
            throw new \InvalidArgumentException('Function getFilmByUniqueName must have uniqueName');
        }
        $film = new Film();
        $film->setUniqueName($uniqueName, $this->sourceName);
        $film->setUniqueEpisode($uniqueEpisode, $this->sourceName);
        $film->setUniqueAlt($uniqueAlt, $this->sourceName);
        try {
            $this->getFilmDetailFromWebsite($film);
        } catch (\Exception $e) {
            $film = null;
        }
        
        return $film;
    }

    public function getFilmBySearchByFilm($film)
    {
        $searchTerms = array();
        $searchTerms["uniqueName"] = $film->getUniqueName($this->sourceName);
        $searchTerms["uniqueEpisode"] = $film->getUniqueEpisode($this->sourceName);
        $searchTerms["uniqueAlt"] = $film->getUniqueAlt($this->sourceName);
        $searchTerms["title"] = $film->getTitle();
        $searchTerms["year"] = $film->getYear();
        $searchTerms["contentType"] = $film->getContentType();
        $searchTerms["season"] = $film->getSeason();
        $searchTerms["episodeNumber"] = $film->getEpisodeNumber();
        $searchTerms["episodeTitle"] = $film->getEpisodeTitle();

        return $this->getFilmBySearch($searchTerms);
    }

    public function getFilmBySearch($searchTerms)
    {
        if (empty($searchTerms) || !is_array($searchTerms)) {
            throw new \InvalidArgumentException('Function '.__FUNCTION__.' must have a searchTerms array');
        }
        
        $uniqueName = array_value_by_key("uniqueName", $searchTerms);
        $uniqueEpisode = array_value_by_key("uniqueEpisode", $searchTerms);
        $uniqueAlt = array_value_by_key("uniqueAlt", $searchTerms);
        $title = array_value_by_key("title", $searchTerms);
        $year = array_value_by_key("year", $searchTerms);
        $contentType = array_value_by_key("contentType", $searchTerms);
        $season = array_value_by_key("season", $searchTerms);
        $episodeNumber = array_value_by_key("episodeNumber", $searchTerms);
        $episodeTitle = array_value_by_key("episodeTitle", $searchTerms);
        
        if (empty($uniqueName) && (empty($title) || empty($year))) {
            throw new \InvalidArgumentException('Function '.__FUNCTION__.' searchTerms must have uniqueName or (title and year)');
        }

        if (!empty($uniqueName)) {
            return $this->getFilmByUniqueName($uniqueName, $uniqueEpisode, $uniqueAlt);
        }

        $film = new Film();
        $film->setTitle($title);
        $film->setYear($year);
        $film->setContentType($contentType);
        $film->setSeason($season);
        $film->setEpisodeNumber($episodeNumber);
        $film->setEpisodeTitle($episodeTitle);
        try {
            $this->getFilmDetailFromWebsite($film, true, Constants::USE_CACHE_ALWAYS);
        } catch (\Exception $e) {
            $film = null;
        }
        
        return $film;
    }

    /**
     * Get the title from html of the film's detail page. Set the value
     * in the Film param.
     *
     * @param string $page      HTML of the film detail page
     * @param Film   $film      Set the title in this Film object
     * @param bool   $overwrite Only overwrite data if 1) $overwrite=true OR/AND 2) data is null
     *
     * @return bool true is value is written to the Film object
     */
    protected function parseDetailPageForTitle($page, $film, $overwrite)
    {
        if (!$overwrite && !is_null($film->getTitle())) {
            return false;
        }
        
        $regex = $this->getDetailPageRegexForTitle();
        if (empty($regex) || 0 === preg_match($regex, $page, $matches)) {
            return false;
        }
        $film->setTitle(html_entity_decode($matches[1], ENT_QUOTES, "utf-8"));
        return true;
    }

    /**
     * Get the film year from html of the film's detail page. Set the value
     * in the Film param.
     *
     * @param string $page      HTML of the film detail page
     * @param Film   $film      Set the title in this Film object
     * @param bool   $overwrite Only overwrite data if 1) $overwrite=true OR/AND 2) data is null
     *
     * @return bool true is value is written to the Film object
     */
    protected function parseDetailPageForFilmYear($page, $film, $overwrite)
    {
        if (!$overwrite && !is_null($film->getYear())) {
            return false;
        }
        
        $regex = $this->getDetailPageRegexForYear();
        if (!empty($regex) && 0 < preg_match($regex, $page, $matches)) {
            $film->setYear($matches[1]);
            return true;
        } else {
            return false;
        }        
    }

    /**
     * Get the image link from html of the film's detail page. Set the value
     * in the Film param.
     *
     * @param string $page      HTML of the film detail page
     * @param Film   $film      Set the image link in this Film object
     * @param bool   $overwrite Only overwrite data if 1) $overwrite=true OR/AND 2) data is null
     *
     * @return bool true is value is written to the Film object
     */
    protected function parseDetailPageForImage($page, $film, $overwrite)
    {
        if (!$overwrite && !is_null($film->getImage()) && !is_null($film->getImage($this->sourceName))) {
            return false;
        }
        
        $regex = $this->getDetailPageRegexForImage();
        if (empty($regex) || 0 === preg_match($regex, $page, $matches)) {
            return false;
        }

        if ($overwrite || is_null($film->getImage($this->sourceName))) {
            $film->setImage($matches[1], $this->sourceName);
        }
        
        return true;
    }

    /**
     * Get the content type from html of the film's detail page. Set the value
     * in the Film param.
     *
     * @param string $page      HTML of the film detail page
     * @param Film   $film      Set the image link in this Film object
     * @param bool   $overwrite Only overwrite data if 1) $overwrite=true OR/AND 2) data is null
     *
     * @return bool true is value is written to the Film object
     */
    protected function parseDetailPageForContentType($page, $film, $overwrite)
    {
        if (!$overwrite && !is_null($film->getContentType())) {
            return false;
        }

        $regex = $this->getDetailPageRegexForContentType();
        if (empty($regex) || 0 === preg_match($regex, $page, $matches)) {
            return false;
        }
        $film->setContentType($matches[1]);
        return true;
    }

    /**
     * Get the season from html of the film's detail page. Set the value
     * in the Film param.
     *
     * @param string $page      HTML of the film detail page
     * @param Film   $film      Set the season in this Film object
     * @param bool   $overwrite Only overwrite data if 1) $overwrite=true OR/AND 2) data is null
     *
     * @return bool true is value is written to the Film object
     */
    protected function parseDetailPageForSeason($page, $film, $overwrite)
    {
        if (!$overwrite && !is_null($film->getSeason())) {
            return false;
        }
        
        $regex = $this->getDetailPageRegexForSeason();
        if (empty($regex) || 0 === preg_match($regex, $page, $matches)) {
            return false;
        }
        $film->setSeason(html_entity_decode($matches[1], ENT_QUOTES, "utf-8"));
        return true;
    }

    /**
     * Get the episode number from html of the film's detail page. Set the value
     * in the Film param.
     *
     * @param string $page      HTML of the film detail page
     * @param Film   $film      Set the episode number in this Film object
     * @param bool   $overwrite Only overwrite data if 1) $overwrite=true OR/AND 2) data is null
     *
     * @return bool true is value is written to the Film object
     */
    protected function parseDetailPageForEpisodeNumber($page, $film, $overwrite)
    {
        if (!$overwrite && !is_null($film->getEpisodeNumber())) {
            return false;
        }
        
        $regex = $this->getDetailPageRegexForEpisodeNumber();
        if (!empty($regex) && 0 < preg_match($regex, $page, $matches)) {
            $film->setEpisodeNumber($matches[1]);
            return true;
        } else {
            return false;
        }        
    }

    /**
     * Get the episode title from html of the film's detail page. Set the value
     * in the Film param.
     *
     * @param string $page      HTML of the film detail page
     * @param Film   $film      Set the episode title in this Film object
     * @param bool   $overwrite Only overwrite data if 1) $overwrite=true OR/AND 2) data is null
     *
     * @return bool true is value is written to the Film object
     */
    protected function parseDetailPageForEpisodeTitle($page, $film, $overwrite)
    {
        if (!$overwrite && !is_null($film->getEpisodeTitle())) {
            return false;
        }
        
        $regex = $this->getDetailPageRegexForEpisodeTitle();
        if (empty($regex) || 0 === preg_match($regex, $page, $matches)) {
            return false;
        }
        $film->setEpisodeTitle(html_entity_decode($matches[1], ENT_QUOTES, "utf-8"));
        return true;
    }

    /**
     * Get the Unique Name from html of the film's detail page. Set the value
     * in the Film param.
     *
     * @param string $page      HTML of the film detail page
     * @param Film   $film      Set the image link in this Film object
     * @param bool   $overwrite Only overwrite data if 1) $overwrite=true OR/AND 2) data is null
     *
     * @return bool true is value is written to the Film object
     */
    protected function parseDetailPageForUniqueName($page, $film, $overwrite)
    {
        if (!$overwrite && !is_null($film->getUniqueName($this->sourceName))) {
            return false;
        }

        $regex = $this->getDetailPageRegexForUniqueName();
        if (empty($regex) || 0 === preg_match($regex, $page, $matches)) {
            return false;
        }
        $film->setUniqueName($matches[1], $this->sourceName);
        return true;
    }

    /**
     * Get the Unique Episode from html of the film's detail page. Set the value
     * in the Film param.
     *
     * @param string $page      HTML of the film detail page
     * @param Film   $film      Set the unique episode link in this Film object
     * @param bool   $overwrite Only overwrite data if 1) $overwrite=true OR/AND 2) data is null
     *
     * @return bool true is value is written to the Film object
     */
    protected function parseDetailPageForUniqueEpisode($page, $film, $overwrite)
    {
        if (!$overwrite && !is_null($film->getUniqueEpisode($this->sourceName))) {
            return false;
        }

        $regex = $this->getDetailPageRegexForUniqueEpisode();
        if (empty($regex) || 0 === preg_match($regex, $page, $matches)) {
            return false;
        }
        $film->setUniqueEpisode($matches[1], $this->sourceName);
        return true;
    }

    /**
     * Get the Unique Alt(ernate) from html of the film's detail page. Set the value
     * in the Film param.
     *
     * @param string $page      HTML of the film detail page
     * @param Film   $film      Set the unique alt link in this Film object
     * @param bool   $overwrite Only overwrite data if 1) $overwrite=true OR/AND 2) data is null
     *
     * @return bool true is value is written to the Film object
     */
    protected function parseDetailPageForUniqueAlt($page, $film, $overwrite)
    {
        if (!$overwrite && !is_null($film->getUniqueAlt($this->sourceName))) {
            return false;
        }

        $regex = $this->getDetailPageRegexForUniqueAlt();
        if (empty($regex) || 0 === preg_match($regex, $page, $matches)) {
            return false;
        }
        $film->setUniqueAlt($matches[1], $this->sourceName);
        return true;
    }

    /**
     * Search website for a unique film. Set attrs in the $film param.
     * Class returns false unless a child implements it. Class will definitely
     * set uniqueName, but might set other attrs as well. Examples
     * like uniqueAlt and uniqueEpisode.
     *
     * @param \RatingSync\Film $film
     *
     * @return boolean success/failure
     */
    public function searchWebsiteForUniqueFilm($film)
    {
        return false;
    }

    public function getFilmUrl($film)
    {
        try {
            $url = $this->http->getBaseUrl() . $this->getFilmDetailPageUrl($film);
        } catch (\Exception $e) {
            $url = null;
        }

        return $url;
    }
}