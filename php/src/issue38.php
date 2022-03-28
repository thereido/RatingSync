<?php
namespace RatingSync;

require_once "/php/includes/DomainConstants.php";
require_once "../main.php";

/**
 * DB has film_source TMDb rows with duplicate uniqueNames.
 * That means separate titles are using the same "unique" TMDb ID.
 */

$issue = new Issue38(false);
try {
    $issue->fixIssue38();
}
catch (\Exception $e) {
    $errorMsg = $e->getMessage() . "\n";
    $errorMsg .= $e->getTraceAsString();
    echo $errorMsg;
    echo $errorMsg . "\n" >> $issue->log($errorMsg);
}

class Issue38 {
    
    private bool $dryRun;
    private string $sqlFilename;
    private string $logFilename;
    
    public function __construct($dryRun = false)
    {
        $this->dryRun = $dryRun;

        $time = (new \DateTime())->format("YmdHis");
        $this->sqlFilename = "issue38_$time.sql";
        $this->logFilename = "issue38_$time.log";
    }

    public function log($msg, $stdout = true)
    {
        try {
            $fp = fopen($this->logFilename, "a");
            fwrite($fp, $msg . "\n");
            fclose($fp);
        }
        catch (\Exception $e) {
            echo "Failure to write to " . $this->logFilename . "\n";
            $stdout = true;
        }

        if ($stdout) {
            echo $msg . "\n";
        }
    }

    /**
     * SELECT COUNT(film_id), film_source.* FROM film_source WHERE source_name='TMDb' GROUP BY uniqueName HAVING COUNT(film_id) > 1;
     *
     * Step 1 for a uniqueName with duplicates
     */
    public function fixIssue38()
    {
        $this->log("Issue 38");
        foreach ($this->getDuplicateUniqueNames() as $row) {
            $sourceName = "TMDb";
            $uniqueName = $row['uniqueName'];

            // Step 1 for a uniqueName with duplicates
            $filmSourceRows = $this->getFilmSources($uniqueName, $sourceName);

            $uniqueNamePrefix = substr($uniqueName, 0, 2);
            if ( $uniqueNamePrefix == "ep" ) {
                $this->fixEpisodes($uniqueName, $filmSourceRows);
            }
            elseif ( $uniqueNamePrefix == "mv" ) {
                $this->fixMovies($uniqueName, $filmSourceRows);
            }
            elseif ( $uniqueNamePrefix == "tv" ) {
                $this->fixShows($uniqueName, $filmSourceRows);
            }
            else {
                $this->log("ERROR: Unknown content type prefix '$uniqueNamePrefix' in $uniqueName");
                break;
            }

            $this->log("Done with uniqueName $uniqueName duplicates");
        }
    }

    private function getDuplicateUniqueNames(): array
    {
        $db = getDatabase();

        $query = "SELECT film_source.* FROM film_source WHERE source_name='TMDb' GROUP BY uniqueName HAVING COUNT(film_id) > 1";
        $result = $db->query($query);

        if ( $result == false ) {
            $errorMsg = "SQL ERROR: " . $db->errorInfo()[2] . "\n";
            $errorMsg .= $query;
            $this->log($errorMsg);
            return array();
        }

        $duplicateUniqueNames = array();

        foreach ($result->fetchAll() as $row) {
            $duplicateUniqueNames[] = $row;
        }

        return $duplicateUniqueNames;
    }

    /**
    -- Step 2 Get TV Series uniqueName, Season and Episode number
    SELECT f.id, f.parent_id, f.title, f.season, f.episodeNumber, f.episodeTitle, parentfs.uniqueName parent_un FROM film f, film_source parentfs WHERE f.id=1902 AND f.parent_id=parentfs.film_id AND parentfs.source_name='TMDb';
    -- Step 3 Get the correct episode uniqueName
    https://api.themoviedb.org/3/tv/62823/season/1/episode/1?api_key=API_KEY&language=en-US
    -- Step 4 Update the correct uniqueName
    UPDATE film_source SET uniqueName='ep1068354' WHERE film_id=1902 AND source_name='TMDb' AND uniqueName='ep1136939';
    -- Step 5 Are there extra film rows for the tv series?
    SELECT * FROM film WHERE title='Glitch' ORDER BY season, episodeNumber
     */
    private function fixEpisodes($uniqueName, $filmSourceRows): void
    {
        $sourceName = Constants::SOURCE_TMDBAPI;

        foreach ($filmSourceRows as $filmSourceRow) {

            $filmId = $filmSourceRow["film_id"];

            try {
                // This does Steps 2, 3, and  4
                $this->fixEpisode($uniqueName, $filmId, $sourceName);
            }
            catch (\Exception $e) {
                // Do nothing. The fixEpisode() method logs errors.
            }
        }
    }

    private function fixMovies($uniqueName, $filmSourceRows): void
    {
        $sourceName = Constants::SOURCE_TMDBAPI;

        //*RT FIXME
        $this->log("Movies not implemented for issue 38 yet. $uniqueName");
    }

    private function fixShows($uniqueName, $filmSourceRows): void
    {
        $sourceName = Constants::SOURCE_TMDBAPI;

        //*RT FIXME
        $this->log("Shows not implemented for issue 38 yet. $uniqueName");
    }

