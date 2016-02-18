<?php
/**
 * Film PHPUnit
 */
namespace RatingSync;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "Film.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "Rating.php";

require_once "SiteTest.php";
require_once "HttpChild.php";

class FilmTest extends \PHPUnit_Framework_TestCase
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
     * @covers \RatingSync\Film::__construct
     */
    public function testObjectCanBeConstructedFromHttp()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
    }

    /**
     * @covers \RatingSync\Film::validContentType
     */
    public function testValidContentTypeTrue()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->assertTrue(Film::validContentType(Film::CONTENT_FILM), Film::CONTENT_FILM . " should be valid");
        $this->assertFalse(Film::validContentType("Bad_Type"), "Bad_Type should be invalid");
    }

    /**
     * @covers  \RatingSync\Film::setFilmName
     * @covers  \RatingSync\Film::getFilmName
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetAndGetFilmName()
    {$this->start(__CLASS__, __FUNCTION__);

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
    }

    /**
     * @covers  \RatingSync\Film::getFilmName
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testFilmNameCanBeRetrievedFromNewObject()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $this->assertNull($film->getFilmName(Constants::SOURCE_IMDB));
    }

    /**
     * @covers  \RatingSync\Film::setUrlName
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testUrlNameCannotBeSetWithInvalidSource()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setUrlName("url_name", "Bad_Source");
    }

    /**
     * @covers  \RatingSync\Film::setUrlName
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testUrlNameCanBeSetWithNull()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setUrlName(null, Constants::SOURCE_IMDB);
    }

    /**
     * @covers  \RatingSync\Film::setUrlName
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testUrlNameCanBeSetWithEmpty()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setUrlName("", Constants::SOURCE_IMDB);
    }

    /**
     * @covers  \RatingSync\Film::setUrlName
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testUrlNameCanBeSetWithNonEmpty()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setUrlName("url_name", Constants::SOURCE_IMDB);
    }

    /**
     * @covers  \RatingSync\Film::getUrlName
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testUrlNameCannotBeGottenWithInvalidSource()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->getUrlName("Bad_Source");
    }

    /**
     * @covers  \RatingSync\Film::getUrlName
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testUrlNameCanBeSetWithNonEmpty
     */
    public function testGetUrlName()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setUrlName("url_name", Constants::SOURCE_IMDB);
        $this->assertEquals("url_name", $film->getUrlName(Constants::SOURCE_IMDB));
    }

    /**
     * @covers  \RatingSync\Film::getUrlName
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testGetUrlNameNeverSet()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $this->assertNull($film->getUrlName(Constants::SOURCE_IMDB));
    }

    /**
     * @covers  \RatingSync\Film::getUrlName
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testUrlNameCanBeSetWithNull
     */
    public function testGetNullUrlName()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setUrlName(null, Constants::SOURCE_IMDB);
        $this->assertNull($film->getUrlName(Constants::SOURCE_IMDB));
    }

    /**
     * @covers  \RatingSync\Film::setUrlName
     * @covers  \RatingSync\Film::getUrlName
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testUrlNameCanBeSetWithEmpty
     */
    public function testSetUrlNameWithEmptySetsToNull()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setUrlName("", Constants::SOURCE_IMDB);
        $this->assertNull($film->getUrlName(Constants::SOURCE_IMDB), "Setting empty URL name should be set to null");
    }

    /**
     * @covers  \RatingSync\Film::setRating
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testSetRatingWithInvalidSource()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setRating(null, "Bad_Source");
    }

    /**
     * @covers  \RatingSync\Film::setRating
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testSetRatingWithNullRatingNullSource()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setRating(null, "Bad_Source");
    }

    /**
     * @covers  \RatingSync\Film::setRating
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testSetRatingWithString()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setRating("Bad_Arg", Constants::SOURCE_IMDB);
    }

    /**
     * @covers  \RatingSync\Film::setRating
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testSetRatingWithNumber()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setRating(7, Constants::SOURCE_IMDB);
    }

    /**
     * @covers  \RatingSync\Film::setRating
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetRatingWithNull()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setRating(null, Constants::SOURCE_IMDB);
    }

    /**
     * @covers  \RatingSync\Film::setRating
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetRatingWithEmpty()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setRating("", Constants::SOURCE_IMDB);
    }

    /**
     * @covers  \RatingSync\Film::setRating
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetRating()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $rating = new Rating(Constants::SOURCE_IMDB);
        $film->setRating($rating, Constants::SOURCE_IMDB);
    }

    /**
     * @covers  \RatingSync\Film::setRating
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetRatingWithNoSource()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $rating = new Rating(Constants::SOURCE_IMDB);
        $film->setRating($rating);
    }

    /**
     * @covers  \RatingSync\Film::setRating
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \Exception
     */
    public function testSetRatingWithIncompatibleSource()
    {$this->start(__CLASS__, __FUNCTION__);

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
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $rating = new Rating(Constants::SOURCE_IMDB);
        $rating->setYourScore(6);
        $film->setRating($rating);
        $this->assertEquals(6, $film->getRating(Constants::SOURCE_IMDB)->getYourScore());
    }

    /**
     * @covers  \RatingSync\Film::setRating
     * @covers  \RatingSync\Film::getRating
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetRating
     * @depends testGetRating
     */
    public function testGetRatingWithMultipleRatings()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $ratingJinni = new Rating(Constants::SOURCE_JINNI);
        $ratingImdb = new Rating(Constants::SOURCE_IMDB);
        $ratingJinni->setYourScore(7);
        $ratingImdb->setYourScore(6);
        $film->setRating($ratingJinni);
        $film->setRating($ratingImdb);
        $this->assertEquals(6, $film->getRating(Constants::SOURCE_IMDB)->getYourScore());
    }

    /**
     * @covers  \RatingSync\Film::getRating
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testGetRatingNeverSet()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $rating = $film->getRating(Constants::SOURCE_IMDB);
        $this->assertEquals(Constants::SOURCE_IMDB, $rating->getSource());
    }

    /**
     * @covers  \RatingSync\Film::getRating
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetRatingWithNull
     */
    public function testGetRatingWasSetNull()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setRating(null, Constants::SOURCE_IMDB);
        $rating = $film->getRating(Constants::SOURCE_IMDB);
        $this->assertEquals(Constants::SOURCE_IMDB, $rating->getSource());
        $this->assertNull($rating->getYourScore());
        $this->assertNull($rating->getSuggestedScore());
        $this->assertNull($rating->getCriticScore());
        $this->assertNull($rating->getUserScore());
    }

    /**
     * @covers  \RatingSync\Film::getRating
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetRatingWithEmpty
     */
    public function testGetRatingWasSetEmpty()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setRating("", Constants::SOURCE_IMDB);
        $rating = $film->getRating(Constants::SOURCE_IMDB);
        $this->assertEquals(Constants::SOURCE_IMDB, $rating->getSource());
        $this->assertNull($rating->getYourScore());
    }

    /**
     * @covers  \RatingSync\Film::getRating
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testGetRatingWithInvalidSource()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->getRating("Bad_Source");
    }

    /**
     * @covers  \RatingSync\Film::setYourScore
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testSetYourScoreWithInvalidSource()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setYourScore("your_score", "Bad_Source");
    }

    /**
     * @covers  \RatingSync\Film::setYourScore
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testSetYourScoreWithBadArg()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setYourScore("Bad_Score", Constants::SOURCE_IMDB);
    }

    /**
     * @covers  \RatingSync\Film::setYourScore
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetYourScoreWithNull()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setYourScore(null, Constants::SOURCE_IMDB);
    }

    /**
     * @covers  \RatingSync\Film::setYourScore
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testSetYourScoreWithEmpty()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setYourScore("", Constants::SOURCE_IMDB);
    }

    /**
     * @covers  \RatingSync\Film::setYourScore
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetYourScore()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setYourScore(7, Constants::SOURCE_IMDB);
    }

    /**
     * @covers  \RatingSync\Film::setYourScore
     * @covers  \RatingSync\Film::getYourScore
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetYourScore
     */
    public function testGetYourScore()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setYourScore(7, Constants::SOURCE_IMDB);
        $this->assertEquals(7, $film->getYourScore(Constants::SOURCE_IMDB));
    }

    /**
     * @covers  \RatingSync\Film::getYourScore
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testGetYourScoreNeverSet()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $this->assertNull($film->getYourScore(Constants::SOURCE_IMDB));
    }

    /**
     * @covers  \RatingSync\Film::getYourScore
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetYourScoreWithNull
     */
    public function testGetYourScoreWasSetNull()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setYourScore(null, Constants::SOURCE_IMDB);
        $this->assertNull($film->getYourScore(Constants::SOURCE_IMDB));
    }

    /**
     * @covers  \RatingSync\Film::getYourScore
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testGetYourScoreWithInvalidSource()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->getYourScore("Bad_Source");
    }

    /**
     * @covers  \RatingSync\Film::setTitle
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetTitleWithNull()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setTitle(null);
    }

    /**
     * @covers  \RatingSync\Film::setTitle
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetTitleWithEmpty()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setTitle("");
    }

    /**
     * @covers  \RatingSync\Film::setTitle
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetTitle()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setTitle("New_Title");
    }

    /**
     * @covers  \RatingSync\Film::setTitle
     * @covers  \RatingSync\Film::getTitle
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetTitle
     */
    public function testGetTitle()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setTitle("New_Title");
        $this->assertEquals("New_Title", $film->getTitle());
    }

    /**
     * @covers  \RatingSync\Film::getTitle
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testGetTitleNeverSet()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $this->assertNull($film->getTitle());
    }

    /**
     * @covers  \RatingSync\Film::getTitle
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetTitleWithNull
     */
    public function testGetTitleWasSetNull()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setTitle(null);
        $this->assertNull($film->getTitle());
    }

    /**
     * @covers  \RatingSync\Film::getTitle
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetTitleWithEmpty
     */
    public function testGetTitleWasSetEmpty()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setTitle("");
        $this->assertEquals("", $film->getTitle());
    }

    /**
     * @covers  \RatingSync\Film::setYear
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testSetYearWithBadArgFloat()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setYear(1999.5);
    }

    /**
     * @covers  \RatingSync\Film::setYear
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testSetYearWithBadArgStringCastToInt()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setYear("1999.5");
    }

    /**
     * @covers  \RatingSync\Film::setYear
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetYearWithNull()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setYear(null);
    }

    /**
     * @covers  \RatingSync\Film::setYear
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetYearWithEmpty()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setYear("");
    }

    /**
     * @covers  \RatingSync\Film::setYear
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetYearInt()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setYear(1942);
    }

    /**
     * @covers  \RatingSync\Film::setYear
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetYearString()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setYear("1942");
    }

    /**
     * @covers  \RatingSync\Film::setYear
     * @covers  \RatingSync\Film::getYear
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetYearInt
     */
    public function testGetYear()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setYear(1942);
        $this->assertEquals(1942, $film->getYear());
    }

    /**
     * @covers  \RatingSync\Film::setYear
     * @covers  \RatingSync\Film::getYear
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetYearString
     */
    public function testGetYearSetFromString()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setYear("1942");
        $this->assertEquals(1942, $film->getYear());
    }

    /**
     * @covers  \RatingSync\Film::getYear
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testGetYearNeverSet()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $this->assertNull($film->getYear());
    }

    /**
     * @covers  \RatingSync\Film::getYear
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetYearWithNull
     */
    public function testGetYearAfterYearWasSetNull()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setYear(null);
        $this->assertNull($film->getYear());
    }

    /**
     * @covers  \RatingSync\Film::getYear
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetYearWithEmpty
     */
    public function testGetYearWasSetEmpty()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setYear("");
        $this->assertNull($film->getYear());
    }

    /**
     * @covers  \RatingSync\Film::setContentType
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testSetContentTypeWithBadArg()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setContentType("Bad_ContentType");
    }

    /**
     * @covers  \RatingSync\Film::setContentType
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetContentTypeWithNull()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setContentType(null);
    }

    /**
     * @covers  \RatingSync\Film::setContentType
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetContentTypeWithEmpty()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setContentType("");
    }

    /**
     * @covers  \RatingSync\Film::setContentType
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetContentType()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setContentType(Film::CONTENT_FILM);
    }

    /**
     * @covers  \RatingSync\Film::setContentType
     * @covers  \RatingSync\Film::getContentType
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetContentType
     */
    public function testGetContentType()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setContentType(Film::CONTENT_FILM);
        $this->assertEquals(Film::CONTENT_FILM, $film->getContentType());
    }

    /**
     * @covers  \RatingSync\Film::getContentType
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testGetContentTypeNeverSet()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $this->assertNull($film->getContentType());
    }

    /**
     * @covers  \RatingSync\Film::getContentType
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetContentTypeWithNull
     */
    public function testGetContentTypeWasSetNull()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setContentType(null);
        $this->assertNull($film->getContentType());
    }

    /**
     * @covers  \RatingSync\Film::getContentType
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetContentTypeWithEmpty
     */
    public function testGetContentTypeWasSetEmpty()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setContentType("");
        $this->assertNull($film->getContentType());
    }

    /**
     * @covers  \RatingSync\Film::setImage
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetImageWithNull()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setImage(null);
    }

    /**
     * @covers  \RatingSync\Film::setImage
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetImageWithEmpty()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setImage("");
    }

    /**
     * @covers  \RatingSync\Film::setImage
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetImage()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setImage("http://example.com/example.jpg");
    }

    /**
     * @covers  \RatingSync\Film::setImage
     * @covers  \RatingSync\Film::getImage
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetImage
     */
    public function testGetImage()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setImage("http://example.com/example.jpg");
        $this->assertEquals("http://example.com/example.jpg", $film->getImage());
    }

    /**
     * @covers  \RatingSync\Film::getImage
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testGetImageNeverSet()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $this->assertNull($film->getImage());
    }

    /**
     * @covers  \RatingSync\Film::getImage
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetImageWithNull
     */
    public function testGetImageWasSetNull()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setImage(null);
        $this->assertNull($film->getImage());
    }

    /**
     * @covers  \RatingSync\Film::getImage
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetImageWithEmpty
     */
    public function testGetImageWasSetEmpty()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setImage("");
        $this->assertEquals("", $film->getImage());
    }

    /**
     * @covers  \RatingSync\Film::setImage
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetImageWithNullAndSource()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setImage(null, Constants::SOURCE_IMDB);
    }

    /**
     * @covers  \RatingSync\Film::setImage
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetImageWithEmptyAndSource()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setImage("", Constants::SOURCE_IMDB);
    }

    /**
     * @covers  \RatingSync\Film::setImage
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testSetImageAndSource()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setImage("http://example.com/example.jpg", Constants::SOURCE_IMDB);
    }

    /**
     * @covers  \RatingSync\Film::setImage
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testSetImageAndInvalidSource()
    {$this->start(__CLASS__, __FUNCTION__);

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
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setImage("http://example.com/example.jpg", Constants::SOURCE_IMDB);
        $this->assertEquals("http://example.com/example.jpg", $film->getImage(Constants::SOURCE_IMDB));
    }

    /**
     * @covers  \RatingSync\Film::setImage
     * @covers  \RatingSync\Film::getImage
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testGetImageAndInvalidSource()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->getImage("BAD SOURCE");
    }

    /**
     * @covers  \RatingSync\Film::getImage
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testGetImageNeverSetAndSource()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $this->assertNull($film->getImage(Constants::SOURCE_IMDB));
    }

    /**
     * @covers  \RatingSync\Film::getImage
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetImageWithNullAndSource
     */
    public function testGetImageWasSetNullAndSource()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setImage(null, Constants::SOURCE_IMDB);
        $this->assertNull($film->getImage(Constants::SOURCE_IMDB));
    }

    /**
     * @covers  \RatingSync\Film::getImage
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testSetImageWithEmptyAndSource
     */
    public function testGetImageWasSetEmptyAndSource()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setImage("", Constants::SOURCE_IMDB);
        $this->assertEquals("", $film->getImage(Constants::SOURCE_IMDB));
    }

    /**
     * @covers  \RatingSync\Film::addGenre
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testAddGenreWithNull()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addGenre(null);
    }

    /**
     * @covers  \RatingSync\Film::addGenre
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testAddGenreWithEmpty()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addGenre("");
    }

    /**
     * @covers  \RatingSync\Film::addGenre
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testAddGenre()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addGenre("Comedy");
    }

    /**
     * @covers  \RatingSync\Film::addGenre
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testAddGenreAddSecondGenre()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addGenre("Comedy");
        $film->addGenre("Horror");
    }

    /**
     * @covers  \RatingSync\Film::addGenre
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testAddGenreDuplicate()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addGenre("Comedy");
        $film->addGenre("Comedy");
    }

    /**
     * @covers  \RatingSync\Film::addGenre
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testAddGenreMultiWithDuplicate()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addGenre("Comedy");
        $film->addGenre("Horror");
        $film->addGenre("Comedy");
    }    

    /**
     * @covers  \RatingSync\Film::addGenre
     * @covers  \RatingSync\Film::getGenres
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testAddGenre
     */
    public function testGetGenres()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addGenre("Comedy");
        $this->assertEquals(array('Comedy'), $film->getGenres());
    }

    /**
     * @covers  \RatingSync\Film::addGenre
     * @covers  \RatingSync\Film::getGenres
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testAddGenreAddSecondGenre
     */
    public function testGetGenresTwoGenres()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addGenre("Comedy");
        $film->addGenre("Horror");
        $this->assertEquals(array('Comedy', 'Horror'), $film->getGenres());
    }

    /**
     * @covers  \RatingSync\Film::addGenre
     * @covers  \RatingSync\Film::getGenres
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testAddGenreDuplicate
     */
    public function testGetGenresDuplicate()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addGenre("Comedy");
        $film->addGenre("Comedy");
        $this->assertEquals(array('Comedy'), $film->getGenres());
    }

    /**
     * @covers  \RatingSync\Film::addGenre
     * @covers  \RatingSync\Film::getGenres
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testAddGenreMultiWithDuplicate
     */
    public function testGetGenresMultiWithDuplicate()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addGenre("Comedy");
        $film->addGenre("Horror");
        $film->addGenre("Comedy");
        $this->assertEquals(array('Comedy', 'Horror'), $film->getGenres());
    }

    /**
     * @covers  \RatingSync\Film::addGenre
     * @covers  \RatingSync\Film::getGenres
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testAddGenre
     */
    public function testGetGenresThreeGenres()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addGenre("Comedy");
        $film->addGenre("Horror");
        $film->addGenre("Drama");
        $this->assertEquals(array('Comedy', 'Horror', 'Drama'), $film->getGenres());
    }

    /**
     * @covers  \RatingSync\Film::removeGenre
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testGetGenresThreeGenres
     */
    public function testRemoveGenre()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addGenre("Comedy");
        $film->addGenre("Horror");
        $film->addGenre("Drama");
        $film->removeGenre("Horror");
        $this->assertEquals(array('Comedy', 'Drama'), $film->getGenres());
    }

    /**
     * @covers  \RatingSync\Film::removeGenre
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testGetGenresThreeGenres
     */
    public function testRemoveGenreWithMissingGenre()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addGenre("Comedy");
        $film->addGenre("Horror");
        $film->addGenre("Drama");
        $film->removeGenre("Sci-Fi");
        $this->assertEquals(array('Comedy', 'Horror', 'Drama'), $film->getGenres());
    }

    /**
     * @covers  \RatingSync\Film::removeAllGenres
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testGetGenresThreeGenres
     */
    public function testRemoveAllGenres()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addGenre("Comedy");
        $film->addGenre("Horror");
        $film->addGenre("Drama");
        $film->removeAllGenres();
        $this->assertEmpty($film->getGenres());
    }

    /**
     * @covers  \RatingSync\Film::addGenre
     * @covers  \RatingSync\Film::isGenre
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testAddGenre
     */
    public function testIsGenreTrue()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addGenre("Comedy");
        $film->addGenre("Horror");
        $film->addGenre("Drama");
        $this->assertTrue($film->isGenre("Horror"));
    }

    /**
     * @covers  \RatingSync\Film::addGenre
     * @covers  \RatingSync\Film::isGenre
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testAddGenre
     */
    public function testIsGenreFalse()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addGenre("Comedy");
        $film->addGenre("Horror");
        $film->addGenre("Drama");
        $this->assertFalse($film->isGenre("Sci-Fi"));
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
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addGenre("Comedy");
        $film->addGenre("Horror");
        $film->addGenre("Drama");
        $film->removeAllGenres();
        $film->addGenre("Comedy");
        $this->assertTrue($film->isGenre("Comedy"));
    }

    /**
     * @covers  \RatingSync\Film::addDirector
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testAddDirectorWithNull()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addDirector(null);
    }

    /**
     * @covers  \RatingSync\Film::addDirector
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testAddDirectorWithEmpty()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addDirector("");
    }

    /**
     * @covers  \RatingSync\Film::addDirector
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testAddDirector()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addDirector("Georges Méliès");
    }

    /**
     * @covers  \RatingSync\Film::addDirector
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testAddDirectorAddSecondDirector()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addDirector("Georges Méliès");
        $film->addDirector("Jennifer Lee");
    }

    /**
     * @covers  \RatingSync\Film::addDirector
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testAddDirectorDuplicate()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addDirector("Jennifer Lee");
        $film->addDirector("Jennifer Lee");
    }

    /**
     * @covers  \RatingSync\Film::addDirector
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testAddDirectorMultiWithDuplicate()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addDirector("Jennifer Lee");
        $film->addDirector("Christopher Nolan");
        $film->addDirector("Jennifer Lee");
    }    

    /**
     * @covers  \RatingSync\Film::addDirector
     * @covers  \RatingSync\Film::getDirectors
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testAddDirector
     */
    public function testGetDirectors()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addDirector("Christopher Nolan");
        $this->assertEquals(array('Christopher Nolan'), $film->getDirectors());
    }

    /**
     * @covers  \RatingSync\Film::addDirector
     * @covers  \RatingSync\Film::getDirectors
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testAddDirectorAddSecondDirector
     */
    public function testGetDirectorsTwoDirectors()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addDirector("Christopher Nolan");
        $film->addDirector("Jennifer Lee");
        $this->assertEquals(array('Christopher Nolan', 'Jennifer Lee'), $film->getDirectors());
    }

    /**
     * @covers  \RatingSync\Film::addDirector
     * @covers  \RatingSync\Film::getDirectors
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testAddDirectorDuplicate
     */
    public function testGetDirectorsDuplicate()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addDirector("Christopher Nolan");
        $film->addDirector("Christopher Nolan");
        $this->assertEquals(array('Christopher Nolan'), $film->getDirectors());
    }

    /**
     * @covers  \RatingSync\Film::addDirector
     * @covers  \RatingSync\Film::getDirectors
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testAddDirectorMultiWithDuplicate
     */
    public function testGetDirectorsMultiWithDuplicate()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addDirector("Christopher Nolan");
        $film->addDirector("Jennifer Lee");
        $film->addDirector("Christopher Nolan");
        $this->assertEquals(array('Christopher Nolan', 'Jennifer Lee'), $film->getDirectors());
    }

    /**
     * @covers  \RatingSync\Film::addDirector
     * @covers  \RatingSync\Film::getDirectors
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testAddDirector
     */
    public function testGetDirectorsThreeDirectors()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addDirector("Christopher Nolan");
        $film->addDirector("Jennifer Lee");
        $film->addDirector("Georges Méliès");
        $this->assertEquals(array('Christopher Nolan', 'Jennifer Lee', 'Georges Méliès'), $film->getDirectors());
    }

    /**
     * @covers  \RatingSync\Film::removeDirector
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testGetDirectorsThreeDirectors
     */
    public function testRemoveDirector()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addDirector("Christopher Nolan");
        $film->addDirector("Jennifer Lee");
        $film->addDirector("Georges Méliès");
        $film->removeDirector("Jennifer Lee");
        $this->assertEquals(array('Christopher Nolan', 'Georges Méliès'), $film->getDirectors());
    }

    /**
     * @covers  \RatingSync\Film::removeDirector
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testGetDirectorsThreeDirectors
     */
    public function testRemoveDirectorWithMissingDirector()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addDirector("Christopher Nolan");
        $film->addDirector("Jennifer Lee");
        $film->addDirector("Georges Méliès");
        $film->removeDirector("Steven Spielberg");
        $this->assertEquals(array('Christopher Nolan', 'Jennifer Lee', 'Georges Méliès'), $film->getDirectors());
    }

    /**
     * @covers  \RatingSync\Film::removeAllDirectors
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testGetDirectorsThreeDirectors
     */
    public function testRemoveAllDirectors()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addDirector("Christopher Nolan");
        $film->addDirector("Jennifer Lee");
        $film->addDirector("Georges Méliès");
        $film->removeAllDirectors();
        $this->assertEmpty($film->getDirectors());
    }

    /**
     * @covers  \RatingSync\Film::getDirectors
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testGetDirectorsNeverSet()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $this->assertCount(0, $film->getDirectors());
    }

    /**
     * @covers  \RatingSync\Film::getDirectors
     * @depends testAddDirector
     * @depends testRemoveAllDirectors
     */
    public function testGetDirectorsWithNoDirectors()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addDirector("Christopher Nolan");
        $film->removeAllDirectors();
        $this->assertCount(0, $film->getDirectors());
    }

    /**
     * @covers  \RatingSync\Film::addDirector
     * @covers  \RatingSync\Film::isDirector
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testAddDirector
     */
    public function testIsDirectorTrue()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addDirector("Christopher Nolan");
        $film->addDirector("Jennifer Lee");
        $film->addDirector("Georges Méliès");
        $this->assertTrue($film->isDirector("Jennifer Lee"));
    }

    /**
     * @covers  \RatingSync\Film::addDirector
     * @covers  \RatingSync\Film::isDirector
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testAddDirector
     */
    public function testIsDirectorFalse()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addDirector("Christopher Nolan");
        $film->addDirector("Jennifer Lee");
        $film->addDirector("Georges Méliès");
        $this->assertFalse($film->isDirector("Steven Spielberg"));
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
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addDirector("Christopher Nolan");
        $film->addDirector("Jennifer Lee");
        $film->addDirector("Georges Méliès");
        $film->removeAllDirectors();
        $film->addDirector("Christopher Nolan");
        $this->assertTrue($film->isDirector("Christopher Nolan"));
    }

    /**
     * @covers  \RatingSync\Film::addXmlChild
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testAddXmlChildFromNullParam()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addXmlChild(null);
    }

    /**
     * @covers  \RatingSync\Film::addXmlChild
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testAddXmlChildFromString()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->addXmlChild("Bad_Arg_As_A_String");
    }

    /**
     * @covers  \RatingSync\Film::addXmlChild
     * @depends testObjectCanBeConstructedFromHttp
     */
    public function testAddXmlChild()
    {$this->start(__CLASS__, __FUNCTION__);

        // Basic test of this function
        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $film->setTitle("Film_Title");
        $xml = new \SimpleXMLElement("<films/>");
        $film->addXmlChild($xml);
        $xmlStr = "<?xml version=\"1.0\"?>\n";
        $xmlStr .= "<films><film title=\"Film_Title\"><title>Film_Title</title><year/><contentType/><image/><directors/><genres/></film></films>";
        $xmlStr .= "\n";
        $this->assertEquals($xmlStr, $xml->asXml());
    }

    /**
     * @covers  \RatingSync\Film::addXmlChild
     * @depends testAddXmlChild
     */
    public function testAddXmlChildWithEmptyFilmObject()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = new Film(new HttpChild(TEST_SITE_USERNAME));
        $xml = new \SimpleXMLElement("<films/>");
        $film->addXmlChild($xml);
        $xmlStr = "<?xml version=\"1.0\"?>\n";
        $xmlStr .= "<films><film title=\"\"><title/><year/><contentType/><image/><directors/><genres/></film></films>";
        $xmlStr .= "\n";
        $this->assertEquals($xmlStr, $xml->asXml());
    }

    /**
     * @covers  \RatingSync\Film::addXmlChild
     * @depends testAddXmlChild
     */
    public function testAddXmlChildWithAllFields()
    {$this->start(__CLASS__, __FUNCTION__);

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
    }

    /**
     * @covers  \RatingSync\Film::addXmlChild
     * @depends testAddXmlChildWithAllFields
     */
    public function testAddXmlChildWithMultipleRatings()
    {$this->start(__CLASS__, __FUNCTION__);

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
    }

    /**
     * @covers  \RatingSync\Film::addXmlChild
     * @depends testAddXmlChild
     */
    public function testAddXmlChildWithMultipleGenres()
    {$this->start(__CLASS__, __FUNCTION__);

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
    }

    /**
     * @covers  \RatingSync\Film::addXmlChild
     * @depends testAddXmlChild
     */
    public function testAddXmlChildWithMultipleDirectors()
    {$this->start(__CLASS__, __FUNCTION__);

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
    }

    /**
     * @covers  \RatingSync\Film::createFromXml
     * @expectedException \InvalidArgumentException
     */
    public function testCreateFromXmlArgsNull()
    {$this->start(__CLASS__, __FUNCTION__);

        Film::createFromXml(null, null);
    }

    /**
     * @covers  \RatingSync\Film::createFromXml
     * @expectedException \InvalidArgumentException
     */
    public function testCreateFromXmlArgsFilmSxeNull()
    {$this->start(__CLASS__, __FUNCTION__);

        Film::createFromXml(null, new HttpChild(TEST_SITE_USERNAME));
    }

    /**
     * @covers  \RatingSync\Film::createFromXml
     * @expectedException \InvalidArgumentException
     */
    public function testCreateFromXmlArgsHttpNull()
    {$this->start(__CLASS__, __FUNCTION__);

        Film::createFromXml(new \SimpleXMLElement("<film><title>film_title</title></film>"), null);
    }

    /**
     * @covers  \RatingSync\Film::createFromXml
     * @expectedException \InvalidArgumentException
     */
    public function testCreateFromXmlArgsFilmSxeWrongType()
    {$this->start(__CLASS__, __FUNCTION__);

        Film::createFromXml("Bad_Type", new HttpChild(TEST_SITE_USERNAME));
    }

    /**
     * @covers  \RatingSync\Film::createFromXml
     * @expectedException \InvalidArgumentException
     */
    public function testCreateFromXmlArgsHttpWrongType()
    {$this->start(__CLASS__, __FUNCTION__);

        Film::createFromXml(new \SimpleXMLElement("<film><title>film_title</title></film>"), "Bad_Type");
    }

    /**
     * @covers  \RatingSync\Film::createFromXml
     * @expectedException \Exception
     */
    public function testCreateFromXmlArgsNoTitle()
    {$this->start(__CLASS__, __FUNCTION__);

        Film::createFromXml(new \SimpleXMLElement("<film><year>1900</year></film>"), new HttpChild(TEST_SITE_USERNAME));
    }

    /**
     * @covers  \RatingSync\Film::createFromXml
     */
    public function testCreateFromXmlArgsGood()
    {$this->start(__CLASS__, __FUNCTION__);

        Film::createFromXml(new \SimpleXMLElement("<film><title>film_title</title></film>"), new HttpChild(TEST_SITE_USERNAME));
    }

    /**
     * @covers  \RatingSync\Film::createFromXml
     * @depends testCreateFromXmlArgsGood
     */
    public function testCreateFromXml()
    {$this->start(__CLASS__, __FUNCTION__);

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
    {$this->start(__CLASS__, __FUNCTION__);

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
    }

    /**
     * @covers  \RatingSync\Film::getFilmFromDb
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testGetFilmFromDbNullFilmId()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = Film::getFilmFromDb(null, new HttpChild(TEST_SITE_USERNAME));
    }

    /**
     * @covers  \RatingSync\Film::getFilmFromDb
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testGetFilmFromDbNonIntFilmId()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = Film::getFilmFromDb("string", new HttpChild(TEST_SITE_USERNAME));
    }

    /**
     * @covers  \RatingSync\Film::getFilmFromDb
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testGetFilmFromDbEmptyFilmId()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = Film::getFilmFromDb("", new HttpChild(TEST_SITE_USERNAME));
    }

    /**
     * @covers  \RatingSync\Film::getFilmFromDb
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testGetFilmFromDbNullHttp()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = Film::getFilmFromDb(1, null);
    }

    /**
     * @covers  \RatingSync\Film::getFilmFromDb
     * @depends testObjectCanBeConstructedFromHttp
     * @expectedException \InvalidArgumentException
     */
    public function testGetFilmFromDbNonObjectHttp()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = Film::getFilmFromDb(1, "string");
    }

    public function testResetDb()
    {$this->start(__CLASS__, __FUNCTION__);

        DatabaseTest::resetDb();
    }

    /**
     * @depends testResetDb
     */
    public function testSetupForGetFilmFromDb()
    {$this->start(__CLASS__, __FUNCTION__);

        $username_site = TEST_IMDB_USERNAME;
        $username_rs = Constants::TEST_RATINGSYNC_USERNAME;
        $filename =  __DIR__ . DIRECTORY_SEPARATOR . "testfile" . DIRECTORY_SEPARATOR . "input_ratings_site.xml";

        $site = new SiteChild($username_site);
        $site->importRatings(Constants::IMPORT_FORMAT_XML, $filename, $username_rs);
    }

    /**
     * @covers  \RatingSync\Film::getFilmFromDb
     * @depends testObjectCanBeConstructedFromHttp
     * @depends testResetDb
     */
    public function testGetFilmFromDbNoError()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = Film::getFilmFromDb(1, new HttpChild(Constants::TEST_RATINGSYNC_USERNAME), Constants::TEST_RATINGSYNC_USERNAME);
    }

    /**
     * @covers  \RatingSync\Film::getFilmFromDb
     * @depends testGetFilmFromDbNoError
     */
    public function testGetFilmFromDbBasicData()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = Film::getFilmFromDb(1, new HttpChild(Constants::TEST_RATINGSYNC_USERNAME), Constants::TEST_RATINGSYNC_USERNAME);

        $this->assertEquals("Frozen", $film->getTitle(), "Title");
        $this->assertEquals(2013, $film->getYear(), "Year");
        $this->assertEquals(Film::CONTENT_FILM, $film->getContentType(), "ContentType");
        $this->assertEquals(1, preg_match('@(frozen_rs_image)@', $film->getImage(), $matches), 'Image link');
    }

    /**
     * @covers  \RatingSync\Film::getFilmFromDb
     * @depends testGetFilmFromDbNoError
     */
    public function testGetFilmFromDbSourceData()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = Film::getFilmFromDb(7, new HttpChild(Constants::TEST_RATINGSYNC_USERNAME), Constants::TEST_RATINGSYNC_USERNAME);

        $sourceName = Constants::SOURCE_IMDB;
        $source = $film->getSource($sourceName);
        $urlName = $source->getUrlName();
        $filmName = $source->getFilmName();
        $image = $source->getImage();        
        $this->assertEquals("UrlName6_imdb", $urlName, "UrlName $sourceName");
        $this->assertEquals("FilmName6_imdb", $filmName, "FilmName $sourceName");
        $this->assertEquals(1, preg_match('@(title6_imdb_image)@', $image, $matches), "Image link $sourceName");

        $sourceName = Constants::SOURCE_JINNI;
        $source = $film->getSource($sourceName);
        $urlName = $source->getUrlName();
        $filmName = $source->getFilmName();
        $image = $source->getImage();        
        $this->assertEquals("UrlName6_jinni", $urlName, "UrlName $sourceName");
        $this->assertEquals("FilmName6_jinni", $filmName, "FilmName $sourceName");
        $this->assertEquals(1, preg_match('@(title6_jinni_image)@', $image, $matches), "Image link $sourceName");

        $sourceName = Constants::SOURCE_RATINGSYNC;
        $source = $film->getSource($sourceName);
        $urlName = $source->getUrlName();
        $filmName = $source->getFilmName();
        $image = $source->getImage();        
        $this->assertEquals("UrlName6_rs", $urlName, "UrlName $sourceName");
        $this->assertEquals("FilmName6_rs", $filmName, "FilmName $sourceName");
        $this->assertEquals(1, preg_match('@(title6_rs_image)@', $image, $matches), "Image link $sourceName");
    }

    /**
     * @covers  \RatingSync\Film::getFilmFromDb
     * @depends testGetFilmFromDbNoError
     */
    public function testGetFilmFromDbRatingData()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = Film::getFilmFromDb(7, new HttpChild(Constants::TEST_RATINGSYNC_USERNAME), Constants::TEST_RATINGSYNC_USERNAME);

        $sourceName = Constants::SOURCE_IMDB;
        $rating = $film->getRating($sourceName);
        $this->assertEquals(5, $rating->getYourScore(), "YourScore $sourceName");
        $this->assertEquals("2015-01-05", date_format($rating->getYourRatingDate(), "Y-m-d"), "YourRatingDate $sourceName");
        $this->assertEquals(6, $rating->getSuggestedScore(), "SuggestedScore $sourceName");
        $this->assertEquals(7, $rating->getCriticScore(), "CriticScore $sourceName");
        $this->assertEquals(8, $rating->getUserScore(), "UserScore $sourceName");

        $sourceName = Constants::SOURCE_JINNI;
        $rating = $film->getRating($sourceName);
        $this->assertEquals(4, $rating->getYourScore(), "YourScore $sourceName");
        $this->assertEquals("2015-01-04", date_format($rating->getYourRatingDate(), "Y-m-d"), "YourRatingDate $sourceName");
        $this->assertEquals(5, $rating->getSuggestedScore(), "SuggestedScore $sourceName");
        $this->assertEquals(6, $rating->getCriticScore(), "CriticScore $sourceName");
        $this->assertEquals(7, $rating->getUserScore(), "UserScore $sourceName");

        $sourceName = Constants::SOURCE_RATINGSYNC;
        $rating = $film->getRating($sourceName);
        $this->assertEquals(6, $rating->getYourScore(), "YourScore $sourceName");
        $this->assertEquals("2015-01-06", date_format($rating->getYourRatingDate(), "Y-m-d"), "YourRatingDate $sourceName");
        $this->assertEquals(7, $rating->getSuggestedScore(), "SuggestedScore $sourceName");
        $this->assertEquals(8, $rating->getCriticScore(), "CriticScore $sourceName");
        $this->assertEquals(9, $rating->getUserScore(), "UserScore $sourceName");
    }

    /**
     * @covers  \RatingSync\Film::getFilmFromDb
     * @depends testGetFilmFromDbNoError
     */
    public function testGetFilmFromDbDirectors()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = Film::getFilmFromDb(1, new HttpChild(Constants::TEST_RATINGSYNC_USERNAME), Constants::TEST_RATINGSYNC_USERNAME);
        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), "Frozen directors");
    }

    /**
     * @covers  \RatingSync\Film::getFilmFromDb
     * @depends testGetFilmFromDbNoError
     */
    public function testGetFilmFromDbGenres()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = Film::getFilmFromDb(1, new HttpChild(Constants::TEST_RATINGSYNC_USERNAME), Constants::TEST_RATINGSYNC_USERNAME);
        $this->assertEquals(array("Adventure", "Animation", "Comedy", "Family", "Fantasy", "Musical"), $film->getGenres(), "Frozen genres");
    }
    
    /**
     * @covers \RatingSync\Film::reconnectImage
     * @depends testSetupForGetFilmFromDb
     */
    public function testReconnectImage()
    {$this->start(__CLASS__, __FUNCTION__);

        $db = getDatabase();

        // Insert a new film and review with no image
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $film = new Film(new HttpChild($username));
        $film->setTitle("Zombeavers");
        $film->setYear(2014);
        $film->setContentType(Film::CONTENT_FILM);
        $rating = new Rating(Constants::SOURCE_RATINGSYNC);
        $rating->setYourScore(6);
        $rating->setYourRatingDate(new \DateTime('2015-6-6'));
        $film->setRating($rating, Constants::SOURCE_RATINGSYNC);
        $film->saveToDb($username);

        // Get a film id 
        $query = "SELECT id, image FROM film WHERE title='Zombeavers'";
        $result = $db->query($query);
        $row = $result->fetch_assoc();
        $filmId = $row['id'];
        
        // Delete the image from the db
        $this->assertTrue($db->query("UPDATE film SET image=NULL WHERE id=".$filmId), "Delete film image");
        $this->assertTrue($db->query("UPDATE film_source SET image=NULL WHERE film_id=".$filmId), "Delete film_source image");

        // Get a Film object from the db
        $http = new HttpImdb("empty_username");
        $film = Film::getFilmFromDb($filmId, $http);

        // Test
        $film->reconnectImage();

        // Verify
        $result = $db->query($query);
        $this->assertEquals(1, $result->num_rows, "There should be one result");
        $row = $result->fetch_assoc();
        $image = $row['image'];
        $this->assertEquals(1, preg_match('@(http://ia.media-imdb.com/images/M/MV5BNTMzMzc4ODc1M15BMl5BanBnXkFtZTgwMTM0MTgxMTE)@', $image, $matches), 'Image link');
    }
    
    /**
     * @covers \RatingSync\Film::reconnectFilmImages
     * @depends testReconnectImage
     */
    public function testReconnectFilmImages()
    {$this->start(__CLASS__, __FUNCTION__);

        $db = getDatabase();

        // Get a film ids
        $query = "SELECT id, image FROM film WHERE title='Frozen'";
        $result = $db->query($query);
        $row = $result->fetch_assoc();
        $filmId1 = intval($row['id']);
        $query = "SELECT id, image FROM film WHERE title='Zombeavers'";
        $result = $db->query($query);
        $row = $result->fetch_assoc();
        $filmId2 = intval($row['id']);
        
        // Delete images from the db
        $this->assertTrue($db->query("UPDATE film SET image=NULL WHERE id IN ($filmId1, $filmId2)"), "Delete film image");
        $this->assertTrue($db->query("UPDATE film_source SET image=NULL WHERE film_id IN ($filmId1, $filmId2)"), "Delete film_source image");

        // Test
        Film::reconnectFilmImages();

        // Verify

        $query = "SELECT id, image FROM film WHERE title='Frozen'";
        $result = $db->query($query);
        $this->assertEquals(1, $result->num_rows, "There should be one result");
        $row = $result->fetch_assoc();
        $image = $row['image'];
        $this->assertEquals(1, preg_match('@(http://ia.media-imdb.com/images/M/MV5BMTQ1MjQwMTE5OF5BMl5BanBnXkFtZTgwNjk3MTcyMDE)@', $image, $matches), 'Image link');

        $query = "SELECT id, image FROM film WHERE title='Zombeavers'";
        $result = $db->query($query);
        $this->assertEquals(1, $result->num_rows, "There should be one result");
        $row = $result->fetch_assoc();
        $image = $row['image'];
        $this->assertEquals(1, preg_match('@(http://ia.media-imdb.com/images/M/MV5BNTMzMzc4ODc1M15BMl5BanBnXkFtZTgwMTM0MTgxMTE)@', $image, $matches), 'Image link');
    }
}

?>
