<?php
namespace RatingSync;

require_once "main.php";
require_once "src/SessionUtility.php";
require_once "src/Film.php";

require_once "src/ajax/getRating.php";

$username = getUsername();

if (array_key_exists("sync", $_GET) && $_GET["sync"] == 1) {
    logDebug("sync $username starting", "ratings.php");
    sync($username);
    logDebug("sync finished", "ratings.php ".__LINE__);
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
    <link href="../css/rs.css" rel="stylesheet">
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
    <form onsubmit="return searchFilm()">
      <input type="text" class="form-control" placeholder="tt0000001" id="searchQuery">
      <input type="submit" class="btn btn-sm btn-primary" value="Search">
    </form>
    <p><span id="debug"></span></p>
    <span id="searchResult"></span>
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
          echo "    <table>\n";
          echo "      <tr>\n";
          echo "        <td>$count. $title ($year)</td>\n";
          echo "      </tr>\n";
          echo "      <tr>\n";
          echo "        <td>" . getRatingHtml($rating) . "</td>\n";
          echo "      </tr>\n";
          echo "      <tr>\n";
          echo "        <td>$date</td>\n";
          echo "      </tr>\n";
          echo "    </table>\n";
          echo "  </td>\n";
          echo "</tr>\n";
      }
      ?>
    </tbody>
  </table>

</div>

<script>
function searchFilm() {
    if (document.getElementById("searchQuery").value == 0) {
        document.getElementById("searchResult").innerHTML = "";
    } else {
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function () {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                /*RT*/// document.getElementById("debug").innerHTML = "readyState=" + xmlhttp.readyState + " status=" + xmlhttp.status;
                document.getElementById("searchResult").innerHTML = xmlhttp.responseText;
            }
            else {
                /*RT*/// document.getElementById("debug").innerHTML = "readyState=" + xmlhttp.readyState + " status=" + xmlhttp.status;
                /*RT*/// document.getElementById("searchResult").innerHTML = xmlhttp.responseText;
            }
        }
        xmlhttp.open("GET", "/php/src/ajax/getSearchFilm.php?q=" + document.getElementById("searchQuery").value, true);
        xmlhttp.send();
    }

    return false;
}
</script>
          
</body>
</html>
