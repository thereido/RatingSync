<?php
/**
 * api.php PHPUnit
 */
namespace RatingSync;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "Constants.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "ajax" . DIRECTORY_SEPARATOR . "api.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "Film.php";

require_once "DatabaseTest.php";
require_once "MainTest.php";

class ApiTest extends RatingSyncTestCase
{
    protected function setUp(): void
    {
        parent::setup();
        //$this->verbose = true;
    }
    
    public static function setupFilms()
    {
        $errorFree = true;
        
        $searchTerms = array();
        $searchTerms['sourceName'] = Constants::SOURCE_IMDB;

        // Frozen 2013
        $searchTerms['uniqueName'] = "tt2294629";
        $response = search($searchTerms, Constants::TEST_RATINGSYNC_USERNAME);
        if (empty($response)) {
            $errorFree = false;
        }

        // Inception 2010
        $searchTerms['uniqueName'] = "tt1375666";
        $response = search($searchTerms, Constants::TEST_RATINGSYNC_USERNAME);
        if (empty($response)) {
            $errorFree = false;
        }

        // Interstellar 2014
        $searchTerms['uniqueName'] = "tt0816692";
        $response = search($searchTerms, Constants::TEST_RATINGSYNC_USERNAME);
        if (empty($response)) {
            $errorFree = false;
        }

        return $errorFree;
    }

    public function testSetup()
    {$this->start(__CLASS__, __FUNCTION__);
        
        DatabaseTest::resetDb();
        $this->assertTrue(self::setupFilms(), "setupFilms() failed");
    }

    public function testApi_getSearchFilm()
    {$this->start(__CLASS__, __FUNCTION__);
    
        // Setup
        $uniqueName = "tt0457433";
        $uniqueEpisode = NULL;
        $uniqueAlt = NULL;
        $title = NULL;
        $year = NULL;
        $season = NULL;
        $episodeNumber = NULL;
        $episodeTitle = NULL;
        $contentType = NULL;
        $sourceName = "IM";

        $searchTerms = array();
        $searchTerms['q'] = $uniqueName;
        $searchTerms['ue'] = $uniqueEpisode;
        $searchTerms['ua'] = $uniqueAlt;
        $searchTerms['t'] = $title;
        $searchTerms['y'] = $year;
        $searchTerms['s'] = $season;
        $searchTerms['en'] = $episodeNumber;
        $searchTerms['et'] = $episodeTitle;
        $searchTerms['ct'] = $contentType;
        $searchTerms['source'] = $sourceName;

        // Test
        $responseJson = api_getSearchFilm(Constants::TEST_RATINGSYNC_USERNAME, $searchTerms);

        // Verify
        $this->assertFalse(empty($responseJson));
        $obj = json_decode($responseJson);
        
        $this->assertTrue(true); // Making sure we made it this far
    }

    /**
     * @covers \RatingSync\api_getFilm
     * @depends testSetup
     */
    public function testApi_getFilm()
    {$this->start(__CLASS__, __FUNCTION__);
    
        // Setup
        $uniqueName = "tt1277737";
        $title = "The Stoning of Soraya M.";
        $rsonly = "0";

        $get = array(); // HTML submit $_GET
        $get['imdb'] = $uniqueName;
        $get['rsonly'] = $rsonly;

        // Test
        $responseJson = api_getFilm(Constants::TEST_RATINGSYNC_USERNAME, $get);

        // Verify
        $this->assertFalse(empty($responseJson));
        $obj = json_decode($responseJson);
        $this->assertEquals($title, $obj->title);
        $db = getDatabase();
        $query = "SELECT title FROM film, film_source WHERE uniqueName='$uniqueName' AND source_name='IMDb' AND id=film_id";
        $result = $db->query($query);
        $this->assertEquals(1, $result->rowCount(), "There should be one result");
        $titleDb = $result->fetch()['title'];
        $this->assertEquals($title, $titleDb, "Title from the db should match '$title'");
    }

