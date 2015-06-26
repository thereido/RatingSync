<?php
/**
 * Jinni class
 */
namespace RatingSync;

require_once "Constants.php";
require_once "Film.php";
require_once "HttpJinni.php";
require_once "Rating.php";
require_once "Site.php";

/**
 * Communicate to/from the Jinni website
 * - Search for films and tv shows
 * - Get details for each and rate it
 * - Export/Import ratings.
 */
class Jinni extends Site
{
    const JINNI_DATE_FORMAT = "n/j/y";

    public function __construct($username)
    {
        parent::__construct($username);
        $this->http = new HttpJinni($username);
        $this->sourceName = Constants::SOURCE_JINNI;
        $this->dateFormat = self::JINNI_DATE_FORMAT;

        if (!$this->validateAfterConstructor()) {
            throw \Exception("Invalid Jinni contructor");
        }
    }

    /**
     * Return a film's unique attribute.  This the attr available from ratings pages
       and from a film detail page.  In most sites the Film ID is always available, but
       Jinni is has URL Name and don't always Film ID.  The Site implentation returns
       Film::getFilmId().  Child classes can return something else.
     *
     * @param \RatingSync\Film $film get the attr from this film
     *
     * @return string unique attribute
     */
    public function getFilmUniqueAttr($film)
    {
        if (!is_null($film) && ($film instanceof Film)) {
            return $film->getUrlName($this->sourceName);
        }
    }

    /**
     * Return the rating page's URL within a website. The URL does not
     * include the base URL.  
     *
     * @param array $args 'pageIndex' key for multiple pages of ratings
     *
     * @return string URL of a rating page
     */
    protected function getRatingPageUrl($args)
    {
        if (empty($args) || !is_array($args) || !array_key_exists('pageIndex', $args) || !is_int($args['pageIndex'])) {
            throw new \InvalidArgumentException('$args must be an array with key "pageIndex" and value an int');
        }

        $pageIndex = $args['pageIndex'];
        return '/user/'.urlencode($this->username).'/ratings?pagingSlider_index='.$pageIndex;
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
        $filmSections = explode('<div class="ratings_cell5">', $page);
        array_shift($films);
        foreach ($filmSections as $filmSection) {
            // Film title, URL name, Content Type (Movie/TV/ShortFilm)
            if (0 === preg_match('@<div class="ratings_cell2" title="([^"]+)">[\s\n\r]+<a href="http://www.jinni.com/(movies|tv|shorts)/([^/]+)/"@', $filmSection, $matches)) {
                continue;
            }
            $title = html_entity_decode($matches[1], ENT_QUOTES, "utf-8");
            $contentType = $matches[2];
            $urlName = $matches[3];
            
            // Rating
            if (0 === preg_match('@RatedORSuggestedValue: (\d+)@', $filmSection, $matches)) {
                continue;
            }
            $yourScore = $matches[1];
            
            // Rating Date
            if (0 === preg_match('@<div class="ratings_cell4"><span[^>]+>(\d+\/\d+\/\d+)<@', $filmSection, $matches)) {
                continue;
            }
            $ratingDate = $matches[1];

            // Film ID
            if (0 === preg_match('@contentId: "(\d+)@', $filmSection, $matches)) {
                continue;
            }
            $filmId = $matches[1];

            // Image
            if (0 === preg_match('@<img src="(http://media[\d]*.jinni.com/(?:tv|movie|shorts|no-image)/[^/]+/[^"]+)"@', $filmSection, $matches)) {
                continue;
            }
            $image = $matches[1];

            $rating = new Rating(Constants::SOURCE_JINNI);
            $rating->setYourScore($yourScore);
            $rating->setYourRatingDate(\DateTime::createFromFormat(self::JINNI_DATE_FORMAT, $ratingDate));

            $films[] = $film = new Film($this->http);
            $film->setRating($rating, Constants::SOURCE_JINNI);
            $film->setTitle($title);
            $film->setFilmId($filmId, Constants::SOURCE_JINNI);
            $film->setUrlName($urlName, Constants::SOURCE_JINNI);
            $film->setImage($image);
            $film->setImage($image, Constants::SOURCE_JINNI);
            if ($contentType == 'movies') {
                $film->setContentType(\RatingSync\Film::CONTENT_FILM);
            } elseif ($contentType == 'tv') {
                $film->setContentType(\RatingSync\Film::CONTENT_TV);
            } elseif ($contentType == 'shorts') {
                $film->setContentType(\RatingSync\Film::CONTENT_SHORTFILM);
            }

            if ($details) {
                $this->getFilmDetailFromWebsite($film, false, $refreshCache);
            }
        }

        return $films;
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
        if (0 == preg_match('@pagingSlider\.addPage\(\d+,false\);[\n|\t]+\$\(document\)@', $page, $matches)) {
            return false;
        }

        if (0 == preg_match('@<input type="hidden" name="pagingSlider_index" id="pagingSlider_index" value="(\d+)" />@', $page, $matches)) {
            return false;
        }
        
        return $matches[1] + 1;
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
        } elseif ( is_null($film->getContentType()) || is_null($film->getUrlName($this->sourceName)) ) {
            throw new \InvalidArgumentException('Function getFilmDetailPageUrl must have Content Type and URL Name');
        }

        switch ($film->getContentType()) {
        case Film::CONTENT_FILM:
            $type = 'movies';
            break;
        case Film::CONTENT_TV:
            $type = 'tv';
            break;
        case Film::CONTENT_SHORTFILM:
            $type = 'shorts';
            break;
        default:
            $type = null;
        }

        $urlName = $film->getUrlName(Constants::SOURCE_JINNI);
        return '/'.$type.'/'.$urlName;
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
        
