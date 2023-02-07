<?php

namespace RatingSync;

use Exception;
use InvalidArgumentException;

require_once "EntityInterface.php";
require_once __DIR__.DIRECTORY_SEPARATOR. ".." .DIRECTORY_SEPARATOR. "src" .DIRECTORY_SEPARATOR. "Constants.php";
require_once __DIR__.DIRECTORY_SEPARATOR. ".." .DIRECTORY_SEPARATOR. "EntityViews" .DIRECTORY_SEPARATOR. "UserView.php";

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

    /**
     * @param int $id
     * @param string $username
     * @param string|null $email
     * @param bool $enabled
     * @param int|null $themeId
     * @throws InvalidArgumentException
     */
    public function __construct(int $id, string $username, string|null $email, bool $enabled, int|null $themeId)
    {
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
     */
    public function save(): int
    {
        $db = getDatabase();
        $id = $this->id;
        $username = $db->quote($this->username);
        $email = is_null($this->email) ? "NULL" : $db->quote($this->email);
        $enabled = $this->enabled ? "true" : "false";
        $themeId = is_null($this->themeId) ? "NULL" : $this->themeId;

        $insert = ($id == -1);

        if ( $insert ) {
            // Insert new user

            $stmt = "INSERT INTO user";
            $stmt .= " (id, username, email, enabled, theme_id)";
            $stmt .= " VALUES (";
            $stmt .= "$id";
            $stmt .= ", $username";
            $stmt .= ", $email";
            $stmt .= ", $enabled";
            $stmt .= ", $themeId";
            $stmt .= ")";

        }
        else {
            // Update

            $stmt = "UPDATE user SET " .
                        "username=$username" .
                        ", email=$email" .
                        ", enabled=$enabled" .
                        ", theme_id=$themeId" .
                        " WHERE id=$id";

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