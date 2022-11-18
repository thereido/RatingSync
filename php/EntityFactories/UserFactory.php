<?php

namespace RatingSync;

require_once __DIR__ . DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR . "UserView.php";
require_once __DIR__ . DIRECTORY_SEPARATOR;

final class UserFactory
{
    private UserView $_userView;

    public function __construct( UserView $view )
    {
        $this->_userView = $view;

    }

    public function build() {

        return new User(
            -1,
            $this->_userView->getUsername(),
            $this->_userView->getEmail(),
            $this->_userView->getEnabled(),
            $this->_userView->getThemeId(),
        );

    }

}