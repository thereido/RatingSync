<?php

namespace RatingSync;

enum UserProperty: string
{

    case Id = "id";
    case Username = "username";
    case Password = "password";
    case Email = "email";
    case Enabled = "enabled";
    case ThemeId = "theme_id";
    case Timestamp = "ts";

    static public function usernameMax(): int
    {
        return 50;
    }

    static public function emailMax(): int
    {
        return 50;
    }

}
