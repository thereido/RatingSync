<?php
/**
 * Imdb PHPUnit
 */
namespace RatingSync;

require_once "../Imdb.php";
require_once "JinniTest.php";

const TEST_IMDB_USERNAME = "ur60460017";

// Class to expose protected members and functions
class ImdbExt extends \RatingSync\Imdb {
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

class ImdbTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers            \RatingSync\Imdb::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testCannotBeConstructedFromNull()
    {
        new Imdb(null);
    }

    /**
     * @covers            \RatingSync\Imdb::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testCannotBeConstructedFromEmptyUsername()
    {
        new Imdb("");
    }

    /**
     * @covers \RatingSync\Imdb::__construct
     */
    public function testObjectCanBeConstructed()
    {
        $site = new Imdb(TEST_IMDB_USERNAME);
        return $site;
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
    }

    /**
     * @covers \RatingSync\Imdb::getRatings
     * @depends testObjectCanBeConstructed
     */
    public function testGetRatings()
    {
        $site = new Imdb(TEST_IMDB_USERNAME);
        $films = $site->getRatings();
    }

    /**
     * @covers \RatingSync\Imdb::getRatings
     * @depends testObjectCanBeConstructed
     */
    public function testGetRatingsFromRandomAccount()
    {
        // Find films even though the account is not logged in
        $site = new Imdb("ur29387747");
        $films = $site->getRatings();
        $this->assertGreaterThan(0, count($films));
    }

    /**
     * @covers \RatingSync\Imdb::getRatings
     * @depends testObjectCanBeConstructed
     * @depends testGetRatings
     */
    public function testGetRatingsCount()
    {
        $site = new Imdb(TEST_IMDB_USERNAME);
        $films = $site->getRatings();
        $this->assertCount(104, $films);
    }

    /**
     * @covers \RatingSync\Imdb::getRatings
     * @depends testObjectCanBeConstructed
     * @depends testGetRatings
     */
    public function testGetRatingsLimitPages()
    {
        $site = new Imdb(TEST_IMDB_USERNAME);
        $films = $site->getRatings(1);
        $this->assertCount(100, $films);
    }

    /**
     * @covers \RatingSync\Jinni::getRatings
     * @depends testObjectCanBeConstructed
     * @depends testGetRatings
     */
    public function testGetRatingsBeginPage()
    {
        $site = new Imdb(TEST_IMDB_USERNAME);
        $films = $site->getRatings(null, 2);
        $this->assertEquals("Unbroken", $films[0]->getTitle());
    }

