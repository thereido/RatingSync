<?php
namespace RatingSync;

use Exception;

abstract class ExternalAdapter
{
    protected const EXPORT_TOTAL_SIZE_DEFAULT = 10000;
    protected const EXPORT_BATCH_SIZE_DEFAULT = 1000;

    protected string        $username;
    protected ExportFormat  $exportFormat;
//    protected int           $exportTotalSize = self::EXPORT_TOTAL_SIZE_DEFAULT;
    protected int           $exportTotalSize = 10;
    protected int           $exportBatchSize = self::EXPORT_BATCH_SIZE_DEFAULT;

    abstract protected function isExportableContentType( Film $film ): bool;
    abstract protected function validateExternalFilm( Film $film ): array;
    abstract protected function createExternalFilm( Film $film, Rating|null $earliestRating = null ): ExternalFilm;
    abstract protected function getEmptyBatch(): string|array;
    abstract protected function addTrackableEntry( mixed $entry, string|array &$batch ): bool;
    abstract protected function addBatch( string|array $batch, array &$batches ): bool;

    public function __construct( string $username, ExportFormat $exportFormat )
    {
        $this->username      = $username;
        $this->exportFormat  = $exportFormat;
    }

    public function exportRatings(): array
    {

        $serviceName        = $this->exportFormat->toString();
        $extensionFormat    = strtoupper( $this->exportFormat->getExtension() );
        logDebug("Export $this->username ratings for importing to $serviceName in $extensionFormat format");

        $export             = []; // See $exportBatch
        $exportBatch        = $this->getEmptyBatch(); // A batch can be a string (CSV) or an array of strings (JSON)
        $exportEntryCount   = 0;
        $batchEntryCount    = 0;

        $films                  = $this->getRatedFilmsForExport( limit: $this->exportTotalSize, includeInactive: true );
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
                    logDebug("Unable to export ratings for film id=" . $film->getId() . " " . $film->getTitle() . " (" . $film->getYear() . ") - invalid content type " . $film->getContentType());
                }
                //else if ( !$validFilm ) { // validateFilm() logs a debug message
                //    logDebug("Unable to export ratings for film id=" . $film->getId() . " " . $film->getTitle() . " (" . $film->getYear() . ") - invalid film");
                //}

                if ( $currentFilmKey == $lastFilmKey ) {
                    $this->finishBatch( $exportBatch, $export, $exportEntryCount, $batchEntryCount );
                }

                continue;
            }

            $exportEntries = $this->getExportEntriesForFilm( film: $film );
            if ( count($exportEntries) == 0 ) {
                $unexportedFilms[] = $film->getId();
                logDebug("Unable to export ratings for film id=" . $film->getId() . " " . $film->getTitle() . " (" . $film->getYear() . ") - no ratings to export");
            }

            $filmEntryCount = 0;
            foreach ( $exportEntries as $entry ) {

                if ( $this->addTrackableEntry( $entry, $exportBatch ) ) {
                    $filmEntryCount += 1;
                }

            }

            $batchEntryCount += $filmEntryCount;

            if ( $filmEntryCount == count($exportEntries) ) {
                $fullyExportedFilms[] = $film->getId();
            }
            else if ( $filmEntryCount > 0 ) {
                logDebug("Partially exported ratings for film id=" . $film->getId() . " " . $film->getTitle() . " (" . $film->getYear() . ")");
                $partiallyExportedFilms[] = $film->getId();
            }
            else {
                $unexportedFilms[] = $film->getId();
                logDebug("Unable to export ratings for film id=" . $film->getId() . " " . $film->getTitle() . " (" . $film->getYear() . ")");
            }

            if ( $batchEntryCount >= $this->exportBatchSize || $currentFilmKey == $lastFilmKey ) {
                $this->finishBatch($exportBatch, $export, $exportEntryCount, $batchEntryCount);
            }

        }

        logDebug("Exported a total of $exportEntryCount ratings for " . count($fullyExportedFilms) . " films in " . count($export) . " batches");
        if ( count($partiallyExportedFilms) > 0 ) {
            logDebug("Partially exported ratings for " . count($partiallyExportedFilms) . " films: " . implode(", ", $partiallyExportedFilms));
        }
        if ( count($unexportedFilms) > 0 ) {
            logDebug("Unable to export ratings for " . count($unexportedFilms) . " films: " . implode(", ", $unexportedFilms));
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

    private function getExportEntriesForFilm(Film $film ): array
    {
        $ratingEntries = [];

        $imdbId                 = $film->getUniqueName(Constants::SOURCE_IMDB);
        $tmdbId                 = $film->getUniqueName(Constants::SOURCE_TMDBAPI);
        $rating                 = $film->getRating(Constants::SOURCE_RATINGSYNC);
        $inactiveRatings        = $film->getSource(Constants::SOURCE_RATINGSYNC)->getArchive();
        $title                  = $film->getTitle();
        $year                   = $film->getYear();

        if ( $imdbId == null && $tmdbId == null ) {
            logDebug("IMDb & TMDb ids are empty for film id=" . $film->getId() . " $title ($year)");
            return $ratingEntries;
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
            return $ratingEntries;
        }

        foreach ( $inactiveRatings as $inactiveRating ) {

            try {
                $ratingEntries[] = $this->getRatingEntry($externalFilm, $inactiveRating);
            }
            catch (Exception) {
                logDebug("Error exporting inactive rating for film id=" . $film->getId() . " $title ($year)");
            }

        }

        try {
            $ratingEntries[] = $this->getRatingEntry($externalFilm, $rating);
        }
        catch (Exception) {
            logDebug("Error exporting rating for film id=" . $film->getId() . " $title ($year)");
        }

        return $ratingEntries;
    }

    /**
     * @throws Exception
     */
    private function getRatingEntry( ExternalFilm $externalFilm, Rating $rating ): string|array
    {

        try {
            return match ($this->exportFormat) {

                ExportFormat::CSV_LETTERBOXD, ExportFormat::CSV_IMDB, ExportFormat::CSV_TMDB => $externalFilm->csvEntry( $rating ),

                ExportFormat::JSON_TRAKT => $externalFilm->jsonEntry( $rating ),

                default => throw new Exception("Unknown export format: " . $this->exportFormat->toString()),

            };
        }
        catch (Exception $e) {
            logDebug("Error creating ExternalFilm for film " . $externalFilm->info());
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

}

abstract class ExternalFilm
{
    protected Film $film;

    abstract static public function validateExternalFilm(Film $film ): array;
    abstract public function csvEntry( Rating $rating ): string;
    abstract public function jsonEntry( Rating $rating ): array;

    public function info(): string
    {
        return "FilmId=" . $this->film->getId() . ", Title=" . $this->film->getTitle() . ", (" . $this->film->getYear() .")";
    }
}
