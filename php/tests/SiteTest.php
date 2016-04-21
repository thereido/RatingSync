<?php
/**
 * Site PHPUnit
 */
namespace RatingSync;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "Site.php";

require_once "SiteChild.php";
require_once "HttpTest.php";
require_once "ImdbTest.php";
require_once "10DatabaseTest.php";
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
    public function setUp()
    {
        parent::setup();
        //$this->verbose = true;
    }

    /**
     * @covers            \RatingSync\Site::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testCannotBeConstructedFromNull()
    {$this->start(__CLASS__, __FUNCTION__);

        new SiteChild(null);
    }

    /**
     * @covers            \RatingSync\Site::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testCannotBeConstructedFromEmptyUsername()
    {$this->start(__CLASS__, __FUNCTION__);

        new SiteChild("");
    }

    /**
     * @covers \RatingSync\Site::__construct
     */
    public function testObjectCanBeConstructed()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new SiteChild(TEST_SITE_USERNAME);
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
        $verifyFilename = "testfile" . DIRECTORY_SEPARATOR . "verify_cache_filmdetailpage.xml";
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
    
    /**
     * @covers \RatingSync\Site::getFilmDetailFromWebsite
     * @depends testValidateAfterConstructorGood
     */
    public function testGetFilmDetailFromWebsiteEmptyFilmOverwriteTrue()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new SiteChild(TEST_SITE_USERNAME);

        $film = new Film();
        $film->setUniqueName("tt2294629", Constants::SOURCE_IMDB);
        $site->getFilmDetailFromWebsite($film, true, Constants::USE_CACHE_NEVER);
        
        $this->assertEquals("Frozen", $film->getTitle(), 'Title');
        $this->assertEquals(2013, $film->getYear(), 'Year');
        $this->assertNull($film->getContentType(), 'Content Type');
        $this->assertNull($film->getImage(), 'Image link (film)');
        $this->assertEquals(1, preg_match('@(http://ia.media-imdb.com/images/M/MV5BMTQ1MjQwMTE5OF5BMl5BanBnXkFtZTgwNjk3MTcyMDE)@', $film->getImage(Constants::SOURCE_IMDB), $matches), 'Image link (IMDb)');
        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), 'Director(s)');
        $this->assertEquals(array("Animation", "Adventure", "Comedy"), $film->getGenres(), 'Genres');
        $this->assertEquals("tt2294629", $film->getUniqueName(Constants::SOURCE_IMDB), 'Film ID');
    }
    
    /**
     * @covers \RatingSync\Site::getFilmDetailFromWebsite
     * @depends testGetFilmDetailFromWebsiteEmptyFilmOverwriteTrue
     */
    public function testGetFilmDetailFromWebsiteFullFilmOverwriteTrue()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new SiteChild(TEST_SITE_USERNAME);

        $film = new Film();

        // Setup original data
        $film->setTitle("Original_Title");
        $film->setYear(1900);
        $film->setContentType(Film::CONTENT_TV);
        $film->setImage("Original_Image");
        $film->setImage("Original_JinniImage", Constants::SOURCE_JINNI);
        $film->setImage("Original_IMDbImage", Constants::SOURCE_IMDB);
        $film->setUniqueName("Original_JinniUniqueName", Constants::SOURCE_JINNI);
        $film->setUniqueName("Original_IMDbUniqueName", Constants::SOURCE_IMDB);
        $film->addGenre("Original_Genre1");
        $film->addGenre("Original_Genre2");
        $film->addDirector("Original_Director1");
        $film->addDirector("Original_Director2");

        // Get detail overwriting
        $film->setUniqueName("tt2294629", Constants::SOURCE_IMDB);
        $site->getFilmDetailFromWebsite($film, true);

        // Test the new data
        $this->assertEquals("Frozen", $film->getTitle(), 'Title');
        $this->assertEquals(2013, $film->getYear(), 'Year');
        $this->assertEquals(Film::CONTENT_TV, $film->getContentType(), 'Content Type');  // SiteChild doesn't get it
        $this->assertEquals(1, preg_match('@(http://ia.media-imdb.com/images/M/MV5BMTQ1MjQwMTE5OF5BMl5BanBnXkFtZTgwNjk3MTcyMDE)@', $film->getImage(Constants::SOURCE_IMDB), $matches), 'Image link');
        $this->assertEquals("tt2294629", $film->getUniqueName($site->_getSourceName()), 'Unique Name');
        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), 'Director(s)');
        $this->assertEquals(array("Animation", "Adventure", "Comedy"), $film->getGenres(), 'Genres');

        // The film detail page does not have these fields.  Don't overwrite them.
        $this->assertEquals("Original_Image", $film->getImage(), 'Film image');

        // Jinni is unchanged
        $this->assertEquals("Original_JinniUniqueName", $film->getUniqueName(Constants::SOURCE_JINNI), 'Unique Name Jinni unchanged');
    }

    /**
     * @covers \RatingSync\Jinni::getFilmDetailFromWebsite
     * @depends testGetFilmDetailFromWebsiteEmptyFilmOverwriteTrue
     */
    public function testGetFilmDetailFromWebsiteEmptyFilmOverwriteFalse()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new SiteChild(TEST_SITE_USERNAME);

        $film = new Film();
        $film->setUniqueName("tt2294629", Constants::SOURCE_IMDB);
        $site->getFilmDetailFromWebsite($film, false);

        // Same results as testGetFilmDetailFromWebsite or testGetFilmDetailFromWebsiteOverwriteTrueOverEmpty
        $this->assertEquals("Frozen", $film->getTitle(), 'Title');
        $this->assertEquals(2013, $film->getYear(), 'Year');
        $this->assertNull($film->getContentType(), 'Content Type'); // IMDb gets it, but SiteChild doesn't
        $this->assertNull($film->getImage(), 'Film image');
        $this->assertEquals(1, preg_match('@(http://ia.media-imdb.com/images/M/MV5BMTQ1MjQwMTE5OF5BMl5BanBnXkFtZTgwNjk3MTcyMDE)@', $film->getImage(Constants::SOURCE_IMDB), $matches), 'Source image');
        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), 'Director(s)');
        $this->assertEquals(array("Animation", "Adventure", "Comedy"), $film->getGenres(), 'Genres');
        $this->assertEquals("tt2294629", $film->getUniqueName(Constants::SOURCE_IMDB), 'Unique Name');
    }

    /**
     * @covers \RatingSync\Jinni::getFilmDetailFromWebsite
     * @depends testGetFilmDetailFromWebsiteEmptyFilmOverwriteTrue
     */
    public function testGetFilmDetailFromWebsiteFullFilmOverwriteFalse()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new SiteChild(TEST_SITE_USERNAME);

        $film = new Film();

        // Setup original data
        $film->setTitle("Original_Title");
        $film->setYear(1900);
        $film->setContentType("FeatureFilm");
        $film->setImage("Original_Image");
        $film->addGenre("Original_Genre1");
        $film->addGenre("Original_Genre2");
        $film->addDirector("Original_Director1");
        $film->addDirector("Original_Director2");
        $film->setImage("Original_Image", Constants::SOURCE_JINNI);
        $film->setUniqueName("Original_JinniUniqueName", Constants::SOURCE_JINNI);
        $film->setImage("Original_Image", Constants::SOURCE_IMDB);

        // Get detail not overwriting
        $film->setUniqueName("tt2294629", Constants::SOURCE_IMDB);
        $site->getFilmDetailFromWebsite($film, false);

        // Same original data
        $this->assertEquals("Original_Title", $film->getTitle(), 'Title');
        $this->assertEquals(1900, $film->getYear(), 'Year');
        $this->assertEquals("FeatureFilm", $film->getContentType(), 'Content Type');
        $this->assertEquals("Original_Image", $film->getImage(), 'Image link');
        $this->assertEquals(array("Original_Director1", "Original_Director2"), $film->getDirectors(), 'Director(s)');
        $this->assertEquals(array("Original_Genre1", "Original_Genre2"), $film->getGenres(), 'Genres');
        $this->assertEquals("Original_JinniUniqueName", $film->getUniqueName(Constants::SOURCE_JINNI), 'Unique Name');
        $this->assertEquals("tt2294629", $film->getUniqueName(Constants::SOURCE_IMDB), 'Unique Name');
    }
    
    /**
     * @covers \RatingSync\Site::parseDetailPageForTitle
     * @covers \RatingSync\Site::parseDetailPageForFilmYear
     * @covers \RatingSync\Site::parseDetailPageForImage
     * @covers \RatingSync\Site::parseDetailPageForContentType
     * @covers \RatingSync\Site::parseDetailPageForUniqueName
     * @covers \RatingSync\Site::parseDetailPageForGenres
     * @covers \RatingSync\Site::parseDetailPageForDirectors
     */
    public function testParseDetailPageEmptyFilmOverwriteTrue()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new SiteChild(TEST_SITE_USERNAME);
        $film = new Film();
        // Get HTML of the film's detail page
        $findFilm = new Film();
        $findFilm->setUniqueName("tt2294629", $site->_getSourceName());
        $site->getFilmDetailFromWebsite($findFilm, true, 60);
        $page = $site->getFilmDetailPageFromCache($findFilm, 60);
        
        $success = $site->_parseDetailPageForTitle($page, $film, true);
        $this->assertTrue($success, 'Parsing film object for Title');
        $this->assertEquals("Frozen", $film->getTitle(), 'Check matching Title (empty film overwrite=true)');

        $success = $site->_parseDetailPageForFilmYear($page, $film, true);
        $this->assertTrue($success, 'Parsing film object for Year');
        $this->assertEquals(2013, $film->getYear(), 'Check matching Year (empty film overwrite=true)');
        
        $success = $site->_parseDetailPageForImage($page, $film, true);
        $this->assertTrue($success, 'Parsing film object for Image');
        $this->assertNull($film->getImage(), 'Film image should be null (empty film overwrite=true)');
        $this->assertEquals(1, preg_match('@(http://ia.media-imdb.com/images/M/MV5BMTQ1MjQwMTE5OF5BMl5BanBnXkFtZTgwNjk3MTcyMDE)@', $film->getImage($site->_getSourceName()), $matches), 'Check matching Image (empty film overwrite=true)');
        
        $success = $site->_parseDetailPageForContentType($page, $film, true);
        $this->assertFalse($success, 'Parsing film object for Content Type');  // IMDb gets it, but SiteChild does not
        
        $success = $site->_parseDetailPageForUniqueName($page, $film, true);
        $this->assertTrue($success, 'Parsing film object for Film Id');
        $this->assertEquals("tt2294629", $film->getUniqueName($site->_getSourceName()), 'Check matching Unique Name (empty film overwrite=true)');
        
        $success = $site->_parseDetailPageForGenres($page, $film, true);
        $this->assertTrue($success, 'Parsing film object for Genres');
        $this->assertEquals(array("Animation", "Adventure", "Comedy"), $film->getGenres(), 'Check matching Gneres (empty film overwrite=true)');
        
        $success = $site->_parseDetailPageForDirectors($page, $film, true);
        $this->assertTrue($success, 'Parsing film object for Directors');
        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), 'Check matching Directors (empty film overwrite=true)');
    }
    
    /**
     * @covers \RatingSync\Site::parseDetailPageForTitle
     * @covers \RatingSync\Site::parseDetailPageForFilmYear
     * @covers \RatingSync\Site::parseDetailPageForImage
     * @covers \RatingSync\Site::parseDetailPageForContentType
     * @covers \RatingSync\Site::parseDetailPageForUniqueName
     * @covers \RatingSync\Site::parseDetailPageForGenres
     * @covers \RatingSync\Site::parseDetailPageForDirectors
     */
    public function testParseDetailPageEmptyFilmOverwriteFalse()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new SiteChild(TEST_SITE_USERNAME);
        $film = new Film();

        // Get HTML of the film's detail page
        $findFilm = new Film();
        $findFilm->setUniqueName("tt2294629", $site->_getSourceName());
        $site->getFilmDetailFromWebsite($findFilm, true, 60);
        $page = $site->getFilmDetailPageFromCache($findFilm, 60);
        
        $success = $site->_parseDetailPageForTitle($page, $film, false);
        $this->assertTrue($success, 'Parsing film object for Title');
        $this->assertEquals("Frozen", $film->getTitle(), 'Check matching Title (empty film overwrite=false)');

        $success = $site->_parseDetailPageForFilmYear($page, $film, false);
        $this->assertTrue($success, 'Parsing film object for Year');
        $this->assertEquals(2013, $film->getYear(), 'Check matching Year (empty film overwrite=false)');

        $success = $site->_parseDetailPageForImage($page, $film, false);
        $this->assertTrue($success, 'Parsing film object for Image');
        $this->assertNull($film->getImage(), 'Image link (film)');
        $this->assertEquals(1, preg_match('@(http://ia.media-imdb.com/images/M/MV5BMTQ1MjQwMTE5OF5BMl5BanBnXkFtZTgwNjk3MTcyMDE)@', $film->getImage($site->_getSourceName()), $matches), 'Image link (source)');
        
        $success = $site->_parseDetailPageForContentType($page, $film, false);
        $this->assertFalse($success, 'Parsing film object for Content Type'); // ContentType not available in the detail page
        $this->assertNull($film->getContentType(), 'Check matching Content Type (empty film overwrite=false)');
        
        $success = $site->_parseDetailPageForUniqueName($page, $film, false);
        $this->assertTrue($success, 'Parsing film object for Film Id');
        $this->assertEquals("tt2294629", $film->getUniqueName($site->_getSourceName()), 'Check matching Unique Name (empty film overwrite=false)');
        
        $success = $site->_parseDetailPageForGenres($page, $film, false);
        $this->assertTrue($success, 'Parsing film object for Genres');
        $this->assertEquals(array("Animation", "Adventure", "Comedy"), $film->getGenres(), 'Check matching Gneres (empty film overwrite=false)');
        
        $success = $site->_parseDetailPageForDirectors($page, $film, false);
        $this->assertTrue($success, 'Parsing film object for Directors');
        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), 'Check matching Directors (empty film overwrite=false)');
    }
    
    /**
     * @covers \RatingSync\Site::parseDetailPageForTitle
     * @covers \RatingSync\Site::parseDetailPageForFilmYear
     * @covers \RatingSync\Site::parseDetailPageForImage
     * @covers \RatingSync\Site::parseDetailPageForContentType
     * @covers \RatingSync\Site::parseDetailPageForUniqueName
     * @covers \RatingSync\Site::parseDetailPageForGenres
     * @covers \RatingSync\Site::parseDetailPageForDirectors
     */
    public function testParseDetailPageFullFilmOverwriteTrue()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new SiteChild(TEST_SITE_USERNAME);
        $film = new Film();

        // Setup original data
        $film->setTitle("Original_Title");
        $film->setYear(1900);
        $film->setContentType(Film::CONTENT_TV);
        $film->setImage("Original_Image");
        $film->setImage("Original_IMDbImage", $site->_getSourceName());
        $film->setImage("Original_JinniImage", Constants::SOURCE_JINNI);
        $film->setUniqueName("Original_IMDbUniqueName", $site->_getSourceName());
        $film->setUniqueName("Original_JinniUniqueName", Constants::SOURCE_JINNI);
        $film->addGenre("Original_Genre1");
        $film->addGenre("Original_Genre2");
        $film->addDirector("Original_Director1");
        $film->addDirector("Original_Director2");

        // Get HTML of the film's detail page
        $findFilm = new Film();
        $findFilm->setUniqueName("tt2294629", $site->_getSourceName());
        $site->getFilmDetailFromWebsite($findFilm, true, 60);
        $page = $site->getFilmDetailPageFromCache($findFilm, 60);
        
        $success = $site->_parseDetailPageForTitle($page, $film, true);
        $this->assertTrue($success, 'Parsing film object for Title');
        $this->assertEquals("Frozen", $film->getTitle(), 'Check matching Title (full film overwrite=true)');

        $success = $site->_parseDetailPageForFilmYear($page, $film, true);
        $this->assertTrue($success, 'Parsing film object for Year');
        $this->assertEquals(2013, $film->getYear(), 'Check matching Year (full film overwrite=true)');

        $success = $site->_parseDetailPageForContentType($page, $film, true);
        $this->assertFalse($success, 'Parsing film object for Content Type');
        $this->assertEquals(Film::CONTENT_TV, $film->getContentType(), 'Check matching Content Type (full film overwrite=true)');

        $success = $site->_parseDetailPageForImage($page, $film, true);
        $this->assertTrue($success, 'Parsing film object for Image');
        $this->assertEquals("Original_Image", $film->getImage(), 'Check matching Image (full film overwrite=true)');
        $this->assertEquals("Original_JinniImage", $film->getImage(Constants::SOURCE_JINNI), 'Check matching Image (full film overwrite=true)');
        $this->assertEquals(1, preg_match('@(http://ia.media-imdb.com/images/M/MV5BMTQ1MjQwMTE5OF5BMl5BanBnXkFtZTgwNjk3MTcyMDE)@', $film->getImage($site->_getSourceName()), $matches), 'Check matching Image (full film overwrite=true)');
        
        $success = $site->_parseDetailPageForUniqueName($page, $film, true);
        $this->assertTrue($success, 'Parsing film object for Film Id');
        $this->assertEquals("tt2294629", $film->getUniqueName($site->_getSourceName()), 'Check matching Unique Name (full film overwrite=true)');
        $this->assertEquals("Original_JinniUniqueName", $film->getUniqueName(Constants::SOURCE_JINNI), 'Check matching Unique Name (full film overwrite=true)');
        
        $success = $site->_parseDetailPageForGenres($page, $film, true);
        $this->assertTrue($success, 'Parsing film object for Genres');
        $this->assertEquals(array("Animation", "Adventure", "Comedy"), $film->getGenres(), 'Check matching Gneres (full film overwrite=true)');
        
        $success = $site->_parseDetailPageForDirectors($page, $film, true);
        $this->assertTrue($success, 'Parsing film object for Directors');
        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), 'Check matching Directors (full film overwrite=true)');
    }
    
    /**
     * @covers \RatingSync\Site::parseDetailPageForTitle
     * @covers \RatingSync\Site::parseDetailPageForFilmYear
     * @covers \RatingSync\Site::parseDetailPageForImage
     * @covers \RatingSync\Site::parseDetailPageForContentType
     * @covers \RatingSync\Site::parseDetailPageForUniqueName
     * @covers \RatingSync\Site::parseDetailPageForGenres
     * @covers \RatingSync\Site::parseDetailPageForDirectors
     */
    public function testParseDetailPageFullFilmOverwriteFalse()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new SiteChild(TEST_SITE_USERNAME);
        $film = new Film();

        // Setup original data
        $film->setTitle("Original_Title");
        $film->setYear(1900);
        $film->setContentType(Film::CONTENT_TV);
        $film->addGenre("Original_Genre1");
        $film->addGenre("Original_Genre2");
        $film->addDirector("Original_Director1");
        $film->addDirector("Original_Director2");
        $film->setImage("Original_Image");
        $film->setImage("Original_Image_Imdb", $site->_getSourceName());
        $film->setImage("Original_Image_Jinni", Constants::SOURCE_JINNI);
        $film->setUniqueName("Original_UniqueName_Imdb", $site->_getSourceName());
        $film->setUniqueName("Original_UniqueName_Jinni", Constants::SOURCE_JINNI);

        // Get HTML of the film's detail page
        $findFilm = new Film();
        $findFilm->setUniqueName("tt2294629", $site->_getSourceName());
        $site->getFilmDetailFromWebsite($findFilm, true, 60);
        $page = $site->getFilmDetailPageFromCache($findFilm, 60);
        
        $success = $site->_parseDetailPageForTitle($page, $film, false);
        $this->assertFalse($success, 'Parsing film object for Title');
        $this->assertEquals("Original_Title", $film->getTitle(), 'Check matching Title (full film overwrite=false)');

        $success = $site->_parseDetailPageForFilmYear($page, $film, false);
        $this->assertFalse($success, 'Parsing film object for Year');
        $this->assertEquals(1900, $film->getYear(), 'Check matching Year (full film overwrite=false)');

        $success = $site->_parseDetailPageForContentType($page, $film, false);
        $this->assertFalse($success, 'Parsing film object for Content Type');
        $this->assertEquals(Film::CONTENT_TV, $film->getContentType(), 'Check matching Content Type (full film overwrite=false)');

        $success = $site->_parseDetailPageForImage($page, $film, false);
        $this->assertFalse($success, 'Parsing film object for Image');
        $this->assertEquals("Original_Image", $film->getImage(), 'Check matching Image (full film overwrite=false)');
        $this->assertEquals("Original_Image_Imdb", $film->getImage($site->_getSourceName()), 'Check matching Image (full film overwrite=false)');
        $this->assertEquals("Original_Image_Jinni", $film->getImage(Constants::SOURCE_JINNI), 'Check matching Image (full film overwrite=false)');
        
        $success = $site->_parseDetailPageForUniqueName($page, $film, false);
        $this->assertFalse($success, 'Parsing film object for Unique Name');
        $this->assertEquals("Original_UniqueName_Imdb", $film->getUniqueName($site->_getSourceName()), 'Check matching Unique Name (full film overwrite=false)');
        $this->assertEquals("Original_UniqueName_Jinni", $film->getUniqueName(Constants::SOURCE_JINNI), 'Check matching Unique Name (full film overwrite=false)');
        
        $success = $site->_parseDetailPageForGenres($page, $film, false);
        $this->assertFalse($success, 'Parsing film object for Genres');
        $this->assertEquals(array("Original_Genre1", "Original_Genre2"), $film->getGenres(), 'Check matching Gneres (full film overwrite=false)');
        
        $success = $site->_parseDetailPageForDirectors($page, $film, false);
        $this->assertFalse($success, 'Parsing film object for Directors');
        $this->assertEquals(array("Original_Director1", "Original_Director2"), $film->getDirectors(), 'Check matching Directors (full film overwrite=false)');
    }
}

?>
