<?php

namespace RatingSync;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "Constants.php";

class ThemeView
{

    private int|null            $id;
    private string              $name;
    private bool                $enabled;

    // Properties only used by the coder using the view. No validation for storage to the DB.
    public string|null          $label = "";
    public bool                 $isActive = false;
    public string|null          $background = "";
    public string|null          $surface = "";
    public string|null          $color1 = "";
    public string|null          $color2 = "";
    public string|null          $color3 = "";

    public function __construct( ThemeEntity $entity ) {

        $this->id = $entity->id;
        $this->name = $entity->name;
        $this->enabled = $entity->enabled;

    }

    public function getId(): int|null {
        return $this->id;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getEnabled(): bool {
        return $this->enabled;
    }

}