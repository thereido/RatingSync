<?php

namespace RatingSync;

use Exception;
use InvalidArgumentException;

require_once "EntityInterface.php";
require_once "UserProperty.php";
require_once __DIR__ .DIRECTORY_SEPARATOR. ".." .DIRECTORY_SEPARATOR. "Views" .DIRECTORY_SEPARATOR. "UserView.php";
require_once __DIR__ .DIRECTORY_SEPARATOR. ".." .DIRECTORY_SEPARATOR. ".." .DIRECTORY_SEPARATOR. "Exceptions" .DIRECTORY_SEPARATOR. "EntityInvalidSaveException.php";
require_once __DIR__ .DIRECTORY_SEPARATOR. ".." .DIRECTORY_SEPARATOR. ".." .DIRECTORY_SEPARATOR. "src" .DIRECTORY_SEPARATOR. "Constants.php";

/**
 * Database User row
 */
final class UserEntity implements EntityInterface
{
    const mandatoryColumns =  array("id", "username", "enabled");
    /** @var int Database ID. Use -1 for a new user. */
    public readonly int $id;
    public readonly string $username;
    public readonly string|null $email;
    public readonly bool $enabled;
    public readonly int|null $themeId;

    private array $invalidPropertyNames = array();
    private array $invalidPropertyMessages = array();

    /**
     * @param int $id
     * @param string $username
     * @param string|null $email
     * @param bool $enabled
     * @param int|null $themeId
     * @throws InvalidArgumentException
     */
    public function __construct(int|null $id, string $username, string|null $email, bool $enabled, int|null $themeId)
    {

        $id = is_null($id) ? -1 : $id;

        // Validation
        $e = null;
        if ( $id < -1 || $id == 0 ) {
            $e = new InvalidArgumentException("Valid values for id are positive integers or -1 for a new user.");
        }
        elseif ( !is_null($themeId) && $themeId < 1 ) {
            $e = new InvalidArgumentException("Valid values for themeId are positive integers");
        }

        if ( !is_null( $e ) ) {
            logError($e->getMessage(), __CLASS__."::".__FUNCTION__.":".__LINE__);
            logError($e->getTraceAsString());
            throw $e;
        }

        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        $this->enabled = $enabled;
        $this->themeId = $themeId;

        // FIXME: Should we call verifyBeforeSaving() and throw exception if false?
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
        $username = $db->quote($this->username);
        $email = is_null($this->email) ? "NULL" : $db->quote($this->email);
        $enabled = $this->enabled ? "true" : "false";
        $themeId = is_null($this->themeId) ? "NULL" : $this->themeId;

        $insert = ($id == -1);

        if ( $insert ) {
            // Insert new user

            $columns = UserProperty::Id->value
                . ", " . UserProperty::Username->value
                . ", " . UserProperty::Email->value
                . ", " . UserProperty::Enabled->value
                . ", " . UserProperty::ThemeId->value;

            $values = $id
                . ", " . $username
                . ", " . $email
                . ", " . $enabled
                . ", " . $themeId;

            $stmt = "INSERT INTO user ($columns) VALUES ($values)";

        }
        else {
            // Update

            $set = UserProperty::Username->value . "=" . $username
                . ", " . UserProperty::Email->value . "=" . $email
                . ", " . UserProperty::Enabled->value . "=" . $enabled
                . ", " . UserProperty::ThemeId->value . "=" . $themeId;

            $where = UserProperty::Id->value . "=" . $id;

            $stmt = "UPDATE user SET $set WHERE $where";

        }

        $success = $db->exec($stmt) !== false;
        if ( ! $success ) {
            $msg = "Error trying to save: $stmt";
            logError($msg, __CLASS__."::".__FUNCTION__.":".__LINE__);
            throw new Exception($msg);
        }

        if ( $insert ) {
            return (int) $db->lastInsertId();
        }

        return $id;
    }


    /**
     * Verify that a save() operation will succeed with the current properties.
     * On an exception you can call invalidProperties() and
     * invalidPropertyMessages() to get more info.
     *
     * @return void On success nothing happens. On failure a EntityInvalidSaveException is thrown.
     * @throws EntityInvalidSaveException
     */
    private function verifyBeforeSaving(): void
    {

        $this->invalidPropertyNames = array();
        $this->invalidPropertyMessages = array();

        // Make sure the strings are not too long
        if ( strlen($this->username) > UserProperty::usernameMax() ) {
            $msg = UserProperty::Username->name
                . " max length is " . UserProperty::usernameMax(); // Changing msg here needs to be changed in UserEntityTest too
            $this->invalidPropertyNames[] = UserProperty::Username->name;
            $this->invalidPropertyMessages[ UserProperty::Username->name ] = $msg;
        }

        if ( strlen($this->email) > UserProperty::emailMax() ) {
            $msg = UserProperty::Email->name
                . " max length is " . UserProperty::emailMax(); // Changing msg here needs to be changed in UserEntityTest too
            $this->invalidPropertyNames[] = UserProperty::Email->name;
            $this->invalidPropertyMessages[ UserProperty::Email->name ] = $msg;
        }

        // Email format (db restrictions only, not business rules).
        // Note: Current there are not email format in the db

        // Make sure the theme in the db is enabled
        if ( !is_null( $this->themeId ) ) {

            $theme = themeMgr()->findViewWithId( $this->themeId );
            if ( $theme === false || $theme->getId() != $this->themeId ) {
                $msg = UserProperty::ThemeId->name
                    . " does not match an active theme"; // Changing msg here needs to be changed in UserEntityTest too
                $this->invalidPropertyNames[] = UserProperty::ThemeId->name;
                $this->invalidPropertyMessages[ UserProperty::ThemeId->name ] = $msg;
            }

        }

        if ( )
        // For a new user
            // is the username already taken?
            // FIXME

            // what about foreign keys... filmlist, user_source...
            // FIXME

        // For an existing user, make user the id and username match
            // FIXME

        if ( count($this->invalidPropertyNames) > 0 ) {

            throw new EntityInvalidSaveException();

        }
    }

    public function invalidProperties(): array
    {
        return $this->invalidPropertyNames;
    }

    public function invalidPropertyMessage( UserProperty $property ): string|false
    {
        if ( ! $property instanceof UserProperty ) {
            return false;
        }

        if ( key_exists( $property->name, $this->invalidPropertyMessages) ) {
            return $this->invalidPropertyMessages[ $property->name ];
        }
        else {
            return false;
        }
    }

    public function equals( UserEntity $other ): bool
    {
        $lhs = $this;
        $rhs = $other;

        if (   $lhs->id != $rhs->id
            || $lhs->username != $rhs->username
            || $lhs->email != $rhs->email
            || $lhs->enabled != $rhs->enabled
            || $lhs->themeId != $rhs->themeId
        ) {
            return false;
        }

        return true;
    }

}