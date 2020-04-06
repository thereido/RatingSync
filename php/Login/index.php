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
$regFormHidden = "hidden";
$regHeaderHidden = "hidden";
/* UNCOMMENT for register $regHeaderHidden = "";*/

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
		$regFormHidden = "hidden";
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
		 * UNCOMMENT for register
		 * When you are ready to register users look for "UNCOMMENT for register" in this file
		 *
        $loginFormDisplay = "none";
        $loginHeaderClass = "";
        $regFormDisplay = "block";
		$regHeaderClass = "active";
		$regFormHidden = "";
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
		UNCOMMENT for register */
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo includeHeadHtmlForAllPages(); ?>
    <title><?php echo Constants::SITE_NAME; ?> Login</title>
	<link rel="icon" href="<?php echo Constants::FAVICON_URL; ?>">
	<script src="../../js/login.js"></script>

    <?php echo $headerScript; ?>
</head>

<body>   

	<div class="container">
    	<div class="row mt-5">
			<div class="col-xl-6 col-lg-7 col-md-9 mx-auto">
				<div class="card card-login">
					<div class="card-header">
						<div class="row mt-3 mt-0">
							<div class="col-6">
								<a href="#" class="<?php echo $loginHeaderClass;?>" id="login-form-link" onClick="showLoginForm()">Login</a>
							</div>
							<div class="col-6" <?php echo $regHeaderHidden; ?>>
								<a href="#" class="<?php echo $regHeaderClass;?>" id="register-form-link" onClick="showRegisterForm()">Register</a>
							</div>
						</div>
						<hr>
					</div>
					<div class="card-body">
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
											<div class="col mx-auto col-sm-6 col-12">
												<input type="submit" name="login-submit" id="login-submit" tabindex="4" class="btn btn-primary btn-login w-100 py-3" value="Log In">
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
								<form id="register-form" <?php echo $regFormHidden; ?> method="post" role="form" onsubmit="validateRegistrationInput(); return false;">
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
											<div class="col mx-auto col-sm-6 col-12">
                                                <button type="button" class="btn btn-primary btn-register w-100 py-3" tabindex="4" onclick="validateRegistrationInput()">
                                                  Register
                                                </button>
											</div>
										</div>
									</div>  
								</form>
                                <div class="modal fade" id="registerModal" tabindex="-1" role="dialog" aria-labelledby="registerModalLabel" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="registerModalLabel">Password warning</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
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
                                                <form id="verify-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" role="form">
                                                    <input type="hidden" name="active-form" id="active-form" value="verify-form">
                                                    <input type="text" name="username" id="username-verify" hidden>
                                                    <input type="text" name="password" id="password-verify" hidden>
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                    <button id="rename-modal-submit" type="submit" class="btn btn-primary">Register</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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

</body>
</html>
