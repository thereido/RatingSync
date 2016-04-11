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
     * Regular expression to find Film Id in film detail HTML page
     *
     * @return string Regular expression to find Film Id in film detail HTML page
     */
    abstract protected function getDetailPageRegexForUniqueName();
    
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
        
        $filename = Constants::cacheFilePath() . $this->sourceName . "_" . $this->username . "_film_" . $film->getUniqueName($this->sourceName) . ".html";

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
        $filename = Constants::cacheFilePath() . $this->sourceName . "_" . $this->username . "_film_" . $film->getUniqueName($this->sourceName) . ".html";
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
        $this->parseDetailPageForUniqueName($page, $film, $overwrite);
        $this->parseDetailPageForGenres($page, $film, $overwrite);
        $this->parseDetailPageForDirectors($page, $film, $overwrite);

        $this->parseDetailPageFurther($page, $film, $overwrite);
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
                $uniqueName = $this->searchWebsiteForUniqueFilm($film);
                $film->setUniqueName($uniqueName, $this->sourceName);
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

    public function getFilmByUniqueName($uniqueName)
    {
        if ( empty($uniqueName) ) {
            throw new \InvalidArgumentException('Function getFilmByUniqueName must have uniqueName');
        }
        $film = new Film();
        $film->setUniqueName($uniqueName, $this->sourceName);
        try {
            $this->getFilmDetailFromWebsite($film);
        } catch (\Exception $e) {
            $film = null;
        }

        return $film;
    }

    public function getFilmBySearch($searchTerms)
    {
        if (empty($searchTerms) || !is_array($searchTerms)) {
            throw new \InvalidArgumentException('Function '.__FUNCTION__.' must have a searchTerms array');
        }
        
        $uniqueName = array_value_by_key("uniqueName", $searchTerms);
        $title = array_value_by_key("title", $searchTerms);
        $year = array_value_by_key("year", $searchTerms);
        $contentType = array_value_by_key("contentType", $searchTerms);
        
        if (empty($uniqueName) && (empty($title) || empty($year))) {
            throw new \InvalidArgumentException('Function '.__FUNCTION__.' searchTerms must have uniqueName or (title and year)');
        }

        if (!empty($uniqueName)) {
            return $this->getFilmByUniqueName($uniqueName);
        }

        $film = new Film();
        $film->setTitle($title);
        $film->setYear($year);
        $film->setContentType($contentType);
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
     * Search website for a unique film and set unique attr on
     * the param Film object. Class returns null unless a child
     * implents it.
     *
     * @param \RatingSync\Film $film
     *
     * @return string Film::uniqueName
     */
    public function searchWebsiteForUniqueFilm($film)
    {
        $uniqueName = null;

        return $uniqueName;
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