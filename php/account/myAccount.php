<?php
namespace RatingSync;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "SessionUtility.php";

$username = SessionUtility::getUsername();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title><?php echo Constants::SITE_NAME; ?></title>
</head>
<body>
<?php
if (!empty($username))
{
    ?>
    <h1>Account</h1>
    <p>Username: <?php echo "$username"; ?></p>
    <p><a href="../Login/logout.php">Logout</a></p>
    <?php
}
else
{
    ?>
    <h1>Account</h1>
    <p>Not logged in. <a href="../Login">Log in now</a>?</p>
    <?php
}
?>
        
</body>
</html>
