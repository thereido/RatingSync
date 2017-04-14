<?php
namespace RatingSync;

require_once "main.php";
require_once "src/Constants.php";

function getPageHeader($forListnameParam = false, $listnames = null) {
    $username = getUsername();
    if (!$forListnameParam && !empty($username)) {
        $listnames = Filmlist::getUserListnamesFromDbByParent($username);
    }

    $signupLink  = '<li><a href="/php/Login"><span class="glyphicon glyphicon-user"></span> Sign Up</a></li>'."\n";
    $loginLink = '<li><a id="myaccount-link" href="/php/Login"><span class="glyphicon glyphicon-log-in"></span> Login</a></li>'."\n";
    $accountLink = '<li><a id="myaccount-link" href="/php/account/myAccount.php">'.$username.'</a></li>'."\n";
    $rightSide = $signupLink . $loginLink;
    if ($username) {
        $rightSide = $accountLink;
    }

    $html  = '<nav class="navbar navbar-default">'."\n";
    $html .= '  <div class="container-fluid">'."\n";
    $html .= '    <div class="navbar-header">'."\n";
    $html .= '      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">'."\n";
    $html .= '        <span class="icon-bar"></span>'."\n";
    $html .= '        <span class="icon-bar"></span>'."\n";
    $html .= '        <span class="icon-bar"></span>'."\n";
    $html .= '      </button>'."\n";
    $html .= '      <a class="navbar-brand" href="/">RatingSync</a>'."\n";
    $html .= '    </div>'."\n";
    $html .= '    <div class="collapse navbar-collapse" id="myNavbar">'."\n";
    // Left side buttons/links
    $html .= '      <ul class="nav navbar-nav">'."\n";
    $html .= '        <li><a href="/php/ratings.php">Your Ratings</a></li>'."\n";
    $html .= '        <li id="nav-watchlist"><a href="/php/userlist.php?l=Watchlist">Watchlist</a></li>'."\n";
    $html .= '        <li class="dropdown">'."\n";
    $html .= '          <a class="dropdown-toggle" data-toggle="dropdown" href="javascript:void(0);">Lists <span class="caret"></span></a>'."\n";
    $html .= '          <ul class="dropdown-menu">'."\n";
    $html .=              getHtmlFilmlistNamesForNav($listnames);
    $html .= '            <li class="divider"></li>'."\n";
    $html .= '            <li><a href="/php/userlist.php?nl=1">Create a list</a></li>'."\n";
    $html .= '          </ul>'."\n";
    $html .= '        </li>'."\n";
    $html .= '      </ul>'."\n";
    // Right side buttons/links
    $html .= '      <ul class="nav navbar-nav navbar-right">'."\n";
    $html .=          $rightSide;
    $html .= '      </ul>'."\n";
    // Search
    $html .= '      <form class="navbar-form navbar-right">'."\n";
    $html .= '        <div class="form-group">'."\n";
    $html .= '          <input type="text" class="form-control" placeholder="Search">'."\n";
    $html .= '        </div>'."\n";
    $html .= '        <button type="submit" class="btn btn-default"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></button>'."\n";
    $html .= '      </form>'."\n";
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
