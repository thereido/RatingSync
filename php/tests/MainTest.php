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
require_once "RatingTest.php";
//require_once "OmdbApiTest.php";
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
            //$constants = OmdbApiTest::getConstants();
            throw new \Exception("Setup OmdbAPI for testing");
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
            echo $query."  SQL Error: ".$db->errorInfo()[2];
            $success = false;
        }
        $query = "INSERT INTO user_source (user_name, source_name, username, password) VALUES ('$username_rs', '".Constants::SOURCE_IMDB."', 'imdb_user1', 'pwd')";
        if (! $db->query($query) ) {
            echo $query."  SQL Error: ".$db->errorInfo()[2];
            $success = false;
        }
        $query = "INSERT INTO user_source (user_name, source_name, username, password) VALUES ('$username_rs', '".Constants::SOURCE_JINNI."', 'jinni_user1', 'pwd')";
        if (! $db->query($query) ) {
            echo $query."  SQL Error: ".$db->errorInfo()[2];
            $success = false;
        }
        $query = "INSERT INTO user_source (user_name, source_name, username, password) VALUES ('$username_rs', '".Constants::SOURCE_RATINGSYNC."', '$username_rs', 'password')";
        if (! $db->query($query) ) {
            echo $query."  SQL Error: ".$db->errorInfo()[2];
            $success = false;
        }
        
        // Save ratings for 3 films for the new user
        $filmId = 1; $filmId2 = 2; $filmId4 = 4;
        $result = $db->query("SELECT * FROM rating WHERE film_id=$filmId AND user_name='".Constants::TEST_RATINGSYNC_USERNAME."' AND source_name='".Constants::SOURCE_IMDB."' AND active=1");
        $rating = new Rating(Constants::SOURCE_IMDB);
        $rating->initFromDbRow($result->fetch());
        $rating->saveToRs($username_rs, $filmId);
        $rating->saveToRs($username_rs, $filmId2);
        $rating->setYourRatingDate(new \DateTime());
        $rating->saveToRs($username_rs, $filmId4);

        $query = "UPDATE rating SET source_name='".Constants::SOURCE_IMDB."' WHERE film_id=$filmId4 AND user_name='$username_rs'";
        if (! $db->query($query) ) {
            echo $query."  SQL Error: ".$db->errorInfo()[2];
            $success = false;
        }

        $filmId = 1;
        $query = "UPDATE rating SET yourRatingDate='2015-1-1' WHERE film_id=$filmId AND user_name='".Constants::TEST_RATINGSYNC_USERNAME."' AND source_name='".Constants::SOURCE_RATINGSYNC."'";
        if (! $db->query($query) ) {
            echo $query."  SQL Error: ".$db->errorInfo()[2];
            $success = false;
        }

        $filmId = 3;
        $query = "UPDATE rating SET source_name='".Constants::SOURCE_IMDB."' WHERE film_id=$filmId AND user_name='".Constants::TEST_RATINGSYNC_USERNAME."'";
        if (! $db->query($query) ) {
            echo $query."  SQL Error: ".$db->errorInfo()[2];
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
        $imdbYourScore = $constants["filmImdbYourScore"];
        $imdbYourRatingDate = $constants["filmImdbYourDate"];

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
        $this->assertGreaterThan(0, count($dbGenres), "Genres");

        // Verify database - IMDb
        $this->assertEquals("tt2294629", $film->getUniqueName(Constants::SOURCE_IMDB), 'UniqueName from source');
        $this->assertEquals('https://m.media-amazon.com/images/M/MV5BMTQ1MjQwMTE5OF5BMl5BanBnXkFtZTgwNjk3MTcyMDE@._V1_SX300.jpg', $film->getImage(Constants::SOURCE_IMDB), 'Image link (IMDb)');
        $this->assertEquals(7, round($film->getCriticScore(Constants::SOURCE_IMDB)), 'Critic score');
        $this->assertEquals(8, round($film->getUserScore(Constants::SOURCE_IMDB)), 'User score');
        $rating = $film->getRating(Constants::SOURCE_IMDB);
        $this->assertEquals($imdbYourScore, $rating->getYourScore(), 'Your Score not available from searchImdb');
        $this->assertEquals($imdbYourRatingDate, $rating->getYourRatingDate()->format(RatingTest::DATE_FORMAT), 'Rating date not available from searchImdb');
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
        $this->assertEquals((new \DateTime())->format(RatingTest::DATE_FORMAT), $rating->getYourRatingDate()->format(RatingTest::DATE_FORMAT), 'Rating date not available from film detail page');
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
        $this->assertEquals((new \DateTime())->format(RatingTest::DATE_FORMAT), $rating->getYourRatingDate()->format(RatingTest::DATE_FORMAT), 'Rating date not available from searchImdb');
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
        $this->assertEquals((new \DateTime())->format(RatingTest::DATE_FORMAT), $rating->getYourRatingDate()->format(RatingTest::DATE_FORMAT), 'Rating date not available from searchImdb');
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

    function deleteAllRatingsForUserFilm($username, $sourceName, $filmId): bool
    {
        $db = getDatabase();

        $query = "DELETE FROM rating WHERE user_name='$username' AND film_id=$filmId AND source_name='$sourceName'";
        if (! $db->query($query) ) {
            $errorMsg = $query."  SQL Error: ".$db->errorInfo()[2];
            echo $errorMsg;
            return false;
        }

        return true;
    }

    function deleteOneRating($username, $sourceName, $filmId, $dateStr): bool
    {
        $db = getDatabase();

        $query = "DELETE FROM rating WHERE user_name='$username' AND film_id=$filmId AND source_name='$sourceName' AND yourRatingDate='$dateStr'";
        if (! $db->query($query) ) {
            $errorMsg = $query."  SQL Error: ".$db->errorInfo()[2];
            echo $errorMsg;
            return false;
        }

        return true;
    }

    /**
     * - The user has no ratings for the film
     * - Set the rating with today's date
     *
     * Expect
     *   - The new rating shows up in the db
     *   - Score matches and the date is today
     *
     * @covers  \RatingSync\setRating
     */
    public function testSetRating()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $db = getDatabase();
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        SessionUtility::setUsername($username);
        $sourceName = Constants::SOURCE_RATINGSYNC;
        $filmId = 1;
        $currentScore = 4;
        $today = new \DateTime();
        $todayStr = $today->format("Y-m-d");

        $deleteSuccess = $this->deleteAllRatingsForUserFilm($username, $sourceName, $filmId);
        $this->assertTrue($deleteSuccess, "Error deleting ratings for $username, $sourceName, $filmId");

        // Test
        $filmReturned = setRating($filmId, $currentScore, $todayStr);

        // Verify film returned
        $rating = $filmReturned->getRating($sourceName);
        $this->assertFalse(empty($rating), "Rating should not be empty");
        $this->assertEquals($currentScore, $rating->getYourScore(), "score");
        $this->assertEquals($today->format("Y-m-d"), $rating->getYourRatingDate()->format("Y-m-d"), "rating date");

        // Verify film from the database
        $dbFilm = Film::getFilmFromDb($filmId, $username);
        $rating = $dbFilm->getRating($sourceName);
        $this->assertFalse(empty($rating), "Rating should not be empty");
        $this->assertEquals($currentScore, $rating->getYourScore(), "score");
        $this->assertEquals($today->format("Y-m-d"), $rating->getYourRatingDate()->format("Y-m-d"), "rating date");
    }

    /**
     * - The user has 1 rating for the film with today's date
     * - The user has no archived ratings for the film
     * - Set the rating with a different score and today's date
     *
     * Expect
     *   - The new rating is in the db with active=1
     *   - The original rating is not archived because it is the same day
     *
     * @covers  \RatingSync\setRating
     * @depends testSetRating
     */
    public function testSetRatingChange()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        SessionUtility::setUsername($username);
        $sourceName = Constants::SOURCE_RATINGSYNC;
        $filmId = 1;
        $newScore = 6;
        $today = new \DateTime();
        $todayStr = $today->format("Y-m-d");

        $originalArchive = Rating::getInactiveRatingsFromDb($username, $sourceName, $filmId);

        // Test
        $filmReturned = setRating($filmId, $newScore, $todayStr);

        // Verify film returned
        $rating = $filmReturned->getRating($sourceName);
        $this->assertEquals($newScore, $rating->getYourScore(), "score should be $newScore");
        $this->assertEquals($today->format("Y-m-d"), $rating->getYourRatingDate()->format("Y-m-d"), "rating date");

        // Verify film from the database
        $dbFilm = Film::getFilmFromDb($filmId, $username);
        $dbRating = $dbFilm->getRating($sourceName);
        $this->assertEquals($newScore, $dbRating->getYourScore(), "score should be $newScore");
        $this->assertEquals($today->format("Y-m-d"), $dbRating->getYourRatingDate()->format("Y-m-d"), "rating date");

        // Verify archive from the database
        $this->assertCount(0, $originalArchive, "Original archive should be empty");
        $dbArchive = Rating::getInactiveRatingsFromDb($username, $sourceName, $filmId);
        $this->assertCount(0, $dbArchive, "Archive should still be empty");
    }

    /**
     * - The user has 1 rating for the film with a date that is not today
     * - The user has no archived ratings for the film
     * - Set the rating with a different score and today's date
     *
     * Expect
     *   - The new rating is in the db with active=1
     *   - The original rating is archived
     *
     * @covers  \RatingSync\setRating
     * @depends testSetRating
     * @depends testSetRatingChange
     */
    public function testSetRatingArchive()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        SessionUtility::setUsername($username);
        $sourceName = Constants::SOURCE_RATINGSYNC;
        $filmId = 1;
        $originalScore = 4;
        $newScore = 6;
        $today = new \DateTime();
        $todayStr = $today->format("Y-m-d");
        $originalDateStr = "2022-03-15";

        $deleteSuccess = $this->deleteAllRatingsForUserFilm($username, $sourceName, $filmId);
        $this->assertTrue($deleteSuccess, "Error deleting ratings for $username, $sourceName, $filmId");

        setRating($filmId, $originalScore, $originalDateStr);
        $originalArchive = Rating::getInactiveRatingsFromDb($username, $sourceName, $filmId);

        // Test
        $filmReturned = setRating($filmId, $newScore, $todayStr);

        // Verify film returned
        $rating = $filmReturned->getRating($sourceName);
        $this->assertEquals($newScore, $rating->getYourScore(), "score should be $newScore");
        $this->assertEquals($today->format("Y-m-d"), $rating->getYourRatingDate()->format("Y-m-d"), "rating date");

        // Verify film from the database
        $dbFilm = Film::getFilmFromDb($filmId, $username);
        $dbRating = $dbFilm->getRating($sourceName);
        $this->assertEquals($newScore, $dbRating->getYourScore(), "score should be $newScore");
        $this->assertEquals($today->format("Y-m-d"), $dbRating->getYourRatingDate()->format("Y-m-d"), "rating date");

        // Verify archive from the database
        $this->assertCount(0, $originalArchive, "Original archive should be empty");
        $dbArchive = Rating::getInactiveRatingsFromDb($username, $sourceName, $filmId);
        $this->assertCount(1, $dbArchive, "Archive should have 1 rating");
        $dbArchivedRating = $dbArchive[0];
        $this->assertEquals($originalScore, $dbArchivedRating->getYourScore(), "archived score");
        $this->assertEquals($originalDateStr, $dbArchivedRating->getYourRatingDate()->format("Y-m-d"), "archived rating date");
    }

    /**
     * - The user has a current rating for the film with an old date
     * - Set the rating with the same score and today's date
     *
     * Expect
     *   - The new rating is in the db with active=1
     *   - The original rating is in the db with active=0
     *
     * @covers  \RatingSync\setRating
     * @depends testSetRating
     */
    public function testSetRatingSameScore()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        SessionUtility::setUsername($username);
        $sourceName = Constants::SOURCE_RATINGSYNC;
        $filmId = 1;
        $originalScore = 4;
        $newScore = 4;
        $today = new \DateTime();
        $todayStr = $today->format("Y-m-d");
        $originalDateStr = "2022-03-15";

        $deleteSuccess = $this->deleteAllRatingsForUserFilm($username, $sourceName, $filmId);
        $this->assertTrue($deleteSuccess, "Error deleting ratings for $username, $sourceName, $filmId");

        setRating($filmId, $originalScore, $originalDateStr);
        $originalArchive = Rating::getInactiveRatingsFromDb($username, $sourceName, $filmId);

        // Test
        $filmReturned = setRating($filmId, $newScore, $todayStr);

        // Verify film returned
        $rating = $filmReturned->getRating($sourceName);
        $this->assertEquals($newScore, $rating->getYourScore(), "score should be $newScore");
        $this->assertEquals($today->format("Y-m-d"), $rating->getYourRatingDate()->format("Y-m-d"), "rating date");

        // Verify film from the database
        $dbFilm = Film::getFilmFromDb($filmId, $username);
        $dbRating = $dbFilm->getRating($sourceName);
        $this->assertEquals($newScore, $dbRating->getYourScore(), "score should be $newScore");
        $this->assertEquals($today->format("Y-m-d"), $dbRating->getYourRatingDate()->format("Y-m-d"), "rating date");

        // Verify archive from the database
        $this->assertCount(0, $originalArchive, "Original archive should be empty");
        $dbArchive = Rating::getInactiveRatingsFromDb($username, $sourceName, $filmId);
        $this->assertCount(1, $dbArchive, "Archive should have 1 rating");
        $dbArchivedRating = $dbArchive[0];
        $this->assertEquals($originalScore, $dbArchivedRating->getYourScore(), "archived score");
        $this->assertEquals($originalDateStr, $dbArchivedRating->getYourRatingDate()->format("Y-m-d"), "archived rating date");

    }

    /**
     * - The user has no ratings for the film
     * - Set the rating with a date in the past
     *
     * Expect
     *   - The new rating shows up in the db
     *   - Score and date matches
     *
     * @covers  \RatingSync\setRating
     * @depends testSetRating
     */
    public function testSetRatingWithDate()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        SessionUtility::setUsername($username);
        $sourceName = Constants::SOURCE_RATINGSYNC;
        $filmId = 1;
        $newScore = 7;
        $pastDateStr = "2022-02-01";

        $deleteSuccess = $this->deleteAllRatingsForUserFilm($username, $sourceName, $filmId);
        $this->assertTrue($deleteSuccess, "Error deleting ratings for $username, $sourceName, $filmId");

        // Test
        $filmReturned = setRating($filmId, $newScore, $pastDateStr);

        // Verify film returned
        $rating = $filmReturned->getRating($sourceName);
        $this->assertEquals($newScore, $rating->getYourScore(), "score should be $newScore");
        $this->assertEquals($pastDateStr, $rating->getYourRatingDate()->format("Y-m-d"), "rating date");

        // Verify film from the database
        $dbFilm = Film::getFilmFromDb($filmId, $username);
        $dbRating = $dbFilm->getRating($sourceName);
        $this->assertEquals($newScore, $dbRating->getYourScore(), "score should be $newScore");
        $this->assertEquals($pastDateStr, $dbRating->getYourRatingDate()->format("Y-m-d"), "rating date");

    }

    /**
     * - The user has a current rating for the film with an old date
     * - The user has no archived ratings for the film
     * - Set the rating with the same score and same date as the current rating
     *
     * Expect
     *   - No changes to current rating
     *   - No changes to archived ratings (there are none)
     *
     * @covers  \RatingSync\setRating
     * @depends testSetRatingWithDate
     */
    public function testSetRatingSameScoreSameDate()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        SessionUtility::setUsername($username);
        $sourceName = Constants::SOURCE_RATINGSYNC;
        $filmId = 1;
        $currentScore = 7;
        $currentDateStr = "2022-02-01";

        // Test
        $filmReturned = setRating($filmId, $currentScore, $currentDateStr);

        // Verify film returned
        $rating = $filmReturned->getRating($sourceName);
        $this->assertEquals($currentScore, $rating->getYourScore(), "score should be $currentScore");
        $this->assertEquals($currentDateStr, $rating->getYourRatingDate()->format("Y-m-d"), "rating date");

        // Verify film from the database
        $dbFilm = Film::getFilmFromDb($filmId, $username);
        $dbRating = $dbFilm->getRating($sourceName);
        $this->assertEquals($currentScore, $dbRating->getYourScore(), "score should be $currentScore");
        $this->assertEquals($currentDateStr, $dbRating->getYourRatingDate()->format("Y-m-d"), "rating date");

        // Verify archive from the database
        $dbArchive = Rating::getInactiveRatingsFromDb($username, $sourceName, $filmId);
        $this->assertCount(0, $dbArchive, "Archive should have no ratings");

    }

    /**
     * - The user has an archived rating
     * - Change the score of the archived rating
     * - The user has an active rating
     *
     * Expect
     *   - Archived rating has the new score
     *   - Archived rating has the same date
     *   - Active rating is not changed
     *
     * @covers  \RatingSync\setRating
     * @depends testSetRating
     * @depends testSetRatingWithDate
     * @depends testSetRatingArchive
     */
    public function testSetRatingUpdateArchive()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        SessionUtility::setUsername($username);
        $sourceName = Constants::SOURCE_RATINGSYNC;
        $filmId = 1;
        $activeScore = 7; // From testSetRatingWithDate
        $activeDateStr = "2022-02-01"; // From testSetRatingWithDate
        $originalArchiveScore = 4;
        $newArchiveScore = 5;
        $ArchiveDateStr = "2022-01-15";

        setRating($filmId, $originalArchiveScore, $ArchiveDateStr);

        // Test
        $filmReturned = setRating($filmId, $newArchiveScore, $ArchiveDateStr);

        // Verify film returned
        $rating = $filmReturned->getRating($sourceName);
        $this->assertEquals($activeScore, $rating->getYourScore(), "score should be $activeScore");
        $this->assertEquals($activeDateStr, $rating->getYourRatingDate()->format("Y-m-d"), "rating date");

        // Verify film from the database
        $dbFilm = Film::getFilmFromDb($filmId, $username);
        $dbRating = $dbFilm->getRating($sourceName);
        $this->assertEquals($activeScore, $dbRating->getYourScore(), "score should be $activeScore");
        $this->assertEquals($activeDateStr, $dbRating->getYourRatingDate()->format("Y-m-d"), "rating date");

        // Verify archive from the database
        $dbArchive = Rating::getInactiveRatingsFromDb($username, $sourceName, $filmId);
        $this->assertCount(1, $dbArchive, "Archive should have 1 rating");
        $dbArchivedRating = $dbArchive[0];
        $this->assertEquals($newArchiveScore, $dbArchivedRating->getYourScore(), "archived score");
        $this->assertEquals($ArchiveDateStr, $dbArchivedRating->getYourRatingDate()->format("Y-m-d"), "archived rating date");

    }

    /**
     * - The user has a current rating for the film
     * - The user has no archived ratings for the film
     * - Delete the rating (set score 0)
     *
     * Expect
     *   - User has no current rating for the film
     *   - The original current rating is now archived
     *
     * @covers  \RatingSync\setRating
     * @depends testSetRating
     */
    public function testSetRatingDeleteCurrent()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        SessionUtility::setUsername($username);
        $sourceName = Constants::SOURCE_RATINGSYNC;
        $filmId = 1;
        $score = 7;
        $dateStr = "2022-03-08";

        $deleteSuccess = $this->deleteAllRatingsForUserFilm($username, $sourceName, $filmId);
        $this->assertTrue($deleteSuccess, "Error deleting ratings for $username, $sourceName, $filmId");

        setRating($filmId, $score, $dateStr);

        // Test
        $filmReturned = setRating($filmId, 0, $dateStr);

        // Verify film returned
        $rating = $filmReturned->getRating($sourceName);
        $this->assertNull($rating->getYourScore(), "Deleted rating should be a null score");
        $this->assertNull($rating->getYourRatingDate(), "Deleted rating should be a null date");

        // Verify film from the database
        $dbFilm = Film::getFilmFromDb($filmId, $username);
        $dbRating = $dbFilm->getRating($sourceName);
        $this->assertNull($dbRating->getYourScore(), "Deleted rating should be a null score");
        $this->assertNull($dbRating->getYourRatingDate(), "Deleted rating should be a null date");

        // Verify archive from the database
        $dbArchive = Rating::getInactiveRatingsFromDb($username, $sourceName, $filmId);
        $this->assertCount(1, $dbArchive, "Archive should have 1 rating");
        $dbArchivedRating = $dbArchive[0];
        $this->assertEquals($score, $dbArchivedRating->getYourScore(), "archived score");
        $this->assertEquals($dateStr, $dbArchivedRating->getYourRatingDate()->format("Y-m-d"), "archived rating date");

    }

    /**
     * - The user has a current rating for the film
     * - The user has 2 archived ratings for the film
     * - Delete the current rating (set score 0)
     *
     * Expect
     *   - The user has no active rating
     *   - The deleted active rating is archived
     *   - There 3 archived ratings
     *     - The deleted active rating
     *     - The 2 existing archived ratings (unchanged)
     *
     * @covers  \RatingSync\setRating
     * @depends testSetRatingDeleteCurrent
     */
    public function testSetRatingDeleteCurrentWithArchive()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        SessionUtility::setUsername($username);
        $sourceName = Constants::SOURCE_RATINGSYNC;
        $filmId = 1;
        $activeScore = 2;
        $archive1Score = 3;
        $archive2Score = 5;
        $activeDateStr = "2022-02-04";
        $archive1DateStr = "2022-01-16";
        $archive2DateStr = "2022-01-15";

        $deleteSuccess = $this->deleteAllRatingsForUserFilm($username, $sourceName, $filmId);
        $this->assertTrue($deleteSuccess, "Error deleting ratings for $username, $sourceName, $filmId");

        setRating($filmId, $activeScore, $activeDateStr);
        setRating($filmId, $archive2Score, $archive2DateStr);
        setRating($filmId, $archive1Score, $archive1DateStr);

        // Test
        $filmReturned = setRating($filmId, 0, $activeDateStr);

        // Verify film returned
        $rating = $filmReturned->getRating($sourceName);
        $this->assertNull($rating->getYourScore(), "Deleted rating should be a null score");
        $this->assertNull($rating->getYourRatingDate(), "Deleted rating should be a null date");

        // Verify film from the database
        $dbFilm = Film::getFilmFromDb($filmId, $username);
        $dbRating = $dbFilm->getRating($sourceName);
        $this->assertNull($dbRating->getYourScore(), "Deleted rating should be a null score");
        $this->assertNull($dbRating->getYourRatingDate(), "Deleted rating should be a null date");

        // Verify archive from the database
        $dbArchive = Rating::getInactiveRatingsFromDb($username, $sourceName, $filmId);
        $this->assertCount(3, $dbArchive, "Archive should have 3 ratings");
        $dbArchivedRating = $dbArchive[0];
        $this->assertEquals($activeScore, $dbArchivedRating->getYourScore(), "archived score");
        $this->assertEquals($activeDateStr, $dbArchivedRating->getYourRatingDate()->format("Y-m-d"), "archived rating date");
        $dbArchivedRating = $dbArchive[1];
        $this->assertEquals($archive1Score, $dbArchivedRating->getYourScore(), "archived score");
        $this->assertEquals($archive1DateStr, $dbArchivedRating->getYourRatingDate()->format("Y-m-d"), "archived rating date");
        $dbArchivedRating = $dbArchive[2];
        $this->assertEquals($archive2Score, $dbArchivedRating->getYourScore(), "archived score");
        $this->assertEquals($archive2DateStr, $dbArchivedRating->getYourRatingDate()->format("Y-m-d"), "archived rating date");

    }

    /**
     * - The user has a current rating for the film
     * - The user has 2 archived ratings for the film
     * - Delete the one of the archived ratings (set score 0)
     *
     * Expect
     *   - The archived rating is deleted
     *   - Current rating is not changed
     *   - The other archived rating is not changed
     *
     * @covers  \RatingSync\setRating
     * @depends testSetRatingDeleteCurrent
     */
    public function testSetRatingDeleteArchived()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        SessionUtility::setUsername($username);
        $sourceName = Constants::SOURCE_RATINGSYNC;
        $filmId = 1;
        $activeScore = 2;
        $archive1Score = 3;
        $archive2Score = 5;
        $activeDateStr = "2022-02-04";
        $archive1DateStr = "2022-01-15";
        $archive2DateStr = "2022-01-16";

        $deleteSuccess = $this->deleteAllRatingsForUserFilm($username, $sourceName, $filmId);
        $this->assertTrue($deleteSuccess, "Error deleting ratings for $username, $sourceName, $filmId");

        setRating($filmId, $activeScore, $activeDateStr);
        setRating($filmId, $archive1Score, $archive1DateStr);
        setRating($filmId, $archive2Score, $archive2DateStr);

        // Test
        $filmReturned = setRating($filmId, 0, $archive1DateStr);

        // Verify film returned
        $rating = $filmReturned->getRating($sourceName);
        $this->assertEquals($activeScore, $rating->getYourScore(), "score should be $activeScore");
        $this->assertEquals($activeDateStr, $rating->getYourRatingDate()->format("Y-m-d"), "rating date");

        // Verify film from the database
        $dbFilm = Film::getFilmFromDb($filmId, $username);
        $dbRating = $dbFilm->getRating($sourceName);
        $this->assertEquals($activeScore, $dbRating->getYourScore(), "score should be $activeScore");
        $this->assertEquals($activeDateStr, $dbRating->getYourRatingDate()->format("Y-m-d"), "rating date");

        // Verify archive from the database
        $dbArchive = Rating::getInactiveRatingsFromDb($username, $sourceName, $filmId);
        $this->assertCount(1, $dbArchive, "Archive should have 1 rating");
        $dbArchivedRating = $dbArchive[0];
        $this->assertEquals($archive2Score, $dbArchivedRating->getYourScore(), "archived score");
        $this->assertEquals($archive2DateStr, $dbArchivedRating->getYourRatingDate()->format("Y-m-d"), "archived rating date");

    }

    /**
     * - The user has a current rating for the film
     * - Current rating date is earlier than today
     * - The user has no archived ratings for the film
     * - Delete the rating (set score 0) with a later date
     *
     * Expect
     *   - User has no active rating for the film
     *   - The original rating is archived
     *
     * @covers  \RatingSync\setRating
     * @depends testSetRating
     * @depends testSetRatingDeleteCurrent
     */
    public function testSetRatingDeleteNewerThanActive()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        SessionUtility::setUsername($username);
        $sourceName = Constants::SOURCE_RATINGSYNC;
        $filmId = 1;
        $score = 7;
        $dateStr = "2022-03-08";
        $laterDateStr = "2022-03-10";

        $deleteSuccess = $this->deleteAllRatingsForUserFilm($username, $sourceName, $filmId);
        $this->assertTrue($deleteSuccess, "Error deleting ratings for $username, $sourceName, $filmId");

        setRating($filmId, $score, $dateStr);

        // Test
        $filmReturned = setRating($filmId, 0, $laterDateStr);

        // Verify film returned
        $rating = $filmReturned->getRating($sourceName);
        $this->assertNull($rating->getYourScore(), "Deleted rating should be a null score");
        $this->assertNull($rating->getYourRatingDate(), "Deleted rating should be a null date");

        // Verify film from the database
        $dbFilm = Film::getFilmFromDb($filmId, $username);
        $dbRating = $dbFilm->getRating($sourceName);
        $this->assertNull($dbRating->getYourScore(), "Deleted rating should be a null score");
        $this->assertNull($dbRating->getYourRatingDate(), "Deleted rating should be a null date");

        // Verify archive from the database
        $dbArchive = Rating::getInactiveRatingsFromDb($username, $sourceName, $filmId);
        $this->assertCount(1, $dbArchive, "Archive should have 1 rating");
        $dbArchivedRating = $dbArchive[0];
        $this->assertEquals($score, $dbArchivedRating->getYourScore(), "archived score");
        $this->assertEquals($dateStr, $dbArchivedRating->getYourRatingDate()->format("Y-m-d"), "archived rating date");

    }

    /**
     * - The user has a current rating for the film
     * - Delete the rating (set score 0) with a null date
     *
     * Expect
     *   - User has no active rating for the film
     *   - The original rating is archived
     *
     * @covers  \RatingSync\setRating
     * @depends testSetRating
     * @depends testSetRatingDeleteCurrent
     */
    public function testSetRatingDeleteNullDate()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        SessionUtility::setUsername($username);
        $sourceName = Constants::SOURCE_RATINGSYNC;
        $filmId = 1;
        $score = 7;
        $dateStr = "2022-03-08";

        $deleteSuccess = $this->deleteAllRatingsForUserFilm($username, $sourceName, $filmId);
        $this->assertTrue($deleteSuccess, "Error deleting ratings for $username, $sourceName, $filmId");

        setRating($filmId, $score, $dateStr);

        // Test
        $filmReturned = setRating($filmId, 0, null);

        // Verify film returned
        $rating = $filmReturned->getRating($sourceName);
        $this->assertNull($rating->getYourScore(), "Deleted rating should be a null score");
        $this->assertNull($rating->getYourRatingDate(), "Deleted rating should be a null date");

        // Verify film from the database
        $dbFilm = Film::getFilmFromDb($filmId, $username);
        $dbRating = $dbFilm->getRating($sourceName);
        $this->assertNull($dbRating->getYourScore(), "Deleted rating should be a null score");
        $this->assertNull($dbRating->getYourRatingDate(), "Deleted rating should be a null date");

        // Verify archive from the database
        $dbArchive = Rating::getInactiveRatingsFromDb($username, $sourceName, $filmId);
        $this->assertCount(1, $dbArchive, "Archive should have 1 rating");
        $dbArchivedRating = $dbArchive[0];
        $this->assertEquals($score, $dbArchivedRating->getYourScore(), "archived score");
        $this->assertEquals($dateStr, $dbArchivedRating->getYourRatingDate()->format("Y-m-d"), "archived rating date");

    }

    /**
     * This suppose to cover all the use cases mentioned in the main::setRating()
     * function comment. This depends on all previous setRating tests because there
     * is no point in testing these is we already know of specific problems.
     *
     * USE CASES FROM main::setRating() COMMENT
     *   Note: If the dateStr param is in the future, then the current date is used.
     * Create/Update Use cases (newDate non-null, scores 1 through 10):
     *   1) Same date as the active rating: Change the score
     *   2) Same date as an archived rating: Change the score
     *   3) No existing active rating and newer than existing archived ratings: Create the new active rating
     *   4) No existing active rating and older than the newest existing archived rating: Archive the new rating
     *   5) Newer rating than the active rating: Archive the existing and create the new active rating
     *   6) Older rating than the active rating: Archive the new rating
     * Create/Update Use cases (newDate=null, scores 1 through 10):
     *   7) No existing active rating, but archived rating is the current date: Delete the archived rating and create the active with current date
     *   8) For all other cases with newDate=null and score range 1-10: Archive the existing and create the new active rating with current date
     * Delete Use cases (score 0):
     *   9) No matching date and no existing active rating: do nothing
     *   10) newDate is null OR newDate is the same or newer than the existing active rating: Archive the existing active rating
     *   11) Same date as an existing archived rating: Delete
     *
     * @covers  \RatingSync\setRating
     * @depends testSetRating
     * @depends testSetRatingChange
     * @depends testSetRatingArchive
     * @depends testSetRatingSameScore
     * @depends testSetRatingWithDate
     * @depends testSetRatingUpdateArchive
     * @depends testSetRatingDeleteCurrent
     * @depends testSetRatingDeleteCurrentWithArchive
     * @depends testSetRatingDeleteArchived
     * @depends testSetRatingDeleteNewerThanActive
     */
    public function testSetRatingUseCasesFromComment()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        SessionUtility::setUsername($username);
        $sourceName = Constants::SOURCE_RATINGSYNC;
        $filmId = 1;
        $today = new \DateTime();
        $todayStr = $today->format("Y-m-d");

        $defaultActiveScore = 5;
        $defaultActiveDateStr = "2022-03-20";
        $archive0Score = 6;
        $archive0Date = "2022-03-16";
        $archive1Score = 7;
        $archive1Date = "2022-03-15";
        $defaultArchive = [["score" => $archive0Score, "date" => $archive0Date], ["score" => $archive1Score, "date" => $archive1Date]];
        $defaultNewScore = 8;

        // Create/Update Use cases (dateStr non-null, scores 1 through 10)
        // 1) Same date as the active rating: Change the score

        $existingActiveScore = $defaultActiveScore;
        $existingActiveDateStr = $defaultActiveDateStr;
        $existingArchive = $defaultArchive;
        $newScore = $defaultNewScore;
        $newDate = $defaultActiveDateStr;

        $this->deleteAllRatingsForUserFilm($username, $sourceName, $filmId);
        setRating($filmId, $existingActiveScore, $existingActiveDateStr);
        setRating($filmId, $archive0Score, $archive0Date);
        setRating($filmId, $archive1Score, $archive1Date);

        // Test (case 1)
        $filmReturned = setRating($filmId, $newScore, $newDate);

        // Verify (case 1)
        $this->assertTrue($filmReturned instanceof Film, "Successful call from setRating should return a Film object");
        $rating = $filmReturned->getRating($sourceName);
        $this->assertEquals($newScore, $rating->getYourScore(), "active score");
        $this->assertEquals($existingActiveDateStr, $rating->getYourRatingDate()->format("Y-m-d"), "active rating date");
        $dbRating = Film::getFilmFromDb($filmId, $username)->getRating($sourceName);
        $this->assertEquals($rating->getYourScore(), $dbRating->getYourScore(), "active score from db");
        $this->assertEquals($rating->getYourRatingDate()->format("Y-m-d"), $dbRating->getYourRatingDate()->format("Y-m-d"), "active rating date from db");
        $dbArchive = Rating::getInactiveRatingsFromDb($username, $sourceName, $filmId);
        $this->assertCount(2, $dbArchive, "Archive count");
        $dbArchivedRating = $dbArchive[0];
        $this->assertEquals($existingArchive[0]["score"], $dbArchivedRating->getYourScore(), "archived score");
        $this->assertEquals($existingArchive[0]["date"], $dbArchivedRating->getYourRatingDate()->format("Y-m-d"), "archived rating date");
        $dbArchivedRating = $dbArchive[1];
        $this->assertEquals($existingArchive[1]["score"], $dbArchivedRating->getYourScore(), "archived score");
        $this->assertEquals($existingArchive[1]["date"], $dbArchivedRating->getYourRatingDate()->format("Y-m-d"), "archived rating date");

        // Create/Update Use cases (dateStr non-null, scores 1 through 10)
        // 2) Same date as an archived rating: Change the score

        $existingActiveScore = $defaultActiveScore;
        $existingActiveDateStr = $defaultActiveDateStr;
        $existingArchive = $defaultArchive;
        $newScore = $defaultNewScore;
        $newDate = $archive0Date; // Same as the newest archived rating

        $this->deleteAllRatingsForUserFilm($username, $sourceName, $filmId);
        setRating($filmId, $existingActiveScore, $existingActiveDateStr);
        setRating($filmId, $archive0Score, $archive0Date);
        setRating($filmId, $archive1Score, $archive1Date);

        // Test (case 2)
        $filmReturned = setRating($filmId, $newScore, $newDate);

        // Verify (case 2)
        $this->assertTrue($filmReturned instanceof Film, "Successful call from setRating should return a Film object");
        $rating = $filmReturned->getRating($sourceName);
        $this->assertEquals($existingActiveScore, $rating->getYourScore(), "active score");
        $this->assertEquals($existingActiveDateStr, $rating->getYourRatingDate()->format("Y-m-d"), "active rating date");
        $dbRating = Film::getFilmFromDb($filmId, $username)->getRating($sourceName);
        $this->assertEquals($rating->getYourScore(), $dbRating->getYourScore(), "active score from db");
        $this->assertEquals($rating->getYourRatingDate()->format("Y-m-d"), $dbRating->getYourRatingDate()->format("Y-m-d"), "active rating date from db");
        $dbArchive = Rating::getInactiveRatingsFromDb($username, $sourceName, $filmId);
        $this->assertCount(2, $dbArchive, "Archive count");
        $dbArchivedRating = $dbArchive[0];
        $this->assertEquals($newScore, $dbArchivedRating->getYourScore(), "archived score");
        $this->assertEquals($newDate, $dbArchivedRating->getYourRatingDate()->format("Y-m-d"), "archived rating date");
        $dbArchivedRating = $dbArchive[1];
        $this->assertEquals($existingArchive[1]["score"], $dbArchivedRating->getYourScore(), "archived score");
        $this->assertEquals($existingArchive[1]["date"], $dbArchivedRating->getYourRatingDate()->format("Y-m-d"), "archived rating date");

        // Create/Update Use cases (dateStr non-null, scores 1 through 10)
        // 3) No existing active rating and newer than existing archived ratings: Create the new active rating

        $existingActiveScore = $defaultActiveScore;
        $existingActiveDateStr = $defaultActiveDateStr;
        $existingArchive = $defaultArchive;
        $newScore = $defaultNewScore;
        $newDate = $todayStr;

        $this->deleteAllRatingsForUserFilm($username, $sourceName, $filmId);
        setRating($filmId, $existingActiveScore, $existingActiveDateStr); // active rating (to be archived before the test)
        setRating($filmId, $archive0Score, $archive0Date);
        setRating($filmId, $archive1Score, $archive1Date);
        setRating($filmId, 0, $existingActiveDateStr); // Delete active rating (which will be archived)

        // Test (case 3)
        $filmReturned = setRating($filmId, $newScore, $newDate);

        // Verify (case 3)
        $this->assertTrue($filmReturned instanceof Film, "Successful call from setRating should return a Film object");
        $rating = $filmReturned->getRating($sourceName);
        $this->assertEquals($newScore, $rating->getYourScore(), "active score");
        $this->assertEquals($newDate, $rating->getYourRatingDate()->format("Y-m-d"), "active rating date");
        $dbRating = Film::getFilmFromDb($filmId, $username)->getRating($sourceName);
        $this->assertEquals($rating->getYourScore(), $dbRating->getYourScore(), "active score from db");
        $this->assertEquals($rating->getYourRatingDate()->format("Y-m-d"), $dbRating->getYourRatingDate()->format("Y-m-d"), "active rating date from db");
        $dbArchive = Rating::getInactiveRatingsFromDb($username, $sourceName, $filmId);
        $this->assertCount(3, $dbArchive, "Archive count");
        $dbArchivedRating = $dbArchive[0];
        $this->assertEquals($existingActiveScore, $dbArchivedRating->getYourScore(), "archived score");
        $this->assertEquals($existingActiveDateStr, $dbArchivedRating->getYourRatingDate()->format("Y-m-d"), "archived rating date");
        $dbArchivedRating = $dbArchive[1];
        $this->assertEquals($existingArchive[0]["score"], $dbArchivedRating->getYourScore(), "archived score");
        $this->assertEquals($existingArchive[0]["date"], $dbArchivedRating->getYourRatingDate()->format("Y-m-d"), "archived rating date");
        $dbArchivedRating = $dbArchive[2];
        $this->assertEquals($existingArchive[1]["score"], $dbArchivedRating->getYourScore(), "archived score");
        $this->assertEquals($existingArchive[1]["date"], $dbArchivedRating->getYourRatingDate()->format("Y-m-d"), "archived rating date");

        // Create/Update Use cases (dateStr non-null, scores 1 through 10)
        // 4) No existing active rating and older than the newest existing archived rating: Archive the new rating

        $existingActiveScore = $defaultActiveScore;
        $existingActiveDateStr = $defaultActiveDateStr;
        $existingArchive = $defaultArchive;
        $newScore = 3;
        $newDate = "2022-03-14";

        $this->deleteAllRatingsForUserFilm($username, $sourceName, $filmId);
        setRating($filmId, $existingActiveScore, $existingActiveDateStr); // active rating (to be delete before the test)
        setRating($filmId, $archive0Score, $archive0Date);
        setRating($filmId, $archive1Score, $archive1Date);
        $deleteSuccess = $this->deleteOneRating($username, $sourceName, $filmId, $existingActiveDateStr); // The active rating
        $this->assertTrue($deleteSuccess, "Failed delete the active rating before testing case 4");

        // Test (case 4)
        $filmReturned = setRating($filmId, $newScore, $newDate);

        // Verify (case 4)
        $this->assertTrue($filmReturned instanceof Film, "Successful call from setRating should return a Film object");
        $rating = $filmReturned->getRating($sourceName);
        $this->assertNull($rating->getYourScore(), "active score");
        $this->assertNull($rating->getYourRatingDate(), "active rating date");
        $dbRating = Film::getFilmFromDb($filmId, $username)->getRating($sourceName);
        $this->assertNull($dbRating->getYourScore(), "active score from db");
        $this->assertNull($dbRating->getYourRatingDate(), "active rating date from db");
        $dbArchive = Rating::getInactiveRatingsFromDb($username, $sourceName, $filmId);
        $this->assertCount(3, $dbArchive, "Archive count");
        $dbArchivedRating = $dbArchive[0];
        $this->assertEquals($existingArchive[0]["score"], $dbArchivedRating->getYourScore(), "archived score");
        $this->assertEquals($existingArchive[0]["date"], $dbArchivedRating->getYourRatingDate()->format("Y-m-d"), "archived rating date");
        $dbArchivedRating = $dbArchive[1];
        $this->assertEquals($existingArchive[1]["score"], $dbArchivedRating->getYourScore(), "archived score");
        $this->assertEquals($existingArchive[1]["date"], $dbArchivedRating->getYourRatingDate()->format("Y-m-d"), "archived rating date");
        $dbArchivedRating = $dbArchive[2];
        $this->assertEquals($newScore, $dbArchivedRating->getYourScore(), "archived score");
        $this->assertEquals($newDate, $dbArchivedRating->getYourRatingDate()->format("Y-m-d"), "archived rating date");

        // Create/Update Use cases (dateStr non-null, scores 1 through 10)
        // 5) Newer rating than the active rating: Archive the existing and create the new active rating

        $existingActiveScore = $defaultActiveScore;
        $existingActiveDateStr = $defaultActiveDateStr;
        $existingArchive = $defaultArchive;
        $newScore = $defaultNewScore;
        $newDate = $todayStr;

        $this->deleteAllRatingsForUserFilm($username, $sourceName, $filmId);
        setRating($filmId, $existingActiveScore, $existingActiveDateStr);
        setRating($filmId, $archive0Score, $archive0Date);
        setRating($filmId, $archive1Score, $archive1Date);

        // Test (case 5)
        $filmReturned = setRating($filmId, $newScore, $newDate);

        // Verify (case 5)
        $this->assertTrue($filmReturned instanceof Film, "Successful call from setRating should return a Film object");
        $rating = $filmReturned->getRating($sourceName);
        $this->assertEquals($newScore, $rating->getYourScore(), "active score");
        $this->assertEquals($newDate, $rating->getYourRatingDate()->format("Y-m-d"), "active rating date");
        $dbRating = Film::getFilmFromDb($filmId, $username)->getRating($sourceName);
        $this->assertEquals($rating->getYourScore(), $dbRating->getYourScore(), "active score from db");
        $this->assertEquals($rating->getYourRatingDate()->format("Y-m-d"), $dbRating->getYourRatingDate()->format("Y-m-d"), "active rating date from db");
        $dbArchive = Rating::getInactiveRatingsFromDb($username, $sourceName, $filmId);
        $this->assertCount(3, $dbArchive, "Archive count");
        $dbArchivedRating = $dbArchive[0];
        $this->assertEquals($existingActiveScore, $dbArchivedRating->getYourScore(), "archived score");
        $this->assertEquals($existingActiveDateStr, $dbArchivedRating->getYourRatingDate()->format("Y-m-d"), "archived rating date");
        $dbArchivedRating = $dbArchive[1];
        $this->assertEquals($existingArchive[0]["score"], $dbArchivedRating->getYourScore(), "archived score");
        $this->assertEquals($existingArchive[0]["date"], $dbArchivedRating->getYourRatingDate()->format("Y-m-d"), "archived rating date");
        $dbArchivedRating = $dbArchive[2];
        $this->assertEquals($existingArchive[1]["score"], $dbArchivedRating->getYourScore(), "archived score");
        $this->assertEquals($existingArchive[1]["date"], $dbArchivedRating->getYourRatingDate()->format("Y-m-d"), "archived rating date");

        // Create/Update Use cases (dateStr non-null, scores 1 through 10)
        // 6) Older rating than the active rating: Archive the new rating

        $existingActiveScore = $defaultActiveScore;
        $existingActiveDateStr = $defaultActiveDateStr;
        $existingArchive = $defaultArchive;
        $newScore = $defaultNewScore;
        $newDate = "2022-03-19"; // Between the active and the lasted archive

        $this->deleteAllRatingsForUserFilm($username, $sourceName, $filmId);
        setRating($filmId, $existingActiveScore, $existingActiveDateStr);
        setRating($filmId, $archive0Score, $archive0Date);
        setRating($filmId, $archive1Score, $archive1Date);

        // Test (case 6)
        $filmReturned = setRating($filmId, $newScore, $newDate);

        // Verify (case 6)
        $this->assertTrue($filmReturned instanceof Film, "Successful call from setRating should return a Film object");
        $rating = $filmReturned->getRating($sourceName);
        $this->assertEquals($existingActiveScore, $rating->getYourScore(), "active score");
        $this->assertEquals($existingActiveDateStr, $rating->getYourRatingDate()->format("Y-m-d"), "active rating date");
        $dbRating = Film::getFilmFromDb($filmId, $username)->getRating($sourceName);
        $this->assertEquals($rating->getYourScore(), $dbRating->getYourScore(), "active score from db");
        $this->assertEquals($rating->getYourRatingDate()->format("Y-m-d"), $dbRating->getYourRatingDate()->format("Y-m-d"), "active rating date from db");
        $dbArchive = Rating::getInactiveRatingsFromDb($username, $sourceName, $filmId);
        $this->assertCount(3, $dbArchive, "Archive count");
        $dbArchivedRating = $dbArchive[0];
        $this->assertEquals($newScore, $dbArchivedRating->getYourScore(), "archived score");
        $this->assertEquals($newDate, $dbArchivedRating->getYourRatingDate()->format("Y-m-d"), "archived rating date");
        $dbArchivedRating = $dbArchive[1];
        $this->assertEquals($existingArchive[0]["score"], $dbArchivedRating->getYourScore(), "archived score");
        $this->assertEquals($existingArchive[0]["date"], $dbArchivedRating->getYourRatingDate()->format("Y-m-d"), "archived rating date");
        $dbArchivedRating = $dbArchive[2];
        $this->assertEquals($existingArchive[1]["score"], $dbArchivedRating->getYourScore(), "archived score");
        $this->assertEquals($existingArchive[1]["date"], $dbArchivedRating->getYourRatingDate()->format("Y-m-d"), "archived rating date");

        // Create/Update Use cases (dateStr=null, scores 1 through 10):
        // 7) No existing active rating, but archived rating is the current date: Delete the archived rating and create the active with current date

        $existingActiveScore = $defaultActiveScore;
        $existingActiveDateStr = $defaultActiveDateStr;
        $todayStr = today()->format("Y-m-d");
        $existingArchive = [["score" => $archive0Score, "date" => $todayStr], ["score" => $archive1Score, "date" => $archive1Date]];
        $newScore = $defaultNewScore;
        $newDate = null;

        $this->deleteAllRatingsForUserFilm($username, $sourceName, $filmId);
        setRating($filmId, $existingActiveScore, $existingActiveDateStr); // active rating (to be deleted before the test)
        setRating($filmId, $archive0Score, $todayStr);
        setRating($filmId, $archive1Score, $archive1Date);
        $deleteSuccess = $this->deleteOneRating($username, $sourceName, $filmId, $existingActiveDateStr); // The active rating
        $this->assertTrue($deleteSuccess, "Failed delete the active rating before testing case 7");

        // Test (case 7)
        $filmReturned = setRating($filmId, $newScore, $newDate);

        // Verify (case 7)
        $this->assertTrue($filmReturned instanceof Film, "Successful call from setRating should return a Film object");
        $rating = $filmReturned->getRating($sourceName);
        $this->assertEquals($newScore, $rating->getYourScore(), "active score");
        $this->assertEquals($todayStr, $rating->getYourRatingDate()->format("Y-m-d"), "active rating date");
        $dbRating = Film::getFilmFromDb($filmId, $username)->getRating($sourceName);
        $this->assertEquals($rating->getYourScore(), $dbRating->getYourScore(), "active score from db");
        $this->assertEquals($rating->getYourRatingDate()->format("Y-m-d"), $dbRating->getYourRatingDate()->format("Y-m-d"), "active rating date from db");
        $dbArchive = Rating::getInactiveRatingsFromDb($username, $sourceName, $filmId);
        $this->assertCount(1, $dbArchive, "Archive count");
        $dbArchivedRating = $dbArchive[0];
        $this->assertEquals($existingArchive[1]["score"], $dbArchivedRating->getYourScore(), "archived score");
        $this->assertEquals($existingArchive[1]["date"], $dbArchivedRating->getYourRatingDate()->format("Y-m-d"), "archived rating date");

        // Create/Update Use cases (dateStr=null, scores 1 through 10):
        // 8) For all other cases with newDate=null and score range 1-10: Archive the existing and create the new active rating with current date
        // 8a) With an active rating newDate=null and score range 1-10

        $existingActiveScore = $defaultActiveScore;
        $existingActiveDateStr = $defaultActiveDateStr;
        $existingArchive = $defaultArchive;
        $newScore = $defaultNewScore;
        $newDate = null;

        $this->deleteAllRatingsForUserFilm($username, $sourceName, $filmId);
        setRating($filmId, $existingActiveScore, $existingActiveDateStr);
        setRating($filmId, $archive0Score, $archive0Date);
        setRating($filmId, $archive1Score, $archive1Date);

        // Test (case 8a)
        $filmReturned = setRating($filmId, $newScore, $newDate);

        // Verify (case 8a)
        $this->assertTrue($filmReturned instanceof Film, "Successful call from setRating should return a Film object");
        $rating = $filmReturned->getRating($sourceName);
        $this->assertEquals($newScore, $rating->getYourScore(), "active score");
        $this->assertEquals($todayStr, $rating->getYourRatingDate()->format("Y-m-d"), "active rating date");
        $dbRating = Film::getFilmFromDb($filmId, $username)->getRating($sourceName);
        $this->assertEquals($rating->getYourScore(), $dbRating->getYourScore(), "active score from db");
        $this->assertEquals($rating->getYourRatingDate()->format("Y-m-d"), $dbRating->getYourRatingDate()->format("Y-m-d"), "active rating date from db");
        $dbArchive = Rating::getInactiveRatingsFromDb($username, $sourceName, $filmId);
        $this->assertCount(3, $dbArchive, "Archive count");
        $dbArchivedRating = $dbArchive[0];
        $this->assertEquals($existingActiveScore, $dbArchivedRating->getYourScore(), "archived score");
        $this->assertEquals($existingActiveDateStr, $dbArchivedRating->getYourRatingDate()->format("Y-m-d"), "archived rating date");
        $dbArchivedRating = $dbArchive[1];
        $this->assertEquals($existingArchive[0]["score"], $dbArchivedRating->getYourScore(), "archived score");
        $this->assertEquals($existingArchive[0]["date"], $dbArchivedRating->getYourRatingDate()->format("Y-m-d"), "archived rating date");
        $dbArchivedRating = $dbArchive[2];
        $this->assertEquals($existingArchive[1]["score"], $dbArchivedRating->getYourScore(), "archived score");
        $this->assertEquals($existingArchive[1]["date"], $dbArchivedRating->getYourRatingDate()->format("Y-m-d"), "archived rating date");

        // Create/Update Use cases (dateStr=null, scores 1 through 10):
        // 8) For all other cases with newDate=null and score range 1-10: Archive the existing and create the new active rating with current date
        // 8b) Without an active rating newDate=null and score range 1-10

        $existingActiveScore = $defaultActiveScore;
        $existingActiveDateStr = $defaultActiveDateStr;
        $existingArchive = $defaultArchive;
        $newScore = $defaultNewScore;
        $newDate = null;

        $this->deleteAllRatingsForUserFilm($username, $sourceName, $filmId);
        setRating($filmId, $existingActiveScore, $existingActiveDateStr); // active rating (to be deleted before the test)
        setRating($filmId, $archive0Score, $archive0Date);
        setRating($filmId, $archive1Score, $archive1Date);
        $deleteSuccess = $this->deleteOneRating($username, $sourceName, $filmId, $existingActiveDateStr); // The active rating
        $this->assertTrue($deleteSuccess, "Failed delete the active rating before testing case 8b");

        // Test (case 8b)
        $filmReturned = setRating($filmId, $newScore, $newDate);

        // Verify (case 8b)
        $this->assertTrue($filmReturned instanceof Film, "Successful call from setRating should return a Film object");
        $rating = $filmReturned->getRating($sourceName);
        $this->assertEquals($newScore, $rating->getYourScore(), "active score");
        $this->assertEquals($todayStr, $rating->getYourRatingDate()->format("Y-m-d"), "active rating date");
        $dbRating = Film::getFilmFromDb($filmId, $username)->getRating($sourceName);
        $this->assertEquals($rating->getYourScore(), $dbRating->getYourScore(), "active score from db");
        $this->assertEquals($rating->getYourRatingDate()->format("Y-m-d"), $dbRating->getYourRatingDate()->format("Y-m-d"), "active rating date from db");
        $dbArchive = Rating::getInactiveRatingsFromDb($username, $sourceName, $filmId);
        $this->assertCount(2, $dbArchive, "Archive count");
        $dbArchivedRating = $dbArchive[0];
        $this->assertEquals($existingArchive[0]["score"], $dbArchivedRating->getYourScore(), "archived score");
        $this->assertEquals($existingArchive[0]["date"], $dbArchivedRating->getYourRatingDate()->format("Y-m-d"), "archived rating date");
        $dbArchivedRating = $dbArchive[1];
        $this->assertEquals($existingArchive[1]["score"], $dbArchivedRating->getYourScore(), "archived score");
        $this->assertEquals($existingArchive[1]["date"], $dbArchivedRating->getYourRatingDate()->format("Y-m-d"), "archived rating date");

        // Delete Use cases (score 0):
        // 9) No matching date and no existing active rating: no nothing

        $existingActiveScore = $defaultActiveScore;
        $existingActiveDateStr = $defaultActiveDateStr;
        $existingArchive = $defaultArchive;
        $deleteScore = 0;
        $deleteDate = "2021-06-01"; // Should not match any existing ratings

        $this->deleteAllRatingsForUserFilm($username, $sourceName, $filmId);
        setRating($filmId, $existingActiveScore, $existingActiveDateStr);
        setRating($filmId, $archive0Score, $archive0Date);
        setRating($filmId, $archive1Score, $archive1Date);

        // Test (case 9)
        $filmReturned = setRating($filmId, $deleteScore, $deleteDate);

        // Verify (case 9)
        $this->assertTrue($filmReturned instanceof Film, "Successful call from setRating should return a Film object");
        $rating = $filmReturned->getRating($sourceName);
        $this->assertEquals($existingActiveScore, $rating->getYourScore(), "active score");
        $this->assertEquals($existingActiveDateStr, $rating->getYourRatingDate()->format("Y-m-d"), "active rating date");
        $dbRating = Film::getFilmFromDb($filmId, $username)->getRating($sourceName);
        $this->assertEquals($rating->getYourScore(), $dbRating->getYourScore(), "active score from db");
        $this->assertEquals($rating->getYourRatingDate()->format("Y-m-d"), $dbRating->getYourRatingDate()->format("Y-m-d"), "active rating date from db");
        $dbArchive = Rating::getInactiveRatingsFromDb($username, $sourceName, $filmId);
        $this->assertCount(2, $dbArchive, "Archive count");
        $dbArchivedRating = $dbArchive[0];
        $this->assertEquals($existingArchive[0]["score"], $dbArchivedRating->getYourScore(), "archived score");
        $this->assertEquals($existingArchive[0]["date"], $dbArchivedRating->getYourRatingDate()->format("Y-m-d"), "archived rating date");
        $dbArchivedRating = $dbArchive[1];
        $this->assertEquals($existingArchive[1]["score"], $dbArchivedRating->getYourScore(), "archived score");
        $this->assertEquals($existingArchive[1]["date"], $dbArchivedRating->getYourRatingDate()->format("Y-m-d"), "archived rating date");

        // Delete Use cases (score 0):
        // 10) newDate is null OR newDate is the same or newer than the existing active rating: Archive the existing active rating
        // 10a) newDate is null

        $existingActiveScore = $defaultActiveScore;
        $existingActiveDateStr = $defaultActiveDateStr;
        $existingArchive = $defaultArchive;
        $deleteScore = 0;
        $deleteDate = null;

        $this->deleteAllRatingsForUserFilm($username, $sourceName, $filmId);
        setRating($filmId, $existingActiveScore, $existingActiveDateStr);
        setRating($filmId, $archive0Score, $archive0Date);
        setRating($filmId, $archive1Score, $archive1Date);

        // Test (case 10a)
        $filmReturned = setRating($filmId, $deleteScore, $deleteDate);

        // Verify (case 10a)
        $this->assertTrue($filmReturned instanceof Film, "Successful call from setRating should return a Film object");
        $rating = $filmReturned->getRating($sourceName);
        $this->assertNull($rating->getYourScore(), "active score");
        $this->assertNull($rating->getYourRatingDate(), "active rating date");
        $dbRating = Film::getFilmFromDb($filmId, $username)->getRating($sourceName);
        $this->assertEquals($rating->getYourScore(), $dbRating->getYourScore(), "active score from db");
        $this->assertEquals($rating->getYourRatingDate(), $dbRating->getYourRatingDate(), "active rating date from db");
        $dbArchive = Rating::getInactiveRatingsFromDb($username, $sourceName, $filmId);
        $this->assertCount(3, $dbArchive, "Archive count");
        $dbArchivedRating = $dbArchive[0];
        $this->assertEquals($existingActiveScore, $dbArchivedRating->getYourScore(), "archived score");
        $this->assertEquals($existingActiveDateStr, $dbArchivedRating->getYourRatingDate()->format("Y-m-d"), "archived rating date");
        $dbArchivedRating = $dbArchive[1];
        $this->assertEquals($existingArchive[0]["score"], $dbArchivedRating->getYourScore(), "archived score");
        $this->assertEquals($existingArchive[0]["date"], $dbArchivedRating->getYourRatingDate()->format("Y-m-d"), "archived rating date");
        $dbArchivedRating = $dbArchive[2];
        $this->assertEquals($existingArchive[1]["score"], $dbArchivedRating->getYourScore(), "archived score");
        $this->assertEquals($existingArchive[1]["date"], $dbArchivedRating->getYourRatingDate()->format("Y-m-d"), "archived rating date");

        // Delete Use cases (score 0):
        // 10) newDate is null OR newDate is the same or newer than the existing active rating: Archive the existing active rating
        // 10b) newDate is the same as the existing active rating

        $existingActiveScore = $defaultActiveScore;
        $existingActiveDateStr = $defaultActiveDateStr;
        $existingArchive = $defaultArchive;
        $deleteScore = 0;
        $deleteDate = $defaultActiveDateStr;

        $this->deleteAllRatingsForUserFilm($username, $sourceName, $filmId);
        setRating($filmId, $existingActiveScore, $existingActiveDateStr);
        setRating($filmId, $archive0Score, $archive0Date);
        setRating($filmId, $archive1Score, $archive1Date);

        // Test (case 10b)
        $filmReturned = setRating($filmId, $deleteScore, $deleteDate);

        // Verify (case 10b)
        $this->assertTrue($filmReturned instanceof Film, "Successful call from setRating should return a Film object");
        $rating = $filmReturned->getRating($sourceName);
        $this->assertNull($rating->getYourScore(), "active score");
        $this->assertNull($rating->getYourRatingDate(), "active rating date");
        $dbRating = Film::getFilmFromDb($filmId, $username)->getRating($sourceName);
        $this->assertEquals($rating->getYourScore(), $dbRating->getYourScore(), "active score from db");
        $this->assertEquals($rating->getYourRatingDate(), $dbRating->getYourRatingDate(), "active rating date from db");
        $dbArchive = Rating::getInactiveRatingsFromDb($username, $sourceName, $filmId);
        $this->assertCount(3, $dbArchive, "Archive count");
        $dbArchivedRating = $dbArchive[0];
        $this->assertEquals($existingActiveScore, $dbArchivedRating->getYourScore(), "archived score");
        $this->assertEquals($existingActiveDateStr, $dbArchivedRating->getYourRatingDate()->format("Y-m-d"), "archived rating date");
        $dbArchivedRating = $dbArchive[1];
        $this->assertEquals($existingArchive[0]["score"], $dbArchivedRating->getYourScore(), "archived score");
        $this->assertEquals($existingArchive[0]["date"], $dbArchivedRating->getYourRatingDate()->format("Y-m-d"), "archived rating date");
        $dbArchivedRating = $dbArchive[2];
        $this->assertEquals($existingArchive[1]["score"], $dbArchivedRating->getYourScore(), "archived score");
        $this->assertEquals($existingArchive[1]["date"], $dbArchivedRating->getYourRatingDate()->format("Y-m-d"), "archived rating date");

        // Delete Use cases (score 0):
        // 10) newDate is null OR newDate is the same or newer than the existing active rating: Archive the existing active rating
        // 10c) Newer than the existing active rating

        $existingActiveScore = $defaultActiveScore;
        $existingActiveDateStr = $defaultActiveDateStr;
        $existingArchive = $defaultArchive;
        $deleteScore = 0;
        $deleteDate = $todayStr;

        $this->deleteAllRatingsForUserFilm($username, $sourceName, $filmId);
        setRating($filmId, $existingActiveScore, $existingActiveDateStr);
        setRating($filmId, $archive0Score, $archive0Date);
        setRating($filmId, $archive1Score, $archive1Date);

        // Test (case 10c)
        $filmReturned = setRating($filmId, $deleteScore, $deleteDate);

        // Verify (case 10c)
        $this->assertTrue($filmReturned instanceof Film, "Successful call from setRating should return a Film object");
        $rating = $filmReturned->getRating($sourceName);
        $this->assertNull($rating->getYourScore(), "active score");
        $this->assertNull($rating->getYourRatingDate(), "active rating date");
        $dbRating = Film::getFilmFromDb($filmId, $username)->getRating($sourceName);
        $this->assertEquals($rating->getYourScore(), $dbRating->getYourScore(), "active score from db");
        $this->assertEquals($rating->getYourRatingDate(), $dbRating->getYourRatingDate(), "active rating date from db");
        $dbArchive = Rating::getInactiveRatingsFromDb($username, $sourceName, $filmId);
        $this->assertCount(3, $dbArchive, "Archive count");
        $dbArchivedRating = $dbArchive[0];
        $this->assertEquals($existingActiveScore, $dbArchivedRating->getYourScore(), "archived score");
        $this->assertEquals($existingActiveDateStr, $dbArchivedRating->getYourRatingDate()->format("Y-m-d"), "archived rating date");
        $dbArchivedRating = $dbArchive[1];
        $this->assertEquals($existingArchive[0]["score"], $dbArchivedRating->getYourScore(), "archived score");
        $this->assertEquals($existingArchive[0]["date"], $dbArchivedRating->getYourRatingDate()->format("Y-m-d"), "archived rating date");
        $dbArchivedRating = $dbArchive[2];
        $this->assertEquals($existingArchive[1]["score"], $dbArchivedRating->getYourScore(), "archived score");
        $this->assertEquals($existingArchive[1]["date"], $dbArchivedRating->getYourRatingDate()->format("Y-m-d"), "archived rating date");

        // Delete Use cases (score 0):
        // 11) Same date as an existing archived rating: Delete

        $existingActiveScore = $defaultActiveScore;
        $existingActiveDateStr = $defaultActiveDateStr;
        $existingArchive = $defaultArchive;
        $deleteScore = 0;
        $deleteDate = $existingArchive[1]["date"];

        $this->deleteAllRatingsForUserFilm($username, $sourceName, $filmId);
        setRating($filmId, $existingActiveScore, $existingActiveDateStr);
        setRating($filmId, $archive0Score, $archive0Date);
        setRating($filmId, $archive1Score, $archive1Date);

        // Test (case 11)
        $filmReturned = setRating($filmId, $deleteScore, $deleteDate);

        // Verify (case 11)
        $this->assertTrue($filmReturned instanceof Film, "Successful call from setRating should return a Film object");
        $rating = $filmReturned->getRating($sourceName);
        $this->assertEquals($existingActiveScore, $rating->getYourScore(), "active score");
        $this->assertEquals($existingActiveDateStr, $rating->getYourRatingDate()->format("Y-m-d"), "active rating date");
        $dbRating = Film::getFilmFromDb($filmId, $username)->getRating($sourceName);
        $this->assertEquals($rating->getYourScore(), $dbRating->getYourScore(), "active score from db");
        $this->assertEquals($rating->getYourRatingDate()->format("Y-m-d"), $dbRating->getYourRatingDate()->format("Y-m-d"), "active rating date from db");
        $dbArchive = Rating::getInactiveRatingsFromDb($username, $sourceName, $filmId);
        $this->assertCount(1, $dbArchive, "Archive count");
        $dbArchivedRating = $dbArchive[0];
        $this->assertEquals($existingArchive[0]["score"], $dbArchivedRating->getYourScore(), "archived score");
        $this->assertEquals($existingArchive[0]["date"], $dbArchivedRating->getYourRatingDate()->format("Y-m-d"), "archived rating date");

    }
}

?>
