<?php
namespace RatingSync;

require_once "main.php";
require_once "pageHeader.php";
require_once "src/SessionUtility.php";
require_once "src/Film.php";
require_once "src/Filmlist.php";

require_once "src/ajax/getHtmlFilmlists.php";

$username = getUsername();
$listnames = null;
$filmlistHeader = "";
$filmlistPagination = "";
$pageNum = array_value_by_key("p", $_POST);
if (empty($pageNum)) {
    $pageNum = 1;
}

if (!empty($username)) {
    $listnames = Filmlist::getUserListsFromDbByParent($username, false);
    $filmlistHeader = getHtmlFilmlistsHeader($listnames, null, "Your Ratings");
    $filmlistPagination = getHmtlFilmlistPagination("./ratings.php");
}

$pageHeader = getPageHeader(true, $listnames);
$pageFooter = getPageFooter();
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
    <?php echo includeJavascriptFiles(); ?>
    <script src="../js/ratings.js"></script>
    <script src="../js/filmlistHeader.js"></script>
    <script src="../js/film.js"></script>
</head>

<body onclick="hideFilmDetail()">

<div class="container">
  <?php echo $pageHeader; ?>
  <?php echo $filmlistHeader; ?>

  <div id='rating-detail' class='rating-detail' onMouseEnter="hideable = false;" onMouseLeave="hideable = true;"></div>

    <div id="film-table"></div>
    
  <?php echo $filmlistPagination; ?>
    
  <?php echo $pageFooter; ?>
</div>

<script>
var contextData;
var currentPageNum = <?php echo $pageNum; ?>;;
var defaultPageSize = 96;
var OMDB_API_KEY = "<?php echo Constants::OMDB_API_KEY; ?>";
var prevFilmlistFilterParams = getFilmlistFilterParams();
getRsRatings(defaultPageSize, <?php echo $pageNum; ?>);
</script>
          
</body>
</html>
