<?php
/**
 * Rating class
 *
 * @category 
 * @package  RatingSync
 * @license
 * @author   thereido <github@bagowine.com>
 * @link     https://github.com/thereido/RatingSync
 */
namespace RatingSync;

require_once "Source.php";

/**
 * Store and retrieve rating data for one piece of content (film, tv show...)
 * on one source.  Sources like IMDb, RottenTomatoes, Jinni, etc. or a local
 * database in this app.
 *
 * Source - Local, IMDb, RottenTomatoes, Jinni, etc.
 *
 * @category 
 * @package  RatingSync
 * @license
 * @author   thereido <github@bagowine.com>
 * @link     https://github.com/thereido/RatingSync
 */
class Rating
{
    protected $sourceName;          // Local, IMDb, RottenTomatoes, Jinni, etc.

    protected $yourScore;       // Rating (or score) from you - 1 to 10
    protected $yourRatingDate;  // The day you rated it
    protected $suggestedScore;  // How much they think you would like. Suggested by the source.

    /**
     * Rating data from one source
     *
     * @param string $source IMDb, RottenTomatoes, Jinni, Local, etc. Options are /RatingSync/Constants::SOURCE_***
     */
    public function __construct($source)
    {
        if (! Source::validSource($source) ) {
            throw new \InvalidArgumentException('Source $source invalid');
        }

        $this->sourceName = $source;
    }

    /**
     * Rating data from one source
     *
     * @param array  $row    Row from the Rating table (Columns: yourScore, yourRatingDate, suggestedScore)
     */
    public function initFromDbRow($row)
    {
        if (!empty($row['yourScore'])) {
            $this->setYourScore($row['yourScore']);
        }
        if (!empty($row['yourRatingDate'])) {
            $this->setYourRatingDate(new \DateTime($row['yourRatingDate']));
        }
        if (!empty($row['suggestedScore'])) {
            $this->setSuggestedScore($row['suggestedScore']);
        }
    }

    /**
     * Website the rating comes from
     *
     * @return string \RatingSync\Constants::SOURCE_***
     */
    public function getSource()
    {
        return $this->sourceName;
    }

    /**
     * The rating or score you gave it from 1 to 10
     *
     * @param int $yourScore The rating or score you gave it from 1 to 10
     *
     * @return none
     */
    public function setYourScore($score)
    {
        if (! (is_null($score) ||  $this->validRatingScore($score)) ) {
            throw new \InvalidArgumentException("setYourScore ($score) must be a number between 1 to 10");
        }

        if (is_null($score)) {
            $this->yourScore = null;
        } else {
            $this->yourScore = (float)$score;
        }
    }

    /**
     * What the source's users scored it
     *
     * @return 1 to 10
     */
    public function getYourScore()
    {
        return $this->yourScore;
    }

    /**
     * The day you rated it
     *
     * @param DateTime $yourRatingDate The day you rated it
     *
     * @return none
     */
    public function setYourRatingDate($yourRatingDate)
    {
        if (! (is_null($yourRatingDate) || !is_string($yourRatingDate)) ) {
            throw new \InvalidArgumentException("setYourRatingDate() accepts a DateTime");
        }

        $this->yourRatingDate = $yourRatingDate;
    }

    /**
     * The day you rated it
     *
     * @return string
     */
    public function getYourRatingDate()
    {
        return $this->yourRatingDate;
    }

    /**
     * What the source thinks you would like it
     *
     * @param int $suggestedScore What the source thinks you would like it
     *
     * @return none
     */
    public function setSuggestedScore($score)
    {
        if (! (is_null($score) ||  $this->validRatingScore($score)) ) {
            throw new \InvalidArgumentException("setYourScore ($score) must be a number between 1 to 10");
        }

        if (is_null($score)) {
            $this->suggestedScore = null;
        } else {
            $this->suggestedScore = (float)$score;
        }
    }

    /**
     * What the source thinks you would like it
     *
     * @return int
     */
    public function getSuggestedScore()
    {
        return $this->suggestedScore;
    }

    /**
     * Valid scores are numbers 1 to 10.  Strings can be casted.
     *
     * @param float $score 1 to 10
     *
     * @return true=valid, false=invalid
     */
    public static function validRatingScore($score)
    {
        if ( is_numeric($score) &&
             (0 <= (float)$score && (float)$score <= 10) ) {
            return true;
        } else {
            return false;
        }
    }

