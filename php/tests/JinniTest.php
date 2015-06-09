<?php
/**
 * Jinni PHPUnit
 */
namespace RatingSync;

require_once "../Jinni.php";

const TEST_JINNI_USERNAME = "testratingsync";

// Class to expose protected members and functions
class JinniExt extends \RatingSync\Jinni {
    function _getHttp() { return $this->http; }
    function _getSourceName() { return $this->sourceName; }

    function _getRatingPageUrl($args) { return $this->getRatingPageUrl($args); }
    function _getNextRatingPageNumber($page) { return $this->getNextRatingPageNumber($page); }
    function _parseDetailPageForTitle($page, $film, $overwrite) { return $this->parseDetailPageForTitle($page, $film, $overwrite); }
    function _parseDetailPageForFilmYear($page, $film, $overwrite) { return $this->parseDetailPageForFilmYear($page, $film, $overwrite); }
    function _parseDetailPageForImage($page, $film, $overwrite) { return $this->parseDetailPageForImage($page, $film, $overwrite); }
    function _parseDetailPageForContentType($page, $film, $overwrite) { return $this->parseDetailPageForContentType($page, $film, $overwrite); }
    function _parseDetailPageForFilmId($page, $film, $overwrite) { return $this->parseDetailPageForFilmId($page, $film, $overwrite); }
    function _parseDetailPageForUrlName($page, $film, $overwrite) { return $this->parseDetailPageForUrlName($page, $film, $overwrite); }
    function _parseDetailPageForRating($page, $film, $overwrite) { return $this->parseDetailPageForRating($page, $film, $overwrite); }
    function _parseDetailPageForGenres($page, $film, $overwrite) { return $this->parseDetailPageForGenres($page, $film, $overwrite); }
    function _parseDetailPageForDirectors($page, $film, $overwrite) { return $this->parseDetailPageForDirectors($page, $film, $overwrite); }
}

class JinniTest extends \PHPUnit_Framework_TestCase
{
    public $debug;
    public $lastTestTime;

    public function setUp()
    {
        $this->debug = true;
        $this->lastTestTime = new \DateTime();
    }

    /**
     * @covers            \RatingSync\Jinni::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testCannotBeConstructedFromNull()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        new Jinni(null);
    }

    /**
     * @covers            \RatingSync\Jinni::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testCannotBeConstructedFromEmptyUsername()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        new Jinni("");
    }

    /**
     * @covers \RatingSync\Jinni::__construct
     */
    public function testObjectCanBeConstructed()
    {
        $site = new Jinni(TEST_JINNI_USERNAME);

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers \RatingSync\Jinni::getRatings
     * @depends testObjectCanBeConstructed
     * @expectedException \RatingSync\HttpUnauthorizedRedirectException
     */
    public function testGetRatingsUsernameWithNoMatch()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        $site = new Jinni("---Username--No--Match---");
        $films = $site->getRatings();
    }

