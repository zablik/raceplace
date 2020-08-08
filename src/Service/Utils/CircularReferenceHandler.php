<?php

namespace App\Service\Utils;

class CircularReferenceHandler
{
    public function __invoke($object)
    {
        return $object->getId();
    }
}
