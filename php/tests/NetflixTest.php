<?php
/**
 * Netflix PHPUnit
 */
namespace RatingSync;

//require_once "../src/Netflix.php";
require_once "DatabaseTest.php";
require_once "RatingSyncTestCase.php";

const TEST_NETFLIX_USERNAME = "testnetflixuser";
const TEST_NETFLIX_UNIQUENAME = "80047396";
const TEST_NETFLIX_UNIQUEALT = "";
const TEST_NETFLIX_TITLE = "Experimenter";
const TEST_NETFLIX_YEAR = 2015;

    // Out of order

class NetflixTest extends RatingSyncTestCase
{
    protected function setUp(): void
    {
        parent::setup();
        //$this->verbose = true;
    }

    public function testNetflix_NoTests()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->assertTrue(true); // Making sure we made it this far
    }
}

?>
