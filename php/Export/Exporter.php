<?php

namespace RatingSync;

use Exception;

require_once "ExportBatch.php";

abstract class Exporter
{
    private const   EXPORT_FILM_LIMIT_DEFAULT       = 10000;
    private const   EXPORT_FILE_ENTRY_LIMIT_DEFAULT = 1000;

    public readonly ExportCollectionType    $Type;

    protected readonly ExternalAdapter  $adapter;

    private readonly ?string            $collectionName;
    private readonly ExportDestination  $destination;
    private readonly ExportFileFormat   $format;

    /**
     * @param string|null $collectionName A null collectionName implies this is for Ratings. Non-null means this is for a Collection.
     * @return array Array<Film>: Films to be exported
     */
    abstract protected function getFilms( int $limit, ?string $collectionName = null ): array;

    /**
     * @param Film $film
     * @return array Array<ExportEntry>: Entries for a film. Based on the export type, it could be one entry (film in a Collection) or one/many (Ratings for the film).
     */
    abstract protected function getEntries(Film $film ): array;


    /**
     * @param ExportDestination $destination External adapter (TMDb, Letterboxd, Trakt, etc)
     * @param string|null $collectionName A null collectionName implies this is for Ratings. Non-null means this is for a Collection.
     * @throws Exception
     */
    public function __construct( ExportDestination $destination, string $collectionName = null )
    {
        $isCollection   = $collectionName !== null && !empty(trim($collectionName));
        $collectionType = $isCollection ? ExportCollectionType::COLLECTION : ExportCollectionType::RATINGS;
        $adapter        = self::getExternalAdapter( $destination, $collectionType );

        $this->adapter          = $adapter;
        $this->collectionName   = $collectionName;
        $this->destination      = $destination;
        $this->format           = $adapter->ExportFormat;
        $this->Type             = $collectionType;
    }

    /**
     * @return array Array<string>: filenames of files exported
     */
    public function export(): array
    {

        $destination        = $this->destination->value;
        $extensionFormat    = $this->format->getExtension();

        $collectionMsg  = $this->collectionName ? "collection '$this->collectionName'" : "ratings";
        logDebug("Export " . getUsername() . " $collectionMsg for importing to $destination in $extensionFormat format");

        $films  = $this->getFilms( self::EXPORT_FILM_LIMIT_DEFAULT, $this->collectionName );
        $export = $this->buildExportBatch( films: $films );

        return $export->saveToDisk( $this->collectionName );

    }

    /**
     * @param array $films Films to be exported
     * @return ExportBatch
     */
    private function buildExportBatch( array $films ): ExportBatch
    {

        $countSuccessfulEntries = 0;
        $export                 = new ExportBatch( adapter: $this->adapter, fileEntryLimit: self::EXPORT_FILE_ENTRY_LIMIT_DEFAULT );
        $fullyExportedFilms     = []; // Type: int - Film IDs
        $partiallyExportedFilms = []; // Type: int - Film IDs
        $unexportedFilms        = []; // Type: int - Film IDs

        foreach ( $films as $film ) {

            // Validation
            $exportThisFilm = $this->adapter->validateExportableFilm( $film );
            if ( ! $exportThisFilm ) {
                $unexportedFilms[] = $film->getId();
                continue;
            }

            // Add entries to the batch
            $entriesFromThisFilm    = $this->getEntries( film: $film );
            try {
                $countSuccessfulEntriesFromThisFilm = $export->addEntries($entriesFromThisFilm);
                $countSuccessfulEntries             += $countSuccessfulEntriesFromThisFilm;
            }
            catch ( Exception $e ) {
                logError( $e->getMessage(), $e );
                break;
            }

            // Logging results for this film
            $wereAllFilmEntriesExported = $countSuccessfulEntriesFromThisFilm == count($entriesFromThisFilm);
            if ( $wereAllFilmEntriesExported ) {
                $fullyExportedFilms[] = $film->getId();
            }
            else if ( $countSuccessfulEntriesFromThisFilm > 0 ) {
                logDebug("Partially exported $countSuccessfulEntriesFromThisFilm entries of " . count($entriesFromThisFilm) . " entries for film id=" . $film->getId() . " " . $film->getTitle() . " (" . $film->getYear() . ")");
                $partiallyExportedFilms[] = $film->getId();
            }
            else {
                $unexportedFilms[] = $film->getId();
                logDebug("Unable to export entries for film id=" . $film->getId() . " " . $film->getTitle() . " (" . $film->getYear() . ")");
            }

        }

        // Logging full results
        logDebug("Exporting a total of $countSuccessfulEntries entries for " . count($fullyExportedFilms) . " film(s) in " . $export->countFiles() . " file(s)");
        if ( count($partiallyExportedFilms) > 0 ) {
            logDebug("Partially exporting entries for " . count($partiallyExportedFilms) . " film(s): " . implode(", ", $partiallyExportedFilms));
        }
        if ( count($unexportedFilms) > 0 ) {
            logDebug("Unable to export entries for " . count($unexportedFilms) . " film(s): " . implode(", ", $unexportedFilms));
        }

        return $export;

    }

