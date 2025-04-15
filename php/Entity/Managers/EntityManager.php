<?php

namespace RatingSync;

use Exception;
use PDO;
use PDOStatement;

require_once __DIR__ .DIRECTORY_SEPARATOR. ".." .DIRECTORY_SEPARATOR. ".." .DIRECTORY_SEPARATOR. "src" .DIRECTORY_SEPARATOR. "Constants.php";
require_once __DIR__ .DIRECTORY_SEPARATOR. ".." .DIRECTORY_SEPARATOR. "Entities" .DIRECTORY_SEPARATOR. "Entity.php";

abstract class EntityManager
{

    const NEW_ENTITY_ID = -1;

    static protected PDO|null $db;

    abstract protected function mandatoryColumns(): array;
    abstract protected function entityFromRow( Array $row ): Entity;

    protected function getDb(): PDO
    {

        if ( ! empty( self::$db ) ) {
            return self::$db;
        }

        $db_name = Constants::DB_MODE == Constants::DB_MODE_TEST ? Constants::DB_TEST_DATABASE : Constants::DB_DATABASE;
        $dsn = "mysql:host=". Constants::DB_HOSTNAME .";dbname=$db_name";

        try {

            self::$db = new PDO( $dsn, Constants::DB_ADMIN_USER, Constants::DB_ADMIN_PWD );
            self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

        } catch(PDOException $e) {

            logError("DB connection failed", prefix: __CLASS__."::".__FUNCTION__.":".__LINE__, e: $e);
            die("DB Connection failed: " . $e->getMessage());

        } catch(\Exception $e) {

            logError("DB connection failed", prefix: __CLASS__."::".__FUNCTION__.":".__LINE__, e: $e);

        }

        return self::$db;

    }

    /**
     * @throws Exception
     */
    protected function findWithQuery( string $query ): Entity|false
    {

        try {

            $result = $this->getDb()->query($query);

        }
        catch (Exception $e) {

            logError("Exception with query: $query", prefix: __CLASS__."::".__FUNCTION__.":".__LINE__, e: $e);
            throw $e;

        }

        if ( $result === false ) {
            return false;
        }

        if ( $result->rowCount() == 1 ) {

            $row = $result->fetch();

            if ( ! $row ) {
                return false;
            }

            $validColumns = $this->validateColumnNames( array_keys( $row ) );
            if ( ! $validColumns ) {
                return false;
            }

            return $this->entityFromRow( $row );

        }
        else {

            logDebug($result->rowCount() . " entities with query: $query");
            return false;

        }

    }

    /**
     * @throws Exception
     */
    protected function findMultipleDbResult( string $query ): array|false
    {

        try {

            $result = $this->getDb()->query($query);

        }
        catch (Exception $e) {

            logError("Exception with query: $query", e: $e);
            throw $e;

        }

        if ( $result === false ) {
            return false;
        }

        $entities = array();
        $rows = $result->fetchAll();

        foreach ($rows as $row) {

            try {

                $entity = $this->entityFromRow( $row );

            }
            catch (Exception $e) {

                logError("Error constructing a Entity", prefix: __CLASS__."::".__FUNCTION__.":".__LINE__, e: $e);
                return false;

            }

            $entities[] = $entity;

        }

        return $entities;

    }

    protected function boolFromInt( $int ): bool {

        if ( is_int($int) && $int > 0 ) {
            return true;
        }
        elseif ( is_bool($int)  ) {
            return $int;
        }
        else {
            return false;
        }

    }

    private function validateColumnNames( array $columns ): bool
    {

        $mandatoryKeys = $this->mandatoryColumns();
        $intersection = array_intersect( $mandatoryKeys, $columns);

        return count($mandatoryKeys) == count($intersection);

    }

}