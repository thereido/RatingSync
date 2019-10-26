<?php
/**
 * TmdbApi PHPUnit
 */
namespace RatingSync;

require_once "../src/TmdbApi.php";
require_once "DatabaseTest.php";
require_once "RatingSyncTestCase.php";

const TESTFILM_PRIMARY_TITLE = "Frozen";
const TESTFILM_PRIMARY_YEAR = 2013;
const TESTFILM_PRIMARY_IMDBID = "tt2294629";
const TESTFILM_PRIMARY_TMDBID = "mv109445"; // mv + TESTFILM_PRIMARY_TMDBID_SOURCEID
const TESTFILM_PRIMARY_TMDBID_SOURCEID = "109445";
const TESTFILM_PRIMARY_USER_SCORE = 7.3;
const TESTFILM_PRIMARY_CRITIC_SCORE = 7.4;
const TESTFILM_PRIMARY_IMAGE = "/eFnGmj63QPUpK7QUWSOUhypIQOT.jpg";
const TESTFILM_PRIMARY_DIRECTORS = array("Chris Buck", "Jennifer Lee");
const TESTFILM_PRIMARY_GENRES = array("Animation", "Adventure", "Family");

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

const TESTEPISODE_TITLE = "Game of Thrones";
const TESTEPISODE_EPISODETITLE = "Garden of Bones";
const TESTEPISODE_YEAR = 2012;
const TESTEPISODE_IMDBID = "tt2069319";
const TESTEPISODE_TMDBID = "ep63069"; // ep + TESTEPISODE_TMDBID_SOURCEID
const TESTEPISODE_TMDBID_SOURCEID = "63069";
const TESTEPISODE_USER_SCORE = 8.216;
const TESTEPISODE_CRITIC_SCORE = null;
const TESTEPISODE_IMAGE = "/4j2j97GFao2NX4uAtMbr0Qhx2K2.jpg";
const TESTEPISODE_DIRECTORS = array("David Petrarca");
const TESTEPISODE_GENRES = array("Action", "Adventure", "Drama");
const TESTEPISODE_SEASON_NUM = 2;
const TESTEPISODE_EPISODE_NUM = 4;

// Class to expose protected members and functions
class TmdbApiExt extends \RatingSync\TmdbApi {
    function _getSourceName() { return $this->sourceName; }
    function _buildUrlFilmDetail($film) { return $this->buildUrlFilmDetail($film); }
    function _getSearchResultFromResponse($response, $title, $year, $searchType) { return $this->getSearchResultFromResponse($response, $title, $year, $searchType); }
    function _getUniqueNameFromSourceId($sourceId, $contentType) { return $this->getUniqueNameFromSourceId($sourceId, $contentType); }
}

class TmdbApiTest extends RatingSyncTestCase
{
    protected function setUp(): void
    {
        parent::setup();
        //$this->verbose = true;
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
        $film->setUniqueName(TESTFILM_PRIMARY_TMDBID, $api->_getSourceName());

        // Test
        $url = $api->_buildUrlFilmDetail($film);

        // Verify
        $this->assertStringContainsString("/movie/".TESTFILM_PRIMARY_TMDBID_SOURCEID, $url);
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
        $film->setUniqueName(TESTFILM_PRIMARY_TMDBID, $api->_getSourceName());

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
        $film->setUniqueName(TESTSERIES_TMDBID, $api->_getSourceName());

        // Test
        $url = $api->_buildUrlFilmDetail($film);

        // Verify
        $this->assertStringContainsString("/tv/".TESTSERIES_TMDBID_SOURCEID, $url);
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
        $film->setUniqueName(TESTSERIES_TMDBID, $api->_getSourceName());

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
     */
    public function testSetupTvSeries()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $api = new TmdbApiExt();
        $film = new Film();
        $film->setTitle(TESTSERIES_TITLE);
        $film->setYear(TESTSERIES_YEAR);
        $film->setContentType(Film::CONTENT_TV_SERIES);
        $film->setUniqueName(TESTSERIES_TMDBID, $api->_getSourceName());

        // Test
        $success = $film->saveToDb();

        // Verify
        $this->assertTrue($success, "saveToDb() should succeed");
    }
    
    /**
     * @covers \RatingSync\TmdbApi::buildUrlFilmDetail
     * @depends testSetupTvSeries
     */
    public function testBuildUrlFilmDetailTvEpisode()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $api = new TmdbApiExt();
        $film = new Film();
        $parent = Film::getFilmFromDbByUniqueName(TESTSERIES_TMDBID, $api->_getSourceName());
        $film->setParentId($parent->getId());
        $film->setContentType(Film::CONTENT_TV_EPISODE);
        $film->setSeason(TESTEPISODE_SEASON_NUM);
        $film->setEpisodeNumber(TESTEPISODE_EPISODE_NUM);

