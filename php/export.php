<?php
namespace RatingSync;
require_once "main.php";
require_once "pageHeader.php";
require_once "src/Constants.php";
require_once "Export/ExportEnums.php";

// Define constants for readability
const POST_REQUEST_METHOD = "POST";

// Define variables
$username = getUsername();
$pageHeader = getPageHeader();
$pageFooter = getPageFooter();
$success = null;
$exportedFilenames = null;
$collections = null;

// Sanitize and process the export request
if (!empty($username) && $_SERVER["REQUEST_METHOD"] === POST_REQUEST_METHOD) {
    $exportedFilenames = processExportRequest( $_POST );
    $success = !empty($exportedFilenames);
}

if (!empty($username)) {
    $collections = Filmlist::getUserListsFromDbByParent($username, false);
    $watchlistKey = array_search("Watchlist", array_column($collections, "listname"));
    if ($watchlistKey !== false) {
        unset($collections[$watchlistKey]);
    }
}

/**
 * Process the export request
 *
 * @param array $post
 * @return array|false Success or fail status, or null if invalid
 */
function processExportRequest( array $post ): array|false
{
    $destination = sanitizeInput($post["exportDest"]);
    $collectionName = sanitizeInput( $post["collectionName"] ?? "" );
    $exportDestination = getExportDestination($destination);

    if (is_null($exportDestination)) {
        return false;
    }

    if ($collectionName == "Ratings") {
        $collectionName = "";
    }

    return export( $exportDestination, $collectionName );
}

/**
 * Get export destination based on input
 *
 * @param string $destination
 * @return ExportOldFormat|null Export format constant or null if invalid
 */
function getExportDestination(string $destination): ?ExportDestination
{
    return match ($destination) {
        "letterboxd"    => ExportDestination::LETTERBOXD,
        "trakt"         => ExportDestination::TRAKT,
        "tmdb"          => ExportDestination::TMDB,
        "imdb"          => ExportDestination::IMDB,
        default         => null,
    };
}

/**
 * Sanitize user input
 *
 * @param string $data
 * @return string Sanitized data
 */
function sanitizeInput(string $data): string
{
    return htmlspecialchars(stripslashes(trim($data)));
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo includeHeadHtmlForAllPages(); ?>
    <title><?php echo Constants::SITE_NAME; ?> Export</title>
    <link rel="icon" href="<?php echo Constants::FAVICON_URL; ?>">
    <?php if (empty($username)) {
        echo '<script type="text/javascript">window.location.href = "/php/Login"</script>';
    } ?>
</head>
<body>
    <div class="container">
        <?php echo $pageHeader; ?>
        <form role="form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="well" style="text-align:center">
                <h1 id="exportTitle">Export Ratings</h1>
            </div>
            <div class="row">
                <div class="col-sm-offset-1 col-sm-10">
                    <?php
                    if (!is_null($success)) {
                        if ($success) {
                            echo '<div class="alert alert-success">';
                            echo '<strong>Success!</strong>';
                            echo '<br/>';
                            foreach ($exportedFilenames as $filename) {
                                $baseFilename = basename($filename);
                                $userDir = "/" . $username . "/";
                                echo '<div><a href="' . Constants::RS_OUTPUT_URL_PATH . $userDir . $baseFilename . '">' . $baseFilename . '</a></div>';
                            }
                            echo '</div>';
                        } else {
                            echo '<div class="alert alert-warning"><strong>Failure!</strong> Something went wrong.</div>';
                        }
                    }
                    ?>
                </div>
            </div>
            <div class="form-group">
                <div class="row justify-content-center">
                    <div class="col-sm-3 col-md-3 col-lg-2 col-xl-2 mt-3">
                        <label for="format">Export format:</label>
                    </div>
                    <div class="col-sm-4 col-md-3 col-lg-2 col-xl-2 mt-2">
                        <select class="form-control" id="exportDest" name="exportDest" onchange="onChangeFormat()">
                            <option value="letterboxd">Letterboxd</option>
                            <option value="trakt">Trakt</option>
                            <option value="tmdb">TMDb</option>
                            <option value="imdb">IMDb</option>
                        </select>
                    </div>
                </div>
                <div class="row justify-content-center mt-3">
                    <div class="col-sm-3 col-md-3 col-lg-2 col-xl-2 mt-2">
                        <label for="collectionName">List:</label>
                    </div>
                    <div class="col-sm-4 col-md-3 col-lg-2 col-xl-2">
                        <select class="form-control" id="collectionName" name="collectionName" onchange="onChangeCollectionName()">
                            <option value="Ratings">Ratings</option>
                            <option value="Watchlist">Watchlist</option>
                            <?php
                            if (!empty($collections) && count($collections) > 0) {
                                echo '<optgroup label="Lists">';
                                $otherLists = getHtmlFilmlistNamesForExport($collections);
                                echo $otherLists;
                                echo '</optgroup>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12" style="text-align:center">
                    <input type="submit" name="submitBtn" class="btn btn-lg btn-primary" href="#" role="button"
                           value="Export">
                </div>
            </div>
        </form>
        <?php echo $pageFooter; ?>
    </div>
<script>
    <?php echo Constants::echoJavascriptConstants(); ?>
    let pageId = SITE_PAGE.Export;

    function onChangeFormat() {
        const destinationSelect = document.getElementById('exportDest');
        const collectionSelect = document.getElementById('collectionName');
        if (destinationSelect.value === 'letterboxd') {
            collectionSelect.disabled = false;
        }
        else {
            collectionSelect.disabled = true;
            collectionSelect.selectedIndex = 0;
            onChangeCollectionName();
        }
    }

    function onChangeCollectionName() {
        const exportTitle = document.getElementById('exportTitle');
        const collectionSelect = document.getElementById('collectionName');

        exportTitle.innerHTML = "Export " + collectionSelect.value;
    }
</script>
</body>
</html>