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
    {
        $this->writeToFile($msg, $this->logFilename, $stdout);
    }

    private function writeToFile($msg, $filename, $stdout = true)
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
        $sourceName = "TMDb";

        // Movies
        $this->log("=====================Movies\n=====================");
        foreach ($this->getDuplicateUniqueNames("mv") as $row) {

            $uniqueName = $row['uniqueName'];
            $filmSourceRows = $this->getFilmSources($uniqueName, $sourceName);
            $this->fixMovies($uniqueName, $filmSourceRows);
        }

        // TV Shows
        $this->log("\n=====================\nTV Shows\n=====================");
        foreach ($this->getDuplicateUniqueNames("tv") as $row) {

            $uniqueName = $row['uniqueName'];
            $filmSourceRows = $this->getFilmSources($uniqueName, $sourceName);
            $this->fixShows($uniqueName, $filmSourceRows);
        }

        // TV Episodes
        $this->log("\n=====================\nTV Episodes\n=====================");
        foreach ($this->getDuplicateUniqueNames("ep") as $row) {

            $uniqueName = $row['uniqueName'];
            $filmSourceRows = $this->getFilmSources($uniqueName, $sourceName);
            $this->fixEpisodes($uniqueName, $filmSourceRows);
        }
    }

    private function getDuplicateUniqueNames($uniqueNamePrefix): array
    {
        $query = "SELECT film_source.* FROM film_source WHERE source_name='TMDb' AND uniqueName LIKE '$uniqueNamePrefix%' GROUP BY uniqueName HAVING COUNT(film_id) > 1";
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

        $this->log("Done with uniqueName $uniqueName duplicates");
    }

    private function fixMovies($uniqueName, $filmSourceRows): void
    {
        $this->fixFilm($uniqueName, $filmSourceRows);
    }

    private function fixShows($uniqueName, $filmSourceRows): void
    {
        $this->fixFilm($uniqueName, $filmSourceRows, true);
    }

    private function fixFilm($uniqueName, $filmSourceRows, $isTvShow = false): void
    {
        $contentStr = $isTvShow ? "TV Show" : "Movie";
        echo "\n******** $contentStr $uniqueName **********\n";

        $filmRows = array();
        $ratingRows = array();
        $archiveRows = array();
        $filmlistRows = array();
        $querySuccess = $this->queryFilmData($filmSourceRows, $filmRows, $ratingRows, $archiveRows, $filmlistRows);

        if ( ! $querySuccess ) {
            $this->logError("Skipping uniqueName $uniqueName");
            return;
        }

        $defaultFilmId = null;
        $ratings = array();
        $ratingNum = 0;
        $prompt = "Which movie id to keep? ";
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

            foreach ($ratingRows as $ratingRow) {
                if ( strcmp($ratingRow['film_id'], $filmId) == 0 ) {
                    $date = $ratingRow['yourRatingDate'];
                    $score = $ratingRow['yourScore'];
                    $user = $ratingRow['user_name'];
                    $ratingNum++;
                    echo "\t[$ratingNum] $user scored $score on $date\n";
                    $ratings["$ratingNum"] = ["num" => $ratingNum, "filmId" => $filmId, "date" => $date, "score" => $score, "user" => $user];
                }
            }
            foreach ($archiveRows as $archiveRow) {
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

        // Ask for ratings to keep
        $ratingsToKeep = $this->getRatingsToKeep($keepFilmId, $ratings);

        // Ask to add the film to lists
        $filmlistsToKeep = $this->getFilmlistsToKeep($keepFilmId, $filmlistRows);

        // Confirmation (film, ratings, and lists)
        $confirm = $this->confirmFilm($keepFilmId, $ratingsToKeep, $ratings, $filmlistsToKeep);

        if ( strcasecmp($confirm, "y") == 0 ) {

            $this->apply($keepFilmId, $ratingsToKeep, $filmlistsToKeep, $filmlistRows, $filmRows);

        }
        else {
            readline("Okay. You can run this again if you want another try. [Any key to move on]");
        }
    }

    private function queryFilmData($filmSourceRows, &$filmRows, &$ratingRows, &$archiveRows, &$filmlistRows): bool
    {
        $filmsQuery = "SELECT film.* FROM film WHERE id IN (";
        $ratingsQuery = "SELECT rating.* FROM rating WHERE source_name='RatingSync' AND film_id IN (";
        $archiveQuery = "SELECT rating_archive.* FROM rating_archive WHERE source_name='RatingSync' AND film_id IN (";
        $filmlistQuery = "SELECT filmlist.* FROM filmlist WHERE film_id IN (";
        $comma = "";
        foreach ($filmSourceRows as $filmSourceRow) {
            $filmsQuery .= $comma . " " . $filmSourceRow["film_id"];
            $ratingsQuery .= $comma . " " . $filmSourceRow["film_id"];
            $archiveQuery .= $comma . " " . $filmSourceRow["film_id"];
            $filmlistQuery .= $comma . " " . $filmSourceRow["film_id"];
            $comma = ",";
        }
        $filmsQuery .= ") ORDER BY id desc";
        $ratingsQuery .= ") ORDER BY film_id desc, user_name, yourRatingDate";
        $archiveQuery .= ") ORDER BY film_id desc, user_name, yourRatingDate";
        $filmlistQuery .= ") ORDER BY user_name, listname, position, film_id desc";

        $filmsResult = $this->selectQuery($filmsQuery);
        if ($filmsResult == false) {
            $this->logError("Query failed: $filmsQuery");
            return false;
        }

        $ratingsResult = $this->selectQuery($ratingsQuery);
        if ($ratingsResult == false) {
            $this->logError("Query failed: $ratingsQuery");
            return false;
        }

        $archiveResult = $this->selectQuery($archiveQuery);
        if ($archiveResult == false) {
            $this->logError("Query failed: $archiveQuery");
            return false;
        }

        $filmlistResult = $this->selectQuery($filmlistQuery);
        if ($filmlistResult == false) {
            $this->logError("Query failed: $filmlistQuery");
            return false;
        }

        $filmRows = $filmsResult->fetchAll();
        $ratingRows = $ratingsResult->fetchAll();
        $archiveRows = $archiveResult->fetchAll();
        $filmlistRows = $filmlistResult->fetchAll();

        return true;
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

    // These come from multiple filmIds, but they will all end up with the keepFilmId
    // array[username] = listname
    //
    // user1
    //   Watchlist
    //   favorites
    //   Initial D franchise
    // user2
    //   Awful movies
    //   Initial D franchise
    //
    private function getFilmlistsToKeep($keepFilmId, $filmlistRows): array
    {
        $userLists = array();
        $listNum = 0;
        foreach ($filmlistRows as $filmlistRow) {
            $listNum++;
            $username = $filmlistRow["user_name"];
            $listname = $filmlistRow["listname"];

            if ( ! array_key_exists($username, $userLists) ) {
                $userLists[$username] = array();
            }

            if ( ! in_array($listname, $userLists[$username]) ) {
                $userLists[$username][] = array("num" => $listNum, "listname" => $listname);
            }
        }

        if ( count($userLists) < 1 ) {
            return array();
        }

        $listNumsStr = "";
        $comma = "";
        foreach (array_keys($userLists) as $userWithLists) {
            echo "\t  $userWithLists's lists\n";
            $lists = $userLists[$userWithLists];
            foreach ($lists as $list) {
                $listNumsStr .= $comma . $list["num"];
                $comma = ",";
                echo "\t    [" . $list["num"] . "] " . $list["listname"] . "\n";
            }
        }

        $answer = readline("Keep all lists (default) or pick them? [$listNumsStr, n(one)]  ");
        $answer = trim($answer);

        if ( strcasecmp($answer, "n") == 0 ) {
            return array();
        }
        elseif ( empty($answer) || strcasecmp($answer, "a") == 0 ) {
            return $userLists;
        }

        $numsToKeep = array();
        $explodedAnswer = explode(",", $answer);
        foreach ($explodedAnswer as $num) {
            $numsToKeep[] = trim($num);
        }

        $listsToKeep = array();
        foreach (array_keys($userLists) as $username) {
            $lists = $userLists[$username];
            foreach ($lists as $list) {
                $listNumStr = "" . $list["num"];
                if ( in_array($listNumStr, $numsToKeep) ) {
                    if ( ! array_key_exists($username, $listsToKeep) ) {
                        $listsToKeep[$username] = array();
                    }

                    $listname = $list["listname"];
                    if ( ! in_array($listname, $listsToKeep[$username]) ) {
                        $listsToKeep[$username][] = array("num" => $listNumStr, "listname" => $listname);
                    }
                }
            }
        }

        return $listsToKeep;
    }

    private function getFilmSources($uniqueName, $sourceName): array
    {
        $query = "SELECT * FROM film_source WHERE source_name='$sourceName' AND (uniqueName='$uniqueName' OR parentUniqueName)";
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
            $errorMsg .= "\n  Query: $query";
            $errorMsg .= "\n  Count: " . $result->rowCount();
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

    private function executeDbStmt($query, $stdout = false): bool
    {
        $db = getDatabase();
        $query .= ";";

        // Write to standard output
        $this->log($query, $stdout);

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

    // Confirmation (film, ratings, and lists)
    private function confirmFilm($keepFilmId, $ratingsToKeep, $ratings, $filmlistsToAdd): string
    {
        // Confirm the film to keep
        echo "\n\tFilmId $keepFilmId will be used.\n";

        // Confirm ratings
        if (count($ratingsToKeep) > 0) {
            echo "\t  Ratings to keep:\n";
            $archiveMsg = "";
            foreach ($ratingsToKeep as $keeper) {
                echo "\t\t[" . $keeper["num"] . "] $archiveMsg" . $keeper["user"] . " rated " . $keeper["score"] . " on " . $keeper["date"] . "\n";
                $archiveMsg = "archive ";
            }
        }
        elseif (count($ratings) > 0) {
            echo "\t  There are ratings, but you are not keeping any of them.\n";
        }
        else {
            echo "\t  No ratings\n";
        }

        // Confirm lists
        if (count($filmlistsToAdd) > 0) {
            echo "\t  Lists to keep:\n";
            $usernames = array_keys($filmlistsToAdd);
            foreach ($usernames as $username) {
                foreach ($filmlistsToAdd[$username] as $list) {
                    $num = $list["num"];
                    $listname = $list["listname"];
                    echo "\t\t[$num] '$listname' ($username)\n";
                }
            }
        }
        else {
            echo "\t  No lists\n";
        }

        $confirm = readline("Continue? [Y/n]  ");
        $confirm = trim($confirm);
        if ( strlen($confirm) < 1 ) {
            $confirm = "Y";
        }

        return $confirm;
    }

    private function apply($keepFilmId, $ratingsToKeep, $filmlistsToKeep, $filmlistRows, $filmRows): void
    {
        $filmIds = array();
        foreach ($filmRows as $filmRow) {
            $filmIds[] = $filmRow['id'];
        }

        $success = $this->applyRatings($keepFilmId, $ratingsToKeep);
        if ( ! $success ) {
            $this->logError("Failed applying ratings. Not continuing to apply the changes for film id $keepFilmId. May have makes some changes already.");
            return;
        }

        $success = $this->applyFilmlists($keepFilmId, $filmlistsToKeep, $filmlistRows);
        if ( ! $success ) {
            $this->logError("Failed applying filmlists. Not continuing to apply the changes for film id $keepFilmId. May have makes some changes already.");
            return;
        }

        // Re-parent episodes to the show we are keeping
        $success = $this->applyReparentEpisodes($keepFilmId, $filmRows);
        if ( ! $success ) {
            $this->logError("Failed applying re-parenting episodes. Not continuing to apply the changes for film id $keepFilmId. May have makes some changes already.");
            return;
        }

        // Delete "wrong" films
        $this->applyDeleteFilms($keepFilmId, $filmRows);
    }

    private function applyRatings($keepFilmId, $ratingsToKeep): bool
    {
        // Set ratings
        if ( ! $this->deleteAllRatings($keepFilmId) ) {
            $this->logError("deleteAllRatings($keepFilmId) Failed. No more changes for this movie.");
            return false;
        }

        if ( ! $this->addRatings($keepFilmId, $ratingsToKeep) ) {
            $errorMsg = "addRatings($keepFilmId, ratingsToKeep) Failed. No more changes for this movie.";
            foreach ($ratingsToKeep as $rtk) {
                $errorMsg .= "\n\t" . $rtk["username"] . " score=" . $rtk["score"] . "  " . $rtk["date"];
            }
            $this->logError($errorMsg);
            return false;
        }

        return true;
    }

    private function applyFilmlists($keepFilmId, $filmlistsToKeep, $filmlistRows): bool
    {
        $usernames = array_keys($filmlistsToKeep);

        foreach ($usernames as $username) {
            $lists = $filmlistsToKeep[$username];

            foreach ($lists as $list){
                $listname = $list["listname"];

                $filmIdsOnTheListForThisUser = array();
                foreach ($filmlistRows as $filmlistRow) {
                    if ($username == $filmlistRow["user_name"] && $listname == $filmlistRow["listname"]) {
                        $filmIdsOnTheListForThisUser[] = $filmlistRow["film_id"];
                    }
                }

                // FIXME
                // Make sure this works if the list has a parent (constructor wants it)
                $filmlist = new Filmlist($username, $listname);
                $filmlist->initFromDb();
                $errorFree = $filmlist->addItem($keepFilmId, true);
                if ( ! $errorFree ) {
                    $this->logError("'$listname'->addItem($keepFilmId, true) - Errors");
                }

                foreach ($filmIdsOnTheListForThisUser as $filmId) {
                    if ($keepFilmId == $filmId) {
                        continue;
                    }

                    $errorFree = $filmlist->removeItem($filmId, true);
                    if ( ! $errorFree ) {
                        $this->logError("'$listname'->removeItem($filmId, true) - Errors");
                    }
                }
            }

        }

        return true;
    }

    private function applyReparentEpisodes($keepFilmId, $filmRows): bool
    {
        $obsoleteParentIdsStr = "";
        $comma = "";
        foreach ($filmRows as $filmRow) {
            $filmId = $filmRow['id'];
            if ($filmId == $keepFilmId) {
                continue;
            }

            $obsoleteParentIdsStr .= $comma . $filmId;
            $comma = ", ";
        }

        $stmt = "UPDATE film SET parent_id=$keepFilmId WHERE parent_id IN ($obsoleteParentIdsStr)";
        $success = $this->executeDbStmt($stmt);

        if ( ! $success ) {
            $this->logError("ERROR: $stmt");
            return false;
        }

        return true;
    }

    private function applyDeleteFilms($keepFilmId, $filmRows): bool
    {
        foreach ($filmRows as $filmRow) {
            $filmId = $filmRow['id'];
            if ($filmId == $keepFilmId) {
                continue;
            }

            $title = $filmRow['title'];
            $year = $filmRow['year'];
            $this->deleteFilm($filmId, $title, $year);
        }

        return true;
    }
    
}


