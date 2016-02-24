<?php
namespace RatingSync;

require_once "../../main.php";
require_once "../../src/Constants.php";

echo "Begin\n";
$username = Constants::TEST_RATINGSYNC_USERNAME;
\RatingSync\sync($username);
echo "\nEnd";
?>
