<?php
/**
 * Http PHPUnit
 */
namespace RatingSync;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "Http.php";

require_once "RatingSyncTestCase.php";

class HttpTest extends RatingSyncTestCase
{
    public function setUp()
    {
        parent::setup();
        //$this->verbose = true;
    }

    /**
     * @covers \RatingSync\Http::__construct
     */
    public function testObjectCanBeConstructed()
    {$this->start(__CLASS__, __FUNCTION__);

        $http = new Http(Constants::SOURCE_RATINGSYNC);
    }

    /**
     * @covers \RatingSync\Http::__construct
     */
    public function testCannotBeConstructedFromEmptyUsername()
    {$this->start(__CLASS__, __FUNCTION__);

        new Http(Constants::SOURCE_RATINGSYNC, "");
    }

    /**
     * @covers            \RatingSync\Http::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testCannotBeConstructedFromInvalidSiteName()
    {$this->start(__CLASS__, __FUNCTION__);

        new Http("invalid_site_name");
    }

    /**
     * @covers \RatingSync\Http::getPage
     * @depends testObjectCanBeConstructed
     * @expectedException \InvalidArgumentException
     */
    public function testCannotGetPageWithNullPage()
    {$this->start(__CLASS__, __FUNCTION__);

        $http = new Http(Constants::SOURCE_RATINGSYNC);
        $http->getPage(null);
    }

    /**
     * @covers \RatingSync\Http::getPage
     * @depends testObjectCanBeConstructed
     * @expectedException \RatingSync\HttpNotFoundException
     */
    public function testCannotGetPageWithNotFound()
    {$this->start(__CLASS__, __FUNCTION__);

        $http = new Http(Constants::SOURCE_RATINGSYNC);
        $http->getPage("/findthis");
    }

    /**
     * @covers \RatingSync\Http::getPage
     * @depends testObjectCanBeConstructed
     * @expectedException \RatingSync\HttpErrorException
     */
    public function testGetPageHttpError()
    {$this->start(__CLASS__, __FUNCTION__);

        $http = new Http(Constants::SOURCE_IMDB);
        $http->getPage('Bad URL');
    }

    /**
     * @covers \RatingSync\Http::getPage
     * @depends testObjectCanBeConstructed
     */
    public function testGetLightweightPage()
    {$this->start(__CLASS__, __FUNCTION__);

        $http = new Http(Constants::SOURCE_RATINGSYNC);
        $page = $http->getPage("/index.php");
        $this->assertGreaterThan(0, stripos($page, "<title>RatingSync</title>"), "Get a lightweight page");
    }

    /**
     * @covers \RatingSync\Http::getPage
     * @depends testObjectCanBeConstructed
     */
    public function testGetPageWithHeadersOnly()
    {$this->start(__CLASS__, __FUNCTION__);

        $http = new Http(Constants::SOURCE_RATINGSYNC);
        $headers = $http->getPage("/index.php", null, true);
        $this->assertStringStartsWith("HTTP/1.1 200 OK", rtrim($headers), "getPage() with headersOnly=true should start with this");
    }

    /**
     * @covers \RatingSync\Http::getPage
     * @depends testObjectCanBeConstructed
     */
    public function testGetPageImdb()
    {$this->start(__CLASS__, __FUNCTION__);

        $http = new Http(Constants::SOURCE_IMDB);
        $page = $http->getPage("/help/?general/&ref_=hlp_brws");
        $this->assertGreaterThan(0, stripos($page, "<title>Help : General Info</title>"), "Get IMDb 'About' page");
    }

    /**
     * @covers \RatingSync\Http::getPage
     * @depends testObjectCanBeConstructed
     */
    /*
    public function testGetPageNetflix()
    {$this->start(__CLASS__, __FUNCTION__);

        $http = new Http(Constants::SOURCE_NETFLIX);
        $page = $http->getPage("/about");
        $this->assertGreaterThan(0, stripos($page, "<title>About instantwatcher"), "Get Netflix  'About' page");
    }
    */

    /**
     * @covers \RatingSync\Http::getPage
     * @depends testObjectCanBeConstructed
     */
    /*
    public function testGetPageAmazon()
    {$this->start(__CLASS__, __FUNCTION__);

        $http = new Http(Constants::SOURCE_AMAZON);
        $page = $http->getPage("/about");
        $this->assertGreaterThan(0, stripos($page, "<title>About instantwatcher"), "Get Amazon 'About' page");
    }
    */
}

?>
