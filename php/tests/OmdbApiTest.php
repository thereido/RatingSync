<?php
/**
 * OmdbApi PHPUnit
 */
namespace RatingSync;

require_once "../src/OmdbApi.php";
require_once "DatabaseTest.php";
require_once "RatingSyncTestCase.php";

const TESTFILM_PRIMARY_TITLE = "Frozen";
const TESTFILM_PRIMARY_YEAR = 2013;
const TESTFILM_PRIMARY_IMDBID = "tt2294629";
const TESTFILM_PRIMARY_USER_SCORE = 7.5;
const TESTFILM_PRIMARY_CRITIC_SCORE = 7.4;
const TESTFILM_PRIMARY_IMAGE = "MV5BMTQ1MjQwMTE5OF5BMl5BanBnXkFtZTgwNjk3MTcyMDE";
const TESTFILM_PRIMARY_DIRECTORS = array("Chris Buck", "Jennifer Lee");
const TESTFILM_PRIMARY_GENRES = array("Animation", "Adventure", "Comedy", "Family", "Fantasy", "Musical");

const TESTSERIES_TITLE = "Game of Thrones";
const TESTSERIES_YEAR = 2011;
const TESTSERIES_IMDBID = "tt0944947";
const TESTSERIES_USER_SCORE = 9.5;
const TESTSERIES_CRITIC_SCORE = null;
const TESTSERIES_IMAGE = "MV5BMjA5NzA5NjMwNl5BMl5BanBnXkFtZTgwNjg2OTk2NzM";
const TESTSERIES_DIRECTORS = array();
const TESTSERIES_GENRES = array("Action", "Adventure", "Drama", "Fantasy", "Romance");

const TESTEPISODE_TITLE = "Game of Thrones";
const TESTEPISODE_EPISODETITLE = "Garden of Bones";
const TESTEPISODE_YEAR = 2012;
const TESTEPISODE_IMDBID = "tt2069319";
const TESTEPISODE_USER_SCORE = 8.8;
const TESTEPISODE_CRITIC_SCORE = null;
const TESTEPISODE_IMAGE = "MV5BMTczMjc5MTY0NF5BMl5BanBnXkFtZTcwMDY5NDgzNw";
const TESTEPISODE_DIRECTORS = array("David Petrarca");
const TESTEPISODE_GENRES = array("Action", "Adventure", "Drama", "Fantasy", "Romance");

// Class to expose protected members and functions
class OmdbApiExt extends \RatingSync\OmdbApi {
    function _getHttp() { return $this->http; }
    function _getSourceName() { return $this->sourceName; }

    function _getFilmDetailPageUrl($film) { return $this->getFilmDetailPageUrl($film); }
}

class OmdbApiTest extends RatingSyncTestCase
{
    protected function setUp(): void
    {
        parent::setup();
        //$this->verbose = true;
    }

    /**
     * @covers \RatingSync\OmdbApi::__construct
     */
    public function testObjectCanBeConstructed()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new OmdbApi();

