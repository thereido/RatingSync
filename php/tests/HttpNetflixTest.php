<?php
/**
 * HttpNetflix PHPUnit
 */
namespace RatingSync;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "HttpNetflix.php";

// Child class to expose protected members and functions
class HttpNetflixExt extends \RatingSync\HttpNetflix {
    function _getBaseUrl() { return $this->baseUrl; }
    function _getLightweightUrl() { return $this->lightweightUrl; }

    function _validateAfterConstructor() { return $this->validateAfterConstructor(); }
    function _putCookiesInRequest($ch) { return $this->putCookiesInRequest($ch); }
}

class HttpNetflixTest extends \PHPUnit_Framework_TestCase
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

    /**
     * @covers            \RatingSync\HttpNetflix::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testCannotBeConstructedFromNull()
    {$this->start(__CLASS__, __FUNCTION__);

        new HttpNetflix(null);
    }

    /**
     * @covers            \RatingSync\HttpNetflix::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testCannotBeConstructedFromEmptyUsername()
    {$this->start(__CLASS__, __FUNCTION__);

        new HttpNetflix("");
    }

    /**
     * @covers            \RatingSync\HttpNetflix::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testCannotBeConstructedFromInt()
    {$this->start(__CLASS__, __FUNCTION__);

        new HttpNetflix(1);
    }

    /**
     * @covers \RatingSync\HttpNetflix::__construct
     */
    public function testObjectCanBeConstructedFromStringValue()
    {$this->start(__CLASS__, __FUNCTION__);

        $http = new HttpNetflix("username");
    }

    /**
     * @covers \RatingSync\HttpNetflix::__construct
     * @depends testObjectCanBeConstructedFromStringValue
     */
    public function testConstructorValidated()
    {$this->start(__CLASS__, __FUNCTION__);

        $http = new HttpNetflixExt("username");
        $this->assertTrue($http->_validateAfterConstructor());
    }

    /**
     * @covers \RatingSync\HttpNetflix::getPage
     * @depends testConstructorValidated
     * @expectedException \InvalidArgumentException
     */
    public function testCannotGetPageWithNullPage()
    {$this->start(__CLASS__, __FUNCTION__);

        $http = new HttpNetflixExt("username");
        $http->getPage(null);
    }

    /**
     * @covers \RatingSync\HttpNetflix::getPage
     * @depends testConstructorValidated
     * @expectedException \RatingSync\HttpNotFoundException
     */
    public function testCannotGetPageWithNotFound()
    {$this->start(__CLASS__, __FUNCTION__);

        $http = new HttpNetflixExt("username");
        $http->getPage("/findthis");
    }

    /**
     * @covers \RatingSync\HttpNetflix::getPage
     * @depends testConstructorValidated
     * @expectedException \RatingSync\HttpErrorException
     */
    public function testGetPageHttpError()
    {$this->start(__CLASS__, __FUNCTION__);

        $http = new HttpNetflixExt("username");
        $http->getPage('Bad URL');
    }

    /**
     * @covers \RatingSync\HttpNetflix::getPage
     * @depends testConstructorValidated
     */
    public function testGetPageAbout()
    {$this->start(__CLASS__, __FUNCTION__);

        $http = new HttpNetflixExt("username");
        $page = $http->getPage('/about');
        $this->assertGreaterThan(0, stripos($page, "<title>About instantwatcher"), "Get 'About' page");
    }

    /**
     * @covers \RatingSync\HttpNetflix::getPage
     */
    public function testBaseUrl()
    {$this->start(__CLASS__, __FUNCTION__);

        // This is just tell us if the BaseUrl changed so we need to update some other tests
        $http = new HttpNetflixExt("username");
        $this->assertEquals("http://instantwatcher.com", $http->_getBaseUrl(), "/RatingSync/HttpNetflix::\$baseUrl has changed, which might affect other tests");
    }

    /**
     * @covers \RatingSync\HttpNetflix::getPage
     */
    public function testGetPageWithHeadersOnly()
    {$this->start(__CLASS__, __FUNCTION__);

        $http = new HttpNetflixExt("username");
        $headers = $http->getPage($http->_getLightweightUrl(), null, true);
        $this->assertStringEndsWith("Content-Encoding: gzip", rtrim($headers), "getPage() with headersOnly=true is not ending in a header");
    }
}

?>
