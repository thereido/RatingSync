<?php
/**
 * RatingSync global constants
 */
namespace RatingSync;

class Constants
{
    const RS_OUTPUT_URL_PATH = "/php/output/";
    
    static function outputFilePath()
    {
        return DIRECTORY_SEPARATOR . "php" . DIRECTORY_SEPARATOR . "output" . DIRECTORY_SEPARATOR;
    }
}
?>
