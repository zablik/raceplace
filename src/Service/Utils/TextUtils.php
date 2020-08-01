<?php

namespace App\Service\Utils;

class TextUtils
{
    public static function splitLines(string $text)
    {
        return preg_split('/((\r?\n)|(\r\n?))/i', $text);
    }
}
