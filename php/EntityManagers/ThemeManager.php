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

    public function findViewWithId( int $id ): ThemeView|false {

        $entity = $this->findWithId( $id );

        if ( ! $entity ) {
            return false;
        }

        return new ThemeView( $entity );

    }

    public function findWithId( int $id ): ThemeEntity|false {

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

    public function findViewWithName( string $name ): ThemeView|false {

        if ( empty($name) ) {
            return false;
        }

        $entity = $this->findWithName( $name );

        if ( ! $entity ) {
            return false;
        }

        return new ThemeView( $entity );

    }

    public function findWithName( string $name ): ThemeEntity|false {

        if ( empty($name) ) {
            return false;
        }

        $nameEscapedAndQuoted = $this->getDb()->quote($name);

        $query =    "SELECT * FROM theme" .
                    "  WHERE name=$nameEscapedAndQuoted" .
                    "    AND enabled=TRUE";

        try {
            return $this->findWithQuery( $query );
        }
        catch (Exception) {
            return false;
        }

    }

    public function findViewAll( bool $onlyEnabled = true ): array|false {

        $views = [];

        $entities = $this->findAll( $onlyEnabled );

        foreach ($entities as $entity) {
            $entity = new ThemeView( $entity );
            $views[$entity->getId()] = $entity;
        }

        return $views;

    }

    public function findAll( bool $onlyEnabled = true ): array|false {

        $entities = [];

        $query = "SELECT * FROM theme";

        if ( $onlyEnabled ) {
            $query .= " WHERE enabled=TRUE";
        }

        try {

            $result = $this->findMultipleDbResult( $query );

        }
        catch (Exception) {
            return false;
        }

        $rows = $result->fetchAll();

        if ( ! $rows ) {
            return false;
        }

        foreach ($rows as $row) {
            $entities[] = $this->entityFromRow( $row );
        }

        return $entities;

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

        return $this->entityFromRow( $row );

    }

    /**
     * @throws Exception
     */
    public function findDefaultView(): ThemeView|false {

        $entity = $this->findDefaultEntity();

        return new ThemeView( $entity );

    }

    protected function entityFromRow( Array $row ): ThemeEntity
    {
        $id = $row["id"];
        $name = $row["name"];
        $enabled = $row["enabled"];
        $default = $row["default"];

        return new ThemeEntity($id, $name, $enabled, $default);
    }

}