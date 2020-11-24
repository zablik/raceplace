<?php

namespace App\Service\ResultPageParsers\OBelarus;

use App\Entity\RaceResultsSource;
use App\Service\ResultPageParsers\DTO\ResultsTable;
use App\Service\ResultPageParsers\DTO\ResultsTableRow;
use App\Service\ResultPageParsers\WebDataParserInterface;
use App\Service\Utils\TextUtils;
use Symfony\Component\DomCrawler\Crawler;

class WebResultsTableParser implements WebDataParserInterface
{
    const RACE_TYPE = 'race-type';
    const RACE_GROUP = 'race-group';
    const RACE_DISTANCE = 'race-distance';
    const RACE_CODE = 'race-code';

    private static function getAvailableGroups()
    {
        return [
            TableConfig::GROUP__FEMALE,
            TableConfig::GROUP__MALE,
        ];
    }

    /**
     * @param string $html
     * @param RaceResultsSource $resultsSource
     * @return ResultsTable[]
     */
    public function parse(string $html, RaceResultsSource $resultsSource): array
    {
        $nodes = (new Crawler($html))
            ->filterXPath('//div[@id=\'results-body\']')
            ->children();

        $results = [];
        $raceCount = $resultsCount = 0;
        $skipFollowingContent = false;
        foreach ($nodes as $node) {
            if ($skipFollowingContent) {
                $skipFollowingContent = false;
                continue;
            }

            $results[$raceCount] = new ResultsTable();
            if ($node->nodeName === 'h2') {
                $raceParams = $this->parseRaceParams($node->nodeValue);
                if (is_null($raceParams)) {
                    $skipFollowingContent = true;
                    continue;
                }

                $results[$raceCount]->code = $raceParams[self::RACE_CODE];
                $results[$raceCount]->group = $raceParams[self::RACE_GROUP];
                $raceCount++;
            } elseif ($node->nodeName === 'pre') {
                $results[$resultsCount++]->results = $this->parseTextTable(
                    $node->nodeValue,
                    $resultsSource->getTableConfigType()
                );
            }
        }

        $results = array_filter($results, fn(ResultsTable $table) => !empty($table->code) && !empty($table->results));

        return $results;
    }

    protected function parseRaceParams(string $tableTitle): ?array
    {
        $tableTitle = TableConfig::customTableTitleConverter($tableTitle);
        $regex = sprintf('/^([a-zа-яё\d:\-]+)\s-.*-\s(%s)/iu', implode('|', self::getAvailableGroups()));
        if (!preg_match($regex, $tableTitle, $match)) {
            return null;
            //throw new ParseResultsException(sprintf('Unable to parse race params from title "%s"', $tableTitle));
        }

        // TODO: #parser Provide [race-code => group] in configuration
        return [
            self::RACE_CODE => trim($match[1], ':'),
            self::RACE_GROUP => $match[2],
        ];
    }

    protected static function parseRow(string $row, string $type): ResultsTableRow
    {
        $columnsConfig = TableConfig::getConfig($type);

        $result = new ResultsTableRow();
        foreach ($columnsConfig as $colName => $cnf) {
            $result->$colName = trim(mb_substr($row, $cnf['from'], $cnf['length']));
        }

        if ($hook = TableConfig::getHook($type)) {
            $result = $hook($result);
        }

        return $result;
    }

    protected static function validateRow(string $line): bool
    {
        return !empty(str_replace(['-', ' '], '', $line))
            && !preg_match('/(Фамилия Имя|Дистанция|Результат|Главный судья|Главный секретарь)/i', $line);
    }

    protected function parseTextTable(string $text, string $type): array
    {
        $results = [];

        $lines = TextUtils::splitLines($text);
        $lines = array_filter($lines, fn($line) => WebResultsTableParser::validateRow($line));

        foreach ($lines as $line) {
            $results[] = WebResultsTableParser::parseRow($line, $type);
        }

        return $results;
    }
}
