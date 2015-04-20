<?php
/**
 * HMTL page Export
 * Ask for input, do the export, show success/fail
 *
 * INPUT
     Username
     Source
     Format
     Filename
 *
 */
require_once "./main.php";
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>RatingSync Export</title>
    </head>
    <body>

<?php
// define variables and set to empty values
$username = $source = $format = $filename = $returnMsg = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = test_input($_POST["username"]);
    $source = test_input($_POST["source"]);
    $format = test_input($_POST["format"]);
    $filename = test_input($_POST["filename"]);

    /* FIXME - input validation */
    
    if (\RatingSync\export($username, $source, $format, $filename)) {
        $returnMsg = "Export successful!";
    } else {
        $returnMsg = "<b>Something went wrong</b>";
    }
}

function test_input($data) {
     $data = trim($data);
     $data = stripslashes($data);
     $data = htmlspecialchars($data);
     return $data;
}
?>

<h2>RatingSync Export</h2>
<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>"> 
   Username: <input type="text" name="username" value="<?php echo $username; ?>">
   <br><br>
   Export From:
   <input type="radio" name="source" value="jinni" checked>Jinni
   <br><br>
   Format:
   <input type="radio" name="format" value="xml" checked>XML
   <br><br>
   Download File: <input type="text" name="filename" value="<?php echo $filename; ?>">
   <br><br>
   <input type="submit" name="submit" value="Submit"> 
</form>

<?php
echo "<h2>Your Input:</h2>";
echo $returnMsg;
?>
    </body>
</html>
