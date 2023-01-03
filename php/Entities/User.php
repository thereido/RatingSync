<?php

namespace RatingSync;

use Exception;

require_once "EntityInterface.php";
require_once __DIR__.DIRECTORY_SEPARATOR. ".." .DIRECTORY_SEPARATOR. "src" .DIRECTORY_SEPARATOR. "Constants.php";
require_once __DIR__.DIRECTORY_SEPARATOR. ".." .DIRECTORY_SEPARATOR. "EntityViews" .DIRECTORY_SEPARATOR. "UserView.php";

/**
 * Database User row
 */
final class User implements EntityInterface
{
    /** @var int Database ID. Use -1 for a new user. */
    public readonly int $id;
    public readonly string $username;
    public readonly string|null $email;
    public readonly bool $enabled;
    public readonly int|null $themeId;

    public function __construct(int $id, string $username, string|null $email, bool $enabled, int|null $themeId)
    {
        // FIXME valid the values

        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        $this->enabled = $enabled;
        $this->themeId = $themeId;
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
        $enabled = $this->enabled;
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
            logError($msg);
            throw new Exception($msg);
        }

        if ( $insert ) {
            return (int) $db->lastInsertId();
        }

        return $id;
    }

}