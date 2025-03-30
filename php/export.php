<?php
namespace RatingSync;
require_once "main.php";
require_once "pageHeader.php";
require_once "src/Constants.php";

// Define constants for readability
const POST_REQUEST_METHOD = "POST";

// Define variables
$username = getUsername();
$pageHeader = getPageHeader();
$pageFooter = getPageFooter();
$success = null;
$exportedFilenames = null;

// Sanitize and process the export request
if (!empty($username) && $_SERVER["REQUEST_METHOD"] === POST_REQUEST_METHOD) {
    $exportedFilenames = processExportRequest($username, $_POST);
    $success = !empty($exportedFilenames);
}

/**
 * Process the export request
 *
 * @param string $username
 * @param array $post
 * @return array|false Success or fail status, or null if invalid
 */
function processExportRequest(string $username, array $post): array|false
{
    $format = sanitizeInput($post["format"]);
    $collectionName = sanitizeInput( $post["collectionName"] ?? "" );
    $exportFormat = getExportFormat($format, $collectionName);

    if (is_null($exportFormat)) {
        return false;
    }

    return \RatingSync\export($username, $exportFormat, $collectionName);
}

/**
 * Get export format based on input
 *
 * @param string $format
 * @param string $collectionName
 * @return ExportFormat|null Export format constant or null if invalid
 */
function getExportFormat(string $format, string $collectionName): ?ExportFormat
{
    return match ($format) {
        "letterboxd" => empty($collectionName) || $collectionName == "ratings" ? ExportFormat::LETTERBOXD_RATINGS : ExportFormat::LETTERBOXD_COLLECTION,
        "trakt" => ExportFormat::TRAKT_RATINGS,
        "tmdb" => ExportFormat::TMDB_RATINGS,
        "imdb" => ExportFormat::IMDB_RATINGS,
        default => null,
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
<form role="form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
    <div class="container">
        <?php echo $pageHeader; ?>
        <div class="well" style="text-align:center">
            <h1>Export Ratings</h1>
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
                            echo '<div><a href="' . \RatingSync\Constants::RS_OUTPUT_URL_PATH . $baseFilename . '">' . $baseFilename . '</a></div>';
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
                    <select class="form-control" id="format" name="format" onchange="updateCollectionName()">
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
                    <select class="form-control" id="collectionName" name="collectionName">
                        <option value="ratings">Ratings</option>
                        <option value="Watchlist">Watchlist</option>
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
        <?php echo $pageFooter; ?>
    </div>
</form>
<script>
    <?php echo Constants::echoJavascriptConstants(); ?>
    let pageId = SITE_PAGE.Export;

    function updateCollectionName() {
        var formatSelect = document.getElementById('format');
        var collectionSelect = document.getElementById('collectionName');
        if (formatSelect.value === 'letterboxd') {
            collectionSelect.disabled = false;
        }
        else {
            collectionSelect.disabled = true;
            collectionSelect.selectedIndex = 0;
        }
    }
</script>
</body>
</html>