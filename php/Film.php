<?php
/**
 * Film Class
 */
namespace RatingSync;

require_once "Http.php";
require_once "main.php";

class Film {
    const CONTENT_FILM      = 'FeatureFilm';
    const CONTENT_TV        = 'TvSeries';
    const CONTENT_SHORTFILM = 'ShortFilm';

    /**
     * @var http
     */
    protected $http;
    
    protected $id;
    protected $title;
    protected $year;
    protected $contentType;
    protected $image;
    protected $sources = array();
    protected $genres = array();
    protected $directors = array();

    public function __construct(Http $http)
    {
        if (! ($http instanceof Http) ) {
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
     * <film title="">
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
           <source name="">
               <image/>
               <filmName/>
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
        if (! $xml instanceof \SimpleXMLElement ) {
            throw new \InvalidArgumentException('Function addXmlChild must be given a SimpleXMLElement');
        }

        $filmXml = $xml->addChild("film");
        $filmXml->addAttribute('title', $this->getTitle());
        $filmXml->addChild('title', htmlspecialchars($this->getTitle()));
        $filmXml->addChild('year', $this->getYear());
        $filmXml->addChild('contentType', $this->getContentType());
        $filmXml->addChild('image', $this->getImage());

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
            $sourceXml->addChild('filmName', $source->getFilmName());
            $sourceXml->addChild('urlName', $source->getUrlName());
            $rating = $source->getRating();
            $ratingXml = $sourceXml->addChild('rating');
            $ratingXml->addChild('yourScore', $rating->getYourScore());
            $ratingDate = null;
            if (!is_null($rating->getYourRatingDate())) {
                $ratingDate = $rating->getYourRatingDate()->format("Y-n-j");
            }
            $ratingXml->addChild('yourRatingDate', $ratingDate);
            $ratingXml->addChild('suggestedScore', $rating->getSuggestedScore());
            $ratingXml->addChild('criticScore', $rating->getCriticScore());
            $ratingXml->addChild('userScore', $rating->getUserScore());
        }
    }

