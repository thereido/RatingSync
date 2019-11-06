<?php
/**
 * RatingSyncSite class for testing as a RatingSyncSite class.
 */
namespace RatingSync;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "RatingSyncSite.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "Constants.php";

require_once "SiteRatingsChild.php";
require_once "ImdbTest.php";
require_once "XfinityTest.php";
require_once "DatabaseTest.php";
require_once "RatingSyncTestCase.php";
require_once "MainTest.php";

class RatingSyncSiteTest extends RatingSyncTestCase
{
    protected function setUp(): void
    {
        parent::setup();
        //$this->verbose = true;
    }

    /**
     * @covers            \RatingSync\RatingSyncSite::__construct
     */
    public function testCannotBeConstructedFromNull()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        new RatingSyncSite(null);
    }

    /**
     * @covers            \RatingSync\RatingSyncSite::__construct
     */
    public function testCannotBeConstructedFromEmptyUsername()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        new RatingSyncSite("");
    }

    /**
     * @covers \RatingSync\RatingSyncSite::__construct
     */
    public function testObjectCanBeConstructed()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new RatingSyncSite(Constants::TEST_RATINGSYNC_USERNAME);

        $this->assertTrue(true); // Making sure we made it this far
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

        $this->assertTrue(MainTest::setupRatings(), "MainTest::setupRatings() failed");
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
                $this->assertEquals(4, $film->getCriticScore($sourceName), "CriticScore");
                $this->assertEquals(5, $film->getUserScore($sourceName), "UserScore");
                $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), "Directors");
                $this->assertEquals(array("Adventure", "Animation", "Comedy", "Family", "Fantasy", "Musical"), $film->getGenres(), "Genres");
                $rating = $film->getRating($sourceName);
                $this->assertTrue(!empty($rating), "Rating");
                $this->assertEquals(2, $rating->getYourScore(), "YourScore");
                $this->assertEquals("2015-01-01", date_format($rating->getYourRatingDate(), "Y-m-d"), "YourRatingDate");
                $this->assertEquals(3, $rating->getSuggestedScore(), "SuggestedScore");
            } elseif ($title == $film7Title) {
                $foundFilmId7 = true;
                $this->assertEquals(2006, $film->getYear(), "Year");
                $this->assertEquals(Film::CONTENT_FILM, $film->getContentType(), "ContentType");
                $this->assertEquals("http://example.com/title6_image.jpeg", $film->getImage(), "Image");
                $this->assertEquals(8, $film->getCriticScore($sourceName), "CriticScore");
                $this->assertEquals(9, $film->getUserScore($sourceName), "UserScore");
                $this->assertEquals(array("Director6.1"), $film->getDirectors(), "Directors");
                $this->assertEquals(array("Genre6.1"), $film->getGenres(), "Genres");
                $rating = $film->getRating($sourceName);
                $this->assertTrue(!empty($rating), "Rating");
                $this->assertEquals(6, $rating->getYourScore(), "YourScore");
                $this->assertEquals("2015-01-06", date_format($rating->getYourRatingDate(), "Y-m-d"), "YourRatingDate");
                $this->assertEquals(7, $rating->getSuggestedScore(), "SuggestedScore");
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

        $this->assertTrue(true); // Making sure we made it this far
    }

    /**
     * @depends testSetupRatings
     */
    public function testSetupStreams()
    {$this->start(__CLASS__, __FUNCTION__);

        $searchTerms = array();
        $searchTerms["uniqueName"] = TEST_XFINITY_UNIQUENAME;
        $searchTerms["uniqueAlt"] = TEST_XFINITY_UNIQUEALT;
        $searchTerms["title"] = TEST_XFINITY_TITLE;
        $searchTerms["year"] = TEST_XFINITY_YEAR;
        $searchTerms["sourceName"] = Constants::SOURCE_XFINITY;
        $film = \RatingSync\search($searchTerms, Constants::TEST_RATINGSYNC_USERNAME)['match'];

        $this->assertFalse(is_null($film));
        $this->assertEquals(TEST_XFINITY_TITLE, $film->getTitle());
    }

    /**
     * @covers \RatingSync\RatingSyncSite::getStreamUrl
     * @depends testSetupRatings
     */
    /* Xfinity unavailable for streams
    public function testGetStreamUrl()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $site = new RatingSyncSite(Constants::TEST_RATINGSYNC_USERNAME);
        $searchTerms["uniqueName"] = TEST_XFINITY_UNIQUENAME;
        $searchTerms["uniqueAlt"] = TEST_XFINITY_UNIQUEALT;
        $searchTerms["sourceName"] = Constants::SOURCE_XFINITY;
        $film = \RatingSync\search($searchTerms, Constants::TEST_RATINGSYNC_USERNAME)['match'];
        $filmId = $film->getId();
        $xfinity = new Xfinity(TEST_XFINITY_USERNAME);
        $url = $xfinity->getStreamUrl($film->getId());
        $source = $film->getSource(Constants::SOURCE_XFINITY);
        $source->setStreamUrl($url);
        $source->refreshStreamDate();
        $source->saveFilmSourceToDb($filmId);

        // Test
        $url = $site->getStreamUrl($filmId);

        // Verify
        $this->assertEquals("http://xfinitytv.comcast.net/watch/".TEST_XFINITY_UNIQUEALT."/".TEST_XFINITY_UNIQUENAME."/movies#filter=online", $url, Constants::SOURCE_XFINITY." streaming URL");
    }
    */

    /**
     * @covers \RatingSync\RatingSyncSite::getStreamUrl
     * @depends testSetupRatings
     */
    /* Xfinity unavailable for streams
    public function testGetStreamUrlTvSeries()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $site = new RatingSyncSite(Constants::TEST_RATINGSYNC_USERNAME);
        $searchTerms["uniqueName"] = TEST_XFINITY_UNIQUENAME_TV;
        $searchTerms["uniqueAlt"] = TEST_XFINITY_UNIQUEALT_TV;
        $searchTerms["sourceName"] = Constants::SOURCE_XFINITY;
        $film = \RatingSync\search($searchTerms, Constants::TEST_RATINGSYNC_USERNAME)['match'];
        $filmId = $film->getId();
        $xfinity = new Xfinity(TEST_XFINITY_USERNAME);
        $url = $xfinity->getStreamUrl($film->getId());
        $source = $film->getSource(Constants::SOURCE_XFINITY);
        $source->setStreamUrl($url);
        $source->refreshStreamDate();
        $source->saveFilmSourceToDb($filmId);

        // Test
        $url = $site->getStreamUrl($filmId);

        // Verify
        $this->assertStringStartsWith("http://xfinitytv.comcast.net/watch/".TEST_XFINITY_UNIQUEALT_TV."/".TEST_XFINITY_UNIQUENAME_TV."/full-episodes#filter=online", $url, Constants::SOURCE_XFINITY." streaming URL");
    }
    */

    /**
     * @covers \RatingSync\RatingSyncSite::getStreamUrl
     * @depends testSetupRatings
     */
    /* Xfinity unavailable for streams
    public function testGetStreamUrlTvEpisode()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $site = new RatingSyncSite(Constants::TEST_RATINGSYNC_USERNAME);
        $searchTerms["uniqueName"] = TEST_XFINITY_UNIQUENAME_TV;
        $searchTerms["uniqueAlt"] = TEST_XFINITY_UNIQUEALT_TV;
        $searchTerms["uniqueEpisode"] = TEST_XFINITY_UNIQUEEPISODE_TV;
        $searchTerms["sourceName"] = Constants::SOURCE_XFINITY;
        $film = \RatingSync\search($searchTerms, Constants::TEST_RATINGSYNC_USERNAME)['match'];
        $filmId = $film->getId();
        $xfinity = new Xfinity(TEST_XFINITY_USERNAME);
        $url = $xfinity->getStreamUrl($film->getId());
        $source = $film->getSource(Constants::SOURCE_XFINITY);
        $source->setStreamUrl($url);
        $source->refreshStreamDate();
        $source->saveFilmSourceToDb($filmId);

        // Test
        $url = $site->getStreamUrl($filmId);

        // Verify
        $this->assertEquals("http://xfinitytv.comcast.net/watch/".TEST_XFINITY_UNIQUEALT_TV."/".TEST_XFINITY_UNIQUENAME_TV."/full-episodes#filter=online&episode=".TEST_XFINITY_UNIQUEEPISODE_TV, $url, Constants::SOURCE_XFINITY." streaming URL");
    }
    */

    /**
     * @covers \RatingSync\RatingSyncSite::getStreamUrl
     * @depends testObjectCanBeConstructed
     */
    /* Xfinity unavailable for streams
    public function testGetStreamUrlFilmNoLongerAvailable()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $site = new RatingSyncSite(Constants::TEST_RATINGSYNC_USERNAME);
        $film = new Film();
        $film->setUniqueName("100000000", Constants::SOURCE_XFINITY);
        $film->setTitle("testGetStreamUrlFilmNoLongerAvailable");
        $film->saveToDb();

        // Test
        $url = $site->getStreamUrl($film->getId());

        // Verify
        $this->assertEmpty($url, "Should be empty ($url)");
    }
    */
}

?>
