<?php
/**
 * Imdb PHPUnit
 */
namespace RatingSync;

require_once "../Imdb.php";

const TEST_IMDB_USERNAME = "ur60460017";

// Class to expose protected members and functions
class ImdbExt extends \RatingSync\Imdb {
    function _getHttp() { return $this->http; }
    function _getSourceName() { return $this->sourceName; }
    function _getUsername() { return $this->username; }

    function _getRatingPageUrl($args) { return $this->getRatingPageUrl($args); }
    function _getNextRatingPageNumber($page) { return $this->getNextRatingPageNumber($page); }
    function _parseDetailPageForTitle($page, $film, $overwrite) { return $this->parseDetailPageForTitle($page, $film, $overwrite); }
    function _parseDetailPageForFilmYear($page, $film, $overwrite) { return $this->parseDetailPageForFilmYear($page, $film, $overwrite); }
    function _parseDetailPageForImage($page, $film, $overwrite) { return $this->parseDetailPageForImage($page, $film, $overwrite); }
    function _parseDetailPageForContentType($page, $film, $overwrite) { return $this->parseDetailPageForContentType($page, $film, $overwrite); }
    function _parseDetailPageForFilmName($page, $film, $overwrite) { return $this->parseDetailPageForFilmName($page, $film, $overwrite); }
    function _parseDetailPageForUrlName($page, $film, $overwrite) { return $this->parseDetailPageForUrlName($page, $film, $overwrite); }
    function _parseDetailPageForRating($page, $film, $overwrite) { return $this->parseDetailPageForRating($page, $film, $overwrite); }
    function _parseDetailPageForGenres($page, $film, $overwrite) { return $this->parseDetailPageForGenres($page, $film, $overwrite); }
    function _parseDetailPageForDirectors($page, $film, $overwrite) { return $this->parseDetailPageForDirectors($page, $film, $overwrite); }
}

class ImdbTest extends \PHPUnit_Framework_TestCase
{
    public $debug;
    public $lastTestTime;

    public function setUp()
    {
        $this->debug = false;
        $this->lastTestTime = new \DateTime();
    }

