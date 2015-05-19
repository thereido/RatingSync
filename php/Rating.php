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

date_default_timezone_set('America/New_York');

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
    protected $source;          // Local, IMDb, RottenTomatoes, Jinni, etc.

    protected $filmId;          // Unique Id in the given website (source)
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
        if (! static::validSource($source) ) {
            throw new \InvalidArgumentException('Source $source invalid');
        }

        $this->source = $source;
    }

    public static function validSource($source)
    {       
        if (in_array($source, array(Constants::SOURCE_JINNI, Constants::SOURCE_IMDB, Constants::SOURCE_RATINGSYNC))) {
            return true;
        }
        return false;
    }

    /**
     * Website the rating comes from
     *
     * @return string \RatingSync\Constants::SOURCE_***
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * ID to find this film within the source
     *
     * @param string $filmId ID to find this film within the source
     *
     * @return none
     */
    public function setFilmId($filmId)
    {
        $this->filmId = $filmId;
    }

    /**
     * Return the id... This only works if the id is already set. This function does not
     * retrieve it from the local db from the source.
     *
     * @return string matches id in a /RatingSync/Film
     */
    public function getFilmId()
    {
        return $this->filmId;
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
            throw new \InvalidArgumentException("setYourScore ($score) must be an int between 1 to 10");
        }

        if (is_null($score)) {
            $this->yourScore = null;
        } else {
            $this->yourScore = (int)$score;
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
            throw new \InvalidArgumentException("setYourScore ($score) must be an int between 1 to 10");
        }

        if (is_null($score)) {
            $this->suggestedScore = null;
        } else {
            $this->suggestedScore = (int)$score;
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
            throw new \InvalidArgumentException("setYourScore ($score) must be an int between 1 to 10");
        }

        if (is_null($score)) {
            $this->criticScore = null;
        } else {
            $this->criticScore = (int)$score;
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
            throw new \InvalidArgumentException("setYourScore ($score) must be an int between 1 to 10");
        }

        if (is_null($score)) {
            $this->userScore = null;
        } else {
            $this->userScore = (int)$score;
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
     * Valid scores are integers 1 to 10.  Strings can be casted, but
     * no floats.
     *
     * @param int $score 1 to 10
     *
     * @return true=valid, false=invalid
     */
    public function validRatingScore($score)
    {
        if ( is_numeric($score) &&
             ((float)$score == (int)$score) &&
             (1 <= (int)$score && (int)$score <= 10) ) {
            return true;
        } else {
            return false;
        }
    }
}