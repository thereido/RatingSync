<?php

namespace RatingSync;

enum ExportCollectionType: string
{

    case RATINGS    = "Ratings";
    case COLLECTION = "Collection/List";

}

enum ExportDestination: string
{

    case LETTERBOXD = "Letterboxd";
    case IMDB       = "IMDb";
    case TMDB       = "TMDb";
    case TRAKT      = "Trakt";

}

enum ExportFileFormat: string
{

    case CSV    = "CSV";
    case JSON   = "JSON";

    /**
     * @return string
     */
    public function getExtension(): string
    {
        return match ($this) {
            self::JSON  => "json",
            self::CSV   => "csv",
        };
    }

}