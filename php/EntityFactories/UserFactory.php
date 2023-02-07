<?php

namespace RatingSync;

require_once __DIR__.DIRECTORY_SEPARATOR. ".." .DIRECTORY_SEPARATOR. "Entities" .DIRECTORY_SEPARATOR. "UserEntity.php";
require_once __DIR__.DIRECTORY_SEPARATOR. ".." .DIRECTORY_SEPARATOR. "EntityViews" .DIRECTORY_SEPARATOR. "UserView.php";

final class UserFactory
{
    private UserView $_userView;

    public function __construct( UserView $view )
    {

        $this->_userView = $view;

    }

    public function build(): UserEntity {

        $userId = $this->_userView->getId();
        if ( is_null($userId) ) {
            $userId = -1;
        }

        return new UserEntity(
            $userId,
            $this->_userView->getUsername(),
            $this->_userView->getEmail(),
            $this->_userView->isEnabled(),
            $this->_userView->getThemeId(true),
        );

    }

}