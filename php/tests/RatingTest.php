<?php
/**
 * Rating PHPUnit
 */
namespace RatingSync;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "Rating.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "Constants.php";

require_once "SiteRatingsChild.php";
require_once "ImdbTest.php";
require_once "DatabaseTest.php";
require_once "RatingSyncTestCase.php";

// Class to expose protected members and functions
class RatingExt extends \RatingSync\Rating {
    function _empty(): bool { return $this->empty(); }
}

class RatingTest extends RatingSyncTestCase
{
    const DATE_FORMAT = 'Y-m-d';

    protected function setUp(): void
    {
        parent::setup();
        //$this->verbose = true;
    }

    /**
     * @covers            \RatingSync\Rating::__construct
     */
    public function testCannotBeConstructedFromNull()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        new Rating(null);
    }

    /**
     * @covers            \RatingSync\Rating::__construct
     */
    public function testCannotBeConstructedFromInvalidSource()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        new Rating("Netflux");
    }

    /**
     * @covers \RatingSync\Rating::__construct
     */
    public function testObjectCanBeConstructedFromStringValue()
    {$this->start(__CLASS__, __FUNCTION__);

        $rating = new Rating("Jinni");

        $this->assertTrue(true); // Making sure we made it this far
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
     */
    public function testYourScoreCannotBeSetWithNonNumericalString()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $r->setYourScore("Not an int");
    }

    /**
     * @covers  \RatingSync\Rating::setYourScore
     * @depends testObjectCanBeConstructedFromStringValue
     */
    public function testYourScoreCannotBeSetWithNegative()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $r->setYourScore(-1);
    }

    /**
     * @covers  \RatingSync\Rating::setYourScore
     * @depends testObjectCanBeConstructedFromStringValue
     */
    public function testYourScoreCannotBeSetWithHigherThan10()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

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
     */
    public function testYourRatingDateCannotBeSetWithString()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

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
     */
    public function testSuggestedScoreCannotBeSetWithNonNumericalString()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $r->setSuggestedScore("Not an int");
    }

    /**
     * @covers  \RatingSync\Rating::setSuggestedScore
     * @depends testObjectCanBeConstructedFromStringValue
     */
    public function testSuggestedScoreCannotBeSetWithNegative()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        $r = new Rating(\RatingSync\Constants::SOURCE_IMDB);
        $r->setSuggestedScore(-1);
    }

    /**
     * @covers  \RatingSync\Rating::setSuggestedScore
     * @depends testObjectCanBeConstructedFromStringValue
     */
    public function testSuggestedScoreCannotBeSetWithHigherThan10()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

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

        $this->assertTrue(true); // Making sure we made it this far
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
                   " AND user_name='$username_rs'" .
                   " AND active=1";
        $result = $db->query($query);
        $this->assertEquals(1, $result->rowCount(), "There sure be one Film/Source row $filmId/$sourceName");
        $row = $result->fetch();
        $rating = new Rating($sourceName);
        $rating->initFromDbRow($row);

        $this->assertEquals($sourceName, $rating->getSource(), "Source");
        $this->assertEquals(2, $rating->getYourScore(), "Your Score");
        $this->assertEquals("2015-01-02", date_format($rating->getYourRatingDate(), 'Y-m-d'), "Your Rating Date");
        $this->assertEquals(3, $rating->getSuggestedScore(), "Suggested Score");
    }
    
    public function testAddFilms()
    {$this->start(__CLASS__, __FUNCTION__);
        // No test on this. Setup for other tests
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        
        $title = "Title Rating::testSaveToDb";
        $year = 2016;
        $film = new Film();
        $film->setTitle($title);
        $film->setYear($year);
        $film->setContentType(Film::CONTENT_FILM);
        $film->setImage("FilmImage");
        $film->addGenre("Comedy");
        $film->addDirector("Director");
        $this->assertTrue($film->saveToDb(), "Saving film $title");
        
        $title = "Title Rating::testSaveToDbEmptyData";
        $year = 2016;
        $film = new Film();
        $film->setTitle($title);
        $film->setYear($year);
        $film->setContentType(Film::CONTENT_FILM);
        $film->setImage("FilmImage");
        $film->addGenre("Comedy");
        $film->addDirector("Director");
        $this->assertTrue($film->saveToDb(), "Saving film $title");
        
        $title = "Title Rating::testSaveToDbOutsideSite";
        $year = 2016;
        $film = new Film();
        $film->setTitle($title);
        $film->setYear($year);
        $film->setContentType(Film::CONTENT_FILM);
        $film->setImage("FilmImage");
        $film->addGenre("Comedy");
        $film->addDirector("Director");
        $this->assertTrue($film->saveToDb(), "Saving film $title");
        
        $title = "Title Rating::testSaveToDbWithOtherUsernameAndOtherSource";
        $year = 2016;
        $film = new Film();
        $film->setTitle($title);
        $film->setYear($year);
        $film->setContentType(Film::CONTENT_FILM);
        $film->setImage("FilmImage");
        $film->setCriticScore(2, Constants::SOURCE_IMDB);
        $film->setUserScore(2, Constants::SOURCE_IMDB);
        $film->addGenre("Comedy");
        $film->addDirector("Director");
        $rating = new Rating(Constants::SOURCE_IMDB);
        $rating->setYourScore(2);
        $ratingDate = new \DateTime('2015-11-30');
        $rating->setYourRatingDate($ratingDate);
        $rating->setSuggestedScore(2);
        $film->setRating($rating, Constants::SOURCE_IMDB);
        $this->assertTrue($film->saveToDb($username), "Saving film $title");
        $db = getDatabase();
        $newUsername = "rs_user1";
        $querySuccess = $db->query("REPLACE INTO user (username, password) VALUES ('$newUsername', 'password')") !== false;
        $this->assertTrue($querySuccess, "Insert user $newUsername");
        $film = new Film();
        $film->setTitle($title);
        $film->setYear($year);
        $film->setContentType(Film::CONTENT_FILM);
        $film->setImage("FilmImage");
        $film->setCriticScore(1, Constants::SOURCE_RATINGSYNC);
        $film->setUserScore(1, Constants::SOURCE_RATINGSYNC);
        $film->addGenre("Comedy");
        $film->addDirector("Director");
        $rating = new Rating(Constants::SOURCE_RATINGSYNC);
        $rating->setYourScore(1);
        $ratingDate = new \DateTime('2015-12-01');
        $rating->setYourRatingDate($ratingDate);
        $rating->setSuggestedScore(1);
        $film->setRating($rating, Constants::SOURCE_RATINGSYNC);
        $this->assertTrue($film->saveToDb($newUsername), "Saving film $title");
        
        $title = "Title Rating::testSaveToDbRatingExists";
        $year = 2016;
        $film = new Film();
        $film->setTitle($title);
        $film->setYear($year);
        $film->setContentType(Film::CONTENT_FILM);
        $film->setImage("FilmImage");
        $film->setCriticScore(1, Constants::SOURCE_RATINGSYNC);
        $film->setUserScore(1, Constants::SOURCE_RATINGSYNC);
        $film->addGenre("Comedy");
        $film->addDirector("Director");
        $rating = new Rating(Constants::SOURCE_RATINGSYNC);
        $rating->setYourScore(1);
        $ratingDate = new \DateTime('2015-12-01');
        $rating->setYourRatingDate($ratingDate);
        $rating->setSuggestedScore(1);
        $film->setRating($rating, Constants::SOURCE_RATINGSYNC);
        $this->assertTrue($film->saveToDb($username), "Saving film $title");
        
        $title = "Title Rating::testSaveToDbRatingExistsWithNewerDate";
        $year = 2016;
        $film = new Film();
        $film->setTitle($title);
        $film->setYear($year);
        $film->setContentType(Film::CONTENT_FILM);
        $film->setImage("FilmImage");
        $film->setCriticScore(1, Constants::SOURCE_RATINGSYNC);
        $film->setUserScore(1, Constants::SOURCE_RATINGSYNC);
        $film->addGenre("Comedy");
        $film->addDirector("Director");
        $rating = new Rating(Constants::SOURCE_RATINGSYNC);
        $rating->setYourScore(1);
        $rating->setYourRatingDate(new \DateTime('2022-01-01'));
        $rating->setSuggestedScore(1);
        $film->setRating($rating, Constants::SOURCE_RATINGSYNC);
        $this->assertTrue($film->saveToDb($username), "Saving film $title");
    }
    
    /**
     * @covers  \RatingSync\Rating::saveToDb
     */
    public function testSaveToDbEmptyUsername()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        // Get the new Film ID
        $db = getDatabase();
        $title = "Title Rating::testSaveToDb";
        $year = 2016;
        $query = "SELECT id FROM film WHERE title='$title' AND year=$year";
        $result = $db->query($query);
        $filmId = $result->fetch()['id'];
        
        // Test
        $rating = new Rating(Constants::SOURCE_RATINGSYNC);
        $rating->saveToDb("", $filmId);

        // Verify
        // Expect exception
    }
    
    /**
     * @covers  \RatingSync\Rating::saveToDb
     */
    public function testSaveToDbEmptyFilmId()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);
        
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
        $filmId = $result->fetch()['id'];
        
        // Test
        $username = "notfound_username";
        $rating = new Rating(Constants::SOURCE_RATINGSYNC);
        $rating->setYourScore(5);
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
        $rating->setYourScore(5);
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

        // Get the new Film ID
        $db = getDatabase();
        $title = "Title Rating::testSaveToDb";
        $year = 2016;
        $query = "SELECT id FROM film WHERE title='$title' AND year=$year";
        $result = $db->query($query);
        $filmId = $result->fetch()['id'];

        
        // Test
        $success = $rating->saveToDb($username, $filmId);

        // Verify
        $this->assertTrue($success, "Rating::saveToDb should succeed");
        $query = "SELECT * FROM rating WHERE user_name='$username' AND film_id=$filmId AND source_name='".$rating->getSource()."' AND active=1";
        $result = $db->query($query);
        $this->assertEquals(1, $result->rowCount());
        $dbRating = new Rating($rating->getSource());
        $dbRating->initFromDbRow($result->fetch());
        $this->assertEquals($rating->getYourScore(), $dbRating->getYourScore(), 'Your score');
        $this->assertEquals(date_format($rating->getYourRatingDate(), 'Y-m-d'), date_format($dbRating->getYourRatingDate(), 'Y-m-d'), "Your rating date");
        $this->assertEquals($rating->getSuggestedScore(), $dbRating->getSuggestedScore(), 'Your score');
    }
    
    /**
     * - Do not set rating data except source
     * - No ratings for this film/username/source in the db
     *
     * Expect
     *   - No rating row in db for this film/username/source combination
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
        $filmId = $result->fetch()['id'];
        
        // Test
        $success = $rating->saveToDb($username, $filmId);

        // Verify
        $this->assertTrue($success, "Rating::saveToDb should succeed");
        $query = "SELECT * FROM rating WHERE user_name='$username' AND film_id=$filmId AND source_name='".$rating->getSource()."' AND active=1";
        $result = $db->query($query);
        $this->assertEquals(0, $result->rowCount());
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

        // Get the new Film ID
        $db = getDatabase();
        $title = "Title Rating::testSaveToDbOutsideSite";
        $year = 2016;
        $query = "SELECT id FROM film WHERE title='$title' AND year=$year";
        $result = $db->query($query);
        $filmId = $result->fetch()['id'];
        
        // Test
        $success = $rating->saveToDb($username, $filmId);

        // Verify
        $this->assertTrue($success, "Rating::saveToDb should succeed");
        $query = "SELECT * FROM rating WHERE user_name='$username' AND film_id=$filmId AND source_name='".$rating->getSource()."' AND active=1";
        $result = $db->query($query);
        $this->assertEquals(1, $result->rowCount());
        $dbRating = new Rating($rating->getSource());
        $dbRating->initFromDbRow($result->fetch());
        $this->assertEquals($rating->getYourScore(), $dbRating->getYourScore(), 'Your score');
        $this->assertEquals(date_format($rating->getYourRatingDate(), 'Y-m-d'), date_format($dbRating->getYourRatingDate(), 'Y-m-d'), "Your rating date");
        $this->assertEquals($rating->getSuggestedScore(), $dbRating->getSuggestedScore(), 'Your score');
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

        // Get the new Film ID
        $db = getDatabase();
        $title = "Title Rating::testSaveToDbWithOtherUsernameAndOtherSource";
        $year = 2016;
        $query = "SELECT id FROM film WHERE title='$title' AND year=$year";
        $result = $db->query($query);
        $filmId = $result->fetch()['id'];
        
        // Test
        $success = $rating->saveToDb($username, $filmId);

        // Verify
        $this->assertTrue($success, "Rating::saveToDb should succeed");
        $query = "SELECT * FROM rating WHERE film_id=$filmId AND active=1";
        $result = $db->query($query);
        $this->assertEquals(3, $result->rowCount(), "Should be 3 ratings for this film");
        foreach ($result->fetchAll() as $row) {
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

            if ($dbSource == $rating->getSource()) {
                if ($dbUsername == $username) {
                    $this->assertEquals($rating->getYourScore(), $dbYourScore, 'Your score');
                    $this->assertEquals(date_format($rating->getYourRatingDate(), 'Y-m-d'), $dbRatingDateStr, "Your rating date");
                    $this->assertEquals($rating->getSuggestedScore(), $dbSuggestedScore, 'Your score');
                }
                elseif ($dbUsername == "rs_user1") {
                    $this->assertEquals(1, $dbYourScore, 'Your score');
                    $this->assertEquals('2015-12-01', $dbRatingDateStr, "Your rating date");
                    $this->assertEquals(1, $dbSuggestedScore, 'Your score');
                }
            }
            elseif ($dbSource == Constants::SOURCE_IMDB) {
                $this->assertEquals(2, $dbYourScore, 'Your score');
                $this->assertEquals('2015-11-30', $dbRatingDateStr, "Your rating date");
                $this->assertEquals(2, $dbSuggestedScore, 'Your score');
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

        // Get the new Film ID
        $db = getDatabase();
        $title = "Title Rating::testSaveToDbRatingExists";
        $year = 2016;
        $query = "SELECT id FROM film WHERE title='$title' AND year=$year";
        $result = $db->query($query);
        $filmId = $result->fetch()['id'];
        
        // Test
        $success = $rating->saveToDb($username, $filmId);

        // Verify
        $this->assertTrue($success, "Rating::saveToDb should succeed");
        $query = "SELECT * FROM rating WHERE user_name='$username' AND film_id=$filmId AND source_name='".$rating->getSource()."' AND active=1";
        $result = $db->query($query);
        $this->assertEquals(1, $result->rowCount());
        $dbRating = new Rating($rating->getSource());
        $dbRating->initFromDbRow($result->fetch());
        $this->assertEquals($rating->getYourScore(), $dbRating->getYourScore(), 'Your score');
        $this->assertEquals(date_format($rating->getYourRatingDate(), 'Y-m-d'), date_format($dbRating->getYourRatingDate(), 'Y-m-d'), "Your rating date");
        $this->assertEquals($rating->getSuggestedScore(), $dbRating->getSuggestedScore(), 'Your score');
        
        $query = "SELECT * FROM rating WHERE user_name='$username' AND film_id=$filmId AND source_name='".$rating->getSource()."'" .
                   " AND yourRatingDate='2015-12-01' AND active=0";
        $result = $db->query($query);
        $this->assertEquals(1, $result->rowCount());
        $dbRating = new Rating($rating->getSource());
        $dbRating->initFromDbRow($result->fetch());
        $this->assertEquals(1, $dbRating->getYourScore(), 'Your score');
        $this->assertEquals(1, $dbRating->getSuggestedScore(), 'Your score');
    }
    
    /**
     * - Existing rating in the db for this $username/$film/source
     * - Existing rating has yourRatingDate in the past (not empty)
     * - Set rating source
     * - For this rating set yourRatingDate newer than the one set by testSaveToDbRatingExists
     * - Leave all other data empty
     *
     * Expect
     *   - Verify rating and archive tables both have the same data (except rating date)
     *   - Verify the data is from existing (except rating date)
     *   - Verify yourRatingDate from the db is today and from the archive it is the original date
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
        $todayDate = new \DateTime();
        $originalRatingDate = "2015-12-06";
        $rating->setYourRatingDate(new \DateTime());

        // Get the new Film ID
        $db = getDatabase();
        $title = "Title Rating::testSaveToDbOutsideSite";
        $year = 2016;
        $query = "SELECT id FROM film WHERE title='$title' AND year=$year";
        $result = $db->query($query);
        $filmId = $result->fetch()['id'];
        
        // Test
        $success = $rating->saveToDb($username, $filmId);

        // Verify
        $this->assertTrue($success, "Rating::saveToDb should succeed");
        $query = "SELECT * FROM rating WHERE user_name='$username' AND film_id=$filmId AND source_name='".$rating->getSource()."' AND active=1";
        $result = $db->query($query);
        $this->assertEquals(1, $result->rowCount());
        $dbRating = new Rating($rating->getSource());
        $dbRating->initFromDbRow($result->fetch());
        $this->assertEquals(6, $dbRating->getYourScore(), 'Your score');
        $this->assertEquals(date_format($todayDate, 'Y-m-d'), date_format($dbRating->getYourRatingDate(), 'Y-m-d'), "Your rating date");
        $this->assertEquals(6, $dbRating->getSuggestedScore(), 'Suggested score');
        
        $query = "SELECT * FROM rating WHERE user_name='$username' AND film_id=$filmId AND source_name='".$rating->getSource()."' AND active=0" .
                   " AND yourRatingDate='$originalRatingDate'";
        $result = $db->query($query);
        $this->assertEquals(0, $result->rowCount());
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

        // Get the new Film ID
        $db = getDatabase();
        $title = "Title Rating::testSaveToDbRatingExistsWithNewerDate";
        $year = 2016;
        $query = "SELECT id FROM film WHERE title='$title' AND year=$year";
        $result = $db->query($query);
        $filmId = $result->fetch()['id'];
        
        // Test
        $success = $rating->saveToDb($username, $filmId);

        // Verify
        $this->assertTrue($success, "Rating::saveToDb should succeed");
        $query = "SELECT * FROM rating WHERE user_name='$username' AND film_id=$filmId AND source_name='".$rating->getSource()."' AND active=1";
        $result = $db->query($query);
        $this->assertEquals(1, $result->rowCount());
        $dbRating = new Rating($rating->getSource());
        $dbRating->initFromDbRow($result->fetch());
        $this->assertEquals(1, $dbRating->getYourScore(), 'Your score');
        $this->assertEquals('2022-01-01', $dbRating->getYourRatingDate()?->format(self::DATE_FORMAT), "Your rating date");
        $this->assertEquals(1, $dbRating->getSuggestedScore(), 'Suggested score');
        
        $query = "SELECT * FROM rating WHERE user_name='$username' AND film_id=$filmId AND source_name='".$rating->getSource()."' AND active=0" .
                   " AND yourRatingDate='".date_format($rating->getYourRatingDate(), 'Y-m-d')."' ORDER BY ts LIMIT 1";
        $result = $db->query($query);
        $this->assertEquals(1, $result->rowCount());
        $dbArchiveRating = new Rating($rating->getSource());
        $dbArchiveRating->initFromDbRow($result->fetch());
        $this->assertEquals($rating->getYourScore(), $dbArchiveRating->getYourScore(), 'Your score');
        $this->assertEquals(date_format($rating->getYourRatingDate(), 'Y-m-d'), date_format($dbArchiveRating->getYourRatingDate(), 'Y-m-d'), "Your rating date");
        $this->assertEquals($rating->getSuggestedScore(), $dbArchiveRating->getSuggestedScore(), 'Suggested score');
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
        $originalRatingDate = "2022-01-01";
        $newRatingDate = "2022-02-01";
        $rating->setYourRatingDate(new \DateTime($newRatingDate));
        $rating->setSuggestedScore(8);

        // Get the new Film ID
        $db = getDatabase();
        $title = "Title Rating::testSaveToDbRatingExistsWithNewerDate";
        $year = 2016;
        $query = "SELECT id FROM film WHERE title='$title' AND year=$year";
        $result = $db->query($query);
        $filmId = $result->fetch()['id'];
        
        // Test
        $success = $rating->saveToDb($username, $filmId);

        // Verify
        $this->assertTrue($success, "Rating::saveToDb should succeed");
        $query = "SELECT * FROM rating WHERE user_name='$username' AND film_id=$filmId AND source_name='".$rating->getSource()."' AND active=1";
        $result = $db->query($query);
        $this->assertEquals(1, $result->rowCount());
        $dbRating = new Rating($rating->getSource());
        $dbRating->initFromDbRow($result->fetch());
        $this->assertEquals($rating->getYourScore(), $dbRating->getYourScore(), 'Your score');
        $this->assertEquals($newRatingDate, date_format($dbRating->getYourRatingDate(), 'Y-m-d'), "Your rating date");
        $this->assertEquals($rating->getSuggestedScore(), $dbRating->getSuggestedScore(), 'Suggested score');
        
        $query = "SELECT * FROM rating WHERE user_name='$username' AND film_id=$filmId AND source_name='".$rating->getSource()."' AND active=0" .
                   " AND yourRatingDate='$originalRatingDate' ORDER BY ts LIMIT 1";
        $result = $db->query($query);
        $this->assertEquals(1, $result->rowCount());
        $dbArchiveRating = new Rating($rating->getSource());
        $dbArchiveRating->initFromDbRow($result->fetch());
        $this->assertEquals(1, $dbArchiveRating->getYourScore(), 'Your score');
        $this->assertEquals($originalRatingDate, date_format($dbArchiveRating->getYourRatingDate(), 'Y-m-d'), "Your rating date");
        $this->assertEquals(1, $dbArchiveRating->getSuggestedScore(), 'Suggested score');
    }

    /**
     * @covers  \RatingSync\Rating::saveToRs
     */
    public function testSaveToRsSetNulls()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        $rating = new Rating(Constants::SOURCE_RATINGSYNC);
        $rating->saveToRs(null, null);
    }

    /**
     * @covers  \RatingSync\Rating::saveToRs
     */
    public function testSaveToRsSetNullUsername()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        $rating = new Rating(Constants::SOURCE_RATINGSYNC);
        $filmId = 1;
        $rating->saveToRs(null, $filmId);
    }

    /**
     * @covers  \RatingSync\Rating::saveToRs
     */
    public function testSaveToRsSetNullFilmId()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

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
        $rating->setYourRatingDate(new \DateTime());
        $rating->setYourScore(6);
        $username = "Garbage Username";
        $filmId = 1;
        $success = $rating->saveToRs($username, $filmId);

        $this->assertFalse($success, "Should fail with no user found");
    }

    /**
     * @covers  \RatingSync\Rating::saveToDb
     * @depends testObjectCanBeConstructedFromStringValue
     * @depends testSetupRatings
     */
    public function testSaveToRsFilmNotFound()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\Exception::class);

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
        $film = new Film();
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
        $film = new Film();
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
        $film = new Film();
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

        $this->assertTrue(true); // Making sure we made it this far
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
        $filmId = $result->fetch()['id'];
        $this->assertGreaterThan(0, $filmId, "Film ID should be found (greater than 0)");

        // Get the rating in the db from another source (IMDb)
        $query = "SELECT * FROM rating WHERE user_name='$username' AND film_id=$filmId AND source_name='".Constants::SOURCE_IMDB."' AND active=1";
        $result = $db->query($query);
        $row = $result->fetch();
        $rating = new Rating(Constants::SOURCE_IMDB);
        $rating->initFromDbRow($row);

        // Copy the new IMDb rating to a RS rating
        $rating->saveToRs($username, $filmId);

        $query = "SELECT * FROM rating WHERE user_name='$username' AND film_id=$filmId AND source_name='".Constants::SOURCE_RATINGSYNC."' AND active=1";
        $result = $db->query($query);
        $this->assertEquals(1, $result->rowCount());
        $row = $result->fetch();
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
        $filmId = $result->fetch()['id'];

        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $rating = new Rating(Constants::SOURCE_RATINGSYNC);
        $rating->setYourScore(2);
        $ratingDate = new \DateTime();
        $rating->setYourRatingDate($ratingDate);

        // Update the same RS rating
        $rating->saveToRs($username, $filmId);

        $query = "SELECT * FROM rating WHERE user_name='$username' AND film_id=$filmId AND source_name='".Constants::SOURCE_RATINGSYNC."' AND active=1";
        $result = $db->query($query);
        $this->assertEquals(1, $result->rowCount());
        $row = $result->fetch();
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
        $filmId = $result->fetch()['id'];

        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $rating = new Rating(Constants::SOURCE_RATINGSYNC);
        $rating->setYourScore(2);
        $ratingDate = new \DateTime("2016-01-14");
        $rating->setYourRatingDate($ratingDate);

        // Update the same RS rating
        $rating->saveToRs($username, $filmId);

        $query = "SELECT * FROM rating WHERE user_name='$username' AND film_id=$filmId AND source_name='".Constants::SOURCE_RATINGSYNC."' AND active=1";
        $result = $db->query($query);
        $this->assertEquals(1, $result->rowCount());
        $row = $result->fetch();
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
        $filmId = $result->fetch()['id'];

        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $rating = new Rating(Constants::SOURCE_RATINGSYNC);
        $rating->setYourScore(3);

        // Update the same RS rating
        $rating->saveToRs($username, $filmId);

        $query = "SELECT * FROM rating WHERE user_name='$username' AND film_id=$filmId AND source_name='".Constants::SOURCE_RATINGSYNC."' AND active=1";
        $result = $db->query($query);
        $this->assertEquals(1, $result->rowCount());
        $row = $result->fetch();
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
        $filmId = $result->fetch()['id'];

        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $rating = new Rating(Constants::SOURCE_IMDB);
        $rating->setYourScore(3);

        // Update the same RS rating
        $rating->saveToRs($username, $filmId);

        $query = "SELECT * FROM rating WHERE user_name='$username' AND film_id=$filmId AND source_name='".Constants::SOURCE_RATINGSYNC."' AND active=1";
        $result = $db->query($query);
        $this->assertEquals(1, $result->rowCount());
        $row = $result->fetch();
        $this->assertEquals(3, $row['yourScore'], 'Your score');
        $this->assertEquals((new \DateTime())->format(self::DATE_FORMAT), ($row['yourRatingDate']), "Your rating date");
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
        $query = "SELECT * FROM rating WHERE user_name='$username' AND film_id=$filmId AND source_name='".Constants::SOURCE_IMDB."' AND active=1";
        $result = $db->query($query);
        $row = $result->fetch();
        $rating = new Rating(Constants::SOURCE_IMDB);
        $rating->initFromDbRow($row);

        $rating->setYourScore(null);
        $rating->saveToRs($username, $filmId);
        
        $query = "SELECT * FROM rating WHERE user_name='$username' AND film_id=$filmId AND source_name='".Constants::SOURCE_RATINGSYNC."' AND active=1";
        $result = $db->query($query);
        $this->assertEquals(1, $result->rowCount());
        $row = $result->fetch();
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
        $rating->saveToRs($username, $filmId);
        
        $query = "SELECT * FROM rating WHERE user_name='$username' AND film_id=$filmId AND source_name='".Constants::SOURCE_RATINGSYNC."' AND active=1";
        $result = $db->query($query);
        $this->assertEquals(1, $result->rowCount());
        $row = $result->fetch();
        $this->assertEquals(1, $row['yourScore'], 'Your score');
        $this->assertEquals('2016-02-04', $row['yourRatingDate'], "Your rating date");
        $this->assertEmpty($row['suggestedScore'], 'Your score');
    }

    /**
     * @covers  \RatingSync\Rating::saveToRs
     * @depends testSaveToRsNewRsRatingFromAnotherSource
     */
    public function testSaveToRsFilmSourceExists()
    {$this->start(__CLASS__, __FUNCTION__);
        // Nothing to do because it's tested by testSaveToRsNewRsRatingFromAnotherSource

        $this->assertTrue(true); // Making sure we made it this far
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
        $filmId = $result->fetch()['id'];

        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $rating = new Rating(Constants::SOURCE_RATINGSYNC);
        $rating->setYourScore(3);
        $rating->setYourRatingDate(new \DateTime('2016-01-15'));

        // Update the same RS rating
        $rating->saveToRs($username, $filmId);

        $query = "SELECT * FROM rating WHERE user_name='$username' AND film_id=$filmId AND source_name='".Constants::SOURCE_RATINGSYNC."' AND active=1";
        $result = $db->query($query);
        $this->assertEquals(1, $result->rowCount());
        $row = $result->fetch();
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
        $filmId = $result->fetch()['id'];

        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $rating = new Rating(Constants::SOURCE_RATINGSYNC);
        $rating->setYourScore(3);
        $rating->setYourRatingDate(new \DateTime('2016-01-16'));

        // Update the same RS rating
        $rating->saveToRs($username, $filmId);

        $query = "SELECT * FROM rating WHERE user_name='$username' AND film_id=$filmId AND source_name='".Constants::SOURCE_RATINGSYNC."' AND active=1";
        $result = $db->query($query);
        $this->assertEquals(1, $result->rowCount());
        $row = $result->fetch();
        $this->assertEquals(3, $row['yourScore'], 'Your score');
        $this->assertEquals("2016-01-16", $row['yourRatingDate'], "Your rating date");
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
        $filmId = $result->fetch()['id'];

        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $rating = new Rating(Constants::SOURCE_RATINGSYNC);
        $rating->setYourScore(4);
        $rating->setYourRatingDate(new \DateTime('2016-01-17'));
        $rating->setSuggestedScore(8);

        // Update the same RS rating
        $rating->saveToRs($username, $filmId);

        $query = "SELECT * FROM rating WHERE user_name='$username' AND film_id=$filmId AND source_name='".Constants::SOURCE_RATINGSYNC."' AND active=1";
        $result = $db->query($query);
        $this->assertEquals(1, $result->rowCount());
        $row = $result->fetch();
        $this->assertEquals(4, $row['yourScore'], 'Your score');
        $this->assertEquals("2016-01-17", $row['yourRatingDate'], "Your rating date");
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
        $filmId = $result->fetch()['id'];

        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $rating = new Rating(Constants::SOURCE_RATINGSYNC);
        $rating->setYourScore(5);
        $rating->setYourRatingDate(new \DateTime('2016-01-10'));

        // Update the same RS rating
        $rating->saveToRs($username, $filmId);

        $query = "SELECT * FROM rating WHERE user_name='$username' AND film_id=$filmId AND source_name='".Constants::SOURCE_RATINGSYNC."' AND active=1";
        $result = $db->query($query);
        $this->assertEquals(1, $result->rowCount());
        $row = $result->fetch();
        $this->assertEquals(4, $row['yourScore'], 'Your score');
        $this->assertEquals("2016-01-17", $row['yourRatingDate'], "Your rating date");
    }

    /**
     * - Construct Rating with source RatingSync
     * - Do not set any other values
     *
     * Expect
     *   - true
     *
     * @covers  \RatingSync\Rating::empty
     * @depends testYourScoreCanBeSetWithInt
     * @depends testYourRatingDateCanBeSetWithDateObject
     * @depends testSuggestedScoreCanBeSetWithInt
     */
    public function testEmpty()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $rating = new RatingExt(Constants::SOURCE_RATINGSYNC);

        // Test
        $isEmpty = $rating->_empty();

        // Verify
        $this->assertTrue($isEmpty);
    }

    /**
     * - Construct Rating with source IMDb
     * - Do not set any other values
     *
     * Expect
     *   - true
     *
     * @covers  \RatingSync\Rating::empty
     * @depends testEmpty
     */
    public function testEmptyExternal()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $rating = new RatingExt(Constants::SOURCE_IMDB);

        // Test
        $isEmpty = $rating->_empty();

        // Verify
        $this->assertTrue($isEmpty);
    }

    /**
     * - Construct Rating with source RatingSync
     * - Set yourRatingDate
     *
     * Expect
     *   - false
     *
     * @covers  \RatingSync\Rating::empty
     * @depends testEmpty
     */
    public function testEmptyWithDate()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $rating = new RatingExt(Constants::SOURCE_RATINGSYNC);
        $rating->setYourRatingDate(new \DateTime());

        // Test
        $isEmpty = $rating->_empty();

        // Verify
        $this->assertFalse($isEmpty);
    }

    /**
     * - Construct Rating with source RatingSync
     * - Set score
     *
     * Expect
     *   - false
     *
     * @covers  \RatingSync\Rating::empty
     * @depends testEmpty
     */
    public function testEmptyWithScore()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $rating = new RatingExt(Constants::SOURCE_RATINGSYNC);
        $rating->setYourScore(7);

        // Test
        $isEmpty = $rating->_empty();

        // Verify
        $this->assertFalse($isEmpty);
    }

    /**
     * - Construct Rating with source RatingSync
     * - Set suggested score
     *
     * Expect
     *   - false
     *
     * @covers  \RatingSync\Rating::empty
     * @depends testEmpty
     */
    public function testEmptyWithSuggested()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $rating = new RatingExt(Constants::SOURCE_RATINGSYNC);
        $rating->setSuggestedScore(4);

        // Test
        $isEmpty = $rating->_empty();

        // Verify
        $this->assertFalse($isEmpty);
    }

    /**
     * - Construct Rating with source RatingSync
     * - Set yourRatingDate and your score
     *
     * Expect
     *   - false
     *
     * @covers  \RatingSync\Rating::empty
     * @depends testEmpty
     */
    public function testEmptyWithDateAndScore()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $rating = new RatingExt(Constants::SOURCE_RATINGSYNC);
        $rating->setYourRatingDate(new \DateTime());
        $rating->setYourScore(7);

        // Test
        $isEmpty = $rating->_empty();

        // Verify
        $this->assertFalse($isEmpty);
    }

    /**
     * - Construct Rating with source RatingSync
     * - Set yourRatingDate and suggested score
     *
     * Expect
     *   - false
     *
     * @covers  \RatingSync\Rating::empty
     * @depends testEmpty
     */
    public function testEmptyWithDateAndSuggested()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $rating = new RatingExt(Constants::SOURCE_RATINGSYNC);
        $rating->setYourRatingDate(new \DateTime());
        $rating->setSuggestedScore(7);

        // Test
        $isEmpty = $rating->_empty();

        // Verify
        $this->assertFalse($isEmpty);
    }

    /**
     * - Construct Rating with source RatingSync
     * - Set suggested score and your score
     *
     * Expect
     *   - false
     *
     * @covers  \RatingSync\Rating::empty
     * @depends testEmpty
     */
    public function testEmptyWithScoreAndSuggested()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $rating = new RatingExt(Constants::SOURCE_RATINGSYNC);
        $rating->setYourScore(5);
        $rating->setSuggestedScore(6);

        // Test
        $isEmpty = $rating->_empty();

        // Verify
        $this->assertFalse($isEmpty);
    }

    /**
     * - Construct Rating with source RatingSync
     * - Set all 3, date, suggested score and your score
     *
     * Expect
     *   - false
     *
     * @covers  \RatingSync\Rating::empty
     * @depends testEmpty
     */
    public function testEmptyWithAllValues()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $rating = new RatingExt(Constants::SOURCE_RATINGSYNC);
        $rating->setYourRatingDate(new \DateTime());
        $rating->setYourScore(5);
        $rating->setSuggestedScore(6);

        // Test
        $isEmpty = $rating->_empty();

        // Verify
        $this->assertFalse($isEmpty);
    }

    /**
     * - No ratings (user/film) newer than 2022/10/16 in the DB
     * - Create rating on 2022/10/17 with watched=true
     *
     * Expect
     *   - New active rating in the DB with watched=true
     *
     * @covers \RatingSync\Rating::saveRatingToDb
     * @depends testSaveToDb
     */
    public function testWatched()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $filmId = 1;
        $newScore = 5;
        $watched = true;
        $newDate = new \DateTime('2022-10-17');

        // Test
        $success = Rating::saveRatingToDb($filmId, $username, SetRatingScoreValue::create($newScore), $watched, $newDate);

        // Verify
        $this->assertTrue($success, "Rating::saveRatingToDb should succeed");
        $dbRating = Rating::getRatingFromDb($username, Constants::SOURCE_RATINGSYNC, $filmId);
        $this->assertEquals($newScore, $dbRating->getYourScore(), 'Your score');
        $this->assertEquals(date_format($newDate, 'Y-m-d'), date_format($dbRating->getYourRatingDate(), 'Y-m-d'), "Your rating date");
        $this->assertEquals($watched, $dbRating->getWatched(), 'Watched');
    }

    /**
     * - No ratings (user/film) on the current date in the DB
     * - Create rating with without setting watched or date
     *
     * Expect
     *   - New active rating in the DB with watched=true
     *
     * @covers \RatingSync\Rating::saveRatingToDb
     * @depends testSaveToDb
     * @depends testWatched
     */
    public function testWatchedDefault()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $filmId = 1;
        $newScore = 6;
        $watched = true;
        $today = new \DateTime();

        // Test
        $success = Rating::saveRatingToDb($filmId, $username, SetRatingScoreValue::create($newScore));

        // Verify
        $this->assertTrue($success, "Rating::saveRatingToDb should succeed");
        $dbRating = Rating::getRatingFromDb($username, Constants::SOURCE_RATINGSYNC, $filmId);
        $this->assertEquals($newScore, $dbRating->getYourScore(), 'Your score');
        $this->assertEquals(date_format($today, 'Y-m-d'), date_format($dbRating->getYourRatingDate(), 'Y-m-d'), "Your rating date");
        $this->assertEquals($watched, $dbRating->getWatched(), 'Watched');
    }

    /**
     * - Create rating with watched=false and the date older than the active rating
     *
     * Expect
     *   - New rating in the DB with watched=false
     *
     * @covers \RatingSync\Rating::saveRatingToDb
     * @depends testSaveToDb
     * @depends testWatched
     */
    public function testWatchedFalse()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $filmId = 1;
        $newScore = 4;
        $watched = false;
        $newDate = new \DateTime('1957-05-17');

        // Test
        $success = Rating::saveRatingToDb($filmId, $username, SetRatingScoreValue::create($newScore), $watched, $newDate);

        // Verify
        $this->assertTrue($success, "Rating::saveRatingToDb should succeed");
        $archive = Rating::getInactiveRatingsFromDb($username, Constants::SOURCE_RATINGSYNC, $filmId);
        $dbRating = $this->getRatingByDate($newDate, $archive);
        $this->assertEquals($newScore, $dbRating->getYourScore(), 'Your score');
        $this->assertEquals(date_format($newDate, 'Y-m-d'), date_format($dbRating->getYourRatingDate(), 'Y-m-d'), "Your rating date");
        $this->assertEquals($watched, $dbRating->getWatched(), 'Watched');
    }

    /**
     * - Create rating watched=true and a "viewing" score and the date older than the active rating
     *
     * Expect
     *   - New active rating in the DB with watched=true
     *
     * @covers \RatingSync\Rating::saveRatingToDb
     * @depends testSaveToDb
     * @depends testWatched
     */
    public function testWatchedViewing()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $filmId = 1;
        $watched = true;
        $newDate = new \DateTime('1957-05-18');

        // Test
        $success = Rating::saveRatingToDb($filmId, $username, SetRatingScoreValue::View, $watched, $newDate);

        // Verify
        $this->assertTrue($success, "Rating::saveRatingToDb should succeed");
        $archive = Rating::getInactiveRatingsFromDb($username, Constants::SOURCE_RATINGSYNC, $filmId);
        $dbRating = $this->getRatingByDate($newDate, $archive);
        $this->assertEquals(SetRatingScoreValue::View->getScore(), $dbRating->getYourScore(), 'Your score');
        $this->assertEquals(date_format($newDate, 'Y-m-d'), date_format($dbRating->getYourRatingDate(), 'Y-m-d'), "Your rating date");
        $this->assertEquals($watched, $dbRating->getWatched(), 'Watched');
    }

    /**
     * - Create rating watched=false and a "viewing" score
     *
     * Expect
     *   - Failure. A viewing without watching is invalid.
     *
     * @covers \RatingSync\Rating::saveRatingToDb
     * @depends testSaveToDb
     * @depends testWatchedViewing
     */
    public function testWatchedViewingFalse()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $filmId = 1;
        $watched = false;
        $newDate = new \DateTime('1957-05-19');

        // Test
        $success = Rating::saveRatingToDb($filmId, $username, SetRatingScoreValue::View, $watched, $newDate);

        // Verify
        $this->assertFalse($success, "Rating::saveRatingToDb should fail");
        $archive = Rating::getInactiveRatingsFromDb($username, Constants::SOURCE_RATINGSYNC, $filmId);
        $dbRating = $this->getRatingByDate($newDate, $archive);
        $this->assertNull($dbRating, "No rating should have been created");
    }

    private function getRatingByDate( \DateTime $date, array $ratings ): Rating | null
    {
        $rating = null;
        foreach ($ratings as $oneRating) {
            if ( date_format($date, 'Y-m-d') == date_format($oneRating->getYourRatingDate(), 'Y-m-d') ) {
                $rating = $oneRating;
                break;
            }
        }

        return $rating;
    }

    /**
     * Test getRatingFromDb($username, $sourceName, $filmId)
     * including a user/source/film combo with multiple archived ratings
     */

}

?>
