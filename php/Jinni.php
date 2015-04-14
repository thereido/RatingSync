<?php
/**
 * Jinni class
 */
namespace RatingSync;

require_once __DIR__."/Film.php";
require_once __DIR__."/HttpJinni.php";
require_once __DIR__."/Rating.php";

/**
 * Communicate to/from the Jinni website
 * - Search for films and tv shows
 * - Get details for each and rate it
 * - Export/Import ratings.
 */
class Jinni
{
    public $http;
    protected $username;

    public function __construct($username)
    {
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
            $films[] = $film = new \RatingSync\Film($this->http);
            $rating = new \RatingSync\Rating(\RatingSync\Rating::SOURCE_JINNI);
            $rating->setFilmId($result['id']);
            $film->setRating($rating);
            $film->setName($result['name']);
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
            // URL name and film name
            if (0 === preg_match('@<div class="ratings_cell2" title="([^"]+)">[\s\n\r]+<a href="http://www.jinni.com/(?:movies|tv)/([^/]+)/" class="ratings_link" onclick="">([^"]+)</a>@', $filmSection, $matches)) {
                continue;
            }
            
            // Rating
            if (0 === preg_match('@RatedORSuggestedValue: (\d+)@', $filmSection, $ratingMatches)) {
                continue;
            }
            
            // Rating Date
            if (0 === preg_match('@<div class="ratings_cell4"><span[^>]+>(\d+\/\d+\/\d+)<@', $filmSection, $ratingDateMatches)) {
                continue;
            }

            // Film ID
            if (0 === preg_match('@contentId: "(\d+)@', $filmSection, $filmIdMatches)) {
                continue;
            }

            // Image and Content type (Movie/TV/ShortFilm)
            if (0 === preg_match('@<img src="(http://media1.jinni.com/(tv|movie|shorts)/[^/]+/[^"]+)"@', $filmSection, $contentTypeMatches)) {
                continue;
            }

            $rating = new \RatingSync\Rating(\RatingSync\Rating::SOURCE_JINNI);
            $rating->setYourScore($ratingMatches[1]);
            $rating->setYourRatingDate($ratingDateMatches[1]);
            $rating->setFilmId($filmIdMatches[1]);

            $films[] = $film = new \RatingSync\Film($this->http);
            $film->setRating($rating);
            $film->setName(htmlspecialchars_decode($matches[1]));
            $film->setUrlName($matches[2], \RatingSync\Rating::SOURCE_JINNI);
            $film->setImage($contentTypeMatches[1]);
            if ($contentTypeMatches[2] == 'movie') {
                $film->setContentType(\RatingSync\Film::CONTENT_FILM);
            } elseif ($contentTypeMatches[2] == 'tv') {
                $film->setContentType(\RatingSync\Film::CONTENT_TV);
            } elseif ($contentTypeMatches[2] == 'shorts') {
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

    public function getFilmDetailFromWebsite($film, $overwrite = false)
    {
        // See which detail needed from the website
        $overwriteYear = true;
        $overwriteRating = true;
        $needToRetrieveFromWebsite = true;
        if (!$overwrite) {
            $overwriteYear = false;
            $overwriteRating = false;
            $needToRetrieveFromWebsite = false;
            if ($film->getYear() == null) {
                $overwriteYear = true;
                $needToRetrieveFromWebsite = true;
            }
            if ($film->getRating(\RatingSync\Rating::SOURCE_JINNI) == null) {
                $overwriteRating = true;
                $needToRetrieveFromWebsite = true;
            }
        }

        if (!$needToRetrieveFromWebsite) {
            return;
        }

        $urlName = $film->getUrlName(\RatingSync\Rating::SOURCE_JINNI);

        switch ($film->getContentType()) {
        case \RatingSync\Film::CONTENT_FILM:
            $type = 'movies';
            break;
        case \RatingSync\Film::CONTENT_TV:
            $type = 'tv';
            break;
        case \RatingSync\Film::CONTENT_SHORTFILM:
            $type = 'shorts';
            break;
        default:
            $type = null;
        }
        $page = $this->http->getPage('/'.$type.'/'.$urlName);

        // Year
        if ($overwriteYear) {
            if (0 < preg_match('@<h1 class=\"title1\">.*, (\d\d\d\d)<\/h1>@', $page, $matches)) {
                $film->setYear($matches[1]);
            }
        }

        // Rating
        // FIXME
    }
}