    public function testApi_getFilmFailure()
    {$this->start(__CLASS__, __FUNCTION__);
    
        // Setup
        $uniqueName = "tt0042897";

        $get = array(); // HTML submit $_GET
        $get['imdb'] = $uniqueName;

        // Test
        $responseJson = api_getFilm(Constants::TEST_RATINGSYNC_USERNAME, $get);

        // Verify
        $this->assertFalse(empty($responseJson));
        $obj = json_decode($responseJson);
        $this->assertEquals("false", $obj->Success);
    }
    
    /**
     * - Get 2 films by Film Ids existing in the db
     *
     * Expect
     *   - Response is JSON with 2 films
     *   - Check the titles
     *
     * @covers  \RatingSync\api_getFilms()
     * @depends testSetup
     */
    public function testApi_getFilmsFilmIds()
    {$this->start(__CLASS__, __FUNCTION__);

        // Set up
        $filmId1 = "1"; // Frozen 2013
        $filmId2 = "2"; // Inception 2010
        $params = array();
        $params["id"] = "$filmId1 $filmId2";

        // Test
        $responseJson = api_getFilms(Constants::TEST_RATINGSYNC_USERNAME, $params);

        // Verify
        $this->assertFalse(empty($responseJson));
        $obj = json_decode($responseJson);
        $films = $obj->films;
        $this->assertEquals(2, count($films), "Response should have 2 films");
        $this->assertEquals("Frozen", $films[0]->title, "First title");
        $this->assertEquals("Inception", $films[1]->title, "Second title");
    }
    
    /**
     * - Get 2 films by IMDb unique names existing in the db
     *
     * Expect
     *   - Response is JSON with 2 films
     *   - Check the titles
     *
     * @covers  \RatingSync\api_getFilms()
     * @depends testSetup
     */
    public function testApi_getFilmsImdbIds()
    {$this->start(__CLASS__, __FUNCTION__);

        // Set up
        $imdbId1 = "tt2294629_" . Film::CONTENT_FILM; // Frozen 2013
        $imdbId2 = "tt1375666_" . Film::CONTENT_FILM; // Inception 2010
        $params = array();
        $params["imdbcts"] = "$imdbId1 $imdbId2";

        // Test
        $responseJson = api_getFilms(Constants::TEST_RATINGSYNC_USERNAME, $params);

        // Verify
        $this->assertFalse(empty($responseJson));
        $obj = json_decode($responseJson);
        $films = $obj->films;
        $this->assertEquals(2, count($films), "Response should have 2 films");
        $this->assertEquals("Frozen", $films[0]->title, "First title");
        $this->assertEquals("Inception", $films[1]->title, "Second title");
    }
    
    /**
     * - Use 2 bogus Film Ids
     *
     * Expect
     *   - Response is JSON with 0 films
     *
     * @covers  \RatingSync\api_getFilms()
     * @depends testSetup
     */
    public function testApi_getFilmsFilmIdsNoResult()
    {$this->start(__CLASS__, __FUNCTION__);

        // Set up
        $params = array();
        $params["id"] = "9999991 9999992";

        // Test
        $responseJson = api_getFilms(Constants::TEST_RATINGSYNC_USERNAME, $params);

        // Verify
        $this->assertFalse(empty($responseJson));
        $obj = json_decode($responseJson);
        $films = $obj->films;
        $this->assertEquals(0, count($films), "Response should have 0 films");
    }
    
    /**
     * - Use 2 bogus IMDb unique names
     *
     * Expect
     *   - Response is JSON with 0 films
     *
     * @covers  \RatingSync\api_getFilms()
     * @depends testSetup
     */
    public function testApi_getFilmsImdbIdsNoResult()
    {$this->start(__CLASS__, __FUNCTION__);

        // Set up
        $imdbId1 = "9999991_" . Film::CONTENT_FILM;
        $imdbId2 = "9999992_" . Film::CONTENT_FILM;
        $params = array();
        $params["imdbcts"] = "$imdbId1 $imdbId2";

        // Test
        $responseJson = api_getFilms(Constants::TEST_RATINGSYNC_USERNAME, $params);

        // Verify
        $this->assertFalse(empty($responseJson));
        $obj = json_decode($responseJson);
        $films = $obj->films;
        $this->assertEquals(0, count($films), "Response should have 0 films");
    }
    
