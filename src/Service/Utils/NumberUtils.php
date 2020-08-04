<?php

namespace App\Service\Utils;

class NumberUtils
{
    const PRECISION = 0.00001;

    public static function floatEq(float $a, float $b)
    {
        if ($a === 0 && $b === 0){
            return true;
        }

        return abs(($a - $b) / ($b ?: $a)) < self::PRECISION;
    }

    public static function strToFloat(string $value): ?float
    {
        $value = str_replace(',', '.', $value);
        $value = preg_replace('/[^\d.]/i', '', $value);

        if (intval($value) === 0) {
            return null;
        }

        return floatval($value);
    }
}