    /**
     * @covers            \RatingSync\Imdb::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testCannotBeConstructedFromNull()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        new Imdb(null);
    }

    /**
     * @covers            \RatingSync\Imdb::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testCannotBeConstructedFromEmptyUsername()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        new Imdb("");
    }

    /**
     * @covers \RatingSync\Imdb::__construct
     */
    public function testObjectCanBeConstructed()
    {
        $site = new Imdb(TEST_IMDB_USERNAME);

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers \RatingSync\Imdb::getRatings
     * @depends testObjectCanBeConstructed
     * @expectedException \RatingSync\HttpNotFoundException
     */
    public function testGetRatingsUsernameWithNoMatch()
    {
        $site = new Imdb("---Username--No--Match---");
        $films = $site->getRatings();

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers \RatingSync\Imdb::cacheRatingsPage
     * @depends testObjectCanBeConstructed
     */
    public function testCacheRatingsPage()
    {
        $site = new ImdbExt(TEST_IMDB_USERNAME);

        $page = "<html><body><h2>Rating page 2</h2></body></html>";
        $verifyFilename = "testfile" . DIRECTORY_SEPARATOR . "verify_cache_ratingspage.xml";
        $fp = fopen($verifyFilename, "w");
        fwrite($fp, $page);
        fclose($fp);

        $site->cacheRatingsPage($page, 2);
        $testFilename = Constants::cacheFilePath() . $site->_getSourceName() . "_" . TEST_IMDB_USERNAME . "_ratings_2.html";
        $this->assertFileExists($testFilename, 'Cache file exists');
        $this->assertFileEquals($verifyFilename, $testFilename, 'cache file vs verify file');
        
        unlink($verifyFilename);
        unlink($testFilename);

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers \RatingSync\Imdb::cacheFilmDetailPage
     * @depends testObjectCanBeConstructed
     */
    public function testCacheFilmDetailPage()
    {
        $site = new ImdbExt(TEST_IMDB_USERNAME);
        $film = new Film($site->http);
        $film->setFilmName("tt2294629", $site->_getSourceName());
        
        $page = "<html><body><h2>Film Detail</h2></body></html>";
        $verifyFilename = "testfile" . DIRECTORY_SEPARATOR . "verify_cache_filmdetailpage.xml";
        $fp = fopen($verifyFilename, "w");
        fwrite($fp, $page);
        fclose($fp);
        
        $site->cacheFilmDetailPage($page, $film);
        $testFilename = Constants::cacheFilePath() . $site->_getSourceName() . "_" . TEST_IMDB_USERNAME . "_film_" . $site->getFilmUniqueAttr($film) . ".html";
        $this->assertFileExists($testFilename, 'Cache file exists');
        $this->assertFileEquals($verifyFilename, $testFilename, 'cache file vs verify file');
        
        unlink($verifyFilename);
        unlink($testFilename);

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers \RatingSync\Imdb::getRatings
     * @depends testObjectCanBeConstructed
     * @depends testCacheRatingsPage
     */
    public function testGetRatings()
    {
        $site = new Imdb(TEST_IMDB_USERNAME);
        $films = $site->getRatings();

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers \RatingSync\Imdb::getRatings
     * @depends testGetRatings
     */
    public function testGetRatingsUsingCacheAlways()
    {
        // Want cached files ready for this test: Yes
        $site = new Imdb(TEST_IMDB_USERNAME);

        // limitPages=null, beginPage=1, detail=false, refreshCache=-1 (always use cache)
        $films = $site->getRatings(null, 1, false, Constants::USE_CACHE_ALWAYS);

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers \RatingSync\Imdb::getRatings
     * @depends testGetRatings
     */
    public function testGetRatingsUsingCacheNever()
    {
        // Want cached files ready for this test: Yes
        $site = new Imdb(TEST_IMDB_USERNAME);

        // limitPages=null, beginPage=1, detail=false, refreshCache=0 (refresh now)
        $films = $site->getRatings(null, 1, false, Constants::USE_CACHE_NEVER);

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers \RatingSync\Imdb::getRatings
     * @depends testGetRatings
     */
    public function testGetRatingsUsingCacheWithRecentFiles()
    {
        // Want cached files ready for this test: Yes
        $site = new ImdbExt(TEST_IMDB_USERNAME);
        
        sleep(1);
        $timeBeforeGetRatings = time();
        // limitPages=null, beginPage=1, detail=false, refreshCache=60
        $films = $site->getRatings(null, 1, false, 60);
        
        $filename = Constants::cacheFilePath() . $site->_getSourceName() . "_" . TEST_IMDB_USERNAME . "_ratings_1.html";
        $this->assertLessThan($timeBeforeGetRatings, filemtime($filename), "Cache should not be new");

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers \RatingSync\Imdb::getRatings
     * @depends testGetRatings
     */
    public function testGetRatingsUsingCacheWithOldFiles()
    {
        // Want cached files ready for this test: Yes
        $site = new ImdbExt(TEST_IMDB_USERNAME);

        $timeBeforeGetRatings = time();
        sleep(1);
        // limitPages=null, beginPage=1, detail=false, refreshCache=60
        $films = $site->getRatings(null, 1, false, 0.01);
        
        $filename = Constants::cacheFilePath() . $site->_getSourceName() . "_" . TEST_IMDB_USERNAME . "_ratings_1.html";
        $this->assertGreaterThan($timeBeforeGetRatings, filemtime($filename), "Cache should be new");

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers \RatingSync\Imdb::getRatings
     * @depends testObjectCanBeConstructed
     * @depends testGetRatings
     */
    public function testGetRatingsCount()
    {
        $site = new Imdb(TEST_IMDB_USERNAME);
        $films = $site->getRatings(null, 1, false, 60);
        $this->assertCount(104, $films);

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers \RatingSync\Imdb::getRatings
     * @depends testObjectCanBeConstructed
     * @depends testGetRatings
     */
    public function testGetRatingsLimitPages()
    {
        $site = new Imdb(TEST_IMDB_USERNAME);
        $films = $site->getRatings(1, 1, false, 60);
        $this->assertCount(100, $films);

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers \RatingSync\Imdb::getRatings
     * @depends testObjectCanBeConstructed
     * @depends testGetRatings
     */
    public function testGetRatingsBeginPage()
    {
        $site = new Imdb(TEST_IMDB_USERNAME);
        $films = $site->getRatings(null, 2, false, 60);
        $this->assertEquals("Unbroken", $films[0]->getTitle());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }
    
    /**
     * @covers \RatingSync\Imdb::getRatings
     * @depends testObjectCanBeConstructed
     * @depends testGetRatingsLimitPages
     * @depends testGetRatingsBeginPage
     */
    public function testGetRatingsDetailsNoCache()
    {
        $site = new ImdbExt(TEST_IMDB_USERNAME);
        $films = $site->getRatings(1, 1, true, Constants::USE_CACHE_NEVER);
        $this->assertCount(100, $films);
        $film = $films[0];
        $this->assertEquals("Almost Famous", $film->getTitle(), 'Title');
        $this->assertEquals(2000, $film->getYear(), 'Year');
        $this->assertEquals("FeatureFilm", $film->getContentType(), 'Content Type');
        $this->assertEquals(1, preg_match('@(http://ia.media-imdb.com/images/M/MV5BMTI0MDc0MzIyM15BMl5BanBnXkFtZTYwMzc4NzA)@', $film->getImage(), $matches), 'Image link');
        $this->assertEquals(array("Cameron Crowe"), $film->getDirectors(), 'Director(s)');
        $this->assertEquals(array("Drama", "Music", "Romance"), $film->getGenres(), 'Genres');
        $this->assertNull($film->getUrlName($site->_getSourceName()), 'URL Name');
        $rating = $film->getRating($site->_getSourceName());
        $this->assertFalse(is_null($rating));
        $this->assertEquals(6, $rating->getYourScore(), 'Your Score');

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers \RatingSync\Imdb::getRatings
     * @covers \RatingSync\Imdb::cacheRatingsPage
     * @covers \RatingSync\Imdb::cacheFilmDetailPage
     * @depends testGetRatingsDetailsNoCache
     * @depends testCacheRatingsPage
     * @depends testCacheFilmDetailPage
     */
    public function testGetRatingsDetails()
    {
        $site = new ImdbExt(TEST_IMDB_USERNAME);
        $films = $site->getRatings(null, 1, true, 60);
        $this->assertCount(104, $films);
        $film = $films[0];
        $this->assertEquals("Almost Famous", $film->getTitle(), 'Title');
        $this->assertEquals(2000, $film->getYear(), 'Year');
        $this->assertEquals("FeatureFilm", $film->getContentType(), 'Content Type');
        $this->assertEquals(1, preg_match('@(http://ia.media-imdb.com/images/M/MV5BMTI0MDc0MzIyM15BMl5BanBnXkFtZTYwMzc4NzA)@', $film->getImage(), $matches), 'Image link');
        $this->assertEquals(array("Cameron Crowe"), $film->getDirectors(), 'Director(s)');
        $this->assertEquals(array("Drama", "Music", "Romance"), $film->getGenres(), 'Genres');
        $this->assertNull($film->getUrlName($site->_getSourceName()), 'URL Name');
        $rating = $film->getRating($site->_getSourceName());
        $this->assertFalse(is_null($rating));
        $this->assertEquals(6, $rating->getYourScore(), 'Your Score');

        // Cache files should exist for Ratings pages and Detail pages
        $firstRatingsFile = Constants::cacheFilePath() . $site->_getSourceName() . "_" . $site->_getUsername() . "_ratings_1.html";
        $firstDetailFile = Constants::cacheFilePath() . $site->_getSourceName() . "_" . $site->_getUsername() . "_film_" . $site->getFilmUniqueAttr($film) . ".html";
        $this->assertFileExists($firstRatingsFile, 'First ratings page cache file should exist');
        $this->assertFileExists($firstDetailFile, 'First film detail page cache file should exist');

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }
    
    /**
     * @covers \RatingSync\Imdb::getSearchSuggestions
     * @depends testObjectCanBeConstructed
    public function testGetSearchSuggestions()
    {
        // Search suggestions are not implemented for IMDb
    }
     */

    /**
     * @covers \RatingSync\Imdb::getFilmDetailFromWebsite
     * @depends testObjectCanBeConstructed
     * @expectedException \InvalidArgumentException
     */
    public function testGetFilmDetailFromWebsiteFromNull()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        $site = new Imdb(TEST_IMDB_USERNAME);
        $site->getFilmDetailFromWebsite(null);
    }

    /**
     * @covers \RatingSync\Imdb::getFilmDetailFromWebsite
     * @depends testObjectCanBeConstructed
     * @expectedException \InvalidArgumentException
     */
    public function testGetFilmDetailFromWebsiteFromString()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        $site = new Imdb(TEST_IMDB_USERNAME);
        $site->getFilmDetailFromWebsite("String_Not_Film_Object");
    }

    /**
     * @covers \RatingSync\Imdb::getFilmDetailFromWebsite
     * @depends testObjectCanBeConstructed
     * @expectedException \InvalidArgumentException
     */
    public function testGetFilmDetailFromWebsiteWithoutFilmName()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        $site = new Imdb(TEST_IMDB_USERNAME);
        $film = new Film($site->http);
        $site->getFilmDetailFromWebsite($film);
    }

    /**
     * @covers \RatingSync\Imdb::getFilmDetailFromWebsite
     * @depends testObjectCanBeConstructed
     * @expectedException \Exception
     */
    public function testGetFilmDetailFromWebsiteNoMatch()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        $site = new ImdbExt(TEST_IMDB_USERNAME);
        $film = new Film($site->http);
        $film->setFilmName("NO_FILMID_MATCH", $site->_getSourceName());
        $site->getFilmDetailFromWebsite($film, true);
    }

    /**
     * @covers \RatingSync\Imdb::getFilmDetailFromWebsite
     * @depends testObjectCanBeConstructed
     */
    public function testGetFilmDetailFromWebsite()
    {
        $site = new ImdbExt(TEST_IMDB_USERNAME);

        $film = new Film($site->http);
        $film->setFilmName("tt2294629", $site->_getSourceName());
        $site->getFilmDetailFromWebsite($film, true);

        $this->assertEquals("Frozen", $film->getTitle(), 'Title');
        $this->assertEquals(2013, $film->getYear(), 'Year');
        $this->assertEquals("FeatureFilm", $film->getContentType(), 'Content Type');
        $this->assertEquals(1, preg_match('@(http://ia.media-imdb.com/images/M/MV5BMTQ1MjQwMTE5OF5BMl5BanBnXkFtZTgwNjk3MTcyMDE)@', $film->getImage(), $matches), 'Image link');
        $this->assertEquals("tt2294629", $film->getFilmName($site->_getSourceName()), 'Film ID');
        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), 'Director(s)');
        $this->assertEquals(array("Animation", "Adventure", "Comedy"), $film->getGenres(), 'Genres');
        $rating = $film->getRating($site->_getSourceName());
        $this->assertEquals(7.4, $rating->getCriticScore(), 'Critic score');
        $this->assertEquals(7.7, $rating->getUserScore(), 'User score');

        // Not available in the detail page
        $this->assertNull($film->getUrlName($site->_getSourceName()), 'URL Name');
        $this->assertNull($rating->getYourScore(), 'Your Score');
        $this->assertNull($rating->getYourRatingDate(), 'Rating date');
        $this->assertNull($rating->getSuggestedScore(), 'Suggested score');

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers \RatingSync\Imdb::getFilmDetailFromWebsite
     * @depends testGetFilmDetailFromWebsite
     */
    public function testGetFilmDetailFromWebsiteOverwriteTrueOverEmpty()
    {
        $site = new ImdbExt(TEST_IMDB_USERNAME);

        $film = new Film($site->http);
        $film->setFilmName("tt2294629", $site->_getSourceName());
        $site->getFilmDetailFromWebsite($film, true);

        // Same results as testGetFilmDetailFromWebsite
        $this->assertEquals("Frozen", $film->getTitle(), 'Title');
        $this->assertEquals(2013, $film->getYear(), 'Year');
        $this->assertEquals("FeatureFilm", $film->getContentType(), 'Content Type');
        $this->assertEquals(1, preg_match('@(http://ia.media-imdb.com/images/M/MV5BMTQ1MjQwMTE5OF5BMl5BanBnXkFtZTgwNjk3MTcyMDE)@', $film->getImage(), $matches), 'Image link');
        $this->assertEquals(1, preg_match('@(http://ia.media-imdb.com/images/M/MV5BMTQ1MjQwMTE5OF5BMl5BanBnXkFtZTgwNjk3MTcyMDE)@', $film->getImage($site->_getSourceName()), $matches), 'Image link');
        $this->assertEquals("tt2294629", $film->getFilmName($site->_getSourceName()), 'Film ID');
        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), 'Director(s)');
        $this->assertEquals(array("Animation", "Adventure", "Comedy"), $film->getGenres(), 'Genres');
        $rating = $film->getRating($site->_getSourceName());
        $this->assertEquals(7.4, $rating->getCriticScore(), 'Critic score');
        $this->assertEquals(7.7, $rating->getUserScore(), 'User score');

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers \RatingSync\Imdb::getFilmDetailFromWebsite
     * @depends testGetFilmDetailFromWebsite
     */
    public function testGetFilmDetailFromWebsiteOverwriteTrueOverOriginalData()
    {
        $site = new Imdb(TEST_IMDB_USERNAME);

        $film = new Film($site->http);

        // Setup original data
        $film->setTitle("Original_Title");
        $film->setYear(1900);
        $film->setContentType("FeatureFilm");
        $film->setImage("Original_Image");
        $film->addGenre("Original_Genre1");
        $film->addGenre("Original_Genre2");
        $film->addDirector("Original_Director1");
        $film->addDirector("Original_Director2");

        // Setup original data (IMDb)
        $film->setFilmName("Original_FilmName_Imdb", Constants::SOURCE_IMDB);
        $film->setImage("Original_Image_Imdb", Constants::SOURCE_IMDB);
        $film->setUrlName("Original_UrlName_Imdb", Constants::SOURCE_IMDB);
        $ratingImdbOrig = new Rating(Constants::SOURCE_IMDB);
        $ratingImdbOrig->setYourScore(2);
        $ratingImdbOrig->setYourRatingDate(new \DateTime('2000-01-02'));
        $ratingImdbOrig->setSuggestedScore(3);
        $ratingImdbOrig->setCriticScore(4);
        $ratingImdbOrig->setUserScore(5);
        $film->setRating($ratingImdbOrig, Constants::SOURCE_IMDB);

        // Setup original data (Jinni)
        $film->setFilmName("Original_FilmName_Jinni", Constants::SOURCE_JINNI);
        $film->setImage("Original_Image_Jinni", Constants::SOURCE_JINNI);
        $film->setUrlName("Original_UrlName_Jinni", Constants::SOURCE_JINNI);
        $ratingJinniOrig = new Rating(Constants::SOURCE_JINNI);
        $ratingJinniOrig->setYourScore(1);
        $ratingJinniOrig->setYourRatingDate(new \DateTime('2000-01-01'));
        $ratingJinniOrig->setSuggestedScore(2);
        $ratingJinniOrig->setCriticScore(3);
        $ratingJinniOrig->setUserScore(4);
        $film->setRating($ratingJinniOrig, Constants::SOURCE_JINNI);

        // Get detail overwriting
        $film->setFilmName("tt2294629", Constants::SOURCE_IMDB);
        $site->getFilmDetailFromWebsite($film, true);

        // Verify - new data
        $this->assertEquals("Frozen", $film->getTitle(), 'Title');
        $this->assertEquals(2013, $film->getYear(), 'Year');
        $this->assertEquals("FeatureFilm", $film->getContentType(), 'Content Type');
        $this->assertEquals(1, preg_match('@(http://ia.media-imdb.com/images/M/MV5BMTQ1MjQwMTE5OF5BMl5BanBnXkFtZTgwNjk3MTcyMDE)@', $film->getImage(), $matches), 'Image link');
        $this->assertEquals(array("Animation", "Adventure", "Comedy"), $film->getGenres(), 'Genres');
        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), 'Director(s)');

        // Verify - new data (IMDb)
        $this->assertEquals("tt2294629", $film->getFilmName(Constants::SOURCE_IMDB), 'Film ID');
        $rating = $film->getRating(Constants::SOURCE_IMDB);
        $this->assertEquals(7.4, $rating->getCriticScore(), 'Critic score');
        $this->assertEquals(7.7, $rating->getUserScore(), 'User score');
        // The film detail page does not have these fields.  Don't overwrite them.
        $this->assertEquals("Original_UrlName_Imdb", $film->getUrlName(Constants::SOURCE_IMDB), 'URL Name');
        $this->assertEquals(2, $rating->getYourScore(), 'Your Score');
        $this->assertEquals("1/2/2000", $rating->getYourRatingDate()->format("n/j/Y"), 'Rating date');
        $this->assertEquals(3, $rating->getSuggestedScore(), 'Suggested score');

        // Verify - new data (Jinni, unchanged)
        $this->assertEquals("Original_FilmName_Jinni", $film->getFilmName(Constants::SOURCE_JINNI), 'Film ID (Jinni)');
        $this->assertEquals("Original_UrlName_Jinni", $film->getUrlName(Constants::SOURCE_JINNI), 'URL Name (Jinni)');
        $rating = $film->getRating(Constants::SOURCE_JINNI);
        $this->assertEquals(1, $rating->getYourScore(), 'Your Score (Jinni)');
        $this->assertEquals("1/1/2000", $rating->getYourRatingDate()->format("n/j/Y"), 'Rating date (Jinni)');
        $this->assertEquals(2, $rating->getSuggestedScore(), 'Suggested score (Jinni)');
        $this->assertEquals(3, $rating->getCriticScore(), 'Critic score (Jinni)');
        $this->assertEquals(4, $rating->getUserScore(), 'User score (Jinni)');

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers \RatingSync\Imdb::getFilmDetailFromWebsite
     * @depends testGetFilmDetailFromWebsiteOverwriteTrueOverEmpty
     */
    public function testGetFilmDetailFromWebsiteOverwriteFalseOverOriginalData()
    {
        $site = new Imdb(TEST_IMDB_USERNAME);
        $film = new Film($site->http);

        // Setup original data
        $film->setTitle("Original_Title");
        $film->setYear(1900);
        $film->setImage("Original_Image");
        $film->setContentType("FeatureFilm");
        $film->addGenre("Original_Genre1");
        $film->addGenre("Original_Genre2");
        $film->addDirector("Original_Director1");
        $film->addDirector("Original_Director2");

        // Setup original data (IMDb)
        $film->setFilmName("Original_FilmName_Imdb", Constants::SOURCE_IMDB);
        $film->setImage("Original_Image_Imdb", Constants::SOURCE_IMDB);
        $film->setUrlName("Original_UrlName_Imdb", Constants::SOURCE_IMDB);
        $ratingImdbOrig = new Rating(Constants::SOURCE_IMDB);
        $ratingImdbOrig->setYourScore(2);
        $ratingImdbOrig->setYourRatingDate(new \DateTime('2000-01-02'));
        $ratingImdbOrig->setSuggestedScore(3);
        $ratingImdbOrig->setCriticScore(4);
        $ratingImdbOrig->setUserScore(5);
        $film->setRating($ratingImdbOrig, Constants::SOURCE_IMDB);

        // Setup original data (Jinni)
        $film->setImage("Original_Image_Jinni", Constants::SOURCE_JINNI);
        $film->setFilmName("Original_FilmName_Jinni", Constants::SOURCE_JINNI);
        $film->setUrlName("Original_UrlName_Jinni", Constants::SOURCE_JINNI);
        $ratingJinniOrig = new Rating(Constants::SOURCE_JINNI);
        $ratingJinniOrig->setYourScore(1);
        $ratingJinniOrig->setYourRatingDate(new \DateTime('2000-01-01'));
        $ratingJinniOrig->setSuggestedScore(2);
        $ratingJinniOrig->setCriticScore(3);
        $ratingJinniOrig->setUserScore(4);
        $film->setRating($ratingJinniOrig, Constants::SOURCE_JINNI);

        // Get detail not overwriting
        $film->setFilmName("tt2294629", Constants::SOURCE_IMDB);
        $site->getFilmDetailFromWebsite($film, false);

        // Verify - Same original data
        $this->assertEquals("Original_Title", $film->getTitle(), 'Title');
        $this->assertEquals(1900, $film->getYear(), 'Year');
        $this->assertEquals("Original_Image", $film->getImage(), 'Image link');
        $this->assertEquals("FeatureFilm", $film->getContentType(), 'Content Type');
        $this->assertEquals(array("Original_Director1", "Original_Director2"), $film->getDirectors(), 'Director(s)');
        $this->assertEquals(array("Original_Genre1", "Original_Genre2"), $film->getGenres(), 'Genres');

        // Verify - Same original data (IMDb)
        $this->assertEquals("tt2294629", $film->getFilmName(Constants::SOURCE_IMDB), 'Film ID');
        $this->assertEquals("Original_UrlName_Imdb", $film->getUrlName(Constants::SOURCE_IMDB), 'URL Name');
        $rating = $film->getRating(Constants::SOURCE_IMDB);
        $this->assertEquals(2, $rating->getYourScore(), 'Your Score');
        $this->assertEquals("1/2/2000", $rating->getYourRatingDate()->format("n/j/Y"), 'Your Rating Date');
        $this->assertEquals(3, $rating->getSuggestedScore(), 'Suggested score');
        $this->assertEquals(4, $rating->getCriticScore(), 'Critic score');
        $this->assertEquals(5, $rating->getUserScore(), 'User score');

        // Verify - Same original data (Jinni)
        $this->assertEquals("Original_FilmName_Jinni", $film->getFilmName(Constants::SOURCE_JINNI), 'Film ID (Jinni)');
        $this->assertEquals("Original_UrlName_Jinni", $film->getUrlName(Constants::SOURCE_JINNI), 'URL Name (Jinni)');
        $rating = $film->getRating(Constants::SOURCE_JINNI);
        $this->assertEquals(1, $rating->getYourScore(), 'Your Score (Jinni)');
        $this->assertEquals("1/1/2000", $rating->getYourRatingDate()->format("n/j/Y"), 'Your Rating Date (Jinni)');
        $this->assertEquals(2, $rating->getSuggestedScore(), 'Suggested score (Jinni)');
        $this->assertEquals(3, $rating->getCriticScore(), 'Critic score (Jinni)');
        $this->assertEquals(4, $rating->getUserScore(), 'User score (Jinni)');

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers \RatingSync\Imdb::getFilmDetailFromWebsite
     * @depends testGetFilmDetailFromWebsite
     */
    public function testGetFilmDetailFromWebsiteOverwriteFalseOverEmpty()
    {
        $site = new ImdbExt(TEST_IMDB_USERNAME);

        $film = new Film($site->http);
        $film->setFilmName("tt2294629", $site->_getSourceName());
        $site->getFilmDetailFromWebsite($film, false);

        // Same results as testGetFilmDetailFromWebsite or testGetFilmDetailFromWebsiteOverwriteTrueOverEmpty
        $this->assertEquals("Frozen", $film->getTitle(), 'Title');
        $this->assertEquals(2013, $film->getYear(), 'Year');
        $this->assertEquals("FeatureFilm", $film->getContentType(), 'Content Type');
        $this->assertEquals(1, preg_match('@(http://ia.media-imdb.com/images/M/MV5BMTQ1MjQwMTE5OF5BMl5BanBnXkFtZTgwNjk3MTcyMDE)@', $film->getImage(), $matches), 'Image link');
        $this->assertEquals(1, preg_match('@(http://ia.media-imdb.com/images/M/MV5BMTQ1MjQwMTE5OF5BMl5BanBnXkFtZTgwNjk3MTcyMDE)@', $film->getImage($site->_getSourceName()), $matches), 'Image link');
        $this->assertEquals("tt2294629", $film->getFilmName($site->_getSourceName()), 'Film ID');
        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), 'Director(s)');
        $this->assertEquals(array("Animation", "Adventure", "Comedy"), $film->getGenres(), 'Genres');
        $rating = $film->getRating($site->_getSourceName());
        $this->assertEquals(7.4, $rating->getCriticScore(), 'Critic score');
        $this->assertEquals(7.7, $rating->getUserScore(), 'User score');

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers \RatingSync\Imdb::getFilmDetailFromWebsite
     * @depends testGetFilmDetailFromWebsiteOverwriteTrueOverOriginalData
     */
    public function testGetFilmDetailFromWebsiteOverwriteDefault()
    {
        $site = new Imdb(TEST_IMDB_USERNAME);

        $film = new Film($site->http);

        // Setup original data
        $film->setTitle("Original_Title");
        $film->setYear(1900);
        $film->setContentType("FeatureFilm");
        $film->setImage("Original_Image");
        $film->addGenre("Original_Genre1");
        $film->addGenre("Original_Genre2");
        $film->addDirector("Original_Director1");
        $film->addDirector("Original_Director2");

        // Setup original data (IMDb)
        $film->setFilmName("Original_FilmName_Imdb", Constants::SOURCE_IMDB);
        $film->setImage("Original_Image_Imdb", Constants::SOURCE_IMDB);
        $film->setUrlName("Original_UrlName_Imdb", Constants::SOURCE_IMDB);
        $ratingImdbOrig = new Rating(Constants::SOURCE_IMDB);
        $ratingImdbOrig->setYourScore(2);
        $ratingImdbOrig->setYourRatingDate(new \DateTime('2000-01-02'));
        $ratingImdbOrig->setSuggestedScore(3);
        $ratingImdbOrig->setCriticScore(4);
        $ratingImdbOrig->setUserScore(5);
        $film->setRating($ratingImdbOrig, Constants::SOURCE_IMDB);

        // Setup original data (Jinni)
        $film->setFilmName("Original_FilmName_Jinni", Constants::SOURCE_JINNI);
        $film->setImage("Original_Image_Jinni", Constants::SOURCE_JINNI);
        $film->setUrlName("Original_UrlName_Jinni", Constants::SOURCE_JINNI);
        $ratingJinniOrig = new Rating(Constants::SOURCE_JINNI);
        $ratingJinniOrig->setYourScore(1);
        $ratingJinniOrig->setYourRatingDate(new \DateTime('2000-01-01'));
        $ratingJinniOrig->setSuggestedScore(2);
        $ratingJinniOrig->setCriticScore(3);
        $ratingJinniOrig->setUserScore(4);
        $film->setRating($ratingJinniOrig, Constants::SOURCE_JINNI);

        // Get detail overwriting
        $film->setFilmName("tt2294629", Constants::SOURCE_IMDB);
        $site->getFilmDetailFromWebsite($film);

        // Verify - Same original data
        $this->assertEquals("Frozen", $film->getTitle(), 'Title');
        $this->assertEquals(2013, $film->getYear(), 'Year');
        $this->assertEquals("FeatureFilm", $film->getContentType(), 'Content Type');
        $this->assertEquals(1, preg_match('@(http://ia.media-imdb.com/images/M/MV5BMTQ1MjQwMTE5OF5BMl5BanBnXkFtZTgwNjk3MTcyMDE)@', $film->getImage(), $matches), 'Image link');
        $this->assertEquals(array("Animation", "Adventure", "Comedy"), $film->getGenres(), 'Genres');
        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), 'Director(s)');

        // Verify - Same original data (IMDb)
        $this->assertEquals("tt2294629", $film->getFilmName(Constants::SOURCE_IMDB), 'Film ID');
        $rating = $film->getRating(Constants::SOURCE_IMDB);
        $this->assertEquals(7.4, $rating->getCriticScore(), 'Critic score');
        $this->assertEquals(7.7, $rating->getUserScore(), 'User score');
        // The film detail page does not have these fields.  Don't overwrite them.
        $this->assertEquals("Original_UrlName_Imdb", $film->getUrlName(Constants::SOURCE_IMDB), 'URL Name');
        $this->assertEquals(2, $rating->getYourScore(), 'Your Score');
        $this->assertEquals("1/2/2000", $rating->getYourRatingDate()->format("n/j/Y"), 'Rating date');
        $this->assertEquals(3, $rating->getSuggestedScore(), 'Suggested score');

        // Verify - Same original data (Jinni, unchanged)
        $this->assertEquals("Original_FilmName_Jinni", $film->getFilmName(Constants::SOURCE_JINNI), 'Film ID (Jinni)');
        $this->assertEquals("Original_UrlName_Jinni", $film->getUrlName(Constants::SOURCE_JINNI), 'URL Name (Jinni)');
        $rating = $film->getRating(Constants::SOURCE_JINNI);
        $this->assertEquals(1, $rating->getYourScore(), 'Your Score (Jinni)');
        $this->assertEquals("1/1/2000", $rating->getYourRatingDate()->format("n/j/Y"), 'Rating date (Jinni)');
        $this->assertEquals(2, $rating->getSuggestedScore(), 'Suggested score (Jinni)');
        $this->assertEquals(3, $rating->getCriticScore(), 'Critic score (Jinni)');
        $this->assertEquals(4, $rating->getUserScore(), 'User score (Jinni)');

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers \RatingSync\Imdb::getFilmDetailFromWebsite
     * @expectedException \InvalidArgumentException
     */
    public function testGetFilmDetailFromWebsiteOverwriteFalseOverEmptyFilm()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        $site = new Imdb(TEST_IMDB_USERNAME);
        $film = new Film($site->http);
        $site->getFilmDetailFromWebsite($film);
    }

    /**
     * @covers \RatingSync\Imdb::getFilmDetailFromWebsite
     * @depends testGetFilmDetailFromWebsite
     */
    public function testMultipleGenres()
    {
        $site = new ImdbExt(TEST_IMDB_USERNAME);

        $film = new Film($site->http);
        $film->setFilmName("tt2294629", $site->_getSourceName());
        $site->getFilmDetailFromWebsite($film);

        $this->assertEquals(array("Animation", "Adventure", "Comedy"), $film->getGenres(), 'Genres');

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers \RatingSync\Imdb::getFilmDetailFromWebsite
     * @depends testGetFilmDetailFromWebsite
     */
    public function testMultipleDirectors()
    {
        $site = new ImdbExt(TEST_IMDB_USERNAME);

        $film = new Film($site->http);
        $film->setFilmName("tt2294629", $site->_getSourceName());
        $site->getFilmDetailFromWebsite($film, true, 60);

        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), 'Director(s)');

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }
    
    /**
     * @covers \RatingSync\Imdb::exportRatings
     * @depends testObjectCanBeConstructed
     */
/*
    public function testExportRatingsXmlNoDetail()
    {
        $site = new Imdb(TEST_IMDB_USERNAME);

        $testFilename = "ratings_test.xml";
        $success = $site->exportRatings("XML", $testFilename, false);
        $this->assertTrue($success);

        $fullTestFilename = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . Constants::outputFilePath() . $testFilename;
        $fullVerifyFilename = "testfile/verify_ratings_nodetail_imdb.xml";
        $this->assertTrue(is_readable($fullTestFilename), 'Need to read downloaded file ' . $fullTestFilename);
        $this->assertTrue(is_readable($fullVerifyFilename), 'Need to read verify file ' . $fullVerifyFilename);

        $fp_test = fopen($fullTestFilename, "r");
        $fp_verify = fopen($fullVerifyFilename, "r");
        $testFileSize = filesize($fullTestFilename);
        $verifyFileSize = filesize($fullVerifyFilename);
        $this->assertEquals($testFileSize, $verifyFileSize, 'File sizes - test vs verify');
        $test = fread($fp_test, filesize($fullTestFilename));
        $verify = fread($fp_verify, filesize($fullVerifyFilename));
        $this->assertEquals($test, $verify, 'Match exported file vs verify file');
        fclose($fp_test);
        fclose($fp_verify);

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }
*/
    
    /**
     * @covers \RatingSync\Imdb::exportRatings
     * @depends testObjectCanBeConstructed
     */
/*
    public function testExportRatingsXmlDetail()
    {
        $site = new Imdb(TEST_IMDB_USERNAME);

        $testFilename = "ratings_test.xml";
        $success = $site->exportRatings("XML", $testFilename, true, 60);
        $this->assertTrue($success);

        $fullTestFilename = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . Constants::outputFilePath() . $testFilename;
        $fullVerifyFilename = "testfile/verify_ratings_detail_imdb.xml";
        $this->assertTrue(is_readable($fullTestFilename), 'Need to read downloaded file ' . $fullTestFilename);
        $this->assertTrue(is_readable($fullVerifyFilename), 'Need to read verify file ' . $fullVerifyFilename);

        $fp_test = fopen($fullTestFilename, "r");
        $fp_verify = fopen($fullVerifyFilename, "r");
        $testFileSize = filesize($fullTestFilename);
        $verifyFileSize = filesize($fullVerifyFilename);
        $this->assertEquals($verifyFileSize, $testFileSize, 'File sizes - test vs verify');
        $test = fread($fp_test, filesize($fullTestFilename));
        $verify = fread($fp_verify, filesize($fullVerifyFilename));
        $this->assertEquals($test, $verify, 'Match exported file vs verify file');
        fclose($fp_test);
        fclose($fp_verify);

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }
*/
    
    /**
     * @covers \RatingSync\Imdb::getRatingPageUrl
     * @depends testObjectCanBeConstructed
     * @expectedException \InvalidArgumentException
     */
    public function testGetRatingPageUrlWithArgsNull()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        $site = new ImdbExt(TEST_IMDB_USERNAME);
        $site->_getRatingPageUrl(null);
    }
    
    /**
     * @covers \RatingSync\Imdb::getRatingPageUrl
     * @depends testObjectCanBeConstructed
     * @expectedException \InvalidArgumentException
     */
    public function testGetRatingPageUrlWithArgsEmpty()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        $site = new ImdbExt(TEST_IMDB_USERNAME);
        $site->_getRatingPageUrl(array());
    }
    
