<?php
/**
 * RatingSync global constants
 */
namespace RatingSync;

class Constants
{
    const RS_OUTPUT_URL_PATH            = "/php/output/";
    const SOURCE_JINNI                  = "Jinni";
    const SOURCE_IMDB                   = "IMDb";
    const SOURCE_RATINGSYNC             = "RatingSync";
    const EXPORT_FORMAT_XML             = "XML";
    const IMPORT_FORMAT_XML             = "XML";
    const USE_CACHE_ALWAYS              = -1;
    const USE_CACHE_NEVER               = 0;
    const DB_DATABASE                   = "db_rs";
    const DB_TEST_DATABASE              = "db_test_rs";
    const DB_ADMIN_USER                 = "admin_rs";
    const DB_MODE_STANDARD              = "STANDARD";
    const DB_MODE_TEST                  = "TEST";
    const DB_MODE                       = self::DB_MODE_TEST;
    const TEST_RATINGSYNC_USERNAME      = "testratingsync";
    
    static function outputFilePath()
    {
        return DIRECTORY_SEPARATOR . "php" . DIRECTORY_SEPARATOR . "output" . DIRECTORY_SEPARATOR;
    }
    
    static function cacheFilePath()
    {
        return __DIR__ .  DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR;
    }
}
?>
