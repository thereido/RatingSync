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

    /** Content Type filter
     * Keys are the content type. Values are boolean.
     * Filter out films by contentType if the key appears and the value is false.
     * If the film's contentType is not in the filter or the value is anything
     * other then false the film is not affected by the filter.
     */
    protected $contentTypeFilter = array();

    /** List filter
     *  No keys. Values are the listnames from the db table user_filmlist.
     *  Filter out films that are not in any lists in the filter. The filter does
     *  not affect results if the filter is empty.
     */
    protected $listFilter = array();
    
    /** Genre filter
     *  No keys. Values are genre names from the db genre table.
     *  Filter out films with a matching genres. The filter does not affect results
     *  if the filter is empty.  $genreFilterMatchAny affects whether the films must
     *  have one or more genres in the filter (Any=true) OR the films must have all
     *  of the genres in the filter (Any=false).
     */
    protected $genreFilter = array();

    // Genre filter with Any or All
    //   True matches a film with Any genre in the filter
    //   False matches a film with All genres in the filter
    protected $genreFilterMatchAny = true;

    public function __construct($username)
    {
        parent::__construct($username);
        $this->sourceName = Constants::SOURCE_RATINGSYNC;
        $this->http = new Http($this->sourceName, $username);
        $this->dateFormat = self::RATINGSYNC_DATE_FORMAT;
        $this->maxCriticScore = 100;
        $this->clearContentTypeFilter();
        $this->clearListFilter();
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
    protected function getDetailPageRegexForTitle($contentType = Film::CONTENT_FILM) { }
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

        $orderBy = "ORDER BY yourRatingDate DESC";

        $limit = "";
        if (!empty($limitPages)) {
            $beginRecord = ($limitPages * $beginPage) - $limitPages;
            $limit = "LIMIT $beginRecord, $limitPages";
        }
        
        $db = getDatabase();
        $query = $this->getRatingsQuery("DISTINCT rating.film_id", $orderBy, $limit);
        $result = $db->query($query);
        // Iterate over films rated
        while ($row = $result->fetch_assoc()) {
            $filmId = intval($row["film_id"]);
            $film = Film::getFilmFromDb($filmId, $this->username);
            $films[] = $film;
        }
        
        return $films;
    }

    public function countRatings() {
        $db = getDatabase();
        $query = $this->getRatingsQuery("count(DISTINCT rating.film_id) as count");
        $result = $db->query($query);
        $row = $result->fetch_assoc();
        
        return $row["count"];
    }

    protected function getRatingsQuery($selectCols, $orderBy = "", $limit = "") {
        $queryTables = "rating";

        $contentTypeFilterWhere = "";
        $contentTypeFilteredOut = $this->getContentTypeFilterCommaDelimited();
        if (!empty($contentTypeFilteredOut)) {
            $queryTables .= ", film";
            $contentTypeFilterWhere .= " AND rating.film_id=film.id ";
            $contentTypeFilterWhere .= " AND contentType NOT IN (" . $contentTypeFilteredOut . ") ";
        }

        $listFilterWhere = "";
        $listsFilteredIn = $this->getListFilterCommaDelimited();
        if (!empty($listsFilteredIn)) {
            $queryTables .= ", filmlist";
            $listFilterWhere .= " AND rating.user_name=filmlist.user_name ";
            $listFilterWhere .= " AND rating.film_id=filmlist.film_id ";
            $listFilterWhere .= " AND listname IN (" . $listsFilteredIn . ") ";
        }

        $genreFilterWhere = "";
        if (count($this->getGenreFilter()) > 0) {
            if ($this->getGenreFilterMatchAny()) {
                $genresFilteredIn = $this->getGenreFilterCommaDelimited();
                $genreFilterWhere .= " AND rating.film_id IN (SELECT film_id FROM film_genre WHERE genre_name IN ($genresFilteredIn)) ";
            } else {
                foreach ($this->getGenreFilter() as $genre) {
                    $genreFilterWhere .= " AND EXISTS (SELECT * FROM film_genre WHERE rating.film_id=film_genre.film_id AND genre_name='$genre') ";
                }
            }
        }

        $query  = "SELECT $selectCols FROM $queryTables";
        $query .= " WHERE rating.user_name='" .$this->username. "'";
        $query .= "   AND source_name='" .$this->sourceName. "'";
        $query .=     $contentTypeFilterWhere;
        $query .=     $listFilterWhere;
        $query .=     $genreFilterWhere;
        $query .= " $orderBy";
        $query .= " $limit";

        return $query;
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

    /**
     * @param array|null Keys are contentTypes. Values are true/false.
     */
    public function setContentTypeFilter($newFilter = array()) {
        if (is_array($newFilter)) {
            $this->contentTypeFilter = $newFilter;
        }
    }
    
    public function clearContentTypeFilter() {
        $this->contentTypeFilter = array();
    }

    /**
     * @param array values listnames
     */
    public function setListFilter($newFilter = array()) {
        if (is_array($newFilter)) {
            $this->listFilter = $newFilter;
        }
    }
    
    public function clearListFilter() {
        $this->listFilter = array();
    }

    /**
     * @return array Genre filter
     */
    protected function getGenreFilter()
    {
        return $this->genreFilter;
    }

    /**
     * @param array $genreFilter
     *
     * @return none
     */
    public function setGenreFilter($genreFilter)
    {
        if (!is_array($genreFilter) && !is_null($genreFilter)) {
            throw new \InvalidArgumentException(__FUNCTION__.' param must be an array or null');
        }

        if (is_null($genreFilter)) {
            $genreFilter = array();
        }

        $this->genreFilter = $genreFilter;
    }

    /**
     * @return string genreFilterMatchAny
     */
    public function getGenreFilterMatchAny()
    {
        return $this->genreFilterMatchAny;
    }

    /**
     * @param string $genreFilterMatchAny
     *
     * @return none
     */
    public function setGenreFilterMatchAny($genreFilterMatchAny)
    {
        if (!is_bool($genreFilterMatchAny)) {
            throw new \InvalidArgumentException(__FUNCTION__." param must have be a boolean");
        }

        $this->genreFilterMatchAny = $genreFilterMatchAny;
    }

    public function getContentTypeFilterCommaDelimited() {
        $filteredOut = "";
        $comma = "";\
        reset($this->contentTypeFilter);
        while (list($key, $val) = each($this->contentTypeFilter)) {
            if (Film::validContentType($key) && $val === false) {
                $filteredOut .= $comma . "'$key'";
                $comma = ", ";
            }
        }
        
        return $filteredOut;
    }

    public function getListFilterCommaDelimited() {
        $commaDelimitedLists = "";
        $comma = "";
        foreach ($this->listFilter as $listname) {
            $commaDelimitedLists .= $comma . "'$listname'";
            $comma = ", ";
        }
        
        return $commaDelimitedLists;
    }

    public function getGenreFilterCommaDelimited() {
        $commaDelimitedLists = "";
        $comma = "";
        foreach ($this->genreFilter as $item) {
            $commaDelimitedLists .= $comma . "'$item'";
            $comma = ", ";
        }
        
        return $commaDelimitedLists;
    }
}