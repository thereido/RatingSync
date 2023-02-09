<?php

namespace RatingSync;

use Exception;

require_once "EntityInterface.php";
require_once __DIR__ .DIRECTORY_SEPARATOR. ".." .DIRECTORY_SEPARATOR. ".." .DIRECTORY_SEPARATOR. "src" .DIRECTORY_SEPARATOR. "Constants.php";
require_once __DIR__ .DIRECTORY_SEPARATOR. ".." .DIRECTORY_SEPARATOR. "Views" .DIRECTORY_SEPARATOR. "ThemeView.php";

/**
 * Database Theme
 */
final class ThemeEntity extends Entity
{
    const mandatoryColumns =  array("id", "name", "enabled", "default");
    public readonly int $id;
    public readonly string $name;
    public readonly bool $enabled;
    public readonly bool $default;

    public function __construct(int $id, string $name, bool $enabled, bool $default)
    {
        // FIXME valid the values

        $this->id = $id;
        $this->name = $name;
        $this->enabled = $enabled;
        $this->default = $default;

    }

    static public function mandatoryColumns(): array
    {
        return self::mandatoryColumns;
    }


    /**
     * Save to the database
     *
     * @return int Database ID of the object saved
     * @throws Exception
     * @throws EntityInvalidSaveException
     */
    public function save(): int
    {
        $this->verifyBeforeSaving();

        $db = getDatabase();
        $id = $this->id;
        $name = $db->quote($this->name);
        $enabled = $this->enabled;
        $default = $this->default;

        $stmt = "REPLACE theme";
        $stmt .= " (id, name, enabled)";
        $stmt .= " VALUES (";
        $stmt .= "$id";
        $stmt .= ", $name)";
        $stmt .= ", $enabled";
        $stmt .= ", $default";
        $stmt .= ")";

        $success = $db->exec($stmt) !== false;
        if ( ! $success ) {
            $msg = "Error trying to save: $stmt";
            logError($msg, __CLASS__."::".__FUNCTION__.":".__LINE__);
            throw new Exception($msg);
        }

        return (int) $db->lastInsertId();
    }

    protected function verifyBeforeSaving(): void
    {

        $this->invalidProperties = array();
        $this->invalidPropertyMessages = array();

        // Make sure the strings are not too long

        // FIXME

        if ( $this->id == EntityManager::NEW_ENTITY_ID ) {
            // New entity

            // FIXME

        }
        else {
            // Existing entity

            // FIXME
        }

        if ( count($this->invalidProperties) > 0 ) {

            throw new EntityInvalidSaveException();

        }

    }

}
