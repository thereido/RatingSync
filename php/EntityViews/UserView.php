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
    private int|null            $entityThemeId;

    private ThemeView|null      $theme;

    public function __construct( UserEntity $entity ) {

        $this->id = $entity->id;
        $this->username = $entity->username;
        $this->email = $entity->email;
        $this->enabled = $entity->enabled;
        $this->entityThemeId = $entity->themeId;
        $this->theme = null;

        $theme = null;
        if ( ! empty( $this->entityThemeId ) ) {
            $theme = themeMgr()->findViewWithId( $this->entityThemeId );
        }

        if ( $theme instanceof ThemeView ) {
            $this->theme = $theme;
        }
        else {
            $this->setThemeToDefault();
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

    public function getThemeId( bool $ignoreDefault = false ): int|null {

        if ( $ignoreDefault ) {
            return $this->entityThemeId;
        }
        else {
            return $this->theme->getId();
        }
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
        $this->entityThemeId = $theme->getId();

        return true;

    }

    private function setThemeToDefault(): bool {

        try {

            $theme = themeMgr()->findDefaultView();

        }
        catch (Exception) {

            logError("New UserView without a theme. No default available.", __CLASS__."::".__FUNCTION__.":".__LINE__);
            return false;

        }

        if ( $theme instanceof ThemeView ) {
            $this->theme = $theme;
            return true;
        }

        return false;

    }

}