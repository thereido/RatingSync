<?php
/**
 * RatingSyncSite class
 */
namespace RatingSync;

require_once "SiteRatings.php";

/**
 * Communicate to/from the RatingSync website/database
 * - Search for films and tv shows
 * - Get details for each and rate it
 * - Export/Import ratings.
 */
class RatingSyncSite extends \RatingSync\SiteRatings
{
    const RATINGSYNC_DATE_FORMAT = "n/j/y";

    public function __construct($username)
    {
        parent::__construct($username);
        $this->sourceName = Constants::SOURCE_RATINGSYNC;
        $this->http = new Http($this->sourceName, $username);
        $this->dateFormat = self::RATINGSYNC_DATE_FORMAT;
        $this->maxCriticScore = 100;
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
        if (empty($args) || !is_array($args) || !array_key_exists('pageIndex', $args) || !is_int($args['pageIndex'])) {
            throw new \InvalidArgumentException('$args must be an array with key "pageIndex" and value an int');
        }

        $pageIndex = $args['pageIndex'];
        $startIndex = (($pageIndex - 1) * 100) + 1;
        return '/user/'.urlencode($this->username).'/ratings?start='.$startIndex.'&view=detail&sort=title:asc';
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

    /**
     * Internal site doesn't use this
     */
    protected function getFilmsFromRatingsPage($page, $details = false, $refreshCache = 0) { }
    protected function getFilmDetailPageUrl($film) { }
    protected function parseDetailPageForGenres($page, $film, $overwrite) { }
    protected function parseDetailPageForDirectors($page, $film, $overwrite) { }
    protected function getDetailPageRegexForTitle() { }
    protected function getDetailPageRegexForYear() { }
    protected function getDetailPageRegexForImage() { }
    protected function getDetailPageRegexForContentType() { }
    protected function getDetailPageRegexForSeason() { }
    protected function getDetailPageRegexForEpisodeTitle() { }
    protected function getDetailPageRegexForEpisodeNumber() { }
    protected function getDetailPageRegexForUniqueName() { }
    protected function getDetailPageRegexForUniqueEpisode() { }
    protected function getDetailPageRegexForUniqueAlt() { }
    protected function getDetailPageRegexForYourScore($film) { }
    protected function getDetailPageRegexForRatingDate() { }
    protected function getDetailPageRegexForSuggestedScore($film) { }
    protected function getDetailPageRegexForCriticScore() { }
    protected function getDetailPageRegexForUserScore() { }
    protected function parseDetailPageForContentType($page, $film, $overwrite) { }
    protected function getSearchUrl($args) { }

    /**
     * Get every rating on $this->username's account. Ratings come from the
     * RatingSync app database (not a website).
     *
     * @param int|null $limitPages   Limit the number of pages of ratings
     * @param int|1    $beginPage    First page of rating results
     * @param bool     $details      Bring full film details (slower)
     * @param int|0    $refreshCache N/A - this class does not use cache
     *
     * @return array of Film
     */
    public function getRatings($limitPages = null, $beginPage = 1, $details = false, $refreshCache = Constants::USE_CACHE_NEVER)
    {
        $refreshCache = Constants::USE_CACHE_NEVER;
        $films = array();

        $db = getDatabase();
        $query = "SELECT film_id FROM rating WHERE " .
                    "user_name='" .$this->username. "' AND " .
                    "source_name='" .$this->sourceName. "'" .
                    "ORDER BY yourRatingDate DESC";
        $result = $db->query($query);
        // Iterate over films rated
        while ($row = $result->fetch_assoc()) {
            $filmId = intval($row["film_id"]);
            $film = Film::getFilmFromDb($filmId, $this->username);
            $films[] = $film;
        }
        
        return $films;
    }

    /**
     * Bring user's ratings in the db from all sources into sync.
     * 1. A rating from a another source -> copy to RS
     * 2. If RS has a older rating for the same film -> overwrite to RS
     * 3. If RS has a newer rating for the same film -> do nothing
     *
     * @param string $username RatingSync user
     */
    function syncRatings($username)
    {
        $db = getDatabase();
        $query = "SELECT * FROM rating as rating_other" .
                 " WHERE rating_other.user_name='$username'" .
                   " AND rating_other.source_name<>'" .$this->sourceName. "'" .
                   " AND (NOT EXISTS (SELECT NULL FROM rating as rating_rs" .
                                     " WHERE rating_rs.source_name='" .$this->sourceName. "'" .
                                       " AND rating_rs.user_name=rating_other.user_name" .
                                       " AND rating_rs.film_id=rating_other.film_id)" .
                        " OR" .
                        " EXISTS (SELECT NULL FROM rating as rating_rs" .
                                 " WHERE rating_rs.source_name='" .$this->sourceName. "'" .
                                   " AND rating_rs.user_name=rating_other.user_name" .
                                   " AND rating_rs.film_id=rating_other.film_id" .
                                   " AND rating_rs.yourRatingDate<rating_other.yourRatingDate))";
        $result = $db->query($query);

        // Iterate over ratings from other sources
        while ($row = $result->fetch_assoc()) {
            $rating = new Rating($row["source_name"]);
            $rating->initFromDbRow($row);
            $rating->saveToRs($username, $row["film_id"]);
        }
    }

    /**
     * Return getFilmDetailPageUrl($film) if it is available for streaming.
     * The return includes base URL. Use the first source with a return.
     * Order: Netflix, Amazon, xfinity, Hulu
     */
    public function getStreamUrl($filmId, $onlyFree = true)
    {
        if (empty($filmId) || !is_int(intval($filmId))) {
            throw new \InvalidArgumentException(__FUNCTION__." \$filmId must be an int (filmId=$filmId)");
        }

        $url = null;
        $film = Film::getFilmFromDb($filmId);
        $streams = $film->getStreams();
        $streamNetflix = array_value_by_key(Constants::SOURCE_NETFLIX, $streams);
        $streamAmazon = array_value_by_key(Constants::SOURCE_AMAZON, $streams);
        $streamXfinity = array_value_by_key(Constants::SOURCE_XFINITY, $streams);
        $streamHulu = array_value_by_key(Constants::SOURCE_HULU, $streams);

        $url = array_value_by_key("url", $streamNetflix);
        if (empty($url)) {
            $url = array_value_by_key("url", $streamAmazon);
        }
        if (empty($url)) {
            $url = array_value_by_key("url", $streamXfinity);
        }
        if (empty($url)) {
            $url = array_value_by_key("url", $streamHulu);
        }

        return $url;
    }
}