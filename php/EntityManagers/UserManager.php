<?php

namespace RatingSync;

require_once "EntityManager.php";

final class UserManager extends EntityManager
{

    public function __construct() {
    }

    public function findWithId( int $id ): User|null {

        $query = "SELECT * FROM user WHERE id=" . $id;

        try {
            return $this->findWithQuery( $query );
        }
        catch (Exception) {
            return false;
        }

    }

    public function findWithUsername( string $username ): User|false {

        if ( empty($username) ) {
            return false;
        }

        $usernameEscapedAndQuoted = $this->getDb()->quote($username);

        $query = "SELECT * FROM user WHERE username=$usernameEscapedAndQuoted";

        try {
            return $this->findWithQuery( $query );
        }
        catch (Exception) {
            return false;
        }

    }

    /**
     * @throws Exception
     */
    private function findWithQuery( string $query ): User|false
    {

        try {

            $result = $this->getDb()->query($query);

        }
        catch (Exception $e) {

            logError("Exception with query: $query");
            logError($e->getMessage() . "\n" . $e->getTraceAsString());
            throw $e;

        }

        if ( $result->rowCount() == 1 ) {

            $row = $result->fetch();
            $id = $row["id"];
            $username = $row["username"];
            $email = $row["email"];
            $enabled = $row["enabled"];
            $themeId = $row["theme_id"];

            return new User($id, $username, $email, $enabled, $themeId);

        }
        else {

            logDebug($result->rowCount() . " users with query: $query");
            return false;

        }

    }

}