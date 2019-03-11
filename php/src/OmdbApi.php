<?php
/**
 * OmdbApi class
 */
namespace RatingSync;

require_once "Site.php";

/**
 * Get data from the OMDb API website
 * - Search for films and tv shows
 * - Get details for each
 */
class OmdbApi extends \RatingSync\Site
{ 
    public function __construct()
    {
        parent::__construct("empty_username");
        $this->sourceName = Constants::SOURCE_OMDBAPI;
        $this->http = new Http($this->sourceName);
    }

    /**
     * Search for a unique film from the OMDb API. If successfull, take
     * info from OMDb and set it into the film object.
     *
     * @param \RatingSync\Film $film
     *
     * @return boolean success/failure
     */
    public function searchWebsiteForUniqueFilm($film)
    {
        if (!($film instanceof Film)) {
            throw new \InvalidArgumentException('$film must be an array with key "pageIndex" and value an int');
        }

        $result = null;
        $filmUrl = $this->getFilmUrl($film);
        if (!empty($filmUrl)) {
            $json = $this->http->getPage($filmUrl);
            $result = json_decode($json, true);
        }

        if (empty($result) || $result["Response"] != "True") {
            return false;
        }

        $film->setUniqueName($result["imdbID"], $this->sourceName);
        
        return true;
    }
    
