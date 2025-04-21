<?php

namespace RatingSync;

use Exception;

/**
 * Represents an entry to be exported in a specific format (CSV or JSON).
 * Determines the format and value type based on the provided input.
 */
class ExportEntry
{
    public readonly ExportFileFormat $Format;
    public readonly string|array $Value;

    /**
     * @param mixed $value
     * @throws Exception
     */
    public function __construct( mixed $value )
    {

        if ( is_string( $value ) ) {
            $this->Format   = ExportFileFormat::CSV;
            $this->Value    = trim( $value );
        }
        else if ( is_array( $value ) ) {
            $this->Format   = ExportFileFormat::JSON;
            $this->Value    = $value;
        }
        else {
            throw new Exception("Value must be a string (CSV) or an array (JSON).");
        }

    }

    public function __toString(): string
    {
        return "ExportEntry format=" . $this->Format->value . " (" . $this->Value . ")";
    }

}