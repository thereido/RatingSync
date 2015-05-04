<?php
/**
 * HttpJinni PHPUnit
 */
namespace RatingSync;

require_once "HttpJinni.php";

class HttpJinniExtension extends \RatingSync\HttpJinni {
    function _buildApiParamString($params) {
        return $this->buildApiParamString($params);
    }
    function _buildParamVar($var) {
        return $this->buildParamVar($var);
    }
    function _parseSearchSuggestionResults($result) {
        return $this->parseSearchSuggestionResults($result);
    }
    function _rawApiCall($scriptName, $method, array $params = array()) {
        return $this->rawApiCall($scriptName, $method, $params);
    }
    function _getSessionID() {
        return $this->jSessionID;
    }
    function _getBaseUrl() {
        return $this->baseUrl;
    }
    function _getUsername() {
        return $this->username;
    }
}

class HttpJinniTest extends \PHPUnit_Framework_TestCase
{
    protected static $http;
    protected static $testFileDir;

    public function setup() 
    {
        self::$http = new HttpJinniExtension("username");
        self::$testFileDir = __DIR__ . DIRECTORY_SEPARATOR . "testfile" . DIRECTORY_SEPARATOR;
    }

    /**
     * @covers            \RatingSync\HttpJinni::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testCannotBeConstructedFromNull()
    {
        new HttpJinni(null);
    }

    /**
     * @covers            \RatingSync\HttpJinni::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testCannotBeConstructedFromEmptyUsername()
    {
        new HttpJinni("");
    }

    /**
     * @covers            \RatingSync\HttpJinni::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testCannotBeConstructedFromInt()
    {
        new HttpJinni(1);
    }

    /**
     * @covers \RatingSync\HttpJinni::__construct
     */
    public function testObjectCanBeConstructedFromStringValue()
    {
        $http = new HttpJinni("username");
        return $http;
    }

    /**
     * @covers \RatingSync\HttpJinni::getPage
     * @expectedException \InvalidArgumentException
     */
    public function testCannotGetPageWithNullPage() {
        self::$http->getPage(null);
    }

    /**
     * @covers \RatingSync\HttpJinni::getPage
     */
    public function testCannotGetPageAndReturnsFalse() {
        $page = self::$http->getPage("Bad Page");
        $this->assertFalse($page);
    }

    /**
     * @covers \RatingSync\HttpJinni::getPage
     */
    public function testGetPageAbout() {
        $page = self::$http->getPage('/info/about.html');
        $this->assertGreaterThan(0, stripos($page, "About Jinni</title>"), "Getting the Jinni 'About' page does not look right");
    }

    /**
     * @covers \RatingSync\HttpJinni::getPage
     */
    public function testGetPageGetSessionID() {
        $page = self::$http->getPage('/info/about.html');
        $this->assertGreaterThan(0, strlen(self::$http->_getSessionID()), "getPage() did not bring a jSessionID");
    }

    /**
     * @covers \RatingSync\HttpJinni::getPage
     */
    public function testBaseUrl() {
        // This is just tell us if the BaseUrl changed so we need to update some other tests
        $this->assertEquals("http://www.jinni.com", self::$http->_getBaseUrl(), "/RatingSync/HttpJinni::\$baseUrl has changed, which might affect other tests");
    }

    /**
     * @covers \RatingSync\HttpJinni::getPage
     */
    public function testGetPageNeedsUsername() {
        $this->assertGreaterThan(0, strlen(self::$http->_getUsername()));
    }

    /**
     * @covers \RatingSync\HttpJinni::getPage
     */
    public function testGetPageWithHeadersOnly() {
        $headers = self::$http->getPage('/info/about.html', null, true);
        $this->assertStringEndsWith("Connection: keep-alive", rtrim($headers), "getPage() with headersOnly=true is not ending in a header");
    }

