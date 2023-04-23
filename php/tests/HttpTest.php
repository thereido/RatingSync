<?php
/**
 * Http PHPUnit
 */
namespace RatingSync;

require_once __DIR__ .DIRECTORY_SEPARATOR.  ".." .DIRECTORY_SEPARATOR. "src" .DIRECTORY_SEPARATOR. "Http.php";
require_once __DIR__ .DIRECTORY_SEPARATOR.  ".." .DIRECTORY_SEPARATOR. "main.php";

require_once "RatingSyncTestCase.php";

class HttpTest extends RatingSyncTestCase
{
    protected function setUp(): void
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

        $this->assertTrue(true); // Making sure we made it this far
    }

    /**
     * @covers \RatingSync\Http::__construct
     */
    public function testCannotBeConstructedFromEmptyUsername()
    {$this->start(__CLASS__, __FUNCTION__);

        new Http(Constants::SOURCE_RATINGSYNC, "");

        $this->assertTrue(true); // Making sure we made it this far
    }

    /**
     * @covers            \RatingSync\Http::__construct
     */
    public function testCannotBeConstructedFromInvalidSiteName()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        new Http("invalid_site_name");
    }

    /**
     * @covers \RatingSync\Http::getPage
     * @depends testObjectCanBeConstructed
     */
    public function testCannotGetPageWithNullPage()
    {$this->start(__CLASS__, __FUNCTION__);
        
        $this->expectException(\InvalidArgumentException::class);

        $http = new Http(Constants::SOURCE_RATINGSYNC);
        $http->getPage(null);
    }

    /**
     * @covers \RatingSync\Http::getPage
     * @depends testObjectCanBeConstructed
     */
    public function testCannotGetPageWithNotFound()
    {$this->start(__CLASS__, __FUNCTION__);
        
        $this->expectException(\RatingSync\HttpNotFoundException::class);

        $http = new Http(Constants::SOURCE_RATINGSYNC);
        $http->getPage("/findthis");
    }

    /**
     * @covers \RatingSync\Http::getPage
     * @depends testObjectCanBeConstructed
     */
    public function testGetPageHttpError()
    {$this->start(__CLASS__, __FUNCTION__);
        
        $this->expectException(\RatingSync\HttpErrorException::class);

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
    public function testGetPageTmdbApi()
    {$this->start(__CLASS__, __FUNCTION__);

        $http = new Http(Constants::SOURCE_TMDBAPI);
        $json = $http->getPage("/movie/109445?api_key=" . Constants::TMDB_API_KEY);
        $result = json_decode($json, true);
        $this->assertFalse(empty($result), "Result should not be empty");
        $this->assertEquals("Frozen", $result["title"], "Result 'title' should be 'Frozen'");
    }

    /**
     * @covers \RatingSync\Http::getPage
     * @depends testObjectCanBeConstructed
     */
    /*
    public function testGetPageOmdbApi()
    {$this->start(__CLASS__, __FUNCTION__);

        $http = new Http(Constants::SOURCE_OMDBAPI);
        $json = $http->getPage("&i=tt2294629");
        $result = json_decode($json, true);
        $this->assertFalse(empty($result), "Result should not be empty");
        $this->assertEquals("True", $result["Response"], "Result 'Response' should be 'True'");
    }
    */

    /**
     * @covers \RatingSync\Http::getPage
     * @depends testObjectCanBeConstructed
     */
    /*
    public function testGetPageImdb()
    {$this->start(__CLASS__, __FUNCTION__);

        $http = new Http(Constants::SOURCE_IMDB);
        $page = $http->getPage("/conditions");
        $this->assertGreaterThan(0, stripos($page, "<title>Conditions of Use - IMDb</title>"), "Get IMDb 'Conditions' page");
    }
    */

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
