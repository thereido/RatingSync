<?php
/**
 * Source PHPUnit
 */
namespace RatingSync;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "Source.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "Constants.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "Rating.php";

require_once "SiteRatingsChild.php";
require_once "ImdbTest.php";
require_once "NetflixTest.php";
require_once "AmazonTest.php";
require_once "XfinityTest.php";
require_once "DatabaseTest.php";
require_once "RatingSyncTestCase.php";

class SourceTest extends RatingSyncTestCase
{
    protected function setUp(): void
    {
        parent::setup();
        //$this->verbose = true;
    }

    /**
     * @covers \RatingSync\Source::__construct
     */
    public function testObjectCanBeConstructed()
    {$this->start(__CLASS__, __FUNCTION__);

        $source = new Source(Constants::SOURCE_JINNI);

        $this->assertTrue(true); // Making sure we made it this far
    }

    /**
     * @covers \RatingSync\Source::validSource
     */
    public function testValidSource()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->assertTrue(Source::validSource(Constants::SOURCE_JINNI), Constants::SOURCE_JINNI . " should be valid");
        $this->assertFalse(Source::validSource("Bad_Source"), "Bad_Source should be invalid");
    }

    /**
     * @covers  \RatingSync\Source::getName
     * @depends testObjectCanBeConstructed
     */
    public function testGetNameFromNewObject()
    {$this->start(__CLASS__, __FUNCTION__);

        $source = new Source(Constants::SOURCE_JINNI);
        $this->assertEquals(Constants::SOURCE_JINNI, $source->getName());
    }

    /**
     * @covers  \RatingSync\Source::setUniqueName
     * @covers  \RatingSync\Source::getUniqueName
     * @depends testObjectCanBeConstructed
     */
    public function testSetAndGetUniqueName()
    {$this->start(__CLASS__, __FUNCTION__);

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
    }

    /**
     * @covers  \RatingSync\Source::getUniqueName
     * @depends testObjectCanBeConstructed
     */
    public function testUniqueNameCanBeRetrievedFromNewObject()
    {$this->start(__CLASS__, __FUNCTION__);

        $source = new Source(Constants::SOURCE_JINNI);
        $this->assertNull($source->getUniqueName());
    }

    /**
     * @covers  \RatingSync\Source::getUniqueName
     * @depends testObjectCanBeConstructed
     */
    public function testUniqueNameCanBeRetrievedFromNewRsObjectNullFilmId()
    {$this->start(__CLASS__, __FUNCTION__);

        $source = new Source(Constants::SOURCE_RATINGSYNC);
        $this->assertNull($source->getUniqueName());
    }

    /**
     * @covers  \RatingSync\Source::getUniqueName
     * @depends testObjectCanBeConstructed
     */
    public function testUniqueNameCanBeRetrievedFromNewRsObjectWithFilmId()
    {$this->start(__CLASS__, __FUNCTION__);

        $source = new Source(Constants::SOURCE_RATINGSYNC, 15);
        $this->assertEquals("rs15", $source->getUniqueName());
    }

    /**
     * @covers  \RatingSync\Source::setCriticScore
     * @covers  \RatingSync\Source::getCriticScore
     * @depends testObjectCanBeConstructed
     */
    public function testCriticScoreCannotBeSetWithFloat()
    {$this->start(__CLASS__, __FUNCTION__);

        $source = new Source(Constants::SOURCE_RATINGSYNC);
        $source->setCriticScore(6.5);
        $this->assertEquals(6.5, $source->getCriticScore());
    }

    /**
     * @covers  \RatingSync\Source::setCriticScore
     * @covers  \RatingSync\Source::getCriticScore
     * @depends testObjectCanBeConstructed
     */
    public function testCriticScoreCannotBeSetWithFloatString()
    {$this->start(__CLASS__, __FUNCTION__);

        $source = new Source(Constants::SOURCE_RATINGSYNC);
        $source->setCriticScore("6.5");
        $this->assertEquals(6.5, $source->getCriticScore());
    }

    /**
     * @covers  \RatingSync\Source::setCriticScore
     * @depends testObjectCanBeConstructed
     */
    public function testCriticScoreCannotBeSetWithNonNumericalString()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        $source = new Source(Constants::SOURCE_RATINGSYNC);
        $source->setCriticScore("Not an int");
    }

    /**
     * @covers  \RatingSync\Source::setCriticScore
     * @depends testObjectCanBeConstructed
     */
    public function testCriticScoreCannotBeSetWithNegative()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        $source = new Source(Constants::SOURCE_RATINGSYNC);
        $source->setCriticScore(-1);
    }

    /**
     * @covers  \RatingSync\Source::setCriticScore
     * @depends testObjectCanBeConstructed
     */
    public function testCriticScoreCannotBeSetWithHigherThan10()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        $source = new Source(Constants::SOURCE_RATINGSYNC);
        $source->setCriticScore(11);
    }

    /**
     * @covers  \RatingSync\Source::setCriticScore
     * @covers  \RatingSync\Source::getCriticScore
     * @depends testObjectCanBeConstructed
     */
    public function testCriticScoreCanBeSetWithInt()
    {$this->start(__CLASS__, __FUNCTION__);

        $source = new Source(Constants::SOURCE_RATINGSYNC);
        $source->setCriticScore(6);
        $this->assertEquals(6, $source->getCriticScore());
    }

    /**
     * @covers  \RatingSync\Source::setCriticScore
     * @covers  \RatingSync\Source::getCriticScore
     * @depends testObjectCanBeConstructed
     */
    public function testCriticScoreCanBeSetWithIntString()
    {$this->start(__CLASS__, __FUNCTION__);

        $source = new Source(Constants::SOURCE_RATINGSYNC);
        $source->setCriticScore("6");
        $this->assertEquals(6, $source->getCriticScore());
    }

    /**
     * @covers  \RatingSync\Source::setCriticScore
     * @covers  \RatingSync\Source::getCriticScore
     * @depends testObjectCanBeConstructed
     */
    public function testCriticScoreCanBeSetWithNull()
    {$this->start(__CLASS__, __FUNCTION__);

        $source = new Source(Constants::SOURCE_RATINGSYNC);
        $source->setCriticScore(null);
        $this->assertNull($source->getCriticScore());
    }

    /**
     * @covers  \RatingSync\Source::getCriticScore
     * @depends testObjectCanBeConstructed
     */
    public function testCriticScoreCanBeRetrievedFromNewObject()
    {$this->start(__CLASS__, __FUNCTION__);

        $source = new Source(Constants::SOURCE_RATINGSYNC);
        $this->assertNull($source->getCriticScore());
    }

    /**
     * @covers  \RatingSync\Source::setUserScore
     * @covers  \RatingSync\Source::getUserScore
     * @depends testObjectCanBeConstructed
     */
    public function testUserScoreCanBeSetWithFloat()
    {$this->start(__CLASS__, __FUNCTION__);

        $source = new Source(Constants::SOURCE_RATINGSYNC);
        $source->setUserScore(6.5);
        $this->assertEquals(6.5, $source->getUserScore());
    }

    /**
     * @covers  \RatingSync\Source::setUserScore
     * @covers  \RatingSync\Source::getUserScore
     * @depends testObjectCanBeConstructed
     */
    public function testUserScoreCanBeSetWithFloatString()
    {$this->start(__CLASS__, __FUNCTION__);

        $source = new Source(Constants::SOURCE_RATINGSYNC);
        $source->setUserScore("6.5");
        $this->assertEquals(6.5, $source->getUserScore());
    }

    /**
     * @covers  \RatingSync\Source::setUserScore
     * @depends testObjectCanBeConstructed
     */
    public function testUserScoreCannotBeSetWithNonNumericalString()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        $source = new Source(Constants::SOURCE_RATINGSYNC);
        $source->setUserScore("Not an int");
    }

    /**
     * @covers  \RatingSync\Source::setUserScore
     * @depends testObjectCanBeConstructed
     */
    public function testUserScoreCannotBeSetWithNegative()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        $source = new Source(Constants::SOURCE_RATINGSYNC);
        $source->setUserScore(-1);
    }

    /**
     * @covers  \RatingSync\Source::setUserScore
     * @depends testObjectCanBeConstructed
     */
    public function testUserScoreCannotBeSetWithHigherThan10()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        $source = new Source(Constants::SOURCE_RATINGSYNC);
        $source->setUserScore(11);
    }

    /**
     * @covers  \RatingSync\Source::setUserScore
     * @covers  \RatingSync\Source::getUserScore
     * @depends testObjectCanBeConstructed
     */
    public function testUserScoreCanBeSetWithInt()
    {$this->start(__CLASS__, __FUNCTION__);

        $source = new Source(Constants::SOURCE_RATINGSYNC);
        $source->setUserScore(6);
        $this->assertEquals(6, $source->getUserScore());
    }

    /**
     * @covers  \RatingSync\Source::setUserScore
     * @covers  \RatingSync\Source::getUserScore
     * @depends testObjectCanBeConstructed
     */
    public function testUserScoreCanBeSetWithIntString()
    {$this->start(__CLASS__, __FUNCTION__);

        $source = new Source(Constants::SOURCE_RATINGSYNC);
        $source->setUserScore("6");
        $this->assertEquals(6, $source->getUserScore());
    }

    /**
     * @covers  \RatingSync\Source::setUserScore
     * @covers  \RatingSync\Source::getUserScore
     * @depends testObjectCanBeConstructed
     */
    public function testUserScoreCanBeSetWithNull()
    {$this->start(__CLASS__, __FUNCTION__);

        $source = new Source(Constants::SOURCE_RATINGSYNC);
        $source->setUserScore(null);
        $this->assertNull($source->getUserScore());
    }

    /**
     * @covers  \RatingSync\Source::getUserScore
     * @depends testObjectCanBeConstructed
     */
    public function testUserScoreCanBeRetrievedFromNewObject()
    {$this->start(__CLASS__, __FUNCTION__);

        $source = new Source(Constants::SOURCE_RATINGSYNC);
        $this->assertNull($source->getUserScore());
    }

    /**
     * @covers  \RatingSync\Source::setRating
     * @depends testObjectCanBeConstructed
     */
    public function testSetRatingWithString()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        $source = new Source(Constants::SOURCE_JINNI);
        $source->setRating("Bad_Arg");
    }

    /**
     * @covers  \RatingSync\Source::setRating
     * @depends testObjectCanBeConstructed
     */
    public function testSetRatingWithNumber()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        $source = new Source(Constants::SOURCE_JINNI);
        $source->setRating(7);
    }

    /**
     * @covers  \RatingSync\Source::setRating
     * @depends testObjectCanBeConstructed
     */
    public function testSetRatingWithNull()
    {$this->start(__CLASS__, __FUNCTION__);

        $source = new Source(Constants::SOURCE_JINNI);
        $source->setRating(null);

        $this->assertTrue(true); // Making sure we made it this far
    }

    /**
     * @covers  \RatingSync\Source::setRating
     * @depends testObjectCanBeConstructed
     */
    public function testSetRatingWithEmpty()
    {$this->start(__CLASS__, __FUNCTION__);

        $source = new Source(Constants::SOURCE_JINNI);
        $source->setRating("");

        $this->assertTrue(true); // Making sure we made it this far
    }

    /**
     * @covers  \RatingSync\Source::setRating
     * @depends testObjectCanBeConstructed
     */
    public function testSetRatingWithMismatchedSource()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        $source = new Source(Constants::SOURCE_JINNI);
        $rating = new Rating(Constants::SOURCE_IMDB);
        $source->setRating($rating);
    }

    /**
     * @covers  \RatingSync\Source::setRating
     * @depends testGetNameFromNewObject
     */
    public function testSetRating()
    {$this->start(__CLASS__, __FUNCTION__);

        $source = new Source(Constants::SOURCE_JINNI);
        $rating = new Rating($source->getName());
        $source->setRating($rating);

        $this->assertTrue(true); // Making sure we made it this far
    }

    /**
     * @covers  \RatingSync\Source::setRating
     * @covers  \RatingSync\Source::getRating
     * @depends testObjectCanBeConstructed
     * @depends testSetRating
     */
    public function testGetRating()
    {$this->start(__CLASS__, __FUNCTION__);

        $source = new Source(Constants::SOURCE_JINNI);
        $rating = new Rating(Constants::SOURCE_JINNI);
        $rating->setYourScore(6);
        $source->setRating($rating);
        $this->assertEquals(6, $source->getRating()->getYourScore());
    }

    /**
     * @covers  \RatingSync\Source::getRating
     * @depends testObjectCanBeConstructed
     */
    public function testGetRatingNeverSet()
    {$this->start(__CLASS__, __FUNCTION__);

        $source = new Source(Constants::SOURCE_JINNI);
        $rating = $source->getRating();
        $this->assertEquals(Constants::SOURCE_JINNI, $rating->getSource());
    }

    /**
     * @covers  \RatingSync\Source::getRating
     * @depends testObjectCanBeConstructed
     * @depends testSetRatingWithNull
     */
    public function testGetRatingWasSetNull()
    {$this->start(__CLASS__, __FUNCTION__);

        $source = new Source(Constants::SOURCE_JINNI);
        $source->setRating(null);
        $rating = $source->getRating();
        $this->assertEquals(Constants::SOURCE_JINNI, $rating->getSource());
        $this->assertNull($rating->getYourScore());
        $this->assertNull($rating->getSuggestedScore());
    }

    /**
     * @covers  \RatingSync\Source::getRating
     * @depends testObjectCanBeConstructed
     * @depends testSetRatingWithEmpty
     */
    public function testGetRatingWasSetEmpty()
    {$this->start(__CLASS__, __FUNCTION__);

        $source = new Source(Constants::SOURCE_JINNI);
        $source->setRating("");
        $rating = $source->getRating();
        $this->assertEquals(Constants::SOURCE_JINNI, $rating->getSource());
        $this->assertNull($rating->getYourScore());
    }

    /**
     * @covers  \RatingSync\Source::setYourScore
     * @depends testObjectCanBeConstructed
     */
    public function testSetYourScoreWithBadArg()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        $source = new Source(Constants::SOURCE_JINNI);
        $source->setYourScore("Bad_Score");
    }

    /**
     * @covers  \RatingSync\Source::setYourScore
     * @depends testObjectCanBeConstructed
     */
    public function testSetYourScoreWithNull()
    {$this->start(__CLASS__, __FUNCTION__);

        $source = new Source(Constants::SOURCE_JINNI);
        $source->setYourScore(null);

        $this->assertTrue(true); // Making sure we made it this far
    }

    /**
     * @covers  \RatingSync\Source::setYourScore
     * @depends testObjectCanBeConstructed
     */
    public function testSetYourScoreWithEmpty()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        $source = new Source(Constants::SOURCE_JINNI);
        $source->setYourScore("");
    }

    /**
     * @covers  \RatingSync\Source::setYourScore
     * @depends testObjectCanBeConstructed
     */
    public function testSetYourScore()
    {$this->start(__CLASS__, __FUNCTION__);

        $source = new Source(Constants::SOURCE_JINNI);
        $source->setYourScore(7);

        $this->assertTrue(true); // Making sure we made it this far
    }

    /**
     * @covers  \RatingSync\Source::setYourScore
     * @covers  \RatingSync\Source::getYourScore
     * @depends testObjectCanBeConstructed
     * @depends testSetYourScore
     */
    public function testGetYourScore()
    {$this->start(__CLASS__, __FUNCTION__);

        $source = new Source(Constants::SOURCE_JINNI);
        $source->setYourScore(7);
        $this->assertEquals(7, $source->getYourScore());
    }

    /**
     * @covers  \RatingSync\Source::getYourScore
     * @depends testObjectCanBeConstructed
     */
    public function testGetYourScoreNeverSet()
    {$this->start(__CLASS__, __FUNCTION__);

        $source = new Source(Constants::SOURCE_JINNI);
        $this->assertNull($source->getYourScore());
    }

    /**
     * @covers  \RatingSync\Source::getYourScore
     * @depends testObjectCanBeConstructed
     * @depends testSetYourScoreWithNull
     */
    public function testGetYourScoreWasSetNull()
    {$this->start(__CLASS__, __FUNCTION__);

        $source = new Source(Constants::SOURCE_JINNI);
        $source->setYourScore(null);
        $this->assertNull($source->getYourScore());
    }

    /**
     * @covers  \RatingSync\Source::setImage
     * @depends testObjectCanBeConstructed
     */
    public function testSetImageWithNull()
    {$this->start(__CLASS__, __FUNCTION__);

        $source = new Source(Constants::SOURCE_JINNI);
        $source->setImage(null);

        $this->assertTrue(true); // Making sure we made it this far
    }

    /**
     * @covers  \RatingSync\Source::setImage
     * @depends testObjectCanBeConstructed
     */
    public function testSetImageWithEmpty()
    {$this->start(__CLASS__, __FUNCTION__);

        $source = new Source(Constants::SOURCE_JINNI);
        $source->setImage("");

        $this->assertTrue(true); // Making sure we made it this far
    }

    /**
     * @covers  \RatingSync\Source::setImage
     * @depends testObjectCanBeConstructed
     */
    public function testSetImage()
    {$this->start(__CLASS__, __FUNCTION__);

        $source = new Source(Constants::SOURCE_JINNI);
        $source->setImage("http://example.com/example.jpg");

        $this->assertTrue(true); // Making sure we made it this far
    }

    /**
     * @covers  \RatingSync\Source::setImage
     * @covers  \RatingSync\Source::getImage
     * @depends testObjectCanBeConstructed
     * @depends testSetImage
     */
    public function testGetImage()
    {$this->start(__CLASS__, __FUNCTION__);

        $source = new Source(Constants::SOURCE_JINNI);
        $source->setImage("http://example.com/example.jpg");
        $this->assertEquals("http://example.com/example.jpg", $source->getImage());
    }

    /**
     * @covers  \RatingSync\Source::getImage
     * @depends testObjectCanBeConstructed
     */
    public function testGetImageNeverSet()
    {$this->start(__CLASS__, __FUNCTION__);

        $source = new Source(Constants::SOURCE_JINNI);
        $this->assertNull($source->getImage());
    }

    /**
     * @covers  \RatingSync\Source::getImage
     * @depends testObjectCanBeConstructed
     * @depends testSetImageWithNull
     */
    public function testGetImageWasSetNull()
    {$this->start(__CLASS__, __FUNCTION__);

        $source = new Source(Constants::SOURCE_JINNI);
        $source->setImage(null);
        $this->assertNull($source->getImage());
    }

    /**
     * @covers  \RatingSync\Source::getImage
     * @depends testObjectCanBeConstructed
     * @depends testSetImageWithEmpty
     */
    public function testGetImageWasSetEmpty()
    {$this->start(__CLASS__, __FUNCTION__);

        $source = new Source(Constants::SOURCE_JINNI);
        $source->setImage("");
        $this->assertEquals("", $source->getImage());
    }

    public function testResetDb()
    {$this->start(__CLASS__, __FUNCTION__);

        DatabaseTest::resetDb();

        $this->assertTrue(true); // Making sure we made it this far
    }

    /**
     * @covers  \RatingSync\Source::saveFilmSourceToDb
     * @depends testObjectCanBeConstructed
     */
    public function testAddNewFilmSourceSetNull()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        $source = new Source(Constants::SOURCE_IMDB);
        $source->saveFilmSourceToDb(null);
    }

    /**
     * @covers  \RatingSync\Source::saveFilmSourceToDb
     * @depends testObjectCanBeConstructed
     */
    public function testSaveNewFilmSourceSetEmpty()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        $source = new Source(Constants::SOURCE_IMDB);
        $source->saveFilmSourceToDb("");
    }

    /**
     * @covers  \RatingSync\Source::saveFilmSourceToDb
     * @depends testObjectCanBeConstructed
     * @depends testResetDb
     */
    public function testSaveNewFilmSourceFilmNotFound()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\Exception::class);

        DatabaseTest::resetDb();
        $source = new Source(Constants::SOURCE_IMDB);
        $source->saveFilmSourceToDb(1);
    }

    /**
     * @depends testResetDb
     */
    public function testSetupRatings()
    {$this->start(__CLASS__, __FUNCTION__);

        DatabaseTest::resetDb();
        $username_site = TEST_IMDB_USERNAME;
        $username_rs = Constants::TEST_RATINGSYNC_USERNAME;
        $site = new SiteRatingsChild($username_site);
        $filename =  __DIR__ . DIRECTORY_SEPARATOR . "testfile" . DIRECTORY_SEPARATOR . "input_ratings_site.xml";
        $films = $site->importRatings(Constants::IMPORT_FORMAT_XML, $filename, $username_rs);

        $this->assertTrue(true); // Making sure we made it this far
    }

    /**
     * @covers  \RatingSync\Source::saveFilmSourceToDb
     * @depends testObjectCanBeConstructed
     * @depends testSetupRatings
     */
    public function testSaveNewFilmSourceDuplicate()
    {$this->start(__CLASS__, __FUNCTION__);

        $db = getDatabase();
        $source = new Source(Constants::SOURCE_IMDB);
        $filmId = 1;
        $source->saveFilmSourceToDb($filmId);
        $query = "SELECT * FROM film_source" .
                 " WHERE film_id=$filmId" .
                   " AND source_name='".$source->getName()."'";
        $result = $db->query($query);
        $this->assertEquals(1, $result->rowCount(), "There sure be one Film/Source row $filmId/" . $source->getName());
    }

    /**
     * @covers  \RatingSync\Source::saveFilmSourceToDb
     * @depends testObjectCanBeConstructed
     * @depends testSetupRatings
     */
    public function testSaveNewFilmSource()
    {$this->start(__CLASS__, __FUNCTION__);

        // There sure be a film_source where film_id=3, but not for IMDb
        $db = getDatabase();
        $source = new Source(Constants::SOURCE_IMDB);
        $filmId = 3;
        $source->saveFilmSourceToDb($filmId);
        $query = "SELECT * FROM film_source" .
                 " WHERE film_id=$filmId" .
                   " AND source_name='".$source->getName()."'";
        $result = $db->query($query);
        $this->assertEquals(1, $result->rowCount(), "There sure be one Film/Source row $filmId/" . $source->getName());
    }

    /**
     * @covers  \RatingSync\Source::saveFilmSourceToDb
     * @depends testObjectCanBeConstructed
     * @depends testSetupRatings
     */
    public function testAddNewFilmSourceImageFromSource()
    {$this->start(__CLASS__, __FUNCTION__);

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
        $this->assertEquals(1, $result->rowCount(), "There sure be one Film/Source row $filmId/" . $source->getName());
        $row = $result->fetch();
        $this->assertEquals("http://example.com/title2_imdb_image.jpeg", $row['image']);
    }

    /**
     * @covers  \RatingSync\Source::saveFilmSourceToDb
     * @depends testObjectCanBeConstructed
     */
    public function testAddNewFilmSourceImageFromNowhere()
    {$this->start(__CLASS__, __FUNCTION__);

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
        $this->assertEquals(1, $result->rowCount(), "There sure be one Film/Source row $filmId/" . $source->getName());
        $row = $result->fetch();
        $this->assertEmpty($row['image']);
    }

    /**
     * @covers  \RatingSync\Source::saveFilmSourceToDb
     * @depends testObjectCanBeConstructed
     * @depends testSetupRatings
     */
    public function testAddNewFilmSourceWithNoOtherFilmSource()
    {$this->start(__CLASS__, __FUNCTION__);

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
        $this->assertEquals(1, $result->rowCount(), "There sure be one Film/Source row $filmId/" . $source->getName());
    }
    
    /**
     * - Streams available for at least 1 film for all provider
     *
     * Expect
     *   - verify the stream URL or each provider
     *
     * @covers \RatingSync\Source::refreshStreamsByFilm
     */
    public function testRefreshStreamsByFilm()
    {$this->start(__CLASS__, __FUNCTION__);
        
        // Setup
        /*
        $filmForNetflix = new Film();
        $filmForNetflix->setUniqueName(TEST_NETFLIX_UNIQUENAME, Constants::SOURCE_NETFLIX);
        $filmForNetflix->setTitle(TEST_NETFLIX_TITLE);
        $filmForNetflix->setYear(TEST_NETFLIX_YEAR);
        $filmForNetflix->saveToDb();
        $filmForNetflixId = $filmForNetflix->getId();
        
        $filmForAmazon = new Film();
        $filmForAmazon->setUniqueName(TEST_AMAZON_UNIQUENAME, Constants::SOURCE_AMAZON);
        $filmForAmazon->setTitle(TEST_AMAZON_TITLE);
        $filmForAmazon->setYear(TEST_AMAZON_YEAR);
        $filmForAmazon->saveToDb();
        $filmForAmazonId = $filmForAmazon->getId();
        */
        
        /*
        $filmForXfinity = new Film();
        $filmForXfinity->setUniqueName(TEST_XFINITY_UNIQUENAME, Constants::SOURCE_XFINITY);
        $filmForXfinity->setUniqueAlt(TEST_XFINITY_UNIQUEALT, Constants::SOURCE_XFINITY);
        $filmForXfinity->setTitle(TEST_XFINITY_TITLE);
        $filmForXfinity->setYear(TEST_XFINITY_YEAR);
        $filmForXfinity->saveToDb();
        $filmForXfinityId = $filmForXfinity->getId();
        
        $tvSeriesForXfinity = new Film();
        $tvSeriesForXfinity->setUniqueName(TEST_XFINITY_UNIQUENAME_TV, Constants::SOURCE_XFINITY);
        $tvSeriesForXfinity->setUniqueAlt(TEST_XFINITY_UNIQUEALT_TV, Constants::SOURCE_XFINITY);
        $tvSeriesForXfinity->setContentType(Film::CONTENT_TV_SERIES);
        $tvSeriesForXfinity->setTitle(TEST_XFINITY_TITLE_TV);
        $tvSeriesForXfinity->setYear(TEST_XFINITY_YEAR_TV);
        $tvSeriesForXfinity->saveToDb();
        $tvSeriesForXfinityId = $tvSeriesForXfinity->getId();
        
        $tvEpisodeForXfinity = new Film();
        $tvEpisodeForXfinity->setUniqueName(TEST_XFINITY_UNIQUENAME_TV, Constants::SOURCE_XFINITY);
        $tvEpisodeForXfinity->setUniqueAlt(TEST_XFINITY_UNIQUEALT_TV, Constants::SOURCE_XFINITY);
        $tvEpisodeForXfinity->setUniqueEpisode(TEST_XFINITY_UNIQUEEPISODE_TV, Constants::SOURCE_XFINITY);
        $tvEpisodeForXfinity->setContentType(Film::CONTENT_TV_SERIES);
        $tvEpisodeForXfinity->setTitle(TEST_XFINITY_TITLE_TV);
        $tvEpisodeForXfinity->setYear(TEST_XFINITY_YEAR_TV);
        $tvEpisodeForXfinity->saveToDb();
        $tvEpisodeForXfinityId = $tvEpisodeForXfinity->getId();
        */

        // Test
        /*
        Source::refreshStreamsByFilm($filmForNetflixId);
        $filmForNetflix = Film::getFilmFromDb($filmForNetflixId);
        Source::refreshStreamsByFilm($filmForAmazonId);
        $filmForAmazon = Film::getFilmFromDb($filmForAmazonId);
        */
        /*
        Source::refreshStreamsByFilm($filmForXfinityId);
        Source::refreshStreamsByFilm($tvSeriesForXfinityId);
        Source::refreshStreamsByFilm($tvEpisodeForXfinityId);
        $filmForXfinity = Film::getFilmFromDb($filmForXfinityId);
        $tvSeriesForXfinity = Film::getFilmFromDb($tvSeriesForXfinityId);
        $tvEpisodeForXfinity = Film::getFilmFromDb($tvEpisodeForXfinityId);
        */

        // Verify
        /*
        $this->assertEquals("http://www.netflix.com/title/".TEST_NETFLIX_UNIQUENAME, $filmForNetflix->getSource(Constants::SOURCE_NETFLIX)->getStreamUrl(), "Netflix stream");
        $this->assertEquals("http://www.amazon.com/gp/video/primesignup?&t=0m0s&redirectToAsin=B00778C6V4&tag=iw_prime_movie-20&ref_=asc_homepage", $filmForAmazon->getSource(Constants::SOURCE_AMAZON)->getStreamUrl(), "Amazon stream");
        */
        /*
        $this->assertEquals("http://xfinitytv.comcast.net/watch/".TEST_XFINITY_UNIQUEALT."/".TEST_XFINITY_UNIQUENAME."/movies#filter=online", $filmForXfinity->getSource(Constants::SOURCE_XFINITY)->getStreamUrl(), "filmForXfinity stream");
        $this->assertStringStartsWith("http://xfinitytv.comcast.net/watch/".TEST_XFINITY_UNIQUEALT_TV."/".TEST_XFINITY_UNIQUENAME_TV."/full-episodes#filter=online", $tvSeriesForXfinity->getSource(Constants::SOURCE_XFINITY)->getStreamUrl(), "tvSeriesForXfinity stream");
        $this->assertEquals("http://xfinitytv.comcast.net/watch/".TEST_XFINITY_UNIQUEALT_TV."/".TEST_XFINITY_UNIQUENAME_TV."/full-episodes#filter=online&episode=".TEST_XFINITY_UNIQUEEPISODE_TV, $tvEpisodeForXfinity->getSource(Constants::SOURCE_XFINITY)->getStreamUrl(), "tvEpisodeForXfinity stream");
        */

        $this->assertTrue(true); // Making sure we made it this far
    }
}

?>
