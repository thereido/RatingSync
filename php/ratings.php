<?php
namespace RatingSync;

require_once "main.php";
require_once "src/SessionUtility.php";
require_once "src/Film.php";

$username = getUsername();

if (array_key_exists("sync", $_GET) && $_GET["sync"] == 1) {
    logDebug("sync $username starting", "ratings.php");
    sync($username);
    logDebug("sync finished", "ratings.php ".__LINE__);
}
$searchFilm = null;
$searchQuery = null;
if (array_key_exists("q", $_GET)) {
    $searchQuery = $_GET['q'];
    logDebug("Search: $searchQuery", "ratings.php ".__LINE__);
    $searchFilm = search($searchQuery, $username);
    if (!empty($searchFilm)) {
        $searchImage = $searchFilm->getImage();
        $searchTitle = $searchFilm->getTitle();
        $searchYear = $searchFilm->getYear();
        $searchRsScore = $searchFilm->getYourScore(Constants::SOURCE_RATINGSYNC);
        $searchImdbLabel = "IMDb users";
        $searchImdbScore = $searchFilm->getRating(Constants::SOURCE_IMDB)->getUserScore();
        $searchImdbYourScore = $searchFilm->getRating(Constants::SOURCE_IMDB)->getYourScore();
        if (!empty($searchImdbYourScore)) {
            $searchImdbLabel = "IMDb you";
            $searchImdbScore = $searchImdbYourScore;
        }
    }
}

$site = new \RatingSync\RatingSyncSite($username);
$films = $site->getRatings();
logDebug("Rating count " . count($films), "ratings.php ".__LINE__);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>RSync: Your Ratings</title>
    <link href="../css/bootstrap_rs.min.css" rel="stylesheet">
    <script src="../js/bootstrap.min.js"></script>
</head>

<body>
<?php

function test_input($data)
{
     $data = trim($data);
     $data = stripslashes($data);
     $data = htmlspecialchars($data);
     return $data;
}
?>

<div class="container">
  <!-- Header -->
  <div class="header clearfix">
    <nav>
      <ul class="nav nav-pills pull-right">
        <li role="presentation" class="active"><a href="/">Home</a></li>
        <li role="presentation">
            <?php
            if (empty($username)) {
                echo '<a id="myaccount-link" href="/php/Login">Login</a>';
            } else {
                echo '<a id="myaccount-link" href="/php/account/myAccount.html">'.$username.'</a>';
            }
            ?>
        </li>
      </ul>
    </nav>
    <h3 class="text-muted">RatingSync</h3>
  </div> <!-- header -->

  <div class="well well-sm">
    <h2>Ratings</h2>
  </div>

  <div>
    <form method="get" action="ratings.php">
      <input type="text" class="form-control" placeholder="tt0000001" name="q" value="<?php echo $searchQuery; ?>">
      <input type="submit" class="btn btn-sm btn-primary" value="Search">
    </form>
    <?php
    if (!empty($searchFilm)) {
        echo "<table align='center'>\n";
        echo "  <tr>\n";
        echo "    <td>\n";
        echo "      <img src='$searchImage' />\n";
        echo "    </td>\n";
        echo "    <td>\n";
        echo "      <table>\n";
        echo "        <tr>\n";
        echo "          <td>$searchTitle ($searchYear)</td><td/>\n";
        echo "        </tr>\n";
        echo "        <tr>\n";
        echo "          <td>You: $searchRsScore</td>\n";
        echo "          <td>$searchImdbLabel: $searchImdbScore</td>\n";
        echo "        </tr>\n";
        echo "      </table>\n";
        echo "    </td>\n";
        echo "  </tr>\n";
        echo "</table>\n";
    } elseif (!empty($searchQuery)) {
        echo "<p>No result</p>";
    }
    ?>
  </div>
    
  <table class="table table-striped">
    <tbody>
      <?php
      $count = 0;
      foreach($films as $film) {
          $count = $count + 1;
          $image = $film->getImage();
          $title = $film->getTitle();
          $year = $film->getYear();
          $rating = $film->getRating(Constants::SOURCE_RATINGSYNC);
          $yourScore = $rating->getYourScore();
          $yourRatingDate = $rating->getYourRatingDate();
          $date = null;
          if (!empty($yourRatingDate)) {
              $date = date_format($yourRatingDate, 'm/d/Y');
          }
          $imdbScore = $film->getRating(Constants::SOURCE_IMDB)->getUserScore();
          echo "<tr>\n";
          echo "  <td><img src='$image' /></td>\n";
          echo "  <td>\n";
          echo "  <table>\n";
          echo "    <tr>\n";
          echo "      <td colspan=2>$count. $title ($year)</td>\n";
          echo "    </tr>\n";
          echo "    <tr>\n";
          echo "      <td>Rated: $yourScore</td>\n";
          echo "      <td>$date</td>\n";
          echo "    </tr>\n";
          echo "  </table>\n";
          echo "</tr>\n";
      }
      ?>
    </tbody>
  </table>

</div>        
</body>
</html>
