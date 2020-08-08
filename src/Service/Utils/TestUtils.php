<?php

namespace App\Service\Utils;

class TestUtils
{
    public static function setProtectedProperty(object $object, string $property, string $value)
    {
        $reflection = new \ReflectionClass($object);
        $reflectionProperty = $reflection->getProperty($property);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $value);
        $reflectionProperty->setAccessible(false);

        return $object;
    }

    public static function getProtectedProperty(object $object, string $property)
    {
        $reflection = new \ReflectionClass($object);
        $reflectionProperty = $reflection->getProperty($property);
        $reflectionProperty->setAccessible(true);
        $value = $reflectionProperty->getValue($object);
        $reflectionProperty->setAccessible(false);

        return $value;
    }
}
