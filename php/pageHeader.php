<?php
namespace RatingSync;

require_once "main.php";
require_once "src/Constants.php";

function includeHeadHtmlForAllPages() {
    $html = '';
    $html .= '<meta charset="utf-8" />' . "\n";
    $html .= includeBootstrapDependencies();
    $html .= includeFontAwesomeDependencies();
    $html .= includeJavascriptFiles();
    $html .= '<link href="/css/rs.css" rel="stylesheet">' . "\n";

    return $html;
}

/**
 * All pages should call this function in the HTML head. Javascript files
 * needed by all pages are inlcuded.
 *
 */
function includeJavascriptFiles() {
    $html = '';
    $html .= '<script src="/Chrome/constants.js"></script>' . "\n";
    $html .= '<script src="/Chrome/rsCommon.js"></script>' . "\n";
    $html .= '<script src="/js/pageHeader.js"></script>' . "\n";
    $html .= '<script src="/js/search.js"></script>' . "\n";

    return $html;
}

function includeBootstrapDependencies() {
    $html = '';
    $html .= '<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">' . "\n";
    $html .= '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css" integrity="sha384-zCbKRCUGaJDkqS1kPbPd7TveP5iyJE0EjAuZQTgFLD2ylzuqKfdKlfG/eSrtxUkn" crossorigin="anonymous">' . "\n";
    $html .= '<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>' . "\n";
    $html .= '<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-fQybjgWLrvvRgtW6bFlB7jaZrFsaBXjsOMm/tB9LTS58ONXgqbR9W8oWht/amnpF" crossorigin="anonymous"></script>' . "\n";

    return $html;
}

function includeFontAwesomeDependencies() {
    $html = '';
    $html .= '<script src="https://kit.fontawesome.com/a79b70d2ab.js" crossorigin="anonymous"></script>' . "\n";

    return $html;
}

function getPageHeader($forListnameParam = false, $listnames = null) {
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
    $html .= '          <a class="dropdown-item" href="/php/Login/logout.php">Sign Out</a>'."\n";
    $html .= '        </div>'."\n";
    $html .= '      </li>'."\n";
    $html .= '    </ul>'."\n";
    $html .= '  </div>'."\n";
    $html .= '</nav>'."\n";

    return $html;
}

function getHtmlFilmlistNamesForNav($listnames, $level = 0) {
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

?>
