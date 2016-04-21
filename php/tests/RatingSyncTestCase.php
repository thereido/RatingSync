<?php
/**
 * RatingSync PHPUnit base TestCase class
 */
namespace RatingSync;

class RatingSyncTestCase extends \PHPUnit_Framework_TestCase
{
    public $verbose;
    public $timer;

    public function setUp()
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

    public function tearDown()
    {
        $format = " %s secs";
        $interval = $this->timer->diff(date_create());
        if ($interval->i > 0) $format = " %i mins" . $format;
        if ($interval->h > 0) $format = " %h hours" . $format;
        if ($this->verbose) echo $this->timer->diff(date_create())->format($format) . "\n";
    }

    public function testBaseClass()
    {$this->start(__CLASS__, __FUNCTION__);
    }
}

?>