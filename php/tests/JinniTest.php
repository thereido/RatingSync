<?php
/**
 * Jinni PHPUnit
 */
namespace RatingSync;

require_once "Jinni.php";

const TEST_USERNAME = "testratingsync";

class JinniTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers            \RatingSync\Jinni::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testCannotBeConstructedFromNull()
    {
        new Jinni(null);
    }

    /**
     * @covers            \RatingSync\Jinni::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testCannotBeConstructedFromEmptyUsername()
    {
        new Jinni("");
    }

    /**
     * @covers \RatingSync\Jinni::__construct
     */
    public function testObjectCanBeConstructed()
    {
        $jinni = new Jinni(TEST_USERNAME);
        return $jinni;
    }

    /**
     * @covers \RatingSync\Jinni::getRatings
     * @depends testObjectCanBeConstructed
     * @expectedException \RatingSync\HttpUnauthorizedRedirectException
     */
    public function testGetRatingsUsernameWithNoMatch()
    {
        $jinni = new Jinni("---Username--No--Match---");
        $films = $jinni->getRatings();
    }

    /**
     * @covers \RatingSync\Jinni::getRatings
     * @depends testObjectCanBeConstructed
     */
    public function testGetRatings()
    {
        $jinni = new Jinni(TEST_USERNAME);
        $films = $jinni->getRatings();
    }

    /**
     * @covers \RatingSync\Jinni::getRatings
     * @depends testObjectCanBeConstructed
     */
    public function testGetRatingsFromRandomAccount()
    {
        // Find films even though the account is not logged in
        $jinni = new Jinni("Alyssa.Mann");
        $films = $jinni->getRatings();
        $this->assertGreaterThan(0, count($films));
    }

    /**
     * @covers \RatingSync\Jinni::getRatings
     * @depends testObjectCanBeConstructed
     * @depends testGetRatings
     */
    public function testGetRatingsCount()
    {
        $jinni = new Jinni(TEST_USERNAME);
        $films = $jinni->getRatings();
        $this->assertCount(21, $films);
    }

    /**
     * @covers \RatingSync\Jinni::getRatings
     * @depends testObjectCanBeConstructed
     * @depends testGetRatings
     */
    public function testGetRatingsLimitPages()
    {
        $jinni = new Jinni(TEST_USERNAME);
        $films = $jinni->getRatings(1);
        $this->assertCount(20, $films);
    }

    /**
     * @covers \RatingSync\Jinni::getRatings
     * @depends testObjectCanBeConstructed
     * @depends testGetRatings
     */
    public function testGetRatingsBeginPage()
    {
        $jinni = new Jinni(TEST_USERNAME);
        $films = $jinni->getRatings(null, 2);
        $this->assertEquals("The Shawshank Redemption", $films[0]->getTitle());
    }

    /**
     * @covers \RatingSync\Jinni::getRatings
     * @depends testObjectCanBeConstructed
     * @depends testGetRatingsLimitPages
     * @depends testGetRatingsBeginPage
     */
    public function testGetRatingsDetails()
    {
        $jinni = new Jinni(TEST_USERNAME);
        $films = $jinni->getRatings(1, 1, true);
        $film = $films[0];
        $this->assertEquals("Frozen", $film->getTitle(), 'Title');
        $this->assertEquals(2013, $film->getYear(), 'Year');
        $this->assertEquals("FeatureFilm", $film->getContentType(), 'Content Type');
        $this->assertEquals("http://media.jinni.com/movie/frozen-2013/frozen-2013-5.jpeg", $film->getImage(), 'Image link');
        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), 'Director(s)');
        $this->assertEquals(array("Adventure", "Animation", "Fantasy", "Musical", "Family", "Comedy"), $film->getGenres(), 'Genres');
        $this->assertEquals("frozen-2013", $film->getUrlName(RATING::SOURCE_JINNI), 'URL Name');
        $rating = $film->getRating(RATING::SOURCE_JINNI);
        $this->assertEquals(8, $rating->getYourScore(), 'Your Score');
        $this->assertEquals("5/4/2015", $rating->getYourRatingDate()->format("n/j/Y"), 'Your Rating Date');
    }

    /**
     * @covers \RatingSync\Jinni::getSearchSuggestions
     * @depends testObjectCanBeConstructed
     */
    public function testGetSearchSuggestions()
    {
        $jinni = new Jinni(TEST_USERNAME);
        $films = $jinni->getSearchSuggestions("Shawshank");
        $titles = array();
        foreach ($films as $film) {
            $titles[] = $film->getTitle();
        }
        $this->assertTrue(in_array("The Shawshank Redemption", $titles));
    }

    /**
     * @covers \RatingSync\Jinni::getFilmDetailFromWebsite
     * @depends testObjectCanBeConstructed
     * @expectedException \InvalidArgumentException
     */
    public function testGetFilmDetailFromWebsiteFromNull()
    {
        $jinni = new Jinni(TEST_USERNAME);
        $jinni->getFilmDetailFromWebsite(null);
    }

    /**
     * @covers \RatingSync\Jinni::getFilmDetailFromWebsite
     * @depends testObjectCanBeConstructed
     * @expectedException \InvalidArgumentException
     */
    public function testGetFilmDetailFromWebsiteFromString()
    {
        $jinni = new Jinni(TEST_USERNAME);
        $jinni->getFilmDetailFromWebsite("String_Not_Film_Object");
    }

    /**
     * @covers \RatingSync\Jinni::getFilmDetailFromWebsite
     * @depends testObjectCanBeConstructed
     * @expectedException \InvalidArgumentException
     */
    public function testGetFilmDetailFromWebsiteWithoutUrlName()
    {
        $jinni = new Jinni(TEST_USERNAME);
        $film = new Film($jinni->http);
        $film->setContentType("FeatureFilm");
        $jinni->getFilmDetailFromWebsite($film);
    }

    /**
     * @covers \RatingSync\Jinni::getFilmDetailFromWebsite
     * @depends testObjectCanBeConstructed
     * @expectedException \InvalidArgumentException
     */
    public function testGetFilmDetailFromWebsiteWithoutContentType()
    {
        $jinni = new Jinni(TEST_USERNAME);
        $film = new Film($jinni->http);
        $film->setUrlName("frozen-2013", Rating::SOURCE_JINNI);
        $jinni->getFilmDetailFromWebsite($film);
    }

    /**
     * @covers \RatingSync\Jinni::getFilmDetailFromWebsite
     * @depends testObjectCanBeConstructed
     * @expectedException \Exception
     */
    public function testGetFilmDetailFromWebsiteNoMatch()
    {
        $jinni = new Jinni(TEST_USERNAME);
        $film = new Film($jinni->http);
        $film->setContentType("FeatureFilm");
        $film->setUrlName("NO_MATCH_URLNAME", Rating::SOURCE_JINNI);
        $jinni->getFilmDetailFromWebsite($film, true);
    }

    /**
     * @covers \RatingSync\Jinni::getFilmDetailFromWebsite
     * @depends testObjectCanBeConstructed
     */
    public function testGetFilmDetailFromWebsite()
    {
        $jinni = new Jinni(TEST_USERNAME);

        $film = new Film($jinni->http);
        $film->setContentType("FeatureFilm");
        $film->setUrlName("frozen-2013", Rating::SOURCE_JINNI);
        $jinni->getFilmDetailFromWebsite($film, true);

        $this->assertEquals("Frozen", $film->getTitle(), 'Title');
        $this->assertEquals(2013, $film->getYear(), 'Year');
        $this->assertEquals("FeatureFilm", $film->getContentType(), 'Content Type');
        $this->assertEquals(1, preg_match('@(http://media[\d]*.jinni.com/(?:tv|movie|shorts|no-image)/frozen-2013/[^"]+)@', $film->getImage(), $matches), 'Image link');
        $this->assertEquals("frozen-2013", $film->getUrlName(RATING::SOURCE_JINNI), 'URL Name');
        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), 'Director(s)');
        $this->assertEquals(array("Adventure", "Animation", "Fantasy", "Musical", "Family", "Comedy"), $film->getGenres(), 'Genres');
        $rating = $film->getRating(RATING::SOURCE_JINNI);
        $this->assertEquals("999", $rating->getFilmId(), 'Film ID');
        $this->assertEquals(8, $rating->getYourScore(), 'Your Score');
        $this->assertNull($rating->getYourRatingDate(), 'Rating date not available from film detail page');
        $this->assertNull($rating->getSuggestedScore(), 'Suggested score not available is you are rated the film');
        $this->assertNull($rating->getCriticScore(), 'Critic score not available from Jinni');
        $this->assertNull($rating->getUserScore(), 'User score not available from Jinni');
    }

    /**
     * @covers \RatingSync\Jinni::getFilmDetailFromWebsite
     * @depends testGetFilmDetailFromWebsite
     */
    public function testGetFilmDetailFromWebsiteOverwriteTrueOverEmpty()
    {
        $jinni = new Jinni(TEST_USERNAME);

        $film = new Film($jinni->http);
        $film->setContentType("FeatureFilm");
        $film->setUrlName("frozen-2013", Rating::SOURCE_JINNI);
        $jinni->getFilmDetailFromWebsite($film, true);

        // Same results as testGetFilmDetailFromWebsite
        $this->assertEquals("Frozen", $film->getTitle(), 'Title');
        $this->assertEquals(2013, $film->getYear(), 'Year');
        $this->assertEquals("FeatureFilm", $film->getContentType(), 'Content Type');
        $this->assertEquals(1, preg_match('@(http://media[\d]*.jinni.com/(?:tv|movie|shorts|no-image)/frozen-2013/[^"]+)@', $film->getImage(), $matches), 'Image link');
        $this->assertEquals("frozen-2013", $film->getUrlName(RATING::SOURCE_JINNI), 'URL Name');
        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), 'Director(s)');
        $this->assertEquals(array("Adventure", "Animation", "Fantasy", "Musical", "Family", "Comedy"), $film->getGenres(), 'Genres');
        $rating = $film->getRating(RATING::SOURCE_JINNI);
        $this->assertEquals("999", $rating->getFilmId(), 'Film ID');
        $this->assertEquals(8, $rating->getYourScore(), 'Your Score');
        $this->assertNull($rating->getYourRatingDate(), 'Rating date not available from film detail page');
        $this->assertNull($rating->getSuggestedScore(), 'Suggested score not available is you are rated the film');
        $this->assertNull($rating->getCriticScore(), 'Critic score not available from Jinni');
        $this->assertNull($rating->getUserScore(), 'User score not available from Jinni');
    }

    /**
     * @covers \RatingSync\Jinni::getFilmDetailFromWebsite
     * @depends testGetFilmDetailFromWebsite
     */
    public function testGetFilmDetailFromWebsiteOverwriteTrueOverOriginalData()
    {
        $jinni = new Jinni(TEST_USERNAME);

        $film = new Film($jinni->http);

        // Setup original data
        $film->setTitle("Original_Title");
        $film->setYear(1900);
        $film->setImage("Original_Image");
        $film->setUrlName("Original_JinniUrlName", Rating::SOURCE_JINNI);
        $film->setUrlName("Original_IMDbUrlName", Rating::SOURCE_IMDB);
        $film->addGenre("Original_Genre1");
        $film->addGenre("Original_Genre2");
        $film->addDirector("Original_Director1");
        $film->addDirector("Original_Director2");
        $ratingJinniOrig = new Rating(Rating::SOURCE_JINNI);
        $ratingJinniOrig->setFilmId("Original_JinniFilmId");
        $ratingJinniOrig->setYourScore(1);
        $ratingJinniOrig->setYourRatingDate(new \DateTime('2000-01-01'));
        $ratingJinniOrig->setSuggestedScore(2);
        $ratingJinniOrig->setCriticScore(3);
        $ratingJinniOrig->setUserScore(4);
        $film->setRating($ratingJinniOrig, Rating::SOURCE_JINNI);
        $ratingImdbOrig = new Rating(Rating::SOURCE_IMDB);
        $ratingImdbOrig->setFilmId("Original_ImdbFilmId");
        $ratingImdbOrig->setYourScore(2);
        $ratingImdbOrig->setYourRatingDate(new \DateTime('2000-01-02'));
        $ratingImdbOrig->setSuggestedScore(3);
        $ratingImdbOrig->setCriticScore(4);
        $ratingImdbOrig->setUserScore(5);
        $film->setRating($ratingImdbOrig, Rating::SOURCE_IMDB);

        // Get detail overwriting
        $film->setContentType("FeatureFilm");
        $film->setUrlName("frozen-2013", Rating::SOURCE_JINNI);
        $jinni->getFilmDetailFromWebsite($film, true);

        // Test the new data
        $this->assertEquals("Frozen", $film->getTitle(), 'Title');
        $this->assertEquals(2013, $film->getYear(), 'Year');
        $this->assertEquals("FeatureFilm", $film->getContentType(), 'Content Type');
        $this->assertEquals(1, preg_match('@(http://media[\d]*.jinni.com/(?:tv|movie|shorts|no-image)/frozen-2013/[^"]+)@', $film->getImage(), $matches), 'Image link');
        $this->assertEquals("frozen-2013", $film->getUrlName(RATING::SOURCE_JINNI), 'URL Name');
        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), 'Director(s)');
        $this->assertEquals(array("Adventure", "Animation", "Fantasy", "Musical", "Family", "Comedy"), $film->getGenres(), 'Genres');
        $rating = $film->getRating(RATING::SOURCE_JINNI);
        $this->assertEquals("999", $rating->getFilmId(), 'Film ID');
        $this->assertEquals(8, $rating->getYourScore(), 'Your Score');

        // The film detail page does not have these fields.  Don't overwrite them.
        $this->assertEquals("1/1/2000", $rating->getYourRatingDate()->format("n/j/Y"), 'Rating date');
        $this->assertEquals(2, $rating->getSuggestedScore(), 'Suggested score not available is you are rated the film');
        $this->assertEquals(3, $rating->getCriticScore(), 'Critic score not available from Jinni');
        $this->assertEquals(4, $rating->getUserScore(), 'User score not available from Jinni');

        // IMDb Rating is unchanged
        $rating = $film->getRating(RATING::SOURCE_IMDB);
        $this->assertEquals("Original_ImdbFilmId", $rating->getFilmId(), 'Film ID');
        $this->assertEquals(2, $rating->getYourScore(), 'Your Score');
        $this->assertEquals("1/2/2000", $rating->getYourRatingDate()->format("n/j/Y"), 'Rating date');
        $this->assertEquals(3, $rating->getSuggestedScore(), 'Suggested score not available is you are rated the film');
        $this->assertEquals(4, $rating->getCriticScore(), 'Critic score not available from Jinni');
        $this->assertEquals(5, $rating->getUserScore(), 'User score not available from Jinni');
    }

    /**
     * @covers \RatingSync\Jinni::getFilmDetailFromWebsite
     * @depends testGetFilmDetailFromWebsiteOverwriteTrueOverEmpty
     */
    public function testGetFilmDetailFromWebsiteOverwriteFalseOverOriginalData()
    {
        $jinni = new Jinni(TEST_USERNAME);

        $film = new Film($jinni->http);

        // Setup original data
        $film->setTitle("Original_Title");
        $film->setYear(1900);
        $film->setImage("Original_Image");
        $film->setUrlName("Original_IMDbUrlName", Rating::SOURCE_IMDB);
        $film->addGenre("Original_Genre1");
        $film->addGenre("Original_Genre2");
        $film->addDirector("Original_Director1");
        $film->addDirector("Original_Director2");
        $ratingJinniOrig = new Rating(Rating::SOURCE_JINNI);
        $ratingJinniOrig->setFilmId("Original_JinniFilmId");
        $ratingJinniOrig->setYourScore(1);
        $ratingJinniOrig->setYourRatingDate(new \DateTime('2000-01-01'));
        $ratingJinniOrig->setSuggestedScore(2);
        $ratingJinniOrig->setCriticScore(3);
        $ratingJinniOrig->setUserScore(4);
        $film->setRating($ratingJinniOrig, Rating::SOURCE_JINNI);
        $ratingImdbOrig = new Rating(Rating::SOURCE_IMDB);
        $ratingImdbOrig->setFilmId("Original_ImdbFilmId");
        $ratingImdbOrig->setYourScore(2);
        $ratingImdbOrig->setYourRatingDate(new \DateTime('2000-01-02'));
        $ratingImdbOrig->setSuggestedScore(3);
        $ratingImdbOrig->setCriticScore(4);
        $ratingImdbOrig->setUserScore(5);
        $film->setRating($ratingImdbOrig, Rating::SOURCE_IMDB);

        // Get detail not overwriting
        $film->setContentType("FeatureFilm");
        $film->setUrlName("frozen-2013", Rating::SOURCE_JINNI);
        $jinni->getFilmDetailFromWebsite($film, false);

        // Same original data
        $this->assertEquals("FeatureFilm", $film->getContentType(), 'Content Type');
        $this->assertEquals("frozen-2013", $film->getUrlName(RATING::SOURCE_JINNI), 'Jinni URL Name');
        $this->assertEquals("Original_Title", $film->getTitle(), 'Title');
        $this->assertEquals(1900, $film->getYear(), 'Year');
        $this->assertEquals("Original_Image", $film->getImage(), 'Image link');
        $this->assertEquals("Original_IMDbUrlName", $film->getUrlName(RATING::SOURCE_IMDB), 'Jinni URL Name');
        $this->assertEquals(array("Original_Director1", "Original_Director2"), $film->getDirectors(), 'Director(s)');
        $this->assertEquals(array("Original_Genre1", "Original_Genre2"), $film->getGenres(), 'Genres');
        $rating = $film->getRating(RATING::SOURCE_JINNI);
        $this->assertEquals("Original_JinniFilmId", $rating->getFilmId(), 'Film ID');
        $this->assertEquals(1, $rating->getYourScore(), 'Your Score');
        $this->assertEquals("1/1/2000", $rating->getYourRatingDate()->format("n/j/Y"), 'Your Rating Date');
        $this->assertEquals(2, $rating->getSuggestedScore(), 'Suggested score');
        $this->assertEquals(3, $rating->getCriticScore(), 'Critic score');
        $this->assertEquals(4, $rating->getUserScore(), 'User score');
        $rating = $film->getRating(RATING::SOURCE_IMDB);
        $this->assertEquals("Original_ImdbFilmId", $rating->getFilmId(), 'Film ID');
        $this->assertEquals(2, $rating->getYourScore(), 'Your Score');
        $this->assertEquals("1/2/2000", $rating->getYourRatingDate()->format("n/j/Y"), 'Your Rating Date');
        $this->assertEquals(3, $rating->getSuggestedScore(), 'Suggested score');
        $this->assertEquals(4, $rating->getCriticScore(), 'Critic score');
        $this->assertEquals(5, $rating->getUserScore(), 'User score');
    }

    /**
     * @covers \RatingSync\Jinni::getFilmDetailFromWebsite
     * @depends testGetFilmDetailFromWebsite
     */
    public function testGetFilmDetailFromWebsiteOverwriteFalseOverEmpty()
    {
        $jinni = new Jinni(TEST_USERNAME);

        $film = new Film($jinni->http);
        $film->setContentType("FeatureFilm");
        $film->setUrlName("frozen-2013", Rating::SOURCE_JINNI);
        $jinni->getFilmDetailFromWebsite($film, false);

        // Same results as testGetFilmDetailFromWebsite or testGetFilmDetailFromWebsiteOverwriteTrueOverEmpty
        $this->assertEquals("Frozen", $film->getTitle(), 'Title');
        $this->assertEquals(2013, $film->getYear(), 'Year');
        $this->assertEquals("FeatureFilm", $film->getContentType(), 'Content Type');
        $this->assertEquals(1, preg_match('@(http://media[\d]*.jinni.com/(?:tv|movie|shorts|no-image)/frozen-2013/[^"]+)@', $film->getImage(), $matches), 'Image link');
        $this->assertEquals("frozen-2013", $film->getUrlName(RATING::SOURCE_JINNI), 'URL Name');
        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), 'Director(s)');
        $this->assertEquals(array("Adventure", "Animation", "Fantasy", "Musical", "Family", "Comedy"), $film->getGenres(), 'Genres');
        $rating = $film->getRating(RATING::SOURCE_JINNI);
        $this->assertEquals("999", $rating->getFilmId(), 'Film ID');
        $this->assertEquals(8, $rating->getYourScore(), 'Your Score');
        $this->assertNull($rating->getYourRatingDate(), 'Rating date not available from film detail page');
        $this->assertNull($rating->getSuggestedScore(), 'Suggested score not available is you are rated the film');
        $this->assertNull($rating->getCriticScore(), 'Critic score not available from Jinni');
        $this->assertNull($rating->getUserScore(), 'User score not available from Jinni');
    }

    /**
     * @covers \RatingSync\Jinni::getFilmDetailFromWebsite
     * @depends testGetFilmDetailFromWebsiteOverwriteTrueOverOriginalData
     */
    public function testGetFilmDetailFromWebsiteOverwriteDefault()
    {
        $jinni = new Jinni(TEST_USERNAME);

        $film = new Film($jinni->http);

        // Setup original data
        $film->setTitle("Original_Title");
        $film->setYear(1900);
        $film->setImage("Original_Image");
        $film->setUrlName("Original_JinniUrlName", Rating::SOURCE_JINNI);
        $film->setUrlName("Original_IMDbUrlName", Rating::SOURCE_IMDB);
        $film->addGenre("Original_Genre1");
        $film->addGenre("Original_Genre2");
        $film->addDirector("Original_Director1");
        $film->addDirector("Original_Director2");
        $ratingJinniOrig = new Rating(Rating::SOURCE_JINNI);
        $ratingJinniOrig->setFilmId("Original_JinniFilmId");
        $ratingJinniOrig->setYourScore(1);
        $ratingJinniOrig->setYourRatingDate(new \DateTime('2000-01-01'));
        $ratingJinniOrig->setSuggestedScore(2);
        $ratingJinniOrig->setCriticScore(3);
        $ratingJinniOrig->setUserScore(4);
        $film->setRating($ratingJinniOrig, Rating::SOURCE_JINNI);
        $ratingImdbOrig = new Rating(Rating::SOURCE_IMDB);
        $ratingImdbOrig->setFilmId("Original_ImdbFilmId");
        $ratingImdbOrig->setYourScore(2);
        $ratingImdbOrig->setYourRatingDate(new \DateTime('2000-01-02'));
        $ratingImdbOrig->setSuggestedScore(3);
        $ratingImdbOrig->setCriticScore(4);
        $ratingImdbOrig->setUserScore(5);
        $film->setRating($ratingImdbOrig, Rating::SOURCE_IMDB);

        // Get detail overwriting
        $film->setContentType("FeatureFilm");
        $film->setUrlName("frozen-2013", Rating::SOURCE_JINNI);
        $jinni->getFilmDetailFromWebsite($film);

        // Test the new data (overwrite default param is true)
        $this->assertEquals("Frozen", $film->getTitle(), 'Title');
        $this->assertEquals(2013, $film->getYear(), 'Year');
        $this->assertEquals("FeatureFilm", $film->getContentType(), 'Content Type');
        $this->assertEquals(1, preg_match('@(http://media[\d]*.jinni.com/(?:tv|movie|shorts|no-image)/frozen-2013/[^"]+)@', $film->getImage(), $matches), 'Image link');
        $this->assertEquals("frozen-2013", $film->getUrlName(RATING::SOURCE_JINNI), 'URL Name');
        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), 'Director(s)');
        $this->assertEquals(array("Adventure", "Animation", "Fantasy", "Musical", "Family", "Comedy"), $film->getGenres(), 'Genres');
        $rating = $film->getRating(RATING::SOURCE_JINNI);
        $this->assertEquals("999", $rating->getFilmId(), 'Film ID');
        $this->assertEquals(8, $rating->getYourScore(), 'Your Score');
        $this->assertEquals("1/1/2000", $rating->getYourRatingDate()->format("n/j/Y"), 'Rating date');
        $this->assertEquals(2, $rating->getSuggestedScore(), 'Suggested score not available is you are rated the film');
        $this->assertEquals(3, $rating->getCriticScore(), 'Critic score not available from Jinni');
        $this->assertEquals(4, $rating->getUserScore(), 'User score not available from Jinni');
        $rating = $film->getRating(RATING::SOURCE_IMDB);
        $this->assertEquals("Original_ImdbFilmId", $rating->getFilmId(), 'Film ID');
        $this->assertEquals(2, $rating->getYourScore(), 'Your Score');
        $this->assertEquals("1/2/2000", $rating->getYourRatingDate()->format("n/j/Y"), 'Rating date');
        $this->assertEquals(3, $rating->getSuggestedScore(), 'Suggested score not available is you are rated the film');
        $this->assertEquals(4, $rating->getCriticScore(), 'Critic score not available from Jinni');
        $this->assertEquals(5, $rating->getUserScore(), 'User score not available from Jinni');
    }

    /**
     * @covers \RatingSync\Jinni::getFilmDetailFromWebsite
     * @expectedException \InvalidArgumentException
     */
    public function testGetFilmDetailFromWebsiteOverwriteFalseOverEmptyFilm()
    {
        $jinni = new Jinni(TEST_USERNAME);
        $film = new Film($jinni->http);
        $jinni->getFilmDetailFromWebsite($film);
    }

    /**
     * @covers \RatingSync\Jinni::getFilmDetailFromWebsite
     * @depends testGetFilmDetailFromWebsite
     */
    public function testMultipleGenres()
    {
        $jinni = new Jinni(TEST_USERNAME);

        $film = new Film($jinni->http);
        $film->setContentType("FeatureFilm");
        $film->setUrlName("frozen-2013", Rating::SOURCE_JINNI);
        $jinni->getFilmDetailFromWebsite($film);

        $this->assertEquals(array("Adventure", "Animation", "Fantasy", "Musical", "Family", "Comedy"), $film->getGenres(), 'Genres');
    }

    /**
     * @covers \RatingSync\Jinni::getFilmDetailFromWebsite
     * @depends testGetFilmDetailFromWebsite
     */
    public function testMultipleDirectors()
    {
        $jinni = new Jinni(TEST_USERNAME);

        $film = new Film($jinni->http);
        $film->setContentType("FeatureFilm");
        $film->setUrlName("frozen-2013", Rating::SOURCE_JINNI);
        $jinni->getFilmDetailFromWebsite($film);

        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), 'Director(s)');
    }
    
    /**
     * @covers \RatingSync\Jinni::exportRatings
     * @depends testObjectCanBeConstructed
     */
    public function testExportRatingsXmlNoDetail()
    {
        $jinni = new Jinni(TEST_USERNAME);

        $testFilename = "ratings_test.xml";
        $success = $jinni->exportRatings("XML", $testFilename, false);
        $this->assertTrue($success);

        $fullTestFilename = ".." . Constants::RS_OUTPUT_PATH . $testFilename;
        $fullVerifyFilename = "tests/testfile/verify_ratings_nodetail.xml";
        $this->assertTrue(is_readable($fullTestFilename), 'Need to read downloaded file ' . $fullTestFilename);
        $this->assertTrue(is_readable($fullVerifyFilename), 'Need to read verify file ' . $fullVerifyFilename);

        $fp_test = fopen($fullTestFilename, "r");
        $fp_verify = fopen($fullVerifyFilename, "r");
        $test = fread($fp_test, filesize($fullTestFilename));
        $verify = fread($fp_verify, filesize($fullVerifyFilename));
        $this->assertEquals($test, $verify, 'Match exported file vs verify file');
        fclose($fp_test);
        fclose($fp_verify);
    }

    /**
     * @covers \RatingSync\Jinni::exportRatings
     * @depends testObjectCanBeConstructed
     */
    public function testExportRatingsXmlDetail()
    {
        $jinni = new Jinni(TEST_USERNAME);

        $testFilename = "ratings_test.xml";
        $success = $jinni->exportRatings("XML", $testFilename, true);
        $this->assertTrue($success);

        $fullTestFilename = ".." . Constants::RS_OUTPUT_PATH . $testFilename;
        $fullVerifyFilename = "tests/testfile/verify_ratings_detail.xml";
        $this->assertTrue(is_readable($fullTestFilename), 'Need to read downloaded file ' . $fullTestFilename);
        $this->assertTrue(is_readable($fullVerifyFilename), 'Need to read verify file ' . $fullVerifyFilename);

        $fp_test = fopen($fullTestFilename, "r");
        $fp_verify = fopen($fullVerifyFilename, "r");
        $test = fread($fp_test, filesize($fullTestFilename));
        $verify = fread($fp_verify, filesize($fullVerifyFilename));
        $this->assertEquals($test, $verify, 'Match exported file vs verify file');
        fclose($fp_test);
        fclose($fp_verify);
    }
}

?>
