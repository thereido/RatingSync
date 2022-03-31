<?php
namespace RatingSync;

require_once "/php/includes/DomainConstants.php";
require_once "../main.php";

/**
 * DB has film_source TMDb rows with duplicate uniqueNames.
 * That means separate titles are using the same "unique" TMDb ID.
 */

$issue = new Issue38(true);
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
    private string $errorLogFilename;
    
    public function __construct($dryRun = false)
    {
        $this->dryRun = $dryRun;

        $now = new \DateTime();
        $now->setTimezone(new \DateTimeZone("AMERICA/NEW_YORK"));
        $time = $now->format("Ymd_Hi.s");
        $this->sqlFilename = "/home/tui/RatingSync/issue38/issue38_$time.sql";
        $this->logFilename = "/home/tui/RatingSync/issue38/issue38_$time.log";
        $this->errorLogFilename = "/home/tui/RatingSync/issue38/issue38_$time.error.log";
    }

    public function logError($msg, $stdout = true)
    {
        $this->log($msg, $stdout);
        $this->writeToFile($msg, $this->errorLogFilename, false);
    }

    public function log($msg, $stdout = true)
    {;
        $this->writeToFile($msg, $this->logFilename);
    }

    public function writeToFile($msg, $filename, $stdout = true)
    {
        try {
            $fp = fopen($filename, "a");
            fwrite($fp, $msg . "\n");
            fclose($fp);
        }
        catch (\Exception $e) {
            echo "Failure to write to " . $filename . "\n";
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
                $this->logError("ERROR: Unknown content type prefix '$uniqueNamePrefix' in $uniqueName", true, false);
                break;
            }

//*RT*            $this->log("Done with uniqueName $uniqueName duplicates");
        }
    }

    private function getDuplicateUniqueNames(): array
    {
        $query = "SELECT film_source.* FROM film_source WHERE source_name='TMDb' GROUP BY uniqueName HAVING COUNT(film_id) > 1";
        $result = $this->selectQuery($query);

        if ( $result == false ) {
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
        echo "\n******** Movie $uniqueName **********\n";

        $filmsQuery = "SELECT film.* FROM film WHERE id IN (";
        $ratingsQuery = "SELECT rating.* FROM rating WHERE source_name='RatingSync' AND film_id IN (";
        $archiveQuery = "SELECT rating_archive.* FROM rating_archive WHERE source_name='RatingSync' AND film_id IN (";
        $comma = "";
        foreach ($filmSourceRows as $filmSourceRow) {
            $filmsQuery .= $comma . " " . $filmSourceRow["film_id"];
            $ratingsQuery .= $comma . " " . $filmSourceRow["film_id"];
            $archiveQuery .= $comma . " " . $filmSourceRow["film_id"];
            $comma = ",";
        }
        $filmsQuery .= ") ORDER BY id desc";
        $ratingsQuery .= ") ORDER BY film_id desc, user_name, yourRatingDate";
        $archiveQuery .= ") ORDER BY film_id desc, user_name, yourRatingDate";

        $filmsResult = $this->selectQuery($filmsQuery);

        if ($filmsResult == false) {
            $this->log("Query failed: $filmsQuery");
        }

        $defaultFilmId = null;
        $ratings = array();
        $currentRatings = array();
        $ratingNum = 0;
        $prompt = "Which movie id to keep? ";
        $filmRows = $filmsResult->fetchAll();
//*RT*        foreach ($filmsResult->fetchAll() as $filmRow) {
        foreach ($filmRows as $filmRow) {
            $filmId = $filmRow['id'];
            $title = $filmRow['title'];
            $year = $filmRow['year'];

            if (empty($defaultFilmId)) {
                $defaultFilmId = $filmId;
                $prompt .= "[*$defaultFilmId*";
            }
            else {
                $prompt .= ", $filmId";
            }

            echo "  $filmId $title ($year)\n";

            $ratingsResult = $this->selectQuery($ratingsQuery);
            $archiveResult = $this->selectQuery($archiveQuery);

            if ($ratingsResult == false || $archiveResult == false) {
                $this->log("One or both of these queries failed:\n$ratingsQuery\n$archiveQuery");
            }

            foreach ($ratingsResult->fetchAll() as $ratingRow) {
                if ( strcmp($ratingRow['film_id'], $filmId) == 0 ) {
                    $date = $ratingRow['yourRatingDate'];
                    $score = $ratingRow['yourScore'];
                    $user = $ratingRow['user_name'];
                    $ratingNum++;
                    echo "\t[$ratingNum] $user scored $score on $date\n";
                    $ratings["$ratingNum"] = ["num" => $ratingNum, "filmId" => $filmId, "date" => $date, "score" => $score, "user" => $user];
                    $currentRatings[$user] = $ratings["$ratingNum"];
                }
            }
            foreach ($archiveResult->fetchAll() as $archiveRow) {
                if ( strcmp($archiveRow['film_id'], $filmId) == 0 ) {
                    $date = $archiveRow['yourRatingDate'];
                    $score = $archiveRow['yourScore'];
                    $user = $archiveRow['user_name'];
                    $ratingNum++;
                    echo "\t[$ratingNum] archived - $user scored $score on $date\n";
                    $ratings["$ratingNum"] = ["num" => $ratingNum, "filmId" => $filmId, "date" => $date, "score" => $score, "user" => $user];
                }
            }
        }
        $prompt .= ", n]  ";

        $keepFilmId = readline($prompt);
        if ( empty($keepFilmId) ) {
            $keepFilmId = $defaultFilmId;
        }
        elseif ( strcasecmp($keepFilmId, "n") == 0 ) {
            $this->log("No changes for $uniqueName");
            return;
        }

        //$ratingsToMove = $this->promptForRatingsToMove($ratings, $keepFilmId, $currentRatings);
        $ratingsToKeep = $this->getRatingsToKeep($keepFilmId, $ratings);

        // Need to add the keeper film to any filmlists?
        //// Skipping this because there are no cases in production

        // Confirm the film to keep, the ratings to move, and the filmlists film with be added
        echo "\n\tFilmId $keepFilmId will be used.\n";
        if (count($ratingsToKeep) > 0) {
            echo "\t  Ratings to keep:\n";
            $archiveMsg = "";
            foreach ($ratingsToKeep as $keeper) {
                echo "\t  [" . $keeper["num"] . "] $archiveMsg" . $keeper["user"] . " rated " . $keeper["score"] . " on " . $keeper["date"] . "\n";
                $archiveMsg = "archive ";
            }
        }
        elseif (count($ratings) > 0) {
            echo "\t  There are ratings, but you are not keeping any of them.\n";
        }
        else {
            echo "\t  No ratings\n";
        }
/*RT*
        if (count($ratingsToMove) > 0) {
            echo "\t  The following ratings will be moved:\n";
            foreach ($ratingsToMove as $rtm) {
                $username = $rtm["user"];
                $currentRating = $this->getCurrentRating($username, $currentRatings);
                $ratingStatus = "Archive";
                if ( empty($currentRating) || $currentRating["num"] == $rtm["num"] ) {
                    $ratingStatus = "NEW CURRENT";
                }
                echo "\t$ratingStatus [" . $rtm["num"] . "] " . $rtm["user"] . " rated " . $rtm["score"] . " on " . $rtm["date"] . "\n";
            }
        } else {
            echo "\t  No ratings will be moved.\n";
        }
*RT*/
        $confirm = readline("Continue? [Y/n/r]  ");
        $confirm = trim($confirm);
        if ( strlen($confirm) < 1 ) {
            $confirm = "Y";
        }

        if ( strcasecmp($confirm, "y") == 0 ) {

            // Set ratings
            if ( ! $this->deleteAllRatings($keepFilmId) ) {
                $this->logError("deleteAllRatings($keepFilmId) Failed. No more changes for this movie.");
                return;
            }
            if ( ! $this->addRatings($keepFilmId, $ratingsToKeep) ) {
                $errorMsg = "addRatings($keepFilmId, ratingsToKeep) Failed. No more changes for this movie.";
                foreach ($ratingsToKeep as $rtk) {
                    $errorMsg .= "\n\t" . $rtk["username"] . " score=" . $rtk["score"] . "  " . $rtk["date"];
                }
                $this->logError($errorMsg);
                return;
            }

/*RT*
            // Move ratings
            // INSERT INTO rating_archive (user_name, source_name, film_id, yourScore, yourRatingDate, suggestedScore) VALUES ('thereido', 'RatingSync', 346, 8, '2014-01-21', NULL);
            // UPDATE rating SET yourScore=8, yourRatingDate='2020-11-27' WHERE film_id=346 AND user_name= AND source_name='RatingSync';
            foreach ($ratingsToKeep as $keeper) {


                $isArchive = true;
                $username = $keeper["user"];
                if ( array_key_exists($username, $currentRatings) && $keeper["num"] == $currentRatings[$username]["num"] ) {
                    $this->archiveCurrentRating($username, $keepFilmId);
                    $isArchive = false;
                }

                $this->moveRating($keeper, $keepFilmId, $isArchive);
            }
*RT*/

            // Add filmlist entries
            //// Skipping this because there are no cases in production

            // Delete "wrong" films
            foreach ($filmRows as $filmRow) {
                $filmId = $filmRow['id'];
                if ($filmId == $keepFilmId) {
                    continue;
                }

                $title = $filmRow['title'];
                $year = $filmRow['year'];
                $this->deleteFilm($filmId, $title, $year);
            }
        }
        elseif ( strcasecmp($confirm, "n") == 0 ) {
            readline("Change ratings. NOT IMPLEMENTED yet. No changes made for this movie. [Any key to move on]");
        }
        else {
            readline("Okay. You can run this again if you want another try. [Any key to move on]");
        }
    }

    private function promptForRatingsToMove($ratings, $ignoreFilmId, &$currentRatings): array
    {
        $answer = "";
        $eligibleRatingStr = "";
        $eligibleRatings = array();
        $comma = "";
        foreach ($ratings as $rating) {
            if ($rating["filmId"] != $ignoreFilmId) {
                $numStr = $rating["num"] . "";
                $eligibleRatingStr .= $comma . $rating["num"];
                $comma = ", ";

                $eligibleRatings[$numStr] = $rating;
            }
        }
        if ( ! empty($eligibleRatingStr) ) {
            $answer = readline("Any ratings to move to film $ignoreFilmId? [$eligibleRatingStr]  ");
            $answer = trim($answer);
        }

        $ratingsToMove = array();
        if ( ! empty($answer) ) {
            $ratingNums = explode(",", $answer);
            foreach ($ratingNums as $num) {
                $numStr = trim($num);
                if (array_key_exists($numStr, $eligibleRatings)) {

                    $ratingsToMove[$numStr] = $eligibleRatings[$numStr];

                    $chosenRating = $ratingsToMove[$numStr];
                    $username = $chosenRating["user"];
                    $currentRating = $this->getCurrentRating($username, $currentRatings);

                    if ( empty($currentRating) || $chosenRating["date"] > $currentRating["date"] ) {
                        $currentRatings[$username] = $chosenRating;
                    }
                }
            }
        }

        return $ratingsToMove;
    }

    private function getCurrentRating($username, $currentRatings): array | null
    {
        if ( array_key_exists($username, $currentRatings) ) {
            return $currentRatings[$username];
        }

        return null;
    }

    private function fixShows($uniqueName, $filmSourceRows): void
    {
        $sourceName = Constants::SOURCE_TMDBAPI;

        //*RT FIXME
        $this->log("Shows not implemented for issue 38 yet. $uniqueName");
    }

    private function getRatingsToKeep($keepFilmId, $ratings): array
    {
        $ratingsByDate = array();
        $keepers = array();

        foreach ($ratings as $rating) {
            $ratingsByDate[$rating["date"]] = $rating;
        }

        krsort($ratingsByDate);

        $previousDate = null;
        foreach ($ratingsByDate as $rating) {
            if ( $rating["date"] != $previousDate ) {
                $num = $rating["num"];
                $keepers["$num"] = $rating;
            }
        }

        return $keepers;
    }

    private function getFilmSources($uniqueName, $sourceName): array
    {
        $query = "SELECT * FROM film_source WHERE source_name='$sourceName' AND uniqueName='$uniqueName'";
        $result = $this->selectQuery($query);

        if ( $result == false ) {
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
            $this->logError($errorMsg);
            throw $e;
        }
        if (empty($correctUniqueNameRaw)) {
            $errorMsg = "ERROR: Did not get correctUniqueNameRaw from $sourceName for parentTmdbIdRaw=$parentTmdbIdRaw, seasonNum=$seasonNum, episodeNum=$episodeNum";
            $this->logError($errorMsg);
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
                $this->logError("ERROR: $query");
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
        $query = "SELECT f.id, f.parent_id, f.title, f.season, f.episodeNumber, f.episodeTitle, parentfs.uniqueName parent_un FROM film f, film_source parentfs WHERE f.id=$filmId AND f.parent_id=parentfs.film_id AND parentfs.source_name='$sourceName';";
        $result = $this->selectQuery($query);

        if ( $result == false ) {
            return array();
        }

        if ($result->rowCount() != 1) {
            $errorMsg = "ERROR: Expected 1 film_source row for filmId=$filmId, source_name=$sourceName and a parent";
            $this->logError($errorMsg);
            return array();
        }

        return $result->fetch();
    }

    private function selectQuery($query): \PDOStatement | bool
    {
        $db = getDatabase();
        $result = $db->query($query . ";");

        if ( $result == false ) {
            $errorMsg = "SQL ERROR: " . $db->errorInfo()[2] . "\n";
            $errorMsg .= $query;
            $this->logError($errorMsg);
            return false;
        }

        return $result;
    }

    private function executeDbStmt($query): bool
    {
        $db = getDatabase();
        $query .= ";";

        // Write to standard output
        $this->log($query);

        // Write to the sql file
        try {
            $fp = fopen($this->sqlFilename, "a");
            fwrite($fp, "$query\n");
            fclose($fp);
        }
        catch (\Exception $e) {
            $errorMsg = "ERROR: Failure to write to " . $this->sqlFilename;
            $this->logError($errorMsg);
            return false;
        }

        // Run the statement in the DB
        if ( ! $this->dryRun ) {
            if (!$db->query($query)) {
                $errorMsg = "SQL ERROR: " . $db->errorInfo()[2] . "\n";
                $errorMsg .= $query;
                $this->logError($errorMsg);
                return false;
            }
        }

        return true;
    }

    private function deleteFilm($filmId, $title, $year)
    {
        $tables = array();
        $tables[] = "credit";
        $tables[] = "film_genre";
        $tables[] = "rating_archive";
        $tables[] = "rating";
        $tables[] = "film_source";
        $tables[] = "filmlist";

        $stmt = "";
        foreach ($tables as $tableName) {
            $stmt .= "DELETE FROM $tableName WHERE film_id=$filmId; -- '$title' $year\n";
        }

        $stmt .= "DELETE FROM film WHERE id=$filmId; -- '$title' $year\n-- ";

        $this->executeDbStmt($stmt);
    }

    private function deleteAllRatings($filmId): bool
    {
        $stmt = "DELETE FROM rating WHERE film_id=$filmId AND source_name='RATINGSYNC'";
        $stmt .= ";\n";
        $stmt .= "DELETE FROM rating_archive WHERE film_id=$filmId AND source_name='RATINGSYNC'";
        if ( ! $this->executeDbStmt($stmt) ) {
            return false;
        }

        return true;
    }

    private function addRatings($keepFilmId, $ratings): bool
    {
        $users = array();
        $stmt = null;
        foreach ($ratings as $rating) {
            $username = $rating["user"];
            $score = $rating["score"];
            $date = $rating["date"];

            if ( empty($stmt) ) {
                $stmt = "INSERT INTO rating ";
                $users[] = $username;
            }
            else {
                $stmt .= ";\n";

                if ( in_array($username, $users) ) {
                    $stmt .= "INSERT INTO rating_archive ";
                }
                else {
                    $stmt .= "INSERT INTO rating ";
                    $users[] = $username;
                }
            }

            $stmt .= "(user_name, source_name, film_id, yourScore, yourRatingDate, suggestedScore) VALUES ('$username', 'RatingSync', $keepFilmId, '$score', '$date', NULL)";
        }

        if ( ! empty($stmt) ) {
            if ( ! $this->executeDbStmt($stmt) ) {
                return false;
            }
        }

        return true;
    }

    private function archiveCurrentRating($username, $keepFilmId): bool
    {
        // Get the current rating
        $query = "SELECT * FROM rating WHERE user_name='$username' AND film_id=$keepFilmId AND source_name='RATINGSYNC'";
        $result = $this->selectQuery($query);
        if ( $result == false || $result->rowCount() != 1 ) {
            return false;
        }
        $currentRatingRow = $result->fetch();

        // Insert the current rating into the archive table
        $score = $currentRatingRow['yourScore'];
        $date = $currentRatingRow['yourRatingDate'];
        $stmt = "INSERT INTO rating_archive (user_name, source_name, film_id, yourScore, yourRatingDate, suggestedScore) VALUES ('$username', 'RatingSync', $keepFilmId, '$score', '$date', NULL)";
        if ( ! $this->executeDbStmt($stmt) ) {
            return false;
        }

        // Delete the current rating
        $stmt = "DELETE FROM rating WHERE user_name='$username' AND film_id=$keepFilmId AND source_name='RATINGSYNC'";
        if ( ! $this->executeDbStmt($stmt) ) {
            return false;
        }

        return true;
    }

    private function moveRating($rating, $keepFilmId, $isArchive): bool
    {
        $table = $isArchive ? "rating_archive" : "rating";

        $score = $rating['score'];
        $date = $rating['date'];
        $oldFilmId = $rating['filmId'];
        $stmt = "UPDATE $table SET film_id=$keepFilmId WHERE film_id=$oldFilmId AND yourScore=$score AND yourRatingDate='$date' AND source_name='RatingSync'";
        if ( ! $this->executeDbStmt($stmt) ) {
            return false;
        }

        return true;
    }
    
}
