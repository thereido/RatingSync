<?php
namespace RatingSync;

require_once "../main.php";
require_once "../src/Constants.php";
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>Register RatingSync</title>
<?php
$msg = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $_SESSION['LoggedIn'] = 0;
    if (!empty($_POST['username']) && !empty($_POST['password']) && !empty($_POST['confirm-password'])) {
        $username = $db->real_escape_string($_POST['username']);
        $password = md5($db->real_escape_string($_POST['password']));
        $confirmPwd = md5($db->real_escape_string($_POST['confirm-password']));

        if ($password != $confirmPwd) {
            $msg = "<h1>Passwords do not match</h1>";
        } else {
            $db = getDatabase();
     
            $query = "SELECT * FROM user WHERE username='$username'";
            $result = $db->query($query);     
            if ($result->num_rows == 1) {
                $msg = "<h1>Username taken</h1>";
            } else {
                $columns = "username, password";
                $values = "'$username', '$password'";
                if ($db->query("INSERT INTO user ($columns) VALUES ($values)")) {
                    $_SESSION['Username'] = $username;
                    $_SESSION['LoggedIn'] = 1;
         
                    echo '<script type="text/javascript">window.location.href = "../../index.html"</script>';
                    $msg = "<h1>Success</h1><br>If not redirected automatically, follow the link <a href='../../index.html'>here</a>.<br>";
                }
            }
        }
    }
}
?>

    </head>
    <body>
<?php
if ($_SESSION['LoggedIn'] == 0) {
    
}
?>
    </body>
</html>
