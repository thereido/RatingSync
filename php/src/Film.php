<?php
/**
 * Film Class
 */
namespace RatingSync;

require_once "Http.php";
require_once "Filmlist.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "main.php";

class Film {
    const CONTENT_FILM      = 'FeatureFilm';
    const CONTENT_SHORTFILM = 'ShortFilm';
    const CONTENT_TV_MOVIE  = 'TvMovie';
    const CONTENT_TV_SERIES = 'TvSeries';
    const CONTENT_TV_SEASON = 'TvSeason';
    const CONTENT_TV_EPISODE = 'TvEpisode';

    // Data from a data source (OMDb, TMDb...)
    const ATTR_IMDB_ID = "imdbId";
    const ATTR_PARENT_ID = "parentId";
    const ATTR_TITLE = "title";
    const ATTR_YEAR = "year";
    const ATTR_CONTENT_TYPE = "contentType";
    const ATTR_SEASON_COUNT = "seasonCount";
    const ATTR_SEASON_NUM = "season";
    const ATTR_EPISODE_NUM = "episodeNumber";
    const ATTR_EPISODE_TITLE = "episodeTitle";
    const ATTR_GENRES = "genres";
    const ATTR_DIRECTORS = "directors";
    
    protected $id;
    protected $parentId;
    protected $title;
    protected $year;
    protected $contentType;
    protected $seasonCount;
    protected $season;
    protected $episodeNumber;
    protected $episodeTitle;
    protected $image;
    protected $refreshDate;
    protected $sources = array();
    protected $genres = array();
    protected $directors = array();
    protected $filmlists = array();

    public function __construct()
    {
    }

    public static function validContentType($contentType)
    {
        $validTypes = array(static::CONTENT_FILM,
                            static::CONTENT_SHORTFILM,
                            static::CONTENT_TV_MOVIE,
                            static::CONTENT_TV_SERIES,
                            static::CONTENT_TV_SEASON,
                            static::CONTENT_TV_EPISODE);
        if (in_array($contentType, $validTypes)) {
            return true;
        }
        return false;
    }

    /**
     * <film title="">
           <parentId/>
           <title/>
           <year/>
           <contentType/>
           <seasonCount/>
           <season/>
           <episodeNumber/>
           <episodeTitle/>
           <image/>
           <refreshDate/>
           <directors>
               <director/>
           </directors>
           <genres>
               <genre/>
           </genres>
           <source name="">
               <image/>
               <uniqueName/>
               <parentUniqueName/>
               <uniqueEpisode/>
               <uniqueAlt/>
               <streamUrl/>
               <streamDate/>
               <criticScore/>
               <userScore/>
               <rating>
                   <yourScore/>
                   <yourRatingDate/>
                   <suggestedScore/>
               </rating>
           </source>
           <filmlists>
               <listname/>
           </filmlists>
       </film>
     *
     * @param SimpleXMLElement $xml Add new <film> into this param
     *
     * @return none
     */
    public function addXmlChild($xml)
    {
        if (! $xml instanceof \SimpleXMLElement ) {
            throw new \InvalidArgumentException('Function addXmlChild must be given a SimpleXMLElement');
        }

        $filmXml = $xml->addChild("film");
        $filmXml->addAttribute('title', $this->getTitle());
        if (!empty($this->getParentId())) {
            $filmXml->addChild('parentId', $this->getParentId());
        }
        $filmXml->addChild('title', htmlspecialchars($this->getTitle()));
        $filmXml->addChild('year', $this->getYear());
        $filmXml->addChild('contentType', $this->getContentType());
        if (!empty($this->getSeasonCount())) {
            $filmXml->addChild('seasonCount', $this->getSeasonCount());
        }
        if (!empty($this->getSeason())) {
            $filmXml->addChild('season', htmlspecialchars($this->getSeason()));
        }
        if (!empty($this->getEpisodeNumber())) {
            $filmXml->addChild('episodeNumber', $this->getEpisodeNumber());
        }
        if (!empty($this->getEpisodeTitle())) {
            $filmXml->addChild('episodeTitle', htmlspecialchars($this->getEpisodeTitle()));
        }
        $filmXml->addChild('image', $this->getImage());
        $filmXml->addChild('refreshDate', $this->getRefreshDate());

        $directorsXml = $filmXml->addChild('directors');
        $directors = $this->getDirectors();
        foreach ($directors as $director) {
            $directorsXml->addChild('director', htmlspecialchars($director));
        }

        $genresXml = $filmXml->addChild('genres');
        foreach ($this->getGenres() as $genre) {
            $genresXml->addChild('genre', htmlentities($genre, ENT_COMPAT, "utf-8"));
        }

        foreach ($this->sources as $source) {
            $sourceXml = $filmXml->addChild('source');
            $sourceXml->addAttribute('name', $source->getName());
            $sourceXml->addChild('image', $source->getImage());
            $sourceXml->addChild('uniqueName', $source->getUniqueName());
            if (!empty($source->getParentUniqueName())) {
                $sourceXml->addChild('parentUniqueName', $source->getParentUniqueName());
            }
            if (!empty($source->getUniqueEpisode())) {
                $sourceXml->addChild('uniqueEpisode', $source->getUniqueEpisode());
            }
            if (!empty($source->getUniqueAlt())) {
                $sourceXml->addChild('uniqueAlt', $source->getUniqueAlt());
            }
            if (!empty($source->getStreamUrl())) {
                $sourceXml->addChild('streamUrl', $source->getStreamUrl());
            }
            if (!empty($source->getStreamDate())) {
                $sourceXml->addChild('streamDate', $source->getStreamDate());
            }
            $sourceXml->addChild('criticScore', $source->getCriticScore());
            $sourceXml->addChild('userScore', $source->getUserScore());
            $rating = $source->getRating();
            $ratingXml = $sourceXml->addChild('rating');
            $ratingXml->addChild('yourScore', $rating->getYourScore());
            $ratingDate = null;
            if (!is_null($rating->getYourRatingDate())) {
                $ratingDate = $rating->getYourRatingDate()->format("Y-n-j");
            }
            $ratingXml->addChild('yourRatingDate', $ratingDate);
            $ratingXml->addChild('suggestedScore', $rating->getSuggestedScore());
        }

        $filmlists = $this->getFilmlists();
        if (count($filmlists) > 0) {
            $listsXml = $filmXml->addChild('filmlists');
            foreach ($filmlists as $listname) {
                $listsXml->addChild('listname', htmlentities($listname, ENT_COMPAT, "utf-8"));
            }
        }
    }

