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

$user = userView( $username );
$activeTheme = $user?->getTheme();

$dbThemes = themeMgr()->findViewAll(true);
$themes = [];
foreach ($dbThemes as $theme) {

    $theme->isActive = $theme->getId() == $activeTheme?->getId();
    $theme->label = $theme->getName();

    if ( strtolower($theme->getName()) == "dark"  ) {
        $theme->background = "#121212";  // background var(--dark--bg)
        $theme->surface = "#212121"; // surface var(--dark--bg-surface)
        $theme->color1 = "var(--default--primary)"; // primary var(--default--primary)
        $theme->color2 = "var(--default--fg)"; // text var(--default--fg
        $theme->color3 = "rgba(56, 56, 56)"; // dropdown background var(--default--bg-input-text)
        $theme->label = "Default";

        $themes[] = $theme;
    }
    else if ( strtolower($theme->getName()) == "grey"  ) {
        $theme->background = "#424242";  // background var(--grey--bg)
        $theme->surface = "#383838"; // surface var(--grey--bg-surface)
        $theme->color1 = "var(--default--primary)"; // primary var(--default--primary)
        $theme->color2 = "var(--default--fg)"; // text var(--default--fg)
        $theme->color3 = "#424242"; // dropdown background var(--grey--bg-input-text)

        $themes[] = $theme;
    }

}

function htmlFromTheme( ThemeView $theme ): string
{

    $id = $theme->getId();
    $name = $theme->getName();

    $onClick = "";
    $isChecked = "";
    if ( $theme->isActive ) {
        $isChecked = "is-checked";
    }
    else {
        $onClick = "onClick='setTheme($id)'";
    }

    $html = " ";
    $html .= "<div class='set-theme-wrapper'>\n";
    $html .= "    <div class='set-theme $isChecked data-theme-id='$id' $onClick style='background-color: $theme->background'>\n";
    $html .= "        <input id='set-theme_grey' class='set-theme-input' type='radio' value='$name'>\n";
    $html .= "        <div class='theme-surface' style='background-color: $theme->surface'>\n";
    $html .= "            <span class='set-theme-color' style='background-color: $theme->color1'></span>\n";
    $html .= "            <span class='set-theme-color-short' style='background-color: $theme->color2'></span>\n";
    $html .= "            <span class='set-theme-color' style='background-color: $theme->color3'></span>\n";
    $html .= "        </div>\n";
    $html .= "        <span class='set-theme-check fas fa-check'></span>\n";
    $html .= "    </div>\n";
    $html .= "    <label class='set-theme-label' data-theme-id='$id'>$theme->label</label>\n";
    $html .= "</div>\n";

    return $html;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo includeHeadHtmlForAllPages( $user ); ?>
    <title><?php echo Constants::SITE_NAME; ?> Account</title>
	<link rel="icon" href="<?php echo Constants::FAVICON_URL; ?>">
    <link href="/css/rs-themes.css" rel="stylesheet">
    <?php if (empty($username)) { echo '<script type="text/javascript">window.location.href = "/php/Login"</script>'; } ?>
    <script src="../../js/theme.js"></script>
</head>
<body>

<div id="debug" class="container"></div>

<div id="alert-placeholder" class="alert-placeholder"></div>

<div class="container">

    <?php echo $pageHeader; ?>

    <div class='card mt-3'>
        <div class="card-body">
            <h2>Account</h2>
        </div>
    </div>

    <div><p><span id="debug"></span></p></div>

    <p>Username: <?php echo "$username"; ?></p>

    <form>
        <div class="themes">

            <?php
            foreach ($themes as $theme) {
                echo htmlFromTheme( $theme );
            }
            ?>

        </div>
    </form>

    <p><a href="../Login/logout.php"><button class="btn btn-primary">Logout</button></a></p>

</div>

<script>
    <?php echo Constants::echoJavascriptConstants(); ?>
</script>
        
</body>
</html>
