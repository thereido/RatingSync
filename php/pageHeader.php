<?php
namespace RatingSync;

use Exception;

require_once "main.php";
require_once "src/Constants.php";
require_once __DIR__ .DIRECTORY_SEPARATOR. "Entity" .DIRECTORY_SEPARATOR. "Entities" .DIRECTORY_SEPARATOR. "UserEntity.php";
require_once __DIR__ .DIRECTORY_SEPARATOR. "Entity" .DIRECTORY_SEPARATOR. "Managers" .DIRECTORY_SEPARATOR. "UserManager.php";
require_once __DIR__ .DIRECTORY_SEPARATOR. "Entity" .DIRECTORY_SEPARATOR. "Views" .DIRECTORY_SEPARATOR. "UserView.php";

function includeHeadHtmlForAllPages( UserView $user = null ): string {

    if ( empty( $user ) ) {
        $user = userView();
    }

    $themeName = $user?->getThemeName();
    if ( is_null($themeName) ) {
        $themeName = Constants::THEME_DEFAULT;
    }

    $html  = '<meta charset="utf-8" />' . "\n";
    $html .= includeBootstrapDependencies();
    $html .= includeFontAwesomeDependencies();
    $html .= includeJavascriptFiles();
    $html .= '<link href="/css/rs.css" rel="stylesheet">' . "\n";
    $html .= '<link href="/css/switches.css" rel="stylesheet">' . "\n";
    $html .= "<link href='/css/rs-theme-$themeName.css' rel='stylesheet'>\n";

    return $html;
}

/**
 * All pages should call this function in the HTML head. Javascript files
 * needed by all pages are included.
 *
 */
function includeJavascriptFiles(): string {
    $html  = '<script src="/Chrome/constants.js"></script>' . "\n";
    $html .= '<script src="/Chrome/rsCommon.js"></script>' . "\n";
    $html .= '<script src="/js/pageHeader.js"></script>' . "\n";
    $html .= '<script src="/js/search.js"></script>' . "\n";

    return $html;
}

function includeBootstrapDependencies(int $majorVersion = 4): string {

    $bootstrapCssUrl = $popperUrl = $bootstrapScriptUrl = "";
    $bootstrapCssHash = $popperHash = $bootstrapScriptHash = "";
    $jqueryScript = "";

    switch ($majorVersion) {

        case 5:
            // Bootstrap 5.3
            $bootstrapCssUrl     = "https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css";
            $popperUrl           = "https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js";
            $bootstrapScriptUrl  = "https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js";

            $bootstrapCssHash    = "sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN";
            $popperHash          = "sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r";
            $bootstrapScriptHash = "sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+";
            break;

        case 4:
        default:
            // Bootstrap 4.6
            $bootstrapCssUrl     = "https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css";
            $jqueryUrl           = "https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js";
            $popperUrl           = "https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js";
            $bootstrapScriptUrl  = "https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js";

            $bootstrapCssHash    = "sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N";
            $jqueryHash          = "sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj";
            $popperHash          = "sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN";
            $bootstrapScriptHash = "sha384-+sLIOodYLS7CIrQpBjl+C7nPvqq+FbNUBDunl/OZv93DB7Ln/533i8e/mZXLi/P+";

            $jqueryScript    = "<script src=\"$jqueryUrl\" integrity=\"$jqueryHash\" crossorigin=\"anonymous\"></script>\n";

    }

    $bootstrapLink   = "<link href=\"$bootstrapCssUrl\" integrity=\"$bootstrapCssHash\" rel=\"stylesheet\" crossorigin=\"anonymous\">\n";
    $popperScript    = "<script src=\"$popperUrl\" integrity=\"$popperHash\" crossorigin=\"anonymous\"></script>\n";
    $bootstrapScript = "<script src=\"$bootstrapScriptUrl\" integrity=\"$bootstrapScriptHash\" crossorigin=\"anonymous\"></script>\n";

    $html  = "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">\n";
    $html .= "$bootstrapLink";
    $html .= "$jqueryScript";
    $html .= "$popperScript";
    $html .= "$bootstrapScript";

    return $html;
}

function includeFontAwesomeDependencies(): string {
    return '<script src="https://kit.fontawesome.com/a79b70d2ab.js" crossorigin="anonymous"></script>' . "\n";
}

