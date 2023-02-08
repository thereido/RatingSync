<?php
/**
 * Site PHPUnit
 */
namespace RatingSync;

require_once __DIR__ .DIRECTORY_SEPARATOR. ".." .DIRECTORY_SEPARATOR. "src" .DIRECTORY_SEPARATOR. "Site.php";

require_once "SiteChild.php";
require_once "HttpTest.php";
require_once "ImdbTest.php";
require_once "DatabaseTest.php";
require_once "RatingSyncTestCase.php";

const TEST_SITE_USERNAME = TEST_IMDB_USERNAME;

/**
 Suggested tests for a child class
   - testCannotBeConstructedFromNull
   - testCannotBeConstructedFromEmptyUsername
   - testObjectCanBeConstructed
   - testCacheFilmDetailPage
   - testGetFilmDetailFromWebsiteFromNull
   - testGetFilmDetailFromWebsiteFromString
   - testGetFilmDetailFromWebsiteWithoutUniqueName
   - testGetFilmDetailFromWebsiteNoMatch
   - testGetFilmDetailFromWebsite
   - testGetFilmDetailFromWebsiteOverwriteTrueOverEmpty
   - testGetFilmDetailFromWebsiteOverwriteTrueOverOriginalData
   - testGetFilmDetailFromWebsiteOverwriteFalseOverOriginalData
   - testGetFilmDetailFromWebsiteOverwriteFalseOverEmpty
   - testGetFilmDetailFromWebsiteOverwriteDefault
   - testGetFilmDetailFromWebsiteOverwriteFalseOverEmptyFilm
   - testMultipleGenres
   - testMultipleDirectors
   - testParseDetailPageEmptyFilmOverwriteTrue
   - testParseDetailPageEmptyFilmOverwriteFalse
   - testParseDetailPageFullFilmOverwriteTrue
   - testParseDetailPageFullFilmOverwriteFalse
   - testParseFilmsFromFile
   - testFromExportFileToFilmObjectAndBackToXml
 */

class SiteTest extends RatingSyncTestCase
{
    protected function setUp(): void
    {
        parent::setup();
        //$this->verbose = true;
    }

    /**
     * @covers            \RatingSync\Site::__construct
     */
    public function testCannotBeConstructedFromNull()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        new SiteChild(null);
    }

    /**
     * @covers            \RatingSync\Site::__construct
     */
    public function testCannotBeConstructedFromEmptyUsername()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        new SiteChild("");
    }

    /**
     * @covers \RatingSync\Site::__construct
     */
    public function testObjectCanBeConstructed()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new SiteChild(TEST_SITE_USERNAME);

        $this->assertTrue(true); // Making sure we made it this far
    }

    /**
     * @covers \RatingSync\Site::validateAfterConstructor
     * @depends testObjectCanBeConstructed
     */
    public function testValidateAfterConstructorNoHttp()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new SiteChild(TEST_SITE_USERNAME);
        $site->_setSourceName(Constants::SOURCE_IMDB);
        $site->_setHttp(null);
        $this->assertFalse($site->_validateAfterConstructor());
    }

    /**
     * @covers \RatingSync\Site::validateAfterConstructor
     * @depends testObjectCanBeConstructed
     */
    public function testValidateAfterConstructorNoSourceName()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new SiteChild(TEST_SITE_USERNAME);
        $site->_setSourceName(null);
        $site->_setHttp(new Http(Constants::SOURCE_IMDB, TEST_SITE_USERNAME));
        $this->assertFalse($site->_validateAfterConstructor());
    }

    /**
     * @covers \RatingSync\Site::validateAfterConstructor
     * @depends testObjectCanBeConstructed
     */
    public function testValidateAfterConstructorGood()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new SiteChild(TEST_SITE_USERNAME);
        $this->assertTrue($site->_validateAfterConstructor());
    }

    /**
     * @covers \RatingSync\Site::cacheFilmDetailPage
     * @depends testObjectCanBeConstructed
     */
    public function testCacheFilmDetailPage()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new SiteChild(TEST_SITE_USERNAME);
        $film = new Film();
        $film->setUniqueName("tt2294629", $site->_getSourceName());
        
        $page = "<html><body><h2>Film Detail</h2></body></html>";
        $verifyFilename = __DIR__ . DIRECTORY_SEPARATOR . "testfile" . DIRECTORY_SEPARATOR . "verify_cache_filmdetailpage.xml";
        $fp = fopen($verifyFilename, "w");
        fwrite($fp, $page);
        fclose($fp);
        
        $site->cacheFilmDetailPage($page, $film);
        $testFilename = Constants::cacheFilePath() . $site->_getSourceName() . "_" . TEST_SITE_USERNAME . "_film_" . $film->getUniqueName($site->_getSourceName()) . ".html";
        $this->assertFileExists($testFilename, 'Cache file exists');
        $this->assertFileEquals($verifyFilename, $testFilename, 'cache file vs verify file');
        
        unlink($verifyFilename);
        unlink($testFilename);
    }
}

?>
