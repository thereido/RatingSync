<?php
namespace RatingSync;

use DateTime;
use Exception;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "Export" . DIRECTORY_SEPARATOR . "ExportEntry.php";

class TraktAdapter extends ExternalAdapter
{
    // https://trakt.tv/settings/data

    /**
     * @param ExportCollectionType $collectionType
     * @throws Exception
     */
    public function __construct( ExportCollectionType $collectionType )
    {
        parent::__construct( collectionType: $collectionType, className: __CLASS__, exportFormat: ExportFileFormat::JSON );
    }

    /**
     * @return ExportDestination
     */
    protected static function exportDestination(): ExportDestination
    {
        return ExportDestination::TRAKT;
    }

    /**
     * @param Film $film
     * @return array
     */
    protected function validateExportableExternalFilm( Film $film ): array
    {
        return TraktFilm::validateExternalFilm( $film );
    }

    /**
     * @param Film $film
     * @param Rating|null $earliestRating
     * @return ExternalFilm
     * @throws Exception
     */
    public function createExternalFilm(Film $film, Rating|null $earliestRating = null ): ExternalFilm
    {
        return new TraktFilm( $film );
    }

    /**
     * @return string
     */
    public function getCsvHeader(): string
    {
        return "";
    }
}

class TraktFilm extends ExternalFilm
{
    private string|null     $imdbId;
    private string|null     $tmdbId;
    private DateTime|null  $watchlistedAt;

    /**
     * @param Film $film
     * @throws Exception
     */
    public function __construct( Film $film )
    {
        $imdbId                 = $film->getUniqueName(Constants::SOURCE_IMDB);
        $tmdbId                 = $film->getUniqueName(Constants::SOURCE_TMDBAPI);

        if ( $imdbId == null && $tmdbId == null ) {
            throw new Exception("Either IMDb & TMDb id must be provided. IMDb=$imdbId, TMDb=$tmdbId");
        }

        $this->imdbId        = $imdbId;
        $this->tmdbId        = $tmdbId;
        $this->watchlistedAt = null;
        $this->film          = $film;
    }

    /**
     * @param Film $film
     * @return array Array<string>: An empty return is a valid film. An invalid film gets one or more reasons.
     */
    static public function validateExternalFilm( Film $film ): array
    {
        $problems   = [];
        $imdbId     = $film->getUniqueName(Constants::SOURCE_IMDB);
        $tmdbId     = $film->getUniqueName(Constants::SOURCE_TMDBAPI);

        if ( $imdbId == null || $tmdbId == null ) {

            if ( $imdbId == null ) {
                $problems[] = "Empty IMDb id";
            }

            if ( $tmdbId == null ) {
                $problems[] = "Empty TMDb id";
            }

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
        // [
        //  {
        //    "imdb_id": "tt0068646",
        //    "watched_at": "2024-10-25T20:00:00Z",
        //    "watchlisted_at": "2024-10-01T10:00:00Z",
        //  },
        //  {
        //    "imdb_id": "tt15239678",
        //    "watchlisted_at": "2024-04-30T11:00:00Z",
        //    "rating": 9,
        //    "rated_at": "2024-10-25T21:00:00Z",
        //  },
        //  {
        //    "imdb_id": "tt4281724",
        //    "watched_at": "2024-01-12T02:00:00Z",
        //  }
        // ]

        $this->film->populateImdbIdToDb();

        $imdbId         = $this->imdbId;
        $tmdbId         = $this->tmdbId ? substr($this->tmdbId, offset: 2) : null;
        $watchlistedAt  = $this->watchlistedAt?->format("Y-m-d\TH:i:s\Z");
        $score          = $rating?->getYourScore();
        $ratingAt       = $rating?->getYourRatingDate()?->format("Y-m-d\TH:i:s\Z");
        $watchedAt      = $ratingAt;

        $entry              = [];

        if ( $imdbId != null ) {
            $entry['imdb_id'] = $imdbId;
        }
        else if ( $tmdbId != null ) {
            $entry['tmdb_id'] = intval( substr($this->tmdbId, offset: 2) );
        }

        if ( $watchedAt != null ) {
            $entry['watched_at'] = $watchedAt;
        }

        if ( $watchlistedAt != null ) {
            $entry['watchlisted_at'] = $watchlistedAt;
        }

        if ( $rating != null ) {
            $entry['rating']    = $score;
            $entry['rated_at']  = $ratingAt;
        }

        return new ExportEntry( $entry );
    }

}
