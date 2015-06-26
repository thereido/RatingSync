<?php
/**
 * HttpImdb PHPUnit
 */
namespace RatingSync;

require_once "../HttpImdb.php";

// Child class to expose protected members and functions
class HttpImdbExt extends \RatingSync\HttpImdb {
    function _getSessionId() { return $this->sessionId; }
    function _getCookieUu() { return $this->cookieUu; }
    function _getCookieCs() { return $this->cookieCs; }
    function _getBaseUrl() { return $this->baseUrl; }
    function _getLightweightUrl() { return $this->lightweightUrl; }

    function _validateAfterConstructor() { return $this->validateAfterConstructor(); }
    function _putCookiesInRequest($ch) { return $this->putCookiesInRequest($ch); }
}

class HttpImdbTest extends \PHPUnit_Framework_TestCase
{
    public $debug;
    public $lastTestTime;

    public function setUp()
    {
        $this->debug = false;
        $this->lastTestTime = new \DateTime();
    }

    /**
     * @covers            \RatingSync\HttpImdb::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testCannotBeConstructedFromNull()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        new HttpImdb(null);
    }

    /**
     * @covers            \RatingSync\HttpImdb::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testCannotBeConstructedFromEmptyUsername()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        new HttpImdb("");
    }

    /**
     * @covers            \RatingSync\HttpImdb::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testCannotBeConstructedFromInt()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        new HttpImdb(1);
    }

    /**
     * @covers \RatingSync\HttpImdb::__construct
     */
    public function testObjectCanBeConstructedFromStringValue()
    {
        $http = new HttpImdb("username");

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers \RatingSync\HttpImdb::__construct
     * @depends testObjectCanBeConstructedFromStringValue
     */
    public function testConstructorValidated()
    {
        $http = new HttpImdbExt("username");
        $this->assertTrue($http->_validateAfterConstructor());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers \RatingSync\HttpImdb::getPage
     * @depends testConstructorValidated
     * @expectedException \InvalidArgumentException
     */
    public function testCannotGetPageWithNullPage()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        $http = new HttpImdbExt("username");
        $http->getPage(null);
    }

    /**
     * @covers \RatingSync\HttpImdb::getPage
     * @depends testConstructorValidated
     * @expectedException \RatingSync\HttpNotFoundException
     */
    public function testCannotGetPageWithNotFound()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        $http = new HttpImdbExt("username");
        $http->getPage("/findthis");
    }

    /**
     * @covers \RatingSync\HttpImdb::getPage
     * @depends testConstructorValidated
     * @expectedException \RatingSync\HttpErrorException
     */
    public function testGetPageHttpError()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        $http = new HttpImdbExt("username");
        $http->getPage('Bad URL');
    }

    /**
     * @covers \RatingSync\HttpImdb::getPage
     * @depends testConstructorValidated
     */
    public function testGetPageAbout()
    {
        $http = new HttpImdbExt("username");
        $page = $http->getPage('/help/?general/&ref_=hlp_brws');
        $this->assertGreaterThan(0, stripos($page, "<title>Help : General Info</title>"), "Get 'About' page");

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }
    
    /**
     * @covers \RatingSync\HttpImdb::getPage
     */
    public function testGetCookies() {
        $http = new HttpImdbExt("username");
        $page = $http->getPage($http->_getLightweightUrl());
        $this->assertGreaterThan(0, strlen($http->_getSessionId()), "Session ID Cookie");
        $this->assertGreaterThan(0, strlen($http->_getCookieUu()), "uu Cookie");
        $this->assertGreaterThan(0, strlen($http->_getCookieCs()), "cs Cookie");

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers \RatingSync\HttpImdb::getPage
     */
    public function testBaseUrl()
    {
        // This is just tell us if the BaseUrl changed so we need to update some other tests
        $http = new HttpImdbExt("username");
        $this->assertEquals("http://www.imdb.com", $http->_getBaseUrl(), "/RatingSync/HttpImdb::\$baseUrl has changed, which might affect other tests");

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers \RatingSync\HttpImdb::getPage
     */
    public function testGetPageWithHeadersOnly() {
        $http = new HttpImdbExt("username");
        $headers = $http->getPage($http->_getLightweightUrl(), null, true);
        $this->assertStringEndsWith("Transfer-Encoding: chunked", rtrim($headers), "getPage() with headersOnly=true is not ending in a header");

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers \RatingSync\HttpImdb::putCookiesInRequest
     */
    public function testPutCookiesInRequestWithoutException() {
        $http = new HttpImdbExt("username");

        $ch = curl_init($http->_getBaseUrl());
        $http->_putCookiesInRequest($ch);

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers \RatingSync\HttpImdb::storeCookiesFromResponse
     * @depends testGetPageAbout
     */
    public function testStoreCookiesFromResponse() {
        $http = new HttpImdbExt("username");
        $page = $http->getPage($http->_getLightweightUrl());
        $this->assertFalse(empty($http->_getSessionId()));
        $this->assertFalse(empty($http->_getCookieUu()));
        $this->assertFalse(empty($http->_getCookieCs()));

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }
}

?>
