<?php

namespace RatingSync;

use PDO;

require_once __DIR__.DIRECTORY_SEPARATOR. ".." .DIRECTORY_SEPARATOR. "src" .DIRECTORY_SEPARATOR. "Constants.php";

abstract class EntityManager
{

    static protected PDO $db;

    protected function getDb()
    {

        if ( ! empty( self::$db ) ) {
            return self::$db;
        }

        $mode = Constants::DB_MODE;

        if ($mode == Constants::DB_MODE_STANDARD) {

            $db_name = Constants::DB_DATABASE;

        } else if ($mode == Constants::DB_MODE_TEST) {

            $db_name = Constants::DB_TEST_DATABASE;

        }

        try {

            self::$db = new PDO( "mysql:host=localhost;dbname=$db_name", Constants::DB_ADMIN_USER, Constants::DB_ADMIN_PWD );
            self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

        } catch(PDOException $e) {

            logError("Connection failed: " . $e->getMessage());
            die("Connection failed: " . $e->getMessage());

        } catch(\Exception $e) {

            logError("Connection failed: " . $e->getMessage());

        }

        return self::$db;

    }

}