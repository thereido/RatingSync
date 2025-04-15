<?php
namespace RatingSync;

use Exception;

abstract class ExternalAdapter
{
    private const   EXPORT_TOTAL_SIZE_DEFAULT   = 10000;
    private const   EXPORT_BATCH_SIZE_DEFAULT   = 1000;
    protected int   $exportTotalSize            = self::EXPORT_TOTAL_SIZE_DEFAULT;
    protected int   $exportBatchSize            = self::EXPORT_BATCH_SIZE_DEFAULT;

    protected string        $username;
    protected ExportFormat  $exportFormat;
    protected array $supportedExportFormats = [];
    protected array $exportableContentTypes = [Film::CONTENT_FILM, Film::CONTENT_TV_SERIES, Film::CONTENT_TV_EPISODE];

    abstract protected function validateExternalFilm( Film $film ): array;
    abstract protected function createExternalFilm( Film $film, Rating|null $earliestRating = null ): ExternalFilm;
    abstract protected function getEmptyBatch(): string|array;
    abstract protected function addTrackableEntry( mixed $entry, string|array &$batch ): bool;
    abstract protected function addBatch( string|array $batch, array &$batches ): bool;

    /**
     * @throws Exception
     */
    protected function __construct(string $username, ExportFormat $format, string $className )
    {
        if ( !in_array( $format, $this->supportedExportFormats) ) {
            throw new \Exception("Export format ($format->name) is not supported for $className adapter");
        }

        $this->username      = $username;
        $this->exportFormat  = $format;
    }

    public function exportRatings(): array
    {

        $serviceName        = $this->exportFormat->toString();
        $extensionFormat    = strtoupper( $this->exportFormat->getExtension() );
        logDebug("Export $this->username ratings for importing to $serviceName in $extensionFormat format");

        $films              = $this->getRatedFilmsForExport( limit: $this->exportTotalSize, includeInactive: true );

        return $this->exportEntries( $films );
    }

    public function exportFilmCollection( string $collectionName ): array
    {

        $serviceName        = $this->exportFormat->toString();
        $extensionFormat    = strtoupper( $this->exportFormat->getExtension() );
        logDebug("Export $this->username \"$collectionName\" for importing to $serviceName in $extensionFormat format");

        $films = $this->getFilmCollectionForExport( name: $collectionName, limit: $this->exportTotalSize );

        return $this->exportEntries( $films );
    }

