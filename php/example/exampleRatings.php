<?php
$username = "freereido";
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>RatingSync Test - jinni ratings</title>
    </head>
    <body>
        <h1>RatingSync Test - jinni ratings (<?php echo $username; ?>)</h1>
<?php
require_once "../Jinni.php";

$jinni = new \RatingSync\Jinni($username);

$films = $jinni->getRatings(1, 1, true);
echo "<h2>Count: " . count($films) . "</h2>";

echo "<table>";
echo "  <tr><td>Name</td><td>Your Score</td><td>Rated Date</td><td>Genre</td><td>Director</td><td>Content</td><td>pic</td></tr>";
foreach($films as $film) {
    $rating = $film->getRating("Jinni");
    $genres = "";
    foreach ($film->getGenres() as $genre) {
        if (strlen($genres) > 0) $genres = "$genres, ";
        $genres = $genres . $genre;
    }
    echo "<tr>";
    echo "<td>" . $film->getTitle() . " (" . $film->getYear() . ")</td>";
    echo "<td>" . $rating->getYourScore() . "</td>";
    echo "<td>" . $rating->getYourRatingDate()->format("n/j/Y") . "</td>";
    echo "<td>" . $genres . "</td>";
    echo "<td>" . $film->getDirector() . "</td>";
    echo "<td>" . $film->getContentType() . "</td>";
    echo "<td><img src='" . $film->getImage() . "' /></td>";
    echo "</tr>";
}
echo "</table>";

?>
    </body>
</html>
