<?php
/**
 * Source PHPUnit
 */
namespace RatingSync;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "Source.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "Constants.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "Rating.php";

require_once "SiteChild.php";
require_once "ImdbTest.php";
require_once "10DatabaseTest.php";

class SourceTest extends \PHPUnit_Framework_TestCase
{
    public $debug;
    public $lastTestTime;

    public function setUp()
    {
        $this->debug = false;
        $this->lastTestTime = new \DateTime();
    }

    /**
     * @covers \RatingSync\Source::__construct
     */
    public function testObjectCanBeConstructed()
    {
        $source = new Source(Constants::SOURCE_JINNI);

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers \RatingSync\Source::validSource
     */
    public function testValidSource()
    {
        $this->assertTrue(Source::validSource(Constants::SOURCE_JINNI), Constants::SOURCE_JINNI . " should be valid");
        $this->assertFalse(Source::validSource("Bad_Source"), "Bad_Source should be invalid");

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Source::getName
     * @depends testObjectCanBeConstructed
     */
    public function testGetNameFromNewObject()
    {
        $source = new Source(Constants::SOURCE_JINNI);
        $this->assertEquals(Constants::SOURCE_JINNI, $source->getName());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Source::setUniqueName
     * @covers  \RatingSync\Source::getUniqueName
     * @depends testObjectCanBeConstructed
     */
    public function testSetAndGetUniqueName()
    {
        $source = new Source(Constants::SOURCE_JINNI);
        
        // Null
        $source->setUniqueName(null);
        $this->assertNull($source->getUniqueName());

        // Empty String
        $source->setUniqueName("");
        $this->assertEquals("", $source->getUniqueName());
        
        // Int
        $source->setUniqueName(1234);
        $this->assertEquals(1234, $source->getUniqueName());
        
        // Number as a string
        $source->setUniqueName("1234");
        $this->assertEquals(1234, $source->getUniqueName());
        
        // Alpha-num string
        $source->setUniqueName("Film 1D");
        $this->assertEquals("Film 1D", $source->getUniqueName());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Source::getUniqueName
     * @depends testObjectCanBeConstructed
     */
    public function testUniqueNameCanBeRetrievedFromNewObject()
    {
        $source = new Source(Constants::SOURCE_JINNI);
        $this->assertNull($source->getUniqueName());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Source::getUniqueName
     * @depends testObjectCanBeConstructed
     */
    public function testUniqueNameCanBeRetrievedFromNewRsObjectNullFilmId()
    {
        $source = new Source(Constants::SOURCE_RATINGSYNC);
        $this->assertNull($source->getUniqueName());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Source::getUniqueName
     * @depends testObjectCanBeConstructed
     */
    public function testUniqueNameCanBeRetrievedFromNewRsObjectWithFilmId()
    {
        $source = new Source(Constants::SOURCE_RATINGSYNC, 15);
        $this->assertEquals("rs15", $source->getUniqueName());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Source::setCriticScore
     * @covers  \RatingSync\Source::getCriticScore
     * @depends testObjectCanBeConstructed
     */
    public function testCriticScoreCannotBeSetWithFloat()
    {
        $source = new Source(Constants::SOURCE_RATINGSYNC);
        $source->setCriticScore(6.5);
        $this->assertEquals(6.5, $source->getCriticScore());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Source::setCriticScore
     * @covers  \RatingSync\Source::getCriticScore
     * @depends testObjectCanBeConstructed
     */
    public function testCriticScoreCannotBeSetWithFloatString()
    {
        $source = new Source(Constants::SOURCE_RATINGSYNC);
        $source->setCriticScore("6.5");
        $this->assertEquals(6.5, $source->getCriticScore());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Source::setCriticScore
     * @depends testObjectCanBeConstructed
     * @expectedException \InvalidArgumentException
     */
    public function testCriticScoreCannotBeSetWithNonNumericalString()
    {
        $source = new Source(Constants::SOURCE_RATINGSYNC);
        $source->setCriticScore("Not an int");
    
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Source::setCriticScore
     * @depends testObjectCanBeConstructed
     * @expectedException \InvalidArgumentException
     */
    public function testCriticScoreCannotBeSetWithNegative()
    {
        $source = new Source(Constants::SOURCE_RATINGSYNC);
        $source->setCriticScore(-1);
    
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Source::setCriticScore
     * @depends testObjectCanBeConstructed
     * @expectedException \InvalidArgumentException
     */
    public function testCriticScoreCannotBeSetWithHigherThan10()
    {
        $source = new Source(Constants::SOURCE_RATINGSYNC);
        $source->setCriticScore(11);
    
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Source::setCriticScore
     * @covers  \RatingSync\Source::getCriticScore
     * @depends testObjectCanBeConstructed
     */
    public function testCriticScoreCanBeSetWithInt()
    {
        $source = new Source(Constants::SOURCE_RATINGSYNC);
        $source->setCriticScore(6);
        $this->assertEquals(6, $source->getCriticScore());
    
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Source::setCriticScore
     * @covers  \RatingSync\Source::getCriticScore
     * @depends testObjectCanBeConstructed
     */
    public function testCriticScoreCanBeSetWithIntString()
    {
        $source = new Source(Constants::SOURCE_RATINGSYNC);
        $source->setCriticScore("6");
        $this->assertEquals(6, $source->getCriticScore());
    
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Source::setCriticScore
     * @covers  \RatingSync\Source::getCriticScore
     * @depends testObjectCanBeConstructed
     */
    public function testCriticScoreCanBeSetWithNull()
    {
        $source = new Source(Constants::SOURCE_RATINGSYNC);
        $source->setCriticScore(null);
        $this->assertNull($source->getCriticScore());
    
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Source::getCriticScore
     * @depends testObjectCanBeConstructed
     */
    public function testCriticScoreCanBeRetrievedFromNewObject()
    {
        $source = new Source(Constants::SOURCE_RATINGSYNC);
        $this->assertNull($source->getCriticScore());
    
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Source::setUserScore
     * @covers  \RatingSync\Source::getUserScore
     * @depends testObjectCanBeConstructed
     */
    public function testUserScoreCanBeSetWithFloat()
    {
        $source = new Source(Constants::SOURCE_RATINGSYNC);
        $source->setUserScore(6.5);
        $this->assertEquals(6.5, $source->getUserScore());
    
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Source::setUserScore
     * @covers  \RatingSync\Source::getUserScore
     * @depends testObjectCanBeConstructed
     */
    public function testUserScoreCanBeSetWithFloatString()
    {
        $source = new Source(Constants::SOURCE_RATINGSYNC);
        $source->setUserScore("6.5");
        $this->assertEquals(6.5, $source->getUserScore());
    
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Source::setUserScore
     * @depends testObjectCanBeConstructed
     * @expectedException \InvalidArgumentException
     */
    public function testUserScoreCannotBeSetWithNonNumericalString()
    {
        $source = new Source(Constants::SOURCE_RATINGSYNC);
        $source->setUserScore("Not an int");
    
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Source::setUserScore
     * @depends testObjectCanBeConstructed
     * @expectedException \InvalidArgumentException
     */
    public function testUserScoreCannotBeSetWithNegative()
    {
        $source = new Source(Constants::SOURCE_RATINGSYNC);
        $source->setUserScore(-1);
    
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Source::setUserScore
     * @depends testObjectCanBeConstructed
     * @expectedException \InvalidArgumentException
     */
    public function testUserScoreCannotBeSetWithHigherThan10()
    {
        $source = new Source(Constants::SOURCE_RATINGSYNC);
        $source->setUserScore(11);
    
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Source::setUserScore
     * @covers  \RatingSync\Source::getUserScore
     * @depends testObjectCanBeConstructed
     */
    public function testUserScoreCanBeSetWithInt()
    {
        $source = new Source(Constants::SOURCE_RATINGSYNC);
        $source->setUserScore(6);
        $this->assertEquals(6, $source->getUserScore());
    
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Source::setUserScore
     * @covers  \RatingSync\Source::getUserScore
     * @depends testObjectCanBeConstructed
     */
    public function testUserScoreCanBeSetWithIntString()
    {
        $source = new Source(Constants::SOURCE_RATINGSYNC);
        $source->setUserScore("6");
        $this->assertEquals(6, $source->getUserScore());
    
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Source::setUserScore
     * @covers  \RatingSync\Source::getUserScore
     * @depends testObjectCanBeConstructed
     */
    public function testUserScoreCanBeSetWithNull()
    {
        $source = new Source(Constants::SOURCE_RATINGSYNC);
        $source->setUserScore(null);
        $this->assertNull($source->getUserScore());
    
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Source::getUserScore
     * @depends testObjectCanBeConstructed
     */
    public function testUserScoreCanBeRetrievedFromNewObject()
    {
        $source = new Source(Constants::SOURCE_RATINGSYNC);
        $this->assertNull($source->getUserScore());
    
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Source::setRating
     * @depends testObjectCanBeConstructed
     * @expectedException \InvalidArgumentException
     */
    public function testSetRatingWithString()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        $source = new Source(Constants::SOURCE_JINNI);
        $source->setRating("Bad_Arg");
    }

    /**
     * @covers  \RatingSync\Source::setRating
     * @depends testObjectCanBeConstructed
     * @expectedException \InvalidArgumentException
     */
    public function testSetRatingWithNumber()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        $source = new Source(Constants::SOURCE_JINNI);
        $source->setRating(7);
    }

    /**
     * @covers  \RatingSync\Source::setRating
     * @depends testObjectCanBeConstructed
     */
    public function testSetRatingWithNull()
    {
        $source = new Source(Constants::SOURCE_JINNI);
        $source->setRating(null);

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Source::setRating
     * @depends testObjectCanBeConstructed
     */
    public function testSetRatingWithEmpty()
    {
        $source = new Source(Constants::SOURCE_JINNI);
        $source->setRating("");

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Source::setRating
     * @depends testObjectCanBeConstructed
     * @expectedException \InvalidArgumentException
     */
    public function testSetRatingWithMismatchedSource()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        $source = new Source(Constants::SOURCE_JINNI);
        $rating = new Rating(Constants::SOURCE_IMDB);
        $source->setRating($rating);
    }

    /**
     * @covers  \RatingSync\Source::setRating
     * @depends testGetNameFromNewObject
     */
    public function testSetRating()
    {
        $source = new Source(Constants::SOURCE_JINNI);
        $rating = new Rating($source->getName());
        $source->setRating($rating);

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Source::setRating
     * @covers  \RatingSync\Source::getRating
     * @depends testObjectCanBeConstructed
     * @depends testSetRating
     */
    public function testGetRating()
    {
        $source = new Source(Constants::SOURCE_JINNI);
        $rating = new Rating(Constants::SOURCE_JINNI);
        $rating->setYourScore(6);
        $source->setRating($rating);
        $this->assertEquals(6, $source->getRating()->getYourScore());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Source::getRating
     * @depends testObjectCanBeConstructed
     */
    public function testGetRatingNeverSet()
    {
        $source = new Source(Constants::SOURCE_JINNI);
        $rating = $source->getRating();
        $this->assertEquals(Constants::SOURCE_JINNI, $rating->getSource());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Source::getRating
     * @depends testObjectCanBeConstructed
     * @depends testSetRatingWithNull
     */
    public function testGetRatingWasSetNull()
    {
        $source = new Source(Constants::SOURCE_JINNI);
        $source->setRating(null);
        $rating = $source->getRating();
        $this->assertEquals(Constants::SOURCE_JINNI, $rating->getSource());
        $this->assertNull($rating->getYourScore());
        $this->assertNull($rating->getSuggestedScore());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Source::getRating
     * @depends testObjectCanBeConstructed
     * @depends testSetRatingWithEmpty
     */
    public function testGetRatingWasSetEmpty()
    {
        $source = new Source(Constants::SOURCE_JINNI);
        $source->setRating("");
        $rating = $source->getRating();
        $this->assertEquals(Constants::SOURCE_JINNI, $rating->getSource());
        $this->assertNull($rating->getYourScore());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Source::setYourScore
     * @depends testObjectCanBeConstructed
     * @expectedException \InvalidArgumentException
     */
    public function testSetYourScoreWithBadArg()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        $source = new Source(Constants::SOURCE_JINNI);
        $source->setYourScore("Bad_Score");
    }

    /**
     * @covers  \RatingSync\Source::setYourScore
     * @depends testObjectCanBeConstructed
     */
    public function testSetYourScoreWithNull()
    {
        $source = new Source(Constants::SOURCE_JINNI);
        $source->setYourScore(null);

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Source::setYourScore
     * @depends testObjectCanBeConstructed
     * @expectedException \InvalidArgumentException
     */
    public function testSetYourScoreWithEmpty()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        $source = new Source(Constants::SOURCE_JINNI);
        $source->setYourScore("");
    }

    /**
     * @covers  \RatingSync\Source::setYourScore
     * @depends testObjectCanBeConstructed
     */
    public function testSetYourScore()
    {
        $source = new Source(Constants::SOURCE_JINNI);
        $source->setYourScore(7);

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Source::setYourScore
     * @covers  \RatingSync\Source::getYourScore
     * @depends testObjectCanBeConstructed
     * @depends testSetYourScore
     */
    public function testGetYourScore()
    {
        $source = new Source(Constants::SOURCE_JINNI);
        $source->setYourScore(7);
        $this->assertEquals(7, $source->getYourScore());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Source::getYourScore
     * @depends testObjectCanBeConstructed
     */
    public function testGetYourScoreNeverSet()
    {
        $source = new Source(Constants::SOURCE_JINNI);
        $this->assertNull($source->getYourScore());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Source::getYourScore
     * @depends testObjectCanBeConstructed
     * @depends testSetYourScoreWithNull
     */
    public function testGetYourScoreWasSetNull()
    {
        $source = new Source(Constants::SOURCE_JINNI);
        $source->setYourScore(null);
        $this->assertNull($source->getYourScore());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Source::setImage
     * @depends testObjectCanBeConstructed
     */
    public function testSetImageWithNull()
    {
        $source = new Source(Constants::SOURCE_JINNI);
        $source->setImage(null);

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Source::setImage
     * @depends testObjectCanBeConstructed
     */
    public function testSetImageWithEmpty()
    {
        $source = new Source(Constants::SOURCE_JINNI);
        $source->setImage("");

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Source::setImage
     * @depends testObjectCanBeConstructed
     */
    public function testSetImage()
    {
        $source = new Source(Constants::SOURCE_JINNI);
        $source->setImage("http://example.com/example.jpg");

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Source::setImage
     * @covers  \RatingSync\Source::getImage
     * @depends testObjectCanBeConstructed
     * @depends testSetImage
     */
    public function testGetImage()
    {
        $source = new Source(Constants::SOURCE_JINNI);
        $source->setImage("http://example.com/example.jpg");
        $this->assertEquals("http://example.com/example.jpg", $source->getImage());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Source::getImage
     * @depends testObjectCanBeConstructed
     */
    public function testGetImageNeverSet()
    {
        $source = new Source(Constants::SOURCE_JINNI);
        $this->assertNull($source->getImage());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Source::getImage
     * @depends testObjectCanBeConstructed
     * @depends testSetImageWithNull
     */
    public function testGetImageWasSetNull()
    {
        $source = new Source(Constants::SOURCE_JINNI);
        $source->setImage(null);
        $this->assertNull($source->getImage());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Source::getImage
     * @depends testObjectCanBeConstructed
     * @depends testSetImageWithEmpty
     */
    public function testGetImageWasSetEmpty()
    {
        $source = new Source(Constants::SOURCE_JINNI);
        $source->setImage("");
        $this->assertEquals("", $source->getImage());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    public function testResetDb()
    {
        DatabaseTest::resetDb();

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Source::saveFilmSourceToDb
     * @depends testObjectCanBeConstructed
     * @expectedException \InvalidArgumentException
     */
    public function testAddNewFilmSourceSetNull()
    {
        $source = new Source(Constants::SOURCE_IMDB);
        $source->saveFilmSourceToDb(null);

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Source::saveFilmSourceToDb
     * @depends testObjectCanBeConstructed
     * @expectedException \InvalidArgumentException
     */
    public function testSaveNewFilmSourceSetEmpty()
    {
        $source = new Source(Constants::SOURCE_IMDB);
        $source->saveFilmSourceToDb("");

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Source::saveFilmSourceToDb
     * @depends testObjectCanBeConstructed
     * @depends testResetDb
     * @expectedException \Exception
     */
    public function testSaveNewFilmSourceFilmNotFound()
    {
        DatabaseTest::resetDb();
        $source = new Source(Constants::SOURCE_IMDB);
        $source->saveFilmSourceToDb(1);

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @depends testResetDb
     */
    public function testSetupRatings()
    {
        DatabaseTest::resetDb();
        $username_site = TEST_IMDB_USERNAME;
        $username_rs = Constants::TEST_RATINGSYNC_USERNAME;
        $site = new SiteChild($username_site);
        $filename =  __DIR__ . DIRECTORY_SEPARATOR . "testfile" . DIRECTORY_SEPARATOR . "input_ratings_site.xml";
        $films = $site->importRatings(Constants::IMPORT_FORMAT_XML, $filename, $username_rs);

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Source::saveFilmSourceToDb
     * @depends testObjectCanBeConstructed
     * @depends testSetupRatings
     */
    public function testSaveNewFilmSourceDuplicate()
    {
        $db = getDatabase();
        $source = new Source(Constants::SOURCE_IMDB);
        $filmId = 1;
        $source->saveFilmSourceToDb($filmId);
        $query = "SELECT * FROM film_source" .
                 " WHERE film_id=$filmId" .
                   " AND source_name='".$source->getName()."'";
        $result = $db->query($query);
        $this->assertEquals(1, $result->num_rows, "There sure be one Film/Source row $filmId/" . $source->getName());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Source::saveFilmSourceToDb
     * @depends testObjectCanBeConstructed
     * @depends testSetupRatings
     */
    public function testSaveNewFilmSource()
    {
        // There sure be a film_source where film_id=3, but not for IMDb
        $db = getDatabase();
        $source = new Source(Constants::SOURCE_IMDB);
        $filmId = 3;
        $source->saveFilmSourceToDb($filmId);
        $query = "SELECT * FROM film_source" .
                 " WHERE film_id=$filmId" .
                   " AND source_name='".$source->getName()."'";
        $result = $db->query($query);
        $this->assertEquals(1, $result->num_rows, "There sure be one Film/Source row $filmId/" . $source->getName());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Source::saveFilmSourceToDb
     * @depends testObjectCanBeConstructed
     * @depends testSetupRatings
     */
    public function testAddNewFilmSourceImageFromSource()
    {
        // There is a RS film/source row film_id=5 and no image
        // There is no IMDb film/source row
        $db = getDatabase();
        $source = new Source(Constants::SOURCE_IMDB);
        $source->setImage('http://example.com/title2_imdb_image.jpeg');
        $filmId = 5;
        $source->saveFilmSourceToDb($filmId);
        $query = "SELECT * FROM film_source" .
                 " WHERE film_id=$filmId" .
                   " AND source_name='".$source->getName()."'";
        $result = $db->query($query);
        $this->assertEquals(1, $result->num_rows, "There sure be one Film/Source row $filmId/" . $source->getName());
        $row = $result->fetch_assoc();
        $this->assertEquals("http://example.com/title2_imdb_image.jpeg", $row['image']);

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Source::saveFilmSourceToDb
     * @depends testObjectCanBeConstructed
     */
    public function testAddNewFilmSourceImageFromNowhere()
    {
        // There is one or more film/source row film_id=6 and none with an image
        // There is no IMDb film/source row
        // No not use $source->setImage()
        $db = getDatabase();
        $source = new Source(Constants::SOURCE_IMDB);
        $filmId = 6;
        $source->saveFilmSourceToDb($filmId);
        $query = "SELECT * FROM film_source" .
                 " WHERE film_id=$filmId" .
                   " AND source_name='".$source->getName()."'";
        $result = $db->query($query);
        $this->assertEquals(1, $result->num_rows, "There sure be one Film/Source row $filmId/" . $source->getName());
        $row = $result->fetch_assoc();
        $this->assertEmpty($row['image']);

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Source::saveFilmSourceToDb
     * @depends testObjectCanBeConstructed
     * @depends testSetupRatings
     */
    public function testAddNewFilmSourceWithNoOtherFilmSource()
    {
        // There is no film/source row film_id=8
        // There is film row id=8
        $db = getDatabase();
        $source = new Source(Constants::SOURCE_IMDB);
        $filmId = 8;
        $source->saveFilmSourceToDb($filmId);
        $query = "SELECT * FROM film_source" .
                 " WHERE film_id=$filmId" .
                   " AND source_name='".$source->getName()."'";
        $result = $db->query($query);
        $this->assertEquals(1, $result->num_rows, "There sure be one Film/Source row $filmId/" . $source->getName());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }
}

?>
