<?php
/**
 * Netflix class
 */
namespace RatingSync;

require_once "Site.php";
require_once "HttpNetflix.php";

/**
 * Communicate to/from the Netflix website
 * - Search for films and tv shows
 * - Get details for each and rate it
 * - Export/Import ratings.
 */
class Netflix extends Site
{
    const NETFLIX_DATE_FORMAT = "n/j/y";

    public function __construct($username)
    {
        parent::__construct($username);
        $this->http = new HttpNetflix($username);
        $this->sourceName = Constants::SOURCE_NETFLIX;
        $this->dateFormat = self::NETFLIX_DATE_FORMAT;
        $this->maxRatingScore = 5;
    }
    
    /**
     * Return the rating page's URL within a website. The URL does not
     * include the base URL.  
     *
     * @param array $args See the child class version of args
     *
     * @return string URL of a rating page
     */
    protected function getRatingPageUrl($args)
    {
        return '/MoviesYouveSeen';
    }

    /**
     * Page number for the next page of ratings. False if not available.
     *
     * @param string $page Html of the current ratings page
     *
     * @return int|false
     */
    protected function getNextRatingPageNumber($page)
    {
        return false;
    }

    /**
     * Create Film objects from the HTML of a ratings page.  Films get only
       data available from ratings page. If the $details param is true, then
       each film goes to another page for full detail. Using $details=true
       can take a long time.
     *
     * @param string     $page         HTML from a page of ratings
     * @param bool|false $details      Get all data for each film
     * @param int|0      $refreshCache Use cache for files modified within mins from now. -1 means always use cache. Zero means never use cache.
     *
     * @return array Film class objects
     */
    protected function getFilmsFromRatingsPage($page, $details = false, $refreshCache = 0)
    {
        $films = array();

        return $films;
    }

    /**
     * Return the film detail page's URL within a website. The URL does not
     * include the base URL.  
     *
     * @param \RatingSync\Film $film Film the URL goes to
     *
     * @return string URL of a film detail page
     */
    protected function getFilmDetailPageUrl($film)
    {
        if (! $film instanceof Film ) {
            throw new \InvalidArgumentException('Function getFilmDetailPageUrl must be given a Film object');
        } elseif ( empty($film->getUniqueName($this->sourceName)) ) {
            throw new \InvalidArgumentException('Function getFilmDetailPageUrl must have unique attr (uniqueName, '.$this->sourceName.')');
        }

        $uniqueName = $film->getUniqueName($this->sourceName);
        return "/title/$uniqueName";
    }

    /**
     * Return URL within a website for searching films. The URL does not
     * include the base URL.  
     *
     * @param array $args Keys: query
     *
     * @return string URL of a rating page
     */
    public function getSearchUrl($args)
    {
        if (empty($args) || !is_array($args) || !array_key_exists('query', $args) || empty($args['query']))
        {
            throw new \InvalidArgumentException('$args must be an array with key "query" (non-empty)');
        }

        $query = urlencode($args['query']);

        return "/search/$query";
    }

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
    protected function parseDetailPageForGenres($page, $film, $overwrite)
    {
        if (!$overwrite && !empty($film->getGenres())) {
            return false;
        }
        $originalGenres = $film->getGenres();
        $didFindGenres = false;
        
        if ($overwrite || empty($film->getGenres())) {
            $film->removeAllGenres();
            
            preg_match_all('/<a type="genre"[^>]*>([^<]+)/', $page, $genreMatches);
            $genres = $genreMatches[1];
            foreach ($genres as $genre) {
                $film->addGenre(html_entity_decode($genre, ENT_QUOTES, "utf-8"));
                $didFindGenres = true;
            }
        }

        if (!$didFindGenres) {
            if (!empty($originalGenres)) {
                foreach ($originalGenres as $genre) {
                    $film->addGenre($genre);
                }
            }
            return false;
        }
        return true;
    }

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
    protected function parseDetailPageForDirectors($page, $film, $overwrite)
    {
        return false;
    }

    /**
     * Regular expression to find the film title in film detail HTML page
     *
     * @return string Regular expression to find the film title in film detail HTML page
     */
    protected function getDetailPageRegexForTitle() {
        $loggedIn = false;
        if ($loggedIn) {
            return '/<div class="title has-jawbone-nav-transition"[^>]*>([^<]+)/';
        } else {
            return '/class=\"title\"[^>]*>([^<]+)</';
        }
    }

    /**
     * Regular expression to find the film year in film detail HTML page
     *
     * @return string Regular expression to find the film year in film detail HTML page
     */
    protected function getDetailPageRegexForYear() {
        return '/<span class="year"[^>]*>([^<]+)/';
    }

    /**
     * Regular expression to find the image in film detail HTML page
     *
     * @return string Regular expression to find the image in film detail HTML page
     */
    protected function getDetailPageRegexForImage() {
        return '';
    }

    /**
     * Regular expression to find Content Type in film detail HTML page
     *
     * @return string Regular expression to find Content Type in film detail HTML page
     */
    protected function getDetailPageRegexForContentType() {
        return '';
    }

    /**
     * Regular expression to find Film Id in film detail HTML page
     *
     * @return string Regular expression to find Film Id in film detail HTML page
     */
    protected function getDetailPageRegexForUniqueName() {
        return '/\/title\/([^"]+)"/';
    }

    /**
     * Regular expression to find your rating score in film detail HTML page
     *
     * @param \RatingSync\Film $film Film data
     *
     * @return string Regular expression to find your rating score in film detail HTML page
     */
    protected function getDetailPageRegexForYourScore($film) {
        return '/<span data-rating="(\d)" class="star sb-placeholder/';
    }

    /**
     * Regular expression to find your rating date in film detail HTML page
     *
     * @return string Regular expression to find your rating date in film detail HTML page
     */
    protected function getDetailPageRegexForRatingDate() {
        return '';
    }

    /**
     * Regular expression to find suggested score in film detail HTML page
     *
     * @param \RatingSync\Film $film Film data
     *
     * @return string Regular expression to find suggested score in film detail HTML page
     */
    protected function getDetailPageRegexForSuggestedScore($film) {
        return '';
    }

    /**
     * Regular expression to find critic score in film detail HTML page
     *
     * @return string Regular expression to find critic score in film detail HTML page
     */
    protected function getDetailPageRegexForCriticScore() {
        return '';
    }

    /**
     * Regular expression to find user score in film detail HTML page
     *
     * @return string Regular expression to find user score in film detail HTML page
     */
    protected function getDetailPageRegexForUserScore() {
        return '';
    }

    /**
     * Search website for a unique film and set unique attr on
     * the param Film object.
     *
     * @param \RatingSync\Film $film
     *
     * @return string Film::uniqueName
     */
    public function searchWebsiteForUniqueFilm($film)
    {
        if (!($film instanceof Film)) {
            throw new \InvalidArgumentException('$film must be an array with key "pageIndex" and value an int');
        }
        
        return null;
    }
}