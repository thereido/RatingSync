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

    public static function start()
    {
        $oldLevel = error_reporting(E_ALL & ~E_NOTICE);
        session_start();
        error_reporting($oldLevel);
    }

    public static function login($username, $password)
    {
        $success = false;
        self::logout();
        self::start();

        $db = getDatabase();     
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
        if (session_status() == PHP_SESSION_ACTIVE) {
            $_SESSION = array();
            session_destroy();
        }
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

    public static function registerUser($username, $password)
    {
        $success = false;
        $failure = false;
        $db = getDatabase();

        // Create User
        $columns = "username, password, enabled";
        $values = "'$username', '$password', FALSE";
        $query = "INSERT INTO user ($columns) VALUES ($values)";
        logDebug($query, __FUNCTION__." ".__LINE__);
        $failure = !($db->query($query));

        // Create default userlist
        if (!$failure) {
            $columns = "user_name, listname";
            $values = "'$username', '".Constants::LIST_DEFAULT."'";
            $query = "INSERT INTO user_filmlist ($columns) VALUES ($values)";
            logDebug($query, __FUNCTION__." ".__LINE__);
            $failure = !($db->query($query));
        }

        // Enable the user
        if (!$failure) {
            $columns = "username, password, enabled";
            $values = "'$username', '$password', FALSE";
            $query = "UPDATE user SET enabled=TRUE WHERE username='$username'";
            logDebug($query, __FUNCTION__." ".__LINE__);
            $failure = !($db->query($query));
        }

        // If there were no failures, that means success
        if (!$failure) {
            $success = true;
        }

        return $success;
    }
}

?>