    /**
     * @covers \RatingSync\HttpJinni::buildParamVarString
     */
    public function testBuildParamVarString()
    {
        // search string and content type
        $this->assertEquals(
            "c0-param0=string:Everything You\n".
            "c0-param1=Object_Object:{contentTypeFilter:string:TvSeries}\n",
            self::$http->_buildApiParamString(array('Everything You', (object)array('contentTypeFilter' => 'TvSeries')))
        );
        // null context type
        $this->assertEquals(
            "c0-param0=string:Everything You\n".
            "c0-param1=Object_Object:{contentTypeFilter:null:null}\n",
            self::$http->_buildApiParamString(array('Everything You', (object)array('contentTypeFilter' => null)))
        );
        // Comma in the search string
        $this->assertEquals(
            "c0-param0=string:About Sex, But\n".
            "c0-param1=Object_Object:{contentTypeFilter:null:null}\n",
            self::$http->_buildApiParamString(array('About Sex, But', (object)array('contentTypeFilter' => null)))
        );
        // Apostrophe
        $this->assertEquals(
            "c0-param0=string:Hobson's\n".
            "c0-param1=Object_Object:{contentTypeFilter:null:null}\n",
            self::$http->_buildApiParamString(array('Hobson\'s', (object)array('contentTypeFilter' => null)))
        );
    }

    /**
     * @covers \RatingSync\HttpJinni::buildParamVar
     */
    public function testBuildParamVar()
    {
        // Array object
        $this->assertEquals("Object_Object:{contentTypeFilter:null:null}", self::$http->_buildParamVar((object)array('contentTypeFilter' => null)));
        // String
        $this->assertEquals("string:test", self::$http->_buildParamVar("test"));
        // Int
        $this->assertEquals("number:123", self::$http->_buildParamVar(123));
    }

    /**
     * @covers \RatingSync\HttpJinni::parseSearchSuggestionResults
     * @expectedException \Exception
     */
    public function testCannotParseSearchSuggestionResultsWithEmptySearch() {
        self::$http->_parseSearchSuggestionResults("");
    }

    /**
     * @covers \RatingSync\HttpJinni::parseSearchSuggestionResults
     * @expectedException \Exception
     */
    public function testCannotParseSearchSuggestionResultsWithNullSearch() {
        self::$http->_parseSearchSuggestionResults(null);
    }

    /**
     * @covers \RatingSync\HttpJinni::parseSearchSuggestionResults
     */
    public function testCanParseSearchSuggestionResultsWithTheMatrix() {
        $resultStr = <<<EOD
//#DWR-INSERT
//#DWR-REPLY
var s0=[];var s1={};var s2={};var s3={};var s4={};var s5={};var s6={};var s7={};var s8={};var s9={};var s10={};s0[0]=s1;s0[1]=s2;s0[2]=s3;s0[3]=s4;s0[4]=s5;s0[5]=s6;s0[6]=s7;s0[7]=s8;s0[8]=s9;s0[9]=s10;
s1.categoryType=null;s1.entityType='Title';s1.id="191";s1.name="The Matrix";s1.popularity=null;s1.titleType='FeatureFilm';s1.year=1999;
s2.categoryType=null;s2.entityType='Title';s2.id="192";s2.name="The Matrix Reloaded";s2.popularity=null;s2.titleType='FeatureFilm';s2.year=2003;
s3.categoryType=null;s3.entityType='Title';s3.id="484";s3.name="The Matrix Revolutions";s3.popularity=null;s3.titleType='FeatureFilm';s3.year=2003;
s4.categoryType=null;s4.entityType='Title';s4.id="8656";s4.name="The Matrix Revisited";s4.popularity=null;s4.titleType='FeatureFilm';s4.year=2001;
s5.categoryType=null;s5.entityType='Title';s5.id="87155";s5.name="The Approval Matrix";s5.popularity=null;s5.titleType='TvSeries';s5.year=2014;
s6.categoryType=null;s6.entityType='Title';s6.id="40910";s6.name="The Animatrix: Matriculated";s6.popularity=null;s6.titleType='Short';s6.year=2003;
s7.categoryType=null;s7.entityType='Title';s7.id="7571";s7.name="The Animatrix";s7.popularity=null;s7.titleType='FeatureFilm';s7.year=2003;
s8.categoryType=null;s8.entityType='Title';s8.id="34040";s8.name="Threat Matrix";s8.popularity=null;s8.titleType='TvSeries';s8.year=2003;
s9.categoryType=null;s9.entityType='Title';s9.id="40882";s9.name="The Animatrix: Beyond";s9.popularity=null;s9.titleType='Short';s9.year=2003;
s10.categoryType=null;s10.entityType='Title';s10.id="40834";s10.name="The Animatrix: Kid\'s Story";s10.popularity=null;s10.titleType='Short';s10.year=2003;
dwr.engine._remoteHandleCallback('1','0',{results:s0,searchPhrase:"the matrix",suggestTime:0.034233891});
EOD;
        $results = self::$http->_parseSearchSuggestionResults($resultStr);
        $this->assertTrue(count($results) == 10);
        $this->assertEquals(array('contentType' => 'FeatureFilm', 'id' => 191, 'title' => 'The Matrix', 'year' => 1999), $results[0]);
        $this->assertEquals(array('contentType' => 'FeatureFilm', 'id' => 192, 'title' => 'The Matrix Reloaded', 'year' => 2003), $results[1]);
        $this->assertEquals(array('contentType' => 'TvSeries', 'id' => 34040, 'title' => 'Threat Matrix', 'year' => 2003), $results[7]);
        $this->assertEquals(array('contentType' => 'Short', 'id' => 40834, 'title' => 'The Animatrix: Kid\'s Story', 'year' => 2003), $results[9]);
    }

