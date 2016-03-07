<?php
/**
 * Rating PHPUnit
 */
namespace RatingSync;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "Rating.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "Constants.php";

require_once "SiteChild.php";
require_once "ImdbTest.php";
require_once "10DatabaseTest.php";

class RatingTest extends \PHPUnit_Framework_TestCase
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
     * @covers            \RatingSync\Rating::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testCannotBeConstructedFromNull()
    {$this->start(__CLASS__, __FUNCTION__);

        new Rating(null);
    }

    /**
     * @covers            \RatingSync\Rating::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testCannotBeConstructedFromInvalidSource()
    {$this->start(__CLASS__, __FUNCTION__);

        new Rating("Netflux");
    }

    /**
     * @covers \RatingSync\Rating::__construct
     */
    public function testObjectCanBeConstructedFromStringValue()
    {$this->start(__CLASS__, __FUNCTION__);

        $rating = new Rating("Jinni");
    }

    /**
     * @covers  \RatingSync\Rating::getSource
     * @depends testObjectCanBeConstructedFromStringValue
     */
    public function testSourceCanBeRetrieved()
    {$this->start(__CLASS__, __FUNCTION__);

        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $this->assertEquals(\RatingSync\Constants::SOURCE_IMDB, $r->getSource());
    }

    /**
     * @covers  \RatingSync\Rating::setYourScore
     * @covers  \RatingSync\Rating::getYourScore
     * @depends testObjectCanBeConstructedFromStringValue
     */
    public function testYourScoreCannotBeSetWithFloat()
    {$this->start(__CLASS__, __FUNCTION__);

        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $r->setYourScore(6.5);
        $this->assertEquals(6.5, $r->getYourScore());
    }

    /**
     * @covers  \RatingSync\Rating::setYourScore
     * @covers  \RatingSync\Rating::getYourScore
     * @depends testObjectCanBeConstructedFromStringValue
     */
    public function testYourScoreCannotBeSetWithFloatString()
    {$this->start(__CLASS__, __FUNCTION__);

        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $r->setYourScore("6.5");
        $this->assertEquals(6.5, $r->getYourScore());
    }

    /**
     * @covers  \RatingSync\Rating::setYourScore
     * @depends testObjectCanBeConstructedFromStringValue
     * @expectedException \InvalidArgumentException
     */
    public function testYourScoreCannotBeSetWithNonNumericalString()
    {$this->start(__CLASS__, __FUNCTION__);

        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $r->setYourScore("Not an int");
    }

    /**
     * @covers  \RatingSync\Rating::setYourScore
     * @depends testObjectCanBeConstructedFromStringValue
     * @expectedException \InvalidArgumentException
     */
    public function testYourScoreCannotBeSetWithNegative()
    {$this->start(__CLASS__, __FUNCTION__);

        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $r->setYourScore(-1);
    }

    /**
     * @covers  \RatingSync\Rating::setYourScore
     * @depends testObjectCanBeConstructedFromStringValue
     * @expectedException \InvalidArgumentException
     */
    public function testYourScoreCannotBeSetWithHigherThan10()
    {$this->start(__CLASS__, __FUNCTION__);

        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $r->setYourScore(11);
    }

    /**
     * @covers  \RatingSync\Rating::setYourScore
     * @covers  \RatingSync\Rating::getYourScore
     * @depends testObjectCanBeConstructedFromStringValue
     */
    public function testYourScoreCanBeSetWithInt()
    {$this->start(__CLASS__, __FUNCTION__);

        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $r->setYourScore(6);
        $this->assertEquals(6, $r->getYourScore());
    }

    /**
     * @covers  \RatingSync\Rating::setYourScore
     * @covers  \RatingSync\Rating::getYourScore
     * @depends testObjectCanBeConstructedFromStringValue
     */
    public function testYourScoreCanBeSetWithIntString()
    {$this->start(__CLASS__, __FUNCTION__);

        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $r->setYourScore("6");
        $this->assertEquals(6, $r->getYourScore());
    }

    /**
     * @covers  \RatingSync\Rating::setYourScore
     * @covers  \RatingSync\Rating::getYourScore
     * @depends testObjectCanBeConstructedFromStringValue
     */
    public function testYourScoreCanBeSetWithNull()
    {$this->start(__CLASS__, __FUNCTION__);

        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $r->setYourScore(null);
        $this->assertNull($r->getYourScore());
    }

    /**
     * @covers  \RatingSync\Rating::getYourScore
     * @depends testObjectCanBeConstructedFromStringValue
     */
    public function testYourScoreCanBeRetrievedFromNewObject()
    {$this->start(__CLASS__, __FUNCTION__);

        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $this->assertNull($r->getYourScore());
    }

    /**
     * @covers  \RatingSync\Rating::setYourRatingDate
     * @depends testObjectCanBeConstructedFromStringValue
     * @expectedException \InvalidArgumentException
     */
    public function testYourRatingDateCannotBeSetWithString()
    {$this->start(__CLASS__, __FUNCTION__);

        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $r->setYourRatingDate("10/12/2012");
    }

    /**
     * @covers  \RatingSync\Rating::setYourRatingDate
     * @covers  \RatingSync\Rating::getYourRatingDate
     * @depends testObjectCanBeConstructedFromStringValue
     */
    public function testYourRatingDateCanBeSetWithNull()
    {$this->start(__CLASS__, __FUNCTION__);

        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $r->setYourRatingDate(null);
        $this->assertNull($r->getYourRatingDate());
    }

    /**
     * @covers  \RatingSync\Rating::setYourRatingDate
     * @covers  \RatingSync\Rating::getYourRatingDate
     * @depends testObjectCanBeConstructedFromStringValue
     */
    public function testYourRatingDateCanBeSetWithDateObject()
    {$this->start(__CLASS__, __FUNCTION__);

        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $date = new \DateTime('2000-01-31');
        $r->setYourRatingDate($date);
        $this->assertEquals($date->getTimestamp(), $r->getYourRatingDate()->getTimestamp());
    }

    /**
     * @covers  \RatingSync\Rating::setSuggestedScore
     * @covers  \RatingSync\Rating::getSuggestedScore
     * @depends testObjectCanBeConstructedFromStringValue
     */
    public function testSuggestedScoreCannotBeSetWithFloat()
    {$this->start(__CLASS__, __FUNCTION__);

        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $r->setSuggestedScore(6.5);
        $this->assertEquals(6.5, $r->getSuggestedScore());
    }

    /**
     * @covers  \RatingSync\Rating::setSuggestedScore
     * @covers  \RatingSync\Rating::getSuggestedScore
     * @depends testObjectCanBeConstructedFromStringValue
     */
    public function testSuggestedScoreCannotBeSetWithFloatString()
    {$this->start(__CLASS__, __FUNCTION__);

        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $r->setSuggestedScore("6.5");
        $this->assertEquals(6.5, $r->getSuggestedScore());
    }

    /**
     * @covers  \RatingSync\Rating::setSuggestedScore
     * @depends testObjectCanBeConstructedFromStringValue
     * @expectedException \InvalidArgumentException
     */
    public function testSuggestedScoreCannotBeSetWithNonNumericalString()
    {$this->start(__CLASS__, __FUNCTION__);

        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $r->setSuggestedScore("Not an int");
    }

    /**
     * @covers  \RatingSync\Rating::setSuggestedScore
     * @depends testObjectCanBeConstructedFromStringValue
     * @expectedException \InvalidArgumentException
     */
    public function testSuggestedScoreCannotBeSetWithNegative()
    {$this->start(__CLASS__, __FUNCTION__);

        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $r->setSuggestedScore(-1);
    }

    /**
     * @covers  \RatingSync\Rating::setSuggestedScore
     * @depends testObjectCanBeConstructedFromStringValue
     * @expectedException \InvalidArgumentException
     */
    public function testSuggestedScoreCannotBeSetWithHigherThan10()
    {$this->start(__CLASS__, __FUNCTION__);

        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $r->setSuggestedScore(11);
    }

    /**
     * @covers  \RatingSync\Rating::setSuggestedScore
     * @covers  \RatingSync\Rating::getSuggestedScore
     * @depends testObjectCanBeConstructedFromStringValue
     */
    public function testSuggestedScoreCanBeSetWithInt()
    {$this->start(__CLASS__, __FUNCTION__);

        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $r->setSuggestedScore(6);
        $this->assertEquals(6, $r->getSuggestedScore());
    }

    /**
     * @covers  \RatingSync\Rating::setSuggestedScore
     * @covers  \RatingSync\Rating::getSuggestedScore
     * @depends testObjectCanBeConstructedFromStringValue
     */
    public function testSuggestedScoreCanBeSetWithIntString()
    {$this->start(__CLASS__, __FUNCTION__);

        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $r->setSuggestedScore("6");
        $this->assertEquals(6, $r->getSuggestedScore());
    }

    /**
     * @covers  \RatingSync\Rating::setSuggestedScore
     * @covers  \RatingSync\Rating::getSuggestedScore
     * @depends testObjectCanBeConstructedFromStringValue
     */
    public function testSuggestedScoreCanBeSetWithNull()
    {$this->start(__CLASS__, __FUNCTION__);

        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $r->setSuggestedScore(null);
        $this->assertNull($r->getSuggestedScore());
    }

    /**
     * @covers  \RatingSync\Rating::getSuggestedScore
     * @depends testObjectCanBeConstructedFromStringValue
     */
    public function testSuggestedScoreCanBeRetrievedFromNewObject()
    {$this->start(__CLASS__, __FUNCTION__);

        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $this->assertNull($r->getSuggestedScore());
    }

    /**
     * @covers  \RatingSync\Rating::setCriticScore
     * @covers  \RatingSync\Rating::getCriticScore
     * @depends testObjectCanBeConstructedFromStringValue
     */
    public function testCriticScoreCannotBeSetWithFloat()
    {$this->start(__CLASS__, __FUNCTION__);

        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $r->setCriticScore(6.5);
        $this->assertEquals(6.5, $r->getCriticScore());
    }

    /**
     * @covers  \RatingSync\Rating::setCriticScore
     * @covers  \RatingSync\Rating::getCriticScore
     * @depends testObjectCanBeConstructedFromStringValue
     */
    public function testCriticScoreCannotBeSetWithFloatString()
    {$this->start(__CLASS__, __FUNCTION__);

        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $r->setCriticScore("6.5");
        $this->assertEquals(6.5, $r->getCriticScore());
    }

    /**
     * @covers  \RatingSync\Rating::setCriticScore
     * @depends testObjectCanBeConstructedFromStringValue
     * @expectedException \InvalidArgumentException
     */
    public function testCriticScoreCannotBeSetWithNonNumericalString()
    {$this->start(__CLASS__, __FUNCTION__);

        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $r->setCriticScore("Not an int");
    }

    /**
     * @covers  \RatingSync\Rating::setCriticScore
     * @depends testObjectCanBeConstructedFromStringValue
     * @expectedException \InvalidArgumentException
     */
    public function testCriticScoreCannotBeSetWithNegative()
    {$this->start(__CLASS__, __FUNCTION__);

        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $r->setCriticScore(-1);
    }

    /**
     * @covers  \RatingSync\Rating::setCriticScore
     * @depends testObjectCanBeConstructedFromStringValue
     * @expectedException \InvalidArgumentException
     */
    public function testCriticScoreCannotBeSetWithHigherThan10()
    {$this->start(__CLASS__, __FUNCTION__);

        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $r->setCriticScore(11);
    }

    /**
     * @covers  \RatingSync\Rating::setCriticScore
     * @covers  \RatingSync\Rating::getCriticScore
     * @depends testObjectCanBeConstructedFromStringValue
     */
    public function testCriticScoreCanBeSetWithInt()
    {$this->start(__CLASS__, __FUNCTION__);

        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $r->setCriticScore(6);
        $this->assertEquals(6, $r->getCriticScore());
    }

    /**
     * @covers  \RatingSync\Rating::setCriticScore
     * @covers  \RatingSync\Rating::getCriticScore
     * @depends testObjectCanBeConstructedFromStringValue
     */
    public function testCriticScoreCanBeSetWithIntString()
    {$this->start(__CLASS__, __FUNCTION__);

        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $r->setCriticScore("6");
        $this->assertEquals(6, $r->getCriticScore());
    }

    /**
     * @covers  \RatingSync\Rating::setCriticScore
     * @covers  \RatingSync\Rating::getCriticScore
     * @depends testObjectCanBeConstructedFromStringValue
     */
    public function testCriticScoreCanBeSetWithNull()
    {$this->start(__CLASS__, __FUNCTION__);

        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $r->setCriticScore(null);
        $this->assertNull($r->getCriticScore());
    }

    /**
     * @covers  \RatingSync\Rating::getCriticScore
     * @depends testObjectCanBeConstructedFromStringValue
     */
    public function testCriticScoreCanBeRetrievedFromNewObject()
    {$this->start(__CLASS__, __FUNCTION__);

        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $this->assertNull($r->getCriticScore());
    }

    /**
     * @covers  \RatingSync\Rating::setUserScore
     * @covers  \RatingSync\Rating::getUserScore
     * @depends testObjectCanBeConstructedFromStringValue
     */
    public function testUserScoreCanBeSetWithFloat()
    {$this->start(__CLASS__, __FUNCTION__);

        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $r->setUserScore(6.5);
        $this->assertEquals(6.5, $r->getUserScore());
    }

    /**
     * @covers  \RatingSync\Rating::setUserScore
     * @covers  \RatingSync\Rating::getUserScore
     * @depends testObjectCanBeConstructedFromStringValue
     */
    public function testUserScoreCanBeSetWithFloatString()
    {$this->start(__CLASS__, __FUNCTION__);

        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $r->setUserScore("6.5");
        $this->assertEquals(6.5, $r->getUserScore());
    }

    /**
     * @covers  \RatingSync\Rating::setUserScore
     * @depends testObjectCanBeConstructedFromStringValue
     * @expectedException \InvalidArgumentException
     */
    public function testUserScoreCannotBeSetWithNonNumericalString()
    {$this->start(__CLASS__, __FUNCTION__);

        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $r->setUserScore("Not an int");
    }

    /**
     * @covers  \RatingSync\Rating::setUserScore
     * @depends testObjectCanBeConstructedFromStringValue
     * @expectedException \InvalidArgumentException
     */
    public function testUserScoreCannotBeSetWithNegative()
    {$this->start(__CLASS__, __FUNCTION__);

        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $r->setUserScore(-1);
    }

    /**
     * @covers  \RatingSync\Rating::setUserScore
     * @depends testObjectCanBeConstructedFromStringValue
     * @expectedException \InvalidArgumentException
     */
    public function testUserScoreCannotBeSetWithHigherThan10()
    {$this->start(__CLASS__, __FUNCTION__);

        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $r->setUserScore(11);
    }

    /**
     * @covers  \RatingSync\Rating::setUserScore
     * @covers  \RatingSync\Rating::getUserScore
     * @depends testObjectCanBeConstructedFromStringValue
     */
    public function testUserScoreCanBeSetWithInt()
    {$this->start(__CLASS__, __FUNCTION__);

        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $r->setUserScore(6);
        $this->assertEquals(6, $r->getUserScore());
    }

    /**
     * @covers  \RatingSync\Rating::setUserScore
     * @covers  \RatingSync\Rating::getUserScore
     * @depends testObjectCanBeConstructedFromStringValue
     */
    public function testUserScoreCanBeSetWithIntString()
    {$this->start(__CLASS__, __FUNCTION__);

        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $r->setUserScore("6");
        $this->assertEquals(6, $r->getUserScore());
    }

    /**
     * @covers  \RatingSync\Rating::setUserScore
     * @covers  \RatingSync\Rating::getUserScore
     * @depends testObjectCanBeConstructedFromStringValue
     */
    public function testUserScoreCanBeSetWithNull()
    {$this->start(__CLASS__, __FUNCTION__);

        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $r->setUserScore(null);
        $this->assertNull($r->getUserScore());
    }

    /**
     * @covers  \RatingSync\Rating::getUserScore
     * @depends testObjectCanBeConstructedFromStringValue
     */
    public function testUserScoreCanBeRetrievedFromNewObject()
    {$this->start(__CLASS__, __FUNCTION__);

        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $this->assertNull($r->getUserScore());
    }

    /**
     * @covers  \RatingSync\Rating::validRatingScore
     * @depends testObjectCanBeConstructedFromStringValue
     */
    public function testValidRatingScores()
    {$this->start(__CLASS__, __FUNCTION__);

        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        
        $this->assertFalse($r->validRatingScore("Not an int"), "Invalid - Not an int"); // Non-Numerical String
        $this->assertFalse($r->validRatingScore(-1), "Invalid - Negative"); // Negative
        $this->assertFalse($r->validRatingScore(11), "Invalid - Higher Than 10"); // Higher Than 10
        $this->assertFalse($r->validRatingScore(null), "Invalid - Null"); // Null
        
        $this->assertTrue($r->validRatingScore(0), "Valid - Zero"); // Zero
        $this->assertTrue($r->validRatingScore(1), "Valid - 1"); // Int in limit
        $this->assertTrue($r->validRatingScore(2), "Valid - 2"); // Int in limit
        $this->assertTrue($r->validRatingScore(3), "Valid - 3"); // Int in limit
        $this->assertTrue($r->validRatingScore(4), "Valid - 4"); // Int in limit
        $this->assertTrue($r->validRatingScore(5), "Valid - 5"); // Int in limit
        $this->assertTrue($r->validRatingScore(6), "Valid - 6"); // Int in limit
        $this->assertTrue($r->validRatingScore(7), "Valid - 7"); // Int in limit
        $this->assertTrue($r->validRatingScore(8), "Valid - 8"); // Int in limit
        $this->assertTrue($r->validRatingScore(9), "Valid - 9"); // Int in limit
        $this->assertTrue($r->validRatingScore(10), "Valid - 10"); // Int in limit
        $this->assertTrue($r->validRatingScore("1"), "Valid - '1'"); // String
        $this->assertTrue($r->validRatingScore("2"), "Valid - '2'"); // String
        $this->assertTrue($r->validRatingScore("3"), "Valid - '3'"); // String
        $this->assertTrue($r->validRatingScore("4"), "Valid - '4'"); // String
        $this->assertTrue($r->validRatingScore("5"), "Valid - '5'"); // String
        $this->assertTrue($r->validRatingScore("6"), "Valid - '6'"); // String
        $this->assertTrue($r->validRatingScore("7"), "Valid - '7'"); // String
        $this->assertTrue($r->validRatingScore("8"), "Valid - '8'"); // String
        $this->assertTrue($r->validRatingScore("9"), "Valid - '9'"); // String
        $this->assertTrue($r->validRatingScore("10"), "Valid - '10'"); // String
        $this->assertTrue($r->validRatingScore(6.5), "Valid - 6.5"); // Float
        $this->assertTrue($r->validRatingScore("6.5"), "Valid - '6.5'"); // Float  String
    }

    public function testResetDb()
    {$this->start(__CLASS__, __FUNCTION__);

        DatabaseTest::resetDb();
    }

    /**
     * @depends testResetDb
     */
    public function testSetupRatings()
    {$this->start(__CLASS__, __FUNCTION__);

        DatabaseTest::resetDb();
        $username_site = TEST_IMDB_USERNAME;
        $username_rs = Constants::TEST_RATINGSYNC_USERNAME;
        $site = new SiteChild($username_site);
        $filename =  __DIR__ . DIRECTORY_SEPARATOR . "testfile" . DIRECTORY_SEPARATOR . "input_ratings_site.xml";
        $films = $site->importRatings(Constants::IMPORT_FORMAT_XML, $filename, $username_rs);
    }

    /**
     * @covers  \RatingSync\Rating::initFromDbRow
     * @depends testObjectCanBeConstructedFromStringValue
     * @depends testSetupRatings
     */
    public function testInitFromDbRow()
    {$this->start(__CLASS__, __FUNCTION__);

        $db = getDatabase();
        $username_rs = Constants::TEST_RATINGSYNC_USERNAME;
        $filmId = 1;
        $sourceName = Constants::SOURCE_RATINGSYNC;
        $query = "SELECT * FROM rating" .
                 " WHERE film_id=$filmId" .
                   " AND source_name='$sourceName'" .
                   " AND user_name='$username_rs'";
        $result = $db->query($query);
        $this->assertEquals(1, $result->num_rows, "There sure be one Film/Source row $filmId/$sourceName");
        $row = $result->fetch_assoc();
        $rating = new Rating($sourceName);
        $rating->initFromDbRow($row);

        $this->assertEquals($sourceName, $rating->getSource(), "Source");
        $this->assertEquals(2, $rating->getYourScore(), "Your Score");
        $this->assertEquals("2015-01-02", date_format($rating->getYourRatingDate(), 'Y-m-d'), "Your Rating Date");
        $this->assertEquals(3, $rating->getSuggestedScore(), "Suggested Score");
        $this->assertEquals(4, $rating->getCriticScore(), "Critic Score");
        $this->assertEquals(5, $rating->getUserScore(), "User Score");
    }
    
    public function testAddFilms()
    {$this->start(__CLASS__, __FUNCTION__);
        // No test on this. Setup for other tests
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        
        $title = "Title Rating::testSaveToDb";
        $year = 2016;
        $http = new HttpImdb(TEST_IMDB_USERNAME);
        $film = new Film($http);
        $film->setTitle($title);
        $film->setYear($year);
        $film->setContentType(Film::CONTENT_FILM);
        $film->setImage("FilmImage");
        $film->addGenre("Comedy");
        $film->addDirector("Director");
        $this->assertTrue($film->saveToDb(), "Saving film $title");
        
        $title = "Title Rating::testSaveToDbEmptyData";
        $year = 2016;
        $http = new HttpImdb(TEST_IMDB_USERNAME);
        $film = new Film($http);
        $film->setTitle($title);
        $film->setYear($year);
        $film->setContentType(Film::CONTENT_FILM);
        $film->setImage("FilmImage");
        $film->addGenre("Comedy");
        $film->addDirector("Director");
        $this->assertTrue($film->saveToDb(), "Saving film $title");
        
        $title = "Title Rating::testSaveToDbOutsideSite";
        $year = 2016;
        $http = new HttpImdb(TEST_IMDB_USERNAME);
        $film = new Film($http);
        $film->setTitle($title);
        $film->setYear($year);
        $film->setContentType(Film::CONTENT_FILM);
        $film->setImage("FilmImage");
        $film->addGenre("Comedy");
        $film->addDirector("Director");
        $this->assertTrue($film->saveToDb(), "Saving film $title");
        
        $title = "Title Rating::testSaveToDbWithOtherUsernameAndOtherSource";
        $year = 2016;
        $http = new HttpImdb(TEST_IMDB_USERNAME);
        $film = new Film($http);
        $film->setTitle($title);
        $film->setYear($year);
        $film->setContentType(Film::CONTENT_FILM);
        $film->setImage("FilmImage");
        $film->addGenre("Comedy");
        $film->addDirector("Director");
        $rating = new Rating(Constants::SOURCE_IMDB);
        $rating->setYourScore(2);
        $ratingDate = new \DateTime('2015-11-30');
        $rating->setYourRatingDate($ratingDate);
        $rating->setSuggestedScore(2);
        $rating->setCriticScore(2);
        $rating->setUserScore(2);
        $film->setRating($rating, Constants::SOURCE_IMDB);
        $this->assertTrue($film->saveToDb($username), "Saving film $title");
        $db = getDatabase();
        $newUsername = "rs_user1";
        $this->assertTrue($db->query("REPLACE INTO user (username, password) VALUES ('$newUsername', 'password')"), "Insert user $newUsername");
        $film = new Film($http);
        $film->setTitle($title);
        $film->setYear($year);
        $film->setContentType(Film::CONTENT_FILM);
        $film->setImage("FilmImage");
        $film->addGenre("Comedy");
        $film->addDirector("Director");
        $rating = new Rating(Constants::SOURCE_RATINGSYNC);
        $rating->setYourScore(1);
        $ratingDate = new \DateTime('2015-12-01');
        $rating->setYourRatingDate($ratingDate);
        $rating->setSuggestedScore(1);
        $rating->setCriticScore(1);
        $rating->setUserScore(1);
        $film->setRating($rating, Constants::SOURCE_RATINGSYNC);
        $this->assertTrue($film->saveToDb($newUsername), "Saving film $title");
        
        $title = "Title Rating::testSaveToDbRatingExists";
        $year = 2016;
        $http = new HttpImdb(TEST_IMDB_USERNAME);
        $film = new Film($http);
        $film->setTitle($title);
        $film->setYear($year);
        $film->setContentType(Film::CONTENT_FILM);
        $film->setImage("FilmImage");
        $film->addGenre("Comedy");
        $film->addDirector("Director");
        $rating = new Rating(Constants::SOURCE_RATINGSYNC);
        $rating->setYourScore(1);
        $ratingDate = new \DateTime('2015-12-01');
        $rating->setYourRatingDate($ratingDate);
        $rating->setSuggestedScore(1);
        $rating->setCriticScore(1);
        $rating->setUserScore(1);
        $film->setRating($rating, Constants::SOURCE_RATINGSYNC);
        $this->assertTrue($film->saveToDb($username), "Saving film $title");
        
        $title = "Title Rating::testSaveToDbRatingExistsEmptyExistingDate";
        $year = 2016;
        $http = new HttpImdb(TEST_IMDB_USERNAME);
        $film = new Film($http);
        $film->setTitle($title);
        $film->setYear($year);
        $film->setContentType(Film::CONTENT_FILM);
        $film->setImage("FilmImage");
        $film->addGenre("Comedy");
        $film->addDirector("Director");
        $rating = new Rating(Constants::SOURCE_RATINGSYNC);
        $rating->setYourScore(1);
        $rating->setSuggestedScore(1);
        $rating->setCriticScore(1);
        $rating->setUserScore(1);
        $film->setRating($rating, Constants::SOURCE_RATINGSYNC);
        $this->assertTrue($film->saveToDb($username), "Saving film $title");
    }
    
    /**
     * @covers  \RatingSync\Rating::saveToDb
     * @expectedException \InvalidArgumentException
     */
    public function testSaveToDbEmptyUsername()
    {$this->start(__CLASS__, __FUNCTION__);

        // Get the new Film ID
        $db = getDatabase();
        $title = "Title Rating::testSaveToDb";
        $year = 2016;
        $query = "SELECT id FROM film WHERE title='$title' AND year=$year";
        $result = $db->query($query);
        $filmId = $result->fetch_assoc()['id'];
        
        // Test
        $rating = new Rating(Constants::SOURCE_RATINGSYNC);
        $rating->saveToDb("", $filmId);

        // Verify
        // Expect exception
    }
    
    /**
     * @covers  \RatingSync\Rating::saveToDb
     * @expectedException \InvalidArgumentException
     */
    public function testSaveToDbEmptyFilmId()
    {$this->start(__CLASS__, __FUNCTION__);
        
        // Test
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $rating = new Rating(Constants::SOURCE_RATINGSYNC);
        $rating->saveToDb($username, null);

        // Verify
        // Expect exception
    }
    
    /**
     * - $username bogus
     * - $filmId matches a film in the db
     * - include yourScore, yourRatingDate
     *
     * Expect
     *   - return false
     *
     * @covers  \RatingSync\Rating::saveToDb
     * @depends testAddFilms
     */
    public function testSaveToDbUserNotFound()
    {$this->start(__CLASS__, __FUNCTION__);

        // Get the new Film ID
        $db = getDatabase();
        $title = "Title Rating::testSaveToDb";
        $year = 2016;
        $query = "SELECT id FROM film WHERE title='$title' AND year=$year";
        $result = $db->query($query);
        $filmId = $result->fetch_assoc()['id'];
        
        // Test
        $username = "notfound_username";
        $rating = new Rating(Constants::SOURCE_RATINGSYNC);
        $success = $rating->saveToDb($username, $filmId);

        // Verify
        $this->assertFalse($success, "Should fail with no user");
    }
    
    /**
     * - $username matches a user in the db
     * - $filmId bogus
     * - include yourScore, yourRatingDate
     *
     * Expect
     *   - return false
     *
     * @covers  \RatingSync\Rating::saveToDb
     */
    public function testSaveToDbFilmNotFound()
    {$this->start(__CLASS__, __FUNCTION__);
        
        // Test
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $rating = new Rating(Constants::SOURCE_RATINGSYNC);
        $success = $rating->saveToDb($username, 1500);

        // Verify
        $this->assertFalse($success, "Should fail with no film found");
    }
    
    /**
     * - Set all rating data
     * - Source: RatingSync
     * - No ratings for this film in the db
     *
     * Expect
     *   - New rating row in db for this film/username/source combination
     *   - Verify the data
     *
     * @covers  \RatingSync\Rating::saveToDb
     * @depends testAddFilms
     */
    public function testSaveToDb()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $rating = new Rating(Constants::SOURCE_RATINGSYNC);
        $rating->setYourScore(5);
        $rating->setYourRatingDate(new \DateTime('2015-12-05'));
        $rating->setSuggestedScore(5);
        $rating->setCriticScore(5);
        $rating->setUserScore(5);

        // Get the new Film ID
        $db = getDatabase();
        $title = "Title Rating::testSaveToDb";
        $year = 2016;
        $query = "SELECT id FROM film WHERE title='$title' AND year=$year";
        $result = $db->query($query);
        $filmId = $result->fetch_assoc()['id'];

        
        // Test
        $success = $rating->saveToDb($username, $filmId);

        // Verify
        $this->assertTrue($success, "Rating::saveToDb should succeed");
        $query = "SELECT * FROM rating WHERE user_name='$username' AND film_id=$filmId AND source_name='".$rating->getSource()."'";
        $result = $db->query($query);
        $this->assertEquals(1, $result->num_rows);
        $dbRating = new Rating($rating->getSource());
        $dbRating->initFromDbRow($result->fetch_assoc());
        $this->assertEquals($rating->getYourScore(), $dbRating->getYourScore(), 'Your score');
        $this->assertEquals(date_format($rating->getYourRatingDate(), 'Y-m-d'), date_format($dbRating->getYourRatingDate(), 'Y-m-d'), "Your rating date");
        $this->assertEquals($rating->getSuggestedScore(), $dbRating->getSuggestedScore(), 'Your score');
        $this->assertEquals($rating->getCriticScore(), $dbRating->getCriticScore(), 'Your score');
        $this->assertEquals($rating->getUserScore(), $dbRating->getUserScore(), 'Your score');
    }
    
    /**
     * - Do not set rating data except source
     * - No ratings for this film/username/source in the db
     *
     * Expect
     *   - New rating row in db for this film/username/source combination
     *   - Verify the empty data
     *
     * @covers  \RatingSync\Rating::saveToDb
     * @depends testAddFilms
     */
    public function testSaveToDbEmptyData()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $rating = new Rating(Constants::SOURCE_RATINGSYNC);

        // Get the new Film ID
        $db = getDatabase();
        $title = "Title Rating::testSaveToDbEmptyData";
        $year = 2016;
        $query = "SELECT id FROM film WHERE title='$title' AND year=$year";
        $result = $db->query($query);
        $filmId = $result->fetch_assoc()['id'];
        
        // Test
        $success = $rating->saveToDb($username, $filmId);

        // Verify
        $this->assertTrue($success, "Rating::saveToDb should succeed");
        $query = "SELECT * FROM rating WHERE user_name='$username' AND film_id=$filmId AND source_name='".$rating->getSource()."'";
        $result = $db->query($query);
        $this->assertEquals(1, $result->num_rows);
        $dbRating = new Rating($rating->getSource());
        $dbRating->initFromDbRow($result->fetch_assoc());
        $this->assertEmpty($dbRating->getYourScore(), 'Your score');
        $this->assertEmpty($dbRating->getYourRatingDate(), "Your rating date");
        $this->assertEmpty($dbRating->getSuggestedScore(), 'Your score');
        $this->assertEmpty($dbRating->getCriticScore(), 'Your score');
        $this->assertEmpty($dbRating->getUserScore(), 'Your score');
    }
    
    /**
     * - Set all rating data
     * - Source: IMDb
     * - No ratings for this film in the db
     *
     * Expect
     *   - New rating row in db for this film/username/source combination
     *   - Verify the data
     *
     * @covers  \RatingSync\Rating::saveToDb
     * @depends testSaveToDb
     */
    public function testSaveToDbOutsideSite()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $rating = new Rating(Constants::SOURCE_IMDB);
        $rating->setYourScore(6);
        $rating->setYourRatingDate(new \DateTime('2015-12-06'));
        $rating->setSuggestedScore(6);
        $rating->setCriticScore(6);
        $rating->setUserScore(6);

        // Get the new Film ID
        $db = getDatabase();
        $title = "Title Rating::testSaveToDbOutsideSite";
        $year = 2016;
        $query = "SELECT id FROM film WHERE title='$title' AND year=$year";
        $result = $db->query($query);
        $filmId = $result->fetch_assoc()['id'];
        
        // Test
        $success = $rating->saveToDb($username, $filmId);

        // Verify
        $this->assertTrue($success, "Rating::saveToDb should succeed");
        $query = "SELECT * FROM rating WHERE user_name='$username' AND film_id=$filmId AND source_name='".$rating->getSource()."'";
        $result = $db->query($query);
        $this->assertEquals(1, $result->num_rows);
        $dbRating = new Rating($rating->getSource());
        $dbRating->initFromDbRow($result->fetch_assoc());
        $this->assertEquals($rating->getYourScore(), $dbRating->getYourScore(), 'Your score');
        $this->assertEquals(date_format($rating->getYourRatingDate(), 'Y-m-d'), date_format($dbRating->getYourRatingDate(), 'Y-m-d'), "Your rating date");
        $this->assertEquals($rating->getSuggestedScore(), $dbRating->getSuggestedScore(), 'Your score');
        $this->assertEquals($rating->getCriticScore(), $dbRating->getCriticScore(), 'Your score');
        $this->assertEquals($rating->getUserScore(), $dbRating->getUserScore(), 'Your score');
    }
    
    /**
     * - Set all rating data
     * - For this rating set yourRatingDate to "now"
     * - Source: RatingSync
     * - Existing rating in the db for this film and this username with another source (IMDb)
     * - Existing rating in the db for this film and this source with another username
     *
     * Expect
     *   - 3 rating rows in db for this film
     *   - Verify the data for the new rating ($username, RatingSync)
     *   - Verify the data for the other 2 ratings ($username, IMDb) and (other username, RatingSync)
     *
     * @covers  \RatingSync\Rating::saveToDb
     * @depends testSaveToDb
     */
    public function testSaveToDbWithOtherUsernameAndOtherSource()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $rating = new Rating(Constants::SOURCE_RATINGSYNC);
        $rating->setYourScore(5);
        $rating->setYourRatingDate(new \DateTime());
        $rating->setSuggestedScore(5);
        $rating->setCriticScore(5);
        $rating->setUserScore(5);

        // Get the new Film ID
        $db = getDatabase();
        $title = "Title Rating::testSaveToDbWithOtherUsernameAndOtherSource";
        $year = 2016;
        $query = "SELECT id FROM film WHERE title='$title' AND year=$year";
        $result = $db->query($query);
        $filmId = $result->fetch_assoc()['id'];
        
        // Test
        $success = $rating->saveToDb($username, $filmId);

        // Verify
        $this->assertTrue($success, "Rating::saveToDb should succeed");
        $query = "SELECT * FROM rating WHERE film_id=$filmId";
        $result = $db->query($query);
        $this->assertEquals(3, $result->num_rows, "Should be 3 ratings for this film");
        while ($row = $result->fetch_assoc()) {
            $dbRating = new Rating($row['source_name']);
            $dbRating->initFromDbRow($row);
            $dbSource = $dbRating->getSource();
            $dbUsername = $row['user_name'];
            $dbYourScore = $dbRating->getYourScore();
            $dbRatingDateStr = null;
            if (!empty($dbRating->getYourRatingDate())) {
                $dbRatingDateStr = date_format($dbRating->getYourRatingDate(), 'Y-m-d');
            }
            $dbSuggestedScore = $dbRating->getSuggestedScore();
            $dbCriticScore = $dbRating->getCriticScore();
            $dbUserScore = $dbRating->getUserScore();

            if ($dbSource == $rating->getSource()) {
                if ($dbUsername == $username) {
                    $this->assertEquals($rating->getYourScore(), $dbYourScore, 'Your score');
                    $this->assertEquals(date_format($rating->getYourRatingDate(), 'Y-m-d'), $dbRatingDateStr, "Your rating date");
                    $this->assertEquals($rating->getSuggestedScore(), $dbSuggestedScore, 'Your score');
                    $this->assertEquals($rating->getCriticScore(), $dbCriticScore, 'Your score');
                    $this->assertEquals($rating->getUserScore(), $dbUserScore, 'Your score');
                }
                elseif ($dbUsername == "rs_user1") {
                    $this->assertEquals(1, $dbYourScore, 'Your score');
                    $this->assertEquals('2015-12-01', $dbRatingDateStr, "Your rating date");
                    $this->assertEquals(1, $dbSuggestedScore, 'Your score');
                    $this->assertEquals(1, $dbCriticScore, 'Your score');
                    $this->assertEquals(1, $dbUserScore, 'Your score');
                }
            }
            elseif ($dbSource == Constants::SOURCE_IMDB) {
                $this->assertEquals(2, $dbYourScore, 'Your score');
                $this->assertEquals('2015-11-30', $dbRatingDateStr, "Your rating date");
                $this->assertEquals(2, $dbSuggestedScore, 'Your score');
                $this->assertEquals(2, $dbCriticScore, 'Your score');
                $this->assertEquals(2, $dbUserScore, 'Your score');
            }
        }
    }
    
    /**
     * - Existing rating in the db for this $username/$film/source
     * - Existing rating has yourRatingDate in the past (not empty)
     * - Source: RatingSync
     * - Set all rating data
     * - For this rating set yourRatingDate newer than the existing one
     * - Different yourScore than the rating in the db
     *
     * Expect
     *   - Verify the new data for the rating in the db ($username/$film/source)
     *   - Verify the old data for archived rating
     *
     * @covers  \RatingSync\Rating::saveToDb
     * @depends testSaveToDb
     */
    public function testSaveToDbRatingExists()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $rating = new Rating(Constants::SOURCE_RATINGSYNC);
        $rating->setYourScore(5);
        $rating->setYourRatingDate(new \DateTime());
        $rating->setSuggestedScore(5);
        $rating->setCriticScore(5);
        $rating->setUserScore(5);

        // Get the new Film ID
        $db = getDatabase();
        $title = "Title Rating::testSaveToDbRatingExists";
        $year = 2016;
        $query = "SELECT id FROM film WHERE title='$title' AND year=$year";
        $result = $db->query($query);
        $filmId = $result->fetch_assoc()['id'];
        
        // Test
        $success = $rating->saveToDb($username, $filmId);

        // Verify
        $this->assertTrue($success, "Rating::saveToDb should succeed");
        $query = "SELECT * FROM rating WHERE user_name='$username' AND film_id=$filmId AND source_name='".$rating->getSource()."'";
        $result = $db->query($query);
        $this->assertEquals(1, $result->num_rows);
        $dbRating = new Rating($rating->getSource());
        $dbRating->initFromDbRow($result->fetch_assoc());
        $this->assertEquals($rating->getYourScore(), $dbRating->getYourScore(), 'Your score');
        $this->assertEquals(date_format($rating->getYourRatingDate(), 'Y-m-d'), date_format($dbRating->getYourRatingDate(), 'Y-m-d'), "Your rating date");
        $this->assertEquals($rating->getSuggestedScore(), $dbRating->getSuggestedScore(), 'Your score');
        $this->assertEquals($rating->getCriticScore(), $dbRating->getCriticScore(), 'Your score');
        $this->assertEquals($rating->getUserScore(), $dbRating->getUserScore(), 'Your score');
        
        $query = "SELECT * FROM rating_archive WHERE user_name='$username' AND film_id=$filmId AND source_name='".$rating->getSource()."'" .
                   " AND yourRatingDate='2015-12-01'";
        $result = $db->query($query);
        $this->assertEquals(1, $result->num_rows);
        $dbRating = new Rating($rating->getSource());
        $dbRating->initFromDbRow($result->fetch_assoc());
        $this->assertEquals(1, $dbRating->getYourScore(), 'Your score');
        $this->assertEquals(1, $dbRating->getSuggestedScore(), 'Your score');
        $this->assertEquals(1, $dbRating->getCriticScore(), 'Your score');
        $this->assertEquals(1, $dbRating->getUserScore(), 'Your score');
    }
    
    /**
     * - Existing rating in the db for this $username/$film/source
     * - Existing rating has yourRatingDate in the past (not empty)
     * - Set rating source
     * - For this rating set yourRatingDate newer than the one set by testSaveToDbRatingExists
     * - Leave all other data empty
     *
     * Expect
     *   - Verify rating and archive tables both have the same data
     *   - Verify the data is from existing 
     *
     * @covers  \RatingSync\Rating::saveToDb
     * @depends testSaveToDbRatingExists
     */
    public function testSaveToDbRatingExistsEmptyData()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $sourceName = Constants::SOURCE_IMDB;
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $rating = new Rating($sourceName);
        $rating->setYourRatingDate(new \DateTime());

        // Get the new Film ID
        $db = getDatabase();
        $title = "Title Rating::testSaveToDbOutsideSite";
        $year = 2016;
        $query = "SELECT id FROM film WHERE title='$title' AND year=$year";
        $result = $db->query($query);
        $filmId = $result->fetch_assoc()['id'];
        
        // Test
        $success = $rating->saveToDb($username, $filmId);

        // Verify
        $this->assertTrue($success, "Rating::saveToDb should succeed");
        $query = "SELECT * FROM rating WHERE user_name='$username' AND film_id=$filmId AND source_name='".$rating->getSource()."'";
        $result = $db->query($query);
        $this->assertEquals(1, $result->num_rows);
        $dbRating = new Rating($rating->getSource());
        $dbRating->initFromDbRow($result->fetch_assoc());
        $this->assertEquals(6, $dbRating->getYourScore(), 'Your score');
        $this->assertEquals("2015-12-06", date_format($dbRating->getYourRatingDate(), 'Y-m-d'), "Your rating date");
        $this->assertEquals(6, $dbRating->getSuggestedScore(), 'Suggested score');
        $this->assertEquals(6, $dbRating->getCriticScore(), 'Critic score');
        $this->assertEquals(6, $dbRating->getUserScore(), 'User score');
        
        $query = "SELECT * FROM rating_archive WHERE user_name='$username' AND film_id=$filmId AND source_name='".$rating->getSource()."'" .
                   " AND yourRatingDate='".date_format($rating->getYourRatingDate(), 'Y-m-d')."'";
        $result = $db->query($query);
        $this->assertEquals(1, $result->num_rows);
        $dbArchiveRating = new Rating($rating->getSource());
        $dbArchiveRating->initFromDbRow($result->fetch_assoc());
        $this->assertEquals($dbRating->getYourScore(), $dbArchiveRating->getYourScore(), 'Your score (archived)');
        $this->assertEquals(date_format($dbRating->getYourRatingDate(), 'Y-m-d'), date_format($dbArchiveRating->getYourRatingDate(), 'Y-m-d'), "Your rating date");
        $this->assertEquals($dbRating->getSuggestedScore(), $dbArchiveRating->getSuggestedScore(), 'Suggested score (archived)');
        $this->assertEquals($dbRating->getCriticScore(), $dbArchiveRating->getCriticScore(), 'Critic score (archived)');
        $this->assertEquals($dbRating->getUserScore(), $dbArchiveRating->getUserScore(), 'User score (archived)');
    }
    
    /**
     * - Existing rating in the db for this $username/$film/source
     * - Existing rating has empty yourRatingDate
     * - Set all rating data
     * - For this rating set yourRatingDate to the past
     *
     * Expect
     *   - Rating table have the new data
     *   - Archive have the existing data (yourRatingDate is NULL)
     *
     * @covers  \RatingSync\Rating::saveToDb
     * @depends testSaveToDbRatingExists
     */
    public function testSaveToDbRatingExistsEmptyExistingDate()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $rating = new Rating(Constants::SOURCE_RATINGSYNC);
        $rating->setYourScore(5);
        $rating->setYourRatingDate(new \DateTime('2015-12-05'));
        $rating->setSuggestedScore(5);
        $rating->setCriticScore(5);
        $rating->setUserScore(5);

        // Get the new Film ID
        $db = getDatabase();
        $title = "Title Rating::testSaveToDbRatingExistsEmptyExistingDate";
        $year = 2016;
        $query = "SELECT id FROM film WHERE title='$title' AND year=$year";
        $result = $db->query($query);
        $filmId = $result->fetch_assoc()['id'];
        
        // Test
        $success = $rating->saveToDb($username, $filmId);

        // Verify
        $this->assertTrue($success, "Rating::saveToDb should succeed");
        $query = "SELECT * FROM rating WHERE user_name='$username' AND film_id=$filmId AND source_name='".$rating->getSource()."'";
        $result = $db->query($query);
        $this->assertEquals(1, $result->num_rows);
        $dbRating = new Rating($rating->getSource());
        $dbRating->initFromDbRow($result->fetch_assoc());
        $this->assertEquals($rating->getYourScore(), $dbRating->getYourScore(), 'Your score');
        $this->assertEquals(date_format($rating->getYourRatingDate(), 'Y-m-d'), date_format($dbRating->getYourRatingDate(), 'Y-m-d'), "Your rating date");
        $this->assertEquals($rating->getSuggestedScore(), $dbRating->getSuggestedScore(), 'Your score');
        $this->assertEquals($rating->getCriticScore(), $dbRating->getCriticScore(), 'Your score');
        $this->assertEquals($rating->getUserScore(), $dbRating->getUserScore(), 'Your score');
        
        $query = "SELECT * FROM rating_archive WHERE user_name='$username' AND film_id=$filmId AND source_name='".$rating->getSource()."'" .
                   " AND yourRatingDate IS NULL";
        $result = $db->query($query);
        $this->assertEquals(1, $result->num_rows);
        $dbArchiveRating = new Rating($rating->getSource());
        $dbArchiveRating->initFromDbRow($result->fetch_assoc());
        $this->assertEquals(1, $dbArchiveRating->getYourScore(), 'Your score');
        $this->assertEmpty($dbArchiveRating->getYourRatingDate(), 'Your rating date');
        $this->assertEquals(1, $dbArchiveRating->getSuggestedScore(), 'Suggested score');
        $this->assertEquals(1, $dbArchiveRating->getCriticScore(), 'Critic score');
        $this->assertEquals(1, $dbArchiveRating->getUserScore(), 'User score');
    }
    
    /**
     * - Existing rating in the db for this $username/$film/source
     * - Set all rating data
     * - For this rating set yourRatingDate older than the existing rating
     * - Different yourScore than the existing rating
     *
     * Expect
     *   - Verify the existing data for the rating in the db ($username/$film/source)
     *   - Verify the new data for archived rating
     *
     * @covers  \RatingSync\Rating::saveToDb
     * @depends testSaveToDbRatingExists
     */
    public function testSaveToDbRatingExistsWithNewerDate()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $rating = new Rating(Constants::SOURCE_RATINGSYNC);
        $rating->setYourScore(7);
        $rating->setYourRatingDate(new \DateTime('2015-06-07'));
        $rating->setSuggestedScore(7);
        $rating->setCriticScore(7);
        $rating->setUserScore(7);

        // Get the new Film ID
        $db = getDatabase();
        $title = "Title Rating::testSaveToDbRatingExistsEmptyExistingDate";
        $year = 2016;
        $query = "SELECT id FROM film WHERE title='$title' AND year=$year";
        $result = $db->query($query);
        $filmId = $result->fetch_assoc()['id'];
        
        // Test
        $success = $rating->saveToDb($username, $filmId);

        // Verify
        $this->assertTrue($success, "Rating::saveToDb should succeed");
        $query = "SELECT * FROM rating WHERE user_name='$username' AND film_id=$filmId AND source_name='".$rating->getSource()."'";
        $result = $db->query($query);
        $this->assertEquals(1, $result->num_rows);
        $dbRating = new Rating($rating->getSource());
        $dbRating->initFromDbRow($result->fetch_assoc());
        $this->assertEquals(5, $dbRating->getYourScore(), 'Your score');
        $this->assertEquals("2015-12-05", date_format($dbRating->getYourRatingDate(), 'Y-m-d'), "Your rating date");
        $this->assertEquals(5, $dbRating->getSuggestedScore(), 'Suggested score');
        $this->assertEquals(5, $dbRating->getCriticScore(), 'Critic score');
        $this->assertEquals(5, $dbRating->getUserScore(), 'User score');
        
        $query = "SELECT * FROM rating_archive WHERE user_name='$username' AND film_id=$filmId AND source_name='".$rating->getSource()."'" .
                   " AND yourRatingDate='".date_format($rating->getYourRatingDate(), 'Y-m-d')."' ORDER BY ts LIMIT 1";
        $result = $db->query($query);
        $this->assertEquals(1, $result->num_rows);
        $dbArchiveRating = new Rating($rating->getSource());
        $dbArchiveRating->initFromDbRow($result->fetch_assoc());
        $this->assertEquals($rating->getYourScore(), $dbArchiveRating->getYourScore(), 'Your score');
        $this->assertEquals(date_format($rating->getYourRatingDate(), 'Y-m-d'), date_format($dbArchiveRating->getYourRatingDate(), 'Y-m-d'), "Your rating date");
        $this->assertEquals($rating->getSuggestedScore(), $dbArchiveRating->getSuggestedScore(), 'Suggested score');
        $this->assertEquals($rating->getCriticScore(), $dbArchiveRating->getCriticScore(), 'Critic score');
        $this->assertEquals($rating->getUserScore(), $dbArchiveRating->getUserScore(), 'User score');
    }
    
    /**
     * - Existing rating in the db for this $username/$film/source
     * - Existing rating has yourRatingDate in the past (not empty)
     * - Set all rating data
     * - For this rating set yourScore to same as the existing rating
     * - For this rating set yourRatingDate newer than the existing rating
     *
     * Expect
     *   - Verify the new data for the rating in the db ($username/$film/source)
     *   - Verify new rating in the db has yourRatingDate from the existing rating
     *   - Verify the old data for archived rating
     *
     * @covers  \RatingSync\Rating::saveToDb
     * @depends testSaveToDb
     */
    public function testSaveToDbRatingExistsSameScore()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $rating = new Rating(Constants::SOURCE_RATINGSYNC);
        $rating->setYourScore(5);
        $rating->setYourRatingDate(new \DateTime('2015-12-08'));
        $rating->setSuggestedScore(8);
        $rating->setCriticScore(8);
        $rating->setUserScore(8);

        // Get the new Film ID
        $db = getDatabase();
        $title = "Title Rating::testSaveToDbRatingExistsEmptyExistingDate";
        $year = 2016;
        $query = "SELECT id FROM film WHERE title='$title' AND year=$year";
        $result = $db->query($query);
        $filmId = $result->fetch_assoc()['id'];
        
        // Test
        $success = $rating->saveToDb($username, $filmId);

        // Verify
        $this->assertTrue($success, "Rating::saveToDb should succeed");
        $query = "SELECT * FROM rating WHERE user_name='$username' AND film_id=$filmId AND source_name='".$rating->getSource()."'";
        $result = $db->query($query);
        $this->assertEquals(1, $result->num_rows);
        $dbRating = new Rating($rating->getSource());
        $dbRating->initFromDbRow($result->fetch_assoc());
        $this->assertEquals($rating->getYourScore(), $dbRating->getYourScore(), 'Your score');
        $this->assertEquals("2015-12-05", date_format($dbRating->getYourRatingDate(), 'Y-m-d'), "Your rating date");
        $this->assertEquals($rating->getSuggestedScore(), $dbRating->getSuggestedScore(), 'Suggested score');
        $this->assertEquals($rating->getCriticScore(), $dbRating->getCriticScore(), 'Critic score');
        $this->assertEquals($rating->getUserScore(), $dbRating->getUserScore(), 'User score');
        
        $query = "SELECT * FROM rating_archive WHERE user_name='$username' AND film_id=$filmId AND source_name='".$rating->getSource()."'" .
                   " AND yourRatingDate='".date_format($rating->getYourRatingDate(), 'Y-m-d')."' ORDER BY ts LIMIT 1";
        $result = $db->query($query);
        $this->assertEquals(1, $result->num_rows);
        $dbArchiveRating = new Rating($rating->getSource());
        $dbArchiveRating->initFromDbRow($result->fetch_assoc());
        $this->assertEquals(5, $dbArchiveRating->getYourScore(), 'Your score');
        $this->assertEquals("2015-12-05", date_format($dbArchiveRating->getYourRatingDate(), 'Y-m-d'), "Your rating date");
        $this->assertEquals(5, $dbArchiveRating->getSuggestedScore(), 'Your score');
        $this->assertEquals(5, $dbArchiveRating->getCriticScore(), 'Your score');
        $this->assertEquals(5, $dbArchiveRating->getUserScore(), 'Your score');
    }

    /**
     * @covers  \RatingSync\Rating::saveToRs
     * @expectedException \InvalidArgumentException
     */
    public function testSaveToRsSetNulls()
    {$this->start(__CLASS__, __FUNCTION__);

        $rating = new Rating(Constants::SOURCE_RATINGSYNC);
        $rating->saveToRs(null, null);
    }

    /**
     * @covers  \RatingSync\Rating::saveToRs
     * @expectedException \InvalidArgumentException
     */
    public function testSaveToRsSetNullUsername()
    {$this->start(__CLASS__, __FUNCTION__);

        $rating = new Rating(Constants::SOURCE_RATINGSYNC);
        $filmId = 1;
        $rating->saveToRs(null, $filmId);
    }

    /**
     * @covers  \RatingSync\Rating::saveToRs
     * @expectedException \InvalidArgumentException
     */
    public function testSaveToRsSetNullFilmId()
    {$this->start(__CLASS__, __FUNCTION__);

        $rating = new Rating(Constants::SOURCE_RATINGSYNC);
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $rating->saveToRs($username, null);
    }

    /**
     * @covers  \RatingSync\Rating::saveToRs
     * @depends testObjectCanBeConstructedFromStringValue
     * @depends testSetupRatings
     */
    public function testSaveToRsUserNotFound()
    {$this->start(__CLASS__, __FUNCTION__);

        $rating = new Rating(Constants::SOURCE_RATINGSYNC);
        $username = "Garbage Username";
        $filmId = 1;
        $success = $rating->saveToRs($username, $filmId);

        $this->assertFalse($success, "Should fail with no user found");
    }

    /**
     * @covers  \RatingSync\Rating::saveToDb
     * @depends testObjectCanBeConstructedFromStringValue
     * @depends testSetupRatings
     * @expectedException \Exception
     */
    public function testSaveToRsFilmNotFound()
    {$this->start(__CLASS__, __FUNCTION__);

        $rating = new Rating(Constants::SOURCE_RATINGSYNC);
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $filmId = -1;
        $rating->saveToRs($username, $filmId);
    }

    /**
     * @depends testObjectCanBeConstructedFromStringValue
     * @depends testSetupRatings
     */
    public function testAddRatings()
    {$this->start(__CLASS__, __FUNCTION__);
        // No test on this. Setup for other tests
        
        $title = "Title testSaveToRsNewRsRatingFromAnotherSource";
        $year = 2016;
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $http = new HttpImdb(TEST_IMDB_USERNAME);
        $film = new Film($http);
        $film->setTitle($title);
        $film->setYear($year);
        $film->setContentType(Film::CONTENT_FILM);
        $film->setImage("FilmImage");
        $film->addGenre("Comedy");
        $film->addDirector("Director");
        $rating = new Rating(Constants::SOURCE_IMDB);
        $rating->setYourScore(1);
        $ratingDate = new \DateTime('2015-11-30');
        $rating->setYourRatingDate($ratingDate);
        $film->setRating($rating, Constants::SOURCE_IMDB);
        $film->saveToDb($username);

        $title = "Title testSaveToRsNoRatingDateOriginal";
        $film = new Film($http);
        $film->setTitle($title);
        $film->setYear($year);
        $film->setContentType(Film::CONTENT_FILM);
        $film->setImage("FilmImage");
        $film->addGenre("Comedy");
        $film->addDirector("Director");
        $rating = new Rating(Constants::SOURCE_IMDB);
        $rating->setYourScore(1);
        $film->setRating($rating, Constants::SOURCE_IMDB);
        $film->saveToDb($username);

        $title = "Title testSaveToRsNoRatingDateBoth";
        $film = new Film($http);
        $film->setTitle($title);
        $film->setYear($year);
        $film->setContentType(Film::CONTENT_FILM);
        $film->setImage("FilmImage");
        $film->addGenre("Comedy");
        $film->addDirector("Director");
        $rating = new Rating(Constants::SOURCE_IMDB);
        $rating->setYourScore(1);
        $film->setRating($rating, Constants::SOURCE_IMDB);
        $film->saveToDb($username);
    }

    /**
     * @covers  \RatingSync\Rating::saveToRs
     * @depends testObjectCanBeConstructedFromStringValue
     * @depends testAddRatings
     * @depends testInitFromDbRow
     */
    public function testSaveToRsNewRsRatingFromAnotherSource()
    {$this->start(__CLASS__, __FUNCTION__);

        // Get the new Film ID
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $title = "Title testSaveToRsNewRsRatingFromAnotherSource";
        $year = 2016;
        $db = getDatabase();
        $query = "SELECT id FROM film WHERE title='$title' AND year=$year";
        $result = $db->query($query);
        $filmId = $result->fetch_assoc()['id'];
        $this->assertGreaterThan(0, $filmId, "Film ID should be found (greater than 0)");

        // Get the rating in the db from another source (IMDb)
        $query = "SELECT * FROM rating WHERE user_name='$username' AND film_id=$filmId AND source_name='".Constants::SOURCE_IMDB."'";
        $result = $db->query($query);
        $row = $result->fetch_assoc();
        $rating = new Rating(Constants::SOURCE_IMDB);
        $rating->initFromDbRow($row);

        // Copy the new IMDb rating to a RS rating
        $rating->saveToRs($username, $filmId);

        $query = "SELECT * FROM rating WHERE user_name='$username' AND film_id=$filmId AND source_name='".Constants::SOURCE_RATINGSYNC."'";
        $result = $db->query($query);
        $this->assertEquals(1, $result->num_rows);
        $row = $result->fetch_assoc();
        $this->assertEquals(1, $row['yourScore'], 'Your score');
        $this->assertEquals('2015-11-30', $row['yourRatingDate'], "Your rating date");
    }

    /**
     * @covers  \RatingSync\Rating::saveToRs
     * @depends testSetupRatings
     * @depends testSaveToRsNewRsRatingFromAnotherSource
     */
    public function testSaveToRsNewRsRatingFromRs()
    {$this->start(__CLASS__, __FUNCTION__);

        // Get the new Film ID
        $db = getDatabase();
        $title = "Title testSaveToRsNewRsRatingFromAnotherSource";
        $year = 2016;
        $query = "SELECT id FROM film WHERE title='$title' AND year=$year";
        $result = $db->query($query);
        $filmId = $result->fetch_assoc()['id'];

        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $rating = new Rating(Constants::SOURCE_RATINGSYNC);
        $rating->setYourScore(2);
        $ratingDate = new \DateTime();
        $rating->setYourRatingDate($ratingDate);

        // Update the same RS rating
        $rating->saveToRs($username, $filmId);

        $query = "SELECT * FROM rating WHERE user_name='$username' AND film_id=$filmId AND source_name='".Constants::SOURCE_RATINGSYNC."'";
        $result = $db->query($query);
        $this->assertEquals(1, $result->num_rows);
        $row = $result->fetch_assoc();
        $this->assertEquals(2, $row['yourScore'], 'Your score');
        $this->assertEquals(date_format($ratingDate, 'Y-m-d'), $row['yourRatingDate'], "Your rating date");
    }

    /**
     * @covers  \RatingSync\Rating::saveToRs
     * @depends testAddRatings
     */
    public function testSaveToRsNoRatingDateOriginal()
    {$this->start(__CLASS__, __FUNCTION__);

        // Get the new Film ID
        $db = getDatabase();
        $title = "Title testSaveToRsNoRatingDateOriginal";
        $year = 2016;
        $query = "SELECT id FROM film WHERE title='$title' AND year=$year";
        $result = $db->query($query);
        $filmId = $result->fetch_assoc()['id'];

        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $rating = new Rating(Constants::SOURCE_RATINGSYNC);
        $rating->setYourScore(2);
        $ratingDate = new \DateTime("2016-01-14");
        $rating->setYourRatingDate($ratingDate);

        // Update the same RS rating
        $rating->saveToRs($username, $filmId);

        $query = "SELECT * FROM rating WHERE user_name='$username' AND film_id=$filmId AND source_name='".Constants::SOURCE_RATINGSYNC."'";
        $result = $db->query($query);
        $this->assertEquals(1, $result->num_rows);
        $row = $result->fetch_assoc();
        $this->assertEquals(2, $row['yourScore'], 'Your score');
        $this->assertEquals(date_format($ratingDate, 'Y-m-d'), $row['yourRatingDate'], "Your rating date");
    }

    /**
     * @covers  \RatingSync\Rating::saveToRs
     * @depends testSaveToRsNoRatingDateOriginal
     */
    public function testSaveToRsNoRatingDateNew()
    {$this->start(__CLASS__, __FUNCTION__);

        // Get the new Film ID
        $db = getDatabase();
        $title = "Title testSaveToRsNoRatingDateOriginal";
        $year = 2016;
        $query = "SELECT id FROM film WHERE title='$title' AND year=$year";
        $result = $db->query($query);
        $filmId = $result->fetch_assoc()['id'];

        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $rating = new Rating(Constants::SOURCE_RATINGSYNC);
        $rating->setYourScore(3);

        // Update the same RS rating
        $rating->saveToRs($username, $filmId);

        $query = "SELECT * FROM rating WHERE user_name='$username' AND film_id=$filmId AND source_name='".Constants::SOURCE_RATINGSYNC."'";
        $result = $db->query($query);
        $this->assertEquals(1, $result->num_rows);
        $row = $result->fetch_assoc();
        $this->assertEquals(2, $row['yourScore'], 'Your score');
        $this->assertEquals("2016-01-14", $row['yourRatingDate'], "Your rating date");
    }
    
    /**
     * @covers  \RatingSync\Rating::saveToRs
     * @depends testAddRatings
     */
    public function testSaveToRsNoRatingDateBoth()
    {$this->start(__CLASS__, __FUNCTION__);

        // Get the new Film ID
        $db = getDatabase();
        $title = "Title testSaveToRsNoRatingDateBoth";
        $year = 2016;
        $query = "SELECT id FROM film WHERE title='$title' AND year=$year";
        $result = $db->query($query);
        $filmId = $result->fetch_assoc()['id'];

        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $rating = new Rating(Constants::SOURCE_IMDB);
        $rating->setYourScore(3);

        // Update the same RS rating
        $rating->saveToRs($username, $filmId);

        $query = "SELECT * FROM rating WHERE user_name='$username' AND film_id=$filmId AND source_name='".Constants::SOURCE_RATINGSYNC."'";
        $result = $db->query($query);
        $this->assertEquals(1, $result->num_rows);
        $row = $result->fetch_assoc();
        $this->assertEquals(3, $row['yourScore'], 'Your score');
        $this->assertEmpty($row['yourRatingDate'], "Your rating date");
    }

    /**
     * @covers  \RatingSync\Rating::saveToRs
     * @depends testObjectCanBeConstructedFromStringValue
     * @depends testSetupRatings
     * @depends testInitFromDbRow
     */
    public function testSaveToRsNoYourScore()
    {$this->start(__CLASS__, __FUNCTION__);
        $db = getDatabase();

        // Get the rating in the db from another source (IMDb)
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $filmId = 1;
        $query = "SELECT * FROM rating WHERE user_name='$username' AND film_id=$filmId AND source_name='".Constants::SOURCE_IMDB."'";
        $result = $db->query($query);
        $row = $result->fetch_assoc();
        $rating = new Rating(Constants::SOURCE_IMDB);
        $rating->initFromDbRow($row);

        $rating->setYourScore(null);
        $rating->saveToRs($username, $filmId);
        
        $query = "SELECT * FROM rating WHERE user_name='$username' AND film_id=$filmId AND source_name='".Constants::SOURCE_RATINGSYNC."'";
        $result = $db->query($query);
        $this->assertEquals(1, $result->num_rows);
        $row = $result->fetch_assoc();
        $this->assertEquals(2, $row['yourScore'], 'Your score');
    }

    /**
     * @covers  \RatingSync\Rating::saveToRs
     * @depends testObjectCanBeConstructedFromStringValue
     * @depends testSetupRatings
     */
    public function testSaveToRsNoFilmSource()
    {$this->start(__CLASS__, __FUNCTION__);
        $db = getDatabase();

        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $filmId = 8;
        $rating = new Rating(Constants::SOURCE_IMDB);
        $rating->setYourScore(1);
        $rating->setYourRatingDate(new \DateTime('2016-02-04'));
        $rating->setSuggestedScore(2);
        $rating->setCriticScore(3);
        $rating->setUserScore(4);
        $rating->saveToRs($username, $filmId);
        
        $query = "SELECT * FROM rating WHERE user_name='$username' AND film_id=$filmId AND source_name='".Constants::SOURCE_RATINGSYNC."'";
        $result = $db->query($query);
        $this->assertEquals(1, $result->num_rows);
        $row = $result->fetch_assoc();
        $this->assertEquals(1, $row['yourScore'], 'Your score');
        $this->assertEquals('2016-02-04', $row['yourRatingDate'], "Your rating date");
        $this->assertEmpty($row['suggestedScore'], 'Your score');
        $this->assertEmpty($row['criticScore'], 'Your score');
        $this->assertEmpty($row['userScore'], 'Your score');
    }

    /**
     * @covers  \RatingSync\Rating::saveToRs
     * @depends testSaveToRsNewRsRatingFromAnotherSource
     */
    public function testSaveToRsFilmSourceExists()
    {$this->start(__CLASS__, __FUNCTION__);
        // Nothing to do because it's tested by testSaveToRsNewRsRatingFromAnotherSource
    }

    /**
     * @covers  \RatingSync\Rating::saveToRs
     * @depends testSaveToRsNoRatingDateOriginal
     */
    public function testSaveToRsNewerRatingChangeScore()
    {$this->start(__CLASS__, __FUNCTION__);

        // Get the new Film ID
        $db = getDatabase();
        $title = "Title testSaveToRsNoRatingDateOriginal";
        $year = 2016;
        $query = "SELECT id FROM film WHERE title='$title' AND year=$year";
        $result = $db->query($query);
        $filmId = $result->fetch_assoc()['id'];

        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $rating = new Rating(Constants::SOURCE_RATINGSYNC);
        $rating->setYourScore(3);
        $rating->setYourRatingDate(new \DateTime('2016-01-15'));

        // Update the same RS rating
        $rating->saveToRs($username, $filmId);

        $query = "SELECT * FROM rating WHERE user_name='$username' AND film_id=$filmId AND source_name='".Constants::SOURCE_RATINGSYNC."'";
        $result = $db->query($query);
        $this->assertEquals(1, $result->num_rows);
        $row = $result->fetch_assoc();
        $this->assertEquals(3, $row['yourScore'], 'Your score');
        $this->assertEquals("2016-01-15", $row['yourRatingDate'], "Your rating date");
    }

    /**
     * @covers  \RatingSync\Rating::saveToRs
     * @depends testSaveToRsNewerRatingChangeScore
     */
    public function testSaveToRsNewerRatingSameScore()
    {$this->start(__CLASS__, __FUNCTION__);

        // Get the new Film ID
        $db = getDatabase();
        $title = "Title testSaveToRsNoRatingDateOriginal";
        $year = 2016;
        $query = "SELECT id FROM film WHERE title='$title' AND year=$year";
        $result = $db->query($query);
        $filmId = $result->fetch_assoc()['id'];

        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $rating = new Rating(Constants::SOURCE_RATINGSYNC);
        $rating->setYourScore(3);
        $rating->setYourRatingDate(new \DateTime('2016-01-16'));

        // Update the same RS rating
        $rating->saveToRs($username, $filmId);

        $query = "SELECT * FROM rating WHERE user_name='$username' AND film_id=$filmId AND source_name='".Constants::SOURCE_RATINGSYNC."'";
        $result = $db->query($query);
        $this->assertEquals(1, $result->num_rows);
        $row = $result->fetch_assoc();
        $this->assertEquals(3, $row['yourScore'], 'Your score');
        $this->assertEquals("2016-01-15", $row['yourRatingDate'], "Your rating date");
    }

    /**
     * @covers  \RatingSync\Rating::saveToRs
     * @depends testSaveToRsNewerRatingSameScore
     */
    public function testSaveToRsSameRatingDateDifferentScore()
    {$this->start(__CLASS__, __FUNCTION__);

        // Get the new Film ID
        $db = getDatabase();
        $title = "Title testSaveToRsNoRatingDateOriginal";
        $year = 2016;
        $query = "SELECT id FROM film WHERE title='$title' AND year=$year";
        $result = $db->query($query);
        $filmId = $result->fetch_assoc()['id'];

        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $rating = new Rating(Constants::SOURCE_RATINGSYNC);
        $rating->setYourScore(4);
        $rating->setYourRatingDate(new \DateTime('2016-01-15'));
        $rating->setSuggestedScore(8);

        // Update the same RS rating
        $rating->saveToRs($username, $filmId);

        $query = "SELECT * FROM rating WHERE user_name='$username' AND film_id=$filmId AND source_name='".Constants::SOURCE_RATINGSYNC."'";
        $result = $db->query($query);
        $this->assertEquals(1, $result->num_rows);
        $row = $result->fetch_assoc();
        $this->assertEquals(3, $row['yourScore'], 'Your score');
        $this->assertEquals("2016-01-15", $row['yourRatingDate'], "Your rating date");
        $this->assertEmpty($row['suggestedScore'], 'Suggested score');
    }

    /**
     * @covers  \RatingSync\Rating::saveToRs
     * @depends testSaveToRsSameRatingDateDifferentScore
     */
    public function testSaveToRsOlderRatingDifferentScore()
    {$this->start(__CLASS__, __FUNCTION__);

        // Get the new Film ID
        $db = getDatabase();
        $title = "Title testSaveToRsNoRatingDateOriginal";
        $year = 2016;
        $query = "SELECT id FROM film WHERE title='$title' AND year=$year";
        $result = $db->query($query);
        $filmId = $result->fetch_assoc()['id'];

        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $rating = new Rating(Constants::SOURCE_RATINGSYNC);
        $rating->setYourScore(5);
        $rating->setYourRatingDate(new \DateTime('2016-01-10'));

        // Update the same RS rating
        $rating->saveToRs($username, $filmId);

        $query = "SELECT * FROM rating WHERE user_name='$username' AND film_id=$filmId AND source_name='".Constants::SOURCE_RATINGSYNC."'";
        $result = $db->query($query);
        $this->assertEquals(1, $result->num_rows);
        $row = $result->fetch_assoc();
        $this->assertEquals(3, $row['yourScore'], 'Your score');
        $this->assertEquals("2016-01-15", $row['yourRatingDate'], "Your rating date");
    }

}

?>
