<?php
namespace RatingSync;

require_once "main.php";
require_once "pageHeader.php";
require_once "src/SessionUtility.php";
require_once "src/Film.php";
require_once "src/Filmlist.php";

$username = getUsername();
$filmId = array_value_by_key("i", $_GET);
$imdbId = array_value_by_key("imdb", $_GET);
$uniqueNameParam = array_value_by_key("un", $_GET);
$sourceId = array_value_by_key("sid", $_GET);
$contentType = array_value_by_key("ct", $_GET);
$parentId = array_value_by_key("pid", $_GET);
$seasonNum = array_value_by_key("season", $_GET);
$episodeNum = array_value_by_key("en", $_GET);

// There are 3 ways to get uniqueName. Set it in this order...
//  1) Selected suggestion item from the search header (selsug-un)
//  2) uniqueName param (un)
//  3) sourceId param (sid)
$uniqueName = null;
if (empty($uniqueName) && array_key_exists("selsug-un", $_GET)) {
    $uniqueName = $_GET['selsug-un'];
    if (!empty($uniqueName)) {
        $contentType = array_value_by_key("selsug-ct", $_GET);
    }
}
if (empty($uniqueName) && !empty($uniqueNameParam)) {
    $uniqueName = $uniqueNameParam;
}
if (empty($uniqueName) && !empty($sourceId) && !empty($contentType)) {
    $uniqueName = getMediaDbApiClient()->getUniqueNameFromSourceId($sourceId, $contentType);
}

$pageHeader = getPageHeader();
$pageFooter = getPageFooter();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo includeHeadHtmlForAllPages(); ?>
    <title><?php echo Constants::SITE_NAME; ?></title>
    <link rel="icon" href="<?php echo Constants::FAVICON_URL; ?>">
    <?php if (empty($username)) { echo '<script type="text/javascript">window.location.href = "/php/Login"</script>'; } ?>
    <script src="../js/ratings.js"></script>
    <script src="../js/film.js"></script>
    <script src="../js/detailPage.js"></script>
    <script src="../js/RatingView.js"></script>
</head>

<body>

<div class="container">
    <?php echo $pageHeader; ?>
    
    <div id="debug" class="container"></div>

    <div id="alert-placeholder" class="alert-placeholder"></div>

    <div class="row pt-3">
        <div class="col-auto mr-auto">
            <div class="card">
                <div class="row px-3 pt-3 pb-1" id="detail-film">
                    <div id="detail-poster-container" class="col-auto">
                    </div>
                    <div class="col pl-0">
                        <detail id="detail"></detail>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="container pt-2">
        <div id="seasons" hidden>
            <label for="seasonSel">Season:</label>
            <select id="seasonSel" onchange="changeSeasonNum()"></select>
        </div>
        
        <detail-episodes id="episodes"></detail-episodes>
    </div>
    
  <?php echo $pageFooter; ?>
</div>

<script>
    <?php echo Constants::echoJavascriptConstants(); ?>
    let pageId = SITE_PAGE.Detail;
    var contextData = JSON.parse('{"films":[]}');
    var username = "<?php getUsername(); ?>";
    var seasonNumParam = "<?php echo $seasonNum; ?>";
    getFilmForFilmPage("<?php echo $filmId; ?>", "<?php echo $uniqueName; ?>", "<?php echo $imdbId; ?>", "<?php echo $contentType; ?>", "<?php echo $parentId; ?>", "<?php echo $seasonNum; ?>", "<?php echo $episodeNum; ?>");

</script>

</body>
</html>
