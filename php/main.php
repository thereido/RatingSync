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

use DateTime;
use Exception;
use PDO;

require_once "src" .DIRECTORY_SEPARATOR. "Constants.php";
require_once "src" .DIRECTORY_SEPARATOR. "Jinni.php";
require_once "src" .DIRECTORY_SEPARATOR. "Imdb.php";
require_once "src" .DIRECTORY_SEPARATOR. "OmdbApi.php";
require_once "src" .DIRECTORY_SEPARATOR. "TmdbApi.php";
require_once "src" .DIRECTORY_SEPARATOR. "Xfinity.php";
require_once "src" .DIRECTORY_SEPARATOR. "RatingSyncSite.php";
require_once "src" .DIRECTORY_SEPARATOR. "SessionUtility.php";
require_once "PDO" .DIRECTORY_SEPARATOR. "DbConn.php";
require_once "Entity" .DIRECTORY_SEPARATOR. "Managers" .DIRECTORY_SEPARATOR. "ThemeManager.php";
require_once "Entity" .DIRECTORY_SEPARATOR. "Managers" .DIRECTORY_SEPARATOR. "UserManager.php";
require_once "Export" .DIRECTORY_SEPARATOR. "ExporterFactory.php";

const FILE_WRITE_MODE = "w";

/**
 * Imports ratings from a file into the RatingSync database.
 *
 * @param string $username The RatingSync user name.
 * @param string $filePath The input file path to read from (e.g., ./output/filename).
 * @param string $importFormat The format of the input file (e.g., XML).
 *
 * @return bool True on success, false on failure.
 */
function import(string $username, string $filePath, string $importFormat): bool
{
    $ratingSyncSite = createRatingSyncSite($username);

    // Use the constant for the default format (e.g., Constants::IMPORT_FORMAT_XML).
    return $ratingSyncSite->importRatings($importFormat, $filePath);
}

/**
 * Export a user's ratings and film collections to a file on the server.
 *
 * @param string $username The username whose ratings/collections are exported.
 * @param ExportDestination $destination
 * @param ?string $collectionName (Optional) The specific collection name to export; null if not applicable.
 * @return array|false              Array of filenames exported or false in case of failure.
 */
function export( ExportDestination $destination, ?string $collectionName = "" ): array|false
{

    try {
        $exporter = ExporterFactory::create( $destination, $collectionName );
    }
    catch (Exception $e) {
        logError("Failed to create Export for destination=$destination->name, collection='$collectionName' error=$e", e: $e );
        return false;
    }

    $exportedFilenames  = $exporter->export();

    logDebug("");
    return $exportedFilenames;

}

/**
 * Create and initialize a RatingSyncSite instance.
 *
 * @param string $username The username for which the site instance is created.
 * @return RatingSyncSite The initialized RatingSyncSite instance.
 */
function createRatingSyncSite(string $username): RatingSyncSite
{
    return new RatingSyncSite($username);
}

/**
 * Writes content to a file.
 *
 * @param string $content
 * @param string $filename
 * @return false|int the number of bytes written, or FALSE on error.
 */
function writeFile(string $content, string $filename): false|int
{
    $fileHandle = fopen($filename, FILE_WRITE_MODE);
    if ($fileHandle === null) {
        return false;
    }

    $writtenBytes = fwrite($fileHandle, $content);
    fclose($fileHandle);

    return $writtenBytes;
}

/**
 * @return PDO
 */
function getDatabase(): PDO
{
    static $dbConn = null;

    if ($dbConn === null) {
        $dbConn = new DbConn();
    }

    try {
        return $dbConn->connect();
    } catch (Exception $e) {
        logError(message: "Unable to get a database connection.", e: $e);
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
        logError("Error getting a user view with username='$username'. An empty username should be okay.", e: $e);
    }

    return null;

}

function debugMessage($input, $prefix = null, $showTime = true, $printArray = null): string
{
    $time = "";
    if ($showTime) {
        $time = date_format(new DateTime(), 'Y-m-d H:i:s') . ": ";
    }

    $prefix = empty($prefix) ? "" : $prefix . ":\t";

    $suffix = "";
    if (is_array($printArray)) {
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
            } catch (Exception $e) {
                $value = "Cannot be converted to string";
            }
            $suffix .= "\t[$quote$key$quote] => $value\n";
        }
        $suffix .= "}";
    }

    $msg = $prefix . $input . $suffix;
    $visibleTime = !empty($msg) ? $time : "";

    return $visibleTime . $msg . PHP_EOL;
}

