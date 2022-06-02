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
$pageNum = array_value_by_key("p", $_POST);
$sort = array_value_by_key("sort", $_POST);
if (empty($sort)) {
    $sort = "pos";
}
$sortDirection = array_value_by_key("direction", $_POST);
if (empty($sortDirection)) {
    $sortDirection = "desc";
}
$listnames = null;
$filmlistHeader = "";
$filmlistPagination = "";

if (empty($pageNum)) {
    $pageNum = 1;
}

$films = array();
if (!empty($username)) {
    $listnames = Filmlist::getUserListsFromDbByParent($username, false);

    $offerListFilter = true;

    $filmlistHeader = getHtmlUserlistHeader($listnames, $sort, $sortDirection, $listname, null, $offerListFilter);
    $filmlistPagination = getHmtlFilmlistPagination("./userlist.php?l=" . $listname);
}

$pageHeader = getPageHeader(true, $listnames);
$pageFooter = getPageFooter();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo includeHeadHtmlForAllPages(); ?>
    <title><?php echo Constants::SITE_NAME; ?> <?php echo $listname ?></title>
    <link rel="icon" href="<?php echo Constants::FAVICON_URL; ?>">
    <?php if (empty($username)) { echo '<script type="text/javascript">window.location.href = "/php/Login"</script>'; } ?>
    <script src="../js/userlist.js"></script>
    <script src="../js/filmlistHeader.js"></script>
    <script src="../js/film.js"></script>
    <script src="../js/RatingView.js"></script>
</head>

<body>

<div class="container">

  <?php echo $pageHeader; ?>
  <?php echo $filmlistHeader; ?>
  <div><p><span id="debug"></span></p></div>

    <div id="film-table" class="mt-3"></div>

  <?php echo $filmlistPagination; ?>

  <?php echo $pageFooter; ?>
</div>

<script>
    <?php echo Constants::echoJavascriptConstants(); ?>
    let pageId = SITE_PAGE.Userlist;
    var contextData;
    var listname = "<?php echo $listname; ?>";
    var currentPageNum = <?php echo $pageNum; ?>;
    var defaultPageSize = 90;
    if (listname) {
        getFilmsForFilmlist(defaultPageSize, currentPageNum);
    }
    var prevFilmlistFilterParams = getFilmlistFilterParams();
</script>
          
</body>
</html>
