<?php
/**
 * SessionUtility Class
 */
namespace RatingSync;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "main.php";

class SessionUtility {

    public function __construct()
    {
    }

    public static function login($username, $password)
    {
        $success = false;
        SessionUtility::logout();

        $db = getDatabase();
        $username = $db->real_escape_string($_POST['username']);
        $password = md5($db->real_escape_string($_POST['password']));
     
        $query = "SELECT * FROM user WHERE username='$username' AND password='$password'";
        $result = $db->query($query);
        if ($result->num_rows == 1) {
            $_SESSION['LoggedIn'] = 1;
            $_SESSION['Username'] = $username;
            $success = true;
        }

        return $success;
    }

    public static function logout()
    {
        $_SESSION['LoggedIn'] = 0;
        $_SESSION['Username'] = "";
    }

    public static function getUsername()
    {
        $username = "";
        if (isset($_SESSION['Username'])) {
            $username = $_SESSION['Username'];
        }
        return $username;
    }

    public static function setUsername($username)
    {
        $_SESSION['Username'] = $username;
    }
}

?>
