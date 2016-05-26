<?php
namespace RatingSync;

require_once "main.php";
require_once "src/SessionUtility.php";
require_once "src/Film.php";
require_once "src/Filmlist.php";

require_once "src/ajax/getHtmlFilm.php";

$username = getUsername();
$listname = array_value_by_key("l", $_GET);
$filmId = array_value_by_key("id", $_GET);
$newList = array_value_by_key("nl", $_GET);

if (!empty($listname) && !empty($filmId)) {
}

$films = array();
$offerToAddFilmThisList = false;
if (empty($listname) && !empty($filmId)) {
    $offerToAddFilmThisList = true;
    $film = Film::getFilmFromDb($filmId, $username);
    if (!empty($film)) {
        $films[] = $film;
    }
} elseif (!empty($listname)) {
    $site = new \RatingSync\RatingSyncSite($username);
    $list = Filmlist::getListFromDb($username, $listname);
    $films = Film::getFilmsByFilmlist($username, $list);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>RS <?php echo $listname ?></title>
    <link href="../css/bootstrap_rs.min.css" rel="stylesheet">
    <link href="../css/rs.css" rel="stylesheet">
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="../js/bootstrap_rs.min.js"></script>
    <script src="../js/ratings.js"></script>
</head>

<body>

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
    <?php echo getHtmlFilmlistsHeader($listname); ?>
  </div>

  <div>
    <form onsubmit="return createFilmlist()">
        <div class="row">
            <div class="col-lg-6">
                <div class="input-group">
                    <span class="input-group-btn">
                        <button class="btn btn-default" type="submit"><span>New list</span></button>
                    </span>
                    <input type="text" class="form-control" id="filmlist-listname">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-1"></div>
            <div class="col-lg-5">
                <?php
                if ($offerToAddFilmThisList) {
                    echo "<input type='checkbox' class='checkbox' id='filmlist-add-this' checked>Add the film to this new list?</input>\n";
                    echo "<input id='filmlist-filmid' value='$filmId' hidden></input>\n";
                }
                ?>
            </div>
        </div>
    </form>
    <p><span id="debug"></span></p>
    <span id="filmlist-create-result"></span>
  </div>
    
  <table class="table table-striped">
    <tbody>
      <?php
      $filmsJson = "{\"films\":[";
      $delimeter = "";
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

          $filmsJson .= $delimeter . $film->json_encode(true);
          $delimeter = ",";
      }
      $filmsJson .= "]}";
      ?>
    </tbody>
  </table>

</div>

<script>var contextData = JSON.parse('<?php echo $filmsJson; ?>');</script>
          
</body>
</html>
