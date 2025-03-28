<?php
namespace RatingSync;

require_once __DIR__ . DIRECTORY_SEPARATOR . "ExternalAdapterJson.php";

class TraktAdapter extends ExternalAdapterJson
{
    // https://trakt.tv/settings/data

    protected ExportFormat $exportFormat = ExportFormat::JSON_TRAKT;

    public function __construct( string $username )
    {
        parent::__construct( username: $username, exportFormat: $this->exportFormat );
    }

    protected function isExportableContentType( Film $film ): bool
    {
        return match ( $film->getContentType() ) {
            Film::CONTENT_FILM, Film::CONTENT_TV_SERIES, Film::CONTENT_TV_EPISODE => true,
            default => false,
        };
    }

    protected function validateExternalFilm( Film $film ): array
    {
        return TraktFilm::validateExternalFilm( $film );
    }

    /**
     * @throws \Exception
     */
    protected function createExternalFilm( Film $film, Rating|null $earliestRating = null ): ExternalFilm
    {
        return new TraktFilm( $film );
    }

}

class TraktFilm extends ExternalFilmJson
{
    private string|null     $imdbId;
    private string|null     $tmdbId;
    private \DateTime|null  $watchlistedAt;

    /**
     * @throws \Exception
     */
    public function __construct( Film $film )
    {
        $imdbId                 = $film->getUniqueName(Constants::SOURCE_IMDB);
        $tmdbId                 = $film->getUniqueName(Constants::SOURCE_TMDBAPI);

        if ( $imdbId == null && $tmdbId == null ) {
            throw new \Exception("Either IMDb & TMDb id must be provided. IMDb=$imdbId, TMDb=$tmdbId");
        }

        $this->imdbId        = $imdbId;
        $this->tmdbId        = $tmdbId;
        $this->watchlistedAt = null;
    }

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

    public function jsonEntry( Rating $rating ): array
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

        return $entry;
    }
}
