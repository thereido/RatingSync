<?php
/**
 * Temp PHPUnit
 */
namespace RatingSync;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "main.php";

class DatabaseTest extends \PHPUnit_Framework_TestCase
{
    public static function resetDb()
    {
        $command = "mysql --user=" . Constants::DB_ADMIN_USER . " --password=pwd " . Constants::DB_TEST_DATABASE;
        exec("$command < ..\..\sql\db_tables_drop.sql");
        exec("$command < ..\..\sql\db_tables_create.sql");
        exec("$command < ..\..\sql\db_insert_initial.sql");
    }

    public function testResetDb()
    {
        self::resetDb();

        $db = getDatabase(Constants::DB_MODE_TEST);
        $result = $db->query("SELECT count(id) as count FROM film");
        $row = $result->fetch_assoc();
        $this->assertEquals(0, $row["count"], "Film rows (should be none)");
        $result = $db->query("SELECT count(user_source.source_name) as count FROM user, source, user_source WHERE user.username='testratingsync' AND user.username=user_source.user_name AND user_source.source_name=source.name");
        $row = $result->fetch_assoc();
        $this->assertEquals(3, $row["count"], "Test user with sources");
    }

}
?>
