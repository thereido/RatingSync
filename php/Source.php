<?php
/**
 * Source class
 */
namespace RatingSync;

/**
 * Store and retrieve film data for one piece of content (film, tv show...)
 * on one source.  Sources like IMDb, RottenTomatoes, Jinni, etc. or a local
 * database in this app.
 */
class Source
{
    protected $name;
    protected $image;
    protected $filmId;
    protected $urlName;
    protected $rating;

    /**
     * Film data from one source
     *
     * @param string $source IMDb, RottenTomatoes, Jinni, Local, etc. Options are /RatingSync/Constants::SOURCE_***
     */
    public function __construct($sourceName)
    {
        if (! self::validSource($sourceName) ) {
            throw new \InvalidArgumentException('Source $sourceName invalid');
        }

        $this->name = $sourceName;
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

    public function setUrlName($urlName)
    {
        if (0 == strlen($urlName)) {
            $urlName = null;
        }
        $this->urlName = $urlName;
    }

    public function getUrlName()
    {
        return $this->urlName;
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
}

?>
