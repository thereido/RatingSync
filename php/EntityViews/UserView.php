<?php

namespace RatingSync;

use Exception;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "Constants.php";

class UserView
{

    private int|null            $id;
    private string              $username;
    private string|null         $email;
    private bool                $enabled;

    private ThemeView|null      $theme;

    public function __construct( User $entity ) {

        $this->id = $entity->id;
        $this->username = $entity->username;
        $this->email = $entity->email;
        $this->enabled = $entity->enabled;

        $theme = themeMgr()->findViewWithUsername( $this->username );
        if ( empty($theme) ) {

            try {

                $theme = themeMgr()->findDefaultView();

            }
            catch (Exception $e) {

                logError("New UserView without a theme. No default available.");

            }

        }

        $this->theme = $theme;

    }

    public function getId(): int|null {
        return $this->id;
    }

    public function getUsername(): string {
        return $this->username;
    }

    public function getEmail(): string|null {
        return $this->email;
    }

    public function getEnabled(): bool {
        return $this->enabled;
    }

    public function getTheme(): ThemeView|null {
        return $this->theme;
    }

    public function getThemeName(): string {
        return $this->theme?->getName() ?: Constants::THEME_DEFAULT;
    }

}