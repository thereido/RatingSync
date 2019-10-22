<?php
/**
 * RatingSync PHPUnit base TestCase class
 */
namespace RatingSync;

use PHPUnit\Framework\TestCase;

class RatingSyncTestCase extends TestCase
{
    public $verbose;
    public $timer;

    protected function setUp(): void
    {
        $this->verbose = true;
        $this->timer = new \DateTime();
    }

    public function start($className, $functionName)
    {
        if ($this->verbose) {
            echo " $className::$functionName ";
            $this->timer = new \DateTime();
        }
    }

    protected function tearDown(): void
    {
        $format = " %s secs";
        $interval = $this->timer->diff(date_create());
        if ($interval->i > 0) $format = " %i mins" . $format;
        if ($interval->h > 0) $format = " %h hours" . $format;
        if ($this->verbose) echo $this->timer->diff(date_create())->format($format) . "\n";
    }

    public function testBaseClass()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->assertTrue(true); // Making sure we made it this far
    }
}

?>