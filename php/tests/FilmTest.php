<?php
/**
 * Film PHPUnit
 */
namespace RatingSync;

require_once "../Film.php";
require_once "../Rating.php";

require_once "SiteTest.php";
require_once "HttpChild.php";

class FilmTest extends \PHPUnit_Framework_TestCase
{
    public $debug;
    public $lastTestTime;

    public function setUp()
    {
        $this->debug = false;
        $this->lastTestTime = new \DateTime();
    }

    /**
     * @covers \RatingSync\Film::__construct
     */
    public function testObjectCanBeConstructedFromHttp()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers \RatingSync\Film::validContentType
     */
    public function testValidContentTypeTrue()
    {
        $this->assertTrue(Film::validContentType(Film::CONTENT_FILM), Film::CONTENT_FILM . " should be valid");
        $this->assertFalse(Film::validContentType("Bad_Type"), "Bad_Type should be invalid");

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::setFilmName
     * @covers  \RatingSync\Film::getFilmName
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetAndGetFilmName()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        
        // Null
        $film->setFilmName(null, Constants::SOURCE_IMDB);
        $this->assertNull($film->getFilmName(Constants::SOURCE_IMDB));

        // Empty String
        $film->setFilmName("", Constants::SOURCE_IMDB);
        $this->assertEquals("", $film->getFilmName(Constants::SOURCE_IMDB));
        
        // Int
        $film->setFilmName(1234, Constants::SOURCE_IMDB);
        $this->assertEquals(1234, $film->getFilmName(Constants::SOURCE_IMDB));
        
        // Number as a string
        $film->setFilmName("1234", Constants::SOURCE_IMDB);
        $this->assertEquals(1234, $film->getFilmName(Constants::SOURCE_IMDB));
        
        // Alpha-num string
        $film->setFilmName("Film 1D", Constants::SOURCE_IMDB);
        $this->assertEquals("Film 1D", $film->getFilmName(Constants::SOURCE_IMDB));

        // Mismatch source
        $film->setFilmName("Film 1D", Constants::SOURCE_IMDB);
        $this->assertNull($film->getFilmName(Constants::SOURCE_JINNI));

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::getFilmName
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testFilmNameCanBeRetrievedFromNewObject()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $this->assertNull($film->getFilmName(Constants::SOURCE_IMDB));

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::setUrlName
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testUrlNameCannotBeSetWithInvalidSource()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setUrlName("url_name", "Bad_Source");
    }

