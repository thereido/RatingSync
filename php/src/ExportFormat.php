<?php
namespace RatingSync;

enum ExportFormat
{
    case CSV_LETTERBOXD;
    case CSV_IMDB;
    case CSV_TMDB;
    case JSON_TRAKT;
    case XML;

    public function toString(): string
    {
        return match ($this) {
            self::CSV_LETTERBOXD => "Letterboxd",
            self::CSV_IMDB => "IMDb",
            self::CSV_TMDB => "TMDb",
            self::JSON_TRAKT => "Trakt",
            self::XML => "XML",
        };
    }

    public function getExtension(): string
    {
        return match ($this) {
            self::CSV_LETTERBOXD, self::CSV_IMDB, self::CSV_TMDB => "csv",
            self::JSON_TRAKT => "json",
            self::XML => "xml",
        };
    }

}