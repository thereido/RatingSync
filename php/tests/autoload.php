<?php
// @codingStandardsIgnoreFile
// @codeCoverageIgnoreStart
spl_autoload_register(
    function($class) {
        static $classes = null;
        if ($classes === null) {
            $classes = array(
                'RatingSync\\constants' => '/src/Constants.php',
                'RatingSync\\ExportBatch' => '/export.php',
                'RatingSync\\film' => '/src/Film.php',
                'RatingSync\\http' => '/src/Http.php',
                'RatingSync\\httpimdb' => '/src/HttpImdb.php',
                'RatingSync\\imdb' => '/src/Imdb.php',
                'RatingSync\\jinni' => '/src/Jinni.php',
                'RatingSync\\main' => '/main.php',
                'RatingSync\\rating' => '/src/Rating.php',
                'RatingSync\\ratingsyncsite' => '/src/RatingSyncSite.php',
                'RatingSync\\site' => '/src/Site.php',
                'RatingSync\\source' => '/src/Source.php',
                'RatingSync\\httperrorexception' => '/src/exceptions/HttpErrorException.php',
                'RatingSync\\httpnotfoundexception' => '/src/exceptions/HttpNotFoundException.php',
                'RatingSync\\httpunauthorizedredirectexception' => '/src/exceptions/HttpUnauthorizedRedirectException.php'
            );
        }
        $cn = strtolower($class);
        if (isset($classes[$cn])) {
            require __DIR__ . $classes[$cn];
        }
    }
);
// @codeCoverageIgnoreEnd