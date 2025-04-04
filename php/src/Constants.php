<?php
/**
 * RatingSync global constants
 */
namespace RatingSync;

require_once "DomainConstants.php";

session_start();

class Constants
{
    const RS_OUTPUT_URL_PATH            = "/php/output/";
    const RS_IMAGE_URL_PATH             = "/image/";
    const SOURCE_JINNI                  = "Jinni";
    const SOURCE_IMDB                   = "IMDb";
    const SOURCE_OMDBAPI                = "OMDb";
    const SOURCE_TMDBAPI                = "TMDb";
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
    const DATE_MIN_STR                  = "1000-01-01";
    const IMAGE_PATH_TMDBAPI            = "https://image.tmdb.org/t/p";
    const DATA_API_DEFAULT              = Constants::SOURCE_TMDBAPI;
    const THEME_DEFAULT                 = "dark";

    /* Constants moved to DomainConstants class
     * php.ini include_path must have the path to DomainConstants.php
     */

    // Database
    const DB_HOSTNAME                   = DomainConstants::DB_HOSTNAME;
    const DB_DATABASE                   = DomainConstants::DB_DATABASE;
    const DB_TEST_DATABASE              = DomainConstants::DB_TEST_DATABASE;
    const DB_ADMIN_USER                 = DomainConstants::DB_ADMIN_USER;
    const DB_ADMIN_PWD                  = DomainConstants::DB_ADMIN_PWD;
    const DB_MODE_STANDARD              = DomainConstants::DB_MODE_STANDARD;
    const DB_MODE_TEST                  = DomainConstants::DB_MODE_TEST;
    const DB_MODE                       = DomainConstants::DB_MODE;

    // RatingSync URLs
    const APP_URL                       = DomainConstants::APP_URL;
    const INTERNAL_API_URL              = DomainConstants::INTERNAL_API_URL;

    // API Keys
    const OMDB_API_KEY                  = DomainConstants::OMDB_API_KEY;
    const TMDB_API_KEY                  = DomainConstants::TMDB_API_KEY;

    // Config/Preferences
    const SITE_NAME                     = DomainConstants::SITE_NAME;
    const FAVICON_URL                   = DomainConstants::FAVICON_URL;
    const DISABLE_REGISTER              = DomainConstants::DISABLE_REGISTER;

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

    static function echoJavascriptConstants(): void
    {
        $jsConstants =  "const RS_URL_BASE = \"" . Constants::APP_URL . "\";"                    . "\n";
        $jsConstants .= "const RS_URL_API = RS_URL_BASE + \"/php/src/ajax/api.php\";"            . "\n";
        $jsConstants .= "const OMDB_API_KEY  = \"" . Constants::OMDB_API_KEY . "\";"             . "\n";
        $jsConstants .= "const TMDB_API_KEY  = \"" . Constants::TMDB_API_KEY . "\";"             . "\n";
        $jsConstants .= "const IMAGE_PATH_TMDBAPI  = \"" . Constants::IMAGE_PATH_TMDBAPI . "\";" . "\n";
        $jsConstants .= "const DATA_API_DEFAULT  = \"" . Constants::DATA_API_DEFAULT . "\";"     . "\n";

        echo $jsConstants;
    }

}

enum SetRatingScoreValue {
    case Delete;
    case View;
    case One;
    case Two;
    case Three;
    case Four;
    case Five;
    case Six;
    case Seven;
    case Eight;
    case Nine;
    case Ten;

    private static function asArray(): array {
        return array(-1 => self::Delete, self::View, self::One, self::Two, self::Three, self::Four, self::Five, self::Six, self::Seven, self::Eight, self::Nine, self::Ten);
    }

    public function getScore(): int {

        $arr = self::asArray();
        return array_search( $this, $arr );

    }

    public static function create(?int $score): SetRatingScoreValue {

        $values = self::asArray();

        if ( ! array_key_exists($score, $values) ) {
            throw new \Exception("Invalid RatingScore");
        }

        return $values[$score];

    }
}

?>
