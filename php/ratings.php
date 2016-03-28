<?php
namespace RatingSync;

require_once "main.php";
require_once "src/SessionUtility.php";
require_once "src/Film.php";
require_once "src/Filmlist.php";

require_once "src/ajax/getHtmlRating.php";
require_once "src/ajax/getHtmlFilm.php";

$username = getUsername();

if (array_key_exists("sync", $_GET) && $_GET["sync"] == 1) {
    logDebug("sync $username starting", "ratings.php");
    sync($username);
    logDebug("sync finished", "ratings.php ".__LINE__);
}
$listname = array_value_by_key("l", $_GET);

$site = new \RatingSync\RatingSyncSite($username);
$films = array();
if (empty($listname)) {
    $films = $site->getRatings();
} else {
    $list = Filmlist::getListFromDb($username, $listname);
    $films = Film::getFilmsByFilmlist($username, $list);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>RSync: Your Ratings</title>
    <link href="../css/bootstrap_rs.min.css" rel="stylesheet">
    <link href="../css/rs.css" rel="stylesheet">
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="../js/bootstrap_rs.min.js"></script>
    <script src="../js/ratings.js"></script>
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
    <div><?php echo getHtmlFilmlistsHeader("Your Ratings"); ?></div>
  </div>

  <div>
    <form onsubmit="return searchFilm()">
        <div class="row">
        <div class="col-lg-6">
        <div class="input-group">
            <span class="input-group-btn">
                <button class="btn btn-default" type="button"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></button>
            </span>
            <input type="text" class="form-control" placeholder="tt0000001">
        </div>
        </div>
        </div>
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
          $uniqueName = $film->getUniqueName(Constants::SOURCE_RATINGSYNC);
          echo "<tr>\n";
          echo "  <td>\n";
          echo "    <span id='$uniqueName'>\n";
          echo getHtmlFilm($film, $count, true, $listname);
          echo "    </span>\n";
          echo "  </td>\n";
          echo "</tr>\n";
      }
      ?>
    </tbody>
  </table>

</div>
          
</body>
</html>
