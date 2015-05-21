<?php
/**
 * Jinni class
 */
namespace RatingSync;

require_once "Constants.php";
require_once "Film.php";
require_once "HttpJinni.php";
require_once "Rating.php";

/**
 * Communicate to/from the Jinni website
 * - Search for films and tv shows
 * - Get details for each and rate it
 * - Export/Import ratings.
 */
class Jinni
{
    const JINNI_DATE_FORMAT = "n/j/y";

    public $http;
    protected $username;

    public function __construct($username)
    {
        if (! (is_string($username) && 0 < strlen($username)) ) {
            throw new \InvalidArgumentException('$username must be non-empty');
        }
        $this->http = new \RatingSync\HttpJinni($username);
        $this->username = $username;
    }

    /**
     * Get every rating on $this->username's account
     *
     * @param int|null $limitPages Limit the number of pages of ratings
     * @param int|1    $beginPage  First page of rating results
     * @param bool     $details    Bring full film details (slower)
     *
     * @return array of \RatingSync\Film
     */
    public function getRatings($limitPages = null, $beginPage = 1, $details = false)
    {
        $films = array();
        // Get one page of ratings
        $page = $this->http->getPage('/user/'.urlencode($this->username).'/ratings?pagingSlider_index='.$beginPage);
        $films = $this->getFilmsFromRatingsPage($page, $details);

        // Get the rest of rating pages
        // While... within the limit and still another page available
        $pageCount = 1;
        while (($limitPages == null || $limitPages > $pageCount) &&
                  ($nextPageNumber = $this->getNextRatingPageNumber($page))
              ) {
            $page = $this->http->getPage('/user/'.urlencode($this->username).'/ratings?pagingSlider_index='.$nextPageNumber);
            $films = array_merge($films, $this->getFilmsFromRatingsPage($page, $details));
            $pageCount++;
        }
        return $films;
    }

    /**
     * Search for a string on Jinni optionally fitered by $type
     * This searches using search suggestions.
     *
     * @param string      $searchStr What goes in the search box
     * @param string|null $type      Content type (movie,tv,etc)
     *
     * @see Film::validContentType()
     * @return array of \RatingSync\Film
     */
    public function getSearchSuggestions($searchStr, $type = null)
    {
        $films = array();
        $results = $this->http->searchSuggestions($searchStr, $type);
        foreach ($results as $result) {
            $films[] = $film = new Film($this->http);
            $rating = new Rating(Constants::SOURCE_JINNI);
            $film->setFilmId($result['id'], Constants::SOURCE_JINNI);
            $film->setRating($rating);
            $film->setTitle($result['title']);
            $film->setYear($result['year']);
            $film->setContentType($result['contentType']);
        }
        return $films;
    }

