<?php

namespace App\Service\Utils;

use App\Service\Utils\Exception\ArrayUtilsException;

class ArrayUtils
{
    public static function combineSelf(array $array)
    {
        $keys = array_map(fn($item) => strval($item), $array);

        if (count(array_unique($keys)) !== count($array)) {
            throw new ArrayUtilsException('array can not be combined, values keys are not unique');
        }

        return array_combine($keys, $array);
    }

    public static function arrayMapWithKeys(callable $getKey, callable $getValue, array $array)
    {
        $result = [];
        foreach ($array as $item) {
            $result[$getKey($item)] = $getValue($item);
        }

        if (count($result) !== count($array)) {
            throw new ArrayUtilsException('array can not be combined, values keys are not unique');
        }

        return $result;
    }

    public static function avg(array $values)
    {
        if (!$values) {
            throw new ArrayUtilsException('Array is empty, can\'t calculate average');
        }

        return array_sum($values) / count($values);
    }


}