    /**
     * New Film object with data from XML
     *
     * @param \SimpleXMLElement $filmSxe Film data
     * @param \RatingSync\Http  $http    -
     *
     * @return a new Film
     */
    public static function createFromXml($filmSxe, $http)
    {
        if (! ($filmSxe instanceof \SimpleXMLElement && $http instanceof Http) ) {
            throw new \InvalidArgumentException('Function createFromXml must be given a SimpleXMLElement and an Http');
        } elseif (empty(Self::xmlStringByKey('title', $filmSxe))) {
            throw new \Exception('Function createFromXml: xml must have a title');
        }

        $film = new Film($http);
        $film->setTitle(html_entity_decode(Self::xmlStringByKey('title', $filmSxe), ENT_QUOTES, "utf-8"));
        $film->setYear(Self::xmlStringByKey('year', $filmSxe));
        $film->setContentType(Self::xmlStringByKey('contentType', $filmSxe));
        $film->setImage(Self::xmlStringByKey('image', $filmSxe));

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
            $source->setFilmName(Self::xmlStringByKey('filmName', $sourceSxe));
            $source->setUrlName(Self::xmlStringByKey('urlName', $sourceSxe));

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
            $criticScore = Self::xmlStringByKey('criticScore', $ratingSxe);
            if (Rating::validRatingScore($criticScore)) {
                $rating->setCriticScore($criticScore);
            }
            $userScore = Self::xmlStringByKey('userScore', $ratingSxe);
            if (Rating::validRatingScore($userScore)) {
                $rating->setUserScore($userScore);
            }

            $source->setRating($rating);
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

    protected function getSource($sourceName)
    {
        if (! Source::validSource($sourceName) ) {
            throw new \InvalidArgumentException('Getting Source $source invalid');
        }
        
        if (empty($this->sources[$sourceName])) {
            $this->sources[$sourceName] = new Source($sourceName);
        }

        return $this->sources[$sourceName];
    }

    public function setFilmName($FilmName, $source)
    {
        if (! Source::validSource($source) ) {
            throw new \InvalidArgumentException('Source $source invalid setting Film ID');
        }

        $this->getSource($source)->setFilmName($FilmName);
    }

    public function getFilmName($source)
    {
        if (! Source::validSource($source) ) {
            throw new \InvalidArgumentException('Source $source invalid getting Film ID');
        }

        return $this->getSource($source)->getFilmName();
    }

    public function setUrlName($urlName, $source)
    {
        if (! Source::validSource($source) ) {
            throw new \InvalidArgumentException('Source $source invalid setting URL name');
        }

        $this->getSource($source)->setUrlName($urlName);
    }

    public function getUrlName($source)
    {
        if (! Source::validSource($source) ) {
            throw new \InvalidArgumentException('Source $source invalid getting URL name');
        }

        return $this->getSource($source)->getUrlName();
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

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
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

    public function saveToDb($username = null)
    {
        $db = getDatabase();

        $filmId = $this->id;
        $title = $db->real_escape_string($this->getTitle());
        $year = $this->getYear();
        if (empty($year)) $year = "NULL";
        $contentType = $this->getContentType();
        $image = $this->getImage();

        // Look for an existing film row
        $newRow = false;
        if (empty($filmId)) {
            $result = $db->query("SELECT id FROM film WHERE title='$title' AND year=$year");
            if ($result->num_rows == 1) {
                $row = $result->fetch_assoc();
                $filmId = $row["id"];
                $this->id = $filmId;
                $newRow = false;
            } else {
                $newRow = true;
            }
        }
        
        // Insert or Update Film row
        if ($newRow) {
            $columns = "title, year, contentType, image";
            $values = "'$title', $year, '$contentType', '$image'";
            if ($db->query("INSERT INTO film ($columns) VALUES ($values)")) {
                $filmId = $db->insert_id;
                $this->id = $filmId;
            }
        } else {
            $values = "title='$title', year=$year, contentType='$contentType', image='$image'";
            $where = "id=$filmId";
            $db->query("UPDATE film SET $values WHERE $where");
        }
        
        // Sources
        foreach ($this->sources as $source) {
            $sourceName = $source->getName();
            $sourceImage = $source->getImage();
            $sourceUrlName = $source->getUrlName();
            $sourceFilmName = $source->getFilmName();
            
            $columns = "film_id, source_name, image, urlName, filmName";
            $values = "$filmId, '$sourceName', '$sourceImage', '$sourceUrlName', '$sourceFilmName'";
            $db->query("REPLACE INTO film_source ($columns) VALUES ($values)");

            // Rating
            $rating = $source->getRating();
            $yourScore = $rating->getYourScore();
            $ratingDate = null;
            if (!is_null($rating->getYourRatingDate())) {
                $ratingDate = $rating->getYourRatingDate()->format("Y-m-d");
            }
            $suggestedScore = $rating->getSuggestedScore();
            $criticScore = $rating->getCriticScore();
            $userScore = $rating->getUserScore();
            
            $columns = "user_name, source_name, film_id";
            $values = "'testratingsync', '$sourceName', $filmId";
            if (!empty($yourScore)) {
                $columns .= ", yourScore";
                $values .= ", $yourScore";
            }
            if (!empty($suggestedScore)) {
                $columns .= ", suggestedScore";
                $values .= ", $suggestedScore";
            }
            if (!empty($criticScore)) {
                $columns .= ", criticScore";
                $values .= ", $criticScore";
            }
            if (!empty($userScore)) {
                $columns .= ", userScore";
                $values .= ", $userScore";
            }
            if (!empty($ratingDate)) {
                $columns .= ", yourRatingDate";
                $values .= ", '$ratingDate'";
            }

            if (!empty($username)) {
                $result = $db->query("SELECT 1 FROM user WHERE username='$username'");
                if ($result->num_rows == 1) {
                    $db->query("REPLACE INTO rating ($columns) VALUES ($values)");
                }
            }
        }

        // Directors
        foreach ($this->getDirectors() as $director) {
            $director = $db->real_escape_string($director);
            $personId;
            $result = $db->query("SELECT id FROM person WHERE fullname='$director'");
            if ($result->num_rows == 1) {
                $row = $result->fetch_assoc();
                $personId = $row["id"];
            } else {
                $columns = "fullname, lastname";
                $values = "'$director', '$director'";
                $db->query("INSERT INTO person ($columns) VALUES ($values)");
                $personId = $db->insert_id;
            }

            $columns = "person_id, film_id, position";
            $values = "$personId, $filmId, '$director'";
            $db->query("REPLACE INTO credit ($columns) VALUES ($values)");
        }

        // Genres
        foreach ($this->getGenres() as $genre) {
            $result = $db->query("SELECT 1 FROM genre WHERE name='$genre'");
            if ($result->num_rows == 0) {
                $columns = "name";
                $values = "'$genre'";
                $db->query("INSERT INTO genre ($columns) VALUES ($values)");
            }

            $columns = "film_id, genre_name";
            $values = "$filmId, '$genre'";
            $db->query("REPLACE INTO film_genre ($columns) VALUES ($values)");
        }

        $db->commit();
    }

    /**
     * Send a rating to a website.
     * NOTE: The website must be logged in already
     *
     * @param string $source Rating website
     */
    public function rateToWebsite($source)
    {
        if (! Source::validSource($sourceName) ) {
            throw new \InvalidArgumentException('Source $source invalid');
        }
    }
}