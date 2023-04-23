<?php

namespace RatingSync;

use InvalidArgumentException;

require_once "Entity.php";
require_once "EntityInterface.php";
require_once __DIR__ .DIRECTORY_SEPARATOR. ".." .DIRECTORY_SEPARATOR. ".." .DIRECTORY_SEPARATOR. "src" .DIRECTORY_SEPARATOR. "Constants.php";
require_once __DIR__ .DIRECTORY_SEPARATOR. ".." .DIRECTORY_SEPARATOR. "Views" .DIRECTORY_SEPARATOR. "ThemeView.php";

/**
 * Database Theme
 */
final class ThemeEntity extends Entity
{
    const mandatoryColumns =  array("id", "name", "enabled");
    public readonly int $id;
    public readonly string $name;
    public readonly bool $enabled;

    /**
     * @param int $id
     * @param string $name
     * @param bool $enabled
     */
    public function __construct(int|null $id, string $name, bool $enabled)
    {
        if ( empty($name) ) {
            throw new InvalidArgumentException(__CLASS__ . " name must not be empty.");
        }

        $id = is_null($id) ? -1 : $id;

        $this->id = $id;
        $this->name = $name;
        $this->enabled = $enabled;

    }

    static public function mandatoryColumns(): array
    {
        return self::mandatoryColumns;
    }

    protected function saveStmt( bool $insert ): string
    {

        $db = getDatabase();
        $id = $this->id;
        $name = DbConn::quoteOrNull( $this->name, $db );
        $enabled = $this->enabled ? "true" : "false";

        if ( $insert ) {
            // Insert new entity

            $columns = ThemeProperty::Name->value
                . ", " . ThemeProperty::Enabled->value;

            $values = $name
                . ", " . $enabled;

            $stmt = "INSERT INTO theme ($columns) VALUES ($values)";

        }
        else {
            // Update

            $set = ThemeProperty::Name->value . "=" . $name
                . ", " . ThemeProperty::Enabled->value . "=" . $enabled;

            $where = ThemeProperty::Id->value . "=" . $id;

            $stmt = "UPDATE theme SET $set WHERE $where";

        }

        return $stmt;

    }

    protected function verifyBeforeSaving(): void
    {

        $this->invalidProperties = array();
        $this->invalidPropertyMessages = array();

        // Make sure the strings are not too long
        if ( (!empty($this->name)) && strlen($this->name) > ThemeProperty::nameMax() ) {

            $property = ThemeProperty::Name;
            $msg = $property->name . " max length is " . ThemeProperty::nameMax(); // Changing msg here needs to be changed in ThemeEntityTest too
            $this->addInvalidProperty( $property->name, $msg );

        }

        if ( $this->id == EntityManager::NEW_ENTITY_ID ) {
            // New entity

            // Is the name already taken?
            $existingEntity = themeMgr()->findWithName( $this->name );
            if ( $existingEntity !== false ) {

                $property = ThemeProperty::Name;
                $msg = $property->name . " ($this->name) is already taken"; // Changing msg here needs to be changed in UserEntityTest too
                $this->addInvalidProperty( $property->name, $msg );

            }

        }
        else {
            // Existing entity

            $existingEntity = themeMgr()->findWithId( $this->id );
            if ( ! ($existingEntity instanceof ThemeEntity) ) {

                $property = ThemeProperty::Id;
                $msg = $property->name . " " . $this->id . " not found"; // Changing msg here needs to be changed in UserEntityTest too
                $this->addInvalidProperty( $property->name, $msg );

            }
        }

        if ( count($this->invalidProperties) > 0 ) {

            throw new EntityInvalidSaveException();

        }

    }

    public function equals( ThemeEntity $other ): bool
    {
        $lhs = $this;
        $rhs = $other;

        if (   $lhs->id != $rhs->id
            || $lhs->name != $rhs->name
            || $lhs->enabled != $rhs->enabled
        ) {
            return false;
        }

        return true;
    }

}
