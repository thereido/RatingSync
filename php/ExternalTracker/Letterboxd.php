<?php
namespace RatingSync;

class Letterboxd// implements ExternalTracker
{
    // https://letterboxd.com/about/importing-data/
    // tmdbID,imdbID,Title,Year,Rating10,WatchedDate,Rewatch

    private const EXPORT_BATCH_SIZE = 1000;

    static public function exportRatingsCsv(RatingSyncSite $site, string $username): array
    {

        logDebug("Beginning export of ratings for $username in Letterboxd format");

        $site->setSort(field: RatingSortField::date);
        $site->setSortDirection(direction: SqlSortDirection::descending);
        //$site->setListFilter($filterListsArr);
        $site->setContentTypeFilter([Film::CONTENT_TV_SERIES, Film::CONTENT_TV_EPISODE]);

        $batches            = [];
        $processedFilmIds   = [];
        $progressCount      = 0;
        $batchCount         = 0;
        $batchCsv           = Letterboxd::csvRatingsHeader();

        $ratings            = $site->getRatings();
        $lastRating         = $ratings[count($ratings)-1];

        foreach ($ratings as $rating) {

            if ( in_array($rating->getFilmId(), $processedFilmIds) ) {
                continue;
            }

            try {
                $film = Film::getFilmFromDb($rating->getFilmId(), username: $username);
            } catch (\Exception $e) {
                logDebug("Error getting film from db. FilmId=" . $rating->getFilmId() . " batchCount=$batchCount");
                continue;
            }

            if ($film->getContentType() != Film::CONTENT_FILM) {
                logDebug("Skipping non-movie. FilmId=" . $rating->getFilmId() . " batchCount=$batchCount");
                continue;
            }

            $batchCsv           .= Letterboxd::csvRatingsRows(film: $film);
            $batchCount         += 1 + count($film->getSource(Constants::SOURCE_RATINGSYNC)->getArchive());
            $processedFilmIds[] = $film->getId();

            if ( $batchCount >= self::EXPORT_BATCH_SIZE || $rating->equals($lastRating) ) {

                $batches[]      = $batchCsv;
                $progressCount  += $batchCount;

                $batchCsv           = Letterboxd::csvRatingsHeader();
                $batchCount         = 0;
            }

        }

        logDebug("Exported a total of $progressCount of " . count($ratings) . " ratings in " . count($batches) . " batches\n");

        return $batches;
    }

    static private function csvRatingsHeader(): string
    {
        return "tmdbID,imdbID,Title,Year,Rating10,WatchedDate,Rewatch" . "\n";
    }

    static private function csvRatingsRows(Film $film): string
    {
        if ($film->getContentType() != Film::CONTENT_FILM) {
            return "";
        }

        $csv = "";

        $title                  = $film->getTitle();
        $year                   = $film->getYear();
        $tmdbId                 = $film->getUniqueName(Constants::SOURCE_TMDBAPI);
        $imdbId                 = $film->getUniqueName(Constants::SOURCE_IMDB) ?? "";
        $rating                 = $film->getRating(Constants::SOURCE_RATINGSYNC);
        $inactiveRatings        = $film->getSource(Constants::SOURCE_RATINGSYNC)->getArchive();
        $currentRatingRewatch   = count($inactiveRatings) > 0;

        if ( $tmdbId == null ) {
            logDebug("TMDB ID empty for id=" . $film->getId() . " $title ($year)");
            return $csv;
        }

        if ( count($inactiveRatings) > 0 ) {

            $sortCallback = function (Rating $a, Rating $b) { return Rating::compareByRatingDate($a, $b); };
            uasort( $inactiveRatings, $sortCallback );
            $sortedKeys = array_keys($inactiveRatings);
            $firstInactiveRatingDate = $inactiveRatings[$sortedKeys[0]]->getYourRatingDate();

            foreach ($inactiveRatings as $inactiveRating) {
                $rewatch = $inactiveRating->getYourRatingDate() > $firstInactiveRatingDate;

                // Write a line for an inactive rating for this film
                $data = new LetterboxdFilm(tmdbId: $tmdbId, imdbId: $imdbId, title: $title, year: $year, rating: $inactiveRating, rewatch: $rewatch);
                $csv .= $data->csvRatingsRow();
            }
        }

        // Write a line for the active rating for this film
        $data = new LetterboxdFilm(tmdbId: $tmdbId, imdbId: $imdbId, title: $title, year: $year, rating: $rating, rewatch: $currentRatingRewatch);
        $csv .= $data->csvRatingsRow();

        return $csv;
    }

}

class LetterboxdFilm
{
    private string  $tmdbId;
    private string  $imdbId;
    private string  $title;
    private int     $year;
    private Rating  $rating;
    private bool    $rewatch;

    public function __construct(string $tmdbId, string $imdbId, string $title, int $year, Rating $rating, bool $rewatch)
    {
        $this->tmdbId   = substr($tmdbId, offset: 2);
        $this->imdbId   = $imdbId;
        $this->title    = $title;
        $this->year     = $year;
        $this->rating   = $rating;
        $this->rewatch  = $rewatch;
    }

    public function csvRatingsRow(): string
    {
        $score      = $this->rating->getYourScore();
        $ratingDate = $this->rating->getYourRatingDate();
        $dateStr    = $ratingDate->format("Y-m-d");
        $rewatchStr = $this->rewatch ? "true" : "false";

        return "$this->tmdbId,$this->imdbId,\"$this->title\",$this->year,$score,$dateStr,$rewatchStr" . "\n";
    }

}