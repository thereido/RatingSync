<?php

namespace RatingSync;

use Exception;

require_once "EntityManager.php";
require_once __DIR__.DIRECTORY_SEPARATOR. ".." .DIRECTORY_SEPARATOR. "Entities" .DIRECTORY_SEPARATOR. "ThemeEntity.php";
require_once __DIR__.DIRECTORY_SEPARATOR. ".." .DIRECTORY_SEPARATOR. "EntityViews" .DIRECTORY_SEPARATOR. "ThemeView.php";

final class ThemeManager extends EntityManager
{

    public function __construct() {
    }

    public function findWithId( int $id ): Theme|null {

        $query = "SELECT * FROM theme WHERE id=" . $id;

        try {
            return $this->findWithQuery( $query );
        }
        catch (Exception) {
            return false;
        }

    }

    public function findViewWithUsername( string $username ): ThemeView|false {

        if ( empty($username) ) {
            return false;
        }

        $entity = $this->findWithUsername( $username );

        if ( ! $entity ) {
            return false;
        }

        return new ThemeView( $entity );

    }

    public function findWithUsername( string $username ): ThemeEntity|false {

        if ( empty($username) ) {
            return false;
        }

        $usernameEscapedAndQuoted = $this->getDb()->quote($username);

        $query =    "SELECT t.* FROM user u, theme t" .
                    "  WHERE u.username=$usernameEscapedAndQuoted" .
                    "    AND u.theme_id=t.id" .
                    "    AND t.enabled=TRUE";

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
    public function findDefaultEntity(): ThemeEntity|false {

        $query =    "SELECT * FROM theme" .
                    "  WHERE `default`=true AND enabled=true" .
                    "  ORDER BY id ASC" .
                    "  LIMIT 1";

        try {

            $result = $this->getDb()->query($query);

        }
        catch (Exception $e) {

            logError("Exception getting default theme: " . $e->getMessage());
            throw $e;

        }

        if ( ! $result ) {

            return false;

        }

        $row = $result->fetch();
        $id = $row["id"];
        $name = $row["name"];
        $enabled = $this->boolFromInt( $row["enabled"] );
        $default = $this->boolFromInt( $row["default"] );

        return new ThemeEntity($id, $name, $enabled, $default);

    }

    /**
     * @throws Exception
     */
    public function findDefaultView(): ThemeView|false {

        $entity = $this->findDefaultEntity();

        return new ThemeView( $entity );

    }

    /**
     * @throws Exception
     */
    private function findWithQuery( string $query ): ThemeEntity|false
    {

        $result = $this->findOneDbResult( $query );

        if ( ! $result ) {

            return false;

        }

        $row = $result->fetch();
        $id = $row["id"];
        $name = $row["name"];
        $enabled = $row["enabled"];
        $default = $row["default"];

        return new ThemeEntity($id, $name, $enabled, $default);

    }

}