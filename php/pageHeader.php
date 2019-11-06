<?php
namespace RatingSync;

require_once "main.php";
require_once "src/Constants.php";

/**
 * All pages should call this function in the HTML head. Javascript files
 * needed by all pages are inlcuded.
 *
 */
function includeJavascriptFiles() {
    $html = '';
    $html .= '<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>' . "\n";
    $html .= '<script src="/js/bootstrap_rs.min.js"></script>' . "\n";
    $html .= '<script src="/Chrome/constants.js"></script>' . "\n";
    $html .= '<script src="/Chrome/rsCommon.js"></script>' . "\n";
    $html .= '<script src="/js/pageHeader.js"></script>' . "\n";
    $html .= '<script src="/js/search.js"></script>' . "\n";

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

    $signupLink  = '<li><a href="/php/Login"><span class="glyphicon glyphicon-user"></span> Sign Up</a></li>'."\n";
    $loginLink = '<li><a id="myaccount-link" href="/php/Login"><span class="glyphicon glyphicon-log-in"></span> Login</a></li>'."\n";
    $accountLink = '<li><a id="myaccount-link" href="/php/account/myAccount.php">'.$username.'</a></li>'."\n";
    $rightSide = $signupLink . $loginLink;
    if ($username) {
        $rightSide = $accountLink;
    }

    $html  = '<nav class="navbar navbar-default rs-navbar">'."\n";
    $html .= '  <div class="container-fluid">'."\n";
    $html .= '    <div class="navbar-header">'."\n";
    $html .= '      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">'."\n";
    $html .= '        <span class="icon-bar"></span>'."\n";
    $html .= '        <span class="icon-bar"></span>'."\n";
    $html .= '        <span class="icon-bar"></span>'."\n";
    $html .= '      </button>'."\n";
    $html .= '      <a class="navbar-brand" href="/">'.Constants::SITE_NAME.'</a>'."\n";
    $html .= '    </div>'."\n";
    $html .= '    <div class="collapse navbar-collapse" id="myNavbar">'."\n";
    // Left side buttons/links
    $html .= '      <ul class="nav navbar-nav">'."\n";
                      // Your Ratings
    $html .= '        <li><a href="/php/ratings.php">Your Ratings</a></li>'."\n";
                      // Watchlist
    $html .= '        <li id="nav-watchlist"><a href="/php/userlist.php?l=Watchlist">Watchlist</a></li>'."\n";
                      // Lists
    $html .= '        <li class="dropdown">'."\n";
    $html .= '          <a class="dropdown-toggle" data-toggle="dropdown" href="javascript:void(0);">Lists <span class="caret"></span></a>'."\n";
    $html .= '          <ul class="dropdown-menu">'."\n";
    $html .=              getHtmlFilmlistNamesForNav($listnames);
    $html .= '            <li class="divider"></li>'."\n";
    $html .= '            <li><a href="/php/userlist.php?nl=1">Create a list</a></li>'."\n";
    $html .= '          </ul>'."\n";
    $html .= '        </li>'."\n";
                      // Search
    $html .= '        <li>'."\n";
    $html .= '          <form class="navbar-form" id="header-search-form" action="/php/search.php" onSubmit="onSubmitHeaderSearch();" method="get">'."\n";
    $html .= '            <div class="input-group">'."\n";
    $html .= '              <div class="input-group-btn"  id="search-dropdown">'."\n";
    $html .= '                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'."\n";
    $html .= '                  <span class="caret"></span>'."\n";
    $html .= '                </button>'."\n";
    $html .= '                <ul class="dropdown-menu" aria-labelledby="searchDropdown">'."\n";
    $html .= '                  <li class="dropdown-header">Search from...</li>'."\n";
    $html .= '                  <li><a class="dropdown-item" href="javascript:onClickSearchDropdown(\'all\');">All</a></li>'."\n";
    $html .= '                  <li><a class="dropdown-item" href="javascript:onClickSearchDropdown(\'ratings\');">Ratings</a></li>'."\n";
    $html .= '                  <li><a class="dropdown-item" href="javascript:onClickSearchDropdown(\'list\');">Watchlist</a></li>'."\n";
    $html .= '                  <li><a class="dropdown-item" href="javascript:onClickSearchDropdown(\'both\');">Ratings & Watchlist</a></li>'."\n";
    $html .= '                </ul>'."\n";
    $html .= '              </div>'."\n";
    $html .= '              <input id="header-search-text" name="search" type="text" class="form-control" placeholder="Search" onkeyup="onKeyUpHeaderSearch(event);" value="'.$headerSearchText.'">'."\n";
    $html .= '            </div>'."\n";
    $html .= '            <button type="submit" class="btn btn-default"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></button>'."\n";
    $html .= '            <div id="header-search-suggestion" hidden></div>'."\n";
    $html .= '            <input id="selected-suggestion-uniquename" name="selsug-un" hidden>'."\n";
    $html .= '            <input id="selected-suggestion-contenttype" name="selsug-ct" hidden>'."\n";
    $html .= '          </form>'."\n";
    $html .= '        </li>'."\n";
    $html .= '      </ul>'."\n";
    // Right side buttons/links
    $html .= '      <ul class="nav navbar-nav navbar-right">'."\n";
    $html .=          $rightSide;
    $html .= '      </ul>'."\n";
    $html .= '    </div>'."\n";
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
            $html .= '<li><a href="/php/userlist.php?l='.$safeListname.'">'. $prefix . $listname .'</a></li>'."\n";
            $html .= getHtmlFilmlistNamesForNav($list["children"], $level+1);
        }
    }

    return $html;
}

?>
