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
if (array_key_exists("header-search-text", $_POST)) {
    $searchQuery = $_POST['header-search-text'];
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
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>RatingSync</title>
    <link href="../css/bootstrap_rs.min.css" rel="stylesheet">
    <link href="../css/rs.css" rel="stylesheet">
    <?php echo includeJavascriptFiles(); ?>
    <script src="../js/ratings.js"></script>
    <script src="../js/film.js"></script>
</head>

<body>

<div class="container">
    <?php echo $pageHeader; ?>

    <div class='well well-sm'>
        <h2><?php echo $searchPageLabel; ?></h2>
    </div>
    
    <div id="debug"></div>

    <table class="table table-striped">
        <tbody id="search-result-tbody">
        </tbody>
    </table>
    
  <?php echo $pageFooter; ?>
</div>

<script>
    var contextData = JSON.parse('{"films":[]}');
    var RS_URL_BASE = "<?php echo Constants::RS_HOST; ?>";
    var RS_URL_API = RS_URL_BASE + "/php/src/ajax/api.php";
    var OMDB_API_KEY = "<?php echo Constants::OMDB_API_KEY; ?>";
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
