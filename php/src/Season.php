<?php
/**
 * Season Class
 */
namespace RatingSync;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "main.php";

/**
 * Season attrs
 *   name
 *   year
 *   number
 *   episodeCount
 *   image
 *   episodes (array)
 * 
 * Episode indexes
 *   seriesFilmId
 *   sourceId (ID from IMDb, OMDb, TMDb... etc)
 *   uniqueName
 *   title
 *   year
 *   number
 *   seasonNum
 *   image
 *   userScore
 */
class Season {

    protected $name;
    protected $year;
    protected $number;
    protected $episodeCount;
    protected $image;
    protected $episodes = array();

    // Data from a data source (OMDb, TMDb...)
    const ATTR_UNIQUE_NAME = "season_uniqueName";
    const ATTR_NAME = "season_name";
    const ATTR_YEAR = "season_year";
    const ATTR_NUM = "season_number";
    const ATTR_IMAGE = "season_image";
    const ATTR_EPISODES = "season_episodes";
    const ATTR_EPISODE_ID = "season_epsiode_id";
    const ATTR_EPISODE_TITLE = "season_epsiode_title";
    const ATTR_EPISODE_YEAR = "season_epsiode_year";
    const ATTR_EPISODE_NUM = "season_epsiode_number";
    const ATTR_EPISODE_IMAGE = "season_epsiode_image";
    const ATTR_EPISODE_USERSCORE = "season_epsiode_userscore";

    public function __construct()
    {
    }

    public function asArray($encodeTitles = false)
    {
        $arr = array();
        $name = $this->getName();
        if ($encodeTitles) {
            $name = htmlentities($name, ENT_QUOTES);
        }
        $arr['name'] = $name;
        $arr['year'] = $this->getYear();
        $arr['number'] = $this->getNumber();
        $arr['episodeCount'] = $this->getEpisodeCount();
        $arr['image'] = $this->getImage();
        
        $arrEpisodes = array();
        foreach ($this->getEpisodes() as $episode) {
            if ($encodeTitles) {
                $episode["title"] = htmlentities($episode["title"], ENT_QUOTES);
            }
            $arrEpisodes[] = $episode;
        }
        $arr['episodes'] = $arrEpisodes;
        
        return $arr;
    }

    public function json_encode($encodeTitles = false)
    {
        $arr = $this->asArray($encodeTitles);
        return json_encode($arr);
    }

    public function setUniqueName($uniqueName, $source)
    {
        if (! Source::validSource($source) ) {
            throw new \InvalidArgumentException('Source $source invalid setting Unique Name');
        }

        $this->getSource($source)->setUniqueName($uniqueName);
    }

