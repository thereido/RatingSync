<?php
namespace RatingSync;

require_once "main.php";
require_once "pageHeader.php";
require_once "src/SessionUtility.php";
require_once "src/Film.php";
require_once "src/Filmlist.php";

$username = getUsername();
$filmId = array_value_by_key("i", $_GET);

$film = Film::getFilmFromDb($filmId, $username);
$internalUniqueName = $film->getUniqueName(Constants::SOURCE_RATINGSYNC);
$currentRating = $film->getRating(Constants::SOURCE_RATINGSYNC);
$currentRatingDate = $currentRating?->getYourRatingDate();
$defaultNewRatingScore = 0;
if ( $currentRatingDate?->format('Ymd') == date_create()->format('Ymd') ) {
    $defaultNewRatingScore = $currentRating->getYourScore();
}
$defaultNewRatingDate = date_create()->format('Y-m-d');
$originalRatingDateStr = $currentRatingDate?->format('Y-m-d');

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
                <div class="mt-3 row mx-0" id="edit-ratings-header">
                    <div class="col">
                        <h4>Rating Archive</h4>
                    </div>
                    <div class="col-auto ml-auto my-auto">
                        <button class="btn btn-primary fas fa-plus fa-xs" data-toggle="modal" data-target="#new-rating-modal" onclick="javascript: populateNewRatingModal(<?php echo $defaultNewRatingScore; ?>, '<?php echo $defaultNewRatingDate; ?>')" aria-hidden="true"></button>
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
                <form action="javascript: editRatingCreate()">
                    <div class="modal-body">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h5>Rate</h5>
                        <h6><?php echo $ratingTitle; ?></h6>
                        <input type="text" id="new-rating-filmid" value="<?php echo $filmId; ?>" hidden>
                        <input type="text" id="new-rating-original-date" value="<?php echo $originalRatingDateStr; ?>" hidden>
                        <input type="text" id="new-rating-uniquename" value="<?php echo $internalUniqueName; ?>" hidden>
                        <input type="date" id="new-rating-date" value="<?php echo $defaultNewRatingDate; ?>" required max="<?php echo date_create()->format('Y-m-d') ?>" min="1850-01-01">
                        <input type="text" id="new-rating-score" value="<?php echo $defaultNewRatingScore; ?>" hidden="true">
                        <rating-stars id="new-rating-stars" class="rating-stars">
                            <span class="rating-star fa-star far fa-xs" id="new-rating-star-1" data-score="1" aria-hidden="true"></span><span class="rating-star fa-star far fa-xs" id="new-rating-star-2" data-score="2" aria-hidden="true"></span><span class="rating-star fa-star far fa-xs" id="new-rating-star-3" data-score="3" aria-hidden="true"></span><span class="rating-star fa-star far fa-xs" id="new-rating-star-4" data-score="4" aria-hidden="true"></span><span class="rating-star fa-star far fa-xs" id="new-rating-star-5" data-score="5" aria-hidden="true"></span><span class="rating-star fa-star far fa-xs" id="new-rating-star-6" data-score="6" aria-hidden="true"></span><span class="rating-star fa-star far fa-xs" id="new-rating-star-7" data-score="7" aria-hidden="true"></span><span class="rating-star fa-star far fa-xs" id="new-rating-star-8" data-score="8" aria-hidden="true"></span><span class="rating-star fa-star far fa-xs" id="new-rating-star-9" data-score="9" aria-hidden="true"></span><span class="rating-star fa-star far fa-xs" id="new-rating-star-10" data-score="10" aria-hidden="true"></span>
                            <score class="pl-1">
                                <yourscore id="new-rating-your-score">
                                    <span class="score-invisible">1</span><span>-</span>
                                </yourscore>
                                <of-possible>/10</of-possible>
                            </score>
                        </rating-stars>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button id="new-rating-modal-submit" type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
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
    const defaultNewRatingScore = <?php echo $defaultNewRatingScore; ?>;
    const defaultNewRatingDate = "<?php echo date_create()->format('Y-m-d'); ?>";
    getFilmForEditPage("<?php echo $filmId; ?>", null, null, "<?php echo $film->getContentType(); ?>", "<?php echo $film->getParentId(); ?>", "<?php echo $film->getSeason(); ?>", "<?php echo $film->getEpisodeNumber(); ?>");
    addNewRatingListeners();
</script>

</body>
</html>