    /**
     * @covers \RatingSync\Jinni::cacheRatingsPage
     * @depends testObjectCanBeConstructed
     */
    public function testCacheRatingsPage()
    {
        $site = new JinniExt(TEST_JINNI_USERNAME);

        $page = "<html><body><h2>Rating page 2</h2></body></html>";
        $verifyFilename = "testfile" . DIRECTORY_SEPARATOR . "verify_cache_ratingspage.xml";
        $fp = fopen($verifyFilename, "w");
        fwrite($fp, $page);
        fclose($fp);

        $site->cacheRatingsPage($page, 2);
        $testFilename = Constants::cacheFilePath() . $site->_getSourceName() . "_" . TEST_JINNI_USERNAME . "_ratings_2.html";
        $this->assertFileExists($testFilename, 'Cache file exists');
        $this->assertFileEquals($verifyFilename, $testFilename, 'cache file vs verify file');
        
        unlink($verifyFilename);
        unlink($testFilename);

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers \RatingSync\Jinni::cacheFilmDetailPage
     * @depends testObjectCanBeConstructed
     */
    public function testCacheFilmDetailPage()
    {
        $site = new JinniExt(TEST_JINNI_USERNAME);
        $film = new Film($site->http);
        $film->setFilmId("999999", $site->_getSourceName());
        
        $page = "<html><body><h2>Film Detail</h2></body></html>";
        $verifyFilename = "testfile" . DIRECTORY_SEPARATOR . "verify_cache_filmdetailpage.xml";
        $fp = fopen($verifyFilename, "w");
        fwrite($fp, $page);
        fclose($fp);
        
        $site->cacheFilmDetailPage($page, $film);
        $testFilename = Constants::cacheFilePath() . $site->_getSourceName() . "_" . TEST_JINNI_USERNAME . "_film_" . $site->getFilmUniqueAttr($film) . ".html";
        $this->assertFileExists($testFilename, 'Cache file exists');
        $this->assertFileEquals($verifyFilename, $testFilename, 'cache file vs verify file');
        
        unlink($verifyFilename);
        unlink($testFilename);

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers \RatingSync\Jinni::getRatings
     * @depends testObjectCanBeConstructed
     */
    public function testGetRatings()
    {
        $site = new Jinni(TEST_JINNI_USERNAME);
        $films = $site->getRatings();

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers \RatingSync\Jinni::getRatings
     * @depends testGetRatings
     */
    public function testGetRatingsUsingCacheAlways()
    {
        // Want cached files ready for this test: Yes
        $site = new jinni(TEST_JINNI_USERNAME);

        // limitPages=null, beginPage=1, detail=false, refreshCache=-1 (always use cache)
        $films = $site->getRatings(null, 1, false, Constants::USE_CACHE_ALWAYS);

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers \RatingSync\Jinni::getRatings
     * @depends testGetRatings
     */
    public function testGetRatingsUsingCacheNever()
    {
        // Want cached files ready for this test: Yes
        $site = new jinni(TEST_JINNI_USERNAME);

        // limitPages=null, beginPage=1, detail=false, refreshCache=0 (refresh now)
        $films = $site->getRatings(null, 1, false, Constants::USE_CACHE_NEVER);

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers \RatingSync\Jinni::getRatings
     * @covers \RatingSync\Jinni::cacheRatingsPage
     * @depends testGetRatings
     */
    public function testCacheAllRatingsPagesWithRecentFiles()
    {
        $site = new JinniExt(TEST_JINNI_USERNAME);

        $pageNums = array('1', '2');
        foreach ($pageNums as $pageNum) {
            $page = '<html><body><h2>Rating page ' . $pageNum . '</h2></body></html>';
            $testFilename = Constants::cacheFilePath() . $site->_getSourceName() . "_" . TEST_JINNI_USERNAME . "_ratings_" . $pageNum . ".html";
            $fp = fopen($testFilename, "w");
            fwrite($fp, $page);
            fclose($fp);
        }
        $originalCacheTime = time();
        sleep(1);

        // limitPages=null, beginPage=1, detail=false, refreshCache=0 (refresh now)
        $films = $site->getRatings(null, 1, false, Constants::USE_CACHE_NEVER);
        
        foreach ($pageNums as $pageNum) {
            $testFilename = Constants::cacheFilePath() . $site->_getSourceName() . "_" . TEST_JINNI_USERNAME . "_ratings_" . $pageNum . ".html";
            $this->assertFileExists($testFilename, 'Cache file ' . $pageNum . ' exists');
            $this->assertGreaterThan($originalCacheTime, filemtime($testFilename), 'Modified time');
            unlink($testFilename);
        }

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers \RatingSync\Jinni::getRatings
     * @depends testGetRatings
     */
    public function testGetRatingsUsingCacheWithOldFiles()
    {
        // Want cached files ready for this test: Yes
        $site = new JinniExt(TEST_JINNI_USERNAME);

        $timeBeforeGetRatings = time();
        sleep(1);
        // limitPages=null, beginPage=1, detail=false, refreshCache=60
        $films = $site->getRatings(null, 1, false, 0.01);
        
        $filename = Constants::cacheFilePath() . $site->_getSourceName() . "_" . TEST_JINNI_USERNAME . "_ratings_1.html";
        $this->assertGreaterThan($timeBeforeGetRatings, filemtime($filename), "Cache shpuld be new");

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers \RatingSync\Jinni::getRatings
     * @depends testObjectCanBeConstructed
     * @depends testGetRatings
     */
    public function testGetRatingsCount()
    {
        $site = new Jinni(TEST_JINNI_USERNAME);
        $films = $site->getRatings();
        $this->assertCount(21, $films);

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers \RatingSync\Jinni::getRatings
     * @depends testObjectCanBeConstructed
     * @depends testGetRatings
     */
    public function testGetRatingsLimitPages()
    {
        $site = new Jinni(TEST_JINNI_USERNAME);
        $films = $site->getRatings(1);
        $this->assertCount(20, $films);

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers \RatingSync\Jinni::getRatings
     * @depends testObjectCanBeConstructed
     * @depends testGetRatings
     */
    public function testGetRatingsBeginPage()
    {
        $site = new Jinni(TEST_JINNI_USERNAME);
        $films = $site->getRatings(null, 2);
        $this->assertEquals("The Shawshank Redemption", $films[0]->getTitle());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }
    
    /**
     * @covers \RatingSync\Jinni::getRatings
     * @depends testObjectCanBeConstructed
     * @depends testGetRatingsLimitPages
     * @depends testGetRatingsBeginPage
     */
    public function testGetRatingsDetailsNoCache()
    {
        $site = new JinniExt(TEST_JINNI_USERNAME);
        $films = $site->getRatings(1, 1, true, Constants::USE_CACHE_NEVER);
        $this->assertCount(20, $films);
        $film = $films[0];
        $this->assertEquals("Frozen", $film->getTitle(), 'Title');
        $this->assertEquals(2013, $film->getYear(), 'Year');
        $this->assertEquals("FeatureFilm", $film->getContentType(), 'Content Type');
        $this->assertEquals("http://media.jinni.com/movie/frozen-2013/frozen-2013-5.jpeg", $film->getImage(), 'Image link');
        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), 'Director(s)');
        $this->assertEquals(array("Adventure", "Animation", "Fantasy", "Musical", "Family", "Comedy"), $film->getGenres(), 'Genres');
        $this->assertEquals("frozen-2013", $film->getUrlName(Constants::SOURCE_JINNI), 'URL Name');
        $rating = $film->getRating($site->_getSourceName());
        $this->assertFalse(is_null($rating));
        $this->assertEquals(8, $rating->getYourScore(), 'Your Score');
        $this->assertEquals("5/4/2015", $rating->getYourRatingDate()->format("n/j/Y"), 'Your Rating Date');

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers \RatingSync\Jinni::getRatings
     * @depends testObjectCanBeConstructed
     * @depends testGetRatingsLimitPages
     * @depends testGetRatingsBeginPage
     */
    public function testGetRatingsDetails()
    {
        $site = new JinniExt(TEST_JINNI_USERNAME);
        $films = $site->getRatings(1, 1, true);
        $film = $films[0];
        $this->assertEquals("Frozen", $film->getTitle(), 'Title');
        $this->assertEquals(2013, $film->getYear(), 'Year');
        $this->assertEquals("FeatureFilm", $film->getContentType(), 'Content Type');
        $this->assertEquals("http://media.jinni.com/movie/frozen-2013/frozen-2013-5.jpeg", $film->getImage(), 'Image link');
        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), 'Director(s)');
        $this->assertEquals(array("Adventure", "Animation", "Fantasy", "Musical", "Family", "Comedy"), $film->getGenres(), 'Genres');
        $this->assertEquals("frozen-2013", $film->getUrlName(Constants::SOURCE_JINNI), 'URL Name');
        $rating = $film->getRating($site->_getSourceName());
        $this->assertEquals(8, $rating->getYourScore(), 'Your Score');
        $this->assertEquals("5/4/2015", $rating->getYourRatingDate()->format("n/j/Y"), 'Your Rating Date');

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers \RatingSync\Jinni::getSearchSuggestions
     * @depends testObjectCanBeConstructed
     */
    public function testGetSearchSuggestions()
    {
        $site = new Jinni(TEST_JINNI_USERNAME);
        $films = $site->getSearchSuggestions("Shawshank");
        $titles = array();
        foreach ($films as $film) {
            $titles[] = $film->getTitle();
        }
        $this->assertTrue(in_array("The Shawshank Redemption", $titles));

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers \RatingSync\Jinni::getFilmDetailFromWebsite
     * @depends testObjectCanBeConstructed
     * @expectedException \InvalidArgumentException
     */
    public function testGetFilmDetailFromWebsiteFromNull()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        $site = new Jinni(TEST_JINNI_USERNAME);
        $site->getFilmDetailFromWebsite(null);
    }

    /**
     * @covers \RatingSync\Jinni::getFilmDetailFromWebsite
     * @depends testObjectCanBeConstructed
     * @expectedException \InvalidArgumentException
     */
    public function testGetFilmDetailFromWebsiteFromString()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        $site = new Jinni(TEST_JINNI_USERNAME);
        $site->getFilmDetailFromWebsite("String_Not_Film_Object");
    }

    /**
     * @covers \RatingSync\Jinni::getFilmDetailFromWebsite
     * @depends testObjectCanBeConstructed
     * @expectedException \InvalidArgumentException
     */
    public function testGetFilmDetailFromWebsiteWithoutUrlName()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        $site = new Jinni(TEST_JINNI_USERNAME);
        $film = new Film($site->http);
        $film->setContentType("FeatureFilm");
        $site->getFilmDetailFromWebsite($film);
    }

    /**
     * @covers \RatingSync\Jinni::getFilmDetailFromWebsite
     * @depends testObjectCanBeConstructed
     * @expectedException \InvalidArgumentException
     */
    public function testGetFilmDetailFromWebsiteWithoutContentType()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        $site = new Jinni(TEST_JINNI_USERNAME);
        $film = new Film($site->http);
        $film->setUrlName("frozen-2013", Constants::SOURCE_JINNI);
        $site->getFilmDetailFromWebsite($film);
    }

    /**
     * @covers \RatingSync\Jinni::getFilmDetailFromWebsite
     * @depends testObjectCanBeConstructed
     * @expectedException \Exception
     */
    public function testGetFilmDetailFromWebsiteNoMatch()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        $site = new Jinni(TEST_JINNI_USERNAME);
        $film = new Film($site->http);
        $film->setContentType("FeatureFilm");
        $film->setUrlName("NO_MATCH_URLNAME", Constants::SOURCE_JINNI);
        $site->getFilmDetailFromWebsite($film, true);
    }

    /**
     * @covers \RatingSync\Jinni::getFilmDetailFromWebsite
     * @depends testObjectCanBeConstructed
     */
    public function testGetFilmDetailFromWebsite()
    {
        $site = new Jinni(TEST_JINNI_USERNAME);

        $film = new Film($site->http);
        $film->setContentType("FeatureFilm");
        $film->setUrlName("frozen-2013", Constants::SOURCE_JINNI);
        $site->getFilmDetailFromWebsite($film, true);

        $this->assertEquals("Frozen", $film->getTitle(), 'Title');
        $this->assertEquals(2013, $film->getYear(), 'Year');
        $this->assertEquals("FeatureFilm", $film->getContentType(), 'Content Type');
        $this->assertEquals(1, preg_match('@(http://media[\d]*.jinni.com/(?:tv|movie|shorts|no-image)/frozen-2013/[^"]+)@', $film->getImage(), $matches), 'Image link');
        $this->assertEquals("frozen-2013", $film->getUrlName(Constants::SOURCE_JINNI), 'URL Name');
        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), 'Director(s)');
        $this->assertEquals(array("Adventure", "Animation", "Fantasy", "Musical", "Family", "Comedy"), $film->getGenres(), 'Genres');
        $rating = $film->getRating(Constants::SOURCE_JINNI);
        $this->assertEquals("70785", $film->getFilmId(Constants::SOURCE_JINNI), 'Film ID');
        $this->assertEquals(8, $rating->getYourScore(), 'Your Score');
        $this->assertNull($rating->getYourRatingDate(), 'Rating date not available from film detail page');
        $this->assertNull($rating->getSuggestedScore(), 'Suggested score not available is you are rated the film');
        $this->assertNull($rating->getCriticScore(), 'Critic score not available from Jinni');
        $this->assertNull($rating->getUserScore(), 'User score not available from Jinni');

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers \RatingSync\Jinni::getFilmDetailFromWebsite
     * @depends testGetFilmDetailFromWebsite
     */
    public function testGetFilmDetailFromWebsiteOverwriteTrueOverEmpty()
    {
        $site = new Jinni(TEST_JINNI_USERNAME);

        $film = new Film($site->http);
        $film->setContentType("FeatureFilm");
        $film->setUrlName("frozen-2013", Constants::SOURCE_JINNI);
        $site->getFilmDetailFromWebsite($film, true);

        // Same results as testGetFilmDetailFromWebsite
        $this->assertEquals("Frozen", $film->getTitle(), 'Title');
        $this->assertEquals(2013, $film->getYear(), 'Year');
        $this->assertEquals("FeatureFilm", $film->getContentType(), 'Content Type');
        $this->assertEquals(1, preg_match('@(http://media[\d]*.jinni.com/(?:tv|movie|shorts|no-image)/frozen-2013/[^"]+)@', $film->getImage(), $matches), 'Image link');
        $this->assertEquals("frozen-2013", $film->getUrlName(Constants::SOURCE_JINNI), 'URL Name');
        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), 'Director(s)');
        $this->assertEquals(array("Adventure", "Animation", "Fantasy", "Musical", "Family", "Comedy"), $film->getGenres(), 'Genres');
        $rating = $film->getRating(Constants::SOURCE_JINNI);
        $this->assertEquals("70785", $film->getFilmId(Constants::SOURCE_JINNI), 'Film ID');
        $this->assertEquals(8, $rating->getYourScore(), 'Your Score');
        $this->assertNull($rating->getYourRatingDate(), 'Rating date not available from film detail page');
        $this->assertNull($rating->getSuggestedScore(), 'Suggested score not available is you are rated the film');
        $this->assertNull($rating->getCriticScore(), 'Critic score not available from Jinni');
        $this->assertNull($rating->getUserScore(), 'User score not available from Jinni');

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers \RatingSync\Jinni::getFilmDetailFromWebsite
     * @depends testGetFilmDetailFromWebsite
     */
    public function testGetFilmDetailFromWebsiteOverwriteTrueOverOriginalData()
    {
        $site = new Jinni(TEST_JINNI_USERNAME);

        $film = new Film($site->http);

        // Setup original data
        $film->setTitle("Original_Title");
        $film->setYear(1900);
        $film->setImage("Original_Image");
        $film->setImage("Original_JinniImage", Constants::SOURCE_JINNI);
        $film->setImage("Original_IMDbImage", Constants::SOURCE_IMDB);
        $film->setFilmId("Original_JinniFilmId", Constants::SOURCE_JINNI);
        $film->setFilmId("Original_IMDbFilmId", Constants::SOURCE_IMDB);
        $film->setUrlName("Original_JinniUrlName", Constants::SOURCE_JINNI);
        $film->setUrlName("Original_IMDbUrlName", Constants::SOURCE_IMDB);
        $film->addGenre("Original_Genre1");
        $film->addGenre("Original_Genre2");
        $film->addDirector("Original_Director1");
        $film->addDirector("Original_Director2");
        $ratingJinniOrig = new Rating(Constants::SOURCE_JINNI);
        $ratingJinniOrig->setYourScore(1);
        $ratingJinniOrig->setYourRatingDate(new \DateTime('2000-01-01'));
        $ratingJinniOrig->setSuggestedScore(2);
        $ratingJinniOrig->setCriticScore(3);
        $ratingJinniOrig->setUserScore(4);
        $film->setRating($ratingJinniOrig, Constants::SOURCE_JINNI);
        $ratingImdbOrig = new Rating(Constants::SOURCE_IMDB);
        $ratingImdbOrig->setYourScore(2);
        $ratingImdbOrig->setYourRatingDate(new \DateTime('2000-01-02'));
        $ratingImdbOrig->setSuggestedScore(3);
        $ratingImdbOrig->setCriticScore(4);
        $ratingImdbOrig->setUserScore(5);
        $film->setRating($ratingImdbOrig, Constants::SOURCE_IMDB);

        // Get detail overwriting
        $film->setContentType("FeatureFilm");
        $film->setUrlName("frozen-2013", Constants::SOURCE_JINNI);
        $site->getFilmDetailFromWebsite($film, true);

        // Test the new data
        $this->assertEquals("Frozen", $film->getTitle(), 'Title');
        $this->assertEquals(2013, $film->getYear(), 'Year');
        $this->assertEquals("FeatureFilm", $film->getContentType(), 'Content Type');
        $this->assertEquals(1, preg_match('@(http://media[\d]*.jinni.com/(?:tv|movie|shorts|no-image)/frozen-2013/[^"]+)@', $film->getImage(), $matches), 'Image link');
        $this->assertEquals("frozen-2013", $film->getUrlName(Constants::SOURCE_JINNI), 'URL Name');
        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), 'Director(s)');
        $this->assertEquals(array("Adventure", "Animation", "Fantasy", "Musical", "Family", "Comedy"), $film->getGenres(), 'Genres');
        $rating = $film->getRating(Constants::SOURCE_JINNI);
        $this->assertEquals("70785", $film->getFilmId(Constants::SOURCE_JINNI), 'Film ID');
        $this->assertEquals(8, $rating->getYourScore(), 'Your Score');

