<?php

namespace RatingSync;

use Exception;

require_once __DIR__.DIRECTORY_SEPARATOR. ".." .DIRECTORY_SEPARATOR. "src" .DIRECTORY_SEPARATOR. "Constants.php";
require_once __DIR__.DIRECTORY_SEPARATOR. ".." .DIRECTORY_SEPARATOR. "EntityViews" .DIRECTORY_SEPARATOR. "ThemeView.php";

/**
 * Database Theme
 */
final class ThemeEntity
{
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
            logError($msg);
            throw new Exception($msg);
        }

        return (int) $db->lastInsertId();
    }

}
