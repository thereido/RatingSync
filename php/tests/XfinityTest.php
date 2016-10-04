<?php
/**
 * Xfinity PHPUnit
 */
namespace RatingSync;

require_once "../src/Xfinity.php";
require_once "10DatabaseTest.php";
require_once "RatingSyncTestCase.php";

const TEST_XFINITY_USERNAME = "testratingsync";

const TEST_XFINITY_UNIQUENAME = "4776771327619264112"; // Vacation
const TEST_XFINITY_UNIQUEALT = "Vacation";
const TEST_XFINITY_TITLE = "Vacation";
const TEST_XFINITY_YEAR = 2015;

const TEST_XFINITY_UNIQUENAME_TV = "5771920406804707112"; // Grimm
const TEST_XFINITY_UNIQUEALT_TV = "Grimm";
const TEST_XFINITY_UNIQUEEPISODE_TV = "4612535181645549112"; // Season 5 Episode 11
const TEST_XFINITY_TITLE_TV = "Grimm";
const TEST_XFINITY_YEAR_TV = 2011;

// Class to expose protected members and functions
class XfinityExt extends \RatingSync\Xfinity {
    function _getHttp() { return $this->http; }
    function _getSourceName() { return $this->sourceName; }
    function _getUsername() { return $this->username; }

    function _parseDetailPageForTitle($page, $film, $overwrite) { return $this->parseDetailPageForTitle($page, $film, $overwrite); }
    function _parseDetailPageForFilmYear($page, $film, $overwrite) { return $this->parseDetailPageForFilmYear($page, $film, $overwrite); }
    function _parseDetailPageForImage($page, $film, $overwrite) { return $this->parseDetailPageForImage($page, $film, $overwrite); }
    function _parseDetailPageForContentType($page, $film, $overwrite) { return $this->parseDetailPageForContentType($page, $film, $overwrite); }
    function _parseDetailPageForUniqueName($page, $film, $overwrite) { return $this->parseDetailPageForUniqueName($page, $film, $overwrite); }
    function _parseDetailPageForGenres($page, $film, $overwrite) { return $this->parseDetailPageForGenres($page, $film, $overwrite); }
    function _parseDetailPageForDirectors($page, $film, $overwrite) { return $this->parseDetailPageForDirectors($page, $film, $overwrite); }

    function _streamAvailableFromDetailPage($page, $film, $onlyFree = true) { return $this->streamAvailableFromDetailPage($page, $film, $onlyFree); }
}

class XfinityTest extends RatingSyncTestCase
{
    public function setUp()
    {
        parent::setup();
        //$this->verbose = true;
    }

