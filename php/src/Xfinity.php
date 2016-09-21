<?php
/**
 * Xfinity class
 */
namespace RatingSync;

require_once "SiteProvider.php";

/**
 * Communicate to/from the Xfinity website
 * - Search for films and tv shows
 */
class Xfinity extends \RatingSync\SiteProvider
{
    const XFINITY_DATE_FORMAT = "n/j/y";
    
    public function __construct($username)
    {
        parent::__construct($username);
        $this->sourceName = Constants::SOURCE_XFINITY;
        $this->http = new Http($this->sourceName, $username);
        $this->dateFormat = self::XFINITY_DATE_FORMAT;
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
        } elseif ( empty($film->getUniqueName($this->sourceName)) || empty($film->getUniqueAlt($this->sourceName)) ) {
            throw new \InvalidArgumentException(__FUNCTION__." Film must have uniqueName (".$film->getUniqueName($this->sourceName).") and uniqueAlt (".$film->getUniqueAlt($this->sourceName).") for ".$this->sourceName);
        }
        
        $uniqueName = $film->getUniqueName($this->sourceName);
        $uniqueEpisode = $film->getUniqueEpisode($this->sourceName);
        $uniqueAlt = $film->getUniqueAlt($this->sourceName);

        $episodeParam = "&episode=$uniqueEpisode";
        if (empty($uniqueEpisode)) {
            $episodeParam = "";
        }

        $contentParam = "movies";
        if ($film->getContentType() == FILM::CONTENT_TV) {
            $contentParam = "full-episodes";
        }
        
        return "/watch/$uniqueAlt/$uniqueName/$contentParam#filter=online$episodeParam";
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

        return "/search?query=$query&limit=15&or=true&resources=odol";
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
    protected function getDetailPageRegexForTitle($contentType = Film::CONTENT_FILM) {
        return '/<meta property="og:title" content="(.+)"\/>/';
    }

    /**
     * Regular expression to find the film year in film detail HTML page
     *
     * @return string Regular expression to find the film year in film detail HTML page
     */
    protected function getDetailPageRegexForYear() {
        return '/<span itemprop="startDate"[^>.]*>([^<]+)/';
    }

    /**
     * Regular expression to find the image in film detail HTML page
     *
     * @return string Regular expression to find the image in film detail HTML page
     */
    protected function getDetailPageRegexForImage() {
        return '/<div class="entity-image movie">\s<img src="([^"]+)/';
    }

    /**
     * Regular expression to find Content Type in film detail HTML page
     *
     * @return string Regular expression to find Content Type in film detail HTML page
     */
    protected function getDetailPageRegexForContentType() {
        return '/<div class="episode_panel">[\s]*<table id="([^"]+)/';
    }

    /**
     * Regular expression to find the film season in film detail HTML page
     *
     * @return string Regular expression to find the film season in film detail HTML page
     */
    protected function getDetailPageRegexForSeason() {
        return '/<dd class="details-season-episode">S([\d]+)/';
    }

    /**
     * Regular expression to find the episode title in film detail HTML page
     *
     * @return string Regular expression to find the episode title in film detail HTML page
     */
    protected function getDetailPageRegexForEpisodeTitle() {
        return '/<dd class="details-title"><h3>([^<]+)\s</';
    }

    /**
     * Regular expression to find the episode number in film detail HTML page
     *
     * @return string Regular expression to find the film season in film detail HTML page
     */
    protected function getDetailPageRegexForEpisodeNumber() {
        return '/<dd class="details-season-episode">[^<]*Ep([\d]+)/';
    }

    /**
     * Regular expression to find Film Id in film detail HTML page
     *
     * @return string Regular expression to find Film Id in film detail HTML page
     */
    protected function getDetailPageRegexForUniqueName() {
        return '/class="details-button">\s*<a[^>]*href="\/watch\/[^\/]+\/([^\/]+)/';
    }

    /**
     * Regular expression to find episode Id in film detail HTML page
     *
     * @return string Regular expression to find episode Id in film detail HTML page
     */
    protected function getDetailPageRegexForUniqueEpisode() {
        return '';
    }