    /**
     * @covers \RatingSync\Jinni::getRatings
     * @depends testObjectCanBeConstructed
     * @depends testGetRatingsLimitPages
     * @depends testGetRatingsBeginPage
     */
    public function testGetRatingsDetails()
    {
        $site = new ImdbExt(TEST_IMDB_USERNAME);
        $films = $site->getRatings(1, 1, true);
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
     * @depends testObjectCanBeConstructed
     */
    public function testCacheFilmDetail()
    {
        // Cache the file for later tests in this unit test class
        $http = new HttpImdb(TEST_IMDB_USERNAME);


        // Frozen (movie)
        $this->cachedDetailPage = $http->getPage("/title/tt2294629/");
        $this->assertGreaterThan(100, strlen($this->cachedDetailPage));
        $filename = JinniTest::getCachePath() . "imdb_frozen-2013.html";
        $fp = fopen($filename, "w");
        fwrite($fp, $this->cachedDetailPage);
        fclose($fp);

        // Good Morning America (ongoing TV series)
        $this->cachedDetailPage = $http->getPage("/title/tt0072506/");
        $this->assertGreaterThan(100, strlen($this->cachedDetailPage));
        $filename = JinniTest::getCachePath() . "imdb_good-morning-america.html";
        $fp = fopen($filename, "w");
        fwrite($fp, $this->cachedDetailPage);
        fclose($fp);
    }

    /**
     * @covers \RatingSync\Imdb::getFilmDetailFromWebsite
     * @depends testObjectCanBeConstructed
     * @expectedException \InvalidArgumentException
     */
    public function testGetFilmDetailFromWebsiteFromNull()
    {
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
        $site = new Imdb(TEST_IMDB_USERNAME);
        $site->getFilmDetailFromWebsite("String_Not_Film_Object");
    }

    /**
     * @covers \RatingSync\Imdb::getFilmDetailFromWebsite
     * @depends testObjectCanBeConstructed
     * @expectedException \InvalidArgumentException
     */
    public function testGetFilmDetailFromWebsiteWithoutFilmId()
    {
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
        $site = new ImdbExt(TEST_IMDB_USERNAME);
        $film = new Film($site->http);
        $film->setFilmId("NO_FILMID_MATCH", $site->_getSourceName());
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
        $film->setFilmId("tt2294629", $site->_getSourceName());
        $site->getFilmDetailFromWebsite($film, true);

        $this->assertEquals("Frozen", $film->getTitle(), 'Title');
        $this->assertEquals(2013, $film->getYear(), 'Year');
        $this->assertEquals("FeatureFilm", $film->getContentType(), 'Content Type');
        $this->assertEquals(1, preg_match('@(http://ia.media-imdb.com/images/M/MV5BMTQ1MjQwMTE5OF5BMl5BanBnXkFtZTgwNjk3MTcyMDE)@', $film->getImage(), $matches), 'Image link');
        $this->assertEquals("tt2294629", $film->getFilmId($site->_getSourceName()), 'Film ID');
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
    }

    /**
     * @covers \RatingSync\Imdb::getFilmDetailFromWebsite
     * @depends testGetFilmDetailFromWebsite
     */
    public function testGetFilmDetailFromWebsiteOverwriteTrueOverEmpty()
    {
        $site = new ImdbExt(TEST_IMDB_USERNAME);

        $film = new Film($site->http);
        $film->setFilmId("tt2294629", $site->_getSourceName());
        $site->getFilmDetailFromWebsite($film, true);

        // Same results as testGetFilmDetailFromWebsite
        $this->assertEquals("Frozen", $film->getTitle(), 'Title');
        $this->assertEquals(2013, $film->getYear(), 'Year');
        $this->assertEquals("FeatureFilm", $film->getContentType(), 'Content Type');
        $this->assertEquals(1, preg_match('@(http://ia.media-imdb.com/images/M/MV5BMTQ1MjQwMTE5OF5BMl5BanBnXkFtZTgwNjk3MTcyMDE)@', $film->getImage(), $matches), 'Image link');
        $this->assertEquals(1, preg_match('@(http://ia.media-imdb.com/images/M/MV5BMTQ1MjQwMTE5OF5BMl5BanBnXkFtZTgwNjk3MTcyMDE)@', $film->getImage($site->_getSourceName()), $matches), 'Image link');
        $this->assertEquals("tt2294629", $film->getFilmId($site->_getSourceName()), 'Film ID');
        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), 'Director(s)');
        $this->assertEquals(array("Animation", "Adventure", "Comedy"), $film->getGenres(), 'Genres');
        $rating = $film->getRating($site->_getSourceName());
        $this->assertEquals(7.4, $rating->getCriticScore(), 'Critic score');
        $this->assertEquals(7.7, $rating->getUserScore(), 'User score');
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
        $film->setFilmId("Original_FilmId_Imdb", Constants::SOURCE_IMDB);
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
        $film->setFilmId("Original_FilmId_Jinni", Constants::SOURCE_JINNI);
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
        $film->setFilmId("tt2294629", Constants::SOURCE_IMDB);
        $site->getFilmDetailFromWebsite($film, true);

