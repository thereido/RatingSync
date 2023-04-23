<?php
/**
 * Jinni PHPUnit
 */
namespace RatingSync;

require_once __DIR__ .DIRECTORY_SEPARATOR. ".." .DIRECTORY_SEPARATOR. "src" .DIRECTORY_SEPARATOR. "Jinni.php";
require_once "RatingSyncTestCase.php";

const TEST_JINNI_USERNAME = "testratingsync";

class JinniTest extends RatingSyncTestCase
{
    protected function setUp(): void
    {
        parent::setup();
        //$this->verbose = true;
    }

    public function testJinni_NoTests()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->assertTrue(true); // Making sure we made it this far
    }
}

?>
