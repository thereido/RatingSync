<?php
/**
 * Xfinity PHPUnit
 */
namespace RatingSync;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "Xfinity.php";
require_once "DatabaseTest.php";
require_once "RatingSyncTestCase.php";

const TEST_XFINITY_USERNAME = "testratingsync";

const TEST_XFINITY_UNIQUENAME = "4776771327619264112"; // Vacation
const TEST_XFINITY_UNIQUEALT = "Vacation";
const TEST_XFINITY_TITLE = "Vacation";
const TEST_XFINITY_YEAR = 2015;

const TEST_XFINITY_UNIQUENAME_TV = "5771920406804707112"; // Grimm
const TEST_XFINITY_UNIQUEALT_TV = "Grimm";
const TEST_XFINITY_UNIQUEEPISODE_TV = "4612535181645549112"; // Season 5 Episode 11
const TEST_XFINITY_TITLE_TV = "Grimm";
const TEST_XFINITY_YEAR_TV = 2011;

class XfinityTest extends RatingSyncTestCase
{
    protected function setUp(): void
    {
        parent::setup();
        //$this->verbose = true;
    }

    public function testXfinity_NoTests()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->assertTrue(true); // Making sure we made it this far
    }
}

?>
