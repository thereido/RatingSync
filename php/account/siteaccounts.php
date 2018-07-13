<?php
namespace RatingSync;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "Constants.php";

if(empty($_SESSION['LoggedIn']) || empty($_SESSION['Username'])) {
    // FIXME Redirect to login
}

$user = new \RatingSync\User($_SESSION['Username']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo Constants::SITE_NAME; ?> Accounts</title>
    <link href="../css/bootstrap_rs.min.css" rel="stylesheet">
</head>
<body>

	<div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
				<form id="account-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
                    <div class="row">
                        <div class="col">
                            <h2>IMDb</h2>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
							<div class="form-group">
								<input type="text" name="siteusername" id="siteusername" tabindex="1" class="form-control" placeholder="IMDb Username" value="<?php echo $user->getUsername(Constants::SOURCE_IMDB); ?>">
							</div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
          
</body>
</html>