    /**
     * @covers            \RatingSync\Xfinity::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testCannotBeConstructedFromNull()
    {$this->start(__CLASS__, __FUNCTION__);

        new Xfinity(null);
    }

    /**
     * @covers            \RatingSync\Xfinity::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testCannotBeConstructedFromEmptyUsername()
    {$this->start(__CLASS__, __FUNCTION__);

        new Xfinity("");
    }

    /**
     * @covers \RatingSync\Xfinity::__construct
     */
    public function testObjectCanBeConstructed()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new Xfinity(TEST_XFINITY_USERNAME);
    }

    /**
     * @covers \RatingSync\Xfinity::cacheFilmDetailPage
     * @depends testObjectCanBeConstructed
     */
    public function testCacheFilmDetailPage()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new XfinityExt(TEST_XFINITY_USERNAME);
        $film = new Film();
        $film->setUniqueName(TEST_XFINITY_UNIQUENAME, $site->_getSourceName()); // The Duff
        $film->setUniqueAlt(TEST_XFINITY_UNIQUEALT, $site->_getSourceName());
        
        $page = "<html><body><h2>Film Detail</h2></body></html>";
        $verifyFilename = "testfile" . DIRECTORY_SEPARATOR . "verify_cache_filmdetailpage.xml";
        $fp = fopen($verifyFilename, "w");
        fwrite($fp, $page);
        fclose($fp);
        
        $site->cacheFilmDetailPage($page, $film);
        $testFilename = Constants::cacheFilePath() . $site->_getSourceName() . "_" . TEST_XFINITY_USERNAME . "_film_" . $film->getUniqueName($site->_getSourceName()) . ".html";
        $this->assertFileExists($testFilename, 'Cache file exists');
        $this->assertFileEquals($verifyFilename, $testFilename, 'cache file vs verify file');
        
        unlink($verifyFilename);
        unlink($testFilename);
    }

    /**
     * @covers \RatingSync\Xfinity::getFilmDetailFromWebsite
     * @depends testObjectCanBeConstructed
     * @expectedException \InvalidArgumentException
     */
    public function testGetFilmDetailFromWebsiteFromNull()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new Xfinity(TEST_XFINITY_USERNAME);
        $site->getFilmDetailFromWebsite(null);
    }

    /**
     * @covers \RatingSync\Xfinity::getFilmDetailFromWebsite
     * @depends testObjectCanBeConstructed
     * @expectedException \InvalidArgumentException
     */
    public function testGetFilmDetailFromWebsiteFromString()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new Xfinity(TEST_XFINITY_USERNAME);
        $site->getFilmDetailFromWebsite("String_Not_Film_Object");
    }

    /**
     * @covers \RatingSync\Xfinity::getFilmDetailFromWebsite
     * @depends testObjectCanBeConstructed
     * @expectedException \Exception
     */
    public function testGetFilmDetailFromWebsiteNoMatch()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new XfinityExt(TEST_XFINITY_USERNAME);
        $film = new Film();
        $film->setUniqueName("NO_FILMID_MATCH", $site->_getSourceName());
        $film->setUniqueAlt(TEST_XFINITY_UNIQUEALT, $site->_getSourceName());
        $site->getFilmDetailFromWebsite($film, true);
    }

    /**
     * @covers \RatingSync\Xfinity::getFilmDetailFromWebsite
     * @depends testObjectCanBeConstructed
     */
    /* Xfinity detail page unavailable
    public function testGetFilmDetailFromWebsite()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new XfinityExt(TEST_XFINITY_USERNAME);

        $film = new Film();
        $film->setUniqueName(TEST_XFINITY_UNIQUENAME, $site->_getSourceName());
        $film->setUniqueAlt(TEST_XFINITY_UNIQUEALT, $site->_getSourceName());
        $site->getFilmDetailFromWebsite($film, true);

        $this->assertEquals(TEST_XFINITY_TITLE, $film->getTitle(), 'Title');
        $this->assertEquals(TEST_XFINITY_YEAR, $film->getYear(), 'Year');
        $this->assertEquals(TEST_XFINITY_UNIQUENAME, $film->getUniqueName($site->_getSourceName()), 'Unique Name');
        $this->assertEquals(Film::CONTENT_FILM, $film->getContentType(), 'Content Type');

        // Not available in the detail page
        $this->assertEquals(0, count($film->getDirectors()), 'Director(s)');
        $this->assertEquals(0, count($film->getGenres()), 'Genres');
    }
    */

    /**
     * @covers \RatingSync\Xfinity::getFilmDetailFromWebsite
     * @depends testObjectCanBeConstructed
     */
    /* Xfinity detail page unavailable
    public function testGetFilmDetailFromWebsiteTv()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new XfinityExt(TEST_XFINITY_USERNAME);

        $film = new Film();
        $film->setUniqueName(TEST_XFINITY_UNIQUENAME_TV, $site->_getSourceName());
        $film->setUniqueEpisode(TEST_XFINITY_UNIQUEEPISODE_TV, $site->_getSourceName());
        $film->setUniqueAlt(TEST_XFINITY_UNIQUEALT_TV, $site->_getSourceName());
        $film->setContentType(Film::CONTENT_TV_SERIES);
        $site->getFilmDetailFromWebsite($film, true);

        $this->assertEquals(TEST_XFINITY_TITLE_TV, $film->getTitle(), 'Title');
        $this->assertEquals(TEST_XFINITY_YEAR_TV, $film->getYear(), 'Year');
        $this->assertEquals(TEST_XFINITY_UNIQUENAME_TV, $film->getUniqueName($site->_getSourceName()), 'Unique Name');

        // Not available in the detail page
        $this->assertEquals(0, count($film->getDirectors()), 'Director(s)');
        $this->assertEquals(0, count($film->getGenres()), 'Genres');
    }
    */
    
    /**
     * @covers \RatingSync\Xfinity::getSearchUrl
     * @depends testObjectCanBeConstructed
     */
    public function testGetSearchUrl()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new Xfinity(TEST_XFINITY_USERNAME);

        $args = array("query" => TEST_XFINITY_TITLE);
        $url = $site->getSearchUrl($args);
        $this->assertEquals("/search?q=".TEST_XFINITY_TITLE, $url);
    }
    
    /**
     * @covers \RatingSync\Xfinity::getSearchUrl
     * @depends testGetSearchUrl
     */
    public function testGetSearchUrlSpecialChars()
    {$this->start(__CLASS__, __FUNCTION__);
    
        $site = new Xfinity(TEST_XFINITY_USERNAME);

        $args = array("query" => "Mr. Peabody & Sherman");
        $url = $site->getSearchUrl($args);
        $this->assertEquals("/search?q=Mr.+Peabody+%26+Sherman", $url);
    }

    public function testResetDb()
    {$this->start(__CLASS__, __FUNCTION__);

        DatabaseTest::resetDb();
    }

    /**
     * @covers \RatingSync\Xfinity::searchWebsiteForUniqueFilm
     * @depends testGetSearchUrl
     */
    /* Xfinity search unavailable
    public function testSearchWebsiteForUniqueFilm()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $site = new XfinityExt(TEST_XFINITY_USERNAME);
        $film = new Film();
        $film->setTitle(TEST_XFINITY_TITLE);
        $film->setYear(TEST_XFINITY_YEAR);

        // Test
        $site->searchWebsiteForUniqueFilm($film);

        // Verify
        $this->assertEquals(TEST_XFINITY_UNIQUENAME, $film->getUniqueName($site->_getSourceName()), "Unique name");
        $this->assertEquals(TEST_XFINITY_UNIQUEALT, $film->getUniqueAlt($site->_getSourceName()), "Unique name");
    }
    */

    /**
     * @covers \RatingSync\Xfinity::streamAvailableFromDetailPage
     * @depends testObjectCanBeConstructed
     */
    /* Xfinity unavailable for streams
    public function testStreamAvailableFromDetailPage()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $site = new XfinityExt(TEST_XFINITY_USERNAME);
        $title = "San Andreas";
        $year = 2015;
        $searchTerms = array();
        $searchTerms["title"] = $title;
        $searchTerms["year"] = $year;
        $searchTerms["sourceName"] = $site->_getSourceName();
        $film = \RatingSync\search($searchTerms, TEST_XFINITY_USERNAME)['match'];
        $page = $site->getFilmDetailPage($film, Constants::USE_CACHE_ALWAYS, true);

        // Test
        $available = $site->_streamAvailableFromDetailPage($page, $film);

        // Verify
        $this->assertTrue($available, "The film '$title ($year)' should be available for Xfinity");
    }
    */

    /**
     * @covers \RatingSync\Xfinity::streamAvailableFromDetailPage
     * @depends testStreamAvailableFromDetailPage
     */
    /* Xfinity unavailable for streams
    public function testStreamAvailableFromDetailPageNotAvailable()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $site = new XfinityExt(TEST_XFINITY_USERNAME);
        $title = "Experimenter";
        $year = 2015;
        $searchTerms = array();
        $searchTerms["title"] = $title;
        $searchTerms["year"] = $year;
        $film = \RatingSync\search($searchTerms, TEST_XFINITY_USERNAME)['match'];
        $page = $site->getFilmDetailPage($film, Constants::USE_CACHE_ALWAYS, true);

        // Test
        $available = $site->_streamAvailableFromDetailPage($page, $film);

        // Verify
        $this->assertFalse($available, "The film '$title ($year)' should not be available for Xfinity");
    }
    */

    /**
     * @covers \RatingSync\Xfinity::getStreamUrl
     * @depends testStreamAvailableFromDetailPage
     */
    /* Xfinity unavailable for streams
    public function testGetStreamUrl()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $site = new XfinityExt(TEST_XFINITY_USERNAME);
        $film = new Film();
        $film->setUniqueName(TEST_XFINITY_UNIQUENAME, $site->_getSourceName());
        $film->setUniqueAlt(TEST_XFINITY_UNIQUEALT, $site->_getSourceName());
        $film->setTitle("empty_title");
        $film->saveToDb();

        // Test
        $url = $site->getStreamUrl($film->getId());

        // Verify
        $this->assertEquals("http://xfinitytv.comcast.net/watch/".TEST_XFINITY_UNIQUEALT."/".TEST_XFINITY_UNIQUENAME."/"."movies#filter=online", $url, $site->_getSourceName()." streaming URL");
    }
    */

    /**
     * @covers \RatingSync\Xfinity::getStreamUrl
     * @depends testStreamAvailableFromDetailPage
     */
    /* Xfinity unavailable for streams
    public function testGetStreamUrlTvSeries()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $site = new XfinityExt(TEST_XFINITY_USERNAME);
        $film = new Film();
        $film->setUniqueName(TEST_XFINITY_UNIQUENAME_TV, $site->_getSourceName());
        $film->setUniqueAlt(TEST_XFINITY_UNIQUEALT_TV, $site->_getSourceName());
        $film->setContentType(Film::CONTENT_TV_SERIES);
        $film->setTitle("empty_title");
        $film->saveToDb();
        $filmId = $film->getId();
        $url = $site->getStreamUrl($filmId);
        $source = $film->getSource($site->_getSourceName());
        $source->setStreamUrl($url);
        $source->refreshStreamDate();
        $source->saveFilmSourceToDb($filmId);

        // Test
        $url = $site->getStreamUrl($filmId);

        // Verify
        $prefix = "http://xfinitytv.comcast.net/watch/".TEST_XFINITY_UNIQUEALT_TV."/".TEST_XFINITY_UNIQUENAME_TV."/full-episodes#filter=online";
        $this->assertStringStartsWith($prefix, $url, $site->_getSourceName()." streaming URL");
    }
    */

    /**
     * @covers \RatingSync\Xfinity::getStreamUrl
     * @depends testStreamAvailableFromDetailPage
     */
    /* Xfinity unavailable for streams
    public function testGetStreamUrlTvEpisode()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $site = new XfinityExt(TEST_XFINITY_USERNAME);
        $film = new Film();
        $film->setUniqueName(TEST_XFINITY_UNIQUENAME_TV, $site->_getSourceName());
        $film->setUniqueAlt(TEST_XFINITY_UNIQUEALT_TV, $site->_getSourceName());
        $film->setUniqueEpisode(TEST_XFINITY_UNIQUEEPISODE_TV, $site->_getSourceName());
        $film->setContentType(Film::CONTENT_TV_SERIES);
        $film->setTitle("empty_title");
        $film->saveToDb();
        $filmId = $film->getId();
        $url = $site->getStreamUrl($filmId);
        $source = $film->getSource($site->_getSourceName());
        $source->setStreamUrl($url);
        $source->refreshStreamDate();
        $source->saveFilmSourceToDb($filmId);

        // Test
        $url = $site->getStreamUrl($filmId);

        // Verify
        $this->assertEquals("http://xfinitytv.comcast.net/watch/".TEST_XFINITY_UNIQUEALT_TV."/".TEST_XFINITY_UNIQUENAME_TV."/full-episodes#filter=online&episode=".TEST_XFINITY_UNIQUEEPISODE_TV, $url, $site->_getSourceName()." streaming URL");
    }
    */

    /**
     * @covers \RatingSync\Xfinity::getStreamUrl
     * @depends testSearchWebsiteForUniqueFilm
     */
    /* Xfinity unavailable for streams
    public function testGetStreamUrlEmptyUniqueName()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $site = new XfinityExt(TEST_XFINITY_USERNAME);
        $film = new Film();
        $film->setTitle(TEST_XFINITY_TITLE);
        $film->setYear(TEST_XFINITY_YEAR);
        $film->saveToDb();

        // Test
        $url = $site->getStreamUrl($film->getId());

        // Verify
        $this->assertEquals("http://xfinitytv.comcast.net/watch/".TEST_XFINITY_UNIQUEALT."/".TEST_XFINITY_UNIQUENAME."/movies#filter=online", $url, $site->_getSourceName()." streaming URL");
    }
    */

    /**
     * @covers \RatingSync\Xfinity::getStreamUrl
     * @depends testSearchWebsiteForUniqueFilm
     */
    /* Xfinity unavailable for streams
    public function testGetStreamUrlEmptyUniqueNameEmptyYear()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $site = new Xfinity(TEST_XFINITY_USERNAME);
        $film = new Film();
        $film->setTitle(TEST_XFINITY_TITLE);
        $film->saveToDb();

        // Test
        $url = $site->getStreamUrl($film->getId());

        // Verify
        $this->assertNull($url);
    }
    */

    /**
     * @covers \RatingSync\Xfinity::getStreamUrl
     * @depends testObjectCanBeConstructed
     */
    /* Xfinity unavailable for streams
    public function testGetStreamUrlFilmNoLongerAvailable()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $site = new XfinityExt(TEST_XFINITY_USERNAME);
        $film = new Film();
        $film->setUniqueName("100000000", $site->_getSourceName());
        $film->setUniqueAlt("2222", $site->_getSourceName());
        $film->setTitle("empty_title");
        $film->saveToDb();

        // Test
        $url = $site->getStreamUrl($film->getId());

        // Verify
        $this->assertEmpty($url, "Should be empty ($url)");
    }
    */
}

?>
