<?php
namespace RatingSync;

class ImdbTracker implements ExternalTracker
{

    static public function exportCsv(array $films): string
    {
        return self::exportRatingsCsv(films: $films);
    }

    static private function csvHeader(): string
    {
        return self::csvRatingsHeader();
    }

    static public function exportRatingsCsv(array $films): string
    {
        $csv = self::csvRatingsHeader();
        foreach ($films as $film) {
            $csv .= self::csvRatingsRows(film: $film);
        }

        return $csv;
    }

    static private function csvRatingsHeader(): string
    {
        return "Position,Const,Created,Modified,Description,Title,URL,Title Type,IMDb Rating,Runtime (mins),Year,Genres,Num Votes,Release Date,Directors,Your Rating,Date Rated" . "\n";
    }

    static private function csvRatingsRows(Film $film): string
    {

        $csv = "";

        $imdbId     = $film->getUniqueName(Constants::SOURCE_IMDB);
        $title      = $film->getTitle();
        $year       = $film->getYear();
        $titleType  = ImdbFilm::titleType( $film->getContentType() );
        $rating     = $film->getRating(Constants::SOURCE_RATINGSYNC);

        if ($imdbId == null) {
            $tmdbId = $film->getUniqueName(Constants::SOURCE_TMDBAPI);
            logDebug("IMDb ID empty for $title (tmdbId=$tmdbId)");
            return $csv;
        }

        if ( $titleType == null ) {
            logDebug("Unknown film type (" . $film->getContentType() .") for $title");
            return $csv;
        }

        if (empty($rating)) {
            logDebug("Rating empty for $title");
            return $csv;
        }

        // Write a line for this film
        $data = new ImdbFilm(imdbId: $imdbId, title: $title, year: $year, mediaType: $titleType, rating: $rating);
        $csv .= $data->csvRatingsRow();

        return $csv;
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