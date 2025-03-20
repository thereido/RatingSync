<?php
/**
 * RatingSyncSite class
 */
namespace RatingSync;

use ArrayObject;

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
    const SORT_RATING_DATE  = 'yourRatingDate';
    const SORT_YOUR_SCORE   = 'yourScore';
    const SORTDIR_ASC       = 'ASC';
    const SORTDIR_DESC      = 'DESC';

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
    
    protected $sort;
    protected $sortDirection;

    public function __construct($username)
    {
        parent::__construct($username);
        $this->sourceName = Constants::SOURCE_RATINGSYNC;
        $this->http = new Http($this->sourceName, $username);
        $this->dateFormat = self::RATINGSYNC_DATE_FORMAT;
        $this->maxCriticScore = 100;
        $this->sort = static::SORT_RATING_DATE;
        $this->sortDirection = static::SORTDIR_DESC;
        $this->clearContentTypeFilter();
        $this->clearListFilter();
    }

    public static function validSort($sort)
    {
        $validSorts = array(static::SORT_RATING_DATE, static::SORT_YOUR_SCORE);
        if (in_array($sort, $validSorts)) {
            return true;
        }
        return false;
    }

    public static function validSortDirection($sortDirection)
    {
        $validSortDirection = array(static::SORTDIR_ASC, static::SORTDIR_DESC);
        if (in_array($sortDirection, $validSortDirection)) {
            return true;
        }
        return false;
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
        $films = array();

        $orderBy = "ORDER BY ";
        if (!empty($this->getSort())) {
            $orderBy .= $this->getSort() . " " . $this->getSortDirection() . ", ";
        }
        $orderBy .= "rating.ts " . $this->getSortDirection();

        $limit = "";
        if (!empty($limitPages)) {
            $beginRecord = ($limitPages * $beginPage) - $limitPages;
            $limit = "LIMIT $beginRecord, $limitPages";
        }
        $db = getDatabase();
        $query = $this->getRatingsQuery("rating.film_id", $orderBy, $limit);
        $result = $db->query($query);
        // Iterate over films rated
        foreach ($result->fetchAll() as $row) {
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
        $row = $result->fetch();
        
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
        $query .= " WHERE active=1";
        $query .= "   AND rating.user_name='" .$this->username. "'";
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
                   " AND rating_other.active=1" .
                   " AND (NOT EXISTS (SELECT NULL FROM rating as rating_rs" .
                                     " WHERE rating_rs.source_name='" .$this->sourceName. "'" .
                                       " AND rating_rs.user_name=rating_other.user_name" .
                                       " AND rating_rs.film_id=rating_other.film_id" .
                                       " AND rating_rs.active=1)" .
                        " OR" .
                        " EXISTS (SELECT NULL FROM rating as rating_rs" .
                                 " WHERE rating_rs.source_name='" .$this->sourceName. "'" .
                                   " AND rating_rs.user_name=rating_other.user_name" .
                                   " AND rating_rs.film_id=rating_other.film_id" .
                                   " AND rating_rs.yourRatingDate<rating_other.yourRatingDate" .
                                   " AND rating_rs.active=1))";
        $result = $db->query($query);

        // Iterate over ratings from other sources
        foreach ($result->fetchAll() as $row) {
            $rating = new Rating($row["source_name"]);
            $rating->initFromDbRow($row);
            if ( !empty($rating->getYourScore()) ) {
                $rating->saveToRs($username, $row["film_id"]);
            }
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
        $comma = "";
        reset($this->contentTypeFilter);

        $iter = (new ArrayObject($this->contentTypeFilter))->getIterator();
        while ($iter->valid()) {

            $current = $iter->current();
            if (Film::validContentType($current)) {
                $filteredOut .= $comma . "'$current'";
                $comma = ", ";
            }

            $iter->next();
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

    public function setSort($sort)
    {
        if (! $this->validSort($sort) ) {
            throw new \InvalidArgumentException(__FUNCTION__." Invalid sort param '$sort'");
        }

        $this->sort = $sort;
    }

    public function getSort()
    {
        return $this->sort;
    }

    public function setSortDirection($sortDirection)
    {
        if (! $this->validSortDirection($sortDirection) ) {
            throw new \InvalidArgumentException(__FUNCTION__." Invalid sortDirection param '$sortDirection'");
        }

        $this->sortDirection = $sortDirection;
    }

    public function getSortDirection()
    {
        return $this->sortDirection;
    }

    public function search($searchQuery, $searchDomain, $listname, $limit)
    {
        $films = array();
        $filmIds = array();
        $db = getDatabase();

        // Search titles with the full $searchQuery
        $query = $this->getSearchSql($searchDomain, $searchQuery, $limit);
        $result = $db->query($query);
        foreach ($result->fetchAll() as $row) {
            $filmId = intval($row["film_id"]);
            $film = Film::getFilmFromDb($filmId, $this->username);
            $films[] = $film;
            $filmIds[] = $film->getId();
        }

        // Search titles with any words in $searchQuery
        $queryWords = explode(" ", $searchQuery);
        if (count($films) < $limit) {
            foreach ($queryWords as $word) {
                $query = $this->getSearchSql($searchDomain, $word, $limit);
                $result = $db->query($query);
                foreach ($result->fetchAll() as $row) {
                    $filmId = intval($row["film_id"]);
                    if (!in_array($filmId, $filmIds)) {
                        $film = Film::getFilmFromDb($filmId, $this->username);
                        $films[] = $film;
                        $filmIds[] = $film->getId();
                    }
                }

                if (count($films) >= $limit) {
                    break;
                }
            }
        }
        
        // Search titles with anything that sounds like words in $searchQuery
        /* This works, but the benefit is probably not worth the performance hit
        if (count($films) < $limit) {
            foreach ($queryWords as $word) {
                $query = $this->getRatingsSqlQuery($word, $limit, true);
                $result = $db->query($query);
                foreach ($result->fetchAll() as $row) {
                    $filmId = intval($row["film_id"]);
                    if (!in_array($filmId, $filmIds)) {
                        $film = Film::getFilmFromDb($filmId, $this->username);
                        $films[] = $film;
                        $filmIds[] = $film->getId();
                    }
                }

                if (count($films) >= $limit) {
                    break;
                }
            }
        }
        */
        
        return $films;
    }

    protected function getSearchSql($searchDomain, $search, $limit, $useSounds = false)
    {
        $sql = "";
        $imdbId = null;

        if (preg_match('/(^tt\d{7}\d*$)/i', $search, $matches)) {
            $imdbId = $matches[1];
        }

        if ($searchDomain == "ratings") {
            $sql = $this->getRatingsSqlQuery($search, $limit, $useSounds, $imdbId);
        } else if ($searchDomain == "list") {
            $sql = $this->getDefaultListSqlQuery($search, $limit, $useSounds, $imdbId);
        } else if ($searchDomain == "both") {
            $sql = $this->getBothSqlQuery($search, $limit, $useSounds, $imdbId);
        }

        return $sql;
    }

    protected function getRatingsSqlQuery($search, $limit, $useSounds = false, $imdbId = null)
    {
        $sounds = "";
        if ($useSounds) {
            $sounds = "SOUNDS";
        }
        
        if (empty($imdbId)) {
            $query  = "SELECT DISTINCT rating.film_id FROM rating, film";
            $query .= " WHERE film.title $sounds LIKE '%$search%'";
            $query .= "   AND rating.user_name = '". $this->username ."'";
            $query .= "   AND rating.source_name = 'RatingSync'";
            $query .= "   AND rating.film_id = film.id";
            $query .= "   AND rating.active = 1";
            $query .= " LIMIT $limit";
        } else {
            $query  = "SELECT DISTINCT rating.film_id FROM rating, film_source";
            $query .= " WHERE film_source.uniqueName = '$imdbId'";
            $query .= "   AND film_source.source_name = 'IMDb'";
            $query .= "   AND rating.user_name = '". $this->username ."'";
            $query .= "   AND rating.source_name = 'RatingSync'";
            $query .= "   AND rating.film_id = film_source.film_id";
            $query .= "   AND rating.active = 1";
            $query .= " LIMIT $limit";
        }

        return $query;
    }

    protected function getDefaultListSqlQuery($search, $limit, $useSounds = false, $imdbId = null)
    {
        $sounds = "";
        if ($useSounds) {
            $sounds = "SOUNDS";
        }
        
        if (empty($imdbId)) {
            $query  = "SELECT DISTINCT filmlist.film_id FROM filmlist, film";
            $query .= " WHERE film.title $sounds LIKE '%$search%'";
            $query .= "   AND filmlist.user_name = '". $this->username ."'";
            $query .= "   AND filmlist.listname = '" . Constants::LIST_DEFAULT . "'";
            $query .= "   AND filmlist.film_id = film.id";
            $query .= " LIMIT $limit";
        } else {
            $query  = "SELECT DISTINCT filmlist.film_id FROM filmlist, film_source";
            $query .= " WHERE film_source.uniqueName = '$imdbId'";
            $query .= "   AND film_source.source_name = 'IMDb'";
            $query .= "   AND filmlist.user_name = '". $this->username ."'";
            $query .= "   AND filmlist.listname = '" . Constants::LIST_DEFAULT . "'";
            $query .= "   AND filmlist.film_id = film_source.film_id";
            $query .= " LIMIT $limit";
        }

        return $query;
    }

    protected function getBothSqlQuery($search, $limit, $useSounds = false, $imdbId = null)
    {
        $sounds = "";
        if ($useSounds) {
            $sounds = "SOUNDS";
        }
        $username = $this->username;
        $listname = Constants::LIST_DEFAULT;
        
        if (empty($imdbId)) {
            $query  = "SELECT DISTINCT id as film_id FROM film";
            $query .= "  WHERE film.title $sounds LIKE '%$search%'";
            $query .= "    AND (";
            $query .= "      id IN (SELECT film_id FROM rating WHERE rating.user_name = '$username' AND rating.source_name = 'RatingSync' AND rating.active = 1)";
            $query .= "      OR";
            $query .= "      id IN (SELECT film_id FROM filmlist WHERE filmlist.user_name = '$username' AND filmlist.listname = '$listname')";
            $query .= "    )";
            $query .= "  LIMIT $limit";
        } else {
            $query  = "SELECT DISTINCT film_id FROM film_source";
            $query .= " WHERE film_source.uniqueName = '$imdbId'";
            $query .= "   AND film_source.source_name = 'IMDb'";
            $query .= "   AND (";
            $query .= "      film_id IN (SELECT film_id FROM rating WHERE rating.user_name = '$username' AND rating.source_name = 'RatingSync' AND rating.active = 1)";
            $query .= "      OR";
            $query .= "      film_id IN (SELECT film_id FROM filmlist WHERE filmlist.user_name = '$username' AND filmlist.listname = '$listname')";
            $query .= "      )";
            $query .= " LIMIT $limit";
        }

        return $query;
    }

    public static function usernameExists($username) {
        $db = getDatabase();

        $query = "SELECT count(1) as count FROM user WHERE username='" . $username . "'";
        $result = $db->query($query);
        $row = $result->fetch();
        
        $exists = false;
        if ($row["count"] > 0) {
            $exists = true;
        }

        return $exists;
    }
}