    public function saveToDb($username, $filmId, $overwriteIfDateEmpty = true)
    {
        if (empty($username) || empty($filmId)) {
            throw new \InvalidArgumentException("\$username ($username) and \$filmId ($filmId) must not be empty");
        } elseif (empty($this->sourceName)) {
            throw new \InvalidArgumentException("Rating must have a sourceName");
        }

        $sourceName = $this->sourceName;
        $ratingDate = $this->getYourRatingDate();
        $active = true;
        $db = getDatabase();

        $existingRating = self::getRatingFromDb($username, $sourceName, $filmId);

        if ( $this->equivalent($existingRating) ) {
            return true;
        }

        if ( empty($ratingDate) ) {
            if (!empty($existingRating) && !$overwriteIfDateEmpty) {
                return false;
            }

            $ratingDate = new \DateTime("now");
            $this->setYourRatingDate($ratingDate);
        }

        if ( ! empty($existingRating) ) {

            if ( $sourceName == Constants::SOURCE_RATINGSYNC ) {

                // Internal rating with an existing rating

                if ( $ratingDate > $existingRating->getYourRatingDate() ) {

                    $valuesForExisting = self::setColumnsAndValues($existingRating, $username, $filmId, false);
                    $replaceExisting = "REPLACE INTO rating (".$valuesForExisting['columns'].") VALUES (".$valuesForExisting['values'].")";
                    logDebug($replaceExisting, __CLASS__."::".__FUNCTION__." ".__LINE__);
                    $success = $db->query($replaceExisting) !== false;
                    if (!$success) {
                        $msg = "SQL Error trying to deactivate a existing rating (".$db->errorCode().") ".$db->errorInfo()[2];
                        logDebug($msg, __CLASS__."::".__FUNCTION__.":".__LINE__);
                        logError($msg);
                        return false;
                    }

                }
                elseif ( $ratingDate < $existingRating->getYourRatingDate() ) {

                    $active = false;

                }

            }
            else {

                // External rating with an existing rating (delete the existing one)

                $query = "DELETE FROM rating WHERE user_name='$username' AND source_name='$sourceName' AND film_id='$filmId'";
                logDebug($query, __CLASS__."::".__FUNCTION__." ".__LINE__);
                $success = $db->query($query) !== false;
                if (!$success) {
                    $msg = "SQL Error delete an external rating (".$db->errorCode().") ".$db->errorInfo()[2];
                    logDebug($msg, __CLASS__."::".__FUNCTION__.":".__LINE__);
                    logError($msg);
                    return false;
                }

            }
        }

        $values = self::setColumnsAndValues($this, $username, $filmId, $active, $existingRating);
        $replaceThis = "REPLACE INTO rating (".$values['columns'].") VALUES (".$values['values'].")";
        logDebug($replaceThis, __CLASS__."::".__FUNCTION__." ".__LINE__);
        $saveSuccess = $db->query($replaceThis) !== false;
        if (!$saveSuccess) {
            $msg = "SQL Error insert/replace a rating (".$db->errorCode().") ".$db->errorInfo()[2];
            logDebug($msg, __CLASS__."::".__FUNCTION__.":".__LINE__);
            logError($msg);
            return false;
        }

        return true;
    }

    public static function getRatingsFromDb($username, $sourceName, $filmId, $active): array
    {
        $db = getDatabase();
        $ratings = array();

        $active = $active == true ? 1 : 0;
        $query = "SELECT * FROM rating WHERE user_name='$username' AND source_name='$sourceName' AND film_id='$filmId' AND active=$active ORDER BY yourRatingDate DESC";
        $result = $db->query($query);
        foreach($result->fetchAll() as $row) {
            $rating = new Rating($sourceName);
            $rating->initFromDbRow($row);
            $ratings[] = $rating;
        }

        return $ratings;
    }

    public static function getInactiveRatingsFromDb(string $username, string $sourceName, int $filmId): array
    {
        return self::getRatingsFromDb($username, $sourceName, $filmId, false);
    }

    public static function getRatingFromDb($username, $sourceName, $filmId): ?Rating
    {
        $ratings = self::getRatingsFromDb($username, $sourceName, $filmId, true);

        if ( count($ratings) == 1 ) {
            return $ratings[0];
        }

        return null;
    }

