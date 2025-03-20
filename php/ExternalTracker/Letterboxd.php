<?php
namespace RatingSync;

class Letterboxd implements ExternalTracker
{
    // https://letterboxd.com/about/importing-data/
    // tmdbID,imdbID,Title,Year,Rating10,WatchedDate,Rewatch

    static public function exportCsv(array $films): string
    {
        return self::exportFilmsCsv(films: $films);
    }

    static private function csvHeader(): string
    {
        return self::csvRatingsHeader();
    }

    static public function exportFilmsCsv(array $films = null): string
    {
        if ($films == null) {
            $site = new RatingSyncSite();
        }
        $csv = Letterboxd::csvHeader();
        foreach ($films as $film) {
            if ($film->getContentType() == Film::CONTENT_FILM) {
                $csv .= Letterboxd::csvRatingsRows(film: $film);
            }
        }

        return $csv;
    }

    static public function exportRatingsCsv(SiteRatings $site): string
    {
        $site->setSort(RatingSyncSite::SORT_RATING_DATE);
        $site->setSortDirection(RatingSyncSite::SORTDIR_DESC);
        //$site->setListFilter($filterListsArr);
        //$site->setGenreFilter($filterGenresArr);
        //$site->setGenreFilterMatchAny($filterGenresMatchAny);
        $site->setContentTypeFilter([Film::CONTENT_TV_SERIES, Film::CONTENT_TV_EPISODE]);

        $films = $site->getRatings(limitPages: 10, beginPage: 1, details: false, refreshCache: Constants::USE_CACHE_NEVER);

        $csv = Letterboxd::csvRatingsHeader();

        foreach ($films as $film) {
            if ($film->getContentType() == Film::CONTENT_FILM) {
                $csv .= Letterboxd::csvRatingsRows(film: $film);
            }
        }

        return $csv;
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