<?php

namespace App\Service\ResultPageParsers\OBelarus;

use App\Entity\RaceResultsSource;
use App\Service\ResultPageParsers\DTO\ResultsTableRow;
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

    const GROUP__MALE = 'Мужчины';
    const GROUP__FEMALE = 'Женщины';

    public static function getConfig(string $type): array
    {
        if (!in_array($type, RaceResultsSource::getConfigTypes())) {
            throw new ParseResultsException(sprintf('Unexpected config type "%s"', $type));
        }

        $generalConfig = self::generalConfig();

        if ($type === RaceResultsSource::TYPE_NO_GROUP) {
            $generalConfig[self::COL_NOTE] = ['from' => 88, 'length' => 10];
        }
        if ($type === RaceResultsSource::TYPE_WITH_PENALTY) {
            $generalConfig[self::COL_NOTE] = ['from' => 85, 'length' => 4];
            $generalConfig[self::COL_DISTANCE] = ['from' => 89, 'length' => 11];
            $generalConfig[self::COL_PLACE] = ['from' => 100, 'length' => 4];
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

    public static function customTableTitleConverter(string $title)
    {
        $config = [
            'Т5-Ж' => 'Т5-Ж -- ' . self::GROUP__FEMALE,
            'Т5-М' => 'Т5-М -- ' . self::GROUP__MALE,
            'Т10-Ж' => 'Т10-Ж -- ' . self::GROUP__FEMALE,
            'Т10-М' => 'Т10-М -- ' . self::GROUP__MALE,
            'Т21-Ж' => 'Т21-Ж -- ' . self::GROUP__FEMALE,
            'Т21-М' => 'Т21-М -- ' . self::GROUP__MALE,

            'Ж-5' => 'Ж-5 -- ' . self::GROUP__FEMALE,
            'М-5' => 'М-5 -- ' . self::GROUP__MALE,
            'Ж-10' => 'Ж-10 -- ' . self::GROUP__FEMALE,
            'М-10' => 'М-10 -- ' . self::GROUP__MALE,

            'Т50:М' => 'Т50:М -- ' . self::GROUP__MALE,
            'Т50:Ж' => 'Т50:Ж -- ' . self::GROUP__FEMALE,

            '21:Ж' => '21:Ж -- ' . self::GROUP__FEMALE,
            '21:М' => '21:М -- ' . self::GROUP__MALE,

            '10:М' => '10:М -- ' . self::GROUP__MALE,
            '10:Ж' => '10:Ж -- ' . self::GROUP__FEMALE,

            '5:М' => '5:М -- ' . self::GROUP__MALE,
            '5:Ж' => '5:Ж -- ' . self::GROUP__FEMALE,

            'М16' => 'М16 -- ' . self::GROUP__MALE,
            'Ж16' => 'Ж16 -- ' . self::GROUP__FEMALE,

            'М25' => 'М25 -- ' . self::GROUP__MALE,
            'Ж25' => 'Ж25 -- ' . self::GROUP__FEMALE,

            'М8' => 'М8 -- ' . self::GROUP__MALE,
            'Ж8' => 'Ж8 -- ' . self::GROUP__FEMALE,




        ];

        return $config[$title] ?? $title;
    }

    public static function getHook(string $type): ?callable
    {
        $hooks = [
            RaceResultsSource::TYPE_WITH_PENALTY => function (ResultsTableRow $row) {
                if (trim($row->note) === '-') {
                    $row->note = '';
                }
                if (!empty($row->note)) {
                    $row->note = sprintf('Штраф %d часов', (int)$row->note);
                }

                return $row;
            }
        ];

        return $hooks[$type] ?? null;
    }
}
