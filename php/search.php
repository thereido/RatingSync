<?php
namespace RatingSync;

require_once "main.php";
require_once "pageHeader.php";
require_once "src/SessionUtility.php";
require_once "src/Film.php";
require_once "src/Filmlist.php";

require_once "src/ajax/getHtmlFilmlists.php";

$username = getUsername();
$pageHeader = getPageHeader();
$pageFooter = getPageFooter();
$filmlistHeader = "";
if (!empty($username)) {
    $filmlistHeader = getHtmlFilmlistsHeader("Search");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>RatingSync</title>
    <link href="../css/bootstrap_rs.min.css" rel="stylesheet">
    <link href="../css/rs.css" rel="stylesheet">
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="../js/bootstrap_rs.min.js"></script>
    <script src="../Chrome/constants.js"></script>
    <script src="../Chrome/rsCommon.js"></script>
    <script src="../js/ratings.js"></script>
    <script src="../js/film.js"></script>
    <script src="../js/search.js"></script>
</head>

<body>

<div class="container">
    <?php echo $pageHeader; ?>
    <?php echo $filmlistHeader; ?>

    <div>
        <form id="search-form" onsubmit="">
            <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-8 col-lg-6"">
                <div class="input-group">
                    <span class="input-group-btn">
                        <button class="btn btn-default" type="button"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></button>
                    </span>
                    <input id="search-text" type="text" class="form-control">
                </div>
            </div>
            </div>
        </form>
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
    var username = "<?php getUsername(); ?>";
    var oldSearchQuery = "";
    setInterval('updateSearch()', 1000);
</script>

</body>
</html>