    /**
     * - Get 2 films by Film Ids existing in the db and 1 bogus one
     *
     * Expect
     *   - Response is JSON with 2 films
     *   - Check the titles
     *
     * @covers  \RatingSync\api_getFilms()
     * @depends testSetup
     */
    public function testApi_getFilmsFilmIdsSomeResultsSomeMiss()
    {$this->start(__CLASS__, __FUNCTION__);

        // Set up
        $filmId1 = "1"; // Frozen 2013
        $filmId2 = "2"; // Inception 2010
        $params = array();
        $params["id"] = "$filmId1 $filmId2 9999991";

        // Test
        $responseJson = api_getFilms(Constants::TEST_RATINGSYNC_USERNAME, $params);

        // Verify
        $this->assertFalse(empty($responseJson));
        $obj = json_decode($responseJson);
        $films = $obj->films;
        $this->assertEquals(2, count($films), "Response should have 2 films");
        $this->assertEquals("Frozen", $films[0]->title, "First title");
        $this->assertEquals("Inception", $films[1]->title, "Second title");
    }
    
    /**
     * - Get 2 films by IMDb unique names existing in the db and 1 bogus one
     *
     * Expect
     *   - Response is JSON with 2 films
     *   - Check the titles
     *
     * @covers  \RatingSync\api_getFilms()
     * @depends testSetup
     */
    public function testApi_getFilmsImdbsIdsSomeResultsSomeMiss()
    {$this->start(__CLASS__, __FUNCTION__);

        // Set up
        $imdbId1 = "tt2294629_" . Film::CONTENT_FILM; // Frozen 2013
        $imdbId2 = "tt1375666_" . Film::CONTENT_FILM; // Inception 2010
        $badImdbId = "9999991_" . Film::CONTENT_FILM;
        $params = array();
        $params["imdbcts"] = "$imdbId1 $imdbId2 $badImdbId";

        // Test
        $responseJson = api_getFilms(Constants::TEST_RATINGSYNC_USERNAME, $params);

        // Verify
        $this->assertFalse(empty($responseJson));
        $obj = json_decode($responseJson);
        $films = $obj->films;
        $this->assertEquals(2, count($films), "Response should have 2 films");
        $this->assertEquals("Frozen", $films[0]->title, "First title");
        $this->assertEquals("Inception", $films[1]->title, "Second title");
    }
    
    /**
     * - Get
     *     - 2 films by Film Ids existing in the db
     *     - 1 film by Film bogus Id
     *     - 1 film by IMDb unique names existing in the db
     *
     * Expect
     *   - Response is JSON with 3 films
     *   - Check the titles
     *
     * @covers  \RatingSync\api_getFilms()
     * @depends testSetup
     */
    public function testApi_getFilms()
    {$this->start(__CLASS__, __FUNCTION__);

        // Set up
        $filmId1 = "1"; // Frozen 2013
        $imdbId2 = "tt1375666_" . Film::CONTENT_FILM; // Inception 2010
        $filmId3 = "3"; // Interstellar 2014
        $params = array();
        $params["id"] = "$filmId1 9999991 $filmId3";
        $params["imdbcts"] = "$imdbId2";

        // Test
        $responseJson = api_getFilms(Constants::TEST_RATINGSYNC_USERNAME, $params);

        // Verify
        $this->assertFalse(empty($responseJson));
        $obj = json_decode($responseJson);
        $films = $obj->films;
        $this->assertEquals(3, count($films), "Response should have 3 films");
        $titles = array();
        foreach ($films as $film) {
            $titles[] = $film->title;
        }
        sort($titles, SORT_STRING);
        $this->assertEquals(array("Frozen", "Inception", "Interstellar"), $titles, "Titles in response should match");
    }

