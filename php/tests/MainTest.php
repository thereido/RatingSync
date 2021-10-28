<?php
/**
 * main.php PHPUnit
 */
namespace RatingSync;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "Constants.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "main.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "Film.php";

require_once "SiteChild.php";
require_once "ImdbTest.php";
require_once "RatingSyncSiteTest.php";
require_once "DatabaseTest.php";
require_once "RatingSyncTestCase.php";
require_once "OmdbApiTest.php";
require_once "TmdbApiTest.php";

class MainTest extends RatingSyncTestCase
{
    const RATING_USER = "rs_user1";

    protected function setUp(): void
    {
        parent::setup();
        //$this->verbose = true;
    }

    public static function getConstants()
    {
        $constants = array();
        if (Constants::DATA_API_DEFAULT == Constants::SOURCE_OMDBAPI) {
            $constants = OmdbApiTest::getConstants();
        } elseif (Constants::DATA_API_DEFAULT == Constants::SOURCE_TMDBAPI) {
            $constants = TmdbApiTest::getConstants();
        }

        return $constants;
    }
    
    public static function setupRatings()
    {
        $db = getDatabase();
        $success = true;

        // Reset DB
        DatabaseTest::resetDb();

        // Import films for test data
        $username_rs = Constants::TEST_RATINGSYNC_USERNAME;
        $site = new SiteRatingsChild(TEST_IMDB_USERNAME);
        $filename =  __DIR__ . DIRECTORY_SEPARATOR . "testfile" . DIRECTORY_SEPARATOR . "input_ratings_site.xml";
        $films = $site->importRatings(Constants::IMPORT_FORMAT_XML, $filename, $username_rs);

        // Insert a RS user to the DB
        $username_rs = "rs_user1";
        $query = "INSERT INTO user (username, password) VALUES ('$username_rs', 'password')";
        if (! $db->query($query) ) {
            echo $query."  SQL Error: ".$db->error;
            $success = false;
        }
        $query = "INSERT INTO user_source (user_name, source_name, username, password) VALUES ('$username_rs', '".Constants::SOURCE_IMDB."', 'imdb_user1', 'pwd')";
        if (! $db->query($query) ) {
            echo $query."  SQL Error: ".$db->error;
            $success = false;
        }
        $query = "INSERT INTO user_source (user_name, source_name, username, password) VALUES ('$username_rs', '".Constants::SOURCE_JINNI."', 'jinni_user1', 'pwd')";
        if (! $db->query($query) ) {
            echo $query."  SQL Error: ".$db->error;
            $success = false;
        }
        $query = "INSERT INTO user_source (user_name, source_name, username, password) VALUES ('$username_rs', '".Constants::SOURCE_RATINGSYNC."', '$username_rs', 'password')";
        if (! $db->query($query) ) {
            echo $query."  SQL Error: ".$db->error;
            $success = false;
        }
        
        // Save ratings for 3 films for the new user
        $filmId = 1; $filmId2 = 2; $filmId4 = 4;
        $result = $db->query("SELECT * FROM rating WHERE film_id=$filmId AND user_name='".Constants::TEST_RATINGSYNC_USERNAME."' AND source_name='".Constants::SOURCE_IMDB."'");
        $rating = new Rating(Constants::SOURCE_IMDB);
        $rating->initFromDbRow($result->fetch_assoc());
        $rating->saveToRs($username_rs, $filmId);
        $rating->saveToRs($username_rs, $filmId2);
        $rating->setYourRatingDate(new \DateTime());
        $rating->saveToRs($username_rs, $filmId4);

        $query = "UPDATE rating SET source_name='".Constants::SOURCE_IMDB."' WHERE film_id=$filmId4 AND user_name='$username_rs'";
        if (! $db->query($query) ) {
            echo $query."  SQL Error: ".$db->error;
            $success = false;
        }

        $filmId = 1;
        $query = "UPDATE rating SET yourRatingDate='2015-1-1' WHERE film_id=$filmId AND user_name='".Constants::TEST_RATINGSYNC_USERNAME."' AND source_name='".Constants::SOURCE_RATINGSYNC."'";
        if (! $db->query($query) ) {
            echo $query."  SQL Error: ".$db->error;
            $success = false;
        }

        $filmId = 3;
        $query = "UPDATE rating SET source_name='".Constants::SOURCE_IMDB."' WHERE film_id=$filmId AND user_name='".Constants::TEST_RATINGSYNC_USERNAME."'";
        if (! $db->query($query) ) {
            echo $query."  SQL Error: ".$db->error;
            $success = false;
        }

        return $success;
    }
    