    public function asArray($encodeTitles = false)
    {
        $arr = array();
        $arr['filmId'] = $this->getId();
        $arr['parentId'] = $this->getParentId();
        $title = $this->getTitle();
        $episodeTitle = $this->getEpisodeTitle();
        if ($encodeTitles) {
            $title = htmlentities($title, ENT_QUOTES);
            $episodeTitle = htmlentities($episodeTitle, ENT_QUOTES);
        }
        $arr['title'] = $title;
        $arr['episodeTitle'] = $episodeTitle;
        $arr['year'] = $this->getYear();
        $arr['contentType'] = $this->getContentType();
        $arr['seasonCount'] = $this->getSeasonCount();
        $arr['season'] = $this->getSeason();
        $arr['episodeNumber'] = $this->getEpisodeNumber();
        $arr['image'] = $this->getImage();
        $arr['refreshDate'] = $this->getRefreshDate();

        $arrDirectors = array();
        foreach ($this->getDirectors() as $director) {
            $arrDirectors[] = htmlentities($director, ENT_QUOTES);
        }
        $arr['directors'] = $arrDirectors;
        
        $arrGenres = array();
        foreach ($this->getGenres() as $genre) {
            $arrGenres[] = htmlentities($genre, ENT_COMPAT, "utf-8");
        }
        $arr['genres'] = $arrGenres;

        $arrSources = array();
        foreach ($this->sources as $source) {
            $name = $source->getName();
            $arrSource = array();
            $arrSource['name'] = $name;
            $arrSource['image'] = $source->getImage();
            $arrSource['uniqueName'] = $source->getUniqueName();
            $arrSource['parentUniqueName'] = $source->getParentUniqueName();
            $arrSource['uniqueEpisode'] = $source->getUniqueEpisode();
            $arrSource['uniqueAlt'] = $source->getUniqueAlt();
            $arrSource['streamUrl'] = $source->getStreamUrl();
            $arrSource['streamDate'] = $source->getStreamDate();
            $arrSource['criticScore'] = $source->getCriticScore();
            $arrSource['userScore'] = $source->getUserScore();

            $arrSource['rating'] = $source->getRating()->asArray();
            $archive = $source->getArchive();
            $arrSource['archiveCount'] = count( $archive );
            $arrSource['archive'] = $archive;

            $arrSources[] = $arrSource;
        }
        $arr['sources'] = $arrSources;
        
        $arrFilmlists = array();
        foreach ($this->getFilmlists() as $listname) {
            $arrFilmlists[] = htmlentities($listname, ENT_COMPAT, "utf-8");
        }
        if (count($arrFilmlists) > 0) {
            $arr['filmlists'] = $arrFilmlists;
        }
        
        return $arr;
    }

    public function json_encode($encodeTitles = false)
    {
        $arr = $this->asArray($encodeTitles);
        return json_encode($arr);
    }

    /**
     * New Film object with data from XML
     *
     * @param \SimpleXMLElement $filmSxe Film data
     *
     * @return a new Film
     */
    public static function createFromXml($filmSxe)
    {
        if (! $filmSxe instanceof \SimpleXMLElement ) {
            throw new \InvalidArgumentException('Function createFromXml must be given a SimpleXMLElement');
        } elseif (empty(Self::xmlStringByKey('title', $filmSxe))) {
            throw new \Exception('Function createFromXml: xml must have a title');
        }

        $film = new Film();
        $film->setParentId(Self::xmlStringByKey('parentId', $filmSxe));
        $film->setTitle(html_entity_decode(Self::xmlStringByKey('title', $filmSxe), ENT_QUOTES, "utf-8"));
        $film->setYear(Self::xmlStringByKey('year', $filmSxe));
        $film->setContentType(Self::xmlStringByKey('contentType', $filmSxe));
        $film->setSeasonCount(Self::xmlStringByKey('seasonCount', $filmSxe));
        $film->setSeason(html_entity_decode(Self::xmlStringByKey('season', $filmSxe), ENT_QUOTES, "utf-8"));
        $film->setEpisodeNumber(Self::xmlStringByKey('episodeNumber', $filmSxe));
        $film->setEpisodeTitle(html_entity_decode(Self::xmlStringByKey('episodeTitle', $filmSxe), ENT_QUOTES, "utf-8"));
        $film->setImage(Self::xmlStringByKey('image', $filmSxe));
        $refreshDateStr = Self::xmlStringByKey('refreshDate', $filmSxe);
        if (!empty($refreshDateStr)) {
            $film->setRefreshDate(new \DateTime($refreshDateStr));
        }

        foreach ($filmSxe->xpath('directors') as $directorsSxe) {
            foreach ($directorsSxe[0]->children() as $directorSxe) {
                if (!empty($directorSxe->__toString())) {
                    $film->addDirector($directorSxe->__toString());
                }
            }
        }

        foreach ($filmSxe->xpath('genres') as $genresSxe) {
            foreach ($genresSxe[0]->children() as $genreSxe) {
                if (!empty($genreSxe->__toString())) {
                    $film->addGenre($genreSxe->__toString());
                }
            }
        }

        foreach ($filmSxe->xpath('source') as $sourceSxe) {
            $sourceName = null;
            $sourceNameSxe = $sourceSxe['name'];
            if (is_null($sourceNameSxe) || is_null($sourceNameSxe[0]) || !Source::validSource($sourceNameSxe[0]->__toString())) {
                continue;
            }
            $source = $film->getSource($sourceNameSxe[0]->__toString());
            $source->setImage(Self::xmlStringByKey('image', $sourceSxe));
            $source->setUniqueName(Self::xmlStringByKey('uniqueName', $sourceSxe));
            $source->setParentUniqueName(Self::xmlStringByKey('parentUniqueName', $sourceSxe));
            $source->setUniqueEpisode(Self::xmlStringByKey('uniqueEpisode', $sourceSxe));
            $source->setUniqueAlt(Self::xmlStringByKey('uniqueAlt', $sourceSxe));
            $source->setStreamUrl(Self::xmlStringByKey('streamUrl', $sourceSxe));
            $source->setStreamDate(Self::xmlStringByKey('streamDate', $sourceSxe));
            $criticScore = Self::xmlStringByKey('criticScore', $sourceSxe);
            if (Rating::validRatingScore($criticScore)) {
                $source->setCriticScore($criticScore);
            }
            $userScore = Self::xmlStringByKey('userScore', $sourceSxe);
            if (Rating::validRatingScore($userScore)) {
                $source->setUserScore($userScore);
            }

            $ratingSxe = $sourceSxe->xpath('rating')[0];
            $rating = new Rating($source->getName());
            $yourScore = Self::xmlStringByKey('yourScore', $ratingSxe);
            if (Rating::validRatingScore($yourScore)) {
                $rating->setYourScore($yourScore);
            }
            $yourRatingDateStr = Self::xmlStringByKey('yourRatingDate', $ratingSxe);
            if (!empty($yourRatingDateStr)) {
                $rating->setYourRatingDate(\DateTime::createFromFormat("Y-n-j", $yourRatingDateStr));
            }
            $suggestedScore = Self::xmlStringByKey('suggestedScore', $ratingSxe);
            if (Rating::validRatingScore($suggestedScore)) {
                $rating->setSuggestedScore($suggestedScore);
            }

            $source->setRating($rating);
        }

        foreach ($filmSxe->xpath('filmlists') as $filmlistsSxe) {
            foreach ($filmlistsSxe[0]->children() as $listnameSxe) {
                if (!empty($listnameSxe->__toString())) {
                    $film->addFilmlist($listnameSxe->__toString());
                }
            }
        }
        
        return $film;
    }

