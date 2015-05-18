<?php
/**
 * RatingSync global constants
 */
namespace RatingSync;

class Constants
{
    const RS_OUTPUT_URL_PATH = "/php/output/";
    const SOURCE_JINNI      = "Jinni";
    const SOURCE_IMDB       = "IMDb";
    const SOURCE_RATINGSYNC = "RatingSync";
    
    static function outputFilePath()
    {
        return DIRECTORY_SEPARATOR . "php" . DIRECTORY_SEPARATOR . "output" . DIRECTORY_SEPARATOR;
    }
}
?>