        // Verify - Same original data
        $this->assertEquals("Frozen", $film->getTitle(), 'Title');
        $this->assertEquals(2013, $film->getYear(), 'Year');
        $this->assertEquals("FeatureFilm", $film->getContentType(), 'Content Type');
        $this->assertEquals(1, preg_match('@(http://ia.media-imdb.com/images/M/MV5BMTQ1MjQwMTE5OF5BMl5BanBnXkFtZTgwNjk3MTcyMDE)@', $film->getImage(), $matches), 'Image link');
        $this->assertEquals(array("Animation", "Adventure", "Comedy"), $film->getGenres(), 'Genres');
        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), 'Director(s)');

        // Verify - Same original data (IMDb)
        $this->assertEquals("tt2294629", $film->getFilmId(Constants::SOURCE_IMDB), 'Film ID');
        $rating = $film->getRating(Constants::SOURCE_IMDB);
        $this->assertEquals(7.4, $rating->getCriticScore(), 'Critic score');
        $this->assertEquals(7.7, $rating->getUserScore(), 'User score');
        // The film detail page does not have these fields.  Don't overwrite them.
        $this->assertEquals("Original_UrlName_Imdb", $film->getUrlName(Constants::SOURCE_IMDB), 'URL Name');
        $this->assertEquals(2, $rating->getYourScore(), 'Your Score');
        $this->assertEquals("1/2/2000", $rating->getYourRatingDate()->format("n/j/Y"), 'Rating date');
        $this->assertEquals(3, $rating->getSuggestedScore(), 'Suggested score');

        // Verify - Same original data (Jinni, unchanged)
        $this->assertEquals("Original_FilmId_Jinni", $film->getFilmId(Constants::SOURCE_JINNI), 'Film ID (Jinni)');
        $this->assertEquals("Original_UrlName_Jinni", $film->getUrlName(Constants::SOURCE_JINNI), 'URL Name (Jinni)');
        $rating = $film->getRating(Constants::SOURCE_JINNI);
        $this->assertEquals(1, $rating->getYourScore(), 'Your Score (Jinni)');
        $this->assertEquals("1/1/2000", $rating->getYourRatingDate()->format("n/j/Y"), 'Rating date (Jinni)');
        $this->assertEquals(2, $rating->getSuggestedScore(), 'Suggested score (Jinni)');
        $this->assertEquals(3, $rating->getCriticScore(), 'Critic score (Jinni)');
        $this->assertEquals(4, $rating->getUserScore(), 'User score (Jinni)');
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
        $film->setFilmId("Original_FilmId_Imdb", Constants::SOURCE_IMDB);
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
        $film->setFilmId("Original_FilmId_Jinni", Constants::SOURCE_JINNI);
        $film->setUrlName("Original_UrlName_Jinni", Constants::SOURCE_JINNI);
        $ratingJinniOrig = new Rating(Constants::SOURCE_JINNI);
        $ratingJinniOrig->setYourScore(1);
        $ratingJinniOrig->setYourRatingDate(new \DateTime('2000-01-01'));
        $ratingJinniOrig->setSuggestedScore(2);
        $ratingJinniOrig->setCriticScore(3);
        $ratingJinniOrig->setUserScore(4);
        $film->setRating($ratingJinniOrig, Constants::SOURCE_JINNI);

        // Get detail not overwriting
        $film->setFilmId("tt2294629", Constants::SOURCE_IMDB);
        $site->getFilmDetailFromWebsite($film, false);

        // Verify - Same original data
        $this->assertEquals("Original_Title", $film->getTitle(), 'Title');
        $this->assertEquals(1900, $film->getYear(), 'Year');
        $this->assertEquals("Original_Image", $film->getImage(), 'Image link');
        $this->assertEquals("FeatureFilm", $film->getContentType(), 'Content Type');
        $this->assertEquals(array("Original_Director1", "Original_Director2"), $film->getDirectors(), 'Director(s)');
        $this->assertEquals(array("Original_Genre1", "Original_Genre2"), $film->getGenres(), 'Genres');

        // Verify - Same original data (IMDb)
        $this->assertEquals("tt2294629", $film->getFilmId(Constants::SOURCE_IMDB), 'Film ID');
        $this->assertEquals("Original_UrlName_Imdb", $film->getUrlName(Constants::SOURCE_IMDB), 'URL Name');
        $rating = $film->getRating(Constants::SOURCE_IMDB);
        $this->assertEquals(2, $rating->getYourScore(), 'Your Score');
        $this->assertEquals("1/2/2000", $rating->getYourRatingDate()->format("n/j/Y"), 'Your Rating Date');
        $this->assertEquals(3, $rating->getSuggestedScore(), 'Suggested score');
        $this->assertEquals(4, $rating->getCriticScore(), 'Critic score');
        $this->assertEquals(5, $rating->getUserScore(), 'User score');

        // Verify - Same original data (Jinni)
        $this->assertEquals("Original_FilmId_Jinni", $film->getFilmId(Constants::SOURCE_JINNI), 'Film ID (Jinni)');
        $this->assertEquals("Original_UrlName_Jinni", $film->getUrlName(Constants::SOURCE_JINNI), 'URL Name (Jinni)');
        $rating = $film->getRating(Constants::SOURCE_JINNI);
        $this->assertEquals(1, $rating->getYourScore(), 'Your Score (Jinni)');
        $this->assertEquals("1/1/2000", $rating->getYourRatingDate()->format("n/j/Y"), 'Your Rating Date (Jinni)');
        $this->assertEquals(2, $rating->getSuggestedScore(), 'Suggested score (Jinni)');
        $this->assertEquals(3, $rating->getCriticScore(), 'Critic score (Jinni)');
        $this->assertEquals(4, $rating->getUserScore(), 'User score (Jinni)');
    }

    /**
     * @covers \RatingSync\Imdb::getFilmDetailFromWebsite
     * @depends testGetFilmDetailFromWebsite
     */
    public function testGetFilmDetailFromWebsiteOverwriteFalseOverEmpty()
    {
        $site = new ImdbExt(TEST_IMDB_USERNAME);

        $film = new Film($site->http);
        $film->setFilmId("tt2294629", $site->_getSourceName());
        $site->getFilmDetailFromWebsite($film, false);

        // Same results as testGetFilmDetailFromWebsite or testGetFilmDetailFromWebsiteOverwriteTrueOverEmpty
        $this->assertEquals("Frozen", $film->getTitle(), 'Title');
        $this->assertEquals(2013, $film->getYear(), 'Year');
        $this->assertEquals("FeatureFilm", $film->getContentType(), 'Content Type');
        $this->assertEquals(1, preg_match('@(http://ia.media-imdb.com/images/M/MV5BMTQ1MjQwMTE5OF5BMl5BanBnXkFtZTgwNjk3MTcyMDE)@', $film->getImage(), $matches), 'Image link');
        $this->assertEquals(1, preg_match('@(http://ia.media-imdb.com/images/M/MV5BMTQ1MjQwMTE5OF5BMl5BanBnXkFtZTgwNjk3MTcyMDE)@', $film->getImage($site->_getSourceName()), $matches), 'Image link');
        $this->assertEquals("tt2294629", $film->getFilmId($site->_getSourceName()), 'Film ID');
        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), 'Director(s)');
        $this->assertEquals(array("Animation", "Adventure", "Comedy"), $film->getGenres(), 'Genres');
        $rating = $film->getRating($site->_getSourceName());
        $this->assertEquals(7.4, $rating->getCriticScore(), 'Critic score');
        $this->assertEquals(7.7, $rating->getUserScore(), 'User score');
    }

    /**
     * @covers \RatingSync\Jinni::getFilmDetailFromWebsite
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
        $film->setFilmId("Original_FilmId_Imdb", Constants::SOURCE_IMDB);
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
        $film->setFilmId("Original_FilmId_Jinni", Constants::SOURCE_JINNI);
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
        $film->setFilmId("tt2294629", Constants::SOURCE_IMDB);
        $site->getFilmDetailFromWebsite($film);

        // Verify - Same original data
        $this->assertEquals("Frozen", $film->getTitle(), 'Title');
        $this->assertEquals(2013, $film->getYear(), 'Year');
        $this->assertEquals("FeatureFilm", $film->getContentType(), 'Content Type');
        $this->assertEquals(1, preg_match('@(http://ia.media-imdb.com/images/M/MV5BMTQ1MjQwMTE5OF5BMl5BanBnXkFtZTgwNjk3MTcyMDE)@', $film->getImage(), $matches), 'Image link');
        $this->assertEquals(array("Animation", "Adventure", "Comedy"), $film->getGenres(), 'Genres');
        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), 'Director(s)');

        // Verify - Same original data (IMDb)
        $this->assertEquals("tt2294629", $film->getFilmId(Constants::SOURCE_IMDB), 'Film ID');
        $rating = $film->getRating(Constants::SOURCE_IMDB);
        $this->assertEquals(7.4, $rating->getCriticScore(), 'Critic score');
        $this->assertEquals(7.7, $rating->getUserScore(), 'User score');
        // The film detail page does not have these fields.  Don't overwrite them.
        $this->assertEquals("Original_UrlName_Imdb", $film->getUrlName(Constants::SOURCE_IMDB), 'URL Name');
        $this->assertEquals(2, $rating->getYourScore(), 'Your Score');
        $this->assertEquals("1/2/2000", $rating->getYourRatingDate()->format("n/j/Y"), 'Rating date');
        $this->assertEquals(3, $rating->getSuggestedScore(), 'Suggested score');

        // Verify - Same original data (Jinni, unchanged)
        $this->assertEquals("Original_FilmId_Jinni", $film->getFilmId(Constants::SOURCE_JINNI), 'Film ID (Jinni)');
        $this->assertEquals("Original_UrlName_Jinni", $film->getUrlName(Constants::SOURCE_JINNI), 'URL Name (Jinni)');
        $rating = $film->getRating(Constants::SOURCE_JINNI);
        $this->assertEquals(1, $rating->getYourScore(), 'Your Score (Jinni)');
        $this->assertEquals("1/1/2000", $rating->getYourRatingDate()->format("n/j/Y"), 'Rating date (Jinni)');
        $this->assertEquals(2, $rating->getSuggestedScore(), 'Suggested score (Jinni)');
        $this->assertEquals(3, $rating->getCriticScore(), 'Critic score (Jinni)');
        $this->assertEquals(4, $rating->getUserScore(), 'User score (Jinni)');
    }

    /**
     * @covers \RatingSync\Imdb::getFilmDetailFromWebsite
     * @expectedException \InvalidArgumentException
     */
    public function testGetFilmDetailFromWebsiteOverwriteFalseOverEmptyFilm()
    {
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
        $film->setFilmId("tt2294629", $site->_getSourceName());
        $site->getFilmDetailFromWebsite($film);

        $this->assertEquals(array("Animation", "Adventure", "Comedy"), $film->getGenres(), 'Genres');
    }

    /**
     * @covers \RatingSync\Imdb::getFilmDetailFromWebsite
     * @depends testGetFilmDetailFromWebsite
     */
    public function testMultipleDirectors()
    {
        $site = new ImdbExt(TEST_IMDB_USERNAME);

        $film = new Film($site->http);
        $film->setFilmId("tt2294629", $site->_getSourceName());
        $site->getFilmDetailFromWebsite($film);

        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), 'Director(s)');
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
        $success = $site->exportRatings("XML", $testFilename, true);
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
    }
