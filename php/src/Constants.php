<?php
/**
 * RatingSync global constants
 */
namespace RatingSync;

date_default_timezone_set('America/New_York');

class Constants
{
    const RS_OUTPUT_URL_PATH            = "/php/src/output/";
    const RS_IMAGE_URL_PATH             = "/image/";
    const SOURCE_JINNI                  = "Jinni";
    const SOURCE_IMDB                   = "IMDb";
    const SOURCE_NETFLIX                = "Netflix";
    const SOURCE_RT                     = "RottenTomatoes";
    const SOURCE_XFINITY                = "xfinity";
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
    const LOGGED_IN_USERNAME            = self::TEST_RATINGSYNC_USERNAME; // Temporary until login works
    const RS_HOST                       = "http://192.168.1.105:55887";

    static function basePath()
    {
        $base = __DIR__;
        if (0 != preg_match('@(.*php).*@', $base, $matches)) {
            $base = $matches[1];
        }

        return $base;
    }
    
    static function outputFilePath()
    {
        return self::basePath() . DIRECTORY_SEPARATOR . "output" . DIRECTORY_SEPARATOR;
    }
    
    static function cacheFilePath()
    {
        return self::basePath() .  DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR;
    }
    
    static function imagePath()
    {
        return self::basePath() .  DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "image" . DIRECTORY_SEPARATOR;
    }
}
?>
