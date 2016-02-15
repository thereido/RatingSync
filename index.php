<?php
namespace RatingSync;

require_once "/php/SessionUtility.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>RatingSync</title>
    <link href="css/bootstrap_rs.min.css" rel="stylesheet">
    <script src="js/bootstrap.min.js"></script>
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
            $username = SessionUtility::getUsername();
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
    <h2>RatingSync Pages</h2>
  </div>  
  <div class="row">
    <div class="col-sm-12">
      <ul>
          <li><a href="/php/export.php">Export</a></li>
          <li><a href="/php/import.php">Import to RS</a></li>
          <li><a href="/php/ratings.php">Your Ratings</a></li>
      </ul>
    </div>
  </div>
  <div class="well well-sm">
    <h2>RatingSync tests</h2>
  </div>
  <div class="row">
    <div class="col-sm-12">
      <ul>
        <li><a href="/php/example/exampleSearch.php">Search</a></li>
        <li><a href="/php/example/exampleRatings.php">Ratings</a></li>
      </ul>
    </div>
  </div>

  <p></p>
  <footer class="footer"></footer>
</div> <!-- container -->
</body>
</html>
