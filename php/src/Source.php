<?php
/**
 * Source class
 */
namespace RatingSync;

require_once "Constants.php";

/**
 * Store and retrieve film data for one piece of content (film, tv show...)
 * on one source.  Sources like IMDb, RottenTomatoes, Jinni, etc. or a local
 * database in this app.
 */
class Source
{
    protected $name;
    protected $image;
    protected $uniqueName;
    protected $rating;
    protected $criticScore;     // Average rating by critics through the source
    protected $userScore;       // Average rating by users through the source

    /**
     * Film data from one source
     *
     * @param string $source IMDb, RottenTomatoes, Jinni, Local, etc. Options are /RatingSync/Constants::SOURCE_***
     */
    public function __construct($sourceName, $filmId = null)
    {
        if (! self::validSource($sourceName) ) {
            throw new \InvalidArgumentException("Source \$sourceName ($sourceName) invalid");
        }

        $this->name = $sourceName;

        if ($this->name == Constants::SOURCE_RATINGSYNC && !empty($filmId)) {
            // Default uniqueName
            $this->uniqueName = "rs$filmId";
        }
    }

    public static function validSource($source)
    {
        $validSources = array(Constants::SOURCE_JINNI,
                                Constants::SOURCE_IMDB,
                                Constants::SOURCE_RATINGSYNC,
                                Constants::SOURCE_NETFLIX,
                                Constants::SOURCE_RT,
                                Constants::SOURCE_XFINITY);

        if (in_array($source, $validSources)) {
            return true;
        }
        return false;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setImage($image)
    {
        $this->image = $image;
    }

    public function getImage()
    {
        return $this->image;
    }

    /**
     * ID to find this film within the source
     *
     * @param string $uniqueName ID to find this film within the source
     *
     * @return none
     */
    public function setUniqueName($uniqueName)
    {
        if (0 == strlen($uniqueName)) {
            $uniqueName = null;
        }
        $this->uniqueName = $uniqueName;
    }

    /**
     * Return the id... This only works if the id is already set. This function does not
     * retrieve it from the local db from the source.
     *
     * @return string matches id in a /RatingSync/Film
     */
    public function getUniqueName()
    {
        return $this->uniqueName;
    }

    public function setRating($yourRating)
    {
        if (! (is_null($yourRating) || "" == $yourRating || $yourRating instanceof Rating) ) {
            throw new \InvalidArgumentException('Rating param must be a Rating Class: '.$yourRating);
        } else if ($yourRating instanceof Rating && $yourRating->getSource() != $this->getName()) {
            throw new \InvalidArgumentException('Source mismatch: Source name ('.$this->getName().') must be match $yourRating source ('.$yourRating->getSource().')');
        }

        if ("" == $yourRating) {
            $yourRating = null;
        }
        $this->rating = $yourRating;
    }

    public function getRating()
    {
        $rating = $this->rating;
        if (is_null($this->rating)) {
            $rating = new Rating($this->name);
        }

        return $rating;
    }

    public function setYourScore($yourScore)
    {
        $rating = $this->getRating($this->name);
        $rating->setYourScore($yourScore);
        $this->setRating($rating, $this->name);
    }

    public function getYourScore()
    {
        return $this->getRating()->getYourScore();
    }

    /**
     * What the source's critics scored it
     *
     * @param int $score What the source's critics scored it
     *
     * @return none
     */
    public function setCriticScore($score)
    {
        if (! (is_null($score) ||  Rating::validRatingScore($score)) ) {
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
     * @param int $score What the source's users scored it
     *
     * @return none
     */
    public function setUserScore($score)
    {
        if (! (is_null($score) ||  Rating::validRatingScore($score)) ) {
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
     * Create a film/source row in the db if not already exists
     *
     * @param int $filmID Database id of the film rated
     */
    public function saveFilmSourceToDb($filmId)
    {
        if (empty($filmId)) {
            throw new \InvalidArgumentException('filmId cannot be empty');
        }
        
        $db = getDatabase();
        $sourceName = $this->getName();
        $sourceImage = $this->getImage();
        $sourceUniqueName = $this->getUniqueName();
        $criticScore = $this->getCriticScore();
        $userScore = $this->getUserScore();
            
        $columns = "film_id, source_name";
        $values = "$filmId, '$sourceName'";
        $set = "SET";
        $setEmpty = $set;
        $setComma = "";
        if (!empty($sourceImage)) {
            $columns .= ", image";
            $values .= ", '$sourceImage'";
            $set .= "$setComma image='$sourceImage'";
            $setComma = ",";
        }
        if (!empty($sourceUniqueName)) {
            $columns .= ", uniqueName";
            $values .= ", '$sourceUniqueName'";
            $set .= "$setComma uniqueName='$sourceUniqueName'";
            $setComma = ",";
        }
        if (!empty($criticScore)) {
            $columns .= ", criticScore";
            $values .= ", $criticScore";
            $set .= "$setComma criticScore=$criticScore";
            $setComma = ",";
        }
        if (!empty($userScore)) {
            $columns .= ", userScore";
            $values .= ", $userScore";
            $set .= "$setComma userScore=$userScore";
            $setComma = ",";
        }
        
        // Look for an existing film row
        $newRow = false;
        $result = $db->query("SELECT 1 FROM film_source WHERE film_id=$filmId AND source_name='$sourceName'");
        if ($result->num_rows == 0) {
            $newRow = true;
        }

        if ($newRow) {
            $query = "INSERT INTO film_source ($columns) VALUES ($values)";
            logDebug($query, __FUNCTION__." ".__LINE__);
            if (! $db->query($query)) {
                throw new \Exception('SQL Error ' . $db->errno . ". " . $db->error);
            }
        } else {
            if ($set != $setEmpty) {
                $query = "UPDATE film_source $set WHERE film_id=$filmId AND source_name='$sourceName'";
                logDebug($query, __FUNCTION__." ".__LINE__);
                if (! $db->query($query)) {
                    throw new \Exception('SQL Error ' . $db->errno . ". " . $db->error);
                }
            }
        }

        return true;
    }
}

?>