    /**
     * Movie, Series, and Episode
     *   - The film should be in the DB with title & year
     *   - For the episode it should also have a parent id, season number and episode number
     *   - The film should have an IMDb source
     *   - The film should not have a Default Data API source (TMDbApi, OmdbApi, etc)
     *   - Set api_getFilm param un, ct
     *   - Set api_getFilm param pid, s, e params for an episode
     *   - Set api_getFilm param rsonly=0 (the film is in the RS DB already)
     *
     * Expect
     *   - The film in the DB has a source for default data API
     *   - Film object from the DB has the correct default API source uniqueName
     *
     * @covers \RatingSync\api_getFilm
     * @depends testSetup
     * @depends testApi_getFilm
     */
    public function testApi_getFilmForNewSourceByUniqueName()
    {$this->start(__CLASS__, __FUNCTION__);
    
        // Setup
        DatabaseTest::resetDb();
        $constants = MainTest::getConstants();
        $sourceName = $constants["sourceName"];
        $rsonly = "0";

        // Movie
            // Setup
        $imdbId = $constants["filmImdbId"];
        $title = $constants["filmTitle"];
        $year = $constants["filmYear"];
        $uniqueName = $constants["filmUniqueName"];

        $film = new Film();
        $film->setUniqueName($imdbId, Constants::SOURCE_IMDB);
        $film->setTitle($title);
        $film->setYear($year);
        $film->saveToDb();
        $film = Film::getFilmFromDbByUniqueName($imdbId, Constants::SOURCE_IMDB);
        $filmId = $film->getId();

        $get = array(); // HTML submit $_GET
        $get['un'] = $uniqueName;
        $get['ct'] = Film::CONTENT_FILM;
        $get['rsonly'] = $rsonly;

            // Test Movie
        $responseJson = api_getFilm(Constants::TEST_RATINGSYNC_USERNAME, $get);

            // Verify Movie
        $film = Film::getFilmFromDb($filmId);
        $this->assertFalse(empty($film), "Film from the DB should not be empty");
        $this->assertEquals($uniqueName, $film->getUniqueName($sourceName), "uniqueName for $sourceName");
    
        // Series
        $imdbId = $constants["seriesImdbId"];
        $title = $constants["seriesTitle"];
        $year = $constants["seriesYear"];
        $uniqueName = $constants["seriesUniqueName"];

        $film = new Film();
        $film->setUniqueName($imdbId, Constants::SOURCE_IMDB);
        $film->setTitle($title);
        $film->setYear($year);
        $film->saveToDb();
        $film = Film::getFilmFromDbByUniqueName($imdbId, Constants::SOURCE_IMDB);
        $filmId = $film->getId();
        $seriesFilmId = $filmId;

        $get = array(); // HTML submit $_GET
        $get['un'] = $uniqueName;
        $get['ct'] = Film::CONTENT_TV_SERIES;
        $get['rsonly'] = $rsonly;

            // Test Series
        $responseJson = api_getFilm(Constants::TEST_RATINGSYNC_USERNAME, $get);

            // Verify Series
        $film = Film::getFilmFromDb($filmId);
        $this->assertFalse(empty($film), "Film from the DB should not be empty");
        $this->assertEquals($uniqueName, $film->getUniqueName($sourceName), "uniqueName for $sourceName");
    
        // Episode
        $imdbId = $constants["episodeImdbId"];
        $title = $constants["episodeTitle"];
        $year = $constants["episodeYear"];
        $seasonNum = $constants["episodeSeasonNum"];
        $episodeNum = $constants["episodeEpisodeNum"];
        $uniqueName = $constants["episodeUniqueName"];

        $film = new Film();
        $film->setUniqueName($imdbId, Constants::SOURCE_IMDB);
        $film->setParentId($seriesFilmId);
        $film->setTitle($title);
        $film->setYear($year);
        $film->setSeason($seasonNum);
        $film->setEpisodeNumber($episodeNum);
        $film->saveToDb();
        $film = Film::getFilmFromDbByUniqueName($imdbId, Constants::SOURCE_IMDB);
        $filmId = $film->getId();

        $get = array(); // HTML submit $_GET
        $get['pid'] = $seriesFilmId;
        $get['un'] = $uniqueName;
        $get['s'] = $seasonNum;
        $get['e'] = $episodeNum;
        $get['ct'] = Film::CONTENT_TV_EPISODE;
        $get['rsonly'] = $rsonly;

            // Test Series
        $responseJson = api_getFilm(Constants::TEST_RATINGSYNC_USERNAME, $get);

            // Verify Series
        $film = Film::getFilmFromDb($filmId);
        $this->assertFalse(empty($film), "Film from the DB should not be empty");
        $this->assertEquals($uniqueName, $film->getUniqueName($sourceName), "uniqueName for $sourceName");
    }

