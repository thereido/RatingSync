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
require_once "main.php";
require_once "src/Constants.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>RatingSync Import</title>
    <link href="../css/bootstrap_rs.min.css" rel="stylesheet">
    <link href="../css/jumbotron-narrow.css" rel="stylesheet">
    <script src="../js/bootstrap.min.js"></script>
</head>

<body>    
  
<?php
// define variables and set to empty values
$username = $format = $filename = $success = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $db = \RatingSync\getDatabase();
    $username = $db->real_escape_string($_POST['username']);
    $filename = $_POST["filename"];
    $format = test_input($_POST["format"]);

    // FIXME - input validation

    $success = \RatingSync\import($username, $filename, $format);
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
        <h1>Import Ratings</h1>
      </div>

      <div class="row">
        <div class="col-sm-offset-1 col-sm-10">
        <?php
        if (!is_null($success)) {
            if ($success) {
                echo '<div class="alert alert-success">';
                echo '<strong>Success!</strong>';
                echo '</div>';
            } else {
                echo '<div class="alert alert-warning"><strong>Failure!</strong> Something went wrong.</div>\n';
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
            <span class="input-group-addon" id="username-addon1">Email</span>
            <input type="text" class="form-control" placeholder="you@domain.com" aria-describedby="username-addon1" name="username" value="<?php echo $username; ?>">
          </div>
        </div>
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

      <p/>
      <footer class="footer">
      </footer>
    </div>
</form>

</body>
</html>