    private function exportEntries( array $films ): array
    {

        $export                 = []; // See $exportBatch
        $exportBatch            = $this->getEmptyBatch(); // A batch can be a string (CSV) or an array of strings (JSON)
        $exportEntryCount       = 0;
        $batchEntryCount        = 0;
        $lastFilmKey            = array_key_last( $films );
        $fullyExportedFilms     = []; // Type: int - Film IDs
        $partiallyExportedFilms = []; // Type: int - Film IDs
        $unexportedFilms        = []; // Type: int - Film IDs

        foreach ( $films as $currentFilmKey => $film ) {

            $validContentType = $this->isExportableContentType( $film );
            $validFilm        = $this->validateFilm( $film );

            if ( !$validContentType || !$validFilm ) {

                $unexportedFilms[] = $film->getId();

                if ( !$validContentType ) {
                    logDebug("Unable to export entries for film id=" . $film->getId() . " " . $film->getTitle() . " (" . $film->getYear() . ") - invalid content type " . $film->getContentType());
                }

                if ( $currentFilmKey == $lastFilmKey ) {
                    $this->finishBatch( $exportBatch, $export, $exportEntryCount, $batchEntryCount );
                }

                continue;
            }

            $exportEntries = $this->getExportEntriesForFilm( film: $film );

            $filmEntryCount         = 0;
            $filmEntryErrorCount    = 0;
            foreach ( $exportEntries as $entry ) {

                try {
                    $this->addTrackableEntry( $entry, $exportBatch );
                    $filmEntryCount += 1;
                }
                catch (Exception $e) {
                    $filmEntryErrorCount += 1;
                    $entryOfTotal = " " . ($filmEntryCount + $filmEntryErrorCount) . "/" . count($exportEntries);
                    $entryOfTotalMsg = count($exportEntries) > 1 ? $entryOfTotal : "";
                    logDebug("Unable to add entry$entryOfTotalMsg. Error: " . $e->getMessage());
                }

            }

            $batchEntryCount += $filmEntryCount;

            if ( $filmEntryCount == count($exportEntries) ) {
                $fullyExportedFilms[] = $film->getId();
            }
            else if ( $filmEntryCount > 0 ) {
                logDebug("Partially exported $filmEntryCount entries of " . count($exportEntries) . " entries for film id=" . $film->getId() . " " . $film->getTitle() . " (" . $film->getYear() . ")");
                $partiallyExportedFilms[] = $film->getId();
            }
            else {
                $unexportedFilms[] = $film->getId();
                logDebug("Unable to export entries for film id=" . $film->getId() . " " . $film->getTitle() . " (" . $film->getYear() . ")");
            }

            if ( $batchEntryCount >= $this->exportBatchSize || $currentFilmKey == $lastFilmKey ) {
                $this->finishBatch($exportBatch, $export, $exportEntryCount, $batchEntryCount);
            }

        }

        logDebug("Exported a total of $exportEntryCount entries for " . count($fullyExportedFilms) . " films in " . count($export) . " batches");
        if ( count($partiallyExportedFilms) > 0 ) {
            logDebug("Partially exported entries for " . count($partiallyExportedFilms) . " films: " . implode(", ", $partiallyExportedFilms));
        }
        if ( count($unexportedFilms) > 0 ) {
            logDebug("Unable to export entries for " . count($unexportedFilms) . " films: " . implode(", ", $unexportedFilms));
        }

        return $export;
    }

    protected function getRatedFilmsForExport( int $limit = null, int $offset = 1, bool $includeInactive = false ): array
    {

        $site = new RatingSyncSite( $this->username );
        $site->setSort( field: RatingSortField::date );
        $site->setSortDirection( direction: SqlSortDirection::descending );

        return $site->getFilmsForExport( limit: $limit, offset: $offset, includeInactive: $includeInactive );

    }

    protected function getFilmCollectionForExport( string $name, int $limit = null ): array
    {

        $list = new Filmlist( $this->username, $name );
        $list->setSort( ListSortField::position );
        $list->setSortDirection( SqlSortDirection::descending );
        $list->setContentFilter( $this->getFilterForContentTypes() );
        $list->initFromDb();

        return $list->getFilms( $limit );
    }

    private function validateFilm( Film $film ): bool
    {
        $invalidReason = $this->validateExternalFilm( $film );

        if ( count($invalidReason) ) {
            $reasons = "";
            foreach ( $invalidReason as $reason ) {
                $delimiter = $reasons == "" ? "" : ", ";
                $reasons .= $delimiter . $reason;
            }

            $contentTypeMsg = $film->getContentType() == Film::CONTENT_TV_EPISODE ? " - s" . $film->getSeason() . " e" . $film->getEpisodeNumber() : "";
            logDebug("Invalid film for exporting ratings. Reason=\"$reasons\". Skipping this film. FilmId=" . $film->getId() . " " . $film->getTitle() . " (" . $film->getYear() . ")$contentTypeMsg");
            return false;
        }

        return true;
    }