*/
    
    /**
     * @covers \RatingSync\Imdb::getRatingPageUrl
     * @depends testObjectCanBeConstructed
     * @expectedException \InvalidArgumentException
     */
    public function testGetRatingPageUrlWithArgsNull()
    {
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
    }
    
    /**
     * @covers \RatingSync\Imdb::getNextRatingPageNumber
     * @depends testObjectCanBeConstructed
     */
    public function testGetNextRatingPageNumberWithNull()
    {
        $site = new ImdbExt(TEST_IMDB_USERNAME);
        
        $this->assertFalse($site->_getNextRatingPageNumber(null));
    }
    
    /**
     * @covers \RatingSync\Imdb::getNextRatingPageNumber
     * @depends testObjectCanBeConstructed
     */
    public function testGetNextRatingPageNumberWithEmpty()
    {
        $site = new ImdbExt(TEST_IMDB_USERNAME);
        
        $this->assertFalse($site->_getNextRatingPageNumber(""));
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
    }
    
    /**
     * @covers \RatingSync\Imdb::parseDetailPageForTitle
     * @covers \RatingSync\Imdb::parseDetailPageFilmYear
     * @covers \RatingSync\Imdb::parseDetailPageImage
     * @covers \RatingSync\Imdb::parseDetailPageContentType
     * @covers \RatingSync\Imdb::parseDetailPageFilmId
     * @covers \RatingSync\Imdb::parseDetailPageUrlName
     * @covers \RatingSync\Imdb::parseDetailPageRating
     * @covers \RatingSync\Imdb::parseDetailPageGenres
     * @covers \RatingSync\Imdb::parseDetailPageDirectors
     * @depends testCacheFilmDetail
     */
    public function testParseDetailPageEmptyFilmOverwriteTrue()
    {
        $site = new ImdbExt(TEST_IMDB_USERNAME);
        $film = new Film($site->_getHttp());

        $filename = JinniTest::getCachePath() . "imdb_frozen-2013.html";
        $fp = fopen($filename, "r");
        $page = fread($fp, filesize($filename));
        fclose($fp);
        
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
        
        $success = $site->_parseDetailPageForFilmId($page, $film, true);
        $this->assertTrue($success, 'Parsing film object for Film Id');
        $this->assertEquals("tt2294629", $film->getFilmId($site->_getSourceName()), 'Check matching Film Id (empty film overwrite=true)');
        
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
    }
    
    /**
     * @covers \RatingSync\Imdb::parseDetailPageForTitle
     * @covers \RatingSync\Imdb::parseDetailPageFilmYear
     * @covers \RatingSync\Imdb::parseDetailPageImage
     * @covers \RatingSync\Imdb::parseDetailPageContentType
     * @covers \RatingSync\Imdb::parseDetailPageFilmId
     * @covers \RatingSync\Imdb::parseDetailPageUrlName
     * @covers \RatingSync\Imdb::parseDetailPageRating
     * @covers \RatingSync\Imdb::parseDetailPageGenres
     * @covers \RatingSync\Imdb::parseDetailPageDirectors
     * @depends testCacheFilmDetail
     */
    public function testParseDetailPageEmptyFilmOverwriteFalse()
    {
        $site = new ImdbExt(TEST_IMDB_USERNAME);
        $film = new Film($site->_getHttp());

        $filename = JinniTest::getCachePath() . "imdb_frozen-2013.html";
        $fp = fopen($filename, "r");
        $page = fread($fp, filesize($filename));
        fclose($fp);
        
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
        
        $success = $site->_parseDetailPageForFilmId($page, $film, false);
        $this->assertTrue($success, 'Parsing film object for Film Id');
        $this->assertEquals("tt2294629", $film->getFilmId($site->_getSourceName()), 'Check matching Film Id (empty film overwrite=false)');
        
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
    }
    
    /**
     * @covers \RatingSync\Imdb::parseDetailPageForTitle
     * @covers \RatingSync\Imdb::parseDetailPageFilmYear
     * @covers \RatingSync\Imdb::parseDetailPageImage
     * @covers \RatingSync\Imdb::parseDetailPageContentType
     * @covers \RatingSync\Imdb::parseDetailPageFilmId
     * @covers \RatingSync\Imdb::parseDetailPageUrlName
     * @covers \RatingSync\Imdb::parseDetailPageRating
     * @covers \RatingSync\Imdb::parseDetailPageGenres
     * @covers \RatingSync\Imdb::parseDetailPageDirectors
     * @depends testCacheFilmDetail
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
        $film->setFilmId("Original_FilmId_Imdb", Constants::SOURCE_IMDB);
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
        $film->setFilmId("Original_FilmId_Jinni", Constants::SOURCE_JINNI);
        $film->setImage("Original_Image_Jinni", Constants::SOURCE_JINNI);
        $film->setUrlName("Original_UrlName_Jinni", Constants::SOURCE_JINNI);
        $ratingJinniOrig = new Rating(Constants::SOURCE_JINNI);
        $ratingJinniOrig->setYourScore(1);
        $ratingJinniOrig->setYourRatingDate(new \DateTime('2000-01-01'));
        $ratingJinniOrig->setSuggestedScore(2);
        $ratingJinniOrig->setCriticScore(3);
        $ratingJinniOrig->setUserScore(4);
        $film->setRating($ratingJinniOrig, Constants::SOURCE_JINNI);

        // Read a Film Detail page cached
        $filename = JinniTest::getCachePath() . "imdb_frozen-2013.html";
        $fp = fopen($filename, "r");
        $page = fread($fp, filesize($filename));
        fclose($fp);
        
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
        
        $success = $site->_parseDetailPageForFilmId($page, $film, true);
        $this->assertTrue($success, 'Parsing film object for Film Id');
        $this->assertEquals("tt2294629", $film->getFilmId($site->_getSourceName()), 'Check matching Film Id (full film overwrite=true)');
        
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
    }
    
    /**
     * @covers \RatingSync\Imdb::parseDetailPageForTitle
     * @covers \RatingSync\Imdb::parseDetailPageFilmYear
     * @covers \RatingSync\Imdb::parseDetailPageImage
     * @covers \RatingSync\Imdb::parseDetailPageContentType
     * @covers \RatingSync\Imdb::parseDetailPageFilmId
     * @covers \RatingSync\Imdb::parseDetailPageUrlName
     * @covers \RatingSync\Imdb::parseDetailPageRating
     * @covers \RatingSync\Imdb::parseDetailPageGenres
     * @covers \RatingSync\Imdb::parseDetailPageDirectors
     * @depends testCacheFilmDetail
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
        $film->setFilmId("Original_FilmId_Imdb", Constants::SOURCE_IMDB);
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
        $film->setFilmId("Original_FilmId_Jinni", Constants::SOURCE_JINNI);
        $film->setImage("Original_Image_Jinni", Constants::SOURCE_JINNI);
        $film->setUrlName("Original_UrlName_Jinni", Constants::SOURCE_JINNI);
        $ratingJinniOrig = new Rating(Constants::SOURCE_JINNI);
        $ratingJinniOrig->setYourScore(1);
        $ratingJinniOrig->setYourRatingDate(new \DateTime('2000-01-01'));
        $ratingJinniOrig->setSuggestedScore(2);
        $ratingJinniOrig->setCriticScore(3);
        $ratingJinniOrig->setUserScore(4);
        $film->setRating($ratingJinniOrig, Constants::SOURCE_JINNI);

        // Read a Film Detail page cached
        $filename = JinniTest::getCachePath() . "imdb_frozen-2013.html";
        $fp = fopen($filename, "r");
        $page = fread($fp, filesize($filename));
        fclose($fp);
        
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
        
        $success = $site->_parseDetailPageForFilmId($page, $film, false);
        $this->assertFalse($success, 'Parsing film object for Film Id');
        $this->assertEquals("Original_FilmId_Jinni", $film->getFilmId(Constants::SOURCE_JINNI), 'Check matching Film Id (full film overwrite=false)');
        $this->assertEquals("Original_FilmId_Imdb", $film->getFilmId($site->_getSourceName()), 'Check matching Film Id (full film overwrite=false)');
        
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
    }
}

?>
