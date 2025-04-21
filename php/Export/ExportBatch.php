<?php

namespace RatingSync;

use Exception;

/**
 * Class ExportBatch
 *
 * This class manages the export of Ratings or a Collection. It supports saving the export to files using a defined
 * external adapter and file entry limit. If a batch has a count of entries above the file entry limit then the
 * ExportBatch with save the export into multiple files.
 *
 * - ExportBatch
 *   - ExportFile 1
 *     - [ExportEntry... up to file entry limit]
 *   - ExportFile 2...
 */
class ExportBatch
{

    private array                       $files              = [];
    private readonly ExternalAdapter    $adapter;
    private readonly int                $fileEntryLimit;

    public function __construct(ExternalAdapter $adapter, int $fileEntryLimit )
    {
        $this->adapter          = $adapter;
        $this->fileEntryLimit   = $fileEntryLimit;
    }

    public function countFiles(): int
    {
        return count( $this->files );
    }

    public function countEntries(): int
    {
        $countEntries = 0;

        foreach ( $this->files as $file ) {
            $countEntries += $file->count();
        }

        return $countEntries;
    }

    /**
     * @param ExportEntry $entry
     * @return bool Success/failure
     * @throws Exception
     */
    public function addEntry( ExportEntry $entry ): bool
    {
        if ( $this->adapter->ExportFormat != $entry->Format ) {
            $msg = "Invalid Format: Export format: " . $this->adapter->ExportFormat->value . ", Entry format: " . $entry->Format->value;
            throw new Exception($msg);
        }

        $file       = $this->fileForNextEntry();
        $success    = $file->addEntry( $entry );

        if ( $success ) {
            $this->files[ count( $this->files ) - 1 ] = $file;
        }
        else {
            logError( "Failed to add $entry" );
        }

        return $success;

    }

    /**
     * @param array $entries Array<ExportEntry>
     * @return int Number of entries successfully added
     * @throws Exception
     */
    public function addEntries( array $entries ): int
    {
        $newEntryCount  = 0;

        foreach ( $entries as $entry ) {

            try {
                if ( $this->addEntry( $entry ) ) {
                    $newEntryCount++;
                }
            }
            catch (Exception $e) {
                $entryOfTotalStr = " " . ($newEntryCount + 1) . "/" . count($entries);
                $entryMsg = count($entries) > 1 ? $entryOfTotalStr : "";
                logError(message: "Unable to add entry$entryMsg.", e: $e);
                throw $e;
            }

        }

        return $newEntryCount;
    }

    /**
     * @param string|null $collectionName A null collectionName implies this is for Ratings. Non-null means this is for a Collection.
     * @return array|false Array<string> Filenames for files successfully written to disk (no path). false on failure.
     */
    public function saveToDisk( string $collectionName = null ): array|false
    {

        $collectionName = $collectionName ?? "Ratings";

        if ( $this->countEntries() == null ) {
            $this->files = [];
            return [];
        }

        $filenameSite           = str_replace(' ', '', Constants::SITE_NAME);
        $filenameUser           = "_"    . getUsername();
        $filenameType           = "_"    . $collectionName;
        $filenameDestination    = "_to_" . $this->adapter->ExportDestination->value;
        $filenameFormat         = "_"    . $this->adapter->ExportFormat->value;

        $filenameBase   = $filenameSite . $filenameUser . $filenameType . $filenameDestination . $filenameFormat;
        $outputDir      = Constants::outputFilePath() . getUsername() . DIRECTORY_SEPARATOR;
        $filesWritten   = array();

        if ( !is_dir( $outputDir ) ) {
            if ( !mkdir( directory: $outputDir, recursive: true ) ) {
                $e = new Exception("Failed to create export output directory: $outputDir");
                logError("Unable to save export batch to disk: ", $e);
            }
        }

        if ( !is_writable( $outputDir ) ) {
            logDebug("Output dir is not writable: $outputDir");
        }

        foreach ( $this->files as $file ) {

            $fileNumber         = count( $this->files ) > 1 ? "_" . count( $filesWritten ) + 1 : "";
            $filename           = $filenameBase . $fileNumber . "." . $this->adapter->ExportFormat->getExtension();
            $filenameWithPath   = $outputDir . $filename;
            $success            = $file->saveToDisk( $filenameWithPath );

            if ( !$success ) {
                return false;
            }

            $filesWritten[] = $filename;

        }

        $this->files = [];
        return $filesWritten;

    }

    /**
     * @return ExportFile Returns the current file adding entries into if it is not full. If the current file is full then a new file is created and returned.
     */
    private function fileForNextEntry(): ExportFile
    {

        $lastKey        = array_key_last( $this->files );
        $noFiles        = empty( $this->files );
        $lastFileIsFull = !is_null($lastKey) && $this->files[$lastKey]->count() >= $this->fileEntryLimit;

        if ( $noFiles || $lastFileIsFull ) {
            $this->files[]  = new ExportFile( format: $this->adapter->ExportFormat, csvHeader: $this->adapter->getCsvHeader() );
            $lastKey        = array_key_last( $this->files );
        }

        return $this->files[ $lastKey ];

    }

}

/**
 * Represents a file to be exported in a specific format (e.g., CSV or JSON).
 */
class ExportFile
{

    public readonly ExportFileFormat   $Format;

    private string  $csvHeader;
    private array   $entries;

    public function __construct(ExportFileFormat $format, string $csvHeader = null )
    {
        $this->csvHeader    = $csvHeader ?? "";
        $this->entries      = [];
        $this->Format       = $format;
    }

    /**
     * @param mixed $entry
     * @return bool
     */
    public function addEntry( ExportEntry $entry ): bool
    {

        if ( $this->validateEntry( $entry ) ) {
            $this->entries[] = $entry;
            return true;
        }

        return false;

    }

    /**
     * @param string $filename
     * @return bool
     */
    public function saveToDisk( string $filename ): bool
    {

        $bytesWritten   = writeFile( $this->__toString(), $filename );

        if ( $bytesWritten === false ) {
            logError("Failed to write to $filename");
            return false;
        }
        else {
            logDebug("Export file: $filename");
            return true;
        }

    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $str = "";

        if ( $this->Format == ExportFileFormat::CSV ) {

            $str .= $this->csvHeader;

            foreach ( $this->entries as $entry ) {
                $str .= $entry->Value . PHP_EOL;
            }

        }
        else if ( $this->Format == ExportFileFormat::JSON ) {
            $str = json_encode( $this->entries, JSON_PRETTY_PRINT );
        }

        return $str;
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty( $this->entries );
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count( $this->entries );
    }

    /**
     * @param ExportEntry $entry
     * @return bool
     */
    private function validateEntry(ExportEntry $entry ): bool
    {
        return !empty($entry->Value)
            && $entry->Format == $this->Format;
    }

}