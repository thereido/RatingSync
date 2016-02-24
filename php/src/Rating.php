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
        
        $db = getDatabase();
        $query = "SELECT * FROM rating" .
                 " WHERE film_id=$filmId" .
                   " AND user_name='$username'" .
                   " AND source_name='" .Constants::SOURCE_RATINGSYNC. "'";
        $result = $db->query($query);
        if ($result->num_rows == 1) {
            // The user has a current RatingSync rating for this film
            $row = $result->fetch_assoc();
            $ratingRs = new Rating(Constants::SOURCE_RATINGSYNC);
            $ratingRs->initFromDbRow($row);

            $yourScore = $this->getYourScore();
            if (($this->getYourRatingDate() > $ratingRs->getYourRatingDate())
                && (!empty($yourScore))
                && ($yourScore != $ratingRs->getYourScore()))
            {
                // This rating is newer than the RS one. Update the score
                $yourScore = $this->getYourScore();
                $ratingDateSet = "";
                $ratingDate = $this->getYourRatingDate();
                if (!empty($ratingDate)) {
                    $ratingDateSet = ", yourRatingDate='" . date_format($ratingDate, 'Y-m-d') . "'";
                }
                $query = "UPDATE rating SET yourScore=$yourScore" . $ratingDateSet .
                             " WHERE film_id=$filmId" .
                               " AND user_name='$username'" .
                               " AND source_name='" .Constants::SOURCE_RATINGSYNC. "'";
                if (! $db->query($query)) {
                    throw new \Exception('DB Failure updating to rating. film_id='.$filmId.', user_name='.Constants::SOURCE_RATINGSYNC.', source_name='.$name);
                }
            }
        } else {
            $ratingRs = new Rating(Constants::SOURCE_RATINGSYNC);
            $source = new Source(Constants::SOURCE_RATINGSYNC, $filmId);
            $source->saveFilmSourceToDb($filmId);
            
            $yourScore = $this->getYourScore();
            $ratingDate = null;
            if (!is_null($this->getYourRatingDate())) {
                $ratingDate = $this->getYourRatingDate()->format("Y-m-d");
            }

            $columns = "user_name, source_name, film_id";
            $values = "'$username', '" .Constants::SOURCE_RATINGSYNC. "', $filmId";
            if (!empty($yourScore)) {
                $columns .= ", yourScore";
                $values .= ", $yourScore";
            }
            if (!empty($ratingDate)) {
                $columns .= ", yourRatingDate";
                $values .= ", '$ratingDate'";
            }

            if (! $db->query("INSERT INTO rating ($columns) VALUES ($values)")) {
                throw new \Exception("DB Failure insert into rating. film_id='$filmId', user_name='$username', source_name='".Constants::SOURCE_RATINGSYNC."'.  SQL Error: ".$db->error);
            }
        }

    }
}