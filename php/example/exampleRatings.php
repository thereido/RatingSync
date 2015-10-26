<?php
$username = "ur60460017";
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>RatingSync Test - IMDb ratings</title>
    </head>
    <body>
        <h1>RatingSync Test - IMDb ratings (<?php echo $username; ?>)</h1>
<?php
require_once "../Imdb.php";

$site = new \RatingSync\IMDb($username);

$films = $site->getRatings(1, 1, false);
echo "<h2>Count: " . count($films) . "</h2>";

echo "<table>";
echo "  <tr><td>Name</td><td>Your Score</td><td>Content</td><td>pic</td></tr>";
foreach($films as $film) {
    $rating = $film->getRating("IMDb");
    echo "<tr>";
    echo "<td>" . $film->getTitle() . "</td>";
    echo "<td>" . $rating->getYourScore() . "</td>";
    //echo "<td>" . $rating->getYourRatingDate()->format("n/j/Y") . "</td>";
    echo "<td>" . $film->getContentType() . "</td>";
    echo "<td><img src='" . $film->getImage() . "' /></td>";
    echo "</tr>";
}
echo "</table>";

?>
    </body>
</html>
