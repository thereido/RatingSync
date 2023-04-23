<?php

namespace RatingSync;

use Exception;
use InvalidArgumentException;

require_once __DIR__ .DIRECTORY_SEPARATOR. ".." .DIRECTORY_SEPARATOR. "Entities" .DIRECTORY_SEPARATOR. "UserEntity.php";
require_once __DIR__ .DIRECTORY_SEPARATOR. ".." .DIRECTORY_SEPARATOR. "Views" .DIRECTORY_SEPARATOR. "UserView.php";

final class UserFactory
{
    private UserView $_userView;

    public function __construct( UserView $view )
    {

        $this->_userView = $view;

    }

    /**
     * @throws Exception
     */
    public function build(): UserEntity {

        $userId = $this->_userView->getId();
        if ( is_null($userId) ) {
            $userId = -1;
        }

        try {
            return new UserEntity(
                $userId,
                $this->_userView->getUsername(),
                $this->_userView->getEmail(),
                $this->_userView->isEnabled(),
                $this->_userView->getThemeId(true),
            );
        } catch (InvalidArgumentException $argEx) {
             $e = new Exception("Invalid UserEntity from this UserView.", 0, $argEx);
             logError($e->getMessage(), __CLASS__."::".__FUNCTION__.":".__LINE__);
             logError($e->getTraceAsString());

             throw $e;
        }

    }

}