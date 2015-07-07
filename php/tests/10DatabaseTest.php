<?php
/**
 * Temp PHPUnit
 */
namespace RatingSync;

require_once "../main.php";

class DatabaseTest extends \PHPUnit_Framework_TestCase
{
    public $debug;
    public $lastTestTime;

    public function setUp()
    {
        $this->debug = false;
        $this->lastTestTime = new \DateTime();
    }

    public function testResetDb()
    {
        exec("mysql --user=rs_user --password=password ratingsync_db < ..\..\sql\db_tables_drop.sql");
        exec("mysql --user=rs_user --password=password ratingsync_db < ..\..\sql\db_tables_create.sql");
        exec("mysql --user=rs_user --password=password ratingsync_db < ..\..\sql\db_insert_initial.sql");

        $db = getDatabase();
        $result = $db->query("SELECT count(id) as count FROM film");
        $row = $result->fetch_assoc();
        $this->assertEquals(0, $row["count"], "Film rows (should be none)");
        $result = $db->query("SELECT count(user_source.source_name) as count FROM user, source, user_source WHERE user.username='testratingsync' AND user.username=user_source.user_name AND user_source.source_name=source.name");
        $row = $result->fetch_assoc();
        $this->assertEquals(3, $row["count"], "Test user with sources");

        if ($this->debug) { echo "\n" . __CLASS__ . "::" . __FUNCTION__ . " " . $this->lastTestTime->diff(date_create())->format('%s secs') . " "; }
    }

}
?>
