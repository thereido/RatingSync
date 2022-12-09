<?php

namespace RatingSync;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "Constants.php";

class ThemeView
{

    private int|null            $id;
    private string              $name;
    private bool                $enabled;

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