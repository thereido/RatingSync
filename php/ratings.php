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
    $filmlistHeader = getHtmlFilmlistsHeader($listnames, null, Constants::RATINGS_PAGE_LABEL);
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
    <title><?php echo Constants::SITE_NAME; ?> Ratings</title>
    <link href="../css/bootstrap_rs.min.css" rel="stylesheet">
    <link href="../css/rs.css" rel="stylesheet">
    <link rel="icon" href="<?php echo Constants::FAVICON_URL; ?>">
    <?php if (empty($username)) { echo '<script type="text/javascript">window.location.href = "/php/Login"</script>'; } ?>
    <?php echo includeJavascriptFiles(); ?>
    <script src="../js/ratings.js"></script>
    <script src="../js/filmlistHeader.js"></script>
    <script src="../js/film.js"></script>
    <script src="../js/userlist.js"></script>
</head>

<div class="container">
  <?php echo $pageHeader; ?>
  <?php echo $filmlistHeader; ?>

    <div id="film-table"></div>
    
  <?php echo $filmlistPagination; ?>
    
  <?php echo $pageFooter; ?>
</div>

<script>
var contextData;
var currentPageNum = <?php echo $pageNum; ?>;;
var defaultPageSize = 96;
var OMDB_API_KEY = "<?php echo Constants::OMDB_API_KEY; ?>";
var TMDB_API_KEY = "<?php echo Constants::TMDB_API_KEY; ?>";
var IMAGE_PATH_TMDBAPI = "<?php echo Constants::IMAGE_PATH_TMDBAPI; ?>";
var DATA_API_DEFAULT = "<?php echo Constants::DATA_API_DEFAULT; ?>";
var prevFilmlistFilterParams = getFilmlistFilterParams();
getRsRatings(defaultPageSize, <?php echo $pageNum; ?>);
</script>
          
</body>
</html>
