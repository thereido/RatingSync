<?php
/**
 * Site PHPUnit
 */
namespace RatingSync;

require_once "../Site.php";
require_once "HttpTest.php";
require_once "JinniTest.php";

require_once "../Jinni.php";

const TEST_SITE_USERNAME = "testratingsync";

/**
 Suggested tests for a child class
   - testCannotBeConstructedFromNull
   - testCannotBeConstructedFromEmptyUsername
   - testObjectCanBeConstructed
   - testGetRatingsUsernameWithNoMatch
   - testGetRatings
   - testGetRatingsFromRandomAccount
   - testGetRatingsCount
   - testGetRatingsLimitPages
   - testGetRatingsBeginPage
   - testGetRatingsDetails
   - testGetSearchSuggestions
   - testGetFilmDetailFromWebsiteFromNull
   - testGetFilmDetailFromWebsiteFromString
   - testGetFilmDetailFromWebsiteWithoutUrlName
   - testGetFilmDetailFromWebsiteWithoutContentType
   - testGetFilmDetailFromWebsiteNoMatch
   - testGetFilmDetailFromWebsite
   - testGetFilmDetailFromWebsiteOverwriteTrueOverEmpty
   - testGetFilmDetailFromWebsiteOverwriteTrueOverOriginalData
   - testGetFilmDetailFromWebsiteOverwriteFalseOverOriginalData
   - testGetFilmDetailFromWebsiteOverwriteFalseOverEmpty
   - testGetFilmDetailFromWebsiteOverwriteDefault
   - testGetFilmDetailFromWebsiteOverwriteFalseOverEmptyFilm
   - testMultipleGenres
   - testMultipleDirectors
   - testExportRatingsXmlNoDetail
   - testExportRatingsXmlDetail
   - testGetRatingPageUrlWithArgsNull
   - testGetRatingPageUrlWithArgsEmpty
   - testGetRatingPageUrlWithPageIndexNull
   - testGetRatingPageUrlWithPageIndexEmpty
   - testGetRatingPageUrlWithPageIndexNotInt
   - testGetRatingPageUrl
   - testGetNextRatingPageNumberWithNull
   - testGetNextRatingPageNumberWithEmpty
   - testGetNextRatingPageNumberFirstPage
   - testGetNextRatingPageNumberLastPage
   - testParseDetailPageEmptyFilmOverwriteTrue
   - testParseDetailPageEmptyFilmOverwriteFalse
   - testParseDetailPageFullFilmOverwriteTrue
   - testParseDetailPageFullFilmOverwriteFalse
 */

class SiteChild extends \RatingSync\Site {
    public function __construct($username)
    {
        parent::__construct($username);
        $this->http = new HttpJinni(TEST_SITE_USERNAME);
        $this->sourceName = Constants::SOURCE_JINNI;
        $this->dateFormat = Jinni::JINNI_DATE_FORMAT;
        if (!$this->validateAfterConstructor()) {
            throw \Exception("Invalid SiteChild contructor");
        }
    }

    function _getHttp() { return $this->http; }
    function _getSourceName() { return $this->sourceName; }
    
    function _setHttp($http) { $this->http = $http; }
    function _setSourceName($sourceName) { $this->sourceName = $sourceName; }

    function _validateAfterConstructor() { return $this->validateAfterConstructor(); }
    function _parseDetailPageForTitle($page, $film, $overwrite) { return $this->parseDetailPageForTitle($page, $film, $overwrite); }
    function _parseDetailPageForFilmYear($page, $film, $overwrite) { return $this->parseDetailPageForFilmYear($page, $film, $overwrite); }
    function _parseDetailPageForImage($page, $film, $overwrite) { return $this->parseDetailPageForImage($page, $film, $overwrite); }
    function _parseDetailPageForContentType($page, $film, $overwrite) { return $this->parseDetailPageForContentType($page, $film, $overwrite); }
    function _parseDetailPageForFilmId($page, $film, $overwrite) { return $this->parseDetailPageForFilmId($page, $film, $overwrite); }
    function _parseDetailPageForUrlName($page, $film, $overwrite) { return $this->parseDetailPageForUrlName($page, $film, $overwrite); }
    function _parseDetailPageForRating($page, $film, $overwrite) { return $this->parseDetailPageForRating($page, $film, $overwrite); }
    function _parseDetailPageForGenres($page, $film, $overwrite) { return $this->parseDetailPageForGenres($page, $film, $overwrite); }
    function _parseDetailPageForDirectors($page, $film, $overwrite) { return $this->parseDetailPageForDirectors($page, $film, $overwrite); }

    // Abstract Function based on \RatingSync\Jinni::getRatingPageUrl
    protected function getRatingPageUrl($args) {
        $pageIndex = $args['pageIndex'];
        return '/user/'.urlencode($this->username).'/ratings?pagingSlider_index='.$pageIndex;
    }

    // Abstract Function based on \RatingSync\Jinni::getFilmsFromRatingsPage
    protected function getFilmsFromRatingsPage($page, $details = false) {
        $film = new Film($this->http);
        $film2 = new Film($this->http);

        $rating = new Rating($this->sourceName);
        $rating->setYourScore(8);
        $rating->setYourRatingDate(new \DateTime('2015-01-02'));
        $film->setRating($rating, $this->sourceName);
        $film->setTitle("Site Title1");
        $film->setFilmId("Site_FilmId1", $this->sourceName);
        $film->setImage("Site_Image1");
        $film->setImage("Site_Image1", $this->sourceName);
        $film->setContentType(\RatingSync\Film::CONTENT_FILM);

        $rating2 = new Rating($this->sourceName);
        $rating2->setYourScore(7);
        $rating2->setYourRatingDate(new \DateTime('2015-01-03'));
        $film2->setRating($rating2, $this->sourceName);
        $film2->setTitle("Site Title2");
        $film2->setFilmId("Site_FilmId2", $this->sourceName);
        $film2->setImage("Site_Image2");
        $film2->setImage("Site_Image2", $this->sourceName);
        $film2->setContentType(\RatingSync\Film::CONTENT_FILM);

        if ($details) {
            $film->setYear(1900);
            $film->setUrlName("Site_UrlName1", $this->sourceName);
            $film->addGenre("Site_Genre1.1");
            $film->addGenre("Site_Genre1.2");
            $film->addDirector("Site_Director1.1");
            $film->addDirector("Site_Director1.2");
            $rating->setSuggestedScore(2);
            $rating->setCriticScore(3);
            $rating->setUserScore(4);
            $film->setRating($rating, $this->sourceName);
            
            $film2->setYear(1902);
            $film2->setUrlName("Site_UrlName2", $this->sourceName);
            $film2->addGenre("Site_Genre2.1");
            $film2->addDirector("Site_Director2.1");
            $rating2->setSuggestedScore(3);
            $rating2->setCriticScore(4);
            $rating2->setUserScore(5);
            $film2->setRating($rating2, $this->sourceName);
        }

        $films = array($film, $film2);
        return $films;
    }

