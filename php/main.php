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

require_once "src/Constants.php";
require_once "src/Jinni.php";
require_once "src/Imdb.php";
require_once "src/OmdbApi.php";
require_once "src/Xfinity.php";
require_once "src/RatingSyncSite.php";
require_once "src/SessionUtility.php";

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

    if ($source == "ratingsync") {
        $site = new RatingSyncSite($username);
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
            $db_conn_standard = new \mysqli("localhost", Constants::DB_ADMIN_USER, Constants::DB_ADMIN_PWD);

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
            $db_conn_test = new \mysqli("localhost", Constants::DB_ADMIN_USER, Constants::DB_ADMIN_PWD);

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

function debugMessage($input, $prefix = null, $showTime = true, $printArray = null) {
    if (!empty($prefix)) {
        $time = "";
        if ($showTime) {
            $time = date_format(new \DateTime(), 'Y-m-d H:i:s');
        }
        $prefix = $time . " " . $prefix . ":\t";
    }
    $suffix = "";
    if ($printArray !== null && is_array($printArray)) {
        $keys = array_keys($printArray);
        $length = count($keys);
        $suffix .= "\narray($length) {\n";
        foreach ($keys as $key) {
            $quote = '"';
            if (is_numeric($key)) {
                $quote = "";
            }
            $value = $printArray[$key];
            try {
                $value = "" . $value;
            } catch (\Exception $e) {
                $value = "Cannot be converted to string";
            }
            $suffix .= "\t[$quote$key$quote] => $value\n";
        }
        $suffix .= "}";
    }

    return $prefix . $input . $suffix . PHP_EOL;
}

function logDebug($input, $prefix = null, $showTime = true, $printArray = null)
{
    $message = debugMessage($input, $prefix, $showTime, $printArray);

    $logfilename =  Constants::outputFilePath() . "logDebug.txt";
    $fp = fopen($logfilename, "a");
    fwrite($fp, $message);
    fclose($fp);
}

function printDebug($input, $prefix = null, $showTime = false, $printArray = null)
{
    $message = debugMessage($input, $prefix, $showTime, $printArray);
    print($message);
}

/**
 * Sync ratings. Bring user's ratings in the db from all sources
 * into sync. 
 *
 * @param string $username RatingSync user
 */
function sync($username)
{
    $site = new RatingSyncSite($username);
    $site->syncRatings($username);
}

function getUsername() {
    return SessionUtility::getUsername();
}

/**
 * Looking for one specific film (and it's parent)
 */
function search($searchTerms, $username = null)
{
    if (empty($searchTerms) || !is_array($searchTerms)) {
        return null;
    }

    if (empty($username)) {
        $username = getUsername();
    }
    $uniqueName = array_value_by_key("uniqueName", $searchTerms);
    $uniqueEpisode = array_value_by_key("uniqueEpisode", $searchTerms);
    $uniqueAlt = array_value_by_key("uniqueAlt", $searchTerms);
    $title = array_value_by_key("title", $searchTerms);
    $year = array_value_by_key("year", $searchTerms);
    $parentYear = array_value_by_key("parentYear", $searchTerms);
    $season = array_value_by_key("season", $searchTerms);
    $episodeNumber = array_value_by_key("episodeNumber", $searchTerms);
    $episodeTitle = array_value_by_key("episodeTitle", $searchTerms);
    $contentType = array_value_by_key("contentType", $searchTerms);
    $sourceName = array_value_by_key("sourceName", $searchTerms);

    // Check searchTerms
    if (empty($uniqueName) && (empty($title) || empty($year))) {
        return null;
    }
    
    $newFilm = false;
    $searchDbResult = Film::searchDb($searchTerms, $username);
    $parentFilm = $searchDbResult['parent'];
    $film = $searchDbResult['match'];

    if (empty($film)) {
        // Not in the DB. Search the API to the content source.
        $sourceApi = new OmdbApi();
        
        if ($sourceName != Constants::SOURCE_RATINGSYNC && $sourceName != Constants::SOURCE_IMDB && $sourceName != Constants::SOURCE_OMDBAPI) {
            // Before searching the source API... remove terms specific to another source
            $searchTerms['uniqueName'] = null;
            $searchTerms['uniqueEpisode'] = null;
            $searchTerms['uniqueAlt'] = null;
        }
        
        $film = $sourceApi->getFilmBySearch($searchTerms);

        if (!empty($film)) {
            $film->setRefreshDate(new \DateTime());
            $film->saveToDb($username);
            $newFilm = true;
        }
    }
    
    if (!empty($film) && !empty($film->getId()) && !empty($uniqueName) && !empty($sourceName)) {
        // Existing film - save source data from the search by this source
        $source = $film->getSource($sourceName);
        if (empty($source->getUniqueName())) {
            $source->setUniqueName($uniqueName);
            $source->setUniqueEpisode($uniqueEpisode);
            $source->setUniqueAlt($uniqueAlt);
            $source->saveFilmSourceToDb($film->getId());
        }
    }
    
    $resultFilms = array();
    $resultFilms['match'] = $film;
    $resultFilms['parent'] = $parentFilm;
    return $resultFilms;
}

function array_value_by_key($key, $a, $nullValue = null) {
    if (!empty($a) && array_key_exists($key, $a) && $a[$key] !== $nullValue) {
        return $a[$key];
    } else {
        return null;
    }
}

function setRating($filmId, $score)
{
    if (empty($filmId)) {
        return null;
    }

    $username = getUsername();
    $film = Film::getFilmFromDb($filmId, $username);
    if (!empty($film)) {
        $rating = $film->getRating(Constants::SOURCE_RATINGSYNC);
        $existingScore = $rating->getYourScore();
        $existingRatingDate = $rating->getYourRatingDate();
        $rating->setYourScore($score);
        $rating->setYourRatingDate(new \DateTime());
        $film->setRating($rating);
        $success = false;
        try {
            $success = $film->saveToDb($username);
        }
        catch (\Exception $e) {
            $success = false;
        }

        if (!$success) {
            $rating->setYourScore($existingScore);
            $rating->setYourRatingDate($existingRatingDate);
            $film->setRating($rating);
        }
    }

    return $film;
}

function getPageFooter() {
    $html = "";
    $html .= "<p/>";
    $html .= "<footer class=\"footer container-fluid\">";
    $html .= "</footer>";

    return $html;
}

?>