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

/**
 * Export ratings from $source and write to a new file.  The file
   is written to the server.
 *
 * @param string $username Account's ratings exported
 * @param string $source   IMDb, Jinni, etc \RatingSync\Constants::SOURCE_***
 * @param string $format   XML
 * @param string $filename Output file name written to ./output/$filename
 *
 * @return bool true/false - success/fail
 */
function export($username, $source, $format)
{
    $filename = "ratings.xml";

    $jinni = new \RatingSync\Jinni($username);
    if ($jinni->exportRatings($format, $filename, true)) {
        return $filename;
    } else {
        return "";
    }
}

?>
