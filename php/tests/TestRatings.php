<?php
$username = "testratingsync";
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

$films = $jinni->getRatings(1, 1, false);
echo "<h2>Count: " . count($films) . "</h2>";

echo "<table>";
echo "  <tr><td>Name</td><td>Your Score</td><td>Rated Date</td><td>Content</td><td>pic</td></tr>";
foreach($films as $film) {
    $rating = $film->getRating("Jinni");
    echo "<tr>";
    echo "<td>" . $film->getTitle() . "</td>";
    echo "<td>" . $rating->getYourScore() . "</td>";
    echo "<td>" . $rating->getYourRatingDate() . "</td>";
    echo "<td>" . $film->getContentType() . "</td>";
    echo "<td><img src='" . $film->getImage() . "' /></td>";
    echo "</tr>";
}
echo "</table>";

?>
    </body>
</html>