    public static function xmlStringByKey($key, $sxe)
    {
        if (empty($key) || empty($sxe)) {
            return null;
        }
        $needleArray = $sxe->xpath($key);
        if (empty($needleArray)) {
            return null;
        }
        $needleSxe = $needleArray[0];
        return $needleSxe->__toString();
    }

    public function getSource($sourceName)
    {
        if (! Source::validSource($sourceName) ) {
            throw new \InvalidArgumentException('Getting Source $source invalid');
        }
        
        if (empty($this->sources[$sourceName])) {
            $this->sources[$sourceName] = new Source($sourceName, $this->getId());
        }

        return $this->sources[$sourceName];
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

    public function setParentUniqueName($parentUniqueName, $source)
    {
        if (! Source::validSource($source) ) {
            throw new \InvalidArgumentException('Source $source invalid setting Parent Unique Name');
        }

        $this->getSource($source)->setParentUniqueName($parentUniqueName);
    }

    public function getParentUniqueName($source)
    {
        if (! Source::validSource($source) ) {
            throw new \InvalidArgumentException('Source $source invalid getting Parent Unique Name');
        }

        return $this->getSource($source)->getParentUniqueName();
    }

    public function setUniqueEpisode($uniqueEpisode, $source)
    {
        if (! Source::validSource($source) ) {
            throw new \InvalidArgumentException('Source $source invalid setting Unique Episode');
        }

        $this->getSource($source)->setUniqueEpisode($uniqueEpisode);
    }

    public function getUniqueEpisode($source)
    {
        if (! Source::validSource($source) ) {
            throw new \InvalidArgumentException('Source $source invalid getting Unique Episode');
        }

        return $this->getSource($source)->getUniqueEpisode();
    }

    public function setUniqueAlt($uniqueAlt, $source)
    {
        if (! Source::validSource($source) ) {
            throw new \InvalidArgumentException('Source $source invalid setting Unique Alt');
        }

        $this->getSource($source)->setUniqueAlt($uniqueAlt);
    }

    public function getUniqueAlt($source)
    {
        if (! Source::validSource($source) ) {
            throw new \InvalidArgumentException('Source $source invalid getting Unique Alt');
        }

        return $this->getSource($source)->getUniqueAlt();
    }

    /**
     * Set or reset the rating for a given source.  If the $source param
     * is not set then use the $yourRating param's source.  If the $yourRating
     * param is null the $source's rating will get reset to null. 
     *
     * @param \RatingSync\Rating $yourRating New rating
     * @param string|null        $source     Rating source
     */
    public function setRating($yourRating, $source = null)
    {
        if (is_null($source)) {
            if (is_null($yourRating)) {
                throw new \InvalidArgumentException('There must be one or both of Source and new Rating');
            } else {
                $source = $yourRating->getSource();
            }
        } else {
            if (! Source::validSource($source) ) {
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
            if (! ($source == $yourRating->getSource()) ) {
                throw new \InvalidArgumentException("If param source is given it must match param rating's source. Param: ".$source." Rating source: ".$yourRating->getSource());
            }
        }

        $this->getSource($source)->setRating($yourRating);
    }

    public function getRating($source)
    {
        if (! Source::validSource($source) ) {
            throw new \InvalidArgumentException('Source '.$source.' invalid getting rating');
        }

        return $this->getSource($source)->getRating();
    }

    public function setYourScore($yourScore, $source)
    {
        if (! Source::validSource($source) ) {
            throw new \InvalidArgumentException('Source $source invalid setting YourScore');
        }

        $rating = $this->getRating($source);
        $rating->setYourScore($yourScore);
        $this->setRating($rating, $source);
    }

    public function getYourScore($source)
    {
        if (! Source::validSource($source) ) {
            throw new \InvalidArgumentException('Source $source invalid getting YourScore');
        }

        return $this->getRating($source)->getYourScore();
    }

    public function setCriticScore($score, $source)
    {
        if (! Source::validSource($source) ) {
            throw new \InvalidArgumentException('Source $source invalid setting Critic Score');
        }

        $this->getSource($source)->setCriticScore($score);
    }

    public function getCriticScore($source)
    {
        if (! Source::validSource($source) ) {
            throw new \InvalidArgumentException('Source $source invalid getting Critic Score');
        }

        return $this->getSource($source)->getCriticScore();
    }

    public function setUserScore($score, $source)
    {
        if (! Source::validSource($source) ) {
            throw new \InvalidArgumentException('Source $source invalid setting User Score');
        }

        $this->getSource($source)->setUserScore($score);
    }

    public function getUserScore($source)
    {
        if (! Source::validSource($source) ) {
            throw new \InvalidArgumentException('Source $source invalid getting User Score');
        }

        return $this->getSource($source)->getUserScore();
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setParentId($id)
    {
        $this->parentId = $id;
    }

    public function getParentId()
    {
        return $this->parentId;
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

    public function setSeasonCount($seasonCount)
    {
        if ("" == $seasonCount) {
            $seasonCount = null;
        }
        if (! ((is_numeric($seasonCount) && ((float)$seasonCount == (int)$seasonCount)) || is_null($seasonCount)) ) {
            throw new \InvalidArgumentException('Season count must be an integer');
        }

        if (!is_null($seasonCount)) {
            $seasonCount = (int)$seasonCount;
        }

        $this->seasonCount = $seasonCount;
    }

    public function getSeasonCount()
    {
        return $this->seasonCount;
    }

    public function setSeason($season)
    {
        $this->season = $season;
    }

    public function getSeason()
    {
        return $this->season;
    }

    public function setEpisodeNumber($episodeNumber)
    {
        if ("" == $episodeNumber) {
            $episodeNumber = null;
        }
        if (! ((is_numeric($episodeNumber) && ((float)$episodeNumber == (int)$episodeNumber)) || is_null($episodeNumber)) ) {
            throw new \InvalidArgumentException('Episode number must be an integer');
        }

        if (!is_null($episodeNumber)) {
            $episodeNumber = (int)$episodeNumber;
        }

        $this->episodeNumber = $episodeNumber;
    }

    public function getEpisodeNumber()
    {
        return $this->episodeNumber;
    }

    public function setEpisodeTitle($episodeTitle)
    {
        $this->episodeTitle = $episodeTitle;
    }

    public function getEpisodeTitle()
    {
        return $this->episodeTitle;
    }

    /**
     * A Film object has it's own image link, and also one for each
     * source.  The $source param can optionally specific which image
     * to set.
     *
     * @param string      $image link
     * @param string|null $source
     */
    public function setImage($image, $source = null)
    {
        if (empty($source)) {
            $this->image = $image;
        } else {
            if (! Source::validSource($source) ) {
                throw new \InvalidArgumentException('Source $source invalid setting image');
            }
            $this->getSource($source)->setImage($image);
        }
    }
    
    /**
     * A Film object has it's own image link, and also one for each
     * source.  The $source param can optionally specific which image
     * to get.
     *
     * @param string|null $source
     */
    public function getImage($source = null)
    {
        if (empty($source)) {
            return $this->image;
        } else {
            if (! Source::validSource($source) ) {
                throw new \InvalidArgumentException('Source $source invalid setting image');
            }
            return $this->getSource($source)->getImage();
        }
    }

    public function setRefreshDate($refreshDate)
    {
        $this->refreshDate = $refreshDate;
    }

    public function getRefreshDate()
    {
        return $this->refreshDate;
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

    public function addDirector($director)
    {
        if (empty($director)) {
            throw new \InvalidArgumentException('addDirector param must not be empty');
        }

        if (!in_array($director, $this->directors)) {
            $this->directors[] = $director;
        }
    }

    public function removeDirector($removeThisDirector)
    {
        $remainingDirectors = array();
        for ($x = 0; $x < count($this->directors); $x++) {
            if ($removeThisDirector != $this->directors[$x]) {
                $remainingDirectors[] = $this->directors[$x];
            }
        }
        $this->directors = $remainingDirectors;
    }

    public function removeAllDirectors()
    {
        $this->directors = array();
    }

    public function getDirectors()
    {
        return $this->directors;
    }

    public function isDirector($director)
    {
        return in_array($director, $this->directors);
    }

    public function addFilmlist($new_listname)
    {
        if (empty($new_listname)) {
            throw new \InvalidArgumentException(__FUNCTION__.' param must not be empty');
        }

        if (!in_array($new_listname, $this->filmlists)) {
            $this->filmlists[] = $new_listname;
        }
    }

    public function removeFilmlist($removeThisListname)
    {
        $remainingFilmlists = array();
        for ($x = 0; $x < count($this->filmlists); $x++) {
            if ($removeThisListname != $this->filmlists[$x]) {
                $remainingFilmlists[] = $this->filmlists[$x];
            }
        }
        $this->filmlists = $remainingFilmlists;
    }

    public function removeAllFilmlists()
    {
        $this->filmlists = array();
    }

    public function getFilmlists()
    {
        return $this->filmlists;
    }

    public function inFilmlist($listname)
    {
        return in_array($listname, $this->filmlists);
    }

    /**
     * Return only streams with a URL
     *
     * @return array Indexes are "url" and "date"
     */
    public function getStreams()
    {
        $streams = array();

        foreach ($this->sources as $source) {
            $sourceName = $source->getName();
            $streamUrl = $source->getStreamUrl();
            $streamDate = $source->getStreamDate();
            if (! empty($streamUrl) ) {
                $stream = array("url" => $streamUrl, "date" => $streamDate);
                $streams[$sourceName] = $stream;
            }
        }

        return $streams;
    }

    public function saveToDb($username = null)
    {
        if (empty($this->getTitle())) {
            throw new \InvalidArgumentException("Film must have a title");
        }
        $db = getDatabase();
        $errorFree = true;

        $filmId = $this->id;
        $parentId = $this->parentId;
        $title = $db->quote($this->getTitle());
        $year = $this->getYear();
        $contentType = $this->getContentType();
        $seasonCount = $this->getSeasonCount();
        $season = $db->quote($this->getSeason());
        $episodeNumber = $this->getEpisodeNumber();
        $episodeTitle = $db->quote($this->getEpisodeTitle());
        $image = $this->getImage();
        $refreshDate = $this->getRefreshDate();
        $refreshDateUpdate = ", refreshDate=NULL";
        if (!empty($refreshDate)) {
            $refreshDateUpdate = ", refreshDate='" . $refreshDate->format('Y-m-d H:i:s') . "'";
        }
        
        if (is_null($parentId)) {
            $parentId = "NULL";
        }
        $selectYear = "year=$year";
        if (is_null($year)) {
            $selectYear = "year IS NULL";
            $year = "NULL";
        }
        if (is_null($seasonCount)) {
            $seasonCount = "NULL";
        }
        $selectEpisodeNumber = "episodeNumber=$episodeNumber";
        if (is_null($episodeNumber)) {
            $selectEpisodeNumber = "episodeNumber IS NULL";
            $episodeNumber = "NULL";
        }
        
        // Look for an existing film row
        $newRow = false;
        if (empty($filmId)) {
            $query  = "SELECT id FROM film";
            $query .= " WHERE title=$title";
            $query .= "   AND $selectYear";
            $query .= "   AND season=$season";
            $query .= "   AND $selectEpisodeNumber";
            $result = $db->query($query);
            if ($result->rowCount() == 1) {
                $row = $result->fetch();
                $filmId = $row["id"];
                $this->id = $filmId;
                $newRow = false;
            } else {
                $newRow = true;
            }
        }
        
        // Insert Film row
        if ($newRow) {
            $columns = "parent_id, title, year, contentType, seasonCount, season, episodeNumber, episodeTitle, image";
            $values = "$parentId, $title, $year, '$contentType', $seasonCount, $season, $episodeNumber, $episodeTitle, '$image'";
            $query = "INSERT INTO film ($columns) VALUES ($values)";
            logDebug($query, __CLASS__."::".__FUNCTION__." ".__LINE__);
            if ($db->query($query)) {
                $filmId = $db->lastInsertId();
                $this->id = $filmId;
                $refreshDateUpdate = "";
            }
        }
        
        // Sources
        foreach ($this->sources as $source) {
            $sourceName = $source->getName();
            if ($sourceName == Constants::SOURCE_RATINGSYNC) {
                if (empty($source->getUniqueName())) {
                    $source->setUniqueName("rs$filmId");
                }
            }
            $success = $source->saveFilmSourceToDb($filmId);
            if (!$success) {
                $errorFree = false;
            }

            // Rating
            if (!empty($username)) {
                $rating = $source->getRating();
                $success = $rating->saveToDb($username, $filmId);
                if (!$success) {
                    $errorFree = false;
                }
            }
        }
        
        // Directors
        foreach ($this->getDirectors() as $director) {
            $director = $db->quote($director);
            $personId = null;
            $result = $db->query("SELECT id FROM person WHERE fullname=$director");
            if ($result->rowCount() == 1) {
                $row = $result->fetch();
                $personId = $row["id"];
            } else {
                $columns = "fullname, lastname";
                $values = "$director, $director";
                $query = "INSERT INTO person ($columns) VALUES ($values)";
                logDebug($query, __CLASS__."::".__FUNCTION__." ".__LINE__);
                $success = $db->query($query) !== false;
                if (!$success) {
                    $errorFree = false;
                    logDebug("SQL Error (".$db->errorCode().") ".$db->errorInfo()[2], __CLASS__."::".__FUNCTION__.":".__LINE__);
                }
                $personId = $db->lastInsertId();
            }

            $columns = "person_id, film_id, position";
            $values = "$personId, $filmId, 'Director'";
            $query = "REPLACE INTO credit ($columns) VALUES ($values)";
            logDebug($query, __CLASS__."::".__FUNCTION__." ".__LINE__);
            $success = $db->query($query) !== false;
            if (!$success) {
                $errorFree = false;
                logDebug("SQL Error (".$db->errorCode().") ".$db->errorInfo()[2], __CLASS__."::".__FUNCTION__.":".__LINE__);
            }
        }
        
        // Genres
        foreach ($this->getGenres() as $genre) {
            $result = $db->query("SELECT 1 FROM genre WHERE name='$genre'");
            if ($result->rowCount() == 0) {
                $columns = "name";
                $values = "'$genre'";
                $query = "INSERT INTO genre ($columns) VALUES ($values)";
                logDebug($query, __CLASS__."::".__FUNCTION__." ".__LINE__);
                $success = $db->query($query) !== false;
                if (!$success) {
                    $errorFree = false;
                    logDebug("SQL Error (".$db->errorCode().") ".$db->errorInfo()[2], __CLASS__."::".__FUNCTION__.":".__LINE__);
                }
            }

            $columns = "film_id, genre_name";
            $values = "$filmId, '$genre'";
            $query = "REPLACE INTO film_genre ($columns) VALUES ($values)";
            logDebug($query, __CLASS__."::".__FUNCTION__." ".__LINE__);
            $success = $db->query($query) !== false;
            if (!$success) {
                $errorFree = false;
                logDebug("SQL Error (".$db->errorCode().") ".$db->errorInfo()[2], __CLASS__."::".__FUNCTION__.":".__LINE__);
            }
        }

        // Filmlists
        if (!empty($username)) {
            Filmlist::saveToDbUserFilmlistsByFilmObjectLists($username, $this);
        }
        
        // Make sure the RatingSync source has an image
        $sourceRs = $this->getSource(Constants::SOURCE_RATINGSYNC);
        $filmImage = $this->getImage();
        if (empty($sourceRs->getImage())) {
            if (empty($filmImage)) {
                // Download an image from another source
                $filmImage = $this->downloadImage();
                $this->setImage($filmImage);
            }
            $sourceRs->setImage($filmImage);
            $success = $sourceRs->saveFilmSourceToDb($filmId);
            if (!$success) {
                $errorFree = false;
            }
        } else {
            // RS Source has an image. Film overwrites it unless it's empty.
            if (empty($filmImage)) {
                // source overwrites the film's empty image
                $filmImage = $sourceRs->getImage();
                $this->setImage($filmImage);
            } else {
                // film overwrites the source's non-empty image
                $sourceRs->setImage($filmImage);
                $success = $sourceRs->saveFilmSourceToDb($filmId);
                if (!$success) {
                    $errorFree = false;
                }
            }
        }
        
        // Update Film row. If this is a new film then this update is
        // only for setting an image.
        $values = "parent_id=$parentId, title=$title, year=$year, contentType='$contentType', seasonCount=$seasonCount, season=$season, episodeNumber=$episodeNumber, episodeTitle=$episodeTitle, image='$filmImage' $refreshDateUpdate";
        $where = "id=$filmId";
        $query = "UPDATE film SET $values WHERE $where";
        logDebug($query, __CLASS__."::".__FUNCTION__." ".__LINE__);
        $success = $db->query($query) !== false;
        if (!$success) {
            $errorFree = false;
            logDebug("SQL Error (".$db->errorCode().") ".$db->errorInfo()[2], __CLASS__."::".__FUNCTION__.":".__LINE__);
        }

        return $errorFree;
    }

    public static function getFilmFromDb($filmId, $username = null)
    {
        if (empty($filmId) || !is_int(intval($filmId))) {
            throw new \InvalidArgumentException("filmId arg must be an int (filmId=$filmId)");
        }
        $filmId = intval($filmId);
        $db = getDatabase();
        
        $result = $db->query("SELECT * FROM film WHERE id=$filmId");
        if ($result->rowCount() != 1) {
            throw new \Exception('Film not found by Film ID: ' .$filmId);
        }
        $row = $result->fetch();

        return self::getFilmFromDbRow($row, $username);
    }

    public static function getFilmFromDbRow($row, $username = null)
    {
        $db = getDatabase();
        $filmId = $row["id"];

        $film = new Film();
        $film->setId($filmId);
        $film->setParentId($row["parent_id"]);
        $film->setTitle($row["title"]);
        $film->setYear($row["year"]);
        $film->setContentType($row["contentType"]);
        $film->setSeasonCount($row["seasonCount"]);
        $film->setSeason($row["season"]);
        $film->setEpisodeNumber($row["episodeNumber"]);
        $film->setEpisodeTitle($row["episodeTitle"]);
        $film->setImage($row["image"]);
        $refreshDateStr = $row['refreshDate'];
        if (!empty($refreshDateStr) && $refreshDateStr >= Constants::DATE_MIN_STR) {
            $refreshDate = new \DateTime($refreshDateStr);
            $film->setRefreshDate(new \DateTime($refreshDateStr));
        }

        // Sources
        $result = $db->query("SELECT * FROM film_source WHERE film_id=$filmId");
        foreach ($result->fetchAll() as $row) {
            $source = $film->getSource($row['source_name']);
            $source->setImage($row['image']);
            $source->setUniqueName($row['uniqueName']);
            $source->setParentUniqueName($row['parentUniqueName']);
            $source->setUniqueEpisode($row['uniqueEpisode']);
            $source->setUniqueAlt($row['uniqueAlt']);
            $source->setStreamUrl($row['streamUrl']);
            $streamDate = $row['streamDate'];
            if (!empty($streamDate) && $streamDate >= "1000-01-01") {
                $source->setStreamDate($streamDate);
            }
            $source->setCriticScore($row['criticScore']);
            $source->setUserScore($row['userScore']);

            // Rating
            if (!empty($username)) {
                $rating = Rating::getRatingFromDb($username, $source->getName(), $filmId);
                if (!empty($rating)) {
                    $source->setRating($rating);
                }

                // Rating archive (inactive ratings)
                $archive = Rating::getInactiveRatingsFromDb($username, $source->getName(), $filmId);
                $source->setArchive($archive);
            }
        }
        
        // Directors
        $query = "SELECT person.* FROM person, credit WHERE id=person_id" .
                 "   AND film_id=$filmId" .
                 "   AND position='Director'";
        $result = $db->query($query);
        foreach ($result->fetchAll() as $row) {
            $film->addDirector($row['fullname']);
        }
        
        // Genres
        $query = "SELECT * FROM film_genre WHERE film_id=$filmId ORDER BY genre_name ASC";
        $result = $db->query($query);
        foreach ($result->fetchAll() as $row) {
            $film->addGenre($row['genre_name']);
        }
        
        // Filmlists
        if (!empty($username)) {
            $query = "SELECT listname FROM filmlist WHERE user_name='$username' AND film_id=$filmId";
            $result = $db->query($query);
            foreach ($result->fetchAll() as $row) {
                $film->addFilmlist($row['listname']);
            }
        }
        
        return $film;
    }

    public static function getFilmFromDbByUniqueName($uniqueName, $sourceName, $username = null)
    {
        if (empty($uniqueName)) {
            throw new \InvalidArgumentException("uniqueName arg must not be empty");
        }
        $db = getDatabase();
        
        $query  = "SELECT film_id FROM film_source";
        $query .= " WHERE source_name='$sourceName'";
        $query .= "   AND uniqueName='$uniqueName'";
        $result = $db->query($query);
        if ($result->rowCount() != 1) {
            return null;
        }
        $row = $result->fetch();
        $filmId = $row["film_id"];

        return self::getFilmFromDb($filmId, $username);
    }

    public static function getFilmFromDbByEpisode($seriesFilmId, $seasonNum, $episodeNum, $username = null)
    {
        if (empty($seriesFilmId) || empty($seasonNum) || empty($episodeNum)) {
            throw new \InvalidArgumentException("seriesFilmId ($seriesFilmId), seasonNum ($seasonNum), and episodeNum ($episodeNum) must all be non-empty");
        }
        $db = getDatabase();
        
        $query  = "SELECT id FROM film";
        $query .= " WHERE parent_id=$seriesFilmId";
        $query .= "   AND season=$seasonNum";
        $query .= "   AND episodeNumber=$episodeNum";
        $result = $db->query($query);
        if ($result->rowCount() != 1) {
            return null;
        }
        $row = $result->fetch();
        $filmId = $row["id"];

        return self::getFilmFromDb($filmId, $username);
    }

    /**
     * Try to get a image (and save to the db) for films that have no valid image.
     */
    public static function reconnectFilmImages()
    {
        $db = getDatabase();
        $query = "SELECT id FROM film";
        $result = $db->query($query);

        foreach ($result->fetchAll() as $row) {
            $film = self::getFilmFromDb($row['id']);
            $http = new Http(Constants::SOURCE_RATINGSYNC);
            $isValid = $http->isPageValid($film->getImage());
            if (!$isValid) {
                $film->setImage(null);
                $film->downloadImage();
                $film->setImage($film->getImage(), Constants::SOURCE_RATINGSYNC);
                $film->saveToDb();
            }
        }
    }

    public function downloadImage()
    {
        $api = getMediaDbApiClient(Constants::DATA_API_DEFAULT);
        $image = $this->getImage($api->getSourceName());
        if (empty($image)) {
            try {
                $api->getFilmDetailFromApi($this, false, Constants::USE_CACHE_ALWAYS);
                $image = $this->getImage($api->getSourceName());
            } catch (\Exception $e) {
                // Do nothing, $image will be empty
            }
        }

        // Download the image
        if (!empty($image)) {
            $uniqueName = $this->getUniqueName(Constants::SOURCE_RATINGSYNC);
            $filename = "$uniqueName.jpg";
            $apiImagePath = $api->getImagePath(MediaDbApiClient::IMAGE_SIZE_LARGE);
            try {
                file_put_contents(Constants::imagePath() . $filename, file_get_contents($apiImagePath . $image));
            } catch (\Exception $e) {
                logDebug("Exception downloading an image for $filename.\n" . $e, __CLASS__."::".__FUNCTION__." ".__LINE__);
            }
            
            $this->setImage(Constants::RS_IMAGE_URL_PATH . $filename);
        }

        return $this->getImage();
    }

    /**
     * Find a film with matching search terms in the database. If the search
     * is for a TV episode return both the episode and the TV Series. Returned
     * as an array. Both items are Film objects with keys 'match' and 'parent'.
     *
     * @return array Both Key 'match' is the film searched for. Key 'parent' is the TV Series for the TV Episode match.
     */
    public static function searchDb($searchTerms, $username)
    {
        if (empty($searchTerms) || !is_array($searchTerms)) {
            return null;
        }
        
        $db = getDatabase();
        $uniqueName = array_value_by_key("uniqueName", $searchTerms);
        //$uniqueEpisode = array_value_by_key("uniqueEpisode", $searchTerms);
        //$uniqueAlt = array_value_by_key("uniqueAlt", $searchTerms);
        $title = array_value_by_key("title", $searchTerms);
        $titleEscapedAndQuoted = $db->quote($title);
        $year = array_value_by_key("year", $searchTerms);
        $parentYear = array_value_by_key("parentYear", $searchTerms);
        $contentType = array_value_by_key("contentType", $searchTerms);
        $season = array_value_by_key("season", $searchTerms);
        $seasonEscapedAndQuoted = $db->quote($season);
        $seasonEscaped = unquote($seasonEscapedAndQuoted);
        $episodeNumber = array_value_by_key("episodeNumber", $searchTerms);
        $episodeTitle = array_value_by_key("episodeTitle", $searchTerms);
        $episodeTitleEscapedAndQuoted = $db->quote($episodeTitle);
        $episodeTitleEscaped = unquote($episodeTitleEscapedAndQuoted);
        $sourceName = array_value_by_key("sourceName", $searchTerms);
        
        $selectTitle = "title=$titleEscapedAndQuoted";
        $selectYear = "year=$year";
        if (is_null($year)) $selectYear = "year IS NULL";
        if (!is_null($parentYear)) $selectYear = "($selectYear OR year=$parentYear)";
        $selectSeason = "season=$seasonEscapedAndQuoted";
        if (empty($season)) $selectSeason = "(season='' OR season IS NULL)";
        $selectEpisodeNumber = "episodeNumber=$episodeNumber";
        if (is_null($episodeNumber)) $selectEpisodeNumber = "episodeNumber IS NULL";
        //$selectUniqueEpisode = "uniqueEpisode='$uniqueEpisode'";
        //if (empty($uniqueEpisode)) $selectUniqueEpisode = "(uniqueEpisode='' OR uniqueEpisode IS NULL)";
        $selectEpisodeTitle = "episodeTitle=$episodeTitleEscapedAndQuoted";
        if (empty($episodeTitle)) $selectEpisodeTitle = "(episodeTitle='' OR episodeTitle IS NULL)";
        $selectSourceName = "";
        if (!empty($sourceName)) $selectSourceName = " AND source_name='$sourceName'";

        $film = null;
        $filmParent = null;
        $filmParentTried = false;
        $query = null;

        $uniqueNameIsUnique = true;
        if (empty($uniqueName) || Source::doesSourceReuseUniqueNames($sourceName, $contentType)) {
            $uniqueNameIsUnique = false;
        }

        // Use uniqueName as only criteria if it is truly unique
        if ($uniqueNameIsUnique) {
            $query  = "SELECT film_id as id FROM film_source WHERE uniqueName='$uniqueName' $selectSourceName";
        }

        // Use uniqueName and season/episode
        if (empty($query) && !empty($uniqueName)) {
            $query  = "SELECT id FROM film f, film_source fs";
            $query .= " WHERE id=film_id";
            $query .= "   AND uniqueName='$uniqueName'";
            $query .=  "  AND (($selectSeason AND $selectEpisodeNumber) OR $selectEpisodeTitle)";
            $query .=     $selectSourceName;
        }

        // Get existing film from a source
        if (!empty($query)) {
            $result = $db->query($query);
            if ($result->rowCount() == 1) {
                $row = $result->fetch();
                $film = self::getFilmFromDb($row['id'], $username);
            }
        }

        // Use Title/Year. Also using season/episode if there is a value.
        if (empty($film) && !empty($title) && !empty($year)) {
            $query  = "SELECT id FROM film";
            $query .= " WHERE $selectTitle AND $selectYear";
            $query .=  "  AND (($selectSeason AND $selectEpisodeNumber) OR $selectEpisodeTitle)";
            
            $result = $db->query($query);
            if ($result->rowCount() == 1) {
                $row = $result->fetch();
                $film = self::getFilmFromDb($row['id'], $username);
            }
        }

        if (!empty($film)) {
            $filmId = $film->getId();

            // Save Film/Source changes
            if (Source::validSource($sourceName)) {
                $source = $film->getSource($sourceName);
                if ($uniqueName != $source->getUniqueName()) {
                    // This source is not in the database yet. Save it now.
                    $source->setUniqueName($uniqueName);
                    //$source->setUniqueEpisode($uniqueEpisode);
                    //$source->setUniqueAlt($uniqueAlt);
                    $source->saveFilmSourceToDb($filmId);
                    $film = self::getFilmFromDb($filmId, $username);
                }
            }
            
            $filmParent = self::getFilmParentFromDb($film, $username);

            // Save Film season/episode changes
            $originalSeason = $film->getSeason();
            $originalEpisodeNumber = $film->getEpisodeNumber();
            $originalEpisodeTitle = $film->getEpisodeTitle();
            $needToSaveFilm = false;
            if (empty($originalSeason) && !empty($season)) {
                $film->setSeason($seasonEscaped);
                $needToSaveFilm = true;
            }
            if (empty($originalEpisodeNumber) && !empty($episodeNumber)) {
                $film->setEpisodeNumber($episodeNumber);
                $needToSaveFilm = true;
            }
            if (empty($originalEpisodeTitle) && !empty($episodeTitle)) {
                $film->setEpisodeTitle($episodeTitleEscaped);
                $needToSaveFilm = true;
            }
            if (in_array($film->getContentType(), array(self::CONTENT_TV_SEASON, self::CONTENT_TV_EPISODE))) {
                $parentId = $film->getParentId();
                if (empty($parentId) && !empty($filmParent)) {
                    $parentId = $filmParent->getId();
                    $film->setParentId($parentId);
                    $needToSaveFilm = true;
                }
            }
            if ($needToSaveFilm) {
                $film->saveToDb($username);
            }
        }
        
        return array("match"=>$film, "parent"=>$filmParent);
    }
    
    public static function getFilmParentFromDb($film, $username = null)
    {
        if (empty($film) && !in_array($film->getContentType(), array(self::CONTENT_TV_SEASON, self::CONTENT_TV_EPISODE))) {
            return null;
        }
        
        $db = getDatabase();
        $parentFilm = null;
        $query = "SELECT id FROM film";

        $parentId = $film->getParentId();
        if (empty($parentId)) {
            $query .= " WHERE contentType='". self::CONTENT_TV_SERIES ."'";
            $query .= "   AND title=". $db->quote($film->getTitle());
            $query .= "   AND year=". $film->getYear();
        } else {
            $query .= " WHERE id=$parentId";
        }

        $result = $db->query($query);
        if ($result->rowCount() == 1) {
            $row = $result->fetch();
            $parentFilm = self::getFilmFromDb($row['id'], $username);
        }

        return $parentFilm;
    }

    /**
     * Update streams for all films for all providers
     */
    public static function refreshAllStreamsForAllFilms()
    {
        $db = getDatabase();
        $query = "SELECT id FROM film";
        $result = $db->query($query);

        foreach ($result->fetchAll() as $row) {
            Source::createAllSourcesToDb($row['id']);
        }
    }

    public function refresh()
    {
        $refreshed = false;
        $needToRefresh = false;
        $currentImageValid = $this->validateImage();
        $currentSeasonCount = $this->getSeasonCount();
        $currentRefreshDate = $this->getRefreshDate();
        $now = new \DateTime();
        $staleDay = $now->sub(new \DateInterval('P30D')); // 30 days ago
        $now = new \DateTime();
    
        // Refresh now if it has been too long or if the image is not valid
        if (empty($currentRefreshDate) || $currentRefreshDate <= $staleDay || !($currentImageValid)) {
            $needToRefresh = true;
        }

        // Refresh a TV series does not have seasonCount set
        if ($this->getContentType() == self::CONTENT_TV_SERIES && empty($currentSeasonCount)) {
            $needToRefresh = true;
        }
        
        if ($needToRefresh) {
            $api = getMediaDbApiClient(Constants::DATA_API_DEFAULT);
            $api->getFilmDetailFromApi($this, true, 60);

            // Any changes?
            if (!$currentImageValid) {
                // Validate the new image before using it
                $http = new Http(Constants::SOURCE_RATINGSYNC);
                if (!empty($refreshedImage) && $http->isPageValid($refreshedImage)) {
                    $this->setImage($refreshedImage, $api->getSourceName());
                    // Download the image
                    if (!empty($refreshedImage)) {
                        $uniqueName = $this->getUniqueName(Constants::SOURCE_RATINGSYNC);
                        $filename = "$uniqueName.jpg";
                        try {
                            file_put_contents(Constants::imagePath() . $filename, file_get_contents($refreshedImage));
                        } catch (\Exception $e) {
                            logDebug("Exception downloading an image for $filename.\n" . $e, __CLASS__."::".__FUNCTION__." ".__LINE__);
                        }
                        
                        $this->setImage(Constants::RS_IMAGE_URL_PATH . $filename);
                        $this->setImage($this->setImage(), Constants::SOURCE_RATINGSYNC);
                    }
                }
            }
    
            $this->setRefreshDate($now);
            $refreshed = true;
        }

        return $refreshed;
    }

    public function validateImage()
    {
        $isValid = false;
        $image = $this->getImage();
    
        $http = new Http(Constants::SOURCE_RATINGSYNC);
        if (!empty($image) && $http->isPageValid($image)) {
            $isValid = true;
        }
        
        return $isValid;
    }
}