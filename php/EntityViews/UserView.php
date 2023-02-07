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

    public function __construct(UserEntity $entity ) {

        $this->id = $entity->id;
        $this->username = $entity->username;
        $this->email = $entity->email;
        $this->enabled = $entity->enabled;
        $this->theme = null;

        $theme = themeMgr()->findViewWithUsername( $this->username );
        if ( ! $theme instanceof ThemeView ) {

            try {

                $theme = themeMgr()->findDefaultView();

            }
            catch (Exception $e) {

                logError("New UserView without a theme. No default available.");

            }

        }

        if ( $theme ) {
            $this->theme = $theme;
        }

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

    public function isEnabled(): bool {
        return $this->enabled;
    }

    /**
     * @param bool $enabled default is true
     * @throws Exception
     */
    public function enable( bool $enabled = true ): void {
        $this->enabled = $enabled;
    }

    public function getTheme(): ThemeView|null {
        return $this->theme;
    }

    public function getThemeName(): string {
        return $this->theme?->getName() ?: Constants::THEME_DEFAULT;
    }

    public function setTheme( int $themeId ): bool {

        $theme = themeMgr()->findViewWithId( $themeId );

        if ( (! $theme instanceof ThemeView) || (! $theme->isEnabled()) ) {
            return false;
        }

        $this->theme = $theme;

        return true;

    }

}