    public function getUniqueName($source)
    {
        if (! Source::validSource($source) ) {
            throw new \InvalidArgumentException('Source $source invalid getting Unique Name');
        }

        $uniqueName = $this->getSource($source)->getUniqueName();
        if (empty($uniqueName) && $source == Constants::SOURCE_OMDBAPI) {
            $uniqueName = $this->getUniqueName(Constants::SOURCE_IMDB);
            $this->setUniqueName($uniqueName, $source);
        }

        return $uniqueName;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
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

    public function setEpisodeCount($episodeCount)
    {
        if ("" == $episodeCount) {
            $episodeCount = null;
        }
        if (! ((is_numeric($episodeCount) && ((float)$episodeCount == (int)$episodeCount)) || is_null($episodeCount)) ) {
            throw new \InvalidArgumentException('Episode count must be an integer');
        }

        if (!is_null($episodeCount)) {
            $episodeCount = (int)$episodeCount;
        }

        $this->episodeCount = $episodeCount;
    }

    public function getEpisodeCount()
    {
        return $this->episodeCount;
    }

    public function setNumber($number)
    {
        if ("" == $number) {
            $number = null;
        }
        if (! ((is_numeric($number) && ((float)$number == (int)$number)) || is_null($number)) ) {
            throw new \InvalidArgumentException('Season number must be an integer');
        }

        if (!is_null($number)) {
            $number = (int)$number;
        }

        $this->number = $number;
    }

    public function getNumber()
    {
        return $this->number;
    }

    public function setImage($image)
    {
        $this->image = $image;
    }
    
    public function getImage()
    {
        return $this->image;
    }

    public function removeAllEpisodes()
    {
        $this->episodes = array();
    }

    public function getEpisodes()
    {
        return $this->episodes;
    }

    public function getEpisode($episodeNumber)
    {
        $episode = null;
        if (array_key_exists($episodeNumber, $this->getEpisodes())) {
            $episode = $this->getEpisodes()[$episodeNumber];
        }

        return $episode;
    }

    public function addEpisode($episodeNumber)
    {
        if (!is_numeric($episodeNumber)) {
            throw new \InvalidArgumentException('addEpisode() episodeNumber param must be a number');
        }

        if (!array_key_exists($episodeNumber, $this->getEpisodes())) {
            $episode = array("number" => $episodeNumber);
            $this->episodes[$episodeNumber] = $episode;
        }
    }

    public function setEpisodeSeriesFilmId($seriesFilmId, $episodeNumber)
    {
        if (!is_numeric($episodeNumber)) {
            throw new \InvalidArgumentException('setEpisodeSeriesFilmId() episodeNumber param must be a number');
        } elseif (is_null($seriesFilmId) || !is_numeric($seriesFilmId)) {
            throw new \InvalidArgumentException('setEpisodeSeriesFilmId() seriesFilmId param must be a number or NULL');
        }

        $this->addEpisode($episodeNumber);
        $this->episodes[$episodeNumber]["seriesFilmId"] = $seriesFilmId;
    }

    public function setEpisodeSourceId($sourceId, $episodeNumber)
    {
        if (!is_numeric($episodeNumber)) {
            throw new \InvalidArgumentException('setEpisodeSourceId() episodeNumber param must be a number');
        }

        $this->addEpisode($episodeNumber);
        $this->episodes[$episodeNumber]["sourceId"] = $sourceId;
    }

    public function setEpisodeUniqueName($uniqueName, $episodeNumber)
    {
        if (!is_numeric($episodeNumber)) {
            throw new \InvalidArgumentException('setEpisodeUniqueName() episodeNumber param must be a number');
        }

        $this->addEpisode($episodeNumber);
        $this->episodes[$episodeNumber]["uniqueName"] = $uniqueName;
    }

    public function setEpisodeUniqueNameBySourceId($sourceId, $episodeNumber)
    {
        if (!is_numeric($episodeNumber)) {
            throw new \InvalidArgumentException('setEpisodeUniqueNameBySourceId() episodeNumber param must be a number');
        }

        $api = getMediaDbApiClient();
        $uniqueName = $api->getUniqueNameFromSourceId($sourceId, Film::CONTENT_TV_EPISODE);

        $this->setEpisodeUniqueName($uniqueName, $episodeNumber);
    }

    public function setEpisodeTitle($title, $episodeNumber)
    {
        if (!is_numeric($episodeNumber)) {
            throw new \InvalidArgumentException('setEpisodeTitle() episodeNumber param must be a number');
        }

        $this->addEpisode($episodeNumber);
        $this->episodes[$episodeNumber]["title"] = $title;
    }

    public function setEpisodeYear($year, $episodeNumber)
    {
        if (!is_numeric($episodeNumber)) {
            throw new \InvalidArgumentException('setEpisodeYear() episodeNumber param must be a number');
        }
        if ("" == $year) {
            $year = null;
        }
        if (! ((is_numeric($year) && ((float)$year == (int)$year) && (1850 <= (int)$year)) || is_null($year)) ) {
            throw new \InvalidArgumentException('Year must be an integer above 1849 or NULL');
        }

        if (!is_null($year)) {
            $year = (int)$year;
        }

        $this->addEpisode($episodeNumber);
        $this->episodes[$episodeNumber]["year"] = $year;
    }

    public function setEpisodeSeasonNumber($seasonNum, $episodeNumber)
    {
        if (!is_numeric($episodeNumber)) {
            throw new \InvalidArgumentException('setEpisodeSeasonNumber() episodeNumber param must be a number');
        } elseif (is_null($seasonNum) || !is_numeric($seasonNum)) {
            throw new \InvalidArgumentException('setEpisodeSeasonNumber() seasonNum param must be a number or NULL');
        }

        $this->addEpisode($episodeNumber);
        $this->episodes[$episodeNumber]["seasonNum"] = $seasonNum;
    }

    public function setEpisodeImage($image, $episodeNumber)
    {
        if (!is_numeric($episodeNumber)) {
            throw new \InvalidArgumentException('setEpisodeImage() episodeNumber param must be a number');
        }

        $this->addEpisode($episodeNumber);
        $this->episodes[$episodeNumber]["image"] = $image;
    }

    public function setEpisodeUserScore($userScore, $episodeNumber)
    {
        if (!is_numeric($episodeNumber)) {
            throw new \InvalidArgumentException('setEpisodeUserScore() episodeNumber param must be a number');
        } elseif (is_null($userScore) || !is_numeric($userScore)) {
            throw new \InvalidArgumentException('setEpisodeUserScore() userScore param must be a number or NULL');
        }

        $this->addEpisode($episodeNumber);
        $this->episodes[$episodeNumber]["userScore"] = $userScore;
    }
}