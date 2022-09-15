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

$ratingTitle = $film->getEpisodeTitle();
if ( empty($ratingTitle) ) {
    $ratingTitle = $film->getTitle();
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
    <script src="../js/editPage.js"></script>
</head>

<body>

<div class="container">
    <?php echo $pageHeader; ?>

    <div id="debug" class="container"></div>
    <div id="alert-placeholder" class="alert-placeholder"></div>

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
                <div class="mt-3 row mx-0" id="add-rating-button">
                    <div class="col-auto ml-auto">
                        <button class="btn btn-primary fas fa-plus fa-xs" data-toggle="modal" data-target="#new-rating-modal" aria-hidden="true"></button>
                    </div>
                </div>

                <div id="edit-ratings" class="mt-1">
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="new-rating-modal" tabindex="-1" role="dialog" aria-labelledby="new-rating-modal-label" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h5>Rate</h5>
                    <h6><?php echo $ratingTitle; ?></h6>
                    <input type="text" id="new-rating-filmid" value="<?php echo $filmId; ?>" hidden>
                    <input type="text" id="new-rating-date" placeholder="m/d/yyyy" value="<?php echo date_create()->format('n/j/Y'); ?>">
                    <input type="text" id="new-rating-score">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button id="new-rating-modal-submit" type="button" class="btn btn-primary" onClick="createRating()">Submit</button>
                </div>
            </div>
        </div>
    </div>

    <?php echo $pageFooter; ?>
</div>

<script>
    <?php echo Constants::echoJavascriptConstants(); ?>
    let pageId = SITE_PAGE.Edit;
    var contextData = JSON.parse('{"films":[]}');
    var username = "<?php getUsername(); ?>";
    getFilmForEditPage("<?php echo $filmId; ?>", null, null, "<?php echo $film->getContentType(); ?>", "<?php echo $film->getParentId(); ?>", "<?php echo $film->getSeason(); ?>", "<?php echo $film->getEpisodeNumber(); ?>");
</script>

</body>
</html>
