<?php
/**
 * Imdb PHPUnit
 */
namespace RatingSync;

require_once "../src/Imdb.php";
require_once "DatabaseTest.php";
require_once "RatingSyncTestCase.php";

const TEST_IMDB_USERNAME = "ur60460017";
const FROZEN_USER_SCORE = 7.5;

class ImdbTest extends RatingSyncTestCase
{
    protected function setUp(): void
    {
        parent::setup();
        //$this->verbose = true;
    }

    public function testImdb_NoTests()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->assertTrue(true); // Making sure we made it this far
    }
}

?>