        $film->removeAllGenres();
        $groupSections = explode('<div class="right_genomeGroup">', $page);
        array_shift($groupSections);
        foreach ($groupSections as $groupSection) {
            if (!stripos($groupSection, "Genres")) {
                continue;
            }
            $geneSections = explode('right_genomeLink', $groupSection);
            array_shift($geneSections);
            foreach ($geneSections as $geneSection) {
                // Letters, Spaces, Hyphens, Numbers
                if (0 < preg_match('@([a-zA-Z \-\d]+)[,]?<\/a>@', $geneSection, $matches)) {
                    $film->addGenre(html_entity_decode($matches[1], ENT_QUOTES, "utf-8"));
                    $didFindGenres = true;
                }
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
        if (!$overwrite && !empty($film->getDirectors())) {
            return false;
        }
        $originalDirectors = $film->getDirectors();
        $didFindDirectors = false;
        
        if ($overwrite || empty($film->getDirectors())) {
            $film->removeAllDirectors();
            if (0 < preg_match('@<b>Directed by:<\/b>(.+)@', $page, $directorLines)) {
                preg_match_all("@<[^>]+>(.*)</[^>]+>@U", $directorLines[1], $directorMatches);
                $directors = $directorMatches[1];
                foreach ($directors as $director) {
                    $film->addDirector(html_entity_decode($director, ENT_QUOTES, "utf-8"));
                    $didFindDirectors = true;
                }
            }
        }

        if (!$didFindDirectors) {
            if (!empty($originalDirectors)) {
                foreach ($originalDirectors as $director) {
                    $film->addDirector($director);
                }
            }
            return false;
        }
        return true;
    }

    /**
     * Regular expression to find the film title in film detail HTML page
     *
     * @return string Regular expression to find the film title in film detail HTML page
     */
    protected function getDetailPageRegexForTitle()
    {
        return '@<h1 class=\"title1\">(.*), \d\d\d\d[^<]*<\/h1>@';
    }

    /**
     * Regular expression to find the film year in film detail HTML page
     *
     * @return string Regular expression to find the film year in film detail HTML page
     */
    protected function getDetailPageRegexForYear()
    {
        return '@<h1 class=\"title1\">.*, (\d\d\d\d)[^<]*<\/h1>@';
    }

    /**
     * Regular expression to find the image in film detail HTML page
     *
     * @return string Regular expression to find the image in film detail HTML page
     */
    protected function getDetailPageRegexForImage()
    {
        return '@<img src="(http://media[\d]*.jinni.com/(?:tv|movie|shorts|no-image)/[^/]+/[^"]+)@';
    }

    /**
     * Regular expression to find Content Type in film detail HTML page. Return is null
       because the detail page does not show it (in Jinni).
     *
     * @return string null
     */
    protected function getDetailPageRegexForContentType()
    {
        return null;
    }

    /**
     * Regular expression to find Film Id in film detail HTML page
     *
     * @param \RatingSync\Film $film Film data
     *
     * @return string Regular expression to find Film Id in film detail HTML page
     */
    protected function getDetailPageRegexForFilmId($film)
    {
        if (is_null($film) || !($film instanceof Film) || empty($film->getUrlName($this->sourceName))) {
            throw new \InvalidArgumentException('Film param must have a URL Name');
        }

        return '/{[^}]+contentId: \"(.+)\"[^}]+uniqueName: \"' . $film->getUrlName($this->sourceName) . '\"/';
    }

    /**
     * Regular expression to find URL Name in film detail HTML page
     *
     * @return string Regular expression to find URL Name in film detail HTML page
     */
    protected function getDetailPageRegexForUrlName()
    {
        return '/<a href=\".*\/(.*)\/\" class.*Overview<\/a>/';   
    }

    /**
     * Regular expression to find your rating score in film detail HTML page
     *
     * @param \RatingSync\Film $film Film data
     *
     * @return string Regular expression to find your rating score in film detail HTML page
     */
    protected function getDetailPageRegexForYourScore($film)
    {
        if (is_null($film) || !($film instanceof Film) || empty($film->getUrlName($this->sourceName))) {
            throw new \InvalidArgumentException('Film param must have a URL Name');
        }

        return '/uniqueName: \"' . $film->getUrlName($this->sourceName) . '\"[^}]+isRatedRating: true[^}]+RatedORSuggestedValue: (\d[\d]?\.?\d?)/';
    }

    /**
     * Regular expression to find your rating date in film detail HTML page
     *
     * @return string Regular expression to find your rating date in film detail HTML page
     */
    protected function getDetailPageRegexForRatingDate()
    {
        return '';   
    }

    /**
     * Regular expression to find suggested score in film detail HTML page
     *
     * @param \RatingSync\Film $film Film data
     *
     * @return string Regular expression to find suggested score in film detail HTML page
     */
    protected function getDetailPageRegexForSuggestedScore($film)
    {
        if (is_null($film) || !($film instanceof Film) || empty($film->getUrlName($this->sourceName))) {
            throw new \InvalidArgumentException('Film param must have a URL Name');
        }

        return '/uniqueName: \"' . $film->getUrlName($this->sourceName) . '\"[^}]+isSugggestedRating: true[^}]+RatedORSuggestedValue: (\d[\d]?\.?\d?)/';
    }

    /**
     * Regular expression to find critic score in film detail HTML page
     *
     * @return string Regular expression to find critic score in film detail HTML page
     */
    protected function getDetailPageRegexForCriticScore()
    {
        return '';   
    }

    /**
     * Regular expression to find user score in film detail HTML page
     *
     * @return string Regular expression to find user score in film detail HTML page
     */
    protected function getDetailPageRegexForUserScore()
    {
        return '';   
    }
}