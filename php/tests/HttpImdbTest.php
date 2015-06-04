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
    /**
     * @covers            \RatingSync\HttpImdb::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testCannotBeConstructedFromNull()
    {
        new HttpImdb(null);
    }

    /**
     * @covers            \RatingSync\HttpImdb::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testCannotBeConstructedFromEmptyUsername()
    {
        new HttpImdb("");
    }

    /**
     * @covers            \RatingSync\HttpImdb::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testCannotBeConstructedFromInt()
    {
        new HttpImdb(1);
    }

    /**
     * @covers \RatingSync\HttpImdb::__construct
     */
    public function testObjectCanBeConstructedFromStringValue()
    {
        $http = new HttpImdb("username");
        return $http;
    }

    /**
     * @covers \RatingSync\HttpImdb::__construct
     * @depends testObjectCanBeConstructedFromStringValue
     */
    public function testConstructorValidated()
    {
        $http = new HttpImdbExt("username");
        $this->assertTrue($http->_validateAfterConstructor());
    }

    /**
     * @covers \RatingSync\HttpImdb::getPage
     * @depends testConstructorValidated
     * @expectedException \InvalidArgumentException
     */
    public function testCannotGetPageWithNullPage()
    {
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
    }

    /**
     * @covers \RatingSync\HttpImdb::getPage
     */
    public function testBaseUrl()
    {
        // This is just tell us if the BaseUrl changed so we need to update some other tests
        $http = new HttpImdbExt("username");
        $this->assertEquals("http://www.imdb.com", $http->_getBaseUrl(), "/RatingSync/HttpImdb::\$baseUrl has changed, which might affect other tests");
    }

    /**
     * @covers \RatingSync\HttpImdb::getPage
     */
    public function testGetPageWithHeadersOnly() {
        $http = new HttpImdbExt("username");
        $headers = $http->getPage($http->_getLightweightUrl(), null, true);
        $this->assertStringEndsWith("Transfer-Encoding: chunked", rtrim($headers), "getPage() with headersOnly=true is not ending in a header");
    }

    /**
     * @covers \RatingSync\HttpImdb::putCookiesInRequest
     */
    public function testPutCookiesInRequestWithoutException() {
        $http = new HttpImdbExt("username");

        $ch = curl_init($http->_getBaseUrl());
        $http->_putCookiesInRequest($ch);

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

    }
}

?>
