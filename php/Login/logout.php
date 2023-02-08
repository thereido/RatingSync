<?php
namespace RatingSync;

require_once __DIR__ .DIRECTORY_SEPARATOR. ".." .DIRECTORY_SEPARATOR. "src" .DIRECTORY_SEPARATOR. "SessionUtility.php";

$username = SessionUtility::logout();
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title><?php echo Constants::SITE_NAME; ?></title>
        <script type="text/javascript">window.location.href = "../Login?dest=none"</script>
    </head>
    <body>
        
    </body>
</html>