    public function getFilmDetailFromWebsite($film, $overwrite = true, $refreshCache = Constants::USE_CACHE_NEVER)
    {
        if (is_null($film) || !($film instanceof Film) ) {
            throw new \InvalidArgumentException('arg1 must be a Film object');
        }
        
        $json = $this->getFilmDetailPage($film, $refreshCache, true);
        $filmJson = json_decode($json, true);
        if (empty($filmJson) || !is_array($filmJson) || $filmJson["Response"] == "False") {
            $errorMsg = "OMDb API request failed. Title=".$film->getTitle();
            $errorMsg .= ", Episode Title=".$film->getEpisodeTitle();
            $errorMsg .= ", Year=" . $film->getYear();
            $errorMsg .= ", UniqueName=" . $film->getUniqueName($this->sourceName);
            logDebug($errorMsg, __CLASS__."::".__FUNCTION__." ".__LINE__, true, $filmJson);
            throw new \Exception('OMDbApi search failed');
        }
        $this->printResultToLog($filmJson);

        // Get values from the API result
        $uniqueName = array_value_by_key("imdbID", $filmJson, "N/A");
        $parentId = null;
        $title = array_value_by_key("Title", $filmJson, "N/A");
        $episodeTitle = null;
        $year = array_value_by_key("Year", $filmJson, "N/A");
        if (!empty($year)) { $year = substr($year, 0, 4); };
        $image = array_value_by_key("Poster", $filmJson, "N/A");
        $userScore = array_value_by_key("imdbRating", $filmJson, "N/A");
        $seasonCount = array_value_by_key("totalSeasons", $filmJson, "N/A");
        $season = array_value_by_key("Season", $filmJson, "N/A");
        $episodeNum = array_value_by_key("Episode", $filmJson, "N/A");
        $genres = array_value_by_key("Genre", $filmJson);
        $directors = array_value_by_key("Director", $filmJson);
        $seriesID = array_value_by_key("seriesID", $filmJson, "N/A");

        $contentType = Film::CONTENT_FILM;
        $type = array_value_by_key("Type", $filmJson);
        if ("series" == $type) { $contentType = Film::CONTENT_TV_SERIES; }
        if ("episode" == $type) { $contentType = Film::CONTENT_TV_EPISODE; }

        if ($contentType == Film::CONTENT_TV_EPISODE) {
            // In RatingSync title is the series title and episodeTitle is separate
            // In OMDbAPI title is the episode title
            $episodeTitle = $title;
            $title = null;

            // Get the series' title
            $searchTerms = array("uniqueName" => $seriesID, "sourceName" => Constants::SOURCE_OMDBAPI);
            $seriesSearchResult = search($searchTerms);
            if (!empty($seriesSearchResult) && !empty($seriesSearchResult["match"])) {
                $seriesFilm = $seriesSearchResult["match"];
                $parentId = $seriesFilm->getId();
                $title = $seriesFilm->getTitle();
            }
        }

        $metacriticScore = array_value_by_key("Metascore", $filmJson, "N/A");
        if (empty($metacriticScore) || !is_numeric($metacriticScore)) {
            $metacriticScore = null;
        } else {
            $metacriticScore = $metacriticScore*10/100;
        }

        // Get the existing values
        $existingUniqueName = $film->getUniqueName($this->sourceName);
        $existingParentId = $film->getParentId();
        $existingTitle = $film->getTitle();
        $existingEpisodeTitle = $film->getEpisodeTitle();
        $existingYear = $film->getYear();
        $existingOMDbImage = $film->getImage($this->sourceName);
        $existingContentType = $film->getContentType();
        $existingSeasonCount = $film->getSeasonCount();
        $existingSeason = $film->getSeason();
        $existingEpisodeNum = $film->getEpisodeNumber();
        $existingUserScore = $film->getUserScore($this->sourceName);
        $existingCriticScore = $film->getCriticScore($this->sourceName);
        $existingGenreCount = count($film->getGenres());
        $existingDirectorCount = count($film->getDirectors());

        // Init/Replace the values when appropiate
        if ($overwrite || is_null($existingUniqueName)) { $film->setImage($uniqueName, $this->sourceName); }
        if ($overwrite || is_null($existingParentId)) { $film->setParentId($parentId); }
        if ($overwrite || is_null($existingTitle)) { $film->setTitle($title); }
        if ($overwrite || is_null($existingEpisodeTitle)) { $film->setEpisodeTitle($episodeTitle); }
        if ($overwrite || is_null($existingYear)) { $film->setYear($year); }
        if ($overwrite || is_null($existingOMDbImage)) { $film->setImage($image, $this->sourceName); }
        if ($overwrite || is_null($existingContentType)) { $film->setContentType($contentType); }
        if ($overwrite || is_null($existingSeasonCount)) { $film->setSeasonCount($seasonCount); }
        if ($overwrite || is_null($existingSeason)) { $film->setSeason($season); }
        if ($overwrite || is_null($existingEpisodeNum)) { $film->setEpisodeNumber($episodeNum); }
        if ($overwrite || is_null($existingUserScore)) { $film->setUserScore($userScore, $this->sourceName); }
        if ($overwrite || is_null($existingCriticScore)) { $film->setCriticScore($metacriticScore, $this->sourceName); }

        if ($overwrite || $existingGenreCount == 0) {
            $film->removeAllGenres();
            if ("N/A" != $genres) {
                $genreTok = strtok($genres, ",");
                while ($genreTok !== false) {
                    $film->addGenre(trim($genreTok));
                    $genreTok = strtok(",");
                }
            }
        }

        $existingDirectorCount = count($film->getDirectors());
        if ($overwrite || $existingDirectorCount == 0) {
            $film->removeAllDirectors();
            if ("N/A" != $directors) {
                $directorTok = strtok($directors, ",");
                while ($directorTok !== false) {
                    $film->addDirector(trim($directorTok));
                    $directorTok = strtok(",");
                }
            }
        }

        // Copy data from OMDb to IMDb
        $existingIMDbUniqueName = $film->getUniqueName(Constants::SOURCE_IMDB);
        $existingIMDbImage = $film->getImage(Constants::SOURCE_IMDB);
        $existingIMDbUserScore = $film->getUserScore(Constants::SOURCE_IMDB);
        $existingIMDbCriticScore = $film->getCriticScore(Constants::SOURCE_IMDB);
        if ($overwrite || is_null($existingIMDbUniqueName)) { $film->setUniqueName($uniqueName, Constants::SOURCE_IMDB); }
        if ($overwrite || is_null($existingIMDbImage)) { $film->setImage($image, Constants::SOURCE_IMDB); }
        if ($overwrite || is_null($existingIMDbUserScore)) { $film->setUserScore($userScore, Constants::SOURCE_IMDB); }
        if ($overwrite || is_null($existingIMDbCriticScore)) { $film->setCriticScore($metacriticScore, Constants::SOURCE_IMDB); }
    }