    /**
     * Movie, Series, and Episode
     *   - The film should be in the DB with title & year
     *   - For the episode it should also have a parent id, season number and episode number
     *   - The film should have an IMDb source
     *   - The film should not have a Default Data API source (TMDbApi, OmdbApi, etc)
     *   - Set api_getFilm param imdb, ct
     *   - Set api_getFilm param pid, s, e params for an episode
     *   - Set api_getFilm param rsonly=0 (the film is in the RS DB already)
     *
     * Expect
     *   - The film in the DB has a source for default data API
     *   - Film object from the DB has the correct default API source uniqueName
     *
     * @covers \RatingSync\api_getFilm
     * @depends testSetup
     * @depends testApi_getFilm
     */
    public function testApi_getFilmForNewSourceByImdbId()
    {$this->start(__CLASS__, __FUNCTION__);
    
        // Setup
        DatabaseTest::resetDb();
        $constants = MainTest::getConstants();
        $sourceName = $constants["sourceName"];
        $rsonly = "0";

        // Movie
            // Setup
        $imdbId = $constants["filmImdbId"];
        $title = $constants["filmTitle"];
        $year = $constants["filmYear"];
        $uniqueName = $constants["filmUniqueName"];

        $film = new Film();
        $film->setUniqueName($imdbId, Constants::SOURCE_IMDB);
        $film->setTitle($title);
        $film->setYear($year);
        $film->saveToDb();
        $film = Film::getFilmFromDbByUniqueName($imdbId, Constants::SOURCE_IMDB);
        $filmId = $film->getId();

        $get = array(); // HTML submit $_GET
        $get['imdb'] = $imdbId;
        $get['ct'] = Film::CONTENT_FILM;
        $get['rsonly'] = $rsonly;

            // Test Movie
        $responseJson = api_getFilm(Constants::TEST_RATINGSYNC_USERNAME, $get);

            // Verify Movie
        $film = Film::getFilmFromDb($filmId);
        $this->assertFalse(empty($film), "Film from the DB should not be empty");
        $this->assertEquals($uniqueName, $film->getUniqueName($sourceName), "uniqueName for $sourceName");
    
        // Series
        $imdbId = $constants["seriesImdbId"];
        $title = $constants["seriesTitle"];
        $year = $constants["seriesYear"];
        $uniqueName = $constants["seriesUniqueName"];

        $film = new Film();
        $film->setUniqueName($imdbId, Constants::SOURCE_IMDB);
        $film->setTitle($title);
        $film->setYear($year);
        $film->saveToDb();
        $film = Film::getFilmFromDbByUniqueName($imdbId, Constants::SOURCE_IMDB);
        $filmId = $film->getId();
        $seriesFilmId = $filmId;

        $get = array(); // HTML submit $_GET
        $get['imdb'] = $imdbId;
        $get['ct'] = Film::CONTENT_TV_SERIES;
        $get['rsonly'] = $rsonly;

            // Test Series
        $responseJson = api_getFilm(Constants::TEST_RATINGSYNC_USERNAME, $get);

            // Verify Series
        $film = Film::getFilmFromDb($filmId);
        $this->assertFalse(empty($film), "Film from the DB should not be empty");
        $this->assertEquals($uniqueName, $film->getUniqueName($sourceName), "uniqueName for $sourceName");
    
        // Episode
        $imdbId = $constants["episodeImdbId"];
        $title = $constants["episodeTitle"];
        $year = $constants["episodeYear"];
        $seasonNum = $constants["episodeSeasonNum"];
        $episodeNum = $constants["episodeEpisodeNum"];
        $uniqueName = $constants["episodeUniqueName"];

        $film = new Film();
        $film->setUniqueName($imdbId, Constants::SOURCE_IMDB);
        $film->setParentId($seriesFilmId);
        $film->setTitle($title);
        $film->setYear($year);
        $film->setSeason($seasonNum);
        $film->setEpisodeNumber($episodeNum);
        $film->saveToDb();
        $film = Film::getFilmFromDbByUniqueName($imdbId, Constants::SOURCE_IMDB);
        $filmId = $film->getId();

        $get = array(); // HTML submit $_GET
        $get['pid'] = $seriesFilmId;
        $get['imdb'] = $imdbId;
        $get['s'] = $seasonNum;
        $get['e'] = $episodeNum;
        $get['ct'] = Film::CONTENT_TV_EPISODE;
        $get['rsonly'] = $rsonly;

            // Test Series
        $responseJson = api_getFilm(Constants::TEST_RATINGSYNC_USERNAME, $get);

            // Verify Series
        $film = Film::getFilmFromDb($filmId);
        $this->assertFalse(empty($film), "Film from the DB should not be empty");
        $this->assertEquals($uniqueName, $film->getUniqueName($sourceName), "uniqueName for $sourceName");
    }

