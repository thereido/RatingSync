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
        $this->sourceName = Constants::SOURCE_OMDBAPI;
        $this->http = new Http($this->sourceName, $username);
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

    // Abstract Function based on \RatingSync\OmdbApi::getFilmDetailPageUrl
    protected function getFilmDetailPageUrl($film) { return "&i=" . $film->getUniqueName($this->sourceName); }

    // Abstract Function based on \RatingSync\OmdbApi::getDetailPageRegexForTitle
    protected function getDetailPageRegexForTitle($contentType = Film::CONTENT_FILM) { return ""; }

    // Abstract Function based on \RatingSync\OmdbApi::getDetailPageRegexForYear
    protected function getDetailPageRegexForYear() { return ""; }

    // Abstract Function based on \RatingSync\OmdbApi::getDetailPageRegexForImage
    protected function getDetailPageRegexForImage() { return ""; }

    // Abstract Function based on \RatingSync\OmdbApi::getDetailPageRegexForContentType
    protected function getDetailPageRegexForContentType() { return ""; }

    // Abstract Function based on \RatingSync\OmdbApi::getDetailPageRegexForSeason
    protected function getDetailPageRegexForSeason() { return ""; }

    // Abstract Function based on \RatingSync\OmdbApi::getDetailPageRegexForEpisodeNumber
    protected function getDetailPageRegexForEpisodeNumber() { return ""; }

    // Abstract Function based on \RatingSync\OmdbApi::getDetailPageRegexForEpisodeTitle
    protected function getDetailPageRegexForEpisodeTitle() { return ""; }

    // Abstract Function based on \RatingSync\OmdbApi::getDetailPageRegexForUniqueName
    protected function getDetailPageRegexForUniqueName() { return ""; }

    // Abstract Function based on \RatingSync\OmdbApi::getDetailPageRegexForUniqueEpisode
    protected function getDetailPageRegexForUniqueEpisode() { return ""; }

    // Abstract Function based on \RatingSync\OmdbApi::getDetailPageRegexForUniqueAlt
    protected function getDetailPageRegexForUniqueAlt() { return ""; }

    // Abstract Function based on \RatingSync\OmdbApi::parseDetailPageForGenres
    protected function parseDetailPageForGenres($page, $film, $overwrite) { return ""; }

    // Abstract Function based on \RatingSync\OmdbApi::parseDetailPageForDirectors
    protected function parseDetailPageForDirectors($page, $film, $overwrite) { return ""; }

    // Abstract Function based on \RatingSync\OmdbApi::getNextRatingPageNumber
    protected function getSearchUrl($args)
    {
        if (empty($args) || !is_array($args) || empty($args["query"]))
        {
            throw new \InvalidArgumentException('$args must be an array with key "query" (non-empty)');
        }
        
        $searchUrl = "&s=" . urlencode($args["query"]);

        return $searchUrl;
    }
}

?>
