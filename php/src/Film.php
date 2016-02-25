<?php
/**
 * Film Class
 */
namespace RatingSync;

require_once "Http.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "main.php";

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
            throw new \InvalidArgumentException('Film contruct must have an Http object');
        }

        $this->http = $http;
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
               <uniqueName/>
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
            $sourceXml->addChild('uniqueName', $source->getUniqueName());
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
            $source->setUniqueName(Self::xmlStringByKey('uniqueName', $sourceSxe));

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

        return $this->getSource($source)->getUniqueName();
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
            $result = $db->query("SELECT id FROM film WHERE title='$title' AND (year=$year OR year IS NULL)");
            if ($result->num_rows == 1) {
                $row = $result->fetch_assoc();
                $filmId = $row["id"];
                $this->id = $filmId;
                $newRow = false;
            } else {
                $newRow = true;
            }
        }
        
        // Insert Film row
        if ($newRow) {
            $columns = "title, year, contentType, image";
            $values = "'$title', $year, '$contentType', '$image'";
            if ($db->query("INSERT INTO film ($columns) VALUES ($values)")) {
                $filmId = $db->insert_id;
                $this->id = $filmId;
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
            $source->saveFilmSourceToDb($filmId);

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
            $values = "'$username', '$sourceName', $filmId";
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
            $values = "$personId, $filmId, 'Director'";
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

        // Make sure the RatingSync source has an image
        $sourceRs = $this->getSource(Constants::SOURCE_RATINGSYNC);
        $filmImage = $this->getImage();
        if (empty($sourceRs->getImage())) {
            if (empty($filmImage)) {
                // Download an image from another source
                $filmImage = $this->downloadImage();
            }
            $sourceRs->setImage($filmImage);
            $sourceRs->saveFilmSourceToDb($filmId);
        } else {
            // RS Source has an image. Film overwrites it unless it's empty.
            if (empty($filmImage)) {
                // source overwrites the film's empty image
                $filmImage = $sourceRs->getImage();
                $this->setImage($filmImage);
            } else {
                // film overwrites the source's non-empty image
                $sourceRs->setImage($filmImage);
                $sourceRs->saveFilmSourceToDb($filmId);
            }
        }
        
        // Update Film row. If this is a new film then this update is
        // only for setting an image.
        $values = "title='$title', year=$year, contentType='$contentType', image='$filmImage'";
        $where = "id=$filmId";
        $db->query("UPDATE film SET $values WHERE $where");

        $db->commit();
    }

    public static function getFilmFromDb($filmId, $http, $username = null)
    {
        if (empty($filmId) || !is_int(intval($filmId))) {
            throw new \InvalidArgumentException("filmId arg must be an int (filmId=$filmId)");
        } elseif (! ($http instanceof Http) ) {
            throw new \InvalidArgumentException('Film contruct must have an Http object');
        }
        $filmId = intval($filmId);
        $db = getDatabase();
        
        $result = $db->query("SELECT * FROM film WHERE id=$filmId");
        if ($result->num_rows != 1) {
            throw new \Exception('Film not found by Film ID: ' .$filmId);
        }
        $film = new Film($http);
        $film->setId($filmId);
        
        $row = $result->fetch_assoc();
        $film->setTitle($row["title"]);
        $film->setYear($row["year"]);
        $film->setContentType($row["contentType"]);
        $film->setImage($row["image"]);

        // Sources
        $result = $db->query("SELECT * FROM film_source WHERE film_id=$filmId");
        while ($row = $result->fetch_assoc()) {
            $source = $film->getSource($row['source_name']);
            $source->setImage($row['image']);
            $source->setUniqueName($row['uniqueName']);

            // Rating
            if (!empty($username)) {
                $query = "SELECT * FROM rating WHERE film_id=$filmId AND source_name='".$source->getName()."' AND user_name='".$username."'";
                $ratingResult = $db->query($query);
                if ($ratingResult->num_rows == 1) {
                    $row = $ratingResult->fetch_assoc();
                    $rating = new Rating($source->getName());
                    $rating->initFromDbRow($row);
                    $source->setRating($rating);
                }
            }
        }
        
        // Directors
        $query = "SELECT person.* FROM person, credit WHERE id=person_id" .
                 "   AND film_id=$filmId" .
                 "   AND position='Director'";
        $result = $db->query($query);
        while ($row = $result->fetch_assoc()) {
            $film->addDirector($row['fullname']);
        }
        
        // Genres
        $query = "SELECT * FROM film_genre WHERE film_id=$filmId ORDER BY genre_name ASC";
        $result = $db->query($query);
        while ($row = $result->fetch_assoc()) {
            $film->addGenre($row['genre_name']);
        }

        return $film;
    }

    /**
     * Try to get a image (and save to the db) for films that have no valid image.
     */
    public static function reconnectFilmImages()
    {
        $db = getDatabase();
        $query = "SELECT id FROM film";
        $result = $db->query($query);

        $http = new HttpRatingSync("empty_username");
        while ($row = $result->fetch_assoc()) {
            $film = self::getFilmFromDb($row['id'], $http);
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
        $imdb = new Imdb("empty_userame");
        try {
            $imdb->getFilmDetailFromWebsite($this, false, Constants::USE_CACHE_ALWAYS);
            $image = $this->getImage(Constants::SOURCE_IMDB);
        } catch (\Exception $e) {
            // Do nothing, $image will be empty
        }

        // Download the image
        if (!empty($image)) {
            $uniqueName = $this->getUniqueName(Constants::SOURCE_RATINGSYNC);
            $filename = "$uniqueName.jpg";
            file_put_contents(Constants::imagePath() . $filename, file_get_contents($image));
            
            $this->setImage(Constants::RS_IMAGE_URL_PATH . $filename);
        }

        return $this->getImage();
    }

    public static function searchDb($searchQuery, $username)
    {
        $film = null;
        $db = getDatabase();
        $result = $db->query("SELECT film_id FROM film_source WHERE uniqueName='$searchQuery'");
        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            $film = self::getFilmFromDb($row['film_id'], new HttpRatingSync($username), $username);
        }

        return $film;
    }
}