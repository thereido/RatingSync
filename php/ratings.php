<?php
namespace RatingSync;

require_once "main.php";
require_once "src/SessionUtility.php";
require_once "src/Film.php";
require_once "src/Filmlist.php";

require_once "src/ajax/getHtmlFilmlists.php";

$username = getUsername();
$pageSize = 100; // how many films to show in the page
$site = null;
$listname = null;
$films = array();
$pageHeader = getPageHeader();
$pageFooter = getPageFooter();
$filmlistHeader = "";

if (!empty($username)) {
    if (array_key_exists("sync", $_GET) && $_GET["sync"] == 1) {
        logDebug("sync $username starting", "ratings.php");
        sync($username);
        logDebug("sync finished", "ratings.php ".__LINE__);
    }
    $listname = array_value_by_key("l", $_GET);
    $pageNum = array_value_by_key("p", $_GET);
    if (empty($pageNum)) {
        $pageNum = 1;
    }

    $site = new \RatingSync\RatingSyncSite($username);
    if (empty($listname)) {
        $films = $site->getRatings($pageSize, $pageNum);
        $totalRatings = $site->countRatings();
    } else {
        $list = Filmlist::getListFromDb($username, $listname);
        $films = Film::getFilmsByFilmlist($username, $list);
    }

    $filmlistHeader = getHtmlFilmlistsHeader("Your Ratings");
}

// Pagination
$previousClass = "disabled";
$nextClass = "disabled";
$previousPageNum = 1;
$nextPageNum = 2;
$pageNum = array_value_by_key("p", $_GET);
if (empty($pageNum)) {
    $pageNum = 1;
}
if ($pageNum > 1) {
    $previousClass = "";
    $previousPageNum = $pageNum - 1;
}
if ($totalRatings > $pageNum * $pageSize) {
    $nextClass = "";
    $nextPageNum = $pageNum + 1;
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
    <?php if (empty($username)) { echo '<script type="text/javascript">window.location.href = "/php/Login"</script>'; } ?>
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="../js/bootstrap_rs.min.js"></script>
    <script src="../Chrome/constants.js"></script>
    <script src="../Chrome/rsCommon.js"></script>
    <script src="../js/ratings.js"></script>
    <script src="../js/film.js"></script>
</head>

<body onclick="hideFilmDetail()">
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
  <?php echo $pageHeader; ?>

  <div class="well well-sm">
    <h2>Ratings</h2>
    <div><?php echo $filmlistHeader; ?></div>
  </div>

  <div id='rating-detail' class='rating-detail' onMouseEnter="hideable = false;" onMouseLeave="hideable = true;"></div>

<?php
    $filmsJson = "{\"films\":[";
    $delimeter = "";
    $count = 0;
    $row = 0;
    $totalFilms = count($films);
    foreach($films as $film) {
        $beginRow = "";
        if ($count % 12 == 0) {
            $beginRow = "<div class='row'>\n";
        }
        $endRow = "";
        if ($count % 12 == 11 || $count == $totalFilms-1) {
            $endRow = "</div>\n";
        }

        $filmId = $film->getId();
        $title = $film->getTitle();
        $titleNoQuotes = htmlentities($title, ENT_QUOTES);
        $image = Constants::RS_HOST . $film->getImage(Constants::SOURCE_RATINGSYNC);
        $showFilmDetailJS = "showFilmDetail($filmId)";
        $count = $count + 1;
        $uniqueName = $film->getUniqueName(Constants::SOURCE_RATINGSYNC);
        $onClick = "onClick='$showFilmDetailJS'";
        $onMouseEnter = "onMouseEnter='detailTimer = setTimeout(function () { $showFilmDetailJS; }, 500)'";
        $onMouseLeave = "onMouseLeave='clearTimeout(detailTimer)'";
        echo "  $beginRow";
        echo "    <div class='col-xs-6 col-sm-4 col-md-3 col-lg-2' id='$uniqueName'>\n";
        echo "      <poster id='poster-$uniqueName' data-filmId='$filmId'>\n";
        echo "        <img src='$image' alt='$titleNoQuotes' $onClick $onMouseEnter $onMouseLeave />\n";
        echo "      </poster>\n";
        echo "    </div>\n";
        echo "  $endRow";

        $filmsJson .= $delimeter . $film->json_encode(true);
        $delimeter = ",";
    }
    $filmsJson .= "]}";
?>

  <ul class="pager">
    <li class="<?php echo $previousClass; ?>"><a href="./ratings.php?p=<?php echo $previousPageNum; ?>">Previous</a></li>
    <li class="<?php echo $nextClass; ?>"><a href="./ratings.php?p=<?php echo $nextPageNum; ?>">Next</a></li>
  </ul>
    
  <?php echo $pageFooter; ?>
</div>

<script>var contextData = JSON.parse('<?php echo $filmsJson; ?>');</script>
          
</body>
</html>
