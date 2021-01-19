<?php
namespace RatingSync;

require_once "Site.php";

abstract class SiteRatings extends \RatingSync\Site
{
    protected $maxRatingScore = 10;
    protected $maxCriticScore = 10;
    protected $maxUserScore = 10;

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

    /*
     * 
     */
    public function parseDetailPageFurther($page, $film, $overwrite = true)
    {
        $this->parseDetailPageForRating($page, $film, $overwrite);
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

        $success = true;
        $outputAsStr = "";
        if (empty($format) || $format == "csv") {
            $outputAsStr = $this->filmsAsCsv($films);
        }
        else {
            $outputAsStr = $this->filmsAsXml($films);
        }

        if ($success && fwrite($fp, $outputAsStr) !== FALSE) {
            $success = true;
        }
        fclose($fp);
        
        return $success;
    }

    public function filmsAsXml($films)
    {
        // Write XML
        $xml = new \SimpleXMLElement("<films/>");
        foreach ($films as $film) {
            $film->addXmlChild($xml);
        }
        $filmCount = $xml->count();
        $xml->addChild('count', $filmCount);

        return $xml->asXml();
    }

    public function filmsAsCsv($films)
    {
        $csv = "imdbID,Title,Year,Rating10,WatchedDate" . "\n"; // letterboxd format
        foreach ($films as $film) {
            if ($film->getContentType() == Film::CONTENT_TV_EPISODE) {
                continue;
            }
            $title = $film->getTitle();
            $year = $film->getYear();
            $imdbId = $film->getUniqueName(Constants::SOURCE_IMDB);
            $rating = $film->getRating(Constants::SOURCE_RATINGSYNC);
            if (empty($rating)) {
                logDebug("Rating empty for $title");
                continue;
            }
            $ratingScore = $rating->getYourScore();
            $ratingDateStr = $rating->getYourRatingDate()->format("Y-m-d");

            if ($imdbId == null) {
                $imdbId = "";
            }

            // Write a line for this film
            $csv .= "$imdbId,\"$title\",$year,$ratingScore,$ratingDateStr" . "\n";
        }

        return $csv;
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
                $film = Film::createFromXml($filmSxe);
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
}

?>