    protected function printResultToLog($filmJson) {
        $title = array_value_by_key("Title", $filmJson);
        $year = array_value_by_key("Year", $filmJson);
        $uniqueName = array_value_by_key("imdbID", $filmJson);
        $seriesID = array_value_by_key("seriesID", $filmJson);
        $msg = "OMDb API result: $title ($year)";
        $msg .= " imdbID/seriesID $uniqueName/$seriesID";
        logDebug($msg, __CLASS__."::".__FUNCTION__." ".__LINE__);
    }

    /**
     * Return a URL for an OMDb API search. The URL does not
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
        return "&i=$uniqueName";
    }

    // Parent abstract functions that will not be used
    protected function getDetailPageRegexForTitle($contentType = Film::CONTENT_FILM) { return ""; }
    protected function getDetailPageRegexForYear()  { return ""; }
    protected function getDetailPageRegexForImage() { return ""; }
    protected function getDetailPageRegexForContentType() { return ""; }
    protected function getDetailPageRegexForSeason() { return ""; }
    protected function getDetailPageRegexForEpisodeTitle() { return ""; }
    protected function getDetailPageRegexForEpisodeNumber() { return ""; }
    protected function getDetailPageRegexForUniqueName() { return ""; }
    protected function getDetailPageRegexForUniqueEpisode() { return ""; }
    protected function getDetailPageRegexForUniqueAlt() { return ""; }
    
    // Parent abstract functions that will not be used
    protected function parseDetailPageForGenres($page, $film, $overwrite) { return ""; }
    protected function parseDetailPageForDirectors($page, $film, $overwrite) { return ""; }

    /**
     * Return URL within a website for searching films. The URL does not
     * include the base URL.  
     *
     * @param array $args See the child class version of args
     *
     * @return string URL of a rating page
     */
    public function getSearchUrl($args)
    {
        if (empty($args) || !is_array($args) || empty($args["query"]))
        {
            throw new \InvalidArgumentException('$args must be an array with key "query" (non-empty)');
        }
        
        $searchUrl = "&s=" . urlencode($args["query"]);

        return $searchUrl;
    }

    /**
     * Return URL for a search for one result.  
     *
     * @param RatingSync/Film $film Has the info for searching
     *
     * @return string URL of a rating page
     */
    public function getFilmUrl($film)
    {
        $uniqueName = $film->getUniqueName($this->sourceName);
        $title = $film->getTitle();
        $episodeTitle = $film->getEpisodeTitle();
        $year = $film->getYear();
        $contentType = $film->getContentType();

        if (empty($uniqueName)) {
            $uniqueNameIMDb = $film->getUniqueName(Constants::SOURCE_IMDB);
            if (!empty($uniqueNameIMDb)) {
                $uniqueName = $uniqueNameIMDb;
            }
        }
        
        if (empty($uniqueName) && (empty($title) || empty($year)) && (empty($episodeTitle) || empty($year)))
        {
            throw new \InvalidArgumentException('film param must have a uniqueName or a year and either title or episodeTitle.');
        }
        
        $filmUrl = "";
        if (!empty($uniqueName)) {
            // "Search" by IMDb ID
            $filmUrl .= "&i=$uniqueName";
        }
        elseif (!empty($year) && (!empty($title) || !empty($episodeTitle))) {
            // Year
            $filmUrl .= "&y=$year";

            // Title
            $titleToUse = $title;
            if (empty($titleToUse)) {
                $titleToUse = $episodeTitle;
            }
            elseif ($contentType == Film::CONTENT_TV_EPISODE && !empty($episodeTitle)) {
                $titleToUse = $episodeTitle;
            }
            $filmUrl .= "&t=" . urlencode($titleToUse);
                
            // Content Type
            if ($contentType == Film::CONTENT_TV_EPISODE) {
                $filmUrl .= "&type=episode";
            } elseif ($contentType == Film::CONTENT_TV_SERIES) {
                $filmUrl .= "&type=series";
            } elseif ($contentType == Film::CONTENT_FILM) {
                $filmUrl .= "&type=movie";
            }
        }

        return $filmUrl;
    }
    
