<?php
/**
 * HMTL page Import
 * Ask for input, import ratings to RatingSync site, show success/fail
 *
 * INPUT
     Username
     Filename
     Format
 *
 */
namespace RatingSync;

require_once "main.php";
require_once "pageHeader.php";
require_once "src/Constants.php";

// define variables and set to empty values
$format = $filename = $success = null;
$username = getUsername();
$pageHeader = getPageHeader();
$pageFooter = getPageFooter();

if (!empty($username)) {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $filename = $_POST["filename"];
        $format = $_POST["format"];

        $success = \RatingSync\import($username, $filename, $format);
        if ($success) {
            header('Location: ratings.php?sync=1');
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>RatingSync Import</title>
    <link href="../css/bootstrap_rs.min.css" rel="stylesheet">
    <link href="../css/jumbotron-narrow.css" rel="stylesheet">
    <?php if (empty($username)) { echo '<script type="text/javascript">window.location.href = "/php/Login"</script>'; } ?>
    <?php echo includeJavascriptFiles(); ?>
</head>

<body>  

<form role="form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    <div class="container">
      <?php echo $pageHeader; ?>

      <div class="well" style="text-align:center">
        <h1>Import Ratings</h1>
      </div>

      <div class="row">
        <div class="col-sm-offset-1 col-sm-10">
        <?php
        if (!is_null($success)) {
            if ($success) {
                echo "<div id='successDiv' class='alert alert-success'>\n";
                echo "  <strong>Success!</strong>\n";
                echo "</div>\n";
            } else {
                echo "<div class='alert alert-warning'><strong>Failure!</strong> Something went wrong.</div>\n";
            }
        }
        ?>
        </div> <!-- /col -->
      </div> <!-- /row -->
            
      <div class="row">
        <div class="col-lg-offset-6 col-lg-5">
          <label>Format</label>
          <label class="col-lg-offset-1 radio-inline"><input type="radio" name="format" value="XML" checked>XML</label>
        </div><!-- /col -->
      </div><!-- /row -->
        
      <p></p>
      <div class="row">
        <div class="col-sm-12">
          <div class="input-group">
            <span class="input-group-addon" id="filename-addon1">Filename</span>
            <input type="text" class="form-control" placeholder="Filename" aria-describedby="filename-addon1" name="filename" value="<?php echo $filename; ?>">
          </div>
        </div>
      </div><!-- /row -->
      
      <p/>
      <div class="row">
        <div class="col-lg-12" style="text-align:center">
          <input type="submit" name="submitBtn" class="btn btn-lg btn-primary" href="#" role="button" value="Import">
        </div>
      </div><!-- /row -->

      <?php echo $pageFooter; ?>
    </div>
</form>

<script>
    var OMDB_API_KEY = "<?php echo Constants::OMDB_API_KEY; ?>";
</script>

</body>
</html>
