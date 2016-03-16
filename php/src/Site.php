<?php
/**
 * Site class. A source of rating account, usually a website like
 * IMDb or Jinni.
 */
namespace RatingSync;

require_once "Constants.php";
require_once "Film.php";
require_once "HttpJinni.php";
require_once "Rating.php";

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
    protected $maxRatingScore = 10;
    protected $maxCriticScore = 10;
    protected $maxUserScore = 10;

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
     * Return the rating page's URL within a website. The URL does not
     * include the base URL.  
     *
     * @param array $args See the child class version of args
     *
     * @return string URL of a rating page
     */
    abstract protected function getRatingPageUrl($args);

    /**
     * Page number for the next page of ratings. False if not available.
     *
     * @param string $page Html of the current ratings page
     *
     * @return int|false
     */
    abstract protected function getNextRatingPageNumber($page);

    /**
     * Create Film objects from the HTML of a ratings page.  Different sites
       show data fields in the rating page. The data available goes to the
       Films from that. If the $details param is true, then each film goes
       to another page for full detail. Using $details=true can take a long
       time.
     *
     * @param string     $page         HTML from a page of ratings
     * @param bool|false $details      Get all data for each film
     * @param int|0      $refreshCache Use cache for files modified within mins from now. -1 means always use cache. Zero means never use cache.
     *
     * @return array Film class objects
     */
    abstract protected function getFilmsFromRatingsPage($page, $details = false, $refreshCache = 0);

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
     * Regular expression to find your rating score in film detail HTML page
     *
     * @param \RatingSync\Film $film Film data
     *
     * @return string Regular expression to find your rating score in film detail HTML page
     */
    abstract protected function getDetailPageRegexForYourScore($film);

    /**
     * Regular expression to find your rating date in film detail HTML page
     *
     * @return string Regular expression to find your rating date in film detail HTML page
     */
    abstract protected function getDetailPageRegexForRatingDate();

    /**
     * Regular expression to find suggested score in film detail HTML page
     *
     * @param \RatingSync\Film $film Film data
     *
     * @return string Regular expression to find suggested score in film detail HTML page
     */
    abstract protected function getDetailPageRegexForSuggestedScore($film);

    /**
     * Regular expression to find critic score in film detail HTML page
     *
     * @return string Regular expression to find critic score in film detail HTML page
     */
    abstract protected function getDetailPageRegexForCriticScore();

    /**
     * Regular expression to find user score in film detail HTML page
     *
     * @return string Regular expression to find user score in film detail HTML page
     */
    abstract protected function getDetailPageRegexForUserScore();
    
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
     * Get every rating on $this->username's account
     *
     * @param int|null $limitPages   Limit the number of pages of ratings
     * @param int|1    $beginPage    First page of rating results
     * @param bool     $details      Bring full film details (slower)
     * @param int|0    $refreshCache Use cache for files modified within mins from now. -1 means always use cache. Zero means never use cache.
     *
     * @return array of Film
     */
    public function getRatings($limitPages = null, $beginPage = 1, $details = false, $refreshCache = Constants::USE_CACHE_NEVER)
    {
        $films = array();
        $args = array('pageIndex' => $beginPage);
        // Get one page of ratings
        $page = $this->getRatingsPageFromCache($beginPage, $refreshCache);
        if (empty($page)) {
            $page = $this->http->getPage($this->getRatingPageUrl($args));
            $this->cacheRatingsPage($page, $beginPage);
        }
        $films = $this->getFilmsFromRatingsPage($page, $details, $refreshCache);

        // Get the rest of rating pages
        // While... within the limit and still another page available
        $pageCount = 1;
        while (($limitPages == null || $limitPages > $pageCount) &&
                  ($nextPageNumber = $this->getNextRatingPageNumber($page))
              ) {
            $args['pageIndex'] = $nextPageNumber;
            $page = $this->getRatingsPageFromCache($nextPageNumber, $refreshCache);
            if (empty($page)) {
                $page = $this->http->getPage($this->getRatingPageUrl($args));
                $this->cacheRatingsPage($page, $nextPageNumber);
            }
            $films = array_merge($films, $this->getFilmsFromRatingsPage($page, $details, $refreshCache));
            $pageCount++;
        }
        return $films;
    }

    /**
     * Return a cached page of ratings if the cached file is fresh enough. The $refreshCache param
     * shows if it is fresh enough. If the file is out of date return null.
     *
     * @param int   $pageNum      Page of rating results
     * @param int|0 $refreshCache Use cache for files modified within mins from now. -1 means always use cache. Zero means never use cache.
     *
     * @return string File as a string. Null if the use cache is not used.
     */
    public function getRatingsPageFromCache($pageNum, $refreshCache = Constants::USE_CACHE_NEVER)
    {
        if (Constants::USE_CACHE_NEVER == $refreshCache) {
            return null;
        }
        
        $filename = Constants::cacheFilePath() . $this->sourceName . "_" . $this->username . "_ratings_$pageNum.html";

        if (!file_exists($filename)) {
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
     * Cache a ratings page in a local file
     *
     * @param string $page    File as a string
     * @param int    $pageNum Page of rating results used in the new filename
     */
    public function cacheRatingsPage($page, $pageNum)
    {
        $filename = Constants::cacheFilePath() . $this->sourceName . "_" . $this->username . "_ratings_$pageNum.html";
        $fp = fopen($filename, "w");
        fwrite($fp, $page);
        fclose($fp);
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

    /**
     * Get the account's ratings from the website and write to a file/database
     *
     * @param string     $format   File format to write to (or database). Currently only XML.
     * @param string     $filename Write to a new (overwrite) file in the output directory
     * @param bool|false $detail   False brings only rating data. True also brings full detail (can take a long time).
     * @param int|0      $useCache Use cache for files modified within mins from now. -1 means always use cache. Zero means never use cache.
     *
     * @return true for success, false for failure
     */
    public function exportRatings($format, $filename, $detail = false, $useCache = Constants::USE_CACHE_NEVER)
    {
        $films = $this->getRatings(null, 1, $detail, $useCache);

        $filename =  Constants::outputFilePath() . $filename;
        $fp = fopen($filename, "w");

        // Write XML
        $xml = new \SimpleXMLElement("<films/>");
        foreach ($films as $film) {
            $film->addXmlChild($xml);
        }
        $filmCount = $xml->count();
        $xml->addChild('count', $filmCount);
        fwrite($fp, $xml->asXml());
        fclose($fp);
        
        return true;
    }

    /**
     * Get ratings from a file and import those ratings to an account at the website
     *
     * @param string $format     File format to read from. Currently only XML.
     * @param string $filename   Full path to the filename reading from
     * @param string $sourceName Website to import to
     *
     * @return true for success, false for failure
     */
    public function importRatings($format, $filename, $username = null, $sourceName = Constants::SOURCE_RATINGSYNC)
    {
        if (! Source::validSource($sourceName) ) {
            throw new \InvalidArgumentException('Source $source invalid');
        } elseif ($sourceName !=  Constants::SOURCE_RATINGSYNC) {
            throw new \InvalidArgumentException('RatingSync database is the only import supported currently (sourceName=' . $sourceName . ')');
        }
        if (! self::validImportFormat($format) ) {
            throw new \InvalidArgumentException('Import format '.$format.' invalid');
        }

        $films = $this->parseFilmsFromFile($format, $filename);
        foreach ($films as $film) {
            if ($sourceName == Constants::SOURCE_RATINGSYNC) {
                $film->saveToDb($username);
            }
        }
        return true;
    }

    /*
     * @param int|0 $refreshCache Use cache for files modified within mins from now. -1 means always use cache. Zero means never use cache.
     */
    public function getFilmDetailFromWebsite($film, $overwrite = true, $refreshCache = Constants::USE_CACHE_NEVER)
    {
        if (is_null($film) || !($film instanceof Film) ) {
            throw new \InvalidArgumentException('arg1 must be a Film object');
        }

        $page = $this->getFilmDetailPageFromCache($film, $refreshCache);
        if (empty($page)) {
            $uniqueName = $film->getUniqueName($this->sourceName);
            if (empty($uniqueName)) {
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

        if (empty($page)) {
            return;
        }
        $this->parseDetailPageForTitle($page, $film, $overwrite);
        $this->parseDetailPageForFilmYear($page, $film, $overwrite);
        $this->parseDetailPageForImage($page, $film, $overwrite);
        $this->parseDetailPageForContentType($page, $film, $overwrite);
        $this->parseDetailPageForUniqueName($page, $film, $overwrite);
        $this->parseDetailPageForRating($page, $film, $overwrite);
        $this->parseDetailPageForGenres($page, $film, $overwrite);
        $this->parseDetailPageForDirectors($page, $film, $overwrite);
    }

    public function getFilmByUniqueName($uniqueName)
    {
        if ( empty($uniqueName) ) {
            throw new \InvalidArgumentException('Function getFilmByUniqueName must have uniqueName');
        }
        $film = new Film($this->http);
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
            throw new \InvalidArgumentException('Function getFilmBySearch must have a searchTerms array');
        }

        $uniqueName = null;
        $title = null;
        $year = null;
        $contentType = null;

        if (array_key_exists("uniqueName", $searchTerms)) {
            $uniqueName = $searchTerms['uniqueName'];
        }
        if (array_key_exists("title", $searchTerms)) {
            $title = $searchTerms['title'];
        }
        if (array_key_exists("year", $searchTerms)) {
            $year = $searchTerms['year'];
        }
        if (array_key_exists("contentType", $searchTerms)) {
            $contentType = $searchTerms['contentType'];
        }
        
        if (empty($uniqueName) && (empty($title) || empty($year))) {
            throw new \InvalidArgumentException('Function getFilmBySearch searchTerms must have uniqueName or (title and year)');
        }

        if (!empty($uniqueName)) {
            return $this->getFilmByUniqueName($uniqueName);
        }

        $film = new Film($this->http);
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
     * Get the rating from html of the film's detail page. Set the value
     * in the Film param.
     *
     * @param string $page      HTML of the film detail page
     * @param Film   $film      Set the rating in this Film object
     * @param bool   $overwrite Only overwrite data if 1) $overwrite=true OR/AND 2) data is null
     */
    protected function parseDetailPageForRating($page, $film, $overwrite)
    {
        $rating = $film->getRating($this->sourceName);

        // Your score
        if ($overwrite || is_null($rating->getYourScore($film))) {
            $regex = $this->getDetailPageRegexForYourScore($film);
            if (!empty($regex) && 0 < preg_match($regex, $page, $matches)) {
                $score = $matches[1];
                if (is_numeric($score)) {
                    $score = $score*10/$this->maxRatingScore;
                }
                $rating->setYourScore($score);
            }
        }

        // Rating Date
        if ($overwrite || is_null($rating->getYourRatingDate())) {
            $regex = $this->getDetailPageRegexForRatingDate();
            if (!empty($regex) && 0 < preg_match($regex, $page, $matches)) {
                $rating->setYourRatingDate($matches[1]);
            }
        }

        // Suggested score
        if ($overwrite || is_null($rating->getSuggestedScore($film))) {
            $regex = $this->getDetailPageRegexForSuggestedScore($film);
            if (!empty($regex) && 0 < preg_match($regex, $page, $matches)) {
                $score = $matches[1];
                if (is_numeric($score)) {
                    $score = $score*10/$this->maxRatingScore;
                }
                $rating->setSuggestedScore($score);
            }
        }

        // Critic Score
        if ($overwrite || is_null($film->getCriticScore($this->sourceName))) {
            $regex = $this->getDetailPageRegexForCriticScore();
            if (!empty($regex) && 0 < preg_match($regex, $page, $matches)) {
                $score = $matches[1];
                if (is_numeric($score)) {
                    $score = $score*10/$this->maxCriticScore;
                }
                $film->setCriticScore($score, $this->sourceName);
            }
        }

        // User Score
        if ($overwrite || is_null($film->getUserScore($this->sourceName))) {
            $regex = $this->getDetailPageRegexForUserScore();
            if (!empty($regex) && 0 < preg_match($regex, $page, $matches)) {
                $score = $matches[1];
                if (is_numeric($score)) {
                    $score = $score*10/$this->maxUserScore;
                }
                $film->setUserScore($score, $this->sourceName);
            }
        }
        
        $film->setRating($rating);
    }

    /**
     * Read films from a file and return them in an array
     *
     * @param string $format   File format to read from. Currently only XML.
     * @param string $filename Input file (including path)
     *
     * @return array of Films
     */
    public function parseFilmsFromFile($format, $filename)
    {
        if (! self::validImportFormat($format) ) {
            throw new \InvalidArgumentException('File parse format '.$format.' invalid');
        }
        
        $xml = simplexml_load_file($filename);
        $xmlFilmArray = $xml->xpath('/films/film');
        
        $films = array();
        foreach ($xmlFilmArray as $filmSxe) {
            try {
                $film = Film::createFromXml($filmSxe, $this->http);
                $films[] = $film;
            } catch (\Exception $e) {
                // Ignore
            }
        }
        
        return $films;
    }

    public static function validExportFormat($format)
    {
        if (in_array($format, array(Constants::EXPORT_FORMAT_XML))) {
            return true;
        }
        return false;
    }

    public static function validImportFormat($format)
    {
        if (in_array($format, array(Constants::IMPORT_FORMAT_XML))) {
            return true;
        }
        return false;
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
}