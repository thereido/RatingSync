<?php
namespace RatingSync;

use Exception;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "Export" . DIRECTORY_SEPARATOR . "ExportEntry.php";

class LetterboxdAdapter extends ExternalAdapter
{
    // https://letterboxd.com/about/importing-data/
    // tmdbID,imdbID,Title,Year,Rating10,WatchedDate,Rewatch

    /**
     * @param ExportCollectionType $collectionType
     * @throws Exception
     */
    public function __construct( ExportCollectionType $collectionType )
    {
        $exportableContentTypes = [Film::CONTENT_FILM];
        $supportedExportCollectionTypes = [ExportCollectionType::COLLECTION, ExportCollectionType::RATINGS];

        parent::__construct( $collectionType, __CLASS__, $exportableContentTypes, $supportedExportCollectionTypes );
    }

    /**
     * @return ExportDestination
     */
    protected static function exportDestination(): ExportDestination
    {
        return ExportDestination::LETTERBOXD;
    }

    /**
     * @return string
     */
    public function getCsvHeader(): string
    {
        return "tmdbID,imdbID,Title,Year,Rating10,WatchedDate,Rewatch" . PHP_EOL;
    }

    /**
     * @param Film $film
     * @return array
     */
    protected function validateExportableExternalFilm( Film $film ): array
    {
        return LetterboxdFilm::validateExternalFilm( $film );
    }

    /**
     * @throws Exception
     */
    public function createExternalFilm(Film $film, Rating|null $earliestRating = null ): ExternalFilm
    {
        return new LetterboxdFilm( $film, $earliestRating );
    }

}

class LetterboxdFilm extends ExternalFilm
{
    private Rating|null     $earliestRating;

    /**
     * @param Film $film
     * @param Rating|null $earliestRating
     * @throws Exception
     */
    public function __construct( Film $film, Rating|null $earliestRating )
    {
        if ( $film->getUniqueName( source: Constants::SOURCE_TMDBAPI  ) == null ) {
            throw new Exception(ExportDestination::TMDB->value . " id must be provided");
        }

        $this->film             = $film;
        $this->earliestRating   = $earliestRating;
    }

    /**
     * @param Film $film
     * @return array Array<string>: An empty return is a valid film. An invalid film gets one or more reasons.
     */
    static public function validateExternalFilm( Film $film ): array
    {
        $problems = [];

        if ( $film->getUniqueName( source: Constants::SOURCE_TMDBAPI  ) == null ) {
            $problems[] = "Empty TMDb id";
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
        $this->film->populateImdbIdToDb();

        $tmdbSource     = $this->film->getSource( Constants::SOURCE_TMDBAPI );
        $imdbSource     = $this->film->getSource( Constants::SOURCE_IMDB );
        $tmdbIdFull     = $tmdbSource->getUniqueName();
        $tmdbId         = $tmdbIdFull ? substr($tmdbIdFull, offset: 2) : null;
        $imdbId         = $imdbSource->getUniqueName();
        $title          = $this->film->getTitle();
        $year           = $this->film->getYear();
        $score          = null;
        $rewatchStr     = null;

        if (is_null($rating)) {
            $ratingAt = $this->film->getRating(Constants::SOURCE_RATINGSYNC)?->getYourRatingDate()?->format("Y-m-d");
        }
        else {
            $score          = $rating->getYourScore();
            $ratingAt       = $rating->getYourRatingDate()?->format("Y-m-d");
            $rewatchStr     = $this->earliestRating === null || $rating->equals($this->earliestRating) ? "false" : "true";
        }

        return new ExportEntry( "$tmdbId,$imdbId,\"$title\",$year,$score,$ratingAt,$rewatchStr" . PHP_EOL );
    }

}