    /**
     * Regular expression to find alternate Id in film detail HTML page
     *
     * @return string Regular expression to find alternate Id in film detail HTML page
     */
    protected function getDetailPageRegexForUniqueAlt() {
        return '/class="details-button">\s*<a[^>]*href="\/watch\/([^\/]+)/';
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
        $uniqueName = null;
        $uniqueAlt = null;
        
        $regex = $this->getSearchPageRegexForUniqueName($title, $film->getYear());
        if (!empty($regex) && 0 < preg_match($regex, $page, $matches)) {
            $uniqueName = $matches[1];
            $film->setUniqueName($uniqueName, $this->sourceName);
        }
        $regex = $this->getSearchPageRegexForUniqueAlt($title, $film->getYear());
        if (!empty($regex) && 0 < preg_match($regex, $page, $matches)) {
            $uniqueAlt = $matches[1];
            $film->setUniqueAlt($uniqueAlt, $this->sourceName);
        }
        
        if (empty($uniqueName) || empty($uniqueAlt)) {
            return false;
        }
        
        return true;
    }

    protected function getSearchPageRegexForUniqueName($title, $year)
    {
        $startWithThe = stripos($title, "the ");
        if ($startWithThe === 0) {
            // Title starts with "The ". Xfinity search uses ", The" at the end
            $title = substr($title, 4) . ", " . substr($title, 0, 3);
        }

        $specialChars = "\/\^\.\[\]\|\(\)\?\*\+\{\}"; // need to do '\' too
        $pattern = "|([$specialChars])|U";
        $escapedTitle = preg_replace($pattern, '\\\\${1}', $title);

        $nextYear = $year + 1;
        $regexYear = "($year|$nextYear)";

        return '/<h3><a href="\/watch\/[^\/]+\/([^\/]+)[^>]*>'.$escapedTitle.'[\s]*<span class="airDates">\('.$regexYear.'/i';
    }

    protected function getSearchPageRegexForUniqueAlt($title, $year)
    {
        $startWithThe = stripos($title, "the ");
        if ($startWithThe === 0) {
            // Title starts with "The ". Xfinity search uses ", The" at the end
            $title = substr($title, 4) . ", " . substr($title, 0, 3);
        }

        $specialChars = "\/\^\.\[\]\|\(\)\?\*\+\{\}"; // need to do '\' too
        $pattern = "|([$specialChars])|U";
        $escapedTitle = preg_replace($pattern, '\\\\${1}', $title);

        $nextYear = $year + 1;
        $regexYear = "($year|$nextYear)";

        return '/<h3><a href="\/watch\/([^\/]+)\/[^\/]+[^>]*>'.$escapedTitle.'[\s]*<span class="airDates">\('.$regexYear.'/i';
    }

    protected function streamAvailableFromDetailPage($page, $film, $onlyFree = true)
    {
        $source = $film->getSource($this->sourceName);
        $regex = '/<tr id="' . $film->getSource($this->sourceName)->getUniqueName() . '" class="[^"]*online[^"]*active[^"]*">/';
        if ($film->getContentType() == Film::CONTENT_TV) {
            $regex = '/class="[^"]*active[^"]*".*[\s]*.*data-cim-video-url="\/watch\/'.$source->getUniqueAlt().'\/'.$source->getUniqueName().'\/([0-9]{4,})/';
        }
        return (0 < preg_match($regex, $page, $matches));
    }

    public function getStreamUrlByPage($page, $film, $onlyFree = true)
    {
        $url = null;
        if ($this->streamAvailableFromDetailPage($page, $film, $onlyFree)) {
            $url = $this->getFilmUrl($film);
        }

        return $url;
    }

    public function convertContentType($contentType)
    {
        if (empty($contentType) || $contentType == "full_movie") {
            $contentType = Film::CONTENT_FILM;
        } elseif (substr($contentType, 0, 6) == "Season") {
            $contentType = Film::CONTENT_TV;
        } else {
            $contentType = Film::CONTENT_FILM;
        }

        return $contentType;
    }
}