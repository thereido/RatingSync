<?php
namespace RatingSync;

require_once "php/src/SessionUtility.php";

require_once "php/main.php";
require_once "php/pageHeader.php";
require_once "php/src/Constants.php";

$pageHeader = getPageHeader();
$pageFooter = getPageFooter();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (array_key_exists("reconnect", $_POST) && $_POST["reconnect"] == 1) {
        Film::reconnectFilmImages();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>RatingSync</title>
    <link href="css/bootstrap_rs.min.css" rel="stylesheet">
    <link href="css/rs.css" rel="stylesheet">
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src="js/bootstrap_rs.min.js"></script>
</head>

<body>
<div class="container">
  <?php echo $pageHeader; ?>

  <div class="well well-sm">
    <h2>RatingSync Pages</h2>
  </div>  
  <div class="row">
    <div class="col-sm-12">
      <ul>
          <li><a href="/php/export.php">Export</a></li>
          <li><a href="/php/import.php">Import to RS</a></li>
          <li><a href="/php/ratings.php">Your Ratings</a></li>
          <li><a href="/php/search.php">Search</a></li>
      </ul>
    </div>
  </div>
  <div class="well well-sm">
    <h2>RatingSync tests</h2>
  </div>
  <div class="row">
    <div class="col-sm-12">
        <form id="reconnectForm" role="form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
            <input hidden id="reconnect" name="reconnect" value="0" />
            <input type="submit" name="submitBtn" class="btn btn-lg btn-primary" href="#" role="button" value="Reconnect Images" onclick="reconnectImages()">
        </form>
    </div>
  </div>
    
  <?php echo $pageFooter; ?>
</div> <!-- container -->
    
<script>
function reconnectImages() {
    document.getElementById("reconnect").value = "1";
}
</script>

</body>
</html>
