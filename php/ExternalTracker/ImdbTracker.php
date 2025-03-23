<?php
namespace RatingSync;

class ImdbTracker// implements ExternalTracker
{

    static public function exportRatingsCsv(RatingSyncSite $site, string $username): string
    {

        logDebug("Beginning export of ratings for $username in IMDb format");

        $exportedRatingCount    = 0;
        $exportedFilmCount      = 0;
        $processedFilmIds       = [];
        $csv                    = self::csvRatingsHeader();

        $ratings                = $site->getRatings();

        foreach ($ratings as $rating) {

            if ( in_array($rating->getFilmId(), $processedFilmIds) ) {
                continue;
            }

            try {
                $film = Film::getFilmFromDb($rating->getFilmId(), username: $username);
            } catch (\Exception $e) {
                logDebug("Error getting film from db. FilmId=" . $rating->getFilmId());
                continue;
            }

            $exportedThisFilm = self::csvRatingRow(film: $film, csv: $csv);
            $processedFilmIds[] = $film->getId();

            $exportedRatingCount    += $exportedThisFilm ? 1 : 0;
            $exportedFilmCount      += $exportedThisFilm ? 1 : 0;

        }

        logDebug("Exported a total of $exportedRatingCount ratings for $exportedFilmCount/" . count($processedFilmIds) . " films the user rated\n");

        return $csv;
    }

    static private function csvRatingsHeader(): string
    {
        return "Position,Const,Created,Modified,Description,Title,URL,Title Type,IMDb Rating,Runtime (mins),Year,Genres,Num Votes,Release Date,Directors,Your Rating,Date Rated" . "\n";
    }

    static private function csvRatingRow(Film $film, string &$csv): bool
    {

        $imdbId     = $film->getUniqueName(Constants::SOURCE_IMDB);
        $title      = $film->getTitle();
        $year       = $film->getYear();
        $rating     = $film->getRating(Constants::SOURCE_RATINGSYNC);

        if ($imdbId == null) {
            logDebug("Skipping IMDB ID empty for $title");
            return 0;
        }

        $contentType = $film->getContentType();
        if ( $contentType == null ) {
            logDebug("Skipping unknown film type (" . $film->getContentType() .") for $title");
            return false;
        }
        $titleType  = ImdbFilm::titleType( $contentType );

        if (empty($rating)) {
            logDebug("Skipping rating empty for $title");
            return false;
        }

        // Write a line for this film
        $data = new ImdbFilm(imdbId: $imdbId, title: $title, year: $year, mediaType: $titleType, rating: $rating);
        $csv .= $data->csvRatingsRow();

        return true;
    }

}

class ImdbFilm
{
    // IMDb v3
    //
    // Position,Const,Created,Modified,Description,Title,URL,Title Type,IMDb Rating,Runtime (mins),Year,Genres,Num Votes,Release Date,Directors,Your Rating,Date Rated
    // 1,tt3416532,2017-05-16,2017-05-16,,A Monster Calls,https://www.imdb.com/title/tt3416532/,movie,7.5,108,2016,"Animation, Drama, Fantasy",64368,2016-09-09,J.A. Bayona,,
    // 2,tt1790809,2017-05-16,2017-05-16,,Pirates of the Caribbean: Dead Men Tell No Tales,https://www.imdb.com/title/tt1790809/,movie,6.6,129,2017,"Action, Adventure, Fantasy",205053,2017-05-11,"Espen Sandberg, Joachim R¯nning",,
    // 3,tt4972582,2017-05-16,2017-05-16,,Split,https://www.imdb.com/title/tt4972582/,movie,7.3,117,2016,"Horror, Thriller",281897,2016-09-26,M. Night Shyamalan,,
    // 4,tt3783958,2017-05-16,2017-05-16,,La La Land,https://www.imdb.com/title/tt3783958/,movie,8.1,128,2016,"Comedy, Drama, Music, Musical, Romance",390132,2016-08-31,Damien Chazelle,,
    // 5,tt3183660,2017-05-16,2017-05-16,,Fantastic Beasts and Where to Find Them,https://www.imdb.com/title/tt3183660/,movie,7.4,133,2016,"Adventure, Family, Fantasy",314374,2016-11-08,David Yates,,

    private string  $imdbId;
    private string  $title;
    private int     $year;
    private string  $mediaType;
    private Rating  $rating;

    public function __construct(string $imdbId, string $title, int $year, string $mediaType, Rating $rating)
    {
        $this->imdbId = $imdbId;
        $this->title = $title;
        $this->year = $year;
        $this->mediaType = $mediaType;
        $this->rating = $rating;
    }

    public function csvRatingsRow(): string
    {
        $score      = $this->rating->getYourScore();
        $ratingDate = $this->rating->getYourRatingDate();
        $dateStr    = $ratingDate->format("Y-m-d");

        return ",$this->imdbId,,,,\"$this->title\",,$this->mediaType,,,$this->year,,,,,$score,$dateStr" . "\n";
    }

    static public function titleType( string $contentType ): string | null
    {
        return match ($contentType) {
            Film::CONTENT_FILM => "Movie",
            Film::CONTENT_TV_SERIES => "TV Series",
            Film::CONTENT_TV_EPISODE => "TV Episode",
            default => null,
        };
    }

}