<?php
/**
 * SimilarFilm Class
 */
namespace RatingSync;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "main.php";

/**
 * Similar attrs
 *   sourceName
 *   uniqueName
 *   title
 *   year
 *   poster
 *   backdrop
 *   score
 */
class SimilarFilm {

    protected $sourceName;
    protected $uniqueName;
    protected $title;
    protected $year;
    protected $contentType;
    protected $poster;
    protected $backdrop;
    protected $score;

    // Data from a data source (OMDb, TMDb...)
    const ATTR_UNIQUE_NAME = "similar_uniqueName";
    const ATTR_TITLE = "similar_title";
    const ATTR_YEAR = "similar_year";
    const ATTR_CONTENT_TYPE = "similar_contentType";
    const ATTR_POSTER = "similar_poster";
    const ATTR_BACKDROP = "similar_backdrop";
    const ATTR_SCORE = "similar_score";

    public function __construct()
    {
    }

    public function asArray($encodeTitles = false)
    {
        $arr = array();
        $name = $this->getSourceName();
        if ($encodeTitles) {
            $name = htmlentities($name, ENT_QUOTES);
        }
        $arr['sourceName'] = $this->getSourceName();
        $arr['uniqueName'] = $this->getUniqueName();
        $arr['title'] = $this->getTitle();
        $arr['year'] = $this->getYear();
        $arr['contentType'] = $this->getContentType();
        $arr['poster'] = $this->getPoster();
        $arr['backdrop'] = $this->getBackdrop();
        $arr['score'] = $this->getScore();

        return $arr;
    }

    public function json_encode($encodeTitles = false)
    {
        $arr = $this->asArray($encodeTitles);
        return json_encode($arr);
    }

    public function setSourceName($sourceName)
    {
        $this->sourceName = $sourceName;
    }

    public function getSourceName()
    {
        return $this->sourceName;
    }

    public function setUniqueName($uniqueName)
    {
        $this->uniqueName = $uniqueName;
    }

    public function getUniqueName()
    {
        return $this->uniqueName;
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
        if ( ! $this->isValidYear($year, true) ) {
            throw new \InvalidArgumentException('Year must be an integer above 1849 or NULL');
        }

        if (is_float($year)) {
            $year = round($year);
        }

        $this->year = $year;
    }

    public function getYear()
    {
        return $this->year;
    }

    private function isValidYear($year, $allowNull = false)
    {
        if ( is_null($year) && ! $allowNull )
            return false;

        if ( ! is_numeric($year) )
            return false;

        if ( ! round($year) > 1849 )
            return false;

        return true;
    }

    public function setContentType($type)
    {
        if ("" == $type) {
            $type = null;
        }
        if (! (is_null($type) || Film::validContentType($type)) ) {
            throw new \InvalidArgumentException('Invalid content type: '.$type);
        }

        $this->contentType = $type;
    }

    public function getContentType()
    {
        return $this->contentType;
    }

    public function setPoster($poster)
    {
        $this->poster = $poster;
    }

    public function getPoster()
    {
        return $this->poster;
    }

    public function setBackdrop($backdrop)
    {
        $this->backdrop = $backdrop;
    }

    public function getBackdrop()
    {
        return $this->backdrop;
    }

    public function getScore()
    {
        return $this->score;
    }

    public function setScore($score)
    {
        if (is_null($score) || !is_numeric($score)) {
            throw new \InvalidArgumentException('setScore() score param must be a number or NULL');
        }

        if (is_float($score)) {
            $score = round($score);
        }

        $this->score = $score;
    }
}