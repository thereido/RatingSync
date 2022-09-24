<?php
/**
 * TmdbApi PHPUnit
 */
namespace RatingSync;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "TmdbApi.php";
require_once "DatabaseTest.php";
require_once "RatingSyncTestCase.php";

// Class to expose protected members and functions
class TmdbApiExt extends \RatingSync\TmdbApi {
    function _getSourceName() { return $this->sourceName; }
    function _buildUrlFilmDetail($film) { return $this->buildUrlFilmDetail($film); }
    function _getSearchResultFromResponse($response, $title, $year, $searchType) { return $this->getSearchResultFromResponse($response, $title, $year, $searchType); }
    function _getUniqueNameFromSourceId($sourceId, $contentType) { return $this->getUniqueNameFromSourceId($sourceId, $contentType); }
    function _validateResponseCredits($json) { return $this->validateResponseCredits($json); }
    function _getSourceIdFromUniqueName($uniqueName) { return $this->getSourceIdFromUniqueName($uniqueName); }
    function _getFilmDetailCacheFilename($film) { return $this->getFilmDetailCacheFilename($film); }
    function _getSeasonCacheFilename($seriesFilmId, $seasonNum) { return $this->getSeasonCacheFilename($seriesFilmId, $seasonNum); }
}

class TmdbApiTest extends RatingSyncTestCase
{
    const TESTFILM_PRIMARY_TITLE = "Frozen";
    const TESTFILM_PRIMARY_YEAR = 2013;
    const TESTFILM_PRIMARY_IMDBID = "tt2294629";
    const TESTFILM_PRIMARY_IMDB_YOURSCORE = 2;
    const TESTFILM_PRIMARY_IMDB_YOURDATE = "2014-01-01";
    const TESTFILM_PRIMARY_TMDBID = "mv109445"; // mv + TESTFILM_PRIMARY_TMDBID_SOURCEID
    const TESTFILM_PRIMARY_TMDBID_SOURCEID = "109445";
    const TESTFILM_PRIMARY_USER_SCORE = 7.3;
    const TESTFILM_PRIMARY_CRITIC_SCORE = null;
    const TESTFILM_PRIMARY_IMAGE = "/kgwjIb2JDHRhNk13lmSxiClFjVk.jpg";
    const TESTFILM_PRIMARY_DIRECTORS = array("Chris Buck", "Jennifer Lee");
    const TESTFILM_PRIMARY_GENRES = array("Animation", "Family", "Adventure", "Fantasy");
    
    const TESTSERIES_TITLE = "Game of Thrones";
    const TESTSERIES_YEAR = 2011;
    const TESTSERIES_IMDBID = "tt0944947";
    const TESTSERIES_TMDBID = "tv1399"; // tv + TESTSERIES_TMDBID_SOURCEID
    const TESTSERIES_TMDBID_SOURCEID = "1399";
    const TESTSERIES_USER_SCORE = 8.1;
    const TESTSERIES_CRITIC_SCORE = null;
    const TESTSERIES_IMAGE = "/u3bZgnGQ9T01sWNhyveQz0wH0Hl.jpg";
    const TESTSERIES_DIRECTORS = array();
    const TESTSERIES_GENRES = array("Sci-Fi & Fantasy", "Drama", "Action & Adventure");
    const TESTSERIES_SEASON_COUNT = 8;
    
    const TESTEPISODE_TITLE = self::TESTSERIES_TITLE;
    const TESTEPISODE_EPISODETITLE = "Garden of Bones";
    const TESTEPISODE_YEAR = 2012;
    const TESTEPISODE_IMDBID = "tt2069319";
    const TESTEPISODE_TMDBID = "ep63069"; // ep + TESTEPISODE_TMDBID_SOURCEID
    const TESTEPISODE_TMDBID_SOURCEID = "63069";
    const TESTEPISODE_PARENT_TMDBID = "tv1399";
    const TESTEPISODE_USER_SCORE = 8.216;
    const TESTEPISODE_CRITIC_SCORE = null;
    const TESTEPISODE_IMAGE = "/4j2j97GFao2NX4uAtMbr0Qhx2K2.jpg";
    const TESTEPISODE_DIRECTORS = array("David Petrarca");
    const TESTEPISODE_GENRES = array("Action", "Adventure", "Drama");
    const TESTEPISODE_SEASON_NUM = 2;
    const TESTEPISODE_EPISODE_NUM = 4;

    public static function getConstants()
    {
        $constants = array();
        $constants["sourceName"]            = Constants::SOURCE_TMDBAPI;

        $constants["filmUniqueName"]        = self::TESTFILM_PRIMARY_TMDBID;
        $constants["filmImdbId"]            = self::TESTFILM_PRIMARY_IMDBID;
        $constants["filmTitle"]             = self::TESTFILM_PRIMARY_TITLE;
        $constants["filmYear"]              = self::TESTFILM_PRIMARY_YEAR;
        $constants["filmGenres"]            = self::TESTFILM_PRIMARY_GENRES;
        $constants["filmDirectors"]         = self::TESTFILM_PRIMARY_DIRECTORS;
        $constants["filmImage"]             = self::TESTFILM_PRIMARY_IMAGE;
        $constants["filmUserScore"]         = self::TESTFILM_PRIMARY_USER_SCORE;
        $constants["filmCriticScore"]       = self::TESTFILM_PRIMARY_CRITIC_SCORE;
        $constants["filmImdbYourScore"]      = self::TESTFILM_PRIMARY_IMDB_YOURSCORE;
        $constants["filmImdbYourDate"]       = self::TESTFILM_PRIMARY_IMDB_YOURDATE;

        $constants["seriesUniqueName"]      = self::TESTSERIES_TMDBID;
        $constants["seriesImdbId"]          = self::TESTSERIES_IMDBID;
        $constants["seriesTitle"]           = self::TESTSERIES_TITLE;
        $constants["seriesYear"]            = self::TESTSERIES_YEAR;
        $constants["seriesGenres"]          = self::TESTSERIES_GENRES;
        $constants["seriesDirectors"]       = self::TESTSERIES_DIRECTORS;
        $constants["seriesImage"]           = self::TESTSERIES_IMAGE;
        $constants["seriesUserScore"]       = self::TESTSERIES_USER_SCORE;
        $constants["seriesCriticScore"]     = self::TESTSERIES_CRITIC_SCORE;

        $constants["episodeUniqueName"]     = self::TESTEPISODE_TMDBID;
        $constants["episodeImdbId"]         = self::TESTEPISODE_IMDBID;
        $constants["episodeTitle"]          = self::TESTEPISODE_TITLE;
        $constants["episodeEpisodeTitle"]   = self::TESTEPISODE_EPISODETITLE;
        $constants["episodeYear"]           = self::TESTEPISODE_YEAR;
        $constants["episodeGenres"]         = self::TESTEPISODE_GENRES;
        $constants["episodeDirectors"]      = self::TESTEPISODE_DIRECTORS;
        $constants["episodeImage"]          = self::TESTEPISODE_IMAGE;
        $constants["episodeUserScore"]      = self::TESTEPISODE_USER_SCORE;
        $constants["episodeCriticScore"]    = self::TESTEPISODE_CRITIC_SCORE;
        $constants["episodeSeasonNum"]      = self::TESTEPISODE_SEASON_NUM;
        $constants["episodeEpisodeNum"]     = self::TESTEPISODE_EPISODE_NUM;

        sort($constants["filmDirectors"]);
        sort($constants["filmGenres"]);
        sort($constants["seriesDirectors"]);
        sort($constants["seriesGenres"]);
        sort($constants["episodeDirectors"]);
        sort($constants["episodeGenres"]);

        return $constants;
    }

    protected function setUp(): void
    {
        parent::setup();
        //$this->verbose = true;
    }

