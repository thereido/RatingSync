<?php
/**
 * Film Class
 */
namespace RatingSync;

class Film {
    const CONTENT_FILM      = 'FeatureFilm';
    const CONTENT_TV        = 'TvSeries';
    const CONTENT_SHORTFILM = 'ShortFilm';

    /**
     * @var http
     */
    protected $http;

    protected $title;
    protected $year;
    protected $contentType;
    protected $image;
    protected $ratings = array();
    protected $urlNames = array();
    protected $genres = array();
    protected $director;
    protected $defaultRatingSource = \RatingSync\Rating::SOURCE_JINNI;

    public function __construct(HttpJinni $http)
    {
        if (! ($http instanceof HttpJinni) ) {
            throw new \InvalidArgumentException('Film contrust must have an Http object');
        }

        $this->http = $http;
        $this->urlNames = array(static::CONTENT_FILM, static::CONTENT_TV, static::CONTENT_SHORTFILM);
    }

    public static function validContentType($type)
    {       
        if (in_array($type, array(static::CONTENT_FILM, static::CONTENT_TV, static::CONTENT_SHORTFILM))) {
            return true;
        }
        return false;
    }

    /**
     * <film>
           <title/>
           <year/>
           <contentType/>
           <image/>
           <directors>
               <director/>
           </directors>
           <genres>
               <genre/>
           </genres>
           <source>
               <sourceName/>
               <filmId/>
               <urlName/>
               <rating>
                   <yourScore/>
                   <yourRatingDate/>
                   <suggestedScore/>
                   <criticScore/>
                   <userScore/>
               </rating>
           </source>
       </film>
     *
     * @param SimpleXMLElement $xml Add new <film> into this param
     *
     * @return none
     */
    public function addXmlChild($xml)
    {
        $filmXml = $xml->addChild("film");
        $filmXml->addChild('title', htmlentities($this->getTitle()));
        $filmXml->addChild('year', $this->getYear());
        $filmXml->addChild('contentType', $this->getContentType());
        $filmXml->addChild('image', $this->getImage());

        $directorsXml = $filmXml->addChild('directors');
        $directorsXml->addChild('director', $this->getDirector()); /* FIXME */

        $genresXml = $filmXml->addChild('genres');
        foreach ($this->getGenres() as $genre) {
            $genresXml->addChild('genre', htmlentities($genre));
        }

        foreach ($this->ratings as $rating) {
            $sourceXml = $filmXml->addChild('source');
            $sourceXml->addChild('sourceName', $rating->getSource());
            $sourceXml->addChild('filmId', $rating->getFilmId());
            $sourceXml->addChild('urlName', $this->getUrlName($rating->getSource()));
            $ratingXml = $sourceXml->addChild('rating');
            $ratingXml->addChild('yourScore', $rating->getYourScore());
            $ratingXml->addChild('yourRatingDate', $rating->getYourRatingDate());
            $ratingXml->addChild('suggestedScore', $rating->getSuggestedScore());
            $ratingXml->addChild('criticScore', $rating->getCriticScore());
            $ratingXml->addChild('userScore', $rating->getUserScore());
        }
    }

    public function setUrlName($urlName, $source)
    {
        if (! Rating::validSource($source) ) {
            throw new \InvalidArgumentException('Source $source invalid setting URL name');
        }

        if (0 == strlen($urlName)) {
            $urlName = null;
        }
        $this->urlNames[$source] = $urlName;
    }

    public function getUrlName($source)
    {
        if (! Rating::validSource($source) ) {
            throw new \InvalidArgumentException('Source $source invalid getting URL name');
        }

        $urlName = null;
        if (array_key_exists($source, $this->urlNames)) {
            $urlName = $this->urlNames[$source];
        }

        return $urlName;
    }

    public function setRating($yourRating, $source = null)
    {
        if (is_null($source)) {
            if (is_null($yourRating)) {
                throw new \InvalidArgumentException('There must be one or both of Source and new Rating');
            } else {
                $source = $yourRating->getSource();
            }
        } else {
            if (! Rating::validSource($source) ) {
                throw new \InvalidArgumentException('Invalid source '.$source);
            }
        }

        if (! (is_null($yourRating) || "" == $yourRating || $yourRating instanceof Rating) ) {
            throw new \InvalidArgumentException('Rating param must be a Rating Class: '.$yourRating);
        }

        if (empty($source)) {
                throw new \Exception('No source found for setting a rating');
        } 

        if ("" == $yourRating) {
            $yourRating = null;
        } else {
            if (is_null($yourRating->getSource())) {
                $yourRating->setSource($source);
            } elseif (! ($source == $yourRating->getSource()) ) {
                throw new \InvalidArgumentException("If param source is given it must match param rating's source. Param: ".$source." Rating source: ".$yourRating->getSource());
            }
        }

        $this->ratings[$source] = $yourRating;
    }

    public function getRating($source)
    {
        if (! Rating::validSource($source) ) {
            throw new \InvalidArgumentException('Source '.$source.' invalid getting rating');
        }

        $rating = null;
        if (array_key_exists($source, $this->ratings)) {
            $rating = $this->ratings[$source];
        } else {
            $rating = new \RatingSync\Rating($source);
        }

        return $rating;
    }

    public function setYourScore($yourScore, $source)
    {
        if (! Rating::validSource($source) ) {
            throw new \InvalidArgumentException('Source $source invalid setting YourScore');
        }

        $rating = $this->getRating($source);
        $rating->setYourScore($yourScore);
        $this->setRating($rating, $source);
    }

    public function getYourScore($source)
    {
        if (! Rating::validSource($source) ) {
            throw new \InvalidArgumentException('Source $source invalid getting YourScore');
        }

        return $this->getRating($source)->getYourScore();
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setYear($year)
    {
        if ("" == $year) {
            $year = null;
        }
        if (! ((is_numeric($year) && ((float)$year == (int)$year) && (1850 <= (int)$year)) || is_null($year)) ) {
            throw new \InvalidArgumentException('Year must be an integer above 1849 or NULL');
        }

        if (!is_null($year)) {
            $year = (int)$year;
        }

        $this->year = $year;
    }

    public function getYear()
    {
        return $this->year;
    }

    public function setContentType($type)
    {
        if ("" == $type) {
            $type = null;
        }
        if (! (is_null($type) || self::validContentType($type)) ) {
            throw new \InvalidArgumentException('Invalid content type: '.$type);
        }

        $this->contentType = $type;
    }

    public function getContentType()
    {
        return $this->contentType;
    }

    public function setImage($image)
    {
        $this->image = $image;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function addGenre($new_genre)
    {
        if (empty($new_genre)) {
            throw new \InvalidArgumentException('addGenre param must not be empty');
        }

        if (!in_array($new_genre, $this->genres)) {
            $this->genres[] = $new_genre;
        }
    }

    public function removeGenre($removeThisGenre)
    {
        $remainingGenres = array();
        for ($x = 0; $x < count($this->genres); $x++) {
            if ($removeThisGenre != $this->genres[$x]) {
                $remainingGenres[] = $this->genres[$x];
            }
        }
        $this->genres = $remainingGenres;
    }

    public function removeAllGenres()
    {
        $this->genres = array();
    }

    public function getGenres()
    {
        return $this->genres;
    }

    public function isGenre($genre)
    {
        return in_array($genre, $this->genres);
    }

    /**
     * @param string $director Separate with commas when they are more than one
     */
    public function setDirector($director)
    {
        $this->director = $director;
    }

    public function getDirector()
    {
        return $this->director;
    }
}