<?php
namespace RatingSync;

require_once "main.php";
require_once "pageHeader.php";
require_once "src/SessionUtility.php";
require_once "src/Film.php";
require_once "src/Filmlist.php";

$username = getUsername();
$filmId = array_value_by_key("i", $_GET);
$imdbUniqueName = array_value_by_key("imdb", $_GET);
$seasonNum = array_value_by_key("season", $_GET);
if (empty($imdbUniqueName) && array_key_exists("selsug-un", $_GET)) {
    $imdbUniqueName = $_GET['selsug-un'];
}
$pageHeader = getPageHeader();
$pageFooter = getPageFooter();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo Constants::SITE_NAME; ?></title>
    <link href="../css/bootstrap_rs.min.css" rel="stylesheet">
    <link href="../css/rs.css" rel="stylesheet">
    <link rel="icon" href="<?php echo Constants::FAVICON_URL; ?>">
    <?php if (empty($username)) { echo '<script type="text/javascript">window.location.href = "/php/Login"</script>'; } ?>
    <?php echo includeJavascriptFiles(); ?>
    <script src="../js/ratings.js"></script>
    <script src="../js/film.js"></script>
    <script src="../js/detailPage.js"></script>
</head>

<body>

<div class="container">
    <?php echo $pageHeader; ?>
    
    <div id="debug" class="container-fluid"></div>
    
    <detail-film id="detail-film" class="container-fluid">
        <poster><img></poster>
        <div id="detail"></div>
    </detail-film>

    <div id="seasons" class="container-fluid" hidden>
        <div class="form-group">
            <label for="seasonSel">Season:</label>
            <select class="form-control" id="seasonSel" onchange="changeSeasonNum()"></select>
        </div> 
    </div>
    
    <detail-episodes id="episodes" class="container-fluid"></detail-episodes>
    
  <?php echo $pageFooter; ?>
</div>

<script>
    var contextData = JSON.parse('{"films":[]}');
    var RS_URL_BASE = "<?php echo Constants::RS_HOST; ?>";
    var RS_URL_API = RS_URL_BASE + "/php/src/ajax/api.php";
    var OMDB_API_KEY = "<?php echo Constants::OMDB_API_KEY; ?>";
    var username = "<?php getUsername(); ?>";
    var seasonNumParam = "<?php echo $seasonNum; ?>";
    getFilmForDetailPage("<?php echo $filmId; ?>", "<?php echo $imdbUniqueName; ?>");
</script>

</body>
</html>
