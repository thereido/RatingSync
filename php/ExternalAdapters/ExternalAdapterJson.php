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

        if ( !is_array( $entry ) ) {
            logError("Unable to add entry to JSON batch. Entry is not an array.");
            return false;
        }

        if ( !is_array( $batch ) ) {
            logError("Unable to add entry to JSON batch. Batch is not an array.");
            return false;
        }

        if ( count($entry) === 0 ) {
            logDebug("Skipping empty entry.");
        }

        $batch[] = $entry;

        return true;
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
