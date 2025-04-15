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
    // Attr names in JSON from a source
    const ATTR_UNIQUE_NAME = "uniqueName";
    const ATTR_IMAGE = "source_image";
    const ATTR_CRITIC_SCORE = "criticScore";
    const ATTR_USER_SCORE = "userScore";
    
    protected $name;
    protected $image;
    protected $uniqueName;
    protected $parentUniqueName;
    protected $uniqueEpisode;
    protected $uniqueAlt;
    protected $streamUrl;
    protected $streamDate;  // Date as a string Y-m-d (1999-01-01)
    protected $rating;
    protected $criticScore;     // Average rating by critics through the source
    protected $userScore;       // Average rating by users through the source
    protected $archive = array(); // Inactive ratings

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
                                Constants::SOURCE_OMDBAPI,
                                Constants::SOURCE_TMDBAPI,
                                Constants::SOURCE_RATINGSYNC,
                                Constants::SOURCE_NETFLIX,
                                Constants::SOURCE_RT,
                                Constants::SOURCE_XFINITY,
                                Constants::SOURCE_HULU,
                                Constants::SOURCE_AMAZON);

        if (in_array($source, $validSources)) {
            return true;
        }
        return false;
    }

    public static function supportedSourceWebsites()
    {
        return array(Constants::SOURCE_IMDB);
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
        $this->uniqueName = empty($uniqueName) ? null : $uniqueName;
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

    public function setParentUniqueName($parentUniqueName)
    {
        $this->parentUniqueName = empty($parentUniqueName) ? null : $parentUniqueName;
    }

    public function getParentUniqueName()
    {
        return $this->parentUniqueName;
    }

    /**
     * Episode to find this film within the source
     *
     * @param string $uniqueEpisode ID to find this film or TV episode within the source
     *
     * @return none
     */
    public function setUniqueEpisode($uniqueEpisode)
    {
        $this->uniqueEpisode = empty($uniqueEpisode) ? null : $uniqueEpisode;
    }

    /**
     * Return the episode... This only works if the id is already set. This function does not
     * retrieve it from the local db from the source.
     *
     * @return string matches id in a /RatingSync/Film
     */
    public function getUniqueEpisode()
    {
        return $this->uniqueEpisode;
    }

    /**
     * Alternate unique id to find this film within the source. Some
     * sources use two unique attributes. Like a number and alpha string.
     *
     * @param string $uniqueAlt ID to find this film within the source
     *
     * @return none
     */
    public function setUniqueAlt($uniqueAlt)
    {
        $this->uniqueAlt = empty($uniqueAlt) ? null : $uniqueAlt;
    }

    /**
     * Return the alternate unique id... This only works if the id is already
     * set. This function does not retrieve it from the local db from the source.
     *
     * @return string matches id in a /RatingSync/Film
     */
    public function getUniqueAlt()
    {
        return $this->uniqueAlt;
    }

    public function setStreamUrl($streamUrl)
    {
        $this->streamUrl = empty($streamUrl) ? null : $streamUrl;
    }

    public function getStreamUrl()
    {
        return $this->streamUrl;
    }

    public function setStreamDate($streamDate)
    {
        if (empty($streamDate) || $streamDate < Constants::DATE_MIN_STR) {
            $streamDate = Constants::DATE_MIN_STR;
        }
        $this->streamDate = $streamDate;
    }

    public function getStreamDate()
    {
        if ($this->streamDate < Constants::DATE_MIN_STR) {
            $this->setStreamDate(Constants::DATE_MIN_STR);
        }
        return $this->streamDate;
    }

    public function refreshStreamDate()
    {
        $this->streamDate = date("Y-m-d");
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

    public function getRating(): Rating
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

    public function setArchive(?array $archive)
    {
        if ( is_null($archive) ) {
            $archive = array();
        }

        $this->archive = $archive;
    }

    public function getArchive(): array
    {
        return $this->archive;
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
        $sourceParentUniqueName = $this->getParentUniqueName();
        $sourceUniqueEpisode = $this->getUniqueEpisode();
        $sourceUniqueAlt = $this->getUniqueAlt();
        $streamUrl = $this->getStreamUrl();
        $streamDate = $this->getStreamDate();
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
        if (!empty($sourceParentUniqueName)) {
            $columns .= ", parentUniqueName";
            $values .= ", '$sourceParentUniqueName'";
            $set .= "$setComma parentUniqueName='$sourceParentUniqueName'";
            $setComma = ",";
        }
        if (!empty($sourceUniqueEpisode)) {
            $columns .= ", uniqueEpisode";
            $values .= ", '$sourceUniqueEpisode'";
            $set .= "$setComma uniqueEpisode='$sourceUniqueEpisode'";
            $setComma = ",";
        }
        if (!empty($sourceUniqueAlt)) {
            $columns .= ", uniqueAlt";
            $values .= ", '$sourceUniqueAlt'";
            $set .= "$setComma uniqueAlt='$sourceUniqueAlt'";
            $setComma = ",";
        }
        if (!empty($streamDate)) {
            $columns .= ", streamUrl, streamDate";
            $values .= ", '$streamUrl', '$streamDate'";
            $set .= "$setComma streamUrl='$streamUrl', streamDate='$streamDate'";
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
        if ($result->rowCount() == 0) {
            $newRow = true;
        }
        
        if ($newRow) {
            $query = "INSERT INTO film_source ($columns) VALUES ($values)";
            logDebug($query, __CLASS__."::".__FUNCTION__." ".__LINE__);
            if (! $db->query($query)) {
                throw new \Exception('SQL Error ' . $db->errorCode() . ". " . $db->errorInfo()[2]);
            }
        } else {
            if ($set != $setEmpty) {
                $query = "UPDATE film_source $set WHERE film_id=$filmId AND source_name='$sourceName'";
                logDebug($query, __CLASS__."::".__FUNCTION__." ".__LINE__);
                if (! $db->query($query)) {
                    throw new \Exception('SQL Error ' . $db->errorCode() . ". " . $db->errorInfo()[2]);
                }
            }
        }

        return true;
    }

    /**
     * Create film_source rows for all sources supported. Get the
     * data from the source websites.
     */
    public static function createAllSourcesToDb($filmId, $username = null)
    {
        if (empty($filmId) || !is_int(intval($filmId))) {
            throw new \InvalidArgumentException("filmId arg must be an int (filmId=$filmId)");
        }

        $film = Film::getFilmFromDb($filmId);
        foreach (self::supportedSourceWebsites() as $sourceName) {
            $source = $film->getSource($sourceName);
            $source->createSourceToDb($film);
        }
    }

    public function createSourceToDb($film)
    {
        if (empty($film) || !($film instanceof Film)) {
            throw new \InvalidArgumentException("film arg must be a /RatingSync/Film");
        }
        
        $needSourceData = empty($this->getUniqueName());
        $needStream = self::validStreamProvider($this->getName());
        $neededDataAvailable = false;

        $site = null;
        $page = null;
        if ($needSourceData || $needStream) {
            $site = self::getSite($this->getName());
            $page = $site->getFilmDetailPage($film, 60, true);
        }
        if (!empty($site) && !empty($page)) {
            $neededDataAvailable = true;
        }

        if ($needSourceData && $neededDataAvailable) {
            $site->parseFilmSource($page, $film);
        }

        if ($needStream && $neededDataAvailable) {
            $url = $site->getStreamUrlByPage($page, $film);
            $this->setStreamUrl($url);
            $this->refreshStreamDate();
        }
        elseif ($site) {
            $this->refreshStreamDate();
        }
            
        $this->saveFilmSourceToDb($film->getId());
    }

    /**
     * Refresh any streams for in the db for this is film if
     * they are older than 1 day.
     */
    public static function refreshStreamsByFilm($filmId, $username = null)
    {
        if (empty($filmId) || !is_int(intval($filmId))) {
            throw new \InvalidArgumentException("filmId arg must be an int (filmId=$filmId)");
        }

        $film = Film::getFilmFromDb($filmId);
        foreach (self::validStreamProviders() as $sourceName) {
            $source = $film->getSource($sourceName);
            $now = new \DateTime();
            $yesterday = $now->sub(new \DateInterval('P1D'));
            
            if (empty($source->getStreamUrl()) || $source->getStreamDate() <= $yesterday) {
                $provider = self::getStreamProvider($sourceName);
                $url = $provider->getStreamUrl($filmId);
                $source->setStreamUrl($url);
                $source->refreshStreamDate();
                $source->saveFilmSourceToDb($filmId);
            }
        }
    }

    public static function getStreamProvider($sourceName, $username = null)
    {
        if (empty($sourceName) || !self::validStreamProvider($sourceName) ) {
            throw new \InvalidArgumentException("\$sourceName ($sourceName) invalid stream provider");
        }

        $provider = null;
        if (empty($username)) {
            $username = getUsername();
        }
        
        if ($sourceName == Constants::SOURCE_XFINITY) {
            $provider = new Xfinity($username);
        }
        /*
          elseif ($sourceName == Constants::SOURCE_NETFLIX) {
            $provider = new Netflix($username);
        } elseif ($sourceName == Constants::SOURCE_AMAZON) {
            $provider = new Amazon($username);
        }
        */
        
        return $provider;
    }

    public static function validStreamProvider($providerName)
    {
        if (in_array($providerName, self::validStreamProviders()))
        {
            return true;
        }
        return false;
    }

    public static function validStreamProviders()
    {
        return array();
    }

    public static function validStreamProvidersBackground()
    {
        return array(Constants::SOURCE_NETFLIX);
    }

    public static function getSite($sourceName, $username = null)
    {
        if (empty($sourceName) || !self::supportedSite($sourceName) ) {
            throw new \InvalidArgumentException("\$sourceName ($sourceName) website not supported by RatingSync yet");
        }

        $site = null;
        if (empty($username)) {
            $username = getUsername();
        }
        
        if ($sourceName == Constants::SOURCE_IMDB) {
            $site = new Imdb($username);
        } elseif ($sourceName == Constants::SOURCE_XFINITY) {
            $site = new Xfinity($username);
        }
        /*
          elseif ($sourceName == Constants::SOURCE_NETFLIX) {
            $site = new Netflix($username);
        } elseif ($sourceName == Constants::SOURCE_AMAZON) {
            $site = new Amazon($username);
        }
        */
        
        return $site;
    }

    public static function supportedSite($sourceName)
    {
        if (in_array($sourceName, self::supportedSourceWebsites()))
        {
            return true;
        }
        return false;
    }

    public static function doesSourceReuseUniqueNames($sourceName, $contentType) {
        if (empty($sourceName) || $sourceName == Constants::SOURCE_XFINITY) {
            if (empty($contentType) || in_array($contentType, array(Film::CONTENT_TV_SERIES, Film::CONTENT_TV_SEASON, Film::CONTENT_TV_EPISODE))) {
                return true;
            }
        }
        return false;
    }
}

?>
