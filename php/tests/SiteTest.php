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
   - testCacheRatingsPage
   - testCacheFilmDetailPage
   - testGetRatings
   - testGetRatingsUsingCacheAlways
   - testGetRatingsUsingCacheNever
   - testGetRatingsUsingCacheWithRecentFiles
   - testGetRatingsUsingCacheWithOldFiles
   - testGetRatingsCount
   - testGetRatingsLimitPages
   - testGetRatingsBeginPage
   - testGetRatingsDetailsNoCache
   - testGetRatingsDetails
   - testGetSearchSuggestions
   - testGetFilmDetailFromWebsiteFromNull
   - testGetFilmDetailFromWebsiteFromString
   - testGetFilmDetailFromWebsiteWithoutFilmName
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
   - testParseFilmsFromFile
   - testFromExportFileToFilmObjectAndBackToXml
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
    function _parseDetailPageForFilmName($page, $film, $overwrite) { return $this->parseDetailPageForFilmName($page, $film, $overwrite); }
    function _parseDetailPageForUrlName($page, $film, $overwrite) { return $this->parseDetailPageForUrlName($page, $film, $overwrite); }
    function _parseDetailPageForRating($page, $film, $overwrite) { return $this->parseDetailPageForRating($page, $film, $overwrite); }
    function _parseDetailPageForGenres($page, $film, $overwrite) { return $this->parseDetailPageForGenres($page, $film, $overwrite); }
    function _parseDetailPageForDirectors($page, $film, $overwrite) { return $this->parseDetailPageForDirectors($page, $film, $overwrite); }

    // Abstract Function based on \RatingSync\Jinni::getRatingPageUrl
    protected function getRatingPageUrl($args) {
        $pageIndex = $args['pageIndex'];
        return '/user/'.urlencode($this->username).'/ratings?pagingSlider_index='.$pageIndex;
    }

    // Abstract Function returns 2 films
    protected function getFilmsFromRatingsPage($page, $details = false, $refreshCache = 0) {
        $film = new Film($this->http);
        $film2 = new Film($this->http);

        $rating = new Rating($this->sourceName);
        $rating->setYourScore(8);
        $rating->setYourRatingDate(new \DateTime('2015-01-02'));
        $film->setRating($rating, $this->sourceName);
        $film->setTitle("Site Title1");
        $film->setFilmName("Site_FilmName1", $this->sourceName);
        $film->setImage("Site_Image1");
        $film->setImage("Site_Image1", $this->sourceName);
        $film->setContentType(\RatingSync\Film::CONTENT_FILM);

        $rating2 = new Rating($this->sourceName);
        $rating2->setYourScore(7);
        $rating2->setYourRatingDate(new \DateTime('2015-01-03'));
        $film2->setRating($rating2, $this->sourceName);
        $film2->setTitle("Site Title2");
        $film2->setFilmName("Site_FilmName2", $this->sourceName);
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
    protected function getNextRatingPageNumber($page) {
        if (0 == preg_match('@pagingSlider\.addPage\(\d+,false\);[\n|\t]+\$\(document\)@', $page, $matches)) {
            return false;
        }

        if (0 == preg_match('@<input type="hidden" name="pagingSlider_index" id="pagingSlider_index" value="(\d+)" />@', $page, $matches)) {
            return false;
        }
        
        return $matches[1] + 1;
    }

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

    // Abstract Function based on \RatingSync\Jinni::getDetailPageRegexForFilmName
    protected function getDetailPageRegexForFilmName($film) {
        if (is_null($film) || !($film instanceof Film) || empty($film->getUrlName($this->sourceName))) {
            throw new \InvalidArgumentException('Film param must have a URL Name');
        }
        
        return '/{[^}]+contentId: \"(.+)\"[^}]+uniqueName: \"' . $film->getUrlName($this->sourceName) . '\"/';
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
                    $film->addGenre(html_entity_decode($matches[1], ENT_QUOTES, "utf-8"));
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
                    $film->addDirector(html_entity_decode($director, ENT_QUOTES, "utf-8"));
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

    public function getFilmUniqueAttr($film)
    {
        if (!is_null($film) && ($film instanceof Film)) {
            return $film->getUrlName($this->sourceName);
        }
    }
}

class SiteTest extends \PHPUnit_Framework_TestCase
{
    public $debug;
    public $lastTestTime;

    public function setUp()
    {
        $this->debug = false;
        $this->lastTestTime = new \DateTime();
    }

    /**
     * @covers            \RatingSync\Site::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testCannotBeConstructedFromNull()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        new SiteChild(null);
    }

    /**
     * @covers            \RatingSync\Site::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testCannotBeConstructedFromEmptyUsername()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        new SiteChild("");
    }

    /**
     * @covers \RatingSync\Site::__construct
     */
    public function testObjectCanBeConstructed()
    {
        $site = new SiteChild(TEST_SITE_USERNAME);

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
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

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
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

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers \RatingSync\Site::validateAfterConstructor
     * @depends testObjectCanBeConstructed
     */
    public function testValidateAfterConstructorGood()
    {
        $site = new SiteChild(TEST_SITE_USERNAME);
        $this->assertTrue($site->_validateAfterConstructor());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers \RatingSync\Site::cacheRatingsPage
     * @depends testObjectCanBeConstructed
     */
    public function testCacheRatingsPage()
    {
        $site = new SiteChild(TEST_SITE_USERNAME);

        $page = "<html><body><h2>Rating page 2</h2></body></html>";
        $verifyFilename = "testfile" . DIRECTORY_SEPARATOR . "verify_cache_ratingspage.xml";
        $fp = fopen($verifyFilename, "w");
        fwrite($fp, $page);
        fclose($fp);

        $site->cacheRatingsPage($page, 2);
        $testFilename = Constants::cacheFilePath() . $site->_getSourceName() . "_" . TEST_SITE_USERNAME . "_ratings_2.html";
        $this->assertFileExists($testFilename, 'Cache file exists');
        $this->assertFileEquals($verifyFilename, $testFilename, 'cache file vs verify file');
        
        unlink($verifyFilename);
        unlink($testFilename);

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers \RatingSync\Site::cacheFilmDetailPage
     * @depends testObjectCanBeConstructed
     */
    public function testCacheFilmDetailPage()
    {
        $site = new SiteChild(TEST_SITE_USERNAME);
        $film = new Film($site->http);
        $film->setFilmName("tt2294629", $site->_getSourceName());
        
        $page = "<html><body><h2>Film Detail</h2></body></html>";
        $verifyFilename = "testfile" . DIRECTORY_SEPARATOR . "verify_cache_filmdetailpage.xml";
        $fp = fopen($verifyFilename, "w");
        fwrite($fp, $page);
        fclose($fp);
        
        $site->cacheFilmDetailPage($page, $film);
        $testFilename = Constants::cacheFilePath() . $site->_getSourceName() . "_" . TEST_SITE_USERNAME . "_film_" . $site->getFilmUniqueAttr($film) . ".html";
        $this->assertFileExists($testFilename, 'Cache file exists');
        $this->assertFileEquals($verifyFilename, $testFilename, 'cache file vs verify file');
        
        unlink($verifyFilename);
        unlink($testFilename);

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }
    
    /**
     * @covers \RatingSync\Site::getRatings
     * @depends testValidateAfterConstructorGood
     */
    public function testGetRatingsWithoutExceptions()
    {
        $site = new SiteChild(TEST_SITE_USERNAME);

        $films = $site->getRatings();

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers \RatingSync\Site::getRatings
     * @covers \RatingSync\Site::cacheRatingsPage
     * @depends testCacheRatingsPage
     * @depends testGetRatingsWithoutExceptions
     */
    public function testCacheAllRatingsPagesWithRecentFiles()
    {
        $site = new SiteChild(TEST_SITE_USERNAME);

        $pageNums = array('1', '2');
        foreach ($pageNums as $pageNum) {
            $page = '<html><body><h2>Rating page ' . $pageNum . '</h2></body></html>';
            $testFilename = Constants::cacheFilePath() . $site->_getSourceName() . "_" . TEST_SITE_USERNAME . "_ratings_" . $pageNum . ".html";
            $fp = fopen($testFilename, "w");
            fwrite($fp, $page);
            fclose($fp);
        }
        $originalCacheTime = time();
        sleep(1);

        // limitPages=null, beginPage=1, detail=false, refreshCache=0 (refresh now)
        $films = $site->getRatings(null, 1, false, Constants::USE_CACHE_NEVER);
        
        foreach ($pageNums as $pageNum) {
            $testFilename = Constants::cacheFilePath() . $site->_getSourceName() . "_" . TEST_SITE_USERNAME . "_ratings_" . $pageNum . ".html";
            $this->assertFileExists($testFilename, 'Cache file ' . $pageNum . ' exists');
            $this->assertGreaterThan($originalCacheTime, filemtime($testFilename), 'Modified time');
            unlink($testFilename);
        }

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers \RatingSync\Site::getRatings
     * @covers \RatingSync\Site::cacheRatingsPage
     * @depends testGetRatingsWithoutExceptions
     * @depends testCacheRatingsPage
     */
    public function testCacheAllRatingsPagesWithNoFiles()
    {
        $site = new SiteChild(TEST_SITE_USERNAME);
        
        $pageNums = array('1', '2');
        foreach ($pageNums as $pageNum) {
            $testFilename = Constants::cacheFilePath() . $site->_getSourceName() . "_" . TEST_SITE_USERNAME . "_ratings_" . $pageNum . ".html";
            if (file_exists($testFilename)) {
                unlink($testFilename);
            }
        }

        // limitPages=null, beginPage=1, detail=false, refreshCache=0 (refresh now)
        $films = $site->getRatings(null, 1, false, Constants::USE_CACHE_NEVER);
        
        foreach ($pageNums as $pageNum) {
            $testFilename = Constants::cacheFilePath() . $site->_getSourceName() . "_" . TEST_SITE_USERNAME . "_ratings_" . $pageNum . ".html";
            $this->assertFileExists($testFilename, 'Cache file ' . $pageNum . ' exists');
        }

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }
    
    /**
     * @covers \RatingSync\Site::getRatings
     * @depends testGetRatingsWithoutExceptions
     * @depends testCacheAllRatingsPagesWithNoFiles
     */
    public function testGetRatingsCount()
    {
        $site = new SiteChild(TEST_SITE_USERNAME);

        // Each page of ratings from SiteChild returns 2 films.  SiteChild get two
        // pages because of the cached pages made by testCacheAllRatingsPagesWithNoFiles
        $films = $site->getRatings();
        $this->assertCount(4, $films);

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }
    
    /**
     * @covers \RatingSync\Site::getRatings
     * @depends testValidateAfterConstructorGood
     */
    public function testGetRatingsDetailsWithoutExceptions()
    {
        $site = new SiteChild(TEST_SITE_USERNAME);

        $films = $site->getRatings(null, 1, true);

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
        
    }
    
    /**
     * @covers \RatingSync\Site::getRatings
     * @depends testValidateAfterConstructorGood
     */
    public function testGetRatingsLimitPagesBeginPageWithoutExceptions()
    {
        $site = new SiteChild(TEST_SITE_USERNAME);

        $films = $site->getRatings(1, 2, false);

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
        
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

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
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

        // Each page of ratings from SiteChild returns 2 films.  SiteChild get two
        // pages because of the cached pages made by testCacheAllRatingsPagesWithNoFiles.
        // The exported file have 2 films twice.
        $this->assertEquals($test, $verify, 'Match exported file vs verify file');
        fclose($fp_test);
        fclose($fp_verify);

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }
    
    /**
     * @covers \RatingSync\Site::exportRatings
     * @depends testValidateAfterConstructorGood
     */
    public function testExportRatingsXmlDetail()
    {
        $site = new SiteChild(TEST_SITE_USERNAME);
        
        $testFilename = "ratings_test.xml";
        $success = $site->exportRatings("XML", $testFilename, true, 60);
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

        // Each page of ratings from SiteChild returns 2 films.  SiteChild get two
        // pages because of the cached pages made by testCacheAllRatingsPagesWithNoFiles.
        // The exported file have 2 films twice.
        $this->assertEquals($test, $verify, 'Match exported file vs verify file');
        fclose($fp_test);
        fclose($fp_verify);

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }
    
    /**
     * @covers \RatingSync\Site::getFilmDetailFromWebsite
     * @depends testValidateAfterConstructorGood
     */
    public function testGetFilmDetailFromWebsiteEmptyFilmOverwriteTrue()
    {
        $site = new SiteChild(TEST_SITE_USERNAME);

        $film = new Film($site->_getHttp());
        $film->setFilmName("70785", Constants::SOURCE_JINNI);
        $film->setContentType("FeatureFilm");
        $film->setUrlName("frozen-2013", Constants::SOURCE_JINNI);
        $site->getFilmDetailFromWebsite($film, true, Constants::USE_CACHE_NEVER);

        $this->assertEquals("Frozen", $film->getTitle(), 'Title');
        $this->assertEquals(2013, $film->getYear(), 'Year');
        $this->assertEquals("FeatureFilm", $film->getContentType(), 'Content Type');
        $this->assertEquals(1, preg_match('@(http://media[\d]*.jinni.com/(?:tv|movie|shorts|no-image)/frozen-2013/[^"]+)@', $film->getImage(), $matches), 'Image link');
        $this->assertEquals("frozen-2013", $film->getUrlName(Constants::SOURCE_JINNI), 'URL Name');
        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), 'Director(s)');
        $this->assertEquals(array("Adventure", "Animation", "Fantasy", "Musical", "Family", "Comedy"), $film->getGenres(), 'Genres');
        $rating = $film->getRating(Constants::SOURCE_JINNI);
        $this->assertEquals("70785", $film->getFilmName(Constants::SOURCE_JINNI), 'Film ID');
        $this->assertEquals(8, $rating->getYourScore(), 'Your Score');
        $this->assertNull($rating->getYourRatingDate(), 'Rating date not available from film detail page');
        $this->assertNull($rating->getSuggestedScore(), 'Suggested score not available is you are rated the film');
        $this->assertNull($rating->getCriticScore(), 'Critic score not available from Jinni');
        $this->assertNull($rating->getUserScore(), 'User score not available from Jinni');

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
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
        $film->setFilmName("Original_JinniFilmName", Constants::SOURCE_JINNI);
        $film->setFilmName("Original_IMDbFilmName", Constants::SOURCE_IMDB);
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
        $film->setFilmName("70785", Constants::SOURCE_JINNI);
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
        $this->assertEquals("70785", $film->getFilmName(Constants::SOURCE_JINNI), 'Film ID');
        $this->assertEquals(8, $rating->getYourScore(), 'Your Score');

        // The film detail page does not have these fields.  Don't overwrite them.
        $this->assertEquals("1/1/2000", $rating->getYourRatingDate()->format("n/j/Y"), 'Rating date');
        $this->assertEquals(2, $rating->getSuggestedScore(), 'Suggested score not available is you are rated the film');
        $this->assertEquals(3, $rating->getCriticScore(), 'Critic score not available from Jinni');
        $this->assertEquals(4, $rating->getUserScore(), 'User score not available from Jinni');

        // IMDb Rating is unchanged
        $rating = $film->getRating(Constants::SOURCE_IMDB);
        $this->assertEquals("Original_IMDbFilmName", $film->getFilmName(Constants::SOURCE_IMDB), 'Film ID');
        $this->assertEquals(2, $rating->getYourScore(), 'Your Score');
        $this->assertEquals("1/2/2000", $rating->getYourRatingDate()->format("n/j/Y"), 'Rating date');
        $this->assertEquals(3, $rating->getSuggestedScore(), 'Suggested score not available is you are rated the film');
        $this->assertEquals(4, $rating->getCriticScore(), 'Critic score not available from Jinni');
        $this->assertEquals(5, $rating->getUserScore(), 'User score not available from Jinni');

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers \RatingSync\Jinni::getFilmDetailFromWebsite
     * @depends testGetFilmDetailFromWebsiteEmptyFilmOverwriteTrue
     */
    public function testGetFilmDetailFromWebsiteEmptyFilmOverwriteFalse()
    {
        $site = new SiteChild(TEST_SITE_USERNAME);

        $film = new Film($site->_getHttp());
        $film->setFilmName("70785", Constants::SOURCE_JINNI);
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
        $this->assertEquals("70785", $film->getFilmName(Constants::SOURCE_JINNI), 'Film ID');
        $this->assertEquals(8, $rating->getYourScore(), 'Your Score');
        $this->assertNull($rating->getYourRatingDate(), 'Rating date not available from film detail page');
        $this->assertNull($rating->getSuggestedScore(), 'Suggested score not available is you are rated the film');
        $this->assertNull($rating->getCriticScore(), 'Critic score not available from Jinni');
        $this->assertNull($rating->getUserScore(), 'User score not available from Jinni');

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
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
        $film->setFilmName("Original_JinniFilmName", Constants::SOURCE_JINNI);
        $ratingJinniOrig = new Rating(Constants::SOURCE_JINNI);
        $ratingJinniOrig->setYourScore(1);
        $ratingJinniOrig->setYourRatingDate(new \DateTime('2000-01-01'));
        $ratingJinniOrig->setSuggestedScore(2);
        $ratingJinniOrig->setCriticScore(3);
        $ratingJinniOrig->setUserScore(4);
        $film->setRating($ratingJinniOrig, Constants::SOURCE_JINNI);
        $film->setImage("Original_Image", Constants::SOURCE_IMDB);
        $film->setFilmName("Original_ImdbFilmName", Constants::SOURCE_IMDB);
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
        $this->assertEquals("Original_JinniFilmName", $film->getFilmName(Constants::SOURCE_JINNI), 'Film ID');
        $this->assertEquals(1, $rating->getYourScore(), 'Your Score');
        $this->assertEquals("1/1/2000", $rating->getYourRatingDate()->format("n/j/Y"), 'Your Rating Date');
        $this->assertEquals(2, $rating->getSuggestedScore(), 'Suggested score');
        $this->assertEquals(3, $rating->getCriticScore(), 'Critic score');
        $this->assertEquals(4, $rating->getUserScore(), 'User score');
        $rating = $film->getRating(Constants::SOURCE_IMDB);
        $this->assertEquals("Original_ImdbFilmName", $film->getFilmName(Constants::SOURCE_IMDB), 'Film ID');
        $this->assertEquals(2, $rating->getYourScore(), 'Your Score');
        $this->assertEquals("1/2/2000", $rating->getYourRatingDate()->format("n/j/Y"), 'Your Rating Date');
        $this->assertEquals(3, $rating->getSuggestedScore(), 'Suggested score');
        $this->assertEquals(4, $rating->getCriticScore(), 'Critic score');
        $this->assertEquals(5, $rating->getUserScore(), 'User score');

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }
    
    /**
     * @covers \RatingSync\Site::parseDetailPageForTitle
     * @covers \RatingSync\Site::parseDetailPageForFilmYear
     * @covers \RatingSync\Site::parseDetailPageForImage
     * @covers \RatingSync\Site::parseDetailPageForContentType
     * @covers \RatingSync\Site::parseDetailPageForFilmName
     * @covers \RatingSync\Site::parseDetailPageForUrlName
     * @covers \RatingSync\Site::parseDetailPageForRating
     * @covers \RatingSync\Site::parseDetailPageForGenres
     * @covers \RatingSync\Site::parseDetailPageForDirectors
     */
    public function testParseDetailPageEmptyFilmOverwriteTrue()
    {
        $site = new SiteChild(TEST_JINNI_USERNAME);
        $film = new Film($site->_getHttp());

        // Get HTML of the film's detail page
        $findFilm = new Film($site->_getHttp());
        $findFilm->setContentType(Film::CONTENT_FILM, $site->_getSourceName());
        $findFilm->setUrlName("frozen-2013", $site->_getSourceName());
        $site->getFilmDetailFromWebsite($findFilm, true, 60);
        $page = $site->getFilmDetailPageFromCache($findFilm, 60);
        
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
        
        $success = $site->_parseDetailPageForFilmName($page, $film, true);
        $this->assertTrue($success, 'Parsing film object for Film Id');
        $this->assertEquals("70785", $film->getFilmName($site->_getSourceName()), 'Check matching Film Id (empty film overwrite=true)');
        
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

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }
    
    /**
     * @covers \RatingSync\Site::parseDetailPageForTitle
     * @covers \RatingSync\Site::parseDetailPageForFilmYear
     * @covers \RatingSync\Site::parseDetailPageForImage
     * @covers \RatingSync\Site::parseDetailPageForContentType
     * @covers \RatingSync\Site::parseDetailPageForFilmName
     * @covers \RatingSync\Site::parseDetailPageForUrlName
     * @covers \RatingSync\Site::parseDetailPageForRating
     * @covers \RatingSync\Site::parseDetailPageForGenres
     * @covers \RatingSync\Site::parseDetailPageForDirectors
     */
    public function testParseDetailPageEmptyFilmOverwriteFalse()
    {
        $site = new SiteChild(TEST_JINNI_USERNAME);
        $film = new Film($site->_getHttp());

        // Get HTML of the film's detail page
        $findFilm = new Film($site->_getHttp());
        $findFilm->setContentType(Film::CONTENT_FILM, $site->_getSourceName());
        $findFilm->setUrlName("frozen-2013", $site->_getSourceName());
        $site->getFilmDetailFromWebsite($findFilm, true, 60);
        $page = $site->getFilmDetailPageFromCache($findFilm, 60);
        
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
        
        $success = $site->_parseDetailPageForFilmName($page, $film, false);
        $this->assertTrue($success, 'Parsing film object for Film Id');
        $this->assertEquals("70785", $film->getFilmName($site->_getSourceName()), 'Check matching Film Id (empty film overwrite=false)');
        
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

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }
    
    /**
     * @covers \RatingSync\Site::parseDetailPageForTitle
     * @covers \RatingSync\Site::parseDetailPageForFilmYear
     * @covers \RatingSync\Site::parseDetailPageForImage
     * @covers \RatingSync\Site::parseDetailPageForContentType
     * @covers \RatingSync\Site::parseDetailPageForFilmName
     * @covers \RatingSync\Site::parseDetailPageForUrlName
     * @covers \RatingSync\Site::parseDetailPageForRating
     * @covers \RatingSync\Site::parseDetailPageForGenres
     * @covers \RatingSync\Site::parseDetailPageForDirectors
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
        $film->setFilmName("Original_JinniFilmName", $site->_getSourceName());
        $film->setFilmName("Original_IMDbFilmName", Constants::SOURCE_IMDB);
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

        // Get HTML of the film's detail page
        $findFilm = new Film($site->_getHttp());
        $findFilm->setContentType(Film::CONTENT_FILM, $site->_getSourceName());
        $findFilm->setUrlName("frozen-2013", $site->_getSourceName());
        $site->getFilmDetailFromWebsite($findFilm, true, 60);
        $page = $site->getFilmDetailPageFromCache($findFilm, 60);
        
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
        
        $success = $site->_parseDetailPageForFilmName($page, $film, true);
        $this->assertTrue($success, 'Parsing film object for Film Id');
        $this->assertEquals("70785", $film->getFilmName($site->_getSourceName()), 'Check matching Film Id (full film overwrite=true)');
        $this->assertEquals("Original_IMDbFilmName", $film->getFilmName(Constants::SOURCE_IMDB), 'Check matching Film Id (full film overwrite=true)');
        
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

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }
    
    /**
     * @covers \RatingSync\Site::parseDetailPageForTitle
     * @covers \RatingSync\Site::parseDetailPageForFilmYear
     * @covers \RatingSync\Site::parseDetailPageForImage
     * @covers \RatingSync\Site::parseDetailPageForContentType
     * @covers \RatingSync\Site::parseDetailPageForFilmName
     * @covers \RatingSync\Site::parseDetailPageForUrlName
     * @covers \RatingSync\Site::parseDetailPageForRating
     * @covers \RatingSync\Site::parseDetailPageForGenres
     * @covers \RatingSync\Site::parseDetailPageForDirectors
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
        $film->setFilmName("Original_FilmName_Jinni", $site->_getSourceName());
        $film->setFilmName("Original_FilmName_Imdb", Constants::SOURCE_IMDB);
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

        // Get HTML of the film's detail page
        $findFilm = new Film($site->_getHttp());
        $findFilm->setContentType(Film::CONTENT_FILM, $site->_getSourceName());
        $findFilm->setUrlName("frozen-2013", $site->_getSourceName());
        $site->getFilmDetailFromWebsite($findFilm, true, 60);
        $page = $site->getFilmDetailPageFromCache($findFilm, 60);
        
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
        
        $success = $site->_parseDetailPageForFilmName($page, $film, false);
        $this->assertFalse($success, 'Parsing film object for Film Id');
        $this->assertEquals("Original_FilmName_Jinni", $film->getFilmName($site->_getSourceName()), 'Check matching Film Id (full film overwrite=false)');
        $this->assertEquals("Original_FilmName_Imdb", $film->getFilmName(Constants::SOURCE_IMDB), 'Check matching Film Id (full film overwrite=false)');
        
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

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }
    
    /**
     * @covers \RatingSync\Site::testParseFilmsFromFile
     * @depends testValidateAfterConstructorGood
     */
    public function testParseFilmsFromFile()
    {
        $site = new SiteChild(TEST_SITE_USERNAME);
        $filename =  __DIR__ . DIRECTORY_SEPARATOR . "testfile" . DIRECTORY_SEPARATOR . "input_ratings_site.xml";
        $films = $site->parseFilmsFromFile(Constants::EXPORT_FORMAT_XML, $filename);

        // Count: Titles 1-7 plus 3 for Frozen plus 0 for the empty <film/>
        $this->assertCount(10, $films, 'Count films');

        // Frozen from Jinni
        $film = $films[0];
        $this->assertEquals("Frozen", $film->getTitle(), "Frozen title");
        $this->assertEquals(2013, $film->getYear(), "Frozen year");
        $this->assertEquals(Film::CONTENT_FILM, $film->getContentType(), "Frozen ContentType");
        $this->assertEquals("http://media.jinni.com/movie/frozen-2013/frozen-2013-5.jpeg", $film->getImage(), "Frozen image");
        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), "Frozen directors");
        $this->assertEquals(array("Adventure", "Animation", "Fantasy", "Musical", "Family", "Comedy"), $film->getGenres(), "Frozen genres");
        $this->assertEquals("http://media.jinni.com/movie/frozen-2013/frozen-2013-5.jpeg", $film->getImage(Constants::SOURCE_JINNI), "Frozen ".Constants::SOURCE_JINNI." image");
        $this->assertEquals("70785", $film->getFilmName(Constants::SOURCE_JINNI), "Frozen ".Constants::SOURCE_JINNI." Film ID");
        $this->assertEquals("frozen-2013", $film->getUrlName(Constants::SOURCE_JINNI), "Frozen ".Constants::SOURCE_JINNI." URL Name");
        $rating = $film->getRating(Constants::SOURCE_JINNI);
        $this->assertEquals(8, $rating->getYourScore(), "Frozen ".Constants::SOURCE_JINNI." your score");
        $this->assertEquals("5/4/15", $rating->getYourRatingDate()->format('n/j/y'), "Frozen ".Constants::SOURCE_JINNI." rating date");
        $this->assertNull($rating->getSuggestedScore(), "Frozen ".Constants::SOURCE_JINNI." suggested score");
        $this->assertNull($rating->getCriticScore(), "Frozen ".Constants::SOURCE_JINNI." critic score");
        $this->assertNull($rating->getUserScore(), "Frozen ".Constants::SOURCE_JINNI." user score");

        // Title1
        $film = $films[1];
        $this->assertEquals("Title1", $film->getTitle(), "Title1 title");
        $this->assertEquals(2001, $film->getYear(), "Title1 year");
        $this->assertEquals(Film::CONTENT_FILM, $film->getContentType(), "Title1 ContentType");
        $this->assertEquals("http://example.com/title1_image.jpeg", $film->getImage(), "Title1 image");
        $this->assertEquals(array("Director1.1"), $film->getDirectors(), "Title1 directors");
        $this->assertEquals(array("Genre1.1"), $film->getGenres(), "Title1 genres");
        $this->assertEquals("http://example.com/title1_rs_image.jpeg", $film->getImage(Constants::SOURCE_RATINGSYNC), "Title1 ".Constants::SOURCE_RATINGSYNC." image");
        $this->assertEquals("FilmName1_rs", $film->getFilmName(Constants::SOURCE_RATINGSYNC), "Title1 ".Constants::SOURCE_RATINGSYNC." Film ID");
        $this->assertEquals("UrlName1_rs", $film->getUrlName(Constants::SOURCE_RATINGSYNC), "Title1 ".Constants::SOURCE_RATINGSYNC." URL Name");
        $rating = $film->getRating(Constants::SOURCE_RATINGSYNC);
        $this->assertEquals(1, $rating->getYourScore(), "Title1 ".Constants::SOURCE_RATINGSYNC." your score");
        $this->assertEquals("1/1/15", $rating->getYourRatingDate()->format('n/j/y'), "Title1 ".Constants::SOURCE_RATINGSYNC." rating date");
        $this->assertEquals(2, $rating->getSuggestedScore(), "Title1 ".Constants::SOURCE_RATINGSYNC." suggested score");
        $this->assertEquals(3, $rating->getCriticScore(), "Title1 ".Constants::SOURCE_RATINGSYNC." critic score");
        $this->assertEquals(4, $rating->getUserScore(), "Title1 ".Constants::SOURCE_RATINGSYNC." user score");

        // Title2
        $film = $films[2];
        $this->assertEquals("Title2", $film->getTitle(), "Title2 title");
        $this->assertEquals(2002, $film->getYear(), "Title2 year");
        $this->assertEquals(Film::CONTENT_FILM, $film->getContentType(), "Title2 ContentType");
        $this->assertEquals("http://example.com/title2_image.jpeg", $film->getImage(), "Title2 image");
        $this->assertEquals(array("Director2.1", "Director2.2"), $film->getDirectors(), "Title2 directors");
        $this->assertEquals(array("Genre2.1", "Genre2.2"), $film->getGenres(), "Title2 genres");
        $this->assertEquals("http://example.com/title2_rs_image.jpeg", $film->getImage(Constants::SOURCE_RATINGSYNC), "Title2 ".Constants::SOURCE_RATINGSYNC." image");
        $this->assertEquals("FilmName2_rs", $film->getFilmName(Constants::SOURCE_RATINGSYNC), "Title2 ".Constants::SOURCE_RATINGSYNC." Film ID");
        $this->assertEquals("UrlName2_rs", $film->getUrlName(Constants::SOURCE_RATINGSYNC), "Title2 ".Constants::SOURCE_RATINGSYNC." URL Name");
        $rating = $film->getRating(Constants::SOURCE_RATINGSYNC);
        $this->assertEquals(2, $rating->getYourScore(), "Title2 ".Constants::SOURCE_RATINGSYNC." your score");
        $this->assertEquals("1/2/15", $rating->getYourRatingDate()->format('n/j/y'), "Title2 ".Constants::SOURCE_RATINGSYNC." rating date");
        $this->assertEquals(3, $rating->getSuggestedScore(), "Title2 ".Constants::SOURCE_RATINGSYNC." suggested score");
        $this->assertEquals(4, $rating->getCriticScore(), "Title2 ".Constants::SOURCE_RATINGSYNC." critic score");
        $this->assertEquals(5, $rating->getUserScore(), "Title2 ".Constants::SOURCE_RATINGSYNC." user score");

        // Title3
        $film = $films[3];
        $this->assertEquals("Title3", $film->getTitle(), "Title3 title");
        $this->assertEmpty($film->getYear(), "Title3 year");
        $this->assertEmpty($film->getContentType(), "Title3 ContentType");
        $this->assertEmpty($film->getImage(), "Title3 image");
        $this->assertEmpty($film->getDirectors(), "Title3 directors");
        $this->assertEmpty($film->getGenres(), "Title3 genres");
        $this->assertEmpty($film->getImage(Constants::SOURCE_RATINGSYNC), "Title3 ".Constants::SOURCE_RATINGSYNC." image");
        $this->assertEmpty($film->getFilmName(Constants::SOURCE_RATINGSYNC), "Title3 ".Constants::SOURCE_RATINGSYNC." Film ID");
        $this->assertEmpty($film->getUrlName(Constants::SOURCE_RATINGSYNC), "Title3 ".Constants::SOURCE_RATINGSYNC." URL Name");
        $rating = $film->getRating(Constants::SOURCE_RATINGSYNC);
        $this->assertEmpty($rating->getYourScore(), "Title3 ".Constants::SOURCE_RATINGSYNC." your score");
        $this->assertEmpty($rating->getYourRatingDate(), "Title3 ".Constants::SOURCE_RATINGSYNC." rating date");
        $this->assertEmpty($rating->getSuggestedScore(), "Title3 ".Constants::SOURCE_RATINGSYNC." suggested score");
        $this->assertEmpty($rating->getCriticScore(), "Title3 ".Constants::SOURCE_RATINGSYNC." critic score");
        $this->assertEmpty($rating->getUserScore(), "Title3 ".Constants::SOURCE_RATINGSYNC." user score");

        // Title4
        $film = $films[4];
        $this->assertEquals("Title4", $film->getTitle(), "Title3 title");
        $this->assertEmpty($film->getYear(), "Title4 year");
        $this->assertEmpty($film->getContentType(), "Title4 ContentType");
        $this->assertEmpty($film->getImage(), "Title4 image");
        $this->assertEmpty($film->getDirectors(), "Title4 directors");
        $this->assertEmpty($film->getGenres(), "Title4 genres");
        $this->assertEmpty($film->getImage(Constants::SOURCE_RATINGSYNC), "Title4 ".Constants::SOURCE_RATINGSYNC." image");
        $this->assertEmpty($film->getFilmName(Constants::SOURCE_RATINGSYNC), "Title4 ".Constants::SOURCE_RATINGSYNC." Film ID");
        $this->assertEmpty($film->getUrlName(Constants::SOURCE_RATINGSYNC), "Title4 ".Constants::SOURCE_RATINGSYNC." URL Name");
        $rating = $film->getRating(Constants::SOURCE_RATINGSYNC);
        $this->assertEmpty($rating->getYourScore(), "Title4 ".Constants::SOURCE_RATINGSYNC." your score");
        $this->assertEmpty($rating->getYourRatingDate(), "Title4 ".Constants::SOURCE_RATINGSYNC." rating date");
        $this->assertEmpty($rating->getSuggestedScore(), "Title4 ".Constants::SOURCE_RATINGSYNC." suggested score");
        $this->assertEmpty($rating->getCriticScore(), "Title4 ".Constants::SOURCE_RATINGSYNC." critic score");
        $this->assertEmpty($rating->getUserScore(), "Title4 ".Constants::SOURCE_RATINGSYNC." user score");

        // Title5
        $film = $films[5];
        $this->assertEquals("Title5", $film->getTitle(), "Title5 title");
        $this->assertEmpty($film->getYear(), "Title5 year");
        $this->assertEmpty($film->getContentType(), "Title5 ContentType");
        $this->assertEmpty($film->getImage(), "Title5 image");
        $this->assertEmpty($film->getDirectors(), "Title5 directors");
        $this->assertEmpty($film->getGenres(), "Title5 genres");
        $this->assertEmpty($film->getImage(Constants::SOURCE_RATINGSYNC), "Title5 ".Constants::SOURCE_RATINGSYNC." image");
        $this->assertEmpty($film->getFilmName(Constants::SOURCE_RATINGSYNC), "Title5 ".Constants::SOURCE_RATINGSYNC." Film ID");
        $this->assertEmpty($film->getUrlName(Constants::SOURCE_RATINGSYNC), "Title5 ".Constants::SOURCE_RATINGSYNC." URL Name");
        $rating = $film->getRating(Constants::SOURCE_RATINGSYNC);
        $this->assertEmpty($rating->getYourScore(), "Title5 ".Constants::SOURCE_RATINGSYNC." your score");
        $this->assertEmpty($rating->getYourRatingDate(), "Title5 ".Constants::SOURCE_RATINGSYNC." rating date");
        $this->assertEmpty($rating->getSuggestedScore(), "Title5 ".Constants::SOURCE_RATINGSYNC." suggested score");
        $this->assertEmpty($rating->getCriticScore(), "Title5 ".Constants::SOURCE_RATINGSYNC." critic score");
        $this->assertEmpty($rating->getUserScore(), "Title5 ".Constants::SOURCE_RATINGSYNC." user score");

        // Title6
        $film = $films[6];
        $this->assertEquals("Title6", $film->getTitle(), "Title6 title");
        $this->assertEquals(2006, $film->getYear(), "Title6 year");
        $this->assertEquals(Film::CONTENT_FILM, $film->getContentType(), "Title6 ContentType");
        $this->assertEquals("http://example.com/title6_image.jpeg", $film->getImage(), "Title6 image");
        $this->assertEquals(array("Director6.1"), $film->getDirectors(), "Title6 directors");
        $this->assertEquals(array("Genre6.1"), $film->getGenres(), "Title6 genres");
        $this->assertEquals("http://example.com/title6_rs_image.jpeg", $film->getImage(Constants::SOURCE_RATINGSYNC), "Title6 ".Constants::SOURCE_RATINGSYNC." image");
        $this->assertEquals("FilmName6_rs", $film->getFilmName(Constants::SOURCE_RATINGSYNC), "Title6 ".Constants::SOURCE_RATINGSYNC." Film ID");
        $this->assertEquals("UrlName6_rs", $film->getUrlName(Constants::SOURCE_RATINGSYNC), "Title6 ".Constants::SOURCE_RATINGSYNC." URL Name");
        $rating = $film->getRating(Constants::SOURCE_RATINGSYNC);
        $this->assertEquals(6, $rating->getYourScore(), "Title6 ".Constants::SOURCE_RATINGSYNC." your score");
        $this->assertEquals("1/6/15", $rating->getYourRatingDate()->format('n/j/y'), "Title6 ".Constants::SOURCE_RATINGSYNC." rating date");
        $this->assertEquals(7, $rating->getSuggestedScore(), "Title6 ".Constants::SOURCE_RATINGSYNC." suggested score");
        $this->assertEquals(8, $rating->getCriticScore(), "Title6 ".Constants::SOURCE_RATINGSYNC." critic score");
        $this->assertEquals(9, $rating->getUserScore(), "Title6 ".Constants::SOURCE_RATINGSYNC." user score");
        $this->assertEquals("http://example.com/title6_imdb_image.jpeg", $film->getImage(Constants::SOURCE_IMDB), "Title6 ".Constants::SOURCE_IMDB." image");
        $this->assertEquals("FilmName6_imdb", $film->getFilmName(Constants::SOURCE_IMDB), "Title6 ".Constants::SOURCE_IMDB." Film ID");
        $this->assertEquals("UrlName6_imdb", $film->getUrlName(Constants::SOURCE_IMDB), "Title6 ".Constants::SOURCE_IMDB." URL Name");
        $rating = $film->getRating(Constants::SOURCE_IMDB);
        $this->assertEquals(5, $rating->getYourScore(), "Title6 ".Constants::SOURCE_IMDB." your score");
        $this->assertEquals("1/5/15", $rating->getYourRatingDate()->format('n/j/y'), "Title6 ".Constants::SOURCE_IMDB." rating date");
        $this->assertEquals(6, $rating->getSuggestedScore(), "Title6 ".Constants::SOURCE_IMDB." suggested score");
        $this->assertEquals(7, $rating->getCriticScore(), "Title6 ".Constants::SOURCE_IMDB." critic score");
        $this->assertEquals(8, $rating->getUserScore(), "Title6 ".Constants::SOURCE_IMDB." user score");

        // Title7
        $film = $films[7];
        $this->assertEquals("Wallace & Gromit: A Matter of Loaf and Déath", $film->getTitle(), "Title7 title");
        $this->assertEquals(array("Georges Méliès"), $film->getDirectors(), "Title7 directors");
        $this->assertEquals(array("Genre 1 & 1ès"), $film->getGenres(), "Title7 genres");

        // Frozen from IMDb
        $film = $films[8];
        $this->assertEquals("Frozen", $film->getTitle(), "Frozen title");
        $this->assertEquals(2013, $film->getYear(), "Frozen year");
        $this->assertEquals(Film::CONTENT_FILM, $film->getContentType(), "Frozen ContentType");
        $this->assertEquals("http://ia.media-imdb.com/images/M/MV5BMTQ1MjQwMTE5OF5BMl5BanBnXkFtZTgwNjk3MTcyMDE@._V1._SY209_CR0,0,140,209_.jpg", $film->getImage(), "Frozen image");
        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), "Frozen directors");
        $this->assertEquals(array("Animation", "Adventure", "Comedy"), $film->getGenres(), "Frozen genres");
        $this->assertEquals("http://ia.media-imdb.com/images/M/MV5BMTQ1MjQwMTE5OF5BMl5BanBnXkFtZTgwNjk3MTcyMDE@._V1._SY209_CR0,0,140,209_.jpg", $film->getImage(Constants::SOURCE_IMDB), "Frozen ".Constants::SOURCE_IMDB." image");
        $this->assertEquals("tt2294629", $film->getFilmName(Constants::SOURCE_IMDB), "Frozen ".Constants::SOURCE_IMDB." Film ID");
        $this->assertNull($film->getUrlName(Constants::SOURCE_IMDB), "Frozen ".Constants::SOURCE_IMDB." URL Name");
        $rating = $film->getRating(Constants::SOURCE_IMDB);
        $this->assertEquals(2, $rating->getYourScore(), "Frozen ".Constants::SOURCE_IMDB." your score");
        $this->assertNull($rating->getYourRatingDate(), "Frozen ".Constants::SOURCE_IMDB." rating date");
        $this->assertNull($rating->getSuggestedScore(), "Frozen ".Constants::SOURCE_IMDB." suggested score");
        $this->assertEquals(7.4, $rating->getCriticScore(), "Frozen ".Constants::SOURCE_IMDB." critic score");
        $this->assertEquals(7.7, $rating->getUserScore(), "Frozen ".Constants::SOURCE_IMDB." user score");

        // Frozen from All Sources
        $film = $films[9];
        $this->assertEquals("Frozen", $film->getTitle(), "Frozen title");
        $this->assertEquals(2013, $film->getYear(), "Frozen year");
        $this->assertEquals(Film::CONTENT_FILM, $film->getContentType(), "Frozen ContentType");
        $this->assertEquals("http://example.com/frozen_rs_image.jpeg", $film->getImage(), "Frozen image");
        $this->assertEquals(array("Chris Buck", "Jennifer Lee"), $film->getDirectors(), "Frozen directors");
        $this->assertEquals(array("Animation", "Adventure", "Comedy", "Fantasy", "Musical", "Family"), $film->getGenres(), "Frozen genres");
        $this->assertEquals("http://example.com/frozen_rs_image.jpeg", $film->getImage(Constants::SOURCE_RATINGSYNC), "Frozen ".Constants::SOURCE_RATINGSYNC." image");
        $this->assertEquals("Frozen_rs", $film->getFilmName(Constants::SOURCE_RATINGSYNC), "Frozen ".Constants::SOURCE_RATINGSYNC." Film ID");
        $this->assertEquals("UrlNameFrozen_rs", $film->getUrlName(Constants::SOURCE_RATINGSYNC), "Frozen ".Constants::SOURCE_RATINGSYNC." URL Name");
        $rating = $film->getRating(Constants::SOURCE_RATINGSYNC);
        $this->assertEquals(2, $rating->getYourScore(), "Frozen ".Constants::SOURCE_RATINGSYNC." your score");
        $this->assertEquals("1/2/15", $rating->getYourRatingDate()->format('n/j/y'), "Frozen ".Constants::SOURCE_RATINGSYNC." rating date");
        $this->assertEquals(3, $rating->getSuggestedScore(), "Frozen ".Constants::SOURCE_RATINGSYNC." suggested score");
        $this->assertEquals(4, $rating->getCriticScore(), "Frozen ".Constants::SOURCE_RATINGSYNC." critic score");
        $this->assertEquals(5, $rating->getUserScore(), "Frozen ".Constants::SOURCE_RATINGSYNC." user score");
        $this->assertEquals("http://media.jinni.com/movie/frozen-2013/frozen-2013-5.jpeg", $film->getImage(Constants::SOURCE_JINNI), "Frozen ".Constants::SOURCE_JINNI." image");
        $this->assertEquals("70785", $film->getFilmName(Constants::SOURCE_JINNI), "Frozen ".Constants::SOURCE_JINNI." Film ID");
        $this->assertEquals("frozen-2013", $film->getUrlName(Constants::SOURCE_JINNI), "Frozen ".Constants::SOURCE_JINNI." URL Name");
        $rating = $film->getRating(Constants::SOURCE_JINNI);
        $this->assertEquals(8, $rating->getYourScore(), "Frozen ".Constants::SOURCE_JINNI." your score");
        $this->assertEquals("5/4/15", $rating->getYourRatingDate()->format('n/j/y'), "Frozen ".Constants::SOURCE_JINNI." rating date");
        $this->assertNull($rating->getSuggestedScore(), "Frozen ".Constants::SOURCE_JINNI." suggested score");
        $this->assertNull($rating->getCriticScore(), "Frozen ".Constants::SOURCE_JINNI." critic score");
        $this->assertNull($rating->getUserScore(), "Frozen ".Constants::SOURCE_JINNI." user score");
        $this->assertEquals("http://ia.media-imdb.com/images/M/MV5BMTQ1MjQwMTE5OF5BMl5BanBnXkFtZTgwNjk3MTcyMDE@._V1._SY209_CR0,0,140,209_.jpg", $film->getImage(Constants::SOURCE_IMDB), "Frozen ".Constants::SOURCE_IMDB." image");
        $this->assertEquals("tt2294629", $film->getFilmName(Constants::SOURCE_IMDB), "Frozen ".Constants::SOURCE_IMDB." Film ID");
        $this->assertNull($film->getUrlName(Constants::SOURCE_IMDB), "Frozen ".Constants::SOURCE_IMDB." URL Name");
        $rating = $film->getRating(Constants::SOURCE_IMDB);
        $this->assertEquals(2, $rating->getYourScore(), "Frozen ".Constants::SOURCE_IMDB." your score");
        $this->assertNull($rating->getYourRatingDate(), "Frozen ".Constants::SOURCE_IMDB." rating date");
        $this->assertNull($rating->getSuggestedScore(), "Frozen ".Constants::SOURCE_IMDB." suggested score");
        $this->assertEquals(7.4, $rating->getCriticScore(), "Frozen ".Constants::SOURCE_IMDB." critic score");
        $this->assertEquals(7.7, $rating->getUserScore(), "Frozen ".Constants::SOURCE_IMDB." user score");

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }
    
    /**
     * @covers \RatingSync\Site::testFromExportFileToFilmObjectAndBackToXml
     * @depends testParseFilmsFromFile
     */
    public function testFromExportFileToFilmObjectAndBackToXml()
    {
        $site = new SiteChild(TEST_SITE_USERNAME);
        
        // Get Film objects from a XML file (original_xml)
        $fullOriginalFilename = "testfile/original_xml.xml";
        $this->assertTrue(is_readable($fullOriginalFilename), 'Need to read verify file ' . $fullOriginalFilename);
        $films = $site->parseFilmsFromFile(Constants::EXPORT_FORMAT_XML, $fullOriginalFilename);

        // Write the Film object to a new XML file (test_writing_xml)
        $xml = new \SimpleXMLElement("<films/>");
        foreach ($films as $film) {
            $film->addXmlChild($xml);
        }
        $filmCount = $xml->count();
        $xml->addChild('count', $filmCount);
        
        $testFilename = "test_writing_xml.xml";
        $fullTestFilename =  __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . Constants::outputFilePath() . $testFilename;
        $fp = fopen($fullTestFilename, "w");
        fwrite($fp, $xml->asXml());
        fclose($fp);

        // Assert they are equal
        $fp_test = fopen($fullTestFilename, "r");
        $fp_verify = fopen($fullOriginalFilename, "r");
        $testFileSize = filesize($fullTestFilename);
        $verifyFileSize = filesize($fullOriginalFilename);
        $this->assertEquals($testFileSize, $verifyFileSize, 'File sizes - test (written) vs verify (original)');
        $test = fread($fp_test, filesize($fullTestFilename));
        $verify = fread($fp_verify, filesize($fullOriginalFilename));
        $this->assertEquals($test, $verify, 'Match test file (written) vs verify file (original)');
        fclose($fp_test);
        fclose($fp_verify);

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    public function testResetDb()
    {
        exec("mysql --user=rs_user --password=password ratingsync_db < ..\..\sql\db_tables_drop.sql");
        exec("mysql --user=rs_user --password=password ratingsync_db < ..\..\sql\db_tables_create.sql");
        exec("mysql --user=rs_user --password=password ratingsync_db < ..\..\sql\db_insert_initial.sql");

        $db = getDatabase();
        $result = $db->query("SELECT count(id) as count FROM film");
        $row = $result->fetch_assoc();
        $this->assertEquals(0, $row["count"], "Film rows (should be none)");
        $result = $db->query("SELECT count(user_source.source_name) as count FROM user, source, user_source WHERE user.username='testratingsync' AND user.username=user_source.user_name AND user_source.source_name=source.name");
        $row = $result->fetch_assoc();
        $this->assertEquals(3, $row["count"], "Test user with sources");

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers \RatingSync\Site::importRatings
     * @depends testParseFilmsFromFile
     * @depends testResetDb
     */
    public function testImport()
    {
        $site = new SiteChild(TEST_SITE_USERNAME);
        
        $filename =  __DIR__ . DIRECTORY_SEPARATOR . "testfile" . DIRECTORY_SEPARATOR . "input_ratings_site.xml";
        $films = $site->importRatings(Constants::IMPORT_FORMAT_XML, $filename);
        
        $db = getDatabase();

        // Count rows in each table
        $result = $db->query("SELECT count(id) as count FROM film");
        $row = $result->fetch_assoc();
        $this->assertEquals(8, $row["count"], "Films");
        $result = $db->query("SELECT count(film_id) as count FROM film_source");
        $row = $result->fetch_assoc();
        $this->assertEquals(10, $row["count"], "Film/Source rows");
        $result = $db->query("SELECT count(film_id) as count FROM rating");
        $row = $result->fetch_assoc();
        $this->assertEquals(10, $row["count"], "Ratings");
        $result = $db->query("SELECT count(name) as count FROM genre");
        $row = $result->fetch_assoc();
        $this->assertEquals(11, $row["count"], "Genres");
        $result = $db->query("SELECT count(film_id) as count FROM film_genre");
        $row = $result->fetch_assoc();
        $this->assertEquals(11, $row["count"], "Film/Genre rows");
        $result = $db->query("SELECT count(fullname) as count FROM person");
        $row = $result->fetch_assoc();
        $this->assertEquals(7, $row["count"], "People");
        $result = $db->query("SELECT count(film_id) as count FROM credit");
        $row = $result->fetch_assoc();
        $this->assertEquals(7, $row["count"], "Credits");

        // Ratings for the test user
        $result = $db->query("SELECT title, yourScore FROM rating, film WHERE rating.user_name='testratingsync' AND rating.source_name='RatingSync' AND film.id=rating.film_id");
        $row = $result->fetch_assoc();
        $this->assertEquals(6, $result->num_rows, "Ratings from RS");
        $result = $db->query("SELECT title, yourScore FROM rating, film WHERE rating.user_name='testratingsync' AND rating.source_name='Jinni' AND film.id=rating.film_id");
        $row = $result->fetch_assoc();
        $this->assertEquals(2, $result->num_rows, "Ratings from Jinni");
        $result = $db->query("SELECT title, yourScore FROM rating, film WHERE rating.user_name='testratingsync' AND rating.source_name='IMDb' AND film.id=rating.film_id");
        $row = $result->fetch_assoc();
        $this->assertEquals(2, $result->num_rows, "Ratings from IMDb");
        $result = $db->query("SELECT source_name, yourScore FROM rating, film WHERE rating.user_name='testratingsync' AND film.title='Title6' AND film.id=rating.film_id");
        while ($row = $result->fetch_assoc()) {
            $source = $row["source_name"];
            if ($source == "RatingSync") {
                $this->assertEquals(6, $row["yourScore"], "Your score for Title6 from RatingSync");
            } elseif ($source == "Jinni") {
                $this->assertEquals(4, $row["yourScore"], "Your score for Title6 from Jinni");
            } elseif ($source == "IMDb") {
                $this->assertEquals(5, $row["yourScore"], "Your score for Title6 from IMDb");
            }
        }

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }
}

?>
