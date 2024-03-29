<?php
namespace RatingSync;

require_once "main.php";
require_once "pageHeader.php";
require_once "src/SessionUtility.php";
require_once "src/Film.php";
require_once "src/Filmlist.php";

require_once "src/ajax/getHtmlFilmlists.php";

$username = getUsername();
$searchQuery = "";
$searchDomain = "";
$searchPageLabel = "Search";
if (array_key_exists("search", $_GET)) {
    $searchQuery = $_GET['search'];
}
if (array_key_exists("sd", $_GET)) {
    $searchDomain = $_GET['sd'];
}
if ($searchDomain == "ratings") {
    $searchPageLabel = "Search Your Ratings";
} else if ($searchDomain == "list") {
    $searchPageLabel = "Search " . Constants::LIST_DEFAULT;
} else if ($searchDomain == "both") {
    $searchPageLabel = "Search Your Ratings & " . Constants::LIST_DEFAULT;
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
    <script src="../js/RatingView.js"></script>
</head>

<body>

<div class="container">
    <?php echo $pageHeader; ?>
    <div id="alert-placeholder" class="alert-placeholder"></div>

    <div class='card mt-3'>
        <div class="card-body">
            <h2><?php echo $searchPageLabel; ?></h2>
        </div>
    </div>
    
    <div id="debug"></div>

    <div class="row">
        <div class="col-auto mr-auto" id="search-results"></div>
    </div>
    
  <?php echo $pageFooter; ?>
</div>

<script>
    <?php echo Constants::echoJavascriptConstants(); ?>
    let pageId = SITE_PAGE.Search;
    var contextData = JSON.parse('{"films":[]}');
    var username = "<?php getUsername(); ?>";
    var oldSearchQuery = "";
    var pageParamSearchDomain = "<?php echo $searchDomain; ?>";
    if (pageParamSearchDomain != "" && pageParamSearchDomain != searchDomain) {
        updateHeaderSearchDomain(pageParamSearchDomain);
    }
    showHeaderSearchInput("<?php echo $searchQuery; ?>");
    fullSearch("<?php echo $searchQuery; ?>");
</script>

</body>
</html>
