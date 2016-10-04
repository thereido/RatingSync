<?php
/**
 * Site PHPUnit
 */
namespace RatingSync;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "SiteRatings.php";

require_once "SiteRatingsChild.php";
require_once "SiteTest.php";
require_once "10DatabaseTest.php";
require_once "RatingSyncTestCase.php";

/**
 Suggested tests for a child class
   - All suggested tests from SiteTest
   - testGetRatingsUsernameWithNoMatch
   - testCacheRatingsPage
   - testGetRatings
   - testGetRatingsUsingCacheAlways
   - testGetRatingsUsingCacheNever
   - testGetRatingsUsingCacheWithRecentFiles
   - testGetRatingsUsingCacheWithOldFiles
   - testGetRatingsCount
   - testGetRatingsLimitPages
   - testGetRatingsBeginPage
   - testGetRatingsDetailsNoCache
   - testGetRatingsDetails
   - testExportRatingsXmlNoDetail
   - testExportRatingsXmlDetail
   - testGetRatingPageUrlWithArgsNull
   - testGetRatingPageUrlWithArgsEmpty
   - testGetRatingPageUrlWithPageIndexNull
   - testGetRatingPageUrlWithPageIndexEmpty
   - testGetRatingPageUrlWithPageIndexNotInt
   - testGetRatingPageUrl
   - testGetNextRatingPageNumberWithNull
   - testGetNextRatingPageNumberWithEmpty
   - testGetNextRatingPageNumberFirstPage
   - testGetNextRatingPageNumberLastPage
   - testFromExportFileToFilmObjectAndBackToXml
 */

class SiteRatingsTest extends RatingSyncTestCase
{
    public function setUp()
    {
        parent::setup();
        //$this->verbose = true;
    }