    protected function getFilmsFromRatingsPage($page, $details = false)
    {
        $films = array();
        $filmSections = explode('<div class="ratings_cell5">', $page);
        array_shift($films);
        foreach ($filmSections as $filmSection) {
            // Film title, URL name, Content Type (Movie/TV/ShortFilm)
            if (0 === preg_match('@<div class="ratings_cell2" title="([^"]+)">[\s\n\r]+<a href="http://www.jinni.com/(movies|tv|shorts)/([^/]+)/"@', $filmSection, $matches)) {
                continue;
            }
            $title = htmlspecialchars_decode($matches[1]);
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
                $this->getFilmDetailFromWebsite($film, false);
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

    public function getFilmDetailFromWebsite($film, $overwrite = true)
    {
        if (! $film instanceof Film ) {
            throw new \InvalidArgumentException('Function getFilmDetailFromWebsite must be given a Film object');
        } elseif ( is_null($film->getContentType()) || is_null($film->getUrlName(Constants::SOURCE_JINNI)) ) {
            throw new \InvalidArgumentException('Function getFilmDetailFromWebsite must have Content Type and URL Name');
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
        $page = $this->http->getPage('/'.$type.'/'.$urlName);

        $this->parseTitleFromDetailPage($page, $film, $overwrite);
        $this->parseFilmYearFromDetailPage($page, $film, $overwrite);
        $this->parseImageFromDetailPage($page, $film, $overwrite);
        $this->parseRatingFromDetailPage($page, $film, $overwrite);

        // Genres
        if ($overwrite || empty($film->getGenres())) {
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
                        $film->addGenre($matches[1]);
                    }
                }
            }
        }

        // Directors
        if ($overwrite || empty($film->getDirectors())) {
            $film->removeAllDirectors();
            if (0 < preg_match('@<b>Directed by:<\/b>(.+)@', $page, $directorLines)) {
                preg_match_all("@<[^>]+>(.*)</[^>]+>@U", $directorLines[1], $directorMatches);
                $directors = $directorMatches[1];
                foreach ($directors as $director) {
                    $film->addDirector($director);
                }
            }
        }
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
    protected function parseTitleFromDetailPage($page, $film, $overwrite)
    {
        if (!$overwrite && !is_null($film->getTitle())) {
            return false;
        }

        if (0 === preg_match('@<h1 class=\"title1\">(.*), \d\d\d\d<\/h1>@', $page, $matches)) {
            return false;
        }
        $film->setTitle($matches[1]);
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
    protected function parseFilmYearFromDetailPage($page, $film, $overwrite)
    {
        if (!$overwrite && !is_null($film->getYear())) {
            return false;
        }

        if (0 < preg_match('@<h1 class=\"title1\">.*, (\d\d\d\d)<\/h1>@', $page, $matches)) {
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
    protected function parseImageFromDetailPage($page, $film, $overwrite)
    {
        if (!$overwrite && !is_null($film->getImage())) {
            return false;
        }

        if (0 === preg_match('@<img src="(http://media[\d]*.jinni.com/(?:tv|movie|shorts|no-image)/[^/]+/[^"]+)@', $page, $matches)) {
            return false;
        }
        $film->setImage($matches[1]);
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
    protected function parseRatingFromDetailPage($page, $film, $overwrite)
    {
        $rating = $film->getRating(Constants::SOURCE_JINNI);
        $urlName = $film->getUrlName(Constants::SOURCE_JINNI);

        // Your score
        if ($overwrite || is_null($rating->getYourScore())) {
            $rating->setYourScore(null);
            if (0 < preg_match('/uniqueName: \"'.$urlName.'\"[^}]+isRatedRating: true/', $page, $matches)) {
                if (0 < preg_match('/uniqueName: \"'.$urlName.'\"[^}]+RatedORSuggestedValue: (\d[\d]?)/', $page, $matches)) {
                    $rating->setYourScore($matches[1]);
                }
            }
        }

        // Suggested score
        if ($overwrite || is_null($rating->getSuggestedScore())) {
            if (0 < preg_match('/uniqueName: \"'.$urlName.'\"[^}]+isSuggestedRating: true/', $page, $matches)) {
                if (0 < preg_match('/uniqueName: \"'.$urlName.'\"[^}]+RatedORSuggestedValue: (\d[\d]?)/', $page, $matches)) {
                    $rating->setSuggestedScore($matches[1]);
                }
            }
        }

        // Rating Date - not available in the detail page

        // Film ID
        if ($overwrite || is_null($film->getFilmId(Constants::SOURCE_JINNI))) {
            $film->setFilmId(null, Constants::SOURCE_JINNI);
            if (0 < preg_match('/uniqueName: \"'.$urlName.'\"[^}]+uniqueId: \"([^\"]+)\"/', $page, $matches)) {
                $film->setFilmId($matches[1], Constants::SOURCE_JINNI);
            }
        }

        // Critic Score - not applicable for Jinni

        // User Score - not applicable for Jinni
            
        $film->setRating($rating);
    }

    /**
     * Get the account's ratings from the website and write to a file/database
     *
     * @param string     $format   File format to write to (or database). Currently only XML.
     * @param string     $filename Write to a new (overwrite) file in the output directory
     * @param bool|false $detail   False brings only rating data. True also brings full detail (can take a long time).
     *
     * @return true for success, false for failure
     */
    public function exportRatings($format, $filename, $detail = false)
    {
        $films = $this->getRatings(null, 1, $detail);

        $filename =  __DIR__ . DIRECTORY_SEPARATOR . ".." . Constants::outputFilePath() . $filename;
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
}