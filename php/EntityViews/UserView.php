<?php

namespace RatingSync;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "Constants.php";

class UserView
{

    public readonly int|null            $id;
    public readonly string              $username;
    public readonly string|null         $email;
    public readonly bool                $enabled;
    public readonly string|null         $themeId;

    public function __construct( User $entity ) {

        $this->id = $entity->id;
        $this->username = $entity->username;
        $this->email = $entity->email;
        $this->enabled = $entity->enabled;
        $this->themeId = $entity->themeId;

    }

}