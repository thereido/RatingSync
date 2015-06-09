<?php
/**
 * Http PHPUnit
 */
namespace RatingSync;

require_once "../Http.php";

class HttpChild extends \RatingSync\Http {
    public function __construct($username)
    {
        parent::__construct($username);
        $this->baseUrl = "http://www.jinni.com";
        $this->lightweightUrl = "/info/about.html";
    }

    function _validateAfterConstructor() { return $this->validateAfterConstructor(); }

    function _getSessionId() { return $this->sessionId; }

    public function searchSuggestions($searchStr, $type = null) {}
}

class HttpTest extends \PHPUnit_Framework_TestCase
{
    public $debug;
    public $lastTestTime;

    public function setUp()
    {
        $this->debug = true;
        $this->lastTestTime = new \DateTime();
    }

    /**
     * @covers            \RatingSync\Http::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testCannotBeConstructedFromNull()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        new HttpChild(null);
    }

    /**
     * @covers            \RatingSync\Http::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testCannotBeConstructedFromEmptyUsername()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        new HttpChild("");
    }

    /**
     * @covers            \RatingSync\Http::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testCannotBeConstructedFromInt()
    {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        new HttpChild(1);
    }

    /**
     * @covers \RatingSync\Http::__construct
     */
    public function testObjectCanBeConstructedFromStringValue()
    {
        $http = new HttpChild("username");

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers \RatingSync\Http::__construct
     * @depends testObjectCanBeConstructedFromStringValue
     */
    public function testConstructorValidated()
    {
        $http = new HttpChild("username");
        $this->assertTrue($http->_validateAfterConstructor());

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers \RatingSync\Http::getPage
     * @depends testConstructorValidated
     * @expectedException \InvalidArgumentException
     */
    public function testCannotGetPageWithNullPage() {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        $http = new HttpChild("username");
        $http->getPage(null);
    }

    /**
     * @covers \RatingSync\Http::getPage
     * @depends testConstructorValidated
     * @expectedException \RatingSync\HttpNotFoundException
     */
    public function testCannotGetPageWithNotFound() {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        $http = new HttpChild("username");
        $http->getPage("/findthis");
    }

    /**
     * @covers \RatingSync\Http::getPage
     * @depends testConstructorValidated
     * @expectedException \RatingSync\HttpErrorException
     */
    public function testGetPageHttpError() {
        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " "; }
        $http = new HttpChild("username");
        $http->getPage('Bad URL');
    }

    /**
     * @covers \RatingSync\Http::getPage
     * @depends testConstructorValidated
     */
    public function testGetPageAbout() {
        $http = new HttpChild("username");
        $page = $http->getPage('/info/about.html');
        $this->assertGreaterThan(0, stripos($page, "About Jinni</title>"), "Get 'About' page");

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

    /**
     * @covers \RatingSync\Http::getPage
     * @depends testConstructorValidated
     */
    public function testGetPageWithHeadersOnly() {
        $http = new HttpChild("username");
        $headers = $http->getPage('/info/about.html', null, true);
        $this->assertStringEndsWith("Connection: keep-alive", rtrim($headers), "getPage() with headersOnly=true is not ending in a header");

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }
}

?>
