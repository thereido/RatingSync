<?php

namespace RatingSync;

use Exception;

require_once "Exporter.php";

class ExporterFactory
{

    /**
     * @param ExportDestination $destination
     * @param string|null $collectionName
     * @return Exporter
     * @throws Exception
     */
    public static function create( ExportDestination $destination, ?string $collectionName = null ): Exporter
    {

        if ( is_null($collectionName) || empty(trim($collectionName)) ) {
            return new RatingsExporter( $destination );
        }
        else {
            return new CollectionExporter( $destination, $collectionName );
        }

    }

}