function getPageHeader($forListnameParam = false, $listnames = null): string {
    $username = getUsername();
    if (!$forListnameParam && !empty($username)) {
        $listnames = Filmlist::getUserListsFromDbByParent($username, false);
    }

    $headerSearchText = "";
    if (array_key_exists("search", $_GET)) {
        $headerSearchText = $_GET['search'];
    }

    $html  = '<nav class="navbar navbar-expand-lg rs-navbar">'."\n";
    $html .= '  <a class="navbar-brand rs-text-muted" href="/">'."\n";
    $html .= '    <img src="'.Constants::RS_IMAGE_URL_PATH.'logo.png" width="30" height="30" class="d-inline-block align-top" alt="">'."\n";
    $html .=      Constants::SITE_NAME."\n";
    $html .= '  </a>'."\n";
    $html .= '  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">'."\n";
    $html .= '    <span class="navbar-toggler-icon"></span>'."\n";
    $html .= '  </button>'."\n";
    $html .= '  <div class="collapse navbar-collapse" id="navbarSupportedContent">'."\n";
    $html .= '    <ul class="navbar-nav mr-auto">'."\n";
    // Ratings
    $html .= '      <a class="nav-link nav-item" href="/php/ratings.php">Your Ratings <span class="sr-only">(current)</span></a>'."\n";
    // Watchlist
    $html .= '      <a class="nav-link" href="/php/userlist.php?l=Watchlist">Watchlist</a>'."\n";
    // Lists
    $html .= '      <li class="nav-item dropdown">'."\n";
    $html .= '        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'."\n";
    $html .= '          Lists'."\n";
    $html .= '        </a>'."\n";
    $html .= '        <div class="dropdown-menu" aria-labelledby="navbarDropdown">'."\n";
    $html .=            getHtmlFilmlistNamesForNav($listnames);
    $html .= '          <div class="dropdown-divider"></div>'."\n";
    $html .= '          <a class="dropdown-item" href="/php/managelists.php">Manage</a>'."\n";
    $html .= '        </div>'."\n";
    $html .= '      </li>'."\n";
    $html .= '    </ul>'."\n";
    // Search
    $html .= '    <form class="form-inline my-2 my-lg-0" id="header-search-form" action="/php/search.php" onSubmit="onSubmitHeaderSearch();" method="get">'."\n";
    $html .= '      <div class="input-group  mr-sm-2">'."\n";
    $html .= '        <div class="input-group-prepend"  id="search-dropdown">'."\n";
    $html .= '          <button type="button" class="input-group-text btn btn-default dropdown-toggle" style="z-index: auto" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'."\n";
    $html .= '            <span class="caret"></span>'."\n";
    $html .= '          </button>'."\n";
    $html .= '          <ul class="dropdown-menu" aria-labelledby="searchDropdown">'."\n";
    $html .= '            <li class="dropdown-header">Search from...</li>'."\n";
    $html .= '            <li><a class="dropdown-item" href="javascript:onClickSearchDropdown(\'all\');">All</a></li>'."\n";
    $html .= '            <li><a class="dropdown-item" href="javascript:onClickSearchDropdown(\'ratings\');">Ratings</a></li>'."\n";
    $html .= '            <li><a class="dropdown-item" href="javascript:onClickSearchDropdown(\'list\');">Watchlist</a></li>'."\n";
    $html .= '            <li><a class="dropdown-item" href="javascript:onClickSearchDropdown(\'both\');">Ratings & Watchlist</a></li>'."\n";
    $html .= '          </ul>'."\n";
    $html .= '        </div>'."\n";
    $html .= '        <input id="header-search-text" name="search" type="search" class="form-control" placeholder="Search" aria-label="Search" onkeyup="onKeyUpHeaderSearch(event);" value="'.$headerSearchText.'">'."\n";
    $html .= '        <div id="header-search-suggestion" hidden></div>'."\n";
    $html .= '        <div class="input-group-append">'."\n";
    $html .= '          <button type="submit" class="input-group-text btn"><span class="fas fa-search"></span></button>'."\n";
    $html .= '        </div>'."\n";
    $html .= '      </div>'."\n";
    $html .= '      <input id="selected-suggestion-uniquename" name="selsug-un" hidden>'."\n";
    $html .= '      <input id="selected-suggestion-contenttype" name="selsug-ct" hidden>'."\n";
    $html .= '      <input id="search-domain-input" name="sd" hidden>'."\n";
    $html .= '    </form>'."\n";
    // User menu
    $html .= '    <ul class="navbar-nav ml-auto">'."\n";
    $html .= '      <li class="nav-item dropdown">'."\n";
    $html .= '        <a class="nav-link" href="#" id="navbarSettingsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'."\n";
    $html .= '          <i class="fas fa-ellipsis-v"></i>'."\n";
    $html .= '        </a>'."\n";
    $html .= '        <div class="dropdown-menu" aria-labelledby="navbarSettingsDropdown">'."\n";
    $html .= '          <a class="dropdown-item" href="/php/account/myAccount.php">'.$username.'</a>'."\n";
    $html .= '          <a class="dropdown-item" href="/php/export.php">Export</a>'."\n";
    $html .= '          <a class="dropdown-item" href="/php/Login/logout.php">Sign Out</a>'."\n";
    $html .= '        </div>'."\n";
    $html .= '      </li>'."\n";
    $html .= '    </ul>'."\n";
    $html .= '  </div>'."\n";
    $html .= '</nav>'."\n";

    return $html;
}

function getHtmlFilmlistNamesForNav($listnames, $level = 0): string {
    $html = "";

    $prefix = "";
    for ($levelIndex = $level; $levelIndex > 0; $levelIndex--) {
        $prefix .= "&nbsp;&nbsp;";
    }
    
    if ($listnames == null) {
        $listnames = array();
    }
    foreach ($listnames as $list) {
        $listname = $list["listname"];
        if ($listname != "Watchlist") {
            $safeListname = htmlentities($listname, ENT_COMPAT, "utf-8");
            $id = "nav-lists-item-$listname";
            $html .= '<a class="dropdown-item" id="'.$id.'" href="/php/userlist.php?l='.$safeListname.'">'. $prefix . $listname .'</a>'."\n";
            $html .= getHtmlFilmlistNamesForNav($list["children"], $level+1);
        }
    }

    return $html;
}

function getHtmlFilmlistNamesForExport($lists, $level = 0): string {
    $html = "";

    $prefix = "";
    for ($levelIndex = $level; $levelIndex > 0; $levelIndex--) {
        $prefix .= "&nbsp;&nbsp;";
    }

    if ($lists == null) {
        $lists = array();
    }

    foreach ($lists as $list) {
        $listname = $list["listname"];
        $safeListname = htmlentities($listname, ENT_COMPAT, "utf-8");
        $html .= '<option value="' . $safeListname . '">' . $prefix . $safeListname . '</option>';
        $html .= getHtmlFilmlistNamesForExport($list["children"], $level+1);
    }

    return $html;
}