    public function testSetup()
    {$this->start(__CLASS__, __FUNCTION__);
    
        $this->assertTrue(self::setupRatings(), "setupRatings() failed");
    }
    
    /**
     * @covers \RatingSync\search
     * @depends testSetup
     */
    public function testSearchDbExists()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $db = getDatabase();
        $constants = $this->getConstants();
        $sourceName = $constants["sourceName"];
        $uniqueName = $constants["filmUniqueName"];

        // Test
        $searchTerms = array("uniqueName" => $uniqueName, "sourceName" => $sourceName);
        $film = search($searchTerms, Constants::TEST_RATINGSYNC_USERNAME)['match'];
        $filmId = $film->getId();

        // Verify database - film
        $title = $constants["filmTitle"];
        $year = $constants["filmYear"];
        $directors = $constants["filmDirectors"];
        $genres = $constants["filmGenres"];
        $film = Film::GetFilmFromDb($filmId, Constants::TEST_RATINGSYNC_USERNAME);
        $filmImage = "http://example.com/frozen_film_image.jpeg";
        $dbDirectors = $film->getDirectors(); sort($dbDirectors);
        $dbGenres = $film->getGenres(); sort($dbGenres);
        $this->assertEquals($title, $film->getTitle(), "Title");
        $this->assertEquals($year, $film->getYear(), "Year");
        $this->assertEquals(Film::CONTENT_FILM, $film->getContentType(), 'Content Type');
        $this->assertEquals($filmImage, $film->getImage(), 'Image link (film)');
        $this->assertEquals($directors, $dbDirectors, 'Director(s)');
        $this->assertEquals($genres, $dbGenres, 'Genres');

        // Verify database - IMDb
        $this->assertEquals("tt2294629", $film->getUniqueName(Constants::SOURCE_IMDB), 'UniqueName from source');
        $this->assertEquals('https://m.media-amazon.com/images/M/MV5BMTQ1MjQwMTE5OF5BMl5BanBnXkFtZTgwNjk3MTcyMDE@._V1_SX300.jpg', $film->getImage(Constants::SOURCE_IMDB), 'Image link (IMDb)');
        $this->assertEquals(7, round($film->getCriticScore(Constants::SOURCE_IMDB)), 'Critic score');
        $this->assertEquals(8, round($film->getUserScore(Constants::SOURCE_IMDB)), 'User score');
        $rating = $film->getRating(Constants::SOURCE_IMDB);
        $this->assertEquals(2, $rating->getYourScore(), 'Your Score not available from searchImdb');
        $this->assertEmpty($rating->getYourRatingDate(), 'Rating date not available from searchImdb');
        $this->assertEmpty($rating->getSuggestedScore(), 'Suggested score not available from searchImdb');