    private function getFilmSources($uniqueName, $sourceName): array
    {
        $db = getDatabase();
        $query = "SELECT * FROM film_source WHERE source_name='$sourceName' AND uniqueName='$uniqueName'";
        $result = $db->query($query);

        if ( $result == false ) {
            $errorMsg = "SQL ERROR: " . $db->errorInfo()[2] . "\n";
            $errorMsg .= $query;
            $this->log($errorMsg);
            return array();
        }

        $filmSourceRows = array();

        foreach ($result->fetchAll() as $row) {
            $filmSourceRows[] = $row;
        }

        return $filmSourceRows;
    }

    private function fixEpisode($uniqueName, $filmId, $sourceName)
    {

        // Step 2 Get parent (TV Series) uniqueName, Season and Episode number
        $filmRow = $this->getEpisodeRow($filmId, $sourceName);
        $parentTmdbId = $filmRow['parent_un'];
        $seasonNum = $filmRow['season'];
        $episodeNum = $filmRow['episodeNumber'];
        $seriesTitle = $filmRow['title'];

        // Step 3 Get the correct episode uniqueName from TmdbApi
        $parentTmdbIdRaw = substr($parentTmdbId, 2);
        $path = "https://api.themoviedb.org/3/tv/$parentTmdbIdRaw/season/$seasonNum/episode/$episodeNum?api_key=API_KEY&language=en-US";
        $tmdb = new TmdbApi();
        $correctUniqueNameRaw = null;
        try {
            $response = $tmdb->apiRequest($path, null, false, false);
            if ( !empty($response) ) {
                $json = json_decode($response, true);
                $correctUniqueNameRaw = array_value_by_key("id", $json);
            }
        }
        catch (\Exception $e) {
            $errorMsg = "ERROR: Exception getting correct uniqueName for filmId=$filmId, s$seasonNum e$episodeNum, duplicate uniqueName $uniqueName" . "\n";
            $errorMsg .= $e->getMessage();
            $this->log($errorMsg);
            throw $e;
        }
        if (empty($correctUniqueNameRaw)) {
            $errorMsg = "ERROR: Did not get correctUniqueNameRaw from $sourceName for parentTmdbIdRaw=$parentTmdbIdRaw, seasonNum=$seasonNum, episodeNum=$episodeNum";
            $this->log($errorMsg);
            throw new \Exception($errorMsg);
        }
        $correctUniqueName = "ep$correctUniqueNameRaw";

        // Step 4 Update with the correct uniqueName
        $query = "UPDATE film_source SET uniqueName='$correctUniqueName' WHERE film_id=$filmId AND source_name='$sourceName' AND uniqueName='$uniqueName'";

        // Step 4 Update with the correct uniqueName
        if ( ! empty($query) ) {
            $queryMsg = "'$seriesTitle' s$seasonNum e$episodeNum";
            $query .= "; -- $queryMsg";

            $success = $this->executeDbStmt($query);

            if ( ! $success ) {
                $this->log("ERROR: $query");
            }
        }
    }

    private function fixMovie($uniqueName, $filmId, $sourceName)
    {
        $this->log("Movie fix not implemented. FilmId=$filmId, $uniqueName");
        return null;
    }

    private function fixSeries($uniqueName, $filmId, $sourceName)
    {
        $this->log("TV Series fix not implemented. FilmId=$filmId, $uniqueName");
        return null;
    }

    private function getEpisodeRow($filmId, $sourceName): array
    {
        $db = getDatabase();
        $query = "SELECT f.id, f.parent_id, f.title, f.season, f.episodeNumber, f.episodeTitle, parentfs.uniqueName parent_un FROM film f, film_source parentfs WHERE f.id=$filmId AND f.parent_id=parentfs.film_id AND parentfs.source_name='$sourceName';";
        $result = $db->query($query);

        if ( $result == false ) {
            $errorMsg = "SQL ERROR: " . $db->errorInfo()[2] . "\n";
            $errorMsg .= $query;
            $this->log($errorMsg);
            return array();
        }

        if ($result->rowCount() != 1) {
            $errorMsg = "ERROR: Expected 1 film_source row for filmId=$filmId, source_name=$sourceName and a parent";
            $this->log($errorMsg);
            return array();
        }

        return $result->fetch();
    }

    private function executeDbStmt($query): bool
    {
        $db = getDatabase();

        // Write to standard output
        echo "$query\n";

        // Write to the sql file
        try {
            $fp = fopen($this->sqlFilename, "a");
            fwrite($fp, "$query\n");
            fclose($fp);
        }
        catch (\Exception $e) {
            $errorMsg = "ERROR: Failure to write to " . $this->sqlFilename;
            $this->log($errorMsg);
            return false;
        }

        // Run the statement in the DB
        if ( ! $this->dryRun ) {
            if (!$db->query($query)) {
                $errorMsg = "SQL ERROR: " . $db->errorInfo()[2] . "\n";
                $errorMsg .= $query;
                $this->log($errorMsg);
                return false;
            }
        }

        return true;
    }
    
}
