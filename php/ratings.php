<?php
namespace RatingSync;

require_once "./main.php";
require_once "./SessionUtility.php";

$username = "testratingsync";
logDebug("Begin $username", "rating.php");
$site = new \RatingSync\RatingSyncSite($username);
$films = $site->getRatings();
logDebug("Count films: " . count($films), "rating.php");
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
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $success = \RatingSync\sync($username);
}

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
            $username = SessionUtility::getUsername();
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

<form role="form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    <div class="col-lg-12" style="text-align:center">
        <input type="submit" name="submitBtn" class="btn btn-lg btn-primary" href="#" role="button" value="Sync">
    </div>
</form>
    
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

<?php
logDebug("End", "rating.php");
?>
