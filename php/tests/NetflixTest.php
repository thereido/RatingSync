<?php
/**
 * Netflix PHPUnit
 */
namespace RatingSync;

//require_once "../src/Netflix.php";
require_once "10DatabaseTest.php";
require_once "RatingSyncTestCase.php";

const TEST_NETFLIX_USERNAME = "testnetflixuser";
const TEST_NETFLIX_UNIQUENAME = "80047396";
const TEST_NETFLIX_UNIQUEALT = "";
const TEST_NETFLIX_TITLE = "Experimenter";
const TEST_NETFLIX_YEAR = 2015;

    // Out of order

class NetflixTest extends RatingSyncTestCase
{
    public function setUp()
    {
        parent::setup();
        //$this->verbose = true;
    }

    public function testNetflix_NoTests()
    {$this->start(__CLASS__, __FUNCTION__);
    }
}

?>
