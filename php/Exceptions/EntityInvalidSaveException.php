<?php

namespace RatingSync;

use \Exception;
class EntityInvalidSaveException extends Exception
{
    public function __construct($message = 'Cannot save the entity with these values')
    {
        return parent::__construct($message, 700);
    }
}