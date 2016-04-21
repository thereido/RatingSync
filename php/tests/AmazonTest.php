<?php
/**
 * Amazon PHPUnit
 */
namespace RatingSync;

require_once "../src/Amazon.php";
require_once "10DatabaseTest.php";
require_once "RatingSyncTestCase.php";

const TEST_AMAZON_USERNAME = "testamazonuser";
const TEST_AMAZON_UNIQUENAME = "3526";
const TEST_AMAZON_UNIQUEALT = "";
const TEST_AMAZON_TITLE = "Absentia";
const TEST_AMAZON_YEAR = 2012;

// Class to expose protected members and functions
class AmazonExt extends \RatingSync\Amazon {
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
}

class AmazonTest extends RatingSyncTestCase
{
    public function setUp()
    {
        parent::setup();
        //$this->verbose = true;
    }

    /**
     * @covers            \RatingSync\Amazon::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testCannotBeConstructedFromNull()
    {$this->start(__CLASS__, __FUNCTION__);

        new Amazon(null);
    }

    /**
     * @covers            \RatingSync\Amazon::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testCannotBeConstructedFromEmptyUsername()
    {$this->start(__CLASS__, __FUNCTION__);

        new Amazon("");
    }

    /**
     * @covers \RatingSync\Amazon::__construct
     */
    public function testObjectCanBeConstructed()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new Amazon(TEST_AMAZON_USERNAME);
    }

    /**
     * @covers \RatingSync\Amazon::cacheFilmDetailPage
     * @depends testObjectCanBeConstructed
     */
    public function testCacheFilmDetailPage()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new AmazonExt(TEST_AMAZON_USERNAME);
        $film = new Film();
        $film->setUniqueName(TEST_AMAZON_UNIQUENAME, $site->_getSourceName());
        
        $page = "<html><body><h2>Film Detail</h2></body></html>";
        $verifyFilename = "testfile" . DIRECTORY_SEPARATOR . "verify_cache_filmdetailpage.xml";
        $fp = fopen($verifyFilename, "w");
        fwrite($fp, $page);
        fclose($fp);
        
        $site->cacheFilmDetailPage($page, $film);
        $testFilename = Constants::cacheFilePath() . $site->_getSourceName() . "_" . TEST_AMAZON_USERNAME . "_film_" . $film->getUniqueName($site->_getSourceName()) . ".html";
        $this->assertFileExists($testFilename, 'Cache file exists');
        $this->assertFileEquals($verifyFilename, $testFilename, 'cache file vs verify file');
        
        unlink($verifyFilename);
        unlink($testFilename);
    }

    /**
     * @covers \RatingSync\Amazon::getFilmDetailFromWebsite
     * @depends testObjectCanBeConstructed
     * @expectedException \InvalidArgumentException
     */
    public function testGetFilmDetailFromWebsiteFromNull()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new Amazon(TEST_AMAZON_USERNAME);
        $site->getFilmDetailFromWebsite(null);
    }

    /**
     * @covers \RatingSync\Amazon::getFilmDetailFromWebsite
     * @depends testObjectCanBeConstructed
     * @expectedException \InvalidArgumentException
     */
    public function testGetFilmDetailFromWebsiteFromString()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new Amazon(TEST_AMAZON_USERNAME);
        $site->getFilmDetailFromWebsite("String_Not_Film_Object");
    }

    /**
     * @covers \RatingSync\Amazon::getFilmDetailFromWebsite
     * @depends testObjectCanBeConstructed
     * @expectedException \Exception
     */
    public function testGetFilmDetailFromWebsiteNoMatch()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new AmazonExt(TEST_AMAZON_USERNAME);
        $film = new Film();
        $film->setUniqueName("NO_FILMID_MATCH", $site->_getSourceName());
        $site->getFilmDetailFromWebsite($film, true);
    }

    /**
     * @covers \RatingSync\Amazon::getFilmDetailFromWebsite
     * @depends testObjectCanBeConstructed
     */
    public function testGetFilmDetailFromWebsite()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new AmazonExt(TEST_AMAZON_USERNAME);

        $film = new Film();
        $film->setUniqueName(TEST_AMAZON_UNIQUENAME, $site->_getSourceName());
        $site->getFilmDetailFromWebsite($film, true);

        $this->assertEquals(TEST_AMAZON_TITLE, $film->getTitle(), 'Title');
        $this->assertEquals(TEST_AMAZON_YEAR, $film->getYear(), 'Year');
        $this->assertEquals(TEST_AMAZON_UNIQUENAME, $film->getUniqueName($site->_getSourceName()), 'Unique Name');

        // Not available in the detail page
        $this->assertNull($film->getContentType(), 'Content Type');
        $this->assertEquals(0, count($film->getDirectors()), 'Director(s)');
        $this->assertEquals(0, count($film->getGenres()), 'Genres');
    }

    public function testResetDb()
    {$this->start(__CLASS__, __FUNCTION__);

        DatabaseTest::resetDb();
    }
    
    /**
     * @covers \RatingSync\Amazon::getSearchUrl
     * @depends testObjectCanBeConstructed
     */
    public function testGetSearchUrl()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new Amazon(TEST_AMAZON_USERNAME);

        $args = array("query" => TEST_AMAZON_TITLE);
        $url = $site->getSearchUrl($args);
        $this->assertEquals("/search?q=".TEST_AMAZON_TITLE, $url);
    }
    
    /**
     * @covers \RatingSync\Amazon::getSearchUrl
     * @depends testGetSearchUrl
     */
    public function testGetSearchUrlSpecialChars()
    {$this->start(__CLASS__, __FUNCTION__);
    
        $site = new Amazon(TEST_AMAZON_USERNAME);

        $args = array("query" => "Mr. Peabody & Sherman");
        $url = $site->getSearchUrl($args);
        $this->assertEquals("/search?q=Mr.+Peabody+%26+Sherman", $url);
    }

    /**
     * @covers \RatingSync\Amazon::searchWebsiteForUniqueFilm
     * @depends testGetSearchUrl
     */
    public function testSearchWebsiteForUniqueFilm()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $site = new AmazonExt(TEST_AMAZON_USERNAME);
        $film = new Film();
        $film->setTitle(TEST_AMAZON_TITLE);
        $film->setYear(TEST_AMAZON_YEAR);

        // Test
        $site->searchWebsiteForUniqueFilm($film);

        // Verify
        $this->assertEquals(TEST_AMAZON_UNIQUENAME, $film->getUniqueName($site->_getSourceName()), "Unique name");
    }

    /**
     * @covers \RatingSync\Amazon::getStreamUrl
     * @depends testObjectCanBeConstructed
     */
    public function testGetStreamUrl()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $site = new AmazonExt(TEST_AMAZON_USERNAME);
        $film = new Film();
        $film->setUniqueName(TEST_AMAZON_UNIQUENAME, $site->_getSourceName());
        $film->setTitle("empty_title");
        $film->saveToDb();

        // Test
        $url = $site->getStreamUrl($film->getId());

        // Verify
        $this->assertEquals("http://www.amazon.com/gp/video/primesignup?&t=0m0s&redirectToAsin=B00778C6V4&tag=iw_prime_movie-20&ref_=asc_homepage", $url, $site->_getSourceName()." streaming URL");
    }

    /**
     * @covers \RatingSync\Amazon::getStreamUrl
     * @depends testSearchWebsiteForUniqueFilm
     */
    public function testGetStreamUrlEmptyUniqueName()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $site = new AmazonExt(TEST_AMAZON_USERNAME);
        $film = new Film();
        $film->setTitle(TEST_AMAZON_TITLE);
        $film->setYear(TEST_AMAZON_YEAR);
        $film->saveToDb();

        // Test
        $url = $site->getStreamUrl($film->getId());

        // Verify
        $this->assertEquals("http://www.amazon.com/gp/video/primesignup?&t=0m0s&redirectToAsin=B00778C6V4&tag=iw_prime_movie-20&ref_=asc_homepage", $url, $site->_getSourceName()." streaming URL");
    }

    /**
     * @covers \RatingSync\Amazon::getStreamUrl
     * @depends testSearchWebsiteForUniqueFilm
     */
    public function testGetStreamUrlEmptyUniqueNameEmptyYear()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $site = new AmazonExt(TEST_AMAZON_USERNAME);
        $film = new Film();
        $film->setTitle(TEST_AMAZON_TITLE);
        $film->saveToDb();

        // Test
        $url = $site->getStreamUrl($film->getId());

        // Verify
        $this->assertNull($url);
    }

    /**
     * @covers \RatingSync\Amazon::getStreamUrl
     * @depends testObjectCanBeConstructed
     */
    public function testGetStreamUrlFilmNoLongerAvailable()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $site = new AmazonExt(TEST_AMAZON_USERNAME);
        $film = new Film();
        $film->setUniqueName("100000000", $site->_getSourceName());
        $film->setTitle("empty_title");
        $film->saveToDb();

        // Test
        $url = $site->getStreamUrl($film->getId());

        // Verify
        $this->assertEmpty($url, "Should be empty ($url)");
    }
}

?>
