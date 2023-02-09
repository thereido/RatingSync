<?php

namespace RatingSync;

require_once "EntityInterface.php";

abstract class Entity implements EntityInterface
{

    protected array $invalidProperties = array();
    protected array $invalidPropertyMessages = array();

    abstract static public function mandatoryColumns(): array;

    /**
     * Save to the database
     *
     * @return int Database ID of the object saved
     * @throws Exception
     * @throws EntityInvalidSaveException
     */
    abstract public function save(): int;

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
     * Use this function after calling save() if the verify function threw an
     * EntityInvalidSaveException. This function shows which properties
     * (UserProperty enum) have a problem. To see the message for each property
     * call invalidPropertyMessage( UserProperty $property ).
     *
     * @return array Array of UserProperty enums for values that would not let the db save the entity
     */
    public function invalidProperties(): array
    {
        return $this->invalidProperties;
    }

    public function invalidPropertyMessage( UserProperty $property ): string|false
    {

        if ( key_exists( $property->name, $this->invalidPropertyMessages) ) {
            return $this->invalidPropertyMessages[ $property->name ];
        }
        else {
            return false;
        }

    }

    protected function addInvalidProperty( UserProperty $property, string $msg ): void
    {

        $this->invalidProperties[] = $property;
        $this->invalidPropertyMessages[ $property->name ] = $msg;

        logDebug($msg, __CLASS__."::".__FUNCTION__.":".__LINE__);

    }

}