<?php
/**
 * Amazon PHPUnit
 */
namespace RatingSync;

//require_once "../src/Amazon.php";
require_once "RatingSyncTestCase.php";

const TEST_AMAZON_USERNAME = "testamazonuser";
const TEST_AMAZON_UNIQUENAME = "3526";
const TEST_AMAZON_UNIQUEALT = "";
const TEST_AMAZON_TITLE = "Absentia";
const TEST_AMAZON_YEAR = 2012;

    // Out of order

class AmazonTest extends RatingSyncTestCase
{
    protected function setUp(): void
    {
        parent::setup();
        //$this->verbose = true;
    }

    public function testAmazon_NoTests()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->assertTrue(true); // Making sure we made it this far
    }
}

?>
