<?php
/**
 * SiteChild class for testing as a concrete Site child class.
 */
namespace RatingSync;

require_once "../Site.php";
require_once "../Imdb.php";
require_once "SiteTest.php";

class SiteChild extends \RatingSync\Site {
    public function __construct($username)
    {
        parent::__construct($username);
        $this->http = new HttpImdb(TEST_SITE_USERNAME);
        $this->sourceName = Constants::SOURCE_IMDB;
        $this->dateFormat = Imdb::IMDB_DATE_FORMAT;
        $this->maxCriticScore = 100;
        if (!$this->validateAfterConstructor()) {
            throw \Exception("Invalid SiteChild contructor");
        }
    }

    function _getHttp() { return $this->http; }
    function _getSourceName() { return $this->sourceName; }
    
    function _setHttp($http) { $this->http = $http; }
    function _setSourceName($sourceName) { $this->sourceName = $sourceName; }

    function _validateAfterConstructor() { return $this->validateAfterConstructor(); }
    function _parseDetailPageForTitle($page, $film, $overwrite) { return $this->parseDetailPageForTitle($page, $film, $overwrite); }
    function _parseDetailPageForFilmYear($page, $film, $overwrite) { return $this->parseDetailPageForFilmYear($page, $film, $overwrite); }
    function _parseDetailPageForImage($page, $film, $overwrite) { return $this->parseDetailPageForImage($page, $film, $overwrite); }
    function _parseDetailPageForContentType($page, $film, $overwrite) { return $this->parseDetailPageForContentType($page, $film, $overwrite); }
    function _parseDetailPageForFilmName($page, $film, $overwrite) { return $this->parseDetailPageForFilmName($page, $film, $overwrite); }
    function _parseDetailPageForUrlName($page, $film, $overwrite) { return $this->parseDetailPageForUrlName($page, $film, $overwrite); }
    function _parseDetailPageForRating($page, $film, $overwrite) { return $this->parseDetailPageForRating($page, $film, $overwrite); }
    function _parseDetailPageForGenres($page, $film, $overwrite) { return $this->parseDetailPageForGenres($page, $film, $overwrite); }
    function _parseDetailPageForDirectors($page, $film, $overwrite) { return $this->parseDetailPageForDirectors($page, $film, $overwrite); }

    // Abstract Function based on \RatingSync\Imdb::getRatingPageUrl
    protected function getRatingPageUrl($args) {
        $pageIndex = $args['pageIndex'];
        $startIndex = (($pageIndex - 1) * 100) + 1;
        return '/user/'.urlencode($this->username).'/ratings?start='.$startIndex.'&view=detail&sort=title:asc';
    }

    // Abstract Function returns 2 films
    protected function getFilmsFromRatingsPage($page, $details = false, $refreshCache = 0) {
        $film = new Film($this->http);
        $film2 = new Film($this->http);

        $rating = new Rating($this->sourceName);
        $rating->setYourScore(8);
        $rating->setYourRatingDate(new \DateTime('2015-01-02'));
        $film->setRating($rating, $this->sourceName);
        $film->setTitle("Site Title1");
        $film->setFilmName("Site_FilmName1", $this->sourceName);
        $film->setImage("Site_Image1");
        $film->setImage("Site_Image1", $this->sourceName);
        $film->setContentType(\RatingSync\Film::CONTENT_FILM);

        $rating2 = new Rating($this->sourceName);
        $rating2->setYourScore(7);
        $rating2->setYourRatingDate(new \DateTime('2015-01-03'));
        $film2->setRating($rating2, $this->sourceName);
        $film2->setTitle("Site Title2");
        $film2->setFilmName("Site_FilmName2", $this->sourceName);
        $film2->setImage("Site_Image2");
        $film2->setImage("Site_Image2", $this->sourceName);
        $film2->setContentType(\RatingSync\Film::CONTENT_FILM);

        if ($details) {
            $film->setYear(1900);
            $film->setUrlName("Site_UrlName1", $this->sourceName);
            $film->addGenre("Site_Genre1.1");
            $film->addGenre("Site_Genre1.2");
            $film->addDirector("Site_Director1.1");
            $film->addDirector("Site_Director1.2");
            $rating->setSuggestedScore(2);
            $rating->setCriticScore(3);
            $rating->setUserScore(4);
            $film->setRating($rating, $this->sourceName);
            
            $film2->setYear(1902);
            $film2->setUrlName("Site_UrlName2", $this->sourceName);
            $film2->addGenre("Site_Genre2.1");
            $film2->addDirector("Site_Director2.1");
            $rating2->setSuggestedScore(3);
            $rating2->setCriticScore(4);
            $rating2->setUserScore(5);
            $film2->setRating($rating2, $this->sourceName);
        }

        $films = array($film, $film2);
        return $films;
    }

