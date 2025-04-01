<?php
namespace RatingSync;

use Exception;

require_once __DIR__ . DIRECTORY_SEPARATOR . "ExternalAdapter.php";

abstract class ExternalAdapterCsv extends ExternalAdapter
{

    abstract protected function getHeader(): string;

    protected function getEmptyBatch(): string|array
    {
        return $this->getHeader() . PHP_EOL;
    }

    /**
     * @throws Exception
     */
    protected function addTrackableEntry(mixed $entry, string|array &$batch ): bool
    {

        if ( !is_string( $entry ) ) {
            throw new Exception("Unable to add entry to CSV batch. Entry is not a string.");
        }

        if ( !is_string( $batch ) ) {
            throw new Exception("Unable to add entry to CSV batch. Batch is not a string.");
        }

        if ( trim( $entry ) === '' ) {
            throw new Exception("Skipping empty entry.");
        }

        $batch .= trim( $entry ) . PHP_EOL;

        return true;
    }

    protected function addBatch( mixed $batch, array &$batches ): bool
    {
        $success = false;

        if ( is_string( $batch ) && trim($batch !== '') ) {
            $batches[]  = $batch . PHP_EOL;
            $success = true;
        }

        return $success;
    }

}
