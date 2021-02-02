<?php
namespace RatingSync;

require "DBController.php";

class Auth {
    function getMemberByUsername($username) {
        $db_handle = new DBController();
        $query = "Select * from members where member_name = ?";
        $result = $db_handle->runQuery($query, 's', array($username));
        return $result;
    }

    function getTokenByUsername($username,$expired) {
        $db_handle = new DBController();
        $query = "Select * from token_auth where username = ? and is_expired = ?";
        $result = $db_handle->runQuery($query, 'si', array($username, $expired));
        return $result;
    }

    function markAsExpired($tokenId) {
        $db_handle = new DBController();
        $query = "UPDATE token_auth SET is_expired = ? WHERE id = ?";
        $expired = 1;
        $result = $db_handle->update($query, 'ii', array($expired, $tokenId));
        return $result;
    }

    function insertToken($username, $random_password_hash, $random_selector_hash, $expiry_date) {
        $db_handle = new DBController();
        $query = "INSERT INTO token_auth (username, password_hash, selector_hash, expiry_date) values (?, ?, ?,?)";
        $result = $db_handle->insert($query, 'ssss', array($username, $random_password_hash, $random_selector_hash, $expiry_date));
        return $result;
    }

    function update($query) {
        mysqli_query($this->conn,$query);
    }

    function clearAuthCookie() {

    }

    function newToken($length) {
        /*RT*/
        srand(time());
        mt_srand(time());
        echo rand() . "<br>";
        echo mt_rand() . "<br>";
        echo rand(20,1000) . "<br>";
        echo mt_rand(1,10) . "<br>";
        return "Random numbers";
    }
}

?>