    /**
     * @covers            \RatingSync\SiteRatings::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testCannotBeConstructedFromNull()
    {$this->start(__CLASS__, __FUNCTION__);

        new SiteRatingsChild(null);
    }

    /**
     * @covers            \RatingSync\SiteRatings::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testCannotBeConstructedFromEmptyUsername()
    {$this->start(__CLASS__, __FUNCTION__);

        new SiteRatingsChild("");
    }

    /**
     * @covers \RatingSync\SiteRatings::__construct
     */
    public function testObjectCanBeConstructed()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new SiteRatingsChild(TEST_SITE_USERNAME);
    }

    /**
     * @covers \RatingSync\SiteRatings::validateAfterConstructor
     * @depends testObjectCanBeConstructed
     */
    public function testValidateAfterConstructorNoHttp()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new SiteRatingsChild(TEST_SITE_USERNAME);
        $site->_setSourceName(Constants::SOURCE_IMDB);
        $site->_setHttp(null);
        $this->assertFalse($site->_validateAfterConstructor());
    }

    /**
     * @covers \RatingSync\SiteRatings::validateAfterConstructor
     * @depends testObjectCanBeConstructed
     */
    public function testValidateAfterConstructorNoSourceName()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new SiteRatingsChild(TEST_SITE_USERNAME);
        $site->_setSourceName(null);
        $site->_setHttp(new Http(Constants::SOURCE_IMDB, TEST_SITE_USERNAME));
        $this->assertFalse($site->_validateAfterConstructor());
    }

    /**
     * @covers \RatingSync\SiteRatings::validateAfterConstructor
     * @depends testObjectCanBeConstructed
     */
    public function testValidateAfterConstructorGood()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new SiteRatingsChild(TEST_SITE_USERNAME);
        $this->assertTrue($site->_validateAfterConstructor());
    }

    /**
     * @covers \RatingSync\SiteRatings::cacheRatingsPage
     * @depends testObjectCanBeConstructed
     */
    public function testCacheRatingsPage()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new SiteRatingsChild(TEST_SITE_USERNAME);

        $page = "<html><body><h2>Rating page 2</h2></body></html>";
        $verifyFilename = "testfile" . DIRECTORY_SEPARATOR . "verify_cache_ratingspage.xml";
        $fp = fopen($verifyFilename, "w");
        fwrite($fp, $page);
        fclose($fp);

        $site->cacheRatingsPage($page, 2);
        $testFilename = Constants::cacheFilePath() . $site->_getSourceName() . "_" . TEST_SITE_USERNAME . "_ratings_2.html";
        $this->assertFileExists($testFilename, 'Cache file exists');
        $this->assertFileEquals($verifyFilename, $testFilename, 'cache file vs verify file');
        
        unlink($verifyFilename);
        unlink($testFilename);
    }

    /**
     * @covers \RatingSync\SiteRatings::cacheFilmDetailPage
     * @depends testObjectCanBeConstructed
     */
    public function testCacheFilmDetailPage()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new SiteRatingsChild(TEST_SITE_USERNAME);
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
     * @covers \RatingSync\SiteRatings::getRatings
     * @depends testValidateAfterConstructorGood
     */
    public function testGetRatingsWithoutExceptions()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new SiteRatingsChild(TEST_SITE_USERNAME);

        $films = $site->getRatings();
    }

    /**
     * @covers \RatingSync\SiteRatings::getRatings
     * @covers \RatingSync\SiteRatings::cacheRatingsPage
     * @depends testCacheRatingsPage
     * @depends testGetRatingsWithoutExceptions
     */
    public function testCacheAllRatingsPagesWithRecentFiles()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new SiteRatingsChild(TEST_SITE_USERNAME);

        $pageNums = array('1', '2');
        foreach ($pageNums as $pageNum) {
            $page = '<html><body><h2>Rating page ' . $pageNum . '</h2></body></html>';
            $testFilename = Constants::cacheFilePath() . $site->_getSourceName() . "_" . TEST_SITE_USERNAME . "_ratings_" . $pageNum . ".html";
            $fp = fopen($testFilename, "w");
            fwrite($fp, $page);
            fclose($fp);
        }
        $originalCacheTime = time();
        sleep(1);

        // limitPages=null, beginPage=1, detail=false, refreshCache=0 (refresh now)
        $films = $site->getRatings(null, 1, false, Constants::USE_CACHE_NEVER);
        
        foreach ($pageNums as $pageNum) {
            $testFilename = Constants::cacheFilePath() . $site->_getSourceName() . "_" . TEST_SITE_USERNAME . "_ratings_" . $pageNum . ".html";
            $this->assertFileExists($testFilename, 'Cache file ' . $pageNum . ' exists');
            $this->assertGreaterThan($originalCacheTime, filemtime($testFilename), 'Modified time');
            unlink($testFilename);
        }
    }

    /**
     * @covers \RatingSync\SiteRatings::getRatings
     * @covers \RatingSync\SiteRatings::cacheRatingsPage
     * @depends testGetRatingsWithoutExceptions
     * @depends testCacheRatingsPage
     */
    public function testCacheAllRatingsPagesWithNoFiles()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new SiteRatingsChild(TEST_SITE_USERNAME);
        
        $pageNums = array('1', '2');
        foreach ($pageNums as $pageNum) {
            $testFilename = Constants::cacheFilePath() . $site->_getSourceName() . "_" . TEST_SITE_USERNAME . "_ratings_" . $pageNum . ".html";
            if (file_exists($testFilename)) {
                unlink($testFilename);
            }
        }

        // limitPages=null, beginPage=1, detail=false, refreshCache=0 (refresh now)
        $films = $site->getRatings(null, 1, false, Constants::USE_CACHE_NEVER);
        
        foreach ($pageNums as $pageNum) {
            $testFilename = Constants::cacheFilePath() . $site->_getSourceName() . "_" . TEST_SITE_USERNAME . "_ratings_" . $pageNum . ".html";
            $this->assertFileExists($testFilename, 'Cache file ' . $pageNum . ' exists');
        }
    }
    
    /**
     * @covers \RatingSync\SiteRatings::getRatings
     * @depends testGetRatingsWithoutExceptions
     * @depends testCacheAllRatingsPagesWithNoFiles
     */
    public function testGetRatingsCount()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new SiteRatingsChild(TEST_SITE_USERNAME);

        // Each page of ratings from SiteChild returns 2 films.  SiteChild get two
        // pages because of the cached pages made by testCacheAllRatingsPagesWithNoFiles
        $films = $site->getRatings();
        $this->assertCount(4, $films);
    }
    
    /**
     * @covers \RatingSync\SiteRatings::getRatings
     * @depends testValidateAfterConstructorGood
     */
    public function testGetRatingsDetailsWithoutExceptions()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new SiteRatingsChild(TEST_SITE_USERNAME);

        $films = $site->getRatings(null, 1, true);
    }
    
    /**
     * @covers \RatingSync\SiteRatings::getRatings
     * @depends testValidateAfterConstructorGood
     */
    public function testGetRatingsLimitPagesBeginPageWithoutExceptions()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new SiteRatingsChild(TEST_SITE_USERNAME);

        $films = $site->getRatings(1, 2, false);
    }
    
    /**
     * @covers \RatingSync\SiteRatings::exportRatings
     * @depends testValidateAfterConstructorGood
     */
    public function testExportRatingsXmlNoDetail()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new SiteRatingsChild(TEST_SITE_USERNAME);
        
        $testFilename = "ratings_test.xml";
        $success = $site->exportRatings(Constants::EXPORT_FORMAT_XML, $testFilename, false);
        $this->assertTrue($success);

        $fullTestFilename = Constants::outputFilePath() . $testFilename;
        $fullVerifyFilename = "testfile/verify_ratings_nodetail_site.xml";
        $this->assertTrue(is_readable($fullTestFilename), 'Need to read downloaded file ' . $fullTestFilename);
        $this->assertTrue(is_readable($fullVerifyFilename), 'Need to read verify file ' . $fullVerifyFilename);

        $fp_test = fopen($fullTestFilename, "r");
        $fp_verify = fopen($fullVerifyFilename, "r");
        $testFileSize = filesize($fullTestFilename);
        $verifyFileSize = filesize($fullVerifyFilename);
        $this->assertEquals($testFileSize, $verifyFileSize, 'File sizes - test vs verify');
        $test = fread($fp_test, filesize($fullTestFilename));
        $verify = fread($fp_verify, filesize($fullVerifyFilename));

        // Each page of ratings from SiteChild returns 2 films.  SiteChild get two
        // pages because of the cached pages made by testCacheAllRatingsPagesWithNoFiles.
        // The exported file have 2 films twice.
        $this->assertEquals($test, $verify, 'Match exported file vs verify file');
        fclose($fp_test);
        fclose($fp_verify);
    }
    
    /**
     * @covers \RatingSync\SiteRatings::exportRatings
     * @depends testValidateAfterConstructorGood
     */
    public function testExportRatingsXmlDetail()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new SiteRatingsChild(TEST_SITE_USERNAME);
        
        $testFilename = "ratings_test.xml";
        $success = $site->exportRatings("XML", $testFilename, true, 60);
        $this->assertTrue($success);

        $fullTestFilename = Constants::outputFilePath() . $testFilename;
        $fullVerifyFilename = "testfile/verify_ratings_detail_site.xml";
        $this->assertTrue(is_readable($fullTestFilename), 'Need to read downloaded file ' . $fullTestFilename);
        $this->assertTrue(is_readable($fullVerifyFilename), 'Need to read verify file ' . $fullVerifyFilename);

        $fp_test = fopen($fullTestFilename, "r");
        $fp_verify = fopen($fullVerifyFilename, "r");
        $testFileSize = filesize($fullTestFilename);
        $verifyFileSize = filesize($fullVerifyFilename);
        $this->assertEquals($testFileSize, $verifyFileSize, 'File sizes - test vs verify');
        $test = fread($fp_test, 22);
        $verify = fread($fp_verify, 22);

        // Each page of ratings from SiteChild returns 2 films.  SiteChild get two
        // pages because of the cached pages made by testCacheAllRatingsPagesWithNoFiles.
        // The exported file have 2 films twice.
        $this->assertEquals($test, $verify, 'Match exported file vs verify file');
        fclose($fp_test);
        fclose($fp_verify);
    }
    
    /**
     * @covers \RatingSync\SiteRatings::getFilmDetailFromWebsite
     * @depends testValidateAfterConstructorGood
     */
    public function testGetFilmDetailFromWebsiteEmptyFilmOverwriteTrue()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new SiteRatingsChild(TEST_SITE_USERNAME);

        $film = new Film();
        $film->setUniqueName("tt2294629", Constants::SOURCE_IMDB);
        $site->getFilmDetailFromWebsite($film, true, Constants::USE_CACHE_NEVER);
        
        $this->assertEquals("Frozen", $film->getTitle(), 'Title');
        $this->assertEquals(2013, $film->getYear(), 'Year');
        $this->assertNull($film->getContentType(), 'Content Type');
        $this->assertNull($film->getImage(), 'Image link (film)');
        $this->assertEquals(1, preg_match('@(https://images-na.ssl-images-amazon.com/images/M/MV5BMTQ1MjQwMTE5OF5BMl5BanBnXkFtZTgwNjk3MTcyMDE)@', $film->getImage(Constants::SOURCE_IMDB), $matches), 'Image link (IMDb)');
        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), 'Director(s)');
        $this->assertEquals(array("Animation", "Adventure", "Comedy"), $film->getGenres(), 'Genres');
        $rating = $film->getRating(Constants::SOURCE_IMDB);
        $this->assertEquals("tt2294629", $film->getUniqueName(Constants::SOURCE_IMDB), 'Film ID');
        $this->assertEquals(7.4, $film->getCriticScore(Constants::SOURCE_IMDB), 'Critic score');
        $this->assertEquals(FROZEN_USER_SCORE, $film->getUserScore(Constants::SOURCE_IMDB), 'User score');
        $this->assertNull($rating->getYourScore(), 'Your Score');
        $this->assertNull($rating->getYourRatingDate(), 'Rating date not available from film detail page');
        $this->assertNull($rating->getSuggestedScore(), 'Suggested score not available is you are rated the film');
    }
    
    /**
     * @covers \RatingSync\SiteRatings::getFilmDetailFromWebsite
     * @depends testGetFilmDetailFromWebsiteEmptyFilmOverwriteTrue
     */
    public function testGetFilmDetailFromWebsiteFullFilmOverwriteTrue()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new SiteRatingsChild(TEST_SITE_USERNAME);

        $film = new Film();

        // Setup original data
        $film->setTitle("Original_Title");
        $film->setYear(1900);
        $film->setContentType(Film::CONTENT_TV_SERIES);
        $film->setImage("Original_Image");
        $film->setImage("Original_JinniImage", Constants::SOURCE_JINNI);
        $film->setImage("Original_IMDbImage", Constants::SOURCE_IMDB);
        $film->setUniqueName("Original_JinniUniqueName", Constants::SOURCE_JINNI);
        $film->setUniqueName("Original_IMDbUniqueName", Constants::SOURCE_IMDB);
        $film->setCriticScore(3, Constants::SOURCE_JINNI);
        $film->setUserScore(4, Constants::SOURCE_JINNI);
        $film->setCriticScore(4, Constants::SOURCE_IMDB);
        $film->setUserScore(5, Constants::SOURCE_IMDB);
        $film->addGenre("Original_Genre1");
        $film->addGenre("Original_Genre2");
        $film->addDirector("Original_Director1");
        $film->addDirector("Original_Director2");
        $ratingJinniOrig = new Rating(Constants::SOURCE_JINNI);
        $ratingJinniOrig->setYourScore(1);
        $ratingJinniOrig->setYourRatingDate(new \DateTime('2000-01-01'));
        $ratingJinniOrig->setSuggestedScore(2);
        $film->setRating($ratingJinniOrig, Constants::SOURCE_JINNI);
        $ratingImdbOrig = new Rating(Constants::SOURCE_IMDB);
        $ratingImdbOrig->setYourScore(2);
        $ratingImdbOrig->setYourRatingDate(new \DateTime('2000-01-02'));
        $ratingImdbOrig->setSuggestedScore(3);
        $film->setRating($ratingImdbOrig, Constants::SOURCE_IMDB);

        // Get detail overwriting
        $film->setUniqueName("tt2294629", Constants::SOURCE_IMDB);
        $site->getFilmDetailFromWebsite($film, true);

        // Test the new data
        $this->assertEquals("Frozen", $film->getTitle(), 'Title');
        $this->assertEquals(2013, $film->getYear(), 'Year');
        $this->assertEquals(Film::CONTENT_TV_SERIES, $film->getContentType(), 'Content Type');  // SiteChild doesn't get it
        $this->assertEquals(1, preg_match('@(https://images-na.ssl-images-amazon.com/images/M/MV5BMTQ1MjQwMTE5OF5BMl5BanBnXkFtZTgwNjk3MTcyMDE)@', $film->getImage(Constants::SOURCE_IMDB), $matches), 'Image link');
        $this->assertEquals("tt2294629", $film->getUniqueName($site->_getSourceName()), 'Unique Name');
        $this->assertEquals(7.4, $film->getCriticScore($site->_getSourceName()), 'Critic score');
        $this->assertEquals(FROZEN_USER_SCORE, $film->getUserScore($site->_getSourceName()), 'User score');
        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), 'Director(s)');
        $this->assertEquals(array("Animation", "Adventure", "Comedy"), $film->getGenres(), 'Genres');
        $rating = $film->getRating($site->_getSourceName());

        // The film detail page does not have these fields.  Don't overwrite them.
        $this->assertEquals("Original_Image", $film->getImage(), 'Film image');
        $this->assertEquals(2, $rating->getYourScore(), 'Your Score');
        $this->assertEquals("1/2/2000", $rating->getYourRatingDate()->format("n/j/Y"), 'Rating date');
        $this->assertEquals(3, $rating->getSuggestedScore(), 'Suggested score');

        // Jinni Rating is unchanged
        $rating = $film->getRating(Constants::SOURCE_JINNI);
        $this->assertEquals("Original_JinniUniqueName", $film->getUniqueName(Constants::SOURCE_JINNI), 'Unique Name Jinni unchanged');
        $this->assertEquals(3, $film->getCriticScore(Constants::SOURCE_JINNI), 'Critic score Jinni unchanged');
        $this->assertEquals(4, $film->getUserScore(Constants::SOURCE_JINNI), 'User score Jinni unchanged');
        $this->assertEquals(1, $rating->getYourScore(), 'Your Score Jinni unchanged');
        $this->assertEquals("1/1/2000", $rating->getYourRatingDate()->format("n/j/Y"), 'Rating date Jinni unchanged');
        $this->assertEquals(2, $rating->getSuggestedScore(), 'Suggested score Jinni unchanged');
    }

    /**
     * @covers \RatingSync\Jinni::getFilmDetailFromWebsite
     * @depends testGetFilmDetailFromWebsiteEmptyFilmOverwriteTrue
     */
    public function testGetFilmDetailFromWebsiteEmptyFilmOverwriteFalse()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new SiteRatingsChild(TEST_SITE_USERNAME);

        $film = new Film();
        $film->setUniqueName("tt2294629", Constants::SOURCE_IMDB);
        $site->getFilmDetailFromWebsite($film, false);

        // Same results as testGetFilmDetailFromWebsite or testGetFilmDetailFromWebsiteOverwriteTrueOverEmpty
        $this->assertEquals("Frozen", $film->getTitle(), 'Title');
        $this->assertEquals(2013, $film->getYear(), 'Year');
        $this->assertNull($film->getContentType(), 'Content Type'); // IMDb gets it, but SiteChild doesn't
        $this->assertNull($film->getImage(), 'Film image');
        $this->assertEquals(1, preg_match('@(https://images-na.ssl-images-amazon.com/images/M/MV5BMTQ1MjQwMTE5OF5BMl5BanBnXkFtZTgwNjk3MTcyMDE)@', $film->getImage(Constants::SOURCE_IMDB), $matches), 'Source image');
        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), 'Director(s)');
        $this->assertEquals(array("Animation", "Adventure", "Comedy"), $film->getGenres(), 'Genres');
        $rating = $film->getRating(Constants::SOURCE_IMDB);
        $this->assertEquals("tt2294629", $film->getUniqueName(Constants::SOURCE_IMDB), 'Unique Name');
        $this->assertEquals(7.4, $film->getCriticScore(Constants::SOURCE_IMDB), 'Critic score not available from Jinni');
        $this->assertEquals(FROZEN_USER_SCORE, $film->getUserScore(Constants::SOURCE_IMDB), 'User score not available from Jinni');
        $this->assertNull($rating->getYourScore(), 'Your Score');
        $this->assertNull($rating->getYourRatingDate(), 'Rating date not available from film detail page');
        $this->assertNull($rating->getSuggestedScore(), 'Suggested score not available is you are rated the film');
    }

    /**
     * @covers \RatingSync\Jinni::getFilmDetailFromWebsite
     * @depends testGetFilmDetailFromWebsiteEmptyFilmOverwriteTrue
     */
    public function testGetFilmDetailFromWebsiteFullFilmOverwriteFalse()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new SiteRatingsChild(TEST_SITE_USERNAME);

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
        $film->setCriticScore(3, Constants::SOURCE_JINNI);
        $film->setUserScore(4, Constants::SOURCE_JINNI);
        $ratingJinniOrig = new Rating(Constants::SOURCE_JINNI);
        $ratingJinniOrig->setYourScore(1);
        $ratingJinniOrig->setYourRatingDate(new \DateTime('2000-01-01'));
        $ratingJinniOrig->setSuggestedScore(2);
        $film->setRating($ratingJinniOrig, Constants::SOURCE_JINNI);
        $film->setImage("Original_Image", Constants::SOURCE_IMDB);
        $film->setCriticScore(4, Constants::SOURCE_IMDB);
        $film->setUserScore(5, Constants::SOURCE_IMDB);
        $ratingImdbOrig = new Rating(Constants::SOURCE_IMDB);
        $ratingImdbOrig->setYourScore(2);
        $ratingImdbOrig->setYourRatingDate(new \DateTime('2000-01-02'));
        $ratingImdbOrig->setSuggestedScore(3);
        $film->setRating($ratingImdbOrig, Constants::SOURCE_IMDB);

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
        $rating = $film->getRating(Constants::SOURCE_JINNI);
        $this->assertEquals("Original_JinniUniqueName", $film->getUniqueName(Constants::SOURCE_JINNI), 'Unique Name');
        $this->assertEquals(3, $film->getCriticScore(Constants::SOURCE_JINNI), 'Critic score');
        $this->assertEquals(4, $film->getUserScore(Constants::SOURCE_JINNI), 'User score');
        $this->assertEquals(1, $rating->getYourScore(), 'Your Score');
        $this->assertEquals("1/1/2000", $rating->getYourRatingDate()->format("n/j/Y"), 'Your Rating Date');
        $this->assertEquals(2, $rating->getSuggestedScore(), 'Suggested score');
        $rating = $film->getRating(Constants::SOURCE_IMDB);
        $this->assertEquals("tt2294629", $film->getUniqueName(Constants::SOURCE_IMDB), 'Unique Name');
        $this->assertEquals(4, $film->getCriticScore(Constants::SOURCE_IMDB), 'Critic score');
        $this->assertEquals(5, $film->getUserScore(Constants::SOURCE_IMDB), 'User score');
        $this->assertEquals(2, $rating->getYourScore(), 'Your Score');
        $this->assertEquals("1/2/2000", $rating->getYourRatingDate()->format("n/j/Y"), 'Your Rating Date');
        $this->assertEquals(3, $rating->getSuggestedScore(), 'Suggested score');
    }
    
    /**
     * @covers \RatingSync\SiteRatings::parseDetailPageForTitle
     * @covers \RatingSync\SiteRatings::parseDetailPageForFilmYear
     * @covers \RatingSync\SiteRatings::parseDetailPageForImage
     * @covers \RatingSync\SiteRatings::parseDetailPageForContentType
     * @covers \RatingSync\SiteRatings::parseDetailPageForUniqueName
     * @covers \RatingSync\SiteRatings::parseDetailPageForRating
     * @covers \RatingSync\SiteRatings::parseDetailPageForGenres
     * @covers \RatingSync\SiteRatings::parseDetailPageForDirectors
     */
    public function testParseDetailPageEmptyFilmOverwriteTrue()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new SiteRatingsChild(TEST_SITE_USERNAME);
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
        $this->assertEquals(1, preg_match('@(https://images-na.ssl-images-amazon.com/images/M/MV5BMTQ1MjQwMTE5OF5BMl5BanBnXkFtZTgwNjk3MTcyMDE)@', $film->getImage($site->_getSourceName()), $matches), 'Check matching Image (empty film overwrite=true)');
        
        $success = $site->_parseDetailPageForContentType($page, $film, true);
        $this->assertFalse($success, 'Parsing film object for Content Type');  // IMDb gets it, but SiteChild does not
        
        $success = $site->_parseDetailPageForUniqueName($page, $film, true);
        $this->assertTrue($success, 'Parsing film object for Film Id');
        $this->assertEquals("tt2294629", $film->getUniqueName($site->_getSourceName()), 'Check matching Unique Name (empty film overwrite=true)');
        
        $success = $site->_parseDetailPageForRating($page, $film, true);
        $this->assertEquals(7.4, $film->getCriticScore($site->_getSourceName()), 'Check matching Critic Score (empty film overwrite=true)');
        $this->assertEquals(FROZEN_USER_SCORE, $film->getUserScore($site->_getSourceName()), 'Check matching User Score (empty film overwrite=true)');
        $rating = $film->getRating($site->_getSourceName());
        $this->assertNull($rating->getYourScore(), 'Check matching YourScore (empty film overwrite=true)');
        $this->assertNull($rating->getYourRatingDate(), 'Check matching Rating Date (empty film overwrite=true)');
        $this->assertNull($rating->getSuggestedScore(), 'Check matching Suggested Score (empty film overwrite=true)');
        
        $success = $site->_parseDetailPageForGenres($page, $film, true);
        $this->assertTrue($success, 'Parsing film object for Genres');
        $this->assertEquals(array("Animation", "Adventure", "Comedy"), $film->getGenres(), 'Check matching Gneres (empty film overwrite=true)');
        
        $success = $site->_parseDetailPageForDirectors($page, $film, true);
        $this->assertTrue($success, 'Parsing film object for Directors');
        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), 'Check matching Directors (empty film overwrite=true)');
    }
    
    /**
     * @covers \RatingSync\SiteRatings::parseDetailPageForTitle
     * @covers \RatingSync\SiteRatings::parseDetailPageForFilmYear
     * @covers \RatingSync\SiteRatings::parseDetailPageForImage
     * @covers \RatingSync\SiteRatings::parseDetailPageForContentType
     * @covers \RatingSync\SiteRatings::parseDetailPageForUniqueName
     * @covers \RatingSync\SiteRatings::parseDetailPageForRating
     * @covers \RatingSync\SiteRatings::parseDetailPageForGenres
     * @covers \RatingSync\SiteRatings::parseDetailPageForDirectors
     */
    public function testParseDetailPageEmptyFilmOverwriteFalse()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new SiteRatingsChild(TEST_SITE_USERNAME);
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
        $this->assertEquals(1, preg_match('@(https://images-na.ssl-images-amazon.com/images/M/MV5BMTQ1MjQwMTE5OF5BMl5BanBnXkFtZTgwNjk3MTcyMDE)@', $film->getImage($site->_getSourceName()), $matches), 'Image link (source)');
        
        $success = $site->_parseDetailPageForContentType($page, $film, false);
        $this->assertFalse($success, 'Parsing film object for Content Type'); // ContentType not available in the detail page
        $this->assertNull($film->getContentType(), 'Check matching Content Type (empty film overwrite=false)');
        
        $success = $site->_parseDetailPageForUniqueName($page, $film, false);
        $this->assertTrue($success, 'Parsing film object for Film Id');
        $this->assertEquals("tt2294629", $film->getUniqueName($site->_getSourceName()), 'Check matching Unique Name (empty film overwrite=false)');
        
        $success = $site->_parseDetailPageForRating($page, $film, false);
        $this->assertEquals(7.4, $film->getCriticScore($site->_getSourceName()), 'Check matching Critic Score (empty film overwrite=false)');
        $this->assertEquals(FROZEN_USER_SCORE, $film->getUserScore($site->_getSourceName()), 'Check matching User Score (empty film overwrite=false)');
        $rating = $film->getRating($site->_getSourceName());
        $this->assertNull($rating->getYourScore(), 'Check matching YourScore (empty film overwrite=false)');
        $this->assertNull($rating->getYourRatingDate(), 'Check matching Rating Date (empty film overwrite=false)');
        $this->assertNull($rating->getSuggestedScore(), 'Check matching Suggested Score (empty film overwrite=false)');
        
        $success = $site->_parseDetailPageForGenres($page, $film, false);
        $this->assertTrue($success, 'Parsing film object for Genres');
        $this->assertEquals(array("Animation", "Adventure", "Comedy"), $film->getGenres(), 'Check matching Gneres (empty film overwrite=false)');
        
        $success = $site->_parseDetailPageForDirectors($page, $film, false);
        $this->assertTrue($success, 'Parsing film object for Directors');
        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), 'Check matching Directors (empty film overwrite=false)');
    }
    
    /**
     * @covers \RatingSync\SiteRatings::parseDetailPageForTitle
     * @covers \RatingSync\SiteRatings::parseDetailPageForFilmYear
     * @covers \RatingSync\SiteRatings::parseDetailPageForImage
     * @covers \RatingSync\SiteRatings::parseDetailPageForContentType
     * @covers \RatingSync\SiteRatings::parseDetailPageForUniqueName
     * @covers \RatingSync\SiteRatings::parseDetailPageForRating
     * @covers \RatingSync\SiteRatings::parseDetailPageForGenres
     * @covers \RatingSync\SiteRatings::parseDetailPageForDirectors
     */
    public function testParseDetailPageFullFilmOverwriteTrue()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new SiteRatingsChild(TEST_SITE_USERNAME);
        $film = new Film();

        // Setup original data
        $film->setTitle("Original_Title");
        $film->setYear(1900);
        $film->setContentType(Film::CONTENT_TV_SERIES);
        $film->setImage("Original_Image");
        $film->setImage("Original_IMDbImage", $site->_getSourceName());
        $film->setImage("Original_JinniImage", Constants::SOURCE_JINNI);
        $film->setUniqueName("Original_IMDbUniqueName", $site->_getSourceName());
        $film->setUniqueName("Original_JinniUniqueName", Constants::SOURCE_JINNI);
        $film->setCriticScore(4, $site->_getSourceName());
        $film->setCriticScore(3, Constants::SOURCE_JINNI);
        $film->setUserScore(5, $site->_getSourceName());
        $film->setUserScore(4, Constants::SOURCE_JINNI);
        $film->addGenre("Original_Genre1");
        $film->addGenre("Original_Genre2");
        $film->addDirector("Original_Director1");
        $film->addDirector("Original_Director2");
        $ratingImdbOrig = new Rating($site->_getSourceName());
        $ratingImdbOrig->setYourScore(2);
        $ratingImdbOrig->setYourRatingDate(new \DateTime('2000-01-02'));
        $ratingImdbOrig->setSuggestedScore(3);
        $film->setRating($ratingImdbOrig, $site->_getSourceName());
        $ratingJinniOrig = new Rating(Constants::SOURCE_JINNI);
        $ratingJinniOrig->setYourScore(1);
        $ratingJinniOrig->setYourRatingDate(new \DateTime('2000-01-01'));
        $ratingJinniOrig->setSuggestedScore(2);
        $film->setRating($ratingJinniOrig, Constants::SOURCE_JINNI);

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
        $this->assertEquals(Film::CONTENT_TV_SERIES, $film->getContentType(), 'Check matching Content Type (full film overwrite=true)');

        $success = $site->_parseDetailPageForImage($page, $film, true);
        $this->assertTrue($success, 'Parsing film object for Image');
        $this->assertEquals("Original_Image", $film->getImage(), 'Check matching Image (full film overwrite=true)');
        $this->assertEquals("Original_JinniImage", $film->getImage(Constants::SOURCE_JINNI), 'Check matching Image (full film overwrite=true)');
        $this->assertEquals(1, preg_match('@(https://images-na.ssl-images-amazon.com/images/M/MV5BMTQ1MjQwMTE5OF5BMl5BanBnXkFtZTgwNjk3MTcyMDE)@', $film->getImage($site->_getSourceName()), $matches), 'Check matching Image (full film overwrite=true)');
        
        $success = $site->_parseDetailPageForUniqueName($page, $film, true);
        $this->assertTrue($success, 'Parsing film object for Film Id');
        $this->assertEquals("tt2294629", $film->getUniqueName($site->_getSourceName()), 'Check matching Unique Name (full film overwrite=true)');
        $this->assertEquals("Original_JinniUniqueName", $film->getUniqueName(Constants::SOURCE_JINNI), 'Check matching Unique Name (full film overwrite=true)');
        
        $success = $site->_parseDetailPageForRating($page, $film, true);
        $this->assertEquals(7.4, $film->getCriticScore($site->_getSourceName()), 'Check matching Critic Score (full film overwrite=true)');
        $this->assertEquals(FROZEN_USER_SCORE, $film->getUserScore($site->_getSourceName()), 'Check matching User Score (full film overwrite=true)');
        $this->assertEquals(3, $film->getCriticScore(Constants::SOURCE_JINNI), 'Check matching Critic Score (full film overwrite=true)');
        $this->assertEquals(4, $film->getUserScore(Constants::SOURCE_JINNI), 'Check matching User Score (full film overwrite=true)');
        $rating = $film->getRating($site->_getSourceName());
        $this->assertEquals(2, $rating->getYourScore(), 'Check matching YourScore (full film overwrite=true)');
        $this->assertEquals(new \DateTime('2000-01-02'), $rating->getYourRatingDate(), 'Check matching Rating Date (full film overwrite=true)');
        $this->assertEquals(3, $rating->getSuggestedScore(), 'Check matching Suggested Score (full film overwrite=true)');
        $rating = $film->getRating(Constants::SOURCE_JINNI);
        $this->assertEquals(1, $rating->getYourScore(), 'Check matching YourScore (full film overwrite=true)');
        $this->assertEquals(new \DateTime('2000-01-01'), $rating->getYourRatingDate(), 'Check matching Rating Date (full film overwrite=true)');
        $this->assertEquals(2, $rating->getSuggestedScore(), 'Check matching Suggested Score (full film overwrite=true)');
        
        $success = $site->_parseDetailPageForGenres($page, $film, true);
        $this->assertTrue($success, 'Parsing film object for Genres');
        $this->assertEquals(array("Animation", "Adventure", "Comedy"), $film->getGenres(), 'Check matching Gneres (full film overwrite=true)');
        
        $success = $site->_parseDetailPageForDirectors($page, $film, true);
        $this->assertTrue($success, 'Parsing film object for Directors');
        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), 'Check matching Directors (full film overwrite=true)');
    }
    
    /**
     * @covers \RatingSync\SiteRatings::parseDetailPageForTitle
     * @covers \RatingSync\SiteRatings::parseDetailPageForFilmYear
     * @covers \RatingSync\SiteRatings::parseDetailPageForImage
     * @covers \RatingSync\SiteRatings::parseDetailPageForContentType
     * @covers \RatingSync\SiteRatings::parseDetailPageForUniqueName
     * @covers \RatingSync\SiteRatings::parseDetailPageForRating
     * @covers \RatingSync\SiteRatings::parseDetailPageForGenres
     * @covers \RatingSync\SiteRatings::parseDetailPageForDirectors
     */
    public function testParseDetailPageFullFilmOverwriteFalse()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new SiteRatingsChild(TEST_SITE_USERNAME);
        $film = new Film();

        // Setup original data
        $film->setTitle("Original_Title");
        $film->setYear(1900);
        $film->setContentType(Film::CONTENT_TV_SERIES);
        $film->addGenre("Original_Genre1");
        $film->addGenre("Original_Genre2");
        $film->addDirector("Original_Director1");
        $film->addDirector("Original_Director2");
        $film->setImage("Original_Image");
        $film->setImage("Original_Image_Imdb", $site->_getSourceName());
        $film->setImage("Original_Image_Jinni", Constants::SOURCE_JINNI);
        $film->setUniqueName("Original_UniqueName_Imdb", $site->_getSourceName());
        $film->setUniqueName("Original_UniqueName_Jinni", Constants::SOURCE_JINNI);
        $film->setCriticScore(4, $site->_getSourceName());
        $film->setCriticScore(3, Constants::SOURCE_JINNI);
        $film->setUserScore(5, $site->_getSourceName());
        $film->setUserScore(4, Constants::SOURCE_JINNI);
        $ratingImdbOrig = new Rating($site->_getSourceName());
        $ratingImdbOrig->setYourScore(2);
        $ratingImdbOrig->setYourRatingDate(new \DateTime('2000-01-02'));
        $ratingImdbOrig->setSuggestedScore(3);
        $film->setRating($ratingImdbOrig, $site->_getSourceName());
        $ratingJinniOrig = new Rating(Constants::SOURCE_JINNI);
        $ratingJinniOrig->setYourScore(1);
        $ratingJinniOrig->setYourRatingDate(new \DateTime('2000-01-01'));
        $ratingJinniOrig->setSuggestedScore(2);
        $film->setRating($ratingJinniOrig, Constants::SOURCE_JINNI);

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
        $this->assertEquals(Film::CONTENT_TV_SERIES, $film->getContentType(), 'Check matching Content Type (full film overwrite=false)');

        $success = $site->_parseDetailPageForImage($page, $film, false);
        $this->assertFalse($success, 'Parsing film object for Image');
        $this->assertEquals("Original_Image", $film->getImage(), 'Check matching Image (full film overwrite=false)');
        $this->assertEquals("Original_Image_Imdb", $film->getImage($site->_getSourceName()), 'Check matching Image (full film overwrite=false)');
        $this->assertEquals("Original_Image_Jinni", $film->getImage(Constants::SOURCE_JINNI), 'Check matching Image (full film overwrite=false)');
        
        $success = $site->_parseDetailPageForUniqueName($page, $film, false);
        $this->assertFalse($success, 'Parsing film object for Unique Name');
        $this->assertEquals("Original_UniqueName_Imdb", $film->getUniqueName($site->_getSourceName()), 'Check matching Unique Name (full film overwrite=false)');
        $this->assertEquals("Original_UniqueName_Jinni", $film->getUniqueName(Constants::SOURCE_JINNI), 'Check matching Unique Name (full film overwrite=false)');
        
        $success = $site->_parseDetailPageForRating($page, $film, false);
        $this->assertEquals(4, $film->getCriticScore($site->_getSourceName()), 'Check matching Critic Score (full film overwrite=false)');
        $this->assertEquals(5, $film->getUserScore($site->_getSourceName()), 'Check matching User Score (full film overwrite=false)');
        $this->assertEquals(3, $film->getCriticScore(Constants::SOURCE_JINNI), 'Check matching Critic Score (full film overwrite=false)');
        $this->assertEquals(4, $film->getUserScore(Constants::SOURCE_JINNI), 'Check matching User Score (full film overwrite=false)');
        $rating = $film->getRating($site->_getSourceName());
        $this->assertEquals(2, $rating->getYourScore(), 'Check matching YourScore (full film overwrite=false)');
        $this->assertEquals(new \DateTime('2000-01-02'), $rating->getYourRatingDate(), 'Check matching Rating Date (full film overwrite=false)');
        $this->assertEquals(3, $rating->getSuggestedScore(), 'Check matching Suggested Score (full film overwrite=false)');
        $rating = $film->getRating(Constants::SOURCE_JINNI);
        $this->assertEquals(1, $rating->getYourScore(), 'Check matching YourScore (full film overwrite=false)');
        $this->assertEquals(new \DateTime('2000-01-01'), $rating->getYourRatingDate(), 'Check matching Rating Date (full film overwrite=false)');
        $this->assertEquals(2, $rating->getSuggestedScore(), 'Check matching Suggested Score (full film overwrite=false)');
        
        $success = $site->_parseDetailPageForGenres($page, $film, false);
        $this->assertFalse($success, 'Parsing film object for Genres');
        $this->assertEquals(array("Original_Genre1", "Original_Genre2"), $film->getGenres(), 'Check matching Gneres (full film overwrite=false)');
        
        $success = $site->_parseDetailPageForDirectors($page, $film, false);
        $this->assertFalse($success, 'Parsing film object for Directors');
        $this->assertEquals(array("Original_Director1", "Original_Director2"), $film->getDirectors(), 'Check matching Directors (full film overwrite=false)');
    }
    
    /**
     * @covers \RatingSync\SiteRatings::testParseFilmsFromFile
     * @depends testValidateAfterConstructorGood
     */
    public function testParseFilmsFromFile()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new SiteRatingsChild(TEST_SITE_USERNAME);
        $filename =  __DIR__ . DIRECTORY_SEPARATOR . "testfile" . DIRECTORY_SEPARATOR . "input_ratings_site.xml";
        $films = $site->parseFilmsFromFile(Constants::EXPORT_FORMAT_XML, $filename);

        // Count: Titles 1-7 plus 3 for Frozen plus 0 for the empty <film/>
        $this->assertCount(10, $films, 'Count films');

        // Frozen from Jinni
        $film = $films[0];
        $this->assertEquals("Frozen", $film->getTitle(), "Frozen title");
        $this->assertEquals(2013, $film->getYear(), "Frozen year");
        $this->assertEquals(Film::CONTENT_FILM, $film->getContentType(), "Frozen ContentType");
        $this->assertEquals("http://media.jinni.com/movie/frozen-2013/frozen-2013-5.jpeg", $film->getImage(), "Frozen image");
        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), "Frozen directors");
        $this->assertEquals(array("Adventure", "Animation", "Fantasy", "Musical", "Family", "Comedy"), $film->getGenres(), "Frozen genres");
        $this->assertEquals("http://media.jinni.com/movie/frozen-2013/frozen-2013-5.jpeg", $film->getImage(Constants::SOURCE_JINNI), "Frozen ".Constants::SOURCE_JINNI." image");
        $this->assertEquals("frozen-2013", $film->getUniqueName(Constants::SOURCE_JINNI), "Frozen ".Constants::SOURCE_JINNI." Unique Name");
        $this->assertNull($film->getCriticScore(Constants::SOURCE_JINNI), "Frozen ".Constants::SOURCE_JINNI." critic score");
        $this->assertNull($film->getUserScore(Constants::SOURCE_JINNI), "Frozen ".Constants::SOURCE_JINNI." user score");
        $rating = $film->getRating(Constants::SOURCE_JINNI);
        $this->assertEquals(8, $rating->getYourScore(), "Frozen ".Constants::SOURCE_JINNI." your score");
        $this->assertEquals("5/4/15", $rating->getYourRatingDate()->format('n/j/y'), "Frozen ".Constants::SOURCE_JINNI." rating date");
        $this->assertNull($rating->getSuggestedScore(), "Frozen ".Constants::SOURCE_JINNI." suggested score");

        // Title1
        $film = $films[1];
        $this->assertEquals("Title1", $film->getTitle(), "Title1 title");
        $this->assertEquals(2001, $film->getYear(), "Title1 year");
        $this->assertEquals(Film::CONTENT_FILM, $film->getContentType(), "Title1 ContentType");
        $this->assertEquals("http://example.com/title1_image.jpeg", $film->getImage(), "Title1 image");
        $this->assertEquals(array("Director1.1"), $film->getDirectors(), "Title1 directors");
        $this->assertEquals(array("Genre1.1"), $film->getGenres(), "Title1 genres");
        $this->assertEquals("http://example.com/title1_rs_image.jpeg", $film->getImage(Constants::SOURCE_RATINGSYNC), "Title1 ".Constants::SOURCE_RATINGSYNC." image");
        $this->assertEquals("UniqueName1_rs", $film->getUniqueName(Constants::SOURCE_RATINGSYNC), "Title1 ".Constants::SOURCE_RATINGSYNC." Unique Name");
        $this->assertEquals(3, $film->getCriticScore(Constants::SOURCE_RATINGSYNC), "Title1 ".Constants::SOURCE_RATINGSYNC." critic score");
        $this->assertEquals(4, $film->getUserScore(Constants::SOURCE_RATINGSYNC), "Title1 ".Constants::SOURCE_RATINGSYNC." user score");
        $rating = $film->getRating(Constants::SOURCE_RATINGSYNC);
        $this->assertEquals(1, $rating->getYourScore(), "Title1 ".Constants::SOURCE_RATINGSYNC." your score");
        $this->assertEquals("1/1/15", $rating->getYourRatingDate()->format('n/j/y'), "Title1 ".Constants::SOURCE_RATINGSYNC." rating date");
        $this->assertEquals(2, $rating->getSuggestedScore(), "Title1 ".Constants::SOURCE_RATINGSYNC." suggested score");

        // Title2
        $film = $films[2];
        $this->assertEquals("Title2", $film->getTitle(), "Title2 title");
        $this->assertEquals(2002, $film->getYear(), "Title2 year");
        $this->assertEquals(Film::CONTENT_FILM, $film->getContentType(), "Title2 ContentType");
        $this->assertEquals("http://example.com/title2_image.jpeg", $film->getImage(), "Title2 image");
        $this->assertEquals(array("Director2.1", "Director2.2"), $film->getDirectors(), "Title2 directors");
        $this->assertEquals(array("Genre2.1", "Genre2.2"), $film->getGenres(), "Title2 genres");
        $this->assertEquals("http://example.com/title2_rs_image.jpeg", $film->getImage(Constants::SOURCE_RATINGSYNC), "Title2 ".Constants::SOURCE_RATINGSYNC." image");
        $this->assertEquals("UniqueName2_rs", $film->getUniqueName(Constants::SOURCE_RATINGSYNC), "Title2 ".Constants::SOURCE_RATINGSYNC." Unique Name");
        $this->assertEquals(4, $film->getCriticScore(Constants::SOURCE_RATINGSYNC), "Title2 ".Constants::SOURCE_RATINGSYNC." critic score");
        $this->assertEquals(5, $film->getUserScore(Constants::SOURCE_RATINGSYNC), "Title2 ".Constants::SOURCE_RATINGSYNC." user score");
        $rating = $film->getRating(Constants::SOURCE_RATINGSYNC);
        $this->assertEquals(2, $rating->getYourScore(), "Title2 ".Constants::SOURCE_RATINGSYNC." your score");
        $this->assertEquals("1/2/15", $rating->getYourRatingDate()->format('n/j/y'), "Title2 ".Constants::SOURCE_RATINGSYNC." rating date");
        $this->assertEquals(3, $rating->getSuggestedScore(), "Title2 ".Constants::SOURCE_RATINGSYNC." suggested score");

        // Title3
        $film = $films[3];
        $this->assertEquals("Title3", $film->getTitle(), "Title3 title");
        $this->assertEmpty($film->getYear(), "Title3 year");
        $this->assertEmpty($film->getContentType(), "Title3 ContentType");
        $this->assertEmpty($film->getImage(), "Title3 image");
        $this->assertEmpty($film->getDirectors(), "Title3 directors");
        $this->assertEmpty($film->getGenres(), "Title3 genres");
        $this->assertEmpty($film->getImage(Constants::SOURCE_RATINGSYNC), "Title3 ".Constants::SOURCE_RATINGSYNC." image");
        $this->assertEmpty($film->getUniqueName(Constants::SOURCE_RATINGSYNC), "Title3 ".Constants::SOURCE_RATINGSYNC." Unique Name");
        $this->assertEmpty($film->getCriticScore(Constants::SOURCE_RATINGSYNC), "Title3 ".Constants::SOURCE_RATINGSYNC." critic score");
        $this->assertEmpty($film->getUserScore(Constants::SOURCE_RATINGSYNC), "Title3 ".Constants::SOURCE_RATINGSYNC." user score");
        $rating = $film->getRating(Constants::SOURCE_RATINGSYNC);
        $this->assertEmpty($rating->getYourScore(), "Title3 ".Constants::SOURCE_RATINGSYNC." your score");
        $this->assertEmpty($rating->getYourRatingDate(), "Title3 ".Constants::SOURCE_RATINGSYNC." rating date");
        $this->assertEmpty($rating->getSuggestedScore(), "Title3 ".Constants::SOURCE_RATINGSYNC." suggested score");

        // Title4
        $film = $films[4];
        $this->assertEquals("Title4", $film->getTitle(), "Title3 title");
        $this->assertEmpty($film->getYear(), "Title4 year");
        $this->assertEmpty($film->getContentType(), "Title4 ContentType");
        $this->assertEmpty($film->getImage(), "Title4 image");
        $this->assertEmpty($film->getDirectors(), "Title4 directors");
        $this->assertEmpty($film->getGenres(), "Title4 genres");
        $this->assertEmpty($film->getImage(Constants::SOURCE_RATINGSYNC), "Title4 ".Constants::SOURCE_RATINGSYNC." image");
        $this->assertEmpty($film->getUniqueName(Constants::SOURCE_RATINGSYNC), "Title4 ".Constants::SOURCE_RATINGSYNC." Unique Name");
        $this->assertEmpty($film->getCriticScore(Constants::SOURCE_RATINGSYNC), "Title4 ".Constants::SOURCE_RATINGSYNC." critic score");
        $this->assertEmpty($film->getUserScore(Constants::SOURCE_RATINGSYNC), "Title4 ".Constants::SOURCE_RATINGSYNC." user score");
        $rating = $film->getRating(Constants::SOURCE_RATINGSYNC);
        $this->assertEmpty($rating->getYourScore(), "Title4 ".Constants::SOURCE_RATINGSYNC." your score");
        $this->assertEmpty($rating->getYourRatingDate(), "Title4 ".Constants::SOURCE_RATINGSYNC." rating date");
        $this->assertEmpty($rating->getSuggestedScore(), "Title4 ".Constants::SOURCE_RATINGSYNC." suggested score");

        // Title5
        $film = $films[5];
        $this->assertEquals("Title5", $film->getTitle(), "Title5 title");
        $this->assertEmpty($film->getYear(), "Title5 year");
        $this->assertEmpty($film->getContentType(), "Title5 ContentType");
        $this->assertEmpty($film->getImage(), "Title5 image");
        $this->assertEmpty($film->getDirectors(), "Title5 directors");
        $this->assertEmpty($film->getGenres(), "Title5 genres");
        $this->assertEmpty($film->getImage(Constants::SOURCE_RATINGSYNC), "Title5 ".Constants::SOURCE_RATINGSYNC." image");
        $this->assertEmpty($film->getUniqueName(Constants::SOURCE_RATINGSYNC), "Title5 ".Constants::SOURCE_RATINGSYNC." Unique Name");
        $this->assertEmpty($film->getCriticScore(Constants::SOURCE_RATINGSYNC), "Title5 ".Constants::SOURCE_RATINGSYNC." critic score");
        $this->assertEmpty($film->getUserScore(Constants::SOURCE_RATINGSYNC), "Title5 ".Constants::SOURCE_RATINGSYNC." user score");
        $rating = $film->getRating(Constants::SOURCE_RATINGSYNC);
        $this->assertEmpty($rating->getYourScore(), "Title5 ".Constants::SOURCE_RATINGSYNC." your score");
        $this->assertEmpty($rating->getYourRatingDate(), "Title5 ".Constants::SOURCE_RATINGSYNC." rating date");
        $this->assertEmpty($rating->getSuggestedScore(), "Title5 ".Constants::SOURCE_RATINGSYNC." suggested score");

        // Title6
        $film = $films[6];
        $this->assertEquals("Title6", $film->getTitle(), "Title6 title");
        $this->assertEquals(2006, $film->getYear(), "Title6 year");
        $this->assertEquals(Film::CONTENT_FILM, $film->getContentType(), "Title6 ContentType");
        $this->assertEquals("http://example.com/title6_image.jpeg", $film->getImage(), "Title6 image");
        $this->assertEquals(array("Director6.1"), $film->getDirectors(), "Title6 directors");
        $this->assertEquals(array("Genre6.1"), $film->getGenres(), "Title6 genres");
        $this->assertEquals("http://example.com/title6_rs_image.jpeg", $film->getImage(Constants::SOURCE_RATINGSYNC), "Title6 ".Constants::SOURCE_RATINGSYNC." image");
        $this->assertEquals("UniqueName6_rs", $film->getUniqueName(Constants::SOURCE_RATINGSYNC), "Title6 ".Constants::SOURCE_RATINGSYNC." Unique Name");
        $this->assertEquals(8, $film->getCriticScore(Constants::SOURCE_RATINGSYNC), "Title6 ".Constants::SOURCE_RATINGSYNC." critic score");
        $this->assertEquals(9, $film->getUserScore(Constants::SOURCE_RATINGSYNC), "Title6 ".Constants::SOURCE_RATINGSYNC." user score");
        $rating = $film->getRating(Constants::SOURCE_RATINGSYNC);
        $this->assertEquals(6, $rating->getYourScore(), "Title6 ".Constants::SOURCE_RATINGSYNC." your score");
        $this->assertEquals("1/6/15", $rating->getYourRatingDate()->format('n/j/y'), "Title6 ".Constants::SOURCE_RATINGSYNC." rating date");
        $this->assertEquals(7, $rating->getSuggestedScore(), "Title6 ".Constants::SOURCE_RATINGSYNC." suggested score");
        $this->assertEquals("http://example.com/title6_imdb_image.jpeg", $film->getImage(Constants::SOURCE_IMDB), "Title6 ".Constants::SOURCE_IMDB." image");
        $this->assertEquals("UniqueName6_imdb", $film->getUniqueName(Constants::SOURCE_IMDB), "Title6 ".Constants::SOURCE_IMDB." Unique Name");
        $this->assertEquals(7, $film->getCriticScore(Constants::SOURCE_IMDB), "Title6 ".Constants::SOURCE_IMDB." critic score");
        $this->assertEquals(8, $film->getUserScore(Constants::SOURCE_IMDB), "Title6 ".Constants::SOURCE_IMDB." user score");
        $rating = $film->getRating(Constants::SOURCE_IMDB);
        $this->assertEquals(5, $rating->getYourScore(), "Title6 ".Constants::SOURCE_IMDB." your score");
        $this->assertEquals("1/5/15", $rating->getYourRatingDate()->format('n/j/y'), "Title6 ".Constants::SOURCE_IMDB." rating date");
        $this->assertEquals(6, $rating->getSuggestedScore(), "Title6 ".Constants::SOURCE_IMDB." suggested score");

        // Title7
        $film = $films[7];
        $this->assertEquals("Wallace & Gromit: A Matter of Loaf and Déath", $film->getTitle(), "Title7 title");
        $this->assertEquals(array("Georges Méliès"), $film->getDirectors(), "Title7 directors");
        $this->assertEquals(array("Genre 1 & 1ès"), $film->getGenres(), "Title7 genres");

        // Frozen from IMDb
        $film = $films[8];
        $this->assertEquals("Frozen", $film->getTitle(), "Frozen title");
        $this->assertEquals(2013, $film->getYear(), "Frozen year");
        $this->assertEquals(Film::CONTENT_FILM, $film->getContentType(), "Frozen ContentType");
        $this->assertEquals(1, preg_match('@(https://images-na.ssl-images-amazon.com/images/M/MV5BMTQ1MjQwMTE5OF5BMl5BanBnXkFtZTgwNjk3MTcyMDE)@', $film->getImage(), $matches), 'Frozen image');
        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), "Frozen directors");
        $this->assertEquals(array("Animation", "Adventure", "Comedy"), $film->getGenres(), "Frozen genres");
        $this->assertEquals(1, preg_match('@(https://images-na.ssl-images-amazon.com/images/M/MV5BMTQ1MjQwMTE5OF5BMl5BanBnXkFtZTgwNjk3MTcyMDE)@', $film->getImage(Constants::SOURCE_IMDB), $matches), "Frozen ".Constants::SOURCE_IMDB." image");
        $this->assertEquals("tt2294629", $film->getUniqueName(Constants::SOURCE_IMDB), "Frozen ".Constants::SOURCE_IMDB." Unique Name");
        $this->assertEquals(7.4, $film->getCriticScore(Constants::SOURCE_IMDB), "Frozen ".Constants::SOURCE_IMDB." critic score");
        $this->assertEquals(7.7, $film->getUserScore(Constants::SOURCE_IMDB), "Frozen ".Constants::SOURCE_IMDB." user score");
        $rating = $film->getRating(Constants::SOURCE_IMDB);
        $this->assertEquals(2, $rating->getYourScore(), "Frozen ".Constants::SOURCE_IMDB." your score");
        $this->assertNull($rating->getYourRatingDate(), "Frozen ".Constants::SOURCE_IMDB." rating date");
        $this->assertNull($rating->getSuggestedScore(), "Frozen ".Constants::SOURCE_IMDB." suggested score");

        // Frozen from All Sources
        $film = $films[9];
        $this->assertEquals("Frozen", $film->getTitle(), "Frozen title");
        $this->assertEquals(2013, $film->getYear(), "Frozen year");
        $this->assertEquals(Film::CONTENT_FILM, $film->getContentType(), "Frozen ContentType");
        $this->assertEquals("http://example.com/frozen_rs_image.jpeg", $film->getImage(), "Frozen image");
        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), "Frozen directors");
        $this->assertEquals(array("Animation", "Adventure", "Comedy", "Fantasy", "Musical", "Family"), $film->getGenres(), "Frozen genres");
        $this->assertEquals("http://example.com/frozen_rs_image.jpeg", $film->getImage(Constants::SOURCE_RATINGSYNC), "Frozen ".Constants::SOURCE_RATINGSYNC." image");
        $this->assertEquals("Frozen_rs", $film->getUniqueName(Constants::SOURCE_RATINGSYNC), "Frozen ".Constants::SOURCE_RATINGSYNC." Unique Name");
        $this->assertEquals(4, $film->getCriticScore(Constants::SOURCE_RATINGSYNC), "Frozen ".Constants::SOURCE_RATINGSYNC." critic score");
        $this->assertEquals(5, $film->getUserScore(Constants::SOURCE_RATINGSYNC), "Frozen ".Constants::SOURCE_RATINGSYNC." user score");
        $rating = $film->getRating(Constants::SOURCE_RATINGSYNC);
        $this->assertEquals(2, $rating->getYourScore(), "Frozen ".Constants::SOURCE_RATINGSYNC." your score");
        $this->assertEquals("1/2/15", $rating->getYourRatingDate()->format('n/j/y'), "Frozen ".Constants::SOURCE_RATINGSYNC." rating date");
        $this->assertEquals(3, $rating->getSuggestedScore(), "Frozen ".Constants::SOURCE_RATINGSYNC." suggested score");
        $this->assertEquals("http://media.jinni.com/movie/frozen-2013/frozen-2013-5.jpeg", $film->getImage(Constants::SOURCE_JINNI), "Frozen ".Constants::SOURCE_JINNI." image");
        $this->assertEquals("frozen-2013", $film->getUniqueName(Constants::SOURCE_JINNI), "Frozen ".Constants::SOURCE_JINNI." Unique Name");
        $this->assertNull($film->getCriticScore(Constants::SOURCE_JINNI), "Frozen ".Constants::SOURCE_JINNI." critic score");
        $this->assertNull($film->getUserScore(Constants::SOURCE_JINNI), "Frozen ".Constants::SOURCE_JINNI." user score");
        $rating = $film->getRating(Constants::SOURCE_JINNI);
        $this->assertEquals(8, $rating->getYourScore(), "Frozen ".Constants::SOURCE_JINNI." your score");
        $this->assertEquals("5/4/15", $rating->getYourRatingDate()->format('n/j/y'), "Frozen ".Constants::SOURCE_JINNI." rating date");
        $this->assertNull($rating->getSuggestedScore(), "Frozen ".Constants::SOURCE_JINNI." suggested score");
        $this->assertEquals(1, preg_match('@(https://images-na.ssl-images-amazon.com/images/M/MV5BMTQ1MjQwMTE5OF5BMl5BanBnXkFtZTgwNjk3MTcyMDE)@', $film->getImage(Constants::SOURCE_IMDB), $matches), "Frozen ".Constants::SOURCE_IMDB." image");
        $this->assertEquals("tt2294629", $film->getUniqueName(Constants::SOURCE_IMDB), "Frozen ".Constants::SOURCE_IMDB." Unique Name");
        $this->assertEquals(7.4, $film->getCriticScore(Constants::SOURCE_IMDB), "Frozen ".Constants::SOURCE_IMDB." critic score");
        $this->assertEquals(7.7, $film->getUserScore(Constants::SOURCE_IMDB), "Frozen ".Constants::SOURCE_IMDB." user score");
        $rating = $film->getRating(Constants::SOURCE_IMDB);
        $this->assertEquals(2, $rating->getYourScore(), "Frozen ".Constants::SOURCE_IMDB." your score");
        $this->assertNull($rating->getYourRatingDate(), "Frozen ".Constants::SOURCE_IMDB." rating date");
        $this->assertNull($rating->getSuggestedScore(), "Frozen ".Constants::SOURCE_IMDB." suggested score");
    }
    
    /**
     * @covers \RatingSync\SiteRatings::testFromExportFileToFilmObjectAndBackToXml
     * @depends testParseFilmsFromFile
     */
    public function testFromExportFileToFilmObjectAndBackToXml()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new SiteRatingsChild(TEST_SITE_USERNAME);
        
        // Get Film objects from a XML file (original_xml)
        $fullOriginalFilename = "testfile/original_xml.xml";
        $this->assertTrue(is_readable($fullOriginalFilename), 'Need to read verify file ' . $fullOriginalFilename);
        $films = $site->parseFilmsFromFile(Constants::EXPORT_FORMAT_XML, $fullOriginalFilename);

        // Write the Film object to a new XML file (test_writing_xml)
        $xml = new \SimpleXMLElement("<films/>");
        foreach ($films as $film) {
            $film->addXmlChild($xml);
        }
        $filmCount = $xml->count();
        $xml->addChild('count', $filmCount);
        
        $testFilename = "test_writing_xml.xml";
        $fullTestFilename =  Constants::outputFilePath() . $testFilename;
        $fp = fopen($fullTestFilename, "w");
        fwrite($fp, $xml->asXml());
        fclose($fp);

        // Assert they are equal
        $fp_test = fopen($fullTestFilename, "r");
        $fp_verify = fopen($fullOriginalFilename, "r");
        $testFileSize = filesize($fullTestFilename);
        $verifyFileSize = filesize($fullOriginalFilename);
        $this->assertEquals($testFileSize, $verifyFileSize, 'File sizes - test (written) vs verify (original)');
        $test = fread($fp_test, filesize($fullTestFilename));
        $verify = fread($fp_verify, filesize($fullOriginalFilename));
        $this->assertEquals($verify, $test, 'Match test file (written) vs verify file (original)');
        fclose($fp_test);
        fclose($fp_verify);
    }

    public function testResetDb()
    {$this->start(__CLASS__, __FUNCTION__);

        DatabaseTest::resetDb();
    }

    /**
     * @covers \RatingSync\SiteRatings::importRatings
     * @depends testParseFilmsFromFile
     * @depends testResetDb
     */
    public function testImport()
    {$this->start(__CLASS__, __FUNCTION__);

        $username_site = TEST_IMDB_USERNAME;
        $username_rs = Constants::TEST_RATINGSYNC_USERNAME;
        $site = new SiteRatingsChild($username_site);
        
        $filename =  __DIR__ . DIRECTORY_SEPARATOR . "testfile" . DIRECTORY_SEPARATOR . "input_ratings_site.xml";
        $films = $site->importRatings(Constants::IMPORT_FORMAT_XML, $filename, $username_rs);
        
        $db = getDatabase(Constants::DB_MODE_TEST);

        // Count rows in each table
        $result = $db->query("SELECT count(id) as count FROM film");
        $row = $result->fetch_assoc();
        $this->assertEquals(8, $row["count"], "Films");
        $result = $db->query("SELECT count(film_id) as count FROM film_source");
        $row = $result->fetch_assoc();
        $this->assertEquals(12, $row["count"], "Film/Source rows");
        $result = $db->query("SELECT count(film_id) as count FROM rating");
        $row = $result->fetch_assoc();
        $this->assertEquals(10, $row["count"], "Ratings");
        $result = $db->query("SELECT count(name) as count FROM genre");
        $row = $result->fetch_assoc();
        $this->assertEquals(11, $row["count"], "Genres");
        $result = $db->query("SELECT count(film_id) as count FROM film_genre");
        $row = $result->fetch_assoc();
        $this->assertEquals(11, $row["count"], "Film/Genre rows");
        $result = $db->query("SELECT count(fullname) as count FROM person");
        $row = $result->fetch_assoc();
        $this->assertEquals(7, $row["count"], "People");
        $result = $db->query("SELECT count(film_id) as count FROM credit");
        $row = $result->fetch_assoc();
        $this->assertEquals(7, $row["count"], "Credits");

        // Ratings for the test user
        $result = $db->query("SELECT title, yourScore FROM rating, film WHERE rating.user_name='$username_rs' AND rating.source_name='RatingSync' AND film.id=rating.film_id");
        $row = $result->fetch_assoc();
        $this->assertEquals(6, $result->num_rows, "Ratings from RS");
        $result = $db->query("SELECT title, yourScore FROM rating, film WHERE rating.user_name='$username_rs' AND rating.source_name='Jinni' AND film.id=rating.film_id");
        $row = $result->fetch_assoc();
        $this->assertEquals(2, $result->num_rows, "Ratings from Jinni");
        $result = $db->query("SELECT title, yourScore FROM rating, film WHERE rating.user_name='$username_rs' AND rating.source_name='IMDb' AND film.id=rating.film_id");
        $row = $result->fetch_assoc();
        $this->assertEquals(2, $result->num_rows, "Ratings from IMDb");
        $result = $db->query("SELECT source_name, yourScore FROM rating, film WHERE rating.user_name='$username_rs' AND film.title='Title6' AND film.id=rating.film_id");
        while ($row = $result->fetch_assoc()) {
            $source = $row["source_name"];
            if ($source == "RatingSync") {
                $this->assertEquals(6, $row["yourScore"], "Your score for Title6 from RatingSync");
            } elseif ($source == "Jinni") {
                $this->assertEquals(4, $row["yourScore"], "Your score for Title6 from Jinni");
            } elseif ($source == "IMDb") {
                $this->assertEquals(5, $row["yourScore"], "Your score for Title6 from IMDb");
            }
        }

        // Title Wallace has no source in the file. Import will save a RS source
        $result = $db->query("SELECT uniqueName FROM film_source WHERE film_id=8 AND source_name='".Constants::SOURCE_RATINGSYNC."'");
        $this->assertEquals(1, $result->num_rows, "Should 1 Film/Source for RatingSync source");
        $row = $result->fetch_assoc();
        $this->assertEquals("rs8", $row["uniqueName"], "Film/Source for filmId 8");
    }
}

?>