function logToFile($filename, $input, $prefix = null, $showTime = true, $printArray = null): void
{
    try {
        $message = debugMessage($input, $prefix, $showTime, $printArray);
    }
    catch (Exception $e) {
        $message = "Exception in debugMessage() " . $e->getCode() . " " . $e->getMessage();
    }

    try {
        $fp = fopen($filename, "a");
        fwrite($fp, $message);
        fclose($fp);
    }
    catch (Exception) {
        // Ignore
    }
}

function logDebug($input, $prefix = null, $showTime = true, $printArray = null): void
{
    $logfilename =  Constants::outputFilePath() . "logDebug.txt";
    logToFile($logfilename, $input, $prefix, $showTime, $printArray);
}

function logError($message, Exception $e = null, $printArray = null): void
{
    $logfilename =  Constants::outputFilePath() . "logError.txt";

    $prefix = "";
    if ( !is_null($e) ) {
        $prefix = $e->getFile() . "::" . $e->getLine();
    }

    logToFile($logfilename, $message, $prefix, true, $printArray);

    $eMsg = "";
    if ( !is_null($e) ) {
        logToFile($logfilename, "$e");
        $eMsg = "\n" . exceptionShortMsg($e);
    }

    logDebug($message . $eMsg, $prefix, true, $printArray);
}

function exceptionShortMsg( Exception $e ): string
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
function sync(string $username): void
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
function search($searchTerms, $username = null): array
{
    $emptyResults = ['match' => null, 'parent' => null];
    if (empty($searchTerms) || !is_array($searchTerms)) {
        return $emptyResults;
    }

    if (empty($username)) {
        $username = getUsername();
    }

    // searchTerm keys
    // parentId, imdbId, uniqueName, uniqueEpisode, uniqueAlt, title, year, parentYear, season, episodeNumber, episodeTitle, contentType, sourceName
    $uniqueName     = array_value_by_key("uniqueName", $searchTerms);
    $uniqueEpisode  = array_value_by_key("uniqueEpisode", $searchTerms);
    $uniqueAlt      = array_value_by_key("uniqueAlt", $searchTerms);
    $title          = array_value_by_key("title", $searchTerms);
    $year           = array_value_by_key("year", $searchTerms);
    $sourceName     = array_value_by_key("sourceName", $searchTerms);

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
            $film->setRefreshDate(new DateTime());
            $film->saveToDb($username);
        }
    }

    if (!empty($film) && !empty($film->getId()) && !empty($uniqueName) && !empty($sourceName)) {
        // Existing film - save source data from the search by this source
        $source = $film->getSource($sourceName);
        if (empty($source->getUniqueName())) {
            $source->setUniqueName($uniqueName);
            $source->setUniqueEpisode($uniqueEpisode);
            $source->setUniqueAlt($uniqueAlt);
            try {
                $source->saveFilmSourceToDb($film->getId());
            }
            catch (Exception $e) {
                logError(message: "Unable to save film source data.", e: $e);
            }
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

/**
 * See the comments at RatingSync::saveRatingToDb()
 *
 * @param int $filmId
 * @param SetRatingScoreValue $score
 * @param bool $watched
 * @param string|null $dateStr
 * @param string|null $originalDateStr
 * @param bool $forceDelete
 * @return Film|null
 */
function setRating(int $filmId, SetRatingScoreValue $score, bool $watched = true, ?string $dateStr = null, ?string $originalDateStr = null, bool $forceDelete = false) : ?Film
{
    if (empty($filmId)) {
        return null;
    }

    $username = getUsername();

    try {
        $date = $dateStr ? new DateTime($dateStr) : null;
        $originalDate = $originalDateStr ? new DateTime($originalDateStr) : null;
        Rating::saveRatingToDb($filmId, $username, $score, $watched, $date, $originalDate, $forceDelete);
    }
    catch (Exception) {
        logDebug("Unable to save rating for filmId=$filmId, username=$username", prefix: __FUNCTION__ . "() " . __FILE__ . ":" . __LINE__);
        return null;
    }

    try {
        $film = Film::getFilmFromDb($filmId, $username);
    }
    catch (Exception) {
        return null;
    }

    return $film;
}

function getPageFooter(): string
{
    return "";
}

function getMediaDbApiClient($sourceName = Constants::DATA_API_DEFAULT): TmdbApi|OmdbApi|null
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

function today(): DateTime
{
    $today = new DateTime();
    $today->setTime(hour: 0, minute: 0);

    return $today;
}
