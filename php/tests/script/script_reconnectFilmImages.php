<?php
namespace RatingSync;

require_once "../../Film.php";
require_once "../../Constants.php";

echo "Begin\n";
Film::reconnectFilmImages();
echo "\nEnd";
?>
