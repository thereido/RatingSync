<?php
namespace RatingSync;

enum ExportFormat
{
    case LETTERBOXD_RATINGS;
    case LETTERBOXD_COLLECTION;
    case IMDB_RATINGS;
    case TMDB_RATINGS;
    case TRAKT_RATINGS;

    public function toString(): string
    {
        return match ($this) {
            self::LETTERBOXD_RATINGS, self::LETTERBOXD_COLLECTION => "Letterboxd",
            self::IMDB_RATINGS => "IMDb",
            self::TMDB_RATINGS => "TMDb",
            self::TRAKT_RATINGS => "Trakt",
        };
    }

    public function getExtension(): string
    {
        return match ($this) {
            self::TRAKT_RATINGS => "json",
            default => "csv",
        };
    }

    public function isRatings(): bool
    {
        return match ($this) {
            self::LETTERBOXD_COLLECTION => false,
            default => true,
        };
    }

    public function isCollection(): bool
    {
        return match ($this) {
            self::LETTERBOXD_COLLECTION => true,
            default => false,
        };
    }

}