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
}
