<?php

namespace App\Service\ResultPageParsers\Arf;

use App\Service\ResultPageParsers\Exception\ParseResultsException;

class TableConfig
{
    const GROUP__MALE = 'Мужчины';
    const GROUP__FEMALE = 'Женщины';

    private static function getGroups()
    {
        return [
            self::GROUP__MALE,
            self::GROUP__FEMALE,
        ];
    }

    public static function titleToGroupConverter(string $title)
    {
        if (in_array($title, self::getGroups())) {
            return $title;
        }

        if (preg_match('/(мужской|женский) зачёт/', $title, $match)) {
            $cnf = [
                'мужской' => self::GROUP__MALE,
                'женский' => self::GROUP__FEMALE,
            ];

            return $cnf[$match[1]];
        }

        return null;

        throw new ParseResultsException('Can\'t parse as group from title');
    }
}
