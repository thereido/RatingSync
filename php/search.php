<?php
namespace RatingSync;

require_once "main.php";
require_once "src/SessionUtility.php";
require_once "src/Film.php";
require_once "src/Filmlist.php";

require_once "src/ajax/getHtmlFilmlists.php";

$username = getUsername();
$query = array_value_by_key("q", $_GET);
$filmId = array_value_by_key("id", $_GET);

$films = array();
if (!empty($filmId)) {
    $film = Film::getFilmFromDb($filmId, $username);
    if (!empty($film)) {
        $films[] = $film;
    }
} elseif (!empty($query)) {
    /*RT*/
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>RS <?php echo $listname ?></title>
    <link href="../css/bootstrap_rs.min.css" rel="stylesheet">
    <link href="../css/rs.css" rel="stylesheet">
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="../js/bootstrap_rs.min.js"></script>
    <script src="../Chrome/constants.js"></script>
    <script src="../Chrome/rsCommon.js"></script>
    <script src="../js/ratings.js"></script>
</head>

<body>

<div class="container">
  <!-- Header -->
  <div class="header clearfix">
    <nav>
      <ul class="nav nav-pills pull-right">
        <li role="presentation" class="active"><a href="/">Home</a></li>
        <li role="presentation">
            <?php
            if (empty($username)) {
                echo '<a id="myaccount-link" href="/php/Login">Login</a>';
            } else {
                echo '<a id="myaccount-link" href="/php/account/myAccount.html">'.$username.'</a>';
            }
            ?>
        </li>
      </ul>
    </nav>
    <h3 class="text-muted">RatingSync</h3>
  </div> <!-- header -->

  <div class="well well-sm">
    <h2>Search</h2>
    <div><?php echo getHtmlFilmlistsHeader(); ?></div>
  </div>

  <div>
    <form onsubmit="return searchFilms()">
        <div class="row">
        <div class="col-lg-6">
        <div class="input-group">
            <span class="input-group-btn">
                <button class="btn btn-default" type="button"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></button>
            </span>
            <input type="text" class="form-control">
        </div>
        </div>
        </div>
    </form>
    <p><span id="debug"></span></p>
    <span id="searchResult"></span>
  </div>
    
  <table class="table table-striped">
    <tbody>
    </tbody>
  </table>

</div>
          
</body>
</html>
