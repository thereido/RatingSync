<?php
// @codingStandardsIgnoreFile
// @codeCoverageIgnoreStart
spl_autoload_register(
    function($class) {
        static $classes = null;
        if ($classes === null) {
            $classes = array(
                'RatingSync\\constants' => '/Constants.php',
                'RatingSync\\export' => '/export.php',
                'RatingSync\\film' => '/Film.php',
                'RatingSync\\http' => '/Http.php',
                'RatingSync\\httpimdb' => '/HttpImdb.php',
                'RatingSync\\imdb' => '/Imdb.php',
                'RatingSync\\jinni' => '/Jinni.php',
                'RatingSync\\main' => '/main.php',
                'RatingSync\\rating' => '/Rating.php',
                'RatingSync\\ratingsyncsite' => 'RatingSyncSite.php',
                'RatingSync\\site' => '/Site.php',
                'RatingSync\\source' => '/Source.php',
                'RatingSync\\httperrorexception' => 'exceptions/HttpErrorException.php',
                'RatingSync\\httpnotfoundexception' => 'exceptions/HttpNotFoundException.php',
                'RatingSync\\httpunauthorizedredirectexception' => 'exceptions/HttpUnauthorizedRedirectException.php'
            );
        }
        $cn = strtolower($class);
        if (isset($classes[$cn])) {
            require __DIR__ . $classes[$cn];
        }
    }
);
// @codeCoverageIgnoreEnd