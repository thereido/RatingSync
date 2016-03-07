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

    /**
     * Film data from one source
     *
     * @param string $source IMDb, RottenTomatoes, Jinni, Local, etc. Options are /RatingSync/Constants::SOURCE_***
     */
    public function __construct($sourceName, $filmId = null)
    {
        if (! self::validSource($sourceName) ) {
            throw new \InvalidArgumentException('Source $sourceName invalid');
        }

        $this->name = $sourceName;

        if ($this->name == Constants::SOURCE_RATINGSYNC && !empty($filmId)) {
            // Default uniqueName
            $this->uniqueName = "rs$filmId";
        }
    }

    public static function validSource($source)
    {
        if (in_array($source, array(Constants::SOURCE_JINNI, Constants::SOURCE_IMDB, Constants::SOURCE_RATINGSYNC))) {
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
            
        $columns = "film_id, source_name";
        $values = "$filmId, '$sourceName'";
        if (!empty($sourceImage)) {
            $columns .= ", image";
            $values .= ", '$sourceImage'";
        }
        if (!empty($sourceUniqueName)) {
            $columns .= ", uniqueName";
            $values .= ", '$sourceUniqueName'";
        }
        if (! $db->query("REPLACE INTO film_source ($columns) VALUES ($values)")) {
            throw new \Exception('SQL Error ' . $db->errno);
        }

        return true;
    }
}

?>