        // Verify database - RS
        $this->assertEquals("rs$filmId", $film->getUniqueName(Constants::SOURCE_RATINGSYNC), "RS uniqueName");
        $this->assertEquals($filmImage, $film->getImage(Constants::SOURCE_RATINGSYNC), "RS image");
        $this->assertEquals(4, round($film->getCriticScore(Constants::SOURCE_RATINGSYNC)), 'Critic score');
        $this->assertEquals(5, round($film->getUserScore(Constants::SOURCE_RATINGSYNC)), 'User score');
        $rating = $film->getRating(Constants::SOURCE_RATINGSYNC);
        $this->assertEquals(2, $rating->getYourScore(), 'Your Score');
        $this->assertEquals("2015-01-01", date_format($rating->getYourRatingDate(), "Y-m-d"), "YourRatingDate");
        $this->assertEquals(3, $rating->getSuggestedScore(), 'Suggested score');
    }
    
    /**
     * - Do not include searchQuery or username
     *
     * Expect
     *   - return null
     *
     * @covers \RatingSync\search
     */
    public function testSearchEmptyArgs()
    {$this->start(__CLASS__, __FUNCTION__);

        $emptySearchResults = search(null, null)['match'];
        $this->assertEmpty($emptySearchResults, "Empty args should return nothing");
    }
    
    /**
     * - searchQuery = ''
     * - username = valid test user
     *
     * Expect
     *   - return null
     *
     * @covers \RatingSync\search
     */
    public function testSearchEmptyQuery()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = search("", Constants::TEST_RATINGSYNC_USERNAME)['match'];
        $this->assertEmpty($film, "Empty query should return nothing");
    }

    public function testResetDb()
    {$this->start(__CLASS__, __FUNCTION__);

        DatabaseTest::resetDb();

        $this->assertTrue(true); // Making sure we made it this far
    }
    
    /**
     * - Film does not exist in the db
     * - searchQuery = valid IMDb uniqueName
     * - username = null
     *
     * Expect
     *   - same as testSearchImdb
     *
     * @covers \RatingSync\search
     * @depends testResetDb
     */
    public function testSearch()
    {$this->start(__CLASS__, __FUNCTION__);

        // Set up
        $constants = $this->getConstants();
        $sourceName = $constants["sourceName"];

        // Movie
                // Setup
        $contentType = Film::CONTENT_FILM;
        $uniqueName = $constants["filmUniqueName"];
        $searchTerms = array("uniqueName" => $uniqueName);
        $searchTerms["sourceName"] = $sourceName;
        $searchTerms["contentType"] = $contentType;

                // Test
        $film = search($searchTerms, Constants::TEST_RATINGSYNC_USERNAME)['match']; // Frozen (2013)
        $filmId = $film->getId();

                // Verify
                    // film object
        $title = $constants["filmTitle"];
        $year = $constants["filmYear"];
        $sourceImage = $constants["filmImage"];
        $userScore = $constants["filmUserScore"];
        $criticScore = $constants["filmCriticScore"];
        $directors = $constants["filmDirectors"];
        $genres = $constants["filmGenres"];
        $filmImage = "/image/rs$filmId.jpg";
        $directorsFromSearch = $film->getDirectors(); sort($directorsFromSearch);
        $genresFromSearch = $film->getGenres(); sort($genresFromSearch);
        $this->assertEquals($title, $film->getTitle(), "Title");
        $this->assertEquals($year, $film->getYear(), "Year");
        $this->assertEquals($contentType, $film->getContentType(), 'Content Type');
        $this->assertEquals($filmImage, $film->getImage(), 'Image link (film)');
        $this->assertEquals(1, preg_match("@($sourceImage)@", $film->getImage($sourceName), $matches), "Image link ($sourceName) should be '" . $sourceImage . "', not '" . $film->getImage($sourceName) . "'");
        $this->assertEquals(round($criticScore), round($film->getCriticScore($sourceName)), 'Critic score');
        $this->assertEquals(round($userScore), round($film->getUserScore($sourceName)), 'User score');
        $this->assertEquals($directors, $directorsFromSearch, 'Director(s)');
        $this->assertEquals($genres, array_intersect($genres, $genresFromSearch), 'Genres');
        $rating = $film->getRating($sourceName);
        $this->assertEquals($uniqueName, $film->getUniqueName($sourceName), 'UniqueName from source');
        $this->assertNull($rating->getYourScore(), 'Your Score');
        $this->assertNull($rating->getYourRatingDate(), 'Rating date not available from film detail page');
        $this->assertNull($rating->getSuggestedScore(), 'Suggested score not available is you are rated the film');
                    // Verify the db film the same way as the object before
        $film = null;
        $film = Film::getFilmFromDb($filmId, Constants::TEST_RATINGSYNC_USERNAME);
        $dbDirectors = $film->getDirectors(); sort($dbDirectors);
        $dbGenres = $film->getGenres(); sort($dbGenres);
        $this->assertEquals($title, $film->getTitle(), "Title");
        $this->assertEquals($year, $film->getYear(), "Year");
        $this->assertEquals($contentType, $film->getContentType(), 'Content Type');
        $this->assertEquals($filmImage, $film->getImage(), 'Image link (film)');
        $this->assertEquals(1, preg_match("@($sourceImage)@", $film->getImage($sourceName), $matches), "Image link ($sourceName)");
        $this->assertEquals(round($criticScore), round($film->getCriticScore($sourceName)), 'Critic score');
        $this->assertEquals(round($userScore), round($film->getUserScore($sourceName)), 'User score');
        $this->assertEquals($directors, $dbDirectors, 'Director(s)');
        $this->assertEquals($genres, array_intersect($genres, $dbGenres), 'Genres');
        $rating = $film->getRating($sourceName);
        $this->assertEquals($uniqueName, $film->getUniqueName($sourceName), 'UniqueName from source');
        $this->assertNull($rating->getYourScore(), 'Your Score');
        $this->assertNull($rating->getYourRatingDate(), 'Rating date not available from film detail page');
        $this->assertNull($rating->getSuggestedScore(), 'Suggested score not available is you are rated the film');
                    // RS source created
        $source = $film->getSource(Constants::SOURCE_RATINGSYNC);
        $this->assertEquals("rs$filmId", $source->getUniqueName(), "RS uniqueName");
        $this->assertEquals("/image/rs$filmId.jpg", $source->getImage(), "RS image");

        // TV Series
                // Setup
        $contentType = Film::CONTENT_TV_SERIES;
        $uniqueName = $constants["seriesUniqueName"];
        $searchTerms = array("uniqueName" => $uniqueName);
        $searchTerms["sourceName"] = $sourceName;
        $searchTerms["contentType"] = $contentType;

                // Test
        $film = search($searchTerms, Constants::TEST_RATINGSYNC_USERNAME)['match']; // Game of Thrones
        $filmId = $film->getId();
        $seriesFilmId = $filmId;

                // Verify
                    // film object
        $title = $constants["seriesTitle"];
        $year = $constants["seriesYear"];
        $sourceImage = $constants["seriesImage"];
        $userScore = $constants["seriesUserScore"];
        $criticScore = $constants["seriesCriticScore"];
        $directors = $constants["seriesDirectors"];
        $genres = $constants["seriesGenres"];
        $filmImage = "/image/rs$filmId.jpg";
        $directorsFromSearch = $film->getDirectors(); sort($directorsFromSearch);
        $genresFromSearch = $film->getGenres(); sort($genresFromSearch);
        $this->assertEquals($title, $film->getTitle(), "Title");
        $this->assertEquals($year, $film->getYear(), "Year");
        $this->assertEquals($contentType, $film->getContentType(), 'Content Type');
        $this->assertEquals($filmImage, $film->getImage(), 'Image link (film)');
        $this->assertEquals(1, preg_match("@($sourceImage)@", $film->getImage($sourceName), $matches), "Image link ($sourceName)");
        $this->assertEquals(round($criticScore), round($film->getCriticScore($sourceName)), 'Critic score');
        $this->assertEquals(round($userScore), round($film->getUserScore($sourceName)), 'User score');
        $this->assertEquals($directors, $directorsFromSearch, 'Director(s)');
        $this->assertEquals($genres, array_intersect($genres, $genresFromSearch), 'Genres');
        $rating = $film->getRating($sourceName);
        $this->assertEquals($uniqueName, $film->getUniqueName($sourceName), 'UniqueName from source');
        $this->assertNull($rating->getYourScore(), 'Your Score');
        $this->assertNull($rating->getYourRatingDate(), 'Rating date not available from film detail page');
        $this->assertNull($rating->getSuggestedScore(), 'Suggested score not available is you are rated the film');
                    // Verify the db film the same way as the object before
        $film = null;
        $film = Film::getFilmFromDb($filmId, Constants::TEST_RATINGSYNC_USERNAME);
        $dbDirectors = $film->getDirectors(); sort($dbDirectors);
        $dbGenres = $film->getGenres(); sort($dbGenres);
        $this->assertEquals($title, $film->getTitle(), "Title");
        $this->assertEquals($year, $film->getYear(), "Year");
        $this->assertEquals($contentType, $film->getContentType(), 'Content Type');
        $this->assertEquals($filmImage, $film->getImage(), 'Image link (film)');
        $this->assertEquals(1, preg_match("@($sourceImage)@", $film->getImage($sourceName), $matches), "Image link ($sourceName)");
        $this->assertEquals(round($criticScore), round($film->getCriticScore($sourceName)), 'Critic score');
        $this->assertEquals(round($userScore), round($film->getUserScore($sourceName)), 'User score');
        $this->assertEquals($directors, $dbDirectors, 'Director(s)');
        $this->assertEquals($genres, array_intersect($genres, $dbGenres), 'Genres');
        $rating = $film->getRating($sourceName);
        $this->assertEquals($uniqueName, $film->getUniqueName($sourceName), 'UniqueName from source');
        $this->assertNull($rating->getYourScore(), 'Your Score');
        $this->assertNull($rating->getYourRatingDate(), 'Rating date not available from film detail page');
        $this->assertNull($rating->getSuggestedScore(), 'Suggested score not available is you are rated the film');
                    // RS source created
        $source = $film->getSource(Constants::SOURCE_RATINGSYNC);
        $this->assertEquals("rs$filmId", $source->getUniqueName(), "RS uniqueName");
        $this->assertEquals("/image/rs$filmId.jpg", $source->getImage(), "RS image");

        // TV Episode
                // Setup
        $contentType = Film::CONTENT_TV_EPISODE;
        $uniqueName = $constants["episodeUniqueName"];
        $searchTerms = array("uniqueName" => $uniqueName);
        $searchTerms["sourceName"] = $sourceName;
        $searchTerms["contentType"] = $contentType;
        $searchTerms["parentId"] = $seriesFilmId;
        $searchTerms["season"] = $constants["episodeSeasonNum"];
        $searchTerms["episodeNumber"] = $constants["episodeEpisodeNum"];

                // Test
        $film = search($searchTerms, Constants::TEST_RATINGSYNC_USERNAME)['match']; // Game of Thrones
        $filmId = $film->getId();

                // Verify
                    // film object
        $title = $constants["episodeTitle"];
        $episodeTitle = $constants["episodeEpisodeTitle"];
        $year = $constants["episodeYear"];
        $sourceImage = $constants["episodeImage"];
        $userScore = $constants["episodeUserScore"];
        $criticScore = $constants["episodeCriticScore"];
        $directors = $constants["episodeDirectors"];
        $genres = $constants["episodeGenres"];
        if (Constants::DATA_API_DEFAULT == Constants::SOURCE_TMDBAPI) {
            // TMDb episode detail does not get us genres
            $genres = array();
        }
        $filmImage = "/image/rs$filmId.jpg";
        $seasonNum = $constants["episodeSeasonNum"];
        $episodeNum = $constants["episodeEpisodeNum"];
        $directorsFromSearch = $film->getDirectors(); sort($directorsFromSearch);
        $genresFromSearch = $film->getGenres(); sort($genresFromSearch);
        $this->assertEquals($title, $film->getTitle(), "Title");
        $this->assertEquals($year, $film->getYear(), "Year");
        $this->assertEquals($contentType, $film->getContentType(), 'Content Type');
        $this->assertEquals($filmImage, $film->getImage(), 'Image link (film)');
        $this->assertEquals(1, preg_match("@($sourceImage)@", $film->getImage($sourceName), $matches), "Image link ($sourceName)");
        $this->assertEquals(round($criticScore), round($film->getCriticScore($sourceName)), 'Critic score');
        $this->assertEquals(round($userScore), round($film->getUserScore($sourceName)), 'User score');
        $this->assertEquals($directors, $directorsFromSearch, 'Director(s)');
        $this->assertEquals($genres, array_intersect($genres, $genresFromSearch), 'Genres');
        $rating = $film->getRating($sourceName);
        $this->assertEquals($uniqueName, $film->getUniqueName($sourceName), 'UniqueName from source');
        $this->assertNull($rating->getYourScore(), 'Your Score');
        $this->assertNull($rating->getYourRatingDate(), 'Rating date not available from film detail page');
        $this->assertNull($rating->getSuggestedScore(), 'Suggested score not available is you are rated the film');
        $this->assertEquals($seasonNum, $seasonNum, "Season number");
        $this->assertEquals($episodeNum, $episodeNum, "Episode number");
                    // Verify the db film the same way as the object before
        $film = null;
        $film = Film::getFilmFromDb($filmId, Constants::TEST_RATINGSYNC_USERNAME);
        $dbDirectors = $film->getDirectors(); sort($dbDirectors);
        $dbGenres = $film->getGenres(); sort($dbGenres);
        $this->assertEquals($title, $film->getTitle(), "Title");
        $this->assertEquals($year, $film->getYear(), "Year");
        $this->assertEquals($contentType, $film->getContentType(), 'Content Type');
        $this->assertEquals($filmImage, $film->getImage(), 'Image link (film)');
        $this->assertEquals(1, preg_match("@($sourceImage)@", $film->getImage($sourceName), $matches), "Image link ($sourceName)");
        $this->assertEquals(round($criticScore), round($film->getCriticScore($sourceName)), 'Critic score');
        $this->assertEquals(round($userScore), round($film->getUserScore($sourceName)), 'User score');
        $this->assertEquals($directors, $dbDirectors, 'Director(s)');
        $this->assertEquals($genres, array_intersect($genres, $dbGenres), 'Genres');
        $rating = $film->getRating($sourceName);
        $this->assertEquals($uniqueName, $film->getUniqueName($sourceName), 'UniqueName from source');
        $this->assertNull($rating->getYourScore(), 'Your Score');
        $this->assertNull($rating->getYourRatingDate(), 'Rating date not available from film detail page');
        $this->assertNull($rating->getSuggestedScore(), 'Suggested score not available is you are rated the film');
                    // RS source created
        $source = $film->getSource(Constants::SOURCE_RATINGSYNC);
        $this->assertEquals("rs$filmId", $source->getUniqueName(), "RS uniqueName");
        $this->assertEquals("/image/rs$filmId.jpg", $source->getImage(), "RS image");
    }
    
    /**
     * @covers \RatingSync\search
     * @depends testSearch
     */
    public function testSearchEmptyUsername()
    {$this->start(__CLASS__, __FUNCTION__);
        // Covered by testSearch

        $this->assertTrue(true); // Making sure we made it this far
    }
    
    /**
     * @covers \RatingSync\search
     * @depends testSearch
     */
    public function testSearchDbMatch()
    {$this->start(__CLASS__, __FUNCTION__);

        // Set up
        // Use the film in db from testSearch
        $constants = $this->getConstants();
        $title = $constants["filmTitle"];

        // Test
        $searchTerms = array("uniqueName" => "rs1", "sourceName" => Constants::SOURCE_RATINGSYNC);
        $film = search($searchTerms, Constants::TEST_RATINGSYNC_USERNAME)['match']; // Buster (1988)

        // Verify
        $this->assertFalse(empty($film), "Film search result should not be empty");
        $this->assertEquals($title, $film->getTitle(), "Title");
    }
    
    /**
     * @covers \RatingSync\search
     * @depends testSearchDbMatch
     */
    public function testSearchDbMatchEmptyUsername()
    {$this->start(__CLASS__, __FUNCTION__);
        // covered by testSearchDbMatch

        $this->assertTrue(true); // Making sure we made it this far
    }
    
    /**
     * @covers \RatingSync\search
     * @depends testSearchDbMatch
     */
    public function testSearchDbMatchNoRatingsForUsername()
    {$this->start(__CLASS__, __FUNCTION__);

        // Set up
        // Use the film in db from testSearch
        $constants = $this->getConstants();
        $title = $constants["filmTitle"];

        // Test
        $searchTerms = array("uniqueName" => "rs1");
        $film = search($searchTerms, Constants::TEST_RATINGSYNC_USERNAME)['match'];

        // Verify
        $this->assertEquals($title, $film->getTitle(), "Title");
        $this->assertEmpty($film->getYourScore(Constants::SOURCE_RATINGSYNC), "Should be no RS rating");
    }
    
    /**
     * @covers \RatingSync\search
     * @depends testSearchDbMatch
     */
    public function testSearchDbMatchWithRatings()
    {$this->start(__CLASS__, __FUNCTION__);

        // Set up
        $constants = $this->getConstants();
        $title = $constants["filmTitle"];
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $searchTerms = array("uniqueName" => "rs1");
        $setupFilm = search($searchTerms, $username)['match']; // Buster (1988)
        $setupFilm->setYourScore(5, Constants::SOURCE_RATINGSYNC);
        $setupFilm->saveToDb($username);

        // Test
        $film = search($searchTerms, $username)['match'];

        // Verify
        $this->assertEquals($title, $film->getTitle(), "Title");
        $this->assertEquals($setupFilm->getYourScore(Constants::SOURCE_RATINGSYNC), $film->getYourScore(Constants::SOURCE_RATINGSYNC), "RS rating");
    }
    
    /**
     * @covers \RatingSync\search
     * @depends testSearch
     */
    public function testSearchDbNoMatchSiteMatch()
    {$this->start(__CLASS__, __FUNCTION__);
        // covered by testSearch

        $this->assertTrue(true); // Making sure we made it this far
    }
    
    /**
     * @covers \RatingSync\search
     */
    public function testSearchDbNoMatchSiteNoMatch()
    {$this->start(__CLASS__, __FUNCTION__);
    
        $searchTerms = array("uniqueName" => "garbage_query", "sourceName" => Constants::SOURCE_IMDB);
        $film = search($searchTerms, Constants::TEST_RATINGSYNC_USERNAME)['match'];
        $this->assertEmpty($film, "Film from uniqueName=garbage_query should be empty");
    }
}

?>
