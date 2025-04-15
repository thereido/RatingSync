<?php
namespace RatingSync;

require_once __DIR__ .DIRECTORY_SEPARATOR. ".." .DIRECTORY_SEPARATOR. "main.php";
require_once __DIR__ .DIRECTORY_SEPARATOR. ".." .DIRECTORY_SEPARATOR. "src" .DIRECTORY_SEPARATOR. "ajax" .DIRECTORY_SEPARATOR. "api.php";
require_once __DIR__ .DIRECTORY_SEPARATOR. ".." .DIRECTORY_SEPARATOR. "src" .DIRECTORY_SEPARATOR. "TmdbApi.php";

$db = getDatabase();
$tmdb = new TmdbApi();
$sourceName = $tmdb->getSourceName();
$query = "SELECT id FROM film";
$result = $db->query($query);

foreach ($result->fetchAll() as $row) {
    $film = Film::getFilmFromDb($row['id']);
    $tmdbUniqueName = $film->getUniqueName($tmdb->getSourceName());
    if (empty($tmdbUniqueName)) {
        $username = null;
        $filmId = $film->getId();
        $imdbId = $film->getUniqueName(Constants::SOURCE_IMDB);
        $uniqueName = null;
        $title = $film->getTitle();
        $year = $film->getYear();
        $getFromRsDbOnly = false;
        $contentType = $film->getContentType();
        $seasonNum = $film->getSeason();
        $episodeNum = $film->getEpisodeNumber();
        $episodeTitle = $film->getEpisodeTitle();
        $parentId = $film->getParentId();
        
        $searchTerms = array();
        $searchTerms["imdbId"] = $imdbId;
        $searchTerms["uniqueName"] = $uniqueName;
        $searchTerms["title"] = $title;
        $searchTerms["year"] = $year;
        $searchTerms["sourceName"] = $sourceName;
        $searchTerms["contentType"] = $contentType;
        $searchTerms["season"] = $seasonNum;
        $searchTerms["episodeNumber"] = $episodeNum;
        $searchTerms["episodeTitle"] = $episodeTitle;
        $searchTerms["parentId"] = $parentId;

        $filmFromApi = null;
        $errorMsg = "";
        $itemMsg = "$filmId $contentType $imdbId $title ($year)";
        if ($contentType == Film::CONTENT_TV_EPISODE) {
            $itemMsg .= " S$seasonNum E$episodeNum '$episodeTitle'";
        }

        // Get film from TMDb with getFilmApi()
        try {
            $filmFromApi = ApiHandler::getFilmApi($username, $filmId, $imdbId, $uniqueName, $getFromRsDbOnly, $contentType, $seasonNum, $episodeNum, $parentId);
        }
        catch (\Exception $e) {
            $errorMsg = "Failed 'get' for $itemMsg";
            if ($e->getCode() == 429) {
                $errorMsg .= " HTTP Error 429 Too many results in a given amount of time";
                logTryAgain($errorMsg);
            }
            else {
                $errorMsg .= " " . $e->getMessage();
            }
        }

        // If there is no result use search()
        if (empty($filmFromApi) || empty($filmFromApi->getUniqueName($tmdb->getSourceName()))) {
            try {
                $filmFromApi = $tmdb->getFilmBySearch($searchTerms);
                $errorMsg = "";
            }
            catch (\Exception $e) {
                if (empty($errorMsg)) {
                    $errorMsg = "Failed search for $itemMsg";
                }
                if ($e->getCode() == 429) {
                    $msg .= " HTTP Error 429 Too many results in a given amount of time";
                    logTryAgain($msg);
                }
                else {
                    $errorMsg .= " " . $e->getMessage();
                }
            }
        }

        if (!empty($filmFromApi) && !empty($filmFromApi->getUniqueName($tmdb->getSourceName()))) {
            $tmdbUniqueName = $filmFromApi->getUniqueName($tmdb->getSourceName());
            $film->saveToDb();
            $msg = "Saved $tmdbUniqueName " . $filmFromApi->getTitle() . " " . $filmFromApi->getEpisodeTitle();
            echo $msg . "\n";
            logInfo($msg);
        } else {
            if (empty($errorMsg)) {
                $errorMsg = "Not found for $itemMsg";
            }
        }
        
        if (!empty($errorMsg)) {
            echo $errorMsg . "\n";
            logError($errorMsg, prefix: __FILE__.":".__LINE__, e: $e);
        }
        
        sleep(1);
    }
}

function logInfo($message)
{
    $logfilename =  Constants::outputFilePath() . "logInfo.txt";
    logLine($message, $logfilename);
}

function logTryAgain($message)
{
    $logfilename =  Constants::outputFilePath() . "logError429.txt";
    logLine($message, $logfilename);
}

function logLine($message, $filename)
{
    $fp = fopen($filename, "a");
    fwrite($fp, $message . PHP_EOL);
    fclose($fp);
}

?>