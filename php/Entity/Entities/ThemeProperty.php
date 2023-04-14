<?php

namespace RatingSync;

enum ThemeProperty: string
{

    case Id = "id";
    case Name = "name";
    case Enabled = "enabled";
    case Timestamp = "ts";

    static public function nameMax(): int
    {
        return 50;
    }

}