        $this->assertTrue(true); // Making sure we made it this far
    }
    
    /**
     * @covers \RatingSync\OmdbApi::getFilmDetailPageUrl
     * @depends testObjectCanBeConstructed
     */
    public function testGetFilmDetailPageUrl()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $site = new OmdbApiExt();
        $film = new Film();
        $film->setUniqueName(TESTFILM_PRIMARY_IMDBID, $site->_getSourceName());

        // Test
        $url = $site->_getFilmDetailPageUrl($film);

        // Verify
        $this->assertEquals("&i=".TESTFILM_PRIMARY_IMDBID, $url);
    }
    
    /**
     * @covers \RatingSync\OmdbApi::getSearchUrl
     * @depends testObjectCanBeConstructed
     */
    public function testGetSearchUrl()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $site = new OmdbApi();
        $args = array("query" => TESTFILM_PRIMARY_TITLE);

        // Test
        $url = $site->getSearchUrl($args);

        // Verify
        $this->assertEquals("&s=".TESTFILM_PRIMARY_TITLE, $url);
    }
    
    /**
     * - Set query with a title with "&" and ":"
     *
     * Expect
     *   - In the URL the special characters have been replaced
     *
     * @covers \RatingSync\OmdbApi::getSearchUrl
     * @depends testObjectCanBeConstructed
     */
    public function testGetSearchUrlSpecialChars()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $site = new OmdbApi();
        $args = array("query" => "Wallace & Gromit: The Curse of the Were-Rabbit");

        // Test
        $url = $site->getSearchUrl($args);

        // Verify
        $this->assertEquals("&s=Wallace+%26+Gromit%3A+The+Curse+of+the+Were-Rabbit", $url);
    }
    
    /**
     * - Set title and year in a new Film object
     *
     * Expect
     *   - The URL shows the title
     *   - The URL shows the year
     *   - URL uses "t" with title (not i or s)
     *
     * @covers \RatingSync\OmdbApi::getFilmUrl
     * @depends testObjectCanBeConstructed
     */
    public function testGetFilmUrl()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $site = new OmdbApi();
        $film = new Film();
        $film->setTitle(TESTFILM_PRIMARY_TITLE);
        $film->setYear(TESTFILM_PRIMARY_YEAR);

        // Test
        $url = $site->getFilmUrl($film);

        // Verify
        $this->assertEquals("&y=".TESTFILM_PRIMARY_YEAR."&t=".urlencode(TESTFILM_PRIMARY_TITLE), $url);
    }

    /**
     * - Set uniqueName in a new Film object
     *
     * Expect
     *   - The URL shows the uniqueName
     *   - URL uses "i" with title (not t or s)
     *
     * @covers \RatingSync\OmdbApi::getFilmUrl
     * @depends testObjectCanBeConstructed
     */
    public function testGetFilmUrlImdbID()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $site = new OmdbApiExt();
        $film = new Film();
        $film->setUniqueName(TESTFILM_PRIMARY_IMDBID, $site->_getSourceName());

        // Test
        $url = $site->getFilmUrl($film);

        // Verify
        $this->assertEquals("&i=".TESTFILM_PRIMARY_IMDBID, $url);
    }

    /**
     * - Set title in a new Film object
     * - Do not set year
     *
     * Expect
     *   - Exception
     *
     * @covers \RatingSync\OmdbApi::getFilmUrl
     */
    public function testGetFilmUrlTitleOnly()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        // Setup
        $site = new OmdbApi();
        $film = new Film();
        $film->setTitle(TESTFILM_PRIMARY_TITLE);

        // Test
        $url = $site->getFilmUrl($film);
    }

    /**
     * - Set year in a new Film object
     * - Do not set title
     *
     * Expect
     *   - Exception
     *
     * @covers \RatingSync\OmdbApi::getFilmUrl
     */
    public function testGetFilmUrlYearOnly()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        // Setup
        $site = new OmdbApi();
        $film = new Film();
        $film->setYear(TESTFILM_PRIMARY_YEAR);

        // Test
        $url = $site->getFilmUrl($film);
    }

    /**
     * - Set episode title in a new Film object
     * - Do not set year
     *
     * Expect
     *   - Exception
     *
     * @covers \RatingSync\OmdbApi::getFilmUrl
     */
    public function testGetFilmUrlEpisodeTitleOnly()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        // Setup
        $site = new OmdbApi();
        $film = new Film();
        $film->setEpisodeTitle(TESTEPISODE_TITLE);

        // Test
        $url = $site->getFilmUrl($film);
    }

    /**
     * - Set title and episode title in a new Film object
     * - Do not set year
     *
     * Expect
     *   - Exception
     *
     * @covers \RatingSync\OmdbApi::getFilmUrl
     */
    public function testGetFilmUrlTitleAndEpisodeTitleNoYear()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        // Setup
        $site = new OmdbApi();
        $film = new Film();
        $film->setTitle(TESTFILM_PRIMARY_TITLE);
        $film->setEpisodeTitle(TESTEPISODE_TITLE);

        // Test
        $url = $site->getFilmUrl($film);
    }

    /**
     * - Set title and year in a new Film object
     *
     * Expect
     *   - The URL shows title and year
     *   - URL uses "t" with title (not i or s)
     *
     * @covers \RatingSync\OmdbApi::getFilmUrl
     * @depends testObjectCanBeConstructed
     */
    public function testGetFilmUrlTitleYear()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $site = new OmdbApi();
        $film = new Film();
        $film->setTitle(TESTFILM_PRIMARY_TITLE);
        $film->setYear(TESTFILM_PRIMARY_YEAR);

        // Test
        $url = $site->getFilmUrl($film);

        // Verify
        $this->assertEquals("&y=".TESTFILM_PRIMARY_YEAR."&t=".urlencode(TESTFILM_PRIMARY_TITLE), $url);
    }

    /**
     * - Set episode title and year in a new Film object
     *
     * Expect
     *   - The URL shows title and year
     *   - URL uses "t" with title (not i or s)
     *
     * @covers \RatingSync\OmdbApi::getFilmUrl
     * @depends testObjectCanBeConstructed
     */
    public function testGetFilmUrlEpisodeTitleYear()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $site = new OmdbApi();
        $film = new Film();
        $film->setEpisodeTitle(TESTEPISODE_TITLE);
        $film->setYear(TESTEPISODE_YEAR);

        // Test
        $url = $site->getFilmUrl($film);

        // Verify
        $this->assertEquals("&y=".TESTEPISODE_YEAR."&t=".urlencode(TESTEPISODE_TITLE), $url);
    }

    /**
     * - Set title, episode title and year in a new Film object
     *
     * Expect
     *   - The URL shows title and year
     *   - Episode title is ignored
     *   - URL uses "t" with title (not i or s)
     *
     * @covers \RatingSync\OmdbApi::getFilmUrl
     * @depends testObjectCanBeConstructed
     */
    public function testGetFilmUrlTitleAndEpisodeTitleWithYear()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $site = new OmdbApi();
        $film = new Film();
        $film->setTitle(TESTFILM_PRIMARY_TITLE);
        $film->setEpisodeTitle(TESTEPISODE_TITLE);
        $film->setYear(TESTEPISODE_YEAR);

        // Test
        $url = $site->getFilmUrl($film);

        // Verify
        $this->assertEquals("&y=".TESTEPISODE_YEAR."&t=".urlencode(TESTFILM_PRIMARY_TITLE), $url);
    }

    /**
     * - Set uniqueName, title, and year in a new Film object
     *
     * Expect
     *   - The URL shows uniqueName
     *   - Episode title and year are ignored
     *   - URL uses "i" with title (not t or s)
     *
     * @covers \RatingSync\OmdbApi::getFilmUrl
     * @depends testObjectCanBeConstructed
     */
    public function testGetFilmUrlImdbIdAndTitleWithYear()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $site = new OmdbApiExt();
        $film = new Film();
        $film->setUniqueName(TESTFILM_PRIMARY_IMDBID, $site->_getSourceName());
        $film->setTitle(TESTFILM_PRIMARY_TITLE);
        $film->setYear(TESTEPISODE_YEAR);

        // Test
        $url = $site->getFilmUrl($film);

        // Verify
        $this->assertEquals("&i=".TESTFILM_PRIMARY_IMDBID, $url);
    }

    /**
     * - Set title, year (primary test film) in a new Film object
     * - Set content type to film
     *
     * Expect
     *   - The URL shows title and year
     *   - type is "movie"
     *   - URL uses "t" with title (not i or s)
     *
     * @covers \RatingSync\OmdbApi::getFilmUrl
     * @depends testObjectCanBeConstructed
     */
    public function testGetFilmUrlContentTypeFilm()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $site = new OmdbApi();
        $film = new Film();
        $film->setTitle(TESTFILM_PRIMARY_TITLE);
        $film->setYear(TESTFILM_PRIMARY_YEAR);
        $film->setContentType(Film::CONTENT_FILM);

        // Test
        $url = $site->getFilmUrl($film);

        // Verify
        $this->assertEquals("&y=".TESTFILM_PRIMARY_YEAR."&t=".urlencode(TESTFILM_PRIMARY_TITLE)."&type=movie", $url);
    }

    /**
     * - Set title, year (tv episode) in a new Film object
     * - Set content type to episode
     *
     * Expect
     *   - The URL shows title and year
     *   - type is "episode"
     *   - URL uses "t" with title (not i or s)
     *
     * @covers \RatingSync\OmdbApi::getFilmUrl
     * @depends testObjectCanBeConstructed
     */
    public function testGetFilmUrlContentTypeEpisode()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $site = new OmdbApi();
        $film = new Film();
        $film->setTitle(TESTEPISODE_TITLE);
        $film->setYear(TESTEPISODE_YEAR);
        $film->setContentType(Film::CONTENT_TV_EPISODE);

        // Test
        $url = $site->getFilmUrl($film);

        // Verify
        $this->assertEquals("&y=".TESTEPISODE_YEAR."&t=".urlencode(TESTEPISODE_TITLE)."&type=episode", $url);
    }

    /**
     * - Set title, year (tv series) in a new Film object
     * - Set content type to series
     *
     * Expect
     *   - The URL shows title and year
     *   - type is "series"
     *   - URL uses "t" with title (not i or s)
     *
     * @covers \RatingSync\OmdbApi::getFilmUrl
     * @depends testObjectCanBeConstructed
     */
    public function testGetFilmUrlContentTypeSeries()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $site = new OmdbApi();
        $film = new Film();
        $film->setTitle(TESTSERIES_TITLE);
        $film->setYear(TESTSERIES_YEAR);
        $film->setContentType(Film::CONTENT_TV_SERIES);

        // Test
        $url = $site->getFilmUrl($film);

        // Verify
        $this->assertEquals("&y=".TESTSERIES_YEAR."&t=".urlencode(TESTSERIES_TITLE)."&type=series", $url);
    }

    /**
     * - Set title, year (primary test film) in a new Film object
     * - Set content type to short
     *
     * Expect
     *   - The URL shows title and year
     *   - type with not be shown
     *   - URL uses "t" with title (not i or s)
     *
     * @covers \RatingSync\OmdbApi::getFilmUrl
     * @depends testObjectCanBeConstructed
     */
    public function testGetFilmUrlContentTypeShort()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $site = new OmdbApi();
        $film = new Film();
        $film->setTitle(TESTFILM_PRIMARY_TITLE);
        $film->setYear(TESTFILM_PRIMARY_YEAR);
        $film->setContentType(Film::CONTENT_SHORTFILM);

        // Test
        $url = $site->getFilmUrl($film);

        // Verify
        $this->assertEquals("&y=".TESTFILM_PRIMARY_YEAR."&t=".urlencode(TESTFILM_PRIMARY_TITLE), $url);
    }

    /**
     * - Set title, year in a new Film object
     * - Set content type to season
     *
     * Expect
     *   - The URL shows title and year
     *   - type with not be shown
     *   - URL uses "t" with title (not i or s)
     *
     * @covers \RatingSync\OmdbApi::getFilmUrl
     * @depends testObjectCanBeConstructed
     */
    public function testGetFilmUrlContentTypeSeason()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $site = new OmdbApi();
        $film = new Film();
        $film->setTitle(TESTFILM_PRIMARY_TITLE);
        $film->setYear(TESTFILM_PRIMARY_YEAR);
        $film->setContentType(Film::CONTENT_TV_SEASON);

        // Test
        $url = $site->getFilmUrl($film);

        // Verify
        $this->assertEquals("&y=".TESTFILM_PRIMARY_YEAR."&t=".urlencode(TESTFILM_PRIMARY_TITLE), $url);
    }

    /**
     * - Set title, year in a new Film object
     * - Do not set content type
     *
     * Expect
     *   - The URL shows title and year
     *   - type with not be shown
     *   - URL uses "t" with title (not i or s)
     *
     * @covers \RatingSync\OmdbApi::getFilmUrl
     * @depends testObjectCanBeConstructed
     */
    public function testGetFilmUrlContentTypeDefault()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $site = new OmdbApi();
        $film = new Film();
        $film->setTitle(TESTFILM_PRIMARY_TITLE);
        $film->setYear(TESTFILM_PRIMARY_YEAR);

        // Test
        $url = $site->getFilmUrl($film);

        // Verify
        $this->assertEquals("&y=".TESTFILM_PRIMARY_YEAR."&t=".urlencode(TESTFILM_PRIMARY_TITLE), $url);
    }

    /**
     * - Set title, year in a new Film object
     *
     * Expect
     *   - Return value is true
     *   - Film object has the correct IMDb ID
     *
     * @covers \RatingSync\OmdbApi::searchWebsiteForUniqueFilm
     * @depends testObjectCanBeConstructed
     */
    public function testSearchWebsiteForUniqueFilm()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $site = new OmdbApiExt();
        $film = new Film();
        $film->setTitle(TESTFILM_PRIMARY_TITLE);
        $film->setYear(TESTFILM_PRIMARY_YEAR);

        // Test
        $success = $site->searchWebsiteForUniqueFilm($film);
        $uniqueName = "";
        if ($success) {
            $uniqueName = $film->getUniqueName($site->_getSourceName());
        }

        // Verify
        $this->assertTrue($success);
        $this->assertEquals(TESTFILM_PRIMARY_IMDBID, $uniqueName);
    }

    /**
     * @covers \RatingSync\OmdbApi::getFilmDetailFromWebsite
     * @depends testObjectCanBeConstructed
     */
    public function testGetFilmDetailFromWebsiteFromNull()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        $site = new OmdbApi();
        $site->getFilmDetailFromWebsite(null);
    }

    /**
     * @covers \RatingSync\OmdbApi::getFilmDetailFromWebsite
     * @depends testObjectCanBeConstructed
     */
    public function testGetFilmDetailFromWebsiteFromString()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        $site = new OmdbApi();
        $site->getFilmDetailFromWebsite("String_Not_Film_Object");
    }

    /**
     * @covers \RatingSync\OmdbApi::getFilmDetailFromWebsite
     * @depends testObjectCanBeConstructed
     */
    public function testGetFilmDetailFromWebsiteWithoutUniqueName()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        $site = new OmdbApi();
        $film = new Film();
        $site->getFilmDetailFromWebsite($film);
    }

    /**
     * @covers \RatingSync\OmdbApi::getFilmDetailFromWebsite
     */
    public function testGetFilmDetailFromWebsiteNoMatch()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\Exception::class);

        $site = new OmdbApiExt();
        $film = new Film();
        $film->setUniqueName("NO_FILMID_MATCH", $site->_getSourceName());
        $site->getFilmDetailFromWebsite($film, true);
    }

    /**
     * - Set uniqueName in a new Film object
     *
     * Expect
     *   - Film values populated... uniqueName, title, year, image, contentType, genres, directors
     *   - Film values NOT populated... your score, rating date, suggested score
     *   - Film values populate for OMDb... image, critic score, user score
     *   - Film values populate for IMDb... image, critic score, user score
     *
     * @covers \RatingSync\OmdbApi::getFilmDetailFromWebsite
     * @depends testObjectCanBeConstructed
     */
    public function testGetFilmDetailFromWebsite()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $site = new OmdbApiExt();
        $film = new Film();

        // Test
        $film->setUniqueName(TESTFILM_PRIMARY_IMDBID, $site->_getSourceName());
        $site->getFilmDetailFromWebsite($film, true);

        // Verify
            // Film data
        $this->assertEquals(TESTFILM_PRIMARY_TITLE, $film->getTitle(), 'Title');
        $this->assertEquals(TESTFILM_PRIMARY_YEAR, $film->getYear(), 'Year');
        $this->assertEquals(Film::CONTENT_FILM, $film->getContentType(), 'Content Type');
        $this->assertEmpty($film->getImage(), 'Film image should be empty');
        $this->assertEquals(TESTFILM_PRIMARY_DIRECTORS, $film->getDirectors(), 'Director(s)');
        $this->assertEquals(TESTFILM_PRIMARY_GENRES, $film->getGenres(), 'Genres');

            // OMDbAPI source-specific
        $this->assertEquals(1, preg_match('@('.TESTFILM_PRIMARY_IMAGE.')@', $film->getImage($site->_getSourceName()), $matches), 'Source image');
        $this->assertEquals(TESTFILM_PRIMARY_IMDBID, $film->getUniqueName($site->_getSourceName()), 'Unique Name');
        $this->assertEquals(TESTFILM_PRIMARY_CRITIC_SCORE, $film->getCriticScore($site->_getSourceName()), 'Critic score');
        $this->assertEquals(TESTFILM_PRIMARY_USER_SCORE, $film->getUserScore($site->_getSourceName()), 'User score');

            // IMDb source-specific
        $this->assertEquals(1, preg_match('@('.TESTFILM_PRIMARY_IMAGE.')@', $film->getImage(Constants::SOURCE_IMDB), $matches), 'Source image');
        $this->assertEquals(TESTFILM_PRIMARY_IMDBID, $film->getUniqueName(Constants::SOURCE_IMDB), 'Unique Name');
        $this->assertEquals(TESTFILM_PRIMARY_CRITIC_SCORE, $film->getCriticScore(Constants::SOURCE_IMDB), 'Critic score');
        $this->assertEquals(TESTFILM_PRIMARY_USER_SCORE, $film->getUserScore(Constants::SOURCE_IMDB), 'User score');

            // Not available in the detail page
        $rating = $film->getRating($site->_getSourceName());
        $this->assertNull($rating->getYourScore(), 'Your Score');
        $this->assertNull($rating->getYourRatingDate(), 'Rating date');
        $this->assertNull($rating->getSuggestedScore(), 'Suggested score');
    }

    /**
     * - Set uniqueName in a new Film object
     * - Set overwrite=true
     *
     * Expect
     *   - Film values populated from the API's data
     *
     * @covers \RatingSync\OmdbApi::getFilmDetailFromWebsite
     * @depends testGetFilmDetailFromWebsite
     */
    public function testGetFilmDetailFromWebsiteOverwriteTrueOverEmpty()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new OmdbApiExt();

        $film = new Film();
        $film->setUniqueName(TESTFILM_PRIMARY_IMDBID, $site->_getSourceName());
        $site->getFilmDetailFromWebsite($film, true);

        // Same results as testGetFilmDetailFromWebsite
            // Film data
        $this->assertEquals(TESTFILM_PRIMARY_TITLE, $film->getTitle(), 'Title');
        $this->assertEquals(TESTFILM_PRIMARY_YEAR, $film->getYear(), 'Year');
        $this->assertEquals(Film::CONTENT_FILM, $film->getContentType(), 'Content Type');
        $this->assertEmpty($film->getImage(), 'Film image should be empty');
        $this->assertEquals(TESTFILM_PRIMARY_DIRECTORS, $film->getDirectors(), 'Director(s)');
        $this->assertEquals(TESTFILM_PRIMARY_GENRES, $film->getGenres(), 'Genres');

            // OMDbAPI source-specific
        $this->assertEquals(1, preg_match('@('.TESTFILM_PRIMARY_IMAGE.')@', $film->getImage($site->_getSourceName()), $matches), 'Source image');
        $this->assertEquals(TESTFILM_PRIMARY_IMDBID, $film->getUniqueName($site->_getSourceName()), 'Unique Name');
        $this->assertEquals(TESTFILM_PRIMARY_CRITIC_SCORE, $film->getCriticScore($site->_getSourceName()), 'Critic score');
        $this->assertEquals(TESTFILM_PRIMARY_USER_SCORE, $film->getUserScore($site->_getSourceName()), 'User score');

            // IMDb source-specific
        $this->assertEquals(1, preg_match('@('.TESTFILM_PRIMARY_IMAGE.')@', $film->getImage(Constants::SOURCE_IMDB), $matches), 'Source image');
        $this->assertEquals(TESTFILM_PRIMARY_IMDBID, $film->getUniqueName(Constants::SOURCE_IMDB), 'Unique Name');
        $this->assertEquals(TESTFILM_PRIMARY_CRITIC_SCORE, $film->getCriticScore(Constants::SOURCE_IMDB), 'Critic score');
        $this->assertEquals(TESTFILM_PRIMARY_USER_SCORE, $film->getUserScore(Constants::SOURCE_IMDB), 'User score');

            // Not available in the detail page
        $rating = $film->getRating($site->_getSourceName());
        $this->assertNull($rating->getYourScore(), 'Your Score');
        $this->assertNull($rating->getYourRatingDate(), 'Rating date');
        $this->assertNull($rating->getSuggestedScore(), 'Suggested score');
    }

    /**
     * - Set overwrite=true
     * - Set original values in a new Film object
     * - Set source-specific original values for OMDbAPI
     * - Set source-specific original values for IMDb
     * - Set source-specific original values for Jinni
     *
     * Expect
     *   - Film values changed from original to OMDb data
     *   - Source-specific OMDbAPI values changed from original to ODMb data
     *   - Source-specific IMDb values changed from original to ODMb data
     *   - Source-specific Jinnni values not changed
     *
     * @covers \RatingSync\OmdbApi::getFilmDetailFromWebsite
     * @depends testGetFilmDetailFromWebsite
     */
    public function testGetFilmDetailFromWebsiteOverwriteTrueOverOriginalData()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $site = new OmdbApiExt();
        $film = new Film();

            // Setup original data
        $film->setTitle("Original_Title");
        $film->setYear(1900);
        $film->setContentType("FeatureFilm");
        $film->setImage("Original_Image");
        $film->addGenre("Original_Genre1");
        $film->addGenre("Original_Genre2");
        $film->addDirector("Original_Director1");
        $film->addDirector("Original_Director2");

            // Setup original data (OMDbAPI)
        $film->setUniqueName("Original_UniqueName_Omdb", $site->_getSourceName());
        $film->setImage("Original_Image_Omdb", $site->_getSourceName());
        $film->setCriticScore(5, $site->_getSourceName());
        $film->setUserScore(6, $site->_getSourceName());
        $ratingOmdbOrig = new Rating($site->_getSourceName());
        $ratingOmdbOrig->setYourScore(3);
        $ratingOmdbOrig->setYourRatingDate(new \DateTime('2000-01-03'));
        $ratingOmdbOrig->setSuggestedScore(4);
        $film->setRating($ratingOmdbOrig, $site->_getSourceName());

            // Setup original data (IMDb)
        $film->setUniqueName("Original_UniqueName_Imdb", Constants::SOURCE_IMDB);
        $film->setImage("Original_Image_Imdb", Constants::SOURCE_IMDB);
        $film->setCriticScore(4, Constants::SOURCE_IMDB);
        $film->setUserScore(5, Constants::SOURCE_IMDB);
        $ratingImdbOrig = new Rating(Constants::SOURCE_IMDB);
        $ratingImdbOrig->setYourScore(2);
        $ratingImdbOrig->setYourRatingDate(new \DateTime('2000-01-02'));
        $ratingImdbOrig->setSuggestedScore(3);
        $film->setRating($ratingImdbOrig, Constants::SOURCE_IMDB);

            // Setup original data (Jinni)
        $film->setUniqueName("Original_UniqueName_Jinni", Constants::SOURCE_JINNI);
        $film->setImage("Original_Image_Jinni", Constants::SOURCE_JINNI);
        $film->setCriticScore(3, Constants::SOURCE_JINNI);
        $film->setUserScore(4, Constants::SOURCE_JINNI);
        $ratingJinniOrig = new Rating(Constants::SOURCE_JINNI);
        $ratingJinniOrig->setYourScore(1);
        $ratingJinniOrig->setYourRatingDate(new \DateTime('2000-01-01'));
        $ratingJinniOrig->setSuggestedScore(2);
        $film->setRating($ratingJinniOrig, Constants::SOURCE_JINNI);

        // Test
        $film->setUniqueName(TESTFILM_PRIMARY_IMDBID, $site->_getSourceName());
        $site->getFilmDetailFromWebsite($film, true);

        // Verify
            // new data
        $this->assertEquals(TESTFILM_PRIMARY_TITLE, $film->getTitle(), 'Title');
        $this->assertEquals(TESTFILM_PRIMARY_YEAR, $film->getYear(), 'Year');
        $this->assertEquals(Film::CONTENT_FILM, $film->getContentType(), 'Content Type');
        $this->assertEquals(TESTFILM_PRIMARY_GENRES, $film->getGenres(), 'Genres');
        $this->assertEquals(TESTFILM_PRIMARY_DIRECTORS, $film->getDirectors(), 'Director(s)');
        $this->assertEquals("Original_Image", $film->getImage(), 'Film image');

            // new data (OMDbAPI)
        $this->assertEquals(TESTFILM_PRIMARY_IMDBID, $film->getUniqueName($site->_getSourceName()), 'Unique Name');
        $rating = $film->getRating($site->_getSourceName());
        $this->assertEquals(TESTFILM_PRIMARY_CRITIC_SCORE, $film->getCriticScore($site->_getSourceName()), 'Critic score');
        $this->assertEquals(TESTFILM_PRIMARY_USER_SCORE, $film->getUserScore($site->_getSourceName()), 'User score');
        $this->assertEquals(1, preg_match('@('.TESTFILM_PRIMARY_IMAGE.')@', $film->getImage($site->_getSourceName()), $matches), 'Source image');
            // The film detail page does not have these fields.  Don't overwrite them.
        $this->assertEquals(3, $rating->getYourScore(), 'Your Score');
        $this->assertEquals("1/3/2000", $rating->getYourRatingDate()->format("n/j/Y"), 'Rating date');
        $this->assertEquals(4, $rating->getSuggestedScore(), 'Suggested score');

            // new data (IMDb)
        $this->assertEquals(TESTFILM_PRIMARY_IMDBID, $film->getUniqueName(Constants::SOURCE_IMDB), 'Unique Name');
        $rating = $film->getRating(Constants::SOURCE_IMDB);
        $this->assertEquals(TESTFILM_PRIMARY_CRITIC_SCORE, $film->getCriticScore(Constants::SOURCE_IMDB), 'Critic score');
        $this->assertEquals(TESTFILM_PRIMARY_USER_SCORE, $film->getUserScore(Constants::SOURCE_IMDB), 'User score');
        $this->assertEquals(1, preg_match('@('.TESTFILM_PRIMARY_IMAGE.')@', $film->getImage(Constants::SOURCE_IMDB), $matches), 'Source image');
            // The film detail page does not have these fields.  Don't overwrite them.
        $this->assertEquals(2, $rating->getYourScore(), 'Your Score');
        $this->assertEquals("1/2/2000", $rating->getYourRatingDate()->format("n/j/Y"), 'Rating date');
        $this->assertEquals(3, $rating->getSuggestedScore(), 'Suggested score');

            // new data (Jinni, unchanged)
        $this->assertEquals("Original_UniqueName_Jinni", $film->getUniqueName(Constants::SOURCE_JINNI), 'Unique Name (Jinni)');
        $this->assertEquals(3, $film->getCriticScore(Constants::SOURCE_JINNI), 'Critic score (Jinni)');
        $this->assertEquals(4, $film->getUserScore(Constants::SOURCE_JINNI), 'User score (Jinni)');
        $rating = $film->getRating(Constants::SOURCE_JINNI);
        $this->assertEquals(1, $rating->getYourScore(), 'Your Score (Jinni)');
        $this->assertEquals("1/1/2000", $rating->getYourRatingDate()->format("n/j/Y"), 'Rating date (Jinni)');
        $this->assertEquals(2, $rating->getSuggestedScore(), 'Suggested score (Jinni)');
    }

    /**
     * - Set overwrite=false
     * - Set original values in a new Film object
     * - Set source-specific original values for OMDbAPI
     * - Set source-specific original values for IMDb
     * - Set source-specific original values for Jinni
     *
     * Expect
     *   - Original values kept
     *
     * @covers \RatingSync\OmdbApi::getFilmDetailFromWebsite
     * @depends testGetFilmDetailFromWebsite
     */
    public function testGetFilmDetailFromWebsiteOverwriteFalseOverOriginalData()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new OmdbApiExt();
        $film = new Film();

        // Setup original data
        $film->setTitle("Original_Title");
        $film->setYear(1900);
        $film->setContentType("FeatureFilm");
        $film->setImage("Original_Image");
        $film->addGenre("Original_Genre1");
        $film->addGenre("Original_Genre2");
        $film->addDirector("Original_Director1");
        $film->addDirector("Original_Director2");

        // Setup original data (OMDbAPI)
        $film->setUniqueName("Original_UniqueName_Omdb", $site->_getSourceName());
        $film->setImage("Original_Image_Omdb", $site->_getSourceName());
        $film->setCriticScore(5, $site->_getSourceName());
        $film->setUserScore(6, $site->_getSourceName());
        $ratingOmdbOrig = new Rating($site->_getSourceName());
        $ratingOmdbOrig->setYourScore(3);
        $ratingOmdbOrig->setYourRatingDate(new \DateTime('2000-01-03'));
        $ratingOmdbOrig->setSuggestedScore(4);
        $film->setRating($ratingOmdbOrig, $site->_getSourceName());

        // Setup original data (IMDb)
        $film->setUniqueName("Original_UniqueName_Imdb", Constants::SOURCE_IMDB);
        $film->setImage("Original_Image_Imdb", Constants::SOURCE_IMDB);
        $film->setCriticScore(4, Constants::SOURCE_IMDB);
        $film->setUserScore(5, Constants::SOURCE_IMDB);
        $ratingImdbOrig = new Rating(Constants::SOURCE_IMDB);
        $ratingImdbOrig->setYourScore(2);
        $ratingImdbOrig->setYourRatingDate(new \DateTime('2000-01-02'));
        $ratingImdbOrig->setSuggestedScore(3);
        $film->setRating($ratingImdbOrig, Constants::SOURCE_IMDB);

        // Setup original data (Jinni)
        $film->setUniqueName("Original_UniqueName_Jinni", Constants::SOURCE_JINNI);
        $film->setImage("Original_Image_Jinni", Constants::SOURCE_JINNI);
        $film->setCriticScore(3, Constants::SOURCE_JINNI);
        $film->setUserScore(4, Constants::SOURCE_JINNI);
        $ratingJinniOrig = new Rating(Constants::SOURCE_JINNI);
        $ratingJinniOrig->setYourScore(1);
        $ratingJinniOrig->setYourRatingDate(new \DateTime('2000-01-01'));
        $ratingJinniOrig->setSuggestedScore(2);
        $film->setRating($ratingJinniOrig, Constants::SOURCE_JINNI);

        // Get detail not overwriting
        $film->setUniqueName(TESTFILM_PRIMARY_IMDBID, $site->_getSourceName());
        $site->getFilmDetailFromWebsite($film, false);

        // Verify - Same original data
        $this->assertEquals("Original_Title", $film->getTitle(), 'Title');
        $this->assertEquals(1900, $film->getYear(), 'Year');
        $this->assertEquals("Original_Image", $film->getImage(), 'Image link');
        $this->assertEquals("FeatureFilm", $film->getContentType(), 'Content Type');
        $this->assertEquals(array("Original_Director1", "Original_Director2"), $film->getDirectors(), 'Director(s)');
        $this->assertEquals(array("Original_Genre1", "Original_Genre2"), $film->getGenres(), 'Genres');

        // Verify - Same original data (OMDbAPI)
        $this->assertEquals(TESTFILM_PRIMARY_IMDBID, $film->getUniqueName($site->_getSourceName()), 'Unique Name');
        $this->assertEquals(5, $film->getCriticScore($site->_getSourceName()), 'Critic score');
        $this->assertEquals(6, $film->getUserScore($site->_getSourceName()), 'User score');
        $rating = $film->getRating($site->_getSourceName());
        $this->assertEquals(3, $rating->getYourScore(), 'Your Score');
        $this->assertEquals("1/3/2000", $rating->getYourRatingDate()->format("n/j/Y"), 'Your Rating Date');
        $this->assertEquals(4, $rating->getSuggestedScore(), 'Suggested score');

        // Verify - Same original data (IMDb)
        $this->assertEquals("Original_UniqueName_Imdb", $film->getUniqueName(Constants::SOURCE_IMDB), 'Unique Name');
        $this->assertEquals(4, $film->getCriticScore(Constants::SOURCE_IMDB), 'Critic score');
        $this->assertEquals(5, $film->getUserScore(Constants::SOURCE_IMDB), 'User score');
        $rating = $film->getRating(Constants::SOURCE_IMDB);
        $this->assertEquals(2, $rating->getYourScore(), 'Your Score');
        $this->assertEquals("1/2/2000", $rating->getYourRatingDate()->format("n/j/Y"), 'Your Rating Date');
        $this->assertEquals(3, $rating->getSuggestedScore(), 'Suggested score');

        // Verify - Same original data (Jinni)
        $this->assertEquals("Original_UniqueName_Jinni", $film->getUniqueName(Constants::SOURCE_JINNI), 'Unique Name (Jinni)');
        $this->assertEquals(3, $film->getCriticScore(Constants::SOURCE_JINNI), 'Critic score (Jinni)');
        $this->assertEquals(4, $film->getUserScore(Constants::SOURCE_JINNI), 'User score (Jinni)');
        $rating = $film->getRating(Constants::SOURCE_JINNI);
        $this->assertEquals(1, $rating->getYourScore(), 'Your Score (Jinni)');
        $this->assertEquals("1/1/2000", $rating->getYourRatingDate()->format("n/j/Y"), 'Your Rating Date (Jinni)');
        $this->assertEquals(2, $rating->getSuggestedScore(), 'Suggested score (Jinni)');
    }

    /**
     * - Set overwrite=false
     * - Do not set original values
     *
     * Expect
     *   - Film values set to OMDb data
     *   - Source-specific OMDbAPI values set to ODMb data
     *   - Source-specific IMDb values set to ODMb data
     *   - Source-specific Jinnni values not set
     *
     * @covers \RatingSync\OmdbApi::getFilmDetailFromWebsite
     * @depends testGetFilmDetailFromWebsite
     */
    public function testGetFilmDetailFromWebsiteOverwriteFalseOverEmpty()
    {$this->start(__CLASS__, __FUNCTION__);

        $site = new OmdbApiExt();

        $film = new Film();
        $film->setUniqueName("tt2294629", $site->_getSourceName());
        $site->getFilmDetailFromWebsite($film, false);

        // Same results as testGetFilmDetailFromWebsite or testGetFilmDetailFromWebsiteOverwriteTrueOverEmpty
            // Film data
        $this->assertEquals(TESTFILM_PRIMARY_TITLE, $film->getTitle(), 'Title');
        $this->assertEquals(TESTFILM_PRIMARY_YEAR, $film->getYear(), 'Year');
        $this->assertEquals(Film::CONTENT_FILM, $film->getContentType(), 'Content Type');
        $this->assertEmpty($film->getImage(), 'Film image should be empty');
        $this->assertEquals(TESTFILM_PRIMARY_DIRECTORS, $film->getDirectors(), 'Director(s)');
        $this->assertEquals(TESTFILM_PRIMARY_GENRES, $film->getGenres(), 'Genres');

            // OMDbAPI source-specific
        $this->assertEquals(1, preg_match('@('.TESTFILM_PRIMARY_IMAGE.')@', $film->getImage($site->_getSourceName()), $matches), 'Source image');
        $this->assertEquals(TESTFILM_PRIMARY_IMDBID, $film->getUniqueName($site->_getSourceName()), 'Unique Name');
        $this->assertEquals(TESTFILM_PRIMARY_CRITIC_SCORE, $film->getCriticScore($site->_getSourceName()), 'Critic score');
        $this->assertEquals(TESTFILM_PRIMARY_USER_SCORE, $film->getUserScore($site->_getSourceName()), 'User score');

            // IMDb source-specific
        $this->assertEquals(1, preg_match('@('.TESTFILM_PRIMARY_IMAGE.')@', $film->getImage(Constants::SOURCE_IMDB), $matches), 'Source image');
        $this->assertEquals(TESTFILM_PRIMARY_IMDBID, $film->getUniqueName(Constants::SOURCE_IMDB), 'Unique Name');
        $this->assertEquals(TESTFILM_PRIMARY_CRITIC_SCORE, $film->getCriticScore(Constants::SOURCE_IMDB), 'Critic score');
        $this->assertEquals(TESTFILM_PRIMARY_USER_SCORE, $film->getUserScore(Constants::SOURCE_IMDB), 'User score');

            // Not available in the detail page
        $rating = $film->getRating($site->_getSourceName());
        $this->assertNull($rating->getYourScore(), 'Your Score');
        $this->assertNull($rating->getYourRatingDate(), 'Rating date');
        $this->assertNull($rating->getSuggestedScore(), 'Suggested score');
    }

    /**
     * - Overwrite default is true
     * - testGetFilmDetailFromWebsiteOverwriteTrueOverOriginalData is the same
     * 
     * @covers \RatingSync\Imdb::getFilmDetailFromWebsite
     * @depends testGetFilmDetailFromWebsiteOverwriteTrueOverOriginalData
     */
    public function testGetFilmDetailFromWebsiteOverwriteDefault()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $site = new OmdbApiExt();
        $film = new Film();

            // Setup original data
        $film->setTitle("Original_Title");
        $film->setYear(1900);
        $film->setContentType("FeatureFilm");
        $film->setImage("Original_Image");
        $film->addGenre("Original_Genre1");
        $film->addGenre("Original_Genre2");
        $film->addDirector("Original_Director1");
        $film->addDirector("Original_Director2");

            // Setup original data (OMDbAPI)
        $film->setUniqueName("Original_UniqueName_Omdb", $site->_getSourceName());
        $film->setImage("Original_Image_Omdb", $site->_getSourceName());
        $film->setCriticScore(5, $site->_getSourceName());
        $film->setUserScore(6, $site->_getSourceName());
        $ratingOmdbOrig = new Rating($site->_getSourceName());
        $ratingOmdbOrig->setYourScore(3);
        $ratingOmdbOrig->setYourRatingDate(new \DateTime('2000-01-03'));
        $ratingOmdbOrig->setSuggestedScore(4);
        $film->setRating($ratingOmdbOrig, $site->_getSourceName());

            // Setup original data (IMDb)
        $film->setUniqueName("Original_UniqueName_Imdb", Constants::SOURCE_IMDB);
        $film->setImage("Original_Image_Imdb", Constants::SOURCE_IMDB);
        $film->setCriticScore(4, Constants::SOURCE_IMDB);
        $film->setUserScore(5, Constants::SOURCE_IMDB);
        $ratingImdbOrig = new Rating(Constants::SOURCE_IMDB);
        $ratingImdbOrig->setYourScore(2);
        $ratingImdbOrig->setYourRatingDate(new \DateTime('2000-01-02'));
        $ratingImdbOrig->setSuggestedScore(3);
        $film->setRating($ratingImdbOrig, Constants::SOURCE_IMDB);

            // Setup original data (Jinni)
        $film->setUniqueName("Original_UniqueName_Jinni", Constants::SOURCE_JINNI);
        $film->setImage("Original_Image_Jinni", Constants::SOURCE_JINNI);
        $film->setCriticScore(3, Constants::SOURCE_JINNI);
        $film->setUserScore(4, Constants::SOURCE_JINNI);
        $ratingJinniOrig = new Rating(Constants::SOURCE_JINNI);
        $ratingJinniOrig->setYourScore(1);
        $ratingJinniOrig->setYourRatingDate(new \DateTime('2000-01-01'));
        $ratingJinniOrig->setSuggestedScore(2);
        $film->setRating($ratingJinniOrig, Constants::SOURCE_JINNI);

        // Test
        $film->setUniqueName(TESTFILM_PRIMARY_IMDBID, $site->_getSourceName());
        $site->getFilmDetailFromWebsite($film);

        // Verify
            // new data
        $this->assertEquals(TESTFILM_PRIMARY_TITLE, $film->getTitle(), 'Title');
        $this->assertEquals(TESTFILM_PRIMARY_YEAR, $film->getYear(), 'Year');
        $this->assertEquals(Film::CONTENT_FILM, $film->getContentType(), 'Content Type');
        $this->assertEquals(TESTFILM_PRIMARY_GENRES, $film->getGenres(), 'Genres');
        $this->assertEquals(TESTFILM_PRIMARY_DIRECTORS, $film->getDirectors(), 'Director(s)');
        $this->assertEquals("Original_Image", $film->getImage(), 'Image link');

            // new data (OMDbAPI)
        $this->assertEquals(TESTFILM_PRIMARY_IMDBID, $film->getUniqueName($site->_getSourceName()), 'Unique Name');
        $rating = $film->getRating($site->_getSourceName());
        $this->assertEquals(TESTFILM_PRIMARY_CRITIC_SCORE, $film->getCriticScore($site->_getSourceName()), 'Critic score');
        $this->assertEquals(TESTFILM_PRIMARY_USER_SCORE, $film->getUserScore($site->_getSourceName()), 'User score');
        $this->assertEquals(1, preg_match('@('.TESTFILM_PRIMARY_IMAGE.')@', $film->getImage($site->_getSourceName()), $matches), 'Source image');
            // The film detail page does not have these fields.  Don't overwrite them.
        $this->assertEquals(3, $rating->getYourScore(), 'Your Score');
        $this->assertEquals("1/3/2000", $rating->getYourRatingDate()->format("n/j/Y"), 'Rating date');
        $this->assertEquals(4, $rating->getSuggestedScore(), 'Suggested score');

            // new data (IMDb)
        $this->assertEquals(TESTFILM_PRIMARY_IMDBID, $film->getUniqueName(Constants::SOURCE_IMDB), 'Unique Name');
        $rating = $film->getRating(Constants::SOURCE_IMDB);
        $this->assertEquals(TESTFILM_PRIMARY_CRITIC_SCORE, $film->getCriticScore(Constants::SOURCE_IMDB), 'Critic score');
        $this->assertEquals(TESTFILM_PRIMARY_USER_SCORE, $film->getUserScore(Constants::SOURCE_IMDB), 'User score');
        $this->assertEquals(1, preg_match('@('.TESTFILM_PRIMARY_IMAGE.')@', $film->getImage(Constants::SOURCE_IMDB), $matches), 'Source image');
            // The film detail page does not have these fields.  Don't overwrite them.
        $this->assertEquals(2, $rating->getYourScore(), 'Your Score');
        $this->assertEquals("1/2/2000", $rating->getYourRatingDate()->format("n/j/Y"), 'Rating date');
        $this->assertEquals(3, $rating->getSuggestedScore(), 'Suggested score');

            // new data (Jinni, unchanged)
        $this->assertEquals("Original_UniqueName_Jinni", $film->getUniqueName(Constants::SOURCE_JINNI), 'Unique Name (Jinni)');
        $this->assertEquals(3, $film->getCriticScore(Constants::SOURCE_JINNI), 'Critic score (Jinni)');
        $this->assertEquals(4, $film->getUserScore(Constants::SOURCE_JINNI), 'User score (Jinni)');
        $rating = $film->getRating(Constants::SOURCE_JINNI);
        $this->assertEquals(1, $rating->getYourScore(), 'Your Score (Jinni)');
        $this->assertEquals("1/1/2000", $rating->getYourRatingDate()->format("n/j/Y"), 'Rating date (Jinni)');
        $this->assertEquals(2, $rating->getSuggestedScore(), 'Suggested score (Jinni)');
    }

    /**
     * @covers \RatingSync\OmdbApi::getFilmDetailFromWebsite
     */
    public function testGetFilmDetailFromWebsiteOverwriteFalseOverEmptyFilm()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        $site = new OmdbApiExt();
        $film = new Film();
        $site->getFilmDetailFromWebsite($film);
    }

    /**
     * - Set uniqueName in a new Film object
     * 
     * Expect
     *   - Genres populated
     * 
     * @covers \RatingSync\OmdbApi::getFilmDetailFromWebsite
     * @depends testGetFilmDetailFromWebsite
     */
    public function testMultipleGenres()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $site = new OmdbApiExt();
        $film = new Film();
        $film->setUniqueName(TESTFILM_PRIMARY_IMDBID, $site->_getSourceName());

        // Test
        $site->getFilmDetailFromWebsite($film);

        // Verify
        $this->assertEquals(TESTFILM_PRIMARY_GENRES, $film->getGenres(), 'Genres');
    }

    /**
     * - Set uniqueName in a new Film object
     * 
     * Expect
     *   - Directors populated
     * 
     * @covers \RatingSync\OmdbApi::getFilmDetailFromWebsite
     * @depends testGetFilmDetailFromWebsite
     */
    public function testMultipleDirectors()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $site = new OmdbApiExt();
        $film = new Film();
        $film->setUniqueName(TESTFILM_PRIMARY_IMDBID, $site->_getSourceName());

        // Test
        $site->getFilmDetailFromWebsite($film);

        // Verify
        $this->assertEquals(TESTFILM_PRIMARY_DIRECTORS, $film->getDirectors(), 'Director(s)');
    }

    /**
     * - Use a uniqueName of TV episode
     *
     * Expect
     *   - Film values populated... uniqueName, title, episodeTitle, year, image, contentType, genres, directors
     *   - Title is the series title 
     *   - Film values populated for OMDb... image, user score
     *   - Film values populated for IMDb... image, user score
     *
     * @covers \RatingSync\OmdbApi::getFilmDetailFromWebsite
     * @depends testGetFilmDetailFromWebsite
     */
    public function testGetFilmDetailFromWebsiteEpisode()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $site = new OmdbApiExt();
        $film = new Film();

        // Test
        $film->setUniqueName(TESTEPISODE_IMDBID, $site->_getSourceName());
        $site->getFilmDetailFromWebsite($film, true);

        // Verify
            // Film data
        $this->assertEquals(TESTEPISODE_TITLE, $film->getTitle(), 'Title');
        $this->assertEquals(TESTEPISODE_EPISODETITLE, $film->getEpisodeTitle(), 'Episode Title');
        $this->assertEquals(TESTEPISODE_YEAR, $film->getYear(), 'Year');
        $this->assertEquals(Film::CONTENT_TV_EPISODE, $film->getContentType(), 'Content Type');
        $this->assertEmpty($film->getImage(), 'Film image should be empty');
        $this->assertEquals(TESTEPISODE_DIRECTORS, $film->getDirectors(), 'Director(s)');
        $this->assertEquals(TESTEPISODE_GENRES, $film->getGenres(), 'Genres');

            // OMDbAPI source-specific
        $this->assertEquals(1, preg_match('@('.TESTEPISODE_IMAGE.')@', $film->getImage($site->_getSourceName()), $matches), 'Source image');
        $this->assertEquals(TESTEPISODE_IMDBID, $film->getUniqueName($site->_getSourceName()), 'Unique Name');
        $this->assertEquals(TESTEPISODE_USER_SCORE, $film->getUserScore($site->_getSourceName()), 'User score');

            // IMDb source-specific
        $this->assertEquals(1, preg_match('@('.TESTEPISODE_IMAGE.')@', $film->getImage(Constants::SOURCE_IMDB), $matches), 'Source image');
        $this->assertEquals(TESTEPISODE_IMDBID, $film->getUniqueName(Constants::SOURCE_IMDB), 'Unique Name');
        $this->assertEquals(TESTEPISODE_USER_SCORE, $film->getUserScore(Constants::SOURCE_IMDB), 'User score');
    }

    /**
     * - Use a uniqueName of TV series
     *
     * Expect
     *   - Film values populated... uniqueName, title, year, image, contentType, genres, directors
     *   - Film values populated for OMDb... image, critic score, user score
     *   - Film values populated for IMDb... image, critic score, user score
     *
     * @covers \RatingSync\OmdbApi::getFilmDetailFromWebsite
     * @depends testGetFilmDetailFromWebsite
     */
    public function testGetFilmDetailFromWebsiteSeries()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $site = new OmdbApiExt();
        $film = new Film();

        // Test
        $film->setUniqueName(TESTSERIES_IMDBID, $site->_getSourceName());
        $site->getFilmDetailFromWebsite($film, true);

        // Verify
            // Film data
        $this->assertEquals(TESTSERIES_TITLE, $film->getTitle(), 'Title');
        $this->assertEquals(TESTSERIES_YEAR, $film->getYear(), 'Year');
        $this->assertEquals(Film::CONTENT_TV_SERIES, $film->getContentType(), 'Content Type');
        $this->assertEmpty($film->getImage(), 'Film image should be empty');
        $this->assertEquals(TESTSERIES_DIRECTORS, $film->getDirectors(), 'Director(s)');
        $this->assertEquals(TESTSERIES_GENRES, $film->getGenres(), 'Genres');

            // OMDbAPI source-specific
        $this->assertEquals(1, preg_match('@('.TESTSERIES_IMAGE.')@', $film->getImage($site->_getSourceName()), $matches), 'Source image');
        $this->assertEquals(TESTSERIES_IMDBID, $film->getUniqueName($site->_getSourceName()), 'Unique Name');
        $this->assertEquals(TESTSERIES_CRITIC_SCORE, $film->getCriticScore($site->_getSourceName()), 'Critic score');
        $this->assertEquals(TESTSERIES_USER_SCORE, $film->getUserScore($site->_getSourceName()), 'User score');

            // IMDb source-specific
        $this->assertEquals(1, preg_match('@('.TESTSERIES_IMAGE.')@', $film->getImage(Constants::SOURCE_IMDB), $matches), 'Source image');
        $this->assertEquals(TESTSERIES_IMDBID, $film->getUniqueName(Constants::SOURCE_IMDB), 'Unique Name');
        $this->assertEquals(TESTSERIES_CRITIC_SCORE, $film->getCriticScore(Constants::SOURCE_IMDB), 'Critic score');
        $this->assertEquals(TESTSERIES_USER_SCORE, $film->getUserScore($site->_getSourceName()), 'User score');
    }
}

?>