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
    const SOURCE_JINNI      = "Jinni";
    const SOURCE_IMDB       = "IMDb";

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
     * @param string $source IMDb, RottenTomatoes, Jinni, Local, etc. Options are /RatingSync/Rating::SOURCE_***
     */
    public function __construct($source)
    {
        $this->source = $source;
    }

    /**
     * Website the rating comes from
     *
     * @return string \RatingSync\Rating::SOURCE_***
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * ID to find this film within the source
     *
     * @param int $filmId ID to find this film within the source
     *
     * @return none
     */
    public function setFilmId($filmId)
    {
        $this->filmId = (int)$filmId;
    }

    /**
     * Return the id... This only works if the id is already set. This function does not
     * retrieve it from the local db from the source.
     *
     * @return int matches id in a /RatingSync/Film
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
    public function setYourScore($yourScore)
    {
        $this->yourScore = (int)$yourScore;
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
     * @param str $yourRatingDate The day you rated it
     *
     * @return none
     */
    public function setYourRatingDate($yourRatingDate)
    {
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
    public function setSuggestedScore($suggestedScore)
    {
        $this->suggestedScore = (int)$suggestedScore;
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
    public function setCriticScore($criticScore)
    {
        $this->criticScore = (int)$criticScore;
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
    public function setUserScore($userScore)
    {
        $this->userScore = (int)$userScore;
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
}