    /**
     * Movie, Series, and Episode
     *   - The film should be in the DB with title & year
     *   - For the episode it should also have a parent id, season number and episode number
     *   - The film should have an IMDb source
     *   - The film should not have a Default Data API source (TMDbApi, OmdbApi, etc)
     *   - Set api_getFilm param id
     *   - Set api_getFilm param rsonly=0 (the film is in the RS DB already)
     *
     * Expect
     *   - The film in the DB has a source for default data API
     *   - Film object from the DB has the correct default API source uniqueName
     *
     * @covers \RatingSync\api_getFilm
     * @depends testSetup
     * @depends testApi_getFilm
     */
    public function testApi_getFilmForNewSourceByFilmId()
    {$this->start(__CLASS__, __FUNCTION__);
    
        // Setup
        DatabaseTest::resetDb();
        $constants = MainTest::getConstants();
        $sourceName = $constants["sourceName"];
        $rsonly = "0";

        // Movie
            // Setup
        $imdbId = $constants["filmImdbId"];
        $title = $constants["filmTitle"];
        $year = $constants["filmYear"];
        $uniqueName = $constants["filmUniqueName"];

        $film = new Film();
        $film->setUniqueName($imdbId, Constants::SOURCE_IMDB);
        $film->setTitle($title);
        $film->setYear($year);
        $film->saveToDb();
        $film = Film::getFilmFromDbByUniqueName($imdbId, Constants::SOURCE_IMDB);
        $filmId = $film->getId();

        $get = array(); // HTML submit $_GET
        $get['id'] = $filmId;
        $get['rsonly'] = $rsonly;

            // Test Movie
        $responseJson = api_getFilm(Constants::TEST_RATINGSYNC_USERNAME, $get);

            // Verify Movie
        $film = Film::getFilmFromDb($filmId);
        $this->assertFalse(empty($film), "Film from the DB should not be empty");
        $this->assertEquals($uniqueName, $film->getUniqueName($sourceName), "uniqueName for $sourceName");
    
        // Series
        $imdbId = $constants["seriesImdbId"];
        $title = $constants["seriesTitle"];
        $year = $constants["seriesYear"];
        $uniqueName = $constants["seriesUniqueName"];

        $film = new Film();
        $film->setUniqueName($imdbId, Constants::SOURCE_IMDB);
        $film->setTitle($title);
        $film->setYear($year);
        $film->saveToDb();
        $film = Film::getFilmFromDbByUniqueName($imdbId, Constants::SOURCE_IMDB);
        $filmId = $film->getId();
        $seriesFilmId = $filmId;

        $get = array(); // HTML submit $_GET
        $get['id'] = $filmId;
        $get['rsonly'] = $rsonly;

            // Test Series
        $responseJson = api_getFilm(Constants::TEST_RATINGSYNC_USERNAME, $get);

            // Verify Series
        $film = Film::getFilmFromDb($filmId);
        $this->assertFalse(empty($film), "Film from the DB should not be empty");
        $this->assertEquals($uniqueName, $film->getUniqueName($sourceName), "uniqueName for $sourceName");
    
        // Episode
        $imdbId = $constants["episodeImdbId"];
        $title = $constants["episodeTitle"];
        $year = $constants["episodeYear"];
        $seasonNum = $constants["episodeSeasonNum"];
        $episodeNum = $constants["episodeEpisodeNum"];
        $uniqueName = $constants["episodeUniqueName"];

        $film = new Film();
        $film->setUniqueName($imdbId, Constants::SOURCE_IMDB);
        $film->setParentId($seriesFilmId);
        $film->setTitle($title);
        $film->setYear($year);
        $film->setSeason($seasonNum);
        $film->setEpisodeNumber($episodeNum);
        $film->saveToDb();
        $film = Film::getFilmFromDbByUniqueName($imdbId, Constants::SOURCE_IMDB);
        $filmId = $film->getId();

        $get = array(); // HTML submit $_GET
        $get['id'] = $filmId;
        $get['rsonly'] = $rsonly;

            // Test Series
        $responseJson = api_getFilm(Constants::TEST_RATINGSYNC_USERNAME, $get);

            // Verify Series
        $film = Film::getFilmFromDb($filmId);
        $this->assertFalse(empty($film), "Film from the DB should not be empty");
        $this->assertEquals($uniqueName, $film->getUniqueName($sourceName), "uniqueName for $sourceName");
    }

