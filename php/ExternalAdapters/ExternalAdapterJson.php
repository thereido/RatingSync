<?php
namespace RatingSync;

require_once __DIR__ . DIRECTORY_SEPARATOR . "ExternalAdapter.php";

abstract class ExternalAdapterJson extends ExternalAdapter
{

    protected function getEmptyBatch(): string|array
    {
        return [];
    }

    public function getHeader(): string|null
    {
        // There is no header for a JSON export
        return null;
    }

    protected function addTrackableEntry( mixed $entry, string|array &$batch ): bool
    {
        $success = false;

        if ( is_array( $batch ) && is_array( $entry ) && count($entry) > 0 ) {
            $batch[] = $entry;
            $success = true;
        }

        return $success;
    }

    protected function addBatch( mixed $batch, array &$batches ): bool
    {
        $success = false;

        if ( is_array( $batch ) && count($batch) > 0 ) {
            $batches[]  = json_encode( $batch, JSON_PRETTY_PRINT ) . "\n";
            $success = true;
        }

        return $success;
    }

}

abstract class ExternalFilmJson extends ExternalFilm
{
    /** This abstract function from the parent: csvEntry() is not used for exporting by JSON transfers  */
    public function csvEntry( Rating $rating ): string { return ''; }
}
