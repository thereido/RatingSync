<?php
/**
 * Source PHPUnit
 */
namespace RatingSync;

require_once "../Source.php";
require_once "../Constants.php";
require_once "../Rating.php";

class SourceTest extends \PHPUnit_Framework_TestCase
{
    public $debug;
    public $lastTestTime;

    public function setUp()
    {
        $this->debug = true;
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
     * @covers  \RatingSync\Source::setFilmId
     * @covers  \RatingSync\Source::getFilmId
     * @depends testObjectCanBeConstructed
     */
    public function testSetAndGetFilmId()
    {
        $source = new Source(Constants::SOURCE_JINNI);
        
        // Null
        $source->setFilmId(null);
        $this->assertNull($source->getFilmId());

        // Empty String
        $source->setFilmId("");
        $this->assertEquals("", $source->getFilmId());
        
        // Int
        $source->setFilmId(1234);
        $this->assertEquals(1234, $source->getFilmId());
        
        // Number as a string
        $source->setFilmId("1234");
        $this->assertEquals(1234, $source->getFilmId());
        
        // Alpha-num string
        $source->setFilmId("Film 1D");
        $this->assertEquals("Film 1D", $source->getFilmId());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Source::getFilmId
     * @depends testObjectCanBeConstructed
     */
    public function testFilmIdCanBeRetrievedFromNewObject()
    {
        $source = new Source(Constants::SOURCE_JINNI);
        $this->assertNull($source->getFilmId());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Source::setUrlName
     * @depends testObjectCanBeConstructed
     */
    public function testUrlNameCanBeSetWithNull()
    {
        $source = new Source(Constants::SOURCE_JINNI);
        $source->setUrlName(null);

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Source::setUrlName
     * @depends testObjectCanBeConstructed
     */
    public function testUrlNameCanBeSetWithEmpty()
    {
        $source = new Source(Constants::SOURCE_JINNI);
        $source->setUrlName("");

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Source::setUrlName
     * @depends testObjectCanBeConstructed
     */
    public function testUrlNameCanBeSetWithNonEmpty()
    {
        $source = new Source(Constants::SOURCE_JINNI);
        $source->setUrlName("url_name");

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Source::getUrlName
     * @depends testObjectCanBeConstructed
     * @depends testUrlNameCanBeSetWithNonEmpty
     */
    public function testGetUrlName()
    {
        $source = new Source(Constants::SOURCE_JINNI);
        $source->setUrlName("url_name");
        $this->assertEquals("url_name", $source->getUrlName());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Source::getUrlName
     * @depends testObjectCanBeConstructed
     */
    public function testGetUrlNameNeverSet()
    {
        $source = new Source(Constants::SOURCE_JINNI);
        $this->assertNull($source->getUrlName());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Source::getUrlName
     * @depends testObjectCanBeConstructed
     * @depends testUrlNameCanBeSetWithNull
     */
    public function testGetNullUrlName()
    {
        $source = new Source(Constants::SOURCE_JINNI);
        $source->setUrlName(null);
        $this->assertNull($source->getUrlName());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers  \RatingSync\Source::setUrlName
     * @covers  \RatingSync\Source::getUrlName
     * @depends testObjectCanBeConstructed
     * @depends testUrlNameCanBeSetWithEmpty
     */
    public function testSetUrlNameWithEmptySetsToNull()
    {
        $source = new Source(Constants::SOURCE_JINNI);
        $source->setUrlName("");
        $this->assertNull($source->getUrlName(), "Setting empty URL name should be set to null");

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
        $this->assertNull($rating->getCriticScore());
        $this->assertNull($rating->getUserScore());

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
}

?>
