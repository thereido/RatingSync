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

use PDO;

require_once "src/Constants.php";
require_once "src/Jinni.php";
require_once "src/Imdb.php";
require_once "src/OmdbApi.php";
require_once "src/TmdbApi.php";
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
    $filename = "ratings.csv";
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
    
    $db_conn = null;
    if ($mode == Constants::DB_MODE_STANDARD) {
        $db_name = Constants::DB_DATABASE;
        if (empty($db_conn_standard)) {
            try {
                $db_conn_standard = new PDO("mysql:host=localhost;dbname=$db_name", Constants::DB_ADMIN_USER, Constants::DB_ADMIN_PWD);
                // set the PDO error mode to exception
                $db_conn_standard->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            } catch(PDOException $e) {
                logDebug("Connection failed: " . $e->getMessage());
                die("Connection failed: " . $e->getMessage());
            }

            $db_conn_standard->queryDone("USE " . $db_name);
        }
        $db_conn = $db_conn_standard;
    } else if ($mode == Constants::DB_MODE_TEST) {
        $db_name = Constants::DB_TEST_DATABASE;
        if (empty($db_conn_test)) {
            try {
                $db_conn_test = new PDO("mysql:host=localhost;dbname=$db_name", Constants::DB_ADMIN_USER, Constants::DB_ADMIN_PWD);
                // set the PDO error mode to exception
                $db_conn_test->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            } catch(PDOException $e) {
                logDebug("Connection failed: " . $e->getMessage());
                die("Connection failed: " . $e->getMessage());
            } catch(\Exception $e) {
                logDebug("Connection failed: " . $e->getMessage());
            }
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
    $message = "";
    try {
        $message = debugMessage($input, $prefix, $showTime, $printArray);
    }
    catch (\Exception $e) {
        $message = "Exception in debugMessage() " . $e->getCode() . " " . $e->getMessage();
    }

    try {
        $logfilename =  Constants::outputFilePath() . "logDebug.txt";
        $fp = fopen($logfilename, "a");
        fwrite($fp, $message);
        fclose($fp);
    }
    catch (\Exception $e) {
        // Ignore
    }
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
    $emptyResults = ['match' => null, 'parent' => null];
    if (empty($searchTerms) || !is_array($searchTerms)) {
        return $emptyResults;
    }

    if (empty($username)) {
        $username = getUsername();
    }
    $parentId = array_value_by_key("parentId", $searchTerms);
    $imdbId = array_value_by_key("imdbId", $searchTerms);
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
    $validSearchTerms = false;
    if (!empty($uniqueName)) {
        $validSearchTerms = true;
    }
    elseif (!empty($title) && !empty($year)) {
        $validSearchTerms = true;
    }
    if (!$validSearchTerms) {
        return $emptyResults;
    }
    
    $newFilm = false;
    $searchDbResult = Film::searchDb($searchTerms, $username);
    $parentFilm = $searchDbResult['parent'];
    $film = $searchDbResult['match'];

    if (empty($film)) {
        // Not in the DB. Search the API to the content source.
        $sourceApi = getMediaDbApiClient();
        if (is_null($sourceApi)) {
            return $emptyResults;
        }
        
        $nonApiSources = array(); // Not including obselete sources
        if (in_array($sourceName, $nonApiSources)) {
            // Before searching the source... remove terms specific to another source
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

        if ($score == 0) {
            // Delete rating

            $success = false;
            try {
                $success = $rating->deleteToDb($username, $filmId);
            }
            catch (\Exception $e) {
                $success = false;
            }

            if ($success) {
                $film->setRating(null, Constants::SOURCE_RATINGSYNC);
            }

        } else {
            // Set rating
            
            $existingScore = $rating->getYourScore();
            $existingRatingDate = $rating->getYourRatingDate();
            $rating->setYourScore($score);
            $rating->setYourRatingDate(new \DateTime());
            
            $success = false;
            try {
                $success = $rating->saveToDb($username, $filmId);
            }
            catch (\Exception $e) {
                logDebug($e, __CLASS__ . "::" . __FUNCTION__ . ":" . __LINE__);
                $success = false;
            }
            
            if ($success) {
                $film->setRating($rating);
            }
            
        }
    }

    return $film;
}

function getPageFooter() {
    $html = "";

    return $html;
}

function getMediaDbApiClient($sourceName = Constants::DATA_API_DEFAULT)
{
    $api = null;
    if ($sourceName == Constants::SOURCE_OMDBAPI) {
        $api = new OmdbApi();
    } elseif ($sourceName == Constants::SOURCE_TMDBAPI) {
        $api = new TmdbApi();
    }
    
    return $api;
}

function unquote($str)
{
    $length = strlen($str);
    if ( $length < 2 ) {
        return $str;
    }

    $startsWithQuote = str_starts_with($str, "'");
    $endsWithQuote = str_starts_with($str, "'");
    if ( !$startsWithQuote || !$endsWithQuote ) {
        return $str;
    }

    return substr($str, 1, $length-2);
}

?>