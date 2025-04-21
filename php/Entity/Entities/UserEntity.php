<?php

namespace RatingSync;

use InvalidArgumentException;

require_once "Entity.php";
require_once "UserProperty.php";
require_once __DIR__ .DIRECTORY_SEPARATOR. ".." .DIRECTORY_SEPARATOR. "Views" .DIRECTORY_SEPARATOR. "UserView.php";
require_once __DIR__ .DIRECTORY_SEPARATOR. ".." .DIRECTORY_SEPARATOR. ".." .DIRECTORY_SEPARATOR. "Exceptions" .DIRECTORY_SEPARATOR. "EntityInvalidSaveException.php";
require_once __DIR__ .DIRECTORY_SEPARATOR. ".." .DIRECTORY_SEPARATOR. ".." .DIRECTORY_SEPARATOR. "src" .DIRECTORY_SEPARATOR. "Constants.php";

/**
 * Database User row
 */
final class UserEntity extends Entity
{
    const mandatoryColumns =  array("id", "username", "enabled");
    /** @var int Database ID. Use -1 for a new user. */
    public readonly int $id;
    public readonly string $username;
    public readonly string|null $email;
    public readonly bool $enabled;
    public readonly int|null $themeId;

    /**
     * @param int|null $id
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
            logError("Error construction a UserEntity username=$username, id=$id", e: $e);
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

    protected function saveStmt( bool $insert ): string
    {

        $db = getDatabase();
        $id = $this->id;
        $username = DbConn::quoteOrNull( $this->username, $db );
        $email = DbConn::quoteOrNull( $this->email, $db );
        $enabled = $this->enabled ? "true" : "false";
        $themeId = is_null($this->themeId) ? "NULL" : $this->themeId;

        if ( $insert ) {
            // Insert new entity

            $columns = UserProperty::Username->value
                . ", " . UserProperty::Email->value
                . ", " . UserProperty::Enabled->value
                . ", " . UserProperty::ThemeId->value;

            $values = $username
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

        return $stmt;

    }

    protected function verifyBeforeSaving(): void
    {

        $this->invalidProperties = array();
        $this->invalidPropertyMessages = array();

        // Make sure the strings are not too long
        if ( (!empty($this->username)) && strlen($this->username) > UserProperty::usernameMax() ) {

            $property = UserProperty::Username;
            $msg = $property->name . " max length is " . UserProperty::usernameMax(); // Changing msg here needs to be changed in UserEntityTest too
            $this->addInvalidProperty( $property->name, $msg );

        }

        if ( (!empty($this->email)) && strlen($this->email) > UserProperty::emailMax() ) {

            $property = UserProperty::Email;
            $msg = $property->name . " max length is " . UserProperty::emailMax(); // Changing msg here needs to be changed in UserEntityTest too
            $this->addInvalidProperty( $property->name, $msg );

        }

        // Email format (db constraints only, not business rules).
        // Note: Current there are not email format in the db

        // Make sure the theme in the db is enabled
        if ( !is_null( $this->themeId ) ) {

            $theme = themeMgr()->findViewWithId( $this->themeId );
            if ( $theme === false || $theme->isEnabled() === false ) {

                $property = UserProperty::ThemeId;
                $msg = $property->name . " does not match an active theme"; // Changing msg here needs to be changed in UserEntityTest too
                $this->addInvalidProperty( $property->name, $msg );

            }

        }

        if ( $this->id == EntityManager::NEW_ENTITY_ID ) {
            // New user

            // Is the username already taken?
            $existingEntity = userMgr()->findWithUsername( $this->username );
            if ( $existingEntity !== false ) {

                $property = UserProperty::Username;
                $msg = $property->name . " ($this->username) is already taken"; // Changing msg here needs to be changed in UserEntityTest too
                $this->addInvalidProperty( $property->name, $msg );

            }

        }
        else {
            // Existing user

            // Make user the id and username match.
            // The db would let you change the username for existing id, except
            // that some tables are using username foreign keys. They should be
            // using id instead. The tables are filmlist, user_filmlist, rating
            // and user_source.
            // Until that is fixed we cannot change the username.

            $existingEntity = userMgr()->findWithId( $this->id );
            if ( $existingEntity instanceof UserEntity ) {

                if ( $this->username !== $existingEntity->username ) {

                    $property = UserProperty::Username;
                    $msg = "It looks like you are trying to change the "
                        . $property->name . ". Currently, that feature is not available."; // Changing msg here needs to be changed in UserEntityTest too
                    $this->addInvalidProperty( $property->name, $msg );

                }

            }
            else {

                $property = UserProperty::Id;
                $msg = $property->name . " " . $this->id . " not found"; // Changing msg here needs to be changed in UserEntityTest too
                $this->addInvalidProperty( $property->name, $msg );

            }

        }

        if ( count($this->invalidProperties) > 0 ) {

            throw new EntityInvalidSaveException();

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