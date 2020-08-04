<?php

namespace App\Service\ResultPageParsers\OBelarus;

use App\Service\ResultPageParsers\Exception\ParseResultsException;
use App\Service\ResultPageParsers\OBelarus\DTO\ResultsTable;
use App\Service\ResultPageParsers\OBelarus\DTO\ResultsTableRow;
use App\Service\Utils\TextUtils;
use Symfony\Component\DomCrawler\Crawler;

class WebResultsTableParser implements WebDataParserInterface
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

    const RACE_TYPE = 'race-type';
    const RACE_GROUP = 'race-group';
    const RACE_DISTANCE = 'race-distance';
    const RACE_CODE = 'race-code';

    const GROUP__MALE = 'Мужчины';
    const GROUP__FEMALE = 'Женщины';

    private static function getAvailableGroups()
    {
        return [
            self::GROUP__FEMALE,
            self::GROUP__MALE,
        ];
    }

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

    /**
     * @param string $html
     * @return ResultsTable[]
     */
    public function parse(string $html): array
    {
        $nodes = (new Crawler($html))
            ->filterXPath('//div[@id=\'results-body\']')
            ->children();

        $results = [];
        $raceCount = $resultsCount = 0;
        foreach ($nodes as $node) {
            $results[$raceCount] = new ResultsTable();
            if ($node->nodeName === 'h2') {
                $raceParams = $this->parseRaceParams($node->nodeValue);
                $results[$raceCount]->code = $raceParams[self::RACE_CODE];
                $results[$raceCount]->group = $raceParams[self::RACE_GROUP];
                $raceCount++;
            } elseif ($node->nodeName === 'pre') {
                $results[$resultsCount++]->results = $this->parseTextTable($node->nodeValue);
            }
        }

        $results = array_filter($results, fn(ResultsTable $table) => !empty($table->code) && !empty($table->results));

        return $results;
    }

    protected function parseRaceParams(string $tableTitle): ?array
    {
        $regex = sprintf('/^([a-zа-яё\d:\-]+)\s-.*-\s(%s)$/iu', implode('|', self::getAvailableGroups()));
        if (!preg_match($regex, $tableTitle, $match)) {
            throw new ParseResultsException(sprintf('Unable to parse race params from title "%s"', $tableTitle));
        }

        // TODO: #parser Provide [race-code => group] in configuration
        return [
            self::RACE_CODE => $match[1],
            self::RACE_GROUP => $match[2],
        ];
    }

    protected static function parseRow(string $row): ResultsTableRow
    {
        $columnsConfig = self::tableConfig();

        $result = new ResultsTableRow();
        foreach ($columnsConfig as $colName => $cnf) {
            $result->$colName = trim(mb_substr($row, $cnf['from'], $cnf['length']));
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
        $lines = array_filter($lines, fn($line) => WebResultsTableParser::validateRow($line));

        foreach ($lines as $line) {
            $results[] = WebResultsTableParser::parseRow($line);
        }

        return $results;
    }
}
