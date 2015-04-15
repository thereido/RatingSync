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
    protected $ratings = [];
    protected $urlNames = [];
    protected $defaultRatingSource = \RatingSync\Rating::SOURCE_JINNI;

    public function __construct(HttpJinni $http)
    {
        $this->http = $http;
    }

    public static function validContentType($type)
    {       
        if (in_array($type, array(static::CONTENT_FILM, static::CONTENT_TV, static::CONTENT_SHORTFILM))) {
            return true;
        }
        return false;
    }

    public function setUrlName($urlName, $source)
    {
        $this->urlNames[$source] = $urlName;
    }

    public function getUrlName($source)
    {
        $urlName = null;
        if (array_key_exists($source, $this->urlNames)) {
            $urlName = $this->urlNames[$source];
        }

        return $urlName;
    }

    public function setRating($yourRating, $source = null)
    {
        if ($source == null) {
            $source = $yourRating->getSource();
        }
        $this->ratings[$source] = $yourRating;
    }

    public function getRating($source)
    {
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
        $rating = getRating($source);
        $rating->setYourScore($yourScore);
        $this->setRating($rating, $source);
    }

    public function getYourScore($source)
    {
        return getRating($source)->getYourScore();
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
        $this->year = (int)$year;
    }

    public function getYear()
    {
        return $this->year;
    }

    public function setContentType($type)
    {
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
}