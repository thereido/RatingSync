<?php
/**
 * Jinni PHPUnit
 */
namespace RatingSync;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "Jinni.php";

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
    function _parseDetailPageForUniqueName($page, $film, $overwrite) { return $this->parseDetailPageForUniqueName($page, $film, $overwrite); }
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
        $this->debug = false;
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
        $film->setUniqueName("999999", $site->_getSourceName());
        
        $page = "<html><body><h2>Film Detail</h2></body></html>";
        $verifyFilename = "testfile" . DIRECTORY_SEPARATOR . "verify_cache_filmdetailpage.xml";
        $fp = fopen($verifyFilename, "w");
        fwrite($fp, $page);
        fclose($fp);
        
        $site->cacheFilmDetailPage($page, $film);
        $testFilename = Constants::cacheFilePath() . $site->_getSourceName() . "_" . TEST_JINNI_USERNAME . "_film_" . $film->getUniqueName($site->_getSourceName()) . ".html";
        $this->assertFileExists($testFilename, 'Cache file exists');
        $this->assertFileEquals($verifyFilename, $testFilename, 'cache file vs verify file');
        
        unlink($verifyFilename);
        unlink($testFilename);

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
     * @covers \RatingSync\Jinni::testParseFilmsFromFile
     * @depends testObjectCanBeConstructed
     */
    public function testParseFilmsFromFile()
    {
        $site = new Jinni(TEST_JINNI_USERNAME);
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
        $this->assertEquals("http://ia.media-imdb.com/images/M/MV5BMTQ1MjQwMTE5OF5BMl5BanBnXkFtZTgwNjk3MTcyMDE@._V1._SY209_CR0,0,140,209_.jpg", $film->getImage(), "Frozen image");
        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), "Frozen directors");
        $this->assertEquals(array("Animation", "Adventure", "Comedy"), $film->getGenres(), "Frozen genres");
        $this->assertEquals("http://ia.media-imdb.com/images/M/MV5BMTQ1MjQwMTE5OF5BMl5BanBnXkFtZTgwNjk3MTcyMDE@._V1._SY209_CR0,0,140,209_.jpg", $film->getImage(Constants::SOURCE_IMDB), "Frozen ".Constants::SOURCE_IMDB." image");
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
        $this->assertEquals("http://ia.media-imdb.com/images/M/MV5BMTQ1MjQwMTE5OF5BMl5BanBnXkFtZTgwNjk3MTcyMDE@._V1._SY209_CR0,0,140,209_.jpg", $film->getImage(Constants::SOURCE_IMDB), "Frozen ".Constants::SOURCE_IMDB." image");
        $this->assertEquals("tt2294629", $film->getUniqueName(Constants::SOURCE_IMDB), "Frozen ".Constants::SOURCE_IMDB." Unique Name");
        $this->assertEquals(7.4, $film->getCriticScore(Constants::SOURCE_IMDB), "Frozen ".Constants::SOURCE_IMDB." critic score");
        $this->assertEquals(7.7, $film->getUserScore(Constants::SOURCE_IMDB), "Frozen ".Constants::SOURCE_IMDB." user score");
        $rating = $film->getRating(Constants::SOURCE_IMDB);
        $this->assertEquals(2, $rating->getYourScore(), "Frozen ".Constants::SOURCE_IMDB." your score");
        $this->assertNull($rating->getYourRatingDate(), "Frozen ".Constants::SOURCE_IMDB." rating date");
        $this->assertNull($rating->getSuggestedScore(), "Frozen ".Constants::SOURCE_IMDB." suggested score");

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }
    
    /**
     * @covers \RatingSync\Jinni::testFromExportFileToFilmObjectAndBackToXml
     * @depends testParseFilmsFromFile
     */
    public function testFromExportFileToFilmObjectAndBackToXml()
    {
        $site = new Jinni(TEST_JINNI_USERNAME);
        
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
        $this->assertEquals($test, $verify, 'Match test file (written) vs verify file (original)');
        fclose($fp_test);
        fclose($fp_verify);

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }
}

?>
