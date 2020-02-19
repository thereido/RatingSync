<?php
namespace RatingSync;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "main.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "pageHeader.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "SessionUtility.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "Constants.php";

// define variables and set to empty values
$username = $password = $loginFormDisplay = $regFormDisplay = $headerScript = null;
$msgSuccess = $msgInfo = $msgWarning = $msgRegSuccess = $msgRegInfo = $msgRegWarning = null;
$loginFormDisplay = "block";
$loginHeaderClass = "active";
$regFormDisplay = "none";
$regHeaderClass = "";

$http_referer = "";
if (array_key_exists("destination", $_POST)) {
    $http_referer = $_POST['destination'];
} elseif (array_key_exists('HTTP_REFERER', $_SERVER) && array_value_by_key('dest', $_GET) != "none") {
    if (strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) !== false) {
        $http_referer = $_SERVER['HTTP_REFERER'];
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($_POST['active-form'] == "login-form") {
        $loginFormDisplay = "block";
        $loginHeaderClass = "active";
        $regFormDisplay = "none";
        $regHeaderClass = "";
        $msgInfo = "<p>Incorrect username or password. Please try again.</p>";
        if (!empty($_POST['username']) && !empty($_POST['password'])) {
            SessionUtility::logout();
            $username = $_POST['username'];
            $password = $_POST['password'];
            if (SessionUtility::login($username, $password)) {
                $destination = $_POST['destination'];
                if (empty($destination)) {
                    $destination = "/";
                }
                $headerScript = '<script type="text/javascript">window.location.href = "'.$destination.'"</script>';
                $msgSuccess = "<strong>Success</strong><br>If not redirected automatically, follow the link <a href='".$destination."'>here</a>.<br>";
            }
        }
    } else if ($_POST['active-form'] == "verify-form") {
		/*
		 * UNCOMMENT when you are ready to register users
		 * 
        $loginFormDisplay = "none";
        $loginHeaderClass = "";
        $regFormDisplay = "block";
        $regHeaderClass = "active";
        if (!empty($_POST['username']) && !empty($_POST['password'])) {
            $username = $_POST['username'];
            $password = $_POST['password'];
            if (SessionUtility::registerUser($username, $password)) {
                if (SessionUtility::login($username, $password)) {
                    $headerScript = '<script type="text/javascript">window.location.href = "/"</script>';
                    $msgRegSuccess = "<strong>Success</strong><br>If not redirected automatically, follow the link <a href='/'>here</a>.<br>";
                } else {
                    $headerScript = '<script type="text/javascript">window.location.href = "/php/Login"</script>';
                    $msgRegSuccess = "<strong>Success</strong><br>If not redirected automatically, follow the link <a href='/php/Login'>here</a>.<br>";
                }
            } else {
                $msgRegWarning = "<strong>Registration failed</strong><br>Please try again. Maybe with a different username and/or password.<br>";
            }
		}
		*/
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo Constants::SITE_NAME; ?> Login</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <link href="../../css/rs.css" rel="stylesheet">
    <?php echo includeJavascriptFiles(); ?>
    <script src="../../js/login.js"></script>
    <style type="text/css">
    body {
    padding-top: 90px;
}
.panel-login {
	border-color: #ccc;
	-webkit-box-shadow: 0px 2px 3px 0px rgba(0,0,0,0.2);
	-moz-box-shadow: 0px 2px 3px 0px rgba(0,0,0,0.2);
	box-shadow: 0px 2px 3px 0px rgba(0,0,0,0.2);
}
.panel-login>.panel-heading {
	color: #00415d;
	background-color: #fff;
	border-color: #fff;
	text-align:center;
}
.panel-login>.panel-heading a{
	text-decoration: none;
	color: #666;
	font-weight: bold;
	font-size: 15px;
	-webkit-transition: all 0.1s linear;
	-moz-transition: all 0.1s linear;
	transition: all 0.1s linear;
}
.panel-login>.panel-heading a.active{
	color: #029f5b;
	font-size: 18px;
}
.panel-login>.panel-heading hr{
	margin-top: 10px;
	margin-bottom: 0px;
	clear: both;
	border: 0;
	height: 1px;
	background-image: -webkit-linear-gradient(left,rgba(0, 0, 0, 0),rgba(0, 0, 0, 0.15),rgba(0, 0, 0, 0));
	background-image: -moz-linear-gradient(left,rgba(0,0,0,0),rgba(0,0,0,0.15),rgba(0,0,0,0));
	background-image: -ms-linear-gradient(left,rgba(0,0,0,0),rgba(0,0,0,0.15),rgba(0,0,0,0));
	background-image: -o-linear-gradient(left,rgba(0,0,0,0),rgba(0,0,0,0.15),rgba(0,0,0,0));
}
.panel-login input[type="text"],.panel-login input[type="email"],.panel-login input[type="password"] {
	height: 45px;
	border: 1px solid #ddd;
	font-size: 16px;
	-webkit-transition: all 0.1s linear;
	-moz-transition: all 0.1s linear;
	transition: all 0.1s linear;
}
.panel-login input:hover,
.panel-login input:focus {
	outline:none;
	-webkit-box-shadow: none;
	-moz-box-shadow: none;
	box-shadow: none;
	border-color: #ccc;
}
.btn-login {
	background-color: #59B2E0;
	outline: none;
	color: #fff;
	font-size: 14px;
	height: auto;
	font-weight: normal;
	padding: 14px 0;
	text-transform: uppercase;
	border-color: #59B2E6;
}
.btn-login:hover,
.btn-login:focus {
	color: #fff;
	background-color: #53A3CD;
	border-color: #53A3CD;
}
.forgot-password {
	text-decoration: underline;
	color: #888;
}
.forgot-password:hover,
.forgot-password:focus {
	text-decoration: underline;
	color: #666;
}

.btn-register {
	background-color: #1CB94E;
	outline: none;
	color: #fff;
	font-size: 14px;
	height: auto;
	font-weight: normal;
	padding: 14px 0;
	text-transform: uppercase;
	border-color: #1CB94A;
}
.btn-register:hover,
.btn-register:focus {
	color: #fff;
	background-color: #1CA347;
	border-color: #1CA347;
}

    </style>
    <?php echo $headerScript; ?>
</head>

<body>   

	<div class="container">
    	<div class="row">
			<div class="col-md-6 col-md-offset-3">
				<div class="panel panel-login">
					<div class="panel-heading">
						<div class="row">
							<div class="col-xs-6">
								<a href="#" class="<?php echo $loginHeaderClass;?>" id="login-form-link">Login</a>
							</div>
							<div class="col-xs-6" hidden>
								<a href="#" class="<?php echo $regHeaderClass;?>" id="register-form-link">Register</a>
							</div>
						</div>
						<hr>
					</div>
					<div class="panel-body">
						<div class="row">
							<div class="col-lg-12">
								<form id="login-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post" role="form" style="display: <?php echo $loginFormDisplay; ?>;">
                                    <input type="hidden" name="active-form" id="active-form" value="login-form">
                                    <input type="hidden" name="destination" id="destination" value="<?php echo $http_referer;?>">
                                    <div id="msg-success" class="alert alert-success" hidden></div>
                                    <div id="msg-info" class="alert alert-info" hidden></div>
                                    <div id="msg-warning" class="alert alert-warning" hidden></div>
									<div class="form-group">
										<input type="text" name="username" id="username" tabindex="1" class="form-control" placeholder="Username" value="">
									</div>
									<div class="form-group">
										<input type="password" name="password" id="password" tabindex="2" class="form-control" placeholder="Password">
									</div>
									<div class="form-group text-center" hidden>
										<input type="checkbox" tabindex="3" class="" name="remember" id="remember">
										<label for="remember"> Remember Me</label>
									</div>
									<div class="form-group">
										<div class="row">
											<div class="col-sm-6 col-sm-offset-3">
												<input type="submit" name="login-submit" id="login-submit" tabindex="4" class="form-control btn btn-login" value="Log In">
											</div>
										</div>
									</div>
									<div class="form-group" hidden>
										<div class="row">
											<div class="col-lg-12">
												<div class="text-center">
													<a href="recover.php" tabindex="5" class="forgot-password">Forgot Password?</a>
												</div>
											</div>
										</div>
									</div>
								</form>
								<form id="register-form" hidden method="post" role="form" style="display: <?php echo $regFormDisplay; ?>;" onsubmit="validateRegistrationInput(); return false;">
                                    <input type="hidden" name="active-form" id="active-form" value="register-form">
                                    <div id="msg-reg-success" class="alert alert-success" hidden></div>
                                    <div id="msg-reg-info" class="alert alert-info" hidden></div>
                                    <div id="msg-reg-warning" class="alert alert-warning" hidden></div>
									<div class="form-group">
										<input type="text" name="username" id="username-reg" tabindex="1" class="form-control" placeholder="Username" value="" required>
									</div>
									<div class="form-group">
										<input type="password" name="password" id="password-reg" tabindex="2" class="form-control" placeholder="Password" required>
									</div>
									<div class="form-group">
										<input type="password" name="confirm-password" id="password-reg-confirm" tabindex="2" class="form-control" placeholder="Confirm Password" required>
									</div>
									<div class="form-group">
										<div class="row">
											<div class="col-sm-6 col-sm-offset-3">
                                                <button type="button" class="form-control btn btn-register" tabindex="4" onclick="validateRegistrationInput()">
                                                  Register
                                                </button>
											</div>
										</div>
									</div>  
								</form>    
                                <form id="verify-form" hidden action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post" role="form">
                                    <input type="hidden" name="active-form" id="active-form" value="verify-form">
                                    <input type="text" name="username" id="username-verify" hidden>
                                    <input type="text" name="password" id="password-verify" hidden>
                                    <div class="modal fade" id="registerModal" tabindex="-1" role="dialog" aria-labelledby="registerModalLabel">
                                      <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                          <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                            <h4 class="modal-title" id="myModalLabel">Password warning</h4>
                                          </div>
                                          <div class="modal-body">
                                              <p><strong>Do not forget your password!</strong></p>
                                              This site has many limitations as it is still in development.
                                              <ul>
                                                  <li>If you forget your password we cannot reset it</li>
                                                  <li>You cannot change your password after you register</li>
                                              </ul>
                                          </div>
                                          <div class="modal-footer">
                                            <button type="button" class="btn btn-default" data-dismiss="modal">Back</button>
                                            <button type="submit" class="btn btn-register">&nbsp;&nbsp;Register&nbsp;&nbsp;</button>
                                          </div>
                                        </div>
                                      </div>
                                    </div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

<script>
    renderMsg("<?php echo $msgSuccess; ?>", document.getElementById("msg-success"));
    renderMsg("<?php echo $msgInfo; ?>", document.getElementById("msg-info"));
    renderMsg("<?php echo $msgWarning; ?>", document.getElementById("msg-warning"));
    renderMsg("<?php echo $msgRegSuccess; ?>", document.getElementById("msg-reg-success"));
    renderMsg("<?php echo $msgRegInfo; ?>", document.getElementById("msg-reg-info"));
    renderMsg("<?php echo $msgRegWarning; ?>", document.getElementById("msg-reg-warning"));
</script>

<script type="text/javascript">

    $(function () {

        $('#login-form-link').click(function (e) {
            $("#login-form").delay(100).fadeIn(100);
            $("#register-form").fadeOut(100);
            $('#register-form-link').removeClass('active');
            $(this).addClass('active');
            e.preventDefault();
        });
        $('#register-form-link').click(function (e) {
            $("#register-form").delay(100).fadeIn(100);
            $("#login-form").fadeOut(100);
            $('#login-form-link').removeClass('active');
            $(this).addClass('active');
            e.preventDefault();
        });

    });

</script>
</body>
</html>