        // Test
        $url = $api->_buildUrlFilmDetail($film);

        // Verify
        $validUrl = "/tv/" . TESTSERIES_TMDBID_SOURCEID;
        $validUrl .= "/season/" . TESTEPISODE_SEASON_NUM;
        $validUrl .= "/episode/" . TESTEPISODE_EPISODE_NUM;
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
        $film->setUniqueName(TESTEPISODE_TMDBID, $api->_getSourceName());

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
        $film->setUniqueName(TESTEPISODE_TMDBID, $api->_getSourceName());

        // Test
        $url = $api->_buildUrlFilmDetail($film);
    }
    
    /**
     * @covers \RatingSync\TmdbApi::buildUrlFilmDetail
     * @depends testBuildUrlFilmDetailTvEpisode
     */
    public function testBuildUrlFilmDetailTvEpisodeWithoutSeasonNum()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        // Setup
        $api = new TmdbApiExt();
        $film = new Film();
        $parent = Film::getFilmFromDbByUniqueName(TESTSERIES_TMDBID, $api->_getSourceName());
        $film->setParentId($parent->getId());
        $film->setContentType(Film::CONTENT_TV_EPISODE);
        $film->getEpisodeNumber(TESTEPISODE_EPISODE_NUM);

        // Test
        $url = $api->_buildUrlFilmDetail($film, TESTSERIES_TMDBID);
    }
    
    /**
     * @covers \RatingSync\TmdbApi::buildUrlFilmDetail
     * @depends testBuildUrlFilmDetailTvEpisode
     */
    public function testBuildUrlFilmDetailTvEpisodeWithoutEpisodeNum()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        // Setup
        $api = new TmdbApiExt();
        $film = new Film();
        $parent = Film::getFilmFromDbByUniqueName(TESTSERIES_TMDBID, $api->_getSourceName());
        $film->setParentId($parent->getId());
        $film->setContentType(Film::CONTENT_TV_EPISODE);
        $film->setSeason(TESTEPISODE_SEASON_NUM);

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
        $title = TESTFILM_PRIMARY_TITLE;
        $year = TESTFILM_PRIMARY_YEAR;
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
        $resultReturned = $api->_getSearchResultFromResponse($response, $title, $year, $searchType);

            // Verify
        $this->assertEquals($matchingResult, $resultReturned, "Movie search");

        // TV Series Search
            // Setup
        $searchType = Film::CONTENT_TV_SERIES;
        $title = TESTSERIES_TITLE;
        $year = TESTSERIES_YEAR;
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
        $resultReturned = $api->_getSearchResultFromResponse($response, $title, $year, $searchType);

            // Verify
        $this->assertEquals($matchingResult, $resultReturned, "TV Series search");
    }
    
    /**
     * @covers \RatingSync\TmdbApi::searchForUniqueName
     * @depends testObjectCanBeConstructed
     */
    public function testSearchForUniqueNameWithImdbId()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $api = new TmdbApi();
        $film = new Film();
        $film->setUniqueName(TESTFILM_PRIMARY_IMDBID, Constants::SOURCE_IMDB);

        // Test
        $uniqueName = $api->searchForUniqueName($film);

        // Verify
        $this->assertEquals(TESTFILM_PRIMARY_TMDBID, $uniqueName);
    }
    
    /**
     * @covers \RatingSync\TmdbApi::searchForUniqueName
     * @depends testGetSearchResultFromResponse
     */
    public function testSearchForUniqueNameMovie()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $api = new TmdbApi();
        $film = new Film();
        $film->setContentType(Film::CONTENT_FILM);
        $film->setTitle(TESTFILM_PRIMARY_TITLE);
        $film->setYear(TESTFILM_PRIMARY_YEAR);

        // Test
        $uniqueName = $api->searchForUniqueName($film);

        // Verify
        $this->assertEquals(TESTFILM_PRIMARY_TMDBID, $uniqueName);
    }
    
    /**
     * @covers \RatingSync\TmdbApi::searchForUniqueName
     * @depends testObjectCanBeConstructed
     */
    public function testSearchForUniqueNameMovieWithoutContentType()
    {$this->start(__CLASS__, __FUNCTION__);

        // This might be supported in the future. If that happens this
        // line about the exception can be removed.
        $this->expectException(\Exception::class);

        // Setup
        $api = new TmdbApi();
        $film = new Film();
        $film->setTitle(TESTFILM_PRIMARY_TITLE);
        $film->setYear(TESTFILM_PRIMARY_YEAR);

        // Test
        $uniqueName = $api->searchForUniqueName($film);

        // Verify
        $this->assertEquals(TESTFILM_PRIMARY_TMDBID, $uniqueName);
    }
    
    /**
     * @covers \RatingSync\TmdbApi::searchForUniqueName
     * @depends testObjectCanBeConstructed
     */
    public function testSearchForUniqueNameTvSeries()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $api = new TmdbApi();
        $film = new Film();
        $film->setContentType(Film::CONTENT_TV_SERIES);
        $film->setTitle(TESTSERIES_TITLE);
        $film->setYear(TESTSERIES_YEAR);

        // Test
        $uniqueName = $api->searchForUniqueName($film);

        // Verify
        $this->assertEquals(TESTSERIES_TMDBID, $uniqueName);
    }
    
    /**
     * @covers \RatingSync\TmdbApi::searchForUniqueName
     * @depends testObjectCanBeConstructed
     */
    public function testSearchForUniqueNameTvSeriesWithoutContentType()
    {$this->start(__CLASS__, __FUNCTION__);

        // This might be supported in the future. If that happens this
        // line about the exception can be removed.
        $this->expectException(\Exception::class);

        // Setup
        $api = new TmdbApi();
        $film = new Film();
        $film->setTitle(TESTSERIES_TITLE);
        $film->setYear(TESTSERIES_YEAR);

        // Test
        $uniqueName = $api->searchForUniqueName($film);

        // Verify
        $this->assertEquals(TESTFILM_PRIMARY_TMDBID, $uniqueName);
    }
    
    /**
     * @covers \RatingSync\TmdbApi::searchForUniqueName
     * @depends testObjectCanBeConstructed
     */
    public function testSearchForUniqueNameTvEpisode()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $api = new TmdbApi();
        $film = new Film();
        $film->setContentType(Film::CONTENT_TV_EPISODE);
        $film->setTitle(TESTEPISODE_TITLE);
        $film->setYear(TESTEPISODE_YEAR);
        $film->setEpisodeTitle(TESTEPISODE_EPISODETITLE);
        $film->setSeason(TESTEPISODE_SEASON_NUM);

        // Test
        $uniqueName = $api->searchForUniqueName($film);

        // Verify
        $this->assertEmpty($uniqueName, "TmdbApi::searchForUniqueName() does not support Tv Episodes");
    }
    
    /**
     * @covers \RatingSync\TmdbApi::searchForUniqueName
     * @depends testObjectCanBeConstructed
     */
    public function testSearchForUniqueNameTvEpisodeWithoutContentType()
    {$this->start(__CLASS__, __FUNCTION__);

        // This might be supported in the future. If that happens this
        // line about the exception can be removed.
        $this->expectException(\Exception::class);

        // Setup
        $api = new TmdbApi();
        $film = new Film();
        $film->setTitle(TESTEPISODE_TITLE);
        $film->setYear(TESTEPISODE_YEAR);

        // Test
        $uniqueName = $api->searchForUniqueName($film);

        // Verify
        $this->assertEmpty($uniqueName, "TmdbApi::searchForUniqueName() does not support Tv Episodes");
    }
    
    /**
     * @covers \RatingSync\TmdbApi::searchForUniqueName
     * @depends testObjectCanBeConstructed
     */
    public function testSearchForUniqueNameWithUniqueName()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $api = new TmdbApiExt();
        $film = new Film();
        $film->setUniqueName(TESTFILM_PRIMARY_TMDBID, $api->_getSourceName());

        // Test
        $uniqueName = $api->searchForUniqueName($film);

        // Verify
        $this->assertEquals(TESTFILM_PRIMARY_TMDBID, $uniqueName);
    }
    
    /**
     * @covers \RatingSync\TmdbApi::getJsonFromApiForFilmDetail
     * @depends testObjectCanBeConstructed
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
        $film->setUniqueName(TESTFILM_PRIMARY_TMDBID, $sourceName);

        // Test
        $filmJson = $api->getJsonFromApiForFilmDetail($film, true, Constants::USE_CACHE_NEVER);

        // Verify
        $tmdbId = $api->jsonValue($filmJson, Source::ATTR_UNIQUE_NAME, TmdbApi::REQUEST_DETAIL_MOVIE);
        $title = $api->jsonValue($filmJson, Film::ATTR_TITLE, TmdbApi::REQUEST_DETAIL_MOVIE);
        $year = substr($api->jsonValue($filmJson, Film::ATTR_YEAR, TmdbApi::REQUEST_DETAIL_MOVIE), 0, 4);
        $image = $api->jsonValue($filmJson, Source::ATTR_IMAGE, TmdbApi::REQUEST_DETAIL_MOVIE);
        $genres = $api->jsonValue($filmJson, Film::ATTR_GENRES, TmdbApi::REQUEST_DETAIL_MOVIE);
        $userScore = $api->jsonValue($filmJson, Source::ATTR_USER_SCORE, TmdbApi::REQUEST_DETAIL_MOVIE);
        $this->assertEquals(TESTFILM_PRIMARY_TMDBID, $api->_getUniqueNameFromSourceId($tmdbId, Film::CONTENT_FILM), "TMDb ID");
        $this->assertEquals(TESTFILM_PRIMARY_TITLE, $title, "Title");
        $this->assertEquals(TESTFILM_PRIMARY_YEAR, $year, "Year");
        $this->assertEquals(TESTFILM_PRIMARY_IMAGE, $image, "Image");
        $this->assertEquals(TESTFILM_PRIMARY_GENRES, $genres, "Genres");
        $this->assertEquals(TESTFILM_PRIMARY_USER_SCORE, $userScore, "User score");

                // TV Series
        // Setup
        $film->setContentType(Film::CONTENT_TV_SERIES);
        $film->setUniqueName(TESTSERIES_TMDBID, $sourceName);

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
        $this->assertEquals(TESTSERIES_TMDBID, $api->_getUniqueNameFromSourceId($tmdbId, Film::CONTENT_TV_SERIES), "TMDb ID");
        $this->assertEquals(TESTSERIES_TITLE, $title, "Title");
        $this->assertEquals(TESTSERIES_YEAR, $year, "Year");
        $this->assertEquals(TESTSERIES_IMAGE, $image, "Image");
        $this->assertEquals(TESTSERIES_SEASON_COUNT, $seasonCount, "Season count");
        $this->assertEquals(TESTSERIES_GENRES, $genres, "Genres");
        $this->assertEquals(TESTSERIES_USER_SCORE, $userScore, "User score");
    }
    
    /**
     * @covers \RatingSync\TmdbApi::getJsonFromApiForFilmDetail
     * @depends testGetJsonFromApiForFilmDetail
     */
    public function testGetJsonFromApiForFilmDetailEpisode()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $api = new TmdbApiExt();
        $film = new Film();
        $sourceName = $api->_getSourceName();
        $parent = Film::getFilmFromDbByUniqueName(TESTSERIES_TMDBID, $sourceName);
        $film->setParentId($parent->getId());
        $film->setContentType(Film::CONTENT_TV_EPISODE);
        $film->setUniqueName(TESTEPISODE_TMDBID, $sourceName);
        $film->setSeason(TESTEPISODE_SEASON_NUM);
        $film->setEpisodeNumber(TESTEPISODE_EPISODE_NUM);

        // Test
        $filmJson = $api->getJsonFromApiForFilmDetail($film, true, Constants::USE_CACHE_NEVER);

        // Verify
        $tmdbId = $api->jsonValue($filmJson, Source::ATTR_UNIQUE_NAME, TmdbApi::REQUEST_DETAIL_EPISODE);
        $year = substr($api->jsonValue($filmJson, Film::ATTR_YEAR, TmdbApi::REQUEST_DETAIL_EPISODE), 0, 4);
        $image = $api->jsonValue($filmJson, Source::ATTR_IMAGE, TmdbApi::REQUEST_DETAIL_EPISODE);
        $episodeTitle = $api->jsonValue($filmJson, Film::ATTR_EPISODE_TITLE, TmdbApi::REQUEST_DETAIL_EPISODE);
        $seasonNum = $api->jsonValue($filmJson, Film::ATTR_SEASON_NUM, TmdbApi::REQUEST_DETAIL_EPISODE);
        $episodeNum = $api->jsonValue($filmJson, Film::ATTR_EPISODE_NUM, TmdbApi::REQUEST_DETAIL_EPISODE);
        $userScore = $api->jsonValue($filmJson, Source::ATTR_USER_SCORE, TmdbApi::REQUEST_DETAIL_EPISODE);
        $this->assertEquals(TESTEPISODE_TMDBID, $api->_getUniqueNameFromSourceId($tmdbId, Film::CONTENT_TV_EPISODE));
        $this->assertEquals(TESTEPISODE_YEAR, $year, "Year");
        $this->assertEquals(TESTEPISODE_IMAGE, $image, "Image");
        $this->assertEquals(TESTEPISODE_EPISODETITLE, $episodeTitle, "Episode title");
        $this->assertEquals(TESTEPISODE_SEASON_NUM, $seasonNum, "Season number");
        $this->assertEquals(TESTEPISODE_EPISODE_NUM, $episodeNum, "Episode number");
        $this->assertEquals(TESTEPISODE_USER_SCORE, $userScore, "Season number");
    }
}

?>