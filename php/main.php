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

require_once __DIR__."/Jinni.php";

/**
 * Export ratings from $source and write to a new file.  The file
   is written to the server.
 *
 * @param string $username Account's ratings exported
 * @param string $source   IMDb, Jinni, etc \RatingSync\Rating::SOURCE_***
 * @param string $format   XML
 * @param string $filename Output file name written to ./output/$filename
 *
 * @return bool true/false - success/fail
 */
function export($username, $source, $format, $filename)
{
    if ($filename == null || strlen($filename) == 0) {
        $filename = "ratings.xml";
    }

    $jinni = new \RatingSync\Jinni($username);
    return $jinni->exportRatings($format, $filename, true);;
}

?>
