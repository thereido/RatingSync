<?php

namespace RatingSync;

use Exception;
use PDO;
use PDOStatement;

require_once __DIR__.DIRECTORY_SEPARATOR. ".." .DIRECTORY_SEPARATOR. "src" .DIRECTORY_SEPARATOR. "Constants.php";
require_once __DIR__.DIRECTORY_SEPARATOR. ".." .DIRECTORY_SEPARATOR. "Entities" .DIRECTORY_SEPARATOR. "EntityInterface.php";

abstract class EntityManager
{

    static protected PDO|null $db;

    protected function getDb(): PDO
    {

        if ( ! empty( self::$db ) ) {
            return self::$db;
        }

        $mode = Constants::DB_MODE;

        if ($mode == Constants::DB_MODE_STANDARD) {

            $db_name = Constants::DB_DATABASE;

        } else if ($mode == Constants::DB_MODE_TEST) {

            $db_name = Constants::DB_TEST_DATABASE;

        }

        try {

            self::$db = new PDO( "mysql:host=localhost;dbname=$db_name", Constants::DB_ADMIN_USER, Constants::DB_ADMIN_PWD );
            self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

        } catch(PDOException $e) {

            logError("Connection failed: " . $e->getMessage());
            die("Connection failed: " . $e->getMessage());

        } catch(\Exception $e) {

            logError("Connection failed: " . $e->getMessage());

        }

        return self::$db;

    }

    /**
     * @throws Exception
     */
    protected function findWithQuery( string $query ): EntityInterface|false
    {

        try {

            $result = $this->getDb()->query($query);

        }
        catch (Exception $e) {

            logError("Exception with query: $query");
            logError($e->getMessage() . "\n" . $e->getTraceAsString());
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

    abstract protected function mandatoryColumns(): array;
    abstract protected function entityFromRow( Array $row ): EntityInterface;

    /**
     * @throws Exception
     */
    protected function findMultipleDbResult( string $query ): array|false
    {

        try {

            $result = $this->getDb()->query($query);

        }
        catch (Exception $e) {

            logError("Exception with query: $query");
            logError($e->getMessage() . "\n" . $e->getTraceAsString());
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

                logError("Error constructing a Entity: " . $e->getMessage());
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