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
require_once "src/ExportFormat.php";
require_once "src/RatingSyncSite.php";
require_once "src/SessionUtility.php";
require_once "PDO/DbConn.php";
require_once "Entity" .DIRECTORY_SEPARATOR. "Managers" .DIRECTORY_SEPARATOR. "ThemeManager.php";
require_once "Entity" .DIRECTORY_SEPARATOR. "Managers" .DIRECTORY_SEPARATOR. "UserManager.php";

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
 * Export a username's ratings to a new file.  The file
 * is written to the server.
 *
 * @param string $username Account's ratings exported
 * @param ExportFormat $format
 *
 * @return bool true/false - success/fail
 */
function export(string $username, ExportFormat $format): bool
{
    $filename   = str_replace(' ', '', Constants::SITE_NAME) . "_ExportRatings_to_" . $format->toString();
    $site       = new RatingSyncSite($username);

    return $site->exportRatings($format, $filename, true);
}

function writeFile(string $content, string $filename, string $extension, int $fileNumber = -1): string|false
{
    $addFileNumber  = $fileNumber >= 0 ? "_" . $fileNumber : "";
    $filename       = $filename . $addFileNumber . "." . $extension;
    $return         = false;

    $fp             = fopen($filename, "w");

    if ( fwrite($fp, $content) !== FALSE ) {
        $return = $filename;
    }

    fclose($fp);

    return $return;
}

function getDatabase($mode = Constants::DB_MODE)
{
    static $dbConn = new DbConn( false );

    try {

        return $dbConn->connect();

    }
    catch ( \Exception $e ) {

        logError(input: "Unable to get a database connection.", prefix: __FUNCTION__."() ".__FILE__.":".__LINE__, e: $e);
        die("DB Connection failed: " . $e->getMessage());

    }

}

function userMgr(): UserManager {

    static $userMgr = new UserManager();
    return $userMgr;

}

function themeMgr(): ThemeManager {

    static $themeMgr = new ThemeManager();
    return $themeMgr;

}

function userView( string $username = null ): UserView|null {

    try {

        $username = empty($username) ? getUsername() : $username;

        if ( ! empty($username) ) {

            return userMgr()->findViewWithUsername( $username ) ?: null;

        }
    }
    catch (Exception $e) {
        logError("Error getting a user view with username='$username'. An empty username should be okay.", prefix: __CLASS__."::".__FUNCTION__.":".__LINE__, e: $e);
    }

    return null;

}

function debugMessage($input, $prefix = null, $showTime = true, $printArray = null) {
    if (!is_null($prefix)) {
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

function logToFile($filename, $input, $prefix = null, $showTime = true, $printArray = null)
{
    $message = "";
    try {
        $message = debugMessage($input, $prefix, $showTime, $printArray);
    }
    catch (\Exception $e) {
        $message = "Exception in debugMessage() " . $e->getCode() . " " . $e->getMessage();
    }

    try {
        $fp = fopen($filename, "a");
        fwrite($fp, $message);
        fclose($fp);
    }
    catch (\Exception $e) {
        // Ignore
    }
}

function logDebug($input, $prefix = null, $showTime = true, $printArray = null): void
{
    $logfilename =  Constants::outputFilePath() . "logDebug.txt";
    logToFile($logfilename, $input, $prefix, $showTime, $printArray);
}

function logError($input, $prefix = null, $showTime = true, \Exception $e = null, $printArray = null): void
{
    $logfilename =  Constants::outputFilePath() . "logError.txt";
    logToFile($logfilename, $input . "\n$e", $prefix, $showTime, $printArray);
    logDebug($input . "\n" . exceptionShortMsg($e), $prefix, $showTime, $printArray);
}

function printDebug($input, $prefix = null, $showTime = false, $printArray = null)
{
    $message = debugMessage($input, $prefix, $showTime, $printArray);
    print($message);
}

function exceptionShortMsg( \Exception $e ): string
{
    return $e->getMessage() . " <= " . $e->getFile() . ":" . $e->getLine();
}

function defaultPrefix( string $class, string $function, int $line ): string
{
    return "$class::$function() $line";
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

/**
 * See the comments at RatingSync::saveRatingToDb()
 *
 * @param $filmId int
 * @param $score int
 * @param $dateStr string | null
 * @param $originalDateStr string | null
 * @param $forceDelete bool
 *
 * @return Film|null
 */
function setRating(int $filmId, SetRatingScoreValue $score, bool $watched = true, ?string $dateStr = null, ?string $originalDateStr = null, bool $forceDelete = false) : ?Film
{
    if (empty($filmId)) {
        return null;
    }

    $username = getUsername();

    try {
        $date = $dateStr ? new \DateTime($dateStr) : null;
        $originalDate = $originalDateStr ? new \DateTime($originalDateStr) : null;
        Rating::saveRatingToDb($filmId, $username, $score, $watched, $date, $originalDate, $forceDelete);
    }
    catch (\Exception) {
        return null;
    }

    return Film::getFilmFromDb($filmId, $username);
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
    $length = empty($str) ? 0 : strlen($str);
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

function today(): \DateTime
{
    $today = new \DateTime();
    $today->setTime(0, 0, 0, 0);

    return $today;
}
