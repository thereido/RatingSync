<?php
namespace RatingSync;

require_once "main.php";
require_once "pageHeader.php";
require_once "src/SessionUtility.php";
require_once "src/Film.php";
require_once "src/Filmlist.php";

$username = getUsername();
$filmId = array_value_by_key("i", $_GET);
$ratingIndex = array_value_by_key("ri", $_GET);

$film = Film::getFilmFromDb($filmId, $username);

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
</head>

<body>

<div class="container">
    <?php echo $pageHeader; ?>

    <div id="debug" class="container"></div>

    <div class="row pt-3" id="detail-film">
        <div class="col-auto mr-auto">
            <div class="card">
                <div class="row p-3" id="detail-film">
                    <div class="col-auto">
                        <poster><img></poster>
                    </div>
                    <div class="col pl-0">
                        <detail id="detail"></detail>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php echo $pageFooter; ?>
</div>

<script>
    let pageId = SITE_PAGE.Edit;
    var contextData = JSON.parse('{"films":[]}');
    var RS_URL_BASE = "<?php echo Constants::RS_HOST; ?>";
    var RS_URL_API = RS_URL_BASE + "/php/src/ajax/api.php";
    var OMDB_API_KEY = "<?php echo Constants::OMDB_API_KEY; ?>";
    var TMDB_API_KEY = "<?php echo Constants::TMDB_API_KEY; ?>";
    var IMAGE_PATH_TMDBAPI = "<?php echo Constants::IMAGE_PATH_TMDBAPI; ?>";
    var DATA_API_DEFAULT = "<?php echo Constants::DATA_API_DEFAULT; ?>";
    var username = "<?php getUsername(); ?>";
    getFilmForDetailPage("<?php echo $filmId; ?>", null, null, "<?php echo $film->getContentType(); ?>", "<?php echo $film->getParentId(); ?>", "<?php echo $film->getSeason(); ?>", "<?php echo $film->getEpisodeNumber(); ?>");
</script>

</body>
</html>
