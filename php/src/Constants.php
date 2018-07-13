<?php
/**
 * RatingSync global constants
 */
namespace RatingSync;

require_once "DomainConstants.php";

session_start();

class Constants
{
    const RS_OUTPUT_URL_PATH            = "/php/src/output/";
    const RS_IMAGE_URL_PATH             = "/image/";
    const SOURCE_JINNI                  = "Jinni";
    const SOURCE_IMDB                   = "IMDb";
    const SOURCE_OMDBAPI                = "OMDb";
    const SOURCE_NETFLIX                = "Netflix";
    const SOURCE_RT                     = "RottenTomatoes";
    const SOURCE_XFINITY                = "xfinity";
    const SOURCE_HULU                   = "Hulu";
    const SOURCE_RATINGSYNC             = "RatingSync";
    const SOURCE_AMAZON                 = "Amazon";
    const SOURCE_YOUTUBE                = "YouTube";
    const SOURCE_HBO                    = "HBO";
    const LIST_DEFAULT                  = "Watchlist";
    const RATINGS_PAGE_LABEL            = "Your Ratings";
    const EXPORT_FORMAT_XML             = "XML";
    const IMPORT_FORMAT_XML             = "XML";
    const USE_CACHE_ALWAYS              = -1;
    const USE_CACHE_NEVER               = 0;
    const TEST_RATINGSYNC_USERNAME      = "testratingsync";

    /* Constants moved to DomainConstants class
     * php.ini include_path must have the path to DomainConstants.php
     */
    const DB_DATABASE                   = DomainConstants::DB_DATABASE;
    const DB_TEST_DATABASE              = DomainConstants::DB_TEST_DATABASE;
    const DB_ADMIN_USER                 = DomainConstants::DB_ADMIN_USER;
    const DB_ADMIN_PWD                  = DomainConstants::DB_ADMIN_PWD;
    const DB_MODE_STANDARD              = DomainConstants::DB_MODE_STANDARD;
    const DB_MODE_TEST                  = DomainConstants::DB_MODE_TEST;
    const DB_MODE                       = DomainConstants::DB_MODE;
    const RS_HOST                       = DomainConstants::RS_HOST;
    const OMDB_API_KEY                  = DomainConstants::OMDB_API_KEY;
    const SITE_NAME                     = DomainConstants::SITE_NAME;

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