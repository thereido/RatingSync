<?php
// @codingStandardsIgnoreFile
// @codeCoverageIgnoreStart
spl_autoload_register(
    function($class) {
        static $classes = null;
        if ($classes === null) {
            $classes = array(
                'ratingSync\\httpjinni' => '/Rating.php',
                'ratingSync\\httpjinni' => '/Film.php',
                'ratingSync\\httpjinni' => '/Jinni.php',
                'ratingSync\\httpjinni' => '/HttpJinni.php'
            );
        }
        $cn = strtolower($class);
        if (isset($classes[$cn])) {
            require __DIR__ . $classes[$cn];
        }
    }
);
// @codeCoverageIgnoreEnd