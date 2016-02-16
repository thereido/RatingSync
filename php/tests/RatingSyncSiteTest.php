<?php
/**
 * RatingSyncSite class for testing as a RatingSyncSite class.
 */
namespace RatingSync;

require_once "../RatingSyncSite.php";
require_once "../Constants.php";

require_once "SiteChild.php";
require_once "ImdbTest.php";
require_once "10DatabaseTest.php";

class RatingSyncSiteTest extends \PHPUnit_Framework_TestCase
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
     * @covers            \RatingSync\RatingSyncSite::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testCannotBeConstructedFromNull()
    {$this->start(__CLASS__, __FUNCTION__);

        new RatingSyncSite(null);
    }

    /**
     * @covers            \RatingSync\RatingSyncSite::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testCannotBeConstructedFromEmptyUsername()
    {$this->start(__CLASS__, __FUNCTION__);

        new RatingSyncSite("");
    }

    /**
     * @covers \RatingSync\RatingSyncSite::__construct
     */
    public function testObjectCanBeConstructed()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new RatingSyncSite(Constants::TEST_RATINGSYNC_USERNAME);
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
        $db = getDatabase();

        DatabaseTest::resetDb();
        $username_imdb = TEST_IMDB_USERNAME;
        $username_rs = Constants::TEST_RATINGSYNC_USERNAME;
        $site = new SiteChild($username_imdb);
        $filename =  __DIR__ . DIRECTORY_SEPARATOR . "testfile" . DIRECTORY_SEPARATOR . "input_ratings_site.xml";
        $films = $site->importRatings(Constants::IMPORT_FORMAT_XML, $filename, $username_rs);

        $username_imdb = "imdb_user1";
        $username_jinni = "jinni_user1";
        $username_rs = "rs_user1";

        $query = "INSERT INTO user (username, password) VALUES ('$username_rs', 'password')";
        $success = $db->query($query);
        $this->assertTrue($success, $query."  SQL Error: ".$db->error);
        $query = "INSERT INTO user_source (user_name, source_name, username, password) VALUES ('$username_rs', '".Constants::SOURCE_IMDB."', 'imdb_user1', 'pwd')";
        $success = $db->query($query);
        $this->assertTrue($success, $query."  SQL Error: ".$db->error);
        $query = "INSERT INTO user_source (user_name, source_name, username, password) VALUES ('$username_rs', '".Constants::SOURCE_JINNI."', 'jinni_user1', 'pwd')";
        $success = $db->query($query);
        $this->assertTrue($success, $query."  SQL Error: ".$db->error);
        $query = "INSERT INTO user_source (user_name, source_name, username, password) VALUES ('$username_rs', '".Constants::SOURCE_RATINGSYNC."', '$username_rs', 'password')";
        $success = $db->query($query);
        $this->assertTrue($success, $query."  SQL Error: ".$db->error);
        
        $filmId = 1; $filmId2 = 2; $filmId4 = 4;
        $result = $db->query("SELECT * FROM rating WHERE film_id=$filmId AND user_name='".Constants::TEST_RATINGSYNC_USERNAME."' AND source_name='".Constants::SOURCE_IMDB."'");
        $rating = new Rating(Constants::SOURCE_IMDB);
        $rating->initFromDbRow($result->fetch_assoc());
        $rating->saveToRs($username_rs, $filmId);
        $rating->saveToRs($username_rs, $filmId2);
        $rating->setYourRatingDate(new \DateTime());
        $rating->saveToRs($username_rs, $filmId4);

        $query = "UPDATE rating SET source_name='".Constants::SOURCE_IMDB."' WHERE film_id=$filmId4 AND user_name='$username_rs'";
        $success = $db->query($query);
        $this->assertTrue($success, $query."  SQL Error: ".$db->error);

        $filmId = 1;
        $query = "UPDATE rating SET yourRatingDate='2015-1-1' WHERE film_id=$filmId AND user_name='".Constants::TEST_RATINGSYNC_USERNAME."' AND source_name='".Constants::SOURCE_RATINGSYNC."'";
        $success = $db->query($query);
        $this->assertTrue($success, $query."  SQL Error: ".$db->error);

        $filmId = 3;
        $query = "UPDATE rating SET source_name='".Constants::SOURCE_IMDB."' WHERE film_id=$filmId AND user_name='".Constants::TEST_RATINGSYNC_USERNAME."'";
        $success = $db->query($query);
        $this->assertTrue($success, $query."  SQL Error: ".$db->error);
    }

    /**
     * @covers \RatingSync\RatingSyncSite::getRatings
     * @depends testObjectCanBeConstructed
     */
    public function testGetRatingsUsernameWithNoMatch()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new RatingSyncSite("---Username--No--Match---");
        $films = $site->getRatings();
        $this->assertEquals(0, count($films), "Zero ratings for a non matching user");
    }

    /**
     * @covers \RatingSync\RatingSyncSite::getRatings
     * @depends testObjectCanBeConstructed
     * @depends testSetupRatings
     */
    public function testGetRatings()
    {$this->start(__CLASS__, __FUNCTION__);

        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $sourceName = Constants::SOURCE_RATINGSYNC;
        $site = new RatingSyncSite($username);
        $films = $site->getRatings();
        $this->assertEquals(5, count($films), "Count ratings for $username");

        $foundFilmId1 = false;  $film1Title = "Frozen";
        $foundFilmId7 = false;  $film7Title = "Title6";
        foreach ($films as $film) {
            $title = $film->getTitle();
            if ($title == $film1Title) {
                $foundFilmId1 = true;
                $this->assertEquals(2013, $film->getYear(), "Year");
                $this->assertEquals(Film::CONTENT_FILM, $film->getContentType(), "ContentType");
                $this->assertEquals("http://example.com/frozen_rs_image.jpeg", $film->getImage(), "Image");
                $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), "Directors");
                $this->assertEquals(array("Adventure", "Animation", "Comedy", "Family", "Fantasy", "Musical"), $film->getGenres(), "Genres");
                $rating = $film->getRating($sourceName);
                $this->assertTrue(!empty($rating), "Rating");
                $this->assertEquals(2, $rating->getYourScore(), "YourScore");
                $this->assertEquals("2015-01-01", date_format($rating->getYourRatingDate(), "Y-m-d"), "YourRatingDate");
                $this->assertEquals(3, $rating->getSuggestedScore(), "SuggestedScore");
                $this->assertEquals(4, $rating->getCriticScore(), "CriticScore");
                $this->assertEquals(5, $rating->getUserScore(), "UserScore");
            } elseif ($title == $film7Title) {
                $foundFilmId7 = true;
                $this->assertEquals(2006, $film->getYear(), "Year");
                $this->assertEquals(Film::CONTENT_FILM, $film->getContentType(), "ContentType");
                $this->assertEquals("http://example.com/title6_image.jpeg", $film->getImage(), "Image");
                $this->assertEquals(array("Director6.1"), $film->getDirectors(), "Directors");
                $this->assertEquals(array("Genre6.1"), $film->getGenres(), "Genres");
                $rating = $film->getRating($sourceName);
                $this->assertTrue(!empty($rating), "Rating");
                $this->assertEquals(6, $rating->getYourScore(), "YourScore");
                $this->assertEquals("2015-01-06", date_format($rating->getYourRatingDate(), "Y-m-d"), "YourRatingDate");
                $this->assertEquals(7, $rating->getSuggestedScore(), "SuggestedScore");
                $this->assertEquals(8, $rating->getCriticScore(), "CriticScore");
                $this->assertEquals(9, $rating->getUserScore(), "UserScore");
            }
        }

        $this->assertTrue($foundFilmId1, "Should find a rating for FilmId 1 ($film1Title)");
        $this->assertTrue($foundFilmId7, "Should find a rating for FilmId 7 ($film7Title)");
    }

    /*RT* testGetRatingsLimitPages *RT*/
    /*RT* testGetRatingsBeginPage *RT*/
    /*RT* testGetRatingsDetailsTrue *RT*/

    /**
     * @covers \RatingSync\RatingSyncSite::getRatings
     * @depends testGetRatings
     */
    public function testGetRatingsFalse()
    {$this->start(__CLASS__, __FUNCTION__);
        
        // Depends on testGetRatings using details=false (default)
        $this->assertTrue(true);
    }

    /**
     * @covers \RatingSync\RatingSyncSite::syncRatings
     * @depends testObjectCanBeConstructed
     * @depends testSetupRatings
     */
    public function testSyncRatings()
    {$this->start(__CLASS__, __FUNCTION__);
        $db = getDatabase();
    
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $site = new RatingSyncSite($username);
        $site->syncRatings($username);

        $query = "SELECT * FROM rating WHERE user_name='".Constants::TEST_RATINGSYNC_USERNAME."' AND source_name='".Constants::SOURCE_RATINGSYNC."' ORDER BY film_id ASC";
        $result = $db->query($query);
        $this->assertEquals(6, $result->num_rows, "Count ".Constants::SOURCE_RATINGSYNC."ratings for ".Constants::TEST_RATINGSYNC_USERNAME);
        $rating = new Rating(Constants::SOURCE_RATINGSYNC);
        while ($row = $result->fetch_assoc()) {
            $rating->initFromDbRow($row);
            $filmId = $row['film_id'];
            if ($filmId == 1) {
                $this->assertEquals(8, $rating->getYourScore(), "Rating score");
                $this->assertEquals("2015-05-04", date_format($rating->getYourRatingDate(), 'Y-m-d'), "Rating date");
            } else if ($filmId == 2) {
                $this->assertEquals(1, $rating->getYourScore(), "Rating score");
                $this->assertEquals("2015-01-01", date_format($rating->getYourRatingDate(), 'Y-m-d'), "Rating date");
            } else if ($filmId == 3) {
                $this->assertEquals(2, $rating->getYourScore(), "Rating score");
                $this->assertEquals("2015-01-02", date_format($rating->getYourRatingDate(), 'Y-m-d'), "Rating date");
            } else if ($filmId == 4) {
                $this->assertFalse(true, "Film Id=4, That rating came from another user");
            } else if ($filmId == 7) {
                $this->assertEquals(6, $rating->getYourScore(), "Rating score");
                $this->assertEquals("2015-01-06", date_format($rating->getYourRatingDate(), 'Y-m-d'), "Rating date");
            }
        }
    }
}

?>