    /**
     * @covers  \RatingSync\Film::setUrlName
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testUrlNameCanBeSetWithNull()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setUrlName(null, Constants::SOURCE_IMDB);

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::setUrlName
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testUrlNameCanBeSetWithEmpty()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setUrlName("", Constants::SOURCE_IMDB);

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::setUrlName
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testUrlNameCanBeSetWithNonEmpty()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setUrlName("url_name", Constants::SOURCE_IMDB);

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::getUrlName
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testUrlNameCannotBeGottenWithInvalidSource()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->getUrlName("Bad_Source");
    }

    /**
     * @covers  \RatingSync\Film::getUrlName
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testUrlNameCanBeSetWithNonEmpty
     */
    public function testGetUrlName()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setUrlName("url_name", Constants::SOURCE_IMDB);
        $this->assertEquals("url_name", $film->getUrlName(Constants::SOURCE_IMDB));

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::getUrlName
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testGetUrlNameNeverSet()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $this->assertNull($film->getUrlName(Constants::SOURCE_IMDB));

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::getUrlName
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testUrlNameCanBeSetWithNull
     */
    public function testGetNullUrlName()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setUrlName(null, Constants::SOURCE_IMDB);
        $this->assertNull($film->getUrlName(Constants::SOURCE_IMDB));

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::setUrlName
     * @covers  \RatingSync\Film::getUrlName
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testUrlNameCanBeSetWithEmpty
     */
    public function testSetUrlNameWithEmptySetsToNull()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setUrlName("", Constants::SOURCE_IMDB);
        $this->assertNull($film->getUrlName(Constants::SOURCE_IMDB), "Setting empty URL name should be set to null");

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::setRating
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testSetRatingWithInvalidSource()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setRating(null, "Bad_Source");
    }

    /**
     * @covers  \RatingSync\Film::setRating
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testSetRatingWithNullRatingNullSource()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setRating(null, "Bad_Source");
    }

    /**
     * @covers  \RatingSync\Film::setRating
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testSetRatingWithString()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setRating("Bad_Arg", Constants::SOURCE_IMDB);
    }

    /**
     * @covers  \RatingSync\Film::setRating
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testSetRatingWithNumber()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setRating(7, Constants::SOURCE_IMDB);
    }

    /**
     * @covers  \RatingSync\Film::setRating
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetRatingWithNull()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setRating(null, Constants::SOURCE_IMDB);

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::setRating
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetRatingWithEmpty()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setRating("", Constants::SOURCE_IMDB);

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::setRating
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetRating()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $rating = new Rating(Constants::SOURCE_IMDB);
        $film->setRating($rating, Constants::SOURCE_IMDB);

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::setRating
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetRatingWithNoSource()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $rating = new Rating(Constants::SOURCE_IMDB);
        $film->setRating($rating);

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::setRating
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \Exception
     */
    public function testSetRatingWithIncompatibleSource()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $rating = new Rating(Constants::SOURCE_IMDB);
        $film->setRating($rating, Constants::SOURCE_JINNI);
    }

    /**
     * @covers  \RatingSync\Film::setRating
     * @covers  \RatingSync\Film::getRating
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetRating
     */
    public function testGetRating()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $rating = new Rating(Constants::SOURCE_IMDB);
        $rating->setYourScore(6);
        $film->setRating($rating);
        $this->assertEquals(6, $film->getRating(Constants::SOURCE_IMDB)->getYourScore());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::setRating
     * @covers  \RatingSync\Film::getRating
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetRating
     * @depends testGetRating
     */
    public function testGetRatingWithMultipleRatings()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $ratingJinni = new Rating(Constants::SOURCE_JINNI);
        $ratingImdb = new Rating(Constants::SOURCE_IMDB);
        $ratingJinni->setYourScore(7);
        $ratingImdb->setYourScore(6);
        $film->setRating($ratingJinni);
        $film->setRating($ratingImdb);
        $this->assertEquals(6, $film->getRating(Constants::SOURCE_IMDB)->getYourScore());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::getRating
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testGetRatingNeverSet()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $rating = $film->getRating(Constants::SOURCE_IMDB);
        $this->assertEquals(Constants::SOURCE_IMDB, $rating->getSource());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::getRating
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetRatingWithNull
     */
    public function testGetRatingWasSetNull()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setRating(null, Constants::SOURCE_IMDB);
        $rating = $film->getRating(Constants::SOURCE_IMDB);
        $this->assertEquals(Constants::SOURCE_IMDB, $rating->getSource());
        $this->assertNull($rating->getYourScore());
        $this->assertNull($rating->getSuggestedScore());
        $this->assertNull($rating->getCriticScore());
        $this->assertNull($rating->getUserScore());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::getRating
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetRatingWithEmpty
     */
    public function testGetRatingWasSetEmpty()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setRating("", Constants::SOURCE_IMDB);
        $rating = $film->getRating(Constants::SOURCE_IMDB);
        $this->assertEquals(Constants::SOURCE_IMDB, $rating->getSource());
        $this->assertNull($rating->getYourScore());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::getRating
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testGetRatingWithInvalidSource()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->getRating("Bad_Source");

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::setYourScore
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testSetYourScoreWithInvalidSource()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setYourScore("your_score", "Bad_Source");

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::setYourScore
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testSetYourScoreWithBadArg()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setYourScore("Bad_Score", Constants::SOURCE_IMDB);
    }

    /**
     * @covers  \RatingSync\Film::setYourScore
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetYourScoreWithNull()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setYourScore(null, Constants::SOURCE_IMDB);

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::setYourScore
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testSetYourScoreWithEmpty()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setYourScore("", Constants::SOURCE_IMDB);
    }

    /**
     * @covers  \RatingSync\Film::setYourScore
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetYourScore()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setYourScore(7, Constants::SOURCE_IMDB);

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::setYourScore
     * @covers  \RatingSync\Film::getYourScore
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetYourScore
     */
    public function testGetYourScore()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setYourScore(7, Constants::SOURCE_IMDB);
        $this->assertEquals(7, $film->getYourScore(Constants::SOURCE_IMDB));

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::getYourScore
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testGetYourScoreNeverSet()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $this->assertNull($film->getYourScore(Constants::SOURCE_IMDB));

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::getYourScore
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetYourScoreWithNull
     */
    public function testGetYourScoreWasSetNull()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setYourScore(null, Constants::SOURCE_IMDB);
        $this->assertNull($film->getYourScore(Constants::SOURCE_IMDB));

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::getYourScore
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testGetYourScoreWithInvalidSource()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->getYourScore("Bad_Source");
    }

    /**
     * @covers  \RatingSync\Film::setTitle
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetTitleWithNull()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setTitle(null);

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::setTitle
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetTitleWithEmpty()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setTitle("");

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::setTitle
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetTitle()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setTitle("New_Title");

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::setTitle
     * @covers  \RatingSync\Film::getTitle
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetTitle
     */
    public function testGetTitle()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setTitle("New_Title");
        $this->assertEquals("New_Title", $film->getTitle());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::getTitle
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testGetTitleNeverSet()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $this->assertNull($film->getTitle());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::getTitle
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetTitleWithNull
     */
    public function testGetTitleWasSetNull()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setTitle(null);
        $this->assertNull($film->getTitle());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::getTitle
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetTitleWithEmpty
     */
    public function testGetTitleWasSetEmpty()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setTitle("");
        $this->assertEquals("", $film->getTitle());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::setYear
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testSetYearWithBadArgFloat()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setYear(1999.5);
    }

    /**
     * @covers  \RatingSync\Film::setYear
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testSetYearWithBadArgStringCastToInt()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setYear("1999.5");
    }

    /**
     * @covers  \RatingSync\Film::setYear
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetYearWithNull()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setYear(null);

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::setYear
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetYearWithEmpty()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setYear("");

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::setYear
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetYearInt()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setYear(1942);

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::setYear
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetYearString()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setYear("1942");

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::setYear
     * @covers  \RatingSync\Film::getYear
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetYearInt
     */
    public function testGetYear()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setYear(1942);
        $this->assertEquals(1942, $film->getYear());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::setYear
     * @covers  \RatingSync\Film::getYear
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetYearString
     */
    public function testGetYearSetFromString()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setYear("1942");
        $this->assertEquals(1942, $film->getYear());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::getYear
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testGetYearNeverSet()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $this->assertNull($film->getYear());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::getYear
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetYearWithNull
     */
    public function testGetYearAfterYearWasSetNull()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setYear(null);
        $this->assertNull($film->getYear());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::getYear
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetYearWithEmpty
     */
    public function testGetYearWasSetEmpty()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setYear("");
        $this->assertNull($film->getYear());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::setContentType
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testSetContentTypeWithBadArg()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setContentType("Bad_ContentType");
    }

    /**
     * @covers  \RatingSync\Film::setContentType
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetContentTypeWithNull()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setContentType(null);

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::setContentType
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetContentTypeWithEmpty()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setContentType("");

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::setContentType
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetContentType()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setContentType(Film::CONTENT_FILM);

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::setContentType
     * @covers  \RatingSync\Film::getContentType
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetContentType
     */
    public function testGetContentType()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setContentType(Film::CONTENT_FILM);
        $this->assertEquals(Film::CONTENT_FILM, $film->getContentType());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::getContentType
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testGetContentTypeNeverSet()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $this->assertNull($film->getContentType());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::getContentType
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetContentTypeWithNull
     */
    public function testGetContentTypeWasSetNull()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setContentType(null);
        $this->assertNull($film->getContentType());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::getContentType
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetContentTypeWithEmpty
     */
    public function testGetContentTypeWasSetEmpty()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setContentType("");
        $this->assertNull($film->getContentType());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::setImage
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetImageWithNull()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setImage(null);

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::setImage
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetImageWithEmpty()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setImage("");

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::setImage
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetImage()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setImage("http://example.com/example.jpg");

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::setImage
     * @covers  \RatingSync\Film::getImage
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetImage
     */
    public function testGetImage()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setImage("http://example.com/example.jpg");
        $this->assertEquals("http://example.com/example.jpg", $film->getImage());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::getImage
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testGetImageNeverSet()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $this->assertNull($film->getImage());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::getImage
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetImageWithNull
     */
    public function testGetImageWasSetNull()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setImage(null);
        $this->assertNull($film->getImage());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::getImage
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetImageWithEmpty
     */
    public function testGetImageWasSetEmpty()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setImage("");
        $this->assertEquals("", $film->getImage());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::setImage
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetImageWithNullAndSource()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setImage(null, Constants::SOURCE_IMDB);

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::setImage
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetImageWithEmptyAndSource()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setImage("", Constants::SOURCE_IMDB);

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::setImage
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetImageAndSource()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setImage("http://example.com/example.jpg", Constants::SOURCE_IMDB);

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::setImage
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testSetImageAndInvalidSource()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setImage("http://example.com/example.jpg", "BAD SOURCE");
    }

    /**
     * @covers  \RatingSync\Film::setImage
     * @covers  \RatingSync\Film::getImage
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetImageAndSource
     */
    public function testGetImageAndSource()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setImage("http://example.com/example.jpg", Constants::SOURCE_IMDB);
        $this->assertEquals("http://example.com/example.jpg", $film->getImage(Constants::SOURCE_IMDB));

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::setImage
     * @covers  \RatingSync\Film::getImage
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testGetImageAndInvalidSource()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->getImage("BAD SOURCE");
    }

    /**
     * @covers  \RatingSync\Film::getImage
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testGetImageNeverSetAndSource()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $this->assertNull($film->getImage(Constants::SOURCE_IMDB));

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::getImage
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetImageWithNullAndSource
     */
    public function testGetImageWasSetNullAndSource()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setImage(null, Constants::SOURCE_IMDB);
        $this->assertNull($film->getImage(Constants::SOURCE_IMDB));

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::getImage
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetImageWithEmptyAndSource
     */
    public function testGetImageWasSetEmptyAndSource()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setImage("", Constants::SOURCE_IMDB);
        $this->assertEquals("", $film->getImage(Constants::SOURCE_IMDB));

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::addGenre
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testAddGenreWithNull()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addGenre(null);
    }

    /**
     * @covers  \RatingSync\Film::addGenre
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testAddGenreWithEmpty()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addGenre("");
    }

    /**
     * @covers  \RatingSync\Film::addGenre
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testAddGenre()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addGenre("Comedy");

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::addGenre
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testAddGenreAddSecondGenre()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addGenre("Comedy");
        $film->addGenre("Horror");

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::addGenre
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testAddGenreDuplicate()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addGenre("Comedy");
        $film->addGenre("Comedy");

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::addGenre
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testAddGenreMultiWithDuplicate()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addGenre("Comedy");
        $film->addGenre("Horror");
        $film->addGenre("Comedy");

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }    

    /**
     * @covers  \RatingSync\Film::addGenre
     * @covers  \RatingSync\Film::getGenres
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testAddGenre
     */
    public function testGetGenres()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addGenre("Comedy");
        $this->assertEquals(array('Comedy'), $film->getGenres());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::addGenre
     * @covers  \RatingSync\Film::getGenres
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testAddGenreAddSecondGenre
     */
    public function testGetGenresTwoGenres()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addGenre("Comedy");
        $film->addGenre("Horror");
        $this->assertEquals(array('Comedy', 'Horror'), $film->getGenres());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::addGenre
     * @covers  \RatingSync\Film::getGenres
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testAddGenreDuplicate
     */
    public function testGetGenresDuplicate()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addGenre("Comedy");
        $film->addGenre("Comedy");
        $this->assertEquals(array('Comedy'), $film->getGenres());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::addGenre
     * @covers  \RatingSync\Film::getGenres
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testAddGenreMultiWithDuplicate
     */
    public function testGetGenresMultiWithDuplicate()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addGenre("Comedy");
        $film->addGenre("Horror");
        $film->addGenre("Comedy");
        $this->assertEquals(array('Comedy', 'Horror'), $film->getGenres());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::addGenre
     * @covers  \RatingSync\Film::getGenres
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testAddGenre
     */
    public function testGetGenresThreeGenres()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addGenre("Comedy");
        $film->addGenre("Horror");
        $film->addGenre("Drama");
        $this->assertEquals(array('Comedy', 'Horror', 'Drama'), $film->getGenres());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::removeGenre
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testGetGenresThreeGenres
     */
    public function testRemoveGenre()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addGenre("Comedy");
        $film->addGenre("Horror");
        $film->addGenre("Drama");
        $film->removeGenre("Horror");
        $this->assertEquals(array('Comedy', 'Drama'), $film->getGenres());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::removeGenre
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testGetGenresThreeGenres
     */
    public function testRemoveGenreWithMissingGenre()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addGenre("Comedy");
        $film->addGenre("Horror");
        $film->addGenre("Drama");
        $film->removeGenre("Sci-Fi");
        $this->assertEquals(array('Comedy', 'Horror', 'Drama'), $film->getGenres());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::removeAllGenres
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testGetGenresThreeGenres
     */
    public function testRemoveAllGenres()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addGenre("Comedy");
        $film->addGenre("Horror");
        $film->addGenre("Drama");
        $film->removeAllGenres();
        $this->assertEmpty($film->getGenres());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::addGenre
     * @covers  \RatingSync\Film::isGenre
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testAddGenre
     */
    public function testIsGenreTrue()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addGenre("Comedy");
        $film->addGenre("Horror");
        $film->addGenre("Drama");
        $this->assertTrue($film->isGenre("Horror"));

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::addGenre
     * @covers  \RatingSync\Film::isGenre
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testAddGenre
     */
    public function testIsGenreFalse()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addGenre("Comedy");
        $film->addGenre("Horror");
        $film->addGenre("Drama");
        $this->assertFalse($film->isGenre("Sci-Fi"));

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::addGenre
     * @covers  \RatingSync\Film::removeAllGenres
     * @covers  \RatingSync\Film::isGenre
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testRemoveAllGenres
     * @depends testIsGenreTrue
     */
    public function testRemoveAllGenresThenAddOne()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addGenre("Comedy");
        $film->addGenre("Horror");
        $film->addGenre("Drama");
        $film->removeAllGenres();
        $film->addGenre("Comedy");
        $this->assertTrue($film->isGenre("Comedy"));

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::addDirector
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testAddDirectorWithNull()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addDirector(null);
    }

    /**
     * @covers  \RatingSync\Film::addDirector
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testAddDirectorWithEmpty()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addDirector("");
    }

    /**
     * @covers  \RatingSync\Film::addDirector
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testAddDirector()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addDirector("Georges Méliès");

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::addDirector
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testAddDirectorAddSecondDirector()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addDirector("Georges Méliès");
        $film->addDirector("Jennifer Lee");

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::addDirector
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testAddDirectorDuplicate()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addDirector("Jennifer Lee");
        $film->addDirector("Jennifer Lee");

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::addDirector
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testAddDirectorMultiWithDuplicate()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addDirector("Jennifer Lee");
        $film->addDirector("Christopher Nolan");
        $film->addDirector("Jennifer Lee");

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }    

    /**
     * @covers  \RatingSync\Film::addDirector
     * @covers  \RatingSync\Film::getDirectors
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testAddDirector
     */
    public function testGetDirectors()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addDirector("Christopher Nolan");
        $this->assertEquals(array('Christopher Nolan'), $film->getDirectors());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::addDirector
     * @covers  \RatingSync\Film::getDirectors
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testAddDirectorAddSecondDirector
     */
    public function testGetDirectorsTwoDirectors()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addDirector("Christopher Nolan");
        $film->addDirector("Jennifer Lee");
        $this->assertEquals(array('Christopher Nolan', 'Jennifer Lee'), $film->getDirectors());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::addDirector
     * @covers  \RatingSync\Film::getDirectors
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testAddDirectorDuplicate
     */
    public function testGetDirectorsDuplicate()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addDirector("Christopher Nolan");
        $film->addDirector("Christopher Nolan");
        $this->assertEquals(array('Christopher Nolan'), $film->getDirectors());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::addDirector
     * @covers  \RatingSync\Film::getDirectors
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testAddDirectorMultiWithDuplicate
     */
    public function testGetDirectorsMultiWithDuplicate()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addDirector("Christopher Nolan");
        $film->addDirector("Jennifer Lee");
        $film->addDirector("Christopher Nolan");
        $this->assertEquals(array('Christopher Nolan', 'Jennifer Lee'), $film->getDirectors());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::addDirector
     * @covers  \RatingSync\Film::getDirectors
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testAddDirector
     */
    public function testGetDirectorsThreeDirectors()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addDirector("Christopher Nolan");
        $film->addDirector("Jennifer Lee");
        $film->addDirector("Georges Méliès");
        $this->assertEquals(array('Christopher Nolan', 'Jennifer Lee', 'Georges Méliès'), $film->getDirectors());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::removeDirector
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testGetDirectorsThreeDirectors
     */
    public function testRemoveDirector()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addDirector("Christopher Nolan");
        $film->addDirector("Jennifer Lee");
        $film->addDirector("Georges Méliès");
        $film->removeDirector("Jennifer Lee");
        $this->assertEquals(array('Christopher Nolan', 'Georges Méliès'), $film->getDirectors());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::removeDirector
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testGetDirectorsThreeDirectors
     */
    public function testRemoveDirectorWithMissingDirector()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addDirector("Christopher Nolan");
        $film->addDirector("Jennifer Lee");
        $film->addDirector("Georges Méliès");
        $film->removeDirector("Steven Spielberg");
        $this->assertEquals(array('Christopher Nolan', 'Jennifer Lee', 'Georges Méliès'), $film->getDirectors());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::removeAllDirectors
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testGetDirectorsThreeDirectors
     */
    public function testRemoveAllDirectors()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addDirector("Christopher Nolan");
        $film->addDirector("Jennifer Lee");
        $film->addDirector("Georges Méliès");
        $film->removeAllDirectors();
        $this->assertEmpty($film->getDirectors());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::getDirectors
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testGetDirectorsNeverSet()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $this->assertCount(0, $film->getDirectors());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::getDirectors
     * @depends testAddDirector
     * @depends testRemoveAllDirectors
     */
    public function testGetDirectorsWithNoDirectors()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addDirector("Christopher Nolan");
        $film->removeAllDirectors();
        $this->assertCount(0, $film->getDirectors());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::addDirector
     * @covers  \RatingSync\Film::isDirector
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testAddDirector
     */
    public function testIsDirectorTrue()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addDirector("Christopher Nolan");
        $film->addDirector("Jennifer Lee");
        $film->addDirector("Georges Méliès");
        $this->assertTrue($film->isDirector("Jennifer Lee"));

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::addDirector
     * @covers  \RatingSync\Film::isDirector
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testAddDirector
     */
    public function testIsDirectorFalse()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addDirector("Christopher Nolan");
        $film->addDirector("Jennifer Lee");
        $film->addDirector("Georges Méliès");
        $this->assertFalse($film->isDirector("Steven Spielberg"));

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::addDirector
     * @covers  \RatingSync\Film::removeAllDirectors
     * @covers  \RatingSync\Film::isDirector
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testRemoveAllDirectors
     * @depends testIsDirectorTrue
     */
    public function testRemoveAllDirectorsThenAddOne()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addDirector("Christopher Nolan");
        $film->addDirector("Jennifer Lee");
        $film->addDirector("Georges Méliès");
        $film->removeAllDirectors();
        $film->addDirector("Christopher Nolan");
        $this->assertTrue($film->isDirector("Christopher Nolan"));

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::addXmlChild
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testAddXmlChildFromNullParam()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addXmlChild(null);
    }

    /**
     * @covers  \RatingSync\Film::addXmlChild
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testAddXmlChildFromString()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addXmlChild("Bad_Arg_As_A_String");
    }

    /**
     * @covers  \RatingSync\Film::addXmlChild
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testAddXmlChild()
    {
        // Basic test of this function
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setTitle("Film_Title");
        $xml = new \SimpleXMLElement("<films/>");
        $film->addXmlChild($xml);
        $xmlStr = "<?xml version=\"1.0\"?>\n";
        $xmlStr .= "<films><film title=\"Film_Title\"><title>Film_Title</title><year/><contentType/><image/><directors/><genres/></film></films>";
        $xmlStr .= "\n";
        $this->assertEquals($xmlStr, $xml->asXml());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::addXmlChild
     * @depends testAddXmlChild
     */
    public function testAddXmlChildWithEmptyFilmObject()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $xml = new \SimpleXMLElement("<films/>");
        $film->addXmlChild($xml);
        $xmlStr = "<?xml version=\"1.0\"?>\n";
        $xmlStr .= "<films><film title=\"\"><title/><year/><contentType/><image/><directors/><genres/></film></films>";
        $xmlStr .= "\n";
        $this->assertEquals($xmlStr, $xml->asXml());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::addXmlChild
     * @depends testAddXmlChild
     */
    public function testAddXmlChildWithAllFields()
    {
        // Film data
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setTitle("Frozen");
        $film->setYear(2013);
        $film->setContentType("FeatureFilm");
        $film->setImage("http://media.jinni.com/movie/frozen-2013/frozen-2013-5.jpeg");
        $film->addDirector("Chris Buck");
        $film->addGenre("Family");

        // Source data
        $film->setImage("http://media.jinni.com/movie/frozen-2013/frozen-2013-5.jpeg", Constants::SOURCE_JINNI);
        $film->setUrlName("frozen-2013", Constants::SOURCE_JINNI);
        $film->setFilmName("999", Constants::SOURCE_JINNI);

        // Rating data
        $rating = new Rating(Constants::SOURCE_JINNI);
        $rating->setYourScore(8);
        $rating->setYourRatingDate(\DateTime::createFromFormat("n/j/Y", "5/1/2015"));
        $rating->setSuggestedScore(7);
        $rating->setCriticScore(8);
        $rating->setUserScore(10);
        $film->setRating($rating, Constants::SOURCE_JINNI);

        $xml = new \SimpleXMLElement("<films/>");
        $film->addXmlChild($xml);
        $xmlStr = "<?xml version=\"1.0\"?>\n";
        $xmlStr .= "<films>";
        $xmlStr .= "<film title=\"Frozen\">";
        $xmlStr .=     "<title>Frozen</title>";
        $xmlStr .=     "<year>2013</year>";
        $xmlStr .=     "<contentType>FeatureFilm</contentType>";
        $xmlStr .=     "<image>http://media.jinni.com/movie/frozen-2013/frozen-2013-5.jpeg</image>";
        $xmlStr .=     "<directors><director>Chris Buck</director></directors>";
        $xmlStr .=     "<genres><genre>Family</genre></genres>";
        $xmlStr .=     "<source name=\"Jinni\">";
        $xmlStr .=         "<image>http://media.jinni.com/movie/frozen-2013/frozen-2013-5.jpeg</image>";
        $xmlStr .=         "<filmName>999</filmName>";
        $xmlStr .=         "<urlName>frozen-2013</urlName>";
        $xmlStr .=         "<rating>";
        $xmlStr .=             "<yourScore>8</yourScore>";
        $xmlStr .=             "<yourRatingDate>2015-5-1</yourRatingDate>";
        $xmlStr .=             "<suggestedScore>7</suggestedScore>";
        $xmlStr .=             "<criticScore>8</criticScore>";
        $xmlStr .=             "<userScore>10</userScore>";
        $xmlStr .=         "</rating>";
        $xmlStr .=     "</source>";
        $xmlStr .= "</film>";
        $xmlStr .= "</films>\n";
        $this->assertEquals($xmlStr, $xml->asXml());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::addXmlChild
     * @depends testAddXmlChildWithAllFields
     */
    public function testAddXmlChildWithMultipleRatings()
    {
        // Film data
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setTitle("Frozen");
        $film->setYear(2013);
        $film->setContentType("FeatureFilm");
        $film->setImage("http://media.jinni.com/movie/frozen-2013/frozen-2013-5.jpeg");
        $film->addDirector("Chris Buck");
        $film->addGenre("Family");

        // Jinni data
        $film->setImage("http://media.jinni.com/movie/frozen-2013/frozen-2013-5.jpeg", Constants::SOURCE_JINNI);
        $film->setFilmName("999", Constants::SOURCE_JINNI);
        $film->setUrlName("frozen-2013", Constants::SOURCE_JINNI);

        // Jinni Rating data
        $rating = new Rating(Constants::SOURCE_JINNI);
        $rating->setYourScore(8);
        $rating->setYourRatingDate(\DateTime::createFromFormat("n/j/Y", "5/1/2015"));
        $rating->setSuggestedScore(7);
        $rating->setCriticScore(8);
        $rating->setUserScore(10);
        $film->setRating($rating, Constants::SOURCE_JINNI);

        // IMDb data
        $film->setFilmName("tt2294629", Constants::SOURCE_IMDB);

        // IMDb Rating data
        $rating = new Rating(Constants::SOURCE_IMDB);
        $rating->setYourScore(4);
        $rating->setYourRatingDate(\DateTime::createFromFormat("n/j/Y", "5/4/2015"));
        $rating->setCriticScore(8);
        $rating->setUserScore(7);
        $film->setRating($rating, Constants::SOURCE_IMDB);

        $xml = new \SimpleXMLElement("<films/>");
        $film->addXmlChild($xml);
        $xmlStr = "<?xml version=\"1.0\"?>\n";
        $xmlStr .= "<films>";
        $xmlStr .= "<film title=\"Frozen\">";
        $xmlStr .=     "<title>Frozen</title>";
        $xmlStr .=     "<year>2013</year>";
        $xmlStr .=     "<contentType>FeatureFilm</contentType>";
        $xmlStr .=     "<image>http://media.jinni.com/movie/frozen-2013/frozen-2013-5.jpeg</image>";
        $xmlStr .=     "<directors><director>Chris Buck</director></directors>";
        $xmlStr .=     "<genres><genre>Family</genre></genres>";
        $xmlStr .=     "<source name=\"Jinni\">";
        $xmlStr .=         "<image>http://media.jinni.com/movie/frozen-2013/frozen-2013-5.jpeg</image>";
        $xmlStr .=         "<filmName>999</filmName>";
        $xmlStr .=         "<urlName>frozen-2013</urlName>";
        $xmlStr .=         "<rating>";
        $xmlStr .=             "<yourScore>8</yourScore>";
        $xmlStr .=             "<yourRatingDate>2015-5-1</yourRatingDate>";
        $xmlStr .=             "<suggestedScore>7</suggestedScore>";
        $xmlStr .=             "<criticScore>8</criticScore>";
        $xmlStr .=             "<userScore>10</userScore>";
        $xmlStr .=         "</rating>";
        $xmlStr .=     "</source>";
        $xmlStr .=     "<source name=\"IMDb\">";
        $xmlStr .=         "<image/>";
        $xmlStr .=         "<filmName>tt2294629</filmName>";
        $xmlStr .=         "<urlName/>";
        $xmlStr .=         "<rating>";
        $xmlStr .=             "<yourScore>4</yourScore>";
        $xmlStr .=             "<yourRatingDate>2015-5-4</yourRatingDate>";
        $xmlStr .=             "<suggestedScore/>";
        $xmlStr .=             "<criticScore>8</criticScore>";
        $xmlStr .=             "<userScore>7</userScore>";
        $xmlStr .=         "</rating>";
        $xmlStr .=     "</source>";
        $xmlStr .= "</film>";
        $xmlStr .= "</films>\n";
        $this->assertEquals($xmlStr, $xml->asXml());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::addXmlChild
     * @depends testAddXmlChild
     */
    public function testAddXmlChildWithMultipleGenres()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setTitle("Frozen");
        $film->addGenre("Family");
        $film->addGenre("Fantasy");

        $xml = new \SimpleXMLElement("<films/>");
        $film->addXmlChild($xml);
        $xmlStr = "<?xml version=\"1.0\"?>\n";
        $xmlStr .= "<films>";
        $xmlStr .= "<film title=\"Frozen\">";
        $xmlStr .=     "<title>Frozen</title>";
        $xmlStr .=     "<year/>";
        $xmlStr .=     "<contentType/>";
        $xmlStr .=     "<image/>";
        $xmlStr .=     "<directors/>";
        $xmlStr .=     "<genres><genre>Family</genre><genre>Fantasy</genre></genres>";
        $xmlStr .= "</film>";
        $xmlStr .= "</films>\n";
        $this->assertEquals($xmlStr, $xml->asXml());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::addXmlChild
     * @depends testAddXmlChild
     */
    public function testAddXmlChildWithMultipleDirectors()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setTitle("Frozen");
        $film->addDirector("Chris Buck");
        $film->addDirector("Jennifer Lee");

        $xml = new \SimpleXMLElement("<films/>");
        $film->addXmlChild($xml);
        $xmlStr = "<?xml version=\"1.0\"?>\n";
        $xmlStr .= "<films>";
        $xmlStr .= "<film title=\"Frozen\">";
        $xmlStr .=     "<title>Frozen</title>";
        $xmlStr .=     "<year/>";
        $xmlStr .=     "<contentType/>";
        $xmlStr .=     "<image/>";
        $xmlStr .=     "<directors><director>Chris Buck</director><director>Jennifer Lee</director></directors>";
        $xmlStr .=     "<genres/>";
        $xmlStr .= "</film>";
        $xmlStr .= "</films>\n";
        $this->assertEquals($xmlStr, $xml->asXml());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::createFromXml
     * @expectedException \InvalidArgumentException
     */
    public function testCreateFromXmlArgsNull()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        Film::createFromXml(null, null);
    }

    /**
     * @covers  \RatingSync\Film::createFromXml
     * @expectedException \InvalidArgumentException
     */
    public function testCreateFromXmlArgsFilmSxeNull()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        Film::createFromXml(null, new HttpChild(TEST_SITE_USERNAME));
    }

    /**
     * @covers  \RatingSync\Film::createFromXml
     * @expectedException \InvalidArgumentException
     */
    public function testCreateFromXmlArgsHttpNull()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        Film::createFromXml(new \SimpleXMLElement("<film><title>film_title</title></film>"), null);
    }

    /**
     * @covers  \RatingSync\Film::createFromXml
     * @expectedException \InvalidArgumentException
     */
    public function testCreateFromXmlArgsFilmSxeWrongType()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        Film::createFromXml("Bad_Type", new HttpChild(TEST_SITE_USERNAME));
    }

    /**
     * @covers  \RatingSync\Film::createFromXml
     * @expectedException \InvalidArgumentException
     */
    public function testCreateFromXmlArgsHttpWrongType()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        Film::createFromXml(new \SimpleXMLElement("<film><title>film_title</title></film>"), "Bad_Type");
    }

    /**
     * @covers  \RatingSync\Film::createFromXml
     * @expectedException \Exception
     */
    public function testCreateFromXmlArgsNoTitle()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        Film::createFromXml(new \SimpleXMLElement("<film><year>1900</year></film>"), new HttpChild(TEST_SITE_USERNAME));
    }

    /**
     * @covers  \RatingSync\Film::createFromXml
     */
    public function testCreateFromXmlArgsGood()
    {
        Film::createFromXml(new \SimpleXMLElement("<film><title>film_title</title></film>"), new HttpChild(TEST_SITE_USERNAME));

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Film::createFromXml
     * @depends testCreateFromXmlArgsGood
     */
    public function testCreateFromXml()
    {
        $filename =  __DIR__ . DIRECTORY_SEPARATOR . "testfile" . DIRECTORY_SEPARATOR . "input_ratings_site.xml";
        $xml = simplexml_load_file($filename);
        $xmlFilmArray = $xml->xpath('/films/film');

        // Title1
        $film = Film::createFromXml($xmlFilmArray[1], new HttpChild(TEST_SITE_USERNAME));
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
        $film = Film::createFromXml($xmlFilmArray[2], new HttpChild(TEST_SITE_USERNAME));
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
        $film = Film::createFromXml($xmlFilmArray[3], new HttpChild(TEST_SITE_USERNAME));
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
        $film = Film::createFromXml($xmlFilmArray[4], new HttpChild(TEST_SITE_USERNAME));
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
        $film = Film::createFromXml($xmlFilmArray[5], new HttpChild(TEST_SITE_USERNAME));
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
        $film = Film::createFromXml($xmlFilmArray[6], new HttpChild(TEST_SITE_USERNAME));
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
        $film = Film::createFromXml($xmlFilmArray[7], new HttpChild(TEST_SITE_USERNAME));
        $this->assertEquals("Wallace & Gromit: A Matter of Loaf and Déath", $film->getTitle(), "Title7 title");
        $this->assertEquals(array("Georges Méliès"), $film->getDirectors(), "Title7 directors");
        $this->assertEquals(array("Genre 1 & 1ès"), $film->getGenres(), "Title7 genres");

        // Frozen from All Sources
        $film = Film::createFromXml($xmlFilmArray[9], new HttpChild(TEST_SITE_USERNAME));
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
     * @covers  \RatingSync\Film::setTitle
     * @covers  \RatingSync\Film::addGenre
     * @covers  \RatingSync\Film::addDirector
     * @covers  \RatingSync\Film::getTitle
     * @covers  \RatingSync\Film::isGenre
     * @covers  \RatingSync\Film::isDirector
     * @covers  \RatingSync\Film::addXmlChild
     * @covers  \RatingSync\Film::createFromXml
     * @depends testSetTitle
     * @depends testAddGenre
     * @depends testAddDirector
     * @depends testGetTitle
     * @depends testIsGenreTrue
     * @depends testIsDirectorTrue
     * @depends testAddXmlChild
     * @depends testCreateFromXml
     */
    public function testStrangeCharactersInNames()
    {
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setTitle("Les Misérables & Gromit's");
        $film->addGenre("Sci-Fi");
        $film->addDirector("Georges Méliès");
        
        // Verify title, genre, director
        $this->assertEquals("Les Misérables & Gromit's", $film->getTitle(), "Title");
        $this->assertTrue($film->isGenre("Sci-Fi"), "Genre");
        $this->assertTrue($film->isDirector("Georges Méliès"), "Director");

        // Verify writing to XML
        $xml = new \SimpleXMLElement("<films/>");
        $film->addXmlChild($xml);
        $xmlStr = "<?xml version=\"1.0\"?>\n";
        $xmlStr .= "<films>";
        $xmlStr .= "<film title=\"Les Mis&#xE9;rables &amp; Gromit's\">";
        $xmlStr .=     "<title>Les Mis&#xE9;rables &amp; Gromit's</title>";
        $xmlStr .=     "<year/><contentType/><image/>";
        $xmlStr .=     "<directors><director>Georges M&#xE9;li&#xE8;s</director></directors>";
        $xmlStr .=     "<genres><genre>Sci-Fi</genre></genres>";
        $xmlStr .= "</film>";
        $xmlStr .= "</films>\n";
        $this->assertEquals($xmlStr, $xml->asXml(), "Writing to XML");

        // Verify reading from XML
        $xmlFilmArray = $xml->xpath('/films/film');
        $filmSxe = $xmlFilmArray[0];
        $readFilm = Film::createFromXml($filmSxe, new HttpChild(TEST_SITE_USERNAME));
        $this->assertEquals("Les Misérables & Gromit's", $readFilm->getTitle(), "Title read from XML");
        $this->assertTrue($readFilm->isGenre("Sci-Fi"), "Genre read from XML");
        $this->assertTrue($readFilm->isDirector("Georges Méliès"), "Director read from XML");

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }
}

?>
