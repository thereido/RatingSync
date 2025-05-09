<?php
namespace RatingSync;

use Exception;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "Export" . DIRECTORY_SEPARATOR . "ExportEntry.php";

class ImdbAdapter extends ExternalAdapter
{

    /**
     * @param ExportCollectionType $collectionType
     * @throws Exception
     */
    public function __construct( ExportCollectionType $collectionType )
    {
        parent::__construct( $collectionType, __CLASS__ );
    }

    /**
     * @return string
     */
    public function getCsvHeader(): string
    {
        return "Position,Const,Created,Modified,Description,Title,URL,Title Type,IMDb Rating,Runtime (mins),Year,Genres,Num Votes,Release Date,Directors,Your Rating,Date Rated" . PHP_EOL;
    }

    /**
     * @return ExportDestination
     */
    protected static function exportDestination(): ExportDestination
    {
        return ExportDestination::IMDB;
    }

    /**
     * @param Film $film
     * @return array
     */
    protected function validateExportableExternalFilm( Film $film ): array
    {
        return ImdbFilm::validateExternalFilm( $film );
    }

    /**
     * @param Film $film
     * @param Rating|null $earliestRating
     * @return ExternalFilm
     * @throws Exception
     */
    public function createExternalFilm(Film $film, Rating|null $earliestRating = null ): ExternalFilm
    {
        return new ImdbFilm( $film );
    }
}


class ImdbFilm extends ExternalFilm
{

    /**
     * @param Film $film
     * @throws Exception
     */
    public function __construct( Film $film )
    {
        if ( $film->getUniqueName( source: Constants::SOURCE_IMDB ) == null ) {
            throw new Exception(ExportDestination::IMDB->value . " id must be provided");
        }

        $this->film = $film;
    }

    /**
     * @param Film $film
     * @return array Array<string>: An empty return is a valid film. An invalid film gets one or more reasons.
     */
    static public function validateExternalFilm( Film $film ): array
    {
        $problems = [];

        if ( $film->getUniqueName( source: Constants::SOURCE_IMDB ) == null ) {
            $problems[] = "Empty IMDb id";
        }

        return $problems;
    }

    /**
     * @param Rating|null $rating
     * @return ExportEntry
     * @throws Exception
     */
    public function exportEntry( Rating|null $rating = null ): ExportEntry
    {
        // IMDb v3
        //
        // Position,Const,Created,Modified,Description,Title,URL,Title Type,IMDb Rating,Runtime (mins),Year,Genres,Num Votes,Release Date,Directors,Your Rating,Date Rated
        // 1,tt3416532,2017-05-16,2017-05-16,,A Monster Calls,https://www.imdb.com/title/tt3416532/,movie,7.5,108,2016,"Animation, Drama, Fantasy",64368,2016-09-09,J.A. Bayona,,
        // 2,tt1790809,2017-05-16,2017-05-16,,Pirates of the Caribbean: Dead Men Tell No Tales,https://www.imdb.com/title/tt1790809/,movie,6.6,129,2017,"Action, Adventure, Fantasy",205053,2017-05-11,"Espen Sandberg, Joachim R¯nning",,
        // 3,tt4972582,2017-05-16,2017-05-16,,Split,https://www.imdb.com/title/tt4972582/,movie,7.3,117,2016,"Horror, Thriller",281897,2016-09-26,M. Night Shyamalan,,
        // 4,tt3783958,2017-05-16,2017-05-16,,La La Land,https://www.imdb.com/title/tt3783958/,movie,8.1,128,2016,"Comedy, Drama, Music, Musical, Romance",390132,2016-08-31,Damien Chazelle,,
        // 5,tt3183660,2017-05-16,2017-05-16,,Fantastic Beasts and Where to Find Them,https://www.imdb.com/title/tt3183660/,movie,7.4,133,2016,"Adventure, Family, Fantasy",314374,2016-11-08,David Yates,,

        $this->film->populateImdbIdToDb();

        $imdbId         = $this->film->getUniqueName( source: Constants::SOURCE_IMDB );
        $title          = $this->film->getTitle();
        $year           = $this->film->getYear();
        $score          = $rating?->getYourScore();
        $ratedAt        = $rating?->getYourRatingDate()?->format("Y-m-d");

        $contentType    = $this->film->getContentType();
        $mediaType = match ($contentType) {
            Film::CONTENT_FILM => "Movie",
            Film::CONTENT_TV_SERIES => "TV Series",
            Film::CONTENT_TV_EPISODE => "TV Episode",
            default => null,
        };

        return new ExportEntry(",$imdbId,,,,\"$title\",,$mediaType,,,$year,,,,,$score,$ratedAt" . PHP_EOL);
    }

}