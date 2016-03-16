<?php
/**
 * main.php PHPUnit
 */
namespace RatingSync;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "Constants.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "main.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "Film.php";

require_once "SiteChild.php";
require_once "ImdbTest.php";
require_once "RatingSyncSiteTest.php";
require_once "10DatabaseTest.php";

class MainTest extends \PHPUnit_Framework_TestCase
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
    
    public function testSetup()
    {$this->start(__CLASS__, __FUNCTION__);
    
        $this->assertTrue(RatingSyncSiteTest::setupRatings(), "RatingSyncSiteTest::setupRatings() failed");
    }
    
    /**
     * @covers \RatingSync\search
     * @depends testSetup
     */
    public function testSearchDbExists()
    {$this->start(__CLASS__, __FUNCTION__);
        $db = getDatabase();

        // Test
        $searchTerms = array("uniqueName" => "tt2294629");
        $film = search($searchTerms); // Frozen (2013)
        $filmId = $film->getId();

        // Verify database - film
        $film = Film::GetFilmFromDb($filmId, new HttpRatingSync(getUsername()), getUsername());
        $this->assertEquals("Frozen", $film->getTitle(), "Title");
        $this->assertEquals(2013, $film->getYear(), "Year");
        $this->assertEquals(Film::CONTENT_FILM, $film->getContentType(), 'Content Type');
        $this->assertEquals("http://example.com/frozen_rs_image.jpeg", $film->getImage(), 'Image link (film)');
        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), 'Director(s)');
        $this->assertEquals(array("Adventure", "Animation", "Comedy", "Family", "Fantasy", "Musical"), $film->getGenres(), 'Genres');

        // Verify database - IMDb
        $this->assertEquals("tt2294629", $film->getUniqueName(Constants::SOURCE_IMDB), 'UniqueName from source');
        $this->assertEquals(1, preg_match('@(http://ia.media-imdb.com/images/M/MV5BMTQ1MjQwMTE5OF5BMl5BanBnXkFtZTgwNjk3MTcyMDE)@', $film->getImage(Constants::SOURCE_IMDB), $matches), 'Image link (IMDb)');
        $this->assertEquals(7, $film->getCriticScore(Constants::SOURCE_IMDB), 'Critic score');
        $this->assertEquals(8, $film->getUserScore(Constants::SOURCE_IMDB), 'User score');
        $rating = $film->getRating(Constants::SOURCE_IMDB);
        $this->assertEquals(2, $rating->getYourScore(), 'Your Score not available from searchImdb');
        $this->assertEmpty($rating->getYourRatingDate(), 'Rating date not available from searchImdb');
        $this->assertEmpty($rating->getSuggestedScore(), 'Suggested score not available from searchImdb');

        // Verify database - RS
        $this->assertEquals("rs$filmId", $film->getUniqueName(Constants::SOURCE_RATINGSYNC), "RS uniqueName");
        $this->assertEquals("http://example.com/frozen_rs_image.jpeg", $film->getImage(Constants::SOURCE_RATINGSYNC), "RS image");
        $this->assertEquals(4, $film->getCriticScore(Constants::SOURCE_RATINGSYNC), 'Critic score');
        $this->assertEquals(5, $film->getUserScore(Constants::SOURCE_RATINGSYNC), 'User score');
        $rating = $film->getRating(Constants::SOURCE_RATINGSYNC);
        $this->assertEquals(2, $rating->getYourScore(), 'Your Score');
        $this->assertEquals("2015-01-01", date_format($rating->getYourRatingDate(), "Y-m-d"), "YourRatingDate");
        $this->assertEquals(3, $rating->getSuggestedScore(), 'Suggested score');
    }
    
    /**
     * - Do not include searchQuery or username
     *
     * Expect
     *   - return null
     *
     * @covers \RatingSync\search
     */
    public function testSearchEmptyArgs()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = search(null, null);
        $this->assertEmpty($film, "Empty args should return nothing");
    }
    
    /**
     * - searchQuery = ''
     * - username = valid test user
     *
     * Expect
     *   - return null
     *
     * @covers \RatingSync\search
     */
    public function testSearchEmptyQuery()
    {$this->start(__CLASS__, __FUNCTION__);

        $film = search("", getUsername());
        $this->assertEmpty($film, "Empty query should return nothing");
    }
    
    /**
     * - Film does not exist in the db
     * - searchQuery = valid IMDb uniqueName
     * - username = null
     *
     * Expect
     *   - same as testSearchImdb
     *
     * @covers \RatingSync\search
     */
    public function testSearch()
    {$this->start(__CLASS__, __FUNCTION__);

        // Set up
        DatabaseTest::resetDb();

        // Test
        $searchTerms = array("uniqueName" => "tt0094819");
        $film = search($searchTerms); // Buster (1988)
        $filmId = $film->getId();

        // Verify film object
        $this->assertEquals("Buster", $film->getTitle(), "Title");
        $this->assertEquals(1988, $film->getYear(), "Year");
        $this->assertEquals(Film::CONTENT_FILM, $film->getContentType(), 'Content Type');
        $this->assertEquals("/image/rs$filmId.jpg", $film->getImage(), 'Image link (film)');
        $this->assertEquals(1, preg_match('@(http://ia.media-imdb.com/images/M/MV5BMTY1ODA3NjU0NV5BMl5BanBnXkFtZTcwMDU1NTQyMQ)@', $film->getImage(Constants::SOURCE_IMDB), $matches), 'Image link (IMDb)');
        $this->assertNull($film->getCriticScore(Constants::SOURCE_IMDB), 'Critic score');
        $this->assertEquals(5.8, $film->getUserScore(Constants::SOURCE_IMDB), 'User score');
        $this->assertEquals(array("David Green"), $film->getDirectors(), 'Director(s)');
        $this->assertEquals(array("Adventure", "Biography", "Comedy"), $film->getGenres(), 'Genres');
        $rating = $film->getRating(Constants::SOURCE_IMDB);
        $this->assertEquals("tt0094819", $film->getUniqueName(Constants::SOURCE_IMDB), 'UniqueName from source');
        $this->assertNull($rating->getYourScore(), 'Your Score');
        $this->assertNull($rating->getYourRatingDate(), 'Rating date not available from film detail page');
        $this->assertNull($rating->getSuggestedScore(), 'Suggested score not available is you are rated the film');

        // Verify database

        $film = null;
        $film = Film::getFilmFromDb($filmId, new HttpRatingSync(getUsername()), getUsername());

        // Verify the db film the same way as the object before
        $this->assertEquals("Buster", $film->getTitle(), "Title");
        $this->assertEquals(1988, $film->getYear(), "Year");
        $this->assertEquals(Film::CONTENT_FILM, $film->getContentType(), 'Content Type');
        $this->assertEquals("/image/rs$filmId.jpg", $film->getImage(), 'Image link (film)');
        $this->assertEquals(1, preg_match('@(http://ia.media-imdb.com/images/M/MV5BMTY1ODA3NjU0NV5BMl5BanBnXkFtZTcwMDU1NTQyMQ)@', $film->getImage(Constants::SOURCE_IMDB), $matches), 'Image link (IMDb)');
        $this->assertNull($film->getCriticScore(Constants::SOURCE_IMDB), 'Critic score');
        $this->assertEquals(6, $film->getUserScore(Constants::SOURCE_IMDB), 'User score');
        $this->assertEquals(array("David Green"), $film->getDirectors(), 'Director(s)');
        $this->assertEquals(array("Adventure", "Biography", "Comedy"), $film->getGenres(), 'Genres');
        $rating = $film->getRating(Constants::SOURCE_IMDB);
        $this->assertEquals("tt0094819", $film->getUniqueName(Constants::SOURCE_IMDB), 'UniqueName from source');
        $this->assertNull($rating->getYourScore(), 'Your Score');
        $this->assertNull($rating->getYourRatingDate(), 'Rating date not available from film detail page');
        $this->assertNull($rating->getSuggestedScore(), 'Suggested score not available is you are rated the film');

        // RS source created
        $source = $film->getSource(Constants::SOURCE_RATINGSYNC);
        $this->assertEquals("rs$filmId", $source->getUniqueName(), "RS uniqueName");
        $this->assertEquals("/image/rs$filmId.jpg", $source->getImage(), "RS uniqueName");
    }
    
    /**
     * @covers \RatingSync\search
     * @depends testSearch
     */
    public function testSearchEmptyUsername()
    {$this->start(__CLASS__, __FUNCTION__);
        // Covered by testSearch
    }
    
    /**
     * @covers \RatingSync\search
     * @depends testSearch
     */
    public function testSearchDbMatch()
    {$this->start(__CLASS__, __FUNCTION__);

        // Set up
        // Use the film in db from testSearch

        // Test
        $searchTerms = array("uniqueName" => "rs1");
        $film = search($searchTerms); // Buster (1988)

        // Verify
        $this->assertEquals("Buster", $film->getTitle(), "Title");
    }
    
    /**
     * @covers \RatingSync\search
     * @depends testSearchDbMatch
     */
    public function testSearchDbMatchEmptyUsername()
    {$this->start(__CLASS__, __FUNCTION__);
        // covered by testSearchDbMatch
    }
    
    /**
     * @covers \RatingSync\search
     * @depends testSearchDbMatch
     */
    public function testSearchDbMatchNoRatingsForUsername()
    {$this->start(__CLASS__, __FUNCTION__);

        // Set up
        // Use the film in db from testSearch

        // Test
        $searchTerms = array("uniqueName" => "rs1");
        $film = search($searchTerms, getUsername());

        // Verify
        $this->assertEquals("Buster", $film->getTitle(), "Title");
        $this->assertEmpty($film->getYourScore(Constants::SOURCE_RATINGSYNC), "Should be no RS rating");
    }
    
    /**
     * @covers \RatingSync\search
     * @depends testSearchDbMatch
     */
    public function testSearchDbMatchWithRatings()
    {$this->start(__CLASS__, __FUNCTION__);

        // Set up
        $username = getUsername();
        $searchTerms = array("uniqueName" => "rs1");
        $setupFilm = search($searchTerms, $username); // Buster (1988)
        $setupFilm->setYourScore(5, Constants::SOURCE_RATINGSYNC);
        $setupFilm->saveToDb($username);

        // Test
        $film = search($searchTerms, $username);

        // Verify
        $this->assertEquals("Buster", $film->getTitle(), "Title");
        $this->assertEquals($setupFilm->getYourScore(Constants::SOURCE_RATINGSYNC), $film->getYourScore(Constants::SOURCE_RATINGSYNC), "RS rating");
    }
    
    /**
     * @covers \RatingSync\search
     * @depends testSearch
     */
    public function testSearchDbNoMatchSiteMatch()
    {$this->start(__CLASS__, __FUNCTION__);
        // covered by testSearch
    }
    
    /**
     * @covers \RatingSync\search
     */
    public function testSearchDbNoMatchSiteNoMatch()
    {$this->start(__CLASS__, __FUNCTION__);
    
        $searchTerms = array("uniqueName" => "garbage_query");
        $film = search($searchTerms);
        $this->assertEmpty($film, "Film from uniqueName=garbage_query should be empty");
    }
}

?>