    public function getSeason($seriesFilmId, $seasonNum, $refreshCache = 60)
    {
        if (is_null($seriesFilmId) || !is_numeric($seriesFilmId) ) {
            throw new \InvalidArgumentException('arg1 must be numeric');
        } else if (is_null($seasonNum) || !is_numeric($seasonNum) ) {
            throw new \InvalidArgumentException('arg2 must be numeric');
        }

        $resultAsArray = null;

        $film = Film::getFilmFromDb($seriesFilmId);
        if (empty($film)) {
            return false;
        }
        $uniqueName = $film->getUniqueName($this->sourceName);

        $seasonPage = $this->getSeasonPageFromCache($seriesFilmId, $seasonNum, $refreshCache);
        if (empty($seasonPage) && !empty($uniqueName)) {
            try {
                $seasonUrl = "&i=" . $uniqueName . "&Season=" . $seasonNum;
                $seasonPage = $this->http->getPage($seasonUrl);
                $resultAsArray =  json_decode($seasonPage, true);
        
                if (!empty($resultAsArray) && $resultAsArray["Response"] != "False") {
                    $this->cacheSeasonPage($seasonPage, $seriesFilmId, $seasonNum);
                }
            } catch (\Exception $e) {
                logDebug($e, __CLASS__."::".__FUNCTION__." ".__LINE__);
                throw $e;
            }
        } else {
            $resultAsArray =  json_decode($seasonPage, true);
        }

        if (empty($resultAsArray) || !is_array($resultAsArray) || $resultAsArray["Response"] == "False") {
            $errorMsg = "OMDb API request failed. Title=".$film->getTitle();
            $errorMsg .= ", Season=" . $seasonNum;
            $errorMsg .= ", UniqueName=" . $uniqueName;
            logDebug($errorMsg, __CLASS__."::".__FUNCTION__." ".__LINE__, true, $resultAsArray);
            throw new \Exception('OMDbApi season failed');
        }

        return $resultAsArray;
    }

    /**
     * Return a cached film page if the cached file is fresh enough. The $refreshCache param
     * shows if it is fresh enough. If the file is out of date return null.
     *
     * @param \RatingSync\Film $film         Film needed detail for
     * @param int              $seasonNum    Season looking for within the film (series)
     * @param int|0            $refreshCache Use cache for files modified within mins from now. -1 means always use cache. Zero means never use cache.
     *
     * @return string File as a string. Null if the use cache is not used.
     */
    public function getSeasonPageFromCache($seriesFilmId, $seasonNum, $refreshCache = Constants::USE_CACHE_NEVER)
    {
        if (Constants::USE_CACHE_NEVER == $refreshCache) {
            return null;
        }
        
        $filename = Constants::cacheFilePath() . $this->sourceName;
        $filename .= "_series_" . $seriesFilmId;
        $filename .= "_season_" . $seasonNum;
        $filename .= ".html";

        if (!file_exists($filename) || (filesize($filename) == 0)) {
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
     * Cache a season result in json in a local file
     *
     * @param string    $page           File as a string
     * @param int       $seriesFilmId   DB film_id of the series
     * @param int       $seasonNum      Season number
     */
    public function cacheSeasonPage($page, $seriesFilmId, $seasonNum)
    {
        $filename = Constants::cacheFilePath() . $this->sourceName;
        $filename .= "_series_" . $seriesFilmId;
        $filename .= "_season_" . $seasonNum;
        $filename .= ".html";
        $fp = fopen($filename, "w");
        fwrite($fp, $page);
        fclose($fp);
    }
}