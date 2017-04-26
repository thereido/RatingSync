<?php
namespace RatingSync;

require_once "main.php";
require_once "pageHeader.php";
require_once "src/SessionUtility.php";
require_once "src/Film.php";
require_once "src/Filmlist.php";

$username = getUsername();
$imdbUniqueName = array_value_by_key("imdb", $_GET);
if (array_key_exists("imdb", $_GET)) {
    $imdbUniqueName = $_GET['imdb'];
} elseif (array_key_exists("selected-suggestion-uniquename", $_POST)) {
    $imdbUniqueName = $_POST['selected-suggestion-uniquename'];
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
    <script src="../js/detailPage.js"></script>
</head>

<body>

<div class="container">
    <?php echo $pageHeader; ?>
    
    <div id="debug"></div>
    
    <detail-film id="detail-film">
        <poster><img width="150px"></poster>
        <div id="detail"></div>
    </detail-film>
    
  <?php echo $pageFooter; ?>
</div>

<script>
    var contextData = JSON.parse('{}');
    var RS_URL_BASE = "<?php echo Constants::RS_HOST; ?>";
    var RS_URL_API = RS_URL_BASE + "/php/src/ajax/api.php";
    var username = "<?php getUsername(); ?>";
    getFilmForDetailPage("<?php echo $imdbUniqueName; ?>");
</script>

</body>
</html>
