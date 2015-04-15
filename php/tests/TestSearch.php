<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>Search Jinni Test</title>
    </head>
<body>

<h1>Search Film Suggestions for 'Wolf Wall Street'</h1>

<?php
require_once "../Jinni.php";

$jinni = new \RatingSync\Jinni("change_to_a_real_username");

$searchResults = $jinni->getSearchSuggestions('Wolf Wall Street', \RatingSync\Film::CONTENT_FILM);

foreach ($searchResults as $film) {
    $name = $film->getTitle();
    echo "Name: $name<br>";
}
?>

</body>
</html>

