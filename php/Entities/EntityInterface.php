<?php

namespace RatingSync;

interface EntityInterface
{
    static public function mandatoryColumns(): array;
    function save(): int;

}