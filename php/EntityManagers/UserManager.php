<?php

namespace RatingSync;

use Exception;

require_once "EntityManager.php";
require_once __DIR__.DIRECTORY_SEPARATOR. ".." .DIRECTORY_SEPARATOR. "Entities" .DIRECTORY_SEPARATOR. "User.php";
require_once __DIR__.DIRECTORY_SEPARATOR. ".." .DIRECTORY_SEPARATOR. "EntityViews" .DIRECTORY_SEPARATOR. "UserView.php";
require_once __DIR__.DIRECTORY_SEPARATOR. ".." .DIRECTORY_SEPARATOR. "EntityFactories" .DIRECTORY_SEPARATOR. "UserFactory.php";

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

    public function findViewWithUsername( string $username ): UserView|false {

        if ( empty($username) ) {
            return false;
        }

        $userEntity = $this->findWithUsername( $username );

        if ( ! $userEntity ) {
            return false;
        }

        return new UserView( $userEntity );

    }

    public function findWithUsername( string $username ): User|false {

        if ( empty($username) ) {
            return false;
        }

        $usernameEscapedAndQuoted = $this->getDb()->quote($username);

        $query = "SELECT * FROM user WHERE username=$usernameEscapedAndQuoted";

        try {
            $entity = $this->findWithQuery( $query );
            return $entity;
        }
        catch (Exception) {
            return false;
        }

    }

    protected function entityFromRow( Array $row ): User
    {
        $id = $row["id"];
        $username = $row["username"];
        $email = $row["email"];
        $enabled = $row["enabled"];
        $themeId = $row["theme_id"];

        $enabled = $this->boolFromInt($enabled);

        return new User($id, $username, $email, $enabled, $themeId);
    }

    /**
     * Create or replace to the db
     *
     * @return int|false User db ID or false on failure
     */
    public function save( UserView $view ): int|false
    {
        $userFactory = new UserFactory( $view );
        $entity = $userFactory->build();

        try {
            return $entity->save();
        }
        catch (Exception) {
            return false;
        }
    }

}