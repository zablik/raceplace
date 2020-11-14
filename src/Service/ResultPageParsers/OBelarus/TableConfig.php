<?php

namespace App\Service\ResultPageParsers\OBelarus;

use App\Entity\RaceResultsSource;
use App\Service\ResultPageParsers\Exception\ParseResultsException;

class TableConfig
{
    const COL_N = 'n';
    const COL_NAME = 'name';
    const COL_REGION = 'regionClub';
    const COL_BORN = 'yearBorn';
    const COL_NUMBER_PLATE = 'numberPlate';
    const COL_DISTANCE = 'distance';
    const COL_TIME = 'time';
    const COL_PLACE = 'place';
    const COL_NOTE = 'note';

    public static function getConfig(string $type): array
    {
        if (!in_array($type, RaceResultsSource::getConfigTypes())) {
            throw new ParseResultsException(sprintf('Unexpected config type "%s"', $type));
        }

        $generalConfig = self::generalConfig();

        if ($type === RaceResultsSource::TYPE_NO_GROUP) {
            $generalConfig[self::COL_NOTE] = ['from' => 88, 'length' => 10];
        }

        return $generalConfig;
    }

    private static function generalConfig()
    {
        return [
            self::COL_N => ['from' => 0, 'length' => 5],
            self::COL_NAME => ['from' => 5, 'length' => 26],
            self::COL_REGION => ['from' => 31, 'length' => 21],
            self::COL_BORN => ['from' => 52, 'length' => 4],
            self::COL_NUMBER_PLATE => ['from' => 58, 'length' => 6],
            self::COL_DISTANCE => ['from' => 64, 'length' => 11],
            self::COL_TIME => ['from' => 75, 'length' => 9],
            self::COL_PLACE => ['from' => 84, 'length' => 4],
            self::COL_NOTE => ['from' => 100, 'length' => 10],
        ];
    }
}