    private function getExportEntriesForFilm( Film $film ): array
    {
        $entries = [];

        $imdbId                 = $film->getUniqueName(Constants::SOURCE_IMDB);
        $tmdbId                 = $film->getUniqueName(Constants::SOURCE_TMDBAPI);
        $title                  = $film->getTitle();
        $year                   = $film->getYear();
        $rating                 = $this->exportFormat->isRatings() ? $film->getRating(Constants::SOURCE_RATINGSYNC) : null;
        $inactiveRatings        = $this->exportFormat->isRatings() ? $film->getSource(Constants::SOURCE_RATINGSYNC)->getArchive() : [];

        if ( $imdbId == null && $tmdbId == null ) {
            logDebug("IMDb & TMDb ids are empty for film id=" . $film->getId() . " $title ($year)");
            return $entries;
        }

        $earliestRating = null;
        if ( count($inactiveRatings) > 0 ) {

            $sortCallback = function (Rating $a, Rating $b) { return Rating::compareByRatingDate($a, $b); };
            uasort($inactiveRatings, $sortCallback);
            $earliestRating = $inactiveRatings[0];

        }

        try {
            $externalFilm = $this->createExternalFilm( $film, $earliestRating );
        }
        catch (Exception $e) {
            logDebug( "Error creating ExternalFilm for film id=" . $film->getId() . " $title ($year)\n" . exceptionShortMsg($e), prefix: __CLASS__ . ":" . __FUNCTION__ . ":" . __LINE__ );
            return $entries;
        }

        foreach ( $inactiveRatings as $inactiveRating ) {

            if ( $inactiveRating?->isInitiated() ) {
                try {
                    $entries[] = $this->buildEntry($externalFilm, $inactiveRating);
                } catch (Exception) {
                    logDebug("Error exporting inactive rating for film id=" . $film->getId() . " $title ($year)");
                }
            }

        }

        if ( $rating?->isInitiated() ) {
            try {
                $entries[] = $this->buildEntry($externalFilm, $rating);
            } catch (Exception) {
                logDebug("Error exporting entry for film id=" . $film->getId() . " $title ($year)");
            }
        }

        return $entries;
    }

    /**
     * @throws Exception
     */
    private function buildEntry( ExternalFilm $externalFilm, Rating|null $rating = null ): string|array
    {
        try {
            return match ($this->exportFormat) {

                ExportFormat::LETTERBOXD_RATINGS,
                ExportFormat::IMDB_RATINGS,
                ExportFormat::TMDB_RATINGS,
                ExportFormat::TRAKT_RATINGS         => $externalFilm->ratingEntry( $rating ),

                ExportFormat::LETTERBOXD_COLLECTION => $externalFilm->filmEntry(),

                default => throw new Exception("Unknown export format: " . $this->exportFormat->toString()),

            };
        }
        catch (Exception $e) {
            logDebug("Error creating export entry for film " . $externalFilm->info());
            throw $e;
        }

    }

    private function finishBatch( string|array &$batch, array &$batches, int &$progressCount, int &$entryCount ): void
    {
        if ( $batch == null ) {
            return;
        }

        $addedTheBatch = $this->addBatch( $batch, $batches );

        if ( !$addedTheBatch ) {
            logDebug("Error adding batch to batches[" . count($batches) . "]. Missing $entryCount entries by skipping this batch. Continuing to the next batch.");
            $batch = $this->getEmptyBatch();
        }

        $progressCount  += $entryCount;
        $entryCount     = 0;
        $batch          = $this->getEmptyBatch();
    }

    private function isExportableContentType( Film $film ): bool
    {
        return in_array( $film->getContentType(), $this->exportableContentTypes );
    }

    private function getFilterForContentTypes(): array
    {
        $types  = [Film::CONTENT_FILM, Film::CONTENT_TV_SERIES, Film::CONTENT_TV_EPISODE];
        $filter = [];
        foreach ( $types as $contentType ) {
            if ( !in_array( $contentType, $this->exportableContentTypes ) ) {
                $filter[] = $contentType;
            }
        }
        return $filter;
    }

}

abstract class ExternalFilm
{
    protected Film $film;

    abstract static public function validateExternalFilm(Film $film ): array;
    abstract public function ratingEntry( Rating $rating ): string|array;
    abstract public function filmEntry(): string|array;

    public function info(): string
    {
        return "FilmId=" . $this->film->getId() . ", Title=" . $this->film->getTitle() . ", (" . $this->film->getYear() .")";
    }
}