    /**
     * - 1) Theme does not exist
     *      - Expect: success=false
     * - 2) Theme is not enabled
     *      - Expect: success=false
     *
     * @covers  \RatingSync\api_setTheme()
     * @depends testSetup
     */
    public function testApi_setThemeInvalidTheme()
    {$this->start(__CLASS__, __FUNCTION__);

        // Set up
        $invalidThemeId = -1;
        $disabledThemeId = 100;

            // 1) Theme does not exist
            //   - Expect: success=false

        // Setup 1
        $get = array(); // HTML submit $_GET
        $get['i'] = $invalidThemeId;

        // Test 1
        $responseJson = api_setTheme(Constants::TEST_RATINGSYNC_USERNAME, $get);

        // Verify 1
        $this->assertFalse(empty($responseJson), "Response should not be empty");
        $obj = json_decode($responseJson);
        $success = $obj->Success;
        $this->assertEquals("false", $success, "Should fail with a non-existing theme");

            // 2) Theme is not enabled
            //   - Expect: success=false

        // Setup 2
        $db = getDatabase();
        $db->exec("INSERT INTO theme (id, name, enabled) VALUES (100, 'test_theme_disabled', false)");
        $get = array(); // HTML submit $_GET
        $get['i'] = $disabledThemeId;

        // Test 2
        $responseJson = api_setTheme(Constants::TEST_RATINGSYNC_USERNAME, $get);

        // Verify 2
        $this->assertFalse(empty($responseJson), "Response should not be empty");
        $obj = json_decode($responseJson);
        $success = $obj->Success;
        $this->assertEquals("false", $success, "Should fail with a disabled theme");

        // Cleanup 2
        $db->exec("DELETE FROM theme WHERE id=$disabledThemeId");
    }

    /**
     * - Set the theme that is the default
     * - User's current theme is null
     *
     * Expect
     *   - Success=true
     *   - User's theme is set in the DB
     *
     * @covers  \RatingSync\api_setTheme()
     * @depends testSetup
     */
    public function testApi_setThemeDefault()
    {$this->start(__CLASS__, __FUNCTION__);

        // Set up
        $defaultThemeId = 1;
        DatabaseTest::resetDb();
        $get = array(); // HTML submit $_GET
        $get['i'] = $defaultThemeId;

        // Test
        $responseJson = api_setTheme(Constants::TEST_RATINGSYNC_USERNAME, $get);

        // Verify
        $this->assertFalse(empty($responseJson), "Response should not be empty");
        $obj = json_decode($responseJson);
        $success = $obj->Success;
        $this->assertEquals("true", $success, "Should succeed");
        $userView = userMgr()->findViewWithUsername(Constants::TEST_RATINGSYNC_USERNAME);
        $this->assertTrue($userView !== false, "Failed to get the user (".Constants::TEST_RATINGSYNC_USERNAME.") from the db");
        $this->assertEquals($defaultThemeId, $userView->getTheme()?->getId(), "The DB does not match the value set");
    }

