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
$pageNum = array_value_by_key("p", $_GET);
$pageHeader = getPageHeader();
$pageFooter = getPageFooter();
$filmlistHeader = "";
$displayNewListInput = "hidden";

if (empty($pageNum)) {
    $pageNum = 1;
}

$films = array();
$offerToAddFilmThisList = false;
if (!empty($username)) {
    if (empty($listname) && !empty($filmId)) {
        $offerToAddFilmThisList = true;
        $film = Film::getFilmFromDb($filmId, $username);
        if (!empty($film)) {
            $films[] = $film;
        }
    }

    $filmlistHeader = getHtmlFilmlistsHeader($listname);

    // New List input will be hidden unless "nl=1"
    if ($newList == 1) {
        $displayNewListInput = "";
    }
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
    <script src="../js/userlist.js"></script>
    <script src="../js/filmlistHeader.js"></script>
    <script src="../js/film.js"></script>
</head>

<body>

<div class="container">
  <?php echo $pageHeader; ?>
  <?php echo $filmlistHeader; ?>

  <div <?php echo $displayNewListInput; ?>>
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

    <div id="film-table"></div>

  <ul id="pagination" class="pager" hidden>
    <li id="previous"><a>Previous</a></li>
    <li id="next"><a>Next</a></li>
  </ul>

  <?php echo $pageFooter; ?>
</div>

<script>
    var contextData;
    var listname = "<?php echo $listname; ?>";
    var currentPageNum = <?php echo $pageNum; ?>;
    var defaultPageSize = 100;
    checkFilterFromUrl();
    getFilmsForFilmlist(defaultPageSize, currentPageNum);
</script>
          
</body>
</html>
