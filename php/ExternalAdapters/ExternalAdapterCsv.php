<?php
namespace RatingSync;

require_once __DIR__ . DIRECTORY_SEPARATOR . "ExternalAdapter.php";

abstract class ExternalAdapterCsv extends ExternalAdapter
{

    abstract protected function getHeader(): string;

    protected function getEmptyBatch(): string|array
    {
        return $this->getHeader() . PHP_EOL;
    }

    protected function addTrackableEntry( mixed $entry, string|array &$batch ): bool
    {
        $success = false;

        if ( is_string( $batch ) && is_string( $entry ) && trim( $entry !== '' ) ) {
            $batch .= trim( $entry ) . PHP_EOL;
            $success = true;
        }

        return $success;
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

abstract class ExternalFilmCsv extends ExternalFilm
{
    /** This abstract function from the parent: jsonEntry() is not used for exporting by CSV transfers  */
    public function jsonEntry( Rating $rating ): array { return []; }
}
