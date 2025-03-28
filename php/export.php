<?php
/**
 * HMTL page Export
 * Ask for input, do the export, show success/fail
 *
 * INPUT
     Username
     Source
     Format
 *
 */
namespace RatingSync;

require_once "main.php";
require_once "pageHeader.php";
require_once "src/Constants.php";

// define variables and set to empty values
$source = $format = $filename = $success = null;

$username = getUsername();
$pageHeader = getPageHeader();
$pageFooter = getPageFooter();

if (!empty($username)) {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $source = test_input($_POST["source"]);
        $format = test_input($_POST["format"]);

        // FIXME - input validation

        $exportFormat = ExportFormat::CSV_LETTERBOXD;
        if ($format == "imdb") {
            $exportFormat = ExportFormat::CSV_IMDB;
        }
        else if ($format == "trakt") {
            $exportFormat = ExportFormat::JSON_TRAKT;
        }
        else if ($format == "tmdb") {
            $exportFormat = ExportFormat::CSV_TMDB;
        }
    
        $success = \RatingSync\export($username, $exportFormat);
    }
}

function test_input($data)
{
     $data = trim($data);
     $data = stripslashes($data);
     $data = htmlspecialchars($data);
     return $data;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo includeHeadHtmlForAllPages(); ?>
    <title><?php echo Constants::SITE_NAME; ?> Export</title>
    <link rel="icon" href="<?php echo Constants::FAVICON_URL; ?>">
    <?php if (empty($username)) { echo '<script type="text/javascript">window.location.href = "/php/Login"</script>'; } ?>
</head>

<body>  

<form role="form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    <div class="container">
      <?php echo $pageHeader; ?>

      <div class="well" style="text-align:center">
        <h1>Export Ratings</h1>
      </div>

      <div class="row">
        <div class="col-sm-offset-1 col-sm-10">
        <?php
        if (!is_null($success)) {
            if ($success) {
                echo '<div class="alert alert-success">';
                echo '<strong>Success!</strong> <a href="' . \RatingSync\Constants::RS_OUTPUT_URL_PATH . $filename . '">Download</a>';
                echo '</div>';
            } else {
                echo '<div class="alert alert-warning"><strong>Failure!</strong> Something went wrong.</div>\n';
            }
        }
        ?>
        </div> <!-- /col -->
      </div> <!-- /row -->
            
      <div class="row">
        <input type="hidden" name="source" value="ratingsync">
        <div class="col-lg-offset-1 col-lg-5">
          <label>Export format:&nbsp;&nbsp;</label>
          <label class="radio-inline"><input type="radio" name="format" value="letterboxd" checked>Letterboxd</label>
          <label class="radio-inline"><input type="radio" name="format" value="trakt">Trakt</label>
          <label class="radio-inline"><input type="radio" name="format" value="tmdb">TMDb</label>
          <label class="radio-inline"><input type="radio" name="format" value="imdb">IMDb</label>
        </div><!-- /col -->
      </div><!-- /row -->
      
      <p/>
      <div class="row">
        <div class="col-lg-12" style="text-align:center">
          <input type="submit" name="submitBtn" class="btn btn-lg btn-primary" href="#" role="button" value="Export">
        </div>
      </div><!-- /row -->
        
      <?php echo $pageFooter; ?>
    </div>
</form>

<script>
    <?php echo Constants::echoJavascriptConstants(); ?>
    let pageId = SITE_PAGE.Export;
</script>

</body>
</html>