    // Abstract Function based on \RatingSync\Imdb::getNextRatingPageNumber
    protected function getNextRatingPageNumber($page) {
        if (0 == preg_match('@Page (\d+) of (\d+)@', $page, $matches)) {
            return false;
        }
        $currentPageNumber = $matches[1];
        $totalPages = $matches[2];

        if ($currentPageNumber == $totalPages) {
            return false;
        }

        return $currentPageNumber + 1;
    }

    // Abstract Function based on \RatingSync\Imdb::getFilmDetailPageUrl
    protected function getFilmDetailPageUrl($film) { return '/title/'.$film->getFilmName($this->sourceName).'/'; }

    // Abstract Function based on \RatingSync\Imdb::getDetailPageRegexForTitle
    protected function getDetailPageRegexForTitle() { return '/<title>(.*) \(.*\d\d\d\d[^\)]*\) - IMDb<\/title>/'; }

    // Abstract Function based on \RatingSync\Imdb::getDetailPageRegexForYear
    protected function getDetailPageRegexForYear() { return '/<title>.* \([^\d]*(\d\d\d\d)[^\)]*\) - IMDb<\/title>/'; }

    // Abstract Function based on \RatingSync\Imdb::getDetailPageRegexForImage
    protected function getDetailPageRegexForImage() { return '/title="[^\(]* \(\d\d\d\d\) Poster"\nsrc="([^"]+)"/'; }

    // Abstract Function based on \RatingSync\Imdb::getDetailPageRegexForContentType
    protected function getDetailPageRegexForContentType() { return '/<div class="infobar">\s*([a-zA-Z \-\/]+)\s*&nbsp;<</'; }

    // Abstract Function based on \RatingSync\Imdb::getDetailPageRegexForFilmName
    protected function getDetailPageRegexForFilmName($film) { return '/<meta property="og:url" content=".*\/(.+)\/"/'; }

    // Abstract Function based on \RatingSync\Imdb::getDetailPageRegexForUrlName
    protected function getDetailPageRegexForUrlName() { return ''; }

    // Abstract Function based on \RatingSync\Imdb::getDetailPageRegexForYourScore
    protected function getDetailPageRegexForYourScore($film) { return '/<span class="rating-rating rating-your"><span class="value">(\d\d?)<\/span>/'; }

    // Abstract Function based on \RatingSync\Imdb::getDetailPageRegexForRatingDate
    protected function getDetailPageRegexForRatingDate() { return ''; }

    // Abstract Function based on \RatingSync\Imdb::getDetailPageRegexForSuggestedScore
    protected function getDetailPageRegexForSuggestedScore($film) { return ''; }

    // Abstract Function based on \RatingSync\Imdb::getDetailPageRegexForCriticScore
    protected function getDetailPageRegexForCriticScore() { return '/provided by Metacritic\.com" > (\d\d?)\/100/'; }

    // Abstract Function based on \RatingSync\Imdb::getDetailPageRegexForUserScore
    protected function getDetailPageRegexForUserScore() { return '/<span itemprop="ratingValue">(\d\.?\d?)<\/span>/'; }

    // Abstract Function based on \RatingSync\Imdb::parseDetailPageForGenres
    protected function parseDetailPageForGenres($page, $film, $overwrite)
    {
        if (!$overwrite && !empty($film->getGenres())) {
            return false;
        }
        $originalGenres = $film->getGenres();
        $didFindGenres = false;
        
        if ($overwrite || empty($film->getGenres())) {
            $film->removeAllGenres();
            
            preg_match_all('/<span class="itemprop" itemprop="genre">([^<]*)<\/span>/', $page, $genreMatches);
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

    // Abstract Function based on \RatingSync\Imdb::parseDetailPageForDirectors
    protected function parseDetailPageForDirectors($page, $film, $overwrite)
    {
        if (!$overwrite && !empty($film->getDirectors())) {
            return false;
        }
        $originalDirectors = $film->getDirectors();
        $didFindDirectors = false;
        
        if ($overwrite || empty($film->getDirectors())) {
            $film->removeAllDirectors();
            if (0 < preg_match('/<h4 class="inline">Director[s]?:<\/h4>(.*?)Writer/s', $page, $sectionMatches)) {
                preg_match_all('/itemprop="name">([^<]*)</', $sectionMatches[1], $directorMatches);
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
}

?>
