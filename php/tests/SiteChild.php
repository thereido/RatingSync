<?php
/**
 * SiteChild class for testing as a concrete Site child class.
 */
namespace RatingSync;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "Site.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "Imdb.php";
require_once "SiteTest.php";

class SiteChild extends \RatingSync\Site {
    public function __construct($username)
    {
        parent::__construct($username);
        $this->sourceName = Constants::SOURCE_IMDB;
        $this->http = new Http(Http::SITE_SOURCE, $this->sourceName, $username);
        $this->dateFormat = Imdb::IMDB_DATE_FORMAT;
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
    function _parseDetailPageForUniqueName($page, $film, $overwrite) { return $this->parseDetailPageForUniqueName($page, $film, $overwrite); }
    function _parseDetailPageForGenres($page, $film, $overwrite) { return $this->parseDetailPageForGenres($page, $film, $overwrite); }
    function _parseDetailPageForDirectors($page, $film, $overwrite) { return $this->parseDetailPageForDirectors($page, $film, $overwrite); }

    // Abstract Function based on \RatingSync\Imdb::getFilmDetailPageUrl
    protected function getFilmDetailPageUrl($film) { return '/title/'.$film->getUniqueName($this->sourceName).'/'; }

    // Abstract Function based on \RatingSync\Imdb::getDetailPageRegexForTitle
    protected function getDetailPageRegexForTitle() { return '/<title>(.*) \(.*\d\d\d\d[^\)]*\) - IMDb<\/title>/'; }

    // Abstract Function based on \RatingSync\Imdb::getDetailPageRegexForYear
    protected function getDetailPageRegexForYear() { return '/<title>.* \([^\d]*(\d\d\d\d)[^\)]*\) - IMDb<\/title>/'; }

    // Abstract Function based on \RatingSync\Imdb::getDetailPageRegexForImage
    protected function getDetailPageRegexForImage() { return '/title="[^\(]*[ \(\d\d\d\d\)]? Poster"\nsrc="([^"]+)"/'; }

    // Abstract Function based on \RatingSync\Imdb::getDetailPageRegexForContentType
    protected function getDetailPageRegexForContentType() { return '/<div class="infobar">\s*([a-zA-Z \-\/]+)\s*&nbsp;<</'; }

    // Abstract Function based on \RatingSync\Imdb::getDetailPageRegexForUniqueName
    protected function getDetailPageRegexForUniqueName() { return '/<meta property="og:url" content=".*\/(.+)\/"/'; }

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

    // Abstract Function based on \RatingSync\Imdb::getNextRatingPageNumber
    protected function getSearchUrl($args) { return "/find?ref_=nv_sr_fn&q='Frozen'&s=all'"; }
    
    // Abstract Function based on \RatingSync\Imdb::getStreamingUrl
    public function getStreamingUrl($film, $onlyFree = true) { return null; }
}

?>