    public static function setColumnsAndValues($rating, $username, $filmId, $active, $previousRating = null)
    {
        if (empty($rating) || !($rating instanceof Rating)) {
            throw new \InvalidArgumentException("\$rating must be a Rating object");
        } elseif (empty($username) || empty($filmId)) {
            throw new \InvalidArgumentException("\$username ($username) and \$filmId ($filmId) must not be empty");
        } elseif (empty($rating->sourceName)) {
            throw new \InvalidArgumentException("Rating must have a sourceName");
        }

        if ( $active == true ) {
            $active = 1;
        } else {
            $active = 0;
        }
        
        $sourceName = $rating->getSource();
        $yourScore = $rating->getYourScore();
        $suggestedScore = $rating->getSuggestedScore();
        $ratingDate = $rating->getYourRatingDate();
        $ratingDateStr = null;
        if (!empty($ratingDate)) {
            $ratingDateStr = $ratingDate->format("Y-m-d");
        }

        // If these values are empty then use the previous rating's values
        // - score, date, suggested score
        $yourScore = !empty($yourScore) ? $yourScore : $previousRating?->getYourScore();
        $suggestedScore = !empty($suggestedScore) ? $suggestedScore : $previousRating?->getSuggestedScore();
        $ratingDateStr = !empty($ratingDateStr) ? $ratingDateStr : $previousRating?->getYourRatingDate()?->format("Y-m-d");
        
        $columns = "user_name, source_name, film_id, active";
        $values = "'$username', '$sourceName', $filmId, $active";
        if (!empty($yourScore)) {
            $columns .= ", yourScore";
            $values .= ", $yourScore";
        }
        if (!empty($suggestedScore)) {
            $columns .= ", suggestedScore";
            $values .= ", $suggestedScore";
        }
        if (!empty($ratingDateStr)) {
            $columns .= ", yourRatingDate";
            $values .= ", '$ratingDateStr'";
        }

        $arr = array("columns" => $columns, "values" => $values);
        return $arr;
    }

    public function deleteToDb($username, $filmId)
    {
        if (empty($username) || empty($filmId)) {
            throw new \InvalidArgumentException("\$username ($username) and \$filmId ($filmId) must not be empty");
        } elseif (empty($this->sourceName)) {
            throw new \InvalidArgumentException("Rating must have a sourceName");
        }
        
        $db = getDatabase();
        $sourceName = $this->sourceName;

        $query = "DELETE FROM rating WHERE user_name='$username' AND source_name='$sourceName' AND film_id='$filmId' AND active=1";
        logDebug($query, __CLASS__."::".__FUNCTION__." ".__LINE__);
        $success = $db->query($query) !== false;
        if (!$success) {
            logDebug("SQL Error (".$db->errorCode().") ".$db->errorInfo()[2], __CLASS__."::".__FUNCTION__.":".__LINE__);
        }

        return $success;
    }

    /**
     * Save rating data from one source to the RatingSync source rating
     * if this one is newer (rating date) than the original
     *
     * @param string $username Database query from the Rating table
     * @param int    $filmId   Database id of the film rated
     */
    public function saveToRs($username, $filmId)
    {
        if (empty($username) || empty($filmId)) {
            throw new \InvalidArgumentException('username and filmId cannot be empty');
        }
        
        $source = new Source(Constants::SOURCE_RATINGSYNC, $filmId);
        $source->saveFilmSourceToDb($filmId);
            
        $ratingRs = new Rating(Constants::SOURCE_RATINGSYNC);
        try {
            $ratingRs->setYourScore($this->getYourScore());
        } catch (\Exception $e) {
            return; // Don't save a rating without a score
        }
        try {
            $ratingRs->setYourRatingDate($this->getYourRatingDate());
        } catch (\InvalidArgumentException $e) {
            // Ignore
        }
        
        return $ratingRs->saveToDb($username, $filmId, false);
    }

    public function asArray(): array
    {
        $arr = array();
        $arr['yourScore'] = $this->getYourScore();
        $ratingDate = null;
        if (!is_null($this->getYourRatingDate())) {
            $ratingDate = $this->getYourRatingDate()->format("Y-n-j");
        }
        $arr['yourRatingDate'] = $ratingDate;
        $arr['suggestedScore'] = $this->getSuggestedScore();

        return $arr;
    }

    // Same values on the same day.
    // Not "equal" because the timestamp is not checked.
    public function equivalent($other)
    {
        if (empty($other)) { return false; }

        $dateFormat = "Y-n-j";

        if ( $other->getSource() == $this->getSource()
             && $other->getYourScore() == $this->getYourScore()
             && $other->getYourRatingDate()?->format($dateFormat) == $this->getYourRatingDate()?->format($dateFormat)
             && $other->getSuggestedScore() == $this->getSuggestedScore() )
        {
            return true;
        }

        return false;
    }
}