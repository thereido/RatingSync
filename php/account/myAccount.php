<?php
namespace RatingSync;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "main.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "pageHeader.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "SessionUtility.php";

$username = getUsername();
$listnames = null;

if (!empty($username)) {
    $listnames = Filmlist::getUserListsFromDbByParent($username, false);
}

$pageHeader = getPageHeader(true, $listnames);
$pageFooter = getPageFooter();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo includeHeadHtmlForAllPages(); ?>
    <title><?php echo Constants::SITE_NAME; ?> Account</title>
	<link rel="icon" href="<?php echo Constants::FAVICON_URL; ?>">
    <?php if (empty($username)) { echo '<script type="text/javascript">window.location.href = "/php/Login"</script>'; } ?>
</head>
<body>

<div class="container">

    <?php echo $pageHeader; ?>

    <div class='card mt-3'>
        <div class="card-body">
            <h2>Account</h2>
        </div>
    </div>

    <div><p><span id="debug"></span></p></div>

    <p>Username: <?php echo "$username"; ?></p>
    <p><a href="../Login/logout.php"><button class="btn btn-primary">Logout</button></a></p>

</div>
        
</body>
</html>