    // Abstract Function based on \RatingSync\Jinni::getNextRatingPageNumber
    protected function getNextRatingPageNumber($page) {}

    // Abstract Function based on \RatingSync\Jinni::getFilmDetailPageUrl
    protected function getFilmDetailPageUrl($film)
    {
        if (! $film instanceof Film ) {
            throw new \InvalidArgumentException('Function getFilmDetailPageUrl must be given a Film object');
        } elseif ( is_null($film->getContentType()) || is_null($film->getUrlName($this->sourceName)) ) {
            throw new \InvalidArgumentException('Function getFilmDetailPageUrl must have Content Type and URL Name');
        }

        switch ($film->getContentType()) {
        case Film::CONTENT_FILM:
            $type = 'movies';
            break;
        case Film::CONTENT_TV:
            $type = 'tv';
            break;
        case Film::CONTENT_SHORTFILM:
            $type = 'shorts';
            break;
        default:
            $type = null;
        }

        $urlName = $film->getUrlName(Constants::SOURCE_JINNI);
        return '/'.$type.'/'.$urlName;
    }

    // Abstract Function based on \RatingSync\Jinni::getDetailPageRegexForTitle
    protected function getDetailPageRegexForTitle() { return '@<h1 class=\"title1\">(.*), \d\d\d\d<\/h1>@'; }

    // Abstract Function based on \RatingSync\Jinni::getDetailPageRegexForYear
    protected function getDetailPageRegexForYear() { return '@<h1 class=\"title1\">.*, (\d\d\d\d)<\/h1>@'; }

    // Abstract Function based on \RatingSync\Jinni::getDetailPageRegexForImage
    protected function getDetailPageRegexForImage() { return '@<img src="(http://media[\d]*.jinni.com/(?:tv|movie|shorts|no-image)/[^/]+/[^"]+)@'; }

    // Abstract Function based on \RatingSync\Jinni::getDetailPageRegexForContentType
    protected function getDetailPageRegexForContentType() { return null; }

    // Abstract Function based on \RatingSync\Jinni::getDetailPageRegexForFilmId
    protected function getDetailPageRegexForFilmId($film) {
        if (is_null($film) || !($film instanceof Film) || empty($film->getUrlName($this->sourceName))) {
            throw new \InvalidArgumentException('Film param must have a URL Name');
        }

        return '/{[^}]+uniqueName: \"' . $film->getUrlName($this->sourceName) . '\"[^}]+uniqueId: \"(.+)\"/';
    }

    // Abstract Function based on \RatingSync\Jinni::getDetailPageRegexForUrlName
    protected function getDetailPageRegexForUrlName() { return '/<a href=\".*\/(.*)\/\" class.*Overview<\/a>/'; }

    // Abstract Function based on \RatingSync\Jinni::getDetailPageRegexForYourScore
    protected function getDetailPageRegexForYourScore($film) {
        if (is_null($film) || !($film instanceof Film) || empty($film->getUrlName($this->sourceName))) {
            throw new \InvalidArgumentException('Film param must have a URL Name');
        }

        return '/uniqueName: \"' . $film->getUrlName($this->sourceName) . '\"[^}]+isRatedRating: true[^}]+RatedORSuggestedValue: (\d[\d]?\.?\d?)/';
    }

    // Abstract Function based on \RatingSync\Jinni::getDetailPageRegexForRatingDate
    protected function getDetailPageRegexForRatingDate() { return ''; }

    // Abstract Function based on \RatingSync\Jinni::getDetailPageRegexForSuggestedScore
    protected function getDetailPageRegexForSuggestedScore($film) {
        if (is_null($film) || !($film instanceof Film) || empty($film->getUrlName($this->sourceName))) {
            throw new \InvalidArgumentException('Film param must have a URL Name');
        }

        return '/uniqueName: \"' . $film->getUrlName($this->sourceName) . '\"[^}]+isSugggestedRating: true[^}]+RatedORSuggestedValue: (\d[\d]?\.?\d?)/';
    }

    // Abstract Function based on \RatingSync\Jinni::getDetailPageRegexForCriticScore
    protected function getDetailPageRegexForCriticScore() { return ''; }

    // Abstract Function based on \RatingSync\Jinni::getDetailPageRegexForUserScore
    protected function getDetailPageRegexForUserScore() { return ''; }

    // Abstract Function based on \RatingSync\Jinni::parseDetailPageForGenres
    protected function parseDetailPageForGenres($page, $film, $overwrite)
    {
        if (!$overwrite && !empty($film->getGenres())) {
            return false;
        }
        $originalGenres = $film->getGenres();
        $didFindGenres = false;
        
        $film->removeAllGenres();
        $groupSections = explode('<div class="right_genomeGroup">', $page);
        array_shift($groupSections);
        foreach ($groupSections as $groupSection) {
            if (!stripos($groupSection, "Genres")) {
                continue;
            }
            $geneSections = explode('right_genomeLink', $groupSection);
            array_shift($geneSections);
            foreach ($geneSections as $geneSection) {
                // Letters, Spaces, Hyphens, Numbers
                if (0 < preg_match('@([a-zA-Z \-\d]+)[,]?<\/a>@', $geneSection, $matches)) {
                    $film->addGenre($matches[1]);
                    $didFindGenres = true;
                }
            }
        }

        if (!$didFindGenres) {
            if (!empty($originalGenres)) {
                foreach ($originalGenres as $genre) {
                    $film->addGenre($genre);
                }
            }
            return false;
        }
        return true;
    }

    // Abstract Function based on \RatingSync\Jinni::parseDetailPageForDirectors
    protected function parseDetailPageForDirectors($page, $film, $overwrite)
    {
        if (!$overwrite && !empty($film->getDirectors())) {
            return false;
        }
        $originalDirectors = $film->getDirectors();
        $didFindDirectors = false;
        
        if ($overwrite || empty($film->getDirectors())) {
            $film->removeAllDirectors();
            if (0 < preg_match('@<b>Directed by:<\/b>(.+)@', $page, $directorLines)) {
                preg_match_all("@<[^>]+>(.*)</[^>]+>@U", $directorLines[1], $directorMatches);
                $directors = $directorMatches[1];
                foreach ($directors as $director) {
                    $film->addDirector($director);
                    $didFindDirectors = true;
                }
            }
        }

        if (!$didFindDirectors) {
            if (!empty($originalDirectors)) {
                foreach ($originalDirectors as $director) {
                    $film->addDirector($director);
                }
            }
            return false;
        }
        return true;
    }
}

class SiteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers            \RatingSync\Site::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testCannotBeConstructedFromNull()
    {
        new SiteChild(null);
    }

    /**
     * @covers            \RatingSync\Site::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testCannotBeConstructedFromEmptyUsername()
    {
        new SiteChild("");
    }

    /**
     * @covers \RatingSync\Site::__construct
     */
    public function testObjectCanBeConstructed()
    {
        $site = new SiteChild(TEST_SITE_USERNAME);
        return $site;
    }

    /**
     * @covers \RatingSync\Site::validateAfterConstructor
     * @depends testObjectCanBeConstructed
     */
    public function testValidateAfterConstructorNoHttp()
    {
        $site = new SiteChild(TEST_SITE_USERNAME);
        $site->_setSourceName(Constants::SOURCE_JINNI);
        $site->_setHttp(null);
        $this->assertFalse($site->_validateAfterConstructor());
    }

    /**
     * @covers \RatingSync\Site::validateAfterConstructor
     * @depends testObjectCanBeConstructed
     */
    public function testValidateAfterConstructorNoSourceName()
    {
        $site = new SiteChild(TEST_SITE_USERNAME);
        $site->_setSourceName(null);
        $site->_setHttp(new HttpJinni(TEST_SITE_USERNAME));
        $this->assertFalse($site->_validateAfterConstructor());
    }

    /**
     * @covers \RatingSync\Site::validateAfterConstructor
     * @depends testObjectCanBeConstructed
     */
    public function testValidateAfterConstructorGood()
    {
        $site = new SiteChild(TEST_SITE_USERNAME);
        $this->assertTrue($site->_validateAfterConstructor());
    }
    
    /**
     * @covers \RatingSync\Site::getRatings
     * @depends testValidateAfterConstructorGood
     */
    public function testGetRatingsWithoutExceptions()
    {
        $site = new SiteChild(TEST_SITE_USERNAME);

        $films = $site->getRatings();
    }
    
    /**
     * @covers \RatingSync\Site::getRatings
     * @depends testGetRatingsWithoutExceptions
     */
    public function testGetRatingsCount()
    {
        $site = new SiteChild(TEST_SITE_USERNAME);

        $films = $site->getRatings();
        $this->assertCount(2, $films);
    }
    
    /**
     * @covers \RatingSync\Site::getRatings
     * @depends testValidateAfterConstructorGood
     */
    public function testGetRatingsDetailsWithoutExceptions()
    {
        $site = new SiteChild(TEST_SITE_USERNAME);

        $films = $site->getRatings(null, 1, true);
        
    }
    
    /**
     * @covers \RatingSync\Site::getRatings
     * @depends testValidateAfterConstructorGood
     */
    public function testGetRatingsLimitPagesBeginPageWithoutExceptions()
    {
        $site = new SiteChild(TEST_SITE_USERNAME);

        $films = $site->getRatings(1, 2, false);
        
    }
    
    /**
     * @covers \RatingSync\Site::getSearchSuggestions
     * @depends testValidateAfterConstructorGood
     */
    public function testGetSearchSuggestionsWithoutExceptions()
    {
        $site = new SiteChild(TEST_SITE_USERNAME);
        $site->_setSourceName(Constants::SOURCE_JINNI);
        $site->_setHttp(new HttpJinni(TEST_SITE_USERNAME));

        $films = $site->getSearchSuggestions("Shawshank");
    }
    
    /**
     * @covers \RatingSync\Site::exportRatings
     * @depends testValidateAfterConstructorGood
     */
    public function testExportRatingsXmlNoDetail()
    {
        $site = new SiteChild(TEST_SITE_USERNAME);
        
        $testFilename = "ratings_test.xml";
        $success = $site->exportRatings(Constants::EXPORT_FORMAT_XML, $testFilename, false);
        $this->assertTrue($success);

        $fullTestFilename = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . Constants::outputFilePath() . $testFilename;
        $fullVerifyFilename = "testfile/verify_ratings_nodetail_site.xml";
        $this->assertTrue(is_readable($fullTestFilename), 'Need to read downloaded file ' . $fullTestFilename);
        $this->assertTrue(is_readable($fullVerifyFilename), 'Need to read verify file ' . $fullVerifyFilename);

        $fp_test = fopen($fullTestFilename, "r");
        $fp_verify = fopen($fullVerifyFilename, "r");
        $testFileSize = filesize($fullTestFilename);
        $verifyFileSize = filesize($fullVerifyFilename);
        $this->assertEquals($testFileSize, $verifyFileSize, 'File sizes - test vs verify');
        $test = fread($fp_test, filesize($fullTestFilename));
        $verify = fread($fp_verify, filesize($fullVerifyFilename));
        $this->assertEquals($test, $verify, 'Match exported file vs verify file');
        fclose($fp_test);
        fclose($fp_verify);
    }
    
    /**
     * @covers \RatingSync\Site::exportRatings
     * @depends testValidateAfterConstructorGood
     */
    public function testExportRatingsXmlDetail()
    {
        $site = new SiteChild(TEST_SITE_USERNAME);
        
        $testFilename = "ratings_test.xml";
        $success = $site->exportRatings("XML", $testFilename, true);
        $this->assertTrue($success);

        $fullTestFilename = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . Constants::outputFilePath() . $testFilename;
        $fullVerifyFilename = "testfile/verify_ratings_detail_site.xml";
        $this->assertTrue(is_readable($fullTestFilename), 'Need to read downloaded file ' . $fullTestFilename);
        $this->assertTrue(is_readable($fullVerifyFilename), 'Need to read verify file ' . $fullVerifyFilename);

        $fp_test = fopen($fullTestFilename, "r");
        $fp_verify = fopen($fullVerifyFilename, "r");
        $testFileSize = filesize($fullTestFilename);
        $verifyFileSize = filesize($fullVerifyFilename);
        $this->assertEquals($testFileSize, $verifyFileSize, 'File sizes - test vs verify');
        $test = fread($fp_test, 22);
        $verify = fread($fp_verify, 22);
        $this->assertEquals($test, $verify, 'Match exported file vs verify file');
        fclose($fp_test);
        fclose($fp_verify);
    }
    
    /**
     * @covers \RatingSync\Site::getFilmDetailFromWebsite
     * @depends testValidateAfterConstructorGood
     */
    public function testGetFilmDetailFromWebsiteEmptyFilmOverwriteTrue()
    {
        $site = new SiteChild(TEST_SITE_USERNAME);

        $film = new Film($site->_getHttp());
        $film->setContentType("FeatureFilm");
        $film->setUrlName("frozen-2013", Constants::SOURCE_JINNI);
        $site->getFilmDetailFromWebsite($film, true);

        $this->assertEquals("Frozen", $film->getTitle(), 'Title');
        $this->assertEquals(2013, $film->getYear(), 'Year');
        $this->assertEquals("FeatureFilm", $film->getContentType(), 'Content Type');
        $this->assertEquals(1, preg_match('@(http://media[\d]*.jinni.com/(?:tv|movie|shorts|no-image)/frozen-2013/[^"]+)@', $film->getImage(), $matches), 'Image link');
        $this->assertEquals("frozen-2013", $film->getUrlName(Constants::SOURCE_JINNI), 'URL Name');
        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), 'Director(s)');
        $this->assertEquals(array("Adventure", "Animation", "Fantasy", "Musical", "Family", "Comedy"), $film->getGenres(), 'Genres');
        $rating = $film->getRating(Constants::SOURCE_JINNI);
        $this->assertEquals("999", $film->getFilmId(Constants::SOURCE_JINNI), 'Film ID');
        $this->assertEquals(8, $rating->getYourScore(), 'Your Score');
        $this->assertNull($rating->getYourRatingDate(), 'Rating date not available from film detail page');
        $this->assertNull($rating->getSuggestedScore(), 'Suggested score not available is you are rated the film');
        $this->assertNull($rating->getCriticScore(), 'Critic score not available from Jinni');
        $this->assertNull($rating->getUserScore(), 'User score not available from Jinni');
    }
    
    /**
     * @covers \RatingSync\Site::getFilmDetailFromWebsite
     * @depends testGetFilmDetailFromWebsiteEmptyFilmOverwriteTrue
     */
    public function testGetFilmDetailFromWebsiteFullFilmOverwriteTrue()
    {
        $site = new SiteChild(TEST_SITE_USERNAME);

        $film = new Film($site->_getHttp());

        // Setup original data
        $film->setTitle("Original_Title");
        $film->setYear(1900);
        $film->setImage("Original_Image");
        $film->setImage("Original_JinniImage", Constants::SOURCE_JINNI);
        $film->setImage("Original_IMDbImage", Constants::SOURCE_IMDB);
        $film->setFilmId("Original_JinniFilmId", Constants::SOURCE_JINNI);
        $film->setFilmId("Original_IMDbFilmId", Constants::SOURCE_IMDB);
        $film->setUrlName("Original_JinniUrlName", Constants::SOURCE_JINNI);
        $film->setUrlName("Original_IMDbUrlName", Constants::SOURCE_IMDB);
        $film->addGenre("Original_Genre1");
        $film->addGenre("Original_Genre2");
        $film->addDirector("Original_Director1");
        $film->addDirector("Original_Director2");
        $ratingJinniOrig = new Rating(Constants::SOURCE_JINNI);
        $ratingJinniOrig->setYourScore(1);
        $ratingJinniOrig->setYourRatingDate(new \DateTime('2000-01-01'));
        $ratingJinniOrig->setSuggestedScore(2);
        $ratingJinniOrig->setCriticScore(3);
        $ratingJinniOrig->setUserScore(4);
        $film->setRating($ratingJinniOrig, Constants::SOURCE_JINNI);
        $ratingImdbOrig = new Rating(Constants::SOURCE_IMDB);
        $ratingImdbOrig->setYourScore(2);
        $ratingImdbOrig->setYourRatingDate(new \DateTime('2000-01-02'));
        $ratingImdbOrig->setSuggestedScore(3);
        $ratingImdbOrig->setCriticScore(4);
        $ratingImdbOrig->setUserScore(5);
        $film->setRating($ratingImdbOrig, Constants::SOURCE_IMDB);

        // Get detail overwriting
        $film->setContentType("FeatureFilm");
        $film->setUrlName("frozen-2013", Constants::SOURCE_JINNI);
        $site->getFilmDetailFromWebsite($film, true);

        // Test the new data
        $this->assertEquals("Frozen", $film->getTitle(), 'Title');
        $this->assertEquals(2013, $film->getYear(), 'Year');
        $this->assertEquals("FeatureFilm", $film->getContentType(), 'Content Type');
        $this->assertEquals(1, preg_match('@(http://media[\d]*.jinni.com/(?:tv|movie|shorts|no-image)/frozen-2013/[^"]+)@', $film->getImage(), $matches), 'Image link');
        $this->assertEquals("frozen-2013", $film->getUrlName(Constants::SOURCE_JINNI), 'URL Name');
        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), 'Director(s)');
        $this->assertEquals(array("Adventure", "Animation", "Fantasy", "Musical", "Family", "Comedy"), $film->getGenres(), 'Genres');
        $rating = $film->getRating(Constants::SOURCE_JINNI);
        $this->assertEquals("999", $film->getFilmId(Constants::SOURCE_JINNI), 'Film ID');
        $this->assertEquals(8, $rating->getYourScore(), 'Your Score');

        // The film detail page does not have these fields.  Don't overwrite them.
        $this->assertEquals("1/1/2000", $rating->getYourRatingDate()->format("n/j/Y"), 'Rating date');
        $this->assertEquals(2, $rating->getSuggestedScore(), 'Suggested score not available is you are rated the film');
        $this->assertEquals(3, $rating->getCriticScore(), 'Critic score not available from Jinni');
        $this->assertEquals(4, $rating->getUserScore(), 'User score not available from Jinni');

        // IMDb Rating is unchanged
        $rating = $film->getRating(Constants::SOURCE_IMDB);
        $this->assertEquals("Original_IMDbFilmId", $film->getFilmId(Constants::SOURCE_IMDB), 'Film ID');
        $this->assertEquals(2, $rating->getYourScore(), 'Your Score');
        $this->assertEquals("1/2/2000", $rating->getYourRatingDate()->format("n/j/Y"), 'Rating date');
        $this->assertEquals(3, $rating->getSuggestedScore(), 'Suggested score not available is you are rated the film');
        $this->assertEquals(4, $rating->getCriticScore(), 'Critic score not available from Jinni');
        $this->assertEquals(5, $rating->getUserScore(), 'User score not available from Jinni');
    }

    /**
     * @covers \RatingSync\Jinni::getFilmDetailFromWebsite
     * @depends testGetFilmDetailFromWebsiteEmptyFilmOverwriteTrue
     */
    public function testGetFilmDetailFromWebsiteEmptyFilmOverwriteFalse()
    {
        $site = new SiteChild(TEST_SITE_USERNAME);

        $film = new Film($site->_getHttp());
        $film->setContentType("FeatureFilm");
        $film->setUrlName("frozen-2013", Constants::SOURCE_JINNI);
        $site->getFilmDetailFromWebsite($film, false);

        // Same results as testGetFilmDetailFromWebsite or testGetFilmDetailFromWebsiteOverwriteTrueOverEmpty
        $this->assertEquals("Frozen", $film->getTitle(), 'Title');
        $this->assertEquals(2013, $film->getYear(), 'Year');
        $this->assertEquals("FeatureFilm", $film->getContentType(), 'Content Type');
        $this->assertEquals(1, preg_match('@(http://media[\d]*.jinni.com/(?:tv|movie|shorts|no-image)/frozen-2013/[^"]+)@', $film->getImage(), $matches), 'Image link');
        $this->assertEquals("frozen-2013", $film->getUrlName(Constants::SOURCE_JINNI), 'URL Name');
        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), 'Director(s)');
        $this->assertEquals(array("Adventure", "Animation", "Fantasy", "Musical", "Family", "Comedy"), $film->getGenres(), 'Genres');
        $rating = $film->getRating(Constants::SOURCE_JINNI);
        $this->assertEquals("999", $film->getFilmId(Constants::SOURCE_JINNI), 'Film ID');
        $this->assertEquals(8, $rating->getYourScore(), 'Your Score');
        $this->assertNull($rating->getYourRatingDate(), 'Rating date not available from film detail page');
        $this->assertNull($rating->getSuggestedScore(), 'Suggested score not available is you are rated the film');
        $this->assertNull($rating->getCriticScore(), 'Critic score not available from Jinni');
        $this->assertNull($rating->getUserScore(), 'User score not available from Jinni');
    }

    /**
     * @covers \RatingSync\Jinni::getFilmDetailFromWebsite
     * @depends testGetFilmDetailFromWebsiteEmptyFilmOverwriteTrue
     */
    public function testGetFilmDetailFromWebsiteFullFilmOverwriteFalse()
    {
        $site = new SiteChild(TEST_SITE_USERNAME);

        $film = new Film($site->_getHttp());

        // Setup original data
        $film->setTitle("Original_Title");
        $film->setYear(1900);
        $film->setImage("Original_Image");
        $film->setUrlName("Original_IMDbUrlName", Constants::SOURCE_IMDB);
        $film->addGenre("Original_Genre1");
        $film->addGenre("Original_Genre2");
        $film->addDirector("Original_Director1");
        $film->addDirector("Original_Director2");
        $film->setImage("Original_Image", Constants::SOURCE_JINNI);
        $film->setFilmId("Original_JinniFilmId", Constants::SOURCE_JINNI);
        $ratingJinniOrig = new Rating(Constants::SOURCE_JINNI);
        $ratingJinniOrig->setYourScore(1);
        $ratingJinniOrig->setYourRatingDate(new \DateTime('2000-01-01'));
        $ratingJinniOrig->setSuggestedScore(2);
        $ratingJinniOrig->setCriticScore(3);
        $ratingJinniOrig->setUserScore(4);
        $film->setRating($ratingJinniOrig, Constants::SOURCE_JINNI);
        $film->setImage("Original_Image", Constants::SOURCE_IMDB);
        $film->setFilmId("Original_ImdbFilmId", Constants::SOURCE_IMDB);
        $ratingImdbOrig = new Rating(Constants::SOURCE_IMDB);
        $ratingImdbOrig->setYourScore(2);
        $ratingImdbOrig->setYourRatingDate(new \DateTime('2000-01-02'));
        $ratingImdbOrig->setSuggestedScore(3);
        $ratingImdbOrig->setCriticScore(4);
        $ratingImdbOrig->setUserScore(5);
        $film->setRating($ratingImdbOrig, Constants::SOURCE_IMDB);

        // Get detail not overwriting
        $film->setContentType("FeatureFilm");
        $film->setUrlName("frozen-2013", Constants::SOURCE_JINNI);
        $site->getFilmDetailFromWebsite($film, false);

        // Same original data
        $this->assertEquals("FeatureFilm", $film->getContentType(), 'Content Type');
        $this->assertEquals("frozen-2013", $film->getUrlName(Constants::SOURCE_JINNI), 'Jinni URL Name');
        $this->assertEquals("Original_Title", $film->getTitle(), 'Title');
        $this->assertEquals(1900, $film->getYear(), 'Year');
        $this->assertEquals("Original_Image", $film->getImage(), 'Image link');
        $this->assertEquals("Original_IMDbUrlName", $film->getUrlName(Constants::SOURCE_IMDB), 'Jinni URL Name');
        $this->assertEquals(array("Original_Director1", "Original_Director2"), $film->getDirectors(), 'Director(s)');
        $this->assertEquals(array("Original_Genre1", "Original_Genre2"), $film->getGenres(), 'Genres');
        $rating = $film->getRating(Constants::SOURCE_JINNI);
        $this->assertEquals("Original_JinniFilmId", $film->getFilmId(Constants::SOURCE_JINNI), 'Film ID');
        $this->assertEquals(1, $rating->getYourScore(), 'Your Score');
        $this->assertEquals("1/1/2000", $rating->getYourRatingDate()->format("n/j/Y"), 'Your Rating Date');
        $this->assertEquals(2, $rating->getSuggestedScore(), 'Suggested score');
        $this->assertEquals(3, $rating->getCriticScore(), 'Critic score');
        $this->assertEquals(4, $rating->getUserScore(), 'User score');
        $rating = $film->getRating(Constants::SOURCE_IMDB);
        $this->assertEquals("Original_ImdbFilmId", $film->getFilmId(Constants::SOURCE_IMDB), 'Film ID');
        $this->assertEquals(2, $rating->getYourScore(), 'Your Score');
        $this->assertEquals("1/2/2000", $rating->getYourRatingDate()->format("n/j/Y"), 'Your Rating Date');
        $this->assertEquals(3, $rating->getSuggestedScore(), 'Suggested score');
        $this->assertEquals(4, $rating->getCriticScore(), 'Critic score');
        $this->assertEquals(5, $rating->getUserScore(), 'User score');
    }
    
    public function testCacheFilmDetail()
    {
        // Cache the file for later tests in this unit test class
        $http = new HttpJinni(TEST_SITE_USERNAME);
        $this->cachedDetailPage = $http->getPage("/movies/frozen-2013/");
        $this->assertStringEndsWith("</html>", $this->cachedDetailPage);

        $filename = JinniTest::getCachePath() . "jinni_frozen-2013.html";
        $fp = fopen($filename, "w");
        fwrite($fp, $this->cachedDetailPage);
        fclose($fp);
    }
    
    /**
     * @covers \RatingSync\Site::parseDetailPageForTitle
     * @covers \RatingSync\Site::parseDetailPageForFilmYear
     * @covers \RatingSync\Site::parseDetailPageForImage
     * @covers \RatingSync\Site::parseDetailPageForContentType
     * @covers \RatingSync\Site::parseDetailPageForFilmId
     * @covers \RatingSync\Site::parseDetailPageForUrlName
     * @covers \RatingSync\Site::parseDetailPageForRating
     * @covers \RatingSync\Site::parseDetailPageForGenres
     * @covers \RatingSync\Site::parseDetailPageForDirectors
     * @depends testCacheFilmDetail
     */
    public function testParseDetailPageEmptyFilmOverwriteTrue()
    {
        $site = new SiteChild(TEST_JINNI_USERNAME);
        $film = new Film($site->_getHttp());

        $filename = JinniTest::getCachePath() . "jinni_frozen-2013.html";
        $fp = fopen($filename, "r");
        $page = fread($fp, filesize($filename));
        fclose($fp);
        
        $success = $site->_parseDetailPageForTitle($page, $film, true);
        $this->assertTrue($success, 'Parsing film object for Title');
        $this->assertEquals("Frozen", $film->getTitle(), 'Check matching Title (empty film overwrite=true)');

        $success = $site->_parseDetailPageForFilmYear($page, $film, true);
        $this->assertTrue($success, 'Parsing film object for Year');
        $this->assertEquals(2013, $film->getYear(), 'Check matching Year (empty film overwrite=true)');

        $success = $site->_parseDetailPageForImage($page, $film, true);
        $this->assertTrue($success, 'Parsing film object for Image');
        $this->assertStringEndsWith("frozen-2013-1.jpeg", $film->getImage(), 'Check matching Image (empty film overwrite=true)');
        
        $success = $site->_parseDetailPageForContentType($page, $film, true);
        $this->assertFalse($success, 'Parsing film object for Content Type'); // ContentType not available in the detail page
        $this->assertNull($film->getContentType(), 'Check matching Content Type (empty film overwrite=true)');
        
        $success = $site->_parseDetailPageForUrlName($page, $film, true);
        $this->assertTrue($success, 'Parsing film object for URL Name');
        $this->assertEquals("frozen-2013", $film->getUrlName($site->_getSourceName()), 'Check matching URL Name (empty film overwrite=true)');
        
        $success = $site->_parseDetailPageForFilmId($page, $film, true);
        $this->assertTrue($success, 'Parsing film object for Film Id');
        $this->assertEquals("999", $film->getFilmId($site->_getSourceName()), 'Check matching Film Id (empty film overwrite=true)');
        
        $success = $site->_parseDetailPageForRating($page, $film, true);
        $rating = $film->getRating($site->_getSourceName());
        $this->assertEquals(8, $rating->getYourScore(), 'Check matching YourScore (empty film overwrite=true)');
        $this->assertNull($rating->getYourRatingDate(), 'Check matching Rating Date (empty film overwrite=true)');
        $this->assertNull($rating->getSuggestedScore(), 'Check matching Suggested Score (empty film overwrite=true)');
        $this->assertNull($rating->getCriticScore(), 'Check matching Critic Score (empty film overwrite=true)');
        $this->assertNull($rating->getUserScore(), 'Check matching User Score (empty film overwrite=true)');
        
        $success = $site->_parseDetailPageForGenres($page, $film, true);
        $this->assertTrue($success, 'Parsing film object for Genres');
        $this->assertEquals(array("Adventure", "Animation", "Fantasy", "Musical", "Family", "Comedy"), $film->getGenres(), 'Check matching Gneres (empty film overwrite=true)');
        
        $success = $site->_parseDetailPageForDirectors($page, $film, true);
        $this->assertTrue($success, 'Parsing film object for Directors');
        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), 'Check matching Directors (empty film overwrite=true)');
    }
    
    /**
     * @covers \RatingSync\Site::parseDetailPageForTitle
     * @covers \RatingSync\Site::parseDetailPageForFilmYear
     * @covers \RatingSync\Site::parseDetailPageForImage
     * @covers \RatingSync\Site::parseDetailPageForContentType
     * @covers \RatingSync\Site::parseDetailPageForFilmId
     * @covers \RatingSync\Site::parseDetailPageForUrlName
     * @covers \RatingSync\Site::parseDetailPageForRating
     * @covers \RatingSync\Site::parseDetailPageForGenres
     * @covers \RatingSync\Site::parseDetailPageForDirectors
     * @depends testCacheFilmDetail
     */
    public function testParseDetailPageEmptyFilmOverwriteFalse()
    {
        $site = new SiteChild(TEST_JINNI_USERNAME);
        $film = new Film($site->_getHttp());

        $filename = JinniTest::getCachePath() . "jinni_frozen-2013.html";
        $fp = fopen($filename, "r");
        $page = fread($fp, filesize($filename));
        fclose($fp);
        
        $success = $site->_parseDetailPageForTitle($page, $film, false);
        $this->assertTrue($success, 'Parsing film object for Title');
        $this->assertEquals("Frozen", $film->getTitle(), 'Check matching Title (empty film overwrite=false)');

        $success = $site->_parseDetailPageForFilmYear($page, $film, false);
        $this->assertTrue($success, 'Parsing film object for Year');
        $this->assertEquals(2013, $film->getYear(), 'Check matching Year (empty film overwrite=false)');

        $success = $site->_parseDetailPageForImage($page, $film, false);
        $this->assertTrue($success, 'Parsing film object for Image');
        $this->assertStringEndsWith("frozen-2013-1.jpeg", $film->getImage(), 'Check matching Image (empty film overwrite=false)');
        
        $success = $site->_parseDetailPageForContentType($page, $film, false);
        $this->assertFalse($success, 'Parsing film object for Content Type'); // ContentType not available in the detail page
        $this->assertNull($film->getContentType(), 'Check matching Content Type (empty film overwrite=false)');
        
        $success = $site->_parseDetailPageForUrlName($page, $film, false);
        $this->assertTrue($success, 'Parsing film object for URL Name');
        $this->assertEquals("frozen-2013", $film->getUrlName($site->_getSourceName()), 'Check matching URL Name (empty film overwrite=false)');
        
        $success = $site->_parseDetailPageForFilmId($page, $film, false);
        $this->assertTrue($success, 'Parsing film object for Film Id');
        $this->assertEquals("999", $film->getFilmId($site->_getSourceName()), 'Check matching Film Id (empty film overwrite=false)');
        
        $success = $site->_parseDetailPageForRating($page, $film, false);
        $rating = $film->getRating($site->_getSourceName());
        $this->assertEquals(8, $rating->getYourScore(), 'Check matching YourScore (empty film overwrite=false)');
        $this->assertNull($rating->getYourRatingDate(), 'Check matching Rating Date (empty film overwrite=false)');
        $this->assertNull($rating->getSuggestedScore(), 'Check matching Suggested Score (empty film overwrite=false)');
        $this->assertNull($rating->getCriticScore(), 'Check matching Critic Score (empty film overwrite=false)');
        $this->assertNull($rating->getUserScore(), 'Check matching User Score (empty film overwrite=false)');
        
        $success = $site->_parseDetailPageForGenres($page, $film, false);
        $this->assertTrue($success, 'Parsing film object for Genres');
        $this->assertEquals(array("Adventure", "Animation", "Fantasy", "Musical", "Family", "Comedy"), $film->getGenres(), 'Check matching Gneres (empty film overwrite=false)');
        
        $success = $site->_parseDetailPageForDirectors($page, $film, false);
        $this->assertTrue($success, 'Parsing film object for Directors');
        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), 'Check matching Directors (empty film overwrite=false)');
    }
    
    /**
     * @covers \RatingSync\Site::parseDetailPageForTitle
     * @covers \RatingSync\Site::parseDetailPageForFilmYear
     * @covers \RatingSync\Site::parseDetailPageForImage
     * @covers \RatingSync\Site::parseDetailPageForContentType
     * @covers \RatingSync\Site::parseDetailPageForFilmId
     * @covers \RatingSync\Site::parseDetailPageForUrlName
     * @covers \RatingSync\Site::parseDetailPageForRating
     * @covers \RatingSync\Site::parseDetailPageForGenres
     * @covers \RatingSync\Site::parseDetailPageForDirectors
     * @depends testCacheFilmDetail
     */
    public function testParseDetailPageFullFilmOverwriteTrue()
    {
        $site = new SiteChild(TEST_JINNI_USERNAME);
        $film = new Film($site->_getHttp());

        // Setup original data
        $film->setTitle("Original_Title");
        $film->setYear(1900);
        $film->setContentType(Film::CONTENT_TV);
        $film->setImage("Original_Image");
        $film->setImage("Original_JinniImage", $site->_getSourceName());
        $film->setImage("Original_IMDbImage", Constants::SOURCE_IMDB);
        $film->setFilmId("Original_JinniFilmId", $site->_getSourceName());
        $film->setFilmId("Original_IMDbFilmId", Constants::SOURCE_IMDB);
        $film->setUrlName("Original_JinniUrlName", $site->_getSourceName());
        $film->setUrlName("Original_IMDbUrlName", Constants::SOURCE_IMDB);
        $film->addGenre("Original_Genre1");
        $film->addGenre("Original_Genre2");
        $film->addDirector("Original_Director1");
        $film->addDirector("Original_Director2");
        $ratingJinniOrig = new Rating($site->_getSourceName());
        $ratingJinniOrig->setYourScore(1);
        $ratingJinniOrig->setYourRatingDate(new \DateTime('2000-01-01'));
        $ratingJinniOrig->setSuggestedScore(2);
        $ratingJinniOrig->setCriticScore(3);
        $ratingJinniOrig->setUserScore(4);
        $film->setRating($ratingJinniOrig, $site->_getSourceName());
        $ratingImdbOrig = new Rating(Constants::SOURCE_IMDB);
        $ratingImdbOrig->setYourScore(2);
        $ratingImdbOrig->setYourRatingDate(new \DateTime('2000-01-02'));
        $ratingImdbOrig->setSuggestedScore(3);
        $ratingImdbOrig->setCriticScore(4);
        $ratingImdbOrig->setUserScore(5);
        $film->setRating($ratingImdbOrig, Constants::SOURCE_IMDB);

        // Read a Film Detail page cached
        $filename = JinniTest::getCachePath() . "jinni_frozen-2013.html";
        $fp = fopen($filename, "r");
        $page = fread($fp, filesize($filename));
        fclose($fp);
        
        $success = $site->_parseDetailPageForTitle($page, $film, true);
        $this->assertTrue($success, 'Parsing film object for Title');
        $this->assertEquals("Frozen", $film->getTitle(), 'Check matching Title (full film overwrite=true)');

        $success = $site->_parseDetailPageForFilmYear($page, $film, true);
        $this->assertTrue($success, 'Parsing film object for Year');
        $this->assertEquals(2013, $film->getYear(), 'Check matching Year (full film overwrite=true)');

        $success = $site->_parseDetailPageForContentType($page, $film, true);
        $this->assertFalse($success, 'Parsing film object for Content Type');
        $this->assertEquals(Film::CONTENT_TV, $film->getContentType(), 'Check matching Content Type (full film overwrite=true)');

        $success = $site->_parseDetailPageForImage($page, $film, true);
        $this->assertTrue($success, 'Parsing film object for Image');
        $this->assertStringEndsWith("frozen-2013-1.jpeg", $film->getImage(), 'Check matching Image (full film overwrite=true)');
        $this->assertStringEndsWith("frozen-2013-1.jpeg", $film->getImage($site->_getSourceName()), 'Check matching Image (full film overwrite=true)');
        $this->assertEquals("Original_IMDbImage", $film->getImage(Constants::SOURCE_IMDB), 'Check matching Image (full film overwrite=true)');
        
        $success = $site->_parseDetailPageForUrlName($page, $film, true);
        $this->assertTrue($success, 'Parsing film object for URL Name');
        $this->assertEquals("frozen-2013", $film->getUrlName($site->_getSourceName()), 'Check matching URL Name (full film overwrite=true)');
        $this->assertEquals("Original_IMDbUrlName", $film->getUrlName(Constants::SOURCE_IMDB), 'Check matching URL Name (full film overwrite=true)');
        
        $success = $site->_parseDetailPageForFilmId($page, $film, true);
        $this->assertTrue($success, 'Parsing film object for Film Id');
        $this->assertEquals("999", $film->getFilmId($site->_getSourceName()), 'Check matching Film Id (full film overwrite=true)');
        $this->assertEquals("Original_IMDbFilmId", $film->getFilmId(Constants::SOURCE_IMDB), 'Check matching Film Id (full film overwrite=true)');
        
        $success = $site->_parseDetailPageForRating($page, $film, true);
        $rating = $film->getRating($site->_getSourceName());
        $this->assertEquals(8, $rating->getYourScore(), 'Check matching YourScore (full film overwrite=true)');
        $this->assertEquals(new \DateTime('2000-01-01'), $rating->getYourRatingDate(), 'Check matching Rating Date (full film overwrite=true)');
        $this->assertEquals(2, $rating->getSuggestedScore(), 'Check matching Suggested Score (full film overwrite=true)');
        $this->assertEquals(3, $rating->getCriticScore(), 'Check matching Critic Score (full film overwrite=true)');
        $this->assertEquals(4, $rating->getUserScore(), 'Check matching User Score (full film overwrite=true)');
        $rating = $film->getRating(Constants::SOURCE_IMDB);
        $this->assertEquals(2, $rating->getYourScore(), 'Check matching YourScore (full film overwrite=true)');
        $this->assertEquals(new \DateTime('2000-01-02'), $rating->getYourRatingDate(), 'Check matching Rating Date (full film overwrite=true)');
        $this->assertEquals(3, $rating->getSuggestedScore(), 'Check matching Suggested Score (full film overwrite=true)');
        $this->assertEquals(4, $rating->getCriticScore(), 'Check matching Critic Score (full film overwrite=true)');
        $this->assertEquals(5, $rating->getUserScore(), 'Check matching User Score (full film overwrite=true)');
        
        $success = $site->_parseDetailPageForGenres($page, $film, true);
        $this->assertTrue($success, 'Parsing film object for Genres');
        $this->assertEquals(array("Adventure", "Animation", "Fantasy", "Musical", "Family", "Comedy"), $film->getGenres(), 'Check matching Gneres (full film overwrite=true)');
        
        $success = $site->_parseDetailPageForDirectors($page, $film, true);
        $this->assertTrue($success, 'Parsing film object for Directors');
        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), 'Check matching Directors (full film overwrite=true)');
    }
    
    /**
     * @covers \RatingSync\Site::parseDetailPageForTitle
     * @covers \RatingSync\Site::parseDetailPageForFilmYear
     * @covers \RatingSync\Site::parseDetailPageForImage
     * @covers \RatingSync\Site::parseDetailPageForContentType
     * @covers \RatingSync\Site::parseDetailPageForFilmId
     * @covers \RatingSync\Site::parseDetailPageForUrlName
     * @covers \RatingSync\Site::parseDetailPageForRating
     * @covers \RatingSync\Site::parseDetailPageForGenres
     * @covers \RatingSync\Site::parseDetailPageForDirectors
     * @depends testCacheFilmDetail
     */
    public function testParseDetailPageFullFilmOverwriteFalse()
    {
        $site = new SiteChild(TEST_JINNI_USERNAME);
        $film = new Film($site->_getHttp());

        // Setup original data
        $film->setTitle("Original_Title");
        $film->setYear(1900);
        $film->setContentType(Film::CONTENT_TV);
        $film->addGenre("Original_Genre1");
        $film->addGenre("Original_Genre2");
        $film->addDirector("Original_Director1");
        $film->addDirector("Original_Director2");
        $film->setImage("Original_Image");
        $film->setImage("Original_Image_Jinni", $site->_getSourceName());
        $film->setImage("Original_Image_Imdb", Constants::SOURCE_IMDB);
        $film->setUrlName("Original_UrlName_Jinni", $site->_getSourceName());
        $film->setUrlName("Original_UrlName_Imdb", Constants::SOURCE_IMDB);
        $film->setFilmId("Original_FilmId_Jinni", $site->_getSourceName());
        $film->setFilmId("Original_FilmId_Imdb", Constants::SOURCE_IMDB);
        $ratingJinniOrig = new Rating($site->_getSourceName());
        $ratingJinniOrig->setYourScore(1);
        $ratingJinniOrig->setYourRatingDate(new \DateTime('2000-01-01'));
        $ratingJinniOrig->setSuggestedScore(2);
        $ratingJinniOrig->setCriticScore(3);
        $ratingJinniOrig->setUserScore(4);
        $film->setRating($ratingJinniOrig, $site->_getSourceName());
        $ratingImdbOrig = new Rating(Constants::SOURCE_IMDB);
        $ratingImdbOrig->setYourScore(2);
        $ratingImdbOrig->setYourRatingDate(new \DateTime('2000-01-02'));
        $ratingImdbOrig->setSuggestedScore(3);
        $ratingImdbOrig->setCriticScore(4);
        $ratingImdbOrig->setUserScore(5);
        $film->setRating($ratingImdbOrig, Constants::SOURCE_IMDB);

        // Read a Film Detail page cached
        $filename = JinniTest::getCachePath() . "jinni_frozen-2013.html";
        $fp = fopen($filename, "r");
        $page = fread($fp, filesize($filename));
        fclose($fp);
        
        $success = $site->_parseDetailPageForTitle($page, $film, false);
        $this->assertFalse($success, 'Parsing film object for Title');
        $this->assertEquals("Original_Title", $film->getTitle(), 'Check matching Title (full film overwrite=false)');

        $success = $site->_parseDetailPageForFilmYear($page, $film, false);
        $this->assertFalse($success, 'Parsing film object for Year');
        $this->assertEquals(1900, $film->getYear(), 'Check matching Year (full film overwrite=false)');

        $success = $site->_parseDetailPageForContentType($page, $film, false);
        $this->assertFalse($success, 'Parsing film object for Content Type');
        $this->assertEquals(Film::CONTENT_TV, $film->getContentType(), 'Check matching Content Type (full film overwrite=false)');

        $success = $site->_parseDetailPageForImage($page, $film, false);
        $this->assertFalse($success, 'Parsing film object for Image');
        $this->assertEquals("Original_Image", $film->getImage(), 'Check matching Image (full film overwrite=false)');
        $this->assertEquals("Original_Image_Jinni", $film->getImage($site->_getSourceName()), 'Check matching Image (full film overwrite=false)');
        $this->assertEquals("Original_Image_Imdb", $film->getImage(Constants::SOURCE_IMDB), 'Check matching Image (full film overwrite=false)');
        
        $success = $site->_parseDetailPageForUrlName($page, $film, false);
        $this->assertFalse($success, 'Parsing film object for URL Name');
        $this->assertEquals("Original_UrlName_Jinni", $film->getUrlName($site->_getSourceName()), 'Check matching URL Name (full film overwrite=false)');
        $this->assertEquals("Original_UrlName_Imdb", $film->getUrlName(Constants::SOURCE_IMDB), 'Check matching URL Name (full film overwrite=false)');
        
        $success = $site->_parseDetailPageForFilmId($page, $film, false);
        $this->assertFalse($success, 'Parsing film object for Film Id');
        $this->assertEquals("Original_FilmId_Jinni", $film->getFilmId($site->_getSourceName()), 'Check matching Film Id (full film overwrite=false)');
        $this->assertEquals("Original_FilmId_Imdb", $film->getFilmId(Constants::SOURCE_IMDB), 'Check matching Film Id (full film overwrite=false)');
        
        $success = $site->_parseDetailPageForRating($page, $film, false);
        $rating = $film->getRating($site->_getSourceName());
        $this->assertEquals(1, $rating->getYourScore(), 'Check matching YourScore (full film overwrite=false)');
        $this->assertEquals(new \DateTime('2000-01-01'), $rating->getYourRatingDate(), 'Check matching Rating Date (full film overwrite=false)');
        $this->assertEquals(2, $rating->getSuggestedScore(), 'Check matching Suggested Score (full film overwrite=false)');
        $this->assertEquals(3, $rating->getCriticScore(), 'Check matching Critic Score (full film overwrite=false)');
        $this->assertEquals(4, $rating->getUserScore(), 'Check matching User Score (full film overwrite=false)');
        $rating = $film->getRating(Constants::SOURCE_IMDB);
        $this->assertEquals(2, $rating->getYourScore(), 'Check matching YourScore (full film overwrite=false)');
        $this->assertEquals(new \DateTime('2000-01-02'), $rating->getYourRatingDate(), 'Check matching Rating Date (full film overwrite=false)');
        $this->assertEquals(3, $rating->getSuggestedScore(), 'Check matching Suggested Score (full film overwrite=false)');
        $this->assertEquals(4, $rating->getCriticScore(), 'Check matching Critic Score (full film overwrite=false)');
        $this->assertEquals(5, $rating->getUserScore(), 'Check matching User Score (full film overwrite=false)');
        
        $success = $site->_parseDetailPageForGenres($page, $film, false);
        $this->assertFalse($success, 'Parsing film object for Genres');
        $this->assertEquals(array("Original_Genre1", "Original_Genre2"), $film->getGenres(), 'Check matching Gneres (full film overwrite=false)');
        
        $success = $site->_parseDetailPageForDirectors($page, $film, false);
        $this->assertFalse($success, 'Parsing film object for Directors');
        $this->assertEquals(array("Original_Director1", "Original_Director2"), $film->getDirectors(), 'Check matching Directors (full film overwrite=false)');
    }
}

?>
