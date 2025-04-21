<?php
namespace RatingSync;

use Exception;

abstract class ExternalAdapter
{
    public readonly ExportDestination       $ExportDestination;
    public readonly ExportFileFormat        $ExportFormat;
    public readonly ExportCollectionType    $ExportType;

    protected readonly array                $exportableCollectionTypes;
    protected readonly array                $exportableContentTypes;

    /**
     * @param Film $film
     * @param Rating|null $earliestRating
     * @return ExternalFilm
     */
    abstract public function createExternalFilm( Film $film, Rating|null $earliestRating = null ): ExternalFilm;

    /**
     * @return string
     */
    abstract public function getCsvHeader(): string;

    /**
     * @return ExportDestination
     */
    abstract protected static function exportDestination(): ExportDestination;

    /**
     * @param Film $film
     * @return array Array<string>: An empty return is a valid film. An invalid film gets one or more reasons.
     */
    abstract protected function validateExportableExternalFilm( Film $film ): array;

    /**
     * @param ExportCollectionType $collectionType
     * @param string $className Subclass constructors pass __CLASS__ as the value for the param in the parent constructor
     * @param array $exportableContentTypes Subclass constructors populate this. Default is to support Movies, TV Series, and TV Episodes.
     * @param array $supportedExportCollectionTypes Subclass constructors populate this. Default is to support only Ratings.
     * @param ExportFileFormat $exportFormat Subclass constructors populate this. Default is CSV.
     * @throws Exception
     */
    protected function __construct( ExportCollectionType $collectionType, string $className, array $exportableContentTypes = [], array $supportedExportCollectionTypes = [], ExportFileFormat $exportFormat = ExportFileFormat::CSV )
    {

        if ( $supportedExportCollectionTypes == null || count($supportedExportCollectionTypes) == 0 ) {
            $supportedExportCollectionTypes = [ExportCollectionType::RATINGS];
        }

        if ( $exportableContentTypes == null || count($exportableContentTypes) == 0 ) {
            $exportableContentTypes = [Film::CONTENT_FILM, Film::CONTENT_TV_SERIES, Film::CONTENT_TV_EPISODE];
        }

        if ( !in_array( $collectionType, $supportedExportCollectionTypes ) ) {
            throw new Exception("Export format ($collectionType->value) is not supported for $className adapter");
        }

        $this->ExportDestination            = static::exportDestination();
        $this->ExportFormat                 = $exportFormat;
        $this->ExportType                   = $collectionType;
        $this->exportableCollectionTypes    = $supportedExportCollectionTypes;
        $this->exportableContentTypes       = $exportableContentTypes;


    }

    /**
     * @param ExternalFilm $externalFilm
     * @param Rating|null $rating
     * @return ExportEntry
     * @throws Exception
     */
    public function buildExportEntry( ExternalFilm $externalFilm, Rating|null $rating = null ): ExportEntry
    {
        return $externalFilm->exportEntry( $rating );
    }

    public function getContentTypeFilterForExport(): array
    {
        $types  = [Film::CONTENT_FILM, Film::CONTENT_TV_SERIES, Film::CONTENT_TV_EPISODE];
        $filter = [];
        foreach ( $types as $contentType ) {
            if ( !in_array( $contentType, $this->exportableContentTypes ) ) {
                $filter[] = $contentType;
            }
        }
        return $filter;
    }

    /**
     * @param Film $film
     * @return bool Success/failure
     */
    public function validateExportableFilm( Film $film ): bool
    {
        $invalidReasons = $this->validateExportableExternalFilm( $film );

        if ( ! $this->validateExportableContentType( $film ) ) {
            $invalidReasons[] = "Invalid content type " . $film->getContentType();
        }

        if ( count($invalidReasons) ) {
            $reasons = "";
            foreach ( $invalidReasons as $reason ) {
                $delimiter = $reasons == "" ? "" : ", ";
                $reasons .= $delimiter . $reason;
            }

            $contentTypeMsg = $film->getContentType() == Film::CONTENT_TV_EPISODE ? " - s" . $film->getSeason() . " e" . $film->getEpisodeNumber() : "";
            logDebug("Invalid film for exporting ratings. Reason=\"$reasons\". Skipping this film. FilmId=" . $film->getId() . " " . $film->getTitle() . " (" . $film->getYear() . ")$contentTypeMsg");
            return false;
        }

        return true;
    }

    /**
     * @param Film $film
     * @return bool
     */
    private function validateExportableContentType( Film $film ): bool
    {
        return in_array( $film->getContentType(), $this->exportableContentTypes );
    }

}

abstract class ExternalFilm
{
    protected Film $film;

    /**
     * @param Film $film
     * @return array Array<string>: An empty return is a valid film. An invalid film gets one or more reasons.
     */
    abstract static public function validateExternalFilm( Film $film ): array;

    /**
     * @param Rating|null $rating
     * @return ExportEntry
     * @throws Exception
     */
    abstract public function exportEntry( Rating|null $rating = null ): ExportEntry;

    /**
     * @return string
     */
    public function info(): string
    {
        return "FilmId=" . $this->film->getId() . ", Title=" . $this->film->getTitle() . ", (" . $this->film->getYear() .")";
    }
}
