<?php
/**
 * Netflix PHPUnit
 */
namespace RatingSync;

require_once "../src/Netflix.php";
require_once "10DatabaseTest.php";

const TEST_NETFLIX_USERNAME = "testnetflixuser";
const TEST_NETFLIX_UNIQUENAME = "80047396";

// Class to expose protected members and functions
class NetflixExt extends \RatingSync\Netflix {
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

class NetflixTest extends \PHPUnit_Framework_TestCase
{
    public $debug;
    public $timer;

    public function setUp()
    {
        $this->debug = false;
    }

    public function start($className, $functionName)
    {
        if ($this->debug) {
            echo " $className::$functionName ";
            $this->timer = new \DateTime();
        }
    }

    public function tearDown()
    {
        if ($this->debug) { echo $this->timer->diff(date_create())->format('%s secs') . "\n"; }
    }

    /**
     * @covers            \RatingSync\Netflix::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testCannotBeConstructedFromNull()
    {$this->start(__CLASS__, __FUNCTION__);

        new Netflix(null);
    }

    /**
     * @covers            \RatingSync\Netflix::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testCannotBeConstructedFromEmptyUsername()
    {$this->start(__CLASS__, __FUNCTION__);

        new Netflix("");
    }

    /**
     * @covers \RatingSync\Netflix::__construct
     */
    public function testObjectCanBeConstructed()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new Netflix(TEST_NETFLIX_USERNAME);
    }

    /**
     * @covers \RatingSync\Netflix::cacheFilmDetailPage
     * @depends testObjectCanBeConstructed
     */
    public function testCacheFilmDetailPage()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new NetflixExt(TEST_NETFLIX_USERNAME);
        $film = new Film($site->http);
        $film->setUniqueName(TEST_NETFLIX_UNIQUENAME, $site->_getSourceName()); // Experimenter
        
        $page = "<html><body><h2>Film Detail</h2></body></html>";
        $verifyFilename = "testfile" . DIRECTORY_SEPARATOR . "verify_cache_filmdetailpage.xml";
        $fp = fopen($verifyFilename, "w");
        fwrite($fp, $page);
        fclose($fp);
        
        $site->cacheFilmDetailPage($page, $film);
        $testFilename = Constants::cacheFilePath() . $site->_getSourceName() . "_" . TEST_NETFLIX_USERNAME . "_film_" . $film->getUniqueName($site->_getSourceName()) . ".html";
        $this->assertFileExists($testFilename, 'Cache file exists');
        $this->assertFileEquals($verifyFilename, $testFilename, 'cache file vs verify file');
        
        unlink($verifyFilename);
        unlink($testFilename);
    }

    /**
     * @covers \RatingSync\Netflix::getFilmDetailFromWebsite
     * @depends testObjectCanBeConstructed
     * @expectedException \InvalidArgumentException
     */
    public function testGetFilmDetailFromWebsiteFromNull()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new Netflix(TEST_NETFLIX_USERNAME);
        $site->getFilmDetailFromWebsite(null);
    }

    /**
     * @covers \RatingSync\Netflix::getFilmDetailFromWebsite
     * @depends testObjectCanBeConstructed
     * @expectedException \InvalidArgumentException
     */
    public function testGetFilmDetailFromWebsiteFromString()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new Netflix(TEST_NETFLIX_USERNAME);
        $site->getFilmDetailFromWebsite("String_Not_Film_Object");
    }

    /**
     * @covers \RatingSync\Netflix::getFilmDetailFromWebsite
     * @depends testObjectCanBeConstructed
     * @expectedException \Exception
     */
    public function testGetFilmDetailFromWebsiteNoMatch()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new NetflixExt(TEST_NETFLIX_USERNAME);
        $film = new Film($site->http);
        $film->setUniqueName("NO_FILMID_MATCH", $site->_getSourceName());
        $site->getFilmDetailFromWebsite($film, true);
    }

    /**
     * @covers \RatingSync\Netflix::getFilmDetailFromWebsite
     * @depends testObjectCanBeConstructed
     */
    public function testGetFilmDetailFromWebsite()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new NetflixExt(TEST_NETFLIX_USERNAME);

        $film = new Film($site->http);
        $film->setUniqueName(TEST_NETFLIX_UNIQUENAME, $site->_getSourceName());
        $site->getFilmDetailFromWebsite($film, true);

        $this->assertEquals("Experimenter", $film->getTitle(), 'Title');
        $this->assertEquals(2015, $film->getYear(), 'Year');
        $this->assertEquals(TEST_NETFLIX_UNIQUENAME, $film->getUniqueName($site->_getSourceName()), 'Unique Name');

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
     * @covers \RatingSync\Netflix::getStreamingUrl
     * @depends testObjectCanBeConstructed
     */
    public function testGetStreamUrl()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $site = new NetflixExt(TEST_NETFLIX_USERNAME);
        $film = new Film($site->http);
        $film->setUniqueName(TEST_NETFLIX_UNIQUENAME, $site->_getSourceName());
        $film->setTitle("empty_title");
        $film->saveToDb();

        // Test
        $url = $site->getStreamingUrl($film->getId());

        // Verify
        $this->assertEquals("http://www.netflix.com/title/".TEST_NETFLIX_UNIQUENAME, $url, $site->_getSourceName()." streaming URL");
    }

    /**
     * @covers \RatingSync\Netflix::getStreamingUrl
     * @depends testObjectCanBeConstructed
     */
    public function testGetStreamUrlEmptyUniqueName()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $site = new NetflixExt(TEST_NETFLIX_USERNAME);
        $film = new Film($site->http);
        $film->setTitle("Experimenter");
        $film->setYear(2015);
        $film->saveToDb();

        // Test
        $url = $site->getStreamingUrl($film->getId());

        // Verify
        $this->assertEquals("http://www.netflix.com/title/".TEST_NETFLIX_UNIQUENAME, $url, $site->_getSourceName()." streaming URL");
    }

    /**
     * @covers \RatingSync\Netflix::getStreamingUrl
     * @depends testObjectCanBeConstructed
     * @expectedException \InvalidArgumentException
     */
    public function testGetStreamUrlEmptyUniqueNameEmptyYear()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $site = new NetflixExt(TEST_NETFLIX_USERNAME);
        $film = new Film($site->http);
        $film->setTitle("Experimenter");
        $film->saveToDb();

        // Test
        $url = $site->getStreamingUrl($film->getId());
    }

    /**
     * @covers \RatingSync\Netflix::getStreamingUrl
     * @depends testObjectCanBeConstructed
     */
    public function testGetStreamUrlFilmNoLongerAvailable()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $site = new NetflixExt(TEST_NETFLIX_USERNAME);
        $film = new Film($site->http);
        $film->setUniqueName("100000000", $site->_getSourceName());
        $film->setTitle("empty_title");
        $film->saveToDb();

        // Test
        $url = $site->getStreamingUrl($film->getId());

        // Verify
        $this->assertEmpty($url, "Should be empty ($url)");
    }
}

?>