    /**
     * - User has a theme
     * - Theme is different from the user's theme
     *
     * Expect
     *   - Success=true
     *   - User's theme is changed
     *
     * @covers  \RatingSync\api_setTheme()
     * @depends testSetup
     * @depends testApi_setThemeDefault
     */
    public function testApi_setThemeDifferentId()
    {$this->start(__CLASS__, __FUNCTION__);

        // Set up
        $defaultThemeId = 1;
        $nonDefaultThemeId = 2;
        $get = array(); // HTML submit $_GET
        $get['i'] = $defaultThemeId;
        api_setTheme(Constants::TEST_RATINGSYNC_USERNAME, $get);

        // Test
        $get['i'] = $nonDefaultThemeId;
        $responseJson = api_setTheme(Constants::TEST_RATINGSYNC_USERNAME, $get);

        // Verify
        $this->assertFalse(empty($responseJson), "Response should not be empty");
        $obj = json_decode($responseJson);
        $success = $obj->Success;
        $this->assertEquals("true", $success, "Should succeed");
        $userView = userMgr()->findViewWithUsername(Constants::TEST_RATINGSYNC_USERNAME);
        $this->assertTrue($userView !== false, "Failed to get the user (".Constants::TEST_RATINGSYNC_USERNAME.") from the db");
        $this->assertEquals($nonDefaultThemeId, $userView->getTheme()?->getId(), "The DB does not match the value set");
    }

    /**
     * - Theme is already the user's theme
     *
     * Expect
     *   - Success=true
     *   - No change
     *
     * @covers  \RatingSync\api_setTheme()
     * @depends testSetup
     * @depends testApi_setThemeDifferentId
     */
    public function testApi_setThemeNoChange()
    {$this->start(__CLASS__, __FUNCTION__);

        // Set up
        $nonDefaultThemeId = 2;
        $get = array(); // HTML submit $_GET
        $get['i'] = $nonDefaultThemeId;
        api_setTheme(Constants::TEST_RATINGSYNC_USERNAME, $get);

        // Test
        $responseJson = api_setTheme(Constants::TEST_RATINGSYNC_USERNAME, $get);

        // Verify
        $this->assertFalse(empty($responseJson), "Response should not be empty");
        $obj = json_decode($responseJson);
        $success = $obj->Success;
        $this->assertEquals("true", $success, "Should succeed");
        $userView = userMgr()->findViewWithUsername(Constants::TEST_RATINGSYNC_USERNAME);
        $this->assertTrue($userView !== false, "Failed to get the user (".Constants::TEST_RATINGSYNC_USERNAME.") from the db");
        $this->assertEquals($nonDefaultThemeId, $userView->getTheme()?->getId(), "The DB does not match the value set");
    }

    /**
     * - User is enabled
     * - Theme is different from the user's current theme
     *
     * Expect
     *   - Success=true
     *   - User's theme is changed
     *
     * @covers  \RatingSync\api_setTheme()
     * @depends testSetup
     * @depends testApi_setThemeDifferentId
     */
    public function testApi_setThemeEnabledUser()
    {$this->start(__CLASS__, __FUNCTION__);

        // Set up
        $defaultThemeId = 1;
        $nonDefaultThemeId = 2;
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $userMgr = userMgr();
        $userView = $userMgr->findViewWithUsername($username);
        $userView->enable(true);
        $userMgr->save($userView);
        $get = array(); // HTML submit $_GET
        $get['i'] = $defaultThemeId;
        api_setTheme($username, $get);

        // Test
        $get['i'] = $nonDefaultThemeId;
        $responseJson = api_setTheme($username, $get);

        // Verify
        $this->assertFalse(empty($responseJson), "Response should not be empty");
        $obj = json_decode($responseJson);
        $success = $obj->Success;
        $this->assertEquals("true", $success, "Should succeed");
        $userView = userMgr()->findViewWithUsername($username);
        $this->assertTrue($userView !== false, "Failed to get the user ($username) from the db");
        $this->assertEquals($nonDefaultThemeId, $userView->getTheme()?->getId(), "The DB does not match the value set");
    }
}

?>
