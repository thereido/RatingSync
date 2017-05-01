<?php
namespace RatingSync;

require_once "main.php";
require_once "pageHeader.php";
require_once "src/SessionUtility.php";
require_once "src/Film.php";
require_once "src/Filmlist.php";

require_once "src/ajax/getHtmlFilmlists.php";

$username = getUsername();
$listname = array_value_by_key("l", $_GET);
if (empty($listname)) {
    $listname = array_value_by_key("l", $_POST);
}
$filmId = array_value_by_key("id", $_GET);
$newList = array_value_by_key("nl", $_GET);
$pageNum = array_value_by_key("p", $_POST);
$listnames = null;
$filmlistHeader = "";
$filmlistPagination = "";
$filmlistSelectOptions = "<option>---</option>";
$displayNewListInput = "hidden";

if (empty($pageNum)) {
    $pageNum = 1;
}

$films = array();
$offerToAddFilmThisList = false;
if (!empty($username)) {
    $listnames = Filmlist::getUserListnamesFromDbByParent($username);
    if (empty($listname) && !empty($filmId)) {
        $offerToAddFilmThisList = true;
        $film = Film::getFilmFromDb($filmId, $username);
        if (!empty($film)) {
            $films[] = $film;
        }
    }

    $filmlistHeaderName = $listname;
    $offerListFilter = true;

    // New List input will be hidden unless "nl=1"
    if ($newList == 1) {
        $displayNewListInput = "";
        $filmlistHeaderName = "Create New List";
        $offerListFilter = false;
        $filmlistSelectOptions .= getHtmlFilmlistSelectOptions($listnames);
    }

    $filmlistHeader = getHtmlFilmlistsHeader($listnames, $listname, $filmlistHeaderName, $offerListFilter);
    $filmlistPagination = getHmtlFilmlistPagination("./userlist.php?l=" . $listname);
}

$pageHeader = getPageHeader(true, $listnames);
$pageFooter = getPageFooter();
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
    <?php echo includeJavascriptFiles(); ?>
    <script src="../js/userlist.js"></script>
    <script src="../js/filmlistHeader.js"></script>
    <script src="../js/film.js"></script>
</head>

<body>

<div class="container">

  <?php echo $pageHeader; ?>
  <?php echo $filmlistHeader; ?>
    
  <div class="panel-body" <?php echo $displayNewListInput; ?>>
    <div class="row">
        <div class="col-lg-6">
            <form onsubmit="return createFilmlist()">
                <div class="form-group">
                    <div class="input-group">
                        <span class="input-group-btn">
                            <button class="btn btn-default" type="button" disabled><span>Sub-list of</span></button>
                        </span>
                        <select class="form-control" id="filmlist-parent">
                            <?php echo $filmlistSelectOptions; ?>
                        </select>
                    </div>
				</div>
                <div class="form-group">
                    <div class="input-group">
                        <span class="input-group-btn">
                            <button class="btn btn-default" type="button" disabled><span>New list</span></button>
                        </span>
                        <input type="text" class="form-control" id="filmlist-listname">
                        <span class="input-group-btn">
                            <button class="btn btn-default" type="submit"><span>Submit</span></button>
                        </span>
                    </div>
                </div>
                <div class="form-group">
                    <?php
                    if ($offerToAddFilmThisList) {
                        echo "<input type='checkbox' class='checkbox' id='filmlist-add-this' checked>Add the film to this new list?</input>\n";
                        echo "<input id='filmlist-filmid' value='$filmId' hidden></input>\n";
                    }
                    ?>
                </div>
            </form>
        </div>
    </div>
    <p><span id="debug"></span></p>
    <span id="filmlist-create-result"></span>
  </div>

    <div id="film-table"></div>

  <?php echo $filmlistPagination; ?>

  <?php echo $pageFooter; ?>
</div>

<script>
    var contextData;
    var listname = "<?php echo $listname; ?>";
    var currentPageNum = <?php echo $pageNum; ?>;
    var defaultPageSize = 100;
    if (listname) {
        getFilmsForFilmlist(defaultPageSize, currentPageNum);
    }
    var prevFilmlistFilterParams = getFilmlistFilterParams();
</script>
          
</body>
</html>