        // The film detail page does not have these fields.  Don't overwrite them.
        $this->assertEquals("1/1/2000", $rating->getYourRatingDate()->format("n/j/Y"), 'Rating date');
        $this->assertEquals(2, $rating->getSuggestedScore(), 'Suggested score not available is you are rated the film');
        $this->assertEquals(3, $rating->getCriticScore(), 'Critic score not available from Jinni');
        $this->assertEquals(4, $rating->getUserScore(), 'User score not available from Jinni');

        // IMDb Rating is unchanged
        $rating = $film->getRating(Constants::SOURCE_IMDB);
        $this->assertEquals("Original_IMDbFilmId", $film->getFilmId(Constants::SOURCE_IMDB), 'Film ID');
        $this->assertEquals(2, $rating->getYourScore(), 'Your Score');
        $this->assertEquals("1/2/2000", $rating->getYourRatingDate()->format("n/j/Y"), 'Rating date');
        $this->assertEquals(3, $rating->getSuggestedScore(), 'Suggested score not available is you are rated the film');
        $this->assertEquals(4, $rating->getCriticScore(), 'Critic score not available from Jinni');
        $this->assertEquals(5, $rating->getUserScore(), 'User score not available from Jinni');

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers \RatingSync\Jinni::getFilmDetailFromWebsite
     * @depends testGetFilmDetailFromWebsiteOverwriteTrueOverEmpty
     */
    public function testGetFilmDetailFromWebsiteOverwriteFalseOverOriginalData()
    {
        $site = new Jinni(TEST_JINNI_USERNAME);

        $film = new Film($site->http);

        // Setup original data
        $film->setTitle("Original_Title");
        $film->setYear(1900);
        $film->setImage("Original_Image");
        $film->setUrlName("Original_IMDbUrlName", Constants::SOURCE_IMDB);
        $film->addGenre("Original_Genre1");
        $film->addGenre("Original_Genre2");
        $film->addDirector("Original_Director1");
        $film->addDirector("Original_Director2");
        $film->setImage("Original_Image", Constants::SOURCE_JINNI);
        $film->setFilmId("Original_JinniFilmId", Constants::SOURCE_JINNI);
        $ratingJinniOrig = new Rating(Constants::SOURCE_JINNI);
        $ratingJinniOrig->setYourScore(1);
        $ratingJinniOrig->setYourRatingDate(new \DateTime('2000-01-01'));
        $ratingJinniOrig->setSuggestedScore(2);
        $ratingJinniOrig->setCriticScore(3);
        $ratingJinniOrig->setUserScore(4);
        $film->setRating($ratingJinniOrig, Constants::SOURCE_JINNI);
        $film->setImage("Original_Image", Constants::SOURCE_IMDB);
        $film->setFilmId("Original_ImdbFilmId", Constants::SOURCE_IMDB);
        $ratingImdbOrig = new Rating(Constants::SOURCE_IMDB);
        $ratingImdbOrig->setYourScore(2);
        $ratingImdbOrig->setYourRatingDate(new \DateTime('2000-01-02'));
        $ratingImdbOrig->setSuggestedScore(3);
        $ratingImdbOrig->setCriticScore(4);
        $ratingImdbOrig->setUserScore(5);
        $film->setRating($ratingImdbOrig, Constants::SOURCE_IMDB);

        // Get detail not overwriting
        $film->setContentType("FeatureFilm");
        $film->setUrlName("frozen-2013", Constants::SOURCE_JINNI);
        $site->getFilmDetailFromWebsite($film, false);

        // Same original data
        $this->assertEquals("FeatureFilm", $film->getContentType(), 'Content Type');
        $this->assertEquals("frozen-2013", $film->getUrlName(Constants::SOURCE_JINNI), 'Jinni URL Name');
        $this->assertEquals("Original_Title", $film->getTitle(), 'Title');
        $this->assertEquals(1900, $film->getYear(), 'Year');
        $this->assertEquals("Original_Image", $film->getImage(), 'Image link');
        $this->assertEquals("Original_IMDbUrlName", $film->getUrlName(Constants::SOURCE_IMDB), 'Jinni URL Name');
        $this->assertEquals(array("Original_Director1", "Original_Director2"), $film->getDirectors(), 'Director(s)');
        $this->assertEquals(array("Original_Genre1", "Original_Genre2"), $film->getGenres(), 'Genres');
        $rating = $film->getRating(Constants::SOURCE_JINNI);
        $this->assertEquals("Original_JinniFilmId", $film->getFilmId(Constants::SOURCE_JINNI), 'Film ID');
        $this->assertEquals(1, $rating->getYourScore(), 'Your Score');
        $this->assertEquals("1/1/2000", $rating->getYourRatingDate()->format("n/j/Y"), 'Your Rating Date');
        $this->assertEquals(2, $rating->getSuggestedScore(), 'Suggested score');
        $this->assertEquals(3, $rating->getCriticScore(), 'Critic score');
        $this->assertEquals(4, $rating->getUserScore(), 'User score');
        $rating = $film->getRating(Constants::SOURCE_IMDB);
        $this->assertEquals("Original_ImdbFilmId", $film->getFilmId(Constants::SOURCE_IMDB), 'Film ID');
        $this->assertEquals(2, $rating->getYourScore(), 'Your Score');
        $this->assertEquals("1/2/2000", $rating->getYourRatingDate()->format("n/j/Y"), 'Your Rating Date');
        $this->assertEquals(3, $rating->getSuggestedScore(), 'Suggested score');
        $this->assertEquals(4, $rating->getCriticScore(), 'Critic score');
        $this->assertEquals(5, $rating->getUserScore(), 'User score');

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers \RatingSync\Jinni::getFilmDetailFromWebsite
     * @depends testGetFilmDetailFromWebsite
     */
    public function testGetFilmDetailFromWebsiteOverwriteFalseOverEmpty()
    {
        $site = new Jinni(TEST_JINNI_USERNAME);

        $film = new Film($site->http);
        $film->setContentType("FeatureFilm");
        $film->setUrlName("frozen-2013", Constants::SOURCE_JINNI);
        $site->getFilmDetailFromWebsite($film, false);

        // Same results as testGetFilmDetailFromWebsite or testGetFilmDetailFromWebsiteOverwriteTrueOverEmpty
        $this->assertEquals("Frozen", $film->getTitle(), 'Title');
        $this->assertEquals(2013, $film->getYear(), 'Year');
        $this->assertEquals("FeatureFilm", $film->getContentType(), 'Content Type');
        $this->assertEquals(1, preg_match('@(http://media[\d]*.jinni.com/(?:tv|movie|shorts|no-image)/frozen-2013/[^"]+)@', $film->getImage(), $matches), 'Image link');
        $this->assertEquals("frozen-2013", $film->getUrlName(Constants::SOURCE_JINNI), 'URL Name');
        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), 'Director(s)');
        $this->assertEquals(array("Adventure", "Animation", "Fantasy", "Musical", "Family", "Comedy"), $film->getGenres(), 'Genres');
        $rating = $film->getRating(Constants::SOURCE_JINNI);
        $this->assertEquals("70785", $film->getFilmId(Constants::SOURCE_JINNI), 'Film ID');
        $this->assertEquals(8, $rating->getYourScore(), 'Your Score');
        $this->assertNull($rating->getYourRatingDate(), 'Rating date not available from film detail page');
        $this->assertNull($rating->getSuggestedScore(), 'Suggested score not available is you are rated the film');
        $this->assertNull($rating->getCriticScore(), 'Critic score not available from Jinni');
        $this->assertNull($rating->getUserScore(), 'User score not available from Jinni');

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers \RatingSync\Jinni::getFilmDetailFromWebsite
     * @depends testGetFilmDetailFromWebsiteOverwriteTrueOverOriginalData
     */
    public function testGetFilmDetailFromWebsiteOverwriteDefault()
    {
        $site = new Jinni(TEST_JINNI_USERNAME);

        $film = new Film($site->http);

        // Setup original data
        $film->setTitle("Original_Title");
        $film->setYear(1900);
        $film->setImage("Original_Image");
        $film->setImage("Original_JinniImage", Constants::SOURCE_JINNI);
        $film->setImage("Original_IMDbImage", Constants::SOURCE_IMDB);
        $film->setFilmId("Original_JinniFilmId", Constants::SOURCE_JINNI);
        $film->setFilmId("Original_ImdbFilmId", Constants::SOURCE_IMDB);
        $film->setUrlName("Original_JinniUrlName", Constants::SOURCE_JINNI);
        $film->setUrlName("Original_IMDbUrlName", Constants::SOURCE_IMDB);
        $film->addGenre("Original_Genre1");
        $film->addGenre("Original_Genre2");
        $film->addDirector("Original_Director1");
        $film->addDirector("Original_Director2");
        $ratingJinniOrig = new Rating(Constants::SOURCE_JINNI);
        $ratingJinniOrig->setYourScore(1);
        $ratingJinniOrig->setYourRatingDate(new \DateTime('2000-01-01'));
        $ratingJinniOrig->setSuggestedScore(2);
        $ratingJinniOrig->setCriticScore(3);
        $ratingJinniOrig->setUserScore(4);
        $film->setRating($ratingJinniOrig, Constants::SOURCE_JINNI);
        $ratingImdbOrig = new Rating(Constants::SOURCE_IMDB);
        $ratingImdbOrig->setYourScore(2);
        $ratingImdbOrig->setYourRatingDate(new \DateTime('2000-01-02'));
        $ratingImdbOrig->setSuggestedScore(3);
        $ratingImdbOrig->setCriticScore(4);
        $ratingImdbOrig->setUserScore(5);
        $film->setRating($ratingImdbOrig, Constants::SOURCE_IMDB);

        // Get detail overwriting
        $film->setContentType("FeatureFilm");
        $film->setUrlName("frozen-2013", Constants::SOURCE_JINNI);
        $site->getFilmDetailFromWebsite($film);

        // Test the new data (overwrite default param is true)
        $this->assertEquals("Frozen", $film->getTitle(), 'Title');
        $this->assertEquals(2013, $film->getYear(), 'Year');
        $this->assertEquals("FeatureFilm", $film->getContentType(), 'Content Type');
        $this->assertEquals(1, preg_match('@(http://media[\d]*.jinni.com/(?:tv|movie|shorts|no-image)/frozen-2013/[^"]+)@', $film->getImage(), $matches), 'Image link');
        $this->assertEquals("frozen-2013", $film->getUrlName(Constants::SOURCE_JINNI), 'URL Name');
        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), 'Director(s)');
        $this->assertEquals(array("Adventure", "Animation", "Fantasy", "Musical", "Family", "Comedy"), $film->getGenres(), 'Genres');
        $rating = $film->getRating(Constants::SOURCE_JINNI);
        $this->assertEquals("70785", $film->getFilmId(Constants::SOURCE_JINNI), 'Film ID');
        $this->assertEquals(8, $rating->getYourScore(), 'Your Score');
        $this->assertEquals("1/1/2000", $rating->getYourRatingDate()->format("n/j/Y"), 'Rating date');
        $this->assertEquals(2, $rating->getSuggestedScore(), 'Suggested score not available is you are rated the film');
        $this->assertEquals(3, $rating->getCriticScore(), 'Critic score not available from Jinni');
        $this->assertEquals(4, $rating->getUserScore(), 'User score not available from Jinni');
        $rating = $film->getRating(Constants::SOURCE_IMDB);
        $this->assertEquals("Original_ImdbFilmId", $film->getFilmId(Constants::SOURCE_IMDB), 'Film ID');
        $this->assertEquals(2, $rating->getYourScore(), 'Your Score');
        $this->assertEquals("1/2/2000", $rating->getYourRatingDate()->format("n/j/Y"), 'Rating date');
        $this->assertEquals(3, $rating->getSuggestedScore(), 'Suggested score not available is you are rated the film');
        $this->assertEquals(4, $rating->getCriticScore(), 'Critic score not available from Jinni');
        $this->assertEquals(5, $rating->getUserScore(), 'User score not available from Jinni');

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers \RatingSync\Jinni::getFilmDetailFromWebsite
     * @expectedException \InvalidArgumentException
     */
    public function testGetFilmDetailFromWebsiteOverwriteFalseOverEmptyFilm()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        $site = new Jinni(TEST_JINNI_USERNAME);
        $film = new Film($site->http);
        $site->getFilmDetailFromWebsite($film);
    }

    /**
     * @covers \RatingSync\Jinni::getFilmDetailFromWebsite
     * @depends testGetFilmDetailFromWebsite
     */
    public function testMultipleGenres()
    {
        $site = new Jinni(TEST_JINNI_USERNAME);

        $film = new Film($site->http);
        $film->setContentType("FeatureFilm");
        $film->setUrlName("frozen-2013", Constants::SOURCE_JINNI);
        $site->getFilmDetailFromWebsite($film);

        $this->assertEquals(array("Adventure", "Animation", "Fantasy", "Musical", "Family", "Comedy"), $film->getGenres(), 'Genres');

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers \RatingSync\Jinni::getFilmDetailFromWebsite
     * @depends testGetFilmDetailFromWebsite
     */
    public function testMultipleDirectors()
    {
        $site = new Jinni(TEST_JINNI_USERNAME);

        $film = new Film($site->http);
        $film->setContentType("FeatureFilm");
        $film->setUrlName("frozen-2013", Constants::SOURCE_JINNI);
        $site->getFilmDetailFromWebsite($film);

        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), 'Director(s)');

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }
    
    /**
     * @covers \RatingSync\Jinni::exportRatings
     * @depends testObjectCanBeConstructed
     */
    public function testExportRatingsXmlNoDetail()
    {
        $site = new Jinni(TEST_JINNI_USERNAME);

        $testFilename = "ratings_test.xml";
        $success = $site->exportRatings("XML", $testFilename, false);
        $this->assertTrue($success);

        $fullTestFilename = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . Constants::outputFilePath() . $testFilename;
        $fullVerifyFilename = "testfile/verify_ratings_nodetail.xml";
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

    /**
     * @covers \RatingSync\Jinni::exportRatings
     * @depends testObjectCanBeConstructed
     */
    public function testExportRatingsXmlDetail()
    {
        $site = new Jinni(TEST_JINNI_USERNAME);

        $testFilename = "ratings_test.xml";
        $success = $site->exportRatings("XML", $testFilename, true);
        $this->assertTrue($success);

        $fullTestFilename = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . Constants::outputFilePath() . $testFilename;
        $fullVerifyFilename = "testfile/verify_ratings_detail.xml";
        $this->assertTrue(is_readable($fullTestFilename), 'Need to read downloaded file ' . $fullTestFilename);
        $this->assertTrue(is_readable($fullVerifyFilename), 'Need to read verify file ' . $fullVerifyFilename);

        $fp_test = fopen($fullTestFilename, "r");
        $fp_verify = fopen($fullVerifyFilename, "r");
        $testFileSize = filesize($fullTestFilename);
        $verifyFileSize = filesize($fullVerifyFilename);
        $this->assertEquals($testFileSize, $verifyFileSize, 'File sizes - test vs verify');
        $test = fread($fp_test, 22);
        $verify = fread($fp_verify, 22);
        $this->assertEquals($test, $verify, 'Match exported file vs verify file');
        fclose($fp_test);
        fclose($fp_verify);

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }
    
    /**
     * @covers \RatingSync\Jinni::getRatingPageUrl
     * @depends testObjectCanBeConstructed
     * @expectedException \InvalidArgumentException
     */
    public function testGetRatingPageUrlWithArgsNull()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        $site = new JinniExt(TEST_JINNI_USERNAME);
        $site->_getRatingPageUrl(null);
    }
    
    /**
     * @covers \RatingSync\Jinni::getRatingPageUrl
     * @depends testObjectCanBeConstructed
     * @expectedException \InvalidArgumentException
     */
    public function testGetRatingPageUrlWithArgsEmpty()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        $site = new JinniExt(TEST_JINNI_USERNAME);
        $site->_getRatingPageUrl(array());
    }
    
    /**
     * @covers \RatingSync\Jinni::getRatingPageUrl
     * @depends testObjectCanBeConstructed
     * @expectedException \InvalidArgumentException
     */
    public function testGetRatingPageUrlWithPageIndexNull()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        $site = new JinniExt(TEST_JINNI_USERNAME);
        $site->_getRatingPageUrl(array('pageIndex' => null));
    }
    
    /**
     * @covers \RatingSync\Jinni::getRatingPageUrl
     * @depends testObjectCanBeConstructed
     * @expectedException \InvalidArgumentException
     */
    public function testGetRatingPageUrlWithPageIndexEmpty()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        $site = new JinniExt(TEST_JINNI_USERNAME);
        $site->_getRatingPageUrl(array('pageIndex' => ""));
    }
    
    /**
     * @covers \RatingSync\Jinni::getRatingPageUrl
     * @depends testObjectCanBeConstructed
     * @expectedException \InvalidArgumentException
     */
    public function testGetRatingPageUrlWithPageIndexNotInt()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        $site = new JinniExt(TEST_JINNI_USERNAME);
        $site->_getRatingPageUrl(array('pageIndex' => "Not_An_Int"));
    }
    
    /**
     * @covers \RatingSync\Jinni::getRatingPageUrl
     * @depends testObjectCanBeConstructed
     */
    public function testGetRatingPageUrl()
    {
        $site = new JinniExt(TEST_JINNI_USERNAME);
        
        $url = $site->_getRatingPageUrl(array('pageIndex' => 3));
        $this->assertEquals('/user/'.TEST_JINNI_USERNAME.'/ratings?pagingSlider_index=3', $url);

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }
    
    /**
     * @covers \RatingSync\Jinni::getNextRatingPageNumber
     * @depends testObjectCanBeConstructed
     */
    public function testGetNextRatingPageNumberWithNull()
    {
        $site = new JinniExt(TEST_JINNI_USERNAME);
        
        $this->assertFalse($site->_getNextRatingPageNumber(null));

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }
    
    /**
     * @covers \RatingSync\Jinni::getNextRatingPageNumber
     * @depends testObjectCanBeConstructed
     */
    public function testGetNextRatingPageNumberWithEmpty()
    {
        $site = new JinniExt(TEST_JINNI_USERNAME);
        
        $this->assertFalse($site->_getNextRatingPageNumber(""));

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }
    
    /**
     * @covers \RatingSync\Jinni::getNextRatingPageNumber
     * @depends testGetRatingPageUrl
     */
    public function testGetNextRatingPageNumberFirstPage()
    {
        $site = new JinniExt(TEST_JINNI_USERNAME);
        
        $args = array('pageIndex' => 1);
        $page = $site->_getHttp()->getPage($site->_getRatingPageUrl($args));
        $this->assertEquals(2, $site->_getNextRatingPageNumber($page));

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }
    
    /**
     * @covers \RatingSync\Jinni::getNextRatingPageNumber
     * @depends testGetRatingPageUrl
     */
    public function testGetNextRatingPageNumberLastPage()
    {
        $site = new JinniExt(TEST_JINNI_USERNAME);
        
        $args = array('pageIndex' => 2);
        $page = $site->_getHttp()->getPage($site->_getRatingPageUrl($args));
        $this->assertFalse($site->_getNextRatingPageNumber($page));

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }
    
    /**
     * @covers \RatingSync\Jinni::parseDetailPageForTitle
     * @covers \RatingSync\Jinni::parseDetailPageFilmYear
     * @covers \RatingSync\Jinni::parseDetailPageImage
     * @covers \RatingSync\Jinni::parseDetailPageContentType
     * @covers \RatingSync\Jinni::parseDetailPageFilmId
     * @covers \RatingSync\Jinni::parseDetailPageUrlName
     * @covers \RatingSync\Jinni::parseDetailPageRating
     * @covers \RatingSync\Jinni::parseDetailPageGenres
     * @covers \RatingSync\Jinni::parseDetailPageDirectors
     */
    public function testParseDetailPageEmptyFilmOverwriteTrue()
    {
        $site = new JinniExt(TEST_JINNI_USERNAME);
        $film = new Film($site->_getHttp());

        // Get HTML of the film's detail page
        $findFilm = new Film($site->_getHttp());
        $findFilm->setFilmId("70785", $site->_getSourceName());
        $findFilm->setContentType(Film::CONTENT_FILM, $site->_getSourceName());
        $findFilm->setUrlName("frozen-2013", $site->_getSourceName());
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
        $this->assertStringEndsWith("frozen-2013-1.jpeg", $film->getImage(), 'Check matching Image (empty film overwrite=true)');
        
        $success = $site->_parseDetailPageForContentType($page, $film, true);
        //$this->assertTrue($success, 'Parsing film object for Content Type'); // ContentType not available in the detail page
        $this->assertNull($film->getContentType(), 'Check matching Content Type (empty film overwrite=true)');
        
        $success = $site->_parseDetailPageForUrlName($page, $film, true);
        $this->assertTrue($success, 'Parsing film object for URL Name');
        $this->assertEquals("frozen-2013", $film->getUrlName($site->_getSourceName()), 'Check matching URL Name (empty film overwrite=true)');
        
        $success = $site->_parseDetailPageForFilmId($page, $film, true);
        $this->assertTrue($success, 'Parsing film object for Film Id');
        $this->assertEquals("70785", $film->getFilmId($site->_getSourceName()), 'Check matching Film Id (empty film overwrite=true)');
        
        $success = $site->_parseDetailPageForRating($page, $film, true);
        $rating = $film->getRating($site->_getSourceName());
        $this->assertEquals(8, $rating->getYourScore(), 'Check matching YourScore (empty film overwrite=true)');
        $this->assertNull($rating->getYourRatingDate(), 'Check matching Rating Date (empty film overwrite=true)');
        $this->assertNull($rating->getSuggestedScore(), 'Check matching Suggested Score (empty film overwrite=true)');
        $this->assertNull($rating->getCriticScore(), 'Check matching Critic Score (empty film overwrite=true)');
        $this->assertNull($rating->getUserScore(), 'Check matching User Score (empty film overwrite=true)');
        
        $success = $site->_parseDetailPageForGenres($page, $film, true);
        $this->assertTrue($success, 'Parsing film object for Genres');
        $this->assertEquals(array("Adventure", "Animation", "Fantasy", "Musical", "Family", "Comedy"), $film->getGenres(), 'Check matching Gneres (empty film overwrite=true)');
        
        $success = $site->_parseDetailPageForDirectors($page, $film, true);
        $this->assertTrue($success, 'Parsing film object for Directors');
        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), 'Check matching Directors (empty film overwrite=true)');

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }
    
    /**
     * @covers \RatingSync\Jinni::parseDetailPageForTitle
     * @covers \RatingSync\Jinni::parseDetailPageFilmYear
     * @covers \RatingSync\Jinni::parseDetailPageImage
     * @covers \RatingSync\Jinni::parseDetailPageContentType
     * @covers \RatingSync\Jinni::parseDetailPageFilmId
     * @covers \RatingSync\Jinni::parseDetailPageUrlName
     * @covers \RatingSync\Jinni::parseDetailPageRating
     * @covers \RatingSync\Jinni::parseDetailPageGenres
     * @covers \RatingSync\Jinni::parseDetailPageDirectors
     */
    public function testParseDetailPageEmptyFilmOverwriteFalse()
    {
        $site = new JinniExt(TEST_JINNI_USERNAME);
        $film = new Film($site->_getHttp());

        // Get HTML of the film's detail page
        $findFilm = new Film($site->_getHttp());
        $findFilm->setFilmId("70785", $site->_getSourceName());
        $findFilm->setContentType(Film::CONTENT_FILM, $site->_getSourceName());
        $findFilm->setUrlName("frozen-2013", $site->_getSourceName());
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
        $this->assertStringEndsWith("frozen-2013-1.jpeg", $film->getImage(), 'Check matching Image (empty film overwrite=false)');
        
        $success = $site->_parseDetailPageForContentType($page, $film, false);
        //$this->assertTrue($success, 'Parsing film object for Content Type'); // ContentType not available in the detail page
        $this->assertNull($film->getContentType(), 'Check matching Content Type (empty film overwrite=false)');
        
        $success = $site->_parseDetailPageForUrlName($page, $film, false);
        $this->assertTrue($success, 'Parsing film object for URL Name');
        $this->assertEquals("frozen-2013", $film->getUrlName($site->_getSourceName()), 'Check matching URL Name (empty film overwrite=false)');
        
        $success = $site->_parseDetailPageForFilmId($page, $film, false);
        $this->assertTrue($success, 'Parsing film object for Film Id');
        $this->assertEquals("70785", $film->getFilmId($site->_getSourceName()), 'Check matching Film Id (empty film overwrite=false)');
        
        $success = $site->_parseDetailPageForRating($page, $film, false);
        $rating = $film->getRating($site->_getSourceName());
        $this->assertEquals(8, $rating->getYourScore(), 'Check matching YourScore (empty film overwrite=false)');
        $this->assertNull($rating->getYourRatingDate(), 'Check matching Rating Date (empty film overwrite=false)');
        $this->assertNull($rating->getSuggestedScore(), 'Check matching Suggested Score (empty film overwrite=false)');
        $this->assertNull($rating->getCriticScore(), 'Check matching Critic Score (empty film overwrite=false)');
        $this->assertNull($rating->getUserScore(), 'Check matching User Score (empty film overwrite=false)');
        
        $success = $site->_parseDetailPageForGenres($page, $film, false);
        $this->assertTrue($success, 'Parsing film object for Genres');
        $this->assertEquals(array("Adventure", "Animation", "Fantasy", "Musical", "Family", "Comedy"), $film->getGenres(), 'Check matching Gneres (empty film overwrite=false)');
        
        $success = $site->_parseDetailPageForDirectors($page, $film, false);
        $this->assertTrue($success, 'Parsing film object for Directors');
        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), 'Check matching Directors (empty film overwrite=false)');

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }
    
    /**
     * @covers \RatingSync\Jinni::parseDetailPageForTitle
     * @covers \RatingSync\Jinni::parseDetailPageFilmYear
     * @covers \RatingSync\Jinni::parseDetailPageImage
     * @covers \RatingSync\Jinni::parseDetailPageContentType
     * @covers \RatingSync\Jinni::parseDetailPageFilmId
     * @covers \RatingSync\Jinni::parseDetailPageUrlName
     * @covers \RatingSync\Jinni::parseDetailPageRating
     * @covers \RatingSync\Jinni::parseDetailPageGenres
     * @covers \RatingSync\Jinni::parseDetailPageDirectors
     */
    public function testParseDetailPageFullFilmOverwriteTrue()
    {
        $site = new JinniExt(TEST_JINNI_USERNAME);
        $film = new Film($site->_getHttp());

        // Setup original data
        $film->setTitle("Original_Title");
        $film->setYear(1900);
        $film->setContentType(Film::CONTENT_TV);
        $film->setImage("Original_Image");
        $film->setImage("Original_JinniImage", $site->_getSourceName());
        $film->setImage("Original_IMDbImage", Constants::SOURCE_IMDB);
        $film->setFilmId("Original_JinniFilmId", $site->_getSourceName());
        $film->setFilmId("Original_IMDbFilmId", Constants::SOURCE_IMDB);
        $film->setUrlName("Original_JinniUrlName", $site->_getSourceName());
        $film->setUrlName("Original_IMDbUrlName", Constants::SOURCE_IMDB);
        $film->addGenre("Original_Genre1");
        $film->addGenre("Original_Genre2");
        $film->addDirector("Original_Director1");
        $film->addDirector("Original_Director2");
        $ratingJinniOrig = new Rating($site->_getSourceName());
        $ratingJinniOrig->setYourScore(1);
        $ratingJinniOrig->setYourRatingDate(new \DateTime('2000-01-01'));
        $ratingJinniOrig->setSuggestedScore(2);
        $ratingJinniOrig->setCriticScore(3);
        $ratingJinniOrig->setUserScore(4);
        $film->setRating($ratingJinniOrig, $site->_getSourceName());
        $ratingImdbOrig = new Rating(Constants::SOURCE_IMDB);
        $ratingImdbOrig->setYourScore(2);
        $ratingImdbOrig->setYourRatingDate(new \DateTime('2000-01-02'));
        $ratingImdbOrig->setSuggestedScore(3);
        $ratingImdbOrig->setCriticScore(4);
        $ratingImdbOrig->setUserScore(5);
        $film->setRating($ratingImdbOrig, Constants::SOURCE_IMDB);

        // Get HTML of the film's detail page
        $findFilm = new Film($site->_getHttp());
        $findFilm->setFilmId("70785", $site->_getSourceName());
        $findFilm->setContentType(Film::CONTENT_FILM, $site->_getSourceName());
        $findFilm->setUrlName("frozen-2013", $site->_getSourceName());
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
        $this->assertStringEndsWith("frozen-2013-1.jpeg", $film->getImage(), 'Check matching Image (full film overwrite=true)');
        $this->assertStringEndsWith("frozen-2013-1.jpeg", $film->getImage($site->_getSourceName()), 'Check matching Image (full film overwrite=true)');
        $this->assertEquals("Original_IMDbImage", $film->getImage(Constants::SOURCE_IMDB), 'Check matching Image (full film overwrite=true)');
        
        $success = $site->_parseDetailPageForUrlName($page, $film, true);
        $this->assertTrue($success, 'Parsing film object for URL Name');
        $this->assertEquals("frozen-2013", $film->getUrlName($site->_getSourceName()), 'Check matching URL Name (full film overwrite=true)');
        $this->assertEquals("Original_IMDbUrlName", $film->getUrlName(Constants::SOURCE_IMDB), 'Check matching URL Name (full film overwrite=true)');
        
        $success = $site->_parseDetailPageForFilmId($page, $film, true);
        $this->assertTrue($success, 'Parsing film object for Film Id');
        $this->assertEquals("70785", $film->getFilmId($site->_getSourceName()), 'Check matching Film Id (full film overwrite=true)');
        $this->assertEquals("Original_IMDbFilmId", $film->getFilmId(Constants::SOURCE_IMDB), 'Check matching Film Id (full film overwrite=true)');
        
        $success = $site->_parseDetailPageForRating($page, $film, true);
        $rating = $film->getRating($site->_getSourceName());
        $this->assertEquals(8, $rating->getYourScore(), 'Check matching YourScore (full film overwrite=true)');
        $this->assertEquals(new \DateTime('2000-01-01'), $rating->getYourRatingDate(), 'Check matching Rating Date (full film overwrite=true)');
        $this->assertEquals(2, $rating->getSuggestedScore(), 'Check matching Suggested Score (full film overwrite=true)');
        $this->assertEquals(3, $rating->getCriticScore(), 'Check matching Critic Score (full film overwrite=true)');
        $this->assertEquals(4, $rating->getUserScore(), 'Check matching User Score (full film overwrite=true)');
        $rating = $film->getRating(Constants::SOURCE_IMDB);
        $this->assertEquals(2, $rating->getYourScore(), 'Check matching YourScore (full film overwrite=true)');
        $this->assertEquals(new \DateTime('2000-01-02'), $rating->getYourRatingDate(), 'Check matching Rating Date (full film overwrite=true)');
        $this->assertEquals(3, $rating->getSuggestedScore(), 'Check matching Suggested Score (full film overwrite=true)');
        $this->assertEquals(4, $rating->getCriticScore(), 'Check matching Critic Score (full film overwrite=true)');
        $this->assertEquals(5, $rating->getUserScore(), 'Check matching User Score (full film overwrite=true)');
        
        $success = $site->_parseDetailPageForGenres($page, $film, true);
        $this->assertTrue($success, 'Parsing film object for Genres');
        $this->assertEquals(array("Adventure", "Animation", "Fantasy", "Musical", "Family", "Comedy"), $film->getGenres(), 'Check matching Gneres (full film overwrite=true)');
        
        $success = $site->_parseDetailPageForDirectors($page, $film, true);
        $this->assertTrue($success, 'Parsing film object for Directors');
        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), 'Check matching Directors (full film overwrite=true)');

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }
    
    /**
     * @covers \RatingSync\Jinni::parseDetailPageForTitle
     * @covers \RatingSync\Jinni::parseDetailPageFilmYear
     * @covers \RatingSync\Jinni::parseDetailPageImage
     * @covers \RatingSync\Jinni::parseDetailPageContentType
     * @covers \RatingSync\Jinni::parseDetailPageFilmId
     * @covers \RatingSync\Jinni::parseDetailPageUrlName
     * @covers \RatingSync\Jinni::parseDetailPageRating
     * @covers \RatingSync\Jinni::parseDetailPageGenres
     * @covers \RatingSync\Jinni::parseDetailPageDirectors
     */
    public function testParseDetailPageFullFilmOverwriteFalse()
    {
        $site = new JinniExt(TEST_JINNI_USERNAME);
        $film = new Film($site->_getHttp());

        // Setup original data
        $film->setTitle("Original_Title");
        $film->setYear(1900);
        $film->setContentType(Film::CONTENT_TV);
        $film->addGenre("Original_Genre1");
        $film->addGenre("Original_Genre2");
        $film->addDirector("Original_Director1");
        $film->addDirector("Original_Director2");
        $film->setImage("Original_Image");
        $film->setImage("Original_Image_Jinni", $site->_getSourceName());
        $film->setImage("Original_Image_Imdb", Constants::SOURCE_IMDB);
        $film->setUrlName("Original_UrlName_Jinni", $site->_getSourceName());
        $film->setUrlName("Original_UrlName_Imdb", Constants::SOURCE_IMDB);
        $film->setFilmId("Original_FilmId_Jinni", $site->_getSourceName());
        $film->setFilmId("Original_FilmId_Imdb", Constants::SOURCE_IMDB);
        $ratingJinniOrig = new Rating($site->_getSourceName());
        $ratingJinniOrig->setYourScore(1);
        $ratingJinniOrig->setYourRatingDate(new \DateTime('2000-01-01'));
        $ratingJinniOrig->setSuggestedScore(2);
        $ratingJinniOrig->setCriticScore(3);
        $ratingJinniOrig->setUserScore(4);
        $film->setRating($ratingJinniOrig, $site->_getSourceName());
        $ratingImdbOrig = new Rating(Constants::SOURCE_IMDB);
        $ratingImdbOrig->setYourScore(2);
        $ratingImdbOrig->setYourRatingDate(new \DateTime('2000-01-02'));
        $ratingImdbOrig->setSuggestedScore(3);
        $ratingImdbOrig->setCriticScore(4);
        $ratingImdbOrig->setUserScore(5);
        $film->setRating($ratingImdbOrig, Constants::SOURCE_IMDB);

        // Get HTML of the film's detail page
        $findFilm = new Film($site->_getHttp());
        $findFilm->setFilmId("70785", $site->_getSourceName());
        $findFilm->setContentType(Film::CONTENT_FILM, $site->_getSourceName());
        $findFilm->setUrlName("frozen-2013", $site->_getSourceName());
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
        $this->assertEquals("Original_Image_Jinni", $film->getImage($site->_getSourceName()), 'Check matching Image (full film overwrite=false)');
        $this->assertEquals("Original_Image_Imdb", $film->getImage(Constants::SOURCE_IMDB), 'Check matching Image (full film overwrite=false)');
        
        $success = $site->_parseDetailPageForUrlName($page, $film, false);
        $this->assertFalse($success, 'Parsing film object for URL Name');
        $this->assertEquals("Original_UrlName_Jinni", $film->getUrlName($site->_getSourceName()), 'Check matching URL Name (full film overwrite=false)');
        $this->assertEquals("Original_UrlName_Imdb", $film->getUrlName(Constants::SOURCE_IMDB), 'Check matching URL Name (full film overwrite=false)');
        
        $success = $site->_parseDetailPageForFilmId($page, $film, false);
        $this->assertFalse($success, 'Parsing film object for Film Id');
        $this->assertEquals("Original_FilmId_Jinni", $film->getFilmId($site->_getSourceName()), 'Check matching Film Id (full film overwrite=false)');
        $this->assertEquals("Original_FilmId_Imdb", $film->getFilmId(Constants::SOURCE_IMDB), 'Check matching Film Id (full film overwrite=false)');
        
        $success = $site->_parseDetailPageForRating($page, $film, false);
        $rating = $film->getRating($site->_getSourceName());
        $this->assertEquals(1, $rating->getYourScore(), 'Check matching YourScore (full film overwrite=false)');
        $this->assertEquals(new \DateTime('2000-01-01'), $rating->getYourRatingDate(), 'Check matching Rating Date (full film overwrite=false)');
        $this->assertEquals(2, $rating->getSuggestedScore(), 'Check matching Suggested Score (full film overwrite=false)');
        $this->assertEquals(3, $rating->getCriticScore(), 'Check matching Critic Score (full film overwrite=false)');
        $this->assertEquals(4, $rating->getUserScore(), 'Check matching User Score (full film overwrite=false)');
        $rating = $film->getRating(Constants::SOURCE_IMDB);
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
     * @covers \RatingSync\Jinni::parseDetailPageForTitle
     * @covers \RatingSync\Jinni::parseDetailPageFilmYear
     * @depends testParseDetailPageEmptyFilmOverwriteTrue
     */
    public function testParseDetailPageOngoingTvSeries()
    {
        $site = new JinniExt(TEST_JINNI_USERNAME);
        $film = new Film($site->_getHttp());

        // Get HTML of the film's detail page
        $findFilm = new Film($site->_getHttp());
        $findFilm->setFilmId("37194", $site->_getSourceName());
        $findFilm->setContentType(Film::CONTENT_TV, $site->_getSourceName());
        $findFilm->setUrlName("good-morning-america", $site->_getSourceName());
        $site->getFilmDetailFromWebsite($findFilm, true, 60);
        $page = $site->getFilmDetailPageFromCache($findFilm, 60);
        
        $success = $site->_parseDetailPageForTitle($page, $film, true);
        $this->assertTrue($success, 'Parsing film object for Title');
        $this->assertEquals("Good Morning America", $film->getTitle(), 'Check matching Title (empty film overwrite=true)');

        $success = $site->_parseDetailPageForFilmYear($page, $film, true);
        $this->assertTrue($success, 'Parsing film object for Year');
        $this->assertEquals(1975, $film->getYear(), 'Check matching Year (empty film overwrite=true)');

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }
}

?>