    /**
     * @covers \RatingSync\HttpJinni::parseSearchSuggestionResults
     */
    public function testCanParseSearchSuggestionResultsIgnoringKeywords() {
        $resultStr = <<<EOD
//#DWR-INSERT
//#DWR-REPLY
var s0=[];var s1={};var s2={};var s3={};var s4={};var s5={};var s6={};var s7={};var s8={};var s9={};var s10={};s0[0]=s1;s0[1]=s2;s0[2]=s3;s0[3]=s4;s0[4]=s5;s0[5]=s6;s0[6]=s7;s0[7]=s8;s0[8]=s9;s0[9]=s10;
s1.categoryType="Keywords";s1.entityType='Category';s1.id="2064";s1.name="Transformation";s1.popularity=null;s1.titleType=null;s1.year=0;
s2.categoryType=null;s2.entityType='Title';s2.id="655";s2.name="Transformers";s2.popularity=null;s2.titleType='FeatureFilm';s2.year=2007;
s3.categoryType="Keywords";s3.entityType='CategorySynonym';s3.id="1548";s3.name="Moral transformation";s3.popularity=null;s3.titleType=null;s3.year=0;
s4.categoryType=null;s4.entityType='Title';s4.id="31240";s4.name="Transformers Prime";s4.popularity=null;s4.titleType='TvSeries';s4.year=2010;
s5.categoryType=null;s5.entityType='Title';s5.id="21128";s5.name="Transformers Armada";s5.popularity=null;s5.titleType='TvSeries';s5.year=2002;
s6.categoryType=null;s6.entityType='Title';s6.id="22387";s6.name="Transformers Animated";s6.popularity=null;s6.titleType='TvSeries';s6.year=2007;
s7.categoryType=null;s7.entityType='Title';s7.id="2861";s7.name="The Transformers: The Movie";s7.popularity=null;s7.titleType='FeatureFilm';s7.year=1986;
s8.categoryType=null;s8.entityType='Title';s8.id="33717";s8.name="Transformers: Dark of the Moon";s8.popularity=null;s8.titleType='FeatureFilm';s8.year=2011;
s9.categoryType=null;s9.entityType='Title';s9.id="58762";s9.name="Transformers: Energon";s9.popularity=null;s9.titleType='TvSeries';s9.year=2004;
s10.categoryType=null;s10.entityType='Title';s10.id="58000";s10.name="Transformers: Cybertron";s10.popularity=null;s10.titleType='TvSeries';s10.year=2005;
dwr.engine._remoteHandleCallback('16','0',{results:s0,searchPhrase:"transform",suggestTime:0.021716644});
EOD;
        $results = self::$http->_parseSearchSuggestionResults($resultStr);
        $this->assertTrue(count($results) == 8);
        $this->assertEquals(array('contentType' => 'FeatureFilm', 'id' => 655, 'title' => 'Transformers', 'year' => 2007), $results[0]);
    }
}

?>
