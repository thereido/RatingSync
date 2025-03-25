<?php
namespace RatingSync;

class Trakt
{
    // https://trakt.tv/settings/data

    private const EXPORT_BATCH_SIZE = 1000;

    static public function exportRatingsJson(RatingSyncSite $site, string $username): array
    {

        logDebug("Beginning export of ratings for $username in Trakt.tv JSON format");

        $site->setSort( field: RatingSortField::date );
        $site->setSortDirection( direction: SqlSortDirection::descending );

        $batches            = []; // Type: JSON Encoded, Content: Batches of trackable entries
        $processedFilmIds   = []; // Type: int
        $progressCount      = 0;
        $batchCount         = 0;
        $batch              = []; // Type: string in JSON format, Content: Trackable entries

        $ratings            = $site->getRatings();
        $lastRating         = $ratings[ count($ratings)-1 ];

        foreach ( $ratings as $rating ) {

            if ( in_array($rating->getFilmId(), $processedFilmIds) ) {
                continue;
            }

            try {
                $film = Film::getFilmFromDb( $rating->getFilmId(), username: $username );
            } catch (\Exception $e) {
                logDebug("Error getting film from db. FilmId=" . $rating->getFilmId() . " batchCount=$batchCount");
                continue;
            }

            foreach ( Trakt::trackableEntries(film: $film) as $entry ) {
                $batch[] = $entry;
            }

            $batchCount         += 1 + count($film->getSource(Constants::SOURCE_RATINGSYNC)->getArchive());
            $processedFilmIds[] = $film->getId();

            if ( $batchCount >= self::EXPORT_BATCH_SIZE || $rating->equals($lastRating) ) {

                $batches[]      = json_encode( $batch, JSON_PRETTY_PRINT ) . "\n";
                $progressCount  += $batchCount;

                $batch = [];
                $batchCount = 0;
            }

        }

        logDebug("Exported a total of $progressCount of " . count($ratings) . " ratings in " . count($batches) . " batches\n");

        return $batches;
    }

    static private function trackableEntries(Film $film): array
    {
        $trackableEntries = [];

        $imdbId                 = $film->getUniqueName(Constants::SOURCE_IMDB);
        $tmdbId                 = $film->getUniqueName(Constants::SOURCE_TMDBAPI);
        $rating                 = $film->getRating(Constants::SOURCE_RATINGSYNC);
        $inactiveRatings        = $film->getSource(Constants::SOURCE_RATINGSYNC)->getArchive();
        $title                  = $film->getTitle();
        $year                   = $film->getYear();

        if ( $imdbId == null && $tmdbId == null ) {
            logDebug("IMDb & TMDb ids are empty for film id=" . $film->getId() . " $title ($year)");
            return $trackableEntries;
        }

        if ( count($inactiveRatings) > 0 ) {

            $sortCallback = function (Rating $a, Rating $b) { return Rating::compareByRatingDate($a, $b); };
            uasort( $inactiveRatings, $sortCallback );

            foreach ( $inactiveRatings as $inactiveRating ) {

                try {
                    $data               = new TraktFilm( imdbId: $imdbId, tmdbId: $tmdbId, watchlistedAt: null, rating: $inactiveRating );
                    $trackableEntries[] = $data->jsonEntry();
                }
                catch (\Exception $e) {
                    logDebug("Error creating TraktFilm for film id=" . $film->getId() . " $title ($year)");
                }

            }
        }

        try {
            $data               = new TraktFilm( imdbId: $imdbId, tmdbId: $tmdbId, watchlistedAt: null, rating: $rating );
            $trackableEntries[] = $data->jsonEntry();
        }
        catch (\Exception $e) {
            logDebug("Error creating TraktFilm for film id=" . $film->getId() . " $title ($year)");
        }

        return $trackableEntries;
    }

}

class TraktFilm
{
    private string|null     $imdbId;
    private string|null     $tmdbId;
    private \DateTime|null  $watchlistedAt;
    private Rating|null     $rating;

    /**
     * @throws \Exception
     */
    public function __construct( string|null $imdbId, string|null $tmdbId, \DateTime|null $watchlistedAt, Rating|null $rating )
    {
        if ( $imdbId == null && $tmdbId == null ) {
            throw new \Exception("Either IMDb & TMDb id must be provided. IMDb=$imdbId, TMDb=$tmdbId");
        }

        $this->imdbId        = $imdbId;
        $this->tmdbId        = $tmdbId;
        $this->watchlistedAt = $watchlistedAt;
        $this->rating        = $rating;
    }

    public function jsonEntry(): array
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
        $score          = $this->rating?->getYourScore();
        $ratingAt       = $this->rating?->getYourRatingDate()->format("Y-m-d\TH:i:s\Z");
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

        if ( $this->rating != null ) {
            $entry['rating']    = $score;
            $entry['rated_at']  = $ratingAt;
        }

        return $entry;
    }

}
