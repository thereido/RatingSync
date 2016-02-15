<?php
/**
 * Functions the HTML pages can use while trying to keep the PHP code
   in to a minimum in those HTML pages.
 *
 * @package RatingSync
 * @author  thereido <github@bagowine.com>
 * @link    https://github.com/thereido/RatingSync
 */
namespace RatingSync;

require_once "Jinni.php";
require_once "Imdb.php";
require_once "RatingSyncSite.php";

/**
 * Import ratings from a file to the database
 *
 * @param string $username RatingSync user
 * @param string $filename Input file name read from ./output/$filename
 * @param string $format   XML
 *
 * @return bool true/false - success/fail
 */
function import($username, $filename, $format)
{
    $site = new RatingSyncSite($username);
    return $site->importRatings($format, $filename, $username);
}

/**
 * Export ratings from $source and write to a new file.  The file
   is written to the server.
 *
 * @param string $username Account's ratings exported
 * @param string $source   IMDb, Jinni, etc Constants::SOURCE_***
 * @param string $format   XML
 * @param string $filename Output file name written to ./output/$filename
 *
 * @return bool true/false - success/fail
 */
function export($username, $source, $format)
{
    $filename = "ratings.xml";
    $site = null;

    if ($source == "jinni") {
        $site = new Jinni($username);
    } elseif ($source == "imdb") {
        $site = new Imdb($username);
    } else {
        return "";
    }

    if ($site->exportRatings($format, $filename, true)) {
        return $filename;
    } else {
        return "";
    }
}

function getDatabase($mode = Constants::DB_MODE)
{
    static $db_conn_standard;
    static $db_conn_test;

    if (! ($mode == Constants::DB_MODE_STANDARD || $mode == Constants::DB_MODE_TEST) ) {
        throw new \InvalidArgumentException('Must set database mode');
    }
    
    $db_conn;
    if ($mode == Constants::DB_MODE_STANDARD) {
        $db_name = Constants::DB_DATABASE;
        if (empty($db_conn_standard)) {
            $db_conn_standard = new \mysqli("localhost", Constants::DB_ADMIN_USER, "pwd");

            // Check connection
            if ($db_conn_standard->connect_error) {
                die("Connection failed: " . $db_conn->connect_error);
            }
            $db_conn_standard->query("USE " . $db_name);
        }
        $db_conn = $db_conn_standard;
    } else if ($mode == Constants::DB_MODE_TEST) {
        $db_name = Constants::DB_TEST_DATABASE;
        if (empty($db_conn_test)) {
            $db_conn_test = new \mysqli("localhost", Constants::DB_ADMIN_USER, "pwd");

            // Check connection
            if ($db_conn_test->connect_error) {
                die("Connection failed: " . $db_conn->connect_error);
            }
            $db_conn_test->query("USE " . $db_name);
        }
        $db_conn = $db_conn_test;
    }


    return $db_conn;
}

function logDebug($input, $prefix = null, $showTime = true)
{
    if (!empty($prefix)) {
        $time = "";
        if ($showTime) {
            $time = date_format(new \DateTime(), 'Y-m-d H:i:s');
        }
        $prefix = $time . " " . $prefix . ":\t";
    }
    $logfilename =  __DIR__ . DIRECTORY_SEPARATOR . ".." . Constants::outputFilePath() . "logDebug.txt";
    $fp = fopen($logfilename, "a");
    fwrite($fp, $prefix . $input . PHP_EOL);
    fclose($fp);
}

?>