    /**
     * @param ExportDestination $destination
     * @param ExportCollectionType $collectionType
     * @return ExternalAdapter
     * @throws Exception
     */
    private static function getExternalAdapter( ExportDestination $destination, ExportCollectionType $collectionType ): ExternalAdapter
    {

        $adapter = match ( $destination ) {
            ExportDestination::IMDB              => new ImdbAdapter( collectionType: $collectionType ),
            ExportDestination::LETTERBOXD        => new LetterboxdAdapter( collectionType: $collectionType ),
            ExportDestination::TMDB              => new TmdbAdapter( collectionType: $collectionType ),
            ExportDestination::TRAKT             => new TraktAdapter( collectionType: $collectionType ),
            //default                              => null,
        };

        if ( $adapter === null ) {
            logDebug("Unknown ExternalAdapter for $destination->name", prefix: __CLASS__ . ":" . __FUNCTION__ . ":" . __LINE__ );
            throw new Exception("Unknown ExternalAdapter for $destination->name");
        }

        return $adapter;
    }

}

class RatingsExporter extends Exporter
{

    /**
     * @param string|null $collectionName A null collectionName implies this is for Ratings. Non-null means this is for a Collection.
     * @return array Array<Film>: Films to be exported
     */
    protected function getFilms( int $limit, ?string $collectionName = null ): array
    {

        $site = new RatingSyncSite( getUsername() );
        $site->setSort( field: RatingSortField::date );
        $site->setSortDirection( direction: SqlSortDirection::descending );
        $site->setContentTypeFilter( $this->adapter->getContentTypeFilterForExport() ); // Filter out TV

        return $site->getFilmsForExport( $limit );

    }

    /**
     * @param Film $film
     * @return array Array<ExportEntry>: Entries for a film. Based on the export type, it could be one entry (film in a Collection) or one/many (Ratings for the film).
     */
    protected function getEntries( Film $film ): array
    {
        $entries = [];

        $imdbId                 = $film->getUniqueName(Constants::SOURCE_IMDB);
        $tmdbId                 = $film->getUniqueName(Constants::SOURCE_TMDBAPI);
        $title                  = $film->getTitle();
        $year                   = $film->getYear();
        $rating                 = $this->Type == ExportCollectionType::RATINGS ? $film->getRating(Constants::SOURCE_RATINGSYNC) : null;
        $inactiveRatings        = $this->Type == ExportCollectionType::RATINGS ? $film->getSource(Constants::SOURCE_RATINGSYNC)->getArchive() : [];

        if ( $imdbId == null && $tmdbId == null ) {
            logDebug("IMDb & TMDb ids are empty for film id=" . $film->getId() . " $title ($year)");
            return [];
        }

        $earliestRating = null;
        if ( count($inactiveRatings) > 0 ) {

            $sortCallback = function (Rating $a, Rating $b) { return Rating::compareByRatingDate($a, $b); };
            uasort($inactiveRatings, $sortCallback);
            $earliestRating = $inactiveRatings[0];

        }

        try {
            $externalFilm = $this->adapter->createExternalFilm( $film, $earliestRating );
        }
        catch ( Exception $e ) {
            logError("Error creating ExternalFilm for film id=" . $film->getId() . " $title ($year)", e: $e );
            return [];
        }

        foreach ( $inactiveRatings as $inactiveRating ) {

            if ( $inactiveRating?->isInitiated() ) {
                try {
                    $entries[] = $this->adapter->buildExportEntry($externalFilm, $inactiveRating);
                } catch (Exception) {
                    logDebug("Error exporting inactive rating for film id=" . $film->getId() . " $title ($year)");
                }
            }

        }

        if ( $rating?->isInitiated() ) {
            try {
                $entries[] = $this->adapter->buildExportEntry($externalFilm, $rating);
            } catch (Exception) {
                logDebug("Error exporting entry for film id=" . $film->getId() . " $title ($year)");
            }
        }

        return $entries;

    }

}

class CollectionExporter extends Exporter
{

    /**
     * @param string|null $collectionName A null collectionName implies this is for Ratings. Non-null means this is for a Collection.
     * @return array Array<Film>: Films to be exported
     */
    protected function getFilms( int $limit, ?string $collectionName = null ): array
    {

        $list = new Filmlist( getUsername(), $collectionName );
        $list->setSort( ListSortField::position );
        $list->setSortDirection( SqlSortDirection::descending );
        $list->setContentFilter( $this->adapter->getContentTypeFilterForExport() );
        $list->initFromDb();

        return $list->getFilms( $limit );

    }

    /**
     * @param Film $film
     * @return array Array<ExportEntry>: Entries for a film. Based on the export type, it could be one entry (film in a Collection) or one/many (Ratings for the film).
     */
    protected function getEntries( Film $film ): array
    {

        $imdbId                 = $film->getUniqueName(Constants::SOURCE_IMDB);
        $tmdbId                 = $film->getUniqueName(Constants::SOURCE_TMDBAPI);
        $title                  = $film->getTitle();
        $year                   = $film->getYear();

        if ( $imdbId == null && $tmdbId == null ) {
            logDebug("IMDb & TMDb ids are empty for film id=" . $film->getId() . " $title ($year)");
            return [];
        }

        try {
            $externalFilm = $this->adapter->createExternalFilm( $film );
        }
        catch (Exception $e) {
            logError("Error creating ExternalFilm for film id=" . $film->getId() . " $title ($year)", e: $e );
            return [];
        }

        try {

            return array( $this->adapter->buildExportEntry( $externalFilm ) );

        } catch (Exception) {
            logDebug("Error exporting entry for film id=" . $film->getId() . " $title ($year)");
            return [];
        }

    }

}