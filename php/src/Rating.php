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
    protected $criticScore;     // Average rating by critics through the source
    protected $userScore;       // Average rating by users through the source

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
     * @param array  $row    Row from the Rating table (Columns: yourScore, yourRatingDate, suggestedScore, criticScore, userScore)
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
        if (!empty($row['criticScore'])) {
            $this->setCriticScore($row['criticScore']);
        }
        if (!empty($row['userScore'])) {
            $this->setUserScore($row['userScore']);
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
     * What the source's critics scored it
     *
     * @param int $criticScore What the source's critics scored it
     *
     * @return none
     */
    public function setCriticScore($score)
    {
        if (! (is_null($score) ||  $this->validRatingScore($score)) ) {
            throw new \InvalidArgumentException("setCriticScore ($score) must be a number between 1 to 10");
        }

        if (is_null($score)) {
            $this->criticScore = null;
        } else {
            $this->criticScore = (float)$score;
        }
    }

    /**
     * What the source's critics scored it
     *
     * @return int
     */
    public function getCriticScore()
    {
        return $this->criticScore;
    }

    /**
     * What the source's users scored it
     *
     * @param int $userScore What the source's users scored it
     *
     * @return none
     */
    public function setUserScore($score)
    {
        if (! (is_null($score) ||  $this->validRatingScore($score)) ) {
            throw new \InvalidArgumentException("setUserScore ($score) must be a number between 0 to 10");
        }

        if (is_null($score)) {
            $this->userScore = null;
        } else {
            $this->userScore = (float)$score;
        }
    }

    /**
     * What the source's users scored it
     *
     * @return 1 to 10
     */
    public function getUserScore()
    {
        return $this->userScore;
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

        $originalThis = $this;
        $sourceName = $this->sourceName;
        $ratingDate = $this->getYourRatingDate();
        $db = getDatabase();

        $existingRating = null;
        $query = "SELECT * FROM rating WHERE user_name='$username' AND source_name='$sourceName' AND film_id='$filmId'";
        $result = $db->query($query);
        if (!empty($result) && $result->num_rows == 1) {
            if (empty($ratingDate) && !$overwriteIfDateEmpty) {
                return false;
            }
            $existingRating = new Rating($sourceName);
            $existingRating->initFromDbRow($result->fetch_assoc());

            if (empty($this->getYourScore())) {
                $this->setYourScore($existingRating->getYourScore());
            }
            if (empty($this->getSuggestedScore())) {
                $this->setSuggestedScore($existingRating->getSuggestedScore());
            }
            if (empty($this->getCriticScore())) {
                $this->setCriticScore($existingRating->getCriticScore());
            }
            if (empty($this->getUserScore())) {
                $this->setUserScore($existingRating->getUserScore());
            }
            if (empty($ratingDate)) {
                $ratingDate = $existingRating->getYourRatingDate();
                $this->setYourRatingDate($ratingDate);
            }
        }

        $saveSuccess = false;
        if (empty($existingRating)) {
            // This is a new rating. Simply insert it to the db.
            $thisValues = self::setColumnsAndValues($this, $username, $filmId);
            $insertThisRating = "REPLACE INTO rating (".$thisValues['columns'].") VALUES (".$thisValues['values'].")";
            $saveSuccess = $db->query($insertThisRating);
        } else {
            // There is an existing rating. Existing vs. This. One goes to the rating
            // table and the other goes the archive table.
            $existingRatingDate = $existingRating->getYourRatingDate();
            if (empty($existingRatingDate) || $originalThis->getYourRatingDate() > $existingRatingDate) {
                // This rating is newer than the existing one. Archive the existing
                // one and update this to the rating table.
                $existingValues = self::setColumnsAndValues($existingRating, $username, $filmId);
                $archive = "INSERT rating_archive (".$existingValues['columns'].") VALUES (".$existingValues['values'].")";
                // Archive
                if ($db->query($archive)) {
                    // Replace
                    if ($this->getYourScore() == $existingRating->getYourScore()) {
                        // The score the score didn't change. Use the existing rating date,
                        // because you don't want to look like the user changed their opinion
                        // today
                        $this->setYourRatingDate($existingRating->getYourRatingDate());
                    }
                    $thisValues = self::setColumnsAndValues($this, $username, $filmId);
                    $replaceThisRating = "REPLACE INTO rating (".$thisValues['columns'].") VALUES (".$thisValues['values'].")";
                    $saveSuccess = $db->query($replaceThisRating);
                }
            } else {
                // This is not newer then the existing rating. Archive this one
                // and leave the rating table alone.
                $originalThisValues = self::setColumnsAndValues($originalThis, $username, $filmId);
                $archive = "INSERT rating_archive (".$originalThisValues['columns'].") VALUES (".$originalThisValues['values'].")";
                $saveSuccess = $db->query($archive);
            }
        }
        
        return $saveSuccess;
    }

    public static function setColumnsAndValues($rating, $username, $filmId)
    {
        if (empty($rating) || !($rating instanceof Rating)) {
            throw new \InvalidArgumentException("\$rating must be a Rating object");
        } elseif (empty($username) || empty($filmId)) {
            throw new \InvalidArgumentException("\$username ($username) and \$filmId ($filmId) must not be empty");
        } elseif (empty($rating->sourceName)) {
            throw new \InvalidArgumentException("Rating must have a sourceName");
        }
        
        $sourceName = $rating->getSource();
        $yourScore = $rating->getYourScore();
        $suggestedScore = $rating->getSuggestedScore();
        $criticScore = $rating->getCriticScore();
        $userScore = $rating->getUserScore();
        $ratingDate = $rating->getYourRatingDate();
        $ratingDateStr = null;
        if (!empty($ratingDate)) {
            $ratingDateStr = $ratingDate->format("Y-m-d");
        }
        
        $columns = "user_name, source_name, film_id";
        $values = "'$username', '$sourceName', $filmId";
        if (!empty($yourScore)) {
            $columns .= ", yourScore";
            $values .= ", $yourScore";
        }
        if (!empty($suggestedScore)) {
            $columns .= ", suggestedScore";
            $values .= ", $suggestedScore";
        }
        if (!empty($criticScore)) {
            $columns .= ", criticScore";
            $values .= ", $criticScore";
        }
        if (!empty($userScore)) {
            $columns .= ", userScore";
            $values .= ", $userScore";
        }
        if (!empty($ratingDateStr)) {
            $columns .= ", yourRatingDate";
            $values .= ", '$ratingDateStr'";
        }

        $arr = array("columns" => $columns, "values" => $values);
        return $arr;
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
}