<?php
/**
 * Rating PHPUnit
 */
namespace RatingSync;

require_once "../Rating.php";
require_once "../Constants.php";

class RatingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers            \RatingSync\Rating::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testCannotBeConstructedFromNull()
    {
        new Rating(null);
    }

    /**
     * @covers            \RatingSync\Rating::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testCannotBeConstructedFromInvalidSource()
    {
        new Rating("Netflux");
    }

    /**
     * @covers \RatingSync\Rating::__construct
     */
    public function testObjectCanBeConstructedFromStringValue()
    {
        $rating = new Rating("Jinni");
        return $rating;
    }

    /**
     * @covers  \RatingSync\Rating::getSource
     * @depends testObjectCanBeConstructedFromStringValue
     */
    public function testSourceCanBeRetrieved()
    {
        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $this->assertEquals(\RatingSync\Constants::SOURCE_IMDB, $r->getSource());
    }

    /**
     * @covers  \RatingSync\Rating::setYourScore
     * @covers  \RatingSync\Rating::getYourScore
     * @depends testObjectCanBeConstructedFromStringValue
     */
    public function testYourScoreCannotBeSetWithFloat()
    {
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
    {
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
    {
        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $r->setYourScore("Not an int");
    }

    /**
     * @covers  \RatingSync\Rating::setYourScore
     * @depends testObjectCanBeConstructedFromStringValue
     * @expectedException \InvalidArgumentException
     */
    public function testYourScoreCannotBeSetWithNegative()
    {
        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $r->setYourScore(-1);
    }

    /**
     * @covers  \RatingSync\Rating::setYourScore
     * @depends testObjectCanBeConstructedFromStringValue
     * @expectedException \InvalidArgumentException
     */
    public function testYourScoreCannotBeSetWithHigherThan10()
    {
        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $r->setYourScore(11);
    }

    /**
     * @covers  \RatingSync\Rating::setYourScore
     * @covers  \RatingSync\Rating::getYourScore
     * @depends testObjectCanBeConstructedFromStringValue
     */
    public function testYourScoreCanBeSetWithInt()
    {
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
    {
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
    {
        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $r->setYourScore(null);
        $this->assertNull($r->getYourScore());
    }

    /**
     * @covers  \RatingSync\Rating::getYourScore
     * @depends testObjectCanBeConstructedFromStringValue
     */
    public function testYourScoreCanBeRetrievedFromNewObject()
    {
        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $this->assertNull($r->getYourScore());
    }

    /**
     * @covers  \RatingSync\Rating::setYourRatingDate
     * @depends testObjectCanBeConstructedFromStringValue
     * @expectedException \InvalidArgumentException
     */
    public function testYourRatingDateCannotBeSetWithString()
    {
        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $r->setYourRatingDate("10/12/2012");
    }

    /**
     * @covers  \RatingSync\Rating::setYourRatingDate
     * @covers  \RatingSync\Rating::getYourRatingDate
     * @depends testObjectCanBeConstructedFromStringValue
     */
    public function testYourRatingDateCanBeSetWithNull()
    {
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
    {
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
    {
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
    {
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
    {
        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $r->setSuggestedScore("Not an int");
    }

    /**
     * @covers  \RatingSync\Rating::setSuggestedScore
     * @depends testObjectCanBeConstructedFromStringValue
     * @expectedException \InvalidArgumentException
     */
    public function testSuggestedScoreCannotBeSetWithNegative()
    {
        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $r->setSuggestedScore(-1);
    }

    /**
     * @covers  \RatingSync\Rating::setSuggestedScore
     * @depends testObjectCanBeConstructedFromStringValue
     * @expectedException \InvalidArgumentException
     */
    public function testSuggestedScoreCannotBeSetWithHigherThan10()
    {
        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $r->setSuggestedScore(11);
    }

    /**
     * @covers  \RatingSync\Rating::setSuggestedScore
     * @covers  \RatingSync\Rating::getSuggestedScore
     * @depends testObjectCanBeConstructedFromStringValue
     */
    public function testSuggestedScoreCanBeSetWithInt()
    {
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
    {
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
    {
        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $r->setSuggestedScore(null);
        $this->assertNull($r->getSuggestedScore());
    }

    /**
     * @covers  \RatingSync\Rating::getSuggestedScore
     * @depends testObjectCanBeConstructedFromStringValue
     */
    public function testSuggestedScoreCanBeRetrievedFromNewObject()
    {
        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $this->assertNull($r->getSuggestedScore());
    }

    /**
     * @covers  \RatingSync\Rating::setCriticScore
     * @covers  \RatingSync\Rating::getCriticScore
     * @depends testObjectCanBeConstructedFromStringValue
     */
    public function testCriticScoreCannotBeSetWithFloat()
    {
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
    {
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
    {
        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $r->setCriticScore("Not an int");
    }

    /**
     * @covers  \RatingSync\Rating::setCriticScore
     * @depends testObjectCanBeConstructedFromStringValue
     * @expectedException \InvalidArgumentException
     */
    public function testCriticScoreCannotBeSetWithNegative()
    {
        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $r->setCriticScore(-1);
    }

    /**
     * @covers  \RatingSync\Rating::setCriticScore
     * @depends testObjectCanBeConstructedFromStringValue
     * @expectedException \InvalidArgumentException
     */
    public function testCriticScoreCannotBeSetWithHigherThan10()
    {
        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $r->setCriticScore(11);
    }

    /**
     * @covers  \RatingSync\Rating::setCriticScore
     * @covers  \RatingSync\Rating::getCriticScore
     * @depends testObjectCanBeConstructedFromStringValue
     */
    public function testCriticScoreCanBeSetWithInt()
    {
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
    {
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
    {
        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $r->setCriticScore(null);
        $this->assertNull($r->getCriticScore());
    }

    /**
     * @covers  \RatingSync\Rating::getCriticScore
     * @depends testObjectCanBeConstructedFromStringValue
     */
    public function testCriticScoreCanBeRetrievedFromNewObject()
    {
        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $this->assertNull($r->getCriticScore());
    }

    /**
     * @covers  \RatingSync\Rating::setUserScore
     * @covers  \RatingSync\Rating::getUserScore
     * @depends testObjectCanBeConstructedFromStringValue
     */
    public function testUserScoreCanBeSetWithFloat()
    {
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
    {
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
    {
        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $r->setUserScore("Not an int");
    }

    /**
     * @covers  \RatingSync\Rating::setUserScore
     * @depends testObjectCanBeConstructedFromStringValue
     * @expectedException \InvalidArgumentException
     */
    public function testUserScoreCannotBeSetWithNegative()
    {
        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $r->setUserScore(-1);
    }

    /**
     * @covers  \RatingSync\Rating::setUserScore
     * @depends testObjectCanBeConstructedFromStringValue
     * @expectedException \InvalidArgumentException
     */
    public function testUserScoreCannotBeSetWithHigherThan10()
    {
        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $r->setUserScore(11);
    }

    /**
     * @covers  \RatingSync\Rating::setUserScore
     * @covers  \RatingSync\Rating::getUserScore
     * @depends testObjectCanBeConstructedFromStringValue
     */
    public function testUserScoreCanBeSetWithInt()
    {
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
    {
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
    {
        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $r->setUserScore(null);
        $this->assertNull($r->getUserScore());
    }

    /**
     * @covers  \RatingSync\Rating::getUserScore
     * @depends testObjectCanBeConstructedFromStringValue
     */
    public function testUserScoreCanBeRetrievedFromNewObject()
    {
        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $this->assertNull($r->getUserScore());
    }

    /**
     * @covers  \RatingSync\Rating::validRatingScore
     * @depends testObjectCanBeConstructedFromStringValue
     */
    public function testValidRatingScores()
    {
        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        
        $this->assertFalse($r->validRatingScore("Not an int"), "Invalid - Not an int"); // Non-Numerical String
        $this->assertFalse($r->validRatingScore(-1), "Invalid - Negative"); // Negative
        $this->assertFalse($r->validRatingScore(11), "Invalid - Higher Than 10"); // Higher Than 10
        $this->assertFalse($r->validRatingScore(0), "Invalid - Zero"); // Zero
        $this->assertFalse($r->validRatingScore(null), "Invalid - Null"); // Null

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
}

?>
