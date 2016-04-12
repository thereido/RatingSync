<?php
/**
 * Amazon class
 */
namespace RatingSync;

require_once "SiteProvider.php";

/**
 * Communicate to/from the Amazon website
 * - Search for films and tv shows
 */
class Amazon extends \RatingSync\SiteProvider
{
    const AMAZON_DATE_FORMAT = "n/j/y";
    
    public function __construct($username)
    {
        parent::__construct($username);
        $this->sourceName = Constants::SOURCE_AMAZON;
        $this->http = new Http($this->sourceName, $username);
        $this->dateFormat = self::AMAZON_DATE_FORMAT;
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

        return "/search?q=$query";
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
        return '/class=\"title\"[^>]*>([^<]+)</';
    }

    /**
     * Regular expression to find the film year in film detail HTML page
     *
     * @return string Regular expression to find the film year in film detail HTML page
     */
    protected function getDetailPageRegexForYear() {
        return '/<span class="year"[^>]*>([^<]+)</';
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
        return '/data-amazon-title-id="([^"]*)"/';
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
            throw new \InvalidArgumentException("\$film must be a \RatingSync\Film object");
        }

        $title = $film->getTitle();
        $args = array("query" => $title);
        $page = $this->http->getPage($this->getSearchUrl($args));
        $regex = $this->getSearchPageRegexForUniqueName($title, $film->getYear());
        if (empty($regex) || 0 === preg_match($regex, $page, $matches)) {
            return false;
        }
        
        return $matches[1];
    }

    protected function getSearchPageRegexForUniqueName($title, $year)
    {
        $specialChars = "\/\^\.\[\]\|\(\)\?\*\+\{\}"; // need to do '\' too
        $pattern = "|([$specialChars])|U";
        $escapedTitle = preg_replace($pattern, '\\\\${1}', $title);

        return '/data-title-id="([^"]*)"[^>]*>'.$escapedTitle.'<\/a><\/span> <span class="year"[^>]*>'.$year.'</';
    }

    protected function getDetailPageRegexForStreamingUrl()
    {
        return '/href="([^"]+)">Play</';
    }
}