    public function testThisIsTheDefaultApi()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->assertEquals(Constants::SOURCE_TMDBAPI, Constants::DATA_API_DEFAULT, "OKAY: TMDb is the default API for data, then parts of TMDbApi cannot be tested. Failing this test will skip those tests.");
    }

    /**
     * @covers \RatingSync\TmdbApi::__construct
     */
    public function testObjectCanBeConstructed()
    {$this->start(__CLASS__, __FUNCTION__);

        $api = new TmdbApi();

        $this->assertTrue(true); // Making sure we made it this far
    }
    
    /**
     * @covers \RatingSync\TmdbApi::buildUrlFilmDetail
     * @depends testObjectCanBeConstructed
     */
    public function testBuildUrlFilmDetailMovie()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $api = new TmdbApiExt();
        $film = new Film();
        $film->setContentType(Film::CONTENT_FILM);
        $film->setUniqueName(self::TESTFILM_PRIMARY_TMDBID, $api->_getSourceName());

        // Test
        $url = $api->_buildUrlFilmDetail($film);

        // Verify
        $this->assertStringContainsString("/movie/".self::TESTFILM_PRIMARY_TMDBID_SOURCEID, $url);
        $this->assertStringContainsString("api_key=".Constants::TMDB_API_KEY, $url);
    }
    
    /**
     * @covers \RatingSync\TmdbApi::buildUrlFilmDetail
     * @depends testBuildUrlFilmDetailMovie
     */
    public function testBuildUrlFilmDetailMovieWithoutContentType()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        // Setup
        $api = new TmdbApiExt();
        $film = new Film();
        $film->setUniqueName(self::TESTFILM_PRIMARY_TMDBID, $api->_getSourceName());

        // Test
        $url = $api->_buildUrlFilmDetail($film);
    }
    
    /**
     * @covers \RatingSync\TmdbApi::buildUrlFilmDetail
     * @depends testObjectCanBeConstructed
     */
    public function testBuildUrlFilmDetailTvSeries()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $api = new TmdbApiExt();
        $film = new Film();
        $film->setContentType(Film::CONTENT_TV_SERIES);
        $film->setUniqueName(self::TESTSERIES_TMDBID, $api->_getSourceName());

        // Test
        $url = $api->_buildUrlFilmDetail($film);

        // Verify
        $this->assertStringContainsString("/tv/".self::TESTSERIES_TMDBID_SOURCEID, $url);
        $this->assertStringContainsString("api_key=".Constants::TMDB_API_KEY, $url);
    }
    
    /**
     * @covers \RatingSync\TmdbApi::buildUrlFilmDetail
     * @depends testBuildUrlFilmDetailTvSeries
     */
    public function testBuildUrlFilmDetailTvSeriesWithoutContentType()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        // Setup
        $api = new TmdbApiExt();
        $film = new Film();
        $film->setUniqueName(self::TESTSERIES_TMDBID, $api->_getSourceName());

        // Test
        $url = $api->_buildUrlFilmDetail($film);
    }

    public function testResetDb()
    {$this->start(__CLASS__, __FUNCTION__);

        DatabaseTest::resetDb();

        $this->assertTrue(true); // Making sure we made it this far
    }

    /**
     * @depends testResetDb
     * @depends testObjectCanBeConstructed
     * @depends testThisIsTheDefaultApi
     */
    public function testSetupTvSeries()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $constants = TmdbApiTest::getConstants();
        $api = new TmdbApiExt();
        $film = new Film();
        $film->setTitle($constants["seriesTitle"]);
        $film->setYear($constants["seriesYear"]);
        $film->setContentType(Film::CONTENT_TV_SERIES);
        $film->setUniqueName($constants["seriesUniqueName"], $api->_getSourceName());

        // Test
        $success = $film->saveToDb();

        // Verify
        $this->assertTrue($success, "saveToDb() should succeed");
    }
    
    /**
     * @covers \RatingSync\TmdbApi::buildUrlFilmDetail
     * @depends testSetupTvSeries
     * @depends testThisIsTheDefaultApi
     */
    public function testBuildUrlFilmDetailTvEpisode()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $api = new TmdbApiExt();
        $film = new Film();
        $parent = Film::getFilmFromDbByUniqueName(self::TESTSERIES_TMDBID, $api->_getSourceName());
        $film->setParentId($parent->getId());
        $film->setContentType(Film::CONTENT_TV_EPISODE);
        $film->setSeason(self::TESTEPISODE_SEASON_NUM);
        $film->setEpisodeNumber(self::TESTEPISODE_EPISODE_NUM);

        // Test
        $url = $api->_buildUrlFilmDetail($film);

        // Verify
        $validUrl = "/tv/" . self::TESTSERIES_TMDBID_SOURCEID;
        $validUrl .= "/season/" . self::TESTEPISODE_SEASON_NUM;
        $validUrl .= "/episode/" . self::TESTEPISODE_EPISODE_NUM;
        $this->assertStringContainsString($validUrl, $url);
        $this->assertStringContainsString("api_key=".Constants::TMDB_API_KEY, $url);
    }
    
    /**
     * @covers \RatingSync\TmdbApi::buildUrlFilmDetail
     * @depends testBuildUrlFilmDetailTvEpisode
     */
    public function testBuildUrlFilmDetailTvEpisodeWithoutContentType()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        // Setup
        $api = new TmdbApiExt();
        $film = new Film();
        $film->setUniqueName(self::TESTEPISODE_TMDBID, $api->_getSourceName());

        // Test
        $url = $api->_buildUrlFilmDetail($film);
    }
    
    /**
     * @covers \RatingSync\TmdbApi::buildUrlFilmDetail
     * @depends testBuildUrlFilmDetailTvEpisode
     */
    public function testBuildUrlFilmDetailTvEpisodeWithoutParent()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        // Setup
        $api = new TmdbApiExt();
        $film = new Film();
        $film->setContentType(Film::CONTENT_TV_EPISODE);
        $film->setUniqueName(self::TESTEPISODE_TMDBID, $api->_getSourceName());

        // Test
        $url = $api->_buildUrlFilmDetail($film);
    }
    
    /**
     * @covers \RatingSync\TmdbApi::buildUrlFilmDetail
     * @depends testBuildUrlFilmDetailTvEpisode
     * @depends testThisIsTheDefaultApi
     */
    public function testBuildUrlFilmDetailTvEpisodeWithoutSeasonNum()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        // Setup
        $api = new TmdbApiExt();
        $film = new Film();
        $parent = Film::getFilmFromDbByUniqueName(self::TESTSERIES_TMDBID, $api->_getSourceName());
        $film->setParentId($parent->getId());
        $film->setContentType(Film::CONTENT_TV_EPISODE);
        $film->getEpisodeNumber(self::TESTEPISODE_EPISODE_NUM);

        // Test
        $url = $api->_buildUrlFilmDetail($film, self::TESTSERIES_TMDBID);
    }
    
    /**
     * @covers \RatingSync\TmdbApi::buildUrlFilmDetail
     * @depends testBuildUrlFilmDetailTvEpisode
     * @depends testThisIsTheDefaultApi
     */
    public function testBuildUrlFilmDetailTvEpisodeWithoutEpisodeNum()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        // Setup
        $api = new TmdbApiExt();
        $film = new Film();
        $parent = Film::getFilmFromDbByUniqueName(self::TESTSERIES_TMDBID, $api->_getSourceName());
        $film->setParentId($parent->getId());
        $film->setContentType(Film::CONTENT_TV_EPISODE);
        $film->setSeason(self::TESTEPISODE_SEASON_NUM);

        // Test
        $url = $api->_buildUrlFilmDetail($film);
    }
    
    /**
     * @covers \RatingSync\TmdbApi::getSearchResultFromResponse
     * @depends testObjectCanBeConstructed
     */
    public function testGetSearchResultFromResponse()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $api = new TmdbApiExt();

        // Movie Search
            // Setup
        $searchType = Film::CONTENT_FILM;
        $title = self::TESTFILM_PRIMARY_TITLE;
        $year = self::TESTFILM_PRIMARY_YEAR;
        $matchingResult = array();
        $matchingResult["title"] = $title;
        $matchingResult["release_date"] = $year . "-11-27";
        $wrongResult1 = array();
        $wrongResult1["title"] = "The Frozen Ground";
        $wrongResult1["release_date"] = $year . "-11-27";
        $wrongResult2 = array();
        $wrongResult2["title"] = $title;;
        $wrongResult2["release_date"] = ($year - 1) . "-11-27";
        $results = array(0 => $wrongResult1, 1 => $wrongResult2, 2 => $matchingResult, 3 => $wrongResult1);
        $response = array("total_results" => 4, "results" => $results);

            // Test
        $resultReturned = $api->_getSearchResultFromResponse($response, $title, $year, TmdbApi::REQUEST_SEARCH_MOVIE);

            // Verify
        $this->assertEquals($matchingResult, $resultReturned, "Movie search");

        // TV Series Search
            // Setup
        $searchType = Film::CONTENT_TV_SERIES;
        $title = self::TESTSERIES_TITLE;
        $year = self::TESTSERIES_YEAR;
        $matchingResult = array();
        $matchingResult["name"] = $title;
        $matchingResult["first_air_date"] = $year . "-11-27";
        $wrongResult1 = array();
        $wrongResult1["name"] = "Game of Chess";
        $wrongResult1["first_air_date"] = $year . "-11-27";
        $wrongResult2 = array();
        $wrongResult2["name"] = $title;;
        $wrongResult2["first_air_date"] = ($year - 1) . "-11-27";
        $results = array(0 => $wrongResult1, 1 => $wrongResult2, 2 => $matchingResult, 3 => $wrongResult1);
        $response = array("total_results" => 4, "results" => $results);

            // Test
        $resultReturned = $api->_getSearchResultFromResponse($response, $title, $year, TmdbApi::REQUEST_SEARCH_SERIES);

            // Verify
        $this->assertEquals($matchingResult, $resultReturned, "TV Series search");
    }
    
    /**
     * @covers \RatingSync\TmdbApi::searchForUniqueName
     * @depends testObjectCanBeConstructed
     * @depends testThisIsTheDefaultApi
     */
    public function testSearchForUniqueNameWithImdbId()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $api = new TmdbApi();
        $film = new Film();
        $film->setUniqueName(self::TESTFILM_PRIMARY_IMDBID, Constants::SOURCE_IMDB);

        // Test
        $uniqueName = $api->searchForUniqueName($film);

        // Verify
        $this->assertEquals(self::TESTFILM_PRIMARY_TMDBID, $uniqueName);
    }
    
    /**
     * @covers \RatingSync\TmdbApi::searchForUniqueName
     * @depends testGetSearchResultFromResponse
     * @depends testThisIsTheDefaultApi
     */
    public function testSearchForUniqueNameMovie()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $api = new TmdbApi();
        $film = new Film();
        $film->setContentType(Film::CONTENT_FILM);
        $film->setTitle(self::TESTFILM_PRIMARY_TITLE);
        $film->setYear(self::TESTFILM_PRIMARY_YEAR);

        // Test
        $uniqueName = $api->searchForUniqueName($film);

        // Verify
        $this->assertEquals(self::TESTFILM_PRIMARY_TMDBID, $uniqueName);
    }
    
    /**
     * @covers \RatingSync\TmdbApi::searchForUniqueName
     * @depends testObjectCanBeConstructed
     * @depends testThisIsTheDefaultApi
     */
    public function testSearchForUniqueNameMovieWithoutContentType()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $api = new TmdbApi();
        $film = new Film();
        $film->setTitle(self::TESTFILM_PRIMARY_TITLE);
        $film->setYear(self::TESTFILM_PRIMARY_YEAR);

        // Test
        $uniqueName = $api->searchForUniqueName($film);

        // Verify
        $this->assertEquals(self::TESTFILM_PRIMARY_TMDBID, $uniqueName);
    }
    
    /**
     * @covers \RatingSync\TmdbApi::searchForUniqueName
     * @depends testObjectCanBeConstructed
     * @depends testThisIsTheDefaultApi
     */
    public function testSearchForUniqueNameTvSeries()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $api = new TmdbApi();
        $film = new Film();
        $film->setContentType(Film::CONTENT_TV_SERIES);
        $film->setTitle(self::TESTSERIES_TITLE);
        $film->setYear(self::TESTSERIES_YEAR);

        // Test
        $uniqueName = $api->searchForUniqueName($film);

        // Verify
        $this->assertEquals(self::TESTSERIES_TMDBID, $uniqueName);
    }
    
    /**
     * @covers \RatingSync\TmdbApi::searchForUniqueName
     * @depends testObjectCanBeConstructed
     * @depends testThisIsTheDefaultApi
     */
    public function testSearchForUniqueNameTvSeriesWithoutContentType()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $api = new TmdbApi();
        $film = new Film();
        $film->setTitle(self::TESTSERIES_TITLE);
        $film->setYear(self::TESTSERIES_YEAR);

        // Test
        $uniqueName = $api->searchForUniqueName($film);

        // Verify
        $this->assertEquals(self::TESTSERIES_TMDBID, $uniqueName);
    }
    
    /**
     * @covers \RatingSync\TmdbApi::searchForUniqueName
     * @depends testObjectCanBeConstructed
     * @depends testThisIsTheDefaultApi
     */
    public function testSearchForUniqueNameTvEpisode()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $api = new TmdbApi();
        $film = new Film();
        $film->setContentType(Film::CONTENT_TV_EPISODE);
        $film->setTitle(self::TESTEPISODE_TITLE);
        $film->setYear(self::TESTEPISODE_YEAR);
        $film->setEpisodeTitle(self::TESTEPISODE_EPISODETITLE);
        $film->setSeason(self::TESTEPISODE_SEASON_NUM);

        // Test
        $uniqueName = $api->searchForUniqueName($film);

        // Verify
        $this->assertEmpty($uniqueName, "TmdbApi::searchForUniqueName() does not support Tv Episodes");
    }
    
    /**
     * @covers \RatingSync\TmdbApi::searchForUniqueName
     * @depends testObjectCanBeConstructed
     * @depends testThisIsTheDefaultApi
     */
    public function testSearchForUniqueNameTvEpisodeWithoutContentType()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $api = new TmdbApi();
        $film = new Film();
        $film->setTitle(self::TESTEPISODE_TITLE);
        $film->setYear(self::TESTEPISODE_YEAR);

        // Test
        $uniqueName = $api->searchForUniqueName($film);

        // Verify
        $this->assertEmpty($uniqueName, "TmdbApi::searchForUniqueName() does not support Tv Episodes");
    }
    
    /**
     * @covers \RatingSync\TmdbApi::searchForUniqueName
     * @depends testObjectCanBeConstructed
     * @depends testThisIsTheDefaultApi
     */
    public function testSearchForUniqueNameWithUniqueName()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $api = new TmdbApiExt();
        $film = new Film();
        $film->setUniqueName(self::TESTFILM_PRIMARY_TMDBID, $api->_getSourceName());

        // Test
        $uniqueName = $api->searchForUniqueName($film);

        // Verify
        $this->assertEquals(self::TESTFILM_PRIMARY_TMDBID, $uniqueName);
    }

    /**
     * @covers \RatingSync\TmdbApi::validateResponseCredits
     * @depends testObjectCanBeConstructed
     */
    public function testValidateResponseCredits()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $api = new TmdbApiExt();
        $response = '{"id": 1}';
        $json = json_decode($response, true);

        // Test
        $msg = $api->_validateResponseCredits($json);

        // Verify
        $this->assertFalse(empty($msg), "Validation message should not be empty");
        $this->assertEquals("Success", $msg, "Validation message should 'Success'");
    }

    /**
     * @covers \RatingSync\TmdbApi::validateResponseCredits
     * @depends testValidateResponseCredits
     */
    public function testValidateResponseCreditsNullResponse()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $api = new TmdbApiExt();
        $response = null;
        $json = json_decode($response, true);

        // Test
        $msg = $api->_validateResponseCredits($json);

        // Verify
        $this->assertFalse(empty($msg), "Validation message should not be empty");
        $this->assertFalse("Success" == $msg, "Validation message should not 'Success'");
        $this->assertGreaterThan(0, strlen($msg), "There should be an error message");
    }

    /**
     * @covers \RatingSync\TmdbApi::validateResponseCredits
     * @depends testValidateResponseCredits
     */
    public function testValidateResponseCreditsEmptyResponse()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $api = new TmdbApiExt();
        $response = "";
        $json = json_decode($response, true);

        // Test
        $msg = $api->_validateResponseCredits($json);

        // Verify
        $this->assertFalse(empty($msg), "Validation message should not be empty");
        $this->assertFalse("Success" == $msg, "Validation message should not 'Success'");
        $this->assertGreaterThan(0, strlen($msg), "There should be an error message");
    }

    /**
     * @covers \RatingSync\TmdbApi::validateResponseCredits
     * @depends testValidateResponseCredits
     */
    public function testValidateResponseCreditsNoId()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $api = new TmdbApiExt();
        $response = '{"test": 1}';
        $json = json_decode($response, true);

        // Test
        $msg = $api->_validateResponseCredits($json);

        // Verify
        $this->assertFalse(empty($msg), "Validation message should not be empty");
        $this->assertFalse("Success" == $msg, "Validation message should not 'Success'");
        $this->assertGreaterThan(0, strlen($msg), "There should be an error message");
    }

    /**
     * @covers \RatingSync\TmdbApi::validateResponseCredits
     * @depends testValidateResponseCredits
     */
    public function testValidateResponseCreditsError()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $api = new TmdbApiExt();
        $response = '{"id": 1, "status_code": 34, "status_message": "Something went wrong"}';
        $json = json_decode($response, true);

        // Test
        $msg = $api->_validateResponseCredits($json);

        // Verify
        $this->assertFalse(empty($msg), "Validation message should not be empty");
        $this->assertFalse("Success" == $msg, "Validation message should not 'Success'");
        $this->assertGreaterThan(0, strlen($msg), "There should be an error message");
    }

    /**
     * @covers \RatingSync\TmdbApi::getCreditsFromApi
     * @depends testObjectCanBeConstructed
     * @depends testThisIsTheDefaultApi
     */
    public function testGetCreditsFromApiNoFilm()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        // Setup
        $api = new TmdbApiExt();

        // Test
        $api->getCreditsFromApi("Not a Film object");
    }

    /**
     * @covers \RatingSync\TmdbApi::getCreditsFromApi
     * @depends testObjectCanBeConstructed
     * @depends testThisIsTheDefaultApi
     */
    public function testGetCreditsFromApiNoContentType()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        // Setup
        $api = new TmdbApiExt();
        $sourceName = $api->_getSourceName();
        $film = new Film();
        $film->setUniqueName(self::TESTFILM_PRIMARY_TMDBID, $sourceName);

        // Test
        $api->getCreditsFromApi($film);
    }

    /**
     * @covers \RatingSync\TmdbApi::getCreditsFromApi
     * @depends testObjectCanBeConstructed
     * @depends testThisIsTheDefaultApi
     */
    public function testGetCreditsFromApiBadContentType()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        // Setup
        $api = new TmdbApiExt();
        $sourceName = $api->_getSourceName();
        $film = new Film();
        $film->setContentType(Film::CONTENT_TV_EPISODE);

        // Test
        $api->getCreditsFromApi($film);
    }

    /**
     * @covers \RatingSync\TmdbApi::getCreditsFromApi
     * @depends testObjectCanBeConstructed
     * @depends testThisIsTheDefaultApi
     */
    public function testGetCreditsFromApiNoUniqueName()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        // Setup
        $api = new TmdbApiExt();
        $sourceName = $api->_getSourceName();
        $film = new Film();
        $film->setContentType(Film::CONTENT_FILM);

        // Test
        $api->getCreditsFromApi($film);
    }

    /**
     * @covers \RatingSync\TmdbApi::getCreditsFromApi
     * @depends testObjectCanBeConstructed
     * @depends testThisIsTheDefaultApi
     */
    public function testGetCreditsFromApiBadUniqueName()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\Exception::class);

        // Setup
        $api = new TmdbApiExt();
        $sourceName = $api->_getSourceName();
        $film = new Film();
        $film->setContentType(Film::CONTENT_FILM);
        $film->setUniqueName("BadUniqueName", $sourceName);

        // Test
        $api->getCreditsFromApi($film);
    }

    /**
     * @covers \RatingSync\TmdbApi::getCreditsFromApi
     * @depends testObjectCanBeConstructed
     * @depends testValidateResponseCredits
     * @depends testThisIsTheDefaultApi
     */
    public function testGetCreditsFromApi()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $api = new TmdbApiExt();
        $sourceName = $api->_getSourceName();

                // Movie
        // Setup
        $film = new Film();
        $film->setContentType(Film::CONTENT_FILM);
        $film->setUniqueName(self::TESTFILM_PRIMARY_TMDBID, $sourceName);

        // Test
        $json = $api->getCreditsFromApi($film);

        // Verify
        $this->assertFalse(empty($json), "Credits result should not be empty");
        $this->assertTrue(array_key_exists("cast", $json), "'cast' should be in the results");
        $this->assertTrue(array_key_exists("crew", $json), "'crew' should be in the results");

                // TV Series
        // Setup
        $film = new Film();
        $film->setContentType(Film::CONTENT_TV_SERIES);
        $film->setUniqueName(self::TESTSERIES_TMDBID, $sourceName);

        // Test
        $json = $api->getCreditsFromApi($film);

        // Verify
        $this->assertFalse(empty($json), "Credits result should not be empty");
        $this->assertTrue(array_key_exists("cast", $json), "'cast' should be in the results");
        $this->assertTrue(array_key_exists("crew", $json), "'crew' should be in the results");
    }
    
    /**
     * @covers \RatingSync\TmdbApi::getJsonFromApiForFilmDetail
     * @depends testObjectCanBeConstructed
     * @depends testThisIsTheDefaultApi
     */
    public function testGetJsonFromApiForFilmDetail()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $api = new TmdbApiExt();
        $film = new Film();
        $sourceName = $api->_getSourceName();

                // Movie
        // Setup
        $film->setContentType(Film::CONTENT_FILM);
        $film->setUniqueName(self::TESTFILM_PRIMARY_TMDBID, $sourceName);

        // Test
        $filmJson = $api->getJsonFromApiForFilmDetail($film, true, Constants::USE_CACHE_NEVER);

        // Verify
        $tmdbId = $api->jsonValue($filmJson, Source::ATTR_UNIQUE_NAME, TmdbApi::REQUEST_DETAIL_MOVIE);
        $title = $api->jsonValue($filmJson, Film::ATTR_TITLE, TmdbApi::REQUEST_DETAIL_MOVIE);
        $year = substr($api->jsonValue($filmJson, Film::ATTR_YEAR, TmdbApi::REQUEST_DETAIL_MOVIE), 0, 4);
        $image = $api->jsonValue($filmJson, Source::ATTR_IMAGE, TmdbApi::REQUEST_DETAIL_MOVIE);
        $genres = $api->jsonValue($filmJson, Film::ATTR_GENRES, TmdbApi::REQUEST_DETAIL_MOVIE);
        $userScore = $api->jsonValue($filmJson, Source::ATTR_USER_SCORE, TmdbApi::REQUEST_DETAIL_MOVIE);
        $requestName = $api->jsonValue($filmJson, TmdbApi::ATTR_API_REQUEST_NAME, TmdbApi::REQUEST_DETAIL_MOVIE);
        $this->assertEquals(self::TESTFILM_PRIMARY_TMDBID, $api->_getUniqueNameFromSourceId($tmdbId, Film::CONTENT_FILM), "TMDb ID");
        $this->assertEquals(self::TESTFILM_PRIMARY_TITLE, $title, "Title");
        $this->assertEquals(self::TESTFILM_PRIMARY_YEAR, $year, "Year");
        $this->assertEquals(self::TESTFILM_PRIMARY_IMAGE, $image, "Image");
        $this->assertGreaterThan(0, count($genres), "Genres");
        $this->assertEquals(round(self::TESTFILM_PRIMARY_USER_SCORE), round($userScore), "User score");
        $this->assertEquals(TmdbApi::REQUEST_DETAIL, $requestName, "Request name");

                // TV Series
        // Setup
        $film->setContentType(Film::CONTENT_TV_SERIES);
        $film->setUniqueName(self::TESTSERIES_TMDBID, $sourceName);

        // Test
        $filmJson = $api->getJsonFromApiForFilmDetail($film, true, Constants::USE_CACHE_NEVER);

        // Verify
        $tmdbId = $api->jsonValue($filmJson, Source::ATTR_UNIQUE_NAME, TmdbApi::REQUEST_DETAIL_SERIES);
        $title = $api->jsonValue($filmJson, Film::ATTR_TITLE, TmdbApi::REQUEST_DETAIL_SERIES);
        $year = substr($api->jsonValue($filmJson, Film::ATTR_YEAR, TmdbApi::REQUEST_DETAIL_SERIES), 0, 4);
        $image = $api->jsonValue($filmJson, Source::ATTR_IMAGE, TmdbApi::REQUEST_DETAIL_SERIES);
        $seasonCount = $api->jsonValue($filmJson, Film::ATTR_SEASON_COUNT, TmdbApi::REQUEST_DETAIL_SERIES);
        $genres = $api->jsonValue($filmJson, Film::ATTR_GENRES, TmdbApi::REQUEST_DETAIL_SERIES);
        $userScore = $api->jsonValue($filmJson, Source::ATTR_USER_SCORE, TmdbApi::REQUEST_DETAIL_SERIES);
        $requestName = $api->jsonValue($filmJson, TmdbApi::ATTR_API_REQUEST_NAME, TmdbApi::REQUEST_DETAIL_SERIES);
        $this->assertEquals(self::TESTSERIES_TMDBID, $api->_getUniqueNameFromSourceId($tmdbId, Film::CONTENT_TV_SERIES), "TMDb ID");
        $this->assertEquals(self::TESTSERIES_TITLE, $title, "Title");
        $this->assertEquals(self::TESTSERIES_YEAR, $year, "Year");
        $this->assertEquals(self::TESTSERIES_IMAGE, $image, "Image");
        $this->assertEquals(self::TESTSERIES_SEASON_COUNT, $seasonCount, "Season count");
        $this->assertGreaterThan(0, count($genres), "Genres");
        $this->assertEquals(round(self::TESTSERIES_USER_SCORE), round($userScore), "User score");
        $this->assertEquals(TmdbApi::REQUEST_DETAIL, $requestName, "Request name");
    }
    
    /**
     * @covers \RatingSync\TmdbApi::getJsonFromApiForFilmDetail
     * @depends testGetJsonFromApiForFilmDetail
     * @depends testThisIsTheDefaultApi
     */
    public function testGetJsonFromApiForFilmDetailEpisode()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $api = new TmdbApiExt();
        $film = new Film();
        $sourceName = $api->_getSourceName();
        $parent = Film::getFilmFromDbByUniqueName(self::TESTSERIES_TMDBID, $sourceName);
        $film->setParentId($parent->getId());
        $film->setContentType(Film::CONTENT_TV_EPISODE);
        $film->setUniqueName(self::TESTEPISODE_TMDBID, $sourceName);
        $film->setSeason(self::TESTEPISODE_SEASON_NUM);
        $film->setEpisodeNumber(self::TESTEPISODE_EPISODE_NUM);

        // Test
        $filmJson = $api->getJsonFromApiForFilmDetail($film, true, Constants::USE_CACHE_NEVER);

        // Verify
        $tmdbId = $api->jsonValue($filmJson, Source::ATTR_UNIQUE_NAME, TmdbApi::REQUEST_DETAIL_EPISODE);
        $year = substr($api->jsonValue($filmJson, Film::ATTR_YEAR, TmdbApi::REQUEST_DETAIL_EPISODE), 0, 4);
        $image = $api->jsonValue($filmJson, Source::ATTR_IMAGE, TmdbApi::REQUEST_DETAIL_EPISODE);
        $episodeTitle = $api->jsonValue($filmJson, Film::ATTR_EPISODE_TITLE, TmdbApi::REQUEST_DETAIL_EPISODE);
        $seasonNum = $api->jsonValue($filmJson, Film::ATTR_SEASON_NUM, TmdbApi::REQUEST_DETAIL_EPISODE);
        $episodeNum = $api->jsonValue($filmJson, Film::ATTR_EPISODE_NUM, TmdbApi::REQUEST_DETAIL_EPISODE);
        $directors = $api->jsonValue($filmJson, Film::ATTR_DIRECTORS, TmdbApi::REQUEST_DETAIL_EPISODE);
        $userScore = $api->jsonValue($filmJson, Source::ATTR_USER_SCORE, TmdbApi::REQUEST_DETAIL_EPISODE);
        $requestName = $api->jsonValue($filmJson, TmdbApi::ATTR_API_REQUEST_NAME, TmdbApi::REQUEST_DETAIL_EPISODE);
        $this->assertEquals(self::TESTEPISODE_TMDBID, $api->_getUniqueNameFromSourceId($tmdbId, Film::CONTENT_TV_EPISODE));
        $this->assertEquals(self::TESTEPISODE_YEAR, $year, "Year");
        $this->assertEquals(self::TESTEPISODE_IMAGE, $image, "Image");
        $this->assertEquals(self::TESTEPISODE_EPISODETITLE, $episodeTitle, "Episode title");
        $this->assertEquals(self::TESTEPISODE_SEASON_NUM, $seasonNum, "Season number");
        $this->assertEquals(self::TESTEPISODE_EPISODE_NUM, $episodeNum, "Episode number");
        $this->assertEquals(self::TESTEPISODE_DIRECTORS, $directors, "Directors");
        $this->assertEquals(round(self::TESTEPISODE_USER_SCORE), round($userScore), "User score");
        $this->assertEquals(TmdbApi::REQUEST_DETAIL, $requestName, "Request name");
    }
    
    /**
     * @covers \RatingSync\TmdbApi::populateFilmDetail
     * @depends testObjectCanBeConstructed
     */
    public function testPopulateForFilmDetailNullJson()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        // Setup
        $api = new TmdbApiExt();
        $sourceName = $api->_getSourceName();
        $film = new Film();
        $film->setContentType(Film::CONTENT_FILM);
        $overwrite = true;
        $json = null;

        // Test
        $api->populateFilmDetail($json, $film, $overwrite);

    }
    
    /**
     * @covers \RatingSync\TmdbApi::populateFilmDetail
     * @depends testObjectCanBeConstructed
     */
    public function testPopulateForFilmDetailStringJson()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        // Setup
        $api = new TmdbApiExt();
        $sourceName = $api->_getSourceName();
        $film = new Film();
        $film->setContentType(Film::CONTENT_FILM);
        $overwrite = true;
        $jsonStr = '{"id": 1}';
        $json = json_decode($jsonStr, true);

        // Test
        $api->populateFilmDetail($jsonStr, $film, $overwrite);

    }
    
    /**
     * @covers \RatingSync\TmdbApi::populateFilmDetail
     * @depends testObjectCanBeConstructed
     */
    public function testPopulateForFilmDetailNullFilm()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        // Setup
        $api = new TmdbApiExt();
        $sourceName = $api->_getSourceName();
        $film = new Film();
        $film->setContentType(Film::CONTENT_FILM);
        $overwrite = true;
        $jsonStr = '{"id": 1}';
        $json = json_decode($jsonStr, true);

        // Test
        $api->populateFilmDetail($jsonStr, null, $overwrite);

    }
    
    /**
     * @covers \RatingSync\TmdbApi::populateFilmDetail
     * @depends testObjectCanBeConstructed
     */
    public function testPopulateForFilmDetailStringFilm()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        // Setup
        $api = new TmdbApiExt();
        $sourceName = $api->_getSourceName();
        $film = new Film();
        $film->setContentType(Film::CONTENT_FILM);
        $overwrite = true;
        $jsonStr = '{"id": 1}';
        $json = json_decode($jsonStr, true);

        // Test
        $api->populateFilmDetail($jsonStr, "String film, not a Film object", $overwrite);

    }
    
    /**
     * @covers \RatingSync\TmdbApi::populateFilmDetail
     * @depends testObjectCanBeConstructed
     */
    public function testPopulateForFilmDetailEmptyContentType()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        // Setup
        $api = new TmdbApiExt();
        $sourceName = $api->_getSourceName();
        $film = new Film();
        //$film->setContentType(Film::CONTENT_FILM);
        $overwrite = true;
        $jsonStr = '{"id": 1}';
        $json = json_decode($jsonStr, true);

        // Test
        $api->populateFilmDetail($jsonStr, $film, $overwrite);

    }
    
    /**
     * @covers \RatingSync\TmdbApi::populateFilmDetail
     * @depends testObjectCanBeConstructed
     */
    public function testPopulateForFilmDetailEpisodeWithoutParentId()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        // Setup
        $api = new TmdbApiExt();
        $sourceName = $api->_getSourceName();
        $film = new Film();
        $film->setContentType(Film::CONTENT_TV_EPISODE);
        $overwrite = true;
        $jsonStr = '{"id": 1}';
        $json = json_decode($jsonStr, true);

        // Test
        $api->populateFilmDetail($jsonStr, $film, $overwrite);

    }
    
    /**
     * @covers \RatingSync\TmdbApi::populateFilmDetail
     * @depends testObjectCanBeConstructed
     * @depends testGetJsonFromApiForFilmDetail
     * @depends testGetJsonFromApiForFilmDetailEpisode
     * @depends testGetCreditsFromApi
     */
    public function testPopulateForFilmDetail()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $api = new TmdbApiExt();
        $sourceName = $api->_getSourceName();
        $overwrite = true;

        // Movie
                // Setup
        $film = new Film();
        $film->setContentType(Film::CONTENT_FILM);
        $film->setUniqueName(self::TESTFILM_PRIMARY_TMDBID, $sourceName);
        $json = $api->getJsonFromApiForFilmDetail($film, true, Constants::USE_CACHE_NEVER);

                // Test
        $api->populateFilmDetail($json, $film, $overwrite);

                // Verify
                    // Film data
        $this->assertEquals(self::TESTFILM_PRIMARY_TITLE, $film->getTitle(), "title");
        $this->assertEquals(self::TESTFILM_PRIMARY_YEAR, $film->getYear(), "year");
        $this->assertEquals(Film::CONTENT_FILM, $film->getContentType(), 'Content Type');
        $this->assertEmpty($film->getImage(), 'Film image should be empty');
        $this->assertEquals(self::TESTFILM_PRIMARY_GENRES, $film->getGenres(), "genres");
        $this->assertEquals(self::TESTFILM_PRIMARY_DIRECTORS, $film->getDirectors(), "directors");    
                    // TMDb API source-specific
        $this->assertEquals(self::TESTFILM_PRIMARY_IMAGE, $film->getImage($sourceName), "source image");
        $this->assertEquals(self::TESTFILM_PRIMARY_TMDBID, $film->getUniqueName($sourceName), "uniqueName");
        $this->assertEquals(self::TESTFILM_PRIMARY_USER_SCORE, round($film->getUserScore($sourceName), 1), "user score");
        $this->assertNull($film->getParentUniqueName($sourceName), "parent uniqueName should be null for a movie");    
                    // IMDb source-specific
        $this->assertEquals(self::TESTFILM_PRIMARY_IMDBID, $film->getUniqueName(Constants::SOURCE_IMDB), "IMDb ID");
        $this->assertEquals(self::TESTFILM_PRIMARY_USER_SCORE, round($film->getUserScore(Constants::SOURCE_IMDB), 1), "IMDb user score");
                    // Not available in the detail request
        $rating = $film->getRating($sourceName);
        $this->assertNull($rating->getYourScore(), 'Your Score');
        $this->assertNull($rating->getYourRatingDate(), 'Rating date');
        $this->assertNull($rating->getSuggestedScore(), 'Suggested score');
        $this->assertNull($film->getCriticScore($sourceName), "Critic score");
        $this->assertNull($film->getCriticScore(Constants::SOURCE_IMDB), "IMDb critic score");
        $this->assertNull($film->getImage(Constants::SOURCE_IMDB), "IMDb image");

        // TV Series
                // Setup
        $film = new Film();
        $film->setContentType(Film::CONTENT_TV_SERIES);
        $film->setUniqueName(self::TESTSERIES_TMDBID, $sourceName);
        $json = $api->getJsonFromApiForFilmDetail($film, true, Constants::USE_CACHE_NEVER);

                // Test
        $api->populateFilmDetail($json, $film, $overwrite);

                // Verify
                    // Film data
        $this->assertEquals(self::TESTSERIES_TITLE, $film->getTitle(), "title");
        $this->assertEquals(self::TESTSERIES_YEAR, $film->getYear(), "year");
        $this->assertEquals(Film::CONTENT_TV_SERIES, $film->getContentType(), 'Content Type');
        $this->assertEmpty($film->getImage(), 'Film image should be empty');
        $this->assertEquals(self::TESTSERIES_GENRES, $film->getGenres(), "genres");
        $this->assertEquals(self::TESTSERIES_DIRECTORS, $film->getDirectors(), "directors");    
                    // TMDb API source-specific
        $this->assertEquals(self::TESTSERIES_IMAGE, $film->getImage($sourceName), "source image");
        $this->assertEquals(self::TESTSERIES_TMDBID, $film->getUniqueName($sourceName), "uniqueName");
        $this->assertEquals(round(self::TESTSERIES_USER_SCORE), round($film->getUserScore($sourceName)), "user score");
        $this->assertNull($film->getParentUniqueName($sourceName), "parent uniqueName should be null for a series");
                    // Not available in the detail request
        $rating = $film->getRating($sourceName);
        $this->assertNull($rating->getYourScore(), 'Your Score');
        $this->assertNull($rating->getYourRatingDate(), 'Rating date');
        $this->assertNull($rating->getSuggestedScore(), 'Suggested score');
        $this->assertNull($film->getCriticScore($sourceName), "Critic score");
        $this->assertNull($film->getUniqueName(Constants::SOURCE_IMDB), "IMDb ID");
        $this->assertNull($film->getImage(Constants::SOURCE_IMDB), "IMDb image");
        $this->assertNull($film->getUserScore(Constants::SOURCE_IMDB), "IMDb user score");
        $this->assertNull($film->getCriticScore(Constants::SOURCE_IMDB), "IMDb critic score");

        // TV Episode
                // Setup
        $film = new Film();
        $film->setContentType(Film::CONTENT_TV_EPISODE);
        $film->setUniqueName(self::TESTEPISODE_TMDBID, $sourceName);
        $film->setSeason(self::TESTEPISODE_SEASON_NUM);
        $film->setEpisodeNumber(self::TESTEPISODE_EPISODE_NUM);
        $parent = Film::getFilmFromDbByUniqueName(self::TESTSERIES_TMDBID, $sourceName);
        $film->setParentId($parent->getId());
        $json = $api->getJsonFromApiForFilmDetail($film, true, Constants::USE_CACHE_NEVER);

                // Test
        $api->populateFilmDetail($json, $film, $overwrite);

                // Verify
                    // Film data
        $this->assertEquals(self::TESTEPISODE_TITLE, $film->getTitle(), "title");
        $this->assertEquals(self::TESTEPISODE_EPISODETITLE, $film->getEpisodeTitle(), 'Episode Title');
        $this->assertEquals(self::TESTEPISODE_YEAR, $film->getYear(), "year");
        $this->assertEquals(Film::CONTENT_TV_EPISODE, $film->getContentType(), 'Content Type');
        $this->assertEmpty($film->getImage(), 'Film image should be empty');
        $this->assertEquals(self::TESTEPISODE_DIRECTORS, $film->getDirectors(), "directors");    
                    // TMDb API source-specific
        $this->assertEquals(self::TESTEPISODE_IMAGE, $film->getImage($sourceName), "source image");
        $this->assertEquals(self::TESTEPISODE_TMDBID, $film->getUniqueName($sourceName), "uniqueName");
        $this->assertEquals(round(self::TESTEPISODE_USER_SCORE), round($film->getUserScore($sourceName)), "user score");
        $this->assertEquals(self::TESTEPISODE_PARENT_TMDBID, $film->getParentUniqueName($sourceName), "parent uniqueName");   
                    // Not available in the detail request
        $rating = $film->getRating($sourceName);
        $this->assertNull($rating->getYourScore(), 'Your Score');
        $this->assertNull($rating->getYourRatingDate(), 'Rating date');
        $this->assertNull($rating->getSuggestedScore(), 'Suggested score');
        $this->assertEmpty($film->getGenres(), "Genres");
        $this->assertNull($film->getCriticScore($sourceName), "Critic score");
        $this->assertNull($film->getUniqueName(Constants::SOURCE_IMDB), "IMDb ID");
        $this->assertNull($film->getImage(Constants::SOURCE_IMDB), "IMDb image");
        $this->assertNull($film->getUserScore(Constants::SOURCE_IMDB), "IMDb user score");
        $this->assertNull($film->getCriticScore(Constants::SOURCE_IMDB), "IMDb critic score");
    }
    
    /**
     * @covers \RatingSync\TmdbApi::getFilmDetailFromApi
     * @depends testGetJsonFromApiForFilmDetail
     * @depends testPopulateForFilmDetail
     */
    public function testGetFilmDetailFromApi()
    {$this->start(__CLASS__, __FUNCTION__);

        // If the tests this depends on are successful then this getFilmDetailFromApi() with succeed
        $this->assertTrue(true);

    }
    
    /**
     * @covers \RatingSync\TmdbApi::getFilmBySearch
     * @depends testObjectCanBeConstructed
     * @depends testThisIsTheDefaultApi
     */
    public function testGetFilmBySearchNullSearchTerms()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        // Setup
        $api = new TmdbApiExt();
        $searchTerms = null;

        // Test
        $api->getFilmBySearch($searchTerms);

    }
    
    /**
     * @covers \RatingSync\TmdbApi::getFilmBySearch
     * @depends testObjectCanBeConstructed
     * @depends testThisIsTheDefaultApi
     */
    public function testGetFilmBySearchEmptySearchTerms()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        // Setup
        $api = new TmdbApiExt();
        $searchTerms = array();

        // Test
        $api->getFilmBySearch($searchTerms);

    }
    
    /**
     * @covers \RatingSync\TmdbApi::getFilmBySearch
     * @depends testObjectCanBeConstructed
     * @depends testThisIsTheDefaultApi
     */
    public function testGetFilmBySearchStringSearchTerms()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        // Setup
        $api = new TmdbApiExt();
        $searchTerms = "Search Terms";

        // Test
        $api->getFilmBySearch($searchTerms);

    }
    
    /**
     * @covers \RatingSync\TmdbApi::getFilmBySearch
     * @depends testObjectCanBeConstructed
     * @depends testThisIsTheDefaultApi
     */
    public function testGetFilmBySearchEpisodeByUniqueName()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $api = new TmdbApiExt();
        $searchTerms = array();
        $searchTerms["uniqueName"] = self::TESTEPISODE_TMDBID;

        // Test
        $film = $api->getFilmBySearch($searchTerms);

        // Verify
        $this->assertNull($film, "I don't expect to get a TV Episode by uniqueName alone");
    }
    
    /**
     * @covers \RatingSync\TmdbApi::getFilmBySearch
     * @depends testObjectCanBeConstructed
     * @depends testGetFilmDetailFromApi
     * @depends testSearchForUniqueNameMovie
     * @depends testSearchForUniqueNameTvSeries
     * @depends testThisIsTheDefaultApi
     */
    public function testGetFilmBySearch()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $api = new TmdbApiExt();
        $sourceName = $api->_getSourceName();

        // Movie by uniqueName, contentType
                // Setup
        $searchTerms = array();
        $searchTerms["uniqueName"] = self::TESTFILM_PRIMARY_TMDBID;
        $searchTerms["contentType"] = Film::CONTENT_FILM;

                // Test
        $film = $api->getFilmBySearch($searchTerms);

                // Verify
        $this->assertFalse(empty($film), "Film should not be empty");
        $this->assertEquals(self::TESTFILM_PRIMARY_TITLE, $film->getTitle(), "title");
        $this->assertEquals(self::TESTFILM_PRIMARY_IMDBID, $film->getUniqueName(Constants::SOURCE_IMDB), "IMDb ID");

        // Movie by title, year, contentType
                // Setup
        $searchTerms = array();
        $searchTerms["title"] = self::TESTFILM_PRIMARY_TITLE;
        $searchTerms["year"] = self::TESTFILM_PRIMARY_YEAR;
        $searchTerms["contentType"] = Film::CONTENT_FILM;

                // Test
        $film = $api->getFilmBySearch($searchTerms);

                // Verify
        $this->assertFalse(empty($film), "Film should not be empty");
        $this->assertEquals(self::TESTFILM_PRIMARY_TMDBID, $film->getUniqueName($sourceName), "TMDb ID");
        $this->assertEquals(self::TESTFILM_PRIMARY_IMDBID, $film->getUniqueName(Constants::SOURCE_IMDB), "IMDb ID");

        // Movie by title, year
                // Setup
        $searchTerms = array();
        $searchTerms["title"] = self::TESTFILM_PRIMARY_TITLE;
        $searchTerms["year"] = self::TESTFILM_PRIMARY_YEAR;

                // Test
        $film = $api->getFilmBySearch($searchTerms);

                // Verify
        $this->assertFalse(empty($film), "Film should not be empty");
        $this->assertEquals(self::TESTFILM_PRIMARY_TMDBID, $film->getUniqueName($sourceName), "TMDb ID");
        $this->assertEquals(self::TESTFILM_PRIMARY_IMDBID, $film->getUniqueName(Constants::SOURCE_IMDB), "IMDb ID");
        $this->assertEquals(Film::CONTENT_FILM, $film->getContentType(), "contentType");

        // Series by uniqueName, contentType
                // Setup
        $searchTerms = array();
        $searchTerms["uniqueName"] = self::TESTSERIES_TMDBID;
        $searchTerms["contentType"] = Film::CONTENT_TV_SERIES;

                // Test
        $film = $api->getFilmBySearch($searchTerms);

                // Verify
        $this->assertFalse(empty($film), "Film should not be empty");
        $this->assertEquals(self::TESTSERIES_TITLE, $film->getTitle(), "title");
        $this->assertEquals(self::TESTSERIES_YEAR, $film->getYear(), "year");

        // Series by title, year, contentType
                // Setup
        $searchTerms = array();
        $searchTerms["title"] = self::TESTSERIES_TITLE;
        $searchTerms["year"] = self::TESTSERIES_YEAR;
        $searchTerms["contentType"] = Film::CONTENT_TV_SERIES;

                // Test
        $film = $api->getFilmBySearch($searchTerms);

                // Verify
        $this->assertFalse(empty($film), "Film should not be empty");
        $this->assertEquals(self::TESTSERIES_TMDBID, $film->getUniqueName($sourceName), "TMDb ID");

        // Series by title, year
                // Setup
        $searchTerms = array();
        $searchTerms["title"] = self::TESTSERIES_TITLE;
        $searchTerms["year"] = self::TESTSERIES_YEAR;

                // Test
        $film = $api->getFilmBySearch($searchTerms);

                // Verify
        $this->assertFalse(empty($film), "Film should not be empty");
        $this->assertEquals(self::TESTSERIES_TMDBID, $film->getUniqueName($sourceName), "TMDb ID");
        $this->assertEquals(Film::CONTENT_TV_SERIES, $film->getContentType(), "contentType");
    }

    /**
     * @covers \RatingSync\TmdbApi::cacheFilmDetail
     * @depends testObjectCanBeConstructed
     */
    public function testCacheFilmDetail()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $api = new TmdbApiExt();
        $sourceName = $api->_getSourceName();
        $verifyFilename = __DIR__ . DIRECTORY_SEPARATOR . "testfile" . DIRECTORY_SEPARATOR . "verify_cache_filmdetail.json";

        // Movie
                // Setup
        $uniqueName = "mvTestUniqueName";
        $film = new Film();
        $film->setContentType(Film::CONTENT_FILM);
        $film->setUniqueName($uniqueName, $sourceName);
        $filmDetailStr = '{"id": "'.$api->_getSourceIdFromUniqueName($uniqueName).'"}';
        
        $fp = fopen($verifyFilename, "w");
        fwrite($fp, $filmDetailStr);
        fclose($fp);
        
                // Test
        $api->cacheFilmDetail($filmDetailStr, $film);

                // Verify
        $testFilename = Constants::cacheFilePath() . $sourceName . "_film_" . $film->getUniqueName($sourceName) . "." . TmdbApi::API_RESPONSE_FORMAT;
        $this->assertFileExists($testFilename, 'Cache file exists');
        $this->assertFileEquals($verifyFilename, $testFilename, 'cache file vs verify file');
        unlink($verifyFilename);
        unlink($testFilename);

        // TV Series
                // Setup
        $uniqueName = "tsTestUniqueName";
        $film = new Film();
        $film->setContentType(Film::CONTENT_TV_SERIES);
        $film->setUniqueName($uniqueName, $sourceName);
        $filmDetailStr = '{"id": "'.$api->_getSourceIdFromUniqueName($uniqueName).'"}';
        
        $fp = fopen($verifyFilename, "w");
        fwrite($fp, $filmDetailStr);
        fclose($fp);
        
                // Test
        $api->cacheFilmDetail($filmDetailStr, $film);

                // Verify
        $testFilename = Constants::cacheFilePath() . $sourceName . "_film_" . $film->getUniqueName($sourceName) . "." . TmdbApi::API_RESPONSE_FORMAT;
        $this->assertFileExists($testFilename, 'Cache file exists');
        $this->assertFileEquals($verifyFilename, $testFilename, 'cache file vs verify file');
        unlink($verifyFilename);
        unlink($testFilename);

        // TV Episode
                // Setup
        $uniqueName = "teTestUniqueName";
        $film = new Film();
        $film->setContentType(Film::CONTENT_TV_EPISODE);
        $film->setUniqueName($uniqueName, $sourceName);
        $film->setSeason(2);
        $film->setEpisodeNumber(4);
        $filmDetailStr = '{"id": "'.$api->_getSourceIdFromUniqueName($uniqueName).'"}';
        
        $fp = fopen($verifyFilename, "w");
        fwrite($fp, $filmDetailStr);
        fclose($fp);
        
                // Test
        $api->cacheFilmDetail($filmDetailStr, $film);

                // Verify
        $testFilename = Constants::cacheFilePath() . $sourceName . "_film_" . $film->getUniqueName($sourceName);
        $testFilename .= "_" . $film->getSeason() . "_" . $film->getEpisodeNumber() . "." . TmdbApi::API_RESPONSE_FORMAT;
        $this->assertFileExists($testFilename, 'Cache file exists');
        $this->assertFileEquals($verifyFilename, $testFilename, 'cache file vs verify file');
        unlink($verifyFilename);
        unlink($testFilename);

        // TV Episode without Season
                // Setup
        $uniqueName = "teTestUniqueName";
        $film = new Film();
        $film->setContentType(Film::CONTENT_TV_EPISODE);
        $film->setUniqueName($uniqueName, $sourceName);
        $film->setEpisodeNumber(4);
        $filmDetailStr = '{"id": "'.$api->_getSourceIdFromUniqueName($uniqueName).'"}';
        
        $fp = fopen($verifyFilename, "w");
        fwrite($fp, $filmDetailStr);
        fclose($fp);
        
                // Test
        $api->cacheFilmDetail($filmDetailStr, $film);

                // Verify
        $testFilename = Constants::cacheFilePath() . $sourceName . "_film_" . $film->getUniqueName($sourceName);
        $testFilename .= "_" . $film->getEpisodeNumber() . "." . TmdbApi::API_RESPONSE_FORMAT;
        $this->assertFileExists($testFilename, 'Cache file exists');
        $this->assertFileEquals($verifyFilename, $testFilename, 'cache file vs verify file');
        unlink($verifyFilename);
        unlink($testFilename);

        // TV Episode without Episode number
                // Setup
        $uniqueName = "teTestUniqueName";
        $film = new Film();
        $film->setContentType(Film::CONTENT_TV_EPISODE);
        $film->setUniqueName($uniqueName, $sourceName);
        $film->setSeason(2);
        $filmDetailStr = '{"id": "'.$api->_getSourceIdFromUniqueName($uniqueName).'"}';
        
        $fp = fopen($verifyFilename, "w");
        fwrite($fp, $filmDetailStr);
        fclose($fp);
        
                // Test
        $api->cacheFilmDetail($filmDetailStr, $film);

                // Verify
        $testFilename = Constants::cacheFilePath() . $sourceName . "_film_" . $film->getUniqueName($sourceName);
        $testFilename .= "_" . $film->getSeason() . "." . TmdbApi::API_RESPONSE_FORMAT;
        $this->assertFileExists($testFilename, 'Cache file exists');
        $this->assertFileEquals($verifyFilename, $testFilename, 'cache file vs verify file');
        unlink($verifyFilename);
        unlink($testFilename);

        // TV Episode without season or epsiode number
                // Setup
        $uniqueName = "teTestUniqueName";
        $film = new Film();
        $film->setContentType(Film::CONTENT_TV_EPISODE);
        $film->setUniqueName($uniqueName, $sourceName);
        $filmDetailStr = '{"id": "'.$api->_getSourceIdFromUniqueName($uniqueName).'"}';
        
        $fp = fopen($verifyFilename, "w");
        fwrite($fp, $filmDetailStr);
        fclose($fp);
        
                // Test
        $api->cacheFilmDetail($filmDetailStr, $film);

                // Verify
        $testFilename = Constants::cacheFilePath() . $sourceName . "_film_" . $film->getUniqueName($sourceName);
        $testFilename .= "." . TmdbApi::API_RESPONSE_FORMAT;
        $this->assertFileExists($testFilename, 'Cache file exists');
        $this->assertFileEquals($verifyFilename, $testFilename, 'cache file vs verify file');
        unlink($verifyFilename);
        unlink($testFilename);
    }

    /**
     * @covers \RatingSync\TmdbApi::getFilmDetailFromCache
     * @depends testCacheFilmDetail
     */
    public function testGetFilmDetailFromCache()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $api = new TmdbApiExt();
        $sourceName = $api->_getSourceName();

        // Movie
                // Setup
        $uniqueName = "mvTestUniqueName";
        $contentType = Film::CONTENT_FILM;
        $film = new Film();
        $film->setContentType($contentType);
        $film->setUniqueName($uniqueName, $sourceName);
        $filmDetailStr = '{"id": "'.$api->_getSourceIdFromUniqueName($uniqueName).'"}';
        $api->cacheFilmDetail($filmDetailStr, $film);
        
                // Test
        $contents = $api->getFilmDetailFromCache($film);

                // Verify
        $this->assertFalse(empty($contents), "Contents should not be empty");
        $this->assertEquals($filmDetailStr, $contents, "Contents should match the test cache file");
        $testFilename = Constants::cacheFilePath() . $api->_getFilmDetailCacheFilename($film);
        unlink($testFilename);

        // TV Series
                // Setup
        $uniqueName = "tsTestUniqueName";
        $contentType = Film::CONTENT_TV_SERIES;
        $film = new Film();
        $film->setContentType($contentType);
        $film->setUniqueName($uniqueName, $sourceName);
        $filmDetailStr = '{"id": "'.$api->_getSourceIdFromUniqueName($uniqueName).'"}';
        $api->cacheFilmDetail($filmDetailStr, $film);
        
                // Test
        $contents = $api->getFilmDetailFromCache($film);

                // Verify
        $this->assertFalse(empty($contents), "Contents should not be empty");
        $this->assertEquals($filmDetailStr, $contents, "Contents should match the test cache file");
        $testFilename = Constants::cacheFilePath() . $api->_getFilmDetailCacheFilename($film);
        unlink($testFilename);

        // TV Episode
                // Setup
        $uniqueName = "teTestUniqueName";
        $contentType = Film::CONTENT_TV_EPISODE;
        $film = new Film();
        $film->setContentType($contentType);
        $film->setUniqueName($uniqueName, $sourceName);
        $filmDetailStr = '{"id": "'.$api->_getSourceIdFromUniqueName($uniqueName).'"}';
        $api->cacheFilmDetail($filmDetailStr, $film);
        
                // Test
        $contents = $api->getFilmDetailFromCache($film);

                // Verify
        $this->assertFalse(empty($contents), "Contents should not be empty");
        $this->assertEquals($filmDetailStr, $contents, "Contents should match the test cache file");
        $testFilename = Constants::cacheFilePath() . $api->_getFilmDetailCacheFilename($film);
        unlink($testFilename);

        // TV Episode without Season
                // Setup
        $uniqueName = "teTestUniqueName";
        $contentType = Film::CONTENT_TV_EPISODE;
        $film = new Film();
        $film->setContentType($contentType);
        $film->setUniqueName($uniqueName, $sourceName);
        $filmDetailStr = '{"id": "'.$api->_getSourceIdFromUniqueName($uniqueName).'"}';
        $api->cacheFilmDetail($filmDetailStr, $film);
        
                // Test
        $contents = $api->getFilmDetailFromCache($film);

                // Verify
        $this->assertFalse(empty($contents), "Contents should not be empty");
        $this->assertEquals($filmDetailStr, $contents, "Contents should match the test cache file");
        $testFilename = Constants::cacheFilePath() . $api->_getFilmDetailCacheFilename($film);
        unlink($testFilename);

        // TV Episode without Episode number
                // Setup
        $uniqueName = "teTestUniqueName";
        $contentType = Film::CONTENT_TV_EPISODE;
        $film = new Film();
        $film->setContentType($contentType);
        $film->setUniqueName($uniqueName, $sourceName);
        $filmDetailStr = '{"id": "'.$api->_getSourceIdFromUniqueName($uniqueName).'"}';
        $api->cacheFilmDetail($filmDetailStr, $film);
        
                // Test
        $contents = $api->getFilmDetailFromCache($film);

                // Verify
        $this->assertFalse(empty($contents), "Contents should not be empty");
        $this->assertEquals($filmDetailStr, $contents, "Contents should match the test cache file");
        $testFilename = Constants::cacheFilePath() . $api->_getFilmDetailCacheFilename($film);
        unlink($testFilename);

        // TV Episode without season or epsiode number
                // Setup
        $uniqueName = "teTestUniqueName";
        $contentType = Film::CONTENT_TV_EPISODE;
        $film = new Film();
        $film->setContentType($contentType);
        $film->setUniqueName($uniqueName, $sourceName);
        $filmDetailStr = '{"id": "'.$api->_getSourceIdFromUniqueName($uniqueName).'"}';
        $api->cacheFilmDetail($filmDetailStr, $film);
        
                // Test
        $contents = $api->getFilmDetailFromCache($film);

                // Verify
        $this->assertFalse(empty($contents), "Contents should not be empty");
        $this->assertEquals($filmDetailStr, $contents, "Contents should match the test cache file");
        $testFilename = Constants::cacheFilePath() . $api->_getFilmDetailCacheFilename($film);
        unlink($testFilename);
    }

    /**
     * @covers \RatingSync\TmdbApi::getFilmDetailFromCache
     * @depends testCacheFilmDetail
     * @depends testGetFilmDetailFromCache
     */
    public function testGetFilmDetailFromCacheNeverCache()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $api = new TmdbApiExt();
        $sourceName = $api->_getSourceName();
        $uniqueName = "mvTestUniqueName";
        $contentType = Film::CONTENT_FILM;
        $film = new Film();
        $film->setContentType($contentType);
        $film->setUniqueName($uniqueName, $sourceName);
        $filmDetailStr = '{"id": "'.$api->_getSourceIdFromUniqueName($uniqueName).'"}';
        $api->cacheFilmDetail($filmDetailStr, $film);
        
        // Test
        $contents = $api->getFilmDetailFromCache($film, Constants::USE_CACHE_NEVER);

        // Verify
        $this->assertEmpty($contents, "Contents should be empty");
        $testFilename = Constants::cacheFilePath() . $api->_getFilmDetailCacheFilename($film);
        if (!file_exists($testFilename)) {
            unlink($testFilename);
        }
    }

    /**
     * @covers \RatingSync\TmdbApi::cacheSeason
     * @depends testObjectCanBeConstructed
     */
    public function testCacheSeason()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $api = new TmdbApiExt();
        $sourceName = $api->_getSourceName();
        $seriesFilmId = 100;
        $seasonNum = 2;
        $verifyFilename = __DIR__ . DIRECTORY_SEPARATOR . "testfile" . DIRECTORY_SEPARATOR . "verify_cache_season.json";
        $seasonStr = '{"id": '.$seriesFilmId.'}';
        
        $fp = fopen($verifyFilename, "w");
        fwrite($fp, $seasonStr);
        fclose($fp);
        
        // Test
        $api->cacheSeason($seasonStr, $seriesFilmId, $seasonNum);

        // Verify
        $testFilename = Constants::cacheFilePath() . $sourceName . "_series_" . $seriesFilmId . "_season_" . $seasonNum . "." . TmdbApi::API_RESPONSE_FORMAT;
        $this->assertFileExists($testFilename, 'Cache file exists');
        $this->assertFileEquals($verifyFilename, $testFilename, 'cache file vs verify file');
        unlink($verifyFilename);
        unlink($testFilename);
    }

    /**
     * @covers \RatingSync\TmdbApi::getSeasonFromCache
     * @depends testCacheSeason
     */
    public function testGetSeasonFromCache()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $api = new TmdbApiExt();
        $uniqueName = "mvTestUniqueName";
        $seriesFilmId = 100;
        $seasonNum = 2;
        $seasonStr = '{"id": '.$seriesFilmId.'}';
        $api->cacheSeason($seasonStr, $seriesFilmId, $seasonNum);
        
        // Test
        $contents = $api->getSeasonFromCache($seriesFilmId, $seasonNum, Constants::USE_CACHE_ALWAYS);

        // Verify
        $this->assertFalse(empty($contents), "Contents should not be empty");
        $this->assertEquals($seasonStr, $contents, "Contents should match the test cache file");
        $testFilename = Constants::cacheFilePath() . $api->_getSeasonCacheFilename($seriesFilmId, $seasonNum);
        unlink($testFilename);
    }

    /**
     * @covers \RatingSync\TmdbApi::getSeasonFromCache
     * @depends testCacheSeason
     * @depends testGetSeasonFromCache
     */
    public function testGetSeasonFromCacheNeverCache()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $api = new TmdbApiExt();
        $uniqueName = "mvTestUniqueName";
        $seriesFilmId = 100;
        $seasonNum = 2;
        $seasonStr = '{"id": '.$seriesFilmId.'}';
        $api->cacheSeason($seasonStr, $seriesFilmId, $seasonNum);
        
        // Test
        $contents = $api->getSeasonFromCache($seriesFilmId, $seasonNum, Constants::USE_CACHE_NEVER);

        // Verify
        $this->assertEmpty($contents, "Contents should be empty");
        $testFilename = Constants::cacheFilePath() . $api->_getSeasonCacheFilename($seriesFilmId, $seasonNum);
        if (!file_exists($testFilename)) {
            unlink($testFilename);
        }
    }

    /**
     * @covers \RatingSync\TmdbApi::getSeasonFromApi
     * @depends testObjectCanBeConstructed
     * @depends testThisIsTheDefaultApi
     */
    public function testGetSeasonFromApiNullSeriesId()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        // Setup
        $api = new TmdbApiExt();
        $seriesFilmId = null;
        $seasonNum = self::TESTEPISODE_SEASON_NUM;

        // Test
        $api->getSeasonFromApi($seriesFilmId, $seasonNum);
    }

    /**
     * @covers \RatingSync\TmdbApi::getSeasonFromApi
     * @depends testObjectCanBeConstructed
     * @depends testThisIsTheDefaultApi
     */
    public function testGetSeasonFromApiStringSeriesId()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        // Setup
        $api = new TmdbApiExt();
        $seriesFilmId = "One";
        $seasonNum = self::TESTEPISODE_SEASON_NUM;

        // Test
        $api->getSeasonFromApi($seriesFilmId, $seasonNum);
    }

    /**
     * @covers \RatingSync\TmdbApi::getSeasonFromApi
     * @depends testObjectCanBeConstructed
     * @depends testThisIsTheDefaultApi
     */
    public function testGetSeasonFromApiNullSeasonNum()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        // Setup
        $api = new TmdbApiExt();
        $sourceName = $api->_getSourceName();
        $film = new Film();
        $series = Film::getFilmFromDbByUniqueName(self::TESTSERIES_TMDBID, $sourceName);
        $seriesFilmId = $series->getId();
        $seasonNum = null;

        // Test
        $api->getSeasonFromApi($seriesFilmId, $seasonNum);
    }

    /**
     * @covers \RatingSync\TmdbApi::getSeasonFromApi
     * @depends testObjectCanBeConstructed
     * @depends testThisIsTheDefaultApi
     */
    public function testGetSeasonFromApiStringSeasonNum()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        // Setup
        $api = new TmdbApiExt();
        $sourceName = $api->_getSourceName();
        $film = new Film();
        $series = Film::getFilmFromDbByUniqueName(self::TESTSERIES_TMDBID, $sourceName);
        $seriesFilmId = $series->getId();
        $seasonNumStr = "One";

        // Test
        $api->getSeasonFromApi($seriesFilmId, $seasonNumStr);
    }

    /**
     * @covers \RatingSync\TmdbApi::getSeasonFromApi
     * @depends testObjectCanBeConstructed
     * @depends testSetupTvSeries
     * @depends testThisIsTheDefaultApi
     */
    public function testGetSeasonFromApi()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $constants = TmdbApiTest::getConstants();
        $api = new TmdbApiExt();
        $sourceName = $api->_getSourceName();
        $film = new Film();
        $series = Film::getFilmFromDbByUniqueName($constants["seriesUniqueName"], $sourceName);
        $seriesFilmId = $series->getId();
        $seasonNum = $constants["episodeSeasonNum"];

        // Test
        $season = $api->getSeasonFromApi($seriesFilmId, $seasonNum, Constants::USE_CACHE_NEVER);

        // Verify
        $this->assertFalse(empty($season), "Season should not be empty");
                // Season attrs
        $seasonTitle = $season->getName();
        $seasonYear = $season->getYear();
        $seasonNum = $season->getNumber();
        $seasonImage = $season->getImage();
                // Episode attrs
        $episodeTitle = $season->getEpisodeTitle($constants["episodeEpisodeNum"]);
        $episodeYear = $season->getEpisodeYear($constants["episodeEpisodeNum"]);
        $episodeSeasonNum = $season->getEpisodeSeasonNumber($constants["episodeEpisodeNum"]);
        $episodeSourceId = $season->getEpisodeSourceId($constants["episodeEpisodeNum"]);
        $episodeUniqueName = $season->getEpisodeUniqueName($constants["episodeEpisodeNum"]);
        $episodeImage = $season->getEpisodeImage($constants["episodeEpisodeNum"]);
        $episodeUserScore = $season->getEpisodeUserScore($constants["episodeEpisodeNum"]);
        //$episodeDirectors = $season->getEpisodeDirectors($constants["episodeEpisodeNum"]);
                // Assert attrs
        //$this->assertEquals(, $seasonTitle, "seasonTitle");
        $this->assertEquals($constants["episodeYear"], $seasonYear, "seasonYear");
        $this->assertEquals($constants["episodeSeasonNum"], $seasonNum, "seasonNum");
        //$this->assertEquals(, $seasonImage, "seasonImage");
        $this->assertEquals($constants["episodeEpisodeTitle"], $episodeTitle, "episodeTitle");
        $this->assertEquals($constants["episodeYear"], $episodeYear, "episodeYear");
        $this->assertEquals($constants["episodeSeasonNum"], $episodeSeasonNum, "episodeSeasonNum");
        $this->assertEquals($constants["episodeUniqueName"], $episodeUniqueName, "episodeUniqueName");
        $this->assertEquals($constants["episodeImage"], $episodeImage, "episodeImage");
        $this->assertEquals(round($constants["episodeUserScore"]), round($episodeUserScore), "episodeUserScore");
        //$this->assertEquals($constants["episodeDirectors"], $episodeDirectors, "episodeDirectors");
    }
}

?>