<?php
namespace RatingSync;

enum ExportFormat
{
    case CSV_LETTERBOXD;
    case CSV_IMDB;
    case JSON_TRAKT;
    case XML;

    public function toString(): string
    {
        return match ($this) {
            self::CSV_LETTERBOXD => "Letterboxd",
            self::CSV_IMDB => "IMDb",
            self::JSON_TRAKT => "Trakt",
            self::XML => "XML",
        };
    }

    public function getExtension(): string
    {
        return match ($this) {
            ExportFormat::CSV_LETTERBOXD, ExportFormat::CSV_IMDB => "csv",
            ExportFormat::JSON_TRAKT => "json",
            ExportFormat::XML => "xml",
        };
    }

}