    /**
     * @covers \RatingSync\Imdb::getRatingPageUrl
     * @depends testObjectCanBeConstructed
     * @expectedException \InvalidArgumentException
     */
    public function testGetRatingPageUrlWithPageIndexNull()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        $site = new ImdbExt(TEST_IMDB_USERNAME);
        $site->_getRatingPageUrl(array('pageIndex' => null));
    }
    
    /**
     * @covers \RatingSync\Imdb::getRatingPageUrl
     * @depends testObjectCanBeConstructed
     * @expectedException \InvalidArgumentException
     */
    public function testGetRatingPageUrlWithPageIndexEmpty()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        $site = new ImdbExt(TEST_IMDB_USERNAME);
        $site->_getRatingPageUrl(array('pageIndex' => ""));
    }
    
    /**
     * @covers \RatingSync\Imdb::getRatingPageUrl
     * @depends testObjectCanBeConstructed
     * @expectedException \InvalidArgumentException
     */
    public function testGetRatingPageUrlWithPageIndexNotInt()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        $site = new ImdbExt(TEST_IMDB_USERNAME);
        $site->_getRatingPageUrl(array('pageIndex' => "Not_An_Int"));
    }
    
    /**
     * @covers \RatingSync\Imdb::getRatingPageUrl
     * @depends testObjectCanBeConstructed
     */
    public function testGetRatingPageUrl()
    {
        $site = new ImdbExt(TEST_IMDB_USERNAME);
        
        $url = $site->_getRatingPageUrl(array('pageIndex' => 3));
        $this->assertEquals('/user/'.TEST_IMDB_USERNAME.'/ratings?start=201&view=detail&sort=title:asc', $url);

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }
    
    /**
     * @covers \RatingSync\Imdb::getNextRatingPageNumber
     * @depends testObjectCanBeConstructed
     */
    public function testGetNextRatingPageNumberWithNull()
    {
        $site = new ImdbExt(TEST_IMDB_USERNAME);
        
        $this->assertFalse($site->_getNextRatingPageNumber(null));

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }
    
    /**
     * @covers \RatingSync\Imdb::getNextRatingPageNumber
     * @depends testObjectCanBeConstructed
     */
    public function testGetNextRatingPageNumberWithEmpty()
    {
        $site = new ImdbExt(TEST_IMDB_USERNAME);
        
        $this->assertFalse($site->_getNextRatingPageNumber(""));

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }
    
    /**
     * @covers \RatingSync\Imdb::getNextRatingPageNumber
     * @depends testGetRatingPageUrl
     */
    public function testGetNextRatingPageNumberFirstPage()
    {
        $site = new ImdbExt(TEST_IMDB_USERNAME);
        
        $args = array('pageIndex' => 1);
        $page = $site->_getHttp()->getPage($site->_getRatingPageUrl($args));
        $this->assertEquals(2, $site->_getNextRatingPageNumber($page));

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }
    
    /**
     * @covers \RatingSync\Imdb::getNextRatingPageNumber
     * @depends testGetRatingPageUrl
     */
    public function testGetNextRatingPageNumberLastPage()
    {
        $site = new ImdbExt(TEST_IMDB_USERNAME);
        
        $args = array('pageIndex' => 2);
        $page = $site->_getHttp()->getPage($site->_getRatingPageUrl($args));
        $this->assertFalse($site->_getNextRatingPageNumber($page));

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }
    
    /**
     * @covers \RatingSync\Imdb::parseDetailPageForTitle
     * @covers \RatingSync\Imdb::parseDetailPageFilmYear
     * @covers \RatingSync\Imdb::parseDetailPageImage
     * @covers \RatingSync\Imdb::parseDetailPageContentType
     * @covers \RatingSync\Imdb::parseDetailPageFilmName
     * @covers \RatingSync\Imdb::parseDetailPageUrlName
     * @covers \RatingSync\Imdb::parseDetailPageRating
     * @covers \RatingSync\Imdb::parseDetailPageGenres
     * @covers \RatingSync\Imdb::parseDetailPageDirectors
     */
    public function testParseDetailPageEmptyFilmOverwriteTrue()
    {
        $site = new ImdbExt(TEST_IMDB_USERNAME);
        $film = new Film($site->_getHttp());

        // Get HTML of the film's detail page
        $findFilm = new Film($site->_getHttp());
        $findFilm->setFilmName("tt2294629", $site->_getSourceName());
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
        $this->assertEquals(1, preg_match('@(http://ia.media-imdb.com/images/M/MV5BMTQ1MjQwMTE5OF5BMl5BanBnXkFtZTgwNjk3MTcyMDE)@', $film->getImage(), $matches), 'Check matching Image (empty film overwrite=true)');
        
        $success = $site->_parseDetailPageForContentType($page, $film, true);
        $this->assertTrue($success, 'Parsing film object for Content Type');
        $this->assertEquals("FeatureFilm", $film->getContentType(), 'Content Type');
        
        $success = $site->_parseDetailPageForUrlName($page, $film, true);
        $this->assertFalse($success, 'Parsing film object for URL Name'); // Not available from an IMDb detail page
        
        $success = $site->_parseDetailPageForFilmName($page, $film, true);
        $this->assertTrue($success, 'Parsing film object for Film Id');
        $this->assertEquals("tt2294629", $film->getFilmName($site->_getSourceName()), 'Check matching Film Id (empty film overwrite=true)');
        
        $success = $site->_parseDetailPageForRating($page, $film, true);
        $rating = $film->getRating($site->_getSourceName());
        $this->assertNull($rating->getYourScore(), 'Check matching YourScore (empty film overwrite=true)');
        $this->assertNull($rating->getYourRatingDate(), 'Check matching Rating Date (empty film overwrite=true)');
        $this->assertNull($rating->getSuggestedScore(), 'Check matching Suggested Score (empty film overwrite=true)');
        $this->assertEquals(7.4, $rating->getCriticScore(), 'Check matching Critic Score (empty film overwrite=true)');
        $this->assertEquals(7.7, $rating->getUserScore(), 'Check matching User Score (empty film overwrite=true)');
        
        $success = $site->_parseDetailPageForGenres($page, $film, true);
        $this->assertTrue($success, 'Parsing film object for Genres');
        $this->assertEquals(array("Animation", "Adventure", "Comedy"), $film->getGenres(), 'Check matching Gneres (empty film overwrite=true)');
        
        $success = $site->_parseDetailPageForDirectors($page, $film, true);
        $this->assertTrue($success, 'Parsing film object for Directors');
        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), 'Check matching Directors (empty film overwrite=true)');

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }
    
    /**
     * @covers \RatingSync\Imdb::parseDetailPageForTitle
     * @covers \RatingSync\Imdb::parseDetailPageFilmYear
     * @covers \RatingSync\Imdb::parseDetailPageImage
     * @covers \RatingSync\Imdb::parseDetailPageContentType
     * @covers \RatingSync\Imdb::parseDetailPageFilmName
     * @covers \RatingSync\Imdb::parseDetailPageUrlName
     * @covers \RatingSync\Imdb::parseDetailPageRating
     * @covers \RatingSync\Imdb::parseDetailPageGenres
     * @covers \RatingSync\Imdb::parseDetailPageDirectors
     */
    public function testParseDetailPageEmptyFilmOverwriteFalse()
    {
        $site = new ImdbExt(TEST_IMDB_USERNAME);
        $film = new Film($site->_getHttp());

        // Get HTML of the film's detail page
        $findFilm = new Film($site->_getHttp());
        $findFilm->setFilmName("tt2294629", $site->_getSourceName());
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
        $this->assertEquals(1, preg_match('@(http://ia.media-imdb.com/images/M/MV5BMTQ1MjQwMTE5OF5BMl5BanBnXkFtZTgwNjk3MTcyMDE)@', $film->getImage(), $matches), 'Check matching Image (empty film overwrite=false)');
        
        $success = $site->_parseDetailPageForContentType($page, $film, false);
        $this->assertTrue($success, 'Parsing film object for Content Type');
        $this->assertEquals("FeatureFilm", $film->getContentType(), 'Content Type');
        
        $success = $site->_parseDetailPageForUrlName($page, $film, false);
        $this->assertFalse($success, 'Parsing film object for URL Name'); // Not available from an IMDb detail page
        
        $success = $site->_parseDetailPageForFilmName($page, $film, false);
        $this->assertTrue($success, 'Parsing film object for Film Id');
        $this->assertEquals("tt2294629", $film->getFilmName($site->_getSourceName()), 'Check matching Film Id (empty film overwrite=false)');
        
        $success = $site->_parseDetailPageForRating($page, $film, false);
        $rating = $film->getRating($site->_getSourceName());
        $this->assertNull($rating->getYourScore(), 'Check matching YourScore (empty film overwrite=true)');
        $this->assertNull($rating->getYourRatingDate(), 'Check matching Rating Date (empty film overwrite=true)');
        $this->assertNull($rating->getSuggestedScore(), 'Check matching Suggested Score (empty film overwrite=true)');
        $this->assertEquals(7.4, $rating->getCriticScore(), 'Check matching Critic Score (empty film overwrite=true)');
        $this->assertEquals(7.7, $rating->getUserScore(), 'Check matching User Score (empty film overwrite=true)');
        
        $success = $site->_parseDetailPageForGenres($page, $film, false);
        $this->assertTrue($success, 'Parsing film object for Genres');
        $this->assertEquals(array("Animation", "Adventure", "Comedy"), $film->getGenres(), 'Check matching Gneres (empty film overwrite=true)');
        
        $success = $site->_parseDetailPageForDirectors($page, $film, false);
        $this->assertTrue($success, 'Parsing film object for Directors');
        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), 'Check matching Directors (empty film overwrite=false)');

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }
    
    /**
     * @covers \RatingSync\Imdb::parseDetailPageForTitle
     * @covers \RatingSync\Imdb::parseDetailPageFilmYear
     * @covers \RatingSync\Imdb::parseDetailPageImage
     * @covers \RatingSync\Imdb::parseDetailPageContentType
     * @covers \RatingSync\Imdb::parseDetailPageFilmName
     * @covers \RatingSync\Imdb::parseDetailPageUrlName
     * @covers \RatingSync\Imdb::parseDetailPageRating
     * @covers \RatingSync\Imdb::parseDetailPageGenres
     * @covers \RatingSync\Imdb::parseDetailPageDirectors
     */
    public function testParseDetailPageFullFilmOverwriteTrue()
    {
        $site = new ImdbExt(TEST_IMDB_USERNAME);
        $film = new Film($site->_getHttp());

        // Setup original data
        $film->setTitle("Original_Title");
        $film->setYear(1900);
        $film->setContentType("FeatureFilm");
        $film->setImage("Original_Image");
        $film->addGenre("Original_Genre1");
        $film->addGenre("Original_Genre2");
        $film->addDirector("Original_Director1");
        $film->addDirector("Original_Director2");

        // Setup original data (IMDb)
        $film->setFilmName("Original_FilmName_Imdb", Constants::SOURCE_IMDB);
        $film->setImage("Original_Image_Imdb", Constants::SOURCE_IMDB);
        $film->setUrlName("Original_UrlName_Imdb", Constants::SOURCE_IMDB);
        $ratingImdbOrig = new Rating(Constants::SOURCE_IMDB);
        $ratingImdbOrig->setYourScore(2);
        $ratingImdbOrig->setYourRatingDate(new \DateTime('2000-01-02'));
        $ratingImdbOrig->setSuggestedScore(3);
        $ratingImdbOrig->setCriticScore(4);
        $ratingImdbOrig->setUserScore(5);
        $film->setRating($ratingImdbOrig, Constants::SOURCE_IMDB);

        // Setup original data (Jinni)
        $film->setFilmName("Original_FilmName_Jinni", Constants::SOURCE_JINNI);
        $film->setImage("Original_Image_Jinni", Constants::SOURCE_JINNI);
        $film->setUrlName("Original_UrlName_Jinni", Constants::SOURCE_JINNI);
        $ratingJinniOrig = new Rating(Constants::SOURCE_JINNI);
        $ratingJinniOrig->setYourScore(1);
        $ratingJinniOrig->setYourRatingDate(new \DateTime('2000-01-01'));
        $ratingJinniOrig->setSuggestedScore(2);
        $ratingJinniOrig->setCriticScore(3);
        $ratingJinniOrig->setUserScore(4);
        $film->setRating($ratingJinniOrig, Constants::SOURCE_JINNI);

        // Get HTML of the film's detail page
        $findFilm = new Film($site->_getHttp());
        $findFilm->setFilmName("tt2294629", $site->_getSourceName());
        $site->getFilmDetailFromWebsite($findFilm, true, 60);
        $page = $site->getFilmDetailPageFromCache($findFilm, 60);
        
        $success = $site->_parseDetailPageForTitle($page, $film, true);
        $this->assertTrue($success, 'Parsing film object for Title');
        $this->assertEquals("Frozen", $film->getTitle(), 'Check matching Title (full film overwrite=true)');

        $success = $site->_parseDetailPageForFilmYear($page, $film, true);
        $this->assertTrue($success, 'Parsing film object for Year');
        $this->assertEquals(2013, $film->getYear(), 'Check matching Year (full film overwrite=true)');

        $success = $site->_parseDetailPageForImage($page, $film, true);
        $this->assertTrue($success, 'Parsing film object for Image');
        $this->assertEquals(1, preg_match('@(http://ia.media-imdb.com/images/M/MV5BMTQ1MjQwMTE5OF5BMl5BanBnXkFtZTgwNjk3MTcyMDE)@', $film->getImage(), $matches), 'Check matching Image (full film overwrite=true)');
        
        $success = $site->_parseDetailPageForContentType($page, $film, true);
        $this->assertFalse($success, 'Parsing film object for Content Type');
        $this->assertEquals("FeatureFilm", $film->getContentType(), 'Content Type');
        
        $success = $site->_parseDetailPageForUrlName($page, $film, true);
        $this->assertFalse($success, 'Parsing film object for URL Name'); // Not available from an IMDb detail page
        $this->assertEquals("Original_UrlName_Imdb", $film->getUrlName($site->_getSourceName()), 'URL Name (full film overwrite=true)');
        
        $success = $site->_parseDetailPageForFilmName($page, $film, true);
        $this->assertTrue($success, 'Parsing film object for Film Id');
        $this->assertEquals("tt2294629", $film->getFilmName($site->_getSourceName()), 'Check matching Film Id (full film overwrite=true)');
        
        $success = $site->_parseDetailPageForRating($page, $film, true);
        $rating = $film->getRating($site->_getSourceName());
        $this->assertEquals(2, $rating->getYourScore(), 'Check matching YourScore (full film overwrite=true)');
        $this->assertEquals(new \DateTime('2000-01-02'), $rating->getYourRatingDate(), 'Check matching Rating Date (full film overwrite=true)');
        $this->assertEquals(3, $rating->getSuggestedScore(), 'Check matching Suggested Score (full film overwrite=true)');
        $this->assertEquals(7.4, $rating->getCriticScore(), 'Check matching Critic Score (full film overwrite=true)');
        $this->assertEquals(7.7, $rating->getUserScore(), 'Check matching User Score (full film overwrite=true)');
        
        $success = $site->_parseDetailPageForGenres($page, $film, true);
        $this->assertTrue($success, 'Parsing film object for Genres');
        $this->assertEquals(array("Animation", "Adventure", "Comedy"), $film->getGenres(), 'Check matching Gneres (full film overwrite=true)');
        
        $success = $site->_parseDetailPageForDirectors($page, $film, true);
        $this->assertTrue($success, 'Parsing film object for Directors');
        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), 'Check matching Directors (full film overwrite=true)');

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }
    
    /**
     * @covers \RatingSync\Imdb::parseDetailPageForTitle
     * @covers \RatingSync\Imdb::parseDetailPageFilmYear
     * @covers \RatingSync\Imdb::parseDetailPageImage
     * @covers \RatingSync\Imdb::parseDetailPageContentType
     * @covers \RatingSync\Imdb::parseDetailPageFilmName
     * @covers \RatingSync\Imdb::parseDetailPageUrlName
     * @covers \RatingSync\Imdb::parseDetailPageRating
     * @covers \RatingSync\Imdb::parseDetailPageGenres
     * @covers \RatingSync\Imdb::parseDetailPageDirectors
     */
    public function testParseDetailPageFullFilmOverwriteFalse()
    {
        $site = new ImdbExt(TEST_IMDB_USERNAME);
        $film = new Film($site->_getHttp());

        // Setup original data
        $film->setTitle("Original_Title");
        $film->setYear(1900);
        $film->setContentType(Film::CONTENT_TV);
        $film->setImage("Original_Image");
        $film->addGenre("Original_Genre1");
        $film->addGenre("Original_Genre2");
        $film->addDirector("Original_Director1");
        $film->addDirector("Original_Director2");

        // Setup original data (IMDb)
        $film->setFilmName("Original_FilmName_Imdb", Constants::SOURCE_IMDB);
        $film->setImage("Original_Image_Imdb", Constants::SOURCE_IMDB);
        $film->setUrlName("Original_UrlName_Imdb", Constants::SOURCE_IMDB);
        $ratingImdbOrig = new Rating(Constants::SOURCE_IMDB);
        $ratingImdbOrig->setYourScore(2);
        $ratingImdbOrig->setYourRatingDate(new \DateTime('2000-01-02'));
        $ratingImdbOrig->setSuggestedScore(3);
        $ratingImdbOrig->setCriticScore(4);
        $ratingImdbOrig->setUserScore(5);
        $film->setRating($ratingImdbOrig, Constants::SOURCE_IMDB);

        // Setup original data (Jinni)
        $film->setFilmName("Original_FilmName_Jinni", Constants::SOURCE_JINNI);
        $film->setImage("Original_Image_Jinni", Constants::SOURCE_JINNI);
        $film->setUrlName("Original_UrlName_Jinni", Constants::SOURCE_JINNI);
        $ratingJinniOrig = new Rating(Constants::SOURCE_JINNI);
        $ratingJinniOrig->setYourScore(1);
        $ratingJinniOrig->setYourRatingDate(new \DateTime('2000-01-01'));
        $ratingJinniOrig->setSuggestedScore(2);
        $ratingJinniOrig->setCriticScore(3);
        $ratingJinniOrig->setUserScore(4);
        $film->setRating($ratingJinniOrig, Constants::SOURCE_JINNI);

        // Get HTML of the film's detail page
        $findFilm = new Film($site->_getHttp());
        $findFilm->setFilmName("tt2294629", $site->_getSourceName());
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
        $this->assertEquals("Original_Image_Jinni", $film->getImage(Constants::SOURCE_JINNI), 'Check matching Image (full film overwrite=false)');
        $this->assertEquals("Original_Image_Imdb", $film->getImage($site->_getSourceName()), 'Check matching Image (full film overwrite=false)');
        
        $success = $site->_parseDetailPageForUrlName($page, $film, false);
        $this->assertFalse($success, 'Parsing film object for URL Name');
        $this->assertEquals("Original_UrlName_Jinni", $film->getUrlName(Constants::SOURCE_JINNI), 'Check matching URL Name (full film overwrite=false)');
        $this->assertEquals("Original_UrlName_Imdb", $film->getUrlName($site->_getSourceName()), 'Check matching URL Name (full film overwrite=false)');
        
        $success = $site->_parseDetailPageForFilmName($page, $film, false);
        $this->assertFalse($success, 'Parsing film object for Film Id');
        $this->assertEquals("Original_FilmName_Jinni", $film->getFilmName(Constants::SOURCE_JINNI), 'Check matching Film Id (full film overwrite=false)');
        $this->assertEquals("Original_FilmName_Imdb", $film->getFilmName($site->_getSourceName()), 'Check matching Film Id (full film overwrite=false)');
        
        $success = $site->_parseDetailPageForRating($page, $film, false);
        $rating = $film->getRating(Constants::SOURCE_JINNI);
        $this->assertEquals(1, $rating->getYourScore(), 'Check matching YourScore (full film overwrite=false)');
        $this->assertEquals(new \DateTime('2000-01-01'), $rating->getYourRatingDate(), 'Check matching Rating Date (full film overwrite=false)');
        $this->assertEquals(2, $rating->getSuggestedScore(), 'Check matching Suggested Score (full film overwrite=false)');
        $this->assertEquals(3, $rating->getCriticScore(), 'Check matching Critic Score (full film overwrite=false)');
        $this->assertEquals(4, $rating->getUserScore(), 'Check matching User Score (full film overwrite=false)');
        $rating = $film->getRating($site->_getSourceName());
        $this->assertEquals(2, $rating->getYourScore(), 'Check matching YourScore (full film overwrite=false)');
        $this->assertEquals(new \DateTime('2000-01-02'), $rating->getYourRatingDate(), 'Check matching Rating Date (full film overwrite=false)');
        $this->assertEquals(3, $rating->getSuggestedScore(), 'Check matching Suggested Score (full film overwrite=false)');
        $this->assertEquals(4, $rating->getCriticScore(), 'Check matching Critic Score (full film overwrite=false)');
        $this->assertEquals(5, $rating->getUserScore(), 'Check matching User Score (full film overwrite=false)');
        
        $success = $site->_parseDetailPageForGenres($page, $film, false);
        $this->assertFalse($success, 'Parsing film object for Genres');
        $this->assertEquals(array("Original_Genre1", "Original_Genre2"), $film->getGenres(), 'Check matching Gneres (full film overwrite=false)');
        
        $success = $site->_parseDetailPageForDirectors($page, $film, false);
        $this->assertFalse($success, 'Parsing film object for Directors');
        $this->assertEquals(array("Original_Director1", "Original_Director2"), $film->getDirectors(), 'Check matching Directors (full film overwrite=false)');

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }
    
    /**
     * @covers \RatingSync\Imdb::testParseFilmsFromFile
     * @depends testObjectCanBeConstructed
     */
    public function testParseFilmsFromFile()
    {
        $site = new Imdb(TEST_IMDB_USERNAME);
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
        $this->assertEquals("70785", $film->getFilmName(Constants::SOURCE_JINNI), "Frozen ".Constants::SOURCE_JINNI." Film ID");
        $this->assertEquals("frozen-2013", $film->getUrlName(Constants::SOURCE_JINNI), "Frozen ".Constants::SOURCE_JINNI." URL Name");
        $rating = $film->getRating(Constants::SOURCE_JINNI);
        $this->assertEquals(8, $rating->getYourScore(), "Frozen ".Constants::SOURCE_JINNI." your score");
        $this->assertEquals("5/4/15", $rating->getYourRatingDate()->format('n/j/y'), "Frozen ".Constants::SOURCE_JINNI." rating date");
        $this->assertNull($rating->getSuggestedScore(), "Frozen ".Constants::SOURCE_JINNI." suggested score");
        $this->assertNull($rating->getCriticScore(), "Frozen ".Constants::SOURCE_JINNI." critic score");
        $this->assertNull($rating->getUserScore(), "Frozen ".Constants::SOURCE_JINNI." user score");

        // Title1
        $film = $films[1];
        $this->assertEquals("Title1", $film->getTitle(), "Title1 title");
        $this->assertEquals(2001, $film->getYear(), "Title1 year");
        $this->assertEquals(Film::CONTENT_FILM, $film->getContentType(), "Title1 ContentType");
        $this->assertEquals("http://example.com/title1_image.jpeg", $film->getImage(), "Title1 image");
        $this->assertEquals(array("Director1.1"), $film->getDirectors(), "Title1 directors");
        $this->assertEquals(array("Genre1.1"), $film->getGenres(), "Title1 genres");
        $this->assertEquals("http://example.com/title1_rs_image.jpeg", $film->getImage(Constants::SOURCE_RATINGSYNC), "Title1 ".Constants::SOURCE_RATINGSYNC." image");
        $this->assertEquals("FilmName1_rs", $film->getFilmName(Constants::SOURCE_RATINGSYNC), "Title1 ".Constants::SOURCE_RATINGSYNC." Film ID");
        $this->assertEquals("UrlName1_rs", $film->getUrlName(Constants::SOURCE_RATINGSYNC), "Title1 ".Constants::SOURCE_RATINGSYNC." URL Name");
        $rating = $film->getRating(Constants::SOURCE_RATINGSYNC);
        $this->assertEquals(1, $rating->getYourScore(), "Title1 ".Constants::SOURCE_RATINGSYNC." your score");
        $this->assertEquals("1/1/15", $rating->getYourRatingDate()->format('n/j/y'), "Title1 ".Constants::SOURCE_RATINGSYNC." rating date");
        $this->assertEquals(2, $rating->getSuggestedScore(), "Title1 ".Constants::SOURCE_RATINGSYNC." suggested score");
        $this->assertEquals(3, $rating->getCriticScore(), "Title1 ".Constants::SOURCE_RATINGSYNC." critic score");
        $this->assertEquals(4, $rating->getUserScore(), "Title1 ".Constants::SOURCE_RATINGSYNC." user score");

        // Title2
        $film = $films[2];
        $this->assertEquals("Title2", $film->getTitle(), "Title2 title");
        $this->assertEquals(2002, $film->getYear(), "Title2 year");
        $this->assertEquals(Film::CONTENT_FILM, $film->getContentType(), "Title2 ContentType");
        $this->assertEquals("http://example.com/title2_image.jpeg", $film->getImage(), "Title2 image");
        $this->assertEquals(array("Director2.1", "Director2.2"), $film->getDirectors(), "Title2 directors");
        $this->assertEquals(array("Genre2.1", "Genre2.2"), $film->getGenres(), "Title2 genres");
        $this->assertEquals("http://example.com/title2_rs_image.jpeg", $film->getImage(Constants::SOURCE_RATINGSYNC), "Title2 ".Constants::SOURCE_RATINGSYNC." image");
        $this->assertEquals("FilmName2_rs", $film->getFilmName(Constants::SOURCE_RATINGSYNC), "Title2 ".Constants::SOURCE_RATINGSYNC." Film ID");
        $this->assertEquals("UrlName2_rs", $film->getUrlName(Constants::SOURCE_RATINGSYNC), "Title2 ".Constants::SOURCE_RATINGSYNC." URL Name");
        $rating = $film->getRating(Constants::SOURCE_RATINGSYNC);
        $this->assertEquals(2, $rating->getYourScore(), "Title2 ".Constants::SOURCE_RATINGSYNC." your score");
        $this->assertEquals("1/2/15", $rating->getYourRatingDate()->format('n/j/y'), "Title2 ".Constants::SOURCE_RATINGSYNC." rating date");
        $this->assertEquals(3, $rating->getSuggestedScore(), "Title2 ".Constants::SOURCE_RATINGSYNC." suggested score");
        $this->assertEquals(4, $rating->getCriticScore(), "Title2 ".Constants::SOURCE_RATINGSYNC." critic score");
        $this->assertEquals(5, $rating->getUserScore(), "Title2 ".Constants::SOURCE_RATINGSYNC." user score");

        // Title3
        $film = $films[3];
        $this->assertEquals("Title3", $film->getTitle(), "Title3 title");
        $this->assertEmpty($film->getYear(), "Title3 year");
        $this->assertEmpty($film->getContentType(), "Title3 ContentType");
        $this->assertEmpty($film->getImage(), "Title3 image");
        $this->assertEmpty($film->getDirectors(), "Title3 directors");
        $this->assertEmpty($film->getGenres(), "Title3 genres");
        $this->assertEmpty($film->getImage(Constants::SOURCE_RATINGSYNC), "Title3 ".Constants::SOURCE_RATINGSYNC." image");
        $this->assertEmpty($film->getFilmName(Constants::SOURCE_RATINGSYNC), "Title3 ".Constants::SOURCE_RATINGSYNC." Film ID");
        $this->assertEmpty($film->getUrlName(Constants::SOURCE_RATINGSYNC), "Title3 ".Constants::SOURCE_RATINGSYNC." URL Name");
        $rating = $film->getRating(Constants::SOURCE_RATINGSYNC);
        $this->assertEmpty($rating->getYourScore(), "Title3 ".Constants::SOURCE_RATINGSYNC." your score");
        $this->assertEmpty($rating->getYourRatingDate(), "Title3 ".Constants::SOURCE_RATINGSYNC." rating date");
        $this->assertEmpty($rating->getSuggestedScore(), "Title3 ".Constants::SOURCE_RATINGSYNC." suggested score");
        $this->assertEmpty($rating->getCriticScore(), "Title3 ".Constants::SOURCE_RATINGSYNC." critic score");
        $this->assertEmpty($rating->getUserScore(), "Title3 ".Constants::SOURCE_RATINGSYNC." user score");

        // Title4
        $film = $films[4];
        $this->assertEquals("Title4", $film->getTitle(), "Title3 title");
        $this->assertEmpty($film->getYear(), "Title4 year");
        $this->assertEmpty($film->getContentType(), "Title4 ContentType");
        $this->assertEmpty($film->getImage(), "Title4 image");
        $this->assertEmpty($film->getDirectors(), "Title4 directors");
        $this->assertEmpty($film->getGenres(), "Title4 genres");
        $this->assertEmpty($film->getImage(Constants::SOURCE_RATINGSYNC), "Title4 ".Constants::SOURCE_RATINGSYNC." image");
        $this->assertEmpty($film->getFilmName(Constants::SOURCE_RATINGSYNC), "Title4 ".Constants::SOURCE_RATINGSYNC." Film ID");
        $this->assertEmpty($film->getUrlName(Constants::SOURCE_RATINGSYNC), "Title4 ".Constants::SOURCE_RATINGSYNC." URL Name");
        $rating = $film->getRating(Constants::SOURCE_RATINGSYNC);
        $this->assertEmpty($rating->getYourScore(), "Title4 ".Constants::SOURCE_RATINGSYNC." your score");
        $this->assertEmpty($rating->getYourRatingDate(), "Title4 ".Constants::SOURCE_RATINGSYNC." rating date");
        $this->assertEmpty($rating->getSuggestedScore(), "Title4 ".Constants::SOURCE_RATINGSYNC." suggested score");
        $this->assertEmpty($rating->getCriticScore(), "Title4 ".Constants::SOURCE_RATINGSYNC." critic score");
        $this->assertEmpty($rating->getUserScore(), "Title4 ".Constants::SOURCE_RATINGSYNC." user score");

        // Title5
        $film = $films[5];
        $this->assertEquals("Title5", $film->getTitle(), "Title5 title");
        $this->assertEmpty($film->getYear(), "Title5 year");
        $this->assertEmpty($film->getContentType(), "Title5 ContentType");
        $this->assertEmpty($film->getImage(), "Title5 image");
        $this->assertEmpty($film->getDirectors(), "Title5 directors");
        $this->assertEmpty($film->getGenres(), "Title5 genres");
        $this->assertEmpty($film->getImage(Constants::SOURCE_RATINGSYNC), "Title5 ".Constants::SOURCE_RATINGSYNC." image");
        $this->assertEmpty($film->getFilmName(Constants::SOURCE_RATINGSYNC), "Title5 ".Constants::SOURCE_RATINGSYNC." Film ID");
        $this->assertEmpty($film->getUrlName(Constants::SOURCE_RATINGSYNC), "Title5 ".Constants::SOURCE_RATINGSYNC." URL Name");
        $rating = $film->getRating(Constants::SOURCE_RATINGSYNC);
        $this->assertEmpty($rating->getYourScore(), "Title5 ".Constants::SOURCE_RATINGSYNC." your score");
        $this->assertEmpty($rating->getYourRatingDate(), "Title5 ".Constants::SOURCE_RATINGSYNC." rating date");
        $this->assertEmpty($rating->getSuggestedScore(), "Title5 ".Constants::SOURCE_RATINGSYNC." suggested score");
        $this->assertEmpty($rating->getCriticScore(), "Title5 ".Constants::SOURCE_RATINGSYNC." critic score");
        $this->assertEmpty($rating->getUserScore(), "Title5 ".Constants::SOURCE_RATINGSYNC." user score");

        // Title6
        $film = $films[6];
        $this->assertEquals("Title6", $film->getTitle(), "Title6 title");
        $this->assertEquals(2006, $film->getYear(), "Title6 year");
        $this->assertEquals(Film::CONTENT_FILM, $film->getContentType(), "Title6 ContentType");
        $this->assertEquals("http://example.com/title6_image.jpeg", $film->getImage(), "Title6 image");
        $this->assertEquals(array("Director6.1"), $film->getDirectors(), "Title6 directors");
        $this->assertEquals(array("Genre6.1"), $film->getGenres(), "Title6 genres");
        $this->assertEquals("http://example.com/title6_rs_image.jpeg", $film->getImage(Constants::SOURCE_RATINGSYNC), "Title6 ".Constants::SOURCE_RATINGSYNC." image");
        $this->assertEquals("FilmName6_rs", $film->getFilmName(Constants::SOURCE_RATINGSYNC), "Title6 ".Constants::SOURCE_RATINGSYNC." Film ID");
        $this->assertEquals("UrlName6_rs", $film->getUrlName(Constants::SOURCE_RATINGSYNC), "Title6 ".Constants::SOURCE_RATINGSYNC." URL Name");
        $rating = $film->getRating(Constants::SOURCE_RATINGSYNC);
        $this->assertEquals(6, $rating->getYourScore(), "Title6 ".Constants::SOURCE_RATINGSYNC." your score");
        $this->assertEquals("1/6/15", $rating->getYourRatingDate()->format('n/j/y'), "Title6 ".Constants::SOURCE_RATINGSYNC." rating date");
        $this->assertEquals(7, $rating->getSuggestedScore(), "Title6 ".Constants::SOURCE_RATINGSYNC." suggested score");
        $this->assertEquals(8, $rating->getCriticScore(), "Title6 ".Constants::SOURCE_RATINGSYNC." critic score");
        $this->assertEquals(9, $rating->getUserScore(), "Title6 ".Constants::SOURCE_RATINGSYNC." user score");
        $this->assertEquals("http://example.com/title6_imdb_image.jpeg", $film->getImage(Constants::SOURCE_IMDB), "Title6 ".Constants::SOURCE_IMDB." image");
        $this->assertEquals("FilmName6_imdb", $film->getFilmName(Constants::SOURCE_IMDB), "Title6 ".Constants::SOURCE_IMDB." Film ID");
        $this->assertEquals("UrlName6_imdb", $film->getUrlName(Constants::SOURCE_IMDB), "Title6 ".Constants::SOURCE_IMDB." URL Name");
        $rating = $film->getRating(Constants::SOURCE_IMDB);
        $this->assertEquals(5, $rating->getYourScore(), "Title6 ".Constants::SOURCE_IMDB." your score");
        $this->assertEquals("1/5/15", $rating->getYourRatingDate()->format('n/j/y'), "Title6 ".Constants::SOURCE_IMDB." rating date");
        $this->assertEquals(6, $rating->getSuggestedScore(), "Title6 ".Constants::SOURCE_IMDB." suggested score");
        $this->assertEquals(7, $rating->getCriticScore(), "Title6 ".Constants::SOURCE_IMDB." critic score");
        $this->assertEquals(8, $rating->getUserScore(), "Title6 ".Constants::SOURCE_IMDB." user score");

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
        $this->assertEquals("http://ia.media-imdb.com/images/M/MV5BMTQ1MjQwMTE5OF5BMl5BanBnXkFtZTgwNjk3MTcyMDE@._V1._SY209_CR0,0,140,209_.jpg", $film->getImage(), "Frozen image");
        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), "Frozen directors");
        $this->assertEquals(array("Animation", "Adventure", "Comedy"), $film->getGenres(), "Frozen genres");
        $this->assertEquals("http://ia.media-imdb.com/images/M/MV5BMTQ1MjQwMTE5OF5BMl5BanBnXkFtZTgwNjk3MTcyMDE@._V1._SY209_CR0,0,140,209_.jpg", $film->getImage(Constants::SOURCE_IMDB), "Frozen ".Constants::SOURCE_IMDB." image");
        $this->assertEquals("tt2294629", $film->getFilmName(Constants::SOURCE_IMDB), "Frozen ".Constants::SOURCE_IMDB." Film ID");
        $this->assertNull($film->getUrlName(Constants::SOURCE_IMDB), "Frozen ".Constants::SOURCE_IMDB." URL Name");
        $rating = $film->getRating(Constants::SOURCE_IMDB);
        $this->assertEquals(2, $rating->getYourScore(), "Frozen ".Constants::SOURCE_IMDB." your score");
        $this->assertNull($rating->getYourRatingDate(), "Frozen ".Constants::SOURCE_IMDB." rating date");
        $this->assertNull($rating->getSuggestedScore(), "Frozen ".Constants::SOURCE_IMDB." suggested score");
        $this->assertEquals(7.4, $rating->getCriticScore(), "Frozen ".Constants::SOURCE_IMDB." critic score");
        $this->assertEquals(7.7, $rating->getUserScore(), "Frozen ".Constants::SOURCE_IMDB." user score");

        // Frozen from All Sources
        $film = $films[9];
        $this->assertEquals("Frozen", $film->getTitle(), "Frozen title");
        $this->assertEquals(2013, $film->getYear(), "Frozen year");
        $this->assertEquals(Film::CONTENT_FILM, $film->getContentType(), "Frozen ContentType");
        $this->assertEquals("http://example.com/frozen_rs_image.jpeg", $film->getImage(), "Frozen image");
        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), "Frozen directors");
        $this->assertEquals(array("Animation", "Adventure", "Comedy", "Fantasy", "Musical", "Family"), $film->getGenres(), "Frozen genres");
        $this->assertEquals("http://example.com/frozen_rs_image.jpeg", $film->getImage(Constants::SOURCE_RATINGSYNC), "Frozen ".Constants::SOURCE_RATINGSYNC." image");
        $this->assertEquals("Frozen_rs", $film->getFilmName(Constants::SOURCE_RATINGSYNC), "Frozen ".Constants::SOURCE_RATINGSYNC." Film ID");
        $this->assertEquals("UrlNameFrozen_rs", $film->getUrlName(Constants::SOURCE_RATINGSYNC), "Frozen ".Constants::SOURCE_RATINGSYNC." URL Name");
        $rating = $film->getRating(Constants::SOURCE_RATINGSYNC);
        $this->assertEquals(2, $rating->getYourScore(), "Frozen ".Constants::SOURCE_RATINGSYNC." your score");
        $this->assertEquals("1/2/15", $rating->getYourRatingDate()->format('n/j/y'), "Frozen ".Constants::SOURCE_RATINGSYNC." rating date");
        $this->assertEquals(3, $rating->getSuggestedScore(), "Frozen ".Constants::SOURCE_RATINGSYNC." suggested score");
        $this->assertEquals(4, $rating->getCriticScore(), "Frozen ".Constants::SOURCE_RATINGSYNC." critic score");
        $this->assertEquals(5, $rating->getUserScore(), "Frozen ".Constants::SOURCE_RATINGSYNC." user score");
        $this->assertEquals("http://media.jinni.com/movie/frozen-2013/frozen-2013-5.jpeg", $film->getImage(Constants::SOURCE_JINNI), "Frozen ".Constants::SOURCE_JINNI." image");
        $this->assertEquals("70785", $film->getFilmName(Constants::SOURCE_JINNI), "Frozen ".Constants::SOURCE_JINNI." Film ID");
        $this->assertEquals("frozen-2013", $film->getUrlName(Constants::SOURCE_JINNI), "Frozen ".Constants::SOURCE_JINNI." URL Name");
        $rating = $film->getRating(Constants::SOURCE_JINNI);
        $this->assertEquals(8, $rating->getYourScore(), "Frozen ".Constants::SOURCE_JINNI." your score");
        $this->assertEquals("5/4/15", $rating->getYourRatingDate()->format('n/j/y'), "Frozen ".Constants::SOURCE_JINNI." rating date");
        $this->assertNull($rating->getSuggestedScore(), "Frozen ".Constants::SOURCE_JINNI." suggested score");
        $this->assertNull($rating->getCriticScore(), "Frozen ".Constants::SOURCE_JINNI." critic score");
        $this->assertNull($rating->getUserScore(), "Frozen ".Constants::SOURCE_JINNI." user score");
        $this->assertEquals("http://ia.media-imdb.com/images/M/MV5BMTQ1MjQwMTE5OF5BMl5BanBnXkFtZTgwNjk3MTcyMDE@._V1._SY209_CR0,0,140,209_.jpg", $film->getImage(Constants::SOURCE_IMDB), "Frozen ".Constants::SOURCE_IMDB." image");
        $this->assertEquals("tt2294629", $film->getFilmName(Constants::SOURCE_IMDB), "Frozen ".Constants::SOURCE_IMDB." Film ID");
        $this->assertNull($film->getUrlName(Constants::SOURCE_IMDB), "Frozen ".Constants::SOURCE_IMDB." URL Name");
        $rating = $film->getRating(Constants::SOURCE_IMDB);
        $this->assertEquals(2, $rating->getYourScore(), "Frozen ".Constants::SOURCE_IMDB." your score");
        $this->assertNull($rating->getYourRatingDate(), "Frozen ".Constants::SOURCE_IMDB." rating date");
        $this->assertNull($rating->getSuggestedScore(), "Frozen ".Constants::SOURCE_IMDB." suggested score");
        $this->assertEquals(7.4, $rating->getCriticScore(), "Frozen ".Constants::SOURCE_IMDB." critic score");
        $this->assertEquals(7.7, $rating->getUserScore(), "Frozen ".Constants::SOURCE_IMDB." user score");

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }
    
    /**
     * @covers \RatingSync\Imdb::testFromExportFileToFilmObjectAndBackToXml
     * @depends testParseFilmsFromFile
     */
    public function testFromExportFileToFilmObjectAndBackToXml()
    {
        $site = new Imdb(TEST_IMDB_USERNAME);
        
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
        $fullTestFilename =  __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . Constants::outputFilePath() . $testFilename;
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
        $this->assertEquals($test, $verify, 'Match test file (written) vs verify file (original)');
        fclose($fp_test);
        fclose($fp_verify);

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }
}

?>
