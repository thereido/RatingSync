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

use DateTime;
use JetBrains\PhpStorm\Pure;

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
            $this->setYourRatingDate(new DateTime($row['yourRatingDate']));
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

    public function saveToDb($username, $filmId, $overwriteIfDateEmpty = true): bool
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

            $ratingDate = today();
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

    public function deleteToDb($username, $filmId, $isActive)
    {
        if (empty($username) || empty($filmId)) {
            throw new \InvalidArgumentException("\$username ($username) and \$filmId ($filmId) must not be empty");
        } elseif (empty($this->sourceName)) {
            throw new \InvalidArgumentException("Rating must have a sourceName");
        }
        
        $db = getDatabase();
        $sourceName = $this->sourceName;

        if ( $isActive ) {
            $whereClause = " AND active=1";
        }
        else {
            $date = $this->getYourRatingDate();
            if ( is_null($date) ) {
                return false;
            }
            $dateStr = $date->format("Y-m-d");

            $whereClause = " AND active=0 AND yourRatingDate='$dateStr'";
        }

        $query = "DELETE FROM rating WHERE user_name='$username' AND source_name='$sourceName' AND film_id='$filmId'" . $whereClause;
        logDebug($query, __CLASS__."::".__FUNCTION__." ".__LINE__);
        $success = $db->query($query) !== false;
        if (!$success) {
            $msg = "SQL Error deleting a rating (".$db->errorCode().") ".$db->errorInfo()[2];
            logDebug($msg, __CLASS__."::".__FUNCTION__.":".__LINE__);
            logError($msg);
            return false;
        }

        return true;
    }

    private function archiveToDb($username, $filmId): bool
    {
        if (empty($username) || empty($filmId)) {
            throw new \InvalidArgumentException("\$username ($username) and \$filmId ($filmId) must have values");
        } elseif (empty($this->sourceName)) {
            throw new \InvalidArgumentException("Rating must have a sourceName");
        }

        $db = getDatabase();
        $sourceName = $this->sourceName;

        $values = self::setColumnsAndValues($this, $username, $filmId, false);
        $replace = "REPLACE INTO rating (".$values['columns'].") VALUES (".$values['values'].")";
        logDebug($replace, __CLASS__."::".__FUNCTION__." ".__LINE__);
        $success = $db->query($replace) !== false;
        if (!$success) {
            $msg = "SQL Error trying to archive a rating (".$db->errorCode().") ".$db->errorInfo()[2];
            logDebug($msg, __CLASS__."::".__FUNCTION__.":".__LINE__);
            logError($msg);
            return false;
        }

        return true;
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

    /**
     * Create, Update or Delete an active or archived user's rating for a film. If
     * the newDate param is in the future, then the current date is used.
     *
     * Create/Update Use cases (newDate non-null, scores 1 through 10):
     *   1) Same date as the active rating: Change the score
     *     1a) originalDate matches an archived rating: also delete original rating
     *   2) Same date as an archived rating: Change the score
     *     2a) originalDate matches active rating: also delete the original active rating, activate the newest archived rating
     *       2a.1) newDate is newest archived rating: 2a results
     *       2a.2) newDate is not newest archived rating 2a results
     *     2b) originalDate matches another archived rating: also delete original rating
     *   3) No existing active rating and newer than existing archived ratings: Create the new active rating
     *     3a) originalDate matches another archived rating: also delete original rating
     *   4) No existing active rating and older than the newest existing archived rating: Archive the new rating
     *     4a) originalDate matches another archived rating: also delete original rating
     *   5) Newer rating than the active rating: Archive the existing active rating and create the new active rating
     *     5a) originalDate matches active rating: Delete the original rating and create the new active rating
     *     5b) originalDate matches another archived rating: Archive the existing active rating and create the new active rating and delete original rating
     *   6) Older rating than the active rating: Archive the new rating
     *     6a) originalDate matches active rating: Delete the original rating, then do use case 3 or 4 (no existing active rating)
     *     6b) originalDate matches another archived rating: also delete original rating
     *
     * Create/Update Use cases (newDate=null, scores 1 through 10):
     *   7) No existing active rating, but archived rating is the current date: Delete the archived rating and create the active with current date
     *   8) For all other cases with newDate=null and score range 1-10: Archive the existing and create the new active rating with current date
     *     8a) With an active rating newDate=null and score range 1-10
     *     8b) Without an active rating newDate=null and score range 1-10
     *
     * Delete Use cases (score 0):
     *   9) No matching date and no existing active rating: do nothing
     *   10) newDate is null OR newDate is the same or newer than the existing active rating: Archive the existing active rating
     *     10a) newDate is null
     *     10b) newDate is the same as the existing active rating
     *     10c) Newer than the existing active rating
     *   11) Same date as an existing archived rating: Delete
     *
     * 12) Create/Update Use cases (newDate non-null, scores 1 through 10, originalDate non-null does not match any ratings): return false without doing anything
     *
     * @param int $filmId
     * @param string $username
     * @param int $newScore
     * @param DateTime | null $newDate
     * @param DateTime | null $originalDate
     * @return bool
     */
    public static function saveRatingToDb(int $filmId, string $username, int $newScore, ?DateTime $newDate = null, ?DateTime $originalDate = null): bool
    {
        try {

            $film = Film::getFilmFromDb($filmId, $username);

        } catch (\Exception $e) {
            logError("Error getting a Film for id=$filmId.\n" . $e->getMessage(), __CLASS__ . "::" . __FUNCTION__ . ":" . __LINE__);
            return false;
        }

        $now = new DateTime();
        if ( $newDate > $now ) {
            $newDate = today();
        }

        $sourceName = Constants::SOURCE_RATINGSYNC;

        $activeRating = $film->getRating($sourceName);
        $activeDate = $activeRating->getYourRatingDate();
        $isRatingActive = !empty($activeDate);
        $archive = Rating::getInactiveRatingsFromDb($username, $sourceName, $filmId);

        $activeDateStr = $activeDate?->format("Y-m-d");
        $originalDateStr = $originalDate?->format("Y-m-d");
        $newDateStr = $newDate?->format("Y-m-d");

        // If using originalDate, delete the matching existing rating
        if ( $newScore != 0 && $newDate != null && $originalDateStr != null && $originalDateStr != $newDateStr ) {
            // Validate originalDate and delete it from the db

            $originalRating = null;
            $originalIsActive = false;
            if ( $activeDateStr == $originalDateStr ) {
                $originalRating = $activeRating;
                $originalIsActive = true;
            }
            else {
                foreach ( $archive as $archivedRating ) {
                    if ( $archivedRating->getYourRatingDate()?->format("Y-m-d") == $originalDateStr ) {
                        $originalRating = $archivedRating;
                        break;
                    }
                }
            }

            if ( ! empty($originalRating) ) {
                $db = getDatabase();
                $query = "DELETE FROM rating WHERE user_name='$username' AND source_name='$sourceName' AND film_id='$filmId' AND yourRatingDate='$originalDateStr'";
                logDebug($query, __CLASS__."::".__FUNCTION__." ".__LINE__);
                $success = $db->query($query) !== false;
                if (!$success) {
                    $msg = "SQL Error delete original rating before changing it (".$db->errorCode().") ".$db->errorInfo()[2];
                    logDebug($msg, __CLASS__."::".__FUNCTION__.":".__LINE__);
                    logError($msg);
                    return false;
                }

                if ( $originalIsActive && count($archive) > 0 ) {
                    // Activate the newest archived rating now that the active rating was deleted.
                    // If it fails ignore it (a message would be nice).
                    $newestArchivedDateStr = $archive[0]->getYourRatingDate()->format("Y-m-d");
                    $query = "UPDATE rating SET active=1 WHERE film_id=$filmId AND user_name='$username' AND yourRatingDate='$newestArchivedDateStr'";
                    logDebug($query, __CLASS__."::".__FUNCTION__." ".__LINE__);
                    $success = $db->query($query) !== false;
                    if (!$success) {
                        $msg = "Failed to activate the newest archived rating after deleting the active rating.";
                        logDebug($msg, __CLASS__."::".__FUNCTION__.":".__LINE__);
                        logError($msg);
                    }
                }

                $saveSuccess = self::saveRatingToDb($filmId, $username, $newScore, $newDate);
                if ( ! $saveSuccess ) {
                    $originalRating->saveToDb($username, $filmId, false);
                }

                return $saveSuccess;
            }
        }

        // Delete (score 0)
        if ($newScore == 0) {

            // Delete Use case - newDate is null OR newDate is the same or newer than the existing active rating: Archive the existing active rating
            if ( $isRatingActive ) {
                if ( empty($newDate) || $newDate >= $activeRating->getYourRatingDate() ) {
                    return $activeRating->archiveToDb($username, $filmId);
                }
            }

            // Delete Use case - Same date as an existing archived rating: Delete
            foreach ( $archive as $archivedRating ) {
                if ( $newDate == $archivedRating->getYourRatingDate() ) {
                    return $archivedRating->deleteToDb($username, $filmId, false);
                }
            }

            // Delete Use case - No matching date and no existing active rating: do nothing
            return false;
        }

        // Create/Update Use cases (newDate=null, scores 1 through 10):
        if ( is_null($newDate) ) {

            // - No existing active rating, but archived rating is the current date: Delete the archived rating and create the active with current date
            if ( !$isRatingActive && count($archive) > 0 && $archive[0]?->getYourRatingDate() == today() ) {
                $deleted = $archive[0]->deleteToDb($username, $filmId, false);
                if ( $deleted ) {
                    return self::createAndSaveToDb($sourceName, $username, $filmId, $newScore, $newDate, false);
                }
                else {
                    return false;
                }
            }

            // - For all other cases with newDate=null and score range 1-10: Archive the existing and create the new active rating with current date
            $newDate = today();
            if ( $isRatingActive ) {
                $successArchiving = $activeRating->archiveToDb($username, $filmId);
                if ( ! $successArchiving ) {
                    return false;
                }
            }

            return self::createAndSaveToDb($sourceName, $username, $filmId, $newScore, $newDate, false);

        }
        // Create/Update Use cases (newDate non-null, scores 1 through 10)
        else {

            if ( $newDateStr == $activeDateStr ) {

                //   - newDate same as the active rating: Change the score

                return self::createAndSaveToDb($sourceName, $username, $filmId, $newScore, $newDate, false);

            }

            $newestArchiveDateStr = "";
            foreach ( $archive as $oneArchived ) {
                $oneDateStr = $oneArchived->getYourRatingDate()->format("Y-m-d");
                if ( $newDateStr == $oneDateStr ) {

                    //   - newDate same as an archived rating: Change the score

                    return self::createAndSaveToDb($sourceName, $username, $filmId, $newScore, $newDate, true);

                }

                if ( empty($newestArchiveDateStr) || $oneDateStr > $newestArchiveDateStr ) { $newestArchiveDateStr = $oneDateStr; }
            }

            if ( ! $isRatingActive ) {

                if ( $newDateStr > $newestArchiveDateStr ) {
                    //   - No existing active rating and newDate is newer than existing archived ratings: Create the new active rating

                    $archiveIt = false;

                }
                else {
                    //   - No existing active rating and newDate is older than the newest existing archived rating: Archive the new rating

                    $archiveIt = true;

                }

            }
            else {

                if ( $newDateStr < $activeDateStr ) {
                    //   - newDate is older than the active rating: Archive the new rating
                    //   - newDate same as an archived rating: Change the score

                    $archiveIt = true;

                }
                else {
                    //   - newDate is newer than the active rating: Archive the existing and create the new active rating

                    $successArchiving = $activeRating->archiveToDb($username, $filmId);
                    if ( ! $successArchiving ) {
                        return false;
                    }

                    $archiveIt = false;

                }

            }

            return self::createAndSaveToDb($sourceName, $username, $filmId, $newScore, $newDate, $archiveIt);

        }

        return false;
    }

    private static function createAndSaveToDb($sourceName, $username, $filmId, $score, $date, $archiveIt = false): bool
    {
        $rating = new Rating($sourceName);
        $rating->setYourScore($score);
        $rating->setYourRatingDate($date);

        if ( $archiveIt ) {
            return $rating->archiveToDb($username, $filmId);
        }
        else {
            return $rating->saveToDb($username, $filmId);
        }
    }

}