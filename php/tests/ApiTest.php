<?php
/**
 * api.php PHPUnit
 */
namespace RatingSync;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "Constants.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "ajax" . DIRECTORY_SEPARATOR . "api.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "Film.php";

require_once "10DatabaseTest.php";

class ApiTest extends RatingSyncTestCase
{
    public function setUp()
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
    }

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
        $this->assertEquals(1, $result->num_rows, "There should be one result");
        $titleDb = $result->fetch_assoc()['title'];
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
        $imdbId1 = "tt2294629"; // Frozen 2013
        $imdbId2 = "tt1375666"; // Inception 2010
        $params = array();
        $params["imdb"] = "$imdbId1 $imdbId2";

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
        $params = array();
        $params["imdb"] = "9999991 9999992";

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
        $imdbId1 = "tt2294629"; // Frozen 2013
        $imdbId2 = "tt1375666"; // Inception 2010
        $params = array();
        $params["imdb"] = "$imdbId1 $imdbId2 9999991";

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
        $imdbId2 = "tt1375666"; // Inception 2010
        $filmId3 = "3"; // Interstellar 2014
        $params = array();
        $params["id"] = "$filmId1 9999991 $filmId3";
        $params["imdb"] = "$imdbId2";

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
}

?>
