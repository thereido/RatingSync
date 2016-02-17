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
require_once "main.php";
require_once "src/Constants.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>RatingSync Export</title>
    <link href="../css/bootstrap_rs.min.css" rel="stylesheet">
    <link href="../css/jumbotron-narrow.css" rel="stylesheet">
    <script src="../js/bootstrap.min.js"></script>
</head>

<body>    
  
<?php
// define variables and set to empty values
$username = $source = $format = $filename = $success = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = test_input($_POST["username"]);
    $source = test_input($_POST["source"]);

    // FIXME - input validation
    
    $filename = \RatingSync\export($username, $source, $format);
    if (empty($filename)) {
        $success = false;
    } else {
        $success = true;
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

<form role="form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    <div class="container">
      <div class="header clearfix">
        <nav>
          <ul class="nav nav-pills pull-right">
            <li role="presentation" class="active"><a href="/">Home</a></li>
          </ul>
        </nav>
        <h3 class="text-muted">RatingSync</h3>
      </div>

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
        <div class="col-lg-offset-1 col-lg-5">
          <label>Export from</label>
          <label class="radio-inline"><input type="radio" name="source" value="ratingsync" checked>RatingSync</label>
          <label class="radio-inline"><input type="radio" name="source" value="imdb">IMDb</label>
        </div><!-- /col -->
      </div><!-- /row -->
        
      <p></p>
      <div class="row">
        <div class="col-sm-12">
          <div class="input-group">
            <span class="input-group-addon" id="username-addon1">Username</span>
            <input type="text" class="form-control" placeholder="Username" aria-describedby="username-addon1" name="username" value="<?php echo $username; ?>">
          </div>
        </div>
      </div><!-- /row -->
      
      <p/>
      <div class="row">
        <div class="col-lg-12" style="text-align:center">
          <input type="submit" name="submitBtn" class="btn btn-lg btn-primary" href="#" role="button" value="Export">
        </div>
      </div><!-- /row -->

      <p/>
      <footer class="footer">
      </footer>
    </div>
</form>

</body>
</html>
