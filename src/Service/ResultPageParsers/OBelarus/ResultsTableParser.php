<?php

namespace App\Service\ResultPageParsers\OBelarus;

use App\Service\Utils\TextUtils;
use Symfony\Component\DomCrawler\Crawler;

abstract class ResultsTableParser
{
    const COL_N = 'n';
    const COL_NAME = 'name';
    const COL_REGION = 'region-club';
    const COL_BORN = 'year-born';
    const COL_NUMBER_PLATE = 'number-plate';
    const COL_DISTANCE = 'distance';
    const COL_TIME = 'time';
    const COL_PLACE = 'place';
    const COL_NOTE = 'note';

    const RACE_TYPE = 'race-type';
    const RACE_GROUP = 'race-group';
    const RACE_DISTANCE = 'race-distance';

    protected static function tableConfig()
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
            self::COL_NOTE => ['from' => 88, 'length' => 10],
        ];
    }

    public function parseResultsPage(string $html): array
    {
        $nodes = (new Crawler($html))
            ->filterXPath('//div[@id=\'results-body\']')
            ->children();

        $results = [];
        $raceCount = $resultsCount = 0;
        foreach ($nodes as $node) {
            if ($node->nodeName === 'h2') {
                $results[$raceCount++]['race'] = $this->parseRaceParams($node->nodeValue);
            } elseif ($node->nodeName === 'pre') {
                $results[$resultsCount++]['results'] = $this->parseTextTable($node->nodeValue);
            }
        }

        $results = array_filter($results, fn($result) => !empty($result['race']) && !empty($result['results']));

        return $results;
    }

    abstract protected function parseRaceParams(string $raceTitle): ?array;

    protected static function parseRow(string $row): array
    {
        $columnsConfig = self::tableConfig();

        $result = [];
        foreach ($columnsConfig as $colName => $cnf) {
            $result[$colName] = trim(mb_substr($row, $cnf['from'], $cnf['length']));
        }

        return $result;
    }

    protected static function validateRow(string $line): bool
    {
        return !empty(str_replace(['-', ' '], '', $line))
            && !preg_match('/(Фамилия Имя|Дистанция|Результат|Главный судья|Главный секретарь)/i', $line);
    }

    protected function parseTextTable(string $text): array
    {
        $results = [];

        $lines = TextUtils::splitLines($text);
        $lines = array_filter($lines, fn($line) => ResultsTableParser::validateRow($line));

        foreach ($lines as $line) {
            $results[] = ResultsTableParser::parseRow($line);
        }

        return $results;
    }
}
