<?php

namespace RatingSync;

use Exception;

require_once "EntityInterface.php";

abstract class Entity implements EntityInterface
{

    protected array $invalidProperties = array();
    protected array $invalidPropertyMessages = array();

    abstract static public function mandatoryColumns(): array;

    /**
     * Verify that a save() operation will succeed with these properties.
     * On an exception you can call invalidProperties() and
     * invalidPropertyMessages() to get more info.
     *
     * Business rules should never be used by this function, only db constraints.
     *
     * @return void On success nothing happens. On failure a EntityInvalidSaveException is thrown.
     * @throws EntityInvalidSaveException
     */
    abstract protected function verifyBeforeSaving(): void;

    /**
     * Return a db statement for saving this entity.
     *
     * @param bool $insert True if this a new entity. False is this an existing entity.
     * @return string
     */
    abstract protected function saveStmt( bool $insert ): string;

    /**
     * Use this function after calling save() if the verify function threw an
     * EntityInvalidSaveException. This function shows which properties
     * (Property name) have a problem. To see the message for each property
     * call invalidPropertyMessage( string $propertyName ).
     *
     * @return array Array of property names as strings for values that would not let the db save the entity
     */
    public function invalidProperties(): array
    {
        return $this->invalidProperties;
    }

    public function invalidPropertyMessage( string $propertyName ): string|false
    {

        if ( key_exists( $propertyName, $this->invalidPropertyMessages) ) {
            return $this->invalidPropertyMessages[ $propertyName ];
        }
        else {
            return false;
        }

    }

    protected function addInvalidProperty(string $propertyName, string $msg ): void
    {

        $this->invalidProperties[] = $propertyName;
        $this->invalidPropertyMessages[ $propertyName ] = $msg;

        logDebug($msg, __CLASS__."::".__FUNCTION__.":".__LINE__);

    }

    /**
     * Save to the database
     *
     * @return false|int Database ID of the object saved. False on failure.
     * @throws Exception
     * @throws EntityInvalidSaveException
     */
    public function save(): false|int
    {

        $this->verifyBeforeSaving();

        $id = $this->id;
        $insert = ($id == -1);
        $stmt = $this->saveStmt( $insert );

        $db = getDatabase();
        $executed = $db->exec( $stmt );
        $returnCode = $executed !== false;

        if ( $returnCode === false ) {
            logError("Error trying to save: $stmt", __CLASS__."::".__FUNCTION__.":".__LINE__);
            return false;
        }
        elseif ( $insert ) {
            return (int) $db->lastInsertId();
        }
        else {
            return $id;
        }

    }

}