<?php
namespace RatingSync;

use Exception;

require_once __DIR__ . DIRECTORY_SEPARATOR . "ExternalAdapterCsv.php";

class LetterboxdAdapter extends ExternalAdapterCsv
{
    // https://letterboxd.com/about/importing-data/
    // tmdbID,imdbID,Title,Year,Rating10,WatchedDate,Rewatch

    protected array $supportedExportFormats = [ExportFormat::LETTERBOXD_COLLECTION, ExportFormat::LETTERBOXD_RATINGS];
    protected array $exportableContentTypes = [Film::CONTENT_FILM];

    public function __construct( string $username, ExportFormat $format )
    {
        parent::__construct( username: $username, format: $format, className: __CLASS__ );
    }

    protected function getHeader(): string
    {
        return "tmdbID,imdbID,Title,Year,Rating10,WatchedDate,Rewatch";
    }

    protected function validateExternalFilm( Film $film ): array
    {
        return LetterboxdFilm::validateExternalFilm( $film );
    }

    /**
     * @throws Exception
     */
    protected function createExternalFilm( Film $film, Rating|null $earliestRating = null ): ExternalFilm
    {
        return new LetterboxdFilm( $film, $earliestRating );
    }

    protected function getRatedFilmsForExport( int $limit = null, int $offset = 1, bool $includeInactive = false ): array
    {

        $site = new RatingSyncSite( $this->username );
        $site->setSort( field: RatingSortField::date );
        $site->setSortDirection( direction: SqlSortDirection::descending );
        $site->setContentTypeFilter( [Film::CONTENT_TV_SERIES, Film::CONTENT_TV_EPISODE] );

        return $site->getFilmsForExport( limit: $limit, offset: $offset, includeInactive: $includeInactive );

    }

}

class LetterboxdFilm extends ExternalFilm
{
    private Rating|null     $earliestRating;

    /**
     * @throws Exception
     */
    public function __construct( Film $film, Rating|null $earliestRating )
    {
        if ( $film->getUniqueName( source: Constants::SOURCE_TMDBAPI  ) == null ) {
            throw new Exception(ExportFormat::TMDB_RATINGS->toString() . " id must be provided");
        }

        $this->film             = $film;
        $this->earliestRating   = $earliestRating;
    }

    static public function validateExternalFilm( Film $film ): array
    {
        $problems = [];

        if ( $film->getUniqueName( source: Constants::SOURCE_TMDBAPI  ) == null ) {
            $problems[] = "Empty TMDb id";
        }

        return $problems;
    }

    public function ratingEntry( ?Rating $rating ): string
    {
        $this->film->populateImdbIdToDb();

        $tmdbSource     = $this->film->getSource( Constants::SOURCE_TMDBAPI );
        $imdbSource     = $this->film->getSource( Constants::SOURCE_IMDB );
        $tmdbIdFull     = $tmdbSource->getUniqueName();
        $tmdbId         = $tmdbIdFull ? substr($tmdbIdFull, offset: 2) : null;
        $imdbId         = $imdbSource->getUniqueName();
        $title          = $this->film->getTitle();
        $year           = $this->film->getYear();
        $score          = null;
        $ratingAt       = null;
        $rewatchStr     = null;

        if (is_null($rating)) {
            $ratingAt = $this->film->getRating(Constants::SOURCE_RATINGSYNC)?->getYourRatingDate()?->format("Y-m-d");
        }
        else {
            $score          = $rating->getYourScore();
            $ratingAt       = $rating->getYourRatingDate()?->format("Y-m-d");
            $rewatchStr     = $this->earliestRating === null || $rating->equals($this->earliestRating) ? "false" : "true";
        }

        return "$tmdbId,$imdbId,\"$title\",$year,$score,$ratingAt,$rewatchStr" . PHP_EOL;
    }

    public function filmEntry(): string|array
    {
        return $this->ratingEntry( rating: null );
    }

}
