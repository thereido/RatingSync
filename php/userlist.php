<?php
namespace RatingSync;

require_once "main.php";
require_once "src/SessionUtility.php";
require_once "src/Film.php";
require_once "src/Filmlist.php";

require_once "src/ajax/getHtmlFilmlists.php";

$username = getUsername();
$listname = array_value_by_key("l", $_GET);
$filmId = array_value_by_key("id", $_GET);
$newList = array_value_by_key("nl", $_GET);
$pageHeader = getPageHeader();
$pageFooter = getPageFooter();
$filmlistHeader = "";

$films = array();
$offerToAddFilmThisList = false;
if (!empty($username)) {
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

    $filmlistHeader = getHtmlFilmlistsHeader($listname);
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
    <?php if (empty($username)) { echo '<script type="text/javascript">window.location.href = "/php/Login"</script>'; } ?>
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="../js/bootstrap_rs.min.js"></script>
    <script src="../Chrome/constants.js"></script>
    <script src="../Chrome/rsCommon.js"></script>
    <script src="../js/ratings.js"></script>
    <script src="../js/userlist.js"></script>
</head>

<body>

<div class="container">
  <?php echo $pageHeader; ?>

  <div class="well well-sm">
    <h2><?php echo $listname; ?></h2>
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

<?php
    $filmsJson = "{\"films\":[";
    $delimeter = "";
    $count = 0;
    $row = 0;
    $totalFilms = count($films);
    foreach($films as $film) {
        $count = $count + 1;
        $column = $count % 12;
        if ($column == 0) {
            $column = 12;
        }

        $beginRow = "";
        $endRow = "";
        if ($column == 1) {
            $beginRow = "<div class='row'>\n";
        } elseif ($column == 12) {
            $endRow = "</div>\n";
        } elseif (count($films) == $count) {
            $endRow = "</div>\n";
        }

        $filmId = $film->getId();
        $title = $film->getTitle();
        $titleNoQuotes = htmlentities($title, ENT_QUOTES);
        $image = Constants::RS_HOST . $film->getImage(Constants::SOURCE_RATINGSYNC);
        $uniqueName = $film->getUniqueName(Constants::SOURCE_RATINGSYNC);
        $onMouseEnter = "onMouseEnter='detailTimer = setTimeout(function () { showFilmDropdownForUserlist($filmId); }, 500)'";
        $onMouseLeave = "onMouseLeave='hideFilmDropdownForUserlist($filmId, detailTimer)'";
        echo "  $beginRow";
        echo "    <div class='col-xs-6 col-sm-4 col-md-3 col-lg-2' id='$uniqueName'>\n";
        echo "      <div class='userlist-film' $onMouseEnter $onMouseLeave>\n";
        echo "        <poster id='poster-$uniqueName' data-filmId='$filmId'>\n";
        echo "          <img src='$image' alt='$titleNoQuotes' />\n";
        echo "          <div id='film-dropdown-$filmId' class='film-dropdown-content film-dropdown-col-$column'></div>\n";
        echo "        </poster>\n";
        echo "        <div class='below-poster' id='poster-extension-$filmId' data-filmId='$filmId'></div>\n";
        echo "      </div>\n";
        echo "    </div>\n";
        echo "  $endRow";

        $filmsJson .= $delimeter . $film->json_encode(true);
        $delimeter = ",";
    }
    $filmsJson .= "]}";
?>

  <?php echo $pageFooter; ?>
</div>

<script>var contextData = JSON.parse('<?php echo $filmsJson; ?>');</script>
